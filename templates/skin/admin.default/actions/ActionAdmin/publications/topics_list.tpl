{extends file='_index.tpl'}

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
                    <th>Title</th>
                    <th>Date</th>
                    <th>Votes</th>
                    <th>Rating</th>
                    <th class="span2">&nbsp;</th>
                </tr>
                </thead>

                <tbody>
                    {foreach $aTopics as $oTopic}
                    <tr>
                        <td class="number">{$oTopic->getId()}</td>
                        <td>
                            <a href="{router page='admin'}users/profile/{$oTopic->getUser()->getId()}/">{$oTopic->getUser()->getLogin()}</a>
                        </td>
                        <td class="name">
                            <a href="{$oTopic->getUrl()}">{$oTopic->getTitle()}</a>
                        </td>
                        <td class="center">{$oTopic->getTopicDateAdd()}</td>
                        <td class="number">{$oTopic->getTopicCountVote()}</td>
                        <td class="number">{$oTopic->getTopicRating()}</td>
                        <td class="center">
                            <a href="{$oTopic->getUrlEdit()}"
                               title="{$aLang.action.admin.topic_edit}">
                                <i class="icon-edit"></i></a>
                            <a href="{router page='content'}delete/{$oTopic->getId()}/?security_ls_key={$ALTO_SECURITY_KEY}" title="{$aLang.topic_delete}" onclick="return confirm('{$aLang.topic_delete_confirm}');" >
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