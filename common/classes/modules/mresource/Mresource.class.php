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

    /** @var  ModuleMresource_MapperMresource */
    protected $oMapper;

    /**
     * Инициализация
     *
     */
    public function Init() {

        $this->oMapper = Engine::GetMapper(__CLASS__);
    }

    /**
     * Создание сущности медиа ресурса ссылки
     *
     * @param $sLink
     *
     * @return Entity
     */
    public function BuildMresourceLink($sLink) {

        $oMresource = Engine::GetEntity('Mresource');
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
            $this->Cache_CleanByTags(array('mresource_update'));
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
            $aCollection = Engine::GetEntityRows('Mresource', $aData['data']);
            if (isset($aCriteria['with'])) {
                if (!is_array($aCriteria['with'])) {
                    $aCriteria['with'] = array($aCriteria['with']);
                }
                foreach($aCriteria['with'] as $sRelEntity) {
                    if ($sRelEntity == 'user') {
                        $aUserId = array_values(array_unique(F::Array_Column($aData['data'], 'user_id')));
                        $aUsers = $this->User_GetUsersByArrayId($aUserId);
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
                            $this->Img_Delete($aMresources[$nId]->GetFile());
                        } else {
                            F::File_Delete($aMresources[$nId]->GetFile());
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
}

// EOF