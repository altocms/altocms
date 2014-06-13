{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="topics"}
{/block}

{block name="layout_content"}

{include file="topics/topic.show.tpl"}

{include file="comments/comment.tree.tpl"
    iTargetId=$oTopic->getId()
    iAuthorId=$oTopic->getUserId()
    sAuthorNotice=$aLang.topic_author
    sTargetType="topic"
    iCountComment=$oTopic->getCountComment()
    sDateReadLast=$oTopic->getDateRead()
    bAllowToComment=!$oTopic->getForbidComment()
    sNoticeNotAllow=$aLang.topic_comment_notallow
    sNoticeCommentAdd=$aLang.topic_comment_add
    bAllowSubscribe=true
    oSubscribeComment=$oTopic->getSubscribeNewComment()
    oTrackComment=$oTopic->getTrackNewComment()
    aPagingCmt=$aPagingCmt
}

{/block}
