{**
 * Прямой эфир
 * Топики отсортированные по времени последнего комментария
 *
 * @styles css/widgets.css
 *}

<div class="block-content">
    <ul class="item-list">
        {foreach $aComments as $oComment}
            {$oUser = $oComment->getUser()}
            {$oTopic = $oComment->getTarget()}
            {$oBlog = $oTopic->getBlog()}
            <li class="js-title-comment"
                data-content="{$oComment->getText()|strip_tags|trim|truncate:100:'...'|escape:'html'}">

                <a href="{$oUser->getProfileUrl()}" class="author">{$oUser->getLogin()}</a> &rarr;
                <a href="{if $oConfig->GetValue('module.comment.nested_per_page')}{router page='comments'}{else}{$oTopic->getUrl()}#comment{/if}{$oComment->getId()}">
                    {$oTopic->getTitle()|escape:'html'}
                </a>

                <p>
                    <time datetime="{date_format date=$oComment->getDate() format='c'}">{date_format date=$oComment->getDate() hours_back="12" minutes_back="60" now="60" day="day H:i" format="j F Y, H:i"}</time>
                    <span id="count-comments" class="block-count">{$oTopic->getCountComment()}</span>
                </p>
            </li>
        {/foreach}
    </ul>
</div>
