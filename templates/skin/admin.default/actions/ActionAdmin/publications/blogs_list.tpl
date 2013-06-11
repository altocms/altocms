{extends file='_index.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a href="#" class="btn btn-primary disabled"><i class="icon-plus-sign"></i></a>
    </div>
    <div class="btn-group">
        <a class="btn {if $sMode=='all' || $sMode==''}active{/if}" href="{router page='admin'}blogs/list/">
            all <span class="badge badge-up">{$nBlogsTotal}</span>
        </a>
        {foreach $aBlogTypes as $aBlogType}
            <a class="btn {if $sMode==$aBlogType.blog_type}active{/if}" href="{router page='admin'}blogs/list/{$aBlogType.blog_type}/">
                {$aBlogType.blog_type} <span class="badge badge-up">{$aBlogType.blog_cnt}</span>
            </a>
        {/foreach}
    </div>
{/block}

{block name="content-body"}

<div class="span12">

    <div class="b-wbox">
        <div class="b-wbox-content nopadding">
            <table class="table table-striped table-condensed blogs-list">
                <thead>
                <tr>
                    <th class="span1">ID</th>
                    <th>User</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Users</th>
                    <th>Votes</th>
                    <th>Rating</th>
                    <th class="span2">&nbsp;</th>
                </tr>
                </thead>

                <tbody>
                    {foreach $aBlogs as $oBlog}
                    <tr>
                        <td class="number">{$oBlog->GetId()}</td>
                        <td>
                            <a href="{router page='admin'}users/profile/{$oBlog->GetOwner()->GetId()}/">{$oBlog->GetOwner()->GetLogin()}</a>
                        </td>
                        <td class="name">
                            <a href="{$oBlog->GetUrlFull()}">{$oBlog->GetTitle()}</a>
                        </td>
                        <td class="center">{$oBlog->GetBlogDateAdd()}</td>
                        <td class="center">{if $oBlog->GetType()!='personal'}<b>{/if}{$oBlog->GetType()}{if $oBlog->GetType()!='personal'}</b>{/if}</td>
                        <td class="number">{$oBlog->GetBlogCountUser()}</td>
                        <td class="number">{$oBlog->GetBlogCountVote()}</td>
                        <td class="number">{$oBlog->GetBlogRating()}</td>
                        <td class="center">
                            {if $oBlog->GetType()=='personal'}
                                <i class="icon-edit opacity50"></i>
                            {else}
                                <a href="{router page='blog'}edit/{$oBlog->GetId()}/" title="{$aLang.action.admin.blog_edit}">
                                    <i class="icon-edit"></i></a>
                            {/if}
                            <a href="#" title="{$aLang.action.admin.blog_delete}" onclick="admin.blog.del('{$aLang.action.admin.blog_del_confirm}','{$sBlogTitle}','{$aBlog.blog_id}'); return false;">
                                <i class="icon-remove"></i></a>
                        </td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>

    {include file="inc.paging.tpl"}

</div>

{/block}