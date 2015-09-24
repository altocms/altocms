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
 * @package modules.mresource
 * @since   1.0
 */
class ModuleMresource extends Module {

    const TYPE_IMAGE = 1;
    const TYPE_VIDEO = 2;
    const TYPE_AUDIO = 4;
    const TYPE_FLASH = 8;
    const TYPE_PHOTO = 16; // Элемент фотосета
    const TYPE_HREF  = 32;
    const TYPE_PHOTO_PRIMARY  = 64; // Обложка фотосета
    const TYPE_OTHERS  = 1024;      // Other types

    /** @var  ModuleMresource_MapperMresource */
    protected $oMapper;

    /**
     * Инициализация
     *
     */
    public function Init() {

        $this->oMapper = E::GetMapper(__CLASS__);
    }

    /**
     * Создание сущности медиа ресурса ссылки
     *
     * @param string $sLink
     *
     * @return ModuleMresource_EntityMresource
     */
    public function BuildMresourceLink($sLink) {

        /** @var ModuleMresource_EntityMresource $oMresource */
        $oMresource = E::GetEntity('Mresource');
        $oMresource->setUrl($this->NormalizeUrl($sLink));

        return $oMresource;
    }

    /**
     * Создание хеш-списка ресурсов, где индексом является хеш
     *
     * @param $aMresources
     *
     * @return array
     */
    public function BuildMresourceHashList($aMresources) {

        if ($this->IsHashList($aMresources)) {
            return $aMresources;
        }
        $aHashList = array();
        foreach ($aMresources as $oMresource) {
            $sHash = $oMresource->GetHashUrl();
            if (isset($aHash[$sHash])) {
                $aHashList[$sHash]->SetIncount(intval($aHashList[$sHash]->GetIncount()) + 1);
            } else {
                $aHashList[$sHash] = $oMresource;
            }
        }
        return $aHashList;
    }

    /**
     * Проверка, является ли массив хеш-списком ресурсов
     *
     * @param array $aMresources
     *
     * @return bool
     */
    public function IsHashList($aMresources) {

        if (is_array($aMresources)) {
            // first element of array
            reset($aMresources);
            $aData = each($aMresources);
            if (($aData['value'] instanceof ModuleMresource_EntityMresource) && ($aData['value']->GetHash() === $aData['key'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Нормализация URL
     *
     * @param string|array $xUrl
     * @param string       $sReplace
     * @param string       $sAdditional
     *
     * @return array|string
     */
    public function NormalizeUrl($xUrl, $sReplace = '@', $sAdditional = '') {

        if (is_array($xUrl)) {
            foreach ($xUrl as $nI => $sUrl) {
                $xUrl[$nI] = $this->NormalizeUrl((string)$sUrl, $sReplace, $sAdditional);
            }
            return $xUrl;
        }
        $sUrl = str_replace(array('http://@' . $sAdditional, 'https://@' . $sAdditional, 'ftp://@' . $sAdditional), $sReplace, $xUrl);

        return F::File_NormPath($sUrl);
    }

    /**
     * Добавление ресурса
     *
     * @param ModuleMresource_EntityMresource $oMediaResource
     *
     * @return bool
     */
    public function Add($oMediaResource) {

        if (!$oMediaResource) {
            return null;
        }
        $iNewId = 0;
        if (is_array($oMediaResource)) {
            $aResources = $oMediaResource;
            // Групповое добавление
            foreach ($aResources as $nIdx => $oResource) {
                if ($iNewId = $this->oMapper->Add($oResource)) {
                    $aResources[$nIdx] = $this->GetMresourceById($iNewId);
                }
            }
        } else {
            if ($iNewId = $this->oMapper->Add($oMediaResource)) {
                $oMediaResource = $this->GetMresourceById($iNewId);
            }
        }
        if ($iNewId) {
            //чистим зависимые кеши
            E::ModuleCache()->CleanByTags(array('mresource_update'));
        }
        return $iNewId;
    }

    /**
     * Add relations between mresources and target
     *
     * @param array|ModuleMresource_EntityMresource $aMresources
     * @param string                                $sTargetType
     * @param int                                   $iTargetId
     *
     * @return bool
     */
    public function AddTargetRel($aMresources, $sTargetType, $iTargetId) {

        if (!is_array($aMresources)) {
            $aMresources = array($aMresources);
        }
        $aMresources = $this->BuildMresourceHashList($aMresources);
        $aNewMresources = $aMresources;

        // Проверяем, есть ли эти ресурсы в базе
        $aMresourcesDb = $this->oMapper->GetMresourcesByHashUrl(array_keys($aMresources));
        if ($aMresourcesDb) {
            /** @var ModuleMresource_EntityMresource $oMresourceDb */
            foreach($aMresourcesDb as $oMresourceDb) {
                if (isset($aMresources[$oMresourceDb->GetHash()])) {
                    // Такой ресурс есть, удаляем из списка на добавление
                    $aMresources[$oMresourceDb->GetHash()]->SetMresourceId($oMresourceDb->GetId());
                    unset($aNewMresources[$oMresourceDb->GetHash()]);
                }
            }
        }

        // Добавляем новые ресурсы, если есть
        if ($aNewMresources) {
            /** @var ModuleMresource_EntityMresource $oNewMresource */
            foreach ($aNewMresources as $oNewMresource) {
                $oSavedMresource = $this->GetMresourcesByUuid($oNewMresource->GetStorageUuid());
                // Если ресурс в базе есть, но файла нет (если удален извне), то удаляем ресус из базы
                if ($oSavedMresource && !$oSavedMresource->isLink() && !$oSavedMresource->Exists()) {
                    $this->DeleteMresources($oSavedMresource, false);
                    $oSavedMresource = null;
                }
                if (!$oSavedMresource) {
                    // Если ресурса нет, то добавляем
                    $nId = $this->oMapper->Add($oNewMresource);
                } else {
                    // Если ресурс есть, то просто его ID берем
                    $nId = $oSavedMresource->getId();
                }
                if ($nId && isset($aMresources[$oNewMresource->GetHash()])) {
                    // Такой ресурс есть, удаляем из списка на добавление
                    $aMresources[$oNewMresource->GetHash()]->SetMresourceId($nId);
                }
            }
        }

        // Добавляем связь ресурса с сущностью
        if ($aMresources) {
            /** @var ModuleMresource_EntityMresource $oMresource */
            foreach($aMresources as $oMresource) {
                if (!$oMresource->GetTargetType()) {
                    $oMresource->SetTargetType($sTargetType);
                }
                if (!$oMresource->GetTargetid()) {
                    $oMresource->SetTargetId($iTargetId);
                }
                $this->oMapper->AddTargetRel($oMresource);
            }
        }
        E::ModuleCache()->CleanByTags(array('mresource_rel_update'));

        return true;
    }

    /**
     * @param int $iId
     *
     * @return ModuleMresource_EntityMresource|null
     */
    public function GetMresourceById($iId) {

        $aData = $this->oMapper->GetMresourcesById(array($iId));
        if (isset($aData[$iId])) {
            return $aData[$iId];
        }
        return null;
    }

    /**
     * @param $xUuid
     *
     * @return array|ModuleMresource_EntityMresource
     */
    public function GetMresourcesByUuid($xUuid) {

        $bSingleRec = !is_array($xUuid);
        $aData = $this->oMapper->GetMresourcesByUuid($xUuid);
        if ($aData) {
            if ($bSingleRec) {
                return reset($aData);
            } else {
                return $aData;
            }
        }
        return $bSingleRec ? null : array();
    }

    /**
     * @param array $aCriteria
     *
     * @return array
     */
    public function GetMresourcesByCriteria($aCriteria) {

        $aData = $this->oMapper->GetMresourcesByCriteria($aCriteria);
        if ($aData['data']) {
            $aCollection = E::GetEntityRows('Mresource', $aData['data']);
            if (isset($aCriteria['with'])) {
                if (!is_array($aCriteria['with'])) {
                    $aCriteria['with'] = array($aCriteria['with']);
                }
                foreach($aCriteria['with'] as $sRelEntity) {
                    if ($sRelEntity == 'user') {
                        $aUserId = array_values(array_unique(F::Array_Column($aData['data'], 'user_id')));
                        $aUsers = E::ModuleUser()->GetUsersByArrayId($aUserId);

                        /** @var ModuleMresource_EntityMresource $oMresource */
                        foreach ($aCollection as $oMresource) {
                            if (isset($aUsers[$oMresource->getUserId()])) {
                                $oMresource->setUser($aUsers[$oMresource->getUserId()]);
                            }
                        }
                        $aUsers = null;
                    }
                }
            }
        } else {
            $aCollection = array();
        }
        return array('collection' => $aCollection, 'count' => 0);
    }

    /**
     * @param array $aFilter
     * @param int   $iPage
     * @param int   $iPerPage
     *
     * @return array
     */
    public function GetMresourcesByFilter($aFilter, $iPage, $iPerPage) {

        $aData = $this->oMapper->GetMresourcesByFilter($aFilter, $iPage, $iPerPage);

        return array('collection' => $aData['data'], 'count' => 0);
    }

    /**
     * @param string         $sTargetType
     * @param int|array|null $xTargetId
     *
     * @return ModuleMresource_EntityMresourceRel[]
     */
    public function GetMresourcesRelByTarget($sTargetType, $xTargetId = null) {

        return $this->GetMresourcesRelByTargetAndUser($sTargetType, $xTargetId, null);
    }

    /**
     * @param string         $sTargetType
     * @param int|array|null $xTargetId
     * @param int|array|null $xUserId
     *
     * @return ModuleMresource_EntityMresourceRel[]
     */
    public function GetMresourcesRelByTargetAndUser($sTargetType, $xTargetId = null, $xUserId = null) {

        $sCacheKey = 'mresource_rel_' . serialize(array($sTargetType, $xTargetId, $xUserId));
        if (false === ($aData = E::ModuleCache()->Get($sCacheKey))) {
            $aData = $this->oMapper->GetMresourcesRelByTargetAndUser($sTargetType, $xTargetId, $xUserId);
            E::ModuleCache()->Set($aData, $sCacheKey, array('mresource_rel_update'), 'P1D');
        }

        return $aData;
    }

    /**
     * Deletes media resources by ID
     *
     * @param $aMresources
     * @param $bDeleteFiles
     * @param $bNoCheckTargets
     *
     * @return bool
     */
    public function DeleteMresources($aMresources, $bDeleteFiles = true, $bNoCheckTargets = false) {

        $aId = $this->_entitiesId($aMresources);
        $bResult = true;

        if ($aId) {
            if ($bDeleteFiles) {
                $aMresources = $this->oMapper->GetMresourcesById($aId);
                if (!$bNoCheckTargets && $aMresources) {
                    /** @var ModuleMresource_EntityMresource $oMresource */
                    foreach ($aMresources as $oMresource) {
                        // Если число ссылок > 0, то не удаляем
                        if ($oMresource->getTargetsCount() > 0) {
                            $iIdx = array_search($oMresource->getId(), $aId);
                            if ($iIdx !== false) {
                                unset($aId[$iIdx]);
                            }
                        }
                    }
                }
            }

            $bResult = $this->oMapper->DeleteMresources($aId);

            if ($bDeleteFiles) {
                if ($bResult && $aMresources && $aId) {
                    // Удаляем файлы
                    foreach ($aId as $nId) {
                        if (isset($aMresources[$nId]) && $aMresources[$nId]->IsFile() && $aMresources[$nId]->CanDelete()) {
                            if ($aMresources[$nId]->IsImage()) {
                                E::ModuleImg()->Delete($aMresources[$nId]->GetFile());
                            } else {
                                F::File_Delete($aMresources[$nId]->GetFile());
                            }
                        }
                    }
                }
            }
        }
        E::ModuleCache()->CleanByTags(array('mresource_update', 'mresource_rel_update'));

        return $bResult;
    }

    /**
     * @param ModuleMresource_EntityMresourceRel[] $aMresourceRel
     *
     * @return bool
     */
    protected function _deleteMresourcesRel($aMresourceRel) {

        $aMresId = array();
        if ($aMresourceRel) {
            if (!is_array($aMresourceRel)) {
                $aMresourceRel = array($aMresourceRel);
            }
            /** @var ModuleMresource_EntityMresourceRel $oResourceRel */
            foreach($aMresourceRel as $oResourceRel) {
                $aMresId[] = $oResourceRel->GetMresourceId();
            }
            $aMresId = array_unique($aMresId);
        }
        $bResult = $this->oMapper->DeleteMresourcesRel(array_keys($aMresourceRel));
        if ($bResult && $aMresId) {
            // TODO: Delete files or not - need to add config options
            //  $this->DeleteMresources($aMresId);
            $this->DeleteMresources($aMresId, false);
        }
        E::ModuleCache()->CleanByTags(array('mresource_update', 'mresource_rel_update'));

        return true;
    }

    /**
     * Deletes media resources' relations by rel ID
     *
     * @param int[] $aId
     *
     * @return bool
     */
    public function DeleteMresourcesRel($aId) {

        if ($aId) {
            $aMresourceRel = $this->oMapper->GetMresourcesRelById($aId);
            if ($aMresourceRel) {
                return $this->_deleteMresourcesRel($aMresourceRel);
            }
        }
        return true;
    }

    /**
     * Deletes mresources' relations by target type & id
     *
     * @param string    $sTargetType
     * @param int|array $xTargetId
     *
     * @return bool
     */
    public function DeleteMresourcesRelByTarget($sTargetType, $xTargetId) {

        $aMresourceRel = $this->oMapper->GetMresourcesRelByTarget($sTargetType, $xTargetId);
        if ($aMresourceRel) {
            $aMresId = array();
            if ($this->oMapper->DeleteTargetRel($sTargetType, $xTargetId)) {

                /** @var ModuleMresource_EntityMresourceRel $oResourceRel */
                foreach ($aMresourceRel as $oResourceRel) {
                    $aMresId[] = $oResourceRel->GetMresourceId();
                }
                $aMresId = array_unique($aMresId);
            }
            if ($aMresId) {
                return $this->DeleteMresources($aMresId);
            }
        }
        E::ModuleCache()->CleanByTags(array('mresource_rel_update'));

        return true;
    }

    /**
     * @param string    $sTargetType
     * @param int|array $xTargetId
     * @param int       $iUserId
     *
     * @return bool
     */
    public function DeleteMresourcesRelByTargetAndUser($sTargetType, $xTargetId, $iUserId) {

        $aMresourceRel = $this->oMapper->GetMresourcesRelByTargetAndUser($sTargetType, $xTargetId, $iUserId);
        if ($aMresourceRel) {
            $aMresId = array();
            if ($this->oMapper->DeleteTargetRel($sTargetType, $xTargetId)) {

                /** @var ModuleMresource_EntityMresourceRel $oResourceRel */
                foreach ($aMresourceRel as $oResourceRel) {
                    $aMresId[] = $oResourceRel->GetMresourceId();
                }
                $aMresId = array_unique($aMresId);
            }
            if ($aMresId) {
                return $this->DeleteMresources($aMresId);
            }
        }
        E::ModuleCache()->CleanByTags(array('mresource_rel_update'));

        return true;
    }

    /**
     * Calc hash of URL for seeking & comparation
     *
     * @param string $sUrl
     *
     * @return string
     */
    public function CalcUrlHash($sUrl) {

        if (substr($sUrl, 0, 1) != '@') {
            $sPathUrl = F::File_LocalUrl($sUrl);
            if ($sPathUrl) {
                $sUrl = '@' . trim($sPathUrl, '/');
            }
        }
        return md5($sUrl);
    }

    /**
     * @param string $sStorage
     * @param string $sFileName
     * @param string $sFileHash
     * @param int    $iUserId
     *
     * @return string
     */
    static public function CreateUuid($sStorage, $sFileName, $sFileHash, $iUserId) {

        $sUuid = '0u' . F::Crc32($iUserId . ':' . $sFileHash, true)
            . '-' . F::Crc32($sStorage . ':' . $sFileName . ':' . $iUserId, true)
            . '-' . F::Crc32($sStorage . ':' . $sFileHash . ':' . $sFileName, true);
        return $sUuid;
    }

    /**
     * Удаляет временную ссылку
     *
     * @param $sTargetTmp
     * @param $sTargetId
     */
    public function ResetTmpRelById($sTargetTmp, $sTargetId) {

        $this->oMapper->ResetTmpRelById($sTargetTmp, $sTargetId);
    }

    /**
     * Удаление изображения
     *
     * @param string $sTargetType
     * @param int    $iTargetId
     * @param int    $iUserId
     */
    public function UnlinkFile($sTargetType, $iTargetId, $iUserId) {

        // Получим и удалим все ресурсы
        $aMresourceRel = $this->GetMresourcesRelByTargetAndUser($sTargetType, $iTargetId, $iUserId);
        if ($aMresourceRel) {
            $aMresId = array();
            /** @var ModuleMresource_EntityMresourceRel $oResourceRel */
            foreach ($aMresourceRel as $oResourceRel) {
                $aMresId[] = $oResourceRel->GetMresourceId();
            }
            if ($aMresId) {
                $this->DeleteMresources($aMresId, TRUE);
            }
        }

        // И связи
        $this->DeleteMresourcesRelByTargetAndUser($sTargetType, $iTargetId, E::UserId());
    }

    /**
     * @return string[]
     */
    public function GetTargetTypes() {

        return $this->oMapper->GetTargetTypes();
    }

    /**
     * @param $sTargetType
     *
     * @return int
     */
    public function GetMresourcesCountByTarget($sTargetType) {

        return $this->oMapper->GetMresourcesCountByTarget($sTargetType);
    }

    /**
     * @param $sTargetType
     * @param $iUserId
     *
     * @return int
     */
    public function GetMresourcesCountByTargetAndUserId($sTargetType, $iUserId) {

        return $this->oMapper->GetMresourcesCountByTargetAndUserId($sTargetType, $iUserId);
    }

    /**
     * @param $sTargetType
     * @param $sTargetId
     * @param $iUserId
     *
     * @return int
     */
    public function GetMresourcesCountByTargetIdAndUserId($sTargetType, $sTargetId, $iUserId) {

        return $this->oMapper->GetMresourcesCountByTargetIdAndUserId($sTargetType, $sTargetId, $iUserId);
    }

    /**
     * Проверяет картинки комментариев
     * E::ModuleMresource()->CheckTargetTextForImages($sTarget, $sTargetId, $sTargetText);
     *
     * @param string $sTargetType
     * @param int    $sTargetId
     * @param string $sTargetText
     *
     * @return bool
     *
     * @internal param ModuleComment_EntityComment $oTarget
     */
    public function CheckTargetTextForImages($sTargetType, $sTargetId, $sTargetText) {

        // 1. Получим uuid рисунков из текста топика и создадим связь с объектом
        // если ее ещё нет.
        if (preg_match_all('~0u\w{8}-\w{8}-\w{8}~', $sTargetText, $aUuid) && isset($aUuid[0])) {

            // Получим uuid ресурсов
            $aUuid = array_unique($aUuid[0]);

            // Найдем ресурсы
            /** @var ModuleMresource_EntityMresource[] $aResult */
            $aResult = $this->GetMresourcesByUuid($aUuid);
            if (!$aResult) {
                return FALSE;
            }

            // Новым рисункам добавим таргет
            $aNewResources = array();
            foreach ($aResult as $sId => $oResource) {
                if ($oResource->getTargetsCount() != 0) {
                    continue;
                }

                // Текущий ресурс новый
                $aNewResources[] = $oResource;
            }

            // Добавим связи, если нужно
            if ($aNewResources) {
                $this->AddTargetRel($aNewResources, $sTargetType, $sTargetId);
            }


            // 2. Пробежимся по ресурсам комментария и если ресурса нет в новых, тогда
            // удалим этот ресурс.
            // Читаем список ресурсов из базы
            $aMresources = $this->GetMresourcesRelByTarget($sTargetType, $sTargetId);

            // Строим список ID ресурсов для удаления
            $aDeleteResources = array();
            foreach ($aMresources as $oMresource) {
                if (!isset($aResult[$oMresource->getMresourceId()])) {
                    // Если ресурса нет в хеш-таблице, то это прентендент на удаление
                    $aDeleteResources[$oMresource->GetId()] = $oMresource->getMresourceId();
                }
            }
            if ($aDeleteResources) {
                $this->DeleteMresources(array_values($aDeleteResources));
                $this->DeleteMresourcesRel(array_keys($aDeleteResources));
            }
        }

        return TRUE;
    }

    /**
     * Прикрепляет временный ресурс к вновь созданному объекту
     *
     * @param string $sTargetType
     * @param string $sTargetId
     * @param $sTargetTmp
     *
     * @return bool|ModuleMresource_EntityMresource
     */
    public function LinkTempResource($sTargetType, $sTargetId, $sTargetTmp) {

        if ($sTargetTmp && E::IsUser()) {

            $sNewPath = E::ModuleUploader()->GetUserImageDir(E::UserId(), true, false);
            $aMresourceRel = $this->GetMresourcesRelByTargetAndUser($sTargetType, 0, E::UserId());

            if ($aMresourceRel) {
                $oResource = array_shift($aMresourceRel);
                $sOldPath = $oResource->GetFile();

                $oStoredFile = E::ModuleUploader()->Store($sOldPath, $sNewPath);
                /** @var ModuleMresource_EntityMresource $oResource */
                $oResource = $this->GetMresourcesByUuid($oStoredFile->getUuid());
                if ($oResource) {
                    $oResource->setUrl($this->NormalizeUrl(E::ModuleUploader()->GetTargetUrl($sTargetType, $sTargetId)));
                    $oResource->setType($sTargetType);
                    $oResource->setUserId(E::UserId());

                    // 4. В свойство поля записать адрес картинки
                    $this->UnlinkFile($sTargetType, 0, E::UserId());
                    $this->AddTargetRel($oResource, $sTargetType, $sTargetId);

                    return $oResource;

                }
            }
        }

        return false;
    }

    /**
     * Обновляет параметры ресурса
     *
     * @param ModuleMresource_EntityMresource $oResource
     *
     * @return bool
     */
    public function UpdateParams($oResource){

        return $this->oMapper->UpdateParams($oResource);
    }

    /**
     * Обновляет url ресурса
     *
     * @param ModuleMresource_EntityMresource $oResource
     *
     * @return bool
     */
    public function UpdateMresouceUrl($oResource){

        return $this->oMapper->UpdateMresouceUrl($oResource);
    }

    /**
     * Обновляет тип ресурса
     *
     * @param ModuleMresource_EntityMresource $oResource
     *
     * @return bool
     */
    public function UpdateType($oResource){

        return $this->oMapper->UpdateType($oResource);
    }

    /**
     * Устанавливает главный рисунок фотосета
     *
     * @param ModuleMresource_EntityMresource $oResource
     * @param $sTargetType
     * @param $sTargetId
     *
     * @return bool
     */
    public function UpdatePrimary($oResource, $sTargetType, $sTargetId){

        return $this->oMapper->UpdatePrimary($oResource, $sTargetType, $sTargetId);
    }

    /**
     * Устанавливает новый порядок сортировки изображений
     *
     * @param $aOrder
     * @param $sTargetType
     * @param $sTargetId
     *
     * @return mixed
     */
    public function UpdateSort($aOrder, $sTargetType, $sTargetId) {

        return $this->oMapper->UpdateSort($aOrder, $sTargetType, $sTargetId);
    }

    /**
     * Возвращает информацию о количестве и обложке фотосета
     *
     * @param $sTargetType
     * @param $sTargetId
     *
     * @return array
     */
    public function GetPhotosetData($sTargetType, $sTargetId) {

        $aMresource = $this->GetMresourcesRelByTarget($sTargetType, $sTargetId);

        $aResult = array(
            'count' => 0,
            'cover' => FALSE,
        );

        if ($aMresource) {

            $aResult['count'] = count($aMresource);

            foreach ($aMresource as $oResource) {
                if ($oResource->IsCover()) {
                    $aResult['cover'] = $oResource->getMresourceId();
                    break;
                }

            }

        }

        return $aResult;

    }

    /**
     * Возвращает категории изображения для пользователя
     *
     * @param $iUserId
     * @param bool $sTopicId
     *
     * @return array
     */
    public function GetImageCategoriesByUserId($iUserId, $sTopicId = FALSE){

        $aRows = $this->oMapper->GetImageCategoriesByUserId($iUserId, $sTopicId);
        $aResult = array();
        if ($aRows) {
            foreach ($aRows as $aRow) {
                $aResult[] = E::GetEntity('Mresource_MresourceCategory', array(
                    'id' => $aRow['ttype'],
                    'count' => $aRow['count'],
                    'label' => E::ModuleLang()->Get('aim_target_type_' . $aRow['ttype']),
                ));
            }
        }
        return $aResult;
    }

    /**
     * @param $iUserId
     * @param $sTopicId
     *
     * @return mixed
     */
    public function GetCurrentTopicResourcesId($iUserId, $sTopicId) {

        return $this->oMapper->GetCurrentTopicResourcesId($iUserId, $sTopicId);
    }

    /**
     * @param int $iUserId
     * @param bool $sTopicId
     *
     * @return bool|Entity
     */
    public function GetCurrentTopicImageCategory($iUserId, $sTopicId = FALSE) {

        $aResourcesId = $this->oMapper->GetCurrentTopicResourcesId($iUserId, $sTopicId);
       if ($aResourcesId) {
           if ($sTopicId) {
               return E::GetEntity('Mresource_MresourceCategory', array(
                   'id' => 'current',
                   'count' => count($aResourcesId),
                   'label' => E::ModuleLang()->Get('aim_target_type_current'),
               ));
           } else {
               return E::GetEntity('Mresource_MresourceCategory', array(
                   'id' => 'tmp',
                   'count' => count($aResourcesId),
                   'label' => E::ModuleLang()->Get('target_type_tmp'),
               ));
           }
        }

        return FALSE;
    }

    /**
     * Получает топики пользователя с картинками
     *
     * @param int $iUserId
     * @param int $iPage
     * @param int $iPerPage
     *
     * @return array
     */
    public function GetTopicsPage($iUserId, $iPage, $iPerPage)  {

        $iCount = 0;
        $aFilter = array(
            'user_id' => $iUserId,
            'mresource_type' => ModuleMresource::TYPE_IMAGE | ModuleMresource::TYPE_PHOTO | ModuleMresource::TYPE_PHOTO_PRIMARY,
            'target_type' => array('photoset', 'topic'),
        );
        if (E::IsUser() && E::User() !== $iUserId) {
            // Если текущий юзер не совпадает с запрашиваемым, то получаем список доступных блогов
            $aFilter['blogs_id'] = E::ModuleBlog()->GetAccessibleBlogsByUser(E::User());
            // И топики должны быть опубликованы
            $aFilter['topic_publish'] = 1;
        }
        if (!E::IsUser()) {
            // Если юзер не авторизован, то считаем все доступные для индексации топики
            $aFilter['topic_index_ignore'] = 0;
        }

        $aTopicInfo = $this->oMapper->GetTopicInfo($aFilter, $iCount, $iPage, $iPerPage);
        if ($aTopicInfo) {

            $aTopics = E::ModuleTopic()->GetTopicsAdditionalData(array_keys($aTopicInfo));
            if ($aTopics) {
                foreach ($aTopics as $sTopicId => $oTopic) {
                    $oTopic->setImagesCount($aTopicInfo[$sTopicId]);
                    $aTopics[$sTopicId] = $oTopic;
                }
            }

            $aResult = array(
                'collection' => $aTopics,
                'count' => $iCount,
            );
        } else {
            $aResult = array(
                'collection' => array(),
                'count' => 0
            );
        }

        return $aResult;
    }

    /**
     * @param int $iUserId
     *
     * @return bool|ModuleMresource_EntityMresourceCategory
     */
    public function GetTalksImageCategory($iUserId) {

        $aTalkInfo = $this->oMapper->GetTalkInfo($iUserId, $iCount, 1, 100000);
        if ($aTalkInfo) {
            return E::GetEntity('Mresource_MresourceCategory', array(
                'id' => 'talks',
                'count' => count($aTalkInfo),
                'label' => E::ModuleLang()->Get('aim_target_type_talks'),
            ));
        }

        return FALSE;
    }

    /**
     * Получает топики пользователя с картинками
     *
     * @param int $iUserId
     * @param int $iPage
     * @param int $iPerPage
     *
     * @return array
     */
    public function GetTalksPage($iUserId, $iPage, $iPerPage)  {

        $iCount = 0;
        $aResult = array(
            'collection' => array(),
            'count' => 0
        );

        $aTalkInfo = $this->oMapper->GetTalkInfo($iUserId, $iCount, $iPage, $iPerPage);
        if ($aTalkInfo) {

            $aTalks = E::ModuleTalk()->GetTalksAdditionalData(array_keys($aTalkInfo));
            if ($aTalks) {
                foreach ($aTalks as $sTopicId => $oTopic) {
                    $oTopic->setImagesCount($aTalkInfo[$sTopicId]);
                    $aTalks[$sTopicId] = $oTopic;
                }
            }

            $aResult['collection'] = $aTalks;
            $aResult['count'] = $iCount;
        }

        return $aResult;
    }

    /**
     * @param int $iUserId
     *
     * @return bool|Entity
     */
    public function GetCommentsImageCategory($iUserId) {

        $aImagesInCommentsCount = $this->GetMresourcesCountByTargetAndUserId(array(
            'talk_comment',
            'topic_comment'
        ), $iUserId);
        if ($aImagesInCommentsCount) {
            return E::GetEntity('Mresource_MresourceCategory', array(
                'id' => 'comments',
                'count' => $aImagesInCommentsCount,
                'label' => E::ModuleLang()->Get('aim_target_type_comments'),
            ));
        }

        return FALSE;
    }
    /**
     * Возвращает категории изображения для пользователя
     * @param int $iUserId
     * @return array
     */
    public function GetAllImageCategoriesByUserId($iUserId){

        $aRows = $this->oMapper->GetAllImageCategoriesByUserId($iUserId);
        $aResult = array();
        if ($aRows) {
            foreach ($aRows as $aRow) {
                $aResult[] = E::GetEntity('Mresource_MresourceCategory', array(
                    'id' => $aRow['ttype'],
                    'count' => $aRow['count'],
                    'label' => E::ModuleLang()->Get('aim_target_type_' . $aRow['ttype']),
                ));
            }
        }
        return $aResult;
    }

    /**
     * Возвращает информацию о категориях изображений пользователя
     * с разбивкой по типу контента
     *
     * @param int $iUserId
     *
     * @return array
     */
    public function GetTopicsImageCategory($iUserId) {

        $aFilter = array(
            'user_id' => $iUserId,
            'mresource_type' => ModuleMresource::TYPE_IMAGE | ModuleMresource::TYPE_PHOTO | ModuleMresource::TYPE_PHOTO_PRIMARY,
            'target_type' => array('photoset', 'topic'),
        );
        if (E::IsUser() && E::User() !== $iUserId) {
            // Если текущий юзер не совпадает с запрашиваемым, то получаем список доступных блогов
            $aFilter['blogs_id'] = E::ModuleBlog()->GetAccessibleBlogsByUser(E::User());
            // И топики должны быть опубликованы
            $aFilter['topic_publish'] = 1;
        }
        if (!E::IsUser()) {
            // Если юзер не авторизован, то считаем все доступные для индексации топики
            $aFilter['topic_index_ignore'] = 0;
        }
        $aData = $this->oMapper->GetCountImagesByTopicType($aFilter);
        if ($aData) {
            foreach ($aData as $xIndex => $aRow) {
                $sLabelKey = 'target_type_' . $aRow['id'];
                if (($sLabel = E::ModuleLang()->Get($sLabelKey)) == mb_strtoupper($sLabelKey)) {
                    /** @var ModuleTopic_EntityContentType $oContentType */
                    $oContentType = E::ModuleTopic()->GetContentTypeByUrl($aRow['id']);
                    if ($oContentType) {
                        $sLabel = $oContentType->getContentTitleDecl();
                    }
                }
                $aData[$xIndex]['label'] = E::ModuleLang()->Get($sLabel);
            }
            $aResult = E::GetEntityRows('Mresource_MresourceCategory', $aData);
        } else {
            $aResult = array();
        }
        return $aResult;
    }

    /**
     * Получает топики пользователя с картинками
     *
     * @param int    $iUserId
     * @param string $sType
     * @param int    $iPage
     * @param int    $iPerPage
     *
     * @return array
     */
    public function GetTopicsPageByType($iUserId, $sType, $iPage, $iPerPage)  {

        $iCount = 0;
        $aFilter = array(
            'user_id' => $iUserId,
            'mresource_type' => ModuleMresource::TYPE_IMAGE | ModuleMresource::TYPE_PHOTO | ModuleMresource::TYPE_PHOTO_PRIMARY,
            'target_type' => array('photoset', 'topic'),
        );
        if (E::IsUser() && E::User() !== $iUserId) {
            // Если текущий юзер не совпадает с запрашиваемым, то получаем список доступных блогов
            $aFilter['blogs_id'] = E::ModuleBlog()->GetAccessibleBlogsByUser(E::User());
            // И топики должны быть опубликованы
            $aFilter['topic_publish'] = 1;
        }
        if (!E::IsUser()) {
            // Если юзер не авторизован, то считаем все доступные для индексации топики
            $aFilter['topic_index_ignore'] = 0;
        }
        $aFilter['topic_type'] = $sType;

        $aTopicInfo = $this->oMapper->GetTopicInfo($aFilter, $iCount, $iPage, $iPerPage);
        if ($aTopicInfo) {

            $aFilter = array(
                'topic_id' => array_keys($aTopicInfo),
                'topic_type' => $sType
            );
            // Результат в формате array('collection'=>..., 'count'=>...)
            $aResult = E::ModuleTopic()->GetTopicsByFilter($aFilter, 1, count($aTopicInfo));

            if ($aResult) {
                /** @var ModuleTopic_EntityTopic $oTopic */
                foreach ($aResult['collection'] as $sTopicId => $oTopic) {
                    $oTopic->setImagesCount($aTopicInfo[$sTopicId]);
                    $aResult['collection'][$sTopicId] = $oTopic;
                }
                $aResult['count'] = $iCount; // total number of topics with images
            }

            return $aResult;
        }

        return array(
            'collection' => array(),
            'count' => 0
        );
    }

    /**
     * Возвращает категории изображения для пользователя
     * @param $iUserId
     *
     * @return mixed
     */
    public function GetCountImagesByUserId($iUserId){

        return $this->oMapper->GetCountImagesByUserId($iUserId);

    }

}

// EOF
