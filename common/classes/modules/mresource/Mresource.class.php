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
     * @param $sLink
     *
     * @return Entity
     */
    public function BuildMresourceLink($sLink) {

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
     * @param $aMresources
     *
     * @return bool
     */
    public function IsHashList($aMresources) {

        if (is_array($aMresources)) {
            // first element of array
            reset($aMresources);
            $aData = each($aMresources);
            if (($aData['value'] instanceof ModuleMresource_EntityMresource)
                && ($aData['value']->GetHash() === $aData['key'])
            ) {
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
                $aUrls[$nI] = $this->NormalizeUrl((string)$sUrl, $sReplace, $sAdditional);
            }
            return $aUrls;
        }
        $sUrl = str_replace(
            array('http://@' . $sAdditional, 'https://@' . $sAdditional, 'ftp://@' . $sAdditional), $sReplace, $xUrl
        );
        return F::File_NormPath($sUrl);
    }

    /**
     * Добавление ресурса
     *
     * @param $oMediaResource
     *
     * @return bool
     */
    public function Add($oMediaResource) {

        if (!$oMediaResource) {
            return null;
        }
        if (is_array($oMediaResource)) {
            $aResources = $oMediaResource;
            // Групповое добавление
            foreach ($aResources as $nIdx => $oResource) {
                if ($nId = $this->oMapper->Add($oMediaResource)) {
                    $aResources[$nIdx] = $this->GetMresourceById($nId);
                }
            }
        } else {
            if ($nId = $this->oMapper->Add($oMediaResource)) {
                $oMediaResource = $this->GetMresourceById($nId);
            }
        }
        if ($nId) {
            //чистим зависимые кеши
            E::ModuleCache()->CleanByTags(array('mresource_update'));
            return $nId;
        }
        return 0;
    }

    /**
     * Add relations between mresources and target
     *
     * @param array  $aMresourcesRel
     * @param string $sTargetType
     * @param int    $nTargetId
     *
     * @return bool
     */
    public function AddTargetRel($aMresourcesRel, $sTargetType, $nTargetId) {

        if (!is_array($aMresourcesRel)) {
            $aMresourcesRel = array($aMresourcesRel);
        }
        $aMresourcesRel = $this->BuildMresourceHashList($aMresourcesRel);
        $aNewMresources = $aMresourcesRel;

        // Проверяем, есть ли эти ресурсы в базе
        $aMresources = $this->oMapper->GetMresourcesByHashUrl(array_keys($aMresourcesRel));
        if ($aMresources) {
            foreach($aMresources as $oMresource) {
                if (isset($aMresourcesRel[$oMresource->GetHash()])) {
                    // Такой ресурс есть, удаляем из списка на добавление
                    $aMresourcesRel[$oMresource->GetHash()]->SetMresourceId($oMresource->GetId());
                    unset($aNewMresources[$oMresource->GetHash()]);
                }
            }
        }

        // Добавляем новые ресурсы, если есть
        if ($aNewMresources) {
            foreach ($aNewMresources as $oMresource) {
                $oSavedMresource = $this->GetMresourcesByUuid($oMresource->GetStorageUuid());
                // Если ресурс в базе есть, но файла нет (если удален извне), то удаляем ресус из базы
                if ($oSavedMresource && !$oSavedMresource->isLink() && !$oSavedMresource->Exists()) {
                    $this->DeleteMresources($oSavedMresource, false);
                    $oSavedMresource = null;
                }
                if (!$oSavedMresource) {
                    // Если ресурса нет, то добавляем
                    $nId = $this->oMapper->Add($oMresource);
                } else {
                    // Если ресурс есть, то просто его ID берем
                    $nId = $oSavedMresource->getId();
                }
                if ($nId && isset($aMresourcesRel[$oMresource->GetHash()])) {
                    // Такой ресурс есть, удаляем из списка на добавление
                    $aMresourcesRel[$oMresource->GetHash()]->SetMresourceId($nId);
                }
            }
        }

        // Добавляем связь ресурса с сущностью
        if ($aMresourcesRel) {
            foreach($aMresourcesRel as $oMresource) {
                if (!$oMresource->GetTargetType()) {
                    $oMresource->SetTargetType($sTargetType);
                }
                if (!$oMresource->GetTargetid()) {
                    $oMresource->SetTargetId($nTargetId);
                }
                $this->oMapper->AddTargetRel($oMresource);
            }
        }
        return true;
    }

    public function GetMresourceById($iId) {

        $aData = $this->oMapper->GetMresourcesById(array($iId));
        if (isset($aData[$iId])) {
            return $aData[$iId];
        }
        return null;
    }

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

    public function GetMresourcesByFilter($aFilter, $nPage, $nPerPage) {

        $aData = $this->oMapper->GetMresourcesByFilter($aFilter, $nPage, $nPerPage);
        return array('collection' => $aData['data'], 'count' => 0);
    }

    public function GetMresourcesByTarget($sTargetType, $nTargetId) {

        $aData = $this->oMapper->GetMresourcesByTarget($sTargetType, $nTargetId);
        return $aData;
    }

    public function GetMresourcesRelByTarget($sTargetType, $nTargetId) {

        $aData = $this->oMapper->GetMresourcesRelByTarget($sTargetType, $nTargetId);
        return $aData;
    }

    public function GetMresourcesRelByTargetAndUser($sTargetType, $nTargetId, $iUserId) {

        $aData = $this->oMapper->GetMresourcesRelByTargetAndUser($sTargetType, $nTargetId, $iUserId);
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

        return $bResult;
    }

    protected function _deleteMresourcesRel($aMresourceRel) {

        $aMresId = array();
        if ($aMresourceRel) {
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
        return true;
    }

    /**
     * Deletes media resources' relations by rel ID
     *
     * @param $aId
     *
     * @return bool
     */
    public function DeleteMresourcesRel($aId) {

        if (!$aId) {
            return;
        }
        $aMresourceRel = $this->oMapper->GetMresourcesRelById($aId);
        if ($aMresourceRel) {
            return $this->_deleteMresourcesRel($aMresourceRel);
        }
        return true;
    }

    /**
     * Deletes mresources' relations by target type & id
     *
     * @param string $sTargetType
     * @param int    $nTargetId
     *
     * @return bool
     */
    public function DeleteMresourcesRelByTarget($sTargetType, $nTargetId) {

        $aMresourceRel = $this->oMapper->GetMresourcesRelByTarget($sTargetType, $nTargetId);
        if ($aMresourceRel) {
            if ($this->oMapper->DeleteTargetRel($sTargetType, $nTargetId)) {
                $aMresId = array();
                foreach ($aMresourceRel as $oResourceRel) {
                    $aMresId[] = $oResourceRel->GetMresourceId();
                }
                $aMresId = array_unique($aMresId);
            }
            return $this->DeleteMresources($aMresId);
        }
        return true;
    }
    public function DeleteMresourcesRelByTargetAndUser($sTargetType, $nTargetId, $iUserId) {

        $aMresourceRel = $this->oMapper->GetMresourcesRelByTargetAndUser($sTargetType, $nTargetId, $iUserId);
        if ($aMresourceRel) {
            if ($this->oMapper->DeleteTargetRel($sTargetType, $nTargetId)) {
                $aMresId = array();
                foreach ($aMresourceRel as $oResourceRel) {
                    $aMresId[] = $oResourceRel->GetMresourceId();
                }
                $aMresId = array_unique($aMresId);
            }
            return $this->DeleteMresources($aMresId);
        }
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

    static public function CreateUuid($sStorage, $sFileName, $sFileHash, $iUserId) {

        $sUuid = '0u' . F::Crc32($iUserId . ':' . $sFileHash, true)
            . '-' . F::Crc32($sStorage . ':' . $sFileName . ':' . $iUserId, true)
            . '-' . F::Crc32($sStorage . ':' . $sFileHash . ':' . $sFileName, true);
        return $sUuid;
    }

    /**
     * Удаляет временную ссылку
     * @param $sTargetTmp
     * @param $sTargetId
     */
    public function ResetTmpRelById($sTargetTmp, $sTargetId) {
        $this->oMapper->ResetTmpRelById($sTargetTmp, $sTargetId);
    }

    /**
     * Удаление изображения
     *
     * @param $sTargetType
     * @param $sTargetId
     * @param $sUserId
     */
    public function UnlinkFile($sTargetType, $sTargetId, $sUserId) {

        // Получим и удалим все ресурсы
        $aMresourceRel = $this->GetMresourcesRelByTargetAndUser($sTargetType, $sTargetId, $sUserId);
        if ($aMresourceRel) {
            $aMresId = array();
            foreach ($aMresourceRel as $oResourceRel) {
                $aMresId[] = $oResourceRel->GetMresourceId();
            }
            if ($aMresId) {
                $this->DeleteMresources($aMresId, TRUE);
            }
        }

        // И связи
        $this->DeleteMresourcesRelByTargetAndUser($sTargetType, $sTargetId, E::UserId());
    }

    public function GetTargetTypes() {
        return $this->oMapper->GetTargetTypes();
    }

    public function GetMresourcesCountByTarget($sTargetType) {
        return $this->oMapper->GetMresourcesCountByTarget($sTargetType);
    }

    public function GetMresourcesCountByTargetAndUserId($sTargetType, $iUserId) {
        return $this->oMapper->GetMresourcesCountByTargetAndUserId($sTargetType, $iUserId);
    }

    public function GetMresourcesCountByTargetIdAndUserId($sTargetType, $sTargetId, $iUserId) {
        return $this->oMapper->GetMresourcesCountByTargetIdAndUserId($sTargetType, $sTargetId, $iUserId);
    }

    /**
     * Проверяет картикнки комментариев
     * E::ModuleMresource()->CheckTargetTextForImages($sTarget, $sTargetId, $sTargetText);
     *
     * @param $sTarget
     * @param $sTargetId
     * @param $sTargetText
     * @return bool
     * @internal param ModuleComment_EntityComment $oTarget
     */
    public function CheckTargetTextForImages($sTarget, $sTargetId, $sTargetText) {

        // 1. Получим uuid рисунков из текста топика и создадим связь с объектом
        // если ее ещё нет.
        if (preg_match_all("~0u\w{8}-\w{8}-\w{8}~", $sTargetText, $aUuid) && isset($aUuid[0])) {

            // Получим uuid ресурсов
            $aUuid = array_unique($aUuid[0]);

            // Найдем ресурсы
            /** @var ModuleMresource_EntityMresource[] $aResult */
            $aResult = E::ModuleMresource()->GetMresourcesByUuid($aUuid);
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
                E::ModuleMresource()->AddTargetRel($aNewResources, $sTarget, $sTargetId);
            }


            // 2. Пробежимся по ресурсам комментария и если ресурса нет в новых, тогда
            // удалим этот ресурс.
            // Читаем список ресурсов из базы
            $aMresources = E::ModuleMresource()->GetMresourcesRelByTarget($sTarget, $sTargetId);

            // Строим список ID ресурсов для удаления
            $aDeleteResources = array();
            foreach ($aMresources as $oMresource) {
                if (!isset($aResult[$oMresource->getMresourceId()])) {
                    // Если ресурса нет в хеш-таблице, то это прентендент на удаление
                    $aDeleteResources[$oMresource->GetId()] = $oMresource->getMresourceId();
                }
            }
            if ($aDeleteResources) {
                E::ModuleMresource()->DeleteMresources(array_values($aDeleteResources));
                E::ModuleMresource()->DeleteMresourcesRel(array_keys($aDeleteResources));
            }
        }

        return TRUE;
    }

    /**
     * Прикрепляетвременный ресурс к вновь созданному объекту
     *
     * @param string $sTargetId
     * @param string $sTargetType
     * @param $sTargetTmp
     * @return bool|mixed|ModuleMresource_EntityMresource
     */
    public function LinkTempResource($sTargetId, $sTargetType, $sTargetTmp) {

        if ($sTargetTmp && E::IsUser()) {

            $sNewPath = E::ModuleUploader()->GetUploadDir($sTargetId, $sTargetType) . '/';
            $aMresourceRel = E::Mresource_GetMresourcesRelByTargetAndUser($sTargetType, 0, E::UserId());

            if ($aMresourceRel) {
                $oResource = array_shift($aMresourceRel);
                $sOldPath = $oResource->GetFile();

                $xStoredFile = E::ModuleUploader()->Store($sOldPath, $sNewPath);
                /** @var ModuleMresource_EntityMresource $oResource */
                $oResource = E::ModuleMresource()->GetMresourcesByUuid($xStoredFile->getUuid());
                if ($oResource) {
                    $oResource->setUrl(E::ModuleMresource()->NormalizeUrl(E::ModuleUploader()->GetTargetUrl($sTargetId, $sTargetType)));
                    $oResource->setType($sTargetType);
                    $oResource->setUserId(E::UserId());

                    // 4. В свойство поля записать адрес картинки
                    E::ModuleMresource()->UnlinkFile($sTargetType, 0, E::UserId());
                    E::ModuleMresource()->AddTargetRel($oResource, $sTargetType, $sTargetId);

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
     * @return bool
     */
    public function UpdateParams($oResource){
        return $this->oMapper->UpdateParams($oResource);
    }

    /**
     * Обновляет тип ресурса
     *
     * @param ModuleMresource_EntityMresource $oResource
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
     */
    public function UpdateSort($aOrder, $sTargetType, $sTargetId) {
        return $this->oMapper->UpdateSort($aOrder, $sTargetType, $sTargetId);
    }

    /**
     * Возвращает информацию о количестве и обложке фотосета
     *
     * @param $sTargetType
     * @param $sTargetId
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
     * @param $iUserId
     * @param bool $sTopicId
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

    public function GetCurrentTopicResourcesId($iUserId, $sTopicId) {
        return $this->oMapper->GetCurrentTopicResourcesId($iUserId, $sTopicId);
    }

    public function GetCurrentTopicImageCategory($iUserId, $sTopicId = FALSE) {
        $aResourcesId = $this->oMapper->GetCurrentTopicResourcesId($iUserId, $sTopicId);
        if ($aResourcesId) {
            return E::GetEntity('Mresource_MresourceCategory', array(
                'id' => 'current',
                'count' => count($aResourcesId),
                'label' => E::ModuleLang()->Get('aim_target_type_current'),
            ));
        }

        return FALSE;
    }



    public function GetTopicsImageCategory($iUserId) {

        $aTopicInfo = $this->oMapper->GetTopicInfo($iUserId, $iCount, 1, 100000);
        if ($aTopicInfo) {
            return E::GetEntity('Mresource_MresourceCategory', array(
                'id' => 'topics',
                'count' => count($aTopicInfo),
                'label' => E::ModuleLang()->Get('aim_target_type_topics'),
            ));
        }

        return FALSE;
    }

    /**
     * Получает топики пользователя с картинками
     * @param $iUserId
     * @return bool|array
     */
    public function GetTopicsPage($iUserId, $iCurrPage, $iPerPage)  {
        $iCount = 0;
        $aResult = array(
            'collection' => array(),
            'count' => 0
        );

        $aTopicInfo = $this->oMapper->GetTopicInfo($iUserId, $iCount, $iCurrPage, $iPerPage);
        if ($aTopicInfo) {

            $aTopics = E::ModuleTopic()->GetTopicsAdditionalData(array_keys($aTopicInfo));
            if ($aTopics) {
                foreach ($aTopics as $sTopicId => $oTopic) {
                    $oTopic->setImagesCount($aTopicInfo[$sTopicId]);
                    $aTopics[$sTopicId] = $oTopic;
                }
            }

            $aResult['collection'] = $aTopics;
            $aResult['count'] = $iCount;
        }

        return $aResult;
    }


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
     * @param $iUserId
     * @return bool|array
     */
    public function GetTalksPage($iUserId, $iCurrPage, $iPerPage)  {
        $iCount = 0;
        $aResult = array(
            'collection' => array(),
            'count' => 0
        );

        $aTalkInfo = $this->oMapper->GetTalkInfo($iUserId, $iCount, $iCurrPage, $iPerPage);
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

    public function GetCommentsImageCategory($iUserId) {

        $aImagesInCommentsCount = E::ModuleMresource()->GetMresourcesCountByTargetAndUserId(array(
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

}

// EOF