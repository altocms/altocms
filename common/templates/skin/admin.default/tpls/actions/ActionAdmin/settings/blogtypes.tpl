{extends file='_index.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a href="{router page='admin'}settings-blogtypes/add/" class="btn btn-primary tip-top"
           title="{$aLang.action.admin.blogtypes_add}"><i class="icon-plus-sign"></i></a>
    </div>
{/block}

{block name="content-body"}
<form action="{router page='admin'}settings-blogtypes/" method="post" id="form_blogtype_list" class="uniform">
    <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
    <input type="hidden" name="blogtype_action" value="">
    <div class="b-wbox">
        <div class="b-wbox-content nopadding">

            <table class="table table-striped table-condensed blogtypes-list">
                <thead>
                <tr>
                    <th></th>
                    <th class="span1">ID</th>
                    <th class="span2">{$aLang.action.admin.blogtypes_typecode}</th>
                    <th>{$aLang.action.admin.blogtypes_name}</th>
                    <th>{$aLang.action.admin.blogtypes_acl_title}</th>
                    <th>{$aLang.action.admin.blogtypes_contenttypes}</th>
                    <th class="span2">{$aLang.action.admin.content_status}</th>
                    <th class="span2">{$aLang.action.admin.content_actions}</th>
                </tr>
                </thead>

                <tbody class="content">
                {foreach $aBlogTypes as $oBlogType}
                    <tr id="blogtype-{$oBlogType->getId()}" class="selectable">
                        <td class="check-row">
                            <input type="checkbox" name="blogtype_sel[{$oBlogType->GetId()}]" />
                        </td>
                        <td class="center">
                            {$oBlogType->getId()}
                        </td>
                        <td>
                            <a href="{router page='admin'}content-blogs/list/{$oBlogType->getTypeCode()}">
                                {$oBlogType->getTypeCode()}
                            </a>
                        </td>
                        <td>
                            {$oBlogType->getTypeName()|escape:'html'}<br/>
                            {foreach $aLangList as $sLang}
                                [ <strong>{$sLang}</strong> ] {$oBlogType->getName($sLang)|escape:'html'}<br/>
                            {/foreach}
                        </td>
                        <td>
                            <div>
                            W:
                            {if $oBlogType->GetAclWrite()==0}
                                {$aLang.action.admin.blogtypes_acl_owner}
                            {elseif $oBlogType->GetAclWrite(ModuleBlog::BLOG_USER_ACL_MEMBER)}
                                {$aLang.action.admin.blogtypes_acl_members} ({$oBlogType->GetMinRateWrite()})
                            {elseif $oBlogType->GetAclWrite(ModuleBlog::BLOG_USER_ACL_USER)}
                                {$aLang.action.admin.blogtypes_acl_users} ({$oBlogType->GetMinRateWrite()})
                            {elseif $oBlogType->GetAclWrite(ModuleBlog::BLOG_USER_ACL_GUEST)}
                                {$aLang.action.admin.blogtypes_acl_guests}
                            {/if}
                            </div>
                            <div>
                            R:
                            {if $oBlogType->GetAclRead()==0}
                                {$aLang.action.admin.blogtypes_acl_owner}
                            {elseif $oBlogType->GetAclRead(ModuleBlog::BLOG_USER_ACL_MEMBER)}
                                {$aLang.action.admin.blogtypes_acl_members} ({$oBlogType->GetMinRateRead()})
                            {elseif $oBlogType->GetAclRead(ModuleBlog::BLOG_USER_ACL_USER)}
                                {$aLang.action.admin.blogtypes_acl_users} ({$oBlogType->GetMinRateRead()})
                            {elseif $oBlogType->GetAclRead(ModuleBlog::BLOG_USER_ACL_GUEST)}
                                {$aLang.action.admin.blogtypes_acl_guests}
                            {/if}
                            </div>
                            <div>
                            C:
                            {if $oBlogType->GetAclComment()==0}
                                {$aLang.action.admin.blogtypes_acl_owner}
                            {elseif $oBlogType->GetAclComment(ModuleBlog::BLOG_USER_ACL_MEMBER)}
                                {$aLang.action.admin.blogtypes_acl_members} ({$oBlogType->GetMinRateComment()})
                            {elseif $oBlogType->GetAclComment(ModuleBlog::BLOG_USER_ACL_USER)}
                                {$aLang.action.admin.blogtypes_acl_users} ({$oBlogType->GetMinRateComment()})
                            {elseif $oBlogType->GetAclComment(ModuleBlog::BLOG_USER_ACL_GUEST)}
                                {$aLang.action.admin.blogtypes_acl_guests}
                            {/if}
                            </div>
                        </td>
                        <td class="center">
                            {if !$oBlogType->GetContentType()}
                                {$aLang.action.admin.blogtypes_contenttypes_any}
                            {else}
                                {$oBlogType->GetContentType()}
                            {/if}
                        </td>
                        <td class="center">
                            <div class="b-switch"
                                 onclick="admin.turnBlogtype('{$oBlogType->GetId()}', '{!$oBlogType->isActive()}'); return false;">
                                <input type="checkbox" {if $oBlogType->isActive()}checked{/if}
                                       name="b-switch-{$oBlogType->GetId()}">
                                <label><i></i></label>
                            </div>
                        </td>
                        <td>
                            <a href="{router page='admin'}settings-blogtypes/edit/{$oBlogType->getId()}/">
                                <i class="icon-edit tip-top" title="{$aLang.action.admin.content_edit}"></i></a>
                            {if $oBlogType AND $oBlogType->CanDelete()}
                            <a href="{router page='admin'}settings-blogtypes/delete/{$oBlogType->getId()}/">
                                <i class="icon-trash tip-top" title="{$aLang.action.admin.content_delete}"></i></a>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>

</form>

    <script>
        var admin = admin || { };
        admin.turnBlogtype = function (blogtypeId, action) {
            if (!action) {
                action = 'deactivate';
            } else if (action != 'deactivate') {
                action = 'activate';
            }
            $('tr[id^=blogtype-] .check-row [type=checkbox]').prop("checked", false);
            $('tr[id^=blogtype-' + blogtypeId + '] .check-row [type=checkbox]').prop("checked", true);
            $('#form_blogtype_list input[name=blogtype_action]').val(action);
            $('#form_blogtype_list').submit();
        };

    </script>

{/block}