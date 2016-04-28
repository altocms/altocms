{if $aWall}
    <ul class="list-unstyled item-list">
        {foreach $aWall as $oWall}
            {$oUser=$oWall->getUser()}
            {$oWallUser=$oWall->getWallUser()}

            <li class="text-muted" title="{$oWall->getText()|strip_tags|trim|truncate:150:'...'|escape:'html'}">
                <p>
                    <a href="{$oUser->getProfileUrl()}" class="author">{$oUser->getDisplayName()}</a>
                    {if $oUser->getId() == $oWallUser->getId()}
                        {$aLang.widget_stream_on_his_wall}
                    {else}
                        {$aLang.widget_stream_on_wall}
                        <a href="{$oWallUser->getProfileUrl()}" class="author">{$oWallUser->getDisplayName()}</a>
                    {/if}

                    <time datetime="{date_format date=$oWall->getDateAdd() format='c'}">
                        · {date_format date=$oWall->getDateAdd() hours_back="12" minutes_back="60" now="60" day="day H:i" format="j F Y, H:i"}
                    </time>
                </p>
                <a href="{$oWall->getUrlWall()}" class="stream-topic">{$oWall->getText()|strip_tags|trim|truncate:150:'...'|escape:'html'}</a>
                <span class="stream-topic text-danger">{$oWall->getCountReply()}</span>
            </li>
        {/foreach}
    </ul>
{else}
    {$aLang.widget_stream_topics_no}
{/if}

<footer class="small text-muted">
    <a href="{R::GetLink("rss")}wall/">RSS</a>
</footer>
