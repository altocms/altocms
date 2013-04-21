<?php
/*-------------------------------------------------------
*
*   LiveStreet Engine Social Networking
*   Copyright © 2008 Mzhelskiy Maxim
*
*--------------------------------------------------------
*
*   Official site: www.livestreet.ru
*   Contact e-mail: rus.engine@gmail.com
*
*   GNU General Public License, version 2:
*   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*
---------------------------------------------------------
*/

/**
 * Модуль для статических страниц
 *
 * @package modules.page
 * @since 1.0
 */
class ModulePage extends Module {
	/** @var ModulePage_MapperPage */
	protected $oMapper;
	protected $aRebuildIds=array();

	/**
	 * Инициализация
	 *
	 */
	public function Init() {
		$this->oMapper=Engine::GetMapper(__CLASS__);
	}
	/**
	 * Добавляет страницу
	 *
	 * @param ModulePage_EntityPage $oPage
	 * @return bool
	 */
	public function AddPage(ModulePage_EntityPage $oPage) {
		if ($sId=$this->oMapper->AddPage($oPage)) {
			$oPage->setId($sId);
			//чистим зависимые кеши
			$this->Cache_Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG,array('page_change',"page_change_{$oPage->getId()}","page_change_urlfull_{$oPage->getUrlFull()}"));
			return true;
		}
		return false;
	}
	/**
	 * Обновляет страницу
	 *
	 * @param ModulePage_EntityPage $oPage
	 * @return bool
	 */
	public function UpdatePage(ModulePage_EntityPage $oPage) {
		if ($this->oMapper->UpdatePage($oPage)) {
			//чистим зависимые кеши
			$this->Cache_Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG,array('page_change',"page_change_{$oPage->getId()}","page_change_urlfull_{$oPage->getUrlFull()}"));
			return true;
		}
		return false;
	}
	/**
	 * Получает страницу по полному УРЛу
	 *
	 * @param string $sUrlFull
	 */
	public function GetPageByUrlFull($sUrlFull,$iActive=1) {
		if (false === ($data = $this->Cache_Get("page_{$sUrlFull}_{$iActive}"))) {
			$data = $this->oMapper->GetPageByUrlFull($sUrlFull,$iActive);
			if ($data) {
				$this->Cache_Set($data, "page_{$sUrlFull}_{$iActive}", array("page_change_{$data->getId()}"), 60*60*24*5);
			} else {
				$this->Cache_Set($data, "page_{$sUrlFull}_{$iActive}", array("page_change_urlfull_{$sUrlFull}"), 60*60*24*5);
			}
		}
		return $data;
	}
	/**
	 * Получает страницу по её айдишнику
	 *
	 * @param int $sId
	 * @return object
	 */
	public function GetPageById($sId) {
		return $this->oMapper->GetPageById($sId);
	}
	/**
	 * Получает список всех страниц ввиде дерева
	 *
	 * @return array
	 */
	public function GetPages($aFilter=array()) {
		$aPages=array();
		$aPagesRow=$this->oMapper->GetPages($aFilter);
		if (count($aPagesRow)) {
			$aPages=$this->BuildPagesRecursive($aPagesRow);
		}
		return $aPages;
	}
	/**
	 * Строит дерево страниц
	 *
	 * @param array $aPages
	 * @param bool $bBegin
	 * @return array
	 */
	protected function BuildPagesRecursive($aPages,$bBegin=true) {
		static $aResultPages;
		static $iLevel;
		if ($bBegin) {
			$aResultPages=array();
			$iLevel=0;
		}
		foreach ($aPages as $aPage) {
			$aTemp=$aPage;
			$aTemp['level']=$iLevel;
			unset($aTemp['childNodes']);
			$aResultPages[]=Engine::GetEntity('Page',$aTemp);
			if (isset($aPage['childNodes']) and count($aPage['childNodes'])>0) {
				$iLevel++;
				$this->BuildPagesRecursive($aPage['childNodes'],false);
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
		$aPages=$this->GetPagesByPid($oPageStart->getId());
		foreach ($aPages as $oPage) {
			if ($oPage->getId()==$oPageStart->getId()) {
				continue;
			}
			if (in_array($oPage->getId(),$this->aRebuildIds)) {
				continue;
			}
			$this->aRebuildIds[]=$oPage->getId();
			$oPage->setUrlFull($oPageStart->getUrlFull().'/'.$oPage->getUrl());
			$this->UpdatePage($oPage);
			$this->RebuildUrlFull($oPage);
		}
	}
	/**
	 * Получает список дочерних страниц первого уровня
	 *
	 * @param string $sPid
	 * @return array
	 */
	public function GetPagesByPid($sPid) {
		return $this->oMapper->GetPagesByPid($sPid);
	}
	/**
	 * Удаляет страницу по её айдишнику
	 * Если тип таблиц БД InnoDB, то удалятся и все дочернии страницы
	 *
	 * @param int $sId
	 * @return bool
	 */
	public function deletePageById($sId) {
		if ($this->oMapper->deletePageById($sId)) {
			//чистим зависимые кеши
			$this->Cache_Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG,array('page_change',"page_change_{$sId}"));
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
	 * Устанавливает ВСЕМ страницам PID = NULL
	 * Это бывает нужно, когда особо "умный" админ зациклит страницы сами на себя..
	 *
	 * @return bool
	 */
	public function SetPagesPidToNull() {
		return $this->oMapper->SetPagesPidToNull();
	}
	/**
	 * Получает слеудующую по сортировке страницу
	 *
	 * @param int $iSort
	 * @param string $sWay
	 * @return ModulePage_EntityPage
	 */
	public function GetNextPageBySort($iSort,$sPid,$sWay='up') {
		return $this->oMapper->GetNextPageBySort($iSort,$sPid,$sWay);
	}
	/**
	 * Получает значение максимальной сртировки
	 *
	 * @return int
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
	 * @return array
	 */
	public function getListOfActivePages(&$iCount, $iCurrPage, $iPerPage) {
		return $this->oMapper->getListOfActivePages($iCount, $iCurrPage, Config::Get('plugin.sitemap.objects_per_page'));
	}

}
?>