Пользователь <a href="{$oUser->getUserWebPath()}">{$oUser->getLogin()}</a> ответил на ваше сообщение на <a href="{$oUserWall->getUserWebPath()}wall/">стене</a><br/>

Ваше сообщение: <i>{$oWallParent->getText()}</i><br/><br/>
Текст ответа: <i>{$oWall->getText()}</i>

<br/><br/>
С уважением, администрация сайта <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>