<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

class ModuleUploader extends Module {

    const ERR_NOT_POST_UPLOADED     = 10001;
    const ERR_NOT_FILE_VARIABLE     = 10002;
    const ERR_MAKE_UPLOAD_DIR       = 10003;
    const ERR_MOVE_UPLOAD_FILE      = 10004;
    const ERR_COPY_UPLOAD_FILE      = 10005;
    const ERR_REMOTE_FILE_OPEN      = 10011;
    const ERR_REMOTE_FILE_MAXSIZE   = 10012;
    const ERR_REMOTE_FILE_READ      = 10013;
    const ERR_REMOTE_FILE_WRITE     = 10014;
    const ERR_NOT_ALLOWED_EXTENSION = 10051;
    const ERR_FILE_TOO_LARGE        = 10052;
    const ERR_IMG_NO_INFO           = 10061;
    const ERR_IMG_LARGE_WIDTH       = 10062;
    const ERR_IMG_LARGE_HEIGHT      = 10063;

    protected $aUploadErrors
        = array(
            UPLOAD_ERR_OK                   => 'Ok',
            UPLOAD_ERR_INI_SIZE             => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE            => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL              => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE              => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR           => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE           => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION            => 'A PHP extension stopped the file upload',
            self::ERR_NOT_POST_UPLOADED     => 'File did not upload via POST method',
            self::ERR_NOT_FILE_VARIABLE     => 'Argument is not $_FILE[] variable',
            self::ERR_MAKE_UPLOAD_DIR       => 'Cannot make upload dir',
            self::ERR_MOVE_UPLOAD_FILE      => 'Cannot move uploaded file',
            self::ERR_COPY_UPLOAD_FILE      => 'Cannot copy uploaded file',
            self::ERR_REMOTE_FILE_OPEN      => 'Cannot open remote file',
            self::ERR_REMOTE_FILE_MAXSIZE   => 'Remote file is too large',
            self::ERR_REMOTE_FILE_READ      => 'Cannot read remote file',
            self::ERR_REMOTE_FILE_WRITE     => 'Cannot write remote file in tmp dir',
            self::ERR_NOT_ALLOWED_EXTENSION => 'Not allowed file extension',
            self::ERR_FILE_TOO_LARGE        => 'File is too large',
            self::ERR_IMG_NO_INFO           => 'Cannot get info about image (may be file is corrupted)',
            self::ERR_IMG_LARGE_WIDTH       => 'Width of image is too large',
            self::ERR_IMG_LARGE_HEIGHT      => 'Height of image is too large',
        );

    protected $nLastError = 0;
    protected $sLastError = '';
    protected $aModConfig = array();
    protected $sDefaultDriver = 'file';
    protected $aRegisteredDrivers = array();
    protected $aLoadedDrivers = array();

    /**
     * Init module
     */
    public function Init() {

        $this->aModConfig = Config::GetData('module.uploader');
        /*
        $this->aModConfig['file_extensions'] = array_merge(
            $this->aModConfig['file_extensions'], (array)Config::Get('module.topic.upload_mime_types')
        );

        $nLimit = F::MemSize2Int(Config::Get('module.topic.max_filesize_limit'));
        if ($nLimit && $nLimit < $this->aModConfig['max_filesize']) {
            $this->aModConfig['max_filesize'] = $nLimit;
        } else {
            $this->aModConfig['max_filesize'] = F::MemSize2Int($this->aModConfig['max_filesize']);
        }

        $this->aModConfig['img_max_width'] = Config::Get('view.img_max_width');
        $this->aModConfig['img_max_height'] = Config::Get('view.img_max_height');
        */
        $this->RegisterDriver('file');
    }

    protected function _resetError() {

        $this->nLastError = 0;
        $this->sLastError = '';
    }

    /**
     * @param string $sDriverName
     * @param string $sClass
     */
    public function RegisterDriver($sDriverName, $sClass = null) {

        if (!$sClass) {
            $sClass = 'Uploader_Driver' . ucfirst($sDriverName);
        }
        $this->aRegisteredDrivers[$sDriverName] = $sClass;
    }

    public function LoadDriver($sDriverName) {

        $sClass = $this->aRegisteredDrivers[$sDriverName];
        return E::GetEntity($sClass);
    }

    /**
     * @return array
     */
    public function GetRegisteredDrivers() {

        return array_keys($this->aRegisteredDrivers);
    }

    /**
     * @param string $sDriverName
     */
    public function SetDefaultDriver($sDriverName) {

        $this->sDefaultDriver = $sDriverName;
    }

    /**
     * @return string
     */
    public function GetDefaultDriver() {

        return $this->sDefaultDriver;
    }

    /**
     * @param $sDriverName
     *
     * @return object|null
     */
    public function GetDriver($sDriverName) {

        if (isset($this->aRegisteredDrivers[$sDriverName])) {
            if (!isset($this->aLoadedDrivers[$sDriverName])) {
                $oDriver = $this->LoadDriver($sDriverName);
                $this->aLoadedDrivers[$sDriverName] = $oDriver;
            }
            return $this->aLoadedDrivers[$sDriverName];
        }
        return null;
    }

    /**
     * Move temporary file to destination
     *
     * @param string $sTmpFile
     * @param string $sTargetFile
     *
     * @return bool
     */
    protected function MoveTmpFile($sTmpFile, $sTargetFile) {

        if (F::File_Move($sTmpFile, $sTargetFile)) {
            return $sTargetFile;
        }
        $this->nLastError = self::ERR_MOVE_UPLOAD_FILE;
        return false;
    }

    /**
     * Return error code
     *
     * @return int
     */
    public function GetError() {

        return $this->nLastError;
    }

    /**
     * Return error messge
     *
     * @param bool $bReset
     *
     * @return string
     */
    public function GetErrorMsg($bReset = true) {

        if ($this->nLastError) {
            if (isset($this->aUploadErrors[$this->nLastError])) {
                $this->sLastError = $this->aUploadErrors[$this->nLastError];
            } else {
                $this->sLastError = 'Unknown error during file uploading';
            }
            $sError = $this->sLastError;
            if ($bReset) {
                $this->nLastError = 0;
            }
            return $sError;
        }
        return null;
    }

    /**
     * @param string $sFile
     * @param string $sConfigKey
     *
     * @return bool
     */
    protected function _checkUploadedImage($sFile, $sConfigKey = '') {

        $aInfo = @getimagesize($sFile);
        if (!$aInfo) {
            $this->nLastError = self::ERR_IMG_NO_INFO;
            return false;
        }
        // Gets local config
        if (!$sConfigKey) {
            $aConfig = $this->aModConfig['images.default'];
        } else {
            $aConfig = $this->aModConfig[$sConfigKey];
        }
        if ($aConfig['max_width'] && F::MemSize2Int($aConfig['max_width']) < $aInfo[0]) {
            $this->nLastError = self::ERR_IMG_LARGE_WIDTH;
            return false;
        }
        if ($aConfig['max_height'] && F::MemSize2Int($aConfig['max_height']) < $aInfo[1]) {
            $this->nLastError = self::ERR_IMG_LARGE_HEIGHT;
            return false;
        }
        return true;
    }

    /**
     * @param string $sFile
     * @param string $sConfigKey
     *
     * @return bool
     */
    protected function _checkUploadedFile($sFile, $sConfigKey = '') {

        $sExtension = strtolower(pathinfo($sFile, PATHINFO_EXTENSION));
        // Gets local config
        if (!$sConfigKey) {
            $aImageExtensions = $this->aModConfig['images.default.image_extensions'];
            if (is_array($aImageExtensions) && in_array($sExtension, $aImageExtensions)) {
                $aConfig = $this->aModConfig['images.default'];
            } else {
                $aConfig = $this->aModConfig['files.default'];
                $aImageExtensions = array();
            }
        } else {
            $aConfig = $this->aModConfig[$sConfigKey];
            $aImageExtensions = (array)$aConfig['image_extensions'];
        }

        // Check allow extensions
        if ($aConfig['file_extensions'] && !in_array($sExtension, $aConfig['file_extensions'])) {
            $this->nLastError = self::ERR_NOT_ALLOWED_EXTENSION;
            return false;
        }
        // Check filesize
        if ($aConfig['file_maxsize'] && filesize($sFile) > F::MemSize2Int($aConfig['file_maxsize'])) {
            $this->nLastError = self::ERR_FILE_TOO_LARGE;
            return false;
        }
        // Check images
        if (in_array($sExtension, $aImageExtensions)) {
            if (!$this->_checkUploadedImage($sFile, $sConfigKey)) {
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

        $this->nLastError = 0;
        if (is_array($aFile) && isset($aFile['tmp_name']) && isset($aFile['name'])) {
            if ($aFile['error'] === UPLOAD_ERR_OK) {
                if (is_uploaded_file($aFile['tmp_name'])) {
                    if ($bOriginalName) {
                        $sTmpFile = F::File_GetUploadDir() . $aFile['name'];
                    } else {
                        $sTmpFile = basename(F::File_UploadUniqname(pathinfo($aFile['name'], PATHINFO_EXTENSION)));
                    }
                    // Copy uploaded file in our temp folder
                    if ($sTmpFile = F::File_MoveUploadedFile($aFile['tmp_name'], $sTmpFile)) {
                        if ($this->_checkUploadedFile($sTmpFile)) {
                            if ($sDir) {
                                $sTmpFile = $this->MoveTmpFile($sTmpFile, $sDir);
                            }
                            return $sTmpFile;
                        } else {
                            F::File_Delete($sTmpFile);
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
        return false;
    }

    /**
     * Upload remote file by URL
     *
     * @param string $sUrl
     * @param string $sDir
     * @param array  $aParams
     *
     * @return bool
     */
    public function UploadRemote($sUrl, $sDir = null, $aParams = array()) {

        $this->nLastError = 0;
        if (!isset($aParams['max_size'])) {
            $aParams['max_size'] = 0;
        } else {
            $aParams['max_size'] = intval($aParams['max_size']);
        }
        $sContent = '';
        if ($aParams['max_size']) {
            $hFile = @fopen($sUrl, 'r');
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
            $sContent = @file_get_contents($sUrl);
            if ($sContent === false) {
                $this->nLastError = self::ERR_REMOTE_FILE_READ;
                return false;
            }
        }
        if ($sContent) {
            $sTmpFile = F::File_UploadUniqname(F::File_GetExtension($sUrl));
            if (!F::File_PutContents($sTmpFile, $sContent)) {
                $this->nLastError = self::ERR_REMOTE_FILE_WRITE;
                return false;
            }
        }
        if ($this->_checkUploadedFile($sTmpFile)) {
            if ($sDir) {
                return $this->MoveTmpFile($sTmpFile, $sDir);
            } else {
                return $sTmpFile;
            }
        }
        return false;
    }

    /**
     * @param string $sFilePath
     * @param string $sDestination
     * @param bool   $bRewrite
     *
     * @return string|bool
     */
    public function Move($sFilePath, $sDestination, $bRewrite = true) {

        if ($sFilePath == $sDestination) {
            $sResult = $sDestination;
        } else {
            $sResult = F::File_Move($sFilePath, $sDestination, $bRewrite);
            if (!$sResult) {
                $this->nLastError = self::ERR_MOVE_UPLOAD_FILE;
            }
        }
        return $sResult;
    }

    /**
     * @param string $sFilePath
     * @param string $sDestination
     *
     * @return string|bool
     */
    public function Copy($sFilePath, $sDestination) {

        if ($sFilePath == $sDestination) {
            $sResult = $sDestination;
        } else {
            $sResult = F::File_Copy($sFilePath, $sDestination);
            if (!$sResult) {
                $this->nLastError = self::ERR_COPY_UPLOAD_FILE;
            }
        }
        return $sResult;
    }

    /**
     * Path to user's upload dir
     *
     * @param int    $iUserId
     * @param string $sDir
     * @param bool   $bAutoMake
     *
     * @return string
     */
    protected function _getUserUploadDir($iUserId, $sDir, $bAutoMake = true) {

        $nMaxLen = 6;
        $nSplitLen = 2;
        $sPath = join('/', str_split(str_pad($iUserId, $nMaxLen, '0', STR_PAD_LEFT), $nSplitLen));
        $sResult = F::File_NormPath(F::File_RootDir() . $sDir . $sPath . '/');
        if ($bAutoMake) {
            F::File_CheckDir($sResult, $bAutoMake);
        }
        return $sResult;
    }

    /**
     * @param int  $iUserId
     * @param bool $bAutoMake
     *
     * @param bool $sType
     *
     * @return string
     */
    public function GetUserImagesUploadDir($iUserId, $bAutoMake = TRUE, $sType = FALSE) {

//        return $this->_getUserUploadDir($nUserId, Config::Get('path.uploads.images'), $bAutoMake);
        $sDir = ($sType && ($sDir = Config::Get('path.uploads.' . $sType))) ? $sDir : Config::Get('path.uploads.images');

        return $this->_getUserUploadDir($iUserId, $sDir, $bAutoMake);
    }

    /**
     * @param int  $iUserId
     * @param bool $bAutoMake
     *
     * @return string
     */
    public function GetUserFilesUploadDir($iUserId, $bAutoMake = true) {

        return $this->_getUserUploadDir($iUserId, Config::Get('path.uploads.files'), $bAutoMake);
    }

    /**
     * Path to user's dir for avatars
     *
     * @param int  $iUserId
     * @param bool $bAutoMake
     *
     * @return string
     */
    public function GetUserAvatarDir($iUserId, $bAutoMake = true) {

        $sResult = $this->GetUserImagesUploadDir($iUserId) . 'avatar/';
        if ($bAutoMake) {
            F::File_CheckDir($sResult, $bAutoMake);
        }
        return $sResult;
    }

    /**
     * Path to user's dir for uploaded images
     *
     * @param int         $iUserId
     * @param bool        $bAutoMake
     * @param string|bool $sType
     *
     * @return string
     */
    public function GetUserImageDir($iUserId = null, $bAutoMake = true, $sType = false) {

        if (is_null($iUserId)) {
            $iUserId = intval(E::UserId());
        }
        $sResult = $this->GetUserImagesUploadDir($iUserId, $bAutoMake, $sType) . date('Y/m/d/');
        if ($bAutoMake) {
            F::File_CheckDir($sResult, $bAutoMake);
        }
        return $sResult;
    }

    /**
     * @param int  $iUserId
     * @param bool $bAutoMake
     *
     * @return string
     */
    public function GetUserFileDir($iUserId, $bAutoMake = true) {

        $sResult = $this->GetUserFilesUploadDir($iUserId) . date('Y/m/d/');
        if ($bAutoMake) {
            F::File_CheckDir($sResult, $bAutoMake);
        }
        return $sResult;
    }

    /**
     * @param string $sDir
     * @param string $sExtension
     * @param int    $nLength
     *
     * @return mixed
     */
    public function Uniqname($sDir, $sExtension, $nLength = 8) {

        return F::File_Uniqname($sDir, $sExtension, $nLength);
    }

    /**
     * @param string $sFile
     *
     * @return string
     */
    public function DefineDriver(&$sFile) {

        if (substr($sFile, 0, 1) == '[' && ($n = strpos($sFile, ']'))) {
            $sDriver = substr($sFile, 1, $n - 1);
            if ($n == strlen($sFile)) {
                $sFile = '';
            } else {
                $sFile = substr($sFile, $n + 1);
            }
        } else {
            $sDriver = $this->sDefaultDriver;
        }
        return $sDriver;
    }

    /**
     * @param string $sFilePath
     *
     * @return bool|string
     */
    public function Exists($sFilePath) {

        $sDriverName = $this->DefineDriver($sFilePath);
        $oDriver = $this->GetDriver($sDriverName);
        return $oDriver->Exists($sFilePath);
    }

    /**
     * @param string $sFile
     * @param string $sDestination
     *
     * @return string|bool
     */
    public function Store($sFile, $sDestination = null) {

        if (!$sDestination) {
            $sDriverName = $this->sDefaultDriver;
        } else {
            $sDriverName = $this->DefineDriver($sDestination);
        }
        if ($sDriverName) {
            $oDriver = $this->GetDriver($sDriverName);
            $oStoredItem = $oDriver->Store($sFile, $sDestination);
            if ($oStoredItem) {
                if (!$oStoredItem->GetUuid()) {
                    $oStoredItem->SetUuid($sDriverName);
                }
                $oMresource = E::GetEntity('Mresource', $oStoredItem);
                E::ModuleMresource()->Add($oMresource);
                return $oStoredItem;
            }
        }
        return false;
    }

    /**
     * @param string $sFilePath
     *
     * @return bool
     */
    public function Delete($sFilePath) {

        $sDriverName = $this->DefineDriver($sFilePath);
        $oDriver = $this->GetDriver($sDriverName);
        return $oDriver->Delete($sFilePath);
    }

    /**
     * @param string $sFilePath
     *
     * @return bool
     */
    public function DeleteAs($sFilePath) {

        $sDriverName = $this->DefineDriver($sFilePath);
        $oDriver = $this->GetDriver($sDriverName);
        return $oDriver->Delete($sFilePath);
    }

    /**
     * @param string $sFilePath
     *
     * @return string
     */
    public function Dir2Url($sFilePath) {

        $sDriverName = $this->DefineDriver($sFilePath);
        $oDriver = $this->GetDriver($sDriverName);
        return $oDriver->Dir2Url($sFilePath);
    }

    /**
     * @param string $sUrl
     *
     * @return string|bool
     */
    public function Url2Dir($sUrl) {

        $aDrivers = $this->GetRegisteredDrivers();
        foreach ($aDrivers as $sDriver) {
            $oDriver = $this->GetDriver($sDriver);
            $sFile = $oDriver->Url2Dir($sUrl);
            if ($sFile) {
                return $sFile;
            }
        }
        return false;
    }

    /**
     * Возвращает максимальное количество картинок для типа объекта
     *
     * @param string $sTarget
     * @param bool   $sTargetId
     *
     * @return bool
     */
    public function GetAllowedCount($sTarget, $sTargetId = FALSE) {

        if ($sTarget == 'topic-multi-image-uploader') {
            $aPhotoSetData = E::ModuleMresource()->GetPhotosetData($sTarget, (int)$sTargetId);
            return $aPhotoSetData['count'] < Config::Get('module.topic.photoset.count_photos_max');
        }

        return FALSE;
    }

    /**
     * Проверяет доступность того или иного целевого объекта, переопределяется
     * плагинами. По умолчанию всё грузить запрещено.
     * Если всё нормально и пользователю разрешено сюда загружать картинки,
     * то метод возвращает целевой объект, иначе значение FALSE.
     *
     * @param string $sTarget
     * @param int|bool $sTargetId
     *
     * @return bool
     */
    public function CheckAccessAndGetTarget($sTarget, $sTargetId = FALSE) {

        // Проверяем право пользователя на прикрепление картинок к топику
        if (mb_strpos($sTarget, 'single-image-uploader') === 0 || $sTarget == 'topic-multi-image-uploader') {

            // Проверям, авторизован ли пользователь
            if (!E::IsUser()) {
                return FALSE;
            }

            // Топик редактируется
            if ($oTopic = E::ModuleTopic()->GetTopicById($sTargetId)) {
                if (!E::ModuleACL()->IsAllowEditTopic($oTopic, E::User())) {
                    return FALSE;
                }
                return $oTopic;
            }

            return TRUE;
        }

        // Загружать аватарки можно только в свой профиль
        if ($sTarget == 'profile_avatar') {

            if ($sTargetId && E::IsUser() && $sTargetId == E::UserId()) {
                return E::User();
            }

            return FALSE;
        }

        // Загружать аватарки можно только в свой профиль
        if ($sTarget == 'profile_photo') {

            if ($sTargetId && E::IsUser() && $sTargetId == E::UserId()) {
                return E::User();
            }

            return FALSE;
        }

        if ($sTarget == 'blog_avatar') {
            /** @var ModuleBlog_EntityBlog $oBlog */
            $oBlog = E::ModuleBlog()->GetBlogById($sTargetId);

            if (!E::IsUser()) {
                return false;
            }

            if (!$oBlog) {
                // Блог еще не создан
                return (E::ModuleACL()->CanCreateBlog(E::User()) || E::IsAdmin());
            }

            if ($oBlog && (E::ModuleACL()->CheckBlogEditBlog($oBlog, E::User()) || E::IsAdmin())) {
                return $oBlog;
            }

            return '';
        }

        return FALSE;
    }

    /**
     * Получает путь к картинкам брендинга
     *
     * @param string $sTargetType Что за картинка
     * @param int    $sTargetId   Идентификатор целевого объекта
     *
     * @return string
     */
    public function GetUploadDir($sTargetType, $sTargetId) {

        if ($sTargetId == "0") {
            $sPath = '_tmp/';

        } else {
            $nMaxLen = 6;
            $nSplitLen = 2;
            $sPath = join('/', str_split(str_pad($sTargetId, $nMaxLen, '0', STR_PAD_LEFT), $nSplitLen));
        }


        $sResult = F::File_NormPath(F::File_RootDir() . Config::Get('path.uploads.root') . "/{$sTargetType}/" . $sPath);
        F::File_CheckDir($sResult, TRUE);

        return $sResult;

    }

    /**
     * Получает URL цели
     *
     * @param $sTargetType
     * @param $iTargetId
     *
     * @return string
     */
    public function GetTargetUrl($sTargetType, $iTargetId) {

        if (mb_strpos($sTargetType, 'single-image-uploader') === 0 || $sTargetType = 'topic-multi-image-uploader') {
            /** @var $oTopic ModuleTopic_EntityTopic */
            if (!$oTopic = E::ModuleTopic()->GetTopicById($iTargetId)) {
                return '';
            }

            return $oTopic->getUrl();
        }

        if ($sTargetType == 'profile_avatar') {
            return R::GetPath('settings');
        }

        if ($sTargetType == 'profile_photo') {
            return R::GetPath('settings');
        }

        if ($sTargetType == 'blog_avatar') {
            /** @var ModuleBlog_EntityBlog $oBlog */
            $oBlog = E::ModuleBlog()->GetBlogById($iTargetId);
            if ($oBlog) {
                return $oBlog->getUrlFull();
            }
            return '';
        }

        return '';
    }

    /**
     * Получает урл изображения целевого объекта
     *
     * @param string      $sTarget
     * @param int         $iTargetId
     * @param bool|string $xSize
     *
     * @return string
     */
    public function GetTargetImageUrl($sTarget, $iTargetId, $xSize=FALSE) {

        $aMResourceRel = E::ModuleMresource()->GetMresourcesRelByTarget($sTarget, $iTargetId);
        if ($aMResourceRel) {
            $oMResource = array_shift($aMResourceRel);

            $sUrl = str_replace('@', Config::Get('path.root.web'), $oMResource->getPathUrl());

            if (!$xSize) {
                return $sUrl;
            }

            return $this->ResizeTargetImage($sUrl, $xSize);
        }

        return '';

    }

    /**
     * Возвращает урл изображения по новому размеру
     *
     * @param string $sOriginalPath
     * @param string    $xSize
     *
     * @return string
     */
    public function ResizeTargetImage($sOriginalPath, $xSize) {

        $sModSuffix = F::File_ImgModSuffix($xSize, pathinfo($sOriginalPath, PATHINFO_EXTENSION));
        $sUrl = $sOriginalPath . $sModSuffix;

        if (Config::Get('module.image.autoresize')) {
            $sFile = E::ModuleUploader()->Url2Dir($sUrl);
            if (!F::File_Exists($sFile)) {
                E::ModuleImg()->Duplicate($sFile);
            }
        }

        return $sUrl;

    }


}

// EOF