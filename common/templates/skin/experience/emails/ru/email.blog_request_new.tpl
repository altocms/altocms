{* Тема оформления Experience v.1.0  для Alto CMS      *}
{* @licence     CC Attribution-ShareAlike   *}

Пользователь «<a href="{$oUserFrom->getProfileUrl()}">{$oUserFrom->getDisplayName()}</a>»</b> запросил разрешение вступить в блог
<a href="{$oBlog->getUrlFull()}">"{$oBlog->getTitle()|escape:'html'}"</a>.
<br /><br />
<a href='{$sPath}'>Посмотреть запрос</a> (Не забудьте предварительно авторизоваться!)
<br />
С уважением, администрация сайта <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>