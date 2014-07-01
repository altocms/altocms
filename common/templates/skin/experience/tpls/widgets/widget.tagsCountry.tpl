 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if $aCountryList AND count($aCountryList)>0}
    <div class="panel panel-default sidebar raised widget widget-tag-country">
        <div class="panel-body">
            <h4 class="panel-header">
                <i class="fa fa-tag"></i>{$aLang.widget_country_tags}
            </h4>

            <div class="panel-content">
                <ul class="list-unstyled list-inline tag-cloud word-wrap">
                    {foreach $aCountryList as $oCountry}
                        <li>
                            <a class="link link-light-gray"
                               href="{router page='people'}country/{$oCountry->getId()}/">
                            <span class="tag-size tag-size-{$oCountry->getSize()}">
                                {$oCountry->getName()|escape:'html'}
                            </span>
                            </a>
                        </li>
                    {/foreach}
                </ul>
            </div>
        </div>
        <div class="panel-footer">
            <ul>
                <li>&nbsp;</li>
            </ul>
        </div>
    </div>

{/if}