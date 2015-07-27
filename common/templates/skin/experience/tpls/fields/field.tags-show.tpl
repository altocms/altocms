 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{$aTags=$oTopic->getTagsArray()}
{if E::IsUser() && $oFavourite}
    {$aFavouriteTags=$oFavourite->getTagsArray()}
{/if}

{if $aTags OR $aFavouriteTags}
    <ul class="topic-tags js-favourite-insert-after-form js-favourite-tags-topic-{$oTopic->getId()}">
        <li><i class="fa fa-tags"></i></li>
        {if $aTags}
            {foreach $aTags as $sTag}
                <li>
                    <a class="link link-lead link-light-gray link-clear" href="{router page='tag'}{$sTag|escape:'url'}/">{$sTag|escape:'html'}</a>{if !$sTag@last}, {/if}
                </li>
            {/foreach}
        {else}
            <li>{$aLang.topic_tags_empty}</li>
        {/if}


        {if E::IsUser()}
            {if $aFavouriteTags}
                {*{if $aTags}, {/if}*}
                {foreach $aFavouriteTags as $sTag}
                    <li class="topic-tags-user js-favourite-tag-user">
                        <a class="link link-lead link-light-gray link-clear"
                           href="{E::User()->getProfileUrl()}favourites/topics/tag/{$sTag|escape:'url'}/">{$sTag|escape:'html'}</a>{if !$sTag@last}, {/if}
                    </li>
                {/foreach}
            {/if}
            <li class="topic-tags-edit js-favourite-tag-edit"
                {if !$oFavourite}style="display:none;"{/if}>
                &nbsp;<a href="#" onclick="return ls.favourite.showEditTags({$oTopic->getId()},'topic',this);"
                   class="link link-lead link-light-gray link-clear">{$aLang.favourite_form_tags_button_show}</a>
            </li>
        {/if}
    <ul>
{/if}