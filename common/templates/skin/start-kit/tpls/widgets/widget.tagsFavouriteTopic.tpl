<section class="panel panel-default widget widget-type-foldable widget-type-favourite-topic">
    <div class="panel-body">

        <header class="widget-header">
            <a href="#" class="link-dotted"
               onclick="jQuery('#block_favourite_topic_content').toggle(); return false;">{$aLang.topic_favourite_tags_block}</a>
        </header>


        <div class="widget-content" id="block_favourite_topic_content">
            <ul class="nav nav-pills">
                <li class="active js-block-favourite-topic-tags-item" data-type="all"><a
                            href="#">{$aLang.topic_favourite_tags_block_all}</a></li>
                <li class="js-block-favourite-topic-tags-item" data-type="user"><a
                            href="#">{$aLang.topic_favourite_tags_block_user}</a></li>

                {hook run='widget_favourite_topic_tags_nav_item'}
            </ul>

            <div class="js-block-favourite-topic-tags-content" data-type="all">
                {if $aFavouriteTopicTags}
                    <ul class="list-unstyled list-inline tag-cloud word-wrap">
                        {foreach $aFavouriteTopicTags as $oTag}
                            <li>
                                <a class="tag-size-{$oTag->getSize()} {if $sFavouriteTag==$oTag->getText()}tag-current{/if}"
                                   title="{$oTag->getCount()}"
                                   href="{$oFavouriteUser->getProfileUrl()}favourites/topics/tag/{$oTag->getText()|escape:'url'}/">{$oTag->getText()}</a>
                            </li>
                        {/foreach}
                    </ul>
                {else}
                    <p class="text-muted">{$aLang.block_tags_empty}</p>
                {/if}
            </div>

            <div class="js-block-favourite-topic-tags-content" data-type="user" style="display: none;">
                {if $aFavouriteTopicUserTags}
                    <ul class="list-unstyled list-inline tag-cloud word-wrap">
                        {foreach $aFavouriteTopicUserTags as $oTag}
                            <li><a class="tag-size-{$oTag->getSize()}" title="{$oTag->getCount()}"
                                   href="{$oFavouriteUser->getProfileUrl()}favourites/topics/tag/{$oTag->getText()|escape:'url'}/">{$oTag->getText()}</a>
                            </li>
                        {/foreach}
                    </ul>
                {else}
                    <p class="text-muted">{$aLang.block_tags_empty}</p>
                {/if}
            </div>
        </div>

    </div>
</section>
