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
    const TYPE_HREF = 32;

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
     * @param $sUrl
     * @param $sReplace
     * @param $aAdditional
     *
     * @return array|string
     */
    public function NormalizeUrl($sUrl, $sReplace = '@', $aAdditional = '') {

        if (is_array($sUrl)) {
            $aUrls = $sUrl;
            foreach ($aUrls as $nI => $sUrl) {
                $aUrls[$nI] = $this->NormalizeUrl((string)$sUrl, $sReplace, $aAdditional);
            }
            return $aUrls;
        }
        $sUrl = str_replace(
            array('http://@' . $aAdditional, 'https://@' . $aAdditional, 'ftp://@' . $aAdditional), $sReplace, $sUrl
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
            // Групповое добавление
            $xResult = array();
            foreach ($oMediaResource as $oResource) {
                if ($nId = $this->oMapper->Add($oResource)) {
                    $xResult[] = $nId;
                    $oResource->setId($nId);
                }
            }
        } else {
            if ($xResult = $this->oMapper->Add($oMediaResource)) {
                $oMediaResource->setId($xResult);
            }
        }
        if ($xResult) {
            //чистим зависимые кеши
            $this->Cache_CleanByTags(array('mresource_update'));
            return $xResult;
        }
        return 0;
    }

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
                $nId = $this->oMapper->Add($oMresource);
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
     * @param $aId
     * @param $bNoCheckTargets
     *
     * @return bool
     */
    public function DeleteMresources($aId, $bNoCheckTargets = false) {

        $aMresources = $this->oMapper->GetMresourcesById($aId);
        if (!$bNoCheckTargets && $aMresources) {
            foreach ($aMresources as $oMresource) {
                // Если число ссылок > 0, то не удаляем
                if ($oMresource->getTargetsCount() > 0) {
                    unset($aId[$oMresource->getId()]);
                }
            }
        }
        $bResult = $this->oMapper->DeleteMresources($aId);
        if ($bResult && $aMresources) {
            // Удаляем файлы
            foreach ($aId as $nId) {
                if (isset($aMresources[$nId]) && $aMresources[$nId]->IsFile() && $aMresources[$nId]->CanDelete()) {
                    F::File_Delete($aMresources[$nId]->GetFile());
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
            $this->DeleteMresources($aMresId);
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

    public function DeleteMresourcesRelByTarget($sTargetType, $nTargetId) {

        $aMresourceRel = $this->oMapper->GetMresourcesRelByTarget($sTargetType, $nTargetId);
        if ($aMresourceRel) {
            return $this->_deleteMresourcesRel($aMresourceRel);
        }
        return true;
    }

}

// EOF