{if E::IsUser()}
    <section class="panel panel-default widget widget-type-activity">
        <div class="panel-body">

            <header class="widget-header">
                <h3 class="widget-title">{$aLang.userfeed_block_blogs_title}</h3>
            </header>

            <div class="widget-content">
                <p class="text-muted">
                    <small>{$aLang.userfeed_settings_note_follow_blogs}</small>
                </p>

                {if count($aUserfeedBlogs)}
                    <ul class="list-unstyled stream-settings-blogs">
                        {foreach $aUserfeedBlogs as $oBlog}
                            {$iBlogId=$oBlog->getId()}
                            <li class="checkbox">
                                <label>
                                    <input class="userfeedBlogCheckbox" type="checkbox"
                                            {if isset($aUserfeedSubscribedBlogs.$iBlogId)} checked="checked"{/if}
                                           onClick="if (jQuery(this).prop('checked')) { ls.userfeed.subscribe('blogs',{$iBlogId}) } else { ls.userfeed.unsubscribe('blogs',{$iBlogId}) } "/>
                                    <a href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape:'html'}</a>
                                </label>
                            </li>
                        {/foreach}
                    </ul>
                {else}
                    <p class="text-muted">
                        <small>{$aLang.userfeed_no_blogs}</small>
                    </p>
                {/if}
            </div>

        </div>
    </section>
{/if}
