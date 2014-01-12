Пользователь <a href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a> ответил на ваше сообщение на
<a href="{$oUserWall->getProfileUrl()}wall/">стене</a><br/>

Ваше сообщение: <i>{$oWallParent->getText()}</i><br/><br/>
Текст ответа: <i>{$oWall->getText()}</i>

<br/><br/>
С уважением, администрация сайта <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>