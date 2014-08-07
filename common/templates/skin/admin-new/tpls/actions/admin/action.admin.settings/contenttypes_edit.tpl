{extends file='_index.tpl'}

{block name="content-bar"}
<div class="col-md-12 mb15">
    <a href="{router page='admin'}settings-contenttypes/" class="btn btn-primary"><i class="ion-plus-round"></i></a>
 </div>
{/block}

{block name="content-body"}

<div class="col-md-12">

{literal}
<script>
    function select class="form-control"field(f) {
        $('#select class="form-control"_inputval').hide();
        $('#daoobj_select class="form-control"').hide();
        //для типа выпадающий список
        if (f == 'select class="form-control"') {
            $('#select class="form-control"_inputval').show();
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
        $("#sortable tbody.content").disableselect class="form-control"ion();

        $("#sortable tbody.content").sortable({
            stop: sortSave
        });
    });
</script>
{/literal}


    <form method="POST" name="typeadd" enctype="multipart/form-data" class="form-horizontal">
        <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>

        <div class="panel panel-default noborder">
                {if $sEvent=='contentadd'}
                    <div class="panel-title"><div class="panel-title">{$aLang.action.admin.contenttypes_add_title}</div></div>
                {elseif $sEvent=='contentedit'}
                    <div class="panel-title"><div class="panel-title">{$aLang.action.admin.contenttypes_edit_title}
                        : {$oContentType->getContentTitle()|escape:'html'}</div></div>
                {/if}
            <div class="panel-body">

                <div class="form-group">
                    <label for="content_title" class="col-sm-2 control-label">
                        {$aLang.action.admin.contenttypes_title}:
                    </label>

                    <div class="col-sm-10">
                        <input class="form-control" type="text" id="content_title" name="content_title"
                               value="{$_aRequest.content_title}"
                               class="form-control"/>
                        <span class="help-block">{$aLang.action.admin.contenttypes_title_notice}</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="content_title_decl" class="col-sm-2 control-label">
                        {$aLang.action.admin.contenttypes_title_decl}:
                    </label>

                    <div class="col-sm-10">
                        <input class="form-control" type="text" id="content_title_decl" name="content_title_decl"
                               value="{$_aRequest.content_title_decl}" class="form-control"/>
                        <span class="help-block">{$aLang.action.admin.contenttypes_title_decl_notice}</span>
                    </div>
                </div>

                <div class="form-group" {if $oContentType && !$oContentType->getContentCandelete()}style="display:none;"{/if}>
                    <label for="content_url" class="col-sm-2 control-label">
                        {$aLang.action.admin.contenttypes_url}:
                    </label>

                    <div class="col-sm-10">
                        <input type="{if isset($_aRequest.content_candelete) && $_aRequest.content_candelete=='0'}hidden{else}text{/if}"
                               id="content_url" name="content_url" value="{$_aRequest.content_url}"
                               class="form-control"/>
                        <span class="help-block">{$aLang.action.admin.contenttypes_url_notice}</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="content_access" class="col-sm-2 control-label">
                        {$aLang.action.admin.contenttypes_type_access}:
                    </label>

                    <div class="col-sm-10">
                        <select class="form-control" name="content_access" id="content_access" class="form-control">
                            <option value="1"
                                    {if $_aRequest.content_access=='1'}selected{/if}>{$aLang.action.admin.contenttypes_type_access_all}</option>
                            <option value="2"
                                    {if $_aRequest.content_access=='2'}selected{/if}>{$aLang.action.admin.contenttypes_type_access_admin}</option>
                        </select class="form-control">
                        <span class="help-block">{$aLang.action.admin.contenttypes_type_access_notice}</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">
                        {$aLang.action.admin.contenttypes_add_additional}:
                    </label>

                    <div class="col-sm-10">
                        <label>
                            <input type="checkbox" id="additional-photoset" name="config[photoset]" value="1"
                                   {if $_aRequest.config.photoset}checked{/if}/>
                            {$aLang.action.admin.contenttypes_additional_photoset}
                        </label>
                        <label>
                            <input type="checkbox" id="additional-question" name="config[question]" value="1"
                                   {if $_aRequest.config.question}checked{/if}/>
                            {$aLang.action.admin.contenttypes_additional_question}
                        </label>
                        <label>
                            <input type="checkbox" id="additional-link" name="config[link]" value="1"
                                   {if $_aRequest.config.link}checked{/if}/>
                            {$aLang.action.admin.contenttypes_additional_link}
                        </label>
                    </div>
                </div>
            </div>

            {if $sEvent=='settings-contenttypesedit'}
                <div class="panel-heading">
                    <div class="panel-title">{$aLang.action.admin.contenttypes_fields_added}</div>
                </div>
                <div class="panel-body clearfix">
                    <div class="table table-striped-responsive"><table id="sortable" class="table table-striped table-bordered mb15">
                        <thead class="topiccck_thead">
                        <tr>
                            <th>{$aLang.action.admin.contenttypes_type}</th>
                            <th>{$aLang.action.admin.contenttypes_name}</th>
                            <th>{$aLang.action.admin.contenttypes_description}</th>
                            <th>{$aLang.action.admin.contenttypes_actions}</th>
                        </tr>
                        </thead>

                        <tbody class="content">
                        {foreach from=$oContentType->getFields() item=oField name=el2}
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
                                    <a href="{router page='admin'}settings-contenttypes-fieldedit/{$oField->getFieldId()}/">{$aLang.action.admin.contenttypes_edit}</a>
                                    |
                                    <a href="{router page='admin'}settings-contenttypes-fielddelete/{$oField->getFieldId()}/?security_key={$ALTO_SECURITY_KEY}"
                                       onclick="return confirm('{$aLang.action.admin.contenttypes_field_detele_confirm}');">{$aLang.action.admin.contenttypes_delete}</a>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table></div>
                        <a class="btn btn-primary pull-right" href="{router page="admin"}settings-contenttypes-fieldadd/{$oContentType->getContentId()}/">
                            <i class="ion-plus-round"></i> {$aLang.action.admin.contenttypes_add_field}
                        </a>
                </div>
            {/if}

                <div class="panel-footer clearfix">
                    <button type="submit" class="btn btn-primary pull-right"
                            name="submit_type_add">{$aLang.action.admin.contenttypes_submit}</button>
                    {if $sEvent=='add'}
                        <p><span class="help-block">{$aLang.action.admin.contenttypes_afteradd}</span></p>
                    {/if}
                </div>

        </div>
    </form>

</div>
{/block}