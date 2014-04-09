{$aTags=$oTopic->getTagsArray()}
{if E::IsUser() && $oFavourite}
    {$aFavouriteTags=$oFavourite->getTagsArray()}
{/if}

{if $aTags OR $aFavouriteTags}
<ul class="small text-muted list-unstyled list-inline topic-tags js-favourite-insert-after-form js-favourite-tags-topic-{$oTopic->getId()}">
    <li><span class="glyphicon glyphicon-tags"></span></li>

    {strip}
        {if $aTags}
            {foreach $aTags as $sTag}
                <li>
                    <a rel="tag" href="{router page='tag'}{$sTag|escape:'url'}/">{$sTag|escape:'html'}</a>{if !$sTag@last}, {/if}
                </li>
            {/foreach}
        {else}
            <li>{$aLang.topic_tags_empty}</li>
        {/if}

        {if E::IsUser()}
            {if $aFavouriteTags}
                {foreach $aFavouriteTags as $sTag}
                    <li class="topic-tags-user js-favourite-tag-user">
                        <a rel="tag" href="{E::User()->getProfileUrl()}favourites/topics/tag/{$sTag|escape:'url'}/">{$sTag|escape:'html'}</a>{if !$sTag@last}, {/if}
                    </li>
                {/foreach}
            {/if}
            <li class="topic-tags-edit js-favourite-tag-edit"
                {if !$oFavourite}style="display:none;"{/if}>
                <a href="#" onclick="return ls.favourite.showEditTags({$oTopic->getId()},'topic',this);"
                   class="link-dotted">{$aLang.favourite_form_tags_button_show}</a>
            </li>
        {/if}
    {/strip}
</ul>
{/if}