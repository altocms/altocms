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
 * Абстракция модуля, от которой наследуются все модули
 *
 * @package engine
 * @since 1.0
 *
 * @method bool MethodExists($sMethodName)
 */
abstract class Module extends LsObject {

    const DEFAULT_ITEMS_PER_PAGE = 20;

    const STATUS_INIT_BEFORE = 1;
    const STATUS_INIT = 2;
    const STATUS_DONE_BEFORE = 3;
    const STATUS_DONE = 4;

    /** @var int Статус модуля */
    protected $nStatus = 0;

    /** @var bool Признак предзагрузки */
    protected $bPreloaded = false;

    final public function __construct() {

    }

    /**
     * Блокируем копирование/клонирование объекта
     *
     */
    protected function __clone() {

    }

    /**
     * Абстрактный метод инициализации модуля, должен быть переопределен в модуле
     *
     */
    abstract public function Init();

    /**
     * Returns array if entity IDs
     *
     * @param mixed $aEntities
     * @param bool  $bUnique
     * @param bool  $bSkipZero
     *
     * @return array
     */
    protected function _entitiesId($aEntities, $bUnique = true, $bSkipZero = true) {

        $aIds = array();
        if (!is_array($aEntities)) {
            $aEntities = array($aEntities);
        }
        foreach ($aEntities as $oEntity) {
            if ($nId = is_object($oEntity) ? intval($oEntity->getId()) : intval($oEntity)) {
                if ($nId || !$bSkipZero) {
                    $aIds[] = $nId;
                }
            }
        }
        if ($aIds && $bUnique) {
            $aIds = array_unique($aIds);
        }
        return $aIds;
    }

    /**
     * Возвращает ID сущности, если передан объект, либо просто ID
     *
     * @param $xEntity
     *
     * @return int|null
     */
    protected function _entityId($xEntity) {

        if (is_scalar($xEntity)) {
            return intval($xEntity);
        } else {
            $aIds = $this->_entitiesId($xEntity);
            if ($aIds) {
                return intval(reset($aIds));
            }
        }
        return null;
    }

    /**
     * Метод срабатывает при завершении работы ядра
     *
     */
    public function Shutdown() {

    }

    /**
     * Устанавливает статус модуля
     *
     * @param   int $nStatus
     */
    public function SetStatus($nStatus) {

        $this->nStatus = $nStatus;
    }

    /**
     * Вовзращает статус модуля
     *
     * @return int
     */
    public function GetStatus() {

        return $this->nStatus;
    }

    public function SetPreloaded($bVal) {

        $this->bPreloaded = (bool)$bVal;
    }

    public function GetPreloaded() {

        return $this->bPreloaded;
    }

    /**
     * Устанавливает признак начала и завершения инициализации модуля
     *
     * @param bool $bBefore
     */
    public function SetInit($bBefore = false) {

        if ($bBefore) {
            $this->SetStatus(self::STATUS_INIT_BEFORE);
        } else {
            $this->SetStatus(self::STATUS_INIT);
        }
    }

    /**
     * Устанавливает признак начала и завершения шатдауна модуля
     *
     * @param bool $bBefore
     */
    public function SetDone($bBefore = false) {

        if ($bBefore) {
            $this->SetStatus(self::STATUS_DONE_BEFORE);
        } else {
            $this->SetStatus(self::STATUS_DONE);
        }
    }

    /**
     * Возвращает значение флага инициализации модуля
     *
     * @return bool
     */
    public function InInitProgress() {

        return $this->GetStatus() == self::STATUS_INIT_BEFORE;
    }

    /**
     * Возвращает значение флага инициализации модуля
     *
     * @return bool
     */
    public function isInit() {

        return $this->GetStatus() >= self::STATUS_INIT;
    }

    /**
     * Возвращает значение флага инициализации модуля
     *
     * @return bool
     */
    public function InShudownProgress() {

        return $this->GetStatus() == self::STATUS_DONE_BEFORE;
    }

    /**
     * Возвращает значение флага инициализации модуля
     *
     * @return bool
     */
    public function isDone() {

        return $this->GetStatus() >= self::STATUS_DONE;
    }

    /**
     * @param string $sMsg
     *
     * @return bool
     */
    public function LogError($sMsg) {

        return F::LogError(get_class($this) . ': ' . $sMsg);
    }

    /**
     * Структурирует массив сущностей - возвращает многомерный массив по заданным ключам
     * <pre>
     * Structurize($aEntities, key1, key2, ...);
     * Structurize($aEntities, array(key1, key2, ...));
     * </pre>
     *
     * @return array
     */
    public function Structurize() {

        $iArgsNum = func_num_args();
        $aArgs = func_get_args();
        if ($iArgsNum == 0) {
            return array();
        } elseif ($iArgsNum == 1) {
            return $aArgs[0];
        }
        $aResult = array();
        $aEntities = $aArgs[0];
        $oEntity = reset($aEntities);
        unset($aArgs[0]);
        if (sizeof($aArgs) == 1 && is_array($aArgs[1])) {
            $aArgs = $aArgs[1];
        }
        foreach($aArgs as $iIdx => $sPropKey) {
            if (!$oEntity->isProp($sPropKey)) {
                unset($aArgs[$iIdx]);
            }
        }
        if ($aArgs) {
            /** @var Entity $oEntity */
            foreach($aEntities as $oEntity) {
                $aItems =& $aResult;
                foreach($aArgs as $sPropKey) {
                    $xKey = $oEntity->getProp($sPropKey);
                    if (!isset($aItems[$xKey])) {
                        $aItems[$xKey] = array();
                    }
                    $aItems =& $aItems[$xKey];
                }
                $aItems[$oEntity->getId()] = $oEntity;
            }
        } else {
            $aResult = $aEntities;
        }
        return $aResult;
    }

    /**
     * @return bool|string
     */
    public function MakeCacheKey() {

        $aStack = debug_backtrace(false);
        $sCaller = '';
        $sFile = false;
        foreach($aStack as $aItem) {
            if ($sFile) {
                if (!empty($aItem['class']) && !empty($aItem['type']) && !empty($aItem['function'])) {
                    $sCaller = $sFile . '|' . $aItem['class'] . $aItem['type'] . $aItem['function'];
                    break;
                }
            }
            if (!empty($aItem['file']) && !empty($aItem['function']) && $aItem['function'] === __FUNCTION__) {
                $sFile = $aItem['file'];
            }
        }
        if ($sCaller) {
            $sActivePlugins = E::GetActivePluginsHash();
            return E::Module('Cache')->Key($sActivePlugins . '|' . $sCaller, func_get_args());
        }
        return false;
    }

}

// EOF