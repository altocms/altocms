The user <a href="{$oUserFrom->getUserWebPath()}">{$oUserFrom->getLogin()}</a> invited you to register on the site <a href="{Config::Get('path.root.web')}">{Config::Get('view.name')}</a><br>
The invitation code:  <b>{$oInvite->getCode()}</b><br>
To register you need to enter the invitation code on <a href="{router page='login'}"> the main page</a>													
<br><br>
Best regards, site administration <a href="{Config::Get('path.root.web')}">{Config::Get('view.name')}</a>
							