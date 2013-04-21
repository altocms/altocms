<ul class="item-list">
    {foreach $aComments as $oComment}
        {$oUser=$oComment->getUser()}
        {$oTopic=$oComment->getTarget()}
        {if $oUser AND $oTopic}
            {$oBlog=$oTopic->getBlog()}

            {if $oBlog}
                <li class="js-title-comment"
                    title="{$oComment->getText()|strip_tags|trim|truncate:100:'...'|escape:'html'}">
                    <a href="{$oUser->getUserWebPath()}"><img src="{$oUser->getProfileAvatarPath(48)}" alt="avatar"
                                                              class="avatar"/></a>

                    <a href="{$oUser->getUserWebPath()}" class="author">{$oUser->getLogin()}</a> &rarr;
                    <a href="{$oBlog->getUrlFull()}" class="blog-name">{$oBlog->getTitle()|escape:'html'}</a> &rarr;
                    <a href="{if $oConfig->GetValue('module.comment.nested_per_page')}{router page='comments'}{else}{$oTopic->getUrl()}#comment{/if}{$oComment->getId()}">{$oTopic->getTitle()|escape:'html'}</a>

                    <p>
                        <time datetime="{date_format date=$oComment->getDate() format='c'}">{date_format date=$oComment->getDate() hours_back="12" minutes_back="60" now="60" day="day H:i" format="j F Y, H:i"}</time>
                        |
                        {$oTopic->getCountComment()} {$oTopic->getCountComment()|declension:$aLang.comment_declension:'russian'}
                    </p>
                </li>
            {/if}
        {/if}
    {/foreach}
</ul>


<footer class="b-widget-footer">
    <a href="{router page='rss'}allcomments/">RSS</a>
</footer>