<a href="{$oUserFrom->getUserWebPath()}">{$oUserFrom->getLogin()}</a> invites you
to join the blog <a href="{$oBlog->getUrlFull()}">"{$oBlog->getTitle()|escape:'html'}"</a>.
<br />
<br />
<a href='{$sPath}'>Have a look at the invitation</a>
<br><br>
Best regards, 
<br>
<a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>