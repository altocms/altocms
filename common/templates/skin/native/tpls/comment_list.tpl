{**
 * Список комментариев
 *
 * @styles css/comments.css
 *}

{foreach $aComments as $oComment}
    {include file='comment.tpl' bList=true}
{/foreach}
