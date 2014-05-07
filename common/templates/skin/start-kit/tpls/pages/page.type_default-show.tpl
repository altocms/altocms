<article class="topic page-type_default js-page">
    {block name="page_header"}
        <header class="topic-header">
            <h1 class="topic-header-title">
                {$oPage->getTitle()|escape:'html'}
            </h1>
        </header>
    {/block}

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

    {block name="page_footer"}
    {/block}
</article>