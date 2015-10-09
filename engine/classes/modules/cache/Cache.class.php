<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   LiveStreet Engine Social Networking by Mzhelskiy Maxim
 *   Site: www.livestreet.ru
 *   E-mail: rus.engine@gmail.com
 *----------------------------------------------------------------------------
 */

F::IncludeFile(C::Get('path.dir.libs') . '/DklabCache/config.php');
F::IncludeFile(LS_DKCACHE_PATH . 'Zend/Cache.php');
F::IncludeFile(LS_DKCACHE_PATH . 'Cache/Backend/MemcachedMultiload.php');
F::IncludeFile(LS_DKCACHE_PATH . 'Cache/Backend/TagEmuWrapper.php');
F::IncludeFile(LS_DKCACHE_PATH . 'Cache/Backend/Profiler.php');

/**
 * Типы кеширования: file и memory
 *
 */
define('SYS_CACHE_TYPE_FILE', 'file');
define('SYS_CACHE_TYPE_MEMORY', 'memory');
define('SYS_CACHE_TYPE_XCACHE', 'xcache');

/**
 * Модуль кеширования
 *
 * Для реализации кеширования используетс библиотека Zend_Cache с бэкэндами File, Memcached и XCache
 *
 * Т.к. в Memcached нет встроенной поддержки тегирования при кешировании, то для реализации тегов используется
 * враппер от Дмитрия Котерова - Dklab_Cache_Backend_TagEmuWrapper.
 *
 * Пример использования:
 * <pre>
 *    // Получает пользователя по его логину
 *    public function GetUserByLogin($sLogin) {
 *        // Пытаемся получить значение из кеша
 *        if (false === ($oUser = E::ModuleCache()->Get("user_login_{$sLogin}"))) {
 *            // Если значение из кеша получить не удалось, то обращаемся к базе данных
 *            $oUser = $this->oMapper->GetUserByLogin($sLogin);
 *            // Записываем значение в кеш
 *            E::ModuleCache()->Set($oUser, "user_login_{$sLogin}", array(), 60*60*24*5);
 *        }
 *        return $oUser;
 *    }
 *
 *    // Обновляет пользовател в БД
 *    public function UpdateUser($oUser) {
 *        // Удаляем кеш конкретного пользователя
 *        E::ModuleCache()->Delete("user_login_{$oUser->getLogin()}");
 *        // Удалем кеш со списком всех пользователей
 *        E::ModuleCache()->Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG,array('user_update'));
 *        // Обновлем пользовател в базе данных
 *        return $this->oMapper->UpdateUser($oUser);
 *    }
 *
 *    // Получает список всех пользователей
 *    public function GetUsers() {
 *        // Пытаемся получить значение из кеша
 *        if (false === ($aUserList = E::ModuleCache()->Get("users"))) {
 *            // Если значение из кеша получить не удалось, то обращаемся к базе данных
 *            $aUserList = $this->oMapper->GetUsers();
 *            // Записываем значение в кеш
 *            E::ModuleCache()->Set($aUserList, "users", array('user_update'), 60*60*24*5);
 *        }
 *        return $aUserList;
 *    }
 * </pre>
 *
 * @package engine.modules
 * @since   1.0
 */
class ModuleCache extends Module {

    const CACHE_MODE_NONE       = 0; // кеширование отключено
    const CACHE_MODE_AUTO       = 1; // включено автокеширование
    const CACHE_MODE_REQUEST    = 2; // кеширование только по запросу
    const CACHE_MODE_FORCE      = 4; // только принудительное кеширование

    const DISABLED_NONE     = 0;
    const DISABLED_SET      = 1;
    const DISABLED_GET      = 2;
    const DISABLED_ALL      = 3;

    const CHECK_FILENAME = 'cache.chk';

    /** @var int Запрет кеширования */
    protected $iDisabled = 0;

    /**
     * Доступные механизмы кеширования
     *
     * @var array
     */
    protected $aCacheTypesAvailable = array();

    /**
     * Доступные механизмы принудительного кеширования
     *
     * @var array
     */
    protected $aCacheTypesForce = array();

    /**
     * Объект бэкенда кеширования / LS-compatible /
     *
     * @var Zend_Cache_Backend
     */
    protected $oBackendCache = null;

    /**
     * Массив объектов движков кеширования
     *
     * @var ICacheBackend[]
     */
    protected $aBackends = array();

    /**
     * Используется кеширование или нет
     *
     * @var bool
     */
    protected $bUseCache;
    /**
     * Тип кеширования, прописан в глобльном конфиге config.php
     *
     * @var string
     */
    protected $sCacheType;

    protected $nCacheMode = self::CACHE_MODE_AUTO;

    protected $sCachePrefix;

    /**
     * Статистика кеширования
     *
     * @var array
     */
    protected $aStats
        = array(
            'time'      => 0,
            'count'     => 0,
            'count_get' => 0,
            'count_set' => 0,
        );

    /**
     * Коэффициент вероятности удаления старого кеша
     *
     * "@see Init
     *
     * @var int
     */
    protected $nRandClearOld = 50;

    /**
     * Инициализируем нужный тип кеша
     *
     */
    public function Init() {

        $this->bUseCache = C::Get('sys.cache.use');
        $this->sCacheType = C::Get('sys.cache.type');
        $this->sCachePrefix = $this->GetCachePrefix();

        $aCacheTypes = (array)C::Get('sys.cache.backends');

        // Доступные механизмы кеширования
        $this->aCacheTypesAvailable = array_map('strtolower', array_keys($aCacheTypes));

        // Механизмы принудительного кеширования
        $this->aCacheTypesForce = (array)C::Get('sys.cache.force');

        if ($this->aCacheTypesForce === true) {
            // Разрешены все
            $this->aCacheTypesForce = $this->aCacheTypesAvailable;
        } else {
            // Разрешены только те, которые есть в списке доступных
            $this->aCacheTypesForce = array_intersect(
                array_map('strtolower', $this->aCacheTypesForce), $this->aCacheTypesAvailable
            );
        }

        // По умолчанию кеширование данных полностью отключено
        $this->nCacheMode = self::CACHE_MODE_NONE;
        if ($this->_backendIsAvailable($this->sCacheType)) {
            if ($this->bUseCache) {
                // Включено автокеширование
                $this->nCacheMode = $this->nCacheMode | self::CACHE_MODE_AUTO | self::CACHE_MODE_REQUEST;
            } else {
                // Включено кеширование по запросу
                $this->nCacheMode = $this->nCacheMode | self::CACHE_MODE_REQUEST;
            }
            // Инициализация механизма кеширования по умолчанию
            $this->_backendInit($this->sCacheType);
        }
        if ($this->aCacheTypesForce) {
            // Разрешено принудительное кеширование
            $this->nCacheMode = $this->nCacheMode | self::CACHE_MODE_FORCE;
        }
        if ($this->nCacheMode != self::CACHE_MODE_NONE) {
            // Дабы не засорять место протухшим кешем, удаляем его в случайном порядке, например 1 из 50 раз
            if (rand(1, $this->nRandClearOld) == 33) {
                $this->Clean(Zend_Cache::CLEANING_MODE_OLD);
            }
        }

        $sCheckFile = C::Get('sys.cache.dir') . self::CHECK_FILENAME;
        if (F::File_CheckDir(C::Get('sys.cache.dir'), true)) {
            // If the control file is not present, then we need to clear cache and create
            if (!F::File_Exists($sCheckFile)) {
                $this->Clean();
            }
        }
        return $this->nCacheMode;
    }

    /**
     * @return string
     */
    public function GetCachePrefix() {

        $sUniqKey = C::Get(C::ALTO_UNIQUE_KEY);
        if (!$sUniqKey) {
            $sUniqKey = E::ModuleSecurity()->GenerateUniqKey();
        }
        return C::Get('sys.cache.prefix') . '_' . F::Crc32($sUniqKey, true);
    }

    /**
     * Проверка режима кеширования
     *
     * @param   string|null $sCacheType
     *
     * @return   int
     */
    protected function _cacheOn($sCacheType = null) {

        if (is_null($sCacheType)) {
            return $this->nCacheMode & self::CACHE_MODE_AUTO;
        } elseif ($sCacheType === true) {
            return $this->nCacheMode & self::CACHE_MODE_REQUEST;
        } elseif (in_array($sCacheType, $this->aCacheTypesForce)) {
            return $this->nCacheMode & self::CACHE_MODE_FORCE;
        }
        return self::CACHE_MODE_NONE;
    }

    /**
     * Инициализация бэкенда кеширования
     *
     * @param string $sCacheType
     *
     * @return string|null
     *
     * @throws Exception
     */
    protected function _backendInit($sCacheType) {

        if (is_string($sCacheType)) {
            $sCacheType = strtolower($sCacheType);
        } elseif ($sCacheType === true || is_null($sCacheType)) {
            $sCacheType = $this->sCacheType;
        }
        if ($sCacheType) {
            if (!isset($this->aBackends[$sCacheType])) {
                if (!in_array($sCacheType, $this->aCacheTypesAvailable)) {

                    // Unknown cache type
                    throw new Exception('Wrong type of caching: ' . $this->sCacheType);
                } else {
                    $aCacheTypes = (array)C::Get('sys.cache.backends');
                    $sClass = 'CacheBackend' . $aCacheTypes[$sCacheType];
                    $sFile = './backend/' . $sClass . '.class.php';
                    if (!F::IncludeFile($sFile)) {
                        throw new Exception('Cannot include cache backend file: ' . basename($sFile));
                    } elseif (!class_exists($sClass, false)) {
                        throw new Exception('Cannot load cache backend class: ' . $sClass);
                    } else {
                        if (!$sClass::IsAvailable() || !($oBackendCache = $sClass::Init(array($this, 'CalcStats')))) {
                            throw new Exception('Cannot use cache type: ' . $sCacheType);
                        } else {
                            $this->aBackends[$sCacheType] = $oBackendCache;
                            //* LS-compatible *//
                            if ($sCacheType == $this->sCacheType) {
                                $this->oBackendCache = $oBackendCache;
                            }
                            //$oBackendCache = null;
                            return $sCacheType;
                        }
                    }
                }
            } else {
                return $sCacheType;
            }
        }
        return null;
    }

    /**
     * The cache type is available
     *
     * @param string $sCacheType
     *
     * @return bool
     */
    protected function _backendIsAvailable($sCacheType) {

        if (is_null($sCacheType) || $sCacheType === true) {
            $sCacheType = $this->sCacheType;
        }
        return $sCacheType && in_array($sCacheType, $this->aCacheTypesAvailable);
    }

    /**
     * @param string $sCacheType
     *
     * @return bool
     * @throws Exception
     */
    protected function _backendIsMultiLoad($sCacheType) {

        if ($sCacheType = $this->_backendInit($sCacheType)) {
            return $this->aBackends[$sCacheType]->IsMultiLoad();
        }
        return false;
    }

    protected function _backendIsConcurrent($sCacheType) {

        if (is_null($sCacheType) || $sCacheType === true) {
            $sCacheType = $this->sCacheType;
        }
        if ($this->_backendInit($sCacheType) && ($sCacheType != 'tmp')) {
            return intval(C::Get('sys.cache.concurrent_delay'));
        }
        return 0;
    }

    /**
     * Внутренний метод получения данных из конкретного вида кеша
     *
     * @param   string  $sCacheType
     * @param   string  $sHash
     *
     * @return  bool|mixed
     */
    protected function _backendLoad($sCacheType, $sHash) {

        if ($sCacheType = $this->_backendInit($sCacheType)) {
            return $this->aBackends[$sCacheType]->Load($sHash);
        }
        return false;
    }

    /**
     * Внутренний метод сохранения данных в конкретном виде кеша
     *
     * @param string   $sCacheType
     * @param mixed    $xData
     * @param string   $sHash
     * @param string[] $aTags
     * @param int      $nTimeLife
     *
     * @return bool
     */
    protected function _backendSave($sCacheType, $xData, $sHash, $aTags, $nTimeLife) {

        if ($sCacheType = $this->_backendInit($sCacheType)) {
            return $this->aBackends[$sCacheType]->Save($xData, $sHash, $aTags, $nTimeLife ? $nTimeLife : false);
        }
        return false;
    }

    /**
     * Внутренний метод сброса кеша по ключу
     *
     * @param string $sCacheType
     * @param string $sHash
     *
     * @return bool
     */
    protected function _backendRemove($sCacheType, $sHash) {

        // Если тип кеша задан, то сбрасываем у него
        if ($sCacheType && isset($this->aBackends[$sCacheType])) {
            return $this->aBackends[$sCacheType]->Remove($sHash);
        } else {
            // Иначе сбрасываем у всех типов кеша
            foreach ($this->aBackends as $oBackend) {
                $oBackend->Remove($sHash);
            }
            return true;
        }
    }

    /**
     * Internal method for clearing of cache
     *
     * @param $sCacheType
     * @param $sMode
     * @param $aTags
     *
     * @return bool
     */
    protected function _backendClean($sCacheType, $sMode, $aTags) {

        // Если тип кеша задан, то сбрасываем у него
        if ($sCacheType && isset($this->aBackends[$sCacheType])) {
            return $this->aBackends[$sCacheType]->Clean($sMode, $aTags);
        } else {
            // Иначе сбрасываем у всех типов кеша
            foreach ($this->aBackends as $oBackend) {
                $oBackend->Clean($sMode, $aTags);
            }
            return true;
        }
    }

    /**
     * Хеширование имени кеш-ключа
     *
     * @param string $sKey
     *
     * @return string
     */
    protected function _hash($sKey) {

        return md5($this->sCachePrefix . $sKey);
    }

    protected function _prepareTags($aTags) {

        // Теги - это массив строковых значений
        if (empty($aTags)) {
            $aTags = array();
        } elseif (!is_array($aTags)) {
            if (!is_string($aTags)) {
                $aTags = array();
            } else {
                $aTags = array((string)$aTags);
            }
        } else {
            $aTags = array_map('strval', $aTags);
        }
        return $aTags;
    }

    /**
     * @param string $sCacheType
     *
     * @return bool
     */
    public function CacheTypeAvailable($sCacheType) {

        return $this->_backendIsAvailable($sCacheType);
    }

    /**
     * Записать значение в кеш
     *
     * The following life time periods are recognized:
     * <pre>
     * Time interval    | Number of seconds
     * ----------------------------------------------------
     * 3600             | 3600 seconds
     * 2 hours          | Two hours = 60 * 60 * 2 = 7200 seconds
     * 1 day + 12 hours | One day and 12 hours = 60 * 60 * 24 + 60 * 60 * 12 = 129600 seconds
     * 3 months         | Three months = 3 * 30 days = 3 * (60 * 60 * 24 * 30) = 7776000 seconds
     * PT3600S          | 3600 seconds
     * P1DT12H          | One day and 12 hours = 60 * 60 * 24 + 60 * 60 * 12 = 129600 seconds
     * P3M              | Three months = 3 * 30 days = 3 * (60 * 60 * 24 * 30) = 7776000 seconds
     * ----------------------------------------------------
     * Full ISO 8601 interval format: PnYnMnDTnHnMnS
     * </pre>
     *
     * @param   mixed               $xData      - Данные для хранения в кеше
     * @param   string              $sCacheKey  - Имя ключа кеширования
     * @param   array               $aTags      - Список тегов, для возможности удалять сразу несколько кешей по тегу
     * @param   string|int|bool     $nTimeLife  - Время жизни кеша (в секундах или в строковом интервале)
     * @param   string|bool|null    $sCacheType - Тип используемого кеша
     *
     * @return  bool
     */
    public function Set($xData, $sCacheKey, $aTags = array(), $nTimeLife = false, $sCacheType = null) {

        if ($sCacheType && strpos($sCacheType, ',') !== false) {
            $aCacheTypes = explode(',', $sCacheType);
            $bResult = false;
            foreach($aCacheTypes as $sCacheType) {
                if (!$sCacheType && in_array($this->sCacheType, $aCacheTypes)) {
                    // skip double cache
                    continue;
                }
                $bResult = $bResult || $this->Set($xData, $sCacheKey, $aTags, $nTimeLife, $sCacheType ? $sCacheType : null);
            }
            return $bResult;
        }

        // Проверяем возможность кеширования
        $nMode = $this->_cacheOn($sCacheType);
        if (!$nMode) {
            return false;
        }

        /*
        // Если модуль завершил свою работу и не включено принудительное кеширование, то ничего не кешируется
        if ($this->isDone() && ($nMode != self::CACHE_MODE_FORCE)) {
            return false;
        }
        */
        if (($this->iDisabled & self::DISABLED_SET) && ($nMode != self::CACHE_MODE_FORCE)) {
            return false;
        }

        // Теги - это массив строковых значений
        $aTags = $this->_prepareTags($aTags);

        if (is_string($nTimeLife)) {
            $nTimeLife = F::ToSeconds($nTimeLife);
        } else {
            $nTimeLife = intval($nTimeLife);
        }
        if (!$sCacheType) {
            $sCacheType = $this->sCacheType;
        }

        // Если необходимо разрешение конкурирующих запросов к кешу, то реальное время жизни кеша увеличиваем
        if ($nTimeLife && ($nConcurrentDaley = $this->_backendIsConcurrent($sCacheType))) {
            $aData = array(
                'time' => time() + $nTimeLife,  // контрольное время жизни кеша
                'tags' => $aTags,               // теги, чтобы можно было пересохранить данные
                'data' => $xData,               // сами данные
            );
            $nTimeLife += $nConcurrentDaley;
        } else {
            $aData = array(
                'time' => false,   // контрольное время не сохраняем, конкурирующие запросы к кешу игнорируем
                'tags' => $aTags,
                'data' => $xData,
            );
        }
        return $this->_backendSave($sCacheType, $aData, $this->_hash($sCacheKey), $aTags, $nTimeLife);
    }

    /**
     * Получить значение из кеша
     *
     * @param   string|array $xCacheKey  - Имя ключа кеширования
     * @param   string|null  $sCacheType - Механизм используемого кеширования
     *
     * @return mixed|bool
     */
    public function Get($xCacheKey, $sCacheType = null) {

        if ($sCacheType && strpos($sCacheType, ',') !== false) {
            $aCacheTypes = explode(',', $sCacheType);
            $xResult = false;
            foreach($aCacheTypes as $sCacheType) {
                $xResult = $this->Get($xCacheKey, $sCacheType ? $sCacheType : null);
                if ($xResult !== false) {
                    return $xResult;
                }
            }
            return $xResult;
        }

        // Checks the possibility of caching and prohibition of caching
        if (!$this->_cacheOn($sCacheType) || ($this->iDisabled & self::DISABLED_GET)) {
            return false;
        }

        if (!is_array($xCacheKey)) {
            $aData = $this->_backendLoad($sCacheType, $this->_hash($xCacheKey));
            if (is_array($aData) && array_key_exists('data', $aData)) {
                // Если необходимо разрешение конкурирующих запросов...
                if (isset($aData['time']) && ($nConcurrentDaley = $this->_backendIsConcurrent($sCacheType))) {
                    if ($aData['time'] < time()) {
                        // Если данные кеша по факту "протухли", то пересохраняем их с доп.задержкой и без метки времени
                        // За время задержки кеш должен пополниться свежими данными
                        $aData['time'] = false;
                        $this->_backendSave($sCacheType, $aData, $this->_hash($xCacheKey), $aData['tags'], $nConcurrentDaley);
                        return false;
                    }
                }
                return $aData['data'];
            }
        } else {
            return $this->MultiGet($xCacheKey, $sCacheType);
        }
        return false;
    }

    /**
     * Поддержка мульти-запросов к кешу
     *
     * Если движок кеша не поддерживает такие запросы, то делаем эмуляцию
     *
     * @param   array   $aCacheKeys     - Массив ключей кеширования
     * @param   string  $sCacheType     - Тип кеша
     *
     * @return bool|array
     */
    public function MultiGet($aCacheKeys, $sCacheType = null) {

        if (count($aCacheKeys) == 0 || !$this->_cacheOn($sCacheType)) {
            return false;
        }
        if ($this->_backendIsMultiLoad($sCacheType)) {
            $aHashKeys = array();
            $aTmpKeys = array();
            foreach ($aCacheKeys as $sCacheKey) {
                $sHash = $this->_hash($sCacheKey);
                $aHashKeys[] = $sHash;
                $aTmpKeys[$sHash] = $sCacheKey;
            }
            $data = $this->_backendLoad($sCacheType, $aHashKeys);
            if ($data && is_array($data)) {
                $aData = array();
                foreach ($data as $key => $value) {
                    $aData[$aTmpKeys[$key]] = $value['data'];
                }
                if (count($aData) > 0) {
                    return $aData;
                }
            }
            return false;
        } else {
            $aData = array();
            foreach ($aCacheKeys as $sCacheKey) {
                if ((false !== ($data = $this->Get($sCacheKey, $sCacheType)))) {
                    $aData[$sCacheKey] = $data;
                }
            }
            if (count($aData) > 0) {
                return $aData;
            }
            return false;
        }
    }

    /**
     * LS-compatible
     *
     * @param   mixed           $data       - Данные для хранения в кеше
     * @param   string          $sCacheKey  - Имя ключа кеширования
     * @param   array           $aTags      - Список тегов, для возможности удалять сразу несколько кешей по тегу
     * @param   string|int|bool $nTimeLife  - Время жизни кеша (в секундах или в строковом интервале)
     *
     * @return  bool
     */
    public function SmartSet($data, $sCacheKey, $aTags = array(), $nTimeLife = false) {

        return $this->Set($data, $sCacheKey, $aTags, $nTimeLife);
    }

    /**
     * LS-compatible
     *
     * @param  string      $sCacheKey      - Имя ключа
     * @param  string|null $sCacheType     - Механизм используемого кеширования
     *
     * @return bool|mixed
     */
    public function SmartGet($sCacheKey, $sCacheType = null) {

        return $this->Get($sCacheKey, $sCacheType);
    }

    /**
     * Delete cache value by its key
     *
     * @param string      $sCacheKey    - Name of cache key
     * @param string|null $sCacheType   - Type of cache (if null then clear in all cache types)
     *
     * @return bool
     */
    public function Delete($sCacheKey, $sCacheType = null) {

        if ($sCacheType && strpos($sCacheType, ',') !== false) {
            $aCacheTypes = explode(',', $sCacheType);
            $bResult = false;
            foreach($aCacheTypes as $sCacheType) {
                $bResult = $bResult || $this->Delete($sCacheKey, $sCacheType ? $sCacheType : null);
            }
            return $bResult;
        }

        if (!$this->bUseCache) {
            return false;
        }
        if (is_array($sCacheKey)) {
            foreach ($sCacheKey as $sItemName) {
                $this->_backendRemove($sCacheType, $this->_hash($sItemName));
            }
            return true;
        } else {
            return $this->_backendRemove($sCacheType, $this->_hash($sCacheKey));
        }
    }

    /**
     * Clear cache
     *
     * @param string      $sMode
     * @param array       $aTags
     * @param string|null $sCacheType - Type of cache (if null then clear in all cache types)
     *
     * @return  bool
     */
    public function Clean($sMode = Zend_Cache::CLEANING_MODE_ALL, $aTags = array(), $sCacheType = null) {

        // Проверим разрешено ли кэширование
        if ($this->_cacheOn($sCacheType)) {
            $aTags = $this->_prepareTags($aTags);
            $bResult = $this->_backendClean($sCacheType, $sMode, $aTags);
        } else {
            $bResult = false;
        }
        F::File_PutContents(C::Get('sys.cache.dir') . self::CHECK_FILENAME, microtime(true));

        return $bResult;
    }

    /**
     * Clear cache by tags
     *
     * @param array       $aTags      - Array of tags
     * @param string|null $sCacheType - Type of cache (if null then clear in all cache types)
     *
     * @return bool
     */
    public function CleanByTags($aTags, $sCacheType = null) {

        $aTags = $this->_prepareTags($aTags);

        if ($sCacheType && strpos($sCacheType, ',') !== false) {
            $aCacheTypes = explode(',', $sCacheType);
            $bResult = false;
            foreach($aCacheTypes as $sCacheType) {
                $bResult = $bResult || $this->CleanByTags($aTags, $sCacheType ? $sCacheType : null);
            }
            return $bResult;
        }

        return $this->Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, $aTags, $sCacheType);
    }

    /**
     * Подсчет статистики использования кеша
     *
     * @param int    $iTime      - Время выполнения метода
     * @param string $sMethod    - Имя метода
     */
    public function CalcStats($iTime, $sMethod) {

        $this->aStats['time'] += $iTime;
        $this->aStats['count']++;
        if ($sMethod == 'Dklab_Cache_Backend_Profiler::load') {
            $this->aStats['count_get']++;
        }
        if ($sMethod == 'Dklab_Cache_Backend_Profiler::save') {
            $this->aStats['count_set']++;
        }
    }

    /**
     * Возвращает статистику использования кеша
     *
     * @return array
     */
    public function GetStats() {

        return $this->aStats;
    }

    /**
     * Сохраняет значение в сверхбыстром временном кеше (кеш времени исполнения скрипта)
     *
     * @param mixed $data
     * @param string $sCacheKey
     */
    public function SetTmp($data, $sCacheKey) {

        $this->Set($data, $sCacheKey, array(), false, 'tmp');
    }

    /**
     * Получает значение из сверхбыстрого временного кеша (кеш времени исполнения скрипта)
     *
     * @param string $sCacheKey    - Имя ключа кеширования
     *
     * @return mixed
     */
    public function GetTmp($sCacheKey) {

        return $this->Get($sCacheKey, 'tmp');
    }

    /**
     * LS-compatible
     * @deprecated
     * @param mixed  $data         - Данные для сохранения в кеше
     * @param string $sCacheKey    - Имя ключа кеширования
     */
    public function SetLife($data, $sCacheKey) {

        $this->SetTmp($data, $sCacheKey);
    }

    /**
     * LS-compatible
     * @deprecated
     * @param string $sCacheKey    - Имя ключа кеширования
     *
     * @return mixed
     */
    public function GetLife($sCacheKey) {

        return $this->GetTmp($sCacheKey);
    }


    public function SetDesabled($xFlag) {

        if ($xFlag === true) {
            $this->iDisabled = self::DISABLED_ALL;
        } elseif ($xFlag === false) {
            $this->iDisabled = self::DISABLED_NONE;
        } else {
            $this->iDisabled = intval($xFlag);
        }
    }

    public function GetDisabled() {

        return $this->iDisabled;
    }

}

// EOF