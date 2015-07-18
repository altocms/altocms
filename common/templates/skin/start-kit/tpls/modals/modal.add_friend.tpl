<div class="modal fade in" id="modal-add_friend">
    <div class="modal-dialog">
        <div class="modal-content">

            <header class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{$aLang.profile_add_friend}</h4>
            </header>

            <form onsubmit="return ls.user.addFriend(this,{$oUserProfile->getId()},'add');">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="add_friend_text">{$aLang.user_friend_add_text_label}</label>
                        <textarea id="add_friend_text" rows="3" class="form-control js-focus-in"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">{$aLang.user_friend_add_submit}</button>
                </div>
            </form>

        </div>
    </div>
</div>
