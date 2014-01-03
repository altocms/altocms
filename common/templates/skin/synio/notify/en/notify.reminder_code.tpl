If you would like to change your password at <a href="{Config::Get('path.root.web')}">{Config::Get('view.name')}</a>,
click on the link below:
<a href="{router page='login'}reminder/{$oReminder->getCode()}/">{router page='login'}reminder/{$oReminder->getCode()}/</a>

<br><br>
Best regards, 
<br>
<a href="{Config::Get('path.root.web')}">{Config::Get('view.name')}</a>