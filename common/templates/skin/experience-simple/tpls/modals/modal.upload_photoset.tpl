 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

<div class="modal fade in" id="modal-upload_photoset">
    <div class="modal-dialog">
        <div class="modal-content">

            <header class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">{$aLang.uploadimg}</h4>
            </header>

            <div class="modal-body">
                <form id="photoset-upload-form" method="POST" enctype="multipart/form-data" onsubmit="return false;">

                    <div id="topic-photo-upload-input" class="topic-photo-upload-input">
                        <label for="photoset-upload-file">{$aLang.topic_photoset_choose_image}:</label>
                        <input type="file" id="photoset-upload-file" name="Filedata"/><br><br>

                        <button type="submit" class="btn btn-blue btn-normal corner-no"  onclick="ls.photoset.upload();">
                            {$aLang.topic_photoset_upload_choose}
                        </button>
                        <button type="submit" class="btn btn-light pull-left btn-normal corner-no"  onclick="ls.photoset.closeForm();">
                            {$aLang.topic_photoset_upload_close}
                        </button>

                        <input type="hidden" name="is_iframe" value="true"/>
                        <input type="hidden" name="topic_id" value="{$_aRequest.topic_id}"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
