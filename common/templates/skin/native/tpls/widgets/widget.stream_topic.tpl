{**
 * Прямой эфир
 * Последние топики
 *
 * @styles css/widgets.css
 *}

<div class="block-content">
    <ul class="item-list">
        {foreach $oTopics as $oTopic}
            {$oUser = $oTopic->getUser()}
            {$oBlog = $oTopic->getBlog()}
            <li class="js-title-topic" title="{$oTopic->getText()|strip_tags|trim|truncate:150:'...'|escape:'html'}">

                <a href="{$oUser->getProfileUrl()}" class="author">{$oUser->getLogin()}</a> &rarr;
                <a href="{$oTopic->getUrl()}">{$oTopic->getTitle()|escape:'html'}</a>

                <p>
                    <time datetime="{date_format date=$oTopic->getDate() format='c'}">
                        {date_format date=$oTopic->getDateAdd() hours_back="12" minutes_back="60" now="60" day="day H:i" format="j F Y, H:i"}
                    </time>
                    <span id="count-comments" class="block-count">{$oTopic->getCountComment()}</span>
                </p>
            </li>
        {/foreach}
    </ul>
</div>