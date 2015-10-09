<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

F::IncludeFile('Storage.class.php');
F::IncludeFile('DataArray.class.php');

/**
 * Управление простым конфигом в виде массива
 *
 * @package engine.lib
 * @since   1.0
 *
 * @method static Config getInstance
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
    const KEY_ROOT = '$root$';
    const KEY_EXTENDS = '$extends$';
    const KEY_REPLACE = '$replace$';
    const KEY_RESET   = '$reset$';

    const CUSTOM_CONFIG_PREFIX = 'custom.config.';
    const ENGINE_CONFIG_PREFIX = 'engine.';

    const ROOT_KEY = '$root$';

    const ALTO_UNIQUE_KEY = 'engine.alto.uniq_key';

    static protected $aElapsedTime = array();

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
     * Constructor
     */
    public function __construct() {

        self::$aElapsedTime = array('set' => 0.0, 'get' => 0.0);
    }

    /**
     * Destructor
     */
    public function __destruct() {

        if (DEBUG) {
            //var_dump('Config', self::$aElapsedTime);
        }
    }

    /**
     * Clear quick map storage
     */
    protected function _clearQuickMap() {

        $this->aQuickMap = array();
        self::_restoreKeyExtensions();
    }

    /**
     * Load configuration array from file
     *
     * @param string $sConfigFile - Путь до файла конфига
     * @param bool   $bReset      - Сбосить старые значения
     * @param string $sRootKey    - Корневой ключ конфига
     * @param int    $nLevel      - Уровень конфига
     *
     * @return  bool|Config
     */
    static public function LoadFromFile($sConfigFile, $bReset = true, $sRootKey = null, $nLevel = null) {

        if (is_integer($sRootKey) && is_null($nLevel)) {
            $nLevel = $sRootKey;
            $sRootKey = null;
        }

        // Check if file exists
        if (F::File_Exists($sConfigFile)) {
            // Get config from file
            if ($aConfig = F::File_IncludeFile($sConfigFile, true, true)) {
                return static::Load($aConfig, $bReset, $sRootKey, $nLevel, $sConfigFile);
            }
        }
        return false;
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
     * @param string $sSource  - Источник
     *
     * @return  bool|Config
     */
    static public function Load($aConfig, $bReset = true, $sRootKey = null, $nLevel = null, $sSource = null) {

        if (is_integer($sRootKey) && is_null($nLevel)) {
            $nLevel = $sRootKey;
            $sRootKey = null;
        }

        // Check if it`s array
        if (!is_array($aConfig)) {
            return false;
        }
        // Set config to current or handle instance
        return static::Set($aConfig, $bReset, $sRootKey, $nLevel, $sSource);
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
        return '[' . $sRootKey . '.__' . $nLevel . '__]';
    }

    /**
     * Return all config array or its part (if composite key passed)
     *
     * @param string $sRootKey Root config key
     * @param int    $nLevel   Config level
     * @param string $sKey     Composite key of config item
     *
     * @return array|mixed|null
     */
    public function GetConfig($sRootKey = null, $nLevel = null, $sKey = null) {

        if (is_null($nLevel)) {
            $nLevel = $this->nLevel;
        }

        $sStorageKey = $this->_storageKey($sRootKey, $nLevel);
        if (is_null($sKey)) {
            $xResult = parent::GetStorage($sStorageKey);
            if (!$xResult) {
                $xResult = array();
            }
        } else {
            $xResult = parent::GetStorageItem($sStorageKey, $sKey);
        }
        return $xResult;
    }

    /**
     * Устанавливает значения конфига
     *
     * @param array  $aConfig  - Массив конфига
     * @param bool   $bReset   - Сбросить старые значения
     * @param string $sRootKey - Корневой ключ конфига
     * @param int    $nLevel   - Уровень конфига
     * @param string $sSource  - Источник
     *
     * @return  bool
     */
    public function SetConfig($aConfig = array(), $bReset = true, $sRootKey = null, $nLevel = null, $sSource = null) {

        if (is_integer($sRootKey) && is_null($nLevel)) {
            $nLevel = $sRootKey;
            $sRootKey = self::DEFAULT_CONFIG_ROOT;
        }

        if (is_null($nLevel)) {
            $nLevel = $this->nLevel;
        }
        $sStorageKey = $this->_storageKey($sRootKey, $nLevel);

        $bResult = parent::SetStorage($sStorageKey, $aConfig, $bReset);
        $this->_clearQuickMap();

        return $bResult;
    }

    /**
     * Checks if the key exists
     *
     * @param string $sKey
     * @param string $sRoot
     *
     * @return array|bool|null
     */
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
     * @param int       $nLevel
     * @param null|bool $bSafe (true - safe mode, false - nonsafe mode, null - auto mode)
     */
    public function _setLevel($nLevel = null, $bSafe = null) {

        if ($nLevel > $this->nLevel) {
            $aConfig = $this->GetConfig(null, $this->nLevel);
            while ($nLevel > $this->nLevel) {
                $this->nLevel += 1;
                if ($bSafe === false) {
                    $this->SetConfig($aConfig, false, null, $this->nLevel);
                } else {
                    // If $bSafe is null then it is "auto" mode
                    if (is_null($bSafe) && $aConfig && !$this->GetConfig(null, $this->nLevel)) {
                        $this->SetConfig($aConfig, false, null, $this->nLevel);
                    } else {
                        $this->SetConfig(array(), false, null, $this->nLevel);
                    }
                }
            }
        } elseif ($nLevel < $this->nLevel) {
            while ($nLevel < $this->nLevel) {
                if (!$bSafe) {
                    $this->_clearLevel($this->nLevel);
                }
                $this->nLevel -= 1;
            }
        } else {
            if ($bSafe === false) {
                $aConfig = $this->GetConfig(null, $nLevel-1);
                if ($aConfig) {
                    $this->SetConfig($aConfig, true, null, $nLevel);
                }
            }
        }
        $this->nLevel = $nLevel;
    }

    /**
     * @return int
     */
    public function _getLevel() {

        return $this->nLevel;
    }

    /**
     * Set config level
     *
     * @param int       $nLevel
     * @param null|bool $bSafe (true - safe mode, false - nonsafe mode, null - auto mode)
     */
    static public function SetLevel($nLevel, $bSafe = null) {

        static::getInstance()->_setLevel($nLevel, $bSafe);
    }

    /**
     * Set config level
     *
     * @param $nLevel
     */
    static public function ResetLevel($nLevel) {

        $oInstance = static::getInstance();
        $oInstance->_setLevel($nLevel, null);
        $oInstance->_setLevel($nLevel, false);
    }

    /**
     * Get config level
     *
     * @return mixed
     */
    static public function GetLevel() {

        return static::getInstance()->_getLevel();
    }

    /**
     * Retrive information from configuration array
     *
     * @param string $sKey     - Ключ
     * @param string $sRootKey - Корневой ключ конфига
     * @param int    $nLevel
     * @param bool   $bRaw
     *
     * @return mixed
     */
    static public function Get($sKey = '', $sRootKey = null, $nLevel = null, $bRaw = false) {

        if (DEBUG) {
            $nTime = microtime(true);
        }

        if (is_integer($sRootKey) && is_null($nLevel)) {
            $bRaw = !is_null($nLevel) ? (bool)$nLevel : false;
            $nLevel = $sRootKey;
            $sRootKey = null;
        }
        // Return all config array
        if (!$sKey) {
            if (DEBUG) {
                self::$aElapsedTime['get'] += (microtime(true) - $nTime);
            }

            return static::getInstance()->GetConfig($sRootKey, $nLevel);
        }

        $xResult = static::getInstance()->GetValue($sKey, $sRootKey, $nLevel, $bRaw);

        // LS-compatibility
        if (!$bRaw && is_null($xResult) && strpos($sKey, 'db.table.') === 0 && $sKey !== 'db.table.prefix') {
            $xResult = str_replace('db.table.', static::Get('db.table.prefix'), $sKey);
        }

        if (DEBUG) {
            $nTime = microtime(true) - $nTime;
            self::$aElapsedTime['get'] += $nTime;
        }

        return $xResult;
    }

    /**
     * @param string $sKey
     *
     * @return DataArray
     */
    static public function GetData($sKey = '') {

        $xData = Config::Get($sKey);
        return new DataArray($xData);
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
     * @param bool   $bRaw
     *
     * @return mixed
     */
    public function GetValue($sKey, $sRootKey = null, $nLevel = null, $bRaw = false) {

        if ($bRaw) {
            // return raw data
            $xConfigData = $this->GetConfig($sRootKey, $nLevel, $sKey);
            return $xConfigData;
        }

        // We cache a current level only, if other level required then we return result without caching
        if (!is_null($nLevel) && $nLevel != $this->nLevel) {
            $sKeyMap = false;
        } else {
            if (is_null($nLevel)) {
                $nLevel = $this->nLevel;
            }
            $sKeyMap = $this->_storageKey($sRootKey, '*') . '.' . $sKey;
        }

        // Config section inherits of other (use $extends$ key)
        if (!$sKeyMap || !empty(self::$aKeyExtends[$sKeyMap])) {

            $xConfigData = $this->GetConfig($sRootKey, $nLevel, $sKey);
            if (is_array($xConfigData) && !empty($xConfigData[self::KEY_EXTENDS]) && is_string($xConfigData[self::KEY_EXTENDS])) {
                $xConfigData = $this->_extendsConfig($sKey, $xConfigData, $sRootKey, $nLevel);
            }
            if (is_string($xConfigData) && strpos($xConfigData, self::KEY_LINK_STR) !== false) {
                $xConfigData = $this->_resolveKeyLink($xConfigData, $sRootKey, $nLevel);
            }
            if ($sKeyMap) {
                // SET QUICK MAP AND CLEAR KEY EXTENDS
                $this->aQuickMap[$sKeyMap] = $xConfigData;
                //if (isset(self::$aKeyExtends[$sKeyMap])) {
                //    unset(self::$aKeyExtends[$sKeyMap]);
                //}
                self::_clearKeyExtension($sKeyMap);
            }

            return $xConfigData;
        }

        if (!isset($this->aQuickMap[$sKeyMap]) && !array_key_exists($sKeyMap,$this->aQuickMap)) {

            // if key has '.' then it has a parent key
            $sParentKey = strstr($sKey, '.', true);

            if ($sParentKey) {
                // If parent section was inserted in quick map we can quickly find subsection in it
                if ($this->aQuickMap) {
                    $xConfigData = $this->_checkQuickMapForParent($sKeyMap, $nLevel);
                    if (!is_null($xConfigData)) {
                        $this->aQuickMap[$sKeyMap] = $xConfigData;
                        return $xConfigData;
                    }
                }

                // May be parent section inherits of other so we need to resolve it
                if (self::$aKeyExtends) {
                    $xConfigData = $this->_checkExtendsForParent($sKeyMap, $nLevel);
                    if (!is_null($xConfigData)) {
                        $this->aQuickMap[$sKeyMap] = $xConfigData;
                        return $xConfigData;
                    }
                }
            }

            $xConfigData = $this->GetConfig($sRootKey, $nLevel, $sKey);
            if (!empty($xConfigData)) {
                if (is_array($xConfigData)) {
                    $xConfigData = $this->_keyReplace($sKey, $xConfigData, $sRootKey, $nLevel);
                } elseif (is_string($xConfigData) && strpos($xConfigData, self::KEY_LINK_STR) !== false) {
                    $xConfigData = $this->_resolveKeyLink($xConfigData, $sRootKey, $nLevel);
                }
            }
            $this->aQuickMap[$sKeyMap] = $xConfigData;
        }

        return $this->aQuickMap[$sKeyMap];
    }

    /**
     * @param string $sKeyMap
     * @param int    $nLevel
     *
     * @return mixed|null
     */
    protected function _checkExtendsForParent($sKeyMap, $nLevel) {

        foreach(self::$aKeyExtends as $sKey => $sSourceKey) {
            if (strstr($sKeyMap, $sKey)) {
                $sEnd = substr($sKeyMap, strlen($sKey));
                if ($sEnd[0] == '.') {
                    $aSubKeys = explode('.', substr($sEnd, 1));
                    if ($iOffset = strpos($sKey, '__].')) {
                        $sParentKey = substr($sKey, $iOffset + 4);
                    } else {
                        $sParentKey = $sKey;
                    }
                    $xData = $this->GetValue($sParentKey);
                    foreach($aSubKeys as $sSubKey) {
                        if (isset($xData[$sSubKey])) {
                            $xData = $xData[$sSubKey];
                        } else {
                            $this->aQuickMap[$sKeyMap] = null;
                            return null;
                        }
                    }
                    $this->aQuickMap[$sKeyMap] = $xData;
                    return $xData;
                }
            }
        }
        return null;
    }

    /**
     * @param string $sKeyMap
     * @param int    $nLevel
     *
     * @return mixed|null
     */
    protected function _checkQuickMapForParent($sKeyMap, $nLevel) {

        $aSubKeys = array();
        $sParentKey = $sKeyMap;
        $iOffset = strpos($sKeyMap, ']');
        while($iPos = strrpos($sParentKey, '.', $iOffset)) {
            $aSubKeys[] = substr($sParentKey, $iPos + 1);
            $sParentKey = substr($sParentKey, 0, $iPos);
            if (isset($this->aQuickMap[$sParentKey])) {
                $xData = $this->aQuickMap[$sParentKey];
                while ($sSubKey = array_pop($aSubKeys)) {
                    if (isset($xData[$sSubKey])) {
                        $xData = $xData[$sSubKey];
                    } else {
                        $this->aQuickMap[$sKeyMap] = null;
                        return null;
                    }
                }
                $this->aQuickMap[$sKeyMap] = $xData;
                return $xData;
            }
        }
        return null;
    }

    /**
     * Заменяет плейсхолдеры ключей в значениях конфига
     *
     * @static
     *
     * @param string|array $xConfigData - Значения конфига
     * @param string       $sRoot       - Корневой ключ конфига
     * @param int          $nLevel
     *
     * @return array|mixed
     */
    static public function KeyReplace($xConfigData, $sRoot = null, $nLevel = null) {

        if (is_null($nLevel)) {
            $nLevel = static::getInstance()->GetLevel();
        }
        return static::getInstance()->_keyReplace(null, $xConfigData, $sRoot, $nLevel);
    }

    /**
     * Replace all placeholders and extend config sections from parent data
     *
     * @param string       $sKeyPath
     * @param array|string $xConfigData
     * @param string       $sRoot
     * @param int          $nLevel
     *
     * @return array|mixed
     */
    public function _keyReplace($sKeyPath, $xConfigData, $sRoot = null, $nLevel) {

        $xResult = $xConfigData;

        if (is_array($xConfigData)) {
            // $xConfigData is array
            $xResult = array();
            // e.g.: '$extends$' => '___module.uploader.images.default___',
            if (is_array($xConfigData) && !empty($xConfigData[self::KEY_EXTENDS]) && is_string($xConfigData[self::KEY_EXTENDS])) {
                $xConfigData = $this->_extendsConfig($sKeyPath, $xConfigData, $sRoot, $nLevel);
            }
            foreach ($xConfigData as $sKey => $xData) {
                if (is_string($sKey) && !is_numeric($sKey) && strpos($sKey, self::KEY_LINK_STR) !== false) {
                    $sNewKey = $this->_keyReplace(null, $sKey, $sRoot, $nLevel);
                    if (!is_scalar($sNewKey)) {
                        $sNewKey = $sKey;
                    }
                } else {
                    $sNewKey = $sKey;
                }
                // Changes placeholders for array or string only
                if (is_array($xData)) {
                    $xResult[$sNewKey] = $this->_keyReplace($sKeyPath ? ($sKeyPath . '.' . $sNewKey) : $sNewKey, $xData, $sRoot, $nLevel);
                } elseif (is_string($xData) && strpos($xData, self::KEY_LINK_STR) !== false) {
                    $xResult[$sNewKey] = $this->_resolveKeyLink($xData, $sRoot, $nLevel);
                } else {
                    $xResult[$sNewKey] = $xData;
                }
            }
        } elseif (is_string($xConfigData) && !is_numeric($xConfigData)) {
            // $xConfigData is string
            if (strpos($xConfigData, self::KEY_LINK_STR) !== false) {
                $xResult = $this->_resolveKeyLink($xConfigData, $sRoot, $nLevel);
            }
        }
        return $xResult;
    }

    /**
     * @param string $sKeyPath
     * @param array  $xConfigData
     * @param string $sRoot
     * @param int    $nLevel
     *
     * @return array
     */
    protected function _extendsConfig($sKeyPath, $xConfigData, $sRoot = null, $nLevel) {

        if (isset($xConfigData[self::KEY_EXTENDS])) {
            $aParentData = array();
            if (is_string($xConfigData[self::KEY_EXTENDS])) {
                $sLinkKey = $this->_storageKey($sRoot, '*') . '.' . $xConfigData[self::KEY_EXTENDS];
                if (isset($this->aQuickMap[$sLinkKey])) {
                    $aParentData = $this->aQuickMap[$sLinkKey];
                } elseif (!$sKeyPath || (strpos($xConfigData[self::KEY_EXTENDS], $sKeyPath) === false)) {
                    // ^^^ Prevents self linking
                    $aParentData = $this->_keyReplace($sKeyPath, $xConfigData[self::KEY_EXTENDS], $sRoot, $nLevel);
                    $this->aQuickMap[$sLinkKey] = $aParentData;
                }
            }
            unset($xConfigData[self::KEY_EXTENDS]);
            if (!empty($aParentData) && is_array($aParentData)) {
                if (!empty($xConfigData[self::KEY_RESET])) {
                    $xConfigData = F::Array_Merge($aParentData, $xConfigData);
                } else {
                    $xConfigData = F::Array_MergeCombo($aParentData, $xConfigData);
                }
            }
            $sKeyMap = $this->_storageKey($sRoot, '*') . '.' . $sKeyPath;
            // SET QUICK MAP AND CLEAR KEY EXTENDS
            $this->aQuickMap[$sKeyMap] = $xConfigData;
            //if (isset(self::$aKeyExtends[$sKeyMap])) {
            //    unset(self::$aKeyExtends[$sKeyMap]);
            //}
            self::_clearKeyExtension($sKeyMap);
        }

        return $xConfigData;
    }

    /**
     * @param string $sKeyLink
     * @param string $sRoot
     * @param int    $nLevel
     *
     * @return mixed
     */
    protected function _resolveKeyLink($sKeyLink, $sRoot = null, $nLevel) {

        $xResult = $sKeyLink;
        if (preg_match_all(self::KEY_LINK_PREG, $sKeyLink, $aMatch, PREG_SET_ORDER)) {
            if (count($aMatch) == 1 && $aMatch[0][0] == $sKeyLink) {
                $xResult = $this->GetValue($aMatch[0][1], $sRoot, $nLevel);
            } else {
                foreach ($aMatch as $aItem) {
                    $sReplacement = $this->GetValue($aItem[1], $sRoot);
                    if ($aItem[2] == '___/' && substr($sReplacement, -1) != '/' && substr($sReplacement, -1) != '\\') {
                        $sReplacement .= '/';
                    }
                    $xResult = str_replace(self::KEY_LINK_STR . $aItem[1] . $aItem[2], $sReplacement, $xResult);
                }
            }
        }
        return $xResult;
    }

    protected function _clearKeyExtension($sKeyMap) {

        if (isset(self::$aKeyExtends[$sKeyMap])) {
            self::$aClearedKeyExtensions[$sKeyMap] = self::$aKeyExtends[$sKeyMap];
            unset(self::$aKeyExtends[$sKeyMap]);
        }
    }

    protected function _restoreKeyExtensions() {

        foreach(self::$aClearedKeyExtensions as $sKey => $sVal) {
            if (empty(self::$aKeyExtends)) {
                self::$aKeyExtends[$sKey] = $sVal;
            }
        }
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
     * Set config value(s)
     * Usage:
     *   Config::Set('key', $xData, ...);
     * or
     *   Config::Set(array('key', $xData), $bReplace, ...);
     *
     * @param string|array $sKey    - Key or Config data array
     * @param mixed        $xValue  - Value(s) or Replace flag
     * @param string       $sRoot   - Root key
     * @param int          $nLevel  - Level of config
     * @param string       $sSource - Source of data
     *
     * @return bool
     */
    static public function Set($sKey, $xValue, $sRoot = self::DEFAULT_CONFIG_ROOT, $nLevel = null, $sSource = null) {

        if (DEBUG) {
            $nTime = microtime(true);
        }

        if (is_array($sKey) && is_bool($xValue)) {
            $aConfigData = $sKey;
            $bReplace = $xValue;
            $xValue = reset($aConfigData);
        } else {
            $aConfigData = array($sKey => $xValue);
            $bReplace = false;
        }

        if ($aConfigData) {
            if (is_integer($sRoot) && (is_null($nLevel) || is_string($nLevel))) {
                if (is_string($nLevel)) {
                    $sSource = $nLevel;
                }
                $nLevel = $sRoot;
                $sRoot = self::DEFAULT_CONFIG_ROOT;
            }

            // Check for KEY_ROOT in config data
            if (isset($xValue[self::KEY_ROOT]) && is_array($xValue[self::KEY_ROOT])) {
                $aRootConfig = $xValue[self::KEY_ROOT];
                foreach ($aRootConfig as $sRootConfigKey => $xRootConfigVal) {
                    static::Set($sRootConfigKey, $xRootConfigVal, $sRoot, $nLevel, $sSource);
                }
                unset($aConfigData[$sKey][self::KEY_ROOT]);
            }

            /** @var Config $oConfig */
            $oConfig = static::getInstance();

            // Check for KEY_REPLACE in config data
            $aClearConfig = self::_extractForReplacement($aConfigData, $oConfig->_storageKey($sRoot, '*'));
            if ($aClearConfig) {
                $oConfig->SetConfig($aClearConfig, false, $sRoot, $nLevel, $sSource);
            }

            $oConfig->SetConfig($aConfigData, $bReplace, $sRoot, $nLevel, $sSource);
        }
        if (DEBUG) {
            self::$aElapsedTime['set'] += (microtime(true) - $nTime);
        }

        return true;
    }

    static protected $bKeyReplace = false;
    static protected $aKeyExtends = array();
    static protected $aClearedKeyExtensions = array();

    static public function _checkForReplacement(&$xItem, $xKey) {

        if (!self::$bKeyReplace) {
            self::$bKeyReplace = ($xKey === Config::KEY_REPLACE || $xKey === Config::KEY_EXTENDS);
        }
    }

    /**
     * Filters config array and extract structure data for replacement
     *
     * @param $aConfig
     *
     * @return array|bool
     */
    static protected function _extractForReplacement(&$aConfig, $sParentKey) {

            self::$bKeyReplace = false;
            array_walk_recursive($aConfig, 'Config::_checkForReplacement');

            if (!self::$bKeyReplace) {
                // Has no KEY_REPLACE in data
                return array();
            }

        return self::_extractForReplacementData($aConfig, 0, $sParentKey);
    }

    /**
     * Filters array and extract structure data for replacement
     *
     * @param array  $aConfig
     * @param int    $iDataLevel
     * @param string $sParentKey
     *
     * @return array|bool
     */
    static protected function _extractForReplacementData(&$aConfig, $iDataLevel = 0, $sParentKey = null) {

        $aResult = array();

        if ($iDataLevel) {
            // KEY_REPLACE on this level
            if (isset($aConfig[self::KEY_REPLACE])) {
                if (is_array($aConfig[self::KEY_REPLACE])) {
                    unset($aConfig[self::KEY_REPLACE]);
                    $aResult = array_fill_keys($aConfig[self::KEY_REPLACE], null);
                } else {
                    //unset($aConfig[self::KEY_REPLACE]);
                    $aResult = true;
                }
                //return $aResult;
            }
            if (isset($aConfig[self::KEY_EXTENDS]) && is_string($aConfig[self::KEY_EXTENDS])) {
                self::$aKeyExtends[$sParentKey] = $aConfig[self::KEY_EXTENDS];
            }
        }

        // KEY_REPLACE on deeper levels
        foreach($aConfig as $xKey => &$xVal) {
            if(is_array($xVal)) {
                $xSubResult = self::_extractForReplacementData($xVal, ++$iDataLevel, $sParentKey . '.' . $xKey);
                if ($xSubResult === true) {
                    $aResult[$xKey] = null;
                } elseif (!empty($xSubResult)) {
                    $aResult[$xKey] = (array)$xSubResult;
                }
            }
        }
        return $aResult;
    }

    /**
     * Find all keys recursively in config array
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
     * Write config data to storage and cache
     *
     * @param string $sPrefix
     * @param array  $aConfig
     * @param bool   $bCacheOnly
     * @param int    $iOrder
     *
     * @return  bool
     */
    static protected function _writeConfig($sPrefix, $aConfig, $bCacheOnly = false, $iOrder = 1) {

        if ($sPrefix && substr($sPrefix, -1) != '.') {
            $sPrefix .= '.';
        }
        $aData = array();
        foreach ($aConfig as $sKey => $sVal) {
            $aData[] = array(
                'storage_key' => $sPrefix . $sKey,
                'storage_val' => serialize($sVal),
                'storage_ord' => $iOrder
            );
        }
        if (E::ModuleAdmin()->UpdateStorageConfig($aData)) {
            //self::_putFileCfg($aConfig);
            self::_deleteFileCfg();
            self::_reReadConfig();
            return true;
        }
        return false;
    }

    static protected function _explodeData($aData, $sPrefix = null) {

        if (sizeof($aData) == 1 && isset($aData[$sPrefix]) && $aData[$sPrefix]['storage_key'] == $sPrefix) {
            // single value
            $xVal = @unserialize($aData[$sPrefix]['storage_val']);
            return $xVal;
        }
        $aResult = new DataArray();
        if ($sPrefix) {
            $aPrefix = array(
                $sPrefix => strlen($sPrefix),
            );
        } else {
            $aPrefix = array(
                self::ENGINE_CONFIG_PREFIX => 0,
                self::CUSTOM_CONFIG_PREFIX => strlen(self::CUSTOM_CONFIG_PREFIX),
            );
        }
        $aExpData = array_fill_keys(array_keys($aPrefix), array());

        foreach ($aData as $aRow) {
            foreach($aPrefix as $sPrefixKey => $iPrefixLen) {
                if (strpos($aRow['storage_key'], $sPrefixKey) === 0) {
                    if ($iPrefixLen) {
                        $sKey = substr($aRow['storage_key'], $iPrefixLen + 1);
                    } else {
                        $sKey = $aRow['storage_key'];
                    }
                    $xVal = @unserialize($aRow['storage_val']);
                    $aExpData[$sPrefixKey][$sKey] = $xVal;
                }
            }
        }
        if ($aExpData) {
            foreach($aExpData as $aDataValues) {
                $aResult->Merge($aDataValues);
            }
        }
        return $aResult->getArrayCopy();
    }

    /**
     * @param string      $sPrefix
     * @param string|null $sConfigKeyPrefix
     * @param bool        $bCacheOnly
     * @param bool        $bRaw
     *
     * @return array
     */
    static protected function _readConfig($sPrefix, $sConfigKeyPrefix = null, $bCacheOnly = false, $bRaw = false) {

        if ($sPrefix && substr($sPrefix, -1) != '.') {
            $sPrefix .= '.';
        }
        $aConfig = array();
        if (!$bRaw && self::_checkFileCfg(!$bCacheOnly)) {
            $aConfig = self::_getFileCfg();
        }
        if (!$aConfig) {
            if (!$bCacheOnly && class_exists('E', false)) {
                // Перечитаем конфиг из базы
                $sPrefix = $sPrefix . $sConfigKeyPrefix;
                $aData = E::ModuleAdmin()->GetStorageConfig($sPrefix);
                $aConfig = self::_explodeData($aData, $sPrefix);
                if ($bRaw) {
                    return $aConfig;
                }
                if (isset($aConfig['plugin'])) {
                    $aConfigPlugins = array_keys($aConfig['plugin']);
                    $aActivePlugins = F::GetPluginsList(false, true);
                    if (!$aActivePlugins) {
                        unset($aConfig['plugin']);
                    } else {
                        $bRootConfig = false;
                        foreach($aConfigPlugins as $sPlugin) {
                            if (!in_array($sPlugin, $aActivePlugins)) {
                                unset($aConfig['plugin'][$sPlugin]);
                            } else {
                                if (isset($aConfig['plugin'][$sPlugin][self::KEY_ROOT])) {
                                    $bRootConfig = true;
                                }
                            }
                        }
                        if (empty($aConfig['plugin'])) {
                            unset($aConfig['plugin']);
                        }
                        // Need to prepare config data
                        if ($bRootConfig) {
                            $aConfigResult = array();
                            foreach($aConfig as $sKey => $xVal) {
                                if ($sKey == 'plugin') {
                                    // sort plugin config by order of active pligin list
                                    foreach($aActivePlugins as $sPluginId) {
                                        if (isset($aConfig['plugin'][$sPluginId])) {
                                            $aPluginConfig = $aConfig['plugin'][$sPluginId];
                                            if (isset($aPluginConfig[self::KEY_ROOT])) {
                                                if (is_array($aPluginConfig[self::KEY_ROOT]) && $aPluginConfig[self::KEY_ROOT]) {
                                                    foreach($aPluginConfig[self::KEY_ROOT] as $sRootKey => $xRootVal) {
                                                        if (isset($aConfigResult[$sRootKey])) {
                                                            $aConfigResult[$sRootKey] = F::Array_MergeCombo($aConfigResult[$sRootKey], $xRootVal);
                                                        } else {
                                                            $aConfigResult[$sRootKey] = $xRootVal;
                                                        }
                                                    }
                                                }
                                                unset($aPluginConfig[self::KEY_ROOT]);
                                            }
                                            if (!empty($aPluginConfig)) {
                                                $aConfigResult['plugin'][$sPluginId] = $aPluginConfig;
                                            }
                                        }
                                    }

                                } else {
                                    $aConfigResult[$sKey] = $xVal;
                                }
                            }
                            $aConfig = $aConfigResult;
                        } // $bRootConfig
                    }
                }
                // Признак того, что кеш конфига синхронизирован с базой
                $aConfig['_db_'] = time();
                self::_putFileCfg($aConfig);
            } else {
                // Признак того, что кеш конфига НЕ синхронизиован с базой
                $aConfig['_db_'] = false;
            }
        } elseif ($sConfigKeyPrefix) {
            $aData = new DataArray($aConfig);
            if ($sPrefix == self::ENGINE_CONFIG_PREFIX) {
                $sConfigKeyPrefix = $sPrefix . $sConfigKeyPrefix;
            }
            return $aData[$sConfigKeyPrefix];
        }
        return $aConfig;
    }

    /**
     * @return array
     */
    static protected function _reReadConfig() {

        self::_readConfig(null, null, false);
    }

    /**
     * @param string $sPrefix
     * @param string|null $sConfigKey
     */
    static protected function _resetConfig($sPrefix, $sConfigKey = null) {

        if ($sPrefix && substr($sPrefix, -1) != '.') {
            $sPrefix .= '.';
        }
        $sPrefix = $sPrefix . $sConfigKey;
        // удаляем настройки конфига из базы
        E::ModuleAdmin()->DeleteStorageConfig($sPrefix);
        // удаляем кеш-файл
        self::_deleteFileCfg();
        // перестраиваем конфиг в кеш-файле
        self::_reReadConfig();
    }

    /**
     * @param string|null $sConfigKey
     * @param bool        $bCacheOnly
     *
     * @return array
     */
    static public function ReadStorageConfig($sConfigKey = null, $bCacheOnly = false) {

        return self::_readConfig('', $sConfigKey, $bCacheOnly);
    }

    /**
     *
     */
    static public function ReReadStorageConfig() {

        return self::_readConfig('', null, false);
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

        return self::_writeConfig(self::CUSTOM_CONFIG_PREFIX, $aConfig, $bCacheOnly);
    }

    /**
     * @param string|null $sConfigKey
     * @param bool        $bCacheOnly
     *
     * @return array
     */
    static public function ReadCustomConfig($sConfigKey = null, $bCacheOnly = false) {

        return self::_readConfig(self::CUSTOM_CONFIG_PREFIX, $sConfigKey, $bCacheOnly);
    }

    /**
     *
     */
    static public function ReReadCustomConfig() {

        return self::_readConfig(self::CUSTOM_CONFIG_PREFIX, null, false);
    }

    /**
     * @param string|null $sConfigKey
     */
    static public function ResetCustomConfig($sConfigKey = null) {

        self::_resetConfig(self::CUSTOM_CONFIG_PREFIX, $sConfigKey);
    }

    /**
     * Write plugin's configuration
     *
     * @param string $sPluginId
     * @param array  $aConfig
     * @param bool   $bCacheOnly
     *
     * @return  bool
     */
    static public function WritePluginConfig($sPluginId, $aConfig, $bCacheOnly = false) {

        if (strpos($sPluginId, 'plugin.') === 0) {
            $sPluginKey = $sPluginId;
        } else {
            $sPluginKey = 'plugin.' . $sPluginId;
        }

        if (!is_array($aConfig) || empty($aConfig)) {
            $aSaveConfig = array($sPluginKey => $aConfig);
        } else {
            $aSaveConfig = array();
            foreach($aConfig as $sKey => $xVal) {
                $aSaveConfig[$sPluginKey . '.' . $sKey] = $xVal;
            }
        }
        return self::_writeConfig(self::CUSTOM_CONFIG_PREFIX, $aSaveConfig, $bCacheOnly);
    }

    /**
     * Read plugin's config
     *
     * @param string      $sPluginId
     * @param string|null $sConfigKey
     * @param bool        $bCacheOnly
     *
     * @return array
     */
    static public function ReadPluginConfig($sPluginId, $sConfigKey = null, $bCacheOnly = false) {

        if (strpos($sPluginId, 'plugin.') === 0) {
            $sPluginKey = $sPluginId;
        } else {
            $sPluginKey = 'plugin.' . $sPluginId;
        }

        if ($sConfigKey) {
            $sConfigKey = $sPluginKey . '.' . $sConfigKey;
        } else {
            $sConfigKey = $sPluginKey;
        }
        return self::_readConfig(self::CUSTOM_CONFIG_PREFIX, $sConfigKey, $bCacheOnly, true);
    }

    /**
     * Reset plugin's config
     *
     * @param string      $sPluginId
     * @param string|null $sConfigKey
     */
    static public function ResetPluginConfig($sPluginId, $sConfigKey = null) {

        if (strpos($sPluginId, 'plugin.') === 0) {
            $sPluginKey = $sPluginId;
        } else {
            $sPluginKey = 'plugin.' . $sPluginId;
        }

        if ($sConfigKey) {
            $sConfigKey = $sPluginKey . '.' . $sConfigKey;
        } else {
            $sConfigKey = $sPluginKey;
        }
        self::_resetConfig(self::CUSTOM_CONFIG_PREFIX, $sConfigKey);
    }

    /**
     * @param array  $aConfig
     * @param bool   $bCacheOnly
     *
     * @return  bool
     */
    static public function WriteEngineConfig($aConfig, $bCacheOnly = false) {

        if (!empty($aConfig) && is_array($aConfig)) {
            $aSaveConfig = array();
            foreach($aConfig as $sKey => $xVal) {
                if (is_string($sKey) && !is_numeric($sKey)) {
                    if (strpos($sKey, self::ENGINE_CONFIG_PREFIX) === 0) {
                        $sKey = substr($sKey, strlen(self::ENGINE_CONFIG_PREFIX));
                    }
                    $aSaveConfig[$sKey] = $xVal;
                }
            }
            return self::_writeConfig(self::ENGINE_CONFIG_PREFIX, $aSaveConfig, $bCacheOnly);
        }
        return false;
    }

    /**
     * @param string|null $sConfigKey
     * @param bool        $bCacheOnly
     *
     * @return array
     */
    static public function ReadEngineConfig($sConfigKey = null, $bCacheOnly = false) {

        return self::_readConfig(self::ENGINE_CONFIG_PREFIX, $sConfigKey, $bCacheOnly);
    }

    /**
     * Reset plugin's config
     *
     * @param string|null $sConfigKey
     */
    static public function ResetEngineConfig($sConfigKey = null) {

        self::_resetConfig(self::ENGINE_CONFIG_PREFIX, $sConfigKey);
    }

    /**
     * Invalidate cache of custom configuration
     */
    static public function InvalidateCachedConfig() {

        // удаляем кеш-файл
        self::_deleteFileCfg();
    }

    /**
     * Возвращает полный путь к кеш-файлу кастомной конфигуации
     * или просто проверяет его наличие
     *
     * @param bool $bCheckOnly
     *
     * @return  string
     */
    static protected function _checkFileCfg($bCheckOnly = false) {

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
    static protected function _deleteFileCfg() {

        $sFile = self::_checkFileCfg(true);
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
    static protected function _putFileCfg($aConfig, $bReset = false) {

        if (is_array($aConfig) && ($sFile = self::_checkFileCfg())) {
            if (!$bReset) {
                // Объединяем текущую конфигурацию с сохраняемой
                $aOldConfig = self::_getFileCfg();
                if ($aOldConfig) {
                    $aData = new DataArray($aOldConfig);
                    foreach($aConfig as $sKey => $xVal) {
                        $aData[$sKey] = $xVal;
                    }
                    $aConfig = $aData->getArrayCopy();
                }
            }
            $aConfig['_timestamp_'] = time();
            $aConfig['_alto_hash_'] = self::_getHash();
            F::File_PutContents($sFile, F::Serialize($aConfig), LOCK_EX);
        }
    }

    /**
     * Читает из файлового кеша кастомную конфигурацию
     *
     * @return  array
     */
    static protected function _getFileCfg() {

        if (($sFile = self::_checkFileCfg()) && ($sData = F::File_GetContents($sFile))) {
            $aConfig = F::Unserialize($sData);
            if (is_array($aConfig)) {
                if (isset($aConfig['_alto_hash_']) && $aConfig['_alto_hash_'] == self::_getHash()) {
                    return $aConfig;
                }
            }
        }
        return array();
    }

    static protected function _getHash() {

        return md5(ALTO_VERSION . serialize(F::GetPluginsList(false, true)));
    }

}

// EOF