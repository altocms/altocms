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
 * Экшен обработки личной почты (сообщения /talk/)
 *
 * @package actions
 * @since   1.0
 */
class ActionTalk extends Action {
    /**
     * Текущий юзер
     *
     * @var ModuleUser_EntityUser|null
     */
    protected $oUserCurrent = null;
    /**
     * Подменю
     *
     * @var string
     */
    protected $sMenuSubItemSelect = '';
    /**
     * Массив ID юзеров адресатов
     *
     * @var array
     */
    protected $aUsersId = array();

    /**
     * Инициализация
     *
     */
    public function Init() {

        // * Проверяем авторизован ли юзер
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('not_access'));
            return R::Action('error');
        }

        // * Получаем текущего юзера
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();
        $this->SetDefaultEvent('inbox');
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('talk_menu_inbox'));

        // * Загружаем в шаблон JS текстовки
        E::ModuleLang()->AddLangJs(
            array(
                 'delete',
                 'talk_inbox_delete_confirm'
            )
        );
    }

    /**
     * Регистрация евентов
     */
    protected function RegisterEvent() {

        $this->AddEvent('inbox', 'EventInbox');
        $this->AddEvent('add', 'EventAdd');
        $this->AddEvent('read', 'EventRead');
        $this->AddEvent('delete', 'EventDelete');
        $this->AddEvent('ajaxaddcomment', 'AjaxAddComment');
        $this->AddEvent('ajaxresponsecomment', 'AjaxResponseComment');
        $this->AddEvent('favourites', 'EventFavourites');
        $this->AddEvent('blacklist', 'EventBlacklist');
        $this->AddEvent('ajaxaddtoblacklist', 'AjaxAddToBlacklist');
        $this->AddEvent('ajaxdeletefromblacklist', 'AjaxDeleteFromBlacklist');
        $this->AddEvent('ajaxdeletetalkuser', 'AjaxDeleteTalkUser');
        $this->AddEvent('ajaxaddtalkuser', 'AjaxAddTalkUser');
        $this->AddEvent('ajaxnewmessages', 'AjaxNewMessages');
    }


    /**********************************************************************************
     ************************ РЕАЛИЗАЦИЯ ЭКШЕНА ***************************************
     **********************************************************************************
     */

    /**
     * Удаление письма
     */
    protected function EventDelete() {

        E::ModuleSecurity()->ValidateSendForm();

        // * Получаем номер сообщения из УРЛ и проверяем существует ли оно
        $sTalkId = $this->GetParam(0);
        if (!($oTalk = E::ModuleTalk()->GetTalkById($sTalkId))) {
            return parent::EventNotFound();
        }

        // * Пользователь входит в переписку?
        if (!($oTalkUser = E::ModuleTalk()->GetTalkUser($oTalk->getId(), $this->oUserCurrent->getId()))) {
            return parent::EventNotFound();
        }

        // * Обработка удаления сообщения
        E::ModuleTalk()->DeleteTalkUserByArray($sTalkId, $this->oUserCurrent->getId());
        R::Location(R::GetPath('talk'));
    }

    /**
     * Отображение списка сообщений
     */
    protected function EventInbox() {

        // * Обработка удаления сообщений
        if (F::GetRequest('submit_talk_del')) {
            E::ModuleSecurity()->ValidateSendForm();

            $aTalksIdDel = F::GetRequest('talk_select');
            if (is_array($aTalksIdDel)) {
                E::ModuleTalk()->DeleteTalkUserByArray(array_keys($aTalksIdDel), $this->oUserCurrent->getId());
            }
        }

        // * Обработка отметки о прочтении
        if (F::GetRequest('submit_talk_read')) {
            E::ModuleSecurity()->ValidateSendForm();

            $aTalksIdDel = F::GetRequest('talk_select');
            if (is_array($aTalksIdDel)) {
                E::ModuleTalk()->MarkReadTalkUserByArray(array_keys($aTalksIdDel), $this->oUserCurrent->getId());
            }
        }

        // * Обработка отметки непрочтенных сообщений
        if (F::GetRequest('submit_talk_unread')) {
            E::ModuleSecurity()->ValidateSendForm();

            $aTalksIdDel = F::GetRequest('talk_select');
            if (is_array($aTalksIdDel)) {
                E::ModuleTalk()->MarkUnreadTalkUserByArray(array_keys($aTalksIdDel), $this->oUserCurrent->getId());
            }
        }
        $this->sMenuSubItemSelect = 'inbox';

        // * Количество сообщений на страницу
        $iPerPage = Config::Get('module.talk.per_page');

        // * Формируем фильтр для поиска сообщений
        $aFilter = $this->BuildFilter();

        // * Если только новые, то добавляем условие в фильтр
        if ($this->GetParam(0) == 'new') {
            $this->sMenuSubItemSelect = 'new';
            $aFilter['only_new'] = true;
            $iPerPage = 50; // новых отображаем только последние 50 писем, без постраничности
        }

        // * Передан ли номер страницы
        $iPage = preg_match('/^page([1-9]\d{0,5})$/i', $this->getParam(0), $aMatch) ? $aMatch[1] : 1;

        // * Получаем список писем
        $aResult = E::ModuleTalk()->GetTalksByFilter($aFilter, $iPage, $iPerPage);

        $aTalks = $aResult['collection'];

        // * Формируем постраничность
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, $iPerPage, Config::Get('pagination.pages.count'),
            R::GetPath('talk') . $this->sCurrentEvent,
            array_intersect_key(
                $_REQUEST,
                array_fill_keys(
                    array('start', 'end', 'keyword', 'sender', 'keyword_text', 'favourite'),
                    ''
                )
            )
        );

        // * Показываем сообщение, если происходит поиск по фильтру
        if (F::GetRequest('submit_talk_filter')) {
            E::ModuleMessage()->AddNotice(
                ($aResult['count'])
                    ? E::ModuleLang()->Get('talk_filter_result_count', array('count' => $aResult['count']))
                    : E::ModuleLang()->Get('talk_filter_result_empty')
            );
        }

        // * Загружаем переменные в шаблон
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        E::ModuleViewer()->Assign('aTalks', $aTalks);
    }

    /**
     * Формирует из REQUEST массива фильтр для отбора писем
     *
     * @return array
     */
    protected function BuildFilter() {

        // * Текущий пользователь
        $aFilter = array(
            'user_id' => $this->oUserCurrent->getId(),
        );

        // * Дата старта поиска
        if ($start = F::GetRequestStr('start')) {
            if (F::CheckVal($start, 'text', 6, 10) && substr_count($start, '.') == 2) {
                list($d, $m, $y) = explode('.', $start);
                if (@checkdate($m, $d, $y)) {
                    $aFilter['date_min'] = "{$y}-{$m}-{$d}";
                } else {
                    E::ModuleMessage()->AddError(
                        E::ModuleLang()->Get('talk_filter_error_date_format'),
                        E::ModuleLang()->Get('talk_filter_error')
                    );
                    unset($_REQUEST['start']);
                }
            } else {
                E::ModuleMessage()->AddError(
                    E::ModuleLang()->Get('talk_filter_error_date_format'),
                    E::ModuleLang()->Get('talk_filter_error')
                );
                unset($_REQUEST['start']);
            }
        }

        // * Дата окончания поиска
        if ($end = F::GetRequestStr('end')) {
            if (F::CheckVal($end, 'text', 6, 10) && substr_count($end, '.') == 2) {
                list($d, $m, $y) = explode('.', $end);
                if (@checkdate($m, $d, $y)) {
                    $aFilter['date_max'] = "{$y}-{$m}-{$d} 23:59:59";
                } else {
                    E::ModuleMessage()->AddError(
                        E::ModuleLang()->Get('talk_filter_error_date_format'),
                        E::ModuleLang()->Get('talk_filter_error')
                    );
                    unset($_REQUEST['end']);
                }
            } else {
                E::ModuleMessage()->AddError(
                    E::ModuleLang()->Get('talk_filter_error_date_format'),
                    E::ModuleLang()->Get('talk_filter_error')
                );
                unset($_REQUEST['end']);
            }
        }

        // * Ключевые слова в теме сообщения
        if (($sKeyRequest = F::GetRequest('keyword')) && is_string($sKeyRequest)) {
            $sKeyRequest = urldecode($sKeyRequest);
            preg_match_all('~(\S+)~u', $sKeyRequest, $aWords);

            if (is_array($aWords[1]) && isset($aWords[1]) && count($aWords[1])) {
                $aFilter['keyword'] = '%' . implode('%', $aWords[1]) . '%';
            } else {
                unset($_REQUEST['keyword']);
            }
        }

        // * Ключевые слова в тексте сообщения
        if (($sKeyRequest = F::GetRequest('keyword_text')) && is_string($sKeyRequest)) {
            $sKeyRequest = urldecode($sKeyRequest);
            preg_match_all('~(\S+)~u', $sKeyRequest, $aWords);

            if (is_array($aWords[1]) && isset($aWords[1]) && count($aWords[1])) {
                $aFilter['text_like'] = '%' . implode('%', $aWords[1]) . '%';
            } else {
                unset($_REQUEST['keyword_text']);
            }
        }

        // * Отправитель
        if (($sSender = F::GetRequest('sender')) && is_string($sSender)) {
            $aFilter['user_login'] = F::Array_Str2Array(urldecode($sSender), ',', true);
        }
        // * Адресат
        if (($sAddressee = F::GetRequest('addressee')) && is_string($sAddressee)) {
            $aFilter['user_login'] = F::Array_Str2Array(urldecode($sAddressee), ',', true);
        }

        // * Искать только в избранных письмах
        if (F::GetRequest('favourite')) {
            $aTalkIdResult = E::ModuleFavourite()->GetFavouritesByUserId(
                $this->oUserCurrent->getId(), 'talk', 1, 500
            ); // ограничиваем
            $aFilter['id'] = $aTalkIdResult['collection'];
            $_REQUEST['favourite'] = 1;
        } else {
            unset($_REQUEST['favourite']);
        }
        return $aFilter;
    }

    /**
     * Отображение списка блэк-листа
     */
    protected function EventBlacklist() {

        $this->sMenuSubItemSelect = 'blacklist';
        $aUsersBlacklist = E::ModuleTalk()->GetBlacklistByUserId($this->oUserCurrent->getId());
        E::ModuleViewer()->Assign('aUsersBlacklist', $aUsersBlacklist);
    }

    /**
     * Отображение списка избранных писем
     */
    protected function EventFavourites() {

        $this->sMenuSubItemSelect = 'favourites';

        // * Передан ли номер страницы
        $iPage = preg_match("/^page([1-9]\d{0,5})$/i", $this->getParam(0), $aMatch) ? $aMatch[1] : 1;

        // * Получаем список писем
        $aResult = E::ModuleTalk()->GetTalksFavouriteByUserId(
            $this->oUserCurrent->getId(),
            $iPage, Config::Get('module.talk.per_page')
        );
        $aTalks = $aResult['collection'];

        // * Формируем постраничность
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.talk.per_page'), Config::Get('pagination.pages.count'),
            R::GetPath('talk') . $this->sCurrentEvent
        );

        // * Загружаем переменные в шаблон
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        E::ModuleViewer()->Assign('aTalks', $aTalks);
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('talk_favourite_inbox'));
    }

    /**
     * Страница создания письма
     */
    protected function EventAdd() {

        $this->sMenuSubItemSelect = 'add';
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('talk_menu_inbox_create'));

        // * Получаем список друзей
        $aUsersFriend = E::ModuleUser()->GetUsersFriend($this->oUserCurrent->getId());
        if ($aUsersFriend['collection']) {
            E::ModuleViewer()->Assign('aUsersFriend', $aUsersFriend['collection']);
        }

        // * Проверяем отправлена ли форма с данными
        if (!F::isPost('submit_talk_add')) {
            return false;
        }

        // * Проверка корректности полей формы
        if (!$this->checkTalkFields()) {
            return false;
        }

        // * Проверяем разрешено ли отправлять инбокс по времени
        if (!E::ModuleACL()->CanSendTalkTime($this->oUserCurrent)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('talk_time_limit'), E::ModuleLang()->Get('error'));
            return false;
        }

        // * Отправляем письмо
        if ($oTalk = E::ModuleTalk()->SendTalk(
            E::ModuleText()->Parser(strip_tags(F::GetRequestStr('talk_title'))), E::ModuleText()->Parser(F::GetRequestStr('talk_text')),
            $this->oUserCurrent, $this->aUsersId
        )
        ) {

            E::ModuleMresource()->CheckTargetTextForImages('talk', $oTalk->getId(), $oTalk->getText());

            R::Location(R::GetPath('talk') . 'read/' . $oTalk->getId() . '/');
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'));
            return R::Action('error');
        }
    }

    /**
     * Чтение письма
     */
    protected function EventRead() {

        $this->sMenuSubItemSelect = 'read';

        // * Получаем номер сообщения из УРЛ и проверяем существует ли оно
        $sTalkId = $this->GetParam(0);
        if (!($oTalk = E::ModuleTalk()->GetTalkById($sTalkId))) {
            return parent::EventNotFound();
        }

        // * Пользователь есть в переписке?
        if (!($oTalkUser = E::ModuleTalk()->GetTalkUser($oTalk->getId(), $this->oUserCurrent->getId()))) {
            return parent::EventNotFound();
        }

        // * Пользователь активен в переписке?
        if ($oTalkUser->getUserActive() != ModuleTalk::TALK_USER_ACTIVE) {
            return parent::EventNotFound();
        }

        // * Обрабатываем добавление коммента
        if (isset($_REQUEST['submit_comment'])) {
            $this->SubmitComment();
        }

        // * Достаём комменты к сообщению
        $aReturn = E::ModuleComment()->GetCommentsByTargetId($oTalk, 'talk');
        $iMaxIdComment = $aReturn['iMaxIdComment'];
        $aComments = $aReturn['comments'];

        // * Помечаем дату последнего просмотра
        $oTalkUser->setDateLast(F::Now());
        $oTalkUser->setCommentIdLast($iMaxIdComment);
        $oTalkUser->setCommentCountNew(0);
        E::ModuleTalk()->UpdateTalkUser($oTalkUser);

        E::ModuleViewer()->AddHtmlTitle($oTalk->getTitle());
        E::ModuleViewer()->Assign('oTalk', $oTalk);
        E::ModuleViewer()->Assign('aComments', $aComments);
        E::ModuleViewer()->Assign('iMaxIdComment', $iMaxIdComment);
        /*
         * Подсчитываем нужно ли отображать комментарии.
         * Комментарии не отображаются, если у вестки только один читатель
         * и ранее созданных комментариев нет.
         */
        if (count($aComments) == 0) {
            $iActiveSpeakers = 0;
            foreach ((array)$oTalk->getTalkUsers() as $oTalkUser) {
                if (($oTalkUser->getUserId() != $this->oUserCurrent->getId())
                    && $oTalkUser->getUserActive() == ModuleTalk::TALK_USER_ACTIVE
                ) {
                    $iActiveSpeakers++;
                    break;
                }
            }
            if ($iActiveSpeakers == 0) {
                E::ModuleViewer()->Assign('bNoComments', true);
            }
        }

        E::ModuleViewer()->Assign('bAllowToComment', true);
        $this->SetTemplateAction('message');
    }

    /**
     * Проверка полей при создании письма
     *
     * @return bool
     */
    protected function checkTalkFields() {
        E::ModuleSecurity()->ValidateSendForm();

        $bOk = true;

        // * Проверяем есть ли заголовок
        if (!F::CheckVal(F::GetRequestStr('talk_title'), 'text', 2, 200)) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('talk_create_title_error'), E::ModuleLang()->Get('error'));
            $bOk = false;
        }

        // * Проверяем есть ли содержание топика
        $iMin = intval(Config::Get('module.talk.min_length'));
        $iMax = intval(Config::Get('module.talk.max_length'));
        if (!F::CheckVal(F::GetRequestStr('talk_text'), 'text', $iMin, $iMax)) {
            if ($iMax) {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('talk_create_text_error_len', array('min'=>$iMin, 'max'=>$iMax)), E::ModuleLang()->Get('error'));
            } else {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('talk_create_text_error_min', array('min'=>$iMin)), E::ModuleLang()->Get('error'));
            }
            $bOk = false;
        }

        // * Проверяем адресатов
        $sUsers = F::GetRequest('talk_users');
        $aUsers = explode(',', (string)$sUsers);
        $aUsersNew = array();
        $aUserInBlacklist = E::ModuleTalk()->GetBlacklistByTargetId($this->oUserCurrent->getId());

        $this->aUsersId = array();
        foreach ($aUsers as $sUser) {
            $sUser = trim($sUser);
            if ($sUser == '' || strtolower($sUser) == strtolower($this->oUserCurrent->getLogin())) {
                continue;
            }
            if (($oUser = E::ModuleUser()->GetUserByLogin($sUser)) && $oUser->getActivate() == 1) {
                // Проверяем, попал ли отправиль в блек лист
                if (!in_array($oUser->getId(), $aUserInBlacklist)) {
                    $this->aUsersId[] = $oUser->getId();
                } else {
                    E::ModuleMessage()->AddError(
                        str_replace(
                            'login',
                            $oUser->getLogin(),
                            E::ModuleLang()->Get(
                                'talk_user_in_blacklist', array('login' => htmlspecialchars($oUser->getLogin()))
                            )
                        ),
                        E::ModuleLang()->Get('error')
                    );
                    $bOk = false;
                    continue;
                }
            } else {
                E::ModuleMessage()->AddError(
                    E::ModuleLang()->Get('talk_create_users_error_not_found') . ' «' . htmlspecialchars($sUser) . '»',
                    E::ModuleLang()->Get('error')
                );
                $bOk = false;
            }
            $aUsersNew[] = $sUser;
        }
        if (!count($aUsersNew)) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('talk_create_users_error'), E::ModuleLang()->Get('error'));
            $_REQUEST['talk_users'] = '';
            $bOk = false;
        } else {
            if (count($aUsersNew) > Config::Get('module.talk.max_users') && !$this->oUserCurrent->isAdministrator()) {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('talk_create_users_error_many'), E::ModuleLang()->Get('error'));
                $bOk = false;
            }
            $_REQUEST['talk_users'] = join(',', $aUsersNew);
        }

        // * Выполнение хуков
        E::ModuleHook()->Run('check_talk_fields', array('bOk' => &$bOk));

        return $bOk;
    }

    /**
     * Получение новых комментариев
     *
     */
    protected function AjaxResponseComment() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');
        $idCommentLast = F::GetRequestStr('idCommentLast');

        // * Проверям авторизован ли пользователь
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Проверяем разговор
        if (!($oTalk = E::ModuleTalk()->GetTalkById(F::GetRequestStr('idTarget')))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
        if (!($oTalkUser = E::ModuleTalk()->GetTalkUser($oTalk->getId(), $this->oUserCurrent->getId()))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Получаем комментарии
        $aReturn = E::ModuleComment()->GetCommentsNewByTargetId($oTalk->getId(), 'talk', $idCommentLast);
        $iMaxIdComment = $aReturn['iMaxIdComment'];

        // * Отмечаем дату прочтения письма
        $oTalkUser->setDateLast(F::Now());
        if ($iMaxIdComment != 0) {
            $oTalkUser->setCommentIdLast($iMaxIdComment);
        }
        $oTalkUser->setCommentCountNew(0);
        E::ModuleTalk()->UpdateTalkUser($oTalkUser);

        $aComments = array();
        $aCmts = $aReturn['comments'];
        if ($aCmts && is_array($aCmts)) {
            foreach ($aCmts as $aCmt) {
                $aComments[] = array(
                    'html'     => $aCmt['html'],
                    'idParent' => $aCmt['obj']->getPid(),
                    'id'       => $aCmt['obj']->getId(),
                );
            }
        }
        E::ModuleViewer()->AssignAjax('aComments', $aComments);
        E::ModuleViewer()->AssignAjax('iMaxIdComment', $iMaxIdComment);
    }

    /**
     * Обработка добавление комментария к письму через ajax
     *
     */
    protected function AjaxAddComment() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');
        $this->SubmitComment();
    }

    /**
     * Обработка добавление комментария к письму
     *
     */
    protected function SubmitComment() {

        // * Проверям авторизован ли пользователь
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return false;
        }

        // * Проверяем разговор
        if (!($oTalk = E::ModuleTalk()->GetTalkById(F::GetRequestStr('cmt_target_id')))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return false;
        }
        if (!($oTalkUser = E::ModuleTalk()->GetTalkUser($oTalk->getId(), $this->oUserCurrent->getId()))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return false;
        }

        // * Проверяем разрешено ли отправлять инбокс по времени
        if (!E::ModuleACL()->CanPostTalkCommentTime($this->oUserCurrent)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('talk_time_limit'), E::ModuleLang()->Get('error'));
            return false;
        }

        // * Проверяем текст комментария
        $sText = E::ModuleText()->Parser(F::GetRequestStr('comment_text'));
        $iMin = intval(Config::Get('module.talk.min_length'));
        $iMax = intval(Config::Get('module.talk.max_length'));
        if (!F::CheckVal($sText, 'text', $iMin, $iMax)) {
            if ($iMax) {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('talk_create_text_error_len', array('min'=>$iMin, 'max'=>$iMax)), E::ModuleLang()->Get('error'));
            } else {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('talk_create_text_error_min', array('min'=>$iMin)), E::ModuleLang()->Get('error'));
            }
            return false;
        }

        // * Проверям на какой коммент отвечаем
        $sParentId = (int)F::GetRequest('reply');
        if (!F::CheckVal($sParentId, 'id')) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return false;
        }
        $oCommentParent = null;
        if ($sParentId != 0) {

            // * Проверяем существует ли комментарий на который отвечаем
            if (!($oCommentParent = E::ModuleComment()->GetCommentById($sParentId))) {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                return false;
            }

            // * Проверяем из одного топика ли новый коммент и тот на который отвечаем
            if ($oCommentParent->getTargetId() != $oTalk->getId()) {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                return false;
            }
        } else {

            // * Корневой комментарий
            $sParentId = null;
        }

        // * Проверка на дублирующий коммент
        if (E::ModuleComment()->GetCommentUnique($oTalk->getId(), 'talk', $this->oUserCurrent->getId(), $sParentId, md5($sText))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_comment_spam'), E::ModuleLang()->Get('error'));
            return false;
        }

        // * Создаём комментарий
        /** @var ModuleComment_EntityComment $oCommentNew */
        $oCommentNew = E::GetEntity('Comment');
        $oCommentNew->setTargetId($oTalk->getId());
        $oCommentNew->setTargetType('talk');
        $oCommentNew->setUserId($this->oUserCurrent->getId());
        $oCommentNew->setText($sText);
        $oCommentNew->setDate(F::Now());
        $oCommentNew->setUserIp(F::GetUserIp());
        $oCommentNew->setPid($sParentId);
        $oCommentNew->setTextHash(md5($sText));
        $oCommentNew->setPublish(1);

        // * Добавляем коммент
        E::ModuleHook()->Run(
            'talk_comment_add_before',
            array('oCommentNew' => $oCommentNew, 'oCommentParent' => $oCommentParent, 'oTalk' => $oTalk)
        );
        if (E::ModuleComment()->AddComment($oCommentNew)) {
            E::ModuleHook()->Run(
                'talk_comment_add_after',
                array('oCommentNew' => $oCommentNew, 'oCommentParent' => $oCommentParent, 'oTalk' => $oTalk)
            );

            E::ModuleViewer()->AssignAjax('sCommentId', $oCommentNew->getId());
            $oTalk->setDateLast(F::Now());
            $oTalk->setUserIdLast($oCommentNew->getUserId());
            $oTalk->setCommentIdLast($oCommentNew->getId());
            $oTalk->setCountComment($oTalk->getCountComment() + 1);
            E::ModuleTalk()->UpdateTalk($oTalk);

            // * Отсылаем уведомления всем адресатам
            $aUsersTalk = E::ModuleTalk()->GetUsersTalk($oTalk->getId(), ModuleTalk::TALK_USER_ACTIVE);

            foreach ($aUsersTalk as $oUserTalk) {
                if ($oUserTalk->getId() != $oCommentNew->getUserId()) {
                    E::ModuleNotify()->SendTalkCommentNew($oUserTalk, $this->oUserCurrent, $oTalk, $oCommentNew);
                }
            }

            // * Увеличиваем число новых комментов
            E::ModuleTalk()->IncreaseCountCommentNew($oTalk->getId(), $oCommentNew->getUserId());
            return true;
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
        }
        return false;
    }

    /**
     * Добавление нового пользователя(-лей) в блек лист (ajax)
     *
     */
    public function AjaxAddToBlacklist() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');
        $sUsers = F::GetRequestStr('users', null, 'post');

        // * Если пользователь не авторизирован, возвращаем ошибку
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        $aUsers = explode(',', $sUsers);

        // * Получаем блекслист пользователя
        $aUserBlacklist = E::ModuleTalk()->GetBlacklistByUserId($this->oUserCurrent->getId());

        $aResult = array();

        // * Обрабатываем добавление по каждому из переданных логинов
        foreach ($aUsers as $sUser) {
            $sUser = trim($sUser);
            if ($sUser == '') {
                continue;
            }

            // * Если пользователь пытается добавить в блеклист самого себя, возвращаем ошибку
            if (strtolower($sUser) == strtolower($this->oUserCurrent->getLogin())) {
                $aResult[] = array(
                    'bStateError' => true,
                    'sMsgTitle'   => E::ModuleLang()->Get('error'),
                    'sMsg'        => E::ModuleLang()->Get('talk_blacklist_add_self')
                );
                continue;
            }

            // * Если пользователь не найден или неактивен, возвращаем ошибку
            if (($oUser = E::ModuleUser()->GetUserByLogin($sUser)) && $oUser->getActivate() == 1) {
                if (!isset($aUserBlacklist[$oUser->getId()])) {
                    if (E::ModuleTalk()->AddUserToBlackList($oUser->getId(), $this->oUserCurrent->getId())) {
                        $aResult[] = array(
                            'bStateError'   => false,
                            'sMsgTitle'     => E::ModuleLang()->Get('attention'),
                            'sMsg'          => E::ModuleLang()->Get(
                                'talk_blacklist_add_ok', array('login' => htmlspecialchars($sUser))
                            ),
                            'sUserId'       => $oUser->getId(),
                            'sUserLogin'    => htmlspecialchars($oUser->getDisplayName()),
                            'sUserWebPath'  => $oUser->getProfileUrl(),
                            'sUserAvatar48' => $oUser->getAvatarUrl(48),
                            'sUserName'     => $oUser->getDisplayName(),
                            'sUserUrl'      => $oUser->getProfileUrl(),
                            'sUserAvatar  ' => $oUser->getAvatarUrl(48),
                        );
                    } else {
                        $aResult[] = array(
                            'bStateError' => true,
                            'sMsgTitle'   => E::ModuleLang()->Get('error'),
                            'sMsg'        => E::ModuleLang()->Get('system_error'),
                            'sUserLogin'  => htmlspecialchars($sUser)
                        );
                    }
                } else {

                    // * Попытка добавить уже существующего в блеклисте пользователя, возвращаем ошибку
                    $aResult[] = array(
                        'bStateError' => true,
                        'sMsgTitle'   => E::ModuleLang()->Get('error'),
                        'sMsg'        => E::ModuleLang()->Get(
                            'talk_blacklist_user_already_have', array('login' => htmlspecialchars($sUser))
                        ),
                        'sUserLogin'  => htmlspecialchars($sUser)
                    );
                    continue;
                }
            } else {
                $aResult[] = array(
                    'bStateError' => true,
                    'sMsgTitle'   => E::ModuleLang()->Get('error'),
                    'sMsg'        => E::ModuleLang()->Get('user_not_found', array('login' => htmlspecialchars($sUser))),
                    'sUserLogin'  => htmlspecialchars($sUser)
                );
            }
        }

        // * Передаем во вьевер массив с результатами обработки по каждому пользователю
        E::ModuleViewer()->AssignAjax('aUsers', $aResult);
    }

    /**
     * Удаление пользователя из блек листа (ajax)
     *
     */
    public function AjaxDeleteFromBlacklist() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');
        $idTarget = F::GetRequestStr('idTarget', null, 'post');

        // * Если пользователь не авторизирован, возвращаем ошибку
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('need_authorization'),
                E::ModuleLang()->Get('error')
            );
            return;
        }

        // * Если пользователь не существуем, возращаем ошибку
        if (!$oUserTarget = E::ModuleUser()->GetUserById($idTarget)) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('user_not_found_by_id', array('id' => htmlspecialchars($idTarget))),
                E::ModuleLang()->Get('error')
            );
            return;
        }

        // * Получаем блеклист пользователя
        $aBlacklist = E::ModuleTalk()->GetBlacklistByUserId($this->oUserCurrent->getId());

        // * Если указанный пользователь не найден в блекслисте, возвращаем ошибку
        if (!isset($aBlacklist[$oUserTarget->getId()])) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get(
                    'talk_blacklist_user_not_found',
                    array('login' => $oUserTarget->getLogin())
                ),
                E::ModuleLang()->Get('error')
            );
            return;
        }

        // * Производим удаление пользователя из блекслиста
        if (!E::ModuleTalk()->DeleteUserFromBlacklist($idTarget, $this->oUserCurrent->getId())) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('system_error'),
                E::ModuleLang()->Get('error')
            );
            return;
        }
        E::ModuleMessage()->AddNoticeSingle(
            E::ModuleLang()->Get(
                'talk_blacklist_delete_ok',
                array('login' => $oUserTarget->getLogin())
            ),
            E::ModuleLang()->Get('attention')
        );
    }

    /**
     * Удаление участника разговора (ajax)
     *
     */
    public function AjaxDeleteTalkUser() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');
        $idTarget = F::GetRequestStr('idTarget', null, 'post');
        $idTalk = F::GetRequestStr('idTalk', null, 'post');

        // * Если пользователь не авторизирован, возвращаем ошибку
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('need_authorization'),
                E::ModuleLang()->Get('error')
            );
            return;
        }

        // * Если удаляемый участник не существует в базе данных, возвращаем ошибку
        if (!$oUserTarget = E::ModuleUser()->GetUserById($idTarget)) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('user_not_found_by_id', array('id' => htmlspecialchars($idTarget))),
                E::ModuleLang()->Get('error')
            );
            return;
        }

        // * Если разговор не найден, или пользователь не является его автором (либо админом), возвращаем ошибку
        if ((!$oTalk = E::ModuleTalk()->GetTalkById($idTalk))
            || (($oTalk->getUserId() != $this->oUserCurrent->getId()) && !$this->oUserCurrent->isAdministrator())
        ) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('talk_not_found'),
                E::ModuleLang()->Get('error')
            );
            return;
        }

        // * Получаем список всех участников разговора
        $aTalkUsers = $oTalk->getTalkUsers();

        // * Если пользователь не является участником разговора или удалил себя самостоятельно  возвращаем ошибку
        if (!isset($aTalkUsers[$idTarget])
            || $aTalkUsers[$idTarget]->getUserActive() == ModuleTalk::TALK_USER_DELETE_BY_SELF
        ) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get(
                    'talk_speaker_user_not_found',
                    array('login' => $oUserTarget->getLogin())
                ),
                E::ModuleLang()->Get('error')
            );
            return;
        }

        // * Удаляем пользователя из разговора,  если удаление прошло неудачно - возвращаем системную ошибку
        if (!E::ModuleTalk()->DeleteTalkUserByArray($idTalk, $idTarget, ModuleTalk::TALK_USER_DELETE_BY_AUTHOR)) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('system_error'),
                E::ModuleLang()->Get('error')
            );
            return;
        }
        E::ModuleMessage()->AddNoticeSingle(
            E::ModuleLang()->Get(
                'talk_speaker_delete_ok',
                array('login' => $oUserTarget->getLogin())
            ),
            E::ModuleLang()->Get('attention')
        );
    }

    /**
     * Добавление нового участника разговора (ajax)
     *
     */
    public function AjaxAddTalkUser() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');
        $sUsers = F::GetRequestStr('users', null, 'post');
        $idTalk = F::GetRequestStr('idTalk', null, 'post');

        // * Если пользователь не авторизирован, возвращаем ошибку
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('need_authorization'),
                E::ModuleLang()->Get('error')
            );
            return;
        }

        // * Если разговор не найден, или пользователь не является его автором (или админом), возвращаем ошибку
        if ((!$oTalk = E::ModuleTalk()->GetTalkById($idTalk))
            || (($oTalk->getUserId() != $this->oUserCurrent->getId()) && !$this->oUserCurrent->isAdministrator())
        ) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('talk_not_found'),
                E::ModuleLang()->Get('error')
            );
            return;
        }

        // * Получаем список всех участников разговора
        $aTalkUsers = $oTalk->getTalkUsers();
        $aUsers = explode(',', $sUsers);

        // * Получаем список пользователей, которые не принимают письма
        $aUserInBlacklist = E::ModuleTalk()->GetBlacklistByTargetId($this->oUserCurrent->getId());

        // * Ограничения на максимальное число участников разговора
        if (count($aTalkUsers) >= Config::Get('module.talk.max_users') && !$this->oUserCurrent->isAdministrator()) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('talk_create_users_error_many'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Обрабатываем добавление по каждому переданному логину пользователя
        foreach ($aUsers as $sUser) {
            $sUser = trim($sUser);
            if ($sUser == '') {
                continue;
            }

            // * Попытка добавить себя
            if (strtolower($sUser) == strtolower($this->oUserCurrent->getLogin())) {
                $aResult[] = array(
                    'bStateError' => true,
                    'sMsgTitle'   => E::ModuleLang()->Get('error'),
                    'sMsg'        => E::ModuleLang()->Get('talk_speaker_add_self')
                );
                continue;
            }
            if (($oUser = E::ModuleUser()->GetUserByLogin($sUser))
                && ($oUser->getActivate() == 1)
            ) {
                if (!in_array($oUser->getId(), $aUserInBlacklist)) {
                    if (array_key_exists($oUser->getId(), $aTalkUsers)) {
                        switch ($aTalkUsers[$oUser->getId()]->getUserActive()) {
                            // * Если пользователь ранее был удален админом разговора, то добавляем его снова
                            case ModuleTalk::TALK_USER_DELETE_BY_AUTHOR:
                                if (
                                    E::ModuleTalk()->AddTalkUser(
                                        E::GetEntity(
                                            'Talk_TalkUser',
                                            array(
                                                 'talk_id'          => $idTalk,
                                                 'user_id'          => $oUser->getId(),
                                                 'date_last'        => null,
                                                 'talk_user_active' => ModuleTalk::TALK_USER_ACTIVE
                                            )
                                        )
                                    )
                                ) {
                                    E::ModuleNotify()->SendTalkNew($oUser, $this->oUserCurrent, $oTalk);
                                    $aResult[] = array(
                                        'bStateError'   => false,
                                        'sMsgTitle'     => E::ModuleLang()->Get('attention'),
                                        'sMsg'          => E::ModuleLang()->Get(
                                            'talk_speaker_add_ok', array('login', htmlspecialchars($sUser))
                                        ),
                                        'sUserId'       => $oUser->getId(),
                                        'sUserLogin'    => $oUser->getLogin(),
                                        'sUserLink'     => $oUser->getUserWebPath(),
                                        'sUserWebPath'  => $oUser->getUserWebPath(),
                                        'sUserAvatar48' => $oUser->getAvatarUrl(48)
                                    );
                                    $bState = true;
                                } else {
                                    $aResult[] = array(
                                        'bStateError' => true,
                                        'sMsgTitle'   => E::ModuleLang()->Get('error'),
                                        'sMsg'        => E::ModuleLang()->Get('system_error')
                                    );
                                }
                                break;

                            // * Если пользователь является активным участником разговора, возвращаем ошибку
                            case ModuleTalk::TALK_USER_ACTIVE:
                                $aResult[] = array(
                                    'bStateError' => true,
                                    'sMsgTitle'   => E::ModuleLang()->Get('error'),
                                    'sMsg'        => E::ModuleLang()->Get(
                                        'talk_speaker_user_already_exist', array('login' => htmlspecialchars($sUser))
                                    )
                                );
                                break;

                            // * Если пользователь удалил себя из разговора самостоятельно, то блокируем повторное добавление
                            case ModuleTalk::TALK_USER_DELETE_BY_SELF:
                                $aResult[] = array(
                                    'bStateError' => true,
                                    'sMsgTitle'   => E::ModuleLang()->Get('error'),
                                    'sMsg'        => E::ModuleLang()->Get(
                                        'talk_speaker_delete_by_self', array('login' => htmlspecialchars($sUser))
                                    )
                                );
                                break;

                            default:
                                $aResult[] = array(
                                    'bStateError' => true,
                                    'sMsgTitle'   => E::ModuleLang()->Get('error'),
                                    'sMsg'        => E::ModuleLang()->Get('system_error')
                                );
                        }
                    } elseif (
                        E::ModuleTalk()->AddTalkUser(
                            E::GetEntity(
                                'Talk_TalkUser',
                                array(
                                     'talk_id'          => $idTalk,
                                     'user_id'          => $oUser->getId(),
                                     'date_last'        => null,
                                     'talk_user_active' => ModuleTalk::TALK_USER_ACTIVE
                                )
                            )
                        )
                    ) {
                        E::ModuleNotify()->SendTalkNew($oUser, $this->oUserCurrent, $oTalk);
                        $aResult[] = array(
                            'bStateError'   => false,
                            'sMsgTitle'     => E::ModuleLang()->Get('attention'),
                            'sMsg'          => E::ModuleLang()->Get(
                                'talk_speaker_add_ok', array('login', htmlspecialchars($sUser))
                            ),
                            'sUserId'       => $oUser->getId(),
                            'sUserLogin'    => $oUser->getLogin(),
                            'sUserLink'     => $oUser->getUserWebPath(),
                            'sUserWebPath'  => $oUser->getUserWebPath(),
                            'sUserAvatar48' => $oUser->getAvatarUrl(48)
                        );
                        $bState = true;
                    } else {
                        $aResult[] = array(
                            'bStateError' => true,
                            'sMsgTitle'   => E::ModuleLang()->Get('error'),
                            'sMsg'        => E::ModuleLang()->Get('system_error')
                        );
                    }
                } else {
                    // * Добавляем пользователь не принимает сообщения
                    $aResult[] = array(
                        'bStateError' => true,
                        'sMsgTitle'   => E::ModuleLang()->Get('error'),
                        'sMsg'        => E::ModuleLang()->Get(
                            'talk_user_in_blacklist', array('login' => htmlspecialchars($sUser))
                        )
                    );
                }
            } else {
                // * Пользователь не найден в базе данных или не активен
                $aResult[] = array(
                    'bStateError' => true,
                    'sMsgTitle'   => E::ModuleLang()->Get('error'),
                    'sMsg'        => E::ModuleLang()->Get('user_not_found', array('login' => htmlspecialchars($sUser)))
                );
            }
        }

        // * Передаем во вьевер массив результатов обработки по каждому пользователю
        E::ModuleViewer()->AssignAjax('aUsers', $aResult);
    }

    /**
     * Возвращает количество новых сообщений
     */
    public function AjaxNewMessages() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');

        if (!$this->oUserCurrent) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        $iCountTalkNew = E::ModuleTalk()->GetCountTalkNew($this->oUserCurrent->getId());
        E::ModuleViewer()->AssignAjax('iCountTalkNew', $iCountTalkNew);
    }

    /**
     * Обработка завершения работу экшена
     */
    public function EventShutdown() {

        if (!E::User()) {
            return;
        }
        $iCountTalkFavourite = E::ModuleTalk()->GetCountTalksFavouriteByUserId($this->oUserCurrent->getId());
        E::ModuleViewer()->Assign('iCountTalkFavourite', $iCountTalkFavourite);

        $iUserId = E::UserId();

        // Get stats of various user publications topics, comments, images, etc. and stats of favourites
        $aProfileStats = E::ModuleUser()->GetUserProfileStats($iUserId);

        // Получим информацию об изображениях пользователя
        /** @var ModuleMresource_EntityMresourceCategory[] $aUserImagesInfo */
        $aUserImagesInfo = E::ModuleMresource()->GetAllImageCategoriesByUserId($iUserId);

        E::ModuleViewer()->Assign('oUserProfile', E::User());
        E::ModuleViewer()->Assign('aProfileStats', $aProfileStats);
        E::ModuleViewer()->Assign('aUserImagesInfo', $aUserImagesInfo);

        // Old style skin compatibility
        E::ModuleViewer()->Assign('iCountTopicUser', $aProfileStats['count_topics']);
        E::ModuleViewer()->Assign('iCountCommentUser', $aProfileStats['count_comments']);
        E::ModuleViewer()->Assign('iCountTopicFavourite', $aProfileStats['favourite_topics']);
        E::ModuleViewer()->Assign('iCountCommentFavourite', $aProfileStats['favourite_comments']);
        E::ModuleViewer()->Assign('iCountNoteUser', $aProfileStats['count_usernotes']);
        E::ModuleViewer()->Assign('iCountWallUser', $aProfileStats['count_wallrecords']);

        E::ModuleViewer()->Assign('iPhotoCount', $aProfileStats['count_images']);
        E::ModuleViewer()->Assign('iCountCreated', $aProfileStats['count_created']);

        E::ModuleViewer()->Assign('iCountFavourite', $aProfileStats['count_favourites']);
        E::ModuleViewer()->Assign('iCountFriendsUser', $aProfileStats['count_friends']);

        E::ModuleViewer()->Assign('sMenuSubItemSelect', $this->sMenuSubItemSelect);
        E::ModuleViewer()->Assign('sMenuSubItemSelect', $this->sMenuSubItemSelect);

        // * Передаем константы состояний участников разговора
        E::ModuleViewer()->Assign('TALK_USER_ACTIVE', ModuleTalk::TALK_USER_ACTIVE);
        E::ModuleViewer()->Assign('TALK_USER_DELETE_BY_SELF', ModuleTalk::TALK_USER_DELETE_BY_SELF);
        E::ModuleViewer()->Assign('TALK_USER_DELETE_BY_AUTHOR', ModuleTalk::TALK_USER_DELETE_BY_AUTHOR);
    }
}

// EOF