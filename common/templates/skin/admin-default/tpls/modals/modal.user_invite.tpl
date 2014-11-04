<div class="modal fade in" id="modal-user_invite">
    <div class="modal-dialog">
        <div class="modal-content">

            <header class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title">{$aLang.action.admin.invite_send_to_mail}</h3>
            </header>

            <form id="modal-user_invite-form" class="uniform" method="post" action="" role="form">
                <div class="modal-body">
                    <div id="modal-user_invite-success" class="text-success" style="display: none;">

                    </div>
                    <div id="modal-user_invite-error" class="text-danger" style="display: none;">

                    </div>
                    <div class="form-group">
                        <label for="invite_listmail" class="control-label">{$aLang.action.admin.invite_listmail}:</label>
                        <textarea id="invite_listmail" class="form-control input-text input-wide" name="invite_listmail" autofocus="autofocus"></textarea>
                    </div>

                    <div class="help-inline" >
                        {$aLang.action.admin.invite_listmail_help}
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        {$aLang.action.admin.invite_send_to_mail}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(function(){
    $('#invite_listmail').autosize();

    $('#modal-user_invite-form').submit(function(){
        admin.user.inviteUserSubmit(this);
        return false;
    });
});
</script>