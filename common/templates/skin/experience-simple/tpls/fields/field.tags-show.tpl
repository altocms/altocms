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
                {if !$bPreview}
                    <div class="topic-share" id="topic_share_{$oTopic->getId()}">
                        {hookb run="topic_share" topic=$oTopic bTopicList=false}
                            <div class="yashare-auto-init"
                                 data-yashareTitle="{$oTopic->getTitle()|escape:'htmlall'}"
                                 data-yashareDescription="{$oTopic->getText()|strip_tags|strip|truncate:100|escape:'htmlall'}"
                                 data-yashareLink="{$oTopic->getUrl()}"
                                 data-yashareL10n="{Config::Get('lang.current')}"
                                 data-yashareTheme="counter"
                                 data-yashareType="small"
                                 {if $oTopic->getPreviewImageUrl()}data-yashareImage="{$oTopic->getPreviewImageUrl()}"{/if}
                                 data-yashareQuickServices="vkontakte,facebook,twitter,odnoklassniki,moimir,lj,gplus"></div>
                        {/hookb}
                    </div>
                {/if}
            </div>
        </div>
    </div>
