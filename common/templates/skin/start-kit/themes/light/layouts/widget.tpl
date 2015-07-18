{block name="widget_vars"}
{/block}

<section class="panel panel-default widget {$sWidgetClass}">
    <div class="panel-body">

        {block name="widget_header"}
        <header class="widget-header">
        </header>
        {/block}

        {hook run='widget_stream_nav_item' assign="sItemsHook"}

        {block name="widget_content"}
        <div class="widget-content">
            {block name="widget_content_nav"}
            {/block}

            {block name="widget_content_body"}
            {/block}
        </div>
        {/block}

        {block name="widget_footer"}
            <footer class="widget-footer">
            </footer>
        {/block}

    </div>
</section>
