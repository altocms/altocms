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
     * @return array
     */
    static public function GetIncludedFiles() {

        return self::$aIncludedFiles;
    }

    static protected $_root = array();

    /**
     * Если загружена конфигурация, то возвращает корневую папку проекта,
     * в противном случае - корневую папку выполняемого веб-приложения
     *
     * @return string|null
     */
    static public function RootDir() {

        if (!empty(self::$_root['dir'])) {
            $sDir = self::$_root['dir'];
        } elseif (class_exists('Config', false) && Config::Get('path.root.dir')) {
            $sDir = Config::Get('path.root.dir');
            self::$_root['dir'] = $sDir;
        } elseif (defined('ALTO_DIR')) {
            $sDir = ALTO_DIR;
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

        if (!empty(self::$_root['url'][$xAddLang])) {
            $sUrl = self::$_root['url'][$xAddLang];
        } elseif (class_exists('Config', false) && ($sUrl = Config::Get('path.root.url'))) {

            // Если требуется, то добавляем в URL язык
            if ($xAddLang && Config::Get('lang.in_url') && class_exists('Router', false)) {
                // Если строковый параметр, то это язык
                if (is_string($xAddLang)) {
                    $sLang = $xAddLang;
                } else {
                    // иначе язык берем из роутера
                    $sLang = R::GetLang();
                }
                if ($sLang) {
                    $sUrl = static::NormPath($sUrl . '/' . $sLang . '/');
                }
            }
            self::$_root['url'][$xAddLang] = $sUrl;
        } elseif (isset($_SERVER['HTTP_HOST'])) {
            $sUrl = F::UrlScheme(true) . $_SERVER['HTTP_HOST'];
        } else {
            $sUrl = null;
        }
        if ($sUrl && substr($sUrl, -1) != '/') {
            $sUrl .= '/';
        }
        return $sUrl;
    }

    /**
     * @return array
     */
    static public function RootUrlAliases() {

        $aResult = array(static::RootUrl());
        if (class_exists('Config', false)) {
            if ($aAliases = Config::Get('path.root.aliases')) {
                if (!is_array($aAliases)) {
                    $aAliases = array((string)$aAliases);
                    if ($sScheme = parse_url($aResult[0], PHP_URL_SCHEME)) {
                        foreach($aAliases as $n => $sAliasUrl) {
                            if (!parse_url($sAliasUrl, PHP_URL_SCHEME)) {
                                $aAliases[$n] = $sScheme . '://' . $sAliasUrl;
                            }
                        }
                    }
                }
                $aResult = array_unique(array_merge($aResult, $aAliases));
            }
        }
        return $aResult;
    }

    /**
     * Нормализует путь к файлу, приводя все слеши (прямой и обратный) к одному виду,
     * по умолчанию - к прямому слешу
     *
     * @param string|string[] $xPath
     * @param string|null  $sSeparator
     *
     * @return string|string[]
     */
    static public function NormPath($xPath, $sSeparator = '/') {

        if (!$sSeparator) {
            $sSeparator = DIRECTORY_SEPARATOR;
        }
        if ($sSeparator == '/') {
            $sAltSeparator = '\\';
        }
        else {
            $sAltSeparator = '/';
        }

        if (is_array($xPath)) {
            $aResult = array();
            foreach ($xPath as $sKey => $s) {
                $aResult[$sKey] = static::NormPath($s, $sSeparator);
            }
            return $aResult;
        }

        $sPrefix = '';
        if (substr($xPath, 0, 2) == '//') {
            // path like '//site.com/...'
            $sPrefix = '//';
            $xPath = substr($xPath, 2);
        } elseif (($nPos = strpos($xPath, '://')) && $nPos) {
            // path like 'http://site.com/...'
            $sPrefix = substr($xPath, 0, $nPos + 3);
            $xPath = substr($xPath, $nPos + 3);
        } elseif (($nPos = strpos($xPath, ':\\')) && $nPos == 1) {
            // path like 'C:\folder\...'
            $sPrefix = substr($xPath, 0, 2) . $sSeparator;
            $xPath = substr($xPath, 3);
        }
        if (strpos($xPath, $sAltSeparator) !== false) {
            $xPath = str_replace($sAltSeparator, $sSeparator, $xPath);
        }

        while (strpos($xPath, $sSeparator . $sSeparator)) {
            $xPath = str_replace($sSeparator . $sSeparator, $sSeparator, $xPath);
        }
        $sResult = $sPrefix . $xPath;
        if (DIRECTORY_SEPARATOR == '\\' && strlen($sResult) > 2) {
            // First symbol in Windows is a disk
            if ($sResult[1] == ':' && $sResult[0] >= 'a' && $sResult[0] <= 'z') {
                $sResult = ucfirst($sResult);
            }
        }
        return $sResult;
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

        return static::CheckDir(static::RootDir() . '/' . $sLocalDir, $bAutoMake, $nMask);
    }

    /**
     * Проверяет наличие папки и автоматически создает ее, если задано
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
            $iOldUmask = umask(0);
            $bResult = @mkdir($sDir, $nMask, true);
            if (!$bResult) {
                F::SysWarning('Can not make dir "' . $sDir . '"');
            }
            umask($iOldUmask);
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
            $sPath = rtrim(static::NormPath($sDir), '/') . '/';

            if (($aFiles = static::ReadDir($sPath, GLOB_MARK))) {
                foreach ($aFiles as $sFile) {
                    if (is_dir($sFile)) {
                        static::RemoveDir($sFile);
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
     * @param string|array $xDir
     * @param bool   $bSafe
     *
     * @return bool
     */
    static public function ClearDir($xDir, $bSafe = true) {

        $bResult = true;
        if (is_array($xDir)) {
            foreach($xDir as $sDir) {
                $bResult = $bResult && static::ClearDir($sDir, $bSafe);
            }
            return $bResult;
        } else {
            $sDir = (string)$xDir;
        }
        $sDir = static::NormPath($sDir);
        if (substr($sDir, -1) != '/') {
            $sDir .= '/';
        }
        if (is_dir($sDir) && ($aFiles = static::ReadDir($sDir))) {
            foreach ($aFiles as $sFile) {
                // delete all files except started with 'dot'
                if (substr(basename($sFile), 0, 1) != '.') {
                    if (is_dir($sFile)) {
                        if ($bSafe) {
                            $bResult = $bResult && static::ClearDir($sFile, $bSafe);
                        } else {
                            $bResult = $bResult && static::RemoveDir($sFile);
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
     * Excludes special names "." and ".." from files/dirs array
     *
     * @param array $aDirs
     *
     * @return mixed
     */
    static protected function _excludeDotted($aDirs) {

        if (!$aDirs || !is_array($aDirs)) {
            return $aDirs;
        }

        // исключаем из выдачи '.' и '..'
        $nCnt = 0;
        foreach ($aDirs as $n => $sFile) {
            if (basename($sFile) == '.' || basename($sFile) == '..') {
                unset($aDirs[$n]);
                if (++$nCnt > 1) {
                    // исключаем лишние циклы
                    return $aDirs;
                }
            }
        }
        return $aDirs;
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
    static public function ReadDir($sDir, $nFlag = 0, $bRecursively = false) {

        if (substr($sDir, -2) == '/*') {
            $sMask = '*';
            $sDir = substr($sDir, 0, strlen($sDir) - 2);
        } elseif ((substr($sDir, -1) != '/') && (substr(basename($sDir), 0, 2) == '*.')) {
            $sMask = basename($sDir);
            $sDir = dirname($sDir);
        } else {
            $sMask = '*';
        }

        if ($bRecursively || ($nFlag & GLOB_ONLYDIR)) {
            $aSubDirs = glob($sDir . ((substr($sDir, -1) !== '/') ? '/' : '') . '{,.}*', $nFlag | GLOB_BRACE | GLOB_ONLYDIR);
            // исключаем из выдачи '.' и '..'
            $aSubDirs = static::_excludeDotted($aSubDirs);
        } else {
            $aSubDirs = array();
        }

        if ($nFlag & GLOB_ONLYDIR && !$bRecursively) {
            return $aSubDirs;
        }

        if (substr($sDir, -1) != '/') {
            $sDir .= '/';
        }
        if (substr($sMask, 0, 1) == '*') {
            $aResult = glob($sDir . '{,.}' . $sMask, $nFlag | GLOB_BRACE);
        } else {
            $aResult = glob($sDir . $sMask, $nFlag | GLOB_BRACE);
        }
        $aResult = static::_excludeDotted($aResult);

        if ($bRecursively && $aSubDirs) {
            foreach ($aSubDirs as $sSubDir) {
                if ($aSubResult = static::ReadDir($sSubDir . '/' . $sMask, $nFlag, $bRecursively)) {
                    $aResult = array_merge($aResult, $aSubResult);
                }
            }
        }
        return $aResult;
    }

    /**
     * Returns files only (exludes directories)
     *
     * @param      $sDir
     * @param int  $nFlag
     * @param bool $bRecursively
     *
     * @return array
     */
    static public function ReadFileList($sDir, $nFlag = 0, $bRecursively = false) {

        if ($nFlag & GLOB_ONLYDIR) {
            return array();
        }
        $sDir = str_replace('\\', '/', $sDir);
        if (substr($sDir, -1) == '/') {
            $sDir .= '*';
        }
        $aFileList = static::ReadDir($sDir, $nFlag, $bRecursively);
        foreach ($aFileList as $nKey => $sFile) {
            if (is_dir($sFile)) {
                unset($aFileList[$nKey]);
            }
        }
        return $aFileList;
    }

    /**
     * Копирование содержимого папки в другую папку
     *
     * @param $sDirSrc
     * @param $sDirTrg
     *
     * @return bool
     */
    static public function CopyDir($sDirSrc, $sDirTrg) {

        $sDirTrg = static::NormPath($sDirTrg . '/');
        $aSource = static::ReadDir($sDirSrc, 0, true);
        foreach ($aSource as $sSource) {
            $sTarget = static::LocalPath($sSource, $sDirSrc);
            if ($sTarget) {
                if (is_file($sSource)) {
                    $bResult = static::Copy($sSource, $sDirTrg . $sTarget);
                    if (!$bResult) {
                        return false;
                    }
                } elseif (is_dir($sSource)) {
                    $bResult = static::CheckDir($sDirTrg . $sTarget);
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
    static public function Url2Dir($sUrl, $sSeparator = '/') {

        // * Delete www from path
        $sUrl = str_replace('//www.', '//', $sUrl);
        if ($nPos = strpos($sUrl, '?')) {
            $sUrl = substr($sUrl, 0, $nPos);
        }
        $sPathWeb = str_replace('//www.', '//', static::RootUrl());
        // * do replace
        $sDir = str_replace($sPathWeb, static::RootDir(), $sUrl);
        return static::NormPath($sDir, $sSeparator);
    }

    /**
     * Преобразование пути к папке на сервере в URL
     *
     * @param string $sDir
     *
     * @return string
     */
    static public function Dir2Url($sDir) {

        return static::NormPath(
            str_replace(
                str_replace(DIRECTORY_SEPARATOR, '/', static::RootDir()),
                static::RootUrl(),
                str_replace(DIRECTORY_SEPARATOR, '/', $sDir)
            ), '/'
        );
    }

    /**
     * Из абсолютного пути выделяет относительный (локальный) относительно рута
     *
     * @param string $sPath
     * @param string|array $xRoot
     *
     * @return string|bool
     */
    static public function LocalPath($sPath, $xRoot) {

        $xResult = false;
        if (is_array($xRoot)) {
            foreach ($xRoot as $sRoot) {
                $xResult = static::LocalPath($sPath, $sRoot);
                if ($xResult) {
                    return $xResult;
                }
            }
            return $xResult;
        } else {
            $sRoot = (string)$xRoot;
        }
        if ($sPath && $sRoot) {
            $sPath = static::NormPath($sPath);
            $sRoot = static::NormPath($sRoot . '/');
            if (DIRECTORY_SEPARATOR == '\\') {
                // case-insensitive in Windows
                if (stripos($sPath, $sRoot) === 0 || stripos($sPath . '/', $sRoot) === 0) {
                    return substr($sPath, strlen($sRoot));
                }
            } else {
                if (strpos($sPath, $sRoot) === 0 || strpos($sPath . '/', $sRoot) === 0) {
                    return substr($sPath, strlen($sRoot));
                }
            }
        }
        return $xResult;
    }

    static public function LocalPathUrl($sPath, $xRoot, $bSaveParams = false) {

        if (!$bSaveParams) {
            if ($iPos = strpos($sPath, '?')) {
                $sPath = substr($sPath, 0, $iPos);
            } elseif (($iPos = strpos($sPath, '#'))) {
                $sPath = substr($sPath, 0, $iPos);
            }
        }
        return static::LocalPath($sPath, $xRoot);
    }

    /**
     * Из абсолютного пути выделяет локальный относительно корневой папки проекта
     *
     * @param string $sPath
     *
     * @return string|bool
     */
    static public function LocalDir($sPath) {

        return static::LocalPath($sPath, static::RootDir());
    }

    /**
     * Из абсолютного URL выделяет локальный относительно корневого URL проекта
     *
     * @param string $sPath
     * @param bool   $bCheckAliases
     * @param bool   $bSaveParams
     *
     * @return string|bool
     */
    static public function LocalUrl($sPath, $bCheckAliases = true, $bSaveParams = false) {

        if (!$sPath) {
            return null;
        } elseif (strlen($sPath) > 1 && $sPath[0] == '/' && $sPath[1] != '/') {
            return $sPath;
        } elseif (strpos($sPath, ':') === false && strpos($sPath, '//') === false) {
            return $sPath;
        }
        if ($bCheckAliases) {
            $sResult = static::LocalPathUrl($sPath, static::RootUrlAliases(), $bSaveParams);
        } else {
            $sResult = static::LocalPathUrl($sPath, static::RootUrl(), $bSaveParams);
        }

        return $sResult;
    }

    /**
     * Является ли путь локальным
     *
     * @param string $sPath
     *
     * @return bool
     */
    static public function IsLocalDir($sPath) {

        return (bool)static::LocalDir($sPath);
    }

    /**
     * Является ли URL локальным
     *
     * @param $sPath
     *
     * @return bool
     */
    static public function IsLocalUrl($sPath) {

        return (bool)static::LocalUrl($sPath);
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
                $xResult = static::NormPath($sFile);
            }
        } elseif (!is_array($aDirs)) {
            return static::Exists((string)$aDirs . '/' . $sFile);
        } else {
            foreach ($aDirs as $sDir) {
                $sResult = static::Exists($sFile, (string)$sDir);
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
     *
     * @param string $sSource
     * @param string $sTarget
     * @param bool   $bRewrite
     *
     * @return bool
     */
    static public function Copy($sSource, $sTarget, $bRewrite = false) {

        if (static::Exists($sTarget) && !$bRewrite) {
            return false;
        }
        if (static::Exists($sSource) && static::CheckDir(dirname($sTarget))) {
            $bResult = @copy($sSource, $sTarget);
            if (!$bResult) {
                F::SysWarning('Can not copy file from "' . $sSource . '" to "' . $sTarget . '"');
            }
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

        if (static::Exists($sFile) || $bNoCheck) {
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
    static public function DeleteAs($sPattern, $bRecursively = false, $bNoCheck = false) {

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
            static::Delete($sSource);
            return $sTarget;
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

        if (static::Exists($sFile)) {
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
     * @param bool   $bLogWarning
     *
     * @return  bool|int
     */
    static public function PutContents($sFile, $sData, $nFlags = 0, $bLogWarning = false) {

        $bResult = false;
        if ($sFile && static::CheckDir(dirname($sFile))) {
            $sData = (string)$sData;
            $iResult = file_put_contents($sFile, $sData, $nFlags);
            $bResult = ($iResult !== false && $iResult == strlen($sData));
        }
        if ($bResult === false && $bLogWarning) {
            F::SysWarning('Cannot write to file "' . $sFile . '"');
        }

        return $bResult;
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
     * @param string     $sPath
     * @param string|int $xOptions
     *
     * @return array
     */
    static public function PathInfo($sPath, $xOptions = null) {

        if (is_numeric($xOptions)) {
            return pathinfo($sPath, $xOptions);
        }
        $aResult = array_merge(
            array(
                 'dirname'   => '',
                 'basename'  => '',
                 'filename'  => '',
                 'extension' => '',
                 'params'    => '',
            ),
            pathinfo(static::NormPath($sPath))
        );
        $n = strpos($aResult['basename'], '?');
        if ($n !== false) {
            $aResult['params'] = substr($aResult['basename'], $n + 1);
            $aResult['basename'] = substr($aResult['basename'], 0, $n);
            $aResult['filename'] = pathinfo($aResult['basename'], PATHINFO_FILENAME);
            $aResult['extension'] = pathinfo($aResult['basename'], PATHINFO_EXTENSION);
        }
        if (substr($sPath, 0, 2) == '//' && preg_match('~^//[a-z0-9\-]+\.[a-z0-9][a-z0-9\-\.]*[a-z0-9]~', $sPath)) {
            // Возможно, это URL с протоколом по умолчанию
            if (isset($_SERVER['SERVER_PROTOCOL'])) {
                $sProtocol = strtolower(strstr($_SERVER['SERVER_PROTOCOL'], '/', true));
            } else {
                $sProtocol = 'http';
            }
            $aUrlInfo = parse_url($sProtocol . ':' . $sPath);
        } else {
            $aUrlInfo = parse_url($sPath);
        }
        if (isset($aUrlInfo['host'])) {
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
        if ($xOptions) {
            return isset($aResult[$xOptions]) ? $aResult[$xOptions] : '';
        }
        return $aResult;
    }

    /**
     * Возвращает расширение файла из переданного полного пути
     *
     * @param string $sPath
     * @param bool   $bToLower
     *
     * @return string
     */
    static public function GetExtension($sPath, $bToLower = false) {

        $aInfo = static::PathInfo($sPath);
        return $bToLower ? strtolower($aInfo['extension']) : $aInfo['extension'];
    }

    /**
     * Замена расширения файла
     *
     * @param $sPath
     * @param $sExtension
     *
     * @return string
     */
    static public function SetExtension($sPath, $sExtension) {

        $aInfo = static::PathInfo($sPath);
        return $aInfo['dirname'] . '/' . $aInfo['filename'] . '.' . $sExtension;
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
        $sNeedle = static::NormPath($sNeedle, '/');
        $aCheckPaths = static::NormPath($aPaths, '/');
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

        if (static::IsLocalDir($sFile)) {
            return static::NormPath($sFile);
        }
        /**
         * TODO: _calledFilePath()
         */
        //return static::NormPath(static::_calledFilePath() . $sFile);
        return $sFile;
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
            if (F::IsDebug()) {
                $sBom = file_get_contents($sFile, true, null, 0, 5);
                if (!$sBom) {
                    F::SysWarning('Error in including file "' . $sFile . '" - file is empty');
                } elseif ($sBom != '<?php') {
                    F::SysWarning('Error in including file "' . $sFile . '" - BOM or other wrong symbols detected');
                }
            }
            if ($bOnce) {
                self::$_temp = include_once($sFile);
            } else {
                self::$_temp = include($sFile);
            }
            $nTime = microtime(true) - self::$_time;
            self::$nIncludedTime += $nTime;
            self::$nIncludedCount++;
            if (F::IsDebug()) {
                self::$aIncludedFiles[] = $sFile
                    . '; result: ' . (is_scalar(self::$_temp) ? self::$_temp : gettype(self::$_temp))
                    . '; time: ' . $nTime;
            }
        } catch (ErrorException $oException) {
            if ($oException->getFile() !== __FILE__) {
                // Ошибка в подключённом файле
                //throw $oException;
                F::SysWarning('Error in including file "' . $sFile . '" - ' . $oException->getMessage());
                return false;
            } else {
                // Файл не был подключён.
                F::SysWarning('Can not include file "' . $sFile . '"');
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

        return static::IncludeFile(Config::Get('path.dir.libs') . '/' . $sFile, $bOnce);
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
        if (static::Exists($sFile)) {
            $xResult = static::IncludeFile($sFile, $bOnce, $bConfig);
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

        if (!is_uploaded_file($sUploadedFile)) {
            return false;
        }
        if (!$sFileName) {
            $sFileName = basename($sUploadedFile);
        } elseif (substr($sFileName, -1) == '/') {
            $sFileName .= basename($sUploadedFile);
        }
        $sDir = dirname($sFileName);
        if (!$sDir || !static::IsLocalDir($sDir)) {
            $sTargetFile = static::GetUploadDir() . $sFileName;
        } else {
            $sTargetFile = $sFileName;
        }
        if (static::CheckDir(dirname($sTargetFile)) && move_uploaded_file($sUploadedFile, $sTargetFile)) {
            return $sTargetFile;
        }
        return false;
    }

    /**
     * Папка для загрузки временных файлов
     *
     * @return string
     */
    static public function GetUploadDir() {

        return static::NormPath(Config::Get('sys.cache.dir') . '/uploads/');
    }

    /**
     * Возвращает путь к общей asset-папке
     *
     * @return string
     */
    static public function GetAssetDir() {

        return static::NormPath(Config::Get('path.runtime.dir') . 'assets/');
    }

    /**
     * Возвращает URL к общей asset-папке
     *
     * @return string
     */
    static public function GetAssetUrl() {

        return Config::Get('path.runtime.url') . 'assets/';
    }

    /**
     * Возвращает уникальное имя файла для конкретной папки
     *
     * @param     $sDir
     * @param     $sExtension
     * @param int $nLength
     *
     * @return string
     */
    static public function Uniqname($sDir, $sExtension, $nLength = 8) {

        $sFileName = dechex(time()) . '-' . F::RandomStr($nLength) . ($sExtension ? ('.' . trim($sExtension, '.')) : '');
        while(static::Exists($sDir . '/' . $sFileName)) {
            $sFileName = static::Uniqname($sDir, $sExtension, $nLength);
        }
        return static::NormPath($sDir . '/' . $sFileName);
    }

    /**
     * Возвращает уникальное имя файла для папки загрузки файлов
     *
     * @param     $sExtension
     * @param int $nLength
     *
     * @return string
     */
    static public function UploadUniqname($sExtension, $nLength = 8) {

        return static::Uniqname(static::GetUploadDir(), $sExtension, $nLength);
    }

    /**
     * Signatures to define MIME types
     *
     * @var array
     */
    static protected $aMimeTypeSignatures
        = array(
            'image/gif'  => array(
                array('offset' => 0, 'signature' => 'GIF87a'),
                array('offset' => 0, 'signature' => 'GIF89a'),
            ),
            'image/jpeg' => array(
                array('offset' => 0, 'signature' => "\xFF\xD8\xFF"),
            ),
            'image/png'  => array(
                array('offset' => 0, 'signature' => "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"),
            ),
            'image/tiff' => array(
                array('offset' => 0, 'signature' => "\x49\x20\x49"),
                array('offset' => 0, 'signature' => "\x49\x49\x2A\x00"),
                array('offset' => 0, 'signature' => "\x4D\x4D\x00\x2A"),
                array('offset' => 0, 'signature' => "\x4D\x4D\x00\x2B"),
            ),
            'image/ico' => array(
                array('offset' => 0, 'signature' => "\x00\x00\x01\x00"),
            ),
        );
    /**
     * Helper variable
     *
     * @var int
     */
    static protected $nMimeTypeSignaturesMax = 0;
    static protected $aMimeFiles = array();

    static public function MimeTypeBySignature($sFile) {

        $sMimeType = '';
        // Defines max signature length
        if (!self::$nMimeTypeSignaturesMax) {
            foreach (self::$aMimeTypeSignatures as $sMimeType => $aSignsCollect) {
                if (isset($aSignsCollect['offset']) || isset($aSignsCollect['offset'])) {
                    $aSignsCollect = array($aSignsCollect);
                }
                foreach ($aSignsCollect as $nIdx => $aSign) {
                    if (is_string($aSign)) {
                        $nOffset = 0;
                        $sSignature = $aSign;
                    } else {
                        $nOffset = isset($aSign['offset']) ? intval($aSign['offset']) : 0;
                        $sSignature = isset($aSign['signature']) ? $aSign['signature'] : '';
                    }
                    $nLen = $nOffset + strlen($sSignature);
                    if ($nLen > self::$nMimeTypeSignaturesMax) {
                        self::$nMimeTypeSignaturesMax = $nLen;
                    }
                    self::$aMimeTypeSignatures[$sMimeType][$nIdx] = array(
                        'offset'    => $nOffset,
                        'signature' => $sSignature,
                    );
                }
            }
        }
        // Reads part of file and compares with signatures
        if ($hFile = fopen($sFile, 'r')) {
            $sBuffer = fgets($hFile, self::$nMimeTypeSignaturesMax);
            fclose($hFile);
            if ($sBuffer) {
                foreach (self::$aMimeTypeSignatures as $sMimeType => $aSignsCollect) {
                    foreach ($aSignsCollect as $aSign) {
                        if (substr($sBuffer, $aSign['offset'], strlen($aSign['signature'])) == $aSign['signature']) {
                            return $sMimeType;
                        }
                    }
                }
            }
        }
        return $sMimeType;
    }
    /**
     * Defines of MimeType of the file
     *
     * @param string $sFile
     *
     * @return string
     */
    static public function MimeType($sFile) {

        if (isset(self::$aMimeFiles[$sFile])) {
            return self::$aMimeFiles[$sFile];
        }

        if (function_exists('finfo_fopen')) {
            $hFinfo = finfo_open(FILEINFO_MIME_TYPE);
        } else {
            $hFinfo = null;
        }
        if ($hFinfo) {
            $sMimeType = finfo_file($hFinfo, $sFile);
            finfo_close($hFinfo);
        } else {
            $sMimeType = self::MimeTypeBySignature($sFile);
            if (!$sMimeType && function_exists('mime_content_type')) {
                $sMimeType = mime_content_type($sFile);
            }
        }
        if ($sMimeType) {
            if ($n = strpos($sMimeType, ';')) {
                $sMimeType = substr($sMimeType, 0, $n);
            }
            self::$aMimeFiles[$sFile] = $sMimeType;
        }

        return self::$aMimeFiles[$sFile];
    }

    /**
     * @param string $sSize
     *
     * @return array()
     */
    static public function ImgModAttr($sSize) {

        $aResult = array(
            'width'     => null, // int - width of image
            'height'    => null, // int - height of image
            'mod'       => null, // str - modificator ['fit', 'crop', 'pad', 'max']
            'attr'      => '',   // str - attributes of HTML tag <img ...>: ' width=... height=...'
            'style'     => '',   // str - value for attributes style of HTML tag <img ...>
            'ratio'     => null, // float - width / height
        );
        if ($sSize) {
            $iPos = strpos($sSize, 'x');
            if ($iPos === false) {
                $iHeight = $iWidth = intval($sSize);
            } elseif ($iPos === 0) {
                $iWidth = 0;
                $iHeight = intval(substr($sSize, 1));
            } else {
                $iWidth = intval(substr($sSize, 0, $iPos));
                $iHeight = intval(substr($sSize, $iPos+1));
            }

            if ($iWidth || $iHeight) {
                if ($iWidth) {
                    $aResult['width'] = $iWidth;
                    $aResult['attr'] .= ' ' . 'width="' . $iWidth . '"';
                }
                if ($iHeight) {
                    $aResult['height'] = $iHeight;
                    $aResult['attr'] .= ' ' . 'height="' . $iHeight . '"';
                }
                if (!empty($aResult['attr'])) {
                    $aResult['attr'] .= ' ';
                }
                // check modificator
                $iMaxWidth = $iMinWidth = $iMaxHeight = $iMinHeight = 0;
                if (strpos($sSize, 'fit')) {
                    $aResult['mod'] = 'fit';
                    $iMaxWidth = $aResult['width'];
                    $iMaxHeight = $aResult['height'];
                } else if (strpos($sSize, 'crop')) {
                    $aResult['mod'] = 'crop';
                } else if (strpos($sSize, 'pad')) {
                    $aResult['mod'] = 'pad';
                    $iMinWidth = $aResult['width'];
                    $iMinHeight = $aResult['height'];
                } else if (strpos($sSize, 'max')) {
                    $aResult['mod'] = 'max';
                }

                if ($iMaxWidth) {
                    $aResult['style'] .= 'max-width:' . $iMaxWidth . 'px;';
                }
                if ($iMaxHeight) {
                    $aResult['style'] .= 'max-height:' . $iMaxHeight . 'px;';
                }
                if ($iMinWidth) {
                    $aResult['style'] .= 'min-width:' . $iMinWidth . 'px;';
                }
                if ($iMinHeight) {
                    $aResult['style'] .= 'min-height:' . $iMinHeight . 'px;';
                }

                if (!$iMaxWidth && !$iMinWidth && $iWidth) {
                    $aResult['style'] .= 'width:' . $iWidth . 'px;';
                }
                if (!$iMaxHeight && !$iMinHeight && $iWidth) {
                    $aResult['style'] .= 'height:' . $iHeight . 'px;';
                }
            }
            if ($iWidth && $iHeight) {
                $aResult['ratio'] = $iWidth / $iHeight;
            }
        }
        return $aResult;
    }

    /**
     * @param string      $sSize
     * @param string|null $sExtension
     *
     * @return string
     */
    static public function ImgModSuffix($sSize, $sExtension = null) {

        $sResult = '';
        if ($sSize) {
            $aAttr = static::ImgModAttr($sSize);
            if ($aAttr['width'] || $aAttr['height']) {
                $sResult .= '-' . ($aAttr['width'] ? $aAttr['width'] : '') . 'x' . $aAttr['height'];
                if ($aAttr['mod']) {
                    $sResult .= '-' . $aAttr['mod'];
                }
                if ($sExtension) {
                    $sResult .= '.' . strtolower($sExtension);
                }
            }
        }
        return $sResult;
    }

}

// EOF
