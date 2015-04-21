 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

<div class="modal fade in modal-write" id="modal-write">
    <div class="modal-dialog">
        <div class="modal-content">

            <header class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">{$aLang.block_create}</h4>
            </header>

            {strip}
                <div class="modal-body">
                    <ul class="list-unstyled list-inline modal-write-list clearfix">
                        {foreach from=$aContentTypes item=oContentType}
                            {if $oContentType->isAccessible()}
                                <li class="write-item-type-topic">
                                    <a href="{router page='content'}{$oContentType->getContentUrl()}/add/" class="content-logo link link-lead link-dark link-clear">
                                        <i class="fa fa-file-text-o"></i>
                                    </a>
                                    <a href="{router page='content'}{$oContentType->getContentUrl()}/add/" class="write-item-link link link-lead link-dark link-clear">{$oContentType->getContentTitle()|escape:'html'}</a>
                                </li>
                            {/if}
                        {/foreach}
                        <li class="write-item-type-blog">
                            <a href="{router page='blog'}add" class="content-logo link link-lead link-dark link-clear">
                                <i class="fa fa-folder-o"></i>
                            </a>
                            <a href="{router page='blog'}add" class="write-item-link link link-lead link-dark link-clear">{$aLang.block_create_blog}</a>
                        </li>
                        <li class="write-item-type-message">
                            <a href="{router page='talk'}add" class="content-logo link link-lead link-dark link-clear">
                                <i class="fa fa-envelope-o"></i>
                            </a>
                            <a href="{router page='talk'}add" class="write-item-link link link-lead link-dark link-clear">{$aLang.block_create_talk}</a>
                        </li>
                        {*{if $iUserCurrentCountTopicDraft}*}
                            <li class="write-item-type-draft">
                                <a href="{router page='content'}drafts/" class="content-logo link link-lead link-dark link-clear">
                                    <i class="fa fa-file-text"></i>
                                </a>
                                <a href="{router page='content'}drafts/" class="write-item-link link link-lead link-dark link-clear">
                                    {$aLang.topic_menu_draft} ({$iUserCurrentCountTopicDraft})
                                </a>
                            </li>
                        {*{/if}*}
                        {hook run='write_item' isPopup=true}
                    </ul>

                </div>
            {/strip}

        </div>
    </div>
</div>
