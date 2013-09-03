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
    protected $aModConfig = array();


    public function Init() {

        $this->aModConfig = Config::Get('module.upload');
        $this->aModConfig['file_extensions'] = array_merge($this->aModConfig['file_extensions'], (array)Config::Get('module.topic.upload_mime_types'));
        $nLimit = F::MemSize2Int(Config::Get('module.topic.max_filesize_limit'));
        if ($nLimit && $nLimit < $this->aModConfig['max_filesize']) {
            $this->aModConfig['max_filesize'] = $nLimit;
        }
        $this->aModConfig['img_max_width'] = Config::Get('view.img_max_width');
        $this->aModConfig['img_max_height'] = Config::Get('view.img_max_height');
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

    protected function _checkUploadedImage($sFile) {

        $aInfo = @getimagesize($sFile);
        if (!$aInfo) {
            return false;
        }
        if ($this->aModConfig['img_max_width'] && $this->aModConfig['img_max_width'] < $aInfo[0]) {
                return false;
            }
            if ($this->aModConfig['img_max_height'] && $this->aModConfig['img_max_height'] < $aInfo[1]) {
                return false;
            }
    }

    protected function _checkUploadedFile($sFile) {

        $sExtension = strtolower(pathinfo($sFile, PATHINFO_EXTENSION));
        // Check allow extensions
        if ($this->aModConfig['file_extensions']
            && !in_array($sExtension, $this->aModConfig['file_extensions'])) {
            return false;
        }
        // Check filesize
        if ($this->aModConfig['max_filesize'] && filesize($sFile) > $this->aModConfig['max_filesize']) {
            return false;
        }
        // Check images
        if (in_array($sExtension, array('gif', 'png', 'jpg', 'jpeg'))) {
            if (!$this->_checkUploadedImage($sFile)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Upload file from client via HTTP POST
     *
     * @param string $aFile
     * @param string $sDir
     * @param bool   $bOriginalName
     *
     * @return bool|string
     */
    public function UploadLocal($aFile, $sDir = null, $bOriginalName = false) {

        if (is_array($aFile) && isset($aFile['tmp_name']) && isset($aFile['name'])) {
            if ($aFile['error'] === UPLOAD_ERR_OK) {
                if (is_uploaded_file($aFile['tmp_name'])) {
                    if ($bOriginalName) {
                        $sTmpFile = $aFile['name'];
                    } else {
                        $sTmpFile = F::RandomStr() . '.' . pathinfo($aFile['name'], PATHINFO_EXTENSION);
                    }
                    if ($sTmpFile = F::File_MoveUploadedFile($aFile['tmp_name'], $sTmpFile)) {
                        if ($this->_checkUploadedFile($sTmpFile)) {
                            if ($sDir) {
                                $sTmpFile = $this->MoveTmpFile($sTmpFile, $sDir);
                            }
                            return $sTmpFile;
                        } else {
                            // TODO: Определить ошибку
                        }
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
        if ($this->_checkUploadedFile($sTmpFile)) {
            return $this->MoveTmpFile($sTmpFile, $sDir);
        } else {
            // TODO: Определить ошибку
        }
        return false;
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
     * @param $sDestination
     * @param $bRewrite
     *
     * @return mixed
     */
    public function Move($sFilePath, $sDestination, $bRewrite = true) {

        return F::File_Move($sFilePath, $sDestination, $bRewrite);
    }

    /**
     * @param $sFilePath
     * @param $sDestination
     *
     * @return mixed
     */
    public function Copy($sFilePath, $sDestination) {

        return F::File_Copy($sFilePath, $sDestination);
    }

    /**
     * @param $sFilePath
     *
     * @return mixed
     */
    public function Delete($sFilePath) {

        return F::File_Delete($sFilePath);
    }

    public function DeleteAs($sFilePattern) {

        return F::File_DeleteAs($sFilePattern);
    }

    /**
     * @param $sFilePath
     *
     * @return mixed
     */
    public function Dir2Url($sFilePath) {

        return F::File_Dir2Url($sFilePath);
    }

    /**
     * @param $sUrl
     *
     * @return mixed
     */
    public function Url2Dir($sUrl) {

        return F::File_Url2Dir($sUrl);
    }

    /**
     * Path to user's upload dir
     *
     * @param int  $nUserId
     * @param bool $bAutoMake
     *
     * @return string
     */
    public function GetUserUploadDir($nUserId, $bAutoMake = true) {

        $nMaxLen = 6;
        $nSplitLen = 2;
        $sPath = join('/', str_split(str_pad($nUserId, $nMaxLen, '0', STR_PAD_LEFT), $nSplitLen));
        $sResult = F::File_NormPath(F::File_RootDir() . Config::Get('path.uploads.images') . $sPath . '/');
        if ($bAutoMake) {
            F::File_CheckDir($sResult, $bAutoMake);
        }
        return $sResult;
    }

    /**
     * Path to user's dir for avatars
     *
     * @param int  $nUserId
     * @param bool $bAutoMake
     *
     * @return string
     */
    public function GetUserAvatarDir($nUserId, $bAutoMake = true) {

        $sResult = $this->GetUserUploadDir($nUserId) . 'avatar/';
        if ($bAutoMake) {
            F::File_CheckDir($sResult, $bAutoMake);
        }
        return $sResult;
    }

    /**
     * Path to user's dir for uploaded images
     *
     * @param int  $nUserId
     * @param bool $bAutoMake
     *
     * @return string
     */
    public function GetUserImageDir($nUserId, $bAutoMake = true) {

        $sResult = $this->GetUserUploadDir($nUserId) . date('Y/m/d/');
        if ($bAutoMake) {
            F::File_CheckDir($sResult, $bAutoMake);
        }
        return $sResult;
    }

    public function Uniqname($sDir, $sExtension, $nLength = 6) {

        return F::File_Uniqname($sDir, $sExtension, $nLength);
    }

}

// EOF