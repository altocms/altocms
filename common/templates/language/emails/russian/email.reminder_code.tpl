{extends file='emails/email.base.tpl'}

{block name='content'}
	Если вы хотите сменить себе пароль на сайте <a href="{Config::Get('path.root.web'{Config::Get('view.name')}me'}</a>, то перейдите по ссылке ниже:<br>
	<a href="{router page='login'}reminder/{$oReminder->getCode()}/">{router page='login'}reminder/{$oReminder->getCode()}/</a>
{/block}