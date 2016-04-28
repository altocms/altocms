You have requested re-activation on the site <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a><br>

Link to the account activation:
<a href="{R::GetLink("registration")}activate/{$oUser->getActivateKey()}/">{R::GetLink("registration")}activate/{$oUser->getActivateKey()}/</a>

<br><br>
Best regards, site administration <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>