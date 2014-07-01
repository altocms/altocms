 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<div class="comments comment-list">
    {foreach $aComments as $oComment}

        {$oUser=$oComment->getUser()}
        {$oTopic=$oComment->getTarget()}

        {if $oUser AND $oTopic}
            {$oBlog=$oTopic->getBlog()}

            {include file="comments/comment.single.tpl" bCommentList=true}

        {/if}
    {/foreach}
</div>

{include file='commons/common.pagination.tpl' aPaging=$aPaging}
