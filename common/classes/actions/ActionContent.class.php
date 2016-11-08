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
    protected $sMenuHeadItemSelect = 'blog';

    /**
     * Меню
     *
     * @var string
     */
    protected $sMenuItemSelect = 'topic';

    /**
     * СубМеню
     *
     * @var string
     */
    protected $sMenuSubItemSelect = 'topic';

    /**
     * Текущий юзер
     *
     * @var ModuleUser_EntityUser|null
     */
    protected $oUserCurrent = null;

    /**
     * Текущий тип контента
     *
     * @var ModuleTopic_EntityContentType|null
     */
    protected $oContentType = null;

    protected $aBlogTypes = array();

    protected $bPersonalBlogEnabled = false;

    /**
     * Инициализация
     *
     */
    public function Init() {

        // * Проверяем авторизован ли юзер
        if (!E::ModuleUser()->IsAuthorization() && (R::GetActionEvent() !== 'go') && (R::GetActionEvent() !== 'photo')) {
            return parent::EventNotFound();
        }
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();

        // * Устанавливаем дефолтный эвент
        $this->SetDefaultEvent('add');

        // * Загружаем в шаблон JS текстовки
        E::ModuleLang()->AddLangJs(
            array('topic_photoset_photo_delete',
                  'topic_photoset_mark_as_preview',
                  'topic_photoset_photo_delete_confirm',
                  'topic_photoset_is_preview',
                  'topic_photoset_upload_choose',
            )
        );
        $this->aBlogTypes = $this->_getAllowBlogTypes();
    }

    /**
     * Регистрируем евенты
     *
     */
    protected function RegisterEvent() {

        $this->AddEventPreg('/^published$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventShowTopics');
        $this->AddEventPreg('/^saved$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventShowTopics');
        $this->AddEventPreg('/^drafts$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventShowTopics');
        $this->AddEvent('edit', array('EventEdit', 'edit'));
        $this->AddEvent('delete', 'EventDelete');

        // Photosets
        if (E::ModuleUser()->IsAuthorization()) {
            $this->AddEventPreg('/^photo$/i', '/^upload$/i', 'EventAjaxPhotoUpload'); // Uploads image to photoset
            $this->AddEventPreg('/^photo$/i', '/^description$/i', 'EventAjaxPhotoDescription'); // Sets description to image of photoset
            $this->AddEventPreg('/^photo$/i', '/^delete$/i', 'EventPhotoDelete'); // Deletes image from photoset
        }
        $this->AddEventPreg('/^photo$/i', '/^getmore$/i', 'EventAjaxPhotoGetMore'); // Gets more images from photosets to showed topic

        // Переход для топика с оригиналом
        $this->AddEvent('go', 'EventGo');

        $this->AddEventPreg('/^add$/i', array('EventAdd', 'add'));
        $this->AddEventPreg('/^[\w\-\_]+$/i', '/^add$/i', array('EventAdd', 'add'));
    }


    /**********************************************************************************
     ************************ РЕАЛИЗАЦИЯ ЭКШЕНА ***************************************
     **********************************************************************************
     */

    /**
     * Returns array of allowed blogs
     *
     * @param array $aFilter
     *
     * @return array
     */
    protected function _getAllowBlogs($aFilter = array()) {

        $oUser = (isset($aFilter['user']) ? $aFilter['user'] : null);
        $sContentTypeName = (isset($aFilter['content_type']) ? $aFilter['content_type'] : null);

        $aBlogs = E::ModuleBlog()->GetBlogsAllowByUser($oUser);

        $aAllowBlogs = array();
        // Добавим персональный блог пользователю
        // Если персональные блоги отключены, то $oPersonalBlog будет равно null и добавлять
        // его в список догступных блогов не стоит, иначе будет ошибка при итерации по
        // массиву $aAllowBlogs.
        if ($oUser && $oPersonalBlog = E::ModuleBlog()->GetPersonalBlogByUserId($oUser->getId())) {
            $aAllowBlogs[] = $oPersonalBlog;
        }

        /** @var ModuleBlog_EntityBlog $oBlog */
        foreach($aBlogs as $oBlog) {
            if (E::ModuleACL()->CanAddTopic($oUser, $oBlog) && $oBlog->IsContentTypeAllow($sContentTypeName)) {
                $aAllowBlogs[$oBlog->getId()] = $oBlog;
            }
        }
        return $aAllowBlogs;
    }

    /**
     * Returns of allowed blog types
     *
     * @return array
     */
    protected function _getAllowBlogTypes() {

        $aBlogTypes = E::ModuleBlog()->GetAllowBlogTypes($this->oUserCurrent, 'write', true);
        $this->bPersonalBlogEnabled = in_array('personal', $aBlogTypes);
        return $aBlogTypes;
    }

    /**
     * Добавление топика
     *
     * @return mixed
     */
    protected function EventAdd() {

        // * Устанавливаем шаблон вывода
        $this->SetTemplateAction('add');
        E::ModuleViewer()->Assign('sMode', 'add');

        // * Вызов хуков
        E::ModuleHook()->Run('topic_add_show');

        // * Получаем тип контента
        if (!$this->oContentType = E::ModuleTopic()->GetContentTypeByUrl($this->sCurrentEvent)) {
            if (!($this->oContentType = E::ModuleTopic()->GetContentTypeDefault())) {
                return parent::EventNotFound();
            }
        }

        E::ModuleViewer()->Assign('oContentType', $this->oContentType);
        $this->sMenuSubItemSelect = $this->oContentType->getContentUrl();

        // * Если тип контента не доступен текущему юзеру
        if (!$this->oContentType->isAccessible()) {
            return parent::EventNotFound();
        }

        $aBlogFilter = array(
            'user' => $this->oUserCurrent,
            'content_type' => $this->oContentType,
        );
        $aBlogsAllow = $this->_getAllowBlogs($aBlogFilter);

        // Такой тип контента не разрешен для пользователя ни в одном из типов блогов
        if (!$aBlogsAllow) {
            return parent::EventNotFound();
        }

        // Проверим можно ли писать в персональный блог такой тип контента
        /** @var ModuleBlog_EntityBlog $oAllowedBlog */
        $this->bPersonalBlogEnabled = FALSE;
        foreach ($aBlogsAllow as $oAllowedBlog) {
            // Нашли среди разрешенных персональный блог
            if ($oAllowedBlog->getType() == 'personal') {
                if (!$oAllowedBlog->getBlogType()->getContentTypes()) {
                    // типы контента не определены, значит, разрешен любой
                    $this->bPersonalBlogEnabled = TRUE;
                } else {
                    foreach ($oAllowedBlog->getBlogType()->getContentTypes() as $oContentType) {
                        if ($oContentType->getId() == $this->oContentType->getId()) {
                            $this->bPersonalBlogEnabled = TRUE;
                            break;
                        }
                    }
                }
                break;
            }
        }

        // * Загружаем переменные в шаблон
        E::ModuleViewer()->Assign('bPersonalBlog', $this->bPersonalBlogEnabled);
        E::ModuleViewer()->Assign('aBlogsAllow', $aBlogsAllow);
        E::ModuleViewer()->Assign('bEditDisabled', false);
        E::ModuleViewer()->AddHtmlTitle(
            E::ModuleLang()->Get('topic_topic_create') . ' ' . mb_strtolower($this->oContentType->getContentTitle(), 'UTF-8')
        );
        if (!is_numeric(F::GetRequest('topic_id'))) {
            $_REQUEST['topic_id'] = '';
        }

        $_REQUEST['topic_show_photoset'] = 1;

        // * Если нет временного ключа для нового топика, то генерируем; если есть, то загружаем фото по этому ключу
        if ($sTargetTmp = E::ModuleSession()->GetCookie('ls_photoset_target_tmp')) {
            E::ModuleSession()->SetCookie('ls_photoset_target_tmp', $sTargetTmp, 'P1D', false);
            E::ModuleViewer()->Assign('aPhotos', E::ModuleTopic()->GetPhotosByTargetTmp($sTargetTmp));
        } else {
            E::ModuleSession()->SetCookie('ls_photoset_target_tmp', F::RandomStr(), 'P1D', false);
        }

        // Если POST-запрос, то обрабатываем отправку формы
        if ($this->IsPost()) {
            return $this->SubmitAdd();
        }

        return null;
    }

    /**
     * Обработка добавления топика
     *
     * @return bool|string
     */
    protected function SubmitAdd() {

        // * Проверяем отправлена ли форма с данными (хотяб одна кнопка)
        if (!F::isPost('submit_topic_publish') && !F::isPost('submit_topic_draft') && !F::isPost('submit_topic_save')) {
            return false;
        }
        /** @var ModuleTopic_EntityTopic $oTopic */
        $oTopic = E::GetEntity('Topic');
        $oTopic->_setValidateScenario('topic');

        // * Заполняем поля для валидации
        $oTopic->setBlogId(F::GetRequestStr('blog_id'));

        // issue 151 (https://github.com/altocms/altocms/issues/151)
        // Некорректная обработка названия блога
        // $oTopic->setTitle(strip_tags(F::GetRequestStr('topic_title')));
        $oTopic->setTitle(E::ModuleTools()->RemoveAllTags(F::GetRequestStr('topic_title')));

        $oTopic->setTextSource(F::GetRequestStr('topic_text'));
        $oTopic->setUserId($this->oUserCurrent->getId());
        $oTopic->setType($this->oContentType->getContentUrl());

        if ($this->oContentType->isAllow('link')) {
            $oTopic->setSourceLink(F::GetRequestStr('topic_field_link'));
        }
        $oTopic->setTags(F::GetRequestStr('topic_field_tags'));

        $oTopic->setDateAdd(F::Now());
        $oTopic->setUserIp(F::GetUserIp());

        $sTopicUrl = E::ModuleTopic()->CorrectTopicUrl($oTopic->MakeTopicUrl());
        $oTopic->setTopicUrl($sTopicUrl);

        // * Проверка корректности полей формы
        if (!$this->checkTopicFields($oTopic)) {
            return false;
        }

        // * Определяем в какой блог делаем запись
        $nBlogId = $oTopic->getBlogId();
        if ($nBlogId == 0) {
            $oBlog = E::ModuleBlog()->GetPersonalBlogByUserId($this->oUserCurrent->getId());
        } else {
            $oBlog = E::ModuleBlog()->GetBlogById($nBlogId);
        }

        // * Если блог не определен, то выдаем предупреждение
        if (!$oBlog) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_create_blog_error_unknown'), E::ModuleLang()->Get('error'));
            return false;
        }

        // * Проверяем права на постинг в блог
        if (!E::ModuleACL()->IsAllowBlog($oBlog, $this->oUserCurrent)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_create_blog_error_noallow'), E::ModuleLang()->Get('error'));
            return false;
        }

        // * Проверяем разрешено ли постить топик по времени
        if (F::isPost('submit_topic_publish') && !E::ModuleACL()->CanPostTopicTime($this->oUserCurrent)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_time_limit'), E::ModuleLang()->Get('error'));
            return false;
        }

        // * Теперь можно смело добавлять топик к блогу
        $oTopic->setBlogId($oBlog->getId());

        // * Получаемый и устанавливаем разрезанный текст по тегу <cut>
        list($sTextShort, $sTextNew, $sTextCut) = E::ModuleText()->Cut($oTopic->getTextSource());

        $oTopic->setCutText($sTextCut);
        $oTopic->setText(E::ModuleText()->Parse($sTextNew));

        // Получаем ссылки, полученные при парсинге текста
        $oTopic->setTextLinks(E::ModuleText()->GetLinks());
        $oTopic->setTextShort(E::ModuleText()->Parse($sTextShort));

        // * Варианты ответов
        if ($this->oContentType->isAllow('poll') && F::GetRequestStr('topic_field_question') && F::GetRequest('topic_field_answers', array())) {
            $oTopic->setQuestionTitle(strip_tags(F::GetRequestStr('topic_field_question')));
            $oTopic->clearQuestionAnswer();
            $aAnswers = F::GetRequest('topic_field_answers', array());
            foreach ($aAnswers as $sAnswer) {
                $sAnswer = trim((string)$sAnswer);
                if ($sAnswer) {
                    $oTopic->addQuestionAnswer($sAnswer);
                }
            }
        }

        $aPhotoSetData = E::ModuleMresource()->GetPhotosetData('photoset', 0);
        $oTopic->setPhotosetCount($aPhotoSetData['count']);
        if ($aPhotoSetData['cover']) {
            $oTopic->setPhotosetMainPhotoId($aPhotoSetData['cover']);
        }

        // * Публикуем или сохраняем
        if (isset($_REQUEST['submit_topic_publish'])) {
            $oTopic->setPublish(1);
            $oTopic->setPublishDraft(1);
            if (!$oTopic->getDateShow()) {
                $oTopic->setDateShow(F::Now());
            }
        } else {
            $oTopic->setPublish(0);
            $oTopic->setPublishDraft(0);
        }

        // * Принудительный вывод на главную
        $oTopic->setPublishIndex(0);
        if (E::ModuleACL()->IsAllowPublishIndex($this->oUserCurrent)) {
            if (F::GetRequest('topic_publish_index')) {
                $oTopic->setPublishIndex(1);
            }
        }

        // * Запрет на комментарии к топику
        $oTopic->setForbidComment(F::GetRequest('topic_forbid_comment', 0));

        // Разрешение/запрет индексации контента топика изначально - как у блога
        if ($oBlogType = $oBlog->GetBlogType()) {
            // Если тип блога определен, то берем из типа блога...
            $oTopic->setTopicIndexIgnore($oBlogType->GetIndexIgnore());
        } else {
            // ...если нет, то индексацию разрешаем
            $oTopic->setTopicIndexIgnore(false);
        }

        $oTopic->setShowPhotoset(F::GetRequest('topic_show_photoset', 0));

        // * Запускаем выполнение хуков
        E::ModuleHook()->Run('topic_add_before', array('oTopic' => $oTopic, 'oBlog' => $oBlog));

        // * Добавляем топик
        if ($this->_addTopic($oTopic)) {
            E::ModuleHook()->Run('topic_add_after', array('oTopic' => $oTopic, 'oBlog' => $oBlog));
            // * Получаем топик, чтоб подцепить связанные данные
            $oTopic = E::ModuleTopic()->GetTopicById($oTopic->getId());

            // * Обновляем количество топиков в блоге
            E::ModuleBlog()->RecalculateCountTopicByBlogId($oTopic->getBlogId());

            // * Добавляем автора топика в подписчики на новые комментарии к этому топику
            E::ModuleSubscribe()->AddSubscribeSimple(
                'topic_new_comment', $oTopic->getId(), $this->oUserCurrent->getMail(), $this->oUserCurrent->getId()
            );

            // * Подписываем автора топика на обновления в трекере
            if ($oTrack = E::ModuleSubscribe()->AddTrackSimple(
                'topic_new_comment', $oTopic->getId(), $this->oUserCurrent->getId()
            )) {
                // Если пользователь не отписался от обновлений топика
                if (!$oTrack->getStatus()) {
                    $oTrack->setStatus(1);
                    E::ModuleSubscribe()->UpdateTrack($oTrack);
                }
            }

            // * Делаем рассылку всем, кто состоит в этом блоге
            if ($oTopic->getPublish() == 1 && $oBlog->getType() != 'personal') {
                E::ModuleTopic()->SendNotifyTopicNew($oBlog, $oTopic, $this->oUserCurrent);
            }
            /**
             * Привязываем фото к ID топика
             */
            if (isset($aPhotos) && count($aPhotos)) {
                E::ModuleTopic()->AttachTmpPhotoToTopic($oTopic);
            }

            // * Удаляем временную куку
            E::ModuleSession()->DelCookie('ls_photoset_target_tmp');

            // Обработаем фотосет
            if ($this->oContentType->isAllow('photoset') && ($sTargetTmp = E::ModuleSession()->GetCookie(ModuleUploader::COOKIE_TARGET_TMP))) {
                // Уберем у ресурса флаг временного размещения и удалим из куки target_tmp
                E::ModuleSession()->DelCookie(ModuleUploader::COOKIE_TARGET_TMP);
            }

            // * Добавляем событие в ленту
            E::ModuleStream()->Write(
                $oTopic->getUserId(), 'add_topic', $oTopic->getId(),
                $oTopic->getPublish() && (!$oBlog->getBlogType() || !$oBlog->getBlogType()->IsPrivate())
            );
            R::Location($oTopic->getUrl());
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'));
            F::SysWarning('System Error');
            return R::Action('error');
        }
    }

    /**
     * Adds new topic
     *
     * @param $oTopic
     *
     * @return bool|ModuleTopic_EntityTopic
     */
    protected function _addTopic($oTopic) {

        return E::ModuleTopic()->AddTopic($oTopic);
    }

    /**
     * Редактирование топика
     *
     */
    protected function EventEdit() {

        // * Получаем номер топика из URL и проверяем существует ли он
        $iTopicId = intval($this->GetParam(0));
        if (!$iTopicId || !($oTopic = E::ModuleTopic()->GetTopicById($iTopicId))) {
            return parent::EventNotFound();
        }

        // * Получаем тип контента
        if (!$this->oContentType = E::ModuleTopic()->GetContentTypeByUrl($oTopic->getType())) {
            return parent::EventNotFound();
        }

        E::ModuleViewer()->Assign('oContentType', $this->oContentType);
        $this->sMenuSubItemSelect = $this->oContentType->getContentUrl();

        // * Есть права на редактирование
        if (!E::ModuleACL()->IsAllowEditTopic($oTopic, $this->oUserCurrent)) {
            return parent::EventNotFound();
        }

        $aBlogFilter = array(
            'user' => $this->oUserCurrent,
            'content_type' => $this->oContentType,
        );
        $aBlogsAllow = $this->_getAllowBlogs($aBlogFilter);

        // Такой тип контента не разрешен для пользователя ни в одном из типов блогов
        if (!$aBlogsAllow) {
            return parent::EventNotFound();
        }

        // * Вызов хука
        E::ModuleHook()->Run('topic_edit_show', array('oTopic' => $oTopic));

        // * Загружаем переменные в шаблон
        E::ModuleViewer()->Assign('bPersonalBlog', $this->bPersonalBlogEnabled);
        E::ModuleViewer()->Assign('aBlogsAllow', $aBlogsAllow);
        E::ModuleViewer()->Assign('bEditDisabled', $oTopic->getQuestionCountVote() == 0 ? false : true);
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('topic_topic_edit'));

        // * Устанавливаем шаблон вывода
        $this->SetTemplateAction('add');
        E::ModuleViewer()->Assign('sMode', 'edit');

        // * Проверяем, отправлена ли форма с данными
        if ($this->IsPost()) {
            // * Обрабатываем отправку формы
            $xResult = $this->SubmitEdit($oTopic);
            if ($xResult !== false) {
                return $xResult;
            }
        } else {
            /**
             * Заполняем поля формы для редактирования
             * Только перед отправкой формы!
             */
            $_REQUEST['topic_title'] = $oTopic->getTitle();
            $_REQUEST['topic_text'] = $oTopic->getTextSource();
            $_REQUEST['blog_id'] = $oTopic->getBlogId();
            $_REQUEST['topic_id'] = $oTopic->getId();
            $_REQUEST['topic_publish_index'] = $oTopic->getPublishIndex();
            $_REQUEST['topic_forbid_comment'] = $oTopic->getForbidComment();
            $_REQUEST['topic_main_photo'] = $oTopic->getPhotosetMainPhotoId();

            $_REQUEST['topic_field_link'] = $oTopic->getSourceLink();
            $_REQUEST['topic_field_tags'] = $oTopic->getTags();

            $_REQUEST['topic_field_question'] = $oTopic->getQuestionTitle();
            $_REQUEST['topic_field_answers'] = array();
            $_REQUEST['topic_show_photoset'] = $oTopic->getShowPhotoset();
            $aAnswers = $oTopic->getQuestionAnswers();
            foreach ($aAnswers as $aAnswer) {
                $_REQUEST['topic_field_answers'][] = $aAnswer['text'];
            }

            foreach ($this->oContentType->getFields() as $oField) {
                if ($oTopic->getField($oField->getFieldId())) {
                    $sValue = $oTopic->getField($oField->getFieldId())->getValueSource();
                    if ($oField->getFieldType() == 'file') {
                        $sValue = unserialize($sValue);
                    }
                    $_REQUEST['fields'][$oField->getFieldId()] = $sValue;
                }
            }
        }

        $sUrlMask = R::GetTopicUrlMask();
        if (strpos($sUrlMask, '%topic_url%') === false) {
            // Нет в маске URL
            $aEditTopicUrl = array(
                'before' => $oTopic->getLink($sUrlMask),
                'input' => '',
                'after' => '',
            );
        } else {
            // В маске есть URL, вместо него нужно вставить <input>
            $aUrlMaskParts = explode('%topic_url%', $sUrlMask);
            $aEditTopicUrl = array(
                'before' => $aUrlMaskParts[0] ? $oTopic->getLink($aUrlMaskParts[0]) : F::File_RootUrl(),
                'input' => $oTopic->getTopicUrl() ? $oTopic->getTopicUrl() : $oTopic->MakeTopicUrl(),
                'after' => (isset($aUrlMaskParts[1]) && $aUrlMaskParts[1]) ? $oTopic->getLink($aUrlMaskParts[1], false) : '',
            );
        }
        if (!isset($_REQUEST['topic_url_input'])) {
            $_REQUEST['topic_url_input'] = $aEditTopicUrl['input'];
        } else {
            $aEditTopicUrl['input'] = $_REQUEST['topic_url_input'];
        }
        if (!isset($_REQUEST['topic_url_short'])) {
            $_REQUEST['topic_url_short'] = $oTopic->getUrlShort();
        }
        E::ModuleViewer()->Assign('aEditTopicUrl', $aEditTopicUrl);

        // Old style templates compatibility
        $_REQUEST['topic_url_before'] = $aEditTopicUrl['before'];
        $_REQUEST['topic_url'] = $aEditTopicUrl['input'];
        $_REQUEST['topic_url_after'] = $aEditTopicUrl['after'];

        E::ModuleViewer()->Assign('oTopic', $oTopic);

        // Добавим картинки фотосета для вывода
        E::ModuleViewer()->Assign(
            'aPhotos',
            E::ModuleMresource()->GetMresourcesRelByTarget('photoset', $oTopic->getId())
        );
    }

    /**
     * Обработка редактирования топика
     *
     * @param ModuleTopic_EntityTopic $oTopic
     *
     * @return mixed
     */
    protected function SubmitEdit($oTopic) {

        $oTopic->_setValidateScenario('topic');

        // * Сохраняем старое значение идентификатора блога
        $iBlogIdOld = $oTopic->getBlogId();

        // * Заполняем поля для валидации
        $iBlogId = F::GetRequestStr('blog_id');
        // if blog_id is empty then save blog not changed
        if (is_numeric($iBlogId)) {
            $oTopic->setBlogId($iBlogId);
        }

        // issue 151 (https://github.com/altocms/altocms/issues/151)
        // Некорректная обработка названия блога
        // $oTopic->setTitle(strip_tags(F::GetRequestStr('topic_title')));
        $oTopic->setTitle(E::ModuleTools()->RemoveAllTags(F::GetRequestStr('topic_title')));

        $oTopic->setTextSource(F::GetRequestStr('topic_text'));

        if ($this->oContentType->isAllow('link')) {
            $oTopic->setSourceLink(F::GetRequestStr('topic_field_link'));
        }
        $oTopic->setTags(F::GetRequestStr('topic_field_tags'));

        $oTopic->setUserIp(F::GetUserIp());

        if ($this->oUserCurrent && ($this->oUserCurrent->isAdministrator() || $this->oUserCurrent->isModerator())) {
            if (F::GetRequestStr('topic_url') && $oTopic->getTopicUrl() != F::GetRequestStr('topic_url')) {
                $sTopicUrl = E::ModuleTopic()->CorrectTopicUrl(F::TranslitUrl(F::GetRequestStr('topic_url')));
                $oTopic->setTopicUrl($sTopicUrl);
            }
        }

        // * Проверка корректности полей формы
        if (!$this->checkTopicFields($oTopic)) {
            return false;
        }

        // * Определяем в какой блог делаем запись
        $nBlogId = $oTopic->getBlogId();
        if ($nBlogId == 0) {
            $oBlog = E::ModuleBlog()->GetPersonalBlogByUserId($oTopic->getUserId());
        } else {
            $oBlog = E::ModuleBlog()->GetBlogById($nBlogId);
        }

        // * Если блог не определен выдаем предупреждение
        if (!$oBlog) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_create_blog_error_unknown'), E::ModuleLang()->Get('error'));
            return false;
        }

        // * Проверяем права на постинг в блог
        if (!E::ModuleACL()->IsAllowBlog($oBlog, $this->oUserCurrent)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_create_blog_error_noallow'), E::ModuleLang()->Get('error'));
            return false;
        }

        // * Проверяем разрешено ли постить топик по времени
        if (isPost('submit_topic_publish') && !$oTopic->getPublishDraft()
            && !E::ModuleACL()->CanPostTopicTime($this->oUserCurrent)
        ) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_time_limit'), E::ModuleLang()->Get('error'));
            return;
        }
        $oTopic->setBlogId($oBlog->getId());

        // * Получаемый и устанавливаем разрезанный текст по тегу <cut>
        list($sTextShort, $sTextNew, $sTextCut) = E::ModuleText()->Cut($oTopic->getTextSource());

        $oTopic->setCutText($sTextCut);
        $oTopic->setText(E::ModuleText()->Parse($sTextNew));

        // Получаем ссылки, полученные при парсинге текста
        $oTopic->setTextLinks(E::ModuleText()->GetLinks());
        $oTopic->setTextShort(E::ModuleText()->Parse($sTextShort));

        // * Изменяем вопрос/ответы, только если еще никто не голосовал
        if ($this->oContentType->isAllow('poll') && F::GetRequestStr('topic_field_question')
            && F::GetRequest('topic_field_answers', array()) && ($oTopic->getQuestionCountVote() == 0)
        ) {
            $oTopic->setQuestionTitle(strip_tags(F::GetRequestStr('topic_field_question')));
            $oTopic->clearQuestionAnswer();
            $aAnswers = F::GetRequest('topic_field_answers', array());
            foreach ($aAnswers as $sAnswer) {
                $sAnswer = trim((string)$sAnswer);
                if ($sAnswer) {
                    $oTopic->addQuestionAnswer($sAnswer);
                }
            }
        }

        $aPhotoSetData = E::ModuleMresource()->GetPhotosetData('photoset', $oTopic->getId());
        $oTopic->setPhotosetCount($aPhotoSetData['count']);
        $oTopic->setPhotosetMainPhotoId($aPhotoSetData['cover']);

        // * Publish or save as a draft
        $bSendNotify = false;
        if (isset($_REQUEST['submit_topic_publish'])) {
            // If the topic has not been published then sets date of show (publication date)
            if (!$oTopic->getPublish() && !$oTopic->getDateShow()) {
                $oTopic->setDateShow(F::Now());
            }
            $oTopic->setPublish(1);
            if ($oTopic->getPublishDraft() == 0) {
                $oTopic->setPublishDraft(1);
                $oTopic->setDateAdd(F::Now());
                $bSendNotify = true;
            }
        } else {
            $oTopic->setPublish(0);
        }

        // * Принудительный вывод на главную
        if (E::ModuleACL()->IsAllowPublishIndex($this->oUserCurrent)) {
            if (F::GetRequest('topic_publish_index')) {
                $oTopic->setPublishIndex(1);
            } else {
                $oTopic->setPublishIndex(0);
            }
        }

        // * Запрет на комментарии к топику
        $oTopic->setForbidComment(F::GetRequest('topic_forbid_comment', 0));

        // Если запрет на индексацию не устанавливался вручную, то задаем, как у блога
        $oBlogType = $oBlog->GetBlogType();
        if ($oBlogType && !$oTopic->getIndexIgnoreLock()) {
            $oTopic->setTopicIndexIgnore($oBlogType->GetIndexIgnore());
        } else {
            $oTopic->setTopicIndexIgnore(false);
        }

        $oTopic->setShowPhotoset(F::GetRequest('topic_show_photoset', 0));

        E::ModuleHook()->Run('topic_edit_before', array('oTopic' => $oTopic, 'oBlog' => $oBlog));

        // * Сохраняем топик
        if ($this->_updateTopic($oTopic)) {
            E::ModuleHook()->Run(
                'topic_edit_after', array('oTopic' => $oTopic, 'oBlog' => $oBlog, 'bSendNotify' => &$bSendNotify)
            );

            // * Обновляем данные в комментариях, если топик был перенесен в новый блог
            if ($iBlogIdOld != $oTopic->getBlogId()) {
                E::ModuleComment()->UpdateTargetParentByTargetId($oTopic->getBlogId(), 'topic', $oTopic->getId());
                E::ModuleComment()->UpdateTargetParentByTargetIdOnline($oTopic->getBlogId(), 'topic', $oTopic->getId());
            }

            // * Обновляем количество топиков в блоге
            if ($iBlogIdOld != $oTopic->getBlogId()) {
                E::ModuleBlog()->RecalculateCountTopicByBlogId($iBlogIdOld);
            }
            E::ModuleBlog()->RecalculateCountTopicByBlogId($oTopic->getBlogId());

            // * Добавляем событие в ленту
            E::ModuleStream()->Write(
                $oTopic->getUserId(), 'add_topic', $oTopic->getId(),
                $oTopic->getPublish() && (!$oBlogType || !$oBlog->getBlogType()->IsPrivate())
            );

            // * Рассылаем о новом топике подписчикам блога
            if ($bSendNotify) {
                E::ModuleTopic()->SendNotifyTopicNew($oBlog, $oTopic, $oTopic->getUser());
            }
            if (!$oTopic->getPublish()
                && !$this->oUserCurrent->isAdministrator()
                && !$this->oUserCurrent->isModerator()
                && $this->oUserCurrent->getId() != $oTopic->getUserId()
            ) {
                R::Location($oBlog->getUrlFull());
            }
            R::Location($oTopic->getUrl());
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'));
            F::SysWarning('System Error');
            return R::Action('error');
        }
    }

    /**
     * Updates topic
     *
     * @param $oTopic
     *
     * @return bool
     */
    protected function _updateTopic($oTopic) {

        return E::ModuleTopic()->UpdateTopic($oTopic);
    }

    /**
     * Удаление топика
     *
     */
    protected function EventDelete() {

        E::ModuleSecurity()->ValidateSendForm();

        // * Получаем номер топика из УРЛ и проверяем существует ли он
        $sTopicId = $this->GetParam(0);
        if (!($oTopic = E::ModuleTopic()->GetTopicById($sTopicId))) {
            return parent::EventNotFound();
        }

        // * проверяем есть ли право на удаление топика
        if (!E::ModuleACL()->IsAllowDeleteTopic($oTopic, $this->oUserCurrent)) {
            return parent::EventNotFound();
        }

        // * Удаляем топик
        E::ModuleHook()->Run('topic_delete_before', array('oTopic' => $oTopic));
        if ($this->_deleteTopic($oTopic)) {
            E::ModuleHook()->Run('topic_delete_after', array('oTopic' => $oTopic));

            // * Перенаправляем на страницу со списком топиков из блога этого топика
            R::Location($oTopic->getBlog()->getUrlFull());
        } else {
            R::Location($oTopic->getUrl());
        }
    }

    /**
     * Deletes the topic
     *
     * @param $oTopic
     *
     * @return bool
     */
    protected function _deleteTopic($oTopic) {

        return E::ModuleTopic()->DeleteTopic($oTopic);
    }

    /**
     * Выводит список топиков
     *
     */
    protected function EventShowTopics() {
        /**
         * Меню
         */
        $this->sMenuSubItemSelect = $this->sCurrentEvent;
        /**
         * Передан ли номер страницы
         */
        $iPage = $this->GetParamEventMatch(0, 2) ? $this->GetParamEventMatch(0, 2) : 1;
        /**
         * Получаем список топиков
         */
        $aResult = E::ModuleTopic()->GetTopicsPersonalByUser(
            $this->oUserCurrent->getId(), $this->sCurrentEvent == 'published' ? 1 : 0, $iPage,
            Config::Get('module.topic.per_page')
        );
        $aTopics = $aResult['collection'];
        /**
         * Формируем постраничность
         */
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.topic.per_page'), Config::Get('pagination.pages.count'),
            R::GetPath('content') . $this->sCurrentEvent
        );
        /**
         * Загружаем переменные в шаблон
         */
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        E::ModuleViewer()->Assign('aTopics', $aTopics);
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('topic_menu_' . $this->sCurrentEvent));
    }

    /**
     * AJAX загрузка изображения в фотосет
     *
     * @return bool
     */
    protected function EventAjaxPhotoUpload() {

        // Устанавливаем формат Ajax ответа. Здесь всегда json, поскольку грузится
        // картинка с помощью flash
        E::ModuleViewer()->SetResponseAjax('json', false);

        // * Проверяем авторизован ли юзер
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('not_access'), E::ModuleLang()->Get('error'));
            return false;
        }

        // * Файл был загружен?
        $aUploadedFile = $this->GetUploadedFile('Filedata');
        if (!$aUploadedFile) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            F::SysWarning('System Error');
            return false;
        }

        $iTopicId = intval(F::GetRequestStr('topic_id'));
        $sTargetId = null;

        // Если от сервера не пришёл ID топика, то пытаемся определить временный код для нового топика.
        // Если и его нет, то это ошибка
        if (!$iTopicId) {
            $sTargetId = E::ModuleSession()->GetCookie('ls_photoset_target_tmp');
            if (!$sTargetId) {
                $sTargetId = F::GetRequestStr('ls_photoset_target_tmp');
            }
            if (!$sTargetId) {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                F::SysWarning('System Error');
                return false;
            }
            $iCountPhotos = E::ModuleTopic()->GetCountPhotosByTargetTmp($sTargetId);
        } else {
            // * Загрузка фото к уже существующему топику
            $oTopic = E::ModuleTopic()->GetTopicById($iTopicId);
            if (!$oTopic || !E::ModuleACL()->IsAllowEditTopic($oTopic, $this->oUserCurrent)) {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                F::SysWarning('System Error');
                return false;
            }
            $iCountPhotos = E::ModuleTopic()->GetCountPhotosByTopicId($iTopicId);
        }

        // * Максимальное количество фото в топике
        if (Config::Get('module.topic.photoset.count_photos_max') && $iCountPhotos >= Config::Get('module.topic.photoset.count_photos_max')) {
            E::ModuleMessage()->AddError(
                E::ModuleLang()->Get(
                    'topic_photoset_error_too_much_photos',
                    array('MAX' => Config::Get('module.topic.photoset.count_photos_max'))
                ), E::ModuleLang()->Get('error')
            );
            return false;
        }

        // * Максимальный размер фото
        if (filesize($aUploadedFile['tmp_name']) > Config::Get('module.topic.photoset.photo_max_size') * 1024) {
            E::ModuleMessage()->AddError(
                E::ModuleLang()->Get(
                    'topic_photoset_error_bad_filesize',
                    array('MAX' => Config::Get('module.topic.photoset.photo_max_size'))
                ), E::ModuleLang()->Get('error')
            );
            return false;
        }

        // * Загружаем файл
        $sFile = E::ModuleTopic()->UploadTopicPhoto($aUploadedFile);
        if ($sFile) {
            // * Создаем фото
            $oPhoto = E::GetEntity('Topic_TopicPhoto');
            $oPhoto->setPath($sFile);
            if ($iTopicId) {
                $oPhoto->setTopicId($iTopicId);
            } else {
                $oPhoto->setTargetTmp($sTargetId);
            }
            if ($oPhoto = E::ModuleTopic()->AddTopicPhoto($oPhoto)) {
                // * Если топик уже существует (редактирование), то обновляем число фотографий в нём
                if (isset($oTopic)) {
                    $oTopic->setPhotosetCount($oTopic->getPhotosetCount() + 1);
                    E::ModuleTopic()->UpdateTopic($oTopic);
                }

                E::ModuleViewer()->AssignAjax('file', $oPhoto->getWebPath('100crop'));
                E::ModuleViewer()->AssignAjax('id', $oPhoto->getId());
                E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('topic_photoset_photo_added'), E::ModuleLang()->Get('attention'));

                return true;
            } else {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                F::SysWarning('System Error');
            }
        } else {
            $sMsg = E::ModuleTopic()->UploadPhotoError();
            if (!$sMsg) {
                $sMsg = E::ModuleLang()->Get('system_error');
            }
            E::ModuleMessage()->AddError($sMsg, E::ModuleLang()->Get('error'));
        }
        return false;
    }

    protected function EventPhotoUpload() {

        return $this->EventAjaxPhotoUpload();
    }

    /**
     * AJAX установка описания фото
     *
     */
    protected function EventAjaxPhotoDescription() {
        /**
         * Устанавливаем формат Ajax ответа
         */
        E::ModuleViewer()->SetResponseAjax('json');
        /**
         * Проверяем авторизован ли юзер
         */
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('not_access'), E::ModuleLang()->Get('error'));
            return R::Action('error');
        }
        /**
         * Поиск фото по id
         */
        $oPhoto = E::ModuleTopic()->GetTopicPhotoById(F::GetRequestStr('id'));
        if ($oPhoto) {
            $sDescription = htmlspecialchars(strip_tags(F::GetRequestStr('text')));
            if ($sDescription != $oPhoto->getDescription()) {
                if ($oPhoto->getTopicId()) {
                    // проверяем права на топик
                    $oTopic = E::ModuleTopic()->GetTopicById($oPhoto->getTopicId());
                    if ($oTopic && E::ModuleACL()->IsAllowEditTopic($oTopic, $this->oUserCurrent)) {
                        $oPhoto->setDescription(htmlspecialchars(strip_tags(F::GetRequestStr('text'))));
                        E::ModuleTopic()->UpdateTopicPhoto($oPhoto);
                    }
                } else {
                    $oPhoto->setDescription(htmlspecialchars(strip_tags(F::GetRequestStr('text'))));
                    E::ModuleTopic()->UpdateTopicPhoto($oPhoto);
                }
                E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('topic_photoset_description_done'));
            }
        }
    }

    protected function EventPhotoDescription() {

        return $this->EventAjaxPhotoDescription();
    }

    /**
     * AJAX подгрузка следующих фото
     *
     */
    protected function EventAjaxPhotoGetMore() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');

        // * Существует ли топик
        $iTopicId = F::GetRequestStr('topic_id');
        $iLastId = F::GetRequest('last_id');
        $sThumbSize = F::GetRequest('thumb_size');
        if (!$sThumbSize) {
            $sThumbSize = '50crop';
        }
        if (!$iTopicId || !($oTopic = E::ModuleTopic()->GetTopicById($iTopicId)) || !$iLastId) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            F::SysWarning('System Error');
            return;
        }

        // * Получаем список фото
        /** @var ModuleMresource_EntityMresourceRel[] $aPhotos */
        $aPhotos = $oTopic->getPhotosetPhotos($iLastId, Config::Get('module.topic.photoset.per_page'));
        $aResult = array();
        if (count($aPhotos)) {
            // * Формируем данные для ajax ответа
            foreach ($aPhotos as $oPhoto) {
                $aResult[] = array(
                    'id'          => $oPhoto->getMresourceId(),
                    //'path_thumb'  => $oPhoto->getLink($sThumbSize),
                    //'path'        => $oPhoto->getLink(),
                    'path_thumb' => $oPhoto->getWebPath($sThumbSize),
                    'path' => $oPhoto->getWebPath(),
                    'description' => $oPhoto->getDescription(),
                );
            }
            E::ModuleViewer()->AssignAjax('photos', $aResult);
        }
        E::ModuleViewer()->AssignAjax('bHaveNext', count($aPhotos) == Config::Get('module.topic.photoset.per_page'));
    }

    /**
     * DEPRECATED
     */
    protected function EventPhotoGetMore() {

        return $this->EventAjaxPhotoGetMore();
    }

    /**
     * AJAX удаление фото
     *
     */
    protected function EventAjaxPhotoDelete() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');

        // * Проверяем авторизован ли юзер
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('not_access'), E::ModuleLang()->Get('error'));
            return false;
        }

        // * Поиск фото по id
        $oPhoto = E::ModuleTopic()->GetTopicPhotoById($this->GetPost('id'));
        if ($oPhoto) {
            if ($oPhoto->getTopicId()) {

                // * Проверяем права на топик
                $oTopic = E::ModuleTopic()->GetTopicById($oPhoto->getTopicId());
                if ($oTopic && E::ModuleACL()->IsAllowEditTopic($oTopic, $this->oUserCurrent)) {
                    E::ModuleTopic()->DeleteTopicPhoto($oPhoto);

                    // * Если удаляем главную фотографию. топика, то её необходимо сменить
                    if ($oPhoto->getId() == $oTopic->getPhotosetMainPhotoId() && $oTopic->getPhotosetCount() > 1) {
                        $aPhotos = $oTopic->getPhotosetPhotos(0, 1);
                        $oTopic->setPhotosetMainPhotoId($aPhotos[0]->getMresourceId());
                    } elseif ($oTopic->getPhotosetCount() == 1) {
                        $oTopic->setPhotosetMainPhotoId(null);
                    }
                    $oTopic->setPhotosetCount($oTopic->getPhotosetCount() - 1);
                    E::ModuleTopic()->UpdateTopic($oTopic);
                    E::ModuleMessage()->AddNotice(
                        E::ModuleLang()->Get('topic_photoset_photo_deleted'), E::ModuleLang()->Get('attention')
                    );
                    return;
                }
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                return;
            }
            E::ModuleTopic()->DeleteTopicPhoto($oPhoto);
            E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('topic_photoset_photo_deleted'), E::ModuleLang()->Get('attention'));
            return;
        }
        E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
        return;
    }

    protected function EventPhotoDelete() {

        return $this->EventAjaxPhotoDelete();
    }

    /**
     * Переход по ссылке с подсчетом количества переходов
     *
     */
    protected function EventGo() {

        // * Получаем номер топика из УРЛ и проверяем существует ли он
        $iTopicId = intval($this->GetParam(0));
        if (!$iTopicId || !($oTopic = E::ModuleTopic()->GetTopicById($iTopicId)) || !$oTopic->getPublish()) {
            return parent::EventNotFound();
        }

        // * проверяем есть ли ссылка на источник
        if (!$oTopic->getSourceLink()) {
            return parent::EventNotFound();
        }

        // * увелививаем число переходов по ссылке
        $oTopic->setSourceLinkCountJump($oTopic->getSourceLinkCountJump() + 1);
        E::ModuleTopic()->UpdateTopic($oTopic);

        // * собственно сам переход по ссылке
        R::Location($oTopic->getSourceLink());
    }

    /*
     * Обработка дополнительных полей
     */
    public function processFields($oTopic) {
    }

    /**
     * Проверка полей формы
     *
     * @param $oTopic
     *
     * @return bool
     */
    protected function checkTopicFields($oTopic) {

        E::ModuleSecurity()->ValidateSendForm();

        $bOk = true;
        /**
         * Валидируем топик
         */
        if (!$oTopic->_Validate()) {
            E::ModuleMessage()->AddError($oTopic->_getValidateError(), E::ModuleLang()->Get('error'));
            $bOk = false;
        }
        /**
         * Выполнение хуков
         */
        E::ModuleHook()->Run('check_topic_fields', array('bOk' => &$bOk));

        return $bOk;
    }

    /**
     * При завершении экшена загружаем необходимые переменные
     *
     */
    public function EventShutdown() {

        E::ModuleViewer()->Assign('sMenuHeadItemSelect', $this->sMenuHeadItemSelect);
        E::ModuleViewer()->Assign('sMenuItemSelect', $this->sMenuItemSelect);
        E::ModuleViewer()->Assign('sMenuSubItemSelect', $this->sMenuSubItemSelect);
    }

}

// EOF
