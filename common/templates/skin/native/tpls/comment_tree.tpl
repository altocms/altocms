{**
 * Комментарии
 *
 * @styles css/comments.css
 *}

{* Добавляем в тулбар кнопку обновления комментариев *}
{wgroup_add name='toolbar' widget='toolbar.comment.tpl'
    aPagingCmt=$aPagingCmt
    iTargetId=$iTargetId
    sTargetType=$sTargetType
    iMaxIdComment=$iMaxIdComment
}


{hook run='comment_tree_begin' iTargetId=$iTargetId sTargetType=$sTargetType}


<div class="comments" id="comments">
    <header class="comments-header">
        <h3>
            {$aLang.comments} <span id="count-comments">{$iCountComment}</span>
        </h3>

        {* Подписка на комментарии *}
        {if $bAllowSubscribe and $oUserCurrent}
        {*<label class="comments-subscribe">
            <input
                type="checkbox"
                id="comment_subscribe"
                class="input-checkbox"
                onchange="ls.subscribe.toggle('{$sTargetType}_new_comment','{$iTargetId}','',this.checked);"
                {if $oSubscribeComment and $oSubscribeComment->getStatus()}checked{/if}>
            {$aLang.comment_subscribe}
        </label>*}

            {$aLang.comment_whatch}
            <input {if $oTrackComment and $oTrackComment->getStatus()}checked="checked"{/if} type="checkbox"
                   id="comment_track" class="input-checkbox"
                   onchange="ls.subscribe.tracktoggle('{$sTargetType}_new_comment','{$iTargetId}',this.checked);">
            <label for="comment_track">{$aLang.comment_track}</label>
            <input {if $oSubscribeComment and $oSubscribeComment->getStatus()}checked="checked"{/if} type="checkbox"
                   id="comment_subscribe" class="input-checkbox"
                   onchange="ls.subscribe.toggle('{$sTargetType}_new_comment','{$iTargetId}','',this.checked);">
            <label for="comment_subscribe">{$aLang.comment_subscribe}</label>
        {/if}

        <a name="comments"></a>
    </header>

    {**
     * Комментарии
     *}
    {$iCurrentLevel = -1}
    {$iMaxLevel = Config::Get('module.comment.max_tree')}

    {foreach $aComments as $oComment}
    {$iCommentLevel = $oComment->getLevel()}

    {if $iCommentLevel > $iMaxLevel}
        {$iCommentLevel = $iMaxLevel}
    {/if}

    {if $iCurrentLevel > $iCommentLevel}
    {section name=closelist1 loop=$iCurrentLevel - $iCommentLevel + 1}</div>{/section}
    {elseif $iCurrentLevel == $iCommentLevel && ! $oComment@first}
    </div>
    {/if}

<div class="comment-wrapper" id="comment_wrapper_id_{$oComment->getId()}">

{include file='comment.tpl'}

{$iCurrentLevel = $iCommentLevel}

{if $oComment@last}
    {section name=closelist2 loop=$iCurrentLevel + 1}</div>{/section}
{/if}
{/foreach}
</div>


{**
 * Страницы
 *}
{include file='comment_pagination.tpl' aPagingCmt=$aPagingCmt}

{hook run='comment_tree_end' iTargetId=$iTargetId sTargetType=$sTargetType}


{**
 * Форма добавления комментария
 *}
{if $bAllowNewComment}
    {$sNoticeNotAllow}
{else}
    {if $oUserCurrent}
    {* Подключение редактора *}
        {include file='forms/editor.init.tpl' sEditorType='comment'}

    {* Ссылка открывающая форму *}
        <h4 class="comment-reply-header" id="comment_id_0">
            <a href="#" class="link-dotted"
               onclick="ls.comments.toggleCommentForm(0); return false;">{$sNoticeCommentAdd}</a>
        </h4>
    {* Форма *}
        <div id="reply" class="comment-reply">
            <form method="post" id="form_comment" onsubmit="return false;" enctype="multipart/form-data">
                {hook run='form_add_comment_begin'}

                <textarea name="comment_text" id="form_comment_text" class="js-editor input-width-full"></textarea>

                {hook run='form_add_comment_end'}

                <input type="hidden" name="reply" value="0" id="form_comment_reply"/>
                <input type="hidden" name="cmt_target_id" value="{$iTargetId}"/>

                <button type="submit" name="submit_comment"
                        id="comment-button-submit"
                        onclick="ls.comments.add('form_comment',{$iTargetId},'{$sTargetType}'); return false;"
                        class="button button-primary">{$aLang.comment_add}</button>
                <button type="button" onclick="ls.comments.preview();" class="button">{$aLang.comment_preview}</button>
            </form>
        </div>
    {else}
        {$aLang.comment_unregistered}
    {/if}
{/if}
