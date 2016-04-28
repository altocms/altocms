 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

Если вы хотите сменить себе пароль на сайте <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>, то перейдите по ссылке ниже:
<a href="{R::GetLink("login")}reminder/{$oReminder->getCode()}/">{R::GetLink("login")}reminder/{$oReminder->getCode()}/</a>

<br><br>
С уважением, администрация сайта <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>