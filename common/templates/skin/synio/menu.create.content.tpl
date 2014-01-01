<script type="text/javascript">
    jQuery(window).load(function () {
        var trigger = $('#dropdown-create-trigger');
        var menu    = $('#dropdown-create-menu');
        var pos     = trigger.offset();

        // Dropdown
        menu.find('li.active').prependTo(menu).click(function () {
            menu.hide();
            return false;
        });
        menu.appendTo('body').css({ 'left': pos.left - 18, 'top': pos.top - 13, 'display': 'none' });

        trigger.click(function () {
            menu.toggle();
            return false;
        });

        // Hide menu
        $(document).click(function () {
            menu.hide();
        });

        $('body').on("click", "#dropdown-create-trigger, #dropdown-create-menu", function (e) {
            e.stopPropagation();
        });

        $(window).resize(function () {
            menu.css({ 'left': $('#dropdown-create-trigger').offset().left - 18 });
        });
    });
</script>


<div class="dropdown-create">
    {strip}
        <h2 class="page-header">{$aLang.block_create}
            <a href="#" class="dropdown-create-trigger link-dashed" id="dropdown-create-trigger">
                {if $sAction=='content'}
                    {foreach from=$aContentTypes item=oContentType}
                        {if $sEvent==$oContentType->getContentUrl()}{$oContentType->getContentTitle()|escape:'html'}{/if}
                    {/foreach}
                {elseif $sMenuItemSelect=='blog'}
                    {$aLang.blog_menu_create}
                {elseif $sMenuItemSelect=='talk'}
                    {$aLang.block_create_talk}
                {else}
                    {hook run='menu_create_item_select' sMenuItemSelect=$sMenuItemSelect}
                {/if}
            </a></h2>
    {/strip}

    <ul class="dropdown-menu-create" id="dropdown-create-menu" style="display: none">
        {foreach from=$aContentTypes item=oContentType}
            {if $oContentType->isAccessible()}
                <li {if $sEvent==$oContentType->getContentUrl()}class="active"{/if}>
                    <a href="{router page='content'}{$oContentType->getContentUrl()}/add/">{$oContentType->getContentTitle()|escape:'html'}</a>
                </li>
            {/if}
        {/foreach}
        <li {if $sMenuItemSelect=='blog'}class="active"{/if}>
            <a href="{router page='blog'}add/">{$aLang.blog_menu_create}</a>
        </li>
        <li {if $sMenuItemSelect=='talk'}class="active"{/if}>
            <a href="{router page='talk'}add/">{$aLang.block_create_talk}</a>
        </li>
        {hook run='menu_create_item' sMenuItemSelect=$sMenuItemSelect}
    </ul>
</div>

{if $sMenuItemSelect=='topic'}
    {if $iUserCurrentCountTopicDraft}
        <a href="{router page='content'}saved/" class="drafts">{$aLang.topic_menu_drafts}
            ({$iUserCurrentCountTopicDraft})</a>
    {/if}
{/if}

{hook run='menu_create' sMenuItemSelect=$sMenuItemSelect sMenuSubItemSelect=$sMenuSubItemSelect}