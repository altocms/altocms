The user <a href="{$oUserFrom->getProfileUrl()}">{$oUserFrom->getDisplayName()}</a> invited you to register on the site
<a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a><br>
The invitation code:  <b>{$oInvite->getCode()}</b><br>
To register you need to enter the invitation code on <a href="{router page='login'}"> the main page</a>
<br><br>
Best regards, site administration <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>
