<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Version: 0.9a
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

/**
 * @package plugin Categories
 * @since 0.9.2
 */

class PluginCategories_ActionCategory extends ActionPlugin {
    public function Init() {
        $this->SetDefaultEvent('index');
    }

    /**
     * Регистрация евентов
     */
    protected function RegisterEvent() {
        $this->AddEvent('index', 'EventIndex');
		$this->AddEventPreg('/^[\w\-\_]+$/i','/^(page([1-9]\d{0,5}))?$/i',array('EventCategoryList','list'));
    }

    protected function EventIndex() {
		
        $this->SetTemplateAction('index');

		$aCategories=$this->PluginCategories_Categories_GetItemsByFilter(array(),'PluginCategories_ModuleCategories_EntityCategory');
		$this->Viewer_Assign("aCategories",$aCategories);
    }

	protected function EventCategoryList() {
		
        $this->SetTemplateAction('list');

		$sCatUrl=$this->sCurrentEvent;
		/**
		 * Проверяем есть ли категория с таким УРЛ
		 */
		if (!($oCategory=$this->PluginCategories_Categories_GetByFilter(array('category_url'=>$sCatUrl),'PluginCategories_ModuleCategories_EntityCategory'))) {
			return parent::EventNotFound();
		}
		/**
		 * Есть ли подключенные к категории блоги
		 */
		if(!($aIds=$oCategory->getBlogIds())){
			return parent::EventNotFound();
		}

		/**
		 * Передан ли номер страницы
		 */
		$iPage=$this->GetParamEventMatch(0,2) ? $this->GetParamEventMatch(0,2) : 1;
		/**
		 * Устанавливаем основной URL для поисковиков
		 */
		if ($iPage==1 && Config::Get('router.config.homepage')=='category') {
			$this->Viewer_SetHtmlCanonical(Config::Get('path.root.web').'/');
		}

		$aFilter=array(
			'blog_type' => array(
				'open',
			),
			'topic_publish' => 1,
			'blog_id'=>$aIds
		);
		
		/**
		 * Получаем список топиков
		 */
		$aResult=$this->Topic_GetTopicsByFilter($aFilter,$iPage,Config::Get('module.topic.per_page'));

		$aTopics=$aResult['collection'];
		/**
		 * Формируем постраничность
		 */
		$aPaging=$this->Viewer_MakePaging($aResult['count'],$iPage,Config::Get('module.topic.per_page'),Config::Get('pagination.pages.count'),rtrim($oCategory->getUrl(),'/'));

		/**
		 * Загружаем переменные в шаблон
		 */
		$this->Viewer_Assign('aPaging',$aPaging);
		$this->Viewer_Assign('aTopics',$aTopics);
		$this->Viewer_Assign('oCategory',$oCategory);


    }

}

// EOF