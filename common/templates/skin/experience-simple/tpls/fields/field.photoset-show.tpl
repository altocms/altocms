{* Тема оформления Experience v.1.0  для Alto CMS      *}
{* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

<div class="topic-photoset">
    {$aPhotos=$oTopic->getPhotosetPhotos(0, Config::Get('module.topic.photoset.per_page'))}
    {if count($aPhotos)<$oTopic->getPhotosetCount()}
        <h4 class="accent">{$oTopic->getPhotosetCount()} {$oTopic->getPhotosetCount()|declension:$aLang.topic_photoset_count_images}</h4>
    {/if}
    <a name="photoset"></a>

    {include file="snippets/snippet.photoset.tpl" aPhotos=$aPhotos}
    <script type="text/javascript">
        ls.photoset.idLast='{$iLastPhotoId+1}';
        ls.photoset.thumbSize='{$sThumbneilSize}';
    </script>

    <div class="clearfix">
        {if count($aPhotos)<$oTopic->getPhotosetCount()}
            <a href="#" id="topic-photo-more" class="btn btn-blue btn-large btn-block topic-photo-more" onclick="ls.photoset.getMore('{$oTopic->getId()}'); return false;">
                {$aLang.topic_photoset_show_more}&nbsp;<i class="fa fa-chevron-down"></i>
            </a>
        {/if}
    </div>
</div>
