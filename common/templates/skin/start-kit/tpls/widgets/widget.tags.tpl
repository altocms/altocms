<section class="panel panel-default widget">
    <div class="panel-body">

        <header class="widget-header">
            <h3 class="widget-title">{$aLang.widget_tags}</h3>
        </header>

        <div class="widget-content">
            {if E::IsUser()}
                <ul class="nav nav-pills">
                    <li class="active">
                        <a href="#" data-toggle="tab" data-target=".js-widget-tags-all">{$aLang.topic_favourite_tags_block_all}</a>
                    </li>
                    <li>
                        <a href="#" data-toggle="tab" data-target=".js-widget-tags-user">{$aLang.topic_favourite_tags_block_user}</a>
                    </li>

                    {hook run='widget_tags_nav_item'}
                </ul>
            {/if}

            <div class="tab-content">
                <div class="tab-pane active js-widget-tags-all">
                    {if $aTags}
                        <ul class="list-unstyled list-inline tag-cloud word-wrap">
                            {foreach $aTags as $oTag}
                                <li>
                                    <a class="tag-size-{$oTag->getSize()}" href="{$oTag->getLink()}">
                                        {$oTag->getText()|escape:'html'}
                                    </a>
                                </li>
                            {/foreach}
                        </ul>
                    {else}
                        <div class="notice-empty">{$aLang.widget_tags_empty}</div>
                    {/if}
                </div>

                {if E::IsUser()}
                    <div class="tab-pane js-widget-tags-user">
                        {if $aTagsUser}
                            <ul class="list-unstyled list-inline tag-cloud word-wrap">
                                {foreach $aTagsUser as $oTag}
                                    <li>
                                        <a class="tag-size-{$oTag->getSize()}" href="{$oTag->getLink()}">
                                            {$oTag->getText()|escape:'html'}
                                        </a>
                                    </li>
                                {/foreach}
                            </ul>
                        {else}
                            <p class="text-muted">{$aLang.widget_tags_empty}</p>
                        {/if}
                    </div>
                {/if}
            </div>
        </div>

    </div>
</section>
