 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{if $params.iCountTopic}
    <div class="toolbar-button toolbar-topic">
        <a href="#" onclick="return ls.toolbar.topic.goPrev();"
           {*title="{$aLang.toolbar_topic_prev}"*}
           class="toolbar-topic-prev-button link link-light-gray"><span class="fa fa-chevron-up"></span></a>
        <a href="#" onclick="return ls.toolbar.topic.goNext();"
           {*title="{$aLang.toolbar_topic_next}"*}
           class="toolbar-topic-next-button link link-light-gray"><span class="fa fa-chevron-down"></span></a>
    </div>
{/if}	
