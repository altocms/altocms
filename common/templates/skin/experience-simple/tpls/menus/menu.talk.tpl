 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

<div class="panel panel-default panel-table flat">

    <div class="panel-body">

        <div class="panel-header">
            <i class="fa fa-envelope-o"></i>&nbsp;{$aLang.talk_menu_inbox}
        </div>

        {include file='actions/talk/action.talk.filter.tpl'}
        {include file='actions/talk/action.talk.speakers.tpl'}


    </div>

    <div class="panel-footer par0">
        <ul class="pa0">
        <li><a class="link link-light-gray link-lead link-clear {if $sMenuSubItemSelect=='inbox'}active{/if}" href="{R::GetLink("talk")}">{$aLang.talk_menu_inbox}</a></li>
        {if $iUserCurrentCountTalkNew}
            <li><a class="link link-light-gray link-lead link-clear{if $sMenuSubItemSelect=='new'}active{/if}" href="{R::GetLink("talk")}inbox/new/">{$aLang.talk_menu_inbox_new}</a></li>
        {/if}
        <li><a class="link link-light-gray link-lead link-clear {if $sMenuSubItemSelect=='add'}active{/if}" href="{R::GetLink("talk")}add/">{$aLang.talk_menu_inbox_create}</a></li>
        <li><a class="link link-light-gray link-lead link-clear {if $sMenuSubItemSelect=='favourites'}active{/if}" href="{R::GetLink("talk")}favourites/">{$aLang.talk_menu_inbox_favourites}{if $iCountTalkFavourite} ({$iCountTalkFavourite}){/if}</a></li>
        <li><a class="link link-light-gray link-lead link-clear {if $sMenuSubItemSelect=='blacklist'}active{/if}" href="{R::GetLink("talk")}blacklist/">{$aLang.talk_menu_inbox_blacklist}</a></li>
        {hook run='menu_talk_talk_item'}

        <li class="pull-right marr0 pa0"><a href="#" class="link link-light-gray link-lead link-clear btn btn-gray"
           onclick="jQuery('#block_talk_search_content').toggle(); return false;">
                <i class="fa fa-search"></i>{$aLang.talk_filter_title}
           </a></li> </ul>
    </div>
    {hook run='menu_talk'}
</div>