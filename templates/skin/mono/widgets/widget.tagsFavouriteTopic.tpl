<section class="b-widget block-type-foldable block-type-favourite-topic">
    <header class="b-widget-header">
        <a href="#" class="link-dotted"
           onclick="jQuery('#block_favourite_topic_content').toggle(); return false;">{$aLang.topic_favourite_tags_block}</a>
    </header>

    <div class="b-widget-menu">
        <ul class="b-nav-pills">
            <li class="active js-block-favourite-topic-tags-item" data-type="all"><a
                    href="#">{$aLang.topic_favourite_tags_block_all}</a></li>
            <li class="js-block-favourite-topic-tags-item" data-type="user"><a
                    href="#">{$aLang.topic_favourite_tags_block_user}</a></li>

        {hook run='block_favourite_topic_tags_nav_item'}
        </ul>
    </div>

    <div class="b-widget-content" id="block_favourite_topic_content">


        <div class="js-block-favourite-topic-tags-content" data-type="all">
        {if $aFavouriteTopicTags}
            <ul class="tag-cloud word-wrap">
                {foreach from=$aFavouriteTopicTags item=oTag}
                    <li><a class="tag-size-{$oTag->getSize()} {if $sFavouriteTag==$oTag->getText()}tag-current{/if}"
                           title="{$oTag->getCount()}"
                           href="{$oFavouriteUser->getUserWebPath()}favourites/topics/tag/{$oTag->getText()|escape:'url'}/">{$oTag->getText()}</a>
                    </li>
                {/foreach}
            </ul>
            {else}
            <div class="notice-empty">{$aLang.block_tags_empty}</div>
        {/if}
        </div>

        <div class="js-block-favourite-topic-tags-content" data-type="user" style="display: none;">
        {if $aFavouriteTopicUserTags}
            <ul class="tag-cloud word-wrap">
                {foreach from=$aFavouriteTopicUserTags item=oTag}
                    <li><a class="tag-size-{$oTag->getSize()}" title="{$oTag->getCount()}"
                           href="{$oFavouriteUser->getUserWebPath()}favourites/topics/tag/{$oTag->getText()|escape:'url'}/">{$oTag->getText()}</a>
                    </li>
                {/foreach}
            </ul>
            {else}
            <div class="notice-empty">{$aLang.block_tags_empty}</div>
        {/if}
        </div>
    </div>
</section>