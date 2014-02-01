<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

class ModuleLogger_EntityLog extends Entity {

    protected $aLogs = array();

    /**
     * Уровни логгирования
     *
     * @var array
     */
    protected $aLogLevels = array(
            'DEBUG',
            'NOTICE',
            'ERROR',
        );

    protected $aRecord = array();

    public function __construct($aParam = null) {

        $this->SetFileName(Config::Get('sys.logs.file'));
        $this->SetFileDir(Config::Get('sys.logs.dir'));

        // Max file size for rotation
        $this->SetSizeForRotate(Config::Get('sys.logs.size_for_rotate'));

        // Текущий уровень логирования
        $this->SetLogLevel(0);

        parent::__construct($aParam);
    }

    public function __destruct() {

        // Если есть несоханенная запись, то сохраняем ее
        if ($this->aRecord) {
            $this->DumpEnd();
        }
    }

    /**
     * Sets dir for log file
     *
     * @param $sDir
     */
    public function SetFileDir($sDir) {

        if (substr($sDir, -1) !== '/') {
            $sDir .= '/';
        }
        parent::SetFileDir($sDir);
    }

    /**
     * Sets max size for file rotation
     *
     * @param $iSize
     */
    public function SetSizeForRotate($iSize) {

        $iSize = intval($iSize);
        if ($iSize > 0) {
            $this->setProp('size_for_rotate', $iSize);
            // Set rotation to on
            $this->SetUseRotate(true);
        } else {
            // Set rotation to off
            $this->SetUseRotate(false);
        }
    }

    /**
     * Dump to log file
     *
     * @param   string $sMsg
     * @param   string $sLevel
     *
     * @return bool
     */
    public function Dump($sMsg, $sLevel = 'DEBUG') {

        // * Если уровень записи в лог больше либо равен текущему уровню, то пишем
        if (!$sLevel || ($nLevelIndex = array_search(strtoupper($sLevel), $this->aLogLevels)) == false) {
            $nLevelIndex = 0;
        }
        if ($nLevelIndex >= $this->GetLogLevel()) {
            $this->DumpBegin($sMsg, $sLevel);
            return $this->DumpEnd();
        }
    }

    protected function _checkLogLevel($sLevel) {

        // Если уровень записи в лог больше либо равен текущему уровню, то создаем запись
        if (!$sLevel || ($nLevelIndex = array_search(strtoupper($sLevel), $this->aLogLevels)) == false) {
            $nLevelIndex = 0;
        }

        if ($nLevelIndex >= $this->GetLogLevel()) {
            return $this->aLogLevels[$nLevelIndex];
        }
        return false;
    }

    /**
     * Begin of record for log file
     *
     * @param string $sMsg
     * @param string $sLevel
     */
    public function DumpBegin($sMsg, $sLevel = 'DEBUG') {

        if ($sLogLevel = $this->_checkLogLevel($sLevel)) {

            // Формируем запись
            $this->aRecord = array(
                'id'    => sprintf('%014.3F-%s', microtime(true), strtoupper(uniqid())),
                'time'  => date('Y-m-d H:i:s'),
                'pid'   => @getmypid(),
                'level' => $sLogLevel,
                'trace' => null,
                'info'  => array($sMsg),
            );

            if ($this->getUseTrace()) {
                $this->aRecord['trace'] = $this->_parserTrace(debug_backtrace());
            }
        }
    }

    /**
     * Append message to record
     *
     * @param string $sMsg
     * @param string $sLevel
     */
    public function DumpAppend($sMsg, $sLevel = 'DEBUG') {

        if ($sLogLevel = $this->_checkLogLevel($sLevel)) {
            if (!$this->aRecord) {
                // Запись не создавалась, надо ее создать
                return $this->DumpBegin($sMsg, $sLevel);
            } elseif ($this->aRecord['level'] != $sLogLevel) {
                // Если уровень логгирования изменился, то пишем текущую запись и создаем новую
                $this->DumpEnd(null, $sLevel);
                return $this->DumpBegin($sMsg, $sLevel);
            }

            // Добавляем текст в запись
            $this->aRecord['info'][] = $sMsg;
        }
    }

    /**
     * End of record for log file
     *
     * @param string $sMsg
     * @param string $sLevel
     *
     * @return bool
     */
    public function DumpEnd($sMsg = null, $sLevel = 'DEBUG') {

        $xResult = false;
        // Если аргументы не переданы, а запись есть, то сохраняем ее
        $bForce = ((func_num_args() == 0) && $this->aRecord);
        if (($sLogLevel = $this->_checkLogLevel($sLevel)) || $bForce) {
            if (!$this->aRecord) {
                // Запись не создавалась, надо ее создать
                return $this->DumpBegin($sMsg, $sLevel);
            } elseif (($this->aRecord['level'] != $sLogLevel) && $sMsg) {
                // Если уровень логгирования изменился, то пишем текущую запись и создаем новую
                $this->DumpEnd();
                return $this->DumpBegin($sMsg, $sLevel);
            }

            // Формируем текст лога со служебной информацией
            if ($this->aRecord) {
                if ($sMsg) {
                    $this->aRecord['info'][] = $sMsg;
                }

                $sMsgOut = '[LOG:' . $this->aRecord['id'] . ']';
                $sMsgOut .= '[' . $this->aRecord['time'] . ']';
                $sMsgOut .= '[PID:' . $this->aRecord['pid'] . ']';
                $sMsgOut .= '[' . $this->aRecord['level'] . "][[\n";

                if (count($this->aRecord['info']) > 1) {
                    $sMsgText = '';
                    foreach ($this->aRecord['info'] as $sTxt) {
                        if ($sMsgText) {
                            $sMsgText .= "\n";
                        }
                        $sMsgText .= $sTxt;
                    }
                } else {
                    $sMsgText = $this->aRecord['info'][0];
                }
                $sMsgOut .= $sMsgText . "\n]]";

                // Если нужно, то добавляем трассировку
                if ($this->aRecord['trace']) {
                    $sMsgOut .= "\n[TRACE: " . $this->aRecord['trace'] . "]\n";
                }
                $sMsgOut .= '[END:' . $this->aRecord['id'] . "]\n";

                // Записываем
                $xResult = $this->_write($sMsgOut);

                // Очищаем текущую запись
                $this->aRecord = array();
            }
        }
        return $xResult;
    }

    /**
     * Writes message to log file
     *
     * @param string $sMsg - message to log
     *
     * @return bool|int
     */
    protected function _write($sMsg) {

        $xResult = false;
        // * Если имя файла не задано то ничего не делаем
        if (!($sFileName = $this->GetFileName())) {
            //throw new Exception("Empty file name for log!");
            return false;
        }
        // * Если имя файла равно '-' то выводим сообщение лога в браузер
        if ($sFileName == '-') {
            echo($sMsg . "<br/>\n");
        } else {
            // * Запись в файл
            if ($xResult = F::File_PutContents($this->GetFileDir() . $sFileName, $sMsg . "\n", FILE_APPEND | LOCK_EX)) {
                // * Если нужно, то делаем ротацию
                if ($this->GetUseRotate() && $this->GetSizeForRotate()) {
                    $this->_rotate();
                }
            }
        }
        return $xResult;
    }

    /**
     * Rotates log files
     */
    protected function _rotate() {

        clearstatcache();
        // Если размер файла лога привысил максимальный то сохраняем текущий файл в архивный,
        // а текущий становится пустым
        $sFileName = $this->GetFileDir() . $this->GetFileName();
        if (filesize($sFileName) >= $this->GetSizeForRotate()) {
            $aPathInfo = pathinfo($sFileName);
            $i = 1;
            while (true) {
                $sNewFullName = $aPathInfo['dirname'] . '/' . $aPathInfo['filename'] . ".$i." . $aPathInfo['extension'];
                if (!F::File_Exists($sNewFullName)) {
                    $this->_rotateRename($i - 1);
                    break;
                }
                $i++;
            }
        }
    }

    /**
     * Переименовывает все файлы логов согласно их последовательности
     *
     * @param int $iNumberLast
     */
    protected function _rotateRename($iNumberLast) {

        $aPathInfo = pathinfo($this->GetFileDir() . $this->GetFileName());
        $aName = explode('.', $aPathInfo['basename']);
        for ($i = $iNumberLast; $i > 0; $i--) {
            $sFullNameCur = $aPathInfo['dirname'] . '/' . $aName[0] . ".$i." . $aName[1];
            $sFullNameNew = $aPathInfo['dirname'] . '/' . $aName[0] . '.' . ($i + 1) . '.' . $aName[1];
            rename($sFullNameCur, $sFullNameNew);
        }
        rename($this->GetFileDir() . $this->GetFileName(), $aPathInfo['dirname'] . '/' . $aName[0] . '.1.' . $aName[1]);
    }

    /**
     * Выполняет форматирование трассировки
     *
     * @param array $aTrace
     *
     * @return string
     */
    protected function _parserTrace($aTrace) {

        $sMsg = '';
        for ($i = count($aTrace) - 1; $i >= 0; $i--) {
            if (isset($aTrace[$i]['class'])) {
                $sFunc = $aTrace[$i]['class'] . $aTrace[$i]['type'] . $aTrace[$i]['function'] . '()';
            } else {
                $sFunc = $aTrace[$i]['function'] . '()';
            }
            $sMsg .= $aTrace[$i]['file'] . '(line:' . $aTrace[$i]['line'] . '){' . $sFunc . '}';
            if ($i != 0) {
                $sMsg .= ' => ';
            }
        }
        return $sMsg;
    }

}

// EOF