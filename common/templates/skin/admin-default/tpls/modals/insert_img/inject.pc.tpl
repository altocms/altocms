<div id="aim-page-pc">
    <form method="POST" action="" enctype="multipart/form-data" onsubmit="return false;">

        {include file="tpls/modals/insert_img/inject.params.tpl"}

        <div class="form-group">
            <input type="file" name="img_file" id="img_file" />
            <br/>
            <span>{$aLang.uploadimg_choose_file}</span>
        </div>

        {hook run="uploadimg_additional"}

        <div class="clearfix">
            <button type="button" class="js-aim-btn-upload-image btn btn-primary pull-right">
                {$aLang.uploadimg_submit}
            </button>
        </div>
    </form>
</div>