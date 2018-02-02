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
class PluginRating_ModuleRating extends PluginRating_Inherit_ModuleRating {

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

        if (!C::Get('plugin.rating.comment.vote')) {
            return 0;
        }
        if (!C::Get('plugin.rating.comment.dislike') && $iValue < 0) {
            return 0;
        }
        /**
         * Устанавливаем рейтинг комментария
         */
        $oComment->setRating($oComment->getRating() + $iValue);
        /**
         * Начисляем силу автору коммента, используя логарифмическое распределение
         */
        $nSkill = $oUser->getSkill();
        $iMinSize = C::Get('plugin.rating.comment.min_change');//0.004;
        $iMaxSize = C::Get('plugin.rating.comment.max_change');//0.5;
        $iSizeRange = $iMaxSize - $iMinSize;
        $iMinCount = log(0 + 1);
        $iMaxCount = log(C::Get('plugin.rating.comment.max_rating') + 1);//500
        $iCountRange = $iMaxCount - $iMinCount;
        if ($iCountRange == 0) {
            $iCountRange = 1;
        }
        if ($nSkill > C::Get('plugin.rating.comment.left_border') and $nSkill < C::Get('plugin.rating.comment.right_border')) {//50-200
            $nSkillNew = $nSkill / C::Get('plugin.rating.comment.mid_divider');//70
        } elseif ($nSkill >= C::Get('plugin.rating.comment.right_border')) {//200
            $nSkillNew = $nSkill / C::Get('plugin.rating.comment.right_divider');//10
        } else {
            $nSkillNew = $nSkill / C::Get('plugin.rating.comment.left_divider');//130
        }
        $iDelta = $iMinSize + (log($nSkillNew + 1) - $iMinCount) * ($iSizeRange / $iCountRange);
        /**
         * Сохраняем силу
         */
        $oUserComment = E::Module('User')->GetUserById($oComment->getUserId());
        $iSkillNew = $oUserComment->getSkill() + $iValue * $iDelta;
        $iSkillNew = ($iSkillNew < 0) ? 0 : $iSkillNew;
        $oUserComment->setSkill($iSkillNew);
        E::Module('User')->Update($oUserComment);

        return $iValue;
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

        $iDeltaRating = 0;
        $nSkill = $oUser->getSkill();

        if (C::Get('plugin.rating.topic.vote') && (C::Get('plugin.rating.topic.dislike') || (!C::Get('plugin.rating.topic.dislike') && $iValue > 0))) {
            /**
             * Устанавливаем рейтинг топика
             */
            $iDeltaRating = $iValue * C::Get('plugin.rating.rating.topic_k1');//1
            if ($nSkill >= C::Get('plugin.rating.rating.topic_border_1') && $nSkill < C::Get('plugin.rating.rating.topic_border_2')) { // 100-250
                $iDeltaRating = $iValue * C::Get('plugin.rating.rating.topic_k2');//2
            } elseif ($nSkill >= C::Get('plugin.rating.rating.topic_border_2') && $nSkill < C::Get('plugin.rating.rating.topic_border_3')) { //250-400
                $iDeltaRating = $iValue * C::Get('plugin.rating.rating.topic_k3');//3
            } elseif ($nSkill >= C::Get('plugin.rating.rating.topic_border_3')) { //400
                $iDeltaRating = $iValue * C::Get('plugin.rating.rating.topic_k4');//4
            }
            $oTopic->setRating($oTopic->getRating() + $iDeltaRating);
        }

        $iMinCount = log(0 + 1);
        $iCountRange = 1;
        if (C::Get('plugin.rating.rating.vote') && (C::Get('plugin.rating.topic.dislike') || (!C::Get('plugin.rating.topic.dislike') && $iValue > 0))) {
            /**
             * Начисляем силу и рейтинг автору топика, используя логарифмическое распределение
             */
            $iMinSize = C::Get('plugin.rating.topic.min_change');//0.1;
            $iMaxSize = C::Get('plugin.rating.topic.max_change');//8;
            $iSizeRange = $iMaxSize - $iMinSize;
            $iMaxCount = log(C::Get('plugin.rating.topic.max_rating') + 1);
            $iCountRange = $iMaxCount - $iMinCount;
            if ($iCountRange == 0) {
                $iCountRange = 1;
            }
            if ($nSkill > C::Get('plugin.rating.topic.left_border') && $nSkill < C::Get('plugin.rating.topic.right_border')) {//200
                $nSkillNew = $nSkill / C::Get('plugin.rating.topic.mid_divider');//70;
            } elseif ($nSkill >= C::Get('plugin.rating.topic.right_border')) {//200
                $nSkillNew = $nSkill / C::Get('plugin.rating.topic.right_divider');//10;
            } else {
                $nSkillNew = $nSkill / C::Get('plugin.rating.topic.left_divider');//100;
            }
            $iDelta = $iMinSize + (log($nSkillNew + 1) - $iMinCount) * ($iSizeRange / $iCountRange);
            /**
             * Сохраняем силу и рейтинг
             */
            $oUserTopic = E::Module('User')->GetUserById($oTopic->getUserId());
            $iSkillNew = $oUserTopic->getSkill() + $iValue * $iDelta;
            $iSkillNew = ($iSkillNew < 0) ? 0 : $iSkillNew;
            $oUserTopic->setSkill($iSkillNew);
            $oUserTopic->setRating($oUserTopic->getRating() + $iValue * $iDelta / C::Get('plugin.rating.topic.auth_coef'));//2.73
            E::Module('User')->Update($oUserTopic);
        }
        if ($nSkill > C::Get('plugin.rating.topic.left_border') && $nSkill < C::Get('plugin.rating.topic.right_border')) {//200
            $nSkillNew = $nSkill / C::Get('plugin.rating.topic.mid_divider');//70;
        } elseif ($nSkill >= C::Get('plugin.rating.topic.right_border')) {//200
            $nSkillNew = $nSkill / C::Get('plugin.rating.topic.right_divider');//10;
        } else {
            $nSkillNew = $nSkill / C::Get('plugin.rating.topic.left_divider');//100;
        }
        $iDelta = $iMinSize + (log($nSkillNew + 1) - $iMinCount) * ($iSizeRange / $iCountRange);
        /**
         * Сохраняем силу и рейтинг
         */
        $oUserTopic = E::Module('User')->GetUserById($oTopic->getUserId());
        $iSkillNew = $oUserTopic->getSkill() + $iValue * $iDelta;
        $iSkillNew = ($iSkillNew < 0) ? 0 : $iSkillNew;
        $oUserTopic->setSkill($iSkillNew);
        $oUserTopic->setRating($oUserTopic->getRating() + $iValue * $iDelta / C::Get('plugin.rating.topic.auth_coef'));//2.73
        E::Module('User')->Update($oUserTopic);

        return $iDeltaRating;
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
    public function VoteBlog($oUser, $oBlog, $iValue)
    {
        if (!C::Get('plugin.rating.blog.vote')) {
            return 0;
        }
        if (!C::Get('plugin.rating.blog.dislike') && $iValue < 0) {
            return 0;
        }
        /**
         * Устанавливаем рейтинг блога, используя логарифмическое распределение
         */
        $nSkill = $oUser->getSkill();
        $iMinSize = C::Get('plugin.rating.blog.min_change');//1.13;
        $iMaxSize = C::Get('plugin.rating.blog.max_change');//15;
        $iSizeRange = $iMaxSize - $iMinSize;
        $iMinCount = log(0 + 1);
        $iMaxCount = log(C::Get('plugin.rating.blog.max_rating') + 1);//500
        $iCountRange = $iMaxCount - $iMinCount;
        if ($iCountRange == 0) {
            $iCountRange = 1;
        }
        if ($nSkill > C::Get('plugin.rating.blog.left_border') && $nSkill < C::Get('plugin.rating.blog.right_border')) {//50-200
            $nSkillNew = $nSkill / C::Get('plugin.rating.blog.mid_divider');//20;
        } elseif ($nSkill >= C::Get('plugin.rating.blog.right_border')) {//200
            $nSkillNew = $nSkill / C::Get('plugin.rating.blog.right_divider');//10;
        } else {
            $nSkillNew = $nSkill / C::Get('plugin.rating.blog.left_divider');//50;
        }
        $iDelta = $iMinSize + (log($nSkillNew + 1) - $iMinCount) * ($iSizeRange / $iCountRange);
        /**
         * Сохраняем рейтинг
         */
        $oBlog->setRating($oBlog->getRating() + $iValue * $iDelta);

        return $iValue * $iDelta;
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

        if (!C::Get('plugin.rating.user.vote')) {
            return 0;
        }
        if (!C::Get('plugin.rating.user.dislike') && $iValue < 0) {
            return 0;
        }
        /**
         * Начисляем силу и рейтинг юзеру, используя логарифмическое распределение
         */
        $nSkill = $oUser->getSkill();
        $iMinSize = C::Get('plugin.rating.user.min_change');//0.42;
        $iMaxSize = C::Get('plugin.rating.user.max_change');//3.2;
        $iSizeRange = $iMaxSize - $iMinSize;
        $iMinCount = log(0 + 1);
        $iMaxCount = log(C::Get('plugin.rating.user.max_rating') + 1); // 500
        $iCountRange = $iMaxCount - $iMinCount;
        if ($iCountRange == 0) {
            $iCountRange = 1;
        }
        if ($nSkill > C::Get('plugin.rating.user.left_border') and $nSkill < C::Get('plugin.rating.user.right_border')) { // 50-200
            $nSkillNew = $nSkill / C::Get('plugin.rating.user.mid_divider'); //70
        } elseif ($nSkill >= C::Get('plugin.rating.user.right_border')) { // 200
            $nSkillNew = $nSkill / C::Get('plugin.rating.user.right_divider'); //2
        } else {
            $nSkillNew = $nSkill / C::Get('plugin.rating.user.left_divider'); //40
        }
        $iDelta = $iMinSize + (log($nSkillNew + 1) - $iMinCount) * ($iSizeRange / $iCountRange);
        /**
         * Определяем новый рейтинг
         */
        $iRatingNew = $oUserTarget->getRating() + $iValue * $iDelta;
        $oUserTarget->setRating($iRatingNew);

        return $iValue * $iDelta;
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

        return true;
    }

}

// EOF