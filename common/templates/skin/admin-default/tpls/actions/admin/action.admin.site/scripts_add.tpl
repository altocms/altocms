{extends file='_index.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a href="{router page='admin'}site-scripts/" class="btn btn-default"><i class="icon icon-action-undo"></i></a>
    </div>
{/block}

{block name="content-body"}
    <div class="span12">
        <form method="post" action="" class="form-horizontal uniform">
            <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>

            <div class="b-wbox">
                <div class="b-wbox-header">
                    <div class="b-wbox-header-title">
                        {$aScript.name}
                    </div>
                </div>
                <div class="b-wbox-content nopadding">
                    <div class="control-group">
                        <label class="control-label" for="script_name">{$aLang.action.admin.script_edit_name}</label>

                        <div class="controls">
                            <input type="text" id="script_name" class="input-text" name="script_name" value="{$_aRequest.script_name}">
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="script_description">{$aLang.action.admin.script_edit_description}</label>

                        <div class="controls">
                            <input type="text" id="script_description" class="input-text" name="script_description" value="{$_aRequest.script_description}" >
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="script_place" class="control-label">{$aLang.action.admin.script_edit_place}</label>

                        <div class="controls">
                            <select name="script_place" id="script_place" class="">
                                <option value="head" {if $_aRequest.script_place=='head'}selected{/if}>
                                    {$aLang.action.admin.script_edit_place_header|escape:'html'}
                                </option>
                                <option value="body" {if $_aRequest.script_place=='body'}selected{/if}>
                                    {$aLang.action.admin.script_edit_place_top|escape:'html'}
                                </option>
                                <option value="end" {if $_aRequest.script_place=='end'}selected{/if}>
                                    {$aLang.action.admin.script_edit_place_end|escape:'html'}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="control-group">
                        <label for="script_code" class="control-label">{$aLang.action.admin.script_edit_code}:</label>

                        <div class="controls">
                        <textarea name="script_code" id="script_code" rows="20" required>{$_aRequest.script_code}</textarea>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label">{$aLang.action.admin.script_exclude_adminpanel}</label>

                        <div class="controls">
                            <label>
                                <input type="checkbox" name="script_exclude_adminpanel" {if $_aRequest.script_exclude_adminpanel}checked="checked"{/if}>
                                {$aLang.action.admin.script_exclude_adminpanel_note}
                            </label>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label">{$aLang.action.admin.script_edit_active}</label>

                        <div class="controls">
                            <label>
                                <input type="radio" name="script_active" value="1" {if $_aRequest.script_active}checked="checked"{/if}> {$aLang.action.admin.word_yes}
                            </label>
                            <label>
                                <input type="radio" name="script_active" value="0" {if !$_aRequest.script_active}checked="checked"{/if}> {$aLang.action.admin.word_no}
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            <div class="navbar navbar-inner">
                <button type="submit" name="submit_script" class="btn btn-primary pull-right">
                    {$aLang.action.admin.save}
                </button>
            </div>

        </form>
    </div>
{/block}