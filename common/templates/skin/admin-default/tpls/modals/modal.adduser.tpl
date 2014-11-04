<div class="modal fade in" id="modal-adduser">
    <div class="modal-dialog">
        <div class="modal-content">

            <header class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title">{$aLang.action.admin.user_add_dialog}</h3>
            </header>

            <form id="modal-adduser-form" class="uniform" method="post" action="" role="form">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="user_add_login" class="control-label">{$aLang.action.admin.user_add_login}:</label>
                        <input type="text" id="user_add_login" class="form-control input-text input-wide" name="user_login" autofocus="autofocus"/>
                    </div>

                    <div class="form-group">
                        <label for="user_add_mail" class="control-label">{$aLang.action.admin.user_add_mail}:</label>
                        <input type="email" id="user_add_mail" class="form-control input-text input-wide" name="user_mail"/>
                    </div>

                    <div class="form-group">
                        <label for="user_add_password" class="control-label">{$aLang.action.admin.user_add_password}:</label>
                        <input type="password" id="user_add_password" class="form-control input-text input-wide" name="user_password"/>
                    </div>

                    <div class="form-group">
                        <label class="control-label checkbox">
                            <input type="checkbox" id="user_add_setadmin"  name="user_setadmin" value="on" />
                            {$aLang.action.admin.user_add_setadmin}
                        </label>
                    </div>
                    <div id="user_add_setadmin_alert" class="alert alert-danger" style="display: none;">
                        {$aLang.action.admin.user_add_setadmin_alert}
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        {$aLang.action.admin.user_field_add}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(function(){
    var alert = $('#user_add_setadmin_alert');
    $('#user_add_setadmin').parents('label:first').click(function(){
        if ($('#user_add_setadmin').prop('checked')) {
            alert.show();
        } else {
            alert.hide();
        }
    });

    $('#modal-adduser-form').submit(function(){
        admin.user.addUserSubmit(this);
        return false;
    });
});
</script>