<div class="panel panel-default widget" id="block_blogs">
    <div class="panel-body">

        <header class="widget-header">
            <h3 class="widget-title">{$aLang.block_blogs}</h3>
        </header>

        <div class="widget-content">
            {if E::IsUser()}
                <ul class="nav nav-pills js-block-blogs-nav">
                    <li class="active js-widget-blogs-item" data-type="top"><a href="#">{$aLang.block_blogs_top}</a></li>
                    <li class="js-widget-blogs-item" data-type="join"><a href="#">{$aLang.block_blogs_join}</a></li>
                    <li class="js-widget-blogs-item" data-type="self"><a href="#">{$aLang.block_blogs_self}</a></li>
                </ul>
            {/if}

            <div class="js-widget-blogs-content">
                {$sBlogsTop}
            </div>

            <footer>
                <a href="{router page='blogs'}" class="small">{$aLang.block_blogs_all}</a>
            </footer>
        </div>

    </div>
</div>
<script>
    jQuery(document).ready(function(){
        $('.js-widget-blogs-item').click(function(){
            ls.widgets.load(this, 'blogs');
            return false;
        });
    });
</script>