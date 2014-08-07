{extends file='./_users.tpl'}
{block name="content-body-sidebar" prepend}
<script type="text/javascript">
  admin.formVote = function (button, value) {
  
      button = $(button);
      value = parseInt(value);
  
      var options = {
          trigger:'manual',
          content:function () {
              var result = '';
              if (value < 0) {
                  result += '<input type="hidden" name="sign" value="-1" />';
                  result += '<i class="icon icon-minus icon-red adm_vote_sign"></i>';
              } else {
                  result += '<input type="hidden" name="sign" value="1" />';
                  result += '<i class="ion-plus-round icon-green adm_vote_sign"></i>';
              }
              result += '<input type="text" name="value" value="' + Math.abs(value) + '" class="adm_vote_value" />';
              result += '<button class="btn btn-xs btn-danger pull-right cancel"><i class="ion-close"></i></button>';
              result += '<button class="btn btn-xs btn-success pull-right confirm"><i class="ion-android-checkmark"></i></button>'
              return result;
          },
          html: true,
          title:false,
          attr:{
              'class':'adm_vote'
          },
          onConfirm:function (event, element) {
              $(event.currentTarget).progressOn();
              var val = parseInt($(element).find('input[name=value]').val() * $(element).find('input[name=sign]').val());
              if (val) {
                  var views = {
                      skill:$('.sidebar .strength .total'),
                      rating:$('.sidebar .voting .total'),
                      voteCount:$('.sidebar .voting .count')
                  };
                  admin.vote('user', '{$oUserProfile->getId()}', val, views, function () {
                      $(event.currentTarget).progressOff();
                  });
              }
          }
      };
  
      var popup = admin.pointup(button, options);
      button.popover('show');
      return false;
  }
  
  $(function(){
      $('.adm_user_list').val('{$oUserProfile->getId()}');
  });
</script>
{assign var="oSession" value=$oUserProfile->getSession()}
{assign var="oVote" value=$oUserProfile->getVote()}
<div class="panel panel-default">
  <div class="panel-body">

    <div class="col-md-6">
      <img src="{$oUserProfile->getAvatarUrl(100)}" alt="avatar"
        class="avatar img-responsive userid-{$oUserProfile->GetId()}"/>
    </div>
    <div class="col-md-6">
    <div class="nickname">{$oUserProfile->getLogin()}</div>
    {if $oUserProfile->getProfileName()}
    <div class="realname">{$oUserProfile->getProfileName()|escape:'html'}</div>
    {/if}
    <div class="nickname">ID: {$oUserProfile->getId()}</div>
    </div>
    <div class="col-md-12" id="user-profile-photo-{$oUserProfile->GetId()}">
      <button class="btn btn-primary btn-block" data-target="#user-profile-photo-img-{$oUserProfile->GetId()}"
        data-toggle="collapse"
        data-parent="#user-profile-photo-{$oUserProfile->GetId()}">
      <i class="icon icon-picture"></i>
      {$aLang.action.admin.user_photo}
      </button>
      <div class="collapse" id="user-profile-photo-img-{$oUserProfile->GetId()}">
        <img src="{$oUserProfile->getPhotoUrl(250)}" alt="photo"
          class="photo img-responsive userid-{$oUserProfile->GetId()}" />
      </div>
    </div>
    <div class="strength col-md-4">
      {$oLang->user_skill}
      <div class="total strong" id="user_skill_{$oUserProfile->getId()}">{$oUserProfile->getSkill()}</div>
    </div>
    <div class="voting col-md-4">
      {$oLang->user_rating}
      <div style="display: inline-block; margin: auto;">
        <i class="icon icon-arrow-up icon-green adm_vote_plus"
          onclick="admin.formVote(this, '{$nParamVoteValue}'); return false;"></i>
        <div class="total strong {if $oUserProfile->getRating()>=0}positive{else}negative{/if}"
          style="display: inline-block;">{if $oUserProfile->getRating()>0}
          +{/if}{$oUserProfile->getRating()}
        </div>
        <i class="icon icon-arrow-down icon-red adm_vote_minus"
          onclick="admin.formVote(this, '-{$nParamVoteValue}'); return false;"></i>
      </div>
    </div>
    <div class="voting col-md-4">
      {$oLang->user_vote_count}
      <div class="count strong">{$oUserProfile->getCountVote()}</div>
    </div>
    <div class="table table-striped-responsive"><table class="table table-striped">
      <tr>
        <th colspan="3">{$aLang.action.admin.user_voted} (cnt/sum)</th>
      </tr>
      <tr>
        <td class="lable">{$aLang.action.admin.user_voted_topics}</td>
        <td class="plus">
          {if $aUserVoteStat.cnt_topics_p}
          {$aUserVoteStat.cnt_topics_p} / {$aUserVoteStat.sum_topics_p}
          {/if}
        </td>
        <td class="minus">
          {if $aUserVoteStat.cnt_topics_m}
          {$aUserVoteStat.cnt_topics_m} /{$aUserVoteStat.sum_topics_m}
          {/if}
        </td>
      </tr>
      <tr>
        <td class="lable">{$aLang.action.admin.user_voted_users}</td>
        <td class="plus">
          {if $aUserVoteStat.cnt_users_p}
          {$aUserVoteStat.cnt_users_p} / {$aUserVoteStat.sum_users_p}
          {/if}
        </td>
        <td class="minus">
          {if $aUserVoteStat.cnt_users_m}
          {$aUserVoteStat.cnt_users_m} /{$aUserVoteStat.sum_users_m}
          {/if}
        </td>
      </tr>
      <tr>
        <td class="lable">{$aLang.action.admin.user_voted_comments}</td>
        <td class="plus">
          {if $aUserVoteStat.cnt_comments_p}
          {$aUserVoteStat.cnt_comments_p} /{$aUserVoteStat.sum_comments_p}
          {/if}
        </td>
        <td class="minus">
          {if $aUserVoteStat.cnt_comments_m}
          {$aUserVoteStat.cnt_comments_m} /{$aUserVoteStat.sum_comments_m}
          {/if}
        </td>
      </tr>
    </table></div>
  </div>
</div>
{if $oUserProfile->IsBannedByLogin()}
<div class="alert alert-block">
  {$aLang.action.admin.ban_upto}
  : {if $oUserProfile->getBanLine()}{$oUserProfile->getBanLine()}{else}{$aLang.action.admin.ban_unlim}{/if}
  <br/>
  <strong>{$oUserProfile->getBanComment()}</strong>
</div>
{/if}
<hr/>
{/block}
{block name="content-body-main"}
{/block}