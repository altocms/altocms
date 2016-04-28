 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

If you want to change your password on the site, <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>, then click on the link below:
<a href="{R::GetLink("login")}reminder/{$oReminder->getCode()}/">{R::GetLink("login")}reminder/{$oReminder->getCode()}/</a>

<br><br>
Best regards, site administration <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>