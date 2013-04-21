{*For desktop*}
<div id="navtop" class="navbar navbar-inverse navbar-fixed-top visible-desktop">
    <div class="navbar-inner">
        <div class="container">
            <a class="brand" href="{cfg name='path.root.web'}" title="{cfg name='view.description'}">{cfg name='view.name'}</a>
            <div class="nav-collapse collapse">

                <div class="navbar-text pull-right">
                {if $iUserCurrentCountTalkNew || $iUserCurrentCountTrack}
                    <div class="box-alert pull-left">
                        <div class="btn-group">
                            {if $iUserCurrentCountTalkNew}
                                <a href="{router page='talk'}" class="new-messages btn btn-warning"><i class="icon-comment icon-white"></i> +{$iUserCurrentCountTalkNew}</a>
                            {/if}
                            {if $iUserCurrentCountTrack}
                                <a href="{router page='feed'}track/" class="new-track btn btn-success"><i class="icon-eye-open icon-white"></i> +{$iUserCurrentCountTrack}</a>
                            {/if}
                        </div>
                    </div>
                {/if}

                    {if $oUserCurrent}
                        <div class="writes btn-group">
                            <a href="{router page='content'}/topic/add/" class="btn btn-primary pull-left write">
                                <i class="icon-plus-sign icon-white"></i>
                                {$aLang.block_create}
                            </a>
                            <a class="btn btn-primary pull-left write dropdown-toggle" data-toggle="dropdown">
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu">
                                {foreach from=$aContentTypes item=oType}
									<li><a href="{router page='content'}{$oType->getContentUrl()}/add/">{$oType->getContentTitle()|escape:'html'}</a></li>
								{/foreach}
								<li><a href="{router page='talk'}add/">{$aLang.block_create_talk}</a></li>
								<li><a href="{router page='blog'}add" class="write-item-link">{$aLang.block_create_blog}</a></li>
                                <li class="divider"></li>
                                <li><a href="{router page='content'}saved/" class="write-item-link">{$aLang.topic_menu_saved} {if $iUserCurrentCountTopicDraft}({$iUserCurrentCountTopicDraft}){/if}</a></li>
                            </ul>
                        </div>
                    {/if}

                    {hook run='userbar_nav'}
                    <ul class="nav nav-pills nav-userbar">
                    {if $oUserCurrent}
                        <li class="nav-userbar-username dropdown">
                            <a href="#" class="username dropdown-toggle" data-toggle="dropdown">
                                <img src="{$oUserCurrent->getProfileAvatarPath(24)}" alt="avatar" class="avatar" />
                                {$oUserCurrent->getLogin()}
                                <b class="caret"></b>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="{$oUserCurrent->getUserWebPath()}" class="username"><i class="icon-user"></i>{$aLang.top_menu_user_profile}</a></li>
                                <li><a href="{$oUserCurrent->getUserWebPath()}favourites/topics/"><i class="icon-star"></i>{$aLang.user_menu_profile_favourites}</a></li>
                                <li><a href="{router page='talk'}" {if $iUserCurrentCountTalkNew}class="new-messages"{/if} id="new_messages" title="{if $iUserCurrentCountTalkNew}{$aLang.user_privat_messages_new}{/if}"><i class="icon-envelope"></i>{$aLang.user_privat_messages}{if $iUserCurrentCountTalkNew} ({$iUserCurrentCountTalkNew}){/if}</a></li>
                                <li><a href="{router page='settings'}profile/"><i class="icon-wrench"></i>{$aLang.user_settings}</a></li>
                                {hook run='userbar_item'}
                                <li class="divider"></li>
                                <li><a href="{router page='login'}exit/?security_ls_key={$LIVESTREET_SECURITY_KEY}"><i class="icon-ban-circle"></i>{$aLang.exit}</a></li>
                            </ul>
                        </li>
                    {else}
                        {hook run='userbar_item'}
                        <li><a href="{router page='login'}" class="js-login-form-show">{$aLang.user_login_submit}</a></li>
                        <li><a href="{router page='registration'}" class="js-registration-form-show">{$aLang.registration_submit}</a></li>
                    {/if}
                    </ul>
                </div>

            </div><!--/.nav-collapse -->
        </div>
    </div>
</div>

{*For tablet & phone*}
<div class="navbar navbar-inverse navbar-fixed-top hidden-desktop">
    <div class="navbar-inner">
        <div class="container">
            <a class="brand" href="{cfg name='path.root.web'}" title="{cfg name='view.description'}">{cfg name='view.name'}</a>
            <div>

                <div class="navbar-text pull-right">
                {if $iUserCurrentCountTalkNew || $iUserCurrentCountTrack}
                    <div class="box-alert pull-left">
                        <div class="btn-group">
                            {if $iUserCurrentCountTalkNew}
                                <a href="{router page='talk'}" class="new-messages btn btn-warning"><i class="icon-comment icon-white"></i> +{$iUserCurrentCountTalkNew}</a>
                            {/if}
                            {if $iUserCurrentCountTrack}
                                <a href="{router page='feed'}track/" class="new-track btn btn-success"><i class="icon-eye-open icon-white"></i> +{$iUserCurrentCountTrack}</a>
                            {/if}
                        </div>
                    </div>
                {/if}

                {if $oUserCurrent}
                    <div class="writes btn-group">
                        <a href="{router page='content'}/topic/add/" class="btn btn-primary pull-left write">
                            <i class="icon-plus-sign icon-white"></i>
                            {$aLang.block_create}
                        </a>
                        <a class="btn btn-primary pull-left write dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            {foreach from=$aContentTypes item=oType}
								<li {if $sEvent==$oType->getContentUrl()}class="active"{/if}><a href="{router page='content'}{$oType->getContentUrl()}/add/">{$oType->getContentTitle()|escape:'html'}</a></li>
							{/foreach}
                            <li><a href="{router page='blog'}add" class="write-item-link">{$aLang.block_create_blog}</a></li>
                            <li class="divider"></li>
                            <li><a href="{router page='content'}saved/" class="write-item-link">{$aLang.topic_menu_saved} {if $iUserCurrentCountTopicDraft}({$iUserCurrentCountTopicDraft}){/if}</a></li>
                        </ul>
                    </div>
                {/if}

                {hook run='userbar_nav'}
                    <ul class="nav nav-pills nav-userbar">
                    {if $oUserCurrent}
                        <li class="nav-userbar-username dropdown">
                            <a href="#" class="username dropdown-toggle" data-toggle="dropdown">
                                <img src="{$oUserCurrent->getProfileAvatarPath(24)}" alt="avatar" class="avatar" />
                                {$oUserCurrent->getLogin()}
                                <b class="caret"></b>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="{$oUserCurrent->getUserWebPath()}" class="username"><i class="icon-user"></i>{$aLang.top_menu_user_profile}</a></li>
                                <li><a href="{$oUserCurrent->getUserWebPath()}favourites/topics/"><i class="icon-star"></i>{$aLang.user_menu_profile_favourites}</a></li>
                                <li><a href="{router page='talk'}" {if $iUserCurrentCountTalkNew}class="new-messages"{/if} id="new_messages" title="{if $iUserCurrentCountTalkNew}{$aLang.user_privat_messages_new}{/if}"><i class="icon-envelope"></i>{$aLang.user_privat_messages}{if $iUserCurrentCountTalkNew} ({$iUserCurrentCountTalkNew}){/if}</a></li>
                                <li><a href="{router page='settings'}profile/"><i class="icon-wrench"></i>{$aLang.user_settings}</a></li>
                                {hook run='userbar_item'}
                                <li class="divider"></li>
                                <li><a href="{router page='login'}exit/?security_ls_key={$LIVESTREET_SECURITY_KEY}"><i class="icon-ban-circle"></i>{$aLang.exit}</a></li>
                            </ul>
                        </li>
                    {else}
                        {hook run='userbar_item'}
                        <li><a href="{router page='login'}" class="js-login-form-show">{$aLang.user_login_submit}</a></li>
                        <li><a href="{router page='registration'}" class="js-registration-form-show">{$aLang.registration_submit}</a></li>
                    {/if}
                    </ul>
                </div>

            </div><!--/.nav-collapse -->
        </div>
    </div>
</div>