{if $aCityList AND count($aCityList)>0}
    <section class="panel panel-default widget">
        <div class="panel-body">

            <header class="widget-header">
                <h3 class="widget-title">{$aLang.block_city_tags}</h3>
            </header>

            <div class="widget-content">
                <ul class="list-unstyled list-inline tag-cloud word-wrap">
                    {foreach $aCityList as $oCity}
                        <li><a class="tag-size-{$oCity->getSize()}"
                               href="{router page='people'}city/{$oCity->getId()}/">{$oCity->getName()|escape:'html'}</a>
                        </li>
                    {/foreach}
                </ul>
            </div>

        </div>
    </section>
{/if}
