{extends file="_index.tpl"}

{block name="content-body"}
    <div class="span12">
        <h4>{$aLang.action.admin.checkdb_deleted_topics}</h4>

        <div class="b-wbox">
            <div class="b-wbox-header">
                <div class="b-wbox-header-title">
                    {$aLang.action.admin.checkdb_topics_comments_online}
                </div>
            </div>
            <div class="b-wbox-content">

                <table class="table table-striped table-bordered table-condensed">
                    <thead>
                    <tr>
                        <th>Deleted topic ID</th>
                        <th>Linked comments ID</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $aCommentsOnlineTopics as $nTopicId=>$aData}
                        <tr>
                            <td>{$nTopicId}</td>
                            <td>
                                {foreach $aData as $aUser}
                                    {$aUser.comment_id}
                                {/foreach}
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
                <br/>
            </div>
            <div class="b-wbox-header">
                <div class="b-wbox-header-title">
                    {$aLang.action.admin.checkdb_topics_comments}
                </div>
            </div>
            <div class="b-wbox-content">

                <table class="table table-striped table-bordered table-condensed">
                    <thead>
                    <tr>
                        <th>Deleted topic ID</th>
                        <th>Linked comments ID</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $aCommentsTopics as $nTopicId=>$aData}
                        <tr>
                            <td>{$nTopicId}</td>
                            <td>
                                {foreach $aData as $aUser}
                                    {$aUser.comment_id}
                                {/foreach}
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
                <br/>
            </div>
        </div>
        <form method="post">
            <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
            <input type="hidden" name="do_action" value="clear_topics_co"/>
            <button class="btn {if $aCommentsOnlineTopics OR $aCommentsTopics}btn-primary{else} disabled{/if}">
                {$aLang.action.admin.checkdb_clear_unlinked_comments}
            </button>
        </form>
    </div>
{/block}

