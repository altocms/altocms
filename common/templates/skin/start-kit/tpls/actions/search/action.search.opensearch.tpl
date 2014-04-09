<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/"> 
	<ShortName>{Config::Get('view.name')}</ShortName>
	<Description>{$sHtmlTitle}</Description> 
	<Contact>{$sAdminMail}</Contact> 
	<Url type="text/html" template="{router page='search'}topics/?q={literal}{searchTerms}{/literal}" /> 
	<LongName>{$sHtmlDescription}</LongName> 
	<Image height="64" width="64" type="image/png">{Config::Get('path.static.skin')}/images/logo.png</Image>
	<Image height="16" width="16" type="image/vnd.microsoft.icon">{Config::Get('path.static.skin')}/images/favicon.ico</Image>
	<Developer>{Config::Get('view.name')} ({Config::Get('path.root.url')})</Developer>
	<Attribution> 
		© «{Config::Get('view.name')}»
	</Attribution> 
	<SyndicationRight>open</SyndicationRight> 
	<AdultContent>false</AdultContent> 
	<Language>ru-ru</Language> 
	<OutputEncoding>UTF-8</OutputEncoding> 
	<InputEncoding>UTF-8</InputEncoding> 
</OpenSearchDescription>