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
 * Files/Dirs functions for Alto CMS
 */
class AltoFunc_File {

    /**
     * @var int - Count of files inclusions
     */
    static protected $nIncludedCount = 0;

    /**
     * @var int - Time of files inclusions
     */
    static protected $nIncludedTime = 0.0;

    /**
     * @var array - Included files
     */
    static protected $aIncludedFiles = array();

    static protected $_temp = null;
    static protected $_time = null;

    /**
     * Returns total count of files inclusions
     *
     * @return int
     */
    static public function GetIncludedCount() {

        return self::$nIncludedCount;
    }

    /**
     * Returns total time of files inclusions
     *
     * @return float
     */
    static public function GetIncludedTime() {

        return self::$nIncludedTime;
    }

    /**
     * Returns total list of included files
     *
     * @return float
     */
    static public function GetIncludedFiles() {

        return self::$aIncludedFiles;
    }

    /**
     * Если загружена конфигурация, то возвращает корневую папку проекта,
     * в противном случае - корневую папку выполняемого веб-приложения
     *
     * @return string|null
     */
    static public function RootDir() {

        if (class_exists('Config', false)) {
            $sDir = Config::Get('path.root.dir');
        } elseif (isset($_SERVER['DOCUMENT_ROOT'])) {
            $sDir = $_SERVER['DOCUMENT_ROOT'];
        } else {
            $sDir = null;
        }
        if ($sDir && (substr($sDir, -1) != '\\') && (substr($sDir, -1) != '/')) {
            $sDir .= '/';
        }
        return $sDir;
    }

    /**
     * Если загружена конфигурация, то возвращает корневой URL проекта,
     * в противном случае - адрес веб-сайта выполняемого приложения
     *
     * @param mixed $xAddLang
     *
     * @return string|null
     */
    static public function RootUrl($xAddLang = false) {

        if (class_exists('Config', false)) {
            $sUrl = Config::Get('path.root.url');

            // Если требуется, то добавляем в URL язык
            if ($xAddLang && Config::Get('lang.in_url') && class_exists('Router', false)) {
                // Если строковый параметр, то это язык
                if (is_string($xAddLang)) {
                    $sLang = $xAddLang;
                } else {
                    // иначе язык берем из роутера
                    $sLang = Router::GetLang();
                }
                if ($sLang) {
                    $sUrl = self::NormPath($sUrl . '/' . $sLang . '/');
                }
            }
        } elseif (isset($_SERVER['HTTP_HOST'])) {
            $sUrl = 'http://' . $_SERVER['HTTP_HOST'];
        } else {
            $sUrl = null;
        }
        if ($sUrl && substr($sUrl, -1) != '/') {
            $sUrl .= '/';
        }
        return $sUrl;
    }

    /**
     * Нормализует путь к файлу, приводя все слеши (прямой и обратный) к одному виду,
     * по умолчанию - у прямому слешу
     *
     * @param string|array $sPath
     * @param string|null  $sSeparator
     *
     * @return string
     */
    static public function NormPath($sPath, $sSeparator = '/') {

        if (!$sSeparator) {
            $sSeparator = DIRECTORY_SEPARATOR;
        }
        if ($sSeparator == '/') {
            $sAltSeparator = '\\';
        }
        else {
            $sAltSeparator = '/';
        }

        if (is_array($sPath)) {
            $aResult = array();
            foreach ($sPath as $sKey => $s) {
                $aResult[$sKey] = self::NormPath($s, $sSeparator);
            }
            return $aResult;
        }

        $sPrefix = '';
        if (substr($sPath, 0, 2) == '//') {
            // path like '//site.com/...'
            $sPrefix = '//';
            $sPath = substr($sPath, 2);
        } elseif (($nPos = strpos($sPath, '://')) && $nPos) {
            // path like 'http://site.com/...'
            $sPrefix = substr($sPath, 0, $nPos + 3);
            $sPath = substr($sPath, $nPos + 3);
        } elseif (($nPos = strpos($sPath, ':\\')) && $nPos == 1) {
            // path like 'C:\folder\...'
            $sPrefix = substr($sPath, 0, 2) . $sSeparator;
            $sPath = substr($sPath, 3);
        }
        if (strpos($sPath, $sAltSeparator) !== false) {
            $sPath = str_replace($sAltSeparator, $sSeparator, $sPath);
        }

        while (strpos($sPath, $sSeparator . $sSeparator)) {
            $sPath = str_replace($sSeparator . $sSeparator, $sSeparator, $sPath);
        }
        return $sPrefix . $sPath;
    }

    /**
     * Проверяет наличие локальной папки (относительно корневой папки проекта),
     * и, если задано, создает ее с соответствующими правами
     *
     * @param string $sLocalDir
     * @param bool   $bAutoMake
     * @param int    $nMask
     *
     * @return bool
     */
    static public function CheckLocalDir($sLocalDir, $bAutoMake = true, $nMask = 0755) {

        return F::File_CheckDir(F::File_RootDir() . '/' . $sLocalDir, $bAutoMake, $nMask);
    }

    /**
     * Проверяет наличие папки и автоматически создает ее, если задано
     * TODO: Логгирование ошибки
     *
     * @param string $sDir
     * @param bool   $bAutoMake
     * @param int    $nMask
     *
     * @return bool
     */
    static public function CheckDir($sDir, $bAutoMake = true, $nMask = 0755) {

        $bResult = is_dir($sDir);
        if (!$bResult && $bAutoMake) {
            $bResult = @mkdir($sDir, $nMask, true);
        }
        return $bResult;
    }

    /**
     * Рекурсивное удаление папки
     *
     * @param string $sDir
     *
     * @return bool
     */
    static public function RemoveDir($sDir) {

        if (is_dir($sDir)) {
            $sPath = rtrim(self::NormPath($sDir), '/') . '/';

            if (($aFiles = self::ReadDir($sPath, GLOB_MARK))) {
                foreach ($aFiles as $sFile) {
                    if (is_dir($sFile)) {
                        self::RemoveDir($sFile);
                    } else {
                        @unlink($sFile);
                    }
                }
            }
            if (is_dir($sPath)) {
                return @rmdir($sPath);
            }
        }
        return true;
    }

    /**
     * Удаление содержимого папки
     *
     * @param string $sDir
     * @param bool   $bSafe
     *
     * @return bool
     */
    static public function ClearDir($sDir, $bSafe = true) {

        $bResult = true;
        $sDir = self::NormPath($sDir);
        if (substr($sDir, -1) != '/') {
            $sDir .= '/';
        }
        if (is_dir($sDir) && ($aFiles = self::ReadDir($sDir))) {
            foreach ($aFiles as $sFile) {
                // delete all files except started with 'dot'
                if (substr(basename($sFile), 0, 1) != '.') {
                    if (is_dir($sFile)) {
                        if ($bSafe) {
                            $bResult = $bResult && self::ClearDir($sFile, $bSafe);
                        } else {
                            $bResult = $bResult && self::RemoveDir($sFile);
                        }
                    } else {
                        $bResult = ($bResult && @unlink($sFile));
                    }
                }
            }
        }
        return $bResult;
    }

    /**
     * Возвращает содержимое папки, в т.ч. и скрытые файлы и подпапки
     *
     * @param string $sDir
     * @param int    $nFlag
     * @param bool   $bRecursively
     *
     * @return array
     */
    static function ReadDir($sDir, $nFlag = 0, $bRecursively = false) {

        if (substr($sDir, -1) == '*') {
            $sDir = substr($sDir, 0, strlen($sDir) - 1);
        }
        $aResult = glob($sDir . '/{,.}*', $nFlag | GLOB_BRACE);
        // исключаем из выдачи '.' и '..'
        $nCnt = 0;
        foreach ($aResult as $n => $sFile) {
            if (basename($sFile) == '.' || basename($sFile) == '..') {
                unset($aResult[$n]);
                if (++$nCnt > 1) {
                    break;
                } // исключаем лишние циклы
            }
        }

        if ($bRecursively) {
            if ($nFlag & GLOB_ONLYDIR) {
                $aSubDirs = $aResult;
            } else {
                $aSubDirs = self::ReadDir($sDir, GLOB_ONLYDIR);
            }
            if ($aSubDirs) {
                foreach ($aSubDirs as $sSubDir) {
                    if ($aSubResult = self::ReadDir($sSubDir, $nFlag)) {
                        $aResult = array_merge($aResult, $aSubResult);
                    }
                }
            }
        }
        return $aResult;
    }

    static function CopyDir($sDirSrc, $sDirTrg) {

        $aSource = self::ReadDir($sDirSrc, 0, true);
        foreach ($aSource as $sSource) {
            $sTarget = self::LocalPath($sSource, $sDirSrc);
            if ($sTarget) {
                if (is_file($sSource)) {
                    $bResult = self::Copy($sSource, $sDirTrg . $sTarget);
                    if (!$bResult) {
                        return false;
                    }
                } elseif (is_dir($sSource)) {
                    $bResult = self::CheckDir($sDirTrg . $sTarget);
                    if (!$bResult) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Преобразование URL проекта в путь к папке на сервере
     *
     * @param string $sUrl
     * @param string $sSeparator
     *
     * @return string
     */
    static public function Url2Dir($sUrl, $sSeparator = null) {

        // * Delete www from path
        $sUrl = str_replace('//www.', '//', $sUrl);
        if ($nPos = strpos($sUrl, '?')) {
            $sUrl = substr($sUrl, 0, $nPos);
        }
        $sPathWeb = str_replace('//www.', '//', F::File_RootUrl());
        // * do replace
        $sDir = str_replace($sPathWeb, F::File_RootDir(), $sUrl);
        return F::File_NormPath($sDir, $sSeparator);
    }

    /**
     * Преобразование пути к папке на сервере в URL
     *
     * @param string $sDir
     *
     * @return string
     */
    static public function Dir2Url($sDir) {

        return F::File_NormPath(
            str_replace(
                str_replace(DIRECTORY_SEPARATOR, '/', F::File_RootDir()),
                F::File_RootUrl(),
                str_replace(DIRECTORY_SEPARATOR, '/', $sDir)
            ), '/'
        );
    }

    /**
     * Из абсолютного пути выделяет относительный (локальный) относительно рута
     *
     * @param string $sPath
     * @param string $sRoot
     *
     * @return string
     */
    static public function LocalPath($sPath, $sRoot) {

        if ($sPath && $sRoot) {
            $sPath = F::File_NormPath($sPath);
            $sRoot = F::File_NormPath($sRoot);
            if (strpos($sPath, $sRoot) === 0) {
                return substr($sPath, strlen($sRoot));
            }
        }
        return false;
    }

    /**
     * Из абсолютного пути выделяет локальный относительно корневой папки проекта
     *
     * @param string $sPath
     *
     * @return string
     */
    static public function LocalDir($sPath) {

        return self::LocalPath($sPath, self::RootDir());
    }

    /**
     * Из абсолютного URL выделяет локальный относительно корневого URL проекта
     *
     * @param string $sPath
     *
     * @return string
     */
    static public function LocalUrl($sPath) {

        return self::LocalPath($sPath, self::RootUrl());
    }

    /**
     * Является ли путь локальным
     *
     * @param string $sPath
     *
     * @return bool
     */
    static public function IsLocalDir($sPath) {

        return (bool)self::LocalDir($sPath);
    }

    /**
     * Является ли URL локальным
     *
     * @param $sPath
     *
     * @return bool
     */
    static public function IsLocalUrl($sPath) {

        return (bool)self::LocalUrl($sPath);
    }

    /**
     * Проверяет наличие файла
     *
     * В отличие от системной функции file_exists() проверяет именно наличие файла, не папки
     * И может проверить наличие файла в конкретной папке или в одной из нескольких папок
     *     F::File_Exists('c:\dir\file.txt') - проверка существования файла 'c:\dir\file.txt'
     *     F::File_Exists('file.txt', 'c:\dir\') - проверка существования файла 'file.txt' в папке 'c:\dir\'
     *     F::File_Exists('file.txt', array('c:\dir\', 'd:\test')) - проверка существования файла 'file.txt' в одной
     *                                                              из папок 'c:\dir\' или 'd:\test'
     *
     * @param string $sFile
     * @param array  $aDirs
     *
     * @return bool|string
     */
    static public function Exists($sFile, $aDirs = array()) {

        $xResult = false;
        if (!$aDirs) {
            if (is_file($sFile)) {
                $xResult = self::NormPath($sFile);
            }
        } elseif (!is_array($aDirs)) {
            return F::File_Exists((string)$aDirs . '/' . $sFile);
        } else {
            foreach ($aDirs as $sDir) {
                $sResult = F::File_Exists($sFile, (string)$sDir);
                if ($sResult) {
                    $xResult = $sResult;
                    break;
                }
            }
        }
        return $xResult;
    }

    /**
     * Копирование файла
     * TODO: Логгирование ошибки
     *
     * @param string $sSource
     * @param string $sTarget
     * @param bool   $bRewrite
     *
     * @return bool
     */
    static public function Copy($sSource, $sTarget, $bRewrite = false) {

        if (F::File_Exists($sTarget) && !$bRewrite) {
            $bResult = true;
        } elseif (F::File_Exists($sSource) && F::File_CheckDir(dirname($sTarget))) {
            $bResult = @copy($sSource, $sTarget);
        } else {
            $bResult = false;
        }
        return $bResult ? $sTarget : false;
    }

    /**
     * Deletes file
     *
     * @param string $sFile
     * @param bool   $bRecursively
     * @param bool   $bNoCheck
     *
     * @return bool
     */
    static public function Delete($sFile, $bRecursively = false, $bNoCheck = false) {

        if (F::File_Exists($sFile) || $bNoCheck) {
            $bResult = @unlink($sFile);
        } else {
            $bResult = true;
        }
        if ($bRecursively && ($aDirs = glob(dirname($sFile) . '/*', GLOB_ONLYDIR))) {
            foreach ($aDirs as $sDir) {
                static::Delete($sDir . '/' . basename($sFile), $bRecursively, $bNoCheck);
            }
        }
        return $bResult;
    }

    /**
     * Deletes files by pattern
     *
     * @param string $sPattern
     * @param bool   $bRecursively
     * @param bool   $bNoCheck
     *
     * @return bool
     */
    static function DeleteAs($sPattern, $bRecursively = false, $bNoCheck = false) {

        $bResult = true;
        $aFiles = glob($sPattern);
        if ($aFiles) {
            foreach ($aFiles as $sFile) {
                $bResult = ($bResult && static::Delete($sFile, $bRecursively, $bNoCheck));
            }
        }
        return $bResult;
    }

    /**
     * Moves file to other destination
     *
     * @param string $sSource
     * @param string $sTarget
     * @param bool   $bRewrite
     *
     * @return bool
     */
    static public function Move($sSource, $sTarget, $bRewrite = false) {

        if (static::Copy($sSource, $sTarget, $bRewrite)) {
            return static::Delete($sSource);
        }
        return false;
    }

    /**
     * Чтение содержимого файла с проверкой на существование
     *
     * @param string $sFile
     *
     * @return bool|string
     */
    static public function GetContents($sFile) {

        if (F::File_Exists($sFile)) {
            return file_get_contents($sFile);
        }
        return false;
    }

    /**
     * Запись данных в файл. Если папки файла нет, то она создается
     *
     * @param string $sFile
     * @param string $sData
     * @param int    $nFlags
     *
     * @return  bool|int
     */
    static public function PutContents($sFile, $sData, $nFlags = 0) {

        if (F::File_CheckDir(dirname($sFile))) {
            return file_put_contents($sFile, $sData, $nFlags);
        }
        return false;
    }

    /**
     * Порционная отдача файла
     *
     * @param string $sFilename
     *
     * @return bool
     */
    static public function PrintChunked($sFilename) {

        $nChunkSize = 1 * (1024 * 1024);
        $xHandle = fopen($sFilename, 'rb');
        if ($xHandle === false) {
            return false;
        }
        while (!feof($xHandle)) {
            $sBuffer = fread($xHandle, $nChunkSize);
            if ($sBuffer !== false) {
                print $sBuffer;
            } else {
                return false;
            }
        }
        fclose($xHandle);
        return true;
    }

    /**
     * Разбирает полный путь файла
     * В отличии от стандартной функции pathinfo() выделяет GET-параметры и очищает от них имя и расширение файла
     *
     * @param string $sPath
     *
     * @return array
     */
    static public function PathInfo($sPath) {

        $aResult = array_merge(
            array(
                 'dirname'   => '',
                 'basename'  => '',
                 'filename'  => '',
                 'extension' => '',
                 'params'    => '',
            ),
            pathinfo(F::File_NormPath($sPath))
        );
        $n = strpos($aResult['extension'], '?');
        if ($n !== false) {
            $aResult['params'] = substr($aResult['extension'], $n + 1);
            $aResult['extension'] = substr($aResult['extension'], 0, $n);
            $n = strpos($aResult['basename'], '?');
            $aResult['basename'] = substr($aResult['basename'], 0, $n);
        }
        if (($aUrlInfo = parse_url($sPath)) && isset($aUrlInfo['host'])) {
            $aResult = array_merge($aResult, $aUrlInfo);
        }
        $aResult = array_merge(
            array(
                 'scheme'   => '',
                 'host'     => '',
                 'port'     => '',
                 'user'     => '',
                 'pass'     => '',
                 'path'     => '',
                 'query'    => '',
                 'fragment' => '',
            ),
            $aResult
        );

        return $aResult;
    }

    /**
     * Возвращает расширение файла из переданного полного пути
     *
     * @param string $sPath
     *
     * @return string
     */
    static public function GetExtension($sPath) {

        $aInfo = F::File_PathInfo($sPath);
        return $aInfo['extension'];
    }

    /**
     * Соответствует ли проверяемый путь одной из заданных масок путей
     * Возвращает ту маску, которой соответствует или false, если не соответствует ни одной
     *
     * @param string       $sNeedle - проверяемый путь
     * @param string|array $aPaths  - путь (или массив путей), на соответствие которым идет проверка
     *
     * @return string|bool
     */
    static public function InPath($sNeedle, $aPaths) {

        if (!is_array($aPaths)) {
            $aPaths = array((string)$aPaths);
        }
        $sNeedle = F::File_NormPath($sNeedle, '/');
        $aCheckPaths = F::File_NormPath($aPaths, '/');
        foreach ($aCheckPaths as $n => $sPath) {
            if ($sPath == '*') {
                return $aPaths[$n];
            } elseif (substr($sPath, -2) == '/*') {
                $sPath = substr($sPath, 0, strlen($sPath) - 2);
                if (strpos($sNeedle, $sPath) === 0) {
                    return $aPaths[$n];
                }
            } else {
                if (substr($sPath, -1) != '/') {
                    $sPath .= '/';
                }
                if ($sNeedle == $sPath) {
                    return $aPaths[$n];
                }
            }
        }
        return false;
    }

    /**
     * Returns full path to file
     *
     * @param string $sFile
     *
     * @return string
     */
    static public function FullDir($sFile) {

        if (self::IsLocalDir($sFile)) {
            return self::NormPath($sFile);
        }
        return self::NormPath(self::_calledFilePath() . $sFile);
    }

    /**
     * Подключение файла
     *
     * @param string $sFile
     * @param bool   $bOnce
     * @param mixed  $xConfig
     *
     * @return mixed
     */
    static public function IncludeFile($sFile, $bOnce = true, $xConfig = false) {

        if (is_array($xConfig)) {
            $config = $xConfig;
        } else {
            $config = array();
        }
        try {
            self::$_time = microtime(true);
            if ($bOnce) {
                self::$_temp = include_once($sFile);
            } else {
                self::$_temp = include($sFile);
            }
            self::$nIncludedTime += (microtime(true) - self::$_time);
            self::$nIncludedCount++;
            if (F::IsDebug()) {
                self::$aIncludedFiles[]
                    = $sFile . '; result: ' . (is_scalar(self::$_temp) ? self::$_temp : gettype(self::$_temp));
            }
        } catch (ErrorException $oException) {
            /**
             * TODO: надо логгировать ошибку подключения
             */
            if ($oException->getFile() !== __FILE__) {
                // Ошибка в подключённом файле
                //throw $oException;
                return false;
            } else {
                // Файл не был подключён.
                return false;
            }
        }
        if (($xConfig !== false) && !is_array(self::$_temp) && is_array($config)) {
            self::$_temp = $config;
        }
        return self::$_temp;
    }

    /**
     * Подключение файла билиотеки
     *
     * @param string $sFile
     * @param bool   $bOnce
     *
     * @return mixed
     */
    static public function IncludeLib($sFile, $bOnce = true) {

        return self::IncludeFile(Config::Get('path.dir.libs') . '/' . $sFile, $bOnce);
    }

    /**
     * Подключение файла, если он существует
     *
     * @param string $sFile
     * @param bool   $bOnce
     * @param bool   $bConfig
     *
     * @return array|mixed|null
     */
    static public function IncludeIfExists($sFile, $bOnce = true, $bConfig = false) {

        $xResult = null;
        if (F::File_Exists($sFile)) {
            $xResult = self::IncludeFile($sFile, $bOnce, $bConfig);
        }
        return $xResult;
    }

    /**
     * Перемещение загруженного файла во временную папку
     * Если второй параметр оканчивается на слеш, то он определяется, как подпапка, куда нужно переместить файл,
     * а имя задается такое же, как у исходного файла
     *
     * @param string $sUploadedFile   - загруженный файл
     * @param string $sFileName       - имя, которое будет присвоено файлу (может быть вида 'dirname/filenane.ext')
     *
     * @return string
     */
    static public function MoveUploadedFile($sUploadedFile, $sFileName = null) {

        if (!$sFileName) {
            $sFileName = basename($sUploadedFile);
        } elseif (substr($sFileName, -1) == '/') {
            $sFileName .= basename($sUploadedFile);
        }
        $sTmpFile = Config::Get('sys.cache.dir') . $sFileName;
        if (static::CheckDir(dirname($sTmpFile)) && move_uploaded_file($sUploadedFile, $sTmpFile)) {
            return $sTmpFile;
        }
    }

}

// EOF