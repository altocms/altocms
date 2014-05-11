<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

/**
 * Модуль логирования
 */
class ModuleLogger extends Module {

    static protected $aLogs = array();

    /**
     * Инициализация, устанавливает имя файла лога
     *
     */
    public function Init() {
    }

    /**
     * Уставливает текущий уровень лога, тот уровень при котором будет производиться запись в файл лога
     *
     * @param int, string('DEBUG','NOTICE','ERROR') $level    Уровень логирования
     * @return bool
     */
    public function SetWriteLevel($level) {

        if (preg_match("/^\d$/", $level) and isset($this->aLogLevels[$level])) {
            $this->nLogLevel = $level;
            return true;
        }
        $level = strtoupper($level);
        if ($key = array_search($level, $this->aLogLevels)) {
            $this->nLogLevel = $key;
            return true;
        }
        return false;
    }

    /**
     * Возвращает текущий уровень лога
     *
     * @return int
     */
    public function GetWriteLevel() {

        return $this->Reset('default')->GetLogLevel();
    }

    /**
     * Использовать трассировку или нет
     *
     * @param bool $bool    Использовать или нет троссировку в логах
     */
    public function SetUseTrace($bool) {

        return $this->Reset('default')->SetUseTrace((bool)$bool);
    }

    /**
     * Использует трассировку или нет
     *
     * @return bool
     */
    public function GetUseTrace() {

        return (bool)$this->Reset('default')->GetUseTrace();
    }

    /**
     * Использовать ротацию логов или нет
     *
     * @param bool $bool
     */
    public function SetUseRotate($bool) {

        return $this->Reset('default')->SetUseRotate((bool)$bool);
    }

    /**
     * Использует ротацию логов или нет
     *
     * @return bool
     */
    public function GetUseRotate() {

        return (bool)$this->Reset('default')->GetUseRotate();
    }

    /**
     * Устанавливает имя файла лога
     *
     * @param string $sFile
     */
    public function SetFileName($sFile) {

        return $this->Reset('default')->SetFileName($sFile);
    }

    /**
     * Получает имя файла лога
     *
     * @return string
     */
    public function GetFileName() {

        return $this->Reset('default')->GetFileName();
    }

    /**
     * Запись в лог с уровнем логирования 'DEBUG'
     *
     * @param string $msg    Сообщение для записи в лог
     */
    public function Debug($msg) {

        $this->log($msg, 'DEBUG');
    }

    /**
     * Запись в лог с уровнем логирования 'ERROR'
     *
     * @param string $msg    Сообщение для записи в лог
     */
    public function Error($msg) {

        $this->log($msg, 'ERROR');
    }

    /**
     * Запись в лог с уровнем логирования 'NOTICE'
     *
     * @param string $msg    Сообщение для записи в лог
     */
    public function Notice($msg) {

        $this->log($msg, 'NOTICE');
    }

    /**
     * Записывает лог
     *
     * @param string $sMsg   - Сообщение для записи в лог
     * @param string $sLevel - Уровень логирования
     *
     * @return bool
     */
    protected function Log($sMsg, $sLevel) {

        return $this->Dump('default', $sMsg, $sLevel);
    }

    /**
     * Производит сохранение в файл
     *
     * @param string $sMsg    Сообщение
     * @return bool
     */
    protected function Write($sMsg) {

        return $this->Dump('default', $sMsg);
    }

    /**
     * @param string $sLog
     * @param string $sFileName
     *
     * @return ModuleLogger_EntityLog
     */
    public function Reset($sLog, $sFileName = null) {

        if (!isset(self::$aLogs[$sLog])) {
            if (!$sFileName) $sFileName = $sLog;
            $oLog = Engine::getInstance()->GetEntity('Logger_Log', array(
                'file_name' => $sFileName,
                'file_dir' => Config::Get('sys.logs.dir'),
            ));
            self::$aLogs[$sLog] = $oLog;
        }
        return self::$aLogs[$sLog];
    }

    /**
     * @param object|string $oLog
     * @param string        $sMsg
     * @param string        $sLevel
     *
     * @return bool
     */
    public function Dump($oLog, $sMsg, $sLevel = null) {

        if (!is_object($oLog)) {
            $oLog = $this->Reset((string)$oLog);
        }
        return $oLog->Dump($sMsg, $sLevel);
    }

}

// EOF