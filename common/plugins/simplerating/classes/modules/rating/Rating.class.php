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
class PluginSimplerating_ModuleRating extends PluginSimplerating_Inherit_ModuleRating {

    /**
     * Инициализация модуля
     *
     */
    public function Init() {

    }

    /**
     * Расчет рейтинга при голосовании за комментарий
     *
     * @param ModuleUser_EntityUser $oUser Объект пользователя, который голосует
     * @param ModuleComment_EntityComment $oComment Объект комментария
     * @param int $iValue
     *
     * @return int
     */
    public function VoteComment($oUser, $oComment, $iValue) {
        /**
         * Устанавливаем рейтинг комментария
         */
        $oComment->setRating($oComment->getRating() + $iValue);
        /**
         * Начисляем рейтинг автору комментария
         */
        $oUserComment = $this->User_GetUserById($oComment->getUserId());
        $oUserComment->setRating((float)$oUserComment->getRating() + (float)C::Get('plugin.simplerating.comment_user_add'));
        $this->User_Update($oUserComment);
        /**
         * Убавляем рейтинг голосующего, если нужно
         */
        if (C::Get('plugin.simplerating.comment_user_remove')) {
            $oUser->setRating((float)$oUser->getRating() + (float)C::Get('plugin.simplerating.comment_user_remove'));
            $this->User_Update($oUser);
        }

        return $iValue;
    }

    /**
     * Расчет рейтинга и силы при гоосовании за топик
     *
     * @param ModuleUser_EntityUser $oUser Объект пользователя, который голосует
     * @param ModuleTopic_EntityTopic $oTopic Объект топика
     * @param int $iValue
     *
     * @return int
     */
    public function VoteTopic($oUser, $oTopic, $iValue) {

        /**
         * Устанавливаем рейтинг топика
         */
        $oTopic->setRating((float)$oTopic->getRating() + (float)C::Get('plugin.simplerating.topic_add'));
        /**
         * Устанавливаем рейтинг автора
         */
        $oUserTopic = $this->User_GetUserById($oTopic->getUserId());
        $oUserTopic->setRating((float)$oUserTopic->getRating() + (float)C::Get('plugin.simplerating.topic_user_add'));
        $this->User_Update($oUserTopic);
        /**
         * Убавляем рейтинг голосующего, если нужно
         */
        if (C::Get('plugin.simplerating.topic_user_remove')) {
            $oUser->setRating((float)$oUser->getRating() + (float)C::Get('plugin.simplerating.topic_user_remove'));
            $this->User_Update($oUser);
        }


        return (float)C::Get('plugin.simplerating.topic_add');
    }

    /**
     * Расчет рейтинга и силы при голосовании за блог
     *
     * @param ModuleUser_EntityUser $oUser Объект пользователя, который голосует
     * @param ModuleBlog_EntityBlog $oBlog Объект блога
     * @param int $iValue
     *
     * @return int
     */
    public function VoteBlog($oUser, $oBlog, $iValue) {

        /**
         * Устанавливаем рейтинг блога
         */
        $oBlog->setRating((float)$oBlog->getRating() + (float)C::Get('plugin.simplerating.blog_add'));
        /**
         * Убавляем рейтинг голосующего, если нужно
         */
        if (C::Get('plugin.simplerating.blog_user_remove')) {
            $oUser->setRating((float)$oUser->getRating() + (float)C::Get('plugin.simplerating.blog_user_remove'));
            $this->User_Update($oUser);
        }


        return (float)C::Get('plugin.simplerating.blog_add');

    }

    /**
     * Расчет рейтинга и силы при голосовании за пользователя
     *
     * @param ModuleUser_EntityUser $oUser
     * @param ModuleUser_EntityUser $oUserTarget
     * @param int $iValue
     *
     * @return float
     */
    public function VoteUser($oUser, $oUserTarget, $iValue) {
        /**
         * Начисляем рейтинг пользователя
         */
        $oUserTarget->setRating((float)$oUserTarget->getRating() + (float)C::Get('plugin.simplerating.user_add'));
        /**
         * Убавляем рейтинг голосующего, если нужно
         */
        if (C::Get('plugin.simplerating.user_remove')) {
            $oUser->setRating((float)$oUser->getRating() + (float)C::Get('plugin.simplerating.user_remove'));
            $this->User_Update($oUser);
        }

        return (float)C::Get('plugin.simplerating.user_add');

    }


    /**
     * Расчет рейтинга блога
     *
     * @return bool
     */
    public function RecalculateBlogRating() {

        /*
         * Получаем статистику
         */
        $aBlogStat = $this->Blog_GetBlogsData(array('personal'));

        foreach ($aBlogStat as $oBlog) {

            $fRating = 0;

            //*** Учет суммы голосов за топики с весовым коэффициентом
            $fRating = $fRating + Config::Get('module.rating.blog.topic_rating_sum') * $oBlog->getSumRating();

            //*** Учет количества топиков с весовым коэффициентом
            $fRating = $fRating + Config::Get('module.rating.blog.count_users') * $oBlog->getCountUser();

            //*** Учет количества топиков с весовым коэффициентом
            $fRating = $fRating + Config::Get('module.rating.blog.count_topic') * $oBlog->getCountTopic();

            $oBlog->setRating($fRating);
            $this->Blog_UpdateBlog($oBlog);

        }

        return TRUE;
    }

}