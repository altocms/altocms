<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   LiveStreet Engine Social Networking by Mzhelskiy Maxim
 *   Site: www.livestreet.ru
 *   E-mail: rus.engine@gmail.com
 *----------------------------------------------------------------------------
 */

/**
 * Модуль для статических страниц
 *
 * @package modules.page
 * @since   1.0
 */
class ModulePage extends Module {

    /** @var ModulePage_MapperPage */
    protected $oMapper;
    protected $aRebuildIds = array();

    /**
     * Инициализация
     *
     */
    public function Init() {

        $this->oMapper = E::GetMapper(__CLASS__);
    }

    /**
     * Добавляет страницу
     *
     * @param ModulePage_EntityPage $oPage
     *
     * @return bool
     */
    public function AddPage(ModulePage_EntityPage $oPage) {

        if ($sId = $this->oMapper->AddPage($oPage)) {
            $oPage->setId($sId);
            //чистим зависимые кеши
            E::ModuleCache()->Clean(
                Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                array('page_new', 'page_update', "page_update_{$oPage->getId()}", "page_update_urlfull_{$oPage->getUrlFull()}")
            );
            return true;
        }
        return false;
    }

    /**
     * Обновляет страницу
     *
     * @param ModulePage_EntityPage $oPage
     *
     * @return bool
     */
    public function UpdatePage(ModulePage_EntityPage $oPage) {

        if ($this->oMapper->UpdatePage($oPage)) {
            //чистим зависимые кеши
            E::ModuleCache()->Clean(
                Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                array('page_update', "page_update_{$oPage->getId()}", "page_update_urlfull_{$oPage->getUrlFull()}")
            );
            return true;
        }
        return false;
    }

    /**
     * Получает страницу по полному УРЛу
     *
     * @param   string  $sUrlFull
     * @param   int     $iActive
     *
     * @return  ModulePage_EntityPage
     */
    public function GetPageByUrlFull($sUrlFull, $iActive = 1) {

        if (false === ($data = E::ModuleCache()->Get("page_{$sUrlFull}_{$iActive}"))) {
            $data = $this->oMapper->GetPageByUrlFull($sUrlFull, $iActive);
            if ($data) {
                E::ModuleCache()->Set(
                    $data, "page_{$sUrlFull}_{$iActive}", array("page_update_{$data->getId()}"), 60 * 60 * 24 * 5
                );
            } else {
                E::ModuleCache()->Set(
                    $data, "page_{$sUrlFull}_{$iActive}", array("page_update_urlfull_{$sUrlFull}"), 60 * 60 * 24 * 5
                );
            }
        }
        return $data;
    }

    /**
     * Получает страницу по её айдишнику
     *
     * @param int $sId
     *
     * @return ModulePage_EntityPage
     */
    public function GetPageById($sId) {

        return $this->oMapper->GetPageById($sId);
    }

    /**
     * Получает список всех страниц ввиде дерева
     *
     * @param   array   $aFilter
     * @return  array
     */
    public function GetPages($aFilter = array()) {

        $aPages = array();
        $sCacheKey = 'page_getpages' . serialize($aFilter);
        if (false === ($aPagesRow = E::ModuleCache()->Get($sCacheKey))) {
            $aPagesRow = $this->oMapper->GetPages($aFilter);
            E::ModuleCache()->Set($aPagesRow, $sCacheKey, array('page_new', 'page_update'), 'P1D');
        }

        if (count($aPagesRow)) {
            $aPages = $this->BuildPagesRecursive($aPagesRow);
        }
        return $aPages;
    }

    /**
     * Строит дерево страниц
     *
     * @param array $aPages
     * @param bool  $bBegin
     *
     * @return array
     */
    protected function BuildPagesRecursive($aPages, $bBegin = true) {

        static $aResultPages;
        static $iLevel;
        if ($bBegin) {
            $aResultPages = array();
            $iLevel = 0;
        }
        foreach ($aPages as $aPage) {
            $aTemp = $aPage;
            $aTemp['level'] = $iLevel;
            unset($aTemp['childNodes']);
            $aResultPages[] = E::GetEntity('Page', $aTemp);
            if (isset($aPage['childNodes']) and count($aPage['childNodes']) > 0) {
                $iLevel++;
                $this->BuildPagesRecursive($aPage['childNodes'], false);
            }
        }
        $iLevel--;

        return $aResultPages;
    }

    /**
     * Рекурсивно обновляет полный URL у всех дочерних страниц(веток)
     *
     * @param ModulePage_EntityPage $oPageStart
     */
    public function RebuildUrlFull($oPageStart) {

        $aPages = $this->GetPagesByPid($oPageStart->getId());
        foreach ($aPages as $oPage) {
            if ($oPage->getId() == $oPageStart->getId()) {
                continue;
            }
            if (in_array($oPage->getId(), $this->aRebuildIds)) {
                continue;
            }
            $this->aRebuildIds[] = $oPage->getId();
            $oPage->setUrlFull($oPageStart->getUrlFull() . '/' . $oPage->getUrl());
            $this->UpdatePage($oPage);
            $this->RebuildUrlFull($oPage);
        }
    }

    /**
     * Получает список дочерних страниц первого уровня
     *
     * @param   int $nPid
     *
     * @return  array
     */
    public function GetPagesByPid($nPid) {

        return $this->oMapper->GetPagesByPid(is_null($nPid) ? null : intval($nPid));
    }

    /**
     * Удаляет страницу по её айдишнику
     * Дочернии страницы не удаляются, а лишаюся "родителя" (переносятся в корень)
     *
     * @param int $nId
     *
     * @return bool
     */
    public function deletePageById($nId) {

        $aPages = $this->GetPagesByPid($nId);
        if ($this->oMapper->deletePageById($nId)) {
            if ($aPages) {
                // удаляем "родителя" у вложенных страниц
                $aPageIds = array_keys($aPages);
                $this->SetPagesPidToNull($aPages);
            }
            //чистим зависимые кеши
            E::ModuleCache()->Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('page_update', "page_update_{$nId}"));
            return true;
        }
        return false;
    }

    /**
     * Получает число статических страниц
     *
     * @return int
     */
    public function GetCountPage() {

        return $this->oMapper->GetCountPage();
    }

    /**
     * Устанавливает всем (или определенным) страницам PID = NULL
     * Это бывает нужно, когда особо "умный" админ зациклит страницы сами на себя..
     *
     * @param   null|array $aPageIds
     *
     * @return  bool
     */
    public function SetPagesPidToNull($aPageIds = array()) {

        return $this->oMapper->SetPagesPidToNull($aPageIds);
    }

    /**
     * Получает слеудующую по сортировке страницу
     *
     * @param int    $iSort
     * @param int    $sPid
     * @param string $sWay
     *
     * @return ModulePage_EntityPage
     */
    public function GetNextPageBySort($iSort, $sPid, $sWay = 'up') {

        return $this->oMapper->GetNextPageBySort($iSort, $sPid, $sWay);
    }

    /**
     * Получает значение максимальной сртировки
     *
     * @param   int
     *
     * @return  int
     */
    public function GetMaxSortByPid($sPid) {

        return $this->oMapper->GetMaxSortByPid($sPid);
    }

    /**
     * Get count of pages
     *
     * @return integer
     */
    public function getCountOfActivePages() {

        return (int)$this->oMapper->getCountOfActivePages();
    }

    /**
     * Get list of active pages
     *
     * @param integer $iCount
     * @param integer $iCurrPage
     * @param integer $iPerPage
     *
     * @return array
     */
    public function getListOfActivePages(&$iCount, $iCurrPage, $iPerPage) {

        return $this->oMapper->getListOfActivePages(
            $iCount, $iCurrPage, Config::Get('plugin.sitemap.items_per_page')
        );
    }

    public function ReSort() {

        return $this->oMapper->ReSort();
    }
}

// EOF