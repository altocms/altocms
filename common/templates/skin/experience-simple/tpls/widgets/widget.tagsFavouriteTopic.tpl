 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

<div class="panel panel-default sidebar flat widget widget-favourite">
    <div class="panel-body">
        <div class="panel-header">
            <i class="fa fa-tags"></i>
            <a class="link link-lead link-clear link-dark"
               href="{router page='comments'}">{$aLang.topic_favourite_tags_block}</a>
        </div>
        <div class="panel-navigation">
            <ul>

                <li class="active js-block-favourite-topic-tags-item" data-type="all">
                    <a class="link link-dual link-lead link-clear"
                       onclick="
                        $(this).parents('.panel-navigation').find('.active').removeClass('active');
                        $(this).addClass('active').parent().addClass('active');
                        $('.js-block-favourite-topic-tags-content-2').hide();
                        $('.js-block-favourite-topic-tags-content-1').show();
                        return false;
                       "

                            href="#">{$aLang.topic_favourite_tags_block_all}</a></li>
                <li class="js-block-favourite-topic-tags-item" data-type="user">
                    <a class="link link-dual link-lead link-clear"
                       onclick="
                        $(this).parents('.panel-navigation').find('.active').removeClass('active');
                        $(this).addClass('active').parent().addClass('active');
                        $('.js-block-favourite-topic-tags-content-1').hide();
                        $('.js-block-favourite-topic-tags-content-2').show();
                        return false;
                       "
                            href="#">{$aLang.topic_favourite_tags_block_user}</a></li>

                {hook run='widget_favourite_topic_tags_nav_item'}
            </ul>
        </div>
        <div class="panel-content js-block-favourite-topic-tags-content-1">
            {if $aFavouriteTopicTags}
                    {foreach $aFavouriteTopicTags as $oTag}
                            <a class="link {if $sFavouriteTag==$oTag->getText()}tag-current{/if}"
                               title="{$oTag->getCount()}"
                               href="{$oTag->getLink()}">
                                <span class="tag-size tag-size-{$oTag->getSize()}">{$oTag->getText()}</span>
                            </a>
                    {/foreach}

            {else}
                <div class="bg-warning">{$aLang.widget_tags_empty}</div>
            {/if}
        </div>

        <div class="panel-content js-block-favourite-topic-tags-content-2" data-type="user" style="display: none;">
            {if $aFavouriteTopicUserTags}
                    {foreach $aFavouriteTopicUserTags as $oTag}
                        <a class="link" title="{$oTag->getCount()}" href="{$oTag->getLink()}">
                            <span class="tag-size tag-size-{$oTag->getSize()}">{$oTag->getText()}</span>
                        </a>
                    {/foreach}
            {else}
                <p class="text-muted">{$aLang.widget_tags_empty}</p>
            {/if}
        </div>
    </div>
</div>