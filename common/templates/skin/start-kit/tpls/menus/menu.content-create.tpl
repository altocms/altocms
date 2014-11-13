<script type="text/javascript">
    jQuery(function ($) {
        var trigger = $('#dropdown-create-trigger');
        var menu = $('#dropdown-create-menu');
        var pos = trigger.position();

        // Dropdown
        menu.css({ 'left': pos.left - 5 });

        trigger.click(function () {
            menu.slideToggle();
            return false;
        });

        // Hide menu
        $(document).click(function () {
            menu.slideUp();
        });

        $('body').on("click", "#dropdown-create-trigger, #dropdown-create-menu", function (e) {
            e.stopPropagation();
        });
    });
</script>

<div class="dropdown-create">
        <div class="page-header">
            <h1>{$aLang.block_create}:
                <a href="#" class="dropdown-create-trigger link-dashed" id="dropdown-create-trigger">
                    {if $sMenuItemSelect!='blog'}
                        {$aLang.topic_menu_add}
                    {elseif $sMenuItemSelect=='blog'}
                        {$aLang.blog_menu_create}
                    {else}
                        {hook run='menu_create_item_select' sMenuItemSelect=$sMenuItemSelect}
                    {/if}
                </a>
            </h1>
        </div>

    <ul class="dropdown-menu" id="dropdown-create-menu" style="display: none">
        <li {if $sMenuItemSelect!='blog'}class="active"{/if}><a
                    href="{router page='content'}add/">{$aLang.topic_menu_add}</a></li>
        <li {if $sMenuItemSelect=='blog'}class="active"{/if}><a
                    href="{router page='blog'}add/">{$aLang.blog_menu_create}</a></li>
        {hook run='menu_create_item' sMenuItemSelect=$sMenuItemSelect}
    </ul>
</div>

{if $sMenuItemSelect=='topic'}
    <ul class="nav nav-pills nav-filter-wrapper">
        {foreach from=$aContentTypes item=oContentType}
            {if $oContentType->isAccessible()}
                <li {if $sMenuSubItemSelect==$oContentType->getContentUrl()}class="active"{/if}>
                    <a href="{router page='content'}{$oContentType->getContentUrl()}/add/">{$oContentType->getContentTitle()|escape:'html'}</a>
                </li>
            {/if}
        {/foreach}
        {if $iUserCurrentCountTopicDraft}
            <li class="pull-right{if $sMenuSubItemSelect=='drafts'} active{/if}">
                <a href="{router page='content'}drafts/">{$aLang.topic_menu_drafts} ({$iUserCurrentCountTopicDraft})</a>
            </li>
        {/if}
        {hook run='menu_create_topic_item'}
    </ul>
{/if}

{hook run='menu_create' sMenuItemSelect=$sMenuItemSelect sMenuSubItemSelect=$sMenuSubItemSelect}
