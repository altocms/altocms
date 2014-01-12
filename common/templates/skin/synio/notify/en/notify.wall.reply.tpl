The user <a href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a> has replied to your post on
<a href="{$oUserWall->getProfileUrl()}wall/"> the wall</a><br/>

Your post was: <i>{$oWallParent->getText()}</i><br/><br/>
Their reply reads as follows: <i>{$oWall->getText()}</i>

<br/><br/>
Best regards, 
<br>
<a href="{Config::Get('path.root.web'{Config::Get('view.name')}me'}</a>