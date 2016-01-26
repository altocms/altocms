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
 * Экшен обработки URL'ов вида /blog/
 *
 * @package actions
 * @since   1.0
 */
class ActionBlog extends Action {

    /**
     * Главное меню
     *
     * @var string
     */
    protected $sMenuHeadItemSelect = 'blog';

    /**
     * Какое меню активно
     *
     * @var string
     */
    protected $sMenuItemSelect = 'blog';

    /**
     * Какое подменю активно
     *
     * @var string
     */
    protected $sMenuSubItemSelect = 'good';

    /**
     * УРЛ блога который подставляется в меню
     *
     * @var string
     */
    protected $sMenuSubBlogUrl;

    /**
     * Текущий пользователь
     *
     * @var ModuleUser_EntityUser|null
     */
    protected $oUserCurrent = null;

    /**
     * Текущий блог
     *
     * @var ModuleBlog_EntityBlog|null
     */
    protected $oCurrentBlog = null;

    /**
     * Current topic
     *
     * @var ModuleTopic_EntityTopic
     */
    protected $oCurrentTopic = null;

    /**
     * Число новых топиков в коллективных блогах
     *
     * @var int
     */
    protected $iCountTopicsCollectiveNew = 0;

    /**
     * Число новых топиков в персональных блогах
     *
     * @var int
     */
    protected $iCountTopicsPersonalNew = 0;

    /**
     * Число новых топиков в конкретном блоге
     *
     * @var int
     */
    protected $iCountTopicsBlogNew = 0;

    /**
     * Число новых топиков
     *
     * @var int
     */
    protected $iCountTopicsNew = 0;

    /**
     * Named filter for topic list
     *
     * @var string
     */
    protected $sTopicFilter = '';

    protected $sTopicFilterPeriod;

    /**
     * Список URL с котрыми запрещено создавать блог
     *
     * @var array
     */
    protected $aBadBlogUrl
        = array(
            'new', 'good', 'bad', 'discussed', 'top', 'edit', 'add', 'admin', 'delete', 'invite',
            'ajaxaddcomment', 'ajaxresponsecomment', 'ajaxgetcomment', 'ajaxupdatecomment',
            'ajaxaddbloginvite', 'ajaxrebloginvite', 'ajaxremovebloginvite',
            'ajaxbloginfo', 'ajaxblogjoin', 'request',
        );

    /**
     * Типы блогов, доступные для создания
     *
     * @var
     */
    protected $aBlogTypes;

    protected $aMenuFilters = array('bad', 'new', 'newall', 'discussed', 'top');

    protected $sMenuDefault = 'good';

    /**
     * Инизиализация экшена
     *
     */
    public function Init() {

        //  Устанавливаем евент по дефолту, т.е. будем показывать хорошие топики из коллективных блогов
        $this->SetDefaultEvent('good');
        $this->sMenuSubBlogUrl = R::GetPath('blog');

        //  Достаём текущего пользователя
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();

        //  Загружаем в шаблон JS текстовки
        E::ModuleLang()->AddLangJs(
            array(
                 'blog_join', 'blog_leave',
            )
        );

        $this->aBlogTypes = E::ModuleBlog()->GetAllowBlogTypes($this->oUserCurrent, 'add');
    }

    /**
     * Регистрируем евенты, по сути определяем УРЛы вида /blog/.../
     *
     */
    protected function RegisterEvent() {

        $this->AddEvent('add', 'EventAddBlog');
        $this->AddEvent('edit', 'EventEditBlog');
        $this->AddEvent('delete', 'EventDeleteBlog');
        $this->AddEventPreg('/^admin$/i', '/^\d+$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventAdminBlog');
        $this->AddEvent('invite', 'EventInviteBlog');
        $this->AddEvent('request', 'EventRequestBlog');

        $this->AddEvent('ajaxaddcomment', 'AjaxAddComment');
        $this->AddEvent('ajaxresponsecomment', 'AjaxResponseComment');
        $this->AddEvent('ajaxgetcomment', 'AjaxGetComment');
        $this->AddEvent('ajaxupdatecomment', 'AjaxUpdateComment');

        $this->AddEvent('ajaxaddbloginvite', 'AjaxAddBlogInvite');
        $this->AddEvent('ajaxrebloginvite', 'AjaxReBlogInvite');
        $this->AddEvent('ajaxremovebloginvite', 'AjaxRemoveBlogInvite');

        $this->AddEvent('ajaxbloginfo', 'AjaxBlogInfo');
        $this->AddEvent('ajaxblogjoin', 'AjaxBlogJoin');

        $this->AddEventPreg('/^(\d+)\.html$/i', array('EventShowTopic', 'topic'));
        $this->AddEventPreg('/^[\w\-\_]+$/i', '/^(\d+)\.html$/i', array('EventShowTopic', 'topic'));

        // в URL должен быть хоть один нецифровой символ
        $this->AddEventPreg('/^([\w\-\_]*[a-z\-\_][\w\-\_]*)\.html$/i', array('EventShowTopicByUrl', 'topic'));

        $this->AddEventPreg('/^[\w\-\_]+$/i', '/^(page([1-9]\d{0,5}))?$/i', array('EventShowBlog', 'blog'));
        $this->AddEventPreg('/^[\w\-\_]+$/i', '/^bad$/i', '/^(page([1-9]\d{0,5}))?$/i', array('EventShowBlog', 'blog'));
        $this->AddEventPreg('/^[\w\-\_]+$/i', '/^new$/i', '/^(page([1-9]\d{0,5}))?$/i', array('EventShowBlog', 'blog'));
        $this->AddEventPreg('/^[\w\-\_]+$/i', '/^newall$/i', '/^(page([1-9]\d{0,5}))?$/i', array('EventShowBlog', 'blog'));
        $this->AddEventPreg('/^[\w\-\_]+$/i', '/^discussed$/i', '/^(page([1-9]\d{0,5}))?$/i', array('EventShowBlog', 'blog'));
        if (C::Get('rating.enabled')) {
            $this->AddEventPreg('/^[\w\-\_]+$/i', '/^top$/i', '/^(page([1-9]\d{0,5}))?$/i', array('EventShowBlog', 'blog'));
        }

        $this->AddEventPreg('/^[\w\-\_]+$/i', '/^users$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventShowUsers');
    }


    /**********************************************************************************
     ************************ РЕАЛИЗАЦИЯ ЭКШЕНА ***************************************
     **********************************************************************************
     */

    /**
     * Добавление нового блога
     *
     */
    protected function EventAddBlog() {

        //  Устанавливаем title страницы
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('blog_create'));

        //  Меню
        $this->sMenuSubItemSelect = 'add';
        $this->sMenuItemSelect = 'blog';

        //  Проверяем авторизован ли пользователь
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('not_access'), E::ModuleLang()->Get('error'));
            return R::Action('error');
        }

        //  check whether the user can create a blog?
        if (!E::ModuleACL()->CanCreateBlog($this->oUserCurrent)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('blog_create_acl'), E::ModuleLang()->Get('error'));
            return R::Action('error');
        }
        E::ModuleHook()->Run('blog_add_show');

        E::ModuleViewer()->Assign('aBlogTypes', $this->aBlogTypes);

        if (F::isPost('submit_blog_add')) {
            // Запускаем проверку корректности ввода полей при добалении блога.
            if (!$this->checkBlogFields()) {
                return false;
            }

            //  Если всё ок то пытаемся создать блог
            /** @var ModuleBlog_EntityBlog $oBlog */
            $oBlog = E::GetEntity('Blog');
            $oBlog->setOwnerId($this->oUserCurrent->getId());

            // issue 151 (https://github.com/altocms/altocms/issues/151)
            // Некорректная обработка названия блога
            // $oBlog->setTitle(strip_tags(F::GetRequestStr('blog_title')));
            $oBlog->setTitle(E::ModuleTools()->RemoveAllTags(F::GetRequestStr('blog_title')));

            // * Парсим текст на предмет разных HTML-тегов
            $sText = E::ModuleText()->Parser(F::GetRequestStr('blog_description'));
            $oBlog->setDescription($sText);
            $oBlog->setType(F::GetRequestStr('blog_type'));
            $oBlog->setDateAdd(F::Now());
            $oBlog->setLimitRatingTopic(floatval(F::GetRequestStr('blog_limit_rating_topic')));
            $oBlog->setUrl(F::GetRequestStr('blog_url'));
            $oBlog->setAvatar(null);

            // * Загрузка аватара блога перенесена в модуль
//        if ($aUploadedFile = $this->GetUploadedFile('avatar')) {
//            if ($sPath = E::ModuleBlog()->UploadBlogAvatar($aUploadedFile, $oBlog)) {
//                $oBlog->setAvatar($sPath);
//            } else {
//                E::ModuleMessage()->AddError(E::ModuleLang()->Get('blog_create_avatar_error'), E::ModuleLang()->Get('error'));
//                return false;
//            }
//        }

            // * Создаём блог
            E::ModuleHook()->Run('blog_add_before', array('oBlog' => $oBlog));
            if ($this->_addBlog($oBlog)) {
                E::ModuleHook()->Run('blog_add_after', array('oBlog' => $oBlog));

                // Читаем блог - это для получения полного пути блога,
                // если он в будущем будет зависит от других сущностей (компании, юзер и т.п.)
                $this->oCurrentBlog = $oBlog = E::ModuleBlog()->GetBlogById($oBlog->getId());

                // Добавляем событие в ленту
                E::ModuleStream()->Write($oBlog->getOwnerId(), 'add_blog', $oBlog->getId());

                // Подписываем владельца блога на свой блог
                E::ModuleUserfeed()->SubscribeUser($oBlog->getOwnerId(), ModuleUserfeed::SUBSCRIBE_TYPE_BLOG, $oBlog->getId());

                R::Location($oBlog->getUrlFull());
            } else {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            }
        }
        return true;
    }

    /**
     * Добавдение блога
     *
     * @param $oBlog
     *
     * @return bool|ModuleBlog_EntityBlog
     */
    protected function _addBlog($oBlog) {

        return E::ModuleBlog()->AddBlog($oBlog);
    }

    /**
     * Редактирование блога
     *
     */
    protected function EventEditBlog() {

        // Меню
        $this->sMenuSubItemSelect = '';
        $this->sMenuItemSelect = 'profile';

        // Передан ли в URL номер блога
        $sBlogId = $this->GetParam(0);
        if (!$oBlog = E::ModuleBlog()->GetBlogById($sBlogId)) {
            return parent::EventNotFound();
        }
        $this->oCurrentBlog = $oBlog;

        // Проверяем тип блога
        if ($oBlog->getType() == 'personal') {
            return parent::EventNotFound();
        }

        // Проверям, авторизован ли пользователь
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('not_access'), E::ModuleLang()->Get('error'));
            return R::Action('error');
        }

        // Проверка на право редактировать блог
        if (!E::ModuleACL()->IsAllowEditBlog($oBlog, $this->oUserCurrent)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('not_access'), E::ModuleLang()->Get('not_access'));
            return R::Action('error');
        }

        E::ModuleHook()->Run('blog_edit_show', array('oBlog' => $oBlog));

        // * Устанавливаем title страницы
        E::ModuleViewer()->AddHtmlTitle($oBlog->getTitle());
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('blog_edit'));

        E::ModuleViewer()->Assign('oBlogEdit', $oBlog);

        if (!isset($this->aBlogTypes[$oBlog->getType()])) {
            $this->aBlogTypes[$oBlog->getType()] = $oBlog->getBlogType();
        }
        E::ModuleViewer()->Assign('aBlogTypes', $this->aBlogTypes);

        // Устанавливаем шаблон для вывода
        $this->SetTemplateAction('add');

        // Если нажали кнопку "Сохранить"
        if (F::isPost('submit_blog_add')) {

            // Запускаем проверку корректности ввода полей при редактировании блога
            if (!$this->checkBlogFields($oBlog)) {
                return false;
            }

            // issue 151 (https://github.com/altocms/altocms/issues/151)
            // Некорректная обработка названия блога
            // $oBlog->setTitle(strip_tags(F::GetRequestStr('blog_title')));
            $oBlog->setTitle(E::ModuleTools()->RemoveAllTags(F::GetRequestStr('blog_title')));

            // Парсим описание блога
            $sText = E::ModuleText()->Parser(F::GetRequestStr('blog_description'));
            $oBlog->setDescription($sText);

            // Если меняется тип блога, фиксируем это
            if ($oBlog->getType() != F::GetRequestStr('blog_type')) {
                $oBlog->setOldType($oBlog->getType());
            }
            $oBlog->setType(F::GetRequestStr('blog_type'));
            $oBlog->setLimitRatingTopic(floatval(F::GetRequestStr('blog_limit_rating_topic')));

            if ($this->oUserCurrent->isAdministrator() || $this->oUserCurrent->isModerator()) {
                $oBlog->setUrl(F::GetRequestStr('blog_url')); // разрешаем смену URL блога только админу
            }

            // Обновляем блог
            E::ModuleHook()->Run('blog_edit_before', array('oBlog' => $oBlog));
            if ($this->_updateBlog($oBlog)) {
                E::ModuleHook()->Run('blog_edit_after', array('oBlog' => $oBlog));
                R::Location($oBlog->getUrlFull());
            } else {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                return R::Action('error');
            }
        } else {

            // Загружаем данные в форму редактирования блога
            $_REQUEST['blog_title'] = $oBlog->getTitle();
            $_REQUEST['blog_url'] = $oBlog->getUrl();
            $_REQUEST['blog_type'] = $oBlog->getType();
            $_REQUEST['blog_description'] = $oBlog->getDescription();
            $_REQUEST['blog_limit_rating_topic'] = $oBlog->getLimitRatingTopic();
            $_REQUEST['blog_id'] = $oBlog->getId();
        }
        return true;
    }

    /**
     * Обновление блога
     *
     * @param ModuleBlog_EntityBlog $oBlog
     *
     * @return bool
     */
    protected function _updateBlog($oBlog) {

        // Удалить аватар (для старых шаблонов)
        if (isset($_REQUEST['avatar_delete'])) {
            E::ModuleBlog()->DeleteBlogAvatar($oBlog);
            $oBlog->setAvatar(null);
        }

        $bResult = E::ModuleBlog()->UpdateBlog($oBlog);

        // Загрузка аватара (для старых шаблонов)
        if ($bResult && ($aUploadedFile = $this->GetUploadedFile('avatar'))) {
            if ($sUrl = E::ModuleBlog()->UploadBlogAvatar($aUploadedFile, $oBlog)) {
                $oBlog->setAvatar($sUrl);
            } else {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('blog_create_avatar_error'), E::ModuleLang()->Get('error'));
                return false;
            }
        }

        return $bResult;
    }

    /**
     * Management of blog's users
     *
     * @return string|null
     */
    protected function EventAdminBlog() {

        //  Меню
        $this->sMenuItemSelect = 'admin';
        $this->sMenuSubItemSelect = '';

        //  Проверяем передан ли в УРЛе номер блога
        $sBlogId = $this->GetParam(0);
        if (!$oBlog = E::ModuleBlog()->GetBlogById($sBlogId)) {
            return parent::EventNotFound();
        }
        $this->oCurrentBlog = $oBlog;

        //  Проверям авторизован ли пользователь
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('not_access'), E::ModuleLang()->Get('error'));
            return R::Action('error');
        }

        //  Проверка на право управлением пользователями блога
        if (!E::ModuleACL()->IsAllowAdminBlog($oBlog, $this->oUserCurrent)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('not_access'), E::ModuleLang()->Get('not_access'));
            return R::Action('error');
        }

        //  Обрабатываем сохранение формы
        if (F::isPost('submit_blog_admin')) {
            E::ModuleSecurity()->ValidateSendForm();

            $aUserRank = F::GetRequest('user_rank', array());
            if (!is_array($aUserRank)) {
                $aUserRank = array();
            }
            foreach ($aUserRank as $sUserId => $sRank) {
                $sRank = (string)$sRank;
                $iUserId = intval($sUserId);
                if (!$iUserId) {
                    continue;
                }
                if (!($oBlogUser = E::ModuleBlog()->GetBlogUserByBlogIdAndUserId($oBlog->getId(), $iUserId))) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                    break;
                }

                //  Увеличиваем число читателей блога
                if (in_array($sRank, array('administrator', 'moderator', 'reader'))
                    && $oBlogUser->getUserRole() == ModuleBlog::BLOG_USER_ROLE_BAN
                ) {
                    $oBlog->setCountUser($oBlog->getCountUser() + 1);
                }

                switch ($sRank) {
                    case 'administrator':
                        $oBlogUser->setUserRole(ModuleBlog::BLOG_USER_ROLE_ADMINISTRATOR);
                        break;
                    case 'moderator':
                        $oBlogUser->setUserRole(ModuleBlog::BLOG_USER_ROLE_MODERATOR);
                        break;
                    case 'reader':
                        $oBlogUser->setUserRole(ModuleBlog::BLOG_USER_ROLE_USER);
                        break;
                    case 'ban_for_comment':
                        $oBlogUser->setUserRole(ModuleBlog::BLOG_USER_ROLE_BAN_FOR_COMMENT);
                        break;
                    case 'ban':
                        if ($oBlogUser->getUserRole() != ModuleBlog::BLOG_USER_ROLE_BAN) {
                            $oBlog->setCountUser($oBlog->getCountUser() - 1);
                        }
                        $oBlogUser->setUserRole(ModuleBlog::BLOG_USER_ROLE_BAN);
                        break;
                    default:
                        $oBlogUser->setUserRole(ModuleBlog::BLOG_USER_ROLE_GUEST);
                }
                E::ModuleBlog()->UpdateRelationBlogUser($oBlogUser);
                E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('blog_admin_users_submit_ok'));
            }
            E::ModuleBlog()->UpdateBlog($oBlog);
        }

        //  Текущая страница
        $iPage = $this->GetParamEventMatch(1, 2) ? $this->GetParamEventMatch(1, 2) : 1;

        //  Получаем список подписчиков блога
        $aResult = E::ModuleBlog()->GetBlogUsersByBlogId(
            $oBlog->getId(),
            array(
                ModuleBlog::BLOG_USER_ROLE_ADMINISTRATOR,
                ModuleBlog::BLOG_USER_ROLE_MODERATOR,
                ModuleBlog::BLOG_USER_ROLE_MEMBER,
                ModuleBlog::BLOG_USER_ROLE_BAN_FOR_COMMENT,
                ModuleBlog::BLOG_USER_ROLE_BAN,
            ),
            $iPage, Config::Get('module.blog.users_per_page'));
        $aBlogUsers = $aResult['collection'];

        //  Формируем постраничность
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.blog.users_per_page'), Config::Get('pagination.pages.count'),
            R::GetPath('blog/admin') . $oBlog->getId()
        );
        E::ModuleViewer()->Assign('aPaging', $aPaging);

        //  Устанавливаем title страницы
        E::ModuleViewer()->AddHtmlTitle($oBlog->getTitle());
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('blog_admin'));

        E::ModuleViewer()->Assign('oBlogEdit', $oBlog);
        E::ModuleViewer()->Assign('aBlogUsers', $aBlogUsers);

        //  Устанавливаем шалон для вывода
        $this->SetTemplateAction('admin');

        // Если блог приватный или только для чтения, получаем приглашенных
        // и добавляем блок-форму для приглашения
        if ($oBlog->getBlogType() && ($oBlog->getBlogType()->IsPrivate() || $oBlog->getBlogType()->IsReadOnly())) {
            $aBlogUsersInvited = E::ModuleBlog()->GetBlogUsersByBlogId(
                $oBlog->getId(), ModuleBlog::BLOG_USER_ROLE_INVITE, null
            );
            E::ModuleViewer()->Assign('aBlogUsersInvited', $aBlogUsersInvited['collection']);
            if (E::ModuleViewer()->TemplateExists('widgets/widget.invite_to_blog.tpl')) {
                E::ModuleViewer()->AddWidget('right', 'widgets/widget.invite_to_blog.tpl');
            } elseif (E::ModuleViewer()->TemplateExists('actions/ActionBlog/invited.tpl')) {

                // LS-compatibility
                E::ModuleViewer()->AddWidget('right', 'actions/ActionBlog/invited.tpl');
            }
        }
        return null;
    }

    /**
     * Проверка полей блога
     *
     * @param ModuleBlog_EntityBlog|null $oBlog
     *
     * @return bool
     */
    protected function checkBlogFields($oBlog = null) {

        //  Проверяем только если была отправлена форма с данными (методом POST)
        if (!F::isPost('submit_blog_add')) {
            $_REQUEST['blog_limit_rating_topic'] = 0;
            return false;
        }
        E::ModuleSecurity()->ValidateSendForm();

        $bOk = true;

        //  Проверяем есть ли название блога
        if (!F::CheckVal( F::GetRequestStr('blog_title'), 'text', 2, 200)) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('blog_create_title_error'), E::ModuleLang()->Get('error'));
            $bOk = false;
        } else {
            //  Проверяем есть ли уже блог с таким названием
            if ($oBlogExists = E::ModuleBlog()->GetBlogByTitle( F::GetRequestStr('blog_title'))) {
                if (!$oBlog || $oBlog->getId() != $oBlogExists->getId()) {
                    E::ModuleMessage()->AddError(
                        E::ModuleLang()->Get('blog_create_title_error_unique'), E::ModuleLang()->Get('error')
                    );
                    $bOk = false;
                }
            }
        }

        //  Проверяем есть ли URL блога, с заменой всех пробельных символов на "_"
        if (!$oBlog || $this->oUserCurrent->isAdministrator()) {
            $blogUrl = preg_replace("/\s+/", '_',  F::GetRequestStr('blog_url'));
            $_REQUEST['blog_url'] = $blogUrl;
            if (!F::CheckVal( F::GetRequestStr('blog_url'), 'login', 2, 50)) {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('blog_create_url_error'), E::ModuleLang()->Get('error'));
                $bOk = false;
            }
        }
        //  Проверяем на счет плохих УРЛов
        if (in_array( F::GetRequestStr('blog_url'), $this->aBadBlogUrl)) {
            E::ModuleMessage()->AddError(
                E::ModuleLang()->Get('blog_create_url_error_badword') . ' ' . join(',', $this->aBadBlogUrl),
                E::ModuleLang()->Get('error')
            );
            $bOk = false;
        }
        //  Проверяем есть ли уже блог с таким URL
        if ($oBlogExists = E::ModuleBlog()->GetBlogByUrl( F::GetRequestStr('blog_url'))) {
            if (!$oBlog || $oBlog->getId() != $oBlogExists->getId()) {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('blog_create_url_error_unique'), E::ModuleLang()->Get('error'));
                $bOk = false;
            }
        }

        // * Проверяем доступные типы блога для создания
        $aBlogTypes = E::ModuleBlog()->GetAllowBlogTypes($this->oUserCurrent, 'add');
        if (!in_array( F::GetRequestStr('blog_type'), array_keys($aBlogTypes))) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('blog_create_type_error'), E::ModuleLang()->Get('error'));
            $bOk = false;
        }

        //  Проверяем есть ли описание блога
        if (!F::CheckVal( F::GetRequestStr('blog_description'), 'text', 10, 3000)) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('blog_create_description_error'), E::ModuleLang()->Get('error'));
            $bOk = false;
        }

        //  Выполнение хуков
        E::ModuleHook()->Run('check_blog_fields', array('bOk' => &$bOk));
        return $bOk;
    }

    /**
     * Показ всех топиков
     *
     */
    protected function EventTopics() {

        $this->sTopicFilter = $this->sCurrentEvent;
        $this->sTopicFilterPeriod = 1; // по дефолту 1 день
        if (in_array( F::GetRequestStr('period'), array(1, 7, 30, 'all'))) {
            $this->sTopicFilterPeriod =  F::GetRequestStr('period');
        }
        if (!in_array($this->sTopicFilter, array('discussed', 'top'))) {
            $this->sTopicFilterPeriod = 'all';
        }

        //  Меню
        //$this->sMenuSubItemSelect = $this->sTopicFilter == 'newall' ? 'new' : $this->sTopicFilter;
        $this->sMenuSubItemSelect = $this->sTopicFilter == 'all' ? 'new' : $this->sTopicFilter;

        //  Передан ли номер страницы
        $iPage = $this->GetParamEventMatch(0, 2) ? $this->GetParamEventMatch(0, 2) : 1;
        if ($iPage == 1 && !F::GetRequest('period')) {
            E::ModuleViewer()->SetHtmlCanonical(R::GetPath('blog') . $this->sTopicFilter . '/');
        }
        //  Получаем список топиков
        $aResult = E::ModuleTopic()->GetTopicsCollective(
            $iPage, Config::Get('module.topic.per_page'), $this->sTopicFilter, $this->sTopicFilterPeriod == 'all' ? null : $this->sTopicFilterPeriod * 60 * 60 * 24
        );
        //  Если нет топиков за 1 день, то показываем за неделю (7)
        if (in_array($this->sTopicFilter, array('discussed', 'top')) && !$aResult['count'] && $iPage == 1 && !F::GetRequest('period')) {
            $this->sTopicFilterPeriod = 7;
            $aResult = E::ModuleTopic()->GetTopicsCollective(
                $iPage, Config::Get('module.topic.per_page'), $this->sTopicFilter,
                $this->sTopicFilterPeriod == 'all' ? null : $this->sTopicFilterPeriod * 60 * 60 * 24
            );
        }
        $aTopics = $aResult['collection'];

        //  Вызов хуков
        E::ModuleHook()->Run('topics_list_show', array('aTopics' => $aTopics));

        //  Формируем постраничность
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $iPage, Config::Get('module.topic.per_page'), Config::Get('pagination.pages.count'),
            R::GetPath('blog') . $this->sTopicFilter,
            in_array($this->sTopicFilter, array('discussed', 'top')) ? array('period' => $this->sTopicFilterPeriod) : array()
        );

        //  Вызов хуков
        E::ModuleHook()->Run('blog_show', array('sShowType' => $this->sTopicFilter));

        //  Загружаем переменные в шаблон
        E::ModuleViewer()->Assign('aTopics', $aTopics);
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        if (in_array($this->sTopicFilter, array('discussed', 'top'))) {
            E::ModuleViewer()->Assign('sPeriodSelectCurrent', $this->sTopicFilterPeriod);
            E::ModuleViewer()->Assign('sPeriodSelectRoot', R::GetPath('blog') . $this->sTopicFilter . '/');
        }
        E::ModuleViewer()->Assign('sTopicFilter', $this->sTopicFilter);
        E::ModuleViewer()->Assign('sTopicFilterPeriod', $this->sTopicFilterPeriod);

        //  Устанавливаем шаблон вывода
        $this->SetTemplateAction('index');
    }

    /**
     * Show topic by URL
     *
     * @return string|null
     */
    protected function EventShowTopicByUrl() {

        $sTopicUrl = $this->GetEventMatch(1);

        // Проверяем есть ли такой топик
        $this->oCurrentTopic = E::ModuleTopic()->GetTopicByUrl($sTopicUrl);
        if (!$this->oCurrentTopic) {
            return parent::EventNotFound();
        }

        return $this->EventShowTopic();
    }

    /**
     * Show topic
     *
     * @return string|null
     */
    protected function EventShowTopic() {

        $this->sMenuHeadItemSelect = 'index';

        $sBlogUrl = '';
        $sTopicUrlMask = R::GetTopicUrlMask();

        if ($this->oCurrentTopic) {
            $this->oCurrentBlog = $this->oCurrentTopic->getBlog();
            if ($this->oCurrentBlog) {
                $sBlogUrl = $this->oCurrentBlog->getUrl();
            }
            $this->sMenuItemSelect = 'blog';
        } else {
            if ($this->GetParamEventMatch(0, 1)) {

                // из коллективного блога
                $sBlogUrl = $this->sCurrentEvent;
                $iTopicId = $this->GetParamEventMatch(0, 1);
                $this->sMenuItemSelect = 'blog';
            } else {
                // из персонального блога
                $iTopicId = $this->GetEventMatch(1);
                $this->sMenuItemSelect = 'log';
            }
            // * Проверяем есть ли такой топик
            if (!$iTopicId || !($this->oCurrentTopic = E::ModuleTopic()->GetTopicById($iTopicId))) {
                return parent::EventNotFound();
            }
        }
        if (!$this->oCurrentTopic->getBlog()) {
            // Этого быть не должно, но если вдруг, то надо отработать
            return parent::EventNotFound();
        }

        $this->sMenuSubItemSelect = '';

        // Trusted user is admin or owner of topic
        if ($this->oUserCurrent && ($this->oUserCurrent->isAdministrator() || $this->oUserCurrent->isModerator() || ($this->oUserCurrent->getId() == $this->oCurrentTopic->getUserId()))) {
            $bTrustedUser = true;
        } else {
            $bTrustedUser = false;
        }

        if (!$bTrustedUser) {
            // Topic with future date
            if ($this->oCurrentTopic->getDate() > date('Y-m-d H:i:s')) {
                return parent::EventNotFound();
            }

            // * Проверяем права на просмотр топика-черновика
            if (!$this->oCurrentTopic->getPublish()) {
                if (!Config::Get('module.topic.draft_link')) {
                    return parent::EventNotFound();
                } else {
                    // Если режим просмотра по прямой ссылке включен, то проверяем параметры
                    $bOk = false;
                    if ($sDraftCode = F::GetRequestStr('draft', null, 'get')) {
                        if (strpos($sDraftCode, ':')) {
                            list($nUser, $sHash) = explode(':', $sDraftCode);
                            if ($this->oCurrentTopic->GetUserId() == $nUser && $this->oCurrentTopic->getTextHash() == $sHash) {
                                $bOk = true;
                            }
                        }
                    }
                    if (!$bOk) {
                        return parent::EventNotFound();
                    }
                }
            }
        }

        // Если номер топика правильный, но URL блога неверный, то корректируем его и перенаправляем на нужный адрес
        if ($sBlogUrl != '' && $this->oCurrentTopic->getBlog()->getUrl() != $sBlogUrl) {
            R::Location($this->oCurrentTopic->getUrl());
        }

        // Если запросили топик с определенной маской, не указаным названием блога,
        // но ссылка на топик и ЧПУ url разные, и это не запрос RSS
        // то перенаправляем на страницу для вывода топика (во избежание дублирования контента по разным URL)
        if ($sTopicUrlMask && $sBlogUrl == '' 
            && $this->oCurrentTopic->getUrl() != R::GetPathWebCurrent() . (substr($this->oCurrentTopic->getUrl(), -1) == '/' ? '/' : '')
            && substr(R::RealUrl(true), 0, 4) !== 'rss/'
        ) {
            R::Location($this->oCurrentTopic->getUrl());
        }

        // Checks rights to show content from the blog
        if (!$this->oCurrentTopic->getBlog()->CanReadBy($this->oUserCurrent)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('acl_cannot_show_content'), E::ModuleLang()->Get('not_access'));
            return R::Action('error');
        }

        // Обрабатываем добавление коммента
        if (isset($_REQUEST['submit_comment'])) {
            $this->SubmitComment();
        }

        // Достаём комменты к топику
        if (!Config::Get('module.comment.nested_page_reverse')
            && Config::Get('module.comment.use_nested')
            && Config::Get('module.comment.nested_per_page')
        ) {
            $iPageDef = ceil(
                E::ModuleComment()->GetCountCommentsRootByTargetId($this->oCurrentTopic->getId(), 'topic') / Config::Get('module.comment.nested_per_page')
            );
        } else {
            $iPageDef = 1;
        }
        $iPage = intval(F::GetRequest('cmtpage', 0));
        if ($iPage < 1) {
            $iPage = $iPageDef;
        }

        $aReturn = E::ModuleComment()->GetCommentsByTargetId($this->oCurrentTopic, 'topic', $iPage, Config::Get('module.comment.nested_per_page'));
        $iMaxIdComment = $aReturn['iMaxIdComment'];
        /** @var ModuleComment_EntityComment[] $aComments */
        $aComments = $aReturn['comments'];

        if ($aComments && $iMaxIdComment && isset($aComments[$iMaxIdComment])) {
            $sLastCommentDate = $aComments[$iMaxIdComment]->getDate();
        } else {
            $sLastCommentDate = null;
        }
        // Если используется постраничность для комментариев - формируем ее
        if (Config::Get('module.comment.use_nested') && Config::Get('module.comment.nested_per_page')) {
            $aPaging = E::ModuleViewer()->MakePaging(
                $aReturn['count'], $iPage, Config::Get('module.comment.nested_per_page'),
                Config::Get('pagination.pages.count'), ''
            );
            if (!Config::Get('module.comment.nested_page_reverse') && $aPaging) {
                // переворачиваем страницы в обратном порядке
                $aPaging['aPagesLeft'] = array_reverse($aPaging['aPagesLeft']);
                $aPaging['aPagesRight'] = array_reverse($aPaging['aPagesRight']);
            }
            E::ModuleViewer()->Assign('aPagingCmt', $aPaging);
        }

//      issue 253 {@link https://github.com/altocms/altocms/issues/253}
//      Запрещаем оставлять комментарии к топику-черновику
//      if ($this->oUserCurrent) {
        if ($this->oUserCurrent && (int)$this->oCurrentTopic->getPublish()) {
            $bAllowToComment = E::ModuleBlog()->GetBlogsAllowTo('comment', $this->oUserCurrent, $this->oCurrentTopic->getBlog()->GetId(), true);
        } else {
            $bAllowToComment = false;
        }

        // Отмечаем прочтение топика
        if ($this->oUserCurrent) {
            $oTopicRead = E::ModuleTopic()->GetTopicRead($this->oCurrentTopic->getId(), $this->oUserCurrent->getid());
            if (!$oTopicRead) {
                /** @var ModuleTopic_EntityTopicRead $oTopicRead */
                $oTopicRead = E::GetEntity('Topic_TopicRead');
                $oTopicRead->setTopicId($this->oCurrentTopic->getId());
                $oTopicRead->setUserId($this->oUserCurrent->getId());
                $oTopicRead->setCommentCountLast($this->oCurrentTopic->getCountComment());
                $oTopicRead->setCommentIdLast($iMaxIdComment);
                $oTopicRead->setDateRead(F::Now());
                E::ModuleTopic()->AddTopicRead($oTopicRead);
            } else {
                if (($oTopicRead->getCommentCountLast() != $this->oCurrentTopic->getCountComment())
                    || ($oTopicRead->getCommentIdLast() != $iMaxIdComment)
                    || (!is_null($sLastCommentDate) && $oTopicRead->getDateRead() <= $sLastCommentDate)
                ) {
                    $oTopicRead->setCommentCountLast($this->oCurrentTopic->getCountComment());
                    $oTopicRead->setCommentIdLast($iMaxIdComment);
                    $oTopicRead->setDateRead(F::Now());
                    E::ModuleTopic()->UpdateTopicRead($oTopicRead);
                }
            }
        }

        // Выставляем SEO данные
        $sTextSeo = strip_tags($this->oCurrentTopic->getText());
        E::ModuleViewer()->SetHtmlDescription(F::CutText($sTextSeo, Config::Get('view.html.description_max_words')));
        E::ModuleViewer()->SetHtmlKeywords($this->oCurrentTopic->getTags());
        E::ModuleViewer()->SetHtmlCanonical($this->oCurrentTopic->getUrl());

        // Вызов хуков
        E::ModuleHook()->Run('topic_show', array('oTopic' => $this->oCurrentTopic));

        // Загружаем переменные в шаблон
        E::ModuleViewer()->Assign('oTopic', $this->oCurrentTopic);
        E::ModuleViewer()->Assign('aComments', $aComments);
        E::ModuleViewer()->Assign('iMaxIdComment', $iMaxIdComment);
        E::ModuleViewer()->Assign('bAllowToComment', $bAllowToComment);

        // Устанавливаем title страницы
        E::ModuleViewer()->AddHtmlTitle($this->oCurrentTopic->getBlog()->getTitle());
        E::ModuleViewer()->AddHtmlTitle($this->oCurrentTopic->getTitle());
        E::ModuleViewer()->SetHtmlRssAlternate(
            R::GetPath('rss') . 'comments/' . $this->oCurrentTopic->getId() . '/', $this->oCurrentTopic->getTitle()
        );

        // Устанавливаем шаблон вывода
        $this->SetTemplateAction('topic');

        // Additional tags for <head>
        $aHeadTags = $this->_getHeadTags($this->oCurrentTopic);
        if ($aHeadTags) {
            E::ModuleViewer()->SetHtmlHeadTags($aHeadTags);
        }

        return null;
    }

    /**
     * Additional tags for <head>
     *
     * @param object $oTopic
     *
     * @return array
     */
    protected function _getHeadTags($oTopic) {

        $aHeadTags = array();
        if (!$oTopic->getPublish()) {
            // Disable indexing of drafts
            $aHeadTags[] = array(
                'meta',
                array(
                    'name' => 'robots',
                    'content' => 'noindex,nofollow',
                ),
            );
        } else {
            // Tags for social networks
            $aHeadTags[] = array(
                'meta',
                array(
                    'property' => 'og:title',
                    'content' => $oTopic->getTitle(),
                ),
            );
            $aHeadTags[] = array(
                'meta',
                array(
                    'property' => 'og:url',
                    'content' => $oTopic->getUrl(),
                ),
            );
            $aHeadTags[] = array(
                'meta',
                array(
                    'property' => 'og:description',
                    'content' => E::ModuleViewer()->GetHtmlDescription(),
                ),
            );
            $aHeadTags[] = array(
                'meta',
                array(
                    'property' => 'og:site_name',
                    'content' => Config::Get('view.name'),
                ),
            );
            $aHeadTags[] = array(
                'meta',
                array(
                    'property' => 'og:type',
                    'content' => 'article',
                ),
            );
            $aHeadTags[] = array(
                'meta',
                array(
                    'name' => 'twitter:card',
                    'content' => 'summary',
                ),
            );
            if ($oTopic->getPreviewImageUrl()) {
                $aHeadTags[] = array(
                    'meta',
                    array(
                        'name' => 'og:image',
                        'content' => $oTopic->getPreviewImageUrl('700crop'),
                    ),
                );
            }

        }
        return $aHeadTags;
    }

    /**
     * Страница со списком читателей блога
     *
     */
    protected function EventShowUsers() {

        $sBlogUrl = $this->sCurrentEvent;

        //  Проверяем есть ли блог с таким УРЛ
        if (!($oBlog = E::ModuleBlog()->GetBlogByUrl($sBlogUrl))) {
            return parent::EventNotFound();
        }
        $this->oCurrentBlog = $oBlog;

            //  Меню
        $this->sMenuSubItemSelect = '';
        $this->sMenuSubBlogUrl = $oBlog->getUrlFull();

        //  Текущая страница
        $iPage = $this->GetParamEventMatch(1, 2) ? $this->GetParamEventMatch(1, 2) : 1;
        $aBlogUsersResult = E::ModuleBlog()->GetBlogUsersByBlogId(
            $oBlog->getId(), ModuleBlog::BLOG_USER_ROLE_USER, $iPage, Config::Get('module.blog.users_per_page')
        );
        $aBlogUsers = $aBlogUsersResult['collection'];

        //  Формируем постраничность
        $aPaging = E::ModuleViewer()->MakePaging(
            $aBlogUsersResult['count'], $iPage, Config::Get('module.blog.users_per_page'),
            Config::Get('pagination.pages.count'), $oBlog->getUrlFull() . 'users'
        );
        E::ModuleViewer()->Assign('aPaging', $aPaging);

        //  Вызов хуков
        E::ModuleHook()->Run('blog_collective_show_users', array('oBlog' => $oBlog));

        //  Загружаем переменные в шаблон
        E::ModuleViewer()->Assign('aBlogUsers', $aBlogUsers);
        E::ModuleViewer()->Assign('iCountBlogUsers', $aBlogUsersResult['count']);
        E::ModuleViewer()->Assign('oBlog', $oBlog);

        //  Устанавливаем title страницы
        E::ModuleViewer()->AddHtmlTitle($oBlog->getTitle());

        //  Устанавливаем шаблон вывода
        $this->SetTemplateAction('users');

        return true;
    }

    /**
     * Вывод топиков из определенного блога
     *
     */
    protected function EventShowBlog() {

        $this->sMenuHeadItemSelect = 'index';

        $this->sTopicFilterPeriod = 1; // по дефолту 1 день
        if (in_array( F::GetRequestStr('period'), array(1, 7, 30, 'all'))) {
            $this->sTopicFilterPeriod =  F::GetRequestStr('period');
        }
        $sBlogUrl = $this->sCurrentEvent;
        $this->sTopicFilter = in_array($this->GetParamEventMatch(0, 0), $this->aMenuFilters)
            ? $this->GetParamEventMatch(0, 0)
            : $this->sMenuDefault;
        if (!in_array($this->sTopicFilter, array('discussed', 'top'))) {
            $this->sTopicFilterPeriod = 'all';
        }

        //  Try to get blog by URL
        $oBlog = E::ModuleBlog()->GetBlogByUrl($sBlogUrl);
        if (!$oBlog && (intval($sBlogUrl) == $sBlogUrl)) {
            // Try to get blog by ID
            $oBlog = E::ModuleBlog()->GetBlogById($sBlogUrl);
        }
        if (!$oBlog) {
            return parent::EventNotFound();
        }
        $this->oCurrentBlog = $oBlog;

            //  Определяем права на отображение закрытого блога
        $oBlogType = $oBlog->GetBlogType();
        if ($oBlogType) {
            $bCloseBlog = !$oBlog->CanReadBy($this->oUserCurrent);
        } else {
            // if blog type not defined then it' open blog
            $bCloseBlog = false;
        }

        // В скрытый блог посторонних совсем не пускам
        if ($bCloseBlog && $oBlog->getBlogType() && $oBlog->GetBlogType()->IsHidden()) {
            return parent::EventNotFound();
        }

        //  Меню
        //$this->sMenuSubItemSelect = $this->sTopicFilter == 'newall' ? 'new' : $this->sTopicFilter;
        $this->sMenuSubItemSelect = $this->sTopicFilter == 'all' ? 'new' : $this->sTopicFilter;
        $this->sMenuSubBlogUrl = $oBlog->getUrlFull();

        //  Передан ли номер страницы
        $iPage = $this->GetParamEventMatch(($this->sTopicFilter == 'good') ? 0 : 1, 2)
            ? $this->GetParamEventMatch(($this->sTopicFilter == 'good') ? 0 : 1, 2)
            : 1;
        if (($iPage == 1) && !F::GetRequest('period') && in_array($this->sTopicFilter, array('discussed', 'top'))) {
            E::ModuleViewer()->SetHtmlCanonical($oBlog->getUrlFull() . $this->sTopicFilter . '/');
        }

        //  Получаем число новых топиков в текущем блоге (даже для закрытых блогов)
        $this->iCountTopicsBlogNew = E::ModuleTopic()->GetCountTopicsByBlogNew($oBlog);

        if (!$bCloseBlog) {
            //  Получаем список топиков
            $aResult = E::ModuleTopic()->GetTopicsByBlog(
                $oBlog, $iPage, Config::Get('module.topic.per_page'), $this->sTopicFilter,
                $this->sTopicFilterPeriod == 'all' ? null : $this->sTopicFilterPeriod * 60 * 60 * 24
            );
            //  Если нет топиков за 1 день, то показываем за неделю (7)
            if (in_array($this->sTopicFilter, array('discussed', 'top')) && !$aResult['count'] && $iPage == 1 && !F::GetRequest('period')) {
                $this->sTopicFilterPeriod = 7;
                $aResult = E::ModuleTopic()->GetTopicsByBlog(
                    $oBlog, $iPage, Config::Get('module.topic.per_page'), $this->sTopicFilter,
                    $this->sTopicFilterPeriod == 'all' ? null : $this->sTopicFilterPeriod * 60 * 60 * 24
                );
            }
            $aTopics = $aResult['collection'];
            //  Формируем постраничность
            if (($this->sTopicFilter == 'good')) {
                $aPaging = E::ModuleViewer()->MakePaging(
                        $aResult['count'], $iPage, Config::Get('module.topic.per_page'),
                        Config::Get('pagination.pages.count'), rtrim($oBlog->getLink(), '/')
                    );
            } elseif (($this->sTopicFilter == 'all') || ($this->sTopicFilter == 'newall')) {
                $aPaging = E::ModuleViewer()->MakePaging(
                    $aResult['count'], $iPage, Config::Get('module.topic.per_page'),
                    Config::Get('pagination.pages.count'), $oBlog->getLink() . $this->sTopicFilter
                );
            } else {
                $aPaging = E::ModuleViewer()->MakePaging(
                        $aResult['count'], $iPage, Config::Get('module.topic.per_page'),
                        Config::Get('pagination.pages.count'), $oBlog->getLink() . $this->sTopicFilter,
                        array('period' => $this->sTopicFilterPeriod)
                    );
            }

            E::ModuleViewer()->Assign('aPaging', $aPaging);
            E::ModuleViewer()->Assign('aTopics', $aTopics);
            E::ModuleViewer()->Assign('iTopicsTotal', $aResult['count']);
            if (in_array($this->sTopicFilter, array('discussed', 'top'))) {
                E::ModuleViewer()->Assign('sPeriodSelectCurrent', $this->sTopicFilterPeriod);
                E::ModuleViewer()->Assign('sPeriodSelectRoot', $oBlog->getLink() . $this->sTopicFilter . '/');
            }
        }
        //  Выставляем SEO данные
        $sTextSeo = strip_tags($oBlog->getDescription());
        E::ModuleViewer()->SetHtmlDescription(F::CutText($sTextSeo, Config::Get('view.html.description_max_words')));

        //  Получаем список юзеров блога
        $aBlogUsersResult = E::ModuleBlog()->GetBlogUsersByBlogId(
            $oBlog->getId(), ModuleBlog::BLOG_USER_ROLE_USER, 1, Config::Get('module.blog.users_per_page')
        );
        $aBlogUsers = $aBlogUsersResult['collection'];
        $aBlogModeratorsResult = E::ModuleBlog()->GetBlogUsersByBlogId(
            $oBlog->getId(), ModuleBlog::BLOG_USER_ROLE_MODERATOR
        );
        $aBlogModerators = $aBlogModeratorsResult['collection'];
        $aBlogAdministratorsResult = E::ModuleBlog()->GetBlogUsersByBlogId(
            $oBlog->getId(), ModuleBlog::BLOG_USER_ROLE_ADMINISTRATOR
        );
        $aBlogAdministrators = $aBlogAdministratorsResult['collection'];

        //  Для админов проекта получаем список блогов и передаем их во вьювер
        if ($this->oUserCurrent && ($this->oUserCurrent->isAdministrator() || $this->oUserCurrent->isModerator())) {
            $aBlogs = E::ModuleBlog()->GetBlogs();
            unset($aBlogs[$oBlog->getId()]);

            E::ModuleViewer()->Assign('aBlogs', $aBlogs);
        }
        //  Вызов хуков
        E::ModuleHook()->Run('blog_collective_show', array('oBlog' => $oBlog, 'sShowType' => $this->sTopicFilter));

        //  Загружаем переменные в шаблон
        E::ModuleViewer()->Assign('aBlogUsers', $aBlogUsers);
        E::ModuleViewer()->Assign('aBlogModerators', $aBlogModerators);
        E::ModuleViewer()->Assign('aBlogAdministrators', $aBlogAdministrators);
        E::ModuleViewer()->Assign('iCountBlogUsers', $aBlogUsersResult['count']);
        E::ModuleViewer()->Assign('iCountBlogModerators', $aBlogModeratorsResult['count']);
        E::ModuleViewer()->Assign('iCountBlogAdministrators', $aBlogAdministratorsResult['count'] + 1);
        E::ModuleViewer()->Assign('oBlog', $oBlog);
        E::ModuleViewer()->Assign('bCloseBlog', $bCloseBlog);
        E::ModuleViewer()->Assign('sTopicFilter', $this->sTopicFilter);
        E::ModuleViewer()->Assign('sTopicFilterPeriod', $this->sTopicFilterPeriod);

        //  Устанавливаем title страницы
        E::ModuleViewer()->AddHtmlTitle($oBlog->getTitle());
        E::ModuleViewer()->SetHtmlRssAlternate(
            R::GetPath('rss') . 'blog/' . $oBlog->getUrl() . '/', $oBlog->getTitle()
        );
        //  Устанавливаем шаблон вывода
        $this->SetTemplateAction('blog');

        return true;
    }

    /**
     * Обработка добавление комментария к топику через ajax
     *
     */
    protected function AjaxAddComment() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');
        $this->SubmitComment();
    }

    /**
     * Обработка добавление комментария к топику
     *
     */
    protected function SubmitComment() {

        // * Проверям авторизован ли пользователь
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Проверяем топик
        if (!($oTopic = E::ModuleTopic()->GetTopicById( F::GetRequestStr('cmt_target_id')))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Возможность постить коммент в топик в черновиках
        if (!$oTopic->getPublish()
//            issue 253 {@link https://github.com/altocms/altocms/issues/253}
//            Запрещаем оставлять комментарии к топику-черновику
//            && ($this->oUserCurrent->getId() != $oTopic->getUserId())
//            && !$this->oUserCurrent->isAdministrator()
        ) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Проверяем разрешено ли постить комменты
        switch (E::ModuleACL()->CanPostComment($this->oUserCurrent, $oTopic)) {
            case ModuleACL::CAN_TOPIC_COMMENT_ERROR_BAN:
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_comment_banned'), E::ModuleLang()->Get('attention'));
                return;
            case ModuleACL::CAN_TOPIC_COMMENT_FALSE:
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_comment_acl'), E::ModuleLang()->Get('error'));
                return;
            }

        // * Проверяем разрешено ли постить комменты по времени
        if (!E::ModuleACL()->CanPostCommentTime($this->oUserCurrent)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_comment_limit'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Проверяем запрет на добавления коммента автором топика
        if ($oTopic->getForbidComment()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_comment_notallow'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Проверяем текст комментария
        $sText = E::ModuleText()->Parser(F::GetRequestStr('comment_text'));
        if (!F::CheckVal($sText, 'text', Config::Val('module.comment.min_length', 2), Config::Val('module.comment.max_length', 10000))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_comment_text_len', array(
                'min' => 2,
                'max' => Config::Val('module.comment.max_length', 10000)
            )), E::ModuleLang()->Get('error'));
            return;
        }
        $iMin = Config::Val('module.comment.min_length', 2);
        $iMax = Config::Val('module.comment.max_length', 0);
        if (!F::CheckVal($sText, 'text', $iMin, $iMax)) {
            if ($iMax) {
                E::ModuleMessage()->AddErrorSingle(
                    E::ModuleLang()->Get('topic_comment_text_len', array('min' => $iMin, 'max' => $iMax)), E::ModuleLang()->Get('error')
                );
            } else {
                E::ModuleMessage()->AddErrorSingle(
                    E::ModuleLang()->Get('topic_comment_text_min', array('min' => $iMin)), E::ModuleLang()->Get('error')
                );
            }
            return;
        }

        // * Проверям на какой коммент отвечаем
        if (!$this->isPost('reply')) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
        $oCommentParent = null;
        $iParentId = intval(F::GetRequest('reply'));
        if ($iParentId != 0) {
            // * Проверяем существует ли комментарий на который отвечаем
            if (!($oCommentParent = E::ModuleComment()->GetCommentById($iParentId))) {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                return;
            }
            // * Проверяем из одного топика ли новый коммент и тот на который отвечаем
            if ($oCommentParent->getTargetId() != $oTopic->getId()) {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                return;
            }
        } else {

            // * Корневой комментарий
            $iParentId = null;
        }

        // * Проверка на дублирующий коммент
        if (E::ModuleComment()->GetCommentUnique($oTopic->getId(), 'topic', $this->oUserCurrent->getId(), $iParentId, md5($sText))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('topic_comment_spam'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Создаём коммент
        /** @var ModuleComment_EntityComment $oCommentNew */
        $oCommentNew = E::GetEntity('Comment');
        $oCommentNew->setTargetId($oTopic->getId());
        $oCommentNew->setTargetType('topic');
        $oCommentNew->setTargetParentId($oTopic->getBlog()->getId());
        $oCommentNew->setUserId($this->oUserCurrent->getId());
        $oCommentNew->setText($sText);
        $oCommentNew->setDate(F::Now());
        $oCommentNew->setUserIp(F::GetUserIp());
        $oCommentNew->setPid($iParentId);
        $oCommentNew->setTextHash(md5($sText));
        $oCommentNew->setPublish($oTopic->getPublish());

        // * Добавляем коммент
        E::ModuleHook()->Run(
            'comment_add_before',
            array('oCommentNew' => $oCommentNew, 'oCommentParent' => $oCommentParent, 'oTopic' => $oTopic)
        );
        if (E::ModuleComment()->AddComment($oCommentNew)) {
            E::ModuleHook()->Run(
                'comment_add_after',
                array('oCommentNew' => $oCommentNew, 'oCommentParent' => $oCommentParent, 'oTopic' => $oTopic)
            );

            E::ModuleViewer()->AssignAjax('sCommentId', $oCommentNew->getId());
            if ($oTopic->getPublish()) {

                // * Добавляем коммент в прямой эфир если топик не в черновиках
                /** @var ModuleComment_EntityCommentOnline $oCommentOnline */
                $oCommentOnline = E::GetEntity('Comment_CommentOnline');
                $oCommentOnline->setTargetId($oCommentNew->getTargetId());
                $oCommentOnline->setTargetType($oCommentNew->getTargetType());
                $oCommentOnline->setTargetParentId($oCommentNew->getTargetParentId());
                $oCommentOnline->setCommentId($oCommentNew->getId());

                E::ModuleComment()->AddCommentOnline($oCommentOnline);
            }

            // * Список емайлов на которые не нужно отправлять уведомление
            $aExcludeMail = array($this->oUserCurrent->getMail());

            // * Отправляем уведомление тому на чей коммент ответили
            if ($oCommentParent && $oCommentParent->getUserId() != $oTopic->getUserId()
                && $oCommentNew->getUserId() != $oCommentParent->getUserId()
            ) {
                $oUserAuthorComment = $oCommentParent->getUser();
                $aExcludeMail[] = $oUserAuthorComment->getMail();
                E::ModuleNotify()->SendCommentReplyToAuthorParentComment(
                    $oUserAuthorComment, $oTopic, $oCommentNew, $this->oUserCurrent
                );
            }

            // issue 131 (https://github.com/altocms/altocms/issues/131)
            // Не работает настройка уведомлений о комментариях к своим топикам

            // Уберём автора топика из рассылки
            /** @var ModuleTopic_EntityTopic $oTopic */
            $aExcludeMail[] = $oTopic->getUser()->getMail();
            // Отправим ему сообщение через отдельный метод, который проверяет эту настройку
            /** @var ModuleComment_EntityComment $oCommentNew */
            E::ModuleNotify()->SendCommentNewToAuthorTopic($oTopic->getUser(), $oTopic, $oCommentNew, $this->oUserCurrent);

            // * Отправка уведомления всем, кто подписан на топик кроме автора
            E::ModuleSubscribe()->Send(
                'topic_new_comment', $oTopic->getId(), 'comment_new.tpl',
                E::ModuleLang()->Get('notify_subject_comment_new'),
                array('oTopic' => $oTopic, 'oComment' => $oCommentNew, 'oUserComment' => $this->oUserCurrent,),
                $aExcludeMail
            );

            // * Подписываем автора коммента на обновления в трекере
            $oTrack = E::ModuleSubscribe()->AddTrackSimple(
                'topic_new_comment', $oTopic->getId(), $this->oUserCurrent->getId()
            );
            if ($oTrack) {
                //если пользователь не отписался от обновлений топика
                if (!$oTrack->getStatus()) {
                    $oTrack->setStatus(1);
                    E::ModuleSubscribe()->UpdateTrack($oTrack);
                }
            }

            // * Добавляем событие в ленту
            E::ModuleStream()->Write(
                $oCommentNew->getUserId(), 'add_comment', $oCommentNew->getId(),
                $oTopic->getPublish() && !$oTopic->getBlog()->IsPrivate()
            );
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
        }
    }

    /**
     * Получение новых комментариев
     *
     */
    protected function AjaxResponseComment() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');

        // * Пользователь авторизован?
        if (!$this->oUserCurrent) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Топик существует?
        $iTopicId = intval(F::GetRequestStr('idTarget', null, 'post'));
        if (!$iTopicId || !($oTopic = E::ModuleTopic()->GetTopicById($iTopicId))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Есть доступ к комментариям этого топика? Закрытый блог?
        if (!E::ModuleACL()->IsAllowShowBlog($oTopic->getBlog(), $this->oUserCurrent)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        $idCommentLast = F::GetRequestStr('idCommentLast', null, 'post');
        $selfIdComment = F::GetRequestStr('selfIdComment', null, 'post');
        $aComments = array();

        // * Если используется постраничность, возвращаем только добавленный комментарий
        if (F::GetRequest('bUsePaging', null, 'post') && $selfIdComment) {
            $oComment = E::ModuleComment()->GetCommentById($selfIdComment);
            if ($oComment && ($oComment->getTargetId() == $oTopic->getId()) && ($oComment->getTargetType() == 'topic')) {

                $aVars = array(
                    'oUserCurrent' => $this->oUserCurrent,
                    'bOneComment' => true,
                    'oComment' => $oComment,
                );
                $sText = E::ModuleViewer()->Fetch(E::ModuleComment()->GetTemplateCommentByTarget($oTopic->getId(), 'topic'));
                $aCmt = array();
                $aCmt[] = array(
                    'html' => $sText,
                    'obj'  => $oComment,
                );
            } else {
                $aCmt = array();
            }
            $aReturn['comments'] = $aCmt;
            $aReturn['iMaxIdComment'] = $selfIdComment;
        } else {
            $aReturn = E::ModuleComment()->GetCommentsNewByTargetId($oTopic->getId(), 'topic', $idCommentLast);
        }
        $iMaxIdComment = $aReturn['iMaxIdComment'];

        /** @var ModuleTopic_EntityTopicRead $oTopicRead */
        $oTopicRead = E::GetEntity('Topic_TopicRead');
        $oTopicRead->setTopicId($oTopic->getId());
        $oTopicRead->setUserId($this->oUserCurrent->getId());
        $oTopicRead->setCommentCountLast($oTopic->getCountComment());
        $oTopicRead->setCommentIdLast($iMaxIdComment);
        $oTopicRead->setDateRead(F::Now());
        E::ModuleTopic()->SetTopicRead($oTopicRead);

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

        E::ModuleViewer()->AssignAjax('iMaxIdComment', $iMaxIdComment);
        E::ModuleViewer()->AssignAjax('aComments', $aComments);
    }

    /**
     * Returns text of comment
     *
     */
    protected function AjaxGetComment() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');

        // * Пользователь авторизован?
        if (!$this->oUserCurrent) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Топик существует?
        $nTopicId = intval($this->GetPost('targetId'));
        if (!$nTopicId || !($oTopic = E::ModuleTopic()->GetTopicById($nTopicId))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        $nCommentId = intval($this->GetPost('commentId'));
        if (!$nCommentId || !($oComment = E::ModuleComment()->GetCommentById($nCommentId))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
        if (!$this->GetPost('submit')) {
            $sText = $oComment->getText();
            // restore <code></code>
            // see ModuleText::CodeSourceParser()
            $sText = str_replace('<pre class="prettyprint"><code>', '<code>', $sText);
            $sText = str_replace('</code></pre>', '</code>', $sText);

            E::ModuleViewer()->AssignAjax('sText', $sText);
            E::ModuleViewer()->AssignAjax('sDateEdit', $oComment->getCommentDateEdit());
        }
    }

    /**
     * Updates comment
     *
     */
    protected function AjaxUpdateComment() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');

        // * Пользователь авторизован?
        if (!$this->oUserCurrent) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }

        if (!E::ModuleSecurity()->ValidateSendForm(false) || $this->GetPost('comment_mode') != 'edit') {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Проверяем текст комментария
        $sNewText = E::ModuleText()->Parser($this->GetPost('comment_text'));
        $iMin = Config::Val('module.comment.min_length', 2);
        $iMax = Config::Val('module.comment.max_length', 0);
        if (!F::CheckVal($sNewText, 'text', $iMin, $iMax)) {
            if ($iMax) {
                E::ModuleMessage()->AddErrorSingle(
                    E::ModuleLang()->Get('topic_comment_text_len', array('min' => $iMin, 'max' => $iMax)), E::ModuleLang()->Get('error')
                );
            } else {
                E::ModuleMessage()->AddErrorSingle(
                    E::ModuleLang()->Get('topic_comment_text_min', array('min' => $iMin)), E::ModuleLang()->Get('error')
                );
            }
            return;
        }

        // * Получаем комментарий
        $iCommentId = intval($this->GetPost('comment_id'));

        /** var ModuleComment_EntityComment $oComment */
        if (!$iCommentId || !($oComment = E::ModuleComment()->GetCommentById($iCommentId))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        if (!$oComment->isEditable()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('comment_cannot_edit'), E::ModuleLang()->Get('error'));
            return;
        }

        if (!$oComment->getEditTime() && !$oComment->isEditable(false)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('comment_edit_timeout'), E::ModuleLang()->Get('error'));
            return;
        }

        // Если все нормально, то обновляем текст
        $oComment->setText($sNewText);
        if (E::ModuleComment()->UpdateComment($oComment)) {
            $oComment = E::ModuleComment()->GetCommentById($iCommentId);
            E::ModuleViewer()->AssignAjax('nCommentId', $oComment->getId());
            E::ModuleViewer()->AssignAjax('sText', $oComment->getText());
            E::ModuleViewer()->AssignAjax('sDateEdit', $oComment->getCommentDateEdit());
            E::ModuleViewer()->AssignAjax('sDateEditText', E::ModuleLang()->Get('date_now'));
            E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('comment_updated'));
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
        }
    }

    /**
     * Обработка ajax запроса на отправку
     * пользователям приглашения вступить в приватный блог
     */
    protected function AjaxAddBlogInvite() {

        // * Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');
        $sUsers = F::GetRequest('users', null, 'post');
        $iBlogId = intval(F::GetRequestStr('idBlog', null, 'post'));

        // * Если пользователь не авторизирован, возвращаем ошибку
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();

        // * Проверяем существование блога
        if (!$iBlogId || !($oBlog = E::ModuleBlog()->GetBlogById($iBlogId)) || !is_string($sUsers)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
        $this->oCurrentBlog = $oBlog;

            // * Проверяем, имеет ли право текущий пользователь добавлять invite в blog
        $oBlogUser = E::ModuleBlog()->GetBlogUserByBlogIdAndUserId($oBlog->getId(), $this->oUserCurrent->getId());
        $bBlogAdministrator = ($oBlogUser ? $oBlogUser->IsBlogAdministrator() : false);
        if ($oBlog->getOwnerId() != $this->oUserCurrent->getId() && !$this->oUserCurrent->isAdministrator() && !$this->oUserCurrent->isModerator() && !$bBlogAdministrator) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        // * Получаем список пользователей блога (любого статуса)
        $aBlogUsersResult = E::ModuleBlog()->GetBlogUsersByBlogId($oBlog->getId(), true, null);
        /** @var ModuleBlog_EntityBlogUser[] $aBlogUsers */
        $aBlogUsers = $aBlogUsersResult['collection'];
        $aUsers = explode(',', $sUsers);

        $aResult = array();

        // * Обрабатываем добавление по каждому из переданных логинов
        foreach ($aUsers as $sUser) {
            $sUser = trim($sUser);
            if ($sUser == '') {
                continue;
            }
            // * Если пользователь пытается добавить инвайт самому себе, возвращаем ошибку
            if (strtolower($sUser) == strtolower($this->oUserCurrent->getLogin())) {
                $aResult[] = array(
                    'bStateError' => true,
                    'sMsgTitle'   => E::ModuleLang()->Get('error'),
                    'sMsg'        => E::ModuleLang()->Get('blog_user_invite_add_self')
                );
                continue;
            }

            // * Если пользователь не найден или неактивен, возвращаем ошибку
            $oUser = E::ModuleUser()->GetUserByLogin($sUser);
            if (!$oUser || $oUser->getActivate() != 1) {
                $aResult[] = array(
                    'bStateError' => true,
                    'sMsgTitle'   => E::ModuleLang()->Get('error'),
                    'sMsg'        => E::ModuleLang()->Get('user_not_found', array('login' => htmlspecialchars($sUser))),
                    'sUserLogin'  => htmlspecialchars($sUser)
                );
                continue;
            }

            if (!isset($aBlogUsers[$oUser->getId()])) {
                // * Создаем нового блог-пользователя со статусом INVITED
                /** @var ModuleBlog_EntityBlogUser $oBlogUserNew */
                $oBlogUserNew = E::GetEntity('Blog_BlogUser');
                $oBlogUserNew->setBlogId($oBlog->getId());
                $oBlogUserNew->setUserId($oUser->getId());
                $oBlogUserNew->setUserRole(ModuleBlog::BLOG_USER_ROLE_INVITE);

                if (E::ModuleBlog()->AddRelationBlogUser($oBlogUserNew)) {
                    $aResult[] = array(
                        'bStateError'   => false,
                        'sMsgTitle'     => E::ModuleLang()->Get('attention'),
                        'sMsg'          => E::ModuleLang()->Get(
                            'blog_user_invite_add_ok',
                            array('login' => htmlspecialchars($sUser))
                        ),
                        'sUserLogin'    => htmlspecialchars($sUser),
                        'sUserWebPath'  => $oUser->getProfileUrl(),
                        'sUserAvatar48' => $oUser->getAvatarUrl(48),
                    );
                    $this->SendBlogInvite($oBlog, $oUser);
                } else {
                    $aResult[] = array(
                        'bStateError' => true,
                        'sMsgTitle'   => E::ModuleLang()->Get('error'),
                        'sMsg'        => E::ModuleLang()->Get('system_error'),
                        'sUserLogin'  => htmlspecialchars($sUser)
                    );
                }
            } elseif ($aBlogUsers[$oUser->getId()]->getUserRole() == ModuleBlog::BLOG_USER_ROLE_NOTMEMBER || $aBlogUsers[$oUser->getId()]->getUserRole() == ModuleBlog::BLOG_USER_ROLE_WISHES) {
                // * Change status of user to INVITED
                /** @var ModuleBlog_EntityBlogUser $oBlogUser */
                $oBlogUser = $aBlogUsers[$oUser->getId()];
                $oBlogUser->setUserRole(ModuleBlog::BLOG_USER_ROLE_INVITE);

                if (E::ModuleBlog()->UpdateRelationBlogUser($oBlogUser)) {
                    $aResult[] = array(
                        'bStateError'   => false,
                        'sMsgTitle'     => E::ModuleLang()->Get('attention'),
                        'sMsg'          => E::ModuleLang()->Get(
                            'blog_user_invite_add_ok',
                            array('login' => htmlspecialchars($sUser))
                        ),
                        'sUserLogin'    => htmlspecialchars($sUser),
                        'sUserWebPath'  => $oUser->getProfileUrl(),
                        'sUserAvatar48' => $oUser->getAvatarUrl(48),
                    );
                    $this->SendBlogInvite($oBlog, $oUser);
                } else {
                    $aResult[] = array(
                        'bStateError' => true,
                        'sMsgTitle'   => E::ModuleLang()->Get('error'),
                        'sMsg'        => E::ModuleLang()->Get('system_error'),
                        'sUserLogin'  => htmlspecialchars($sUser)
                    );
                }
            } else {
                // Попытка добавить приглашение уже существующему пользователю,
                // возвращаем ошибку (сначала определяя ее точный текст)
                switch (true) {
                    case ($aBlogUsers[$oUser->getId()]->getUserRole() == ModuleBlog::BLOG_USER_ROLE_INVITE):
                        $sErrorMessage = E::ModuleLang()->Get(
                            'blog_user_already_invited', array('login' => htmlspecialchars($sUser))
                        );
                        break;
                    case ($aBlogUsers[$oUser->getId()]->getUserRole() > ModuleBlog::BLOG_USER_ROLE_GUEST):
                        $sErrorMessage = E::ModuleLang()->Get(
                            'blog_user_already_exists', array('login' => htmlspecialchars($sUser))
                        );
                        break;
                    case ($aBlogUsers[$oUser->getId()]->getUserRole() == ModuleBlog::BLOG_USER_ROLE_REJECT):
                        $sErrorMessage = E::ModuleLang()->Get(
                            'blog_user_already_reject', array('login' => htmlspecialchars($sUser))
                        );
                        break;
                    default:
                        $sErrorMessage = E::ModuleLang()->Get('system_error');
                }
                $aResult[] = array(
                    'bStateError' => true,
                    'sMsgTitle'   => E::ModuleLang()->Get('error'),
                    'sMsg'        => $sErrorMessage,
                    'sUserLogin'  => htmlspecialchars($sUser)
                );
                continue;
            }
        }

        // * Передаем во вьевер массив с результатами обработки по каждому пользователю
        E::ModuleViewer()->AssignAjax('aUsers', $aResult);
    }

    /**
     * Обработка ajax запроса на отправку
     * повторного приглашения вступить в приватный блог
     */
    protected function AjaxReBlogInvite() {

        //  Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');
        $sUserId = F::GetRequestStr('idUser', null, 'post');
        $sBlogId = F::GetRequestStr('idBlog', null, 'post');

        //  Если пользователь не авторизирован, возвращаем ошибку
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();

        //  Проверяем существование блога
        if (!$oBlog = E::ModuleBlog()->GetBlogById($sBlogId)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
        $this->oCurrentBlog = $oBlog;

        //  Пользователь существует и активен?
        $oUser = E::ModuleUser()->GetUserById($sUserId);
        if (!$oUser || $oUser->getActivate() != 1) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
        //  Проверяем, имеет ли право текущий пользователь добавлять invite в blog
        $oBlogUser = E::ModuleBlog()->GetBlogUserByBlogIdAndUserId($oBlog->getId(), $this->oUserCurrent->getId());
        $bBlogAdministrator = ($oBlogUser ? $oBlogUser->IsBlogAdministrator() : false);
        if ($oBlog->getOwnerId() != $this->oUserCurrent->getId() && !$this->oUserCurrent->isAdministrator() && !$this->oUserCurrent->isModerator() && !$bBlogAdministrator) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        $oBlogUser = E::ModuleBlog()->GetBlogUserByBlogIdAndUserId($oBlog->getId(), $oUser->getId());
        if ($oBlogUser->getUserRole() == ModuleBlog::BLOG_USER_ROLE_INVITE) {
            $this->SendBlogInvite($oBlog, $oUser);
            E::ModuleMessage()->AddNoticeSingle(
                E::ModuleLang()->Get('blog_user_invite_add_ok', array('login' => $oUser->getLogin())),
                E::ModuleLang()->Get('attention')
            );
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
        }
    }

    /**
     * Обработка ajax-запроса на удаление приглашения подписаться на приватный блог
     *
     */
    protected function AjaxRemoveBlogInvite() {

        //  Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');
        $sUserId = F::GetRequestStr('idUser', null, 'post');
        $sBlogId = F::GetRequestStr('idBlog', null, 'post');

        //  Если пользователь не авторизирован, возвращаем ошибку
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();
        //  Проверяем существование блога
        if (!$oBlog = E::ModuleBlog()->GetBlogById($sBlogId)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
        $this->oCurrentBlog = $oBlog;

        //  Пользователь существует и активен?
        $oUser = E::ModuleUser()->GetUserById($sUserId);
        if (!$oUser || $oUser->getActivate() != 1) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
        //  Проверяем, имеет ли право текущий пользователь добавлять invite в blog
        $oBlogUser = E::ModuleBlog()->GetBlogUserByBlogIdAndUserId($oBlog->getId(), $this->oUserCurrent->getId());
        $bBlogAdministrator = ($oBlogUser ? $oBlogUser->IsBlogAdministrator() : false);
        if ($oBlog->getOwnerId() != $this->oUserCurrent->getId() && !$this->oUserCurrent->isAdministrator() && !$this->oUserCurrent->isModerator() && !$bBlogAdministrator) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        $oBlogUser = E::ModuleBlog()->GetBlogUserByBlogIdAndUserId($oBlog->getId(), $oUser->getId());
        if ($oBlogUser->getUserRole() == ModuleBlog::BLOG_USER_ROLE_INVITE) {
            //  Удаляем связь/приглашение
            E::ModuleBlog()->DeleteRelationBlogUser($oBlogUser);
            E::ModuleMessage()->AddNoticeSingle(
                E::ModuleLang()->Get('blog_user_invite_remove_ok', array('login' => $oUser->getLogin())),
                E::ModuleLang()->Get('attention')
            );
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
        }
    }

    /**
     * Выполняет отправку письма модераторам и администратору блога о том, что
     * конкретный пользователь запросил приглашение в блог
     * (по внутренней почте и на email)
     *
     * @param ModuleBlog_EntityBlog   $oBlog           Блог, в который хочет вступить пользователь
     * @param ModuleUser_EntityUser[] $aBlogModerators Модератор/админ, которому отправляем письмо
     * @param ModuleUser_EntityUser   $oGuestUser      Пользователь, который хочет вступить в блог
     */
    protected function SendBlogRequest($oBlog, $aBlogModerators, $oGuestUser) {

        $sTitle = E::ModuleLang()->Get('blog_user_request_title', array('blog_title' => $oBlog->getTitle()));

        F::IncludeLib('XXTEA/encrypt.php');

        //  Формируем код подтверждения в URL
        $sCode = $oBlog->getId() . '_' . $oGuestUser->getId();
        $sCode = rawurlencode(base64_encode(xxtea_encrypt($sCode, Config::Get('module.blog.encrypt'))));

        $aPath = array(
            'accept' => R::GetPath('blog') . 'request/accept/?code=' . $sCode,
            'reject' => R::GetPath('blog') . 'request/reject/?code=' . $sCode
        );

        $sText = E::ModuleLang()->Get(
            'blog_user_request_text',
            array(
                'login'        => $oGuestUser->getLogin(),
                'user_profile' => $oGuestUser->getProfileUrl(),
                'accept_path'  => $aPath['accept'],
                'reject_path'  => $aPath['reject'],
                'blog_title'   => $oBlog->getTitle()
            )
        );
        $oTalk = E::ModuleTalk()->SendTalk($sTitle, $sText, $oGuestUser, $aBlogModerators, FALSE, FALSE);

        foreach ($aBlogModerators as $oUserTo) {
            E::ModuleNotify()->SendBlogUserRequest(
                $oUserTo,
                $this->oUserCurrent,
                $oBlog,
                R::GetPath('talk') . 'read/' . $oTalk->getId() . '/'
            );
        }
        //  Удаляем отправляющего юзера из переписки
        E::ModuleTalk()->DeleteTalkUserByArray($oTalk->getId(), $oGuestUser->getId());
    }

    /**
     * Выполняет отправку приглашения в блог
     * (по внутренней почте и на email)
     *
     * @param ModuleBlog_EntityBlog $oBlog
     * @param ModuleUser_EntityUser $oUser
     */
    protected function SendBlogInvite($oBlog, $oUser) {

        $sTitle = E::ModuleLang()->Get('blog_user_invite_title', array('blog_title' => $oBlog->getTitle()));

        F::IncludeLib('XXTEA/encrypt.php');

        //  Формируем код подтверждения в URL
        $sCode = $oBlog->getId() . '_' . $oUser->getId();
        $sCode = rawurlencode(base64_encode(xxtea_encrypt($sCode, Config::Get('module.blog.encrypt'))));

        $aPath = array(
            'accept' => R::GetPath('blog') . 'invite/accept/?code=' . $sCode,
            'reject' => R::GetPath('blog') . 'invite/reject/?code=' . $sCode
        );

        // Сформируем название типа блога на языке приложения.
        // Это может быть либо название, либо текстовка.
        $sBlogType = mb_strtolower(
            preg_match('~^\{\{(.*)\}\}$~', $sBlogType = $oBlog->getBlogType()->getTypeName(), $aMatches)
                ? E::ModuleLang()->Get($aMatches[1])
                : $sBlogType, 'UTF-8'
        );


        $sText = E::ModuleLang()->Get(
            'blog_user_invite_text',
            array(
                 'login'       => $this->oUserCurrent->getLogin(),
                 'accept_path' => $aPath['accept'],
                 'reject_path' => $aPath['reject'],
                 'blog_title'  => $oBlog->getTitle(),
                 'blog_type'   => $sBlogType,
            )
        );
        $oTalk = E::ModuleTalk()->SendTalk($sTitle, $sText, $this->oUserCurrent, array($oUser), false, false);

        //  Отправляем пользователю заявку
        E::ModuleNotify()->SendBlogUserInvite(
            $oUser, $this->oUserCurrent, $oBlog,
            R::GetPath('talk') . 'read/' . $oTalk->getId() . '/'
        );
        //  Удаляем отправляющего юзера из переписки
        E::ModuleTalk()->DeleteTalkUserByArray($oTalk->getId(), $this->oUserCurrent->getId());
    }

    /**
     * Обработка отправленого пользователю приглашения подписаться на блог
     *
     * @return string|null
     */
    protected function EventInviteBlog() {

        F::IncludeLib('XXTEA/encrypt.php');

        // * Получаем код подтверждения из ревеста и дешефруем его
        $sCode = xxtea_decrypt(base64_decode(rawurldecode(F::GetRequestStr('code'))), Config::Get('module.blog.encrypt'));
        if (!$sCode) {
            return $this->EventNotFound();
        }
        list($sBlogId, $sUserId) = explode('_', $sCode, 2);

        $sAction = $this->GetParam(0);

        // * Получаем текущего пользователя
        if (!E::ModuleUser()->IsAuthorization()) {
            return $this->EventNotFound();
        }
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();

        // * Если приглашенный пользователь не является авторизированным
        if ($this->oUserCurrent->getId() != $sUserId) {
            return $this->EventNotFound();
        }

        // * Получаем указанный блог
        $oBlog = E::ModuleBlog()->GetBlogById($sBlogId);
        if (!$oBlog || !$oBlog->getBlogType() || !($oBlog->getBlogType()->IsPrivate()||$oBlog->getBlogType()->IsReadOnly())) {
            return $this->EventNotFound();
        }
        $this->oCurrentBlog = $oBlog;

        // * Получаем связь "блог-пользователь" и проверяем, чтобы ее тип был INVITE или REJECT
        if (!$oBlogUser = E::ModuleBlog()->GetBlogUserByBlogIdAndUserId($oBlog->getId(), $this->oUserCurrent->getId())) {
            return $this->EventNotFound();
        }
        if ($oBlogUser->getUserRole() > ModuleBlog::BLOG_USER_ROLE_GUEST) {
            $sMessage = E::ModuleLang()->Get('blog_user_invite_already_done');
            E::ModuleMessage()->AddError($sMessage, E::ModuleLang()->Get('error'), true);
            R::Location(R::GetPath('talk'));
            return;
        }
        if (!in_array($oBlogUser->getUserRole(), array(ModuleBlog::BLOG_USER_ROLE_INVITE, ModuleBlog::BLOG_USER_ROLE_REJECT))) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'), true);
            R::Location(R::GetPath('talk'));
            return;
        }

        // * Обновляем роль пользователя до читателя
        $oBlogUser->setUserRole(($sAction == 'accept') ? ModuleBlog::BLOG_USER_ROLE_USER : ModuleBlog::BLOG_USER_ROLE_REJECT);
        if (!E::ModuleBlog()->UpdateRelationBlogUser($oBlogUser)) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'), true);
            R::Location(R::GetPath('talk'));
            return;
        }
        if ($sAction == 'accept') {

            // * Увеличиваем число читателей блога
            $oBlog->setCountUser($oBlog->getCountUser() + 1);
            E::ModuleBlog()->UpdateBlog($oBlog);
            $sMessage = E::ModuleLang()->Get('blog_user_invite_accept');

            // * Добавляем событие в ленту
            E::ModuleStream()->Write($oBlogUser->getUserId(), 'join_blog', $oBlog->getId());
        } else {
            $sMessage = E::ModuleLang()->Get('blog_user_invite_reject');
        }
        E::ModuleMessage()->AddNotice($sMessage, E::ModuleLang()->Get('attention'), true);

        // * Перенаправляем на страницу личной почты
        R::Location(R::GetPath('talk'));
    }

    /**
     * Обработка отправленого админу запроса на вступление в блог
     *
     * @return string|null
     */
    protected function EventRequestBlog() {

        F::IncludeLib('XXTEA/encrypt.php');

        // * Получаем код подтверждения из ревеста и дешефруем его
        $sCode = xxtea_decrypt(base64_decode(rawurldecode(F::GetRequestStr('code'))), Config::Get('module.blog.encrypt'));
        if (!$sCode) {
            return $this->EventNotFound();
        }
        list($sBlogId, $sUserId) = explode('_', $sCode, 2);

        $sAction = $this->GetParam(0);

        // * Получаем текущего пользователя
        if (!E::ModuleUser()->IsAuthorization()) {
            return $this->EventNotFound();
        }
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();

        // Получаем блог
        /** @var ModuleBlog_EntityBlog $oBlog */
        $oBlog = E::ModuleBlog()->GetBlogById($sBlogId);
        if (!$oBlog || !$oBlog->getBlogType() || !($oBlog->getBlogType()->IsPrivate()||$oBlog->getBlogType()->IsReadOnly())) {
            return $this->EventNotFound();
        }
        $this->oCurrentBlog = $oBlog;

        // Проверим, что текущий пользователь имеет право принимать решение
        if (!($oBlog->getUserIsAdministrator() || $oBlog->getUserIsModerator() || $oBlog->getOwnerId() == E::UserId())) {
            return $this->EventNotFound();
        }

        // Получим пользователя, который запрашивает приглашение
        if (!($oGuestUser = E::ModuleUser()->GetUserById($sUserId))) {
            return $this->EventNotFound();
        }

        // * Получаем связь "блог-пользователь" и проверяем, чтобы ее тип был REQUEST
        if (!$oBlogUser = E::ModuleBlog()->GetBlogUserByBlogIdAndUserId($oBlog->getId(), $oGuestUser->getId())) {
            return $this->EventNotFound();
        }

        // Пользователь уже принят в ряды
        if ($oBlogUser->getUserRole() >= ModuleBlog::BLOG_USER_ROLE_USER) {
            $sMessage = E::ModuleLang()->Get('blog_user_request_already_done');
            E::ModuleMessage()->AddError($sMessage, E::ModuleLang()->Get('error'), true);
            R::Location(R::GetPath('talk'));
            return;
        }

        // У пользователя непонятный флаг
        if ($oBlogUser->getUserRole() != ModuleBlog::BLOG_USER_ROLE_WISHES) {
            return $this->EventNotFound();
        }

        // * Обновляем роль пользователя до читателя
        $oBlogUser->setUserRole(($sAction == 'accept') ? ModuleBlog::BLOG_USER_ROLE_USER : ModuleBlog::BLOG_USER_ROLE_NOTMEMBER);
        if (!E::ModuleBlog()->UpdateRelationBlogUser($oBlogUser)) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'), true);
            R::Location(R::GetPath('talk'));
            return;
        }
        if ($sAction == 'accept') {

            // * Увеличиваем число читателей блога
            $oBlog->setCountUser($oBlog->getCountUser() + 1);
            E::ModuleBlog()->UpdateBlog($oBlog);
            $sMessage = E::ModuleLang()->Get('blog_user_request_accept');

            // * Добавляем событие в ленту
            E::ModuleStream()->Write($oBlogUser->getUserId(), 'join_blog', $oBlog->getId());
        } else {
            $sMessage = E::ModuleLang()->Get('blog_user_request_no_accept');
        }
        E::ModuleMessage()->AddNotice($sMessage, E::ModuleLang()->Get('attention'), true);

        // * Перенаправляем на страницу личной почты
        R::Location(R::GetPath('talk'));
    }

    /**
     * Удаление блога
     *
     */
    protected function EventDeleteBlog() {

        E::ModuleSecurity()->ValidateSendForm();

        // * Проверяем передан ли в УРЛе номер блога
        $nBlogId = intval($this->GetParam(0));
        if (!$nBlogId || (!$oBlog = E::ModuleBlog()->GetBlogById($nBlogId))) {
            return parent::EventNotFound();
        }
        $this->oCurrentBlog = $oBlog;

        // * Проверям авторизован ли пользователь
        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('not_access'), E::ModuleLang()->Get('error'));
            return R::Action('error');
        }

        // * проверяем есть ли право на удаление блога
        if (!$nAccess = E::ModuleACL()->IsAllowDeleteBlog($oBlog, $this->oUserCurrent)) {
            return parent::EventNotFound();
        }
        $aTopics = E::ModuleTopic()->GetTopicsByBlogId($nBlogId);

        switch ($nAccess) {
            case ModuleACL::CAN_DELETE_BLOG_EMPTY_ONLY :
                if (is_array($aTopics) && count($aTopics)) {
                    E::ModuleMessage()->AddErrorSingle(
                        E::ModuleLang()->Get('blog_admin_delete_not_empty'), E::ModuleLang()->Get('error'), true
                    );
                    R::Location($oBlog->getUrlFull());
                }
                break;
            case ModuleACL::CAN_DELETE_BLOG_WITH_TOPICS :
                /*
                 * Если указан идентификатор блога для перемещения,
                 * то делаем попытку переместить топики.
                 *
                 * (-1) - выбран пункт меню "удалить топики".
                 */
                $nNewBlogId = intval(F::GetRequestStr('topic_move_to'));
                if (($nNewBlogId > 0) && is_array($aTopics) && count($aTopics)) {
                    if (!$oBlogNew = E::ModuleBlog()->GetBlogById($nNewBlogId)) {
                        E::ModuleMessage()->AddErrorSingle(
                            E::ModuleLang()->Get('blog_admin_delete_move_error'), E::ModuleLang()->Get('error'), true
                        );
                        R::Location($oBlog->getUrlFull());
                    }
                    // * Если выбранный блог является персональным, возвращаем ошибку
                    if ($oBlogNew->getType() == 'personal') {
                        E::ModuleMessage()->AddErrorSingle(
                            E::ModuleLang()->Get('blog_admin_delete_move_personal'), E::ModuleLang()->Get('error'), true
                        );
                        R::Location($oBlog->getUrlFull());
                    }
                    // * Перемещаем топики
                    E::ModuleTopic()->MoveTopics($nBlogId, $nNewBlogId);
                }
                break;
            default:
                return parent::EventNotFound();
        }

        // * Удаляяем блог и перенаправляем пользователя к списку блогов
        E::ModuleHook()->Run('blog_delete_before', array('sBlogId' => $nBlogId));

        if ($this->_deleteBlog($oBlog)) {
            E::ModuleHook()->Run('blog_delete_after', array('sBlogId' => $nBlogId));
            E::ModuleMessage()->AddNoticeSingle(
                E::ModuleLang()->Get('blog_admin_delete_success'), E::ModuleLang()->Get('attention'), true
            );
            R::Location(R::GetPath('blogs'));
        } else {
            R::Location($oBlog->getUrlFull());
        }
    }

    /**
     * Удаление блога
     *
     * @param $oBlog
     *
     * @return bool
     */
    protected function _deleteBlog($oBlog) {

        return E::ModuleBlog()->DeleteBlog($oBlog);
    }

    /**
     * Получение описания блога
     *
     */
    protected function AjaxBlogInfo() {

        //  Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');
        $iBlogId = intval(F::GetRequestStr('idBlog', null, 'post'));
        //  Определяем тип блога и получаем его
        if (($iBlogId == 0) && $this->oUserCurrent) {
            $oBlog = E::ModuleBlog()->GetPersonalBlogByUserId($this->oUserCurrent->getId());
        } elseif ($iBlogId) {
            $this->oCurrentBlog = $oBlog = E::ModuleBlog()->GetBlogById($iBlogId);
        } else {
            $oBlog = null;
        }

        //  если блог найден, то возвращаем описание
        if ($oBlog) {
            $sText = $oBlog->getDescription();
            E::ModuleViewer()->AssignAjax('sText', $sText);
        }
    }

    /**
     * Подключение/отключение к блогу
     *
     */
    protected function AjaxBlogJoin() {

        //  Устанавливаем формат Ajax ответа
        E::ModuleViewer()->SetResponseAjax('json');
        //  Пользователь авторизован?
        if (!$this->oUserCurrent) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        //  Блог существует?
        $nBlogId = intval(F::GetRequestStr('idBlog', null, 'post'));
        if (!$nBlogId || !($oBlog = E::ModuleBlog()->GetBlogById($nBlogId))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
        $this->oCurrentBlog = $oBlog;

        // Type of the blog
        $oBlogType = $oBlog->getBlogType();

        // Current status of user in the blog
        /** @var ModuleBlog_EntityBlogUser $oBlogUser */
        $oBlogUser = E::ModuleBlog()->GetBlogUserByBlogIdAndUserId($oBlog->getId(), $this->oUserCurrent->getId());

        if (!$oBlogUser || ($oBlogUser->getUserRole() < ModuleBlog::BLOG_USER_ROLE_GUEST && (!$oBlogType || $oBlogType->IsPrivate()))) {
            // * Проверяем тип блога на возможность свободного вступления или вступления по запросу
            if ($oBlogType && !$oBlogType->GetMembership(ModuleBlog::BLOG_USER_JOIN_FREE) && !$oBlogType->GetMembership(ModuleBlog::BLOG_USER_JOIN_REQUEST)) {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('blog_join_error_invite'), E::ModuleLang()->Get('error'));
                return;
            }
            if ($oBlog->getOwnerId() != $this->oUserCurrent->getId()) {
                // Subscribe user to the blog
                if ($oBlogType->GetMembership(ModuleBlog::BLOG_USER_JOIN_FREE)) {
                    $bResult = false;
                    if ($oBlogUser) {
                        $oBlogUser->setUserRole(ModuleBlog::BLOG_USER_ROLE_USER);
                        $bResult = E::ModuleBlog()->UpdateRelationBlogUser($oBlogUser);
                    } elseif ($oBlogType->GetMembership(ModuleBlog::BLOG_USER_JOIN_FREE)) {
                        // User can free subscribe to blog
                        /** @var ModuleBlog_EntityBlogUser $oBlogUserNew */
                        $oBlogUserNew = E::GetEntity('Blog_BlogUser');
                        $oBlogUserNew->setBlogId($oBlog->getId());
                        $oBlogUserNew->setUserId($this->oUserCurrent->getId());
                        $oBlogUserNew->setUserRole(ModuleBlog::BLOG_USER_ROLE_USER);
                        $bResult = E::ModuleBlog()->AddRelationBlogUser($oBlogUserNew);
                    }
                    if ($bResult) {
                        E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('blog_join_ok'), E::ModuleLang()->Get('attention'));
                        E::ModuleViewer()->AssignAjax('bState', true);
                        //  Увеличиваем число читателей блога
                        $oBlog->setCountUser($oBlog->getCountUser() + 1);
                        E::ModuleBlog()->UpdateBlog($oBlog);
                        E::ModuleViewer()->AssignAjax('iCountUser', $oBlog->getCountUser());
                        //  Добавляем событие в ленту
                        E::ModuleStream()->Write($this->oUserCurrent->getId(), 'join_blog', $oBlog->getId());
                        //  Добавляем подписку на этот блог в ленту пользователя
                        E::ModuleUserfeed()->SubscribeUser(
                            $this->oUserCurrent->getId(), ModuleUserfeed::SUBSCRIBE_TYPE_BLOG, $oBlog->getId()
                        );
                    } else {
                        $sMsg = ($oBlogType->IsPrivate())
                            ? E::ModuleLang()->Get('blog_join_error_invite')
                            : E::ModuleLang()->Get('system_error');
                        E::ModuleMessage()->AddErrorSingle($sMsg, E::ModuleLang()->Get('error'));
                        return;
                    }
                }

                // Подписываем по запросу
                if ($oBlogType->GetMembership(ModuleBlog::BLOG_USER_JOIN_REQUEST)) {

                    // Подписка уже была запрошена, но результатов пока нет
                    if ($oBlogUser && $oBlogUser->getUserRole() == ModuleBlog::BLOG_USER_ROLE_WISHES) {
                        E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('blog_join_request_already'), E::ModuleLang()->Get('attention'));
                        E::ModuleViewer()->AssignAjax('bState', true);
                        return;
                    }

                    if ($oBlogUser) {
                        if (!in_array($oBlogUser->getUserRole(), array(ModuleBlog::BLOG_USER_ROLE_REJECT, ModuleBlog::BLOG_USER_ROLE_WISHES))) {
                            $sMessage = E::ModuleLang()->Get('blog_user_status_is') . ' "' . E::ModuleBlog()->GetBlogUserRoleName($oBlogUser->getUserRole()) . '"';
                            E::ModuleMessage()->AddNoticeSingle($sMessage, E::ModuleLang()->Get('attention'));
                            E::ModuleViewer()->AssignAjax('bState', true);
                            return;
                        } else {
                            $oBlogUser->setUserRole(ModuleBlog::BLOG_USER_ROLE_WISHES);
                            $bResult = E::ModuleBlog()->UpdateRelationBlogUser($oBlogUser);
                        }
                    } else {
                        // Подписки ещё не было - оформим ее
                        /** @var ModuleBlog_EntityBlogUser $oBlogUserNew */
                        $oBlogUserNew = E::GetEntity('Blog_BlogUser');
                        $oBlogUserNew->setBlogId($oBlog->getId());
                        $oBlogUserNew->setUserId($this->oUserCurrent->getId());
                        $oBlogUserNew->setUserRole(ModuleBlog::BLOG_USER_ROLE_WISHES);
                        $bResult = E::ModuleBlog()->AddRelationBlogUser($oBlogUserNew);
                    }

                    if ($bResult) {
                        // Отправим сообщение модераторам и администраторам блога о том, что
                        // этот пользоватлеь захотел присоединиться к нашему блогу
                        $aBlogUsersResult = E::ModuleBlog()->GetBlogUsersByBlogId(
                            $oBlog->getId(),
                            array(
                                ModuleBlog::BLOG_USER_ROLE_MODERATOR,
                                ModuleBlog::BLOG_USER_ROLE_ADMINISTRATOR
                            ), null
                        );
                        if ($aBlogUsersResult) {
                            /** @var ModuleUser_EntityUser[] $aBlogModerators */
                            $aBlogModerators = array();
                            /** @var ModuleBlog_EntityBlogUser $oCurrentBlogUser */
                            foreach ($aBlogUsersResult['collection'] as $oCurrentBlogUser) {
                                $aBlogModerators[] = $oCurrentBlogUser->getUser();
                            }
                            // Добавим владельца блога к списку
                            $aBlogModerators = array_merge(
                                $aBlogModerators,
                                array($oBlog->getOwner())
                            );
                            $this->SendBlogRequest($oBlog, $aBlogModerators, $this->oUserCurrent);
                        }


                        E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('blog_join_request_send'), E::ModuleLang()->Get('attention'));
                        E::ModuleViewer()->AssignAjax('bState', true);
                        return;
                    }

                }

            } else {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('blog_join_error_self'), E::ModuleLang()->Get('attention'));
                return;
            }
        }
        if ($oBlogUser && ($oBlogUser->getUserRole() == ModuleBlog::BLOG_USER_ROLE_MEMBER)) {

            // Unsubscribe user from the blog
            if (E::ModuleBlog()->DeleteRelationBlogUser($oBlogUser)) {
                E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('blog_leave_ok'), E::ModuleLang()->Get('attention'));
                E::ModuleViewer()->AssignAjax('bState', false);

                //  Уменьшаем число читателей блога
                $oBlog->setCountUser($oBlog->getCountUser() - 1);
                E::ModuleBlog()->UpdateBlog($oBlog);
                E::ModuleViewer()->AssignAjax('iCountUser', $oBlog->getCountUser());

                //  Удаляем подписку на этот блог в ленте пользователя
                E::ModuleUserfeed()->UnsubscribeUser($this->oUserCurrent->getId(), ModuleUserfeed::SUBSCRIBE_TYPE_BLOG, $oBlog->getId());
                return;
            } else {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                return;
            }
        }
        if ($oBlogUser && ($oBlogUser->getUserRole() == ModuleBlog::BLOG_USER_ROLE_NOTMEMBER)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('blog_user_request_no_accept'), E::ModuleLang()->Get('error'));
            return;
        }
        if ($oBlogUser && ($oBlogUser->getUserRole() == ModuleBlog::BLOG_USER_ROLE_BAN)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('blog_leave_error_banned'), E::ModuleLang()->Get('error'));
            return;
        }
        if ($oBlogUser && ($oBlogUser->getUserRole() == ModuleBlog::BLOG_USER_ROLE_BAN_FOR_COMMENT)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('blog_leave_error_banned'), E::ModuleLang()->Get('error'));
            return;
        }
        if ($oBlogUser && ($oBlogUser->getUserRole() == ModuleBlog::BLOG_USER_ROLE_WISHES)) {
            E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('blog_join_request_leave'), E::ModuleLang()->Get('attention'));
            E::ModuleViewer()->AssignAjax('bState', true);
            return;
        }
    }

    /**
     * Выполняется при завершении работы экшена
     *
     */
    public function EventShutdown() {

        //  Загружаем в шаблон необходимые переменные
        E::ModuleViewer()->Assign('sMenuHeadItemSelect', $this->sMenuHeadItemSelect);
        E::ModuleViewer()->Assign('sMenuItemSelect', $this->sMenuItemSelect);
        E::ModuleViewer()->Assign('sMenuSubItemSelect', $this->sMenuSubItemSelect);
        E::ModuleViewer()->Assign('sMenuSubBlogUrl', $this->sMenuSubBlogUrl);
        E::ModuleViewer()->Assign('iCountTopicsCollectiveNew', $this->iCountTopicsCollectiveNew);
        E::ModuleViewer()->Assign('iCountTopicsPersonalNew', $this->iCountTopicsPersonalNew);
        E::ModuleViewer()->Assign('iCountTopicsBlogNew', $this->iCountTopicsBlogNew);
        E::ModuleViewer()->Assign('iCountTopicsNew', $this->iCountTopicsNew);

        E::ModuleViewer()->Assign('BLOG_USER_ROLE_GUEST', ModuleBlog::BLOG_USER_ROLE_GUEST);
        E::ModuleViewer()->Assign('BLOG_USER_ROLE_USER', ModuleBlog::BLOG_USER_ROLE_USER);
        E::ModuleViewer()->Assign('BLOG_USER_ROLE_MODERATOR', ModuleBlog::BLOG_USER_ROLE_MODERATOR);
        E::ModuleViewer()->Assign('BLOG_USER_ROLE_ADMINISTRATOR', ModuleBlog::BLOG_USER_ROLE_ADMINISTRATOR);
        E::ModuleViewer()->Assign('BLOG_USER_ROLE_INVITE', ModuleBlog::BLOG_USER_ROLE_INVITE);
        E::ModuleViewer()->Assign('BLOG_USER_ROLE_REJECT', ModuleBlog::BLOG_USER_ROLE_REJECT);
        E::ModuleViewer()->Assign('BLOG_USER_ROLE_BAN', ModuleBlog::BLOG_USER_ROLE_BAN);
        E::ModuleViewer()->Assign('BLOG_USER_ROLE_BAN_FOR_COMMENT', ModuleBlog::BLOG_USER_ROLE_BAN_FOR_COMMENT);
    }

}

// EOF