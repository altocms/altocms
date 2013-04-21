<section class="b-widget block-type-stream">
    <header class="b-widget-header">
        <a href="{router page='comments'}" title="{$aLang.block_stream_comments_all}">{$aLang.block_stream}</a>

        <div class="block-update js-block-stream-update"></div>
    </header>

{hook run='block_stream_nav_item' assign="sItemsHook"}

    <div class="b-widget-menu">
        <ul class="b-nav-pills js-block-stream-nav" {if $sItemsHook}style="display: none;"{/if}>
            <li class="active js-block-stream-item" data-type="comment"><a href="#">{$aLang.block_stream_comments}</a>
            </li>
            <li class="js-block-stream-item" data-type="topic"><a href="#">{$aLang.block_stream_topics}</a></li>
        {$sItemsHook}
        </ul>

        <ul class="b-nav-pills js-block-stream-dropdown" {if !$sItemsHook}style="display: none;"{/if}>
            <li class="dropdown active js-block-stream-dropdown-trigger"><a href="#">{$aLang.block_stream_comments}</a>
                <i class="arrow"></i>
                <ul class="dropdown-menu js-block-stream-dropdown-items">
                    <li class="active js-block-stream-item" data-type="comment"><a
                            href="#">{$aLang.block_stream_comments}</a></li>
                    <li class="js-block-stream-item" data-type="topic"><a href="#">{$aLang.block_stream_topics}</a></li>
                {$sItemsHook}
                </ul>
            </li>
        </ul>
    </div>

    <div class="b-widget-content js-block-stream-content">
    {$sStreamComments}
    </div>
</section>

