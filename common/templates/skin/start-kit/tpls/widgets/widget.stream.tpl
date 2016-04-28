<section class="panel panel-default widget widget-type-stream">
    <div class="panel-body">

        <header class="widget-header">
            <h3 class="widget-title">
                <a href="{R::GetLink("comments")}" title="{$aLang.widget_stream_comments_all}">{$aLang.widget_stream}</a>
            </h3>
        </header>

        <div class="widget-content">
            <ul class="nav nav-pills widget-content-navs js-widget-stream-navs">
                {foreach $aWidgetParams.items as $sKey=>$aItem}
                    <li {if $aItem@first}class="active"{/if}>
                        <a href="#" data-toggle="tab" data-type="{$aItem.type}">{$aLang[$aItem.text]}</a>
                    </li>
                {/foreach}
                {hook run='widget_stream_nav_item'}
            </ul>
            <div class="widget-content-body js-widget-stream-content">
            </div>
        </div>

    </div>
</section>
