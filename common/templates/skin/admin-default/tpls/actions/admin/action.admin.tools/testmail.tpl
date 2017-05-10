{extends file='_index.tpl'}

{block name="content-bar"}
{/block}

{block name="content-body"}
    <div class="span12">
        <form method="post" action="" class="form-horizontal uniform">
            <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>

            <div class="b-wbox">
                <div class="b-wbox-content nopadding">
                    <div class="control-group">
                        <label class="control-label" for="testmail_address">{$aLang.action.admin.testmail_address_label}</label>

                        <div class="controls">
                            <input type="text" id="testmail_address" class="input-text" name="testmail_address" value="{$_aRequest.testmail_address}">
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="testmail_body" class="control-label">{$aLang.action.admin.testmail_body_label}:</label>

                        <div class="controls">
                        <textarea name="testmail_body" id="testmail_body" rows="5" required>{$_aRequest.testmail_body}</textarea>
                        </div>
                    </div>

                </div>
            </div>

            <div class="navbar navbar-inner">
                <button type="submit" name="testmail_script" class="btn btn-primary pull-right">
                    Send test message
                </button>
            </div>

        </form>
    </div>
{/block}