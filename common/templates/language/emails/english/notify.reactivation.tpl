You have requested re-activation on the site <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a><br>

Link to the account activation:
<a href="{router page='registration'}activate/{$oUser->getActivateKey()}/">{router page='registration'}activate/{$oUser->getActivateKey()}/</a>

<br><br>
Best regards, site administration <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>