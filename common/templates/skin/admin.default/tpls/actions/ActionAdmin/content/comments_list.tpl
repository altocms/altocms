{extends file='./comments.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a href="#" class="btn btn-primary disabled"><i class="icon-plus-sign"></i></a>
    </div>
{/block}

{block name="content-body"}

<div class="span12">

    <div class="b-wbox">
        <div class="b-wbox-content nopadding">
            <table class="table table-striped table-condensed topics-list">
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
                            <a href="{router page='admin'}users-list/profile/{$aTopic.user_login}">{$oComment->GetUser()->GetLogin()}</a>
                        </td>
                        <td class="name">
                            <a href="{$oComment->GetCommentUrlFull()}">{$oComment->GetText()}</a>
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
                        <td class="number">{if $oComment->GetCommentDeleted()}{$aLang.action.admin.yes}{/if}</td>
                        <td class="center">
                            <!--
                            <a href="{router page='topic'}edit/{$oComment->GetId()}/"
                               title="{$aLang.action.admin.topic_edit}">
                                <i class="icon-edit"></i></a>
                            <a href="#" title="{$aLang.action.admin.topic_delete}"
                               onclick="admin.comment.del('{$aLang.action.admin.topic_del_confirm}','{$oComment->GetTitle}','{$aTopic.topic_id}'); return false;">
                                <i class="icon-remove"></i></a>
                                -->
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