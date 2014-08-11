{extends file='_index.tpl'}

{block name="content-bar"}
    {if count($aTypes)>0}
        <div class="btn-group">
            <a href="{router page='admin'}settings-contenttypesadd/" class="btn btn-primary tip-top"
               title="{$aLang.action.admin.contenttypes_add}"><i class="icon icon-plus"></i></a>
        </div>
    {/if}
{/block}

{block name="content-body"}
    <script>
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
                    order.push({ 'id': $(value).attr('id'), 'order': index });
                });
                ls.ajax(aRouter['admin'] + 'ajaxchangeordertypes/', { 'order': order }, function (response) {
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
    {if count($aTypes)>0}
        <div class="b-wbox">
            <div class="b-wbox-content nopadding">

                <table class="table table-striped table-condensed pages-list" id="sortable">
                    <thead>
                    <tr>
                        <th class="span4">{$aLang.action.admin.contenttypes_title}</th>
                        <th>{$aLang.action.admin.contenttypes_url}</th>
                        <th>{$aLang.action.admin.contenttypes_fields_added}</th>
                        <th>{$aLang.action.admin.contenttypes_status}</th>
                        <th class="span2">{$aLang.action.admin.contenttypes_actions}</th>
                    </tr>
                    </thead>

                    <tbody class="content">
                    {foreach from=$aTypes item=oContentType}
                        <tr id="{$oContentType->getContentId()}" class="cursor-x">
                            <td class="center">
                                {$oContentType->getContentTitle()|escape:'html'}{if !$oContentType->getContentCandelete()} <em>
                                    [{$aLang.action.admin.contenttypes_standart}]</em>{/if}
                            </td>
                            <td class="center">
                                {$oContentType->getContentUrl()|escape:'html'}
                            </td>
                            <td>
                                {foreach $oContentType->getFields() as $oField}
                                    {$oField->getFieldName()} ({$oField->getFieldType()}){if !$oField@last}<br/>{/if}
                                {/foreach}
                            </td>
                            <td class="center">
                                <span>
                                    {if $oContentType->getContentActive()}
                                        {$aLang.action.admin.contenttypes_on}
                                    {else}
                                        {$aLang.action.admin.contenttypes_off}
                                    {/if}
                                </span>
                            </td>
                            <td class="center">
                                <a href="{router page='admin'}settings-contenttypesedit/{$oContentType->getContentId()}/">
                                    <i class="icon icon-note tip-top" title="{$aLang.action.admin.contenttypes_edit}"></i></a>
                                <a href="{router page='admin'}settings-contenttypes/?toggle={if $oContentType->getContentActive()}off{else}on{/if}&content_id={$oContentType->getContentId()}&security_key={$ALTO_SECURITY_KEY}">
                                    {if $oContentType->getContentActive()}
                                        <i class="icon icon-ban tip-top"
                                           title="{$aLang.action.admin.contenttypes_turn_off}"></i>
                                    {else}
                                        <i class="icon icon-ok-circle tip-top"
                                           title="{$aLang.action.admin.contenttypes_turn_on}"></i>
                                    {/if}
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    {/if}

{/block}