<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Version: 0.9a
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
 */
abstract class Module extends LsObject {
    const STATUS_INIT_BEFORE = 1;
    const STATUS_INIT = 2;
    const STATUS_DONE_BEFORE = 3;
    const STATUS_DONE = 4;

    /**
     * Объект ядра
     *
     * @var Engine
     */
    protected $oEngine = null;

    /** @var int Статус модуля */
    protected $nStatus = 0;

    /** @var bool Признак предзагрузки */
    protected $bPreloaded = false;

    /**
     * При создании модуля передаем объект ядра
     *
     * @param Engine $oEngine
     */
    final public function __construct(Engine $oEngine) {
        $this->oEngine = $oEngine;
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
     * Возвращает массив ID сущностей, если передан массив объектов, либо просто массив ID
     *
     * @param $aEntities
     * @return array
     */
    protected function _entitiesId($aEntities) {
        $aIds = array();
        if (!is_array($aEntities)) {
            $aEntities = array($aEntities);
        }
        foreach ($aEntities as $oEntity) {
            if ($nId = is_object($oEntity) ? intval($oEntity->GetId()) : intval($oEntity)) {
                $aIds[] = $nId;
            }
        }
        return $aIds;
    }

    /**
     * Возвращает ID сущности, если передан объект, либо просто ID
     *
     * @param $oEntityId
     * @return int|mixed
     */
    protected function _entityId($oEntityId) {
        if (is_scalar($oEntityId)) {
            return intval($oEntityId);
        } else {
            $aIds = $this->_entitiesId($oEntityId);
            if ($aIds)
                return array_shift($aIds);
        }
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

    public function LogError($sMsg) {
        return F::LogError(get_class($this) . ': ' . $sMsg);
    }
}

// EOF