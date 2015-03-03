 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if E::IsUser()}
    {$aPagingCmt=$params.aPagingCmt}
    <div class="toolbar-button " id="update" style="{if $aPagingCmt AND $aPagingCmt.iCountPage > 1}display: none;{/if}">

        <a href="#" class="new-comments link link-lead link-clear link-dark" id="new_comments_counter" style="display: none;"
           title="{$aLang.comment_count_new}" onclick="ls.comments.goToNextComment(); return false;"></a>

        <a href="#" class="update-comments last" id="update-comments"
           onclick="ls.comments.load('{$params.iTargetId}', '{$params.sTargetType}'); return false;"><span
                    class="fa fa-repeat"></span></a>

        <input type="hidden" id="comment_last_id" value="{$params.iMaxIdComment}"/>
        <input type="hidden" id="comment_use_paging" value="{if $aPagingCmt AND $aPagingCmt.iCountPage>1}1{/if}"/>
    </div>
{/if}
