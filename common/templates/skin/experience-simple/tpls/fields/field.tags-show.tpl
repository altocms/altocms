 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{$aTags=$oTopic->getTagsArray()}
{if E::IsUser() && $oFavourite}
    {$aFavouriteTags=$oFavourite->getTagsArray()}
{/if}

    <div class="row tags_and_share">
        <div class="col-md-15">
            <ul class="topic-tags js-favourite-insert-after-form js-favourite-tags-topic-{$oTopic->getId()}">
                {if $aTags OR $aFavouriteTags}
                    <li class="tags-title"><i class="fa fa-tags"></i></li>
                {/if}
                {if $aTags}
                    {foreach $aTags as $sTag}
                        <li>
                            <a class="link link-lead link-light-gray link-clear" href="{router page='tag'}{$sTag|escape:'url'}/">{$sTag|escape:'html'}</a>{if !$sTag@last}, {/if}
                        </li>
                    {/foreach}
                {*{else}*}
                    {*<li>{$aLang.topic_tags_empty}</li>*}
                {/if}


                    {if E::IsUser()}
                        {if $aFavouriteTags}
                            {*{if $aTags}, {/if}*}
                            {foreach $aFavouriteTags as $sTag}
                                <li class="topic-tags-user js-favourite-tag-user">
                                    <a class="link link-lead link-light-gray link-clear"
                                       href="{E::User()->getProfileUrl()}favourites/topics/tag/{$sTag|escape:'url'}/">{$sTag|escape:'html'}</a>{if !$sTag@last}, {/if}
                                </li>
                            {/foreach}
                        {/if}
                        <li class="topic-tags-edit js-favourite-tag-edit"
                            {if !$oFavourite}style="display:none;"{/if}>
                            &nbsp;<a href="#" onclick="return ls.favourite.showEditTags({$oTopic->getId()},'topic',this);"
                               class="link link-lead link-light-gray link-clear">{$aLang.favourite_form_tags_button_show}</a>
                        </li>
                    {/if}
                </ul>
        </div>
        <div class="col-md-9">
            <div class="pull-right">
                <script type="text/javascript">(function() {
                    if (window.pluso)if (typeof window.pluso.start == "function") return;
                    if (window.ifpluso==undefined) { window.ifpluso = 1;
                        var d = document, s = d.createElement('script'), g = 'getElementsByTagName';
                        s.type = 'text/javascript'; s.charset='UTF-8'; s.async = true;
                        s.src = ('https:' == window.location.protocol ? 'https' : 'http')  + '://share.pluso.ru/pluso-like.js';
                        var h=d[g]('body')[0];
                        h.appendChild(s);
                    }})();</script>
                <div class="pluso" data-background="transparent" data-options="medium,round,line,horizontal,counter,theme=04" data-services="facebook,twitter,vkontakte,odnoklassniki,google"></div>
            </div>
        </div>
    </div>
