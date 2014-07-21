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
 * Модуль рассылок уведомлений пользователям
 *
 * @package modules.notify
 * @since   1.0
 */
class ModuleNotify extends Module {
    /**
     * Статусы степени обработки заданий отложенной публикации в базе данных
     */
    const NOTIFY_TASK_STATUS_NULL = 1;
    /**
     * Объект локального вьювера для рендеринга сообщений
     *
     * @var ModuleViewer
     */
    protected $oViewerLocal = null;
    /**
     * Массив заданий на удаленную публикацию
     *
     * @var array
     */
    protected $aTask = array();
    /**
     * Объект маппера
     *
     * @var ModuleNotify_MapperNotify
     */
    protected $oMapper = null;

    /**
     * Префикс шаблонов
     *
     * @var string
     */
    protected $sPrefix = '';

    /**
     * Название директории с шаблономи
     *
     * @var string
     */
    protected $sDir = '';

    /**
     * Инициализация модуля
     * Создаём локальный экземпляр модуля Viewer
     * Момент довольно спорный, но позволяет избавить основной шаблон от мусора уведомлений
     *
     */
    public function Init() {

        $this->oViewerLocal = $this->Viewer_GetLocalViewer();
        $this->oMapper = Engine::GetMapper(__CLASS__);
        $this->sDir = Config::Get('module.notify.dir');
        $this->sPrefix = Config::Get('module.notify.prefix');
    }

    /**
     * Отправляет юзеру уведомление о новом комментарии в его топике
     *
     * @param ModuleUser_EntityUser       $oUserTo         Объект пользователя кому отправляем
     * @param ModuleTopic_EntityTopic     $oTopic          Объект топика
     * @param ModuleComment_EntityComment $oComment        Объект комментария
     * @param ModuleUser_EntityUser       $oUserComment    Объект пользователя, написавшего комментарий
     *
     * @return bool
     */
    public function SendCommentNewToAuthorTopic(
        ModuleUser_EntityUser $oUserTo, ModuleTopic_EntityTopic $oTopic, ModuleComment_EntityComment $oComment,
        ModuleUser_EntityUser $oUserComment
    ) {
        /**
         * Проверяем можно ли юзеру рассылать уведомление
         */
        if (!$oUserTo->getSettingsNoticeNewComment()) {
            return false;
        }
        $this->Send(
            $oUserTo,
            'comment_new.tpl',
            $this->Lang_Get('notify_subject_comment_new'),
            array(
                 'oUserTo'      => $oUserTo,
                 'oTopic'       => $oTopic,
                 'oComment'     => $oComment,
                 'oUserComment' => $oUserComment,
            )
        );
        return true;
    }

    /**
     * Отправляет юзеру уведомление об ответе на его комментарий
     *
     * @param ModuleUser_EntityUser       $oUserTo         Объект пользователя кому отправляем
     * @param ModuleTopic_EntityTopic     $oTopic          Объект топика
     * @param ModuleComment_EntityComment $oComment        Объект комментария
     * @param ModuleUser_EntityUser       $oUserComment    Объект пользователя, написавшего комментарий
     *
     * @return bool
     */
    public function SendCommentReplyToAuthorParentComment(
        ModuleUser_EntityUser $oUserTo, ModuleTopic_EntityTopic $oTopic, ModuleComment_EntityComment $oComment,
        ModuleUser_EntityUser $oUserComment
    ) {
        /**
         * Проверяем можно ли юзеру рассылать уведомление
         */
        if (!$oUserTo->getSettingsNoticeReplyComment()) {
            return false;
        }
        $this->Send(
            $oUserTo,
            'comment_reply.tpl',
            $this->Lang_Get('notify_subject_comment_reply'),
            array(
                 'oUserTo'      => $oUserTo,
                 'oTopic'       => $oTopic,
                 'oComment'     => $oComment,
                 'oUserComment' => $oUserComment,
            )
        );
        return true;
    }

    /**
     * Отправляет юзеру уведомление о новом топике в блоге, в котором он состоит
     *
     * @param ModuleUser_EntityUser   $oUserTo       Объект пользователя кому отправляем
     * @param ModuleTopic_EntityTopic $oTopic        Объект топика
     * @param ModuleBlog_EntityBlog   $oBlog         Объект блога
     * @param ModuleUser_EntityUser   $oUserTopic    Объект пользователя, написавшего топик
     *
     * @return bool
     */
    public function SendTopicNewToSubscribeBlog(
        ModuleUser_EntityUser $oUserTo, ModuleTopic_EntityTopic $oTopic, ModuleBlog_EntityBlog $oBlog,
        ModuleUser_EntityUser $oUserTopic
    ) {
        /**
         * Проверяем можно ли юзеру рассылать уведомление
         */
        if (!$oUserTo->getSettingsNoticeNewTopic()) {
            return false;
        }
        $this->Send(
            $oUserTo,
            'topic_new.tpl',
            $this->Lang_Get('notify_subject_topic_new') . ' «' . htmlspecialchars($oBlog->getTitle()) . '»',
            array(
                 'oUserTo'    => $oUserTo,
                 'oTopic'     => $oTopic,
                 'oBlog'      => $oBlog,
                 'oUserTopic' => $oUserTopic,
            )
        );
        return true;
    }

    /**
     * Отправляет уведомление с новым линком активации
     *
     * @param ModuleUser_EntityUser $oUser    Объект пользователя
     */
    public function SendReactivationCode(ModuleUser_EntityUser $oUser) {

        $this->Send(
            $oUser,
            'reactivation.tpl',
            $this->Lang_Get('notify_subject_reactvation'),
            array(
                 'oUser' => $oUser,
            ), null, true
        );
    }

    /**
     * Отправляет уведомление при регистрации с активацией
     *
     * @param ModuleUser_EntityUser $oUser        Объект пользователя
     * @param string                $sPassword    Пароль пользователя
     */
    public function SendRegistrationActivate(ModuleUser_EntityUser $oUser, $sPassword) {

        $this->Send(
            $oUser,
            'registration_activate.tpl',
            $this->Lang_Get('notify_subject_registration_activate'),
            array(
                 'oUser'     => $oUser,
                 'sPassword' => $sPassword,
            ), null, true
        );
    }

    /**
     * Отправляет уведомление о регистрации
     *
     * @param ModuleUser_EntityUser $oUser        Объект пользователя
     * @param string                $sPassword    Пароль пользователя
     */
    public function SendRegistration(ModuleUser_EntityUser $oUser, $sPassword) {

        $this->Send(
            $oUser,
            'registration.tpl',
            $this->Lang_Get('notify_subject_registration'),
            array(
                 'oUser'     => $oUser,
                 'sPassword' => $sPassword,
            ), null, true
        );
    }

    /**
     * Отправляет инвайт
     *
     * @param ModuleUser_EntityUser   $oUserFrom    Пароль пользователя, который отправляет инвайт
     * @param string                  $sMailTo      Емайл на который отправляем инвайт
     * @param ModuleUser_EntityInvite $oInvite      Объект инвайта
     */
    public function SendInvite(ModuleUser_EntityUser $oUserFrom, $sMailTo, ModuleUser_EntityInvite $oInvite) {

        $this->Send(
            $sMailTo,
            'invite.tpl',
            $this->Lang_Get('notify_subject_invite'),
            array(
                 'sMailTo'   => $sMailTo,
                 'oUserFrom' => $oUserFrom,
                 'oInvite'   => $oInvite,
            )
        );
    }

    /**
     * Отправляет уведомление при новом личном сообщении
     *
     * @param ModuleUser_EntityUser $oUserTo      Объект пользователя, которому отправляем сообщение
     * @param ModuleUser_EntityUser $oUserFrom    Объект пользователя, который отправляет сообщение
     * @param ModuleTalk_EntityTalk $oTalk        Объект сообщения
     *
     * @return bool
     */
    public function SendTalkNew(ModuleUser_EntityUser $oUserTo, ModuleUser_EntityUser $oUserFrom, ModuleTalk_EntityTalk $oTalk) {
        /**
         * Проверяем можно ли юзеру рассылать уведомление
         */
        if (!$oUserTo->getSettingsNoticeNewTalk()) {
            return false;
        }
        $this->Send(
            $oUserTo,
            'talk_new.tpl',
            $this->Lang_Get('notify_subject_talk_new'),
            array(
                 'oUserTo'   => $oUserTo,
                 'oUserFrom' => $oUserFrom,
                 'oTalk'     => $oTalk,
            )
        );
        return true;
    }

    /**
     * Отправляет уведомление о новом сообщение в личке
     *
     * @param ModuleUser_EntityUser       $oUserTo         Объект пользователя, которому отправляем уведомление
     * @param ModuleUser_EntityUser       $oUserFrom       Объект пользователя, которыф написал комментарий
     * @param ModuleTalk_EntityTalk       $oTalk           Объект сообщения
     * @param ModuleComment_EntityComment $oTalkComment    Объект комментария
     *
     * @return bool
     */
    public function SendTalkCommentNew(
        ModuleUser_EntityUser $oUserTo, ModuleUser_EntityUser $oUserFrom, ModuleTalk_EntityTalk $oTalk,
        ModuleComment_EntityComment $oTalkComment
    ) {
        /**
         * Проверяем можно ли юзеру рассылать уведомление
         */
        if (!$oUserTo->getSettingsNoticeNewTalk()) {
            return false;
        }
        $this->Send(
            $oUserTo,
            'talk_comment_new.tpl',
            $this->Lang_Get('notify_subject_talk_comment_new'),
            array(
                 'oUserTo'      => $oUserTo,
                 'oUserFrom'    => $oUserFrom,
                 'oTalk'        => $oTalk,
                 'oTalkComment' => $oTalkComment,
            )
        );
        return true;
    }

    /**
     * Отправляет пользователю сообщение о добавлении его в друзья
     *
     * @param ModuleUser_EntityUser $oUserTo      Объект пользователя
     * @param ModuleUser_EntityUser $oUserFrom    Объект пользователя, которого добавляем в друзья
     * @param string                $sText        Текст сообщения
     * @param string                $sPath        URL для подтверждения дружбы
     *
     * @return bool
     */
    public function SendUserFriendNew(ModuleUser_EntityUser $oUserTo, ModuleUser_EntityUser $oUserFrom, $sText, $sPath) {
        /**
         * Проверяем можно ли юзеру рассылать уведомление
         */
        if (!$oUserTo->getSettingsNoticeNewFriend()) {
            return false;
        }
        $this->Send(
            $oUserTo,
            'user_friend_new.tpl',
            $this->Lang_Get('notify_subject_user_friend_new'),
            array(
                 'oUserTo'   => $oUserTo,
                 'oUserFrom' => $oUserFrom,
                 'sText'     => $sText,
                 'sPath'     => $sPath,
            )
        );
        return true;
    }

    /**
     * Отправляет пользователю сообщение о приглашение его в приватный блог
     *
     * @param ModuleUser_EntityUser $oUserTo      Объект пользователя, который отправляет приглашение
     * @param ModuleUser_EntityUser $oUserFrom    Объект пользователя, которого приглашаем
     * @param ModuleBlog_EntityBlog $oBlog        Объект блога
     * @param                       $sPath
     */
    public function SendBlogUserInvite(ModuleUser_EntityUser $oUserTo, ModuleUser_EntityUser $oUserFrom, ModuleBlog_EntityBlog $oBlog, $sPath) {

        $this->Send(
            $oUserTo,
            'blog_invite_new.tpl',
            $this->Lang_Get('notify_subject_blog_invite_new'),
            array(
                 'oUserTo'   => $oUserTo,
                 'oUserFrom' => $oUserFrom,
                 'oBlog'     => $oBlog,
                 'sPath'     => $sPath,
            )
        );
    }

    /**
     * Уведомление при восстановлении пароля
     *
     * @param ModuleUser_EntityUser     $oUser        Объект пользователя
     * @param ModuleUser_EntityReminder $oReminder    объект напоминания пароля
     */
    public function SendReminderCode(ModuleUser_EntityUser $oUser, ModuleUser_EntityReminder $oReminder) {

        $this->Send(
            $oUser,
            'reminder_code.tpl',
            $this->Lang_Get('notify_subject_reminder_code'),
            array(
                 'oUser'     => $oUser,
                 'oReminder' => $oReminder,
            ), null, true
        );
    }

    /**
     * Уведомление с новым паролем после его восставновления
     *
     * @param ModuleUser_EntityUser $oUser           Объект пользователя
     * @param string                $sNewPassword    Новый пароль
     */
    public function SendReminderPassword(ModuleUser_EntityUser $oUser, $sNewPassword) {

        $this->Send(
            $oUser,
            'reminder_password.tpl',
            $this->Lang_Get('notify_subject_reminder_password'),
            array(
                 'oUser'        => $oUser,
                 'sNewPassword' => $sNewPassword,
            ), null, true
        );
    }

    /**
     * Уведомление при ответе на сообщение на стене
     *
     * @param ModuleWall_EntityWall $oWallParent    Объект сообщения на стене, на которое отвечаем
     * @param ModuleWall_EntityWall $oWall          Объект нового сообщения на стене
     * @param ModuleUser_EntityUser $oUser          Объект пользователя
     */
    public function SendWallReply(ModuleWall_EntityWall $oWallParent, ModuleWall_EntityWall $oWall, ModuleUser_EntityUser $oUser) {

        $this->Send(
            $oWallParent->getUser(),
            'wall_reply.tpl',
            $this->Lang_Get('notify_subject_wall_reply'),
            array(
                 'oWallParent' => $oWallParent,
                 'oUserTo'     => $oWallParent->getUser(),
                 'oWall'       => $oWall,
                 'oUser'       => $oUser,
                 'oUserWall'   => $oWall->getWallUser(), // кому принадлежит стена
            )
        );
    }

    /**
     * Уведомление о новом сообщение на стене
     *
     * @param ModuleWall_EntityWall $oWall    Объект нового сообщения на стене
     * @param ModuleUser_EntityUser $oUser    Объект пользователя
     */
    public function SendWallNew(ModuleWall_EntityWall $oWall, ModuleUser_EntityUser $oUser) {

        $this->Send(
            $oWall->getWallUser(),
            'wall_new.tpl',
            $this->Lang_Get('notify_subject_wall_new'),
            array(
                 'oUserTo'   => $oWall->getWallUser(),
                 'oWall'     => $oWall,
                 'oUser'     => $oUser,
                 'oUserWall' => $oWall->getWallUser(), // кому принадлежит стена
            )
        );
    }

    /**
     * Универсальный метод отправки уведомлений на email
     *
     * @param ModuleUser_EntityUser|string $xUserTo     Кому отправляем (пользователь или email)
     * @param string                       $sTemplate   Шаблон для отправки
     * @param string                       $sSubject    Тема письма
     * @param array                        $aAssign     Ассоциативный массив для загрузки переменных в шаблон письма
     * @param string|null                  $sPluginName Плагин из которого происходит отправка
     * @param bool                         $bForceSend  Отправлять сразу, даже при опции module.notify.delayed = true
     *
     * @return bool
     */
    public function Send($xUserTo, $sTemplate, $sSubject, $aAssign = array(), $sPluginName = null, $bForceSend = false) {

        if ($xUserTo instanceof ModuleUser_EntityUser) {
            $sMail = $xUserTo->getMail();
            $sName = $xUserTo->getLogin();
        } else {
            $sMail = $xUserTo;
            $sName = '';
        }
        /**
         * Передаём в шаблон переменные
         */
        foreach ($aAssign as $sVarName => $sValue) {
            $this->oViewerLocal->Assign($sVarName, $sValue);
        }

        /**
         * Формируем шаблон
         */
        $sTemplate = $this->sPrefix . $sTemplate;
        $sBody = $this->oViewerLocal->Fetch($this->GetTemplatePath($sTemplate, $sPluginName));
        /**
         * Если в конфигураторе указан отложенный метод отправки,
         * то добавляем задание в массив. В противном случае,
         * сразу отсылаем на email
         */
        if (Config::Get('module.notify.delayed') && !$bForceSend) {
            $oNotifyTask = Engine::GetEntity(
                'Notify_Task',
                array(
                     'user_mail'          => $sMail,
                     'user_login'         => $sName,
                     'notify_text'        => $sBody,
                     'notify_subject'     => $sSubject,
                     'date_created'       => F::Now(),
                     'notify_task_status' => self::NOTIFY_TASK_STATUS_NULL,
                )
            );
            if (Config::Get('module.notify.insert_single')) {
                $this->aTask[] = $oNotifyTask;
                $bResult = true;
            } else {
                $bResult = $this->oMapper->AddTask($oNotifyTask);
            }
        } else {
            // * Отправляем e-mail
            $this->Mail_SetAdress($sMail, $sName);
            $this->Mail_SetSubject($sSubject);
            $this->Mail_SetBody($sBody);
            $this->Mail_SetHTML();
            $bResult = $this->Mail_Send();
        }
        return $bResult;
    }

    /**
     * При завершении работы модуля проверяем наличие
     * отложенных заданий в массиве и при необходимости
     * передаем их в меппер
     */
    public function Shutdown() {

        if (!empty($this->aTask) && Config::Get('module.notify.delayed')) {
            $this->oMapper->AddTaskArray($this->aTask);
            $this->aTask = array();
        }
    }

    /**
     * Получает массив заданий на публикацию из базы с указанным количественным ограничением (выборка FIFO)
     *
     * @param  int $iLimit    Количество
     *
     * @return array
     */
    public function GetTasksDelayed($iLimit = 10) {

        return ($aResult = $this->oMapper->GetTasks($iLimit))
            ? $aResult
            : array();
    }

    /**
     * Отправляет на e-mail
     *
     * @param ModuleNotify_EntityTask $oTask    Объект задания на отправку
     */
    public function SendTask($oTask) {

        $this->Mail_SetAdress($oTask->getUserMail(), $oTask->getUserLogin());
        $this->Mail_SetSubject($oTask->getNotifySubject());
        $this->Mail_SetBody($oTask->getNotifyText());
        $this->Mail_SetHTML();
        $this->Mail_Send();
    }

    /**
     * Удаляет отложенное Notify-задание из базы
     *
     * @param  ModuleNotify_EntityTask $oTask    Объект задания на отправку
     *
     * @return bool
     */
    public function DeleteTask($oTask) {

        return $this->oMapper->DeleteTask($oTask);
    }

    /**
     * Удаляет отложенные Notify-задания по списку идентификаторов
     *
     * @param  array $aTaskId    Список ID заданий на отправку
     *
     * @return bool
     */
    public function DeleteTaskByArrayId($aTaskId) {

        return $this->oMapper->DeleteTaskByArrayId($aTaskId);
    }

    /**
     * Returns full path to email templates by name and plugin
     *
     * @param  string        $sName   Template name
     * @param  string|object $xPlugin Name or class of plugin
     *
     * @return string
     */
    public function GetTemplatePath($sName, $xPlugin = null) {

        if ($xPlugin) {
            $sPluginName = Plugin::GetPluginName($xPlugin);
        } else {
            $sPluginName = '';
        }
        $sCacheKey = 'template_path_' . $sName . '-' . $sPluginName;
        if (false === ($sResult = $this->Cache_Get($sCacheKey, 'tmp'))) {
            $bFound = false;
            if ($sPluginName) {
                $sDir = Plugin::GetTemplateDir($sPluginName) . $this->sDir . '/';
            } else {
                $sDir = F::File_NormPath($this->Viewer_GetTemplateDir() . '/' . $this->sDir . '/');
            }

            // Find template by current language
            $sLangDir = $sDir . $this->Lang_GetLang();
            if (is_dir($sLangDir)) {
                $sResult = $sLangDir . '/' . $sName;
                $bFound = true;
            }

            if (!$bFound) {
                // Find by aliases of current language
                if ($aAliases = $this->Lang_GetLangAliases()) {
                    foreach ($aAliases as $sAlias) {
                        if (is_dir($sLangDir = $sDir . $sAlias)) {
                            $sResult = $sLangDir . '/' . $sName;
                            $bFound = true;
                            break;
                        }
                    }
                }
            }

            if (!$bFound) {
                // Find template by default language
                $sLangDir = $sDir . $this->Lang_GetDefaultLang();
                if (is_dir($sLangDir)) {
                    $sResult = $sLangDir . '/' . $sName;
                    $bFound = true;
                }
            }

            if (!$bFound) {
                // Find by aliases of default language
                if ($aAliases = $this->Lang_GetDefaultLangAliases()) {
                    foreach ($aAliases as $sAlias) {
                        if (is_dir($sLangDir = $sDir . $sAlias)) {
                            $sResult = $sLangDir . '/' . $sName;
                            $bFound = true;
                            break;
                        }
                    }
                }
            }

            if (!$bFound) {
                $sResult = $sDir . $this->Lang_GetLangDefault() . '/' . $sName;
            }

            if (is_dir($sResult)) {
                $this->Cache_Set($sResult, $sCacheKey, array(), 'P30D', 'tmp');
            }
        }

        return $sResult;
    }
}

// EOF
