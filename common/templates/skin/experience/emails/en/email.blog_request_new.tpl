{* Тема оформления Experience v.1.0  для Alto CMS      *}
{* @licence     CC Attribution-ShareAlike   *}

The user «<a href="{$oUserFrom->getProfileUrl()}">{$oUserFrom->getDisplayName()}</a>»</b> send request for join the blog
<a href="{$oBlog->getUrlFull()}">"{$oBlog->getTitle()|escape:'html'}"</a>.
<br/><br/>
<a href='{$sPath}'>Have a look tr request</a> (Don't forget to register before!)
<br/>
Best regards, site administration <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>