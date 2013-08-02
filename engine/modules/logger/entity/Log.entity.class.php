<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Version: 0.9a
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

    public function __construct($aParam = false) {
        $this->SetFileName(Config::Get('sys.logs.file'));
        $this->SetFileDir(Config::Get('sys.logs.dir'));

        // Максимальный размер файла при ротации логов в байтах
        $this->SetSizeForRotate(1000000);

        // Использовать автоматическую ротация логов
        $this->SetUseRotate(false);

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

    public function GetRandom() {
        return rand(1000, 9999);
    }

    public function SetSizeForRotate($nSize) {
        if ($nSize && intval($nSize) > 0) {
            $this->setProp('size_for_rotate', intval($nSize));
            $this->SetUseRotate(true);
        } else {
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

    protected function _checlLogLevel($sLevel) {
        // Если уровень записи в лог больше либо равен текущему уровню, то создаем запись
        if (!$sLevel || ($nLevelIndex = array_search(strtoupper($sLevel), $this->aLogLevels)) == false) {
            $nLevelIndex = 0;
        }

        if ($nLevelIndex >= $this->GetLogLevel()) {
            return $this->aLogLevels[$nLevelIndex];
        }
        return false;
    }

    // Начало записи
    public function DumpBegin($sMsg, $sLevel = 'DEBUG') {
        if ($sLogLevel = $this->_checlLogLevel($sLevel)) {

            // Формируем запись
            $this->aRecord = array(
                'id'    => sprintf('%014.2F-%4d', microtime(true), $this->GetRandom()),
                'time'  => date('Y-m-d H:i:s'),
                'pid'   => @getmypid(),
                'level' => $sLogLevel,
                'trace' => null,
                'info'  => array($sMsg),
            );

            if ($this->getUseTrace()) {
                $this->aRecord['trace'] = $this->parserTrace(debug_backtrace());
            }
        }
    }

    // Добавление информации к записи
    public function DumpAppend($sMsg, $sLevel = 'DEBUG') {
        if ($sLogLevel = $this->_checlLogLevel($sLevel)) {
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

    public function DumpEnd($sMsg = null, $sLevel = 'DEBUG') {
        $xResult = false;
        // Если аргументы не переданы, а запись есть, то сохраняем ее
        $bForce = ((func_num_args() == 0) && $this->aRecord);
        if (($sLogLevel = $this->_checlLogLevel($sLevel)) || $bForce) {
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
                $xResult = $this->write($sMsgOut);

                // Очищаем текущую запись
                $this->aRecord = array();
            }
        }
        return $xResult;
    }

    /**
     * Производит сохранение в файл
     *
     * @param   string $msg    Сообщение
     *
     * @return bool
     */
    protected function write($msg) {
        $xResult = false;
        // * Если имя файла не задано то ничего не делаем
        if (!($sFileName = $this->GetFileName())) {
            //throw new Exception("Empty file name for log!");
            return false;
        }
        // * Если имя файла равно '-' то выводим сообщение лога в браузер
        if ($sFileName == '-') {
            echo($msg . "<br>\n");
        } else {
            // * Запись в файл
            if ($xResult = F::File_PutContents($this->GetFileDir() . $sFileName, $msg . "\n", FILE_APPEND | LOCK_EX)) {
                // * Если нужно, то делаем ротацию
                if ($this->GetUseRotate() && $this->GetSizeForRotate()) {
                    $this->rotate();
                }
            }
        }
        return $xResult;
    }

    /**
     * Производит ротацию логов
     *
     */
    protected function rotate() {
        clearstatcache();
        /**
         * Если размер файла лога привысил максимальный то сохраняем текущий файл в архивный, а текущий становится пустым
         */
        if (filesize($this->GetFileDir() . $this->GetFileName()) >= $this->GetSizeForRotate()) {
            $pathinfo = pathinfo($this->sPathLogs . $this->getFileName());
            $name = $pathinfo['basename'];
            $aName = explode('.', $name);
            $i = 1;
            while (true) {
                $sNewName = $aName[0] . ".$i." . $aName[1];
                $sNewFullName = $pathinfo['dirname'] . '/' . $sNewName;
                if (!F::File_Exists($sNewFullName)) {
                    $this->rotateRename($i - 1);
                    break;
                }
                $i++;
            }
        }
    }

    /**
     * Переименовывает все файлы логов согласно их последовательности
     *
     * @param int $numberLast
     */
    protected function rotateRename($numberLast) {
        $pathinfo = pathinfo($this->GetFileDir() . $this->GetFileName());
        $aName = explode('.', $pathinfo['basename']);
        for ($i = $numberLast; $i > 0; $i--) {
            $sFullNameCur = $pathinfo['dirname'] . '/' . $aName[0] . ".$i." . $aName[1];
            $sFullNameNew = $pathinfo['dirname'] . '/' . $aName[0] . '.' . ($i + 1) . '.' . $aName[1];
            rename($sFullNameCur, $sFullNameNew);
        }
        rename($this->GetFileDir() . $this->GetFileName(), $pathinfo['dirname'] . '/' . $aName[0] . '.1.' . $aName[1]);
    }

    /**
     * Выполняет форматирование трассировки
     *
     * @param array $aTrace
     *
     * @return string
     */
    protected function parserTrace($aTrace) {
        $sMsg = '';
        for ($i = count($aTrace) - 1; $i >= 0; $i--) {
            if (isset($aTrace[$i]['class'])) {
                $funct = $aTrace[$i]['class'] . $aTrace[$i]['type'] . $aTrace[$i]['function'] . '()';
            } else {
                $funct = $aTrace[$i]['function'] . '()';
            }
            $sMsg .= $aTrace[$i]['file'] . '(line:' . $aTrace[$i]['line'] . '){' . $funct . '}';
            if ($i != 0) {
                $sMsg .= ' => ';
            }
        }
        return $sMsg;
    }


}

// EOF