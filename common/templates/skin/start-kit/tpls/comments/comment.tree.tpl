{wgroup_add group='toolbar' name='toolbar_comment.tpl'
    aPagingCmt=$aPagingCmt
    iTargetId=$iTargetId
    sTargetType=$sTargetType
    iMaxIdComment=$iMaxIdComment
}

{hook run='comment_tree_begin' iTargetId=$iTargetId sTargetType=$sTargetType}

<div class="comments" id="comments">
    <header class="comments-header">
        <span id="count-comments">{$iCountComment}</span> {$iCountComment|declension:$aLang.comment_declension:$sLang}

        {if $bAllowSubscribe AND E::IsUser()}
            <div class="hidden-xs text-muted subscribe form-inline">
                {$aLang.comment_whatch}:
                <div class="checkbox">
                <label>
                    <input {if $oTrackComment AND $oTrackComment->getStatus()}checked="checked"{/if}
                           type="checkbox" id="comment_track" class="input-checkbox"
                           onchange="ls.subscribe.tracktoggle('{$sTargetType}_new_comment','{$iTargetId}',this.checked);">
                    {$aLang.comment_track}
                </label>
                </div>
                <div class="checkbox">
                <label>
                    <input {if $oSubscribeComment AND $oSubscribeComment->getStatus()}checked="checked"{/if}
                           type="checkbox" id="comment_subscribe" class="input-checkbox"
                           onchange="ls.subscribe.toggle('{$sTargetType}_new_comment','{$iTargetId}','',this.checked);">
                    {$aLang.comment_subscribe}
                </label>
                </div>
            </div>
        {/if}

        <a name="comments"></a>
    </header>

    {$nesting="-1"}
    {foreach $aComments as $oComment}
    {$cmtlevel=$oComment->getLevel()}

    {if $cmtlevel>Config::Get('module.comment.max_tree')}
        {$cmtlevel=Config::Get('module.comment.max_tree')}
    {/if}

    {if $nesting < $cmtlevel}
    {elseif $nesting > $cmtlevel}
    {section name=closelist1  loop=$nesting-$cmtlevel+1}</div>{/section}
    {elseif !$oComment@first}
    </div>
    {/if}

    <div class="comment-wrapper" id="comment_wrapper_id_{$oComment->getId()}">

    {include file='comments/comment.single.tpl'}

    {$nesting=$cmtlevel}
    {if $oComment@last}
        {section name=closelist2 loop=$nesting+1}</div>{/section}
    {/if}
    {/foreach}

{include file='comments/comment.pagination.tpl' aPagingCmt=$aPagingCmt}

{hook run='comment_tree_end' iTargetId=$iTargetId sTargetType=$sTargetType}

{if !$bAllowToComment}
    {$sNoticeNotAllow}
{else}
    {if E::IsUser()}
        {include file='commons/common.editor.tpl'
        sTargetType="{$sTargetType}_comment"
        bTmp='false'
        sImgToLoad='form_comment_text'
        sSettingsTinymce='ls.settings.getTinymceComment()'
        sSettingsMarkitup='ls.settings.getMarkitupComment()'}
        <div class="reply-header" id="comment_id_0">
            <a href="#" class="link-dotted"
               onclick="ls.comments.toggleCommentForm(0); return false;">{$sNoticeCommentAdd}</a>
        </div>

            <form method="post" class="comment-reply js-form-comment" onsubmit="return false;" enctype="multipart/form-data">
                {hook run='form_add_comment_begin'}

                <div class="form-group">
                    <textarea name="comment_text" id="form_comment_text"
                              class="form-control js-editor-wysiwyg js-editor-markitup"></textarea>
                </div>

                {hook run='form_add_comment_end'}

                <input type="hidden" name="comment_mode" value="reply"/>
                <input type="hidden" name="comment_id" value=""/>
                <input type="hidden" name="cmt_target_id" value="{$iTargetId}"/>
                <input type="hidden" name="reply" value="0" id="form_comment_reply"/>

                <button type="button" onclick="ls.comments.preview();"
                        class="btn btn-default js-button-preview">{$aLang.comment_preview}</button>
                <button type="submit" name="submit_comment"
                        id="comment-button-submit"
                        onclick="ls.comments.add(this,'{$iTargetId}','{$sTargetType}'); return false;"
                        class="btn btn-success js-button-submit">{$aLang.comment_add}</button>
                <button type="submit" name="edit_comment"
                        id="comment-button-edit"
                        onclick="ls.comments.editSubmit(this, '{$iTargetId}', '{$sTargetType}'); return false;"
                        class="btn btn-primary btn-edit js-button-edit" style="display: none;">
                    {$aLang.comment_edit_submit}
                </button>
            </form>

    {else}
        {$aLang.comment_unregistered}
    {/if}
{/if}
</div>
