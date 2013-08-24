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
 * @since 1.0
 */
class Config extends Storage {
    /**
     * Default instance to operate with
     *
     * @var string
     */
    const DEFAULT_CONFIG_INSTANCE = '__main__';

    const KEY_LINK_STR = '___';
    const KEY_LINK_PREG = '~___([\S|\.]+)___~Ui';

    const CUSTOM_CONFIG_PREFIX = 'custom.config.';

    /**
     * Mapper rules for Config Path <-> Constant Name relations
     *
     * @var array
     */
    static protected $aMapper = array();

    /** @var Config */
    //static protected $oInstance;

    static protected $sExtensionKey;

    /**
     * Store for configuration entries for current instance
     *
     * @var array
     */
    protected $aConfig = array();

    static protected $aQuickMap = array();

    static protected $bRereadCustomConfig = false;
    /**
     * Disabled constract process
     */
    public function __construct() {

    }

    static protected function _clearQuickMap() {

        static::$aQuickMap = array();
    }
    /**
     * Load configuration array from file
     *
     * @static
     * @param   string  $sFile      - Путь до файла конфига
     * @param   bool    $bReset     - Сбосить старые значения
     * @param   string  $sInstance  - Название инстанции конфига
     * @return  bool|Config
     */
    static public function LoadFromFile($sFile, $bReset = true, $sInstance = self::DEFAULT_CONFIG_INSTANCE) {

        // Check if file exists
        if (!F::File_Exists($sFile)) {
            return false;
        }
        // Get config from file
        if ($aConfig = F::File_IncludeFile($sFile, true, true)) {
            return static::Load($aConfig, $bReset, $sInstance);
        }
    }

    /**
     * Loads configuration array from given array
     *
     * @static
     * @param   array   $aConfig    - Массив конфига
     * @param   bool    $bReset     - Сбросить старые значения
     * @param   string  $sInstance  - Название инстанции конфига
     * @return  bool|Config
     */
    static public function Load($aConfig, $bReset = true, $sInstance = self::DEFAULT_CONFIG_INSTANCE) {

        // Check if it`s array
        if (!is_array($aConfig)) {
            return false;
        }
        // Set config to current or handle instance
        static::getInstance()->SetConfig($aConfig, $bReset, $sInstance);
        return static::getInstance();
    }

    /**
     * Extends configuration from file
     *
     * @static
     * @param   string  $sFile      - Путь до файла конфига
     * @param   bool    $bReset     - Сбосить старые значения
     * @param   string  $sInstance  - Название инстанции конфига
     * @return  bool|Config
     */
    static public function ExtendFromFile($sFile, $bReset = true, $sInstance) {

        static::$sExtensionKey = $sInstance;

        // Check if file exists
        if (!F::File_Exists($sFile)) {
            return false;
        }
        // Get config from file
        if ($aConfig = F::File_IncludeFile($sFile, true, true)) {
            return static::Extend($aConfig, $bReset, $sInstance);
        }
    }

    /**
     * Extends configuration from array
     *
     * @static
     * @param   array   $aConfig    - Массив конфига
     * @param   bool    $bReset     - Сбросить старые значения
     * @param   string  $sInstance  - Название инстанции конфига
     * @return  bool|Config
     */
    static public function Extend($aConfig, $bReset = true, $sInstance) {

        static::$sExtensionKey = $sInstance;

        // Check if it`s array
        if (!is_array($aConfig)) {
            return false;
        }
        // Set config to current or handle instance
        static::getInstance()->SetConfig($aConfig, $bReset, $sInstance);
        return static::getInstance();
    }

    static public function SetExtensionKey($sExtensionKey) {

        if ($sExtensionKey == self::DEFAULT_CONFIG_INSTANCE) {
            $sExtensionKey = null;
        }
        if (static::$sExtensionKey != $sExtensionKey) {
            static::$sExtensionKey = $sExtensionKey;
            static::_clearQuickMap();
        }
    }

    static public function ClearExtensionKey() {

        static::SetExtensionKey(null);
    }

    /**
     * Возвращает текущий полный конфиг
     *
     * @param   string  $sInstance  - Название инстанции конфига
     * @return  array
     */
    public function GetConfig($sInstance = self::DEFAULT_CONFIG_INSTANCE) {

        $aConfig = parent::GetStorage(self::DEFAULT_CONFIG_INSTANCE);
        if ($sInstance != self::DEFAULT_CONFIG_INSTANCE) {
            // нужен расширенный конфиг
            if (!is_null($sInstance)) {
                $aConfig = F::Array_Merge($aConfig, parent::GetStorage($sInstance));
            } elseif (static::$sExtensionKey) {
                $aConfig = F::Array_Merge($aConfig, parent::GetStorage(static::$sExtensionKey));
            }
        }
        return $aConfig;
    }

    /**
     * Устанавливает значения конфига
     *
     * @param   array   $aConfig    - Массив конфига
     * @param   bool    $bReset     - Сбросить старые значения
     * @param   string  $sInstance  - Название инстанции конфига
     * @return  bool
     */
    public function SetConfig($aConfig = array(), $bReset = true, $sInstance = self::DEFAULT_CONFIG_INSTANCE) {

        static::_clearQuickMap();
        return parent::SetStorage($sInstance, $aConfig, $bReset);
    }

    /**
     * Retrive information from configuration array
     *
     * @param  string   $sKey       - Ключ
     * @param  string   $sInstance  - Название инстанции конфига
     * @return mixed
     */
    static public function Get($sKey = '', $sInstance = self::DEFAULT_CONFIG_INSTANCE) {

        // Return all config array
        if (!$sKey) {
            return static::getInstance()->GetConfig($sInstance);
        }

        // Проверяем в локальном кеше, и если там нет, то находим и сохраняем
        $sKeyMap = $sInstance . '.' . $sKey;
        if (!isset(static::$aQuickMap[$sKeyMap])) {
            $sValue = static::getInstance()->GetValue($sKey, $sInstance);
            static::$aQuickMap[$sKeyMap] = $sValue;
        }
        return static::$aQuickMap[$sKeyMap];
    }

    /**
     * As a method Get() but with default value
     *
     * @param string $sKey
     * @param mixed   $xDefault
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
     * @param  string   $sKey       - Ключ
     * @param  string   $sInstance  - Название инстанции конфига
     * @return mixed
     */
    public function GetValue($sKey, $sInstance = self::DEFAULT_CONFIG_INSTANCE) {

        // Return config by path (separator=".")
        $aKeys = explode('.', $sKey);

        $cfg = $this->GetConfig($sInstance);
        foreach ((array)$aKeys as $sK) {
            if (isset($cfg[$sK])) {
                $cfg = $cfg[$sK];
            } else {
                return null;
            }
        }

        $cfg = static::KeyReplace($cfg, $sInstance);
        return $cfg;
    }

    /**
     * Заменяет плейсхолдеры ключей в значениях конфига
     *
     * @static
     * @param string|array $xCfg   Значения конфига
     * @param string $sInstance    Название инстанции конфига
     * @return array|mixed
     */
    static public function KeyReplace($xCfg, $sInstance = self::DEFAULT_CONFIG_INSTANCE) {

        if (is_array($xCfg)) {
            foreach ($xCfg as $k => $v) {
                $k_replaced = static::KeyReplace($k, $sInstance);
                if ($k == $k_replaced) {
                    $xCfg[$k] = static::KeyReplace($v, $sInstance);
                } else {
                    $xCfg[$k_replaced] = static::KeyReplace($v, $sInstance);
                    unset($xCfg[$k]);
                }
            }
        } else {
            if (strpos($xCfg, self::KEY_LINK_STR) !== false
                && preg_match_all(
                    self::KEY_LINK_PREG, $xCfg, $aMatch, PREG_SET_ORDER
                )
            ) {
                foreach ($aMatch as $aItem) {
                    $xCfg = str_replace(
                        self::KEY_LINK_STR . $aItem[1] . self::KEY_LINK_STR, Config::Get($aItem[1], $sInstance), $xCfg
                    );
                }
            }
        }
        return $xCfg;
    }

    /**
     * Try to find element by given key
     * Using function ARRAY_KEY_EXISTS (like in SPL)
     *
     * Workaround for http://bugs.php.net/bug.php?id=40442
     *
     * @param  string $sKey      Path to needed value
     * @param  string $sInstance Name of needed instance
     * @return bool
     */
    static public function isExist($sKey, $sInstance = self::DEFAULT_CONFIG_INSTANCE) {

        return static::getInstance()->IsExists($sInstance, $sKey);
    }

    /**
     * Add information in config array by handle path
     *
     * @param  string   $sKey       - Ключ
     * @param  mixed    $xValue     - Значение
     * @param  string   $sInstance  - Название инстанции конфига
     * @return bool
     */
    static public function Set($sKey, $xValue, $sInstance = self::DEFAULT_CONFIG_INSTANCE) {

        $aKeys = explode('.', $sKey);

        if (isset($xValue['$root$']) && is_array($xValue['$root$'])) {
            $aRoot = $xValue['$root$'];
            unset($xValue['$root$']);
            foreach ($aRoot as $sRootKey => $xVal) {
                if (static::isExist($sRootKey)) {
                    static::Set($sRootKey, F::Array_Merge(Config::Get($sRootKey, $sInstance), $xVal), $sInstance);
                } else {
                    static::Set($sRootKey, $xVal, $sInstance);
                }
            }
        }

        static::getInstance()->SetItem($sInstance, $sKey, $xValue);

        // Сбрасываем локальный кеш
        static::_clearQuickMap();
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
     * Define constants using config-constant mapping
     *
     * @param  string $sKey    Ключ
     * @param  string $sInstance    Название инстанции конфига
     * @return bool
     */
    static public function DefineConstant($sKey = '', $sInstance = self::DEFAULT_CONFIG_INSTANCE) {

        if ($aKeys = static::getInstance()->GetKeys()) {
            foreach ($aKeys as $key) {
                // If there is key-mapping rule, replace it
                $sName = isset(static::$aMapper[$key])
                    ? static::$aMapper[$key]
                    : strtoupper(str_replace('.', '_', $key));
                if ((substr($key, 0, strlen($sKey)) == strtoupper($sKey))
                    && !defined($sName)
                    && (static::isExist($key, $sInstance))
                ) {
                    $cfg = static::Get($key, $sInstance);
                    // Define constant, if found value is scalar or NULL
                    if (is_scalar($cfg) || $cfg === NULL) define(strtoupper($sName), $cfg);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Записывает кастомную конфигурацию
     *
     * @param   array   $aConfig
     * @param   bool    $bCacheOnly
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
                // Признак того, что кеш конфига синхронизиован с базой
                $aConfig['_db_'] = time();
                self::_putCustomCfg($aConfig);
            } else {
                // Признак того, что кеш конфига НЕ синхронизиован с базой
                $aConfig['_db_'] = false;
            }
        }
        return $aConfig;
    }

    static public function ReReadCustomConfig() {

        self::ReadCustomConfig(null, false);
    }

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
     * @param   bool $bCheckOnly
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
     * @param   $aConfig
     * @param   $bReset
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
     * @param   string  $sKeyPrefix
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