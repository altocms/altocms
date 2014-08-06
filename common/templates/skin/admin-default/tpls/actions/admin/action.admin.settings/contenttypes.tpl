{extends file='_index.tpl'}
{block name="content-bar"}
{if count($aTypes)>0}
<div class="col-md-12 mb15">
<a href="{router page='admin'}settings-contenttypesadd/" class="btn btn-primary"
      title="{$aLang.action.admin.contenttypes_add}"><i class="ion-plus-round"></i></a>
</div>
{/if}
{/block}
{block name="content-body"}
<div class="col-md-12">
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
  <div class="panel panel-default">
    <div class="panel-body no-padding">
      <div class="table table-striped-responsive"><table class="table table-striped" id="sortable">
        <thead>
          <tr>
            <th class="span4">{$aLang.action.admin.contenttypes_title}</th>
            <th>{$aLang.action.admin.contenttypes_url}</th>
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
              <i class="ion-edit" title="{$aLang.action.admin.contenttypes_edit}"></i></a>
              <a href="{router page='admin'}settings-contenttypes/?toggle={if $oContentType->getContentActive()}off{else}on{/if}&content_id={$oContentType->getContentId()}&security_key={$ALTO_SECURITY_KEY}">
              {if $oContentType->getContentActive()}
              <i class="ion-power"
                title="{$aLang.action.admin.contenttypes_turn_off}"></i>
              {else}
              <i class="ion-lightbulb"
                title="{$aLang.action.admin.contenttypes_turn_on}"></i>
              {/if}
              </a>
            </td>
          </tr>
          {/foreach}
        </tbody>
      </table></div>
    </div>
  </div>
  {/if}
</div>
{/block}