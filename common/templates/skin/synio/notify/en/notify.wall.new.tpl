The user <a href="{$oUser->getUserWebPath()}">{$oUser->getLogin()}</a> has posted on <a href="{$oUserWall->getUserWebPath()}wall/">your wall</a><br/>

Their post reads as follows: <i>{$oWall->getText()}</i>

<br/><br/>
Best regards, 
<br>
<a href="{Config::Get('path.root.web')}">{Config::Get('view.name')}</a>