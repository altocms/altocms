<div class="topic-photoset">
    {$aPhotos=$oTopic->getPhotosetPhotos(0, Config::Get('module.topic.photoset.per_page'))}
    {if count($aPhotos)<$oTopic->getPhotosetCount()}
    <h4>{$oTopic->getPhotosetCount()} {$oTopic->getPhotosetCount()|declension:$aLang.topic_photoset_count_images}</h4>
    {/if}
    <a name="photoset"></a>

    <div class="clearfix">
        {include file="snippets/snippet.photoset.tpl" aPhotos=$aPhotos}
    </div>
    <script type="text/javascript">
        ls.photoset.idLast='{$iLastPhotoId+1}';
        ls.photoset.thumbSize='{$sThumbneilSize}';
    </script>


    {if count($aPhotos)<$oTopic->getPhotosetCount()}
    <div class="clearfix">
        <br/>
        <a href="#" id="topic-photo-more" class="btn btn-info btn-large btn-block topic-photo-more" onclick="ls.photoset.getMore('{$oTopic->getId()}'); return false;">
            {$aLang.topic_photoset_show_more} &darr;
        </a>
    <div
    {/if}
</div>
