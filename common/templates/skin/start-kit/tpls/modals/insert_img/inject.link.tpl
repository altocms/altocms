<div id="aim-page-link">
    <form method="POST" action="" enctype="multipart/form-data" onsubmit="return false;">

        {include file="tpls/modals/insert_img/inject.params.tpl"}

        <div class="form-group">
            <label for="img_file">{$aLang.uploadimg_url}</label>
            <input type="text" name="img_url" id="img_url" value="http://" class="form-control"/>
        </div>

        {hook run="uploadimg_link_additional"}

        <div class="clearfix text-right">
            <button type="submit" class="btn btn-success js-aim-btn-insert-link">
                {$aLang.uploadimg_link_submit_paste}
            </button>
            {$aLang._or}
            <button type="submit" class="btn btn-success js-aim-btn-upload-image">
                {$aLang.uploadimg_link_submit_load}
            </button>
        </div>

    </form>
</div>