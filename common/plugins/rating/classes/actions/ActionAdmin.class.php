<?php

/**
 * ActionAdmin.class.php
 * Файл экшена плагина Rating
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 *              Является частью плагина Rating
 * @version     0.0.1 от 11.11.2014 12:41
 */
class PluginRating_ActionAdmin extends PluginRating_Inherit_ActionAdmin {

    /**
     * Абстрактный метод регистрации евентов.
     * В нём необходимо вызывать метод AddEvent($sEventName,$sEventFunction)
     * Например:
     *      $this->AddEvent('index', 'EventIndex');
     *      $this->AddEventPreg('/^admin$/i', '/^\d+$/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventAdminBlog');
     */
    protected function RegisterEvent() {
        parent::RegisterEvent();
        $this->AddEvent('settings-rating', 'EventRating');
    }

    public function EventRating() {

        $this->sMainMenuItem = 'settings';

        E::ModuleViewer()->Assign('sPageTitle', E::ModuleLang()->Get('plugin.rating.admin_title'));
        E::ModuleViewer()->Assign('sMainMenuItem', 'settings');
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('plugin.rating.admin_title'));
        $this->SetTemplateAction('settings/rating');

        if (getRequest('submit_rating')) {

            $aData = array();

            $aData['rating.enabled'] = (bool)(getRequest('rating_enabled', FALSE));
            $aData['module.rating.blog.topic_rating_sum'] = (float)(getRequest('topic_rating_sum', FALSE));
            $aData['module.rating.blog.count_topic'] = (float)(getRequest('count_topic', FALSE));

            $aData['acl.vote.user.rating'] = (int)(getRequest('acl_vote_user_rating', 0));
            $aData['acl.vote.blog.rating'] = (int)(getRequest('acl_vote_blog_rating', 0));
            $aData['acl.vote.topic.rating'] = (int)(getRequest('acl_vote_topic_rating', 0));
            $aData['acl.vote.comment.rating'] = (int)(getRequest('acl_vote_comment_rating', 0));

            $aData['plugin.rating.user.vote'] = (bool)(getRequest('user_vote', FALSE));
            $aData['plugin.rating.user.dislike'] = (bool)(getRequest('user_dislike', FALSE));
            $aData['plugin.rating.user.min_change'] = (float)(getRequest('user_min_change', 0));
            $aData['plugin.rating.user.max_change'] = (float)(getRequest('user_max_change', 0));
            $aData['plugin.rating.user.max_rating'] = (float)(getRequest('user_max_rating', 0));
            $aData['plugin.rating.user.right_border'] = (float)(getRequest('user_right_border', 0));
            $aData['plugin.rating.user.left_border'] = (float)(getRequest('user_left_border', 0));
            $aData['plugin.rating.user.left_divider'] = (float)(getRequest('user_left_divider', 0));
            $aData['plugin.rating.user.mid_divider'] = (float)(getRequest('user_mid_divider', 0));
            $aData['plugin.rating.user.right_divider'] = (float)(getRequest('user_right_divider', 0));


            $aData['plugin.rating.blog.vote'] = (bool)(getRequest('blog_vote', FALSE));
            $aData['plugin.rating.blog.dislike'] = (bool)(getRequest('blog_dislike', FALSE));
            $aData['plugin.rating.blog.min_change'] = (float)(getRequest('blog_min_change', 0));
            $aData['plugin.rating.blog.max_change'] = (float)(getRequest('blog_max_change', 0));
            $aData['plugin.rating.blog.max_rating'] = (float)(getRequest('blog_max_rating', 0));
            $aData['plugin.rating.blog.right_border'] = (float)(getRequest('blog_right_border', 0));
            $aData['plugin.rating.blog.left_border'] = (float)(getRequest('blog_left_border', 0));
            $aData['plugin.rating.blog.left_divider'] = (float)(getRequest('blog_left_divider', 0));
            $aData['plugin.rating.blog.mid_divider'] = (float)(getRequest('blog_mid_divider', 0));
            $aData['plugin.rating.blog.right_divider'] = (float)(getRequest('blog_right_divider', 0));


            $aData['plugin.rating.comment.vote'] = (bool)(getRequest('comment_vote', FALSE));
            $aData['plugin.rating.comment.dislike'] = (bool)(getRequest('comment_dislike', FALSE));
            $aData['plugin.rating.comment.min_change'] = (float)(getRequest('comment_min_change', 0));
            $aData['plugin.rating.comment.max_change'] = (float)(getRequest('comment_max_change', 0));
            $aData['plugin.rating.comment.max_rating'] = (float)(getRequest('comment_max_rating', 0));
            $aData['plugin.rating.comment.right_border'] = (float)(getRequest('comment_right_border', 0));
            $aData['plugin.rating.comment.left_border'] = (float)(getRequest('comment_left_border', 0));
            $aData['plugin.rating.comment.left_divider'] = (float)(getRequest('comment_left_divider', 0));
            $aData['plugin.rating.comment.mid_divider'] = (float)(getRequest('comment_mid_divider', 0));
            $aData['plugin.rating.comment.right_divider'] = (float)(getRequest('comment_right_divider', 0));


            $aData['plugin.rating.topic.vote'] = (bool)(getRequest('topic_vote', FALSE));
            $aData['plugin.rating.topic.dislike'] = (bool)(getRequest('topic_dislike', FALSE));
            $aData['plugin.rating.topic.min_change'] = (float)(getRequest('topic_min_change', 0));
            $aData['plugin.rating.topic.max_change'] = (float)(getRequest('topic_max_change', 0));
            $aData['plugin.rating.topic.max_rating'] = (float)(getRequest('topic_max_rating', 0));
            $aData['plugin.rating.topic.right_border'] = (float)(getRequest('topic_right_border', 0));
            $aData['plugin.rating.topic.left_border'] = (float)(getRequest('topic_left_border', 0));
            $aData['plugin.rating.topic.left_divider'] = (float)(getRequest('topic_left_divider', 0));
            $aData['plugin.rating.topic.mid_divider'] = (float)(getRequest('topic_mid_divider', 0));
            $aData['plugin.rating.topic.right_divider'] = (float)(getRequest('topic_right_divider', 0));
            $aData['plugin.rating.topic.auth_coef'] = (float)(getRequest('topic_auth_coef', 0));


            $aData['plugin.rating.rating.vote'] = (bool)(getRequest('rating_topic_vote', FALSE));
            $aData['plugin.rating.rating.topic_border_1'] = (float)(getRequest('rating_topic_border_1', 0));
            $aData['plugin.rating.rating.topic_border_2'] = (float)(getRequest('rating_topic_border_2', 0));
            $aData['plugin.rating.rating.topic_border_3'] = (float)(getRequest('rating_topic_border_3', 0));
            $aData['plugin.rating.rating.topic_k1'] = (float)(getRequest('rating_topic_k1', 0));
            $aData['plugin.rating.rating.topic_k2'] = (float)(getRequest('rating_topic_k2', 0));
            $aData['plugin.rating.rating.topic_k3'] = (float)(getRequest('rating_topic_k3', 0));
            $aData['plugin.rating.rating.topic_k4'] = (float)(getRequest('rating_topic_k4', 0));


            Config::WriteCustomConfig($aData);


            $_REQUEST['rating_enabled'] = $aData['rating.enabled'];
            $_REQUEST['topic_rating_sum'] = $aData['module.rating.blog.topic_rating_sum'];
            $_REQUEST['count_topic'] = $aData['module.rating.blog.count_topic'];

            $_REQUEST['acl_vote_user_rating'] = $aData['acl.vote.user.rating'];
            $_REQUEST['acl_vote_blog_rating'] = $aData['acl.vote.blog.rating'];
            $_REQUEST['acl_vote_topic_rating'] = $aData['acl.vote.topic.rating'];
            $_REQUEST['acl_vote_comment_rating'] = $aData['acl.vote.comment.rating'];

            $_REQUEST['user_vote'] = $aData['plugin.rating.user.vote'];
            $_REQUEST['user_dislike'] = $aData['plugin.rating.user.dislike'];
            $_REQUEST['user_min_change'] = $aData['plugin.rating.user.min_change'];
            $_REQUEST['user_max_change'] = $aData['plugin.rating.user.max_change'];
            $_REQUEST['user_max_rating'] = $aData['plugin.rating.user.max_rating'];
            $_REQUEST['user_right_border'] = $aData['plugin.rating.user.right_border'];
            $_REQUEST['user_left_border'] = $aData['plugin.rating.user.left_border'];
            $_REQUEST['user_left_divider'] = $aData['plugin.rating.user.left_divider'];
            $_REQUEST['user_mid_divider'] = $aData['plugin.rating.user.mid_divider'];
            $_REQUEST['user_right_divider'] = $aData['plugin.rating.user.right_divider'];

            $_REQUEST['blog_vote'] = $aData['plugin.rating.blog.vote'];
            $_REQUEST['blog_dislike'] = $aData['plugin.rating.blog.dislike'];
            $_REQUEST['blog_min_change'] = $aData['plugin.rating.blog.min_change'];
            $_REQUEST['blog_max_change'] = $aData['plugin.rating.blog.max_change'];
            $_REQUEST['blog_max_rating'] = $aData['plugin.rating.blog.max_rating'];
            $_REQUEST['blog_right_border'] = $aData['plugin.rating.blog.right_border'];
            $_REQUEST['blog_left_border'] = $aData['plugin.rating.blog.left_border'];
            $_REQUEST['blog_left_divider'] = $aData['plugin.rating.blog.left_divider'];
            $_REQUEST['blog_mid_divider'] = $aData['plugin.rating.blog.mid_divider'];
            $_REQUEST['blog_right_divider'] = $aData['plugin.rating.blog.right_divider'];

            $_REQUEST['comment_vote'] = $aData['plugin.rating.comment.vote'];
            $_REQUEST['comment_dislike'] = $aData['plugin.rating.comment.dislike'];
            $_REQUEST['comment_min_change'] = $aData['plugin.rating.comment.min_change'];
            $_REQUEST['comment_max_change'] = $aData['plugin.rating.comment.max_change'];
            $_REQUEST['comment_max_rating'] = $aData['plugin.rating.comment.max_rating'];
            $_REQUEST['comment_right_border'] = $aData['plugin.rating.comment.right_border'];
            $_REQUEST['comment_left_border'] = $aData['plugin.rating.comment.left_border'];
            $_REQUEST['comment_left_divider'] = $aData['plugin.rating.comment.left_divider'];
            $_REQUEST['comment_mid_divider'] = $aData['plugin.rating.comment.mid_divider'];
            $_REQUEST['comment_right_divider'] = $aData['plugin.rating.comment.right_divider'];

            $_REQUEST['topic_vote'] = $aData['plugin.rating.topic.vote'];
            $_REQUEST['topic_dislike'] = $aData['plugin.rating.topic.dislike'];
            $_REQUEST['topic_min_change'] = $aData['plugin.rating.topic.min_change'];
            $_REQUEST['topic_max_change'] = $aData['plugin.rating.topic.max_change'];
            $_REQUEST['topic_max_rating'] = $aData['plugin.rating.topic.max_rating'];
            $_REQUEST['topic_right_border'] = $aData['plugin.rating.topic.right_border'];
            $_REQUEST['topic_left_border'] = $aData['plugin.rating.topic.left_border'];
            $_REQUEST['topic_left_divider'] = $aData['plugin.rating.topic.left_divider'];
            $_REQUEST['topic_mid_divider'] = $aData['plugin.rating.topic.mid_divider'];
            $_REQUEST['topic_right_divider'] = $aData['plugin.rating.topic.right_divider'];
            $_REQUEST['topic_auth_coef'] = $aData['plugin.rating.topic.auth_coef'];

            $_REQUEST['rating_vote'] = $aData['plugin.rating.rating.vote'];
            $_REQUEST['rating_topic_border_1'] = $aData['plugin.rating.rating.topic_border_1'];
            $_REQUEST['rating_topic_border_2'] = $aData['plugin.rating.rating.topic_border_2'];
            $_REQUEST['rating_topic_border_3'] = $aData['plugin.rating.rating.topic_border_3'];
            $_REQUEST['rating_topic_k1'] = $aData['plugin.rating.rating.topic_k1'];
            $_REQUEST['rating_topic_k2'] = $aData['plugin.rating.rating.topic_k2'];
            $_REQUEST['rating_topic_k3'] = $aData['plugin.rating.rating.topic_k3'];
            $_REQUEST['rating_topic_k4'] = $aData['plugin.rating.rating.topic_k4'];


            return FALSE;

        }

        $_REQUEST['rating_enabled'] = Config::Get('rating.enabled');
        $_REQUEST['topic_rating_sum'] = Config::Get('module.rating.blog.topic_rating_sum');
        $_REQUEST['count_topic'] = Config::Get('module.rating.blog.count_topic');

        $_REQUEST['acl_vote_user_rating'] = Config::Get('acl.vote.user.rating');
        $_REQUEST['acl_vote_blog_rating'] = Config::Get('acl.vote.blog.rating');
        $_REQUEST['acl_vote_topic_rating'] = Config::Get('acl.vote.topic.rating');
        $_REQUEST['acl_vote_comment_rating'] = Config::Get('acl.vote.comment.rating');

        $_REQUEST['user_vote'] = Config::Get('plugin.rating.user.vote');
        $_REQUEST['user_dislike'] = Config::Get('plugin.rating.user.dislike');
        $_REQUEST['user_min_change'] = Config::Get('plugin.rating.user.min_change');
        $_REQUEST['user_max_change'] = Config::Get('plugin.rating.user.max_change');
        $_REQUEST['user_max_rating'] = Config::Get('plugin.rating.user.max_rating');
        $_REQUEST['user_right_border'] = Config::Get('plugin.rating.user.right_border');
        $_REQUEST['user_left_border'] = Config::Get('plugin.rating.user.left_border');
        $_REQUEST['user_left_divider'] = Config::Get('plugin.rating.user.left_divider');
        $_REQUEST['user_mid_divider'] = Config::Get('plugin.rating.user.mid_divider');
        $_REQUEST['user_right_divider'] = Config::Get('plugin.rating.user.right_divider');

        $_REQUEST['blog_vote'] = Config::Get('plugin.rating.blog.vote');
        $_REQUEST['blog_dislike'] = Config::Get('plugin.rating.blog.dislike');
        $_REQUEST['blog_min_change'] = Config::Get('plugin.rating.blog.min_change');
        $_REQUEST['blog_max_change'] = Config::Get('plugin.rating.blog.max_change');
        $_REQUEST['blog_max_rating'] = Config::Get('plugin.rating.blog.max_rating');
        $_REQUEST['blog_right_border'] = Config::Get('plugin.rating.blog.right_border');
        $_REQUEST['blog_left_border'] = Config::Get('plugin.rating.blog.left_border');
        $_REQUEST['blog_left_divider'] = Config::Get('plugin.rating.blog.left_divider');
        $_REQUEST['blog_mid_divider'] = Config::Get('plugin.rating.blog.mid_divider');
        $_REQUEST['blog_right_divider'] = Config::Get('plugin.rating.blog.right_divider');

        $_REQUEST['comment_vote'] = Config::Get('plugin.rating.comment.vote');
        $_REQUEST['comment_dislike'] = Config::Get('plugin.rating.comment.dislike');
        $_REQUEST['comment_min_change'] = Config::Get('plugin.rating.comment.min_change');
        $_REQUEST['comment_max_change'] = Config::Get('plugin.rating.comment.max_change');
        $_REQUEST['comment_max_rating'] = Config::Get('plugin.rating.comment.max_rating');
        $_REQUEST['comment_right_border'] = Config::Get('plugin.rating.comment.right_border');
        $_REQUEST['comment_left_border'] = Config::Get('plugin.rating.comment.left_border');
        $_REQUEST['comment_left_divider'] = Config::Get('plugin.rating.comment.left_divider');
        $_REQUEST['comment_mid_divider'] = Config::Get('plugin.rating.comment.mid_divider');
        $_REQUEST['comment_right_divider'] = Config::Get('plugin.rating.comment.right_divider');

        $_REQUEST['topic_vote'] = Config::Get('plugin.rating.topic.vote');
        $_REQUEST['topic_dislike'] = Config::Get('plugin.rating.topic.dislike');
        $_REQUEST['topic_min_change'] = Config::Get('plugin.rating.topic.min_change');
        $_REQUEST['topic_max_change'] = Config::Get('plugin.rating.topic.max_change');
        $_REQUEST['topic_max_rating'] = Config::Get('plugin.rating.topic.max_rating');
        $_REQUEST['topic_right_border'] = Config::Get('plugin.rating.topic.right_border');
        $_REQUEST['topic_left_border'] = Config::Get('plugin.rating.topic.left_border');
        $_REQUEST['topic_left_divider'] = Config::Get('plugin.rating.topic.left_divider');
        $_REQUEST['topic_mid_divider'] = Config::Get('plugin.rating.topic.mid_divider');
        $_REQUEST['topic_right_divider'] = Config::Get('plugin.rating.topic.right_divider');
        $_REQUEST['topic_auth_coef'] = Config::Get('plugin.rating.topic.auth_coef');

        $_REQUEST['rating_vote'] = Config::Get('plugin.rating.rating.vote');
        $_REQUEST['rating_topic_border_1'] = Config::Get('plugin.rating.rating.topic_border_1');
        $_REQUEST['rating_topic_border_2'] = Config::Get('plugin.rating.rating.topic_border_2');
        $_REQUEST['rating_topic_border_3'] = Config::Get('plugin.rating.rating.topic_border_3');
        $_REQUEST['rating_topic_k1'] = Config::Get('plugin.rating.rating.topic_k1');
        $_REQUEST['rating_topic_k2'] = Config::Get('plugin.rating.rating.topic_k2');
        $_REQUEST['rating_topic_k3'] = Config::Get('plugin.rating.rating.topic_k3');
        $_REQUEST['rating_topic_k4'] = Config::Get('plugin.rating.rating.topic_k4');


        return FALSE;

    }

}