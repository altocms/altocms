Пользователь «<a href="{$oUserFrom->getUserWebPath()}">{$oUserFrom->getLogin()}</a>»</b> приглашает вас вступить в блог <a href="{$oBlog->getUrlFull()}">"{$oBlog->getTitle()|escape:'html'}"</a>.
<br /><br />
<a href='{$sPath}'>Посмотреть приглашение</a> (Не забудьте предварительно авторизоваться!)
<br />
С уважением, администрация сайта <a href="{Config::Get('path.root.web')}">{Config::Get('view.name')}</a>