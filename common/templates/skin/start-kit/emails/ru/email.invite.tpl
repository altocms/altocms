Пользователь <a href="{$oUserFrom->getProfileUrl()}">{$oUserFrom->getDisplayName()}</a>  пригласил вас зарегистрироваться на сайте
<a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a><br>
Код приглашения:  <b>{$oInvite->getCode()}</b><br>
Для регистрации вам будет необходимо ввести код приглашения на <a href="{router page='login'}">странице входа</a>
<br><br>
С уважением, администрация сайта <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>
