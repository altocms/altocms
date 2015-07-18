 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{if E::IsUser()}
    <script>
        $(function(){
            $('.widget-blogfeed input:checkbox').off('ifChanged').on('ifChanged', function(e) {
                $(this).trigger('change');
            })
        })
    </script>

<div class="panel panel-default sidebar flat widget widget-blogfeed">
    <div class="panel-body pab24">
        <div class="panel-header">
            <i class="fa fa-users"></i>
            {$aLang.userfeed_widget_blogs_title}
        </div>

            <div class="widget-content">
                <p class="text-muted">
                    <small>{$aLang.userfeed_settings_note_follow_blogs}</small>
                </p>

                {if count($aUserfeedBlogs)}
                    <ul class="list-unstyled js-userfeed-bloglist">
                        {foreach $aUserfeedBlogs as $oBlog}
                            {$iBlogId=$oBlog->getId()}
                            <li class="checkbox pal0 js-userfeed-item" data-blog-id="{$iBlogId}">
                                <label>
                                    <input type="checkbox" {if isset($aUserfeedSubscribedBlogs.$iBlogId)} checked="checked"{/if} />&nbsp;
                                    <a href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape:'html'}</a>
                                </label>
                            </li>
                        {/foreach}
                    </ul>
                {else}
                    <div class="bg-warning">
                        {$aLang.userfeed_no_blogs}
                    </div>
                {/if}
            </div>

        </div>
    </div>
{/if}
