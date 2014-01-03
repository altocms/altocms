The user <a href="{$oUserTopic->getUserWebPath()}">{$oUserTopic->getLogin()}</a> posted a new topic - <a href="{$oTopic->getUrl()}">{$oTopic->getTitle()|escape:'html'}</a><br> in a blog <b>«{$oBlog->getTitle()|escape:'html'}»</b>
														
<br><br>
Best regards, site administration <a href="{Config::Get('path.root.web')}">{Config::Get('view.name')}</a>