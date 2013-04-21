<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Version: 0.9a
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
 * Экшен обработки УРЛа вида /content/ - управление своими топиками
 *
 * @package actions
 * @since 1.0
 */
class ActionContent extends Action {
	/**
	 * Главное меню
	 *
	 * @var string
	 */
	protected $sMenuHeadItemSelect='blog';
	/**
	 * Меню
	 *
	 * @var string
	 */
	protected $sMenuItemSelect='topic';
	/**
	 * СубМеню
	 *
	 * @var string
	 */
	protected $sMenuSubItemSelect='topic';
	/**
	 * Текущий юзер
	 *
	 * @var ModuleUser_EntityUser|null
	 */
	protected $oUserCurrent=null;
	/**
	 * Текущий тип контента
	 *
	 * @var ModuleTopic_EntityContent|null
	 */
	protected $oType=null;

	/**
	 * Инициализация
	 *
	 */
	public function Init() {
		/**
		 * Проверяем авторизован ли юзер
		 */
		if (!$this->User_IsAuthorization()) {
			return parent::EventNotFound();
		}
		$this->oUserCurrent=$this->User_GetUserCurrent();
		/**
		 * Устанавливаем дефолтный эвент
		 */
		$this->SetDefaultEvent('add');
		/**
		 * Устанавливаем title страницы
		 */
		//$this->Viewer_AddHtmlTitle($this->Lang_Get('topic_title'));

		/**
		 * Загружаем в шаблон JS текстовки
		 */
		$this->Lang_AddLangJs(array(
								  'topic_photoset_photo_delete','topic_photoset_mark_as_preview','topic_photoset_photo_delete_confirm',
								  'topic_photoset_is_preview','topic_photoset_upload_choose'
							  ));
	}
	/**
	 * Регистрируем евенты
	 *
	 */
	protected function RegisterEvent() {

		$this->AddEventPreg('/^published$/i','/^(page([1-9]\d{0,5}))?$/i','EventShowTopics');
		$this->AddEventPreg('/^saved$/i','/^(page([1-9]\d{0,5}))?$/i','EventShowTopics');
		$this->AddEvent('edit','EventEdit');
		$this->AddEvent('delete','EventDelete');

		//Фото
		$this->AddEvent('deleteimage','EventDeletePhoto'); // Удаление изображения
		$this->AddEvent('upload','EventUpload'); // Загрузка изображения
		$this->AddEvent('getMore','EventGetMore');	// Загрузка изображения на сервер
		$this->AddEvent('setimagedescription','EventSetPhotoDescription'); // Установка описания к фото

		//Переход для топика с оригиналом
		$this->AddEvent('go','EventGo');
		
		$this->AddEventPreg('/^[\w\-\_]+$/i','/^add$/i', array('EventAdd', 'add'));
	}


	/**********************************************************************************
	 ************************ РЕАЛИЗАЦИЯ ЭКШЕНА ***************************************
	 **********************************************************************************
	 */
	
	/**
	 * Редактирование топика
	 *
	 */
	protected function EventEdit() {
		/**
		 * Получаем номер топика из УРЛ и проверяем существует ли он
		 */
		$sTopicId=$this->GetParam(0);
		if (!($oTopic=$this->Topic_GetTopicById($sTopicId))) {
			return parent::EventNotFound();
		}
		/*
		 * Получаем тип контента
		 */
		if(!$this->oType=$this->Topic_GetContentTypeByUrl($oTopic->getType())){
			return parent::EventNotFound();
		}

		$this->Viewer_Assign('oType',$this->oType);
		/**
		 * Если права на редактирование
		 */
		if (!$this->ACL_IsAllowEditTopic($oTopic,$this->oUserCurrent)) {
			return parent::EventNotFound();
		}
		/**
		 * Вызов хуков
		 */
		$this->Hook_Run('topic_edit_show',array('oTopic'=>$oTopic));
		/**
		 * Загружаем переменные в шаблон
		 */
		$this->Viewer_Assign('aBlogsAllow',$this->Blog_GetBlogsAllowByUser($this->oUserCurrent));
		$this->Viewer_Assign('bEditDisabled',$oTopic->getQuestionCountVote()==0 ? false : true);
		$this->Viewer_AddHtmlTitle($this->Lang_Get('topic_topic_edit'));
		/**
		 * Устанавливаем шаблон вывода
		 */
		$this->SetTemplateAction('add');
		/**
		 * Проверяем отправлена ли форма с данными(хотяб одна кнопка)
		 */
		if (isset($_REQUEST['submit_topic_publish']) or isset($_REQUEST['submit_topic_save'])) {
			/**
			 * Обрабатываем отправку формы
			 */
			return $this->SubmitEdit($oTopic);
		} else {
			/**
			 * Заполняем поля формы для редактирования
			 * Только перед отправкой формы!
			 */
			$_REQUEST['topic_title']=$oTopic->getTitle();
			$_REQUEST['topic_text']=$oTopic->getTextSource();
			$_REQUEST['topic_link_url']=$oTopic->getLinkUrl();
			$_REQUEST['topic_tags']=$oTopic->getTags();
			$_REQUEST['blog_id']=$oTopic->getBlogId();
			$_REQUEST['topic_id']=$oTopic->getId();
			$_REQUEST['topic_publish_index']=$oTopic->getPublishIndex();
			$_REQUEST['topic_forbid_comment']=$oTopic->getForbidComment();
			$_REQUEST['topic_main_photo']=$oTopic->getPhotosetMainPhotoId();

			$_REQUEST['question_title']=$oTopic->getQuestionTitle();
			$_REQUEST['answer']=array();
			$aAnswers=$oTopic->getQuestionAnswers();
			foreach ($aAnswers as $aAnswer) {
				$_REQUEST['answer'][]=$aAnswer['text'];
			}

			foreach($this->oType->getFields() as $oField){
				if($oTopic->getField($oField->getFieldId())){
					$sValue=$oTopic->getField($oField->getFieldId())->getValueSource();
					if($oField->getFieldType()=='file'){
						$sValue=unserialize($sValue);
					}
					$_REQUEST['fields'][$oField->getFieldId()]=$sValue;
				}
			}
		}
		$this->Viewer_Assign('aPhotos', $this->Topic_getPhotosByTopicId($oTopic->getId()));
	}
	/**
	 * Удаление топика
	 *
	 */
	protected function EventDelete() {
		$this->Security_ValidateSendForm();
		/**
		 * Получаем номер топика из УРЛ и проверяем существует ли он
		 */
		$sTopicId=$this->GetParam(0);
		if (!($oTopic=$this->Topic_GetTopicById($sTopicId))) {
			return parent::EventNotFound();
		}
		/**
		 * проверяем есть ли право на удаление топика
		 */
		if (!$this->ACL_IsAllowDeleteTopic($oTopic,$this->oUserCurrent)) {
			return parent::EventNotFound();
		}
		/**
		 * Удаляем топик
		 */
		$this->Hook_Run('topic_delete_before', array('oTopic'=>$oTopic));
		$this->Topic_DeleteTopic($oTopic);
		$this->Hook_Run('topic_delete_after', array('oTopic'=>$oTopic));
		/**
		 * Перенаправляем на страницу со списком топиков из блога этого топика
		 */
		Router::Location($oTopic->getBlog()->getUrlFull());
	}
	/**
	 * Добавление топика
	 *
	 */
	protected function EventAdd() {

		/**
		 * Устанавливаем шаблон вывода
		 */
		$this->SetTemplateAction('add');
		
		/**
		 * Вызов хуков
		 */
		$this->Hook_Run('topic_add_show');

		/*
		 * Получаем тип контента
		 */
		if(!$this->oType=$this->Topic_GetContentTypeByUrl($this->sCurrentEvent)){
			return parent::EventNotFound();
		}
		
		$this->Viewer_Assign('oType',$this->oType);
		
		/**
		 * Загружаем переменные в шаблон
		 */
		$this->Viewer_Assign('aBlogsAllow',$this->Blog_GetBlogsAllowByUser($this->oUserCurrent));
		$this->Viewer_Assign('bEditDisabled',false);
		$this->Viewer_AddHtmlTitle($this->Lang_Get('topic_topic_create').' '.mb_strtolower($this->oType->getContentTitle()));
		if (!is_numeric(getRequest('topic_id'))) {
			$_REQUEST['topic_id']='';
		}
		/**
		 * Если нет временного ключа для нового топика, то генерируем. если есть, то загружаем фото по этому ключу
		 */
		if (empty($_COOKIE['ls_photoset_target_tmp'])) {
			setcookie('ls_photoset_target_tmp',  func_generator(), time()+24*3600,Config::Get('sys.cookie.path'),Config::Get('sys.cookie.host'));
		} else {
			setcookie('ls_photoset_target_tmp', $_COOKIE['ls_photoset_target_tmp'], time()+24*3600,Config::Get('sys.cookie.path'),Config::Get('sys.cookie.host'));
			$this->Viewer_Assign('aPhotos', $this->Topic_getPhotosByTargetTmp($_COOKIE['ls_photoset_target_tmp']));
		}
		/**
		 * Обрабатываем отправку формы
		 */
		return $this->SubmitAdd();
	}
	/**
	 * Выводит список топиков
	 *
	 */
	protected function EventShowTopics() {
		/**
		 * Меню
		 */
		$this->sMenuSubItemSelect=$this->sCurrentEvent;
		/**
		 * Передан ли номер страницы
		 */
		$iPage=$this->GetParamEventMatch(0,2) ? $this->GetParamEventMatch(0,2) : 1;
		/**
		 * Получаем список топиков
		 */
		$aResult=$this->Topic_GetTopicsPersonalByUser($this->oUserCurrent->getId(),$this->sCurrentEvent=='published' ? 1 : 0,$iPage,Config::Get('module.topic.per_page'));
		$aTopics=$aResult['collection'];
		/**
		 * Формируем постраничность
		 */
		$aPaging=$this->Viewer_MakePaging($aResult['count'],$iPage,Config::Get('module.topic.per_page'),Config::Get('pagination.pages.count'),Router::GetPath('content').$this->sCurrentEvent);
		/**
		 * Загружаем переменные в шаблон
		 */
		$this->Viewer_Assign('aPaging',$aPaging);
		$this->Viewer_Assign('aTopics',$aTopics);
		$this->Viewer_AddHtmlTitle($this->Lang_Get('topic_menu_'.$this->sCurrentEvent));
	}
	/**
	 * Обработка добавления топика
	 *
	 */
	protected function SubmitAdd() {
		/**
		 * Проверяем отправлена ли форма с данными(хотяб одна кнопка)
		 */
		if (!isPost('submit_topic_publish') and !isPost('submit_topic_save')) {
			return false;
		}
		$oTopic=Engine::GetEntity('Topic');
		$oTopic->_setValidateScenario('topic');
		/**
		 * Заполняем поля для валидации
		 */
		$oTopic->setBlogId(getRequestStr('blog_id'));
		$oTopic->setTitle(strip_tags(getRequestStr('topic_title')));
		$oTopic->setTextSource(getRequestStr('topic_text'));
		$oTopic->setTags(getRequestStr('topic_tags'));
		$oTopic->setUserId($this->oUserCurrent->getId());
		$oTopic->setType($this->oType->getContentUrl());
		if ($this->oType->isAllow('link')){
			$oTopic->setLinkUrl(getRequestStr('topic_link_url'));
		}
		$oTopic->setDateAdd(date("Y-m-d H:i:s"));
		$oTopic->setUserIp(func_getIp());
		/**
		 * Проверка корректности полей формы
		 */
		if (!$this->checkTopicFields($oTopic)) {
			return false;
		}
		/**
		 * Определяем в какой блог делаем запись
		 */
		$iBlogId=$oTopic->getBlogId();
		if ($iBlogId==0) {
			$oBlog=$this->Blog_GetPersonalBlogByUserId($this->oUserCurrent->getId());
		} else {
			$oBlog=$this->Blog_GetBlogById($iBlogId);
		}
		/**
		 * Если блог не определен выдаем предупреждение
		 */
		if (!$oBlog) {
			$this->Message_AddErrorSingle($this->Lang_Get('topic_create_blog_error_unknown'),$this->Lang_Get('error'));
			return false;
		}
		/**
		 * Проверяем права на постинг в блог
		 */
		if (!$this->ACL_IsAllowBlog($oBlog,$this->oUserCurrent)) {
			$this->Message_AddErrorSingle($this->Lang_Get('topic_create_blog_error_noallow'),$this->Lang_Get('error'));
			return false;
		}
		/**
		 * Проверяем разрешено ли постить топик по времени
		 */
		if (isPost('submit_topic_publish') and !$this->ACL_CanPostTopicTime($this->oUserCurrent)) {
			$this->Message_AddErrorSingle($this->Lang_Get('topic_time_limit'),$this->Lang_Get('error'));
			return;
		}
		/**
		 * Теперь можно смело добавлять топик к блогу
		 */
		$oTopic->setBlogId($oBlog->getId());
		/**
		 * Получаемый и устанавливаем разрезанный текст по тегу <cut>
		 */
		list($sTextShort,$sTextNew,$sTextCut) = $this->Text_Cut($oTopic->getTextSource());

		$oTopic->setCutText($sTextCut);
		$oTopic->setText($this->Text_Parser($sTextNew));
		$oTopic->setTextShort($this->Text_Parser($sTextShort));

		/**
		 * Варианты ответов
		 */
		if($this->oType->isAllow('question') && getRequestStr('question_title') && getRequest('answer',array())){
			$oTopic->setQuestionTitle(strip_tags(getRequestStr('question_title')));
			$oTopic->clearQuestionAnswer();
			foreach (getRequest('answer',array()) as $sAnswer) {
				$oTopic->addQuestionAnswer((string)$sAnswer);
			}
		}

		/*
		 * Если есть прикрепленные фото
		 */
		if($this->oType->isAllow('photoset') && $sTargetTmp=$_COOKIE['ls_photoset_target_tmp']){
			if($aPhotos = $this->Topic_getPhotosByTargetTmp($sTargetTmp)){
				if (!($oPhotoMain=$this->Topic_getTopicPhotoById(getRequestStr('topic_main_photo')) and $oPhotoMain->getTargetTmp()==$sTargetTmp)) {
					$oPhotoMain=$aPhotos[0];
				}
				if($oPhotoMain){
					$oTopic->setPhotosetMainPhotoId($oPhotoMain->getId());
					$oTopic->setPhotosetCount(count($aPhotos));
				}
			}
		}

		/**
		 * Публикуем или сохраняем
		 */
		if (isset($_REQUEST['submit_topic_publish'])) {
			$oTopic->setPublish(1);
			$oTopic->setPublishDraft(1);
		} else {
			$oTopic->setPublish(0);
			$oTopic->setPublishDraft(0);
		}
		/**
		 * Принудительный вывод на главную
		 */
		$oTopic->setPublishIndex(0);
		if ($this->ACL_IsAllowPublishIndex($this->oUserCurrent))	{
			if (getRequest('topic_publish_index')) {
				$oTopic->setPublishIndex(1);
			}
		}
		/**
		 * Запрет на комментарии к топику
		 */
		$oTopic->setForbidComment(0);
		if (getRequest('topic_forbid_comment')) {
			$oTopic->setForbidComment(1);
		}
		/**
		 * Запускаем выполнение хуков
		 */
		$this->Hook_Run('topic_add_before', array('oTopic'=>$oTopic,'oBlog'=>$oBlog));
		/**
		 * Добавляем топик
		 */
		if ($this->Topic_AddTopic($oTopic)) {
			$this->Hook_Run('topic_add_after', array('oTopic'=>$oTopic,'oBlog'=>$oBlog));
			/**
			 * Получаем топик, чтоб подцепить связанные данные
			 */
			$oTopic=$this->Topic_GetTopicById($oTopic->getId());

			/*
			 * Заполняем дополнительные поля
			 */
			$this->processFields($oTopic);
			/**
			 * Обновляем количество топиков в блоге
			 */
			$this->Blog_RecalculateCountTopicByBlogId($oTopic->getBlogId());
			/**
			 * Добавляем автора топика в подписчики на новые комментарии к этому топику
			 */
			$this->Subscribe_AddSubscribeSimple('topic_new_comment',$oTopic->getId(),$this->oUserCurrent->getMail(),$this->oUserCurrent->getId());
			/**
			 * Подписываем автора топика на обновления в трекере
			 */
			if ($oTrack=$this->Subscribe_AddTrackSimple('topic_new_comment',$oTopic->getId(),$this->oUserCurrent->getId())) {
				//если пользователь не отписался от обновлений топика
				if(!$oTrack->getStatus()){
					$oTrack->setStatus(1);
					$this->Subscribe_UpdateTrack($oTrack);
				}
			}
			/**
			 * Делаем рассылку спама всем, кто состоит в этом блоге
			 */
			if ($oTopic->getPublish()==1 and $oBlog->getType()!='personal') {
				$this->Topic_SendNotifyTopicNew($oBlog,$oTopic,$this->oUserCurrent);
			}
			/**
			 * Привязываем фото к id топика
			 * здесь нужно это делать одним запросом, а не перебором сущностей
			 */
			if (isset($aPhotos) && count($aPhotos)) {
				foreach($aPhotos as $oPhoto) {
					$oPhoto->setTargetTmp(null);
					$oPhoto->setTopicId($oTopic->getId());
					$this->Topic_updateTopicPhoto($oPhoto);
				}
			}
			/**
			 * Удаляем временную куку
			 */
			setcookie('ls_photoset_target_tmp', null);
			/**
			 * Добавляем событие в ленту
			 */
			$this->Stream_write($oTopic->getUserId(), 'add_topic', $oTopic->getId(),$oTopic->getPublish() && $oBlog->getType()!='close');
			Router::Location($oTopic->getUrl());
		} else {
			$this->Message_AddErrorSingle($this->Lang_Get('system_error'));
			return Router::Action('error');
		}
	}
	/**
	 * Обработка редактирования топика
	 *
	 * @param ModuleTopic_EntityTopic $oTopic
	 * @return mixed
	 */
	protected function SubmitEdit($oTopic) {
		$oTopic->_setValidateScenario('topic');
		/**
		 * Сохраняем старое значение идентификатора блога
		 */
		$sBlogIdOld = $oTopic->getBlogId();
		/**
		 * Заполняем поля для валидации
		 */
		$oTopic->setBlogId(getRequestStr('blog_id'));
		$oTopic->setTitle(strip_tags(getRequestStr('topic_title')));
		$oTopic->setTextSource(getRequestStr('topic_text'));
		if($this->oType->isAllow('link')){
			$oTopic->setLinkUrl(getRequestStr('topic_link_url'));
		}
		$oTopic->setTags(getRequestStr('topic_tags'));
		$oTopic->setUserIp(func_getIp());
		/**
		 * Проверка корректности полей формы
		 */
		if (!$this->checkTopicFields($oTopic)) {
			return false;
		}
		/**
		 * Определяем в какой блог делаем запись
		 */
		$iBlogId=$oTopic->getBlogId();
		if ($iBlogId==0) {
			$oBlog=$this->Blog_GetPersonalBlogByUserId($oTopic->getUserId());
		} else {
			$oBlog=$this->Blog_GetBlogById($iBlogId);
		}
		/**
		 * Если блог не определен выдаем предупреждение
		 */
		if (!$oBlog) {
			$this->Message_AddErrorSingle($this->Lang_Get('topic_create_blog_error_unknown'),$this->Lang_Get('error'));
			return false;
		}
		/**
		 * Проверяем права на постинг в блог
		 */
		if (!$this->ACL_IsAllowBlog($oBlog,$this->oUserCurrent)) {
			$this->Message_AddErrorSingle($this->Lang_Get('topic_create_blog_error_noallow'),$this->Lang_Get('error'));
			return false;
		}
		/**
		 * Проверяем разрешено ли постить топик по времени
		 */
		if (isPost('submit_topic_publish') and !$oTopic->getPublishDraft() and !$this->ACL_CanPostTopicTime($this->oUserCurrent)) {
			$this->Message_AddErrorSingle($this->Lang_Get('topic_time_limit'),$this->Lang_Get('error'));
			return;
		}
		$oTopic->setBlogId($oBlog->getId());
		/**
		 * Получаемый и устанавливаем разрезанный текст по тегу <cut>
		 */
		list($sTextShort,$sTextNew,$sTextCut) = $this->Text_Cut($oTopic->getTextSource());

		$oTopic->setCutText($sTextCut);
		$oTopic->setText($this->Text_Parser($sTextNew));
		$oTopic->setTextShort($this->Text_Parser($sTextShort));

		/**
		 * изменяем вопрос/ответы только если еще никто не голосовал
		 */
		if ($this->oType->isAllow('question') && getRequestStr('question_title') && getRequest('answer',array()) && $oTopic->getQuestionCountVote()==0) {
			$oTopic->setQuestionTitle(strip_tags(getRequestStr('question_title')));
			$oTopic->clearQuestionAnswer();
			foreach (getRequest('answer',array()) as $sAnswer) {
				$oTopic->addQuestionAnswer((string)$sAnswer);
			}
		}
		/*
		 * Если есть прикрепленные фото
		 */
		if($this->oType->isAllow('photoset') && $aPhotos = $oTopic->getPhotosetPhotos()) {
			if (!($oPhotoMain=$this->Topic_getTopicPhotoById(getRequestStr('topic_main_photo')) and $oPhotoMain->getTopicId()==$oTopic->getId())) {
				$oPhotoMain=$aPhotos[0];
			}
			$oTopic->setPhotosetMainPhotoId($oPhotoMain->getId());
			$oTopic->setPhotosetCount(count($aPhotos));
		}
		/**
		 * Публикуем или сохраняем в черновиках
		 */
		$bSendNotify=false;
		if (isset($_REQUEST['submit_topic_publish'])) {
			$oTopic->setPublish(1);
			if ($oTopic->getPublishDraft()==0) {
				$oTopic->setPublishDraft(1);
				$oTopic->setDateAdd(date("Y-m-d H:i:s"));
				$bSendNotify=true;
			}
		} else {
			$oTopic->setPublish(0);
		}
		/**
		 * Принудительный вывод на главную
		 */
		if ($this->ACL_IsAllowPublishIndex($this->oUserCurrent))	{
			if (getRequest('topic_publish_index')) {
				$oTopic->setPublishIndex(1);
			} else {
				$oTopic->setPublishIndex(0);
			}
		}
		/**
		 * Запрет на комментарии к топику
		 */
		$oTopic->setForbidComment(0);
		if (getRequest('topic_forbid_comment')) {
			$oTopic->setForbidComment(1);
		}
		$this->Hook_Run('topic_edit_before', array('oTopic'=>$oTopic,'oBlog'=>$oBlog));
		/**
		 * Сохраняем топик
		 */
		if ($this->Topic_UpdateTopic($oTopic)) {
			$this->Hook_Run('topic_edit_after', array('oTopic'=>$oTopic,'oBlog'=>$oBlog,'bSendNotify'=>&$bSendNotify));

			/*
			 * Заполняем дополнительные поля
			 */
			$this->processFields($oTopic);
			/**
			 * Обновляем данные в комментариях, если топик был перенесен в новый блог
			 */
			if($sBlogIdOld!=$oTopic->getBlogId()) {
				$this->Comment_UpdateTargetParentByTargetId($oTopic->getBlogId(), 'topic', $oTopic->getId());
				$this->Comment_UpdateTargetParentByTargetIdOnline($oTopic->getBlogId(), 'topic', $oTopic->getId());
			}
			/**
			 * Обновляем количество топиков в блоге
			 */
			if ($sBlogIdOld!=$oTopic->getBlogId()) {
				$this->Blog_RecalculateCountTopicByBlogId($sBlogIdOld);
			}
			$this->Blog_RecalculateCountTopicByBlogId($oTopic->getBlogId());
			/**
			 * Добавляем событие в ленту
			 */
			$this->Stream_write($oTopic->getUserId(), 'add_topic', $oTopic->getId(),$oTopic->getPublish() && $oBlog->getType()!='close');
			/**
			 * Рассылаем о новом топике подписчикам блога
			 */
			if ($bSendNotify)	 {
				$this->Topic_SendNotifyTopicNew($oBlog,$oTopic,$oTopic->getUser());
			}
			if (!$oTopic->getPublish() and !$this->oUserCurrent->isAdministrator() and $this->oUserCurrent->getId()!=$oTopic->getUserId()) {
				Router::Location($oBlog->getUrlFull());
			}
			Router::Location($oTopic->getUrl());
		} else {
			$this->Message_AddErrorSingle($this->Lang_Get('system_error'));
			return Router::Action('error');
		}
	}

	/**
	 * AJAX подгрузка следующих фото
	 *
	 */
	protected function EventGetMore() {
		/**
		 * Устанавливаем формат Ajax ответа
		 */
		$this->Viewer_SetResponseAjax('json');
		/**
		 * Существует ли топик
		 */
		$oTopic = $this->Topic_getTopicById(getRequestStr('topic_id'));
		if (!$oTopic || !getRequest('last_id')) {
			$this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
			return false;
		}
		/**
		 * Получаем список фото
		 */
		$aPhotos = $oTopic->getPhotosetPhotos(getRequestStr('last_id'), Config::Get('module.topic.photoset.per_page'));
		$aResult = array();
		if (count($aPhotos)) {
			/**
			 * Формируем данные для ajax ответа
			 */
			foreach($aPhotos as $oPhoto) {
				$aResult[] = array('id' => $oPhoto->getId(), 'path_thumb' => $oPhoto->getWebPath('50crop'), 'path' => $oPhoto->getWebPath(), 'description' => $oPhoto->getDescription());
			}
			$this->Viewer_AssignAjax('photos', $aResult);
		}
		$this->Viewer_AssignAjax('bHaveNext', count($aPhotos)==Config::Get('module.topic.photoset.per_page'));
	}
	/**
	 * AJAX удаление фото
	 *
	 */
	protected function EventDeletePhoto() {
		/**
		 * Устанавливаем формат Ajax ответа
		 */
		$this->Viewer_SetResponseAjax('json');
		/**
		 * Проверяем авторизован ли юзер
		 */
		if (!$this->User_IsAuthorization()) {
			$this->Message_AddErrorSingle($this->Lang_Get('not_access'),$this->Lang_Get('error'));
			return Router::Action('error');
		}
		/**
		 * Поиск фото по id
		 */
		$oPhoto = $this->Topic_getTopicPhotoById(getRequestStr('id'));
		if ($oPhoto) {
			if ($oPhoto->getTopicId()) {
				/**
				 * Проверяем права на топик
				 */
				if ($oTopic=$this->Topic_GetTopicById($oPhoto->getTopicId()) and $this->ACL_IsAllowEditTopic($oTopic,$this->oUserCurrent)) {
					if ($oTopic->getPhotosetCount()>1) {
						$this->Topic_deleteTopicPhoto($oPhoto);
						/**
						 * Если удаляем главную фотку топика, то её необходимо сменить
						 */
						if ($oPhoto->getId()==$oTopic->getPhotosetMainPhotoId()) {
							$aPhotos = $oTopic->getPhotosetPhotos(0,1);
							$oTopic->setPhotosetMainPhotoId($aPhotos[0]->getId());
						}
						$oTopic->setPhotosetCount($oTopic->getPhotosetCount()-1);
						$this->Topic_UpdateTopic($oTopic);
						$this->Message_AddNotice($this->Lang_Get('topic_photoset_photo_deleted'), $this->Lang_Get('attention'));
					} else {
						$this->Message_AddError($this->Lang_Get('topic_photoset_photo_deleted_error_last'), $this->Lang_Get('error'));
					}
					return;
				}
			} else {
				$this->Topic_deleteTopicPhoto($oPhoto);
				$this->Message_AddNotice($this->Lang_Get('topic_photoset_photo_deleted'), $this->Lang_Get('attention'));
				return;
			}
		}
		$this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
	}
	/**
	 * AJAX установка описания фото
	 *
	 */
	protected function EventSetPhotoDescription() {
		/**
		 * Устанавливаем формат Ajax ответа
		 */
		$this->Viewer_SetResponseAjax('json');
		/**
		 * Проверяем авторизован ли юзер
		 */
		if (!$this->User_IsAuthorization()) {
			$this->Message_AddErrorSingle($this->Lang_Get('not_access'),$this->Lang_Get('error'));
			return Router::Action('error');
		}
		/**
		 * Поиск фото по id
		 */
		$oPhoto = $this->Topic_getTopicPhotoById(getRequestStr('id'));
		if ($oPhoto) {
			if ($oPhoto->getTopicId()) {
				// проверяем права на топик
				if ($oTopic=$this->Topic_GetTopicById($oPhoto->getTopicId()) and $this->ACL_IsAllowEditTopic($oTopic,$this->oUserCurrent)) {
					$oPhoto->setDescription(htmlspecialchars(strip_tags(getRequestStr('text'))));
					$this->Topic_updateTopicPhoto($oPhoto);
				}
			} else {
				$oPhoto->setDescription(htmlspecialchars(strip_tags(getRequestStr('text'))));
				$this->Topic_updateTopicPhoto($oPhoto);
			}
		}
	}
	/**
	 * AJAX загрузка фоток
	 *
	 * @return unknown
	 */
	protected function EventUpload() {
		/**
		 * Устанавливаем формат Ajax ответа
		 * В зависимости от типа загрузчика устанавливается тип ответа
		 */
		if (getRequest('is_iframe')) {
			$this->Viewer_SetResponseAjax('jsonIframe', false);
		} else {
			$this->Viewer_SetResponseAjax('json');
		}
		/**
		 * Проверяем авторизован ли юзер
		 */
		if (!$this->User_IsAuthorization()) {
			$this->Message_AddErrorSingle($this->Lang_Get('not_access'),$this->Lang_Get('error'));
			return Router::Action('error');
		}
		/**
		 * Файл был загружен?
		 */
		if (!isset($_FILES['Filedata']['tmp_name'])) {
			$this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
			return false;
		}

		$iTopicId = getRequestStr('topic_id');
		$sTargetId = null;
		$iCountPhotos = 0;
		// Если от сервера не пришёл id топика, то пытаемся определить временный код для нового топика. Если и его нет. то это ошибка
		if (!$iTopicId) {
			$sTargetId = empty($_COOKIE['ls_photoset_target_tmp']) ? getRequestStr('ls_photoset_target_tmp') : $_COOKIE['ls_photoset_target_tmp'];
			if (!$sTargetId) {
				$this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
				return false;
			}
			$iCountPhotos = $this->Topic_getCountPhotosByTargetTmp($sTargetId);
		} else {
			/**
			 * Загрузка фото к уже существующему топику
			 */
			$oTopic = $this->Topic_getTopicById($iTopicId);
			if (!$oTopic or !$this->ACL_IsAllowEditTopic($oTopic,$this->oUserCurrent)) {
				$this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
				return false;
			}
			$iCountPhotos = $this->Topic_getCountPhotosByTopicId($iTopicId);
		}
		/**
		 * Максимальное количество фото в топике
		 */
		if ($iCountPhotos >= Config::Get('module.topic.photoset.count_photos_max')) {
			$this->Message_AddError($this->Lang_Get('topic_photoset_error_too_much_photos', array('MAX' => Config::Get('module.topic.photoset.count_photos_max'))), $this->Lang_Get('error'));
			return false;
		}
		/**
		 * Максимальный размер фото
		 */
		if (filesize($_FILES['Filedata']['tmp_name']) > Config::Get('module.topic.photoset.photo_max_size')*1024) {
			$this->Message_AddError($this->Lang_Get('topic_photoset_error_bad_filesize', array('MAX' => Config::Get('module.topic.photoset.photo_max_size'))), $this->Lang_Get('error'));
			return false;
		}
		/**
		 * Загружаем файл
		 */
		$sFile = $this->Topic_UploadTopicPhoto($_FILES['Filedata']);
		if ($sFile) {
			/**
			 * Создаем фото
			 */
			$oPhoto = Engine::GetEntity('Topic_TopicPhoto');
			$oPhoto->setPath($sFile);
			if ($iTopicId) {
				$oPhoto->setTopicId($iTopicId);
			} else {
				$oPhoto->setTargetTmp($sTargetId);
			}
			if ($oPhoto = $this->Topic_addTopicPhoto($oPhoto)) {
				/**
				 * Если топик уже существует (редактирование), то обновляем число фоток в нём
				 */
				if (isset($oTopic)) {
					$oTopic->setPhotosetCount($oTopic->getPhotosetCount()+1);
					$this->Topic_UpdateTopic($oTopic);
				}

				$this->Viewer_AssignAjax('file', $oPhoto->getWebPath('100crop'));
				$this->Viewer_AssignAjax('id', $oPhoto->getId());
				$this->Message_AddNotice($this->Lang_Get('topic_photoset_photo_added'), $this->Lang_Get('attention'));
			} else {
				$this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
			}
		} else {
			$this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
		}
	}

	/**
	 * Переход по ссылке с подсчетом количества переходов
	 *
	 */
	protected function EventGo() {
		/**
		 * Получаем номер топика из УРЛ и проверяем существует ли он
		 */
		$sTopicId=$this->GetParam(0);
		if (!($oTopic=$this->Topic_GetTopicById($sTopicId)) or !$oTopic->getPublish()) {
			return parent::EventNotFound();
		}
		/**
		 * проверяем есть ли ссылка на оригинал
		 */
		if (!$oTopic->getLinkUrl()) {
			return parent::EventNotFound();
		}
		/**
		 * увелививаем число переходов по ссылке
		 */
		$oTopic->setLinkCountJump($oTopic->getLinkCountJump()+1);
		$this->Topic_UpdateTopic($oTopic);
		/**
		 * собственно сам переход по ссылке
		 */
		Router::Location($oTopic->getLinkUrl());
	}
	
	/*
	 * Обработка дополнительных полей
	 */

	public function processFields($oTopic){

		/*
		 * Чистим существующие значения
		 */
		$this->Topic_DeleteTopicValuesByTopicId($oTopic->getId());

		if($oType=$this->Topic_getContentTypeByUrl($oTopic->getType())){
			//получаем поля для данного типа
			if($aFields=$oType->getFields()){
				
				foreach($aFields as $oField){
					$sText=null;

					if(isset($_REQUEST['fields'][$oField->getFieldId()]) || isset($_FILES['fields_'.$oField->getFieldId()])){

						//текстовые поля
						if(in_array($oField->getFieldType(),array('input','textarea','select'))){
							$sText=$this->Text_Parser($_REQUEST['fields'][$oField->getFieldId()]);
						}
						//поле ссылки
						if($oField->getFieldType()=='link'){
							$sText=$_REQUEST['fields'][$oField->getFieldId()];
						}

						//поле даты
						if($oField->getFieldType()=='date') {
							if(isset($_REQUEST['fields'][$oField->getFieldId()])){

								if(func_check($_REQUEST['fields'][$oField->getFieldId()],'text',6,10) && substr_count($_REQUEST['fields'][$oField->getFieldId()],'.')==2) {
									list($d,$m,$y)=explode('.',$_REQUEST['fields'][$oField->getFieldId()]);
									if(@checkdate($m,$d,$y)) {
										$sText=$_REQUEST['fields'][$oField->getFieldId()];
									}
								}

							}

						}

						//поле с файлом
						if($oField->getFieldType()=='file'){

							if(getRequest('topic_delete_file_'.$oField->getFieldId())){
								if($oTopic->getFile($oField->getFieldId())){
									@unlink(Config::Get('path.root.server').$oTopic->getFile($oField->getFieldId())->getFileUrl());
									$oTopic->setValueField($oField->getFieldId(),'');
								}
							}

							if (isset($_FILES['fields_'.$oField->getFieldId()]) and is_uploaded_file($_FILES['fields_'.$oField->getFieldId()]['tmp_name'])) {

								if (filesize($_FILES['fields_'.$oField->getFieldId()]['tmp_name'])<=Config::Get('module.topic.max_filesize_limit')) {
									$aPathInfo=pathinfo($_FILES['fields_'.$oField->getFieldId()]['name']);

									if (in_array(strtolower($aPathInfo['extension']),Config::Get('module.topic.upload_mime_types'))) {
										$sFileTmp=$_FILES['fields_'.$oField->getFieldId()]['tmp_name'];
										$sDirSave=Config::Get('path.uploads.root').'/files/'.$this->User_GetUserCurrent()->getId().'/'.func_generator(16);
										mkdir(Config::Get('path.root.server').$sDirSave,0777,true);
										if(is_dir(Config::Get('path.root.server').$sDirSave)){

											$sFile=$sDirSave.'/'.func_generator(10).'.'.strtolower($aPathInfo['extension']);
											$sFileFullPath=Config::Get('path.root.server').$sFile;
											if (copy($sFileTmp,$sFileFullPath)) {
												//удаляем старый файл
												if($oTopic->getFile($oField->getFieldId())){
													@unlink(Config::Get('path.root.server').$oTopic->getFile($oField->getFieldId())->getFileUrl());
												}

												$aFileObj=array();
												$aFileObj['file_hash']=func_generator(32);
												$aFileObj['file_name']=$this->Text_Parser($_FILES['fields_'.$oField->getFieldId()]['name']);
												$aFileObj['file_url']=$sFile;
												$aFileObj['file_size']=$_FILES['fields_'.$oField->getFieldId()]['size'];
												$aFileObj['file_extension']=$aPathInfo['extension'];
												$aFileObj['file_downloads']=0;
												$sText=serialize($aFileObj);

												@unlink($sFileTmp);
											}
										}
									}
								}


							}
							@unlink($_FILES['fields_'.$oField->getFieldId()]['tmp_name']);
						}

						//Добавляем поле к топику.
						if($sText){
							
							$oValue=Engine::GetEntity('Topic_ContentValues');
							$oValue->setTargetId($oTopic->getId());
							$oValue->setTargetType('topic');
							$oValue->setFieldId($oField->getFieldId());
							$oValue->setFieldType($oField->getFieldType());
							$oValue->setValue($sText);
							$oValue->setValueSource(($oField->getFieldType()=='file')?$sText:$_REQUEST['fields'][$oField->getFieldId()]);

							$this->Topic_AddTopicValue($oValue);

						}

					}

				}
			}
		}
	}

	
	/**
	 * Проверка полей формы
	 *
	 * @return bool
	 */
	protected function checkTopicFields($oTopic) {
		$this->Security_ValidateSendForm();

		$bOk=true;
		/**
		 * Валидируем топик
		 */
		if (!$oTopic->_Validate()) {
			$this->Message_AddError($oTopic->_getValidateError(),$this->Lang_Get('error'));
			$bOk=false;
		}
		/**
		 * Выполнение хуков
		 */
		$this->Hook_Run('check_topic_fields', array('bOk'=>&$bOk));

		return $bOk;
	}
	/**
	 * При завершении экшена загружаем необходимые переменные
	 *
	 */
	public function EventShutdown() {
		$this->Viewer_Assign('sMenuHeadItemSelect',$this->sMenuHeadItemSelect);
		$this->Viewer_Assign('sMenuItemSelect',$this->sMenuItemSelect);
		$this->Viewer_Assign('sMenuSubItemSelect',$this->sMenuSubItemSelect);
	}
}
?>