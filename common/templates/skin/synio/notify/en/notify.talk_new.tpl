You have a new incoming message from <a href="{$oUserFrom->getProfileUrl()}">{$oUserFrom->getDisplayName()}</a>.
You can read and answer it by clicking on <a href="{router page='talk'}read/{$oTalk->getId()}/"> this link</a><br>
Letter topic: <b>{$oTalk->getTitle()|escape:'html'}</b>
<br>
{if Config::Get('sys.mail.include_talk')}
    Message: <i>{$oTalk->getText()}</i>
    <br>
{/if}
<br><br>
Best regards, 
<br>
<a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>