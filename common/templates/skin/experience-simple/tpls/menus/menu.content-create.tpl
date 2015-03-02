 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<div class="panel panel-default panel-search flat">

    <div class="panel-body">
        {if $sMode!='add'}
            {if $sMenuItemSelect == 'blog'}
                <h2 class="panel-header">{$aLang.block_create_new_blog}</h2>
            {else}
                <h2 class="panel-header">{$aLang.block_edit_topic}</h2>
            {/if}

        {else}
            <h2 class="panel-header">
                {$aLang.block_create_topic}
            </h2>
        {/if}




    </div>

    <div class="panel-footer">
        <ul class="clearfix">


        {if $sMenuItemSelect=='topic'}
            {if $sMode=='add' && $aAllowedContentTypes}
                {foreach from=$aAllowedContentTypes item=oContentType}
                    {if $oContentType->isAccessible()}
                        <li><a class="link link-light-gray link-lead link-clear {if $sMenuSubItemSelect==$oContentType->getContentUrl()}active{/if}"
                           href="{router page='content'}{$oContentType->getContentUrl()}/add/">
                            {$oContentType->getContentTitle()|escape:'html'}
                        </a></li>
                    {/if}
                {/foreach}
            {/if}
            {if $iUserCurrentCountTopicDraft}
                <li><a class="link link-light-gray link-lead link-clear pull-right marr0"
                   href="{router page='content'}drafts/">{$aLang.topic_menu_drafts} ({$iUserCurrentCountTopicDraft})
                </a></li>
            {/if}
            {hook run='menu_create_topic_item'}
        {/if}

        </ul>
    </div>

</div>


{hook run='menu_create' sMenuItemSelect=$sMenuItemSelect sMenuSubItemSelect=$sMenuSubItemSelect}
