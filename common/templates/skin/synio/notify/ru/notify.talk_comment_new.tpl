Пользователь <a href="{$oUserFrom->getUserWebPath()}">{$oUserFrom->getLogin()}</a>
оставил новый комментарий к письму <b>«{$oTalk->getTitle()|escape:'html'}»</b>,
прочитать его можно перейдя по <a href="{router page='talk'}read/{$oTalk->getId()}/#comment{$oTalkComment->getId()}">этой ссылке</a><br>
{if Config::Get('sys.mail.include_talk')}
    Текст сообщения: <i>{$oTalkComment->getText()}</i>
    <br>
{/if}
<br>
Не забудьте предварительно авторизоваться!
<br><br>
С уважением, администрация сайта <a href="{Config::Get('path.root.web')}">{Config::Get('view.name')}</a>