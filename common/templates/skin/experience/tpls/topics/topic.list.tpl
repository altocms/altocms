 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if Config::Get('view.masonry') && !Config::Get('view.masonry_sidebar') && in_array(Router::GetAction(), Config::Get('view.masonry_sidebar_pages'))}{$isMasonry = true}{/if}

{if Config::Get('view.masonry') == true && $isMasonry}
        <script>
            $(function(){
                var $masonryContainer = $('.masonry-container');
                $masonryContainer.imagesLoaded( function() {
                    $masonryContainer.masonry({
                        columnWidth: '.col-lg-6',
                        itemSelector: '.masonry-item'
                    });
                });
            })
        </script>
{elseif Config::Get('view.masonry') }
        <script>
            $(function(){
                var $masonryContainer = $('.masonry-container');
                $masonryContainer.imagesLoaded( function() {
                    $masonryContainer.masonry({
                        columnWidth: '.col-lg-12',
                        itemSelector: '.masonry-item'
                    });
                });
            })
        </script>
{/if}

{if count($aTopics)>0}
    {wgroup_add group='toolbar' name='toolbar_topic.tpl' iCountTopic=count($aTopics)}
    {assign var="bLead" value="1"}
{*<div class="row masonry-container">*}
    {foreach $aTopics as $oTopic}
        {if E::Topic_IsAllowTopicType($oTopic->getType())}
            {$sTopicTemplateName=$oTopic->getTopicTypeTemplate('list')}
            {include file="topics/$sTopicTemplateName" bTopicList=true bLead=$bLead}
            {assign var="bLead" value="0"}
        {/if}
    {/foreach}
{*</div>*}
    {include file='commons/common.pagination.tpl' aPaging=$aPaging}
{else}
    <div class="bg-warning">
        {$aLang.blog_no_topic}
    </div>
{/if}
