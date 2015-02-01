<?php

/**
 * ActionAdmin.class.php
 * Файл экшена плагина Rating
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 *              Является частью плагина Rating
 * @version     0.0.1 от 11.11.2014 12:41
 */
class PluginSimplerating_ActionAdmin extends PluginSimplerating_Inherit_ActionAdmin {

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

    /**
     * Админка брендирвоания
     */
    public function EventRating() {

        $this->sMainMenuItem = 'settings';

        E::ModuleViewer()->Assign('sPageTitle', E::ModuleLang()->Get('plugin.simplerating.admin_title'));
        E::ModuleViewer()->Assign('sMainMenuItem', 'settings');
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('plugin.simplerating.admin_title'));
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

            $aData['plugin.simplerating.user_remove'] = (float)(getRequest('user_remove', 0));
            $aData['plugin.simplerating.user_add'] = (float)(getRequest('user_add', 0));
            $aData['plugin.simplerating.comment_user_remove'] = (float)(getRequest('comment_user_remove', 0));
            $aData['plugin.simplerating.comment_user_add'] = (float)(getRequest('comment_user_add', 0));
            $aData['plugin.simplerating.blog_user_remove'] = (float)(getRequest('blog_user_remove', 0));
            $aData['plugin.simplerating.blog_add'] = (float)(getRequest('blog_add', 0));
            $aData['plugin.simplerating.topic_user_remove'] = (float)(getRequest('topic_user_remove', 0));
            $aData['plugin.simplerating.topic_user_add'] = (float)(getRequest('topic_user_add', 0));
            $aData['plugin.simplerating.topic_add'] = (float)(getRequest('topic_add', 0));

            Config::WriteCustomConfig($aData);


            $_REQUEST['rating_enabled'] = $aData['rating.enabled'];
            $_REQUEST['topic_rating_sum'] = $aData['module.rating.blog.topic_rating_sum'];
            $_REQUEST['count_topic'] = $aData['module.rating.blog.count_topic'];

            $_REQUEST['acl_vote_user_rating'] = $aData['acl.vote.user.rating'];
            $_REQUEST['acl_vote_blog_rating'] = $aData['acl.vote.blog.rating'];
            $_REQUEST['acl_vote_topic_rating'] = $aData['acl.vote.topic.rating'];
            $_REQUEST['acl_vote_comment_rating'] = $aData['acl.vote.comment.rating'];

            $_REQUEST['user_remove'] = $aData['plugin.simplerating.user_remove'];
            $_REQUEST['user_add'] = $aData['plugin.simplerating.user_add'];
            $_REQUEST['comment_user_remove'] = $aData['plugin.simplerating.comment_user_remove'];
            $_REQUEST['comment_user_add'] = $aData['plugin.simplerating.comment_user_add'];
            $_REQUEST['blog_user_remove'] = $aData['plugin.simplerating.blog_user_remove'];
            $_REQUEST['blog_add'] = $aData['plugin.simplerating.blog_add'];
            $_REQUEST['topic_user_remove'] = $aData['plugin.simplerating.topic_user_remove'];
            $_REQUEST['topic_user_add'] = $aData['plugin.simplerating.topic_user_add'];
            $_REQUEST['topic_add'] = $aData['plugin.simplerating.topic_add'];

            return FALSE;

        }

        $_REQUEST['rating_enabled'] = Config::Get('rating.enabled');
        $_REQUEST['topic_rating_sum'] = Config::Get('module.rating.blog.topic_rating_sum');
        $_REQUEST['count_topic'] = Config::Get('module.rating.blog.count_topic');

        $_REQUEST['acl_vote_user_rating'] = Config::Get('acl.vote.user.rating');
        $_REQUEST['acl_vote_blog_rating'] = Config::Get('acl.vote.blog.rating');
        $_REQUEST['acl_vote_topic_rating'] = Config::Get('acl.vote.topic.rating');
        $_REQUEST['acl_vote_comment_rating'] = Config::Get('acl.vote.comment.rating');

        $_REQUEST['user_remove'] = Config::Get('plugin.simplerating.user_remove');
        $_REQUEST['user_add'] = Config::Get('plugin.simplerating.user_add');
        $_REQUEST['comment_user_remove'] = Config::Get('plugin.simplerating.comment_user_remove');
        $_REQUEST['comment_user_add'] = Config::Get('plugin.simplerating.comment_user_add');
        $_REQUEST['blog_user_remove'] = Config::Get('plugin.simplerating.blog_user_remove');
        $_REQUEST['blog_add'] = Config::Get('plugin.simplerating.blog_add');
        $_REQUEST['topic_user_remove'] = Config::Get('plugin.simplerating.topic_user_remove');
        $_REQUEST['topic_user_add'] = Config::Get('plugin.simplerating.topic_user_add');
        $_REQUEST['topic_add'] = Config::Get('plugin.simplerating.topic_add');

        return FALSE;

    }

}