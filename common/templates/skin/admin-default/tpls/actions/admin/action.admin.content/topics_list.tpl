{extends file='./topics.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a href="#" class="btn btn-primary disabled"><i class="icon icon-plus"></i></a>
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
                            <a href="{R::GetLink("admin")}users-list/profile/{$oTopic->getUser()->getId()}/">{$oTopic->getUser()->getDisplayName()}</a>
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
                                <i class="icon icon-note"></i></a>
                            <a href="#" class="js-topic-delete" title="{$aLang.topic_delete}">
                                <i class="icon icon-trash"></i></a>
                        </td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>

    {include file="inc.paging.tpl"}

</div>

<script>
    $(function(){
        $('.js-topic-delete').click(function(){
            ls.modal.confirm(ls.lang.get('topic_delete_confirm_title'), ls.lang.get('topic_delete_confirm_text'), function() {
                document.location = '{R::GetLink("content")}delete/{$oTopic->getId()}/?security_key={$ALTO_SECURITY_KEY}';
            });
            return false;
        });
    });
</script>

{/block}