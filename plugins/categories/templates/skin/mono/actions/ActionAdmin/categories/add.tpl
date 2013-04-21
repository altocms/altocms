{extends file='_index.tpl'}

{block name="content-body"}

<div class="span12">

    <form method="POST" name="typeadd" enctype="multipart/form-data" class="form-horizontal uniform">
        <input type="hidden" name="security_ls_key" value="{$ALTO_SECURITY_KEY}"/>

        <div class="b-wbox">
            <div class="b-wbox-header">
                {if $sEvent=='categoriesadd'}
                    <div class="b-wbox-header-title">{$aLang.plugin.categories.add_title}</div>
                {elseif $sEvent=='categoriesedit'}
                    <div class="b-wbox-header-title">{$aLang.plugin.categories.edit_title}
                        : {$oCategory->getCategoryTitle()|escape:'html'}</div>
                {/if}
            </div>
            <div class="b-wbox-content nopadding">

                <div class="control-group">
                    <label for="category_title" class="control-label">
                        {$aLang.plugin.categories.category_title}:
                    </label>

                    <div class="controls">
                        <input type="text" id="category_title" name="category_title"
                               value="{$_aRequest.category_title}"
                               class="input-text"/>
                        <span class="help-block">{$aLang.plugin.categories.category_title_notice}</span>
                    </div>
                </div>

                <div class="control-group">
                    <label for="category_url" class="control-label">
                        {$aLang.plugin.categories.category_url}:
                    </label>

                    <div class="controls">
                        <input type="text"
                               id="category_url" name="category_url" value="{$_aRequest.category_url}"
                               class="input-text"/>
                        <span class="help-block">{$aLang.plugin.categories.category_url_notice}</span>
                    </div>
                </div>

				<div class="control-group">
                    <label for="blogs" class="control-label">
                        {$aLang.plugin.categories.blogs}:
                    </label>

                    <div class="controls">
						{if $aBlogsCollective}
							{foreach from=$aBlogsCollective item=oBlog}
								{assign var=blog_id value=$oBlog->getId()}
								<div><input type="checkbox" name="blog[{$oBlog->getId()}]" value="1" {if $_aRequest.blog.$blog_id}checked{/if} class="input-text"/> - {$oBlog->getTitle()|escape}</div>
							{/foreach}
						{else}
							<span class="help-block">{$aLang.plugin.categories.no_blogs}</span>
						{/if}
                    </div>
                </div>

            </div>

            <div class="b-wbox-content nopadding">
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"
                            name="submit_category_add">{$aLang.plugin.categories.category_submit}</button>
                </div>
            </div>
        </div>
    </form>

</div>
{/block}