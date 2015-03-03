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
 * Маппер для работы со статическими страницами
 *
 * @package modules.page
 * @since   1.0
 */
class ModulePage_MapperPage extends Mapper {

    public function AddPage(ModulePage_EntityPage $oPage) {
        $sql
            = "INSERT INTO ?_page
			(page_pid,
			page_url,
			page_url_full,
			page_title,
			page_text,
			page_date_add,
			page_seo_keywords,
			page_seo_description,
			page_active,
			page_main,
			page_sort,
			page_auto_br
			)
			VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?d,  ?d,  ?d,  ?d)
		";
        $nId = $this->oDb->query(
            $sql, $oPage->getPid(), $oPage->getUrl(), $oPage->getUrlFull(), $oPage->getTitle(), $oPage->getText(),
            $oPage->getDateAdd(), $oPage->getSeoKeywords(), $oPage->getSeoDescription(), $oPage->getActive(),
            $oPage->getMain(), $oPage->getSort(), $oPage->getAutoBr()
        );
        return $nId ? $nId : false;
    }

    public function UpdatePage(ModulePage_EntityPage $oPage) {

        $sql
            = "UPDATE ?_page
			SET page_pid = ? ,
			page_url = ? ,
			page_url_full = ? ,
			page_title = ? ,
			page_text = ? ,
			page_date_edit = ? ,
			page_seo_keywords = ? ,
			page_seo_description = ? ,
			page_active	 = ?, 		
			page_main	 = ?,		
			page_sort	 = ?, 		
			page_auto_br	 = ?
			WHERE page_id = ?d
		";
        $bResult = $this->oDb->query(
            $sql, $oPage->getPid(), $oPage->getUrl(), $oPage->getUrlFull(), $oPage->getTitle(), $oPage->getText(),
            $oPage->getDateEdit(), $oPage->getSeoKeywords(), $oPage->getSeoDescription(), $oPage->getActive(),
            $oPage->getMain(), $oPage->getSort(), $oPage->getAutoBr(), $oPage->getId()
        );
        return $bResult !== false;
    }

    public function SetPagesPidToNull($aPageIds) {

        $sql
            = "UPDATE ?_page
			SET 
				page_pid = null,
				page_url_full = page_url
			{WHERE page_id IN (?a)}
		";
        $bResult = $this->oDb->query($sql, $aPageIds ? $aPageIds : DBSIMPLE_SKIP);
        return $bResult !== false;
    }

    public function GetPageByUrlFull($sUrlFull, $iActive) {

        $sql = "SELECT * FROM ?_page WHERE page_url_full = ? AND page_active = ?d ";
        if ($aRow = $this->oDb->selectRow($sql, $sUrlFull, $iActive)) {
            return E::GetEntity('Page', $aRow);
        }
        return null;
    }

    public function GetPageById($sId) {

        $sql = "SELECT * FROM ?_page WHERE page_id = ? ";
        if ($aRow = $this->oDb->selectRow($sql, $sId)) {
            return E::GetEntity('Page', $aRow);
        }
        return null;
    }

    public function deletePageById($aIds) {

        if (!is_array($aIds)) {
            $aIds = array($aIds);
        }
        $sql = "DELETE FROM ?_page WHERE page_id IN (?a) ";
        return $this->oDb->query($sql, $aIds) !== false;
    }

    public function GetPages($aFilter) {

        $sPidNULL = '';
        if (array_key_exists('pid', $aFilter) && is_null($aFilter['pid'])) {
            $sPidNULL = 'AND page_pid IS NULL';
        }
        $sql = "SELECT
					page_id as ARRAY_KEY,
					page_pid as PARENT_KEY,
					p.*
				FROM
					?_page AS p
				WHERE 
					1=1
					{ AND page_active = ?d }
					{ AND page_main = ?d }
					{ AND page_pid = ? } {$sPidNULL}
				ORDER BY page_sort DESC, page_id ASC;
					";
        $aRows = $this->oDb->select(
            $sql,
            isset($aFilter['active']) ? $aFilter['active'] : DBSIMPLE_SKIP,
            isset($aFilter['main']) ? $aFilter['main'] : DBSIMPLE_SKIP,
            (array_key_exists('pid', $aFilter) && !is_null($aFilter['pid'])) ? $aFilter['pid'] : DBSIMPLE_SKIP
        );

        return $aRows ? $aRows : array();
    }

    public function GetCountPage() {

        $sql = "SELECT count(*) as count FROM ?_page ";
        return intval($this->oDb->selectCell($sql));
    }

    public function GetPagesByPid($nPid) {

        $sql
            = "SELECT
					p.page_id AS ARRAY_KEY, p.*
				FROM 
					?_page AS p
				WHERE 
					page_pid = ?
                ORDER BY page_sort DESC, page_id ASC";
        $aResult = array();
        if ($aRows = $this->oDb->select($sql, $nPid)) {
            $aResult = E::GetEntityRows('Page', $aRows);
        }
        return $aResult;
    }

    public function GetNextPageBySort($iSort, $sPid, $sWay) {

        if ($sWay == 'up') {
            $sWay = '>';
            $sOrder = 'asc';
        } else {
            $sWay = '<';
            $sOrder = 'desc';
        }
        $sPidNULL = '';
        if (is_null($sPid)) {
            $sPidNULL = 'page_pid IS NULL AND';
        }
        $sql = "SELECT * FROM ?_page
                WHERE { page_pid = ? AND } {$sPidNULL} page_sort {$sWay} ? ORDER BY page_sort {$sOrder}, page_id ASC LIMIT 0,1";
        if ($aRow = $this->oDb->selectRow($sql, is_null($sPid) ? DBSIMPLE_SKIP : $sPid, $iSort)) {
            return E::GetEntity('Page', $aRow);
        }
        return null;
    }

    public function GetMaxSortByPid($sPid) {

        $sPidNULL = '';
        if (is_null($sPid)) {
            $sPidNULL = 'and page_pid IS NULL';
        }
        $sql = "SELECT max(page_sort) as max_sort FROM ?_page
                WHERE 1=1 { AND page_pid = ? } {$sPidNULL} ";
        if ($aRow = $this->oDb->selectRow($sql, is_null($sPid) ? DBSIMPLE_SKIP : $sPid)) {
            return $aRow['max_sort'];
        }
        return 0;
    }


    /**
     * List of active pages
     *
     * @param integer $iCount
     * @param integer $iCurrPage
     * @param integer $iPerPage
     *
     * @return array
     */
    public function getListOfActivePages(&$iCount, $iCurrPage, $iPerPage) {

        $sql
            = 'SELECT
                    `page`.*
                FROM
                    ?_page AS `page`
                WHERE
                    `page`.`page_active` = 1
                ORDER BY
                    page_sort DESC, `page`.`page_id` ASC
                LIMIT
                    ?d, ?d
                ';
        $aResult = array();
        if ($aRows = $this->oDb->selectPage($iCount, $sql, ($iCurrPage - 1) * $iPerPage, $iPerPage)) {
            $aResult = E::GetEntityRows('Page', $aRows);
        }
        return $aResult;
    }

    /**
     * Count of active pages
     *
     * @return integer
     */
    public function getCountOfActivePages() {

        $sql
            = 'SELECT
                    COUNT(`page`.`page_id`)
                FROM
                    ?_page AS `page`
                WHERE
                    `page`.`page_active` = 1
                ';

        return $this->oDb->selectCell($sql);
    }

    public function ReSort() {

        $sql = "SELECT page_id, page_sort FROM ?_page ORDER BY page_sort DESC, page_id ASC";
        $aRows = $this->oDb->select($sql);
        if ($aRows) {
            $aRows = array_reverse($aRows);
            foreach ($aRows as $nKey => $aRow) {
                $sql = "UPDATE ?_page SET page_sort={$nKey} WHERE page_id={$aRow['page_id']}";
                $this->oDb->query($sql);
            }
        }
    }

}

// EOF