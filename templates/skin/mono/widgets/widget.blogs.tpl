<div class="b-widget" id="block_blogs">
    <header class="b-widget-header">
    {$aLang.block_blogs}
        <div class="block-update js-block-blogs-update"></div>
    </header>


    <div class="b-widget-menu">
        <ul class="b-nav-pills js-block-blogs-nav">
            <li class="active js-block-blogs-item" data-type="top"><a href="#">{$aLang.block_blogs_top}</a></li>
        {if $oUserCurrent}
            <li class="js-block-blogs-item" data-type="join"><a href="#">{$aLang.block_blogs_join}</a></li>
            <li class="js-block-blogs-item" data-type="self"><a href="#">{$aLang.block_blogs_self}</a></li>
        {/if}
        </ul>
    </div>


    <div class="b-widget-content js-block-blogs-content">
    {$sBlogsTop}
    </div>

    <footer class="b-widget-footer">
        <a href="{router page='blogs'}">{$aLang.block_blogs_all}</a>
    </footer>
</div>

