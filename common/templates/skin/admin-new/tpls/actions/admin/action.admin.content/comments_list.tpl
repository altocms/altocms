{extends file='./comments.tpl'}

{block name="content-bar"}
<div class="col-md-12">
    <a href="#" class="btn btn-primary mb15 disabled"><i class="ion-plus-round"></i></a>
</div>
{/block}

{block name="content-body"}

<div class="col-md-12">

    <div class="panel panel-default">
        <div class="panel-body no-padding">
            <div class="table table-striped-responsive"><table class="table table-striped topics-list">
                <thead>
                <tr>
                    <th class="span1">ID</th>
                    <th>User</th>
                    <th>Text</th>
                    <th>Target</th>
                    <th>Date</th>
                    <th>Votes</th>
                    <th>Deleted</th>
                    <th class="span2"></th>
                </tr>
                </thead>

                <tbody>
                    {foreach $aComments as $oComment}
                        {$oTarget = $oComment->GetTarget()}
                    <tr>
                        <td class="number">{$oComment->GetId()}</td>
                        <td>
                            <a href="{router page='admin'}users-list/profile/{$oComment->GetUser()->GetId()}/">{$oComment->GetUser()->getDisplayName()}</a>
                        </td>
                        <td class="name">
                            {$oComment->GetText()}
                        </td>
                        <td>
                            {$oComment->GetTargetType()}
                            {if $oTarget}
                                : {if $oTarget->GetTitle()}
                                    {if $oTarget->GetUrlFull()}
                                        <a href="{$oTarget->GetUrlFull()}">{$oTarget->GetTitle()}</a>
                                    {else}
                                        {$oTarget->GetTitle()}
                                    {/if}
                                {/if}
                            {/if}
                        </td>
                        <td class="center">{$oComment->GetCommentDate()}</td>
                        <td class="number">{$oComment->GetCommentCountVote()}</td>
                        <td class="number">{if $oComment->GetCommentDeleted()}{$aLang.action.admin.word_yes}{/if}</td>
                        <td class="center">
                            <!--
                            <a href="{router page='topic'}edit/{$oComment->GetId()}/"
                               title="{$aLang.action.admin.topic_edit}">
                                <i class="ion-ios7-compose"></i></a>
                            <a href="#" title="{$aLang.action.admin.topic_delete}"
                               onclick="admin.comment.del('{$aLang.action.admin.topic_del_confirm}','{$oComment->GetTitle}','{$aTopic.topic_id}'); return false;">
                                <i class="ion-ios7-trash"></i></a>
                                -->
                        </td>
                    </tr>
                    {/foreach}
                </tbody>
            </table></div>
        </div>
    </div>

    {include file="inc.paging.tpl"}

</div>

{/block}