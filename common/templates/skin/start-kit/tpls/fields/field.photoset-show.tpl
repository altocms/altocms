<div class="topic-photoset">
    {$aPhotos=$oTopic->getPhotosetPhotos(0, Config::Get('module.topic.photoset.per_page'))}
    {if count($aPhotos)<$oTopic->getPhotosetCount()}
    <h4>{$oTopic->getPhotosetCount()} {$oTopic->getPhotosetCount()|declension:$aLang.topic_photoset_count_images}</h4>
    {/if}
    <a name="photoset"></a>

    <div class="clearfix">
        {include file="snippets/snippet.photoset.tpl" aPhotos=$aPhotos sClass='clearfix'}
    </div>
    {if count($aPhotos) > 0 AND count($aPhotos) < $oTopic->getPhotosetCount()}
    <div class="clearfix">
        {$oLastResource = end($aPhotos)}
        <script type="text/javascript">
            $(function(){
                ls.photoset.idLast='{$oLastResource->getMresourceId()+1}';
                ls.photoset.nextImagesContainerSelector='.js-topic-photoset-list';
                ls.photoset.itemSelector='#js-topic-photoset-item';
                ls.photoset.thumbSize='x240';
                $('body').on('ls_photoset_update', function() {
                    var $currentContainer = $('.js-topic-photoset-list').last();
                    ls.photoset.prepareLastImages($currentContainer);
                    var $nextContainer = $('<div class="js-topic-photoset-list clearfix"></div>');
                    $nextContainer.insertAfter($currentContainer.last());
                });
            });
        </script>
        <div class="js-topic-photoset-list"></div>
        <script id="js-topic-photoset-item" type="text/template">
            <a href="#" class="topic-photoset-item"><img data-rel="prettyPhoto[pp_gal_]" src="" alt=""/></a>
        </script>
        <a href="#" id="topic-photo-more" class="btn btn-info btn-large btn-block topic-photo-more" onclick="ls.photoset.getMore('{$oTopic->getId()}'); return false;">
            {$aLang.topic_photoset_show_more} &darr;
        </a>
    <div
    {/if}
</div>
