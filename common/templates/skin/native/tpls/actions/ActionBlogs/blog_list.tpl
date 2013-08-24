{**
 * Список блогов
 *
 * @styles css/tables.css
 *}

<table class="table table-blogs">
	{if $bBlogsUseOrder}
		<thead>
			<tr>
				<th class="cell-name cell-tab">
					<div class="cell-tab-inner {if $sBlogOrder=='blog_title'}active{/if}">
						<a href="{$sBlogsRootPage}?order=blog_title&order_way={if $sBlogOrder=='blog_title'}{$sBlogOrderWayNext}{else}{$sBlogOrderWay}{/if}" {if $sBlogOrder=='blog_title'}class="{$sBlogOrderWay}"{/if}><span>{$aLang.blogs_title}</span></a>
					</div>
				</th>

				{if $oUserCurrent}
					<th class="cell-join">{$aLang.blog_join_leave}</th>
				{/if}

				<th class="cell-readers cell-tab">
					<div class="cell-tab-inner {if $sBlogOrder=='blog_count_user'}active{/if}">
						<a href="{$sBlogsRootPage}?order=blog_count_user&order_way={if $sBlogOrder=='blog_count_user'}{$sBlogOrderWayNext}{else}{$sBlogOrderWay}{/if}" {if $sBlogOrder=='blog_count_user'}class="{$sBlogOrderWay}"{/if}>{$aLang.blogs_readers}</a>
					</div>
				</th>
				<th class="cell-rating cell-tab align-center">
					<div class="cell-tab-inner {if $sBlogOrder=='blog_rating'}active{/if}">
						<a href="{$sBlogsRootPage}?order=blog_rating&order_way={if $sBlogOrder=='blog_rating'}{$sBlogOrderWayNext}{else}{$sBlogOrderWay}{/if}" {if $sBlogOrder=='blog_rating'}class="{$sBlogOrderWay}"{/if}>{$aLang.blogs_rating}</a>
					</div>
				</th>
			</tr>
		</thead>
	{else}
		<thead>
			<tr>
				<th class="cell-name cell-tab"><div class="cell-tab-inner">{$aLang.blogs_title}</div></th>

				{if $oUserCurrent}
					<th class="cell-join cell-tab"><div class="cell-tab-inner">{$aLang.blog_join_leave}</div></th>
				{/if}

				<th class="cell-readers cell-tab"><div class="cell-tab-inner">{$aLang.blogs_readers}</div></th>
				<th class="cell-rating align-center cell-tab"><div class="cell-tab-inner">{$aLang.blogs_rating}</div></th>
			</tr>
		</thead>
	{/if}
	
	
	<tbody>
		{if $aBlogs}
			{foreach $aBlogs as $oBlog}
				{$oUserOwner = $oBlog->getOwner()}

				<tr>
					<td class="cell-name">
						<a href="{$oBlog->getUrlFull()}">
							<img src="{$oBlog->getAvatarPath(24)}" width="24" height="24" alt="avatar" class="avatar" />
						</a>
						
						<p>
							<a href="#" data-type="popover-toggle" data-option-url="{router page='ajax'}infobox/info/blog/" data-param-i-blog-id="{$oBlog->getId()}" class="icon-native-question-sign js-popover-default"></a>

							{if $oBlog->getType() == 'close'}
								<i title="{$aLang.blog_closed}" class="icon-lock"></i>
							{/if}

							<a href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape:'html'}</a>
						</p>
					</td>

					{if $oUserCurrent}
						<td class="cell-join">
							{if $oUserCurrent->getId() != $oBlog->getOwnerId() and $oBlog->getType() == 'open'}
								<a href="#" onclick="ls.blog.toggleJoin(this, {$oBlog->getId()}); return false;" class="link-dotted">
									{if $oBlog->getUserIsJoin()}
										{$aLang.blog_leave}
									{else}
										{$aLang.blog_join}
									{/if}
								</a>
							{else}
								&mdash;
							{/if}
						</td>
					{/if}

					<td class="cell-readers" id="blog_user_count_{$oBlog->getId()}">{$oBlog->getCountUser()}</td>
					<td class="cell-rating {if $sBlogOrder=='blog_rating'}{$sBlogOrderWay}{/if}">{$oBlog->getRating()}</td>
				</tr>
			{/foreach}
		{else}
			<tr>
				<td colspan="4">
					{* TODO: Fix error message *}
					{if $sBlogsEmptyList}
						{$sBlogsEmptyList}
					{/if}

					{if !$aBlogs && !$sBlogsEmptyList}
						{$aLang.blog_by_category_empty}
					{/if}
				</td>
			</tr>
		{/if}
	</tbody>
</table>