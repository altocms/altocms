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
 * Экшен обработки ajax запросов
 * Ответ отдает в JSON фомате
 *
 * @package actions
 * @since   1.0
 */
class ActionAjax extends Action {
    /**
     * Текущий пользователь
     *
     * @var ModuleUser_EntityUser|null
     */
    protected $oUserCurrent = null;

    /**
     * Инициализация
     */
    public function Init() {

        // * Устанавливаем формат ответа
        E::ModuleViewer()->SetResponseAjax('json');

        // * Получаем текущего пользователя
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();
    }

    /**
     * Регистрация евентов
     */
    protected function RegisterEvent() {
        if (C::Get('rating.enabled')) {
            $this->AddEventPreg('/^vote$/i', '/^comment$/', 'EventVoteComment');
            $this->AddEventPreg('/^vote$/i', '/^topic$/', 'EventVoteTopic');
            $this->AddEventPreg('/^vote$/i', '/^blog$/', 'EventVoteBlog');
            $this->AddEventPreg('/^vote$/i', '/^user$/', 'EventVoteUser');
        }

        $this->AddEventPreg('/^vote$/i', '/^poll$/', 'EventVotePoll');
        $this->AddEventPreg('/^vote$/i', '/^question$/', 'EventVoteQuestion');

        $this->AddEventPreg('/^favourite$/i', '/^save-tags/', 'EventFavouriteSaveTags');
        $this->AddEventPreg('/^favourite$/i', '/^topic$/', 'EventFavouriteTopic');
        $this->AddEventPreg('/^favourite$/i', '/^comment$/', 'EventFavouriteComment');
        $this->AddEventPreg('/^favourite$/i', '/^talk$/', 'EventFavouriteTalk');

        $this->AddEventPreg('/^stream$/i', '/^comment$/', 'EventStreamComment');
        $this->AddEventPreg('/^stream$/i', '/^topic$/', 'EventStreamTopic');
        $this->AddEventPreg('/^stream$/i', '/^wall/', 'EventStreamWall');

        $this->AddEventPreg('/^blogs$/i', '/^top$/', 'EventBlogsTop');
        $this->AddEventPreg('/^blogs$/i', '/^self$/', 'EventBlogsSelf');
        $this->AddEventPreg('/^blogs$/i', '/^join$/', 'EventBlogsJoin');

        $this->AddEventPreg('/^preview$/i', '/^text$/', 'EventPreviewText');
        $this->AddEventPreg('/^preview$/i', '/^topic/', 'EventPreviewTopic');

        $this->AddEventPreg('/^upload$/i', '/^image$/', 'EventUploadImage');

        $this->AddEventPreg('/^autocompleter$/i', '/^tag$/', 'EventAutocompleterTag');
        $this->AddEventPreg('/^autocompleter$/i', '/^user$/', 'EventAutocompleterUser');

        $this->AddEventPreg('/^comment$/i', '/^delete$/', 'EventCommentDelete');

        $this->AddEventPreg('/^geo/i', '/^get/', '/^regions$/', 'EventGeoGetRegions');
        $this->AddEventPreg('/^geo/i', '/^get/', '/^cities/', 'EventGeoGetCities');

        $this->AddEventPreg('/^infobox/i', '/^info/', '/^blog/', 'EventInfoboxInfoBlog');

        $this->AddEvent('fetch', 'EventFetch');

        // Менеджер изображений
        $this->AddEvent('image-manager-load-tree', 'EventImageManagerLoadTree');
        $this->AddEvent('image-manager-load-images', 'EventImageManagerLoadImages');

    }


    /**********************************************************************************
     ************************ РЕАЛИЗАЦИЯ ЭКШЕНА ***************************************
     **********************************************************************************
     */

    /**
     * Загрузка страницы картинок
     */
    protected function EventImageManagerLoadImages(){

        E::ModuleSecurity()->ValidateSendForm();

        // Менеджер изображений может запускаться в том числе и из админки
        // Если передано название скина админки, то используем его, если же
        // нет, то ту тему, которая установлена для сайта
        if (($sAdminTheme = F::GetRequest('admin')) && E::IsAdmin()) {
            C::Set('view.skin', $sAdminTheme);
        }

        // Получим идентификатор пользователя, изображения которого нужно загрузить
        $iUserId = (int)F::GetRequest('profile', FALSE);
        if ($iUserId && E::ModuleUser()->GetUserById($iUserId)) {
            C::Set('menu.data.profile_images.uid', $iUserId);
        } else {
            // Только пользователь может смотреть своё дерево изображений
            if (!E::IsUser()) {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'));
                return;
            }
            $iUserId = E::UserId();
        }

        $sCategory = F::GetRequestStr('category', FALSE);
        $sPage = F::GetRequestStr('page', '1');
        $sTopicId = F::GetRequestStr('topic_id', FALSE);
        $sTargetType = F::GetRequestStr('target');

        if (!$sCategory) {
            return;
        }

        $aTplVariables = array(
            'sTargetType' => $sTargetType,
            'sTargetId' => $sTopicId,
        );

        // Страница загрузки картинки с компьютера
        if ($sCategory == 'insert-from-pc') {
            $sImages = E::ModuleViewer()->Fetch('modals/insert_img/inject.pc.tpl', $aTplVariables);
            E::ModuleViewer()->AssignAjax('images', $sImages);
            return;
        }

        // Страница загрузки из интернета
        if ($sCategory == 'insert-from-link') {
            $sImages = E::ModuleViewer()->Fetch('modals/insert_img/inject.link.tpl', $aTplVariables);
            E::ModuleViewer()->AssignAjax('images', $sImages);
            return;
        }

        $sTemplateName = 'inject.images.tpl';

        $aResources = array('collection'=>array());
        $iPages = 0;
        if ($sCategory == 'user') {       //ок

            // * Аватар и фото пользователя

            $aResources = E::ModuleMresource()->GetMresourcesByFilter(array(
                'target_type' => array(
                    'profile_avatar',
                    'profile_photo'
                ),
                'user_id'     => $iUserId,
            ), $sPage, Config::Get('module.topic.images_per_page'));
            $sTemplateName = 'inject.images.user.tpl';
            $iPages = 0;
        } elseif ($sCategory == '_topic') {

            // * Конкретный топик

            $oTopic = E::ModuleTopic()->GetTopicById($sTopicId);
            if ($oTopic
                && ($oTopic->isPublished() || $oTopic->getUserId() == E::UserId())
                && E::ModuleACL()->IsAllowShowBlog($oTopic->getBlog(), E::User())) {
                $aResourcesId = E::ModuleMresource()->GetCurrentTopicResourcesId($iUserId, $sTopicId);
                if ($aResourcesId) {
                    $aResources = E::ModuleMresource()->GetMresourcesByFilter(array(
                        'user_id' => $iUserId,
                        'mresource_id' => $aResourcesId,
                    ), $sPage, Config::Get('module.topic.images_per_page'));
                    $aResources['count'] = count($aResourcesId);
                    $iPages = ceil($aResources['count'] / Config::Get('module.topic.images_per_page'));

                    $aTplVariables['oTopic'] = $oTopic;
                }
            }

            $sTemplateName = 'inject.images.tpl';

        } elseif ($sCategory == 'talk') {

            // * Письмо

            /** @var ModuleTalk_EntityTalk $oTopic */
            $oTopic = E::ModuleTalk()->GetTalkById($sTopicId);
            if ($oTopic && E::ModuleTalk()->GetTalkUser($sTopicId, $iUserId)) {

                $aResources = E::ModuleMresource()->GetMresourcesByFilter(array(
                    'user_id' => $iUserId,
                    'target_type' => 'talk',
                    'target_id' => $sTopicId,
                ), $sPage, Config::Get('module.topic.images_per_page'));
                $aResources['count'] = E::ModuleMresource()->GetMresourcesCountByTargetIdAndUserId('talk', $sTopicId, $iUserId);
                $iPages = ceil($aResources['count'] / Config::Get('module.topic.images_per_page'));

                $aTplVariables['oTopic'] = $oTopic;
            }

            $sTemplateName = 'inject.images.tpl';

        } elseif ($sCategory == 'comments') {

            // * Комментарии

            $aResources = E::ModuleMresource()->GetMresourcesByFilter(array(
                'user_id'     => $iUserId,
                'target_type' => array(
                    'talk_comment',
                    'topic_comment'
                )
            ), $sPage, Config::Get('module.topic.images_per_page'));
            $aResources['count'] = E::ModuleMresource()->GetMresourcesCountByTargetAndUserId(array(
                'talk_comment',
                'topic_comment'
            ), $iUserId);
            $iPages = ceil($aResources['count'] / Config::Get('module.topic.images_per_page'));

            $sTemplateName = 'inject.images.tpl';

        } elseif ($sCategory == 'current') {       //ок

            // * Картинки текущего топика (текст, фотосет, одиночные картинки)

            $aResourcesId = E::ModuleMresource()->GetCurrentTopicResourcesId($iUserId, $sTopicId);
            if ($aResourcesId) {
                $aResources = E::ModuleMresource()->GetMresourcesByFilter(array(
                    'user_id' => $iUserId,
                    'mresource_id' => $aResourcesId,
                ), $sPage, Config::Get('module.topic.images_per_page'));
                $aResources['count'] = count($aResourcesId);
                $iPages = ceil($aResources['count'] / Config::Get('module.topic.images_per_page'));

            }

            $sTemplateName = 'inject.images.tpl';


        } elseif ($sCategory == 'blog_avatar') { // ок

            // * Аватары созданных блогов

            $aResources = E::ModuleMresource()->GetMresourcesByFilter(array(
                'target_type' => 'blog_avatar',
                'user_id' => $iUserId,
            ), $sPage, Config::Get('module.topic.group_images_per_page'));
            $aResources['count'] = E::ModuleMresource()->GetMresourcesCountByTargetAndUserId('blog_avatar', $iUserId);

            // Получим блоги
            $aBlogsId = array();
            foreach ($aResources['collection'] as $oResource) {
                $aBlogsId[] = $oResource->getTargetId();
            }
            if ($aBlogsId) {
                $aBlogs = E::ModuleBlog()->GetBlogsAdditionalData($aBlogsId);
                $aTplVariables['aBlogs'] = $aBlogs;
            }

            $sTemplateName = 'inject.images.blog.tpl';
            $iPages = ceil($aResources['count'] / Config::Get('module.topic.group_images_per_page'));


        } elseif ($sCategory == 'topics') { // ок

            // * Страница топиков

            $aTopicsData = E::ModuleMresource()->GetTopicsPage($iUserId, $sPage, Config::Get('module.topic.group_images_per_page'));

            $aTplVariables['aTopics'] = $aTopicsData['collection'];

            $sTemplateName = 'inject.images.topic.tpl';
            $iPages = ceil($aTopicsData['count'] / Config::Get('module.topic.group_images_per_page'));
            $aResources= array('collection'=>array());

        }  elseif (in_array($sCategory, E::ModuleTopic()->GetTopicTypes())) { // ок

            // * Страница топиков

            $aTopicsData = E::ModuleMresource()->GetTopicsPageByType($iUserId, $sCategory, $sPage, Config::Get('module.topic.group_images_per_page'));

            $aTplVariables['aTopics'] = $aTopicsData['collection'];

            $sTemplateName = 'inject.images.topic.tpl';
            $iPages = ceil($aTopicsData['count'] / Config::Get('module.topic.group_images_per_page'));
            $aResources= array('collection'=>array());

        } elseif ($sCategory == 'talks') { // ок

            // * Страница писем

            $aTalksData = E::ModuleMresource()->GetTalksPage($iUserId, $sPage, Config::Get('module.topic.group_images_per_page'));

            $aTplVariables['aTalks'] = $aTalksData['collection'];
            $sTemplateName = 'inject.images.talk.tpl';
            $iPages = ceil($aTalksData['count'] / Config::Get('module.topic.group_images_per_page'));
            $aResources= array('collection'=>array());

        } else {

            // * Прочие изображения

            $aResources = E::ModuleMresource()->GetMresourcesByFilter(array(
                'target_type' => $sCategory,
                'user_id' => $iUserId,
            ), $sPage, Config::Get('module.topic.images_per_page'));
            $iPages = ceil($aResources['count'] / Config::Get('module.topic.images_per_page'));
        }

        $aTplVariables['aResources'] = $aResources['collection'];

        $sPath = F::GetRequest('profile', FALSE) ? 'actions/profile/created_photos/' : 'modals/insert_img/';
        $sImages = E::ModuleViewer()->GetLocalViewer()->Fetch($sPath . $sTemplateName, $aTplVariables);

        E::ModuleViewer()->AssignAjax('images', $sImages);
        E::ModuleViewer()->AssignAjax('category', $sCategory);
        E::ModuleViewer()->AssignAjax('page', $sPage);
        E::ModuleViewer()->AssignAjax('pages', $iPages);

    }


    /**
     * Загрузка дерева изображений пользователя
     */
    protected function EventImageManagerLoadTree(){

        // Менеджер изображений может запускаться в том числе и из админки
        // Если передано название скина админки, то используем его, если же
        // нет, то ту тему, которая установлена для сайта
        if (($sAdminTheme = F::GetRequest('admin')) && E::IsAdmin()) {
            C::Set('view.skin', $sAdminTheme);
        }

        $sPath = ($iUserId = (int)F::GetRequest('profile', FALSE)) ? 'actions/profile/created_photos/' : 'modals/insert_img/';
        if ($iUserId && E::ModuleUser()->GetUserById($iUserId)) {
            C::Set('menu.data.profile_images.uid', $iUserId);
        } else {
            $iUserId = false;
        }

        if ($iUserId) {
            $aVars = array('iUserId' => $iUserId);
            $sCategories = E::ModuleViewer()->GetLocalViewer()->Fetch("{$sPath}inject.categories.tpl", $aVars);
        } else {
            $sCategories = E::ModuleViewer()->GetLocalViewer()->Fetch( "{$sPath}inject.categories.tpl");
        }

        E::ModuleViewer()->AssignAjax('categories', $sCategories);

        return FALSE;
    }


    /**
     * Вывод информации о блоге
     */
    protected function EventInfoboxInfoBlog() {

        // * Если блог существует и он не персональный
        if (!is_string(F::GetRequest('iBlogId'))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'));
            return;
        }

        if (!($oBlog = E::ModuleBlog()->GetBlogById(F::GetRequest('iBlogId'))) /* || $oBlog->getType()=='personal'*/) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'));
            return;
        }

        $aVars = array('oBlog' => $oBlog);

        // Тип блога может быть не определен
        if (!$oBlog->getBlogType() || !$oBlog->getBlogType()->IsPrivate() || $oBlog->getUserIsJoin()) {
            // * Получаем последний топик
            $aResult = E::ModuleTopic()->GetTopicsByFilter(array('blog_id' => $oBlog->getId(), 'topic_publish' => 1), 1, 1);
            $aVars['oTopicLast'] = reset($aResult['collection']);
        }

        // * Устанавливаем переменные для ajax ответа
        E::ModuleViewer()->AssignAjax('sText', E::ModuleViewer()->Fetch('commons/common.infobox_blog.tpl', $aVars));
    }

    /**
     * Получение списка регионов по стране
     */
    protected function EventGeoGetRegions() {

        $iCountryId = F::GetRequestStr('country');
        $iLimit = 200;
        if (is_numeric(F::GetRequest('limit')) && F::GetRequest('limit') > 0) {
            $iLimit = F::GetRequest('limit');
        }

        // * Находим страну
        if (!($oCountry = E::ModuleGeo()->GetGeoObject('country', $iCountryId))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'));
            return;
        }

        // * Получаем список регионов
        $aResult = E::ModuleGeo()->GetRegions(array('country_id' => $oCountry->getId()), array('sort' => 'asc'), 1, $iLimit);
        $aRegions = array();
        foreach ($aResult['collection'] as $oObject) {
            $aRegions[] = array(
                'id'   => $oObject->getId(),
                'name' => $oObject->getName(),
            );
        }

        // * Устанавливаем переменные для ajax ответа
        E::ModuleViewer()->AssignAjax('aRegions', $aRegions);
    }

    /**
     * Получение списка городов по региону
     */
    protected function EventGeoGetCities() {

        $iRegionId = F::GetRequestStr('region');
        $iLimit = 500;
        if (is_numeric(F::GetRequest('limit')) && F::GetRequest('limit') > 0) {
            $iLimit = F::GetRequest('limit');
        }

        // * Находим регион
        if (!($oRegion = E::ModuleGeo()->GetGeoObject('region', $iRegionId))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'));
            return;
        }

        // * Получаем города
        $aResult = E::ModuleGeo()->GetCities(array('region_id' => $oRegion->getId()), array('sort' => 'asc'), 1, $iLimit);
        $aCities = array();
        foreach ($aResult['collection'] as $oObject) {
            $aCities[] = array(
                'id'   => $oObject->getId(),
                'name' => $oObject->getName(),
            );
        }

        // * Устанавливаем переменные для ajax ответа
        E::ModuleViewer()->AssignAjax('aCities', $aCities);
    }

    /**
     * Голосование за комментарий
     *
     */
    protected function EventVoteComment() {

        // * Пользователь авторизован?
        if (!$this->oUserCurrent) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Комментарий существует?
        if (!($oComment = E::ModuleComment()->GetCommentById(F::GetRequestStr('idComment', null, 'post')))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('comment_vote_error_noexists'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Голосует автор комментария?
        if ($oComment->getUserId() == $this->oUserCurrent->getId()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('comment_vote_error_self'), E::ModuleLang()->Get('attention'));
            return;
        }

        // * Пользователь уже голосовал?
        if ($oTopicCommentVote = E::ModuleVote()->GetVote($oComment->getId(), 'comment', $this->oUserCurrent->getId())) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('comment_vote_error_already'), E::ModuleLang()->Get('attention'));
            return;
        }

        // * Время голосования истекло?
        if (strtotime($oComment->getDate()) <= time() - Config::Get('acl.vote.comment.limit_time')) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('comment_vote_error_time'), E::ModuleLang()->Get('attention'));
            return;
        }

        // * Пользователь имеет право голоса?
        switch (E::ModuleACL()->CanVoteComment($this->oUserCurrent, $oComment)) {
            case ModuleACL::CAN_VOTE_COMMENT_ERROR_BAN:
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('comment_vote_error_banned'), E::ModuleLang()->Get('attention'));
                return;
                break;

            case ModuleACL::CAN_VOTE_COMMENT_FALSE:
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('comment_vote_error_acl'), E::ModuleLang()->Get('attention'));
                return;
                break;
        }

        // * Как именно голосует пользователь
        $iValue = F::GetRequestStr('value', null, 'post');
        if (!in_array($iValue, array('1', '-1'))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('comment_vote_error_value'), E::ModuleLang()->Get('attention'));
            return;
        }

        // * Голосуем
        $oTopicCommentVote = E::GetEntity('Vote');
        $oTopicCommentVote->setTargetId($oComment->getId());
        $oTopicCommentVote->setTargetType('comment');
        $oTopicCommentVote->setVoterId($this->oUserCurrent->getId());
        $oTopicCommentVote->setDirection($iValue);
        $oTopicCommentVote->setDate(F::Now());
        $iVal = (float)E::ModuleRating()->VoteComment($this->oUserCurrent, $oComment, $iValue);
        $oTopicCommentVote->setValue($iVal);

        $oComment->setCountVote($oComment->getCountVote() + 1);
        if (E::ModuleVote()->AddVote($oTopicCommentVote) && E::ModuleComment()->UpdateComment($oComment)) {
            E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('comment_vote_ok'), E::ModuleLang()->Get('attention'));
            E::ModuleViewer()->AssignAjax('iRating', $oComment->getRating());
            /**
             * Добавляем событие в ленту
             */
            E::ModuleStream()->Write($oTopicCommentVote->getVoterId(), 'vote_comment', $oComment->getId());
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('comment_vote_error'), E::ModuleLang()->Get('error'));
            return;
        }
    }

    /**
     * Голосование за топик
     *
     */
    protected function EventVoteTopic() {

        // * Пользователь авторизован?
        if (!$this->oUserCurrent) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Топик существует?
        if (!($oTopic = E::ModuleTopic()->GetTopicById(F::GetRequestStr('idTopic', null, 'post')))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Голосует автор топика?
        if ($oTopic->getUserId() == $this->oUserCurrent->getId()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_vote_error_self'), E::ModuleLang()->Get('attention'));
            return;
        }

        // * Пользователь уже голосовал?
        if ($oTopicVote = E::ModuleVote()->GetVote($oTopic->getId(), 'topic', $this->oUserCurrent->getId())) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_vote_error_already'), E::ModuleLang()->Get('attention'));
            return;
        }

        // * Время голосования истекло?
        if (strtotime($oTopic->getDateAdd()) <= time() - Config::Get('acl.vote.topic.limit_time')) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_vote_error_time'), E::ModuleLang()->Get('attention'));
            return;
        }

        // * Как проголосовал пользователь
        $iValue = F::GetRequestStr('value', null, 'post');
        if (!in_array($iValue, array('1', '-1', '0'))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('attention'));
            return;
        }

        // * Права на голосование
        switch (E::ModuleACL()->CanVoteTopic($this->oUserCurrent, $oTopic)) {
            case ModuleACL::CAN_VOTE_TOPIC_ERROR_BAN:
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_vote_error_banned'), E::ModuleLang()->Get('attention'));
                return;
                break;

            case ModuleACL::CAN_VOTE_TOPIC_FALSE:
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_vote_error_acl'), E::ModuleLang()->Get('attention'));
                return;
                break;

            case ModuleACL::CAN_VOTE_TOPIC_NOT_IS_PUBLISHED:
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_vote_error_is_not_published'), E::ModuleLang()->Get('attention'));
                return;
                break;
        }

        // * Голосуем
        $oTopicVote = E::GetEntity('Vote');
        $oTopicVote->setTargetId($oTopic->getId());
        $oTopicVote->setTargetType('topic');
        $oTopicVote->setVoterId($this->oUserCurrent->getId());
        $oTopicVote->setDirection($iValue);
        $oTopicVote->setDate(F::Now());
        $iVal = 0;
        if ($iValue != 0) {
            $iVal = (float)E::ModuleRating()->VoteTopic($this->oUserCurrent, $oTopic, $iValue);
        }
        $oTopicVote->setValue($iVal);
        $oTopic->setCountVote($oTopic->getCountVote() + 1);
        if ($iValue == 1) {
            $oTopic->setCountVoteUp($oTopic->getCountVoteUp() + 1);
        } elseif ($iValue == -1) {
            $oTopic->setCountVoteDown($oTopic->getCountVoteDown() + 1);
        } elseif ($iValue == 0) {
            $oTopic->setCountVoteAbstain($oTopic->getCountVoteAbstain() + 1);
        }
        if (E::ModuleVote()->AddVote($oTopicVote) && E::ModuleTopic()->UpdateTopic($oTopic)) {
            if ($iValue) {
                E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('topic_vote_ok'), E::ModuleLang()->Get('attention'));
            } else {
                E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('topic_vote_ok_abstain'), E::ModuleLang()->Get('attention'));
            }
            E::ModuleViewer()->AssignAjax('iRating', $oTopic->getRating());
            /**
             * Добавляем событие в ленту
             */
            E::ModuleStream()->Write($oTopicVote->getVoterId(), 'vote_topic', $oTopic->getId());
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
    }

    /**
     * Голосование за блог
     *
     */
    protected function EventVoteBlog() {
        /**
         * Пользователь авторизован?
         */
        if (!$this->oUserCurrent) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        /**
         * Блог существует?
         */
        if (!($oBlog = E::ModuleBlog()->GetBlogById(F::GetRequestStr('idBlog', null, 'post')))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
        /**
         * Голосует за свой блог?
         */
        if ($oBlog->getOwnerId() == $this->oUserCurrent->getId()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('blog_vote_error_self'), E::ModuleLang()->Get('attention'));
            return;
        }
        /**
         * Уже голосовал?
         */
        if ($oBlogVote = E::ModuleVote()->GetVote($oBlog->getId(), 'blog', $this->oUserCurrent->getId())) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('blog_vote_error_already'), E::ModuleLang()->Get('attention'));
            return;
        }
        /**
         * Имеет право на голосование?
         */
        switch (E::ModuleACL()->CanVoteBlog($this->oUserCurrent, $oBlog)) {
            case ModuleACL::CAN_VOTE_BLOG_TRUE:
                $iValue = F::GetRequestStr('value', null, 'post');
                if (in_array($iValue, array('1', '-1'))) {
                    $oBlogVote = E::GetEntity('Vote');
                    $oBlogVote->setTargetId($oBlog->getId());
                    $oBlogVote->setTargetType('blog');
                    $oBlogVote->setVoterId($this->oUserCurrent->getId());
                    $oBlogVote->setDirection($iValue);
                    $oBlogVote->setDate(F::Now());
                    $iVal = (float)E::ModuleRating()->VoteBlog($this->oUserCurrent, $oBlog, $iValue);
                    $oBlogVote->setValue($iVal);
                    $oBlog->setCountVote($oBlog->getCountVote() + 1);
                    if (E::ModuleVote()->AddVote($oBlogVote) && E::ModuleBlog()->UpdateBlog($oBlog)) {
                        E::ModuleViewer()->AssignAjax('iCountVote', $oBlog->getCountVote());
                        E::ModuleViewer()->AssignAjax('iRating', $oBlog->getRating());
                        E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('blog_vote_ok'), E::ModuleLang()->Get('attention'));
                        /**
                         * Добавляем событие в ленту
                         */
                        E::ModuleStream()->Write($oBlogVote->getVoterId(), 'vote_blog', $oBlog->getId());
                    } else {
                        E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('attention'));
                        return;
                    }
                } else {
                    E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('attention'));
                    return;
                }
                break;
            case ModuleACL::CAN_VOTE_BLOG_ERROR_CLOSE:
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('blog_vote_error_close'), E::ModuleLang()->Get('attention'));
                return;
                break;
            case ModuleACL::CAN_VOTE_BLOG_ERROR_BAN:
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('blog_vote_error_banned'), E::ModuleLang()->Get('attention'));
                return;
                break;

            default:
            case ModuleACL::CAN_VOTE_BLOG_FALSE:
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('blog_vote_error_acl'), E::ModuleLang()->Get('attention'));
                return;
                break;
        }
    }

    /**
     * Голосование за пользователя
     *
     */
    protected function EventVoteUser() {
        /**
         * Пользователь авторизован?
         */
        if (!$this->oUserCurrent) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        /**
         * Пользователь существует?
         */
        if (!($oUser = E::ModuleUser()->GetUserById(F::GetRequestStr('idUser', null, 'post')))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
        /**
         * Голосует за себя?
         */
        if ($oUser->getId() == $this->oUserCurrent->getId()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('user_vote_error_self'), E::ModuleLang()->Get('attention'));
            return;
        }
        /**
         * Уже голосовал?
         */
        if ($oUserVote = E::ModuleVote()->GetVote($oUser->getId(), 'user', $this->oUserCurrent->getId())) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('user_vote_error_already'), E::ModuleLang()->Get('attention'));
            return;
        }
        /**
         * Имеет право на голосование?
         */
        if (!E::ModuleACL()->CanVoteUser($this->oUserCurrent, $oUser)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('user_vote_error_acl'), E::ModuleLang()->Get('attention'));
            return;
        }
        /**
         * Как проголосовал
         */
        $iValue = F::GetRequestStr('value', null, 'post');
        if (!in_array($iValue, array('1', '-1'))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('attention'));
            return;
        }
        /**
         * Голосуем
         */
        $oUserVote = E::GetEntity('Vote');
        $oUserVote->setTargetId($oUser->getId());
        $oUserVote->setTargetType('user');
        $oUserVote->setVoterId($this->oUserCurrent->getId());
        $oUserVote->setDirection($iValue);
        $oUserVote->setDate(F::Now());
        $iVal = (float)E::ModuleRating()->VoteUser($this->oUserCurrent, $oUser, $iValue);
        $oUserVote->setValue($iVal);
        //$oUser->setRating($oUser->getRating()+$iValue);
        $oUser->setCountVote($oUser->getCountVote() + 1);
        if (E::ModuleVote()->AddVote($oUserVote) && E::ModuleUser()->Update($oUser)) {
            E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('user_vote_ok'), E::ModuleLang()->Get('attention'));
            E::ModuleViewer()->AssignAjax('iRating', number_format($oUser->getRating(), Config::Get('view.skill_length')));
            E::ModuleViewer()->AssignAjax('iSkill', number_format($oUser->getSkill(), Config::Get('view.rating_length')));
            E::ModuleViewer()->AssignAjax('iCountVote', $oUser->getCountVote());
            /**
             * Добавляем событие в ленту
             */
            E::ModuleStream()->Write($oUserVote->getVoterId(), 'vote_user', $oUser->getId());
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
    }

    /**
     * LS-compatibility
     */
    protected function EventVoteQuestion() {

        return $this->EventVotePoll();
    }

    /**
     * Голосование за вариант ответа в опросе
     *
     */
    protected function EventVotePoll() {

        // * Пользователь авторизован?
        if (!$this->oUserCurrent) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Параметры голосования
        $idAnswer = F::GetRequestStr('idAnswer', null, 'post');
        $idTopic = F::GetRequestStr('idTopic', null, 'post');

        // * Топик существует?
        if (!($oTopic = E::ModuleTopic()->GetTopicById($idTopic))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        // *  У топика существует опрос?
        if (!$oTopic->getQuestionAnswers()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Уже голосовал?
        if ($oTopicQuestionVote = E::ModuleTopic()->GetTopicQuestionVote($oTopic->getId(), $this->oUserCurrent->getId())) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_question_vote_already'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Вариант ответа
        $aAnswer = $oTopic->getQuestionAnswers();
        if (!isset($aAnswer[$idAnswer]) && $idAnswer != -1) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        if ($idAnswer == -1) {
            $oTopic->setQuestionCountVoteAbstain($oTopic->getQuestionCountVoteAbstain() + 1);
        } else {
            $oTopic->increaseQuestionAnswerVote($idAnswer);
        }
        $oTopic->setQuestionCountVote($oTopic->getQuestionCountVote() + 1);
        $oTopic->setUserQuestionIsVote(true);

        // * Голосуем(отвечаем на опрос)
        $oTopicQuestionVote = E::GetEntity('Topic_TopicQuestionVote');
        $oTopicQuestionVote->setTopicId($oTopic->getId());
        $oTopicQuestionVote->setVoterId($this->oUserCurrent->getId());
        $oTopicQuestionVote->setAnswer($idAnswer);

        if (E::ModuleTopic()->AddTopicQuestionVote($oTopicQuestionVote) && E::ModuleTopic()->UpdateTopic($oTopic)) {
            E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('topic_question_vote_ok'), E::ModuleLang()->Get('attention'));
            $aVars = array('oTopic' => $oTopic);
            E::ModuleViewer()->AssignAjax('sText', E::ModuleViewer()->Fetch('fields/field.poll-show.tpl', $aVars));
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
    }

    /**
     * Сохраняет теги для избранного
     *
     */
    protected function EventFavouriteSaveTags() {

        // * Пользователь авторизован?
        if (!$this->oUserCurrent) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Объект уже должен быть в избранном
        if ($oFavourite = E::ModuleFavourite()->GetFavourite(F::GetRequestStr('target_id'), F::GetRequestStr('target_type'), $this->oUserCurrent->getId())) {
            // * Обрабатываем теги
            $aTags = explode(',', trim(F::GetRequestStr('tags'), "\r\n\t\0\x0B ."));
            $aTagsNew = array();
            $aTagsNewLow = array();
            $aTagsReturn = array();
            foreach ($aTags as $sTag) {
                $sTag = trim($sTag);
                if (F::CheckVal($sTag, 'text', 2, 50) && !in_array(mb_strtolower($sTag, 'UTF-8'), $aTagsNewLow)) {
                    $sTagEsc = htmlspecialchars($sTag);
                    $aTagsNew[] = $sTagEsc;
                    $aTagsReturn[] = array(
                        'tag' => $sTagEsc,
                        'url' =>
                        $this->oUserCurrent->getUserWebPath() . 'favourites/' . $oFavourite->getTargetType() . 's/tag/'
                            . $sTagEsc . '/', // костыль для URL с множественным числом
                    );
                    $aTagsNewLow[] = mb_strtolower($sTag, 'UTF-8');
                }
            }
            if (!count($aTagsNew)) {
                $oFavourite->setTags('');
            } else {
                $oFavourite->setTags(join(',', $aTagsNew));
            }
            E::ModuleViewer()->AssignAjax('aTags', $aTagsReturn);
            E::ModuleFavourite()->UpdateFavourite($oFavourite);
            return;
        }
        E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
    }

    /**
     * Обработка избранного - топик
     *
     */
    protected function EventFavouriteTopic() {

        // * Пользователь авторизован?
        if (!$this->oUserCurrent) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Можно только добавить или удалить из избранного
        $iType = F::GetRequestStr('type', null, 'post');
        if (!in_array($iType, array('1', '0'))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Топик существует?
        if (!($oTopic = E::ModuleTopic()->GetTopicById(F::GetRequestStr('idTopic', null, 'post')))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Пропускаем топик из черновиков
        if (!$oTopic->getPublish()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('error_favorite_topic_is_draft'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Топик уже в избранном?
        $oFavouriteTopic = E::ModuleTopic()->GetFavouriteTopic($oTopic->getId(), $this->oUserCurrent->getId());
        if (!$oFavouriteTopic && $iType) {
            $oFavouriteTopicNew = E::GetEntity(
                'Favourite',
                array(
                     'target_id'      => $oTopic->getId(),
                     'user_id'        => $this->oUserCurrent->getId(),
                     'target_type'    => 'topic',
                     'target_publish' => $oTopic->getPublish()
                )
            );
            $oTopic->setCountFavourite($oTopic->getCountFavourite() + 1);
            if (E::ModuleTopic()->AddFavouriteTopic($oFavouriteTopicNew) && E::ModuleTopic()->UpdateTopic($oTopic)) {
                E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('topic_favourite_add_ok'), E::ModuleLang()->Get('attention'));
                E::ModuleViewer()->AssignAjax('bState', true);
                E::ModuleViewer()->AssignAjax('iCount', $oTopic->getCountFavourite());
            } else {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                return;
            }
        }
        if (!$oFavouriteTopic && !$iType) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_favourite_add_no'), E::ModuleLang()->Get('error'));
            return;
        }
        if ($oFavouriteTopic && $iType) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_favourite_add_already'), E::ModuleLang()->Get('error'));
            return;
        }
        if ($oFavouriteTopic && !$iType) {
            $oTopic->setCountFavourite($oTopic->getCountFavourite() - 1);
            if (E::ModuleTopic()->DeleteFavouriteTopic($oFavouriteTopic) && E::ModuleTopic()->UpdateTopic($oTopic)) {
                E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('topic_favourite_del_ok'), E::ModuleLang()->Get('attention'));
                E::ModuleViewer()->AssignAjax('bState', false);
                E::ModuleViewer()->AssignAjax('iCount', $oTopic->getCountFavourite());
            } else {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                return;
            }
        }
    }

    /**
     * Обработка избранного - комментарий
     *
     */
    protected function EventFavouriteComment() {

        // * Пользователь авторизован?
        if (!$this->oUserCurrent) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Можно только добавить или удалить из избранного
        $iType = F::GetRequestStr('type', null, 'post');
        if (!in_array($iType, array('1', '0'))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Комментарий существует?
        if (!($oComment = E::ModuleComment()->GetCommentById(F::GetRequestStr('idComment', null, 'post')))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
        // * Комментарий уже в избранном?
        $oFavouriteComment = E::ModuleComment()->GetFavouriteComment($oComment->getId(), $this->oUserCurrent->getId());
        if (!$oFavouriteComment && $iType) {
            $oFavouriteCommentNew = E::GetEntity(
                'Favourite',
                array(
                     'target_id'      => $oComment->getId(),
                     'target_type'    => 'comment',
                     'user_id'        => $this->oUserCurrent->getId(),
                     'target_publish' => $oComment->getPublish()
                )
            );
            $oComment->setCountFavourite($oComment->getCountFavourite() + 1);
            if (E::ModuleComment()->AddFavouriteComment($oFavouriteCommentNew) && E::ModuleComment()->UpdateComment($oComment)) {
                E::ModuleMessage()->AddNoticeSingle(
                    E::ModuleLang()->Get('comment_favourite_add_ok'), E::ModuleLang()->Get('attention')
                );
                E::ModuleViewer()->AssignAjax('bState', true);
                E::ModuleViewer()->AssignAjax('iCount', $oComment->getCountFavourite());
            } else {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                return;
            }
        }
        if (!$oFavouriteComment && !$iType) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('comment_favourite_add_no'), E::ModuleLang()->Get('error'));
            return;
        }
        if ($oFavouriteComment && $iType) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('comment_favourite_add_already'), E::ModuleLang()->Get('error'));
            return;
        }
        if ($oFavouriteComment && !$iType) {
            $oComment->setCountFavourite($oComment->getCountFavourite() - 1);
            if (E::ModuleComment()->DeleteFavouriteComment($oFavouriteComment) && E::ModuleComment()->UpdateComment($oComment)) {
                E::ModuleMessage()->AddNoticeSingle(
                    E::ModuleLang()->Get('comment_favourite_del_ok'), E::ModuleLang()->Get('attention')
                );
                E::ModuleViewer()->AssignAjax('bState', false);
                E::ModuleViewer()->AssignAjax('iCount', $oComment->getCountFavourite());
            } else {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                return;
            }
        }
    }

    /**
     * Обработка избранного - личное сообщение
     *
     */
    protected function EventFavouriteTalk() {

        // * Пользователь авторизован?
        if (!$this->oUserCurrent) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        // * Можно только добавить или удалить из избранного
        $iType = F::GetRequestStr('type', null, 'post');
        if (!in_array($iType, array('1', '0'))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
        // *    Сообщение существует?
        if (!($oTalk = E::ModuleTalk()->GetTalkById(F::GetRequestStr('idTalk', null, 'post')))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
        // * Сообщение уже в избранном?
        $oFavouriteTalk = E::ModuleTalk()->GetFavouriteTalk($oTalk->getId(), $this->oUserCurrent->getId());
        if (!$oFavouriteTalk && $iType) {
            $oFavouriteTalkNew = E::GetEntity(
                'Favourite',
                array(
                     'target_id'      => $oTalk->getId(),
                     'target_type'    => 'talk',
                     'user_id'        => $this->oUserCurrent->getId(),
                     'target_publish' => '1'
                )
            );
            if (E::ModuleTalk()->AddFavouriteTalk($oFavouriteTalkNew)) {
                E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('talk_favourite_add_ok'), E::ModuleLang()->Get('attention'));
                E::ModuleViewer()->AssignAjax('bState', true);
            } else {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                return;
            }
        }
        if (!$oFavouriteTalk && !$iType) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('talk_favourite_add_no'), E::ModuleLang()->Get('error'));
            return;
        }
        if ($oFavouriteTalk && $iType) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('talk_favourite_add_already'), E::ModuleLang()->Get('error'));
            return;
        }
        if ($oFavouriteTalk && !$iType) {
            if (E::ModuleTalk()->DeleteFavouriteTalk($oFavouriteTalk)) {
                E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('talk_favourite_del_ok'), E::ModuleLang()->Get('attention'));
                E::ModuleViewer()->AssignAjax('bState', false);
            } else {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                return;
            }
        }

    }

    /**
     * Обработка получения последних комментов
     * Используется в блоке "Прямой эфир"
     *
     */
    protected function EventStreamComment() {

        $aVars = array();
        if ($aComments = E::ModuleComment()->GetCommentsOnline('topic', Config::Get('widgets.stream.params.limit'))) {
            $aVars['aComments'] = $aComments;
        }
        $sTextResult = E::ModuleViewer()->FetchWidget('stream_comment.tpl', $aVars);
        E::ModuleViewer()->AssignAjax('sText', $sTextResult);
    }

    /**
     * Обработка получения последних топиков
     * Используется в блоке "Прямой эфир"
     *
     */
    protected function EventStreamTopic() {

        $aVars = array();
        if ($aTopics = E::ModuleTopic()->GetTopicsLast(Config::Get('widgets.stream.params.limit'))) {
            $aVars['aTopics'] = $aTopics['collection'];
            // LS-compatibility
            $aVars['oTopics'] = $aTopics['collection'];
        }
        $sTextResult = E::ModuleViewer()->FetchWidget('stream_topic.tpl', $aVars);
        E::ModuleViewer()->AssignAjax('sText', $sTextResult);
    }

    /**
     * Обработка получения последних записей стены
     * Используется в блоке "Прямой эфир"
     *
     */
    protected function EventStreamWall() {

        $aVars = array();
        $aResult = E::ModuleWall()->GetWall(array(), array('date_add' => 'DESC'), 1, Config::Get('widgets.stream.params.limit'));
        if ($aResult['count'] != 0) {
            $aVars['aWall'] = $aResult['collection'];
        }

        $sTextResult = E::ModuleViewer()->FetchWidget('stream_wall.tpl', $aVars);
        E::ModuleViewer()->AssignAjax('sText', $sTextResult);
    }

    /**
     * Обработка получения TOP блогов
     * Используется в блоке "TOP блогов"
     *
     */
    protected function EventBlogsTop() {

        // * Получаем список блогов и формируем ответ
        if ($aResult = E::ModuleBlog()->GetBlogsRating(1, Config::Get('widgets.blogs.params.limit'))) {
            $aVars = array('aBlogs' => $aResult['collection']);

            // Рендерим шаблон виджета
            $sTextResult = E::ModuleViewer()->FetchWidget('blogs_top.tpl', $aVars);
            E::ModuleViewer()->AssignAjax('sText', $sTextResult);
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
    }

    /**
     * Обработка получения своих блогов
     * Используется в блоке "TOP блогов"
     *
     */
    protected function EventBlogsSelf() {

        // * Пользователь авторизован?
        if (!$this->oUserCurrent) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        // * Получаем список блогов и формируем ответ
        if ($aBlogs = E::ModuleBlog()->GetBlogsRatingSelf($this->oUserCurrent->getId(), Config::Get('widgets.blogs.params.limit'))) {
            $aVars = array('aBlogs' => $aBlogs);

            $sTextResult = E::ModuleViewer()->FetchWidget('blogs_top.tpl', $aVars);
            E::ModuleViewer()->AssignAjax('sText', $sTextResult);
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('widget_blogs_self_error'), E::ModuleLang()->Get('attention'));
            return;
        }
    }

    /**
     * Обработка получения подключенных блогов
     * Используется в блоке "TOP блогов"
     *
     */
    protected function EventBlogsJoin() {

        // * Пользователь авторизован?
        if (!$this->oUserCurrent) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        // * Получаем список блогов и формируем ответ
        if ($aBlogs = E::ModuleBlog()->GetBlogsRatingJoin($this->oUserCurrent->getId(), Config::Get('widgets.blogs.params.limit'))) {
            $aVars = array('aBlogs' => $aBlogs);

            // Рендерим шаблон виджета
            $sTextResult = E::ModuleViewer()->FetchWidget('blogs_top.tpl', $aVars);
            E::ModuleViewer()->AssignAjax('sText', $sTextResult);
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('widget_blogs_join_error'), E::ModuleLang()->Get('attention'));
            return;
        }
    }

    /**
     * Предпросмотр топика
     *
     */
    protected function EventPreviewTopic() {
        /**
         * Т.к. используется обработка отправки формы, то устанавливаем тип ответа 'jsonIframe' (тот же JSON только обернутый в textarea)
         * Это позволяет избежать ошибок в некоторых браузерах, например, Opera
         */
        E::ModuleViewer()->SetResponseAjax(F::AjaxRequest(true)?'json':'jsonIframe', false);

        // * Пользователь авторизован?
        if (!$this->oUserCurrent) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        // * Допустимый тип топика?
        if (!E::ModuleTopic()->IsAllowTopicType($sType = F::GetRequestStr('topic_type'))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_create_type_error'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Создаем объект топика для валидации данных
        $oTopic = E::GetEntity('ModuleTopic_EntityTopic');
        $oTopic->_setValidateScenario($sType); // зависит от типа топика

        $oTopic->setTitle(strip_tags(F::GetRequestStr('topic_title')));
        $oTopic->setTextSource(F::GetRequestStr('topic_text'));

        $aTags = F::GetRequestStr('topic_field_tags');
        if (!$aTags) {
            // LS compatibility
            $aTags = F::GetRequestStr('topic_tags');
        }
        $oTopic->setTags($aTags);

        $oTopic->setDateAdd(F::Now());
        $oTopic->setUserId($this->oUserCurrent->getId());
        $oTopic->setType($sType);
        $oTopic->setBlogId(F::GetRequestStr('blog_id'));
        $oTopic->setPublish(1);

        // * Валидируем необходимые поля топика
        $oTopic->_Validate(array('topic_title', 'topic_text', 'topic_tags', 'topic_type'), false);
        if ($oTopic->_hasValidateErrors()) {
            E::ModuleMessage()->AddErrorSingle($oTopic->_getValidateError());
            return false;
        }

        // * Формируем текст топика
        list($sTextShort, $sTextNew, $sTextCut) = E::ModuleText()->Cut($oTopic->getTextSource());
        $oTopic->setCutText($sTextCut);
        $oTopic->setText(E::ModuleText()->Parser($sTextNew));
        $oTopic->setTextShort(E::ModuleText()->Parser($sTextShort));

        // * Готовим дополнительные поля, кроме файлов
        if ($oType = $oTopic->getContentType()) {
            //получаем поля для данного типа
            if ($aFields = $oType->getFields()) {
                $aValues = array();

                // вставляем поля, если они прописаны для топика
                foreach ($aFields as $oField) {
                    if (isset($_REQUEST['fields'][$oField->getFieldId()])) {

                        $sText = null;

                        //текстовые поля
                        if (in_array($oField->getFieldType(), array('input', 'textarea', 'select'))) {
                            $sText = E::ModuleText()->Parser($_REQUEST['fields'][$oField->getFieldId()]);
                        }
                        //поле ссылки
                        if ($oField->getFieldType() == 'link') {
                            $sText = $_REQUEST['fields'][$oField->getFieldId()];
                        }

                        //поле даты
                        if ($oField->getFieldType() == 'date') {
                            if (isset($_REQUEST['fields'][$oField->getFieldId()])) {

                                if (F::CheckVal($_REQUEST['fields'][$oField->getFieldId()], 'text', 6, 10)
                                    && substr_count($_REQUEST['fields'][$oField->getFieldId()], '.') == 2
                                ) {
                                    list($d, $m, $y) = explode('.', $_REQUEST['fields'][$oField->getFieldId()]);
                                    if (@checkdate($m, $d, $y)) {
                                        $sText = $_REQUEST['fields'][$oField->getFieldId()];
                                    }
                                }

                            }

                        }

                        if ($sText) {
                            $oValue = E::GetEntity('Topic_ContentValues');
                            $oValue->setFieldId($oField->getFieldId());
                            $oValue->setFieldType($oField->getFieldType());
                            $oValue->setValue($sText);
                            $oValue->setValueSource($_REQUEST['fields'][$oField->getFieldId()]);

                            $aValues[$oField->getFieldId()] = $oValue;
                        }
                    }
                }
                $oTopic->setTopicValues($aValues);
            }
        }

        // * Рендерим шаблон для предпросмотра топика
        $oViewer = E::ModuleViewer()->GetLocalViewer();
        $oViewer->Assign('oTopic', $oTopic);
        $oViewer->Assign('bPreview', true);

        // Alto-style template
        $sTemplate = 'topics/topic.show.tpl';
        if (!E::ModuleViewer()->TemplateExists($sTemplate)) {
            // LS-style template
            $sTemplate = "topic_preview_{$oTopic->getType()}.tpl";
            if (!E::ModuleViewer()->TemplateExists($sTemplate)) {
                $sTemplate = 'topic_preview_topic.tpl';
            }
        }
        $sTextResult = $oViewer->Fetch($sTemplate);

        // * Передаем результат в ajax ответ
        E::ModuleViewer()->AssignAjax('sText', $sTextResult);
        return true;
    }

    /**
     * Предпросмотр текста
     *
     */
    protected function EventPreviewText() {

        $sText = F::GetRequestStr('text', null, 'post');
        $bSave = F::GetRequest('save', null, 'post');

        // * Экранировать или нет HTML теги
        if ($bSave) {
            $sTextResult = htmlspecialchars($sText);
        } else {
            $sTextResult = E::ModuleText()->Parser($sText);
        }
        // * Передаем результат в ajax ответ
        E::ModuleViewer()->AssignAjax('sText', $sTextResult);
    }

    /**
     * Загрузка изображения
     *
     */
    protected function EventUploadImage() {
        /*
         * Т.к. используется обработка отправки формы, то устанавливаем тип ответа 'jsonIframe'
         * (тот же JSON только обернутый в textarea)
         * Это позволяет избежать ошибок в некоторых браузерах, например, Opera
         */
        E::ModuleViewer()->SetResponseAjax(F::AjaxRequest(true)?'json':'jsonIframe', false);

        // * Пользователь авторизован?
        if (!$this->oUserCurrent) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        $sFile = null;
        // * Был выбран файл с компьютера и он успешно загрузился?
        if ($aUploadedFile = $this->GetUploadedFile('img_file')) {
            $aOptions = array();
            // Check options of uploaded image
            if ($nWidth = $this->GetPost('img_width')) {
                if ($this->GetPost('img_width_unit') == 'percent') {
                    // Max width according width of text area
                    if ($this->GetPost('img_width_ref') == 'text' && ($nWidthText = intval($this->GetPost('img_width_text')))) {
                        $nWidth = round($nWidthText * $nWidth / 100);
                        $aOptions['size']['width'] = $nWidth;
                    }
                }
            }
            $sFile = E::ModuleTopic()->UploadTopicImageFile($aUploadedFile, $this->oUserCurrent, $aOptions);
            if (!$sFile) {
                $sMessage = E::ModuleLang()->Get('uploadimg_file_error');
                if (E::ModuleUploader()->GetError()) {
                    $sMessage .= ' (' . E::ModuleUploader()->GetErrorMsg() . ')';
                }
                E::ModuleMessage()->AddErrorSingle($sMessage, E::ModuleLang()->Get('error'));
                return;
            }
        } elseif (($sUrl = $this->GetPost('img_url')) && ($sUrl != 'http://')) {
            // * Загрузка файла по URL
            if (preg_match('~(https?:\/\/)(\w([\w]+)?\.[\w\.\-\/]+.*)$~i', $sUrl, $aM)) {
                // Иногда перед нормальным адресом встречается лишний 'http://' и прочий "мусор"
                $sUrl = $aM[1] . $aM[2];
                $sFile = E::ModuleTopic()->UploadTopicImageUrl($sUrl, $this->oUserCurrent);
            }
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('uploadimg_file_error'));
            return;
        }
        // * Если файл успешно загружен, формируем HTML вставки и возвращаем в ajax ответе
        if ($sFile) {
            $sText = E::ModuleImg()->BuildHTML($sFile, $_REQUEST);
            E::ModuleViewer()->AssignAjax('sText', $sText);
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleUploader()->GetErrorMsg(), E::ModuleLang()->Get('error'));
        }
    }

    /**
     * Автоподставновка тегов
     *
     */
    protected function EventAutocompleterTag() {

        // * Первые буквы тега переданы?
        if (!($sValue = F::GetRequest('value', null, 'post')) || !is_string($sValue)) {
            return;
        }
        $aItems = array();

        // * Формируем список тегов
        $aTags = E::ModuleTopic()->GetTopicTagsByLike($sValue, 10);
        foreach ($aTags as $oTag) {
            $aItems[] = $oTag->getText();
        }
        // * Передаем результат в ajax ответ
        E::ModuleViewer()->AssignAjax('aItems', $aItems);
    }

    /**
     * Автоподставновка пользователей
     *
     */
    protected function EventAutocompleterUser() {

        // * Первые буквы логина переданы?
        if (!($sValue = F::GetRequest('value', null, 'post')) || !is_string($sValue)) {
            return;
        }
        $aItems = array();

        // * Формируем список пользователей
        /** @var ModuleUser_EntityUser[] $aUsers */
        $aUsers = E::ModuleUser()->GetUsersByLoginLike($sValue, 10);
        foreach ($aUsers as $oUser) {
            $aItems[] =
                (Config::Get('autocomplete.user.show_avatar') ? '<img src="' . $oUser->getAvatarUrl(Config::Get('autocomplete.user.avatar_size')) . '">' : '')
                . $oUser->getLogin();
        }
        // * Передаем результат в ajax ответ
        E::ModuleViewer()->AssignAjax('aItems', $aItems);
    }

    /**
     * Удаление/восстановление комментария
     *
     */
    protected function EventCommentDelete() {

        // * Комментарий существует?
        $idComment = F::GetRequestStr('idComment', null, 'post');
        if (!($oComment = E::ModuleComment()->GetCommentById($idComment))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
        // * Есть права на удаление комментария?
        if (!$oComment->isDeletable()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('not_access'), E::ModuleLang()->Get('error'));
            return;
        }
        // * Устанавливаем пометку о том, что комментарий удален
        $oComment->setDelete(($oComment->getDelete() + 1) % 2);
        E::ModuleHook()->Run('comment_delete_before', array('oComment' => $oComment));
        if (!E::ModuleComment()->UpdateCommentStatus($oComment)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
        E::ModuleHook()->Run('comment_delete_after', array('oComment' => $oComment));

        // * Формируем текст ответа
        if ($bState = (bool)$oComment->getDelete()) {
            $sMsg = E::ModuleLang()->Get('comment_delete_ok');
            $sTextToggle = E::ModuleLang()->Get('comment_repair');
        } else {
            $sMsg = E::ModuleLang()->Get('comment_repair_ok');
            $sTextToggle = E::ModuleLang()->Get('comment_delete');
        }
        // * Обновление события в ленте активности
        E::ModuleStream()->Write($oComment->getUserId(), 'add_comment', $oComment->getId(), !$oComment->getDelete());

        // * Показываем сообщение и передаем переменные в ajax ответ
        E::ModuleMessage()->AddNoticeSingle($sMsg, E::ModuleLang()->Get('attention'));
        E::ModuleViewer()->AssignAjax('bState', $bState);
        E::ModuleViewer()->AssignAjax('sTextToggle', $sTextToggle);
    }

    /**
     *
     */
    protected function EventFetch() {

        $sHtml = '';
        $bState = false;
        if ($sTpl = $this->GetParam(0)) {
            $sTpl = 'ajax.' . $sTpl . '.tpl';
            if (E::ModuleViewer()->TemplateExists($sTpl)) {
                $sHtml = E::ModuleViewer()->Fetch($sTpl);
                $bState = true;
            }
        }
        E::ModuleViewer()->AssignAjax('sHtml', $sHtml);
        E::ModuleViewer()->AssignAjax('bState', $bState);
    }

}

// EOF
