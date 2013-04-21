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
 * @since 0.9.5
 */

class PluginCategories_ActionAdmin extends PluginCategories_Inherits_ActionAdmin {
	protected function RegisterEvent() {
		parent::RegisterEvent();
	    $this->AddEvent('categories', 'EventCategories');
        $this->AddEvent('categoriesadd', 'EventCategoriesAdd');
        $this->AddEvent('categoriesedit', 'EventCategoriesEdit');
	}
	
    // Установка собственного обработчика главной страницы
    protected function _eventConfigLinks() {
        if (($sHomePage = $this->GetPost('homepage')) && ($sHomePage == 'category_homepage')) {
            $aConfig = array(
                'router.config.action_default' => 'homepage',
                'router.config.homepage' => 'category/index',
                'router.config.homepage_select' => 'category_homepage',
            );
            Config::WriteCustomConfig($aConfig);
            Router::Location('admin/config/links');
            exit;
        }
        return parent::_eventConfigLinks();
    }

	protected function EventCategories() {
        $this->_setTitle($this->Lang_Get('plugin.categories.menu_config_categories'));
        $this->SetTemplateAction('categories/list');
        /*
         * Получаем список
         */
        $aFilter = array();
        $aCategories = $this->PluginCategories_Categories_GetItemsByFilter($aFilter, 'PluginCategories_ModuleCategories_EntityCategory');
        $this->Viewer_Assign('aCategories', $aCategories);

        if (getRequest('add')) {
            $this->Message_AddNoticeSingle($this->Lang_Get('plugin.categories.add_success'));
        }

        if (getRequest('edit')) {
            $this->Message_AddNoticeSingle($this->Lang_Get('plugin.categories.edit_success'));
        }
    }

    protected function EventCategoriesAdd() {
        $this->_setTitle($this->Lang_Get('plugin.categories.add_title'));
        $this->SetTemplateAction('categories/add');

        /**
         * Вызов хуков
         */
        $this->Hook_Run('admin_categories_add_show');
        /**
         * Загружаем переменные в шаблон
         */
        $this->Viewer_AddHtmlTitle($this->Lang_Get('plugin.categories.add_title'));

		$aResult=$this->Blog_GetBlogsByFilter(array('type'=>'open'),array('blog_rating'=>'desc'),1,PHP_INT_MAX);
		$aBlogsCollective=$aResult['collection'];
		$this->Viewer_Assign("aBlogsCollective",$aBlogsCollective);

        /**
         * Обрабатываем отправку формы
         */
        return $this->SubmitCategoriesAdd();

    }

    protected function SubmitCategoriesAdd() {
        /**
         * Проверяем отправлена ли форма с данными
         */
        if (!isPost('submit_category_add')) {
            return false;
        }

        /**
         * Проверка корректности полей формы
         */
        if (!$this->CheckCategoryFields()) {
            return false;
        }

        $oCategory = Engine::GetEntity('PluginCategories_ModuleCategories_EntityCategory');
        $oCategory->setCategoryTitle(getRequest('category_title'));
        $oCategory->setCategoryUrl(getRequest('category_url'));
		
        if ($oCategory->Add()) {
			$this->AddRelations($oCategory);
            Router::Location('admin/categories/?add=success');
        }


    }

    protected function EventCategoriesEdit() {

        /**
         * Получаем категорию
         */
        if (!$this->GetParam(0) || !$oCategory = $this->PluginCategories_Categories_GetByFilter(array('category_id'=>$this->GetParam(0)), 'PluginCategories_ModuleCategories_EntityCategory')) {
            return parent::EventNotFound();
        }
        $this->Viewer_Assign('oCategory', $oCategory);

        /**
         * Устанавливаем шаблон вывода
         */
        $this->_setTitle($this->Lang_Get('plugin.categories.edit_title'));
        $this->SetTemplateAction('categories/add');


		$aResult=$this->Blog_GetBlogsByFilter(array('type'=>'open'),array('blog_rating'=>'desc'),1,PHP_INT_MAX);
		$aBlogsCollective=$aResult['collection'];
		$this->Viewer_Assign("aBlogsCollective",$aBlogsCollective);

        /**
         * Проверяем отправлена ли форма с данными
         */
        if (isset($_REQUEST['submit_category_add'])) {
            /**
             * Обрабатываем отправку формы
             */
            return $this->SubmitCategoriesEdit($oCategory);
        } else {
            $_REQUEST['category_id'] = $oCategory->getCategoryId();
            $_REQUEST['category_title'] = $oCategory->getCategoryTitle();
            $_REQUEST['category_url'] = $oCategory->getCategoryUrl();

			$aRelations=$this->PluginCategories_Categories_GetItemsByFilter(array('category_id'=>$this->GetParam(0)),'PluginCategories_ModuleCategories_EntityCategoryRel');
			foreach($aRelations as $oRel){
				$_REQUEST['blog'][$oRel->getBlogId()]=$oRel->getBlogId();
			}
        }

    }

    protected function SubmitCategoriesEdit($oCategory) {
        /**
         * Проверяем отправлена ли форма с данными
         */
        if (!isPost('submit_category_add')) {
            return false;
        }

        /**
         * Проверка корректности полей формы
         */
        if (!$this->CheckCategoryFields()) {
            return false;
        }
		/*
		 * Обновляем данные
		 */
        $oCategory->setCategoryTitle(getRequest('category_title'));
        $oCategory->setCategoryUrl(getRequest('category_url'));

        if ($oCategory->Update()) {
			$this->AddRelations($oCategory);

            Router::Location(Router::GetPath('admin') . 'categories/?edit=success');
        }
    }

	protected function AddRelations($oCategory) {
		/*
		 * Чистим связи и ставим новые
		 */
		$aRelations=$this->PluginCategories_Categories_GetItemsByFilter(array('category_id'=>$oCategory->getCategoryId()),'PluginCategories_ModuleCategories_EntityCategoryRel');
		foreach($aRelations as $oRel){
		   $oRel->Delete();
		}
		$aBlogsRel=getRequest('blog');
		if(is_array($aBlogsRel)){
		   foreach($aBlogsRel as $k=>$v){
			   if(func_check($k, 'id')){
				   $oRel = Engine::GetEntity('PluginCategories_ModuleCategories_EntityCategoryRel');
				   $oRel->setCategoryId($oCategory->getCategoryId());
				   $oRel->setBlogId($k);
				   $oRel->Add();
			   }
		   }
		}
	}

	protected function CheckCategoryFields() {

        $this->Security_ValidateSendForm();

        $bOk = true;

        if (!func_check(getRequest('category_title', null, 'post'), 'text', 2, 200)) {
            $this->Message_AddError($this->Lang_Get('plugin.categories.category_title_error'), $this->Lang_Get('error'));
            $bOk = false;
        }
        if (!func_check(getRequest('category_url', null, 'post'), 'login', 2, 50)) {
            $this->Message_AddError($this->Lang_Get('plugin.categories.category_url_error'), $this->Lang_Get('error'));
            $bOk = false;
        }


        return $bOk;
    }
}

// EOF