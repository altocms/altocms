 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

<div class="panel panel-default sidebar flat widget widget-type-tags">
    <div class="panel-body">
        <div class="panel-header">
            <i class="fa fa-tag"></i>{$aLang.widget_tags}
        </div>

        {if E::IsUser()}
            <div class="panel-navigation">
                <ul>
                    <li class="active">
                        <a href="#" data-toggle="tab" class="link link-dual link-lead link-clear" data-target=".js-widget-tags-all">{$aLang.topic_favourite_tags_block_all}</a>
                    </li>
                    <li>
                        <a href="#" data-toggle="tab" class="link link-dual link-lead link-clear" data-target=".js-widget-tags-user">{$aLang.topic_favourite_tags_block_user}</a>
                    </li>

                    {hook run='widget_tags_nav_item'}
                </ul>
            </div>
        {/if}

        <div class="panel-content tab-content">

            <div class="tab-pane active js-widget-tags-all">
                {if $aTags}
                    <ul class="list-unstyled list-inline tag-cloud word-wrap">
                        {foreach $aTags as $oTag}
                            <li>
                                <a class="link" href="{$oTag->getLink()}">
                                    <span class="tag-size tag-size-{$oTag->getSize()}">
                                        {$oTag->getText()|escape:'html'}
                                    </span>
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                {else}
                    <div class="bg-warning">{$aLang.widget_tags_empty}</div>
                {/if}
            </div>


            {if E::IsUser()}
                <div class="tab-pane js-widget-tags-user">
                    {if $aTagsUser}
                        <ul class="list-unstyled list-inline tag-cloud word-wrap">
                            {foreach $aTagsUser as $oTag}
                                <li>
                                    <a class="link" href="{$oTag->getLink()}">
                                        <span class="tag-size tag-size-{$oTag->getSize()}">
                                            {$oTag->getText()|escape:'html'}
                                        </span>
                                    </a>
                                </li>
                            {/foreach}
                        </ul>
                    {else}
                    <div class="bg-warning">{$aLang.widget_tags_empty}</div>
                    {/if}
                </div>
            {/if}
        </div>
    </div>

    <div class="panel-footer">
        <a href="{router page='tag'}" class="link link-dual link-lead link-clear">
            <i class="fa fa-tags"></i>{$aLang.widget_tags_all}
        </a>
    </div>
</div>

