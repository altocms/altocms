The user <a href="{$oUserTopic->getProfileUrl()}">{$oUserTopic->getDisplayName()}</a> posted a new topic -
<a href="{$oTopic->getUrl()}">{$oTopic->getTitle()|escape:'html'}</a><br> in a blog <b>«{$oBlog->getTitle()|escape:'html'}»</b>

<br><br>
Best regards, site administration <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>