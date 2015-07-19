{* Тема оформления Experience v.1.0  для Alto CMS      *}
{* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{if count($aStreamEvents)}
	{foreach $aStreamEvents as $oStreamEvent}
        {$oTarget = $oStreamEvent->getTarget()}
        {if $oTarget}
            {$oTargetTarget = $oTarget->getTarget()}
        {/if}
        {$sEventType = $oStreamEvent->getEventType()}
        {$oUser = $oStreamEvent->getUser()}
        {$bUserMale = $oUser->getProfileSex() != 'woman'}

		{if {date_format date=$oStreamEvent->getDateAdded() format="j F Y"} != $sDateLast}
			{assign var=sDateLast value={date_format date=$oStreamEvent->getDateAdded() format="j F Y"}}

			<li class="stream-header-date">
                <div class="panel-header">
                    {if {date_format date=$smarty.now format="j F Y"} == $sDateLast}
                        {$aLang.today}
                    {else}
                        {$sDateLast}
                    {/if}
                </div>
			</li>
		{/if}

		{$oUser=$oStreamEvent->getUser()}

		<li class="stream-item stream-item-type-{$sEventType}" style="display: block; clear: both;">
            <div class="row bob mal0 marr0 pab6 mab6">
                <div class="pull-left">
                    <a href="{$oUser->getProfileUrl()}"><img src="{$oUser->getAvatarUrl('small')}" alt="{$oUser->getDisplayName()}" class="avatar" /></a>
                </div>
                <div style="">
                    <div class="stream-body">

                        <a class="userlogo link link-dual link-lead link-clear" data-alto-role="popover"
                           data-api="user/{$oUser->getId()}/info"
                           data-api-param-tpl="default"
                           data-trigger="hover"
                           data-placement="top"
                           data-animation="true"
                           data-cache="true"
                           href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>

                        <span class="small text-muted date" title="{date_format date=$oStreamEvent->getDateAdded()}">{date_format date=$oStreamEvent->getDateAdded() hours_back="12" minutes_back="60" now="60" day="day H:i" format="j F Y, H:i"}</span>


                        {if $sEventType == 'add_topic'}
                            {if $bUserMale} {$aLang.stream_list_event_add_topic} {else} {$aLang.stream_list_event_add_topic_female} {/if}
                            <a href="{$oTarget->getUrl()}">{$oTarget->getTitle()|escape:'html'}</a>
                        {elseif $sEventType == 'add_comment'}
                            {if $bUserMale} {$aLang.stream_list_event_add_comment} {else} {$aLang.stream_list_event_add_comment_female} {/if}
                            <a href="{$oTarget->getLink()}">{if $oTargetTarget}{$oTargetTarget->getTitle()|escape:'html'}{/if}</a>
                            {$sTextEvent=$oTarget->getText()|strip_tags|truncate:200}
                            {if trim($sTextEvent)}
                                <div class="stream-comment-preview"><small>{$sTextEvent}</small></div>
                            {/if}
                        {elseif $sEventType == 'add_blog'}
                            {if $bUserMale} {$aLang.stream_list_event_add_blog} {else} {$aLang.stream_list_event_add_blog_female} {/if}
                            <a href="{$oTarget->getUrlFull()}">{$oTarget->getTitle()|escape:'html'}</a>
                        {elseif $sEventType == 'vote_blog'}
                            {if $bUserMale} {$aLang.stream_list_event_vote_blog} {else} {$aLang.stream_list_event_vote_blog_female} {/if}
                            <a href="{$oTarget->getUrlFull()}">{$oTarget->getTitle()|escape:'html'}</a>
                        {elseif $sEventType == 'vote_topic'}
                            {if $bUserMale} {$aLang.stream_list_event_vote_topic} {else} {$aLang.stream_list_event_vote_topic_female} {/if}
                            <a href="{$oTarget->getUrl()}">{$oTarget->getTitle()|escape:'html'}</a>
                        {elseif $sEventType == 'vote_comment'}
                            {if $bUserMale} {$aLang.stream_list_event_vote_comment} {else} {$aLang.stream_list_event_vote_comment_female} {/if}
                            <a href="{$oTarget->getLink()}">{if $oTargetTarget}{$oTargetTarget->getTitle()|escape:'html'}{/if}</a>
                        {elseif $sEventType == 'vote_user'}
                            {if $bUserMale} {$aLang.stream_list_event_vote_user} {else} {$aLang.stream_list_event_vote_user_female} {/if}
                            <a href="{$oTarget->getProfileUrl()}">{$oTarget->getDisplayName()}</a>
                        {elseif $sEventType == 'join_blog'}
                            {if $bUserMale} {$aLang.stream_list_event_join_blog} {else} {$aLang.stream_list_event_join_blog_female} {/if}
                            <a href="{$oTarget->getUrlFull()}">{$oTarget->getTitle()|escape:'html'}</a>
                        {elseif $sEventType == 'add_friend'}
                            {if $bUserMale} {$aLang.stream_list_event_add_friend} {else} {$aLang.stream_list_event_add_friend_female} {/if}
                            <a href="{$oTarget->getProfileUrl()}">{$oTarget->getDisplayName()}</a>
                        {elseif $sEventType == 'add_wall'}
                            {if $bUserMale} {$aLang.stream_list_event_add_wall} {else} {$aLang.stream_list_event_add_wall_female} {/if}
                            <a href="{$oTarget->getUrlWall()}">{$oTarget->getWallUser()->getDisplayName()}</a>
                            {$sTextEvent=$oTarget->getText()|strip_tags|truncate:200}
                            {if trim($sTextEvent)}
                                <div class="stream-comment-preview"><small>{$sTextEvent}</small></div>
                            {/if}
                        {else}
                            {hook run="stream_list_event_`$sEventType`" oStreamEvent=$oStreamEvent}
                        {/if}
                    </div>
                </div>
            </div>
		</li>
	{/foreach}

	<script type="text/javascript">
		jQuery(document).ready(function($){
			ls.stream.sDateLast = {json var=$sDateLast};
		});
	</script>
{/if}
