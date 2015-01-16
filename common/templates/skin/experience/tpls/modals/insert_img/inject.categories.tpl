{if $aCategories}
    <ul class="image-categories-tree nav nav-stacked">
        {foreach $aCategories as $oCategory}
            <li class="category-show category-show-{$oCategory->getId()}">
                <a href="#"
                   data-category="{$oCategory->getId()}"
                   onclick="return false;">{$oCategory->getLabel()} <span>({$oCategory->getCount()})</span></a>
            </li>
        {/foreach}
    </ul>
{/if}