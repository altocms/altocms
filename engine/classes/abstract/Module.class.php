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
    
    const STATUS_INIT_BEFORE = 1;
    const STATUS_INIT        = 2;
    const STATUS_DONE_BEFORE = 3;
    const STATUS_DONE        = 4;

    /** @var int Статус модуля */
    protected $iStatus = 0;

    /** @var bool Признак предзагрузки */
    protected $bPreloaded = false;

    /** @var  string Instance name */
    protected $sInstanceName;

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
    abstract public function init();

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
     * @param $sInstanceName
     *
     * @return $this
     */
    public function setInstanceName($sInstanceName) {

        $this->sInstanceName = $sInstanceName;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstanceName() {

        return $this->sInstanceName;
    }

    /**
     * Метод срабатывает при завершении работы ядра
     *
     */
    public function shutdown() {

    }

    /**
     * Устанавливает статус модуля
     *
     * @param   int $nStatus
     */
    public function setStatus($nStatus) {

        $this->iStatus = $nStatus;
    }

    /**
     * Вовзращает статус модуля
     *
     * @return int
     */
    public function getStatus() {

        return $this->iStatus;
    }

    public function setPreloaded($bVal) {

        $this->bPreloaded = (bool)$bVal;
    }

    public function isPreloaded() {

        return $this->bPreloaded;
    }

    /**
     * Устанавливает признак начала и завершения инициализации модуля
     *
     * @param bool $bBefore
     */
    public function setInit($bBefore = false) {

        if ($bBefore) {
            $this->setStatus(self::STATUS_INIT_BEFORE);
        } else {
            $this->setStatus(self::STATUS_INIT);
        }
    }

    /**
     * Устанавливает признак начала и завершения шатдауна модуля
     *
     * @param bool $bBefore
     */
    public function setDone($bBefore = false) {

        if ($bBefore) {
            $this->setStatus(self::STATUS_DONE_BEFORE);
        } else {
            $this->setStatus(self::STATUS_DONE);
        }
    }

    /**
     * Возвращает значение флага инициализации модуля
     *
     * @return bool
     */
    public function InInitProgress() {

        return $this->getStatus() == self::STATUS_INIT_BEFORE;
    }

    /**
     * Возвращает значение флага инициализации модуля
     *
     * @return bool
     */
    public function isInit() {

        return $this->getStatus() >= self::STATUS_INIT;
    }

    /**
     * Возвращает значение флага инициализации модуля
     *
     * @return bool
     */
    public function InShudownProgress() {

        return $this->getStatus() == self::STATUS_DONE_BEFORE;
    }

    /**
     * Возвращает значение флага инициализации модуля
     *
     * @return bool
     */
    public function isDone() {

        return $this->getStatus() >= self::STATUS_DONE;
    }

    /**
     * @param string $sMsg
     *
     * @return bool
     */
    public function logError($sMsg) {

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
    public function structurize() {

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
            if (!$oEntity->hasProp($sPropKey)) {
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

}

// EOF