{extends file='emails/email.base.tpl'}

{block name='content'}
	Вы зарегистрировались на сайте <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>
	<br>
	<br>
	Ваши регистрационные данные:<br>
	&nbsp;&nbsp;&nbsp;логин: <b>{$oUser->getLogin()}</b><br>
	&nbsp;&nbsp;&nbsp;пароль: <b>{$sPassword}</b>
{/block}