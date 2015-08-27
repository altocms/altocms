 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{wgroup_add group='toolbar' name='toolbar_comment.tpl'
    aPagingCmt=$aPagingCmt
    iTargetId=$iTargetId
    sTargetType=$sTargetType
    iMaxIdComment=$iMaxIdComment}

{hook run='comment_tree_begin' iTargetId=$iTargetId sTargetType=$sTargetType}

 <script>
     jQuery(function($){
         scroller.loaded();
     });

     (function($){

         scroller = {
             topScrollOffset: -64,
             scrollTiming: 1000,
             pageLoadScrollDelay: 1000,
             scrollToElement: function(whereTo){
                 $.scrollTo(whereTo.replace('comment', 'comment_id_'), scroller.scrollTiming, { offset: { top: scroller.topScrollOffset }, easing: 'easeInOutQuart' });
             },
             generateTempNavId: function(navId){
                 return '_'+navId;
             },
             getNavIdFromHash: function(){
                 var hash = window.location.hash;

                 if (scroller.hashIsTempNavId()) {
                     hash = hash.substring(1);
                 }

                 return hash;
             },
             hashIsTempNavId: function(){
                 var hash = window.location.hash;

                 return hash.substring(0,1) === '#';
             },

             loaded: function(){

                 if (scroller.hashIsTempNavId()) {
                     setTimeout(function(){ scroller.scrollToElement('#'+scroller.getNavIdFromHash()); },scroller.pageLoadScrollDelay);
                 }
             }
         };

     })(jQuery);
 </script>


 <div class="panel panel-default comment">
    <div class="panel-body">
                    <span class="comment-count">
                        <span id="count-comments">{$iCountComment}</span> {$iCountComment|declension:$aLang.comment_declension:$sLang}
                    </span>
        {if $bAllowSubscribe AND E::IsUser()}
            <script>
                $(function(){
                    $('#comment_track').on('ifChanged', function(e) { $('#comment_track').trigger('change'); });
                    $('#comment_subscribe').on('ifChanged', function(e) { $('#comment_subscribe').trigger('change'); });
                })
            </script>
            <ul class="comment-subscribe">
                <li class="long-text">{$aLang.comment_whatch}:</li>
                <li class="short-text">{$aLang.comment_whatch_short}:</li>
                <li>
                    <label>
                        <input {if $oTrackComment AND $oTrackComment->getStatus()}checked="checked"{/if}
                               type="checkbox" id="comment_track" class="input-checkbox"
                               onchange="ls.subscribe.tracktoggle('{$sTargetType}_new_comment','{$iTargetId}',this.checked);">
                        {$aLang.comment_track}
                    </label>
                </li>
                <li>
                    <label>
                        <input {if $oSubscribeComment AND $oSubscribeComment->getStatus()}checked="checked"{/if}
                               type="checkbox" id="comment_subscribe" class="input-checkbox"
                               onchange="ls.subscribe.toggle('{$sTargetType}_new_comment','{$iTargetId}','',this.checked);">
                        {$aLang.comment_subscribe}
                    </label>
                </li>
            </ul>
        {/if}
    </div>
</div>

<div class="comments" id="comments">

    <a name="comments"></a>

    {if $iCountComment == 0}
        <div class="comment-wrapper wrapper-level-0" data-level="0" id="comment_wrapper_id_0"></div>

    {else}

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

        <div class="comment-wrapper wrapper-level-{$cmtlevel + 1}" data-level="{$cmtlevel + 1}" id="comment_wrapper_id_{$oComment->getId()}">

        {include file='comments/comment.single.tpl'}

        {$nesting=$cmtlevel}
        {if $oComment@last}
            {section name=closelist2 loop=$nesting+1}</div>{/section}
        {/if}
        {/foreach}

        {include file='comments/comment.pagination.tpl' aPagingCmt=$aPagingCmt}
    {/if}

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

        <div class="topic-comment-controls reply-header" id="comment_id_0">
            <a class="btn btn-blue btn-normal corner-no" href="#" onclick="ls.comments.toggleCommentForm(0); return false;">{$sNoticeCommentAdd}</a>
            <a class="btn btn-light btn-normal corner-no pull-right"
               data-toggle-all="{$aLang.comment_toggle_all}"
               data-toggle-down="{$aLang.comment_toggle_down}"
               onclick='
                        ls.comments.toggleAll();
                        if ($(this).text()=="{$aLang.comment_toggle_all}") {
                            $(this).text("{$aLang.comment_toggle_down}");
                        } else {
                           $(this).text("{$aLang.comment_toggle_all}");
                        }
                        return false;'
               href="#">{$aLang.comment_toggle_all}</a>
        </div>

            <form method="post" class="comment-reply js-form-comment" onsubmit="return false;" enctype="multipart/form-data">
                {hook run='form_add_comment_begin'}

                <div class="form-group">
                    <textarea name="comment_text" id="form_comment_text"
                              rows="5"
                              class="form-control js-editor-wysiwyg js-editor-markitup"></textarea>
                </div>

                {hook run='form_add_comment_end'}

                <input type="hidden" name="comment_mode" value="reply"/>
                <input type="hidden" name="comment_id" value=""/>
                <input type="hidden" name="cmt_target_id" value="{$iTargetId}"/>
                <input type="hidden" name="reply" value="0" id="form_comment_reply"/>

                <button type="button" onclick="ls.comments.formCommentHide();"
                        class="btn btn-light btn-normal corner-no pull-right js-button-cancel">{$aLang.text_cancel}</button>
                <button type="submit" name="submit_comment"
                        id="comment-button-submit"
                        onclick="ls.comments.add(this,'{$iTargetId}','{$sTargetType}'); return false;"
                        class="btn btn-blue btn-normal corner-no js-button-submit">{$aLang.text_send}</button>
                <button type="submit" name="edit_comment"
                        id="comment-button-edit"
                        onclick="ls.comments.editSubmit(this, '{$iTargetId}', '{$sTargetType}'); return false;"
                        class="btn btn-blue btn-normal corner-no btn-edit js-button-edit" style="display: none;">
                    {$aLang.comment_edit_submit}
                </button>
                <button type="button" onclick="ls.comments.preview();"
                        class="btn btn-light btn-normal corner-no js-button-preview">{$aLang.comment_preview}</button>
            </form>

    {else}
        {$aLang.comment_unregistered}
    {/if}
{/if}
</div>
