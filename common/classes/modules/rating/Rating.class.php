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
 * Модуль управления рейтингами и силой
 *
 * @package modules.rating
 * @since   1.0
 */
class ModuleRating extends Module {

    /**
     * Инициализация модуля
     *
     */
    public function Init() {

    }

    /**
     * Расчет рейтинга при голосовании за комментарий
     *
     * @param ModuleUser_EntityUser       $oUser       Объект пользователя, который голосует
     * @param ModuleComment_EntityComment $oComment    Объект комментария
     * @param int                         $iValue
     *
     * @return int
     */
    public function VoteComment($oUser, $oComment, $iValue) {
        return 0;
    }

    /**
     * Расчет рейтинга и силы при гоосовании за топик
     *
     * @param ModuleUser_EntityUser   $oUser     Объект пользователя, который голосует
     * @param ModuleTopic_EntityTopic $oTopic    Объект топика
     * @param int                     $iValue
     *
     * @return int
     */
    public function VoteTopic($oUser, $oTopic, $iValue) {
        return 0;
    }

    /**
     * Расчет рейтинга и силы при голосовании за блог
     *
     * @param ModuleUser_EntityUser $oUser    Объект пользователя, который голосует
     * @param ModuleBlog_EntityBlog $oBlog    Объект блога
     * @param int                   $iValue
     *
     * @return int
     */
    public function VoteBlog($oUser, $oBlog, $iValue) {
        return 0;
    }

    /**
     * Расчет рейтинга и силы при голосовании за пользователя
     *
     * @param ModuleUser_EntityUser $oUser
     * @param ModuleUser_EntityUser $oUserTarget
     * @param int                   $iValue
     *
     * @return float
     */
    public function VoteUser($oUser, $oUserTarget, $iValue) {
        return 0;
    }


    /**
     * Расчет рейтинга блога
     *
     * @return bool
     */
    public function RecalculateBlogRating() {
        return 0;
    }

}

// EOF