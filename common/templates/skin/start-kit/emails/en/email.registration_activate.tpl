You have been registered on the site <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a><br>
Your credentials:<br>
&nbsp;&nbsp;&nbsp;login: <b>{$oUser->getLogin()}</b><br>
&nbsp;&nbsp;&nbsp;password: <b>{$sPassword}</b><br>
<br>
To complete registration you need to activate your account by clicking this link: 
<a href="{R::GetLink("registration")}activate/{$oUser->getActivateKey()}/">{R::GetLink("registration")}activate/{$oUser->getActivateKey()}/</a>

<br><br>
Best regards, site administration <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>