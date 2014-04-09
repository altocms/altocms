You have a new incoming letter from the user <a href="{$oUserFrom->getProfileUrl()}">{$oUserFrom->getDisplayName()}</a>,
you can read and answer it by clicking on <a href="{router page='talk'}read/{$oTalk->getId()}/"> this link</a><br>
Letter topic: <b>{$oTalk->getTitle()|escape:'html'}</b><br>

{if Config::Get('sys.mail.include_talk')}
    Message: <i>{$oTalk->getText()}</i>	<br>
{/if}

Don't forget to register before!
<br><br>
Best regards, site administration <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>