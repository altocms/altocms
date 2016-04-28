 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<div class="panel panel-default sidebar raised widget-blogs">
    <div class="panel-body">
        <div class="panel-header">
            <i class="fa fa-suitcase"></i>{$aLang.widget_blogs}
        </div>
        <div class="panel-navigation">
            {if E::IsUser()}
                <ul >
                    <li class="active js-widget-blogs-item" data-type="top">
                        <a class="link link-dual link-lead link-clear" href="#">
                            {if C::Get('rating.enabled')}{$aLang.widget_blogs_top}{else}{$aLang.blog_menu_all_list}{/if}
                        </a>
                    </li>
                    <li class="js-widget-blogs-item" data-type="join"><a class="link link-dual link-lead link-clear" href="#">{$aLang.widget_blogs_join}</a></li>
                    <li class="js-widget-blogs-item" data-type="self"><a class="link link-dual link-lead link-clear" href="#">{$aLang.widget_blogs_self}</a></li>
                </ul>
            {/if}
        </div>
        <div class="panel-content js-widget-blogs-content">

            {$sBlogsTop}

        </div>
    </div>
    <div class="panel-footer">
        <a href="{R::GetLink("blogs")}" class="link link-dual link-lead link-clear">
            <i class="fa fa-suitcase"></i>{$aLang.widget_blogs_all}
        </a>
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