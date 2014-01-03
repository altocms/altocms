Вами отправлен запрос на смену емайл адреса пользователя <a href="{$oUser->getUserWebPath()}">{$oUser->getLogin()}</a> на сайте <a href="{Config::Get('path.root.web')}">{Config::Get('view.name')}</a>.<br/>
Старый емайл: <b>{$oChangemail->getMailFrom()}</b><br/>
Новый емайл: <b>{$oChangemail->getMailTo()}</b><br/>


<br/>
Для подтверждения смены емайла пройдите по ссылке:
<a href="{router page='profile'}changemail/confirm-from/{$oChangemail->getCodeFrom()}/">{router page='profile'}changemail/confirm-from/{$oChangemail->getCodeFrom()}/</a>

<br/><br/>
С уважением, администрация сайта <a href="{Config::Get('path.root.web')}">{Config::Get('view.name')}</a>