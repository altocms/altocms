 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<div class="modal fade in" id="modal-add_friend">
    <div class="modal-dialog">
        <div class="modal-content">

            <header class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">{$aLang.profile_add_friend}</h4>
            </header>

            <form onsubmit="return ls.user.addFriend(this,{if $bUserList}$('a.selected').data('uid'){else}{$oUserProfile->getId()}{/if},'add');">
                <div class="modal-body">
                    <div class="form-group mab0">
                        <textarea id="add_friend_text" rows="6" class="form-control js-focus-in" placeholder="{$aLang.user_friend_add_text_label}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-blue btn-normal corner-no">{$aLang.user_friend_add_submit}</button>
                </div>
            </form>

        </div>
    </div>
</div>
