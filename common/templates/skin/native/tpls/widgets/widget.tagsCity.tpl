{**
 * Список городов в которых проживают пользователи
 *
 * @styles css/widgets.css
 *}

{extends file='./_aside.base.tpl'}

{block name='block_title'}{$aLang.block_city_tags}{/block}

{block name='block_content'}
    {if $aCityList && count($aCityList) > 0}
        {foreach $aCityList as $oCity}
            <strong><a href="{router page='people'}city/{$oCity->getId()}/">{$oCity->getName()|escape:'html'}</a>{if ! $oCity@last}, {/if}
            </strong>
        {/foreach}
    {else}
        No cities {* Language *}
    {/if}
{/block}
