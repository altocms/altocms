Пользователь <a href="{$oUserFrom->getProfileUrl()}">{$oUserFrom->getDisplayName()}</a> оставил новый комментарий к письму
<b>«{$oTalk->getTitle()|escape:'html'}»</b>, прочитать его можно перейдя по
<a href="{router page='talk'}read/{$oTalk->getId()}/#comment{$oTalkComment->getId()}">этой ссылке</a><br>
{if Config::Get('sys.mail.include_talk')}
	Текст сообщения: <i>{$oTalkComment->getText()}</i>
    <br>
{/if}
Не забудьте предварительно авторизоваться!
<br><br>
С уважением, администрация сайта <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>