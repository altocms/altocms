 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<div id="topbanner" role="banner" class="b-header-banner">

    {hook run='header_banner_begin'}

    <div class="container">
        <div class="b-header-banner-inner jumbotron" style="{$aWidgetParams.style}">
            <div><a href="{Config::Get('path.root.url')}">{$aWidgetParams.title}</a></div>
        </div>
    </div>

    {hook run='header_banner_end'}

</div>
