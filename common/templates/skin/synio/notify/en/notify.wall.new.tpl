The user <a href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a> has posted on <a href="{$oUserWall->getProfileUrl()}wall/">your wall</a><br/>

Their post reads as follows: <i>{$oWall->getText()}</i>

<br/><br/>
Best regards, 
<br>
<a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>