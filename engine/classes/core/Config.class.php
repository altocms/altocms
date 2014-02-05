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

/**
 * Управление простым конфигом в виде массива
 *
 * @package engine.lib
 * @since   1.0
 */
class Config extends Storage {

    const LEVEL_MAIN        = 0;
    const LEVEL_APP         = 1;
    const LEVEL_CUSTOM      = 2;
    const LEVEL_ACTION      = 3;
    const LEVEL_SKIN        = 4;
    const LEVEL_SKIN_CUSTOM = 5;

    /**
     * Default config root key
     *
     * @var string
     */
    const DEFAULT_CONFIG_ROOT = '__config__';

    const KEY_LINK_STR = '___';
    const KEY_LINK_PREG = '~___([\S|\.]+)(___/|___)~Ui';

    const CUSTOM_CONFIG_PREFIX = 'custom.config.';

    /**
     * Mapper rules for Config Path <-> Constant Name relations
     *
     * @var array
     */
    static protected $aMapper = array();

    static protected $bRereadCustomConfig = false;

    protected $nSaveMode = self::SAVE_MODE_ARR;

    /**
     * Local quick cache
     *
     * @var array
     */
    protected $aQuickMap = array();

    /**
     * Stack levels
     *
     * @var array
     */
    protected $aLevel = array();

    /**
     * Current level
     *
     * @var int
     */
    protected $nLevel = 0;

    /**
     * Disabled constract process
     */
    public function __construct() {

    }

    /**
     * Clear quick map storage
     */
    protected function _clearQuickMap() {

        $this->aQuickMap = array();
    }

    /**
     * Load configuration array from file
     *
     * @param string $sFile    - Путь до файла конфига
     * @param bool   $bReset   - Сбосить старые значения
     * @param string $sRootKey - Корневой ключ конфига
     * @param int    $nLevel   - Уровень конфига
     *
     * @return  bool|Config
     */
    static public function LoadFromFile($sFile, $bReset = true, $sRootKey = null, $nLevel = null) {

        if (is_integer($sRootKey) && is_null($nLevel)) {
            $nLevel = $sRootKey;
            $sRootKey = null;
        }

        // Check if file exists
        if (!F::File_Exists($sFile)) {
            return false;
        }
        // Get config from file
        if ($aConfig = F::File_IncludeFile($sFile, true, true)) {
            return static::Load($aConfig, $bReset, $sRootKey, $nLevel);
        }
    }

    /**
     * Add configuration array from file
     *
     * @param string $sFile
     * @param string $sRootKey
     * @param int    $nLevel
     *
     * @return bool|Config
     */
    static public function AddFromFile($sFile, $sRootKey = null, $nLevel = null) {

        return static::LoadFromFile($sFile, false, $sRootKey, $nLevel);
    }

    /**
     * Loads configuration array from given array
     *
     * @param array  $aConfig  - Массив конфига
     * @param bool   $bReset   - Сбросить старые значения
     * @param string $sRootKey - Корневой ключ конфига
     * @param int    $nLevel   - Уровень конфига
     *
     * @return  bool|Config
     */
    static public function Load($aConfig, $bReset = true, $sRootKey = null, $nLevel = null) {

        if (is_integer($sRootKey) && is_null($nLevel)) {
            $nLevel = $sRootKey;
            $sRootKey = null;
        }

        // Check if it`s array
        if (!is_array($aConfig)) {
            return false;
        }
        // Set config to current or handle instance
        static::getInstance()->SetConfig($aConfig, $bReset, $sRootKey, $nLevel);
        return static::getInstance();
    }

    /**
     * Makes storage key using root key & level
     *
     * @param null $sRootKey
     * @param null $nLevel
     *
     * @return string
     */
    protected function _storageKey($sRootKey = null, $nLevel = null) {

        if (is_null($nLevel)) {
            $nLevel = ($this->nLevel ? $this->nLevel : 0);
        }
        if (!$sRootKey) {
            $sRootKey = self::DEFAULT_CONFIG_ROOT;
        }
        return $sRootKey . '.__[' . $nLevel . ']__';
    }

    /**
     * Возвращает текущий полный конфиг
     *
     * @param string $sKey   - Корневой ключ конфига
     * @param int    $nLevel - Уровень конфига
     *
     * @return  array
     */
    public function GetConfig($sKey = null, $nLevel = null) {

        if (is_null($nLevel)) {
            $nLevel = $this->nLevel;
        }
        /*
        $aResult = array();
        for ($n = 0; $n <= $nLevel; $n++) {
            $sStorageKey = $this->_storageKey($sKey, $n);
            if ($aConfig = parent::GetStorage($sStorageKey)) {
                $aResult = F::Array_Merge($aResult, $aConfig);
            }
        }
        */
        $sStorageKey = $this->_storageKey($sKey, $nLevel);
        $aResult = parent::GetStorage($sStorageKey);
        if (!$aResult) {
            $aResult = array();
        }
        return $aResult;
    }

    /**
     * Устанавливает значения конфига
     *
     * @param array  $aConfig  - Массив конфига
     * @param bool   $bReset   - Сбросить старые значения
     * @param string $sRootKey - Корневой ключ конфига
     * @param int    $nLevel   - Уровень конфига
     *
     * @return  bool
     */
    public function SetConfig($aConfig = array(), $bReset = true, $sRootKey = null, $nLevel = null) {

        if (is_integer($sRootKey) && is_null($nLevel)) {
            $nLevel = $sRootKey;
            $sRootKey = self::DEFAULT_CONFIG_ROOT;
        }

        $this->_clearQuickMap();
        if (is_null($nLevel)) {
            $nLevel = $this->nLevel;
        }
        $sStorageKey = $this->_storageKey($sRootKey, $nLevel);
        return parent::SetStorage($sStorageKey, $aConfig, $bReset);
    }

    public function _isExists($sKey, $sRoot = self::DEFAULT_CONFIG_ROOT) {

        $sStorageKey = $this->_storageKey($sRoot);
        return parent::IsExists($sStorageKey, $sKey);
    }

    /**
     * Очистка заданного (или текущего) уровня конфигурации
     *
     * @param null $nLevel
     */
    public function _clearLevel($nLevel = null) {

        $this->SetConfig(null, true, null, $nLevel);
    }

    /**
     * Установка нового уровня конфигурации
     *
     * @param int  $nLevel
     * @param bool $bClearLevel
     * @param bool $bClearBetween
     */
    public function _setLevel($nLevel = null, $bClearLevel = true, $bClearBetween = false) {

        if ($nLevel > $this->nLevel) {
            $aConfig = $this->GetConfig(null, $this->nLevel);
            while ($nLevel > $this->nLevel) {
                if ($bClearBetween) {
                    $this->_clearLevel(++$this->nLevel);
                } else {
                    $this->SetConfig($aConfig, false, null, ++$this->nLevel);
                }
            }
        } elseif ($nLevel < $this->nLevel) {
            while ($nLevel < $this->nLevel) {
                if ($bClearBetween) {
                    $this->_clearLevel($this->nLevel--);
                } else {
                    $this->SetConfig(array(), false, null, $this->nLevel--);
                }
            }
        } else {
            if ($bClearLevel) {
                $aConfig = $this->GetConfig(null, $nLevel-1);
                if ($aConfig) {
                    $this->SetConfig($aConfig, true, null, $nLevel);
                } else {
                    $this->SetConfig(array(), true, null, $nLevel);
                }
            }
        }
        $this->nLevel = $nLevel;
    }

    static public function SetLevel($nLevel, $bClearBetween = false) {

        return static::getInstance()->_setLevel($nLevel, false, $bClearBetween);
    }

    static public function ResetLevel($nLevel, $bClearBetween = false) {

        return static::getInstance()->_setLevel($nLevel, true, $bClearBetween);
    }

    /**
     * Retrive information from configuration array
     *
     * @param string $sKey     - Ключ
     * @param string $sRootKey - Корневой ключ конфига
     * @param int    $nLevel
     *
     * @return mixed
     */
    static public function Get($sKey = '', $sRootKey = null, $nLevel = null) {

        if (is_integer($sRootKey) && is_null($nLevel)) {
            $nLevel = $sRootKey;
            $sRootKey = null;
        }
        // Return all config array
        if (!$sKey) {
            return static::getInstance()->GetConfig($sRootKey, $nLevel);
        }

        return static::getInstance()->GetValue($sKey, $sRootKey, $nLevel);
    }

    /**
     * As a method Get() but with default value
     *
     * @param string $sKey
     * @param mixed  $xDefault
     *
     * @return mixed|null
     */
    static public function Val($sKey = '', $xDefault = null) {

        $sValue = static::Get($sKey);
        return is_null($sValue) ? $xDefault : $sValue;
    }

    /**
     * Получает значение из конфигурации по переданному ключу
     *
     * @param string $sKey     - Ключ
     * @param string $sRootKey - Корневой ключ конфига
     * @param int    $nLevel
     *
     * @return mixed
     */
    public function GetValue($sKey, $sRootKey = null, $nLevel = null) {

        $sKeyMap = $sRootKey . '.' . (is_null($nLevel) ? '' : ($nLevel . '.')) . $sKey;
        if (!isset($this->aQuickMap[$sKeyMap])) {
            // Return config by path (separator=".")
            $aKeys = explode('.', $sKey);

            $cfg = $this->GetConfig($sRootKey, $nLevel);
            foreach ((array)$aKeys as $sK) {
                if (isset($cfg[$sK])) {
                    $cfg = $cfg[$sK];
                } else {
                    return null;
                }
            }

            $cfg = static::KeyReplace($cfg, $sRootKey);
            $this->aQuickMap[$sKeyMap] = $cfg;
        }

        return $this->aQuickMap[$sKeyMap];
    }

    /**
     * Заменяет плейсхолдеры ключей в значениях конфига
     *
     * @static
     *
     * @param string|array $xCfg  - Значения конфига
     * @param string       $sRoot - Корневой ключ конфига
     *
     * @return array|mixed
     */
    static public function KeyReplace($xCfg, $sRoot = self::DEFAULT_CONFIG_ROOT) {

        if (is_array($xCfg)) {
            $xResult = array();
            foreach ($xCfg as $k => $v) {
                if (strpos($k, self::KEY_LINK_STR) !== false) {
                    $sNewKey = static::KeyReplace($k, $sRoot);
                } else {
                    $sNewKey = $k;
                }
                $xResult[$sNewKey] = static::KeyReplace($v, $sRoot);
                unset($xCfg[$k]);
            }
        } else {
            $xResult = $xCfg;
            if (strpos($xCfg, self::KEY_LINK_STR) !== false
                && preg_match_all(self::KEY_LINK_PREG, $xCfg, $aMatch, PREG_SET_ORDER)
            ) {
                if (count($aMatch) == 1 && $aMatch[0][0] == $xCfg) {
                    $xResult = Config::Get($aMatch[0][1], $sRoot);
                } else {
                    foreach ($aMatch as $aItem) {
                        $sReplacement = Config::Get($aItem[1], $sRoot);
                        if ($aItem[2] == '___/' && substr($sReplacement, -1) != '/' && substr($sReplacement, -1) != '\\') {
                            $sReplacement .= '/';
                        }
                        $xResult = str_replace(self::KEY_LINK_STR . $aItem[1] . $aItem[2], $sReplacement, $xResult);
                    }
                }
            }
        }
        return $xResult;
    }

    /**
     * Try to find element by given key
     * Using function ARRAY_KEY_EXISTS (like in SPL)
     *
     * Workaround for http://bugs.php.net/bug.php?id=40442
     *
     * @param string $sKey  - Path to needed value
     * @param string $sRoot - Name of needed instance
     *
     * @return bool
     */
    static public function isExist($sKey, $sRoot = self::DEFAULT_CONFIG_ROOT) {

        return static::getInstance()->_isExists($sKey, $sRoot);
    }

    /**
     * Add information in config array by handle path
     *
     * @param string $sKey   - Ключ
     * @param mixed  $xValue - Значение
     * @param string $sRoot  - Корневой ключ конфига
     * @param int    $nLevel
     *
     * @return bool
     */
    static public function Set($sKey, $xValue, $sRoot = self::DEFAULT_CONFIG_ROOT, $nLevel = null) {

        if (is_integer($sRoot) && is_null($nLevel)) {
            $nLevel = $sRoot;
            $sRoot = self::DEFAULT_CONFIG_ROOT;
        }
        if (isset($xValue['$root$']) && is_array($xValue['$root$'])) {
            $aRoot = $xValue['$root$'];
            unset($xValue['$root$']);
            foreach ($aRoot as $sRootKey => $xVal) {
                if (static::isExist($sRootKey)) {
                    static::Set($sRootKey, F::Array_MergeCombo(Config::Get($sRootKey, $sRoot), $xVal), $sRoot, $nLevel);
                } else {
                    static::Set($sRootKey, $xVal, $sRoot, $nLevel);
                }
            }
        }

        static::getInstance()->SetConfig(array($sKey => $xValue), false, $sRoot, $nLevel);

        return true;
    }

    /**
     * Find all keys recursivly in config array
     *
     * @return array
     */
    public function GetKeys() {

        $aConfig = $this->GetConfig();
        // If it`s not array, return key
        if (!is_array($aConfig) || !count($aConfig)) {
            return false;
        }
        // If it`s array, get array_keys recursive
        return F::Array_KeysRecursive($aConfig);
    }

    /**
     * Записывает кастомную конфигурацию
     *
     * @param array $aConfig
     * @param bool  $bCacheOnly
     *
     * @return  bool
     */
    static public function WriteCustomConfig($aConfig, $bCacheOnly = false) {

        $aData = array();
        foreach ($aConfig as $sKey => $sVal) {
            $aData[] = array(
                'storage_key' => self::CUSTOM_CONFIG_PREFIX . $sKey,
                'storage_val' => serialize($sVal),
            );
        }
        if ($bCacheOnly || ($bResult = E::Admin_UpdateCustomConfig($aData))) {
            self::_putCustomCfg($aConfig);
            return true;
        }
        return false;
    }

    /**
     * @param string|null $sKeyPrefix
     * @param bool        $bCacheOnly
     *
     * @return array
     */
    static public function ReadCustomConfig($sKeyPrefix = null, $bCacheOnly = false) {

        $aConfig = array();
        if (self::_checkCustomCfg(!$bCacheOnly)) {
            $aConfig = self::_getCustomCfg();
        }
        if (!$aConfig) {
            if (!$bCacheOnly) {
                // Перечитаем конфиг из базы
                $sPrefix = self::CUSTOM_CONFIG_PREFIX . $sKeyPrefix;
                $aData = E::Admin_GetCustomConfig($sPrefix);
                if ($aData) {
                    $nPrefixLen = strlen($sPrefix);
                    $aConfig = array();
                    foreach ($aData as $aRow) {
                        $sKey = substr($aRow['storage_key'], $nPrefixLen);
                        $xVal = @unserialize($aRow['storage_val']);
                        $aConfig[$sKey] = $xVal;
                    }
                }
                // Признак того, что кеш конфига синхронизирован с базой
                $aConfig['_db_'] = time();
                self::_putCustomCfg($aConfig);
            } else {
                // Признак того, что кеш конфига НЕ синхронизиован с базой
                $aConfig['_db_'] = false;
            }
        }
        return $aConfig;
    }

    /**
     *
     */
    static public function ReReadCustomConfig() {

        self::ReadCustomConfig(null, false);
    }

    /**
     * @param string|null $sKeyPrefix
     */
    static public function ResetCustomConfig($sKeyPrefix = null) {

        $sPrefix = self::CUSTOM_CONFIG_PREFIX . $sKeyPrefix;
        // удаляем настройки конфига из базы
        E::Admin_DelCustomConfig($sPrefix);
        // удаляем кеш-файл
        self::_deleteCustomCfg();
        // перестраиваем конфиг в кеш-файле
        self::ReReadCustomConfig();
    }

    /**
     * Возвращает полный путь к кеш-файлу кастомной конфигуации
     * или просто проверяет его наличие
     *
     * @param bool $bCheckOnly
     *
     * @return  string
     */
    static protected function _checkCustomCfg($bCheckOnly = false) {

        $sFile = self::Get('sys.cache.dir') . 'data/custom.cfg';
        if ($bCheckOnly) {
            return F::File_Exists($sFile);
        }
        return $sFile;
    }

    /**
     * Удаляет кеш-файл кастомной конфигуации
     *
     */
    static protected function _deleteCustomCfg() {

        $sFile = self::_checkCustomCfg(true);
        if ($sFile) {
            F::File_Delete($sFile);
        }
    }

    /**
     * Сохраняет в файловом кеше кастомную конфигурацию
     *
     * @param $aConfig
     * @param $bReset
     */
    static protected function _putCustomCfg($aConfig, $bReset = false) {

        if (is_array($aConfig) && ($sFile = self::_checkCustomCfg())) {
            $aConfig['_timestamp_'] = time();
            if (!$bReset) {
                // Объединяем текущую конфигурацию с сохраняемой
                $aOldConfig = self::_getCustomCfg();
                if ($aOldConfig) {
                    $aConfig = F::Array_Merge($aOldConfig, $aConfig);
                }
            }
            F::File_PutContents($sFile, F::Serialize($aConfig));
        }
    }

    /**
     * Читает из файлового кеша кастомную конфигурацию
     *
     * @param string $sKeyPrefix
     *
     * @return  array
     */
    static protected function _getCustomCfg($sKeyPrefix = null) {

        if (($sFile = self::_checkCustomCfg()) && ($sData = F::File_GetContents($sFile))) {
            $aConfig = F::Unserialize($sData);
            if (is_array($aConfig)) {
                return $aConfig;
            }
        }
        $aConfig = array();
        return $aConfig;
    }

}

// EOF