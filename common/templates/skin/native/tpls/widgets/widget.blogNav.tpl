{**
 * Навигация по блогам с возможностью выбрать категорию и блог из этой категории
 *
 * @styles css/widgets.css
 *}


{extends file='./_aside.base.tpl'}

{block name='block_title'}{$aLang.block_blog_navigator}{/block}
{block name='block_type'}blog-navigation{/block}

{block name='block_content'}
    <a href="{router page='topic'}add/" data-type="modal-toggle"
       data-option-target="modal-write">{$aLang.block_create}</a>
    {if $aNavigatorBlogCategories}
        <p><select id="blog-navigator-category" class="width-full blog-navigator-categories"
                   onchange="ls.blog.loadBlogsByCategory($(this).val());">
                <option value="0">{$aLang.blog_category}</option>

                {foreach $aNavigatorBlogCategories as $oCategoryItem}
                    <option style="margin-left: {$oCategoryItem->getLevel()*20}px;"
                            value="{$oCategoryItem->getId()}">{$oCategoryItem->getTitle()|escape:'html'}</option>
                {/foreach}
            </select></p>
        <p><select id="blog-navigator-blog" class="width-full" disabled>
                <option value="0">{$aLang.blog}</option>

                {foreach $aNavigatorBlogs as $oBlogItem}
                    <option value="{$oBlogItem->getId()}"
                            data-url="{$oBlogItem->getUrlFull()}">{$oBlogItem->getTitle()|escape:'html'}</option>
                {/foreach}
            </select></p>
        <button onclick="ls.blog.navigatorGoSelectBlog();" class="button" id="blog-navigator-button"
                disabled>{$aLang.block_blog_navigator_button}</button>
    {else}
        No categories {* TODO: Language *}
    {/if}
{/block}