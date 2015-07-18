Пользователь <a href="{$oUserComment->getProfileUrl()}">{$oUserComment->getDisplayName()}</a> ответил на ваш комментарий в топике
<b>«{$oTopic->getTitle()|escape:'html'}»</b>, прочитать его можно перейдя по
<a href="{if Config::Get('module.comment.nested_per_page')}{router page='comments'}{else}{$oTopic->getUrl()}#comment{/if}{$oComment->getId()}">этой ссылке</a><br>
{if Config::Get('sys.mail.include_comment')}
	Текст сообщения: <i>{$oComment->getText()}</i>
{/if}
<br><br>
С уважением, администрация сайта <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>