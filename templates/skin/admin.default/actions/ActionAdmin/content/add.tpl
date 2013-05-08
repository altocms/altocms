{extends file='_index.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a href="{router page='admin'}content/" class="btn"><i class="icon-chevron-left"></i></a>
    </div>
{/block}

{block name="content-body"}

<div class="span12">

{literal}
<script>
    function selectfield(f) {
        $('#select_inputval').hide();
        $('#daoobj_select').hide();
        //для типа выпадающий список
        if (f == 'select') {
            $('#select_inputval').show();
        }
    }

    var fixHelper = function (e, ui) {
        ui.children().each(function () {
            $(this).width($(this).width());
        });
        return ui;
    };

    var sortSave = function (e, ui) {
        var notes = $('#sortable tbody.content tr');
        if (notes.length > 0) {
            var order = [];
            $.each(notes.get().reverse(), function (index, value) {
                order.push({'id': $(value).attr('id'), 'order': index});
            });
            ls.ajax(aRouter['admin'] + 'ajaxchangeorderfields/', {'order': order}, function (response) {
                if (!response.bStateError) {
                    ls.msg.notice(response.sMsgTitle, response.sMsg);
                } else {
                    ls.msg.error(response.sMsgTitle, response.sMsg);
                }
            });
        }
    };

    $(function () {
        $("#sortable tbody.content").sortable({
            helper: fixHelper
        });
        $("#sortable tbody.content").disableSelection();

        $("#sortable tbody.content").sortable({
            stop: sortSave
        });
    });
</script>
{/literal}


    <form method="POST" name="typeadd" enctype="multipart/form-data" class="form-horizontal uniform">
        <input type="hidden" name="security_ls_key" value="{$ALTO_SECURITY_KEY}"/>

        <div class="b-wbox">
            <div class="b-wbox-header">
                {if $sEvent=='contentadd'}
                    <div class="b-wbox-header-title">{$aLang.action.admin.content_add_title}</div>
                {elseif $sEvent=='contentedit'}
                    <div class="b-wbox-header-title">{$aLang.action.admin.content_edit_title}
                        : {$oType->getContentTitle()|escape:'html'}</div>
                {/if}
            </div>
            <div class="b-wbox-content nopadding">

                <div class="control-group">
                    <label for="content_title" class="control-label">
                        {$aLang.action.admin.content_title}:
                    </label>

                    <div class="controls">
                        <input type="text" id="content_title" name="content_title"
                               value="{$_aRequest.content_title}"
                               class="input-text"/>
                        <span class="help-block">{$aLang.action.admin.content_title_notice}</span>
                    </div>
                </div>

                <div class="control-group">
                    <label for="content_title_decl" class="control-label">
                        {$aLang.action.admin.content_title_decl}:
                    </label>

                    <div class="controls">
                        <input type="text" id="content_title_decl" name="content_title_decl"
                               value="{$_aRequest.content_title_decl}" class="input-text"/>
                        <span class="help-block">{$aLang.action.admin.content_title_decl_notice}</span>
                    </div>
                </div>

                <div class="control-group" {if $oType && !$oType->getContentCandelete()}style="display:none;"{/if}>
                    <label for="content_url" class="control-label">
                        {$aLang.action.admin.content_url}:
                    </label>

                    <div class="controls">
                        <input type="{if isset($_aRequest.content_candelete) && $_aRequest.content_candelete=='0'}hidden{else}text{/if}"
                               id="content_url" name="content_url" value="{$_aRequest.content_url}"
                               class="input-text"/>
                        <span class="help-block">{$aLang.action.admin.content_url_notice}</span>
                    </div>
                </div>

                <div class="control-group">
                    <label for="content_access" class="control-label">
                        {$aLang.action.admin.content_type_access}:
                    </label>

                    <div class="controls">
                        <select name="content_access" id="content_access" class="input-text">
                            <option value="1"
                                    {if $_aRequest.content_access=='1'}selected{/if}>{$aLang.action.admin.content_type_access_all}</option>
                            <option value="2"
                                    {if $_aRequest.content_access=='2'}selected{/if}>{$aLang.action.admin.content_type_access_admin}</option>
                        </select>
                        <span class="help-block">{$aLang.action.admin.content_type_access_notice}</span>
                    </div>
                </div>

                <div class="control-group">
                    <label class="control-label">
                        {$aLang.action.admin.content_add_additional}:
                    </label>

                    <div class="controls">
                        <label>
                            <input type="checkbox" id="additional-photoset" name="config[photoset]" value="1"
                                   {if $_aRequest.config.photoset}checked{/if}/>
                            {$aLang.action.admin.content_additional_photoset}
                        </label>
                        <label>
                            <input type="checkbox" id="additional-question" name="config[question]" value="1"
                                   {if $_aRequest.config.question}checked{/if}/>
                            {$aLang.action.admin.content_additional_question}
                        </label>
                        <label>
                            <input type="checkbox" id="additional-link" name="config[link]" value="1"
                                   {if $_aRequest.config.link}checked{/if}/>
                            {$aLang.action.admin.content_additional_link}
                        </label>
                    </div>
                </div>
            </div>

            {if $sEvent=='contentedit'}
                <div class="b-wbox-header">
                    <div class="b-wbox-header-title">{$aLang.action.admin.content_fields_added}</div>
                </div>
                <div class="b-wbox-content">
                    <table id="sortable" class="table table-bordered">
                        <thead class="topiccck_thead">
                        <tr>
                            <th>{$aLang.action.admin.content_type}</th>
                            <th>{$aLang.action.admin.content_name}</th>
                            <th>{$aLang.action.admin.content_description}</th>
                            <th>{$aLang.action.admin.content_actions}</th>
                        </tr>
                        </thead>

                        <tbody class="content">
                        {foreach from=$oType->getFields() item=oField name=el2}
                            <tr id="{$oField->getFieldId()}" class="cursor-x">
                                <td align="center">
                                    {$oField->getFieldType()}
                                </td>
                                <td align="center">
                                    {$oField->getFieldName()}
                                </td>
                                <td align="center">
                                    {$oField->getFieldDescription()}
                                </td>
                                <td align="center">
                                    <a href="{router page='admin'}fieldedit/{$oField->getFieldId()}/">{$aLang.action.admin.content_edit}</a>
                                    |
                                    <a href="{router page='admin'}fielddelete/{$oField->getFieldId()}/?security_ls_key={$ALTO_SECURITY_KEY}"
                                       onclick="return confirm('{$aLang.action.admin.content_field_detele_confirm}');">{$aLang.action.admin.content_delete}</a>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                    <div class="control-group">
                        <a class="btn fl-r" href="{router page="admin"}fieldadd/{$oType->getContentId()}/">
                            <i class="icon-plus-sign"></i> {$aLang.action.admin.content_add_field}
                        </a>
                    </div>
                </div>
            {/if}

            <div class="b-wbox-content nopadding">
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"
                            name="submit_type_add">{$aLang.action.admin.content_submit}</button>
                    {if $sEvent=='add'}
                        <p><span class="help-block">{$aLang.action.admin.content_afteradd}</span></p>
                    {/if}
                </div>
            </div>
        </div>
    </form>

</div>
{/block}