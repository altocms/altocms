You have sent a request to change user email <a href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>
at <a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>.<br/>
Old email: <b>{$oChangemail->getMailFrom()}</b><br/>
New email: <b>{$oChangemail->getMailTo()}</b><br/>

<br/>
To confirm the email change, please click here:
<a href="{router page='profile'}changemail/confirm-to/{$oChangemail->getCodeTo()}/">{router page='profile'}changemail/confirm-to/{$oChangemail->getCodeTo()}/</a>

<br/><br/>
Best regards, 
<br>
<a href="{Config::Get('path.root.url')}">{Config::Get('view.name')}</a>