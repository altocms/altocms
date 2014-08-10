{extends file='./blogs.tpl'}
{block name="content-bar"}
<div class="col-md-12">
  <a href="#" class="btn btn-primary pull-right"><i class="glyphicon glyphicon-plus"></i></a>
  <ul class="nav nav-pills atlass">
    <li class="{if $sMode=='all' || $sMode==''}active{/if}">
      <a href="{router page='admin'}content-blogs/list/">
      {$aLang.action.admin.blogs_all_types} <span class="label label-primary">{$nBlogsTotal}</span>
      </a>
    </li>
    {foreach $aBlogTypes as $oBlogType}
    <li class="{if $sMode==$oBlogType->GetTypeCode()}active{/if}">
      <a href="{router page='admin'}content-blogs/list/{$oBlogType->GetTypeCode()}/">
      {$oBlogType->GetName()} <span class="label label-primary">{$oBlogType->GetBlogsCount()}</span>
      </a>
    </li>
    {/foreach}
  </ul>
</div>
{/block}
{block name="content-body"}
<div class="col-md-12">
  <div class="panel panel-default">
    <div class="panel-body no-padding">
      <div class="table table-striped-responsive"><table class="table table-striped blogs-list">
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
              <a href="{router page='admin'}users-list/profile/{$oBlog->GetOwner()->GetId()}/">{$oBlog->GetOwner()->getDisplayName()}</a>
            </td>
            <td class="name">
              <a href="{$oBlog->GetUrlFull()}">{$oBlog->GetTitle()}</a>
            </td>
            <td class="center">{$oBlog->GetBlogDateAdd()}</td>
            <td class="center">
              {if $oBlog->GetBlogType()}{$oBlog->GetBlogType()->GetName()}{/if}<br/>
              {if $oBlog->GetType()!='personal'}
              <b>{/if}{$oBlog->GetType()}{if $oBlog->GetType()!='personal'}</b>{/if}
            </td>
            <td class="number">{$oBlog->GetBlogCountUser()}</td>
            <td class="number">{$oBlog->GetBlogCountTopic()}</td>
            <td class="number">{$oBlog->GetBlogCountVote()}</td>
            <td class="number">{$oBlog->GetBlogRating()}</td>
            <td class="center">
              {if $oBlog->GetType()=='personal'}
              <i class="ion-ios7-compose opacity50"></i>
              {else}
              <a href="{router page='blog'}edit/{$oBlog->GetId()}/"
                title="{$aLang.action.admin.blog_edit}">
              <i class="ion-ios7-compose"></i></a>
              {/if}
              <a href="#" title="{$aLang.action.admin.blog_delete}"
                onclick="admin.blog.del('{$oBlog->GetTitle()|escape:'html'}','{$oBlog->GetId()}', '{$oBlog->GetBlogCountTopic()}'); return false;">
              <i class="ion-ios7-trash"></i></a>
            </td>
          </tr>
          {/foreach}
        </tbody>
      </table></div>
    </div>
  </div>
  {include file="inc.paging.tpl"}
</div>
{include_once file="modals/modal.blog_delete.tpl"}
<script>
  var admin = admin || { };
  (function($) {
      admin.blog = admin.blog || { };
      var modal = $('#modal-blog_delete');
      admin.blog.del = function (blogTitle, blogId, topicsNum) {
          if (modal.length) {
              $('#blog_delete_name').text(blogTitle);
              $('#blog_delete_topics').text(topicsNum);
              modal.find('[name=delete_blog_id]').val(blogId);
              if (topicsNum > 0) {
                  $('#blog_delete_choose').show();
              } else {
                  $('#blog_delete_choose').hide();
              }
              modal.modal('show');
          }
          return false;
      };
      $(function(){
          modal.find('[name=delete_topics]').on('change', function(){
              if ($(this).val() == 'delete') {
                  modal.find('[id^=topic_move_to]').hide();
              } else {
                  modal.find('[id^=topic_move_to]').show();
              }
          });
          modal.find('[name=delete_topics]:checked').trigger('change');
      });
  })(jQuery);
</script>
{/block}