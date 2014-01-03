Пользователь <a href="{$oUser->getUserWebPath()}">{$oUser->getLogin()}</a> оставил сообщение на <a href="{$oUserWall->getUserWebPath()}wall/">вашей стене</a><br/>

Текст сообщения: <i>{$oWall->getText()}</i>

<br/><br/>
С уважением, администрация сайта <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>