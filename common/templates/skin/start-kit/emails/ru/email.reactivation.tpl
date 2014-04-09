Вы запросили повторную активацию на сайте <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a><br>

Ссылка на активацию аккаунта:
<a href="{router page='registration'}activate/{$oUser->getActivateKey()}/">{router page='registration'}activate/{$oUser->getActivateKey()}/</a>

<br><br>
С уважением, администрация сайта <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>