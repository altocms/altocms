 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

<div class="panel panel-default flat js-page">

    <div class="panel-body">

        {block name="page_content"}
            <div class="topic-content text">
                {hook run='page_content_begin' topic=$oTopic bTopicList=false}

                {if Config::Get('view.wysiwyg')}
                    {$oPage->getText()}
                {else}
                    {if $oPage->getAutoBr()}
                        {$oPage->getText()|nl2br}
                    {else}
                        {$oPage->getText()}
                    {/if}
                {/if}

                {hook run='page_content_end' topic=$oTopic bTopicList=false}
            </div>
        {/block}

    </div>

</div>