Пользователь <a href="{$oUserTopic->getUserWebPath()}">{$oUserTopic->getLogin()}</a> опубликовал в блоге <b>«{$oBlog->getTitle()|escape:'html'}»</b> новый топик -  <a href="{$oTopic->getUrl()}">{$oTopic->getTitle()|escape:'html'}</a><br>
														
<br><br>
С уважением, администрация сайта <a href="{Config::Get('path.root.web')}">{Config::Get('view.name')}</a>