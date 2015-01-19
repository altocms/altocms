<div id="aim-page-link">
    <form method="POST" action="" enctype="multipart/form-data" onsubmit="return false;">

        {include file="tpls/modals/insert_img/inject.params.tpl"}

        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon">{$aLang.topic_link_create_url}</span>
                <input type="text" name="img_url" id="img_url" value="http://" class="form-control"/>
            </div>
        </div>

        {hook run="uploadimg_link_additional"}

        <div class="clearfix text-right">
            <button type="submit" class="btn btn-light btn-big corner-no js-aim-btn-insert-link">
                {$aLang.uploadimg_link_submit_paste}
            </button>
            &nbsp;{$aLang._or}&nbsp;
            <button type="submit" class="js-aim-btn-upload-image btn btn-blue btn-big corner-no  js-aim-btn-upload-image">
                {$aLang.uploadimg_link_submit_load}
            </button>
        </div>

    </form>
</div>