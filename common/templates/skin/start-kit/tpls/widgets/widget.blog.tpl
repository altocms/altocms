{if $oTopic}
    {$oBlog=$oTopic->getBlog()}
    {if $oBlog->getType()!='personal'}
        <section class="panel panel-default widget widget-type-blog">
            <div class="panel-body">

                <header class="widget-header">
                    <h3 class="widget-title"><a href="{$oBlog->getUrlFull()}">{$oBlog->getTitle()|escape:'html'}</a></h3>
                </header>

                <div class="widget-content">
                    <small class="text-muted">
                        <span id="blog_user_count_{$oBlog->getId()}">{$oBlog->getCountUser()}</span> {$oBlog->getCountUser()|declension:$aLang.reader_declension:$sLang}
                        ·
                        {$oBlog->getCountTopic()} {$oBlog->getCountTopic()|declension:$aLang.topic_declension:$sLang}
                        ·
                        <a href="{R::GetLink("rss")}blog/{$oBlog->getUrl()}/" class="rss">RSS</a>
                    </small>

                    {if E::IsUser() AND E::UserId() != $oBlog->getOwnerId()}
                        <br/>
                        <br/>
                        <button type="submit" class="btn btn-default btn-sm {if $oBlog->getUserIsJoin()}active{/if}"
                                id="blog-join" data-only-text="1"
                                onclick="ls.blog.toggleJoin(this,{$oBlog->getId()}); return false;">{if $oBlog->getUserIsJoin()}{$aLang.blog_leave}{else}{$aLang.blog_join}{/if}</button>
                        &nbsp;&nbsp;
                    {/if}
                </div>

            </div>
        </section>
    {/if}
{/if}