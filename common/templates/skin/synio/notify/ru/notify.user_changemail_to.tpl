Вами отправлен запрос на смену емайл адреса пользователя <a href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>
на сайте <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>.<br/>
Старый емайл: <b>{$oChangemail->getMailFrom()}</b><br/>
Новый емайл: <b>{$oChangemail->getMailTo()}</b><br/>

<br/>
Для подтверждения смены емайла пройдите по ссылке:
<a href="{router page='profile'}changemail/confirm-to/{$oChangemail->getCodeTo()}/">{router page='profile'}changemail/confirm-to/{$oChangemail->getCodeTo()}/</a>

<br/><br/>
С уважением, администрация сайта <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>