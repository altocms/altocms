{extends file="_index.tpl"}

{block name="vars"}
    {$menu="blog"}
{/block}

{block name="content"}
{foreach from=$aCategories item=oCategory}

<section class="block block-type-blog">
	<header class="block-header category-sep">
		<h3><a href="{$oCategory->getUrl()}">{$oCategory->getTitle()|escape:'html'}</a></h3>
	</header>

	<div class="block-content">

		<div class="category-data">
			<div class="category-popular">

				{foreach from=$oCategory->getTopics('popular',1,Config::Get('plugin.categories.topic_per_category_popular')) item=oTopic}

					<div class="category-popular-topic">
						{if $oTopic->getPreviewImage()}
							<img src="{$oTopic->getPreviewImageWebPath('229crop')}" alt="image" />
						{/if}
						<header class="topic-header">
							<h1 class="category-topic-title">
								<a title="{$oTopic->getTitle()|escape:'html'}" href="{$oTopic->getUrl()}">{$oTopic->getTitle()|escape:'html'}</a>
							</h1>
						</header>
						{$oTopic->getText()|strip_tags|trim|truncate:100:'...'}
					</div>

				{/foreach}

			</div>


			<div class="category-new">

				<span>{$aLang.plugin.categories.new}</span>

				<ul>
					{foreach from=$oCategory->getTopics('new',1,Config::Get('plugin.categories.topic_per_category_new')) item=oTopic}
					<li class="popular-topic">
						<a href="{$oTopic->getUrl()}" class="popular-topic-link">{$oTopic->getTitle()|escape:'html'}</a>
						<time class="popular-topic-date" datetime="{date_format date=$oTopic->getDateAdd() format='c'}" title="{date_format date=$oTopic->getDateAdd() format='j/m, H:i'}">{date_format date=$oTopic->getDateAdd() format="j.m, H:i"}</time>
					</li>
					{/foreach}
				</ul>

			</div>
		</div>

	</div>
</section>

{/foreach}
{/block}