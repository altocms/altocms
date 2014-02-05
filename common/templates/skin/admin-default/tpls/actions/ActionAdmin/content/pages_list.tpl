{extends file='./pages.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a href="{router page='admin'}content-pages/add/" class="btn btn-primary"><i class="icon icon-plus-sign"></i></a>
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
                    {foreach $aPages as $oPage}
                    <tr>
                        <td>
                            {$oPage->GetId()}
                        </td>
                        <td style="padding-left: {$oPage->getLevel()*20+10}px;">
                            {if $oPage->getLevel()==0}<i class="icon icon-folder-open"></i>{else}<i class="icon icon-file"></i>{/if}
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
                            <a href="{router page='admin'}content-pages/edit/{$oPage->getId()}/"
                               title="{$aLang.action.admin.pages_admin_action_edit}" class="tip-top i-block">
                                <i class="icon icon-edit"></i>
                            </a>
                            <a href="#" title="{$aLang.action.admin.pages_admin_action_delete}" class="tip-top i-block"
                                  onclick="return admin.confirmDelete('{$oPage->getId()}', '{$oPage->getTitle()}'); return false;">
                                <i class="icon icon-remove"></i>
                            </a>
                            {if $oPage@first}
                                <i class="icon icon-arrow-up icon-gray"></i>
                            {else}
                                <a href="{router page='admin'}content-pages/sort/{$oPage->getId()}/up/?security_key={$ALTO_SECURITY_KEY}"
                                   title="{$aLang.action.admin.pages_admin_sort_up} ({$oPage->getSort()})" class="tip-top i-block">
                                    <i class="icon icon-arrow-up"></i>
                                </a>
                            {/if}
                            {if $oPage@last}
                                <i class="icon icon-arrow-down icon-gray"></i>
                            {else}
                                <a href="{router page='admin'}content-pages/sort/{$oPage->getId()}/down/?security_key={$ALTO_SECURITY_KEY}"
                                   title="{$aLang.action.admin.pages_admin_sort_down} ({$oPage->getSort()})" class="tip-top i-block">
                                    <i class="icon icon-arrow-down"></i>
                                </a>
                            {/if}
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
            content: '{$aLang.action.admin.pages_admin_action_delete_message} "' + title + '"<br/>{$aLang.action.admin.pages_admin_action_delete_confirm}',
            onConfirm: function() {
                document.location = "{router page='admin'}content-pages/delete/" + id + "/?security_key={$ALTO_SECURITY_KEY}";
            }
        })

    }
</script>

{/block}