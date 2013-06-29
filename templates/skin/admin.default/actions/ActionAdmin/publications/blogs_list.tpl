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
            <a class="btn {if $sMode==$aBlogType.blog_type}active{/if}"
               href="{router page='admin'}blogs/list/{$aBlogType.blog_type}/">
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
                        <th>Topics</th>
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
                            <td class="center">{if $oBlog->GetType()!='personal'}
                                <b>{/if}{$oBlog->GetType()}{if $oBlog->GetType()!='personal'}</b>{/if}</td>
                            <td class="number">{$oBlog->GetBlogCountUser()}</td>
                            <td class="number">{$oBlog->GetBlogCountTopic()}</td>
                            <td class="number">{$oBlog->GetBlogCountVote()}</td>
                            <td class="number">{$oBlog->GetBlogRating()}</td>
                            <td class="center">
                                {if $oBlog->GetType()=='personal'}
                                    <i class="icon-edit opacity50"></i>
                                {else}
                                    <a href="{router page='blog'}edit/{$oBlog->GetId()}/"
                                       title="{$aLang.action.admin.blog_edit}">
                                        <i class="icon-edit"></i></a>
                                {/if}
                                <a href="#" title="{$aLang.action.admin.blog_delete}"
                                   onclick="admin.blog.del('{$oBlog->GetTitle()|escape:'html'}','{$oBlog->GetId()}', '{$oBlog->GetBlogCountTopic()}'); return false;">
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
    <div id="blog_delete_form" class="modal">
        <header class="modal-header">
            <button type="button" class="close jqmClose">&times;</button>
            <h3>{$aLang.blog_admin_delete_title}</h3>
        </header>

        <form action="" method="POST" class="modal-content uniform">
            <p>{$aLang.action.admin.blog_del_confirm}<strong id="blog_delete_name"></strong></p>

            <p>{$aLang.action.admin.blog_del_topics}<strong id="blog_delete_topics"></strong></p>

            <p>{$aLang.action.admin.blog_del_topics_choose}</p>

            <p>
                <label>
                    <input type="radio" name="delete_topics" value="delete" checked>{$aLang.blog_delete_clear}
                </label>
                <label>
                    <input type="radio" name="delete_topics" value="move">{$aLang.blog_admin_delete_move}
                    <select name="topic_move_to" id="topic_move_to" class="input-width-full">
                        <option value=""></option>
                        {foreach $aAllBlogs as $nBlogId=>$sBlogTitle}
                            <option value="{$nBlogId}">{$sBlogTitle|escape:'html'}</option>
                        {/foreach}
                    </select>
                </label>
            </p>

            <input type="hidden" name="cmd" value="delete_blog"/>
            <input type="hidden" name="delete_blog_id" value=""/>
            <input type="hidden" name="security_ls_key" value="{$ALTO_SECURITY_KEY}" />
            <input type="hidden" name="return-path" value="{Router::Url('link')}" />
            <button type="submit" class="btn btn-primary">{$aLang.action.admin.blog_delete}</button>
        </form>
    </div>
    <script>
        var admin = admin || { };
        admin.blog = admin.blog || { };
        var path = '{router page='blog'}delete/';
        admin.blog.del = function (blogTitle, blogId, topicsNum) {
            var form = $('#blog_delete_form');
            if (form.length) {
                $('#blog_delete_name').text(blogTitle);
                $('#blog_delete_topics').text(topicsNum);
                form.find('[name=delete_blog_id]').val(blogId);
                form.jqmShow();
            }
        }
        $('#blog_delete_form').jqm();
    </script>
{/block}