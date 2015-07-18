The user <a href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a> replied your post on <a href="{$oUserWall->getProfileUrl()}wall/">wall</a><br/>

Your post: <i>{$oWallParent->getText()}</i><br/><br/>
Reply post: <i>{$oWall->getText()}</i>

<br/><br/>
Best regards, site administration <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>