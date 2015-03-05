{*<ul class="image-categories-tree nav nav-pills nav-stacked">*}
    {*<li class="category-show active">*}
        {*<a href="#"*}
           {*data-category="insert-from-pc"*}
           {*onclick="return false;">{$aLang.insertimg_from_pc}</a>*}
    {*</li>*}
    {*<li class="category-show">*}
        {*<a href="#"*}
           {*data-category="insert-from-link"*}
           {*onclick="return false;">{$aLang.insertimg_from_link}</a>*}
    {*</li>*}
    {*{if $aCategories}*}
        {*{foreach $aCategories as $oCategory}*}
            {*<li class="category-show category-show-{$oCategory->getId()}">*}
                {*<a href="#"*}
                   {*data-category="{$oCategory->getId()}"*}
                   {*onclick="return false;">{$oCategory->getLabel()} <span>({$oCategory->getCount()})</span></a>*}
            {*</li>*}
        {*{/foreach}*}
    {*{/if}*}
{*</ul>*}

{if E::IsUser()}
    {menu id='image_insert' class='js-image-categories-tree image-categories-tree nav nav-pills nav-stacked'}
{/if}
