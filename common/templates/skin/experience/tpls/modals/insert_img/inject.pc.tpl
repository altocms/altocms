<div id="aim-page-pc" class="row">
    <div class="col-md-24">
        <form method="POST" action="" enctype="multipart/form-data" onsubmit="return false;">

            {include file="tpls/modals/insert_img/inject.params.tpl"}

            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon">{$aLang.uploadimg_file}</span>
                    <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                        <div class="form-control" data-trigger="fileinput">
                            <i class="fa fa-file fileinput-exists"></i>
                            <span class="fileinput-filename"></span>
                        </div>
                    <span class="input-group-addon btn btn-default btn-file" >
                        <span style="cursor: pointer"  class="fileinput-new">{$aLang.select}</span>
                        <span style="cursor: pointer"  class="fileinput-exists">{$aLang.select}</span>
                        <input type="file" name="img_file" id="img_file" />
                    </span>
                    </div>
                </div>
            </div>

            {hook run="uploadimg_additional"}

            <div class="clearfix">
                <button type="button" class="js-aim-btn-upload-image btn btn-blue btn-big corner-no pull-right">
                    {$aLang.uploadimg_submit}
                </button>
            </div>
        </form>
    </div>

</div>