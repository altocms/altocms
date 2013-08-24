<header id="header" role="banner">
    {hook run='header_banner_begin'}
    <div class="row-fluid">
        <div class="logos span6">
            <a href="{cfg name='path.root.web'}" class="logo"></a>
            <h1><a href="{cfg name='path.root.web'}">{cfg name='view.name'}</a></h1>
            <h2 class="lead">{cfg name='view.description'}</h2>
        </div>
        {*<div class="span6">
            <div class="bsa well">
                <div class="bsa_it one">
                    <div class="bsa_it_ad">
                        Заказать разработку на основе шаблона bootstrap можно <a href="http://livestreet.ru/profile/s4people/">тут</a>.
                    </div>
                </div>
            </div>
        </div>*}
    </div>

    {hook run='header_banner_end'}
</header>