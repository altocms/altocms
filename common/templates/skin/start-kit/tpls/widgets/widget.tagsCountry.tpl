{if $aCountryList AND count($aCountryList)>0}
    <section class="panel panel-default widget">
        <div class="panel-body">

            <header class="widget-header">
                <h3 class="widget-title">{$aLang.widget_country_tags}</h3>
            </header>

            <div class="widget-content">
                <ul class="list-unstyled list-inline tag-cloud word-wrap">
                    {foreach $aCountryList as $oCountry}
                        <li><a class="tag-size-{$oCountry->getSize()}"
                               href="{R::GetLink("people")}country/{$oCountry->getId()}/">{$oCountry->getName()|escape:'html'}</a>
                        </li>
                    {/foreach}
                </ul>
            </div>

        </div>
    </section>
{/if}
