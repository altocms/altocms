The user <a href="{$oUserComment->getProfileUrl()}">{$oUserComment->getDisplayName()}</a> replied your comment in the topic
<b>«{$oTopic->getTitle()|escape:'html'}»</b>, you can read it by clicking on
<a href="{if Config::Get('module.comment.nested_per_page')}{router page='comments'}{else}{$oTopic->getUrl()}#comment{/if}{$oComment->getId()}">this link</a><br>

{if Config::Get('sys.mail.include_comment')}
	Message: <i>{$oComment->getText()}</i>	
{/if}
<br><br>
Best regards, site administration <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>