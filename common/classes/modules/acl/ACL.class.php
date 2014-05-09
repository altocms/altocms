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
 * ACL (Access Control List)
 * Модуль контроля доступа пользователей к определенным возможностям/функциям
 *
 * @package modules.acl
 * @since   1.0
 */
class ModuleACL extends Module {
    /**
     * Коды ответов на запрос о возможности
     * пользователя голосовать за блог
     */
    const CAN_VOTE_BLOG_FALSE = 0;
    const CAN_VOTE_BLOG_TRUE = 1;
    const CAN_VOTE_BLOG_ERROR_CLOSE = 2;
    /**
     * Коды механизма удаления блога
     */
    const CAN_DELETE_BLOG_EMPTY_ONLY = 1;
    const CAN_DELETE_BLOG_WITH_TOPICS = 2;

    /**
     * Инициализация модуля
     *
     */
    public function Init() {

    }

    /**
     * Проверяет может ли пользователь создавать блоги
     *
     * @param ModuleUser_EntityUser $oUser    Пользователь
     *
     * @return bool
     */
    public function CanCreateBlog(ModuleUser_EntityUser $oUser) {
        if ($oUser->getRating() >= Config::Get('acl.create.blog.rating')) {
            return true;
        }
        return false;
    }

    /**
     * Проверяет может ли пользователь создавать топики в определенном блоге
     *
     * @param ModuleUser_EntityUser $oUser    Пользователь
     * @param ModuleBlog_EntityBlog $oBlog    Блог
     *
     * @return bool
     */
    public function CanAddTopic(ModuleUser_EntityUser $oUser, ModuleBlog_EntityBlog $oBlog) {
        // * Если юзер является создателем блога то разрешаем ему постить
        if ($oUser->getId() == $oBlog->getOwnerId()) {
            return true;
        }
        // * Если рейтинг юзера больше либо равен порогу постинга в блоге то разрешаем постинг
        if ($oUser->getRating() >= $oBlog->getLimitRatingTopic()) {
            return true;
        }
        return false;
    }

    /**
     * Проверяет может ли пользователь создавать комментарии
     *
     * @param ModuleUser_EntityUser $oUser
     * @param null                  $oTopic
     *
     * @return bool
     */
    public function CanPostComment(ModuleUser_EntityUser $oUser, $oTopic = null) {

        // * Проверяем на закрытый блог
        if ($oTopic && !$this->IsAllowShowBlog($oTopic->getBlog(), $oUser)) {
            return false;
        }
        if ($oUser->getRating() >= Config::Get('acl.create.comment.rating')) {
            return true;
        }
        return false;
    }

    /**
     * Проверяет может ли пользователь создавать комментарии по времени(например ограничение максимум 1 коммент в 5 минут)
     *
     * @param ModuleUser_EntityUser $oUser    Пользователь
     *
     * @return bool
     */
    public function CanPostCommentTime(ModuleUser_EntityUser $oUser) {

        if (Config::Get('acl.create.comment.limit_time') > 0 && $oUser->getDateCommentLast()) {
            $sDateCommentLast = strtotime($oUser->getDateCommentLast());
            if ($oUser->getRating() < Config::Get('acl.create.comment.limit_time_rating')
                && ((time() - $sDateCommentLast) < Config::Get('acl.create.comment.limit_time'))
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Проверяет может ли пользователь создавать топик по времени
     *
     * @param  ModuleUser_EntityUser $oUser    Пользователь
     *
     * @return bool
     */
    public function CanPostTopicTime(ModuleUser_EntityUser $oUser) {
        // Для администраторов ограничение по времени не действует
        if ($oUser->isAdministrator()
            || Config::Get('acl.create.topic.limit_time') == 0
            || $oUser->getRating() >= Config::Get('acl.create.topic.limit_time_rating')
        ) {
            return true;
        }

        // * Проверяем, если топик опубликованный меньше чем acl.create.topic.limit_time секунд назад
        $aTopics = $this->Topic_GetLastTopicsByUserId($oUser->getId(), Config::Get('acl.create.topic.limit_time'));
        if (isset($aTopics['count']) && $aTopics['count'] > 0) {
            return false;
        }
        return true;
    }

    /**
     * Проверяет может ли пользователь отправить инбокс по времени
     *
     * @param  ModuleUser_EntityUser $oUser    Пользователь
     *
     * @return bool
     */
    public function CanSendTalkTime(ModuleUser_EntityUser $oUser) {
        // Для администраторов ограничение по времени не действует
        if ($oUser->isAdministrator()
            || Config::Get('acl.create.talk.limit_time') == 0
            || $oUser->getRating() >= Config::Get('acl.create.talk.limit_time_rating')
        ) {
            return true;
        }

        // * Проверяем, если топик опубликованный меньше чем acl.create.topic.limit_time секунд назад
        $aTalks = $this->Talk_GetLastTalksByUserId($oUser->getId(), Config::Get('acl.create.talk.limit_time'));
        if (isset($aTalks['count']) && $aTalks['count'] > 0) {
            return false;
        }
        return true;
    }

    /**
     * Проверяет может ли пользователь создавать комментарии к инбоксу по времени
     *
     * @param  ModuleUser_EntityUser $oUser    Пользователь
     *
     * @return bool
     */
    public function CanPostTalkCommentTime(ModuleUser_EntityUser $oUser) {
        // * Для администраторов ограничение по времени не действует
        if ($oUser->isAdministrator()
            || Config::Get('acl.create.talk_comment.limit_time') == 0
            || $oUser->getRating() >= Config::Get('acl.create.talk_comment.limit_time_rating')
        ) {
            return true;
        }

        // * Проверяем, если топик опубликованный меньше чем acl.create.topic.limit_time секунд назад
        $aTalkComments = $this->Comment_GetCommentsByUserId($oUser->getId(), 'talk', 1, 1);

        // * Если комментариев не было
        if (!is_array($aTalkComments) || $aTalkComments['count'] == 0) {
            return true;
        }
        // * Достаем последний комментарий
        $oComment = array_shift($aTalkComments['collection']);
        $sDate = strtotime($oComment->getDate());

        if ($sDate && ((time() - $sDate) < Config::Get('acl.create.talk_comment.limit_time'))) {
            return false;
        }
        return true;
    }

    /**
     * Проверяет может ли пользователь создавать комментарии используя HTML
     *
     * @param ModuleUser_EntityUser $oUser    Пользователь
     *
     * @return bool
     */
    public function CanUseHtmlInComment(ModuleUser_EntityUser $oUser) {
        return true;
    }

    /**
     * Проверяет может ли пользователь голосовать за конкретный комментарий
     *
     * @param ModuleUser_EntityUser       $oUser       Пользователь
     * @param ModuleComment_EntityComment $oComment    Комментарий
     *
     * @return bool
     */
    public function CanVoteComment(ModuleUser_EntityUser $oUser, ModuleComment_EntityComment $oComment) {
        if ($oUser->getRating() >= Config::Get('acl.vote.comment.rating')) {
            return true;
        }
        return false;
    }

    /**
     * Проверяет может ли пользователь голосовать за конкретный блог
     *
     * @param ModuleUser_EntityUser $oUser    Пользователь
     * @param ModuleBlog_EntityBlog $oBlog    Блог
     *
     * @return bool
     */
    public function CanVoteBlog(ModuleUser_EntityUser $oUser, ModuleBlog_EntityBlog $oBlog) {

        // * Если блог приватный, проверяем является ли пользователь его читателем
        if ($oBlog->getBlogType() && $oBlog->getBlogType()->IsPrivate()) {
            $oBlogUser = $this->Blog_GetBlogUserByBlogIdAndUserId($oBlog->getId(), $oUser->getId());
            if (!$oBlogUser || $oBlogUser->getUserRole() < ModuleBlog::BLOG_USER_ROLE_GUEST) {
                return self::CAN_VOTE_BLOG_ERROR_CLOSE;
            }
        }
        if ($oUser->getRating() >= Config::Get('acl.vote.blog.rating')) {
            return self::CAN_VOTE_BLOG_TRUE;
        }
        return self::CAN_VOTE_BLOG_FALSE;
    }

    /**
     * Проверяет может ли пользователь голосовать за конкретный топик
     *
     * @param ModuleUser_EntityUser   $oUser     Пользователь
     * @param ModuleTopic_EntityTopic $oTopic    Топик
     *
     * @return bool
     */
    public function CanVoteTopic(ModuleUser_EntityUser $oUser, ModuleTopic_EntityTopic $oTopic) {

        if ($oUser->getRating() >= Config::Get('acl.vote.topic.rating')) {
            return true;
        }
        return false;
    }

    /**
     * Проверяет может ли пользователь голосовать за конкретного пользователя
     *
     * @param ModuleUser_EntityUser $oUser          Пользователь
     * @param ModuleUser_EntityUser $oUserTarget    Пользователь за которого голосуем
     *
     * @return bool
     */
    public function CanVoteUser(ModuleUser_EntityUser $oUser, ModuleUser_EntityUser $oUserTarget) {

        if ($oUser->getRating() >= Config::Get('acl.vote.user.rating')) {
            return true;
        }
        return false;
    }

    /**
     * Проверяет можно ли юзеру слать инвайты
     *
     * @param ModuleUser_EntityUser $oUser    Пользователь
     *
     * @return bool
     */
    public function CanSendInvite(ModuleUser_EntityUser $oUser) {

        if ($this->User_GetCountInviteAvailable($oUser) == 0) {
            return false;
        }
        return true;
    }

    /**
     * Можно ли юзеру постить в данный блог
     *
     * @param ModuleBlog_EntityBlog $oBlog    Блог
     * @param ModuleUser_EntityUser $oUser    Пользователь
     *
     * @return bool
     */
    public function IsAllowBlog($oBlog, $oUser) {

        if ($oUser->isAdministrator()) {
            return true;
        }
        if ($oUser->getRating() <= Config::Get('acl.create.topic.limit_rating')) {
            return false;
        }
        if ($oBlog->getOwnerId() == $oUser->getId()) {
            return true;
        }

        return (bool)$this->Blog_GetBlogsAllowTo('write', $oUser, $oBlog->getId(), true);
    }

    /**
     * Проверяет можно или нет юзеру просматривать блог
     *
     * @param ModuleBlog_EntityBlog $oBlog    Блог
     * @param ModuleUser_EntityUser $oUser    Пользователь
     *
     * @return bool
     */
    public function IsAllowShowBlog($oBlog, $oUser) {

        if (!$oBlog->getBlogType() || !$oBlog->getBlogType()->IsPrivate()) {
            return true;
        }
        if (!$oUser && !$oBlog->getBlogType()->GetAclRead(ModuleBlog::BLOG_USER_ACL_GUEST)) {
            return false;
        }
        if ($oUser->isAdministrator()) {
            return true;
        }
        if ($oBlog->getOwnerId() == $oUser->getId()) {
            return true;
        }

        return (bool)$this->Blog_GetBlogsAllowTo('read', $oUser, $oBlog->getId(), true);
    }

    /**
     * Проверяет можно или нет пользователю редактировать данный топик
     *
     * @param  ModuleTopic_EntityTopic $oTopic    Топик
     * @param  ModuleUser_EntityUser   $oUser     Пользователь
     *
     * @return bool
     */
    public function IsAllowEditTopic($oTopic, $oUser) {
        // * Разрешаем если это админ сайта или автор топика
        if ($oTopic->getUserId() == $oUser->getId() || $oUser->isAdministrator()) {
            return true;
        }
        // * Если владелец блога
        if ($oTopic->getBlog()->getOwnerId() == $oUser->getId()) {
            return true;
        }

        // Проверяем права
        return $this->CheckBlogEditContent($oTopic->getBlog(), $oUser);
    }

    /**
     * Проверяет можно или нет пользователю удалять данный топик
     *
     * @param ModuleTopic_EntityTopic $oTopic    Топик
     * @param ModuleUser_EntityUser   $oUser     Пользователь
     *
     * @return bool
     */
    public function IsAllowDeleteTopic($oTopic, $oUser) {
        // * Разрешаем если это админ сайта или автор топика
        if ($oTopic->getUserId() == $oUser->getId() || $oUser->isAdministrator()) {
            return true;
        }
        // * Если владелец блога
        if ($oTopic->getBlog()->getOwnerId() == $oUser->getId()) {
            return true;
        }

        // Проверяем права
        return $this->CheckBlogDeleteContent($oTopic->getBlog(), $oUser);
    }

    /**
     * Проверяет может ли пользователь удалить комментарий
     *
     * @param  ModuleUser_EntityUser $oUser    Пользователь
     *
     * @return bool
     */
    public function CanDeleteComment($oUser) {
        if (!$oUser || !$oUser->isAdministrator()) {
            return false;
        }
        return true;
    }

    /**
     * Проверяет может ли пользователь публиковать на главной
     *
     * @param  ModuleUser_EntityUser $oUser    Пользователь
     *
     * @return bool
     */
    public function IsAllowPublishIndex(ModuleUser_EntityUser $oUser) {
        if ($oUser->isAdministrator()) {
            return true;
        }
        return false;
    }

    /**
     * Проверяет можно или нет пользователю управлять пользователями блога
     *
     * @param  ModuleBlog_EntityBlog $oBlog    Блог
     * @param  ModuleUser_EntityUser $oUser    Пользователь
     *
     * @return bool
     */
    public function IsAllowAdminBlog($oBlog, $oUser) {

        if ($oUser->isAdministrator()) {
            return true;
        }
        // * Разрешаем если это владелец блога
        if ($oBlog->getOwnerId() == $oUser->getId()) {
            return true;
        }

        // Проверка прав на администрирование
        return $this->CheckBlogControlUsers($oBlog, $oUser);
    }

    /**
     * Проверяет можно или нет пользователю редактировать данный блог
     *
     * @param  ModuleBlog_EntityBlog $oBlog    Блог
     * @param  ModuleUser_EntityUser $oUser    Пользователь
     *
     * @return bool
     */
    public function IsAllowEditBlog($oBlog, $oUser) {

        if ($oUser->isAdministrator()) {
            return true;
        }
        // Разрешаем если это владелец блога
        if ($oBlog->getOwnerId() == $oUser->getId()) {
            return true;
        }

        // Проверка прав на редактирование
        return $this->CheckBlogEditBlog($oBlog, $oUser);
    }

    /**
     * Проверяет можно или нет пользователю удалять данный блог
     *
     * @param ModuleBlog_EntityBlog $oBlog    Блог
     * @param ModuleUser_EntityUser $oUser    Пользователь
     *
     * @return bool|int
     */
    public function IsAllowDeleteBlog($oBlog, $oUser) {
        // * Разрешаем если это админ сайта
        if ($oUser->isAdministrator()) {
            return self::CAN_DELETE_BLOG_WITH_TOPICS;
        }
        // * Разрешаем владелецу, но только пустой
        if ($oBlog->getOwnerId() == $oUser->getId()) {
            return self::CAN_DELETE_BLOG_EMPTY_ONLY;
        }

        $oBlogUser = $this->Blog_GetBlogUserByBlogIdAndUserId($oBlog->getId(), $oUser->getId());
        if ($oBlogUser && $oBlogUser->getIsAdministrator()) {
            return self::CAN_DELETE_BLOG_EMPTY_ONLY;
        }
        return false;
    }

    /**
     * Проверка на ограничение по времени на постинг на стене
     *
     * @param ModuleUser_EntityUser $oUser    Пользователь
     * @param ModuleWall_EntityWall $oWall    Объект сообщения на стене
     *
     * @return bool
     */
    public function CanAddWallTime($oUser, $oWall) {

        // * Для администраторов ограничение по времени не действует
        if ($oUser->isAdministrator()
            || Config::Get('acl.create.wall.limit_time') == 0
            || $oUser->getRating() >= Config::Get('acl.create.wall.limit_time_rating')
        ) {
            return true;
        }
        if ($oWall->getUserId() == $oWall->getWallUserId()) {
            return true;
        }
        // * Получаем последнее сообщение
        $aWall = $this->Wall_GetWall(array('user_id' => $oWall->getUserId()), array('id' => 'desc'), 1, 1, array());
        // * Если сообщений нет
        if ($aWall['count'] == 0) {
            return true;
        }

        $oWallLast = array_shift($aWall['collection']);
        $sDate = strtotime($oWallLast->getDateAdd());
        if ($sDate && ((time() - $sDate) < Config::Get('acl.create.wall.limit_time'))) {
            return false;
        }
        return true;
    }

    /************************************************************
     * Набор методов для управления/проверки прав пользователей
     *
     * Права пользователей определяются ролями, которые могут быть объединены в группы
     *
     * На текущем этапе определяется только одна группа 'blogs', внутри которой есть роли 'administrator' и 'moderator',
     * это права администраторов и модераторов блогов
     */

    /**
     * Вспомогательный метод получения прав пользователей
     *
     * @return array
     */
    protected function _getAllUserRights() {

        $aRights = Config::Get('rights');
        // Если права не заданы в конфиге, то задаем права по умолчанию для группы 'blogs'
        if (!$aRights) {
            $aRights = array(
                'blogs' => array(
                    'administrator' => array(
                        'control_users'  => true,
                        'edit_blog'      => true,
                        'edit_content'   => true,
                        'delete_content' => true,
                        'edit_comment'   => true,
                        'delete_comment' => true,
                    ),
                    'moderator'     => array(
                        'control_users' => false,
                        'edit_blog'     => false,
                        'edit_content'  => true,
                        'del_content'   => false,
                        'edit_comment'  => true,
                        'delete_comment'   => true,
                    ),
                ),
            );
        }
        return $aRights;
    }

    /**
     * Получение прав для конкретной группы и, опционально, роли
     *
     * @param string $sGroup
     * @param string $sRole
     *
     * @return array
     */
    public function GetUserRights($sGroup, $sRole = null) {

        $aRights = $this->_getAllUserRights();
        if (isset($aRights[$sGroup])) {
            $aRights = $aRights[$sGroup];
            if ($sRole && isset($aRights[$sRole])) {
                $aRights = $aRights[$sRole];
            }
        } else {
            $aRights = array();
        }
        return $aRights;
    }

    /**
     * Вспомогательный метод проверки прав пользователя блога
     *
     * @param ModuleBlog_EntityBlog $oBlog
     * @param ModuleUser_EntityUser $oUser
     * @param string                $sRights
     *
     * @return bool
     */
    protected function _checkBlogUserRights($oBlog, $oUser, $sRights) {

        $sUserRole = '';
        $bCurrentUser = false;
        $bResult = false;

        if (!$oBlog) {
            return false;
        }

        // Если пользователь не передан, то берется текущий
        if (!$oUser) {
            if ($oUser = $this->User_GetUserCurrent()) {
                $bCurrentUser = true;
            } else {
                return false;
            }
        } elseif ($this->User_GetUserCurrent() && $this->User_GetUserCurrent()->getId() == $oUser->getId()) {
            $bCurrentUser = true;
        }

        $sCacheKey = 'acl_blog_user_rights' . serialize(array($oBlog->GetId(), $oUser ? $oUser->GetId() : 0, $bCurrentUser, $sRights));
        // Сначала проверяем кеш
        if (is_int($xCacheResult = $this->Cache_Get($sCacheKey, 'tmp'))) {
            return $xCacheResult;
        }

        if ($bCurrentUser) {
            // * Для авторизованного пользователя данный код будет работать быстрее
            if ($oBlog->getUserIsAdministrator()) {
                $sUserRole = 'administrator';
            } elseif ($oBlog->getUserIsModerator()) {
                $sUserRole = 'moderator';
            }
        } else {
            $oBlogUser = $this->Blog_GetBlogUserByBlogIdAndUserId($oBlog, $oUser->getId());
            if ($oBlogUser) {
                if ($oBlogUser->getIsAdministrator()) {
                    $sUserRole = 'administrator';
                } elseif ($oBlogUser->getIsModerator()) {
                    $sUserRole = 'moderator';
                }
            }
        }

        if ($sUserRole) {
            $aUserRights = $this->GetUserRights('blogs', $sUserRole);
            $bResult = isset($aUserRights[$sRights]) && (bool)$aUserRights[$sRights];
        }

        $this->Cache_Set($sCacheKey, $bResult ? 1 : 0, array('blog_update', 'user_update'), 0, 'tmp');

        return $bResult;
    }

    /**
     * Право на управление пользователями блога
     *
     * @param ModuleBlog_EntityBlog $oBlog
     * @param ModuleUser_EntityUser $oUser - если не задано, то берется текущий авторизованный пользователь
     *
     * @return bool
     */
    public function CheckBlogControlUsers($oBlog, $oUser = null) {

        return $this->_checkBlogUserRights($oBlog, $oUser, 'control_users');
    }

    /**
     * Право на редактирование блога
     *
     * @param ModuleBlog_EntityBlog $oBlog
     * @param ModuleUser_EntityUser $oUser - если не задано, то берется текущий авторизованный пользователь
     *
     * @return bool
     */
    public function CheckBlogEditBlog($oBlog, $oUser = null) {

        return $this->_checkBlogUserRights($oBlog, $oUser, 'edit_blog');
    }

    /**
     * Право на редактирование контента блога
     *
     * @param ModuleBlog_EntityBlog $oBlog
     * @param ModuleUser_EntityUser $oUser - если не задано, то берется текущий авторизованный пользователь
     *
     * @return bool
     */
    public function CheckBlogEditContent($oBlog, $oUser = null) {

        return $this->_checkBlogUserRights($oBlog, $oUser, 'edit_content');
    }

    /**
     * Право на удаление контента блога
     *
     * @param ModuleBlog_EntityBlog $oBlog
     * @param ModuleUser_EntityUser $oUser - если не задано, то берется текущий авторизованный пользователь
     *
     * @return bool
     */
    public function CheckBlogDeleteContent($oBlog, $oUser = null) {

        return $this->_checkBlogUserRights($oBlog, $oUser, 'delete_content');
    }

    /**
     * Право на редактирование комментариев блога
     *
     * @param ModuleBlog_EntityBlog $oBlog
     * @param ModuleUser_EntityUser $oUser - если не задано, то берется текущий авторизованный пользователь
     *
     * @return bool
     */
    public function CheckBlogEditComment($oBlog, $oUser = null) {

        return $this->_checkBlogUserRights($oBlog, $oUser, 'edit_comment');
    }

    /**
     * Право на удаление комментариев блога
     *
     * @param ModuleBlog_EntityBlog $oBlog
     * @param ModuleUser_EntityUser $oUser - если не задано, то берется текущий авторизованный пользователь
     *
     * @return bool
     */
    public function CheckBlogDeleteComment($oBlog, $oUser = null) {

        return $this->_checkBlogUserRights($oBlog, $oUser, 'delete_comment');
    }

}

// EOF