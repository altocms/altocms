{extends file='_index.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a href="{router page='admin'}pages/add/" class="btn btn-primary"><i class="icon-plus-sign"></i></a>
    </div>
{/block}

{block name="content-body"}

<div class="span12">

    {if $aParams.0=='add'}
        <h3 class="page-sub-header">{$aLang.action.admin.pages_create}</h3>
        {include file='actions/ActionAdmin/page/page_add.tpl'}
    {elseif $aParams.0=='edit'}
        <h3 class="page-sub-header">{$aLang.action.admin.pages_edit} «{$oPageEdit->getTitle()}»</h3>
    {include file='actions/ActionAdmin/page/page_add.tpl'}
    {/if}

    <div class="b-wbox">
        <div class="b-wbox-content nopadding">

            <table class="table table-striped table-condensed pages-list">
                <thead>
                <tr>
                    <th class="span1">ID</th>
                    <th>{$aLang.action.admin.pages_admin_title}</th>
                    <th>{$aLang.action.admin.pages_admin_url}</th>
                    <th>{$aLang.action.admin.pages_admin_active}</th>
                    <th>{$aLang.action.admin.pages_admin_main}</th>
                    <th class="span2"></th>
                </tr>
                </thead>

                <tbody>
                    {foreach from=$aPages item=oPage}
                    <tr>
                        <td>
                            {$oPage->GetId()}
                        </td>
                        <td style="padding-left: {$oPage->getLevel()*20+10}px;">
                            {if $oPage->getLevel()==0}<i class="icon-folder-open"></i>{else}<i class="icon-file"></i>{/if}
                            <a href="{router page='page'}{$oPage->getUrlFull()}/">{$oPage->getTitle()}</a>
                        </td>
                        <td>
                            /{$oPage->getUrlFull()}/
                        </td>
                        <td class="center">
                            {if $oPage->getActive()}
							{$aLang.action.admin.pages_admin_active_yes}
						{else}
							{$aLang.action.admin.pages_admin_active_no}
						{/if}
                        </td>
                        <td class="center">
                            {if $oPage->getMain()}
							{$aLang.action.admin.pages_admin_active_yes}
						{else}
							{$aLang.action.admin.pages_admin_active_no}
						{/if}
                        </td>
                        <td class="center">
                            <a href="{router page='admin'}pages/edit/{$oPage->getId()}/"
                               title="{$aLang.action.admin.pages_admin_action_edit}">
                                <i class="icon-edit"></i>
                            </a>
                            <span title="{$aLang.action.admin.pages_admin_action_delete}" class="tip-top"
                                  onclick="return admin.confirmDelete('{$oPage->getId()}', '{$oPage->getTitle()}');">
                                <i class="icon-remove"></i></span>
                            <a href="{router page='admin'}pages/sort/{$oPage->getId()}/?security_ls_key={$ALTO_SECURITY_KEY}"
                               title="{$aLang.action.admin.pages_admin_sort_up} ({$oPage->getSort()})" class="tip-top">
                                <i class="icon-arrow-up"></i></a>
                            <a href="{router page='admin'}pages/sort/{$oPage->getId()}/down/?security_ls_key={$ALTO_SECURITY_KEY}"
                               title="{$aLang.action.admin.pages_admin_sort_down} ({$oPage->getSort()})" class="tip-top">
                                <i class="icon-arrow-down"></i></a>
                        </td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>

    {include file="inc.paging.tpl"}

</div>

<script>
    var admin = admin || { };

    admin.confirmDelete = function(id, title) {
        admin.confirm({
            header: '{$aLang.action.admin.pages_admin_action_delete}',
            content: '«' + title + '»: {$aLang.action.admin.pages_admin_action_delete_confirm}',
            onConfirm: function() {
                document.location = "{router page='admin'}pages/delete/" + id + "/?security_ls_key={$ALTO_SECURITY_KEY}";
            }
        })

    }
</script>

{/block}