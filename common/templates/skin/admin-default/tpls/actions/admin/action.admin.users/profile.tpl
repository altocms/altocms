{extends file='./_users.tpl'}

{block name="content-body-sidebar" prepend}
<script type="text/javascript">
    admin.formVote = function (button, value) {

        button = $(button);
        value = parseInt(value);

        var options = {
            trigger:'manual',
            placement:'top',
            content:function () {
                var result = '';
                if (value < 0) {
                    result += '<input type="hidden" name="sign" value="-1" />';
                    result += '<i class="icon icon-minus icon-red adm_vote_sign"></i>';
                } else {
                    result += '<input type="hidden" name="sign" value="1" />';
                    result += '<i class="icon icon-plus icon-green adm_vote_sign"></i>';
                }
                result += '<input type="text" name="value" value="' + Math.abs(value) + '" class="adm_vote_value" />';
                result += '<button class="btn btn-mini btn-danger pull-right cancel"><i class="icon icon-close"></i></button>';
                result += '<button class="btn btn-mini btn-success pull-right confirm"><i class="icon icon-check icon-white"></i></button>'
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

//        var popup = admin.pointup(button, options);
        button.popover(options);
        button.popover('show');

        $('.confirm').off('click').on('click', function (e) {
            $(e.currentTarget).progressOn();
            var val = parseInt($(this).parent().find('input[name=value]').val() * $(this).parent().find('input[name=sign]').val());
            if (val) {
                var views = {
                    skill:$('.sidebar .strength .total'),
                    rating:$('.sidebar .voting .total'),
                    voteCount:$('.sidebar .voting .count')
                };
                admin.vote('user', '{$oUserProfile->getId()}', val, views, function () {
                    $(e.currentTarget).progressOff();
                });
            }
            button.popover('hide');
        });
        $(".cancel").off('click').on('click', function (e) {
            button.popover('hide');
        });

        return false;
    }

    $(function(){
        $('.adm_user_list').val('{$oUserProfile->getId()}');
    });
</script>

    {assign var="oSession" value=$oUserProfile->getSession()}
    {assign var="oVote" value=$oUserProfile->getVote()}

<div class="b-wbox">
<div class="user-profile">
    <div class="name">
        <div class="-box">
            <img src="{$oUserProfile->getAvatarUrl(100)}" alt="avatar"
                 class="avatar img-polaroid userid-{$oUserProfile->GetId()}"/>

            <div class="nickname">{$oUserProfile->getLogin()}</div>
            {if $oUserProfile->getProfileName()}
                <div class="realname">{$oUserProfile->getProfileName()|escape:'html'}</div>
            {/if}
			
            <div class="nickname">ID: {$oUserProfile->getId()}</div>
			
        </div>
        <br/>
        <div class="accordion" id="user-profile-photo-{$oUserProfile->GetId()}">
            <div class="accordion-group no-border">
			
            <div class="accordion-heading">
			<div class="b-wbox-header">
			<div class="buttons">
			<button class="btn btn-primary btn-mini " data-target="#user-profile-photo-img-{$oUserProfile->GetId()}"
                            data-toggle="collapse"
                            data-parent="#user-profile-photo-{$oUserProfile->GetId()}">
                        <i class="icon icon-picture"></i>
                        {$aLang.action.admin.user_photo}
                    </button>
			</div>
			</div>
			</div>
                <div class="accordion-body collapse" id="user-profile-photo-img-{$oUserProfile->GetId()}">
                    <img src="{$oUserProfile->getPhotoUrl(250)}" alt="photo"
                         class="photo img-polaroid userid-{$oUserProfile->GetId()}" />
                </div>
            </div>
        </div>

        <div class="row-fluid">
            <div class="strength span3">
                {$aLang.user_skill}
                <div class="total" id="user_skill_{$oUserProfile->getId()}">{$oUserProfile->getSkill()}</div>
            </div>

            <div class="voting span3">
                {$aLang.user_rating}

                <div style="display: inline-block; margin: auto;">
                    <i class="icon icon-arrow-up icon-green adm_vote_plus"
                       onclick="admin.formVote(this, '{$nParamVoteValue}'); return false;"></i>

                    <div class="total {if $oUserProfile->getRating()>=0}positive{else}negative{/if}"
                         style="display: inline-block;">{if $oUserProfile->getRating()>0}
                        +{/if}{$oUserProfile->getRating()}</div>

                    <i class="icon icon-arrow-down icon-red adm_vote_minus"
                       onclick="admin.formVote(this, '-{$nParamVoteValue}'); return false;"></i>
                </div>
            </div>

            <div class="voting span3">
                {$aLang.user_vote_count}
                <div class="count">{$oUserProfile->getCountVote()}</div>
            </div>
        </div>

    </div>

    <table class="table table-condensed vote-stat">
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
    </table>
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


<div class="switch-form-group">

</div>

{/block}

{block name="content-body-main"}

{/block}