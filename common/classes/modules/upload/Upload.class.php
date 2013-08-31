<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

class ModuleUpload extends Module {

    const ERR_NOT_POST_UPLOADED     = 10001;
    const ERR_NOT_FILE_VARIABLE     = 10002;
    const ERR_MAKE_UPLOAD_DIR       = 10003;
    const ERR_REMOTE_FILE_OPEN      = 10004;
    const ERR_REMOTE_FILE_MAXSIZE   = 10005;
    const ERR_REMOTE_FILE_READ      = 10006;
    const ERR_REMOTE_FILE_WRITE     = 10007;

    protected $aUploadErrors
        = array(
            UPLOAD_ERR_OK                 => 'Ok',
            UPLOAD_ERR_INI_SIZE           => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE          => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL            => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE            => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR         => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE         => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION          => 'A PHP extension stopped the file upload',
            self::ERR_NOT_POST_UPLOADED   => 'File did not upload via POST method',
            self::ERR_NOT_FILE_VARIABLE   => 'Argument is not $_FILE[] variable',
            self::ERR_REMOTE_FILE_OPEN    => 'Cannot open remote file',
            self::ERR_REMOTE_FILE_MAXSIZE => 'Remote file is too large',
            self::ERR_REMOTE_FILE_READ    => 'Cannot read remote file',
            self::ERR_REMOTE_FILE_WRITE   => 'Cannot write remote file',
        );

    protected $nLastError = 0;
    protected $sLastError = '';

    public function Init() {

    }

    protected function _resetError() {

        $this->nLastError = 0;
        $this->sLastError = '';
    }

    /**
     * Move temporary file to destination
     *
     * @param $sTmpFile
     * @param $sTargetFile
     *
     * @return bool
     */
    protected function MoveTmpFile($sTmpFile, $sTargetFile) {

        if (F::File_Move($sTmpFile, $sTargetFile)) {
            return $sTargetFile;
        }
        return false;
    }

    public function GetError() {

        return $this->nLastError;
    }

    public function GetErrorMsg() {

        return $this->sLastError;
    }

    /**
     * Upload file from client via HTTP POST
     *
     * @param string      $aFile
     * @param string|null $sDir
     *
     * @return bool|string
     */
    public function UploadLocal($aFile, $sDir = null) {

        if (is_array($aFile) && isset($aFile['tmp_name'])) {
            if ($aFile['error'] === UPLOAD_ERR_OK) {
                if (is_uploaded_file($aFile['tmp_name'])) {
                    $sTmpFile = Config::Get('sys.cache.dir') . F::RandomStr();
                    if ($sTmpFile = F::File_MoveUploadedFile($aFile['tmp_name'], $sTmpFile)) {
                        if ($sDir) {
                            $sTmpFile = $this->MoveTmpFile($sTmpFile, $sDir);
                        }
                        return $sTmpFile;
                    }
                } else {
                    // Файл не был загружен при помощи HTTP POST
                    $this->nLastError = self::ERR_NOT_POST_UPLOADED;
                }
            } else {
                // Ошибка загузки файла
                $this->nLastError = $aFile['error'];
            }
        } else {
            $this->nLastError = self::ERR_NOT_FILE_VARIABLE;
        }
        if (isset($this->aUploadErrors[$this->nLastError])) {
            $this->sLastError = $this->aUploadErrors[$this->nLastError];
        } else {
            $this->sLastError = 'Unknown error during file uploading';
        }
        return false;
    }

    /**
     * Upload remote file by URL
     *
     * @param       $sUrl
     * @param null  $sDir
     * @param array $aParams
     *
     * @return bool
     */
    public function UploadRemote($sUrl, $sDir = null, $aParams = array()) {

        if (!isset($aParams['max_size'])) {
            $aParams['max_size'] = 0;
        } else {
            $aParams['max_size'] = intval($aParams['max_size']);
        }
        $sContent = '';
        if ($aParams['max_size']) {
            $hFile = fopen($sUrl, 'r');
            if (!$hFile) {
                $this->nLastError = self::ERR_REMOTE_FILE_OPEN;
                return false;
            }

            $nSizeKb = 0;
            while (!feof($hFile) && $nSizeKb <= $aParams['max_size']) {
                $sPiece = fread($hFile, 1024);
                if ($sPiece) {
                    $nSizeKb += strlen($sPiece);
                    $sContent .= $sPiece;
                } else {
                    break;
                }
            }
            fclose($hFile);

            // * Если конец файла не достигнут, значит файл имеет недопустимый размер
            if ($nSizeKb > $aParams['max_size']) {
                $this->nLastError = self::ERR_REMOTE_FILE_MAXSIZE;
                return false;
            }
        } else {
            $sContent = file_get_contents($sUrl);
            if ($sContent === false) {
                $this->nLastError = self::ERR_REMOTE_FILE_READ;
                return false;
            }
        }
        if ($sContent) {
            $sTmpFile = Config::Get('sys.cache.dir') . F::RandomStr();
            if (!file_put_contents($sTmpFile, $sContent)) {
                $this->nLastError = self::ERR_REMOTE_FILE_READ;
                return false;
            }
        }
        return $this->MoveTmpFile($sTmpFile, $sDir);
    }

    /**
     * @param $sFilePath
     *
     * @return mixed
     */
    public function Exists($sFilePath) {

        return F::File_Exists($sFilePath);
    }

    /**
     * @param $sFilePath
     *
     * @return mixed
     */
    public function Delete($sFilePath) {

        return F::File_Delete($sFilePath);
    }

    /**
     * @param $sFilePath
     *
     * @return mixed
     */
    public function Dir2Url($sFilePath) {

        return F::File_Dir2($sFilePath);
    }

    /**
     * @param $sUrl
     *
     * @return mixed
     */
    public function Url2Dir($sUrl) {

        return F::File_Url2Dir($sUrl);
    }

}

// EOF