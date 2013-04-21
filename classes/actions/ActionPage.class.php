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
 * @package actions
 * @since 0.9
 */

class ActionPage extends Action {

	public function Init() {
	}
	/**
	 * Регистрируем евенты
	 *
	 */
	protected function RegisterEvent() {
		$this->AddEventPreg('/^[\w\-\_]*$/i','EventShowPage');
	}


	/**********************************************************************************
	 ************************ РЕАЛИЗАЦИЯ ЭКШЕНА ***************************************
	 **********************************************************************************
	 */

	/**
	 * Отображение страницы
	 *
	 * @return unknown
	 */
	protected function EventShowPage() {
		if (!$this->sCurrentEvent) {
			/**
			 * Показывает дефолтную страницу
			 */
			//а это какая страница?
		}
		/**
		 * Составляем полный URL страницы для поиска по нему в БД
		 */
		$sUrlFull=join('/',$this->GetParams());
		if ($sUrlFull!='') {
			$sUrlFull=$this->sCurrentEvent.'/'.$sUrlFull;
		} else {
			$sUrlFull=$this->sCurrentEvent;
		}
		/**
		 * Ищем страничку в БД
		 */
		if (!($oPage=$this->Page_GetPageByUrlFull($sUrlFull,1))) {
			return $this->EventNotFound();
		}
		/**
		 * Заполняем HTML теги и SEO
		 */
		$this->Viewer_AddHtmlTitle($oPage->getTitle());
		if ($oPage->getSeoKeywords()) {
			$this->Viewer_SetHtmlKeywords($oPage->getSeoKeywords());
		}
		if ($oPage->getSeoDescription()) {
			$this->Viewer_SetHtmlDescription($oPage->getSeoDescription());
		}

		$this->Viewer_Assign('oPage',$oPage);
		/**
		 * Устанавливаем шаблон для вывода
		 */
		$this->SetTemplateAction('page');
	}

	
	
}
?>