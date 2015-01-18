<div id="aim-page-pc">
    <form method="POST" action="" enctype="multipart/form-data" onsubmit="return false;">

        {include file="tpls/modals/insert_img/inject.params.tpl"}

        <div class="form-group">
            <label>{$aLang.uploadimg_file}</label>
            <br/>
            <div class="btn btn-default btn-file">
                <span>{$aLang.uploadimg_choose_file}</span>
                <input type="file" name="img_file" id="img_file" />
            </div>
        </div>

        {hook run="uploadimg_additional"}

        <div class="clearfix">
            <button type="button" class="js-aim-btn-upload-image btn btn-success pull-right">
                {$aLang.uploadimg_submit}
            </button>
        </div>
    </form>
</div>