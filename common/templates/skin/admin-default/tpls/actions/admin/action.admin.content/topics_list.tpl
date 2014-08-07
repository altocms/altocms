{extends file='./topics.tpl'}

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
                    <th>Title</th>
                    <th>URL</th>
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
                            <a href="{router page='admin'}users-list/profile/{$oTopic->getUser()->getId()}/">{$oTopic->getUser()->getDisplayName()}</a>
                        </td>
                        <td class="name">
                            <a href="{$oTopic->getUrl()}">{$oTopic->getTitle()}</a>
                        </td>
                        <td class="name">
                            <a href="{$oTopic->getUrl()}">/{$oTopic->getUrl(null, false)}</a>
                        </td>
                        <td class="center">{$oTopic->getTopicDateAdd()}</td>
                        <td class="number">{$oTopic->getTopicCountVote()}</td>
                        <td class="number">{$oTopic->getTopicRating()}</td>
                        <td class="center">
                            <a href="{$oTopic->getUrlEdit()}" title="{$aLang.action.admin.topic_edit}">
                                <i class="ion-ios7-compose"></i></a>
                            <a href="#" class="js-topic-delete" title="{$aLang.topic_delete}">
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

<script>
    $(function(){
        $('.js-topic-delete').click(function(){
            ls.modal.confirm(ls.lang.get('topic_delete_confirm_title'), ls.lang.get('topic_delete_confirm_text'), function() {
                document.location = '{router page='content'}delete/{$oTopic->getId()}/?security_key={$ALTO_SECURITY_KEY}';
            });
            return false;
        });
    });
</script>

{/block}