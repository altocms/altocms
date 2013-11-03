{extends file="_index.tpl"}

{block name="content-body"}
    <div class="span12">
        <h4>{$aLang.action.admin.checkdb_deleted_blogs}</h4>

        <div class="b-wbox">
            <div class="b-wbox-header">
                <div class="b-wbox-header-title">
                    {$aLang.action.admin.checkdb_blogs_joined}
                </div>
            </div>
            <div class="b-wbox-content">
                <table class="table table-striped table-bordered table-condensed">
                    <thead>
                    <tr>
                        <th>Deleted blog ID</th>
                        <th>Joined users</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $aJoinedBlogs as $nBlogId=>$aData}
                        <tr>
                            <td>{$nBlogId}</td>
                            <td>
                                {foreach $aData as $aUser}
                                    {$aUser.user_login}
                                {/foreach}
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
                <form method="post">
                    <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
                    <input type="hidden" name="do_action" value="clear_blogs_joined"/>
                    <button class="btn {if $aJoinedBlogs}btn-primary{else} disabled{/if}">{$aLang.action.admin.checkdb_clear_unlinked_blogs}</button>
                </form>
            </div>
        </div>

        <div class="b-wbox">
            <div class="b-wbox-header">
                <div class="b-wbox-header-title">
                    {$aLang.action.admin.checkdb_blogs_comments_online}
                </div>
            </div>
            <div class="b-wbox-content">

                <table class="table table-striped table-bordered table-condensed">
                    <thead>
                    <tr>
                        <th>Deleted blog ID</th>
                        <th>Linked comments ID</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $aCommentsOnlineBlogs as $nBlogId=>$aData}
                        <tr>
                            <td>{$nBlogId}</td>
                            <td>
                                {foreach $aData as $aUser}
                                    {$aUser.comment_id}
                                {/foreach}
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
                <form method="post">
                    <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
                    <input type="hidden" name="do_action" value="clear_blogs_co"/>
                    <button class="btn {if $aCommentsOnlineBlogs}btn-primary{else} disabled{/if}">{$aLang.action.admin.checkdb_clear_unlinked_blogs}</button>
                </form>
            </div>
        </div>
    </div>
{/block}

