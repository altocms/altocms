 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if E::IsUser()}


<div class="toolbar-button toolbar-write toolbar-menu-popover">
    <div id="hidden-toolbar-write-content" style="display: none;">
        <ul class="toolbar-menu">
                {foreach from=$aContentTypes item=oContentType}
                    {if $oContentType->isAccessible()}
                        <li>
                            <a href="{R::GetLink("content")}{$oContentType->getContentUrl()}/add/">
                                <span><i class="fa fa-file-o"></i></span>
                                <span>{$oContentType->getContentTitle()|escape:'html'}</span>

                            </a>
                        </li>
                    {/if}
                {/foreach}
                <li>
                    <a href="{R::GetLink("blog")}add">
                        <span><i class="fa fa-comment-o"></i></span>
                        <span>{$aLang.block_create_blog}</span>
                    </a>
                </li>
                <li>
                    <a href="{R::GetLink("talk")}add">
                        <span><i class="fa fa-envelope-o"></i></span>
                        <span>{$aLang.block_create_talk}</span>
                    </a>
                </li>
                {hook run='write_item' isPopup=true}
                {if $iUserCurrentCountTopicDraft}
                    <li class="divider"></li>
                    <li>
                        <a href="{R::GetLink("content")}drafts/"
                           class="write-item-link">
                            <span><i class="fa fa-bars"></i></span>
                            <span>{$iUserCurrentCountTopicDraft} {$iUserCurrentCountTopicDraft|declension:$aLang.draft_declension:$sLang}</span>
                        </a>
                    </li>
                {/if}
        </ul>
    </div>
    <a href="#"
       onclick="return false;"
       data-toggle="popover"
       class="toolbar-exit-button link link-light-gray"><span class="fa fa-pencil"></span></a>
</div>
{/if}
