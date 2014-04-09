<div id="topbanner" role="banner" class="b-header-banner carousel slide" data-ride="carousel">

    <div class="container">
        <!-- Indicators -->
        <ol class="carousel-indicators">
            {foreach $aWidgetParams.images as $iKey=>$aImage}
                <li data-target="#topbanner" data-slide-to="{$iKey}" {if $aImage@first}class="active"{/if}></li>
            {/foreach}
        </ol>

        <!-- Wrapper for slides -->
        <div class="carousel-inner">
            {foreach $aWidgetParams.images as $iKey=>$aImage}
                <div class="item {if $aImage@first}active{/if}">
                    <div class="item-wrap">
                        <img src="{$aImage.image}" alt="{$aImage.title}">
                    </div>
                    {if $aImage.title}
                        <div class="carousel-caption">
                            {$aImage.title}
                        </div>
                    {/if}
                </div>
            {/foreach}
        </div>

        <!-- Controls -->
        <a class="left carousel-control" href="#topbanner" data-slide="prev">
            <span class="glyphicon glyphicon-chevron-left"></span>
        </a>
        <a class="right carousel-control" href="#topbanner" data-slide="next">
            <span class="glyphicon glyphicon-chevron-right"></span>
        </a>
    </div>
</div>