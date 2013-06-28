{include file='header.tpl'}
{assign var="oUserOwner" value=$oBlog->getOwner()}
{assign var="oVote" value=$oBlog->getVote()}


<script type="text/javascript">
	jQuery(function($){
		ls.lang.load({lang_load name="blog_fold_info,blog_expand_info"});
	});
</script>


{if $oUserCurrent and $oUserCurrent->isAdministrator()}
	<div id="blog_delete_form" class="modal">
		<header class="modal-header">
			<h3>{$aLang.blog_admin_delete_title}</h3>
			<a href="#" class="close jqmClose"></a>
		</header>
		
		
		<form action="{router page='blog'}delete/{$oBlog->getId()}/" method="POST" class="modal-content">
			<p><label for="topic_move_to">{$aLang.blog_admin_delete_move}:</label>
			<select name="topic_move_to" id="topic_move_to" class="input-width-full">
				<option value="-1">{$aLang.blog_delete_clear}</option>
				{if $aBlogs}
					<optgroup label="{$aLang.blogs}">
						{foreach from=$aBlogs item=oBlogDelete}
							<option value="{$oBlogDelete->getId()}">{$oBlogDelete->getTitle()|escape:'html'}</option>
						{/foreach}
					</optgroup>
				{/if}
			</select></p>
			
			<input type="hidden" value="{$ALTO_SECURITY_KEY}" name="security_ls_key" />
			<button type="submit" class="btn btn-primary">{$aLang.blog_delete}</button>
		</form>
	</div>
{/if}



<div class="blog">
	<header class="blog-header">
		<div id="vote_area_blog_{$oBlog->getId()}" class="btn-group vote {if $oBlog->getRating() > 0}vote-count-positive{elseif $oBlog->getRating() < 0}vote-count-negative{/if}">
            {if {cfg name='view.vote_blog.type'} == 'plus_minus'}
                {*<a href="#" class="vote-up btn" onclick="return ls.vote.vote({$oBlog->getId()},this,1,'blog');"><i class="icon-plus"></i></a>*}
                <div id="vote_total_blog_{$oBlog->getId()}" class="vote-count count btn">{if $oBlog->getRating() > 0}+{/if}{$oBlog->getRating()}</div>
                {*<a href="#" class="vote-down btn" onclick="return ls.vote.vote({$oBlog->getId()},this,-1,'blog');"><i class="icon-minus"></i></a>*}
            {elseif {cfg name='view.vote_blog.type'} == 'minus_plus'}
                {*<a href="#" class="vote-down btn" onclick="return ls.vote.vote({$oBlog->getId()},this,-1,'blog');"><i class="icon-minus"></i></a>*}
                <div id="vote_total_blog_{$oBlog->getId()}" class="vote-count count btn">{if $oBlog->getRating() > 0}+{/if}{$oBlog->getRating()}</div>
                {*<a href="#" class="vote-up btn" onclick="return ls.vote.vote({$oBlog->getId()},this,1,'blog');"><i class="icon-plus"></i></a>*}
            {/if}
		</div>
		
		
		<img src="{$oBlog->getAvatarPath(48)}" alt="avatar" class="avatar" />
		
		
		<h2>
            {if $oBlog->getType()=='close'}<i title="{$aLang.blog_closed}" class="icon icon-lock"></i> {/if}

            {if $oUserCurrent and ($oUserCurrent->getId()==$oBlog->getOwnerId() or $oUserCurrent->isAdministrator() or $oBlog->getUserIsAdministrator() )}
                <div class="btn-group">
                    <a class="btn btn-warning btn-mini dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="{router page='blog'}edit/{$oBlog->getId()}/" title="{$aLang.blog_admin}" class="edit"><i class="icon-user"></i>{$aLang.blog_admin}</a>
                        </li>
                        <li>
                            <a href="{router page='blog'}admin/{$oBlog->getId()}/" title="{$aLang.blog_edit}" class="edit"><i class="icon-edit"></i>{$aLang.blog_edit}</a>
                        </li>
                        <li>
                            {if $oUserCurrent->isAdministrator()}
                                <a href="#" title="{$aLang.blog_delete}" id="blog_delete_show" class="delete"><i class="icon-remove"></i>{$aLang.blog_delete}</a>
                            {else}
                                <a href="{router page='blog'}delete/{$oBlog->getId()}/?security_ls_key={$ALTO_SECURITY_KEY}" title="{$aLang.blog_delete}" onclick="return confirm('{$aLang.blog_admin_delete_confirm}');" ><i class="icon-remove"></i>{$aLang.blog_delete}</a>
                            {/if}
                        </li>
                    </ul>
                </div>
            {/if}

            {$oBlog->getTitle()|escape:'html'}
        </h2>
		
		
		<ul class="actions">
			<li><a href="{router page='rss'}blog/{$oBlog->getUrl()}/" class="rss"><i class="icon-signal-rss" title="{$aLang.blog_rss}"></i></a></li>
			{if $oUserCurrent and $oUserCurrent->getId()!=$oBlog->getOwnerId()}
				<li>
                    <a href="#" onclick="ls.blog.toggleJoin(this,{$oBlog->getId()}); return false;" class="link-dotted">
                        {if $oBlog->getUserIsJoin()}
                            <i class="icon-off"></i>{$aLang.blog_leave}
                        {else}
                            <i class="icon-on"></i>{$aLang.blog_join}
                        {/if}
                    </a>
                </li>
			{/if}
		</ul>
	</header>
	
	
	<div class="blog-more-content" id="blog-more-content" style="display: none;">
		<div class="blog-content">
			<p class="blog-description">{$oBlog->getDescription()}</p>
		</div>
		
		
		<footer class="blog-footer">
			{hook run='blog_info_begin' oBlog=$oBlog}
			<strong>{$aLang.blog_user_administrators} ({$iCountBlogAdministrators}):</strong>							
			<a href="{$oUserOwner->getUserWebPath()}" class="user"><i class="icon-user"></i>{$oUserOwner->getLogin()}</a>
			{if $aBlogAdministrators}			
				{foreach from=$aBlogAdministrators item=oBlogUser}
					{assign var="oUser" value=$oBlogUser->getUser()}  									
					<a href="{$oUser->getUserWebPath()}" class="user"><i class="icon-user"></i>{$oUser->getLogin()}</a>
				{/foreach}	
			{/if}<br />		

			
			<strong>{$aLang.blog_user_moderators} ({$iCountBlogModerators}):</strong>
			{if $aBlogModerators}						
				{foreach from=$aBlogModerators item=oBlogUser}  
					{assign var="oUser" value=$oBlogUser->getUser()}									
					<a href="{$oUser->getUserWebPath()}" class="user"><i class="icon-user"></i>{$oUser->getLogin()}</a>
				{/foreach}							
			{else}
				{$aLang.blog_user_moderators_empty}
			{/if}<br />
			
			
			<strong>{$aLang.blog_user_readers} ({$iCountBlogUsers}):</strong>
			{if $aBlogUsers}
				{foreach from=$aBlogUsers item=oBlogUser}
					{assign var="oUser" value=$oBlogUser->getUser()}
					<a href="{$oUser->getUserWebPath()}" class="user"><i class="icon-user"></i>{$oUser->getLogin()}</a>
				{/foreach}
				
				{if count($aBlogUsers) < $iCountBlogUsers}
					<br /><a href="{$oBlog->getUrlFull()}users/">{$aLang.blog_user_readers_all}</a>
				{/if}
			{else}
				{$aLang.blog_user_readers_empty}
			{/if}
			{hook run='blog_info_end' oBlog=$oBlog}
		</footer>
	</div>
	
	<a href="#" class="blog-more" id="blog-more" onclick="return ls.blog.toggleInfo()">{$aLang.blog_expand_info}</a>
</div>

{hook run='blog_info' oBlog=$oBlog}

<div class="nav-filter-wrapper blog-nav">
	<ul>
		<li {if $sMenuSubItemSelect=='good'}class="active"{/if}><a href="{$sMenuSubBlogUrl}" class="btn">{$aLang.blog_menu_collective_good}</a></li>
        {if $iCountTopicsBlogNew>0}<li {if $sMenuSubItemSelect=='new'}class="active"{/if}><a href="{$sMenuSubBlogUrl}new/" class="btn">{$aLang.blog_menu_collective_new} +{$iCountTopicsBlogNew}</a></li>{/if}
		<li class="btn-group{if $sMenuSubItemSelect=='discussed'} active{/if}">
            <a href="{$sMenuSubBlogUrl}discussed/" class="btn">{$aLang.blog_menu_collective_discussed}</a>
            <a href="#" class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></a>
            <ul class="dropdown-menu">
                <li {if $sPeriodSelectCurrent=='1'}class="active"{/if}><a href="{$sMenuSubBlogUrl}discussed/?period=1">{$aLang.blog_menu_top_period_24h}</a></li>
                <li {if $sPeriodSelectCurrent=='7'}class="active"{/if}><a href="{$sMenuSubBlogUrl}discussed/?period=7">{$aLang.blog_menu_top_period_7d}</a></li>
                <li {if $sPeriodSelectCurrent=='30'}class="active"{/if}><a href="{$sMenuSubBlogUrl}discussed/?period=30">{$aLang.blog_menu_top_period_30d}</a></li>
                <li {if $sPeriodSelectCurrent=='all'}class="active"{/if}><a href="{$sMenuSubBlogUrl}discussed/?period=all">{$aLang.blog_menu_top_period_all}</a></li>
            </ul>
        </li>
		<li class="btn-group{if $sMenuSubItemSelect=='top'} active{/if}">
            <a href="{$sMenuSubBlogUrl}top/" class="btn">{$aLang.blog_menu_collective_top}</a>
            <a href="#" class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></a>
            <ul class="dropdown-menu">
                <li {if $sPeriodSelectCurrent=='1'}class="active"{/if}><a href="{$sMenuSubBlogUrl}top/?period=1">{$aLang.blog_menu_top_period_24h}</a></li>
                <li {if $sPeriodSelectCurrent=='7'}class="active"{/if}><a href="{$sMenuSubBlogUrl}top/?period=7">{$aLang.blog_menu_top_period_7d}</a></li>
                <li {if $sPeriodSelectCurrent=='30'}class="active"{/if}><a href="{$sMenuSubBlogUrl}top/?period=30">{$aLang.blog_menu_top_period_30d}</a></li>
                <li {if $sPeriodSelectCurrent=='all'}class="active"{/if}><a href="{$sMenuSubBlogUrl}top/?period=all">{$aLang.blog_menu_top_period_all}</a></li>
            </ul>
        </li>
		{hook run='menu_blog_blog_item'}
	</ul>
</div>




{if $bCloseBlog}
	{$aLang.blog_close_show}
{else}
	{include file='topic_list.tpl'}
{/if}


{include file='footer.tpl'}