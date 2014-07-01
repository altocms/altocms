 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<script type="text/javascript">
    jQuery(document).ready(function($){
        $("a[rel^='prettyPhoto']").prettyPhoto({
            social_tools: '',
            show_title: true,
            deeplinking: false
        });

        var $masonryTopicGalleryWidth = 6;
        var $masonryTopicGallery = $('.js-topic-photoset-list');

        $masonryTopicGallery.imagesLoaded(function () {
            $('.js-topic-photoset-list').css('margin-bottom', $masonryTopicGalleryWidth + 'px');
            $masonryTopicGallery.masonry({
                columnWidth:  $masonryTopicGalleryWidth,
                itemSelector: '.topic-photoset-item'
            });
        });

    });
</script>

<div class="topic-photoset">
    {$aPhotos=$oTopic->getPhotosetPhotos(0, Config::Get('module.topic.photoset.per_page'))}
    {if count($aPhotos)<$oTopic->getPhotosetCount()}
    <h4 class="accent">{$oTopic->getPhotosetCount()} {$oTopic->getPhotosetCount()|declension:$aLang.topic_photoset_count_images}</h4>
    {/if}
    <a name="photoset"></a>

    <ul class="topic-photoset-list list-unstyled list-inline clearfix js-topic-photoset-list">
        {if count($aPhotos)}
            {foreach $aPhotos as $oPhoto}
                <li class="topic-photoset-item" style="margin-bottom: 6px;">
                    <a class="topic-photoset-image" href="{$oPhoto->getUrl()}" rel="prettyPhoto[pp_gal]"  title="{$oPhoto->getDescription()}">
                        <img src="{$oPhoto->getUrl('x80')}" {$oPhoto->getImgSizeAttr('x80')} alt="{$oPhoto->getDescription()}" class="" />
                    </a>
                </li>
                {$iLastPhotoId=$oPhoto->getId()}
            {/foreach}
        {/if}
        <script type="text/javascript">
            ls.photoset.idLast='{$iLastPhotoId}';
        </script>
    </ul>

    {if count($aPhotos)<$oTopic->getPhotosetCount()}
        <a href="#" id="topic-photo-more" class="btn btn-blue btn-large btn-block topic-photo-more" onclick="ls.photoset.getMore('{$oTopic->getId()}'); return false;">
            {$aLang.topic_photoset_show_more}&nbsp;<i class="fa fa-chevron-down"></i>
        </a>
    {/if}
</div>
