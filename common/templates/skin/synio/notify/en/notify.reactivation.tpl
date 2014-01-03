You have requested re-activation of your account at <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>.<br>
<br>

Here is the link, which will activate your account:
<a href="{router page='registration'}activate/{$oUser->getActivateKey()}/">{router page='registration'}activate/{$oUser->getActivateKey()}/</a>

<br><br>
Best regards, 
<br>
<a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>