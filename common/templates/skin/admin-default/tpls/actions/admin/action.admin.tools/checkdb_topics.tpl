{extends file="_index.tpl"}
{block name="content-body"}
<div class="col-md-12">
  <h4>{$aLang.action.admin.checkdb_deleted_topics}</h4>
  <div class="panel panel-default">
    <div class="panel-heading">
      <div class="panel-title">
        {$aLang.action.admin.checkdb_topics_comments_online}
      </div>
    </div>
    <div class="panel-body no-padding">
      <div class="table table-striped-responsive"><table class="table table-striped table-condensed">
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
      </table></div>
      <div class="panel-footer clearfix">
      <form method="post">
        <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
        <input type="hidden" name="do_action" value="clear_topics_co"/>
        <button class="btn pull-right {if $aCommentsOnlineTopics}btn-primary{else} disabled{/if}">{$aLang.action.admin.checkdb_clear_unlinked_comments}</button>
      </form>
    </div>
    </div>
  </div>
</div>
{/block}