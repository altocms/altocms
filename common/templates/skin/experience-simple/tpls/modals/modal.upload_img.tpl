{* Тема оформления Experience v.1.0  для Alto CMS      *}
{* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

<script>
$(function () {
    $('#modal-upload_img').on('hidden.bs.modal', function () {
        $('#modal-upload_img')
            .find('input').val('')
            .end()
            .find('select :first').attr("selected", "selected")
            .trigger('change') /* update state «selecter» plugin */
            .end()
            .find('span.fileinput-filename').html("")
            .end()
            .find('[name="img_width"]').val("100")
        ;
    })
})
</script>
 <div class="modal fade in" id="modal-upload_img">
     <div class="modal-dialog">
         <div class="modal-content">

             <header class="modal-header">
                 <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                 <h4 class="modal-title">{$aLang.uploadimg}</h4>
             </header>

             <div class="modal-body">
                 <ul class="nav nav-tabs">
                     <li class="active">
                         <a href="#" data-toggle="tab" data-target=".js-pane-upload_img_pc">{$aLang.uploadimg_from_pc}</a></li>
                     <li>
                         <a href="#" data-toggle="tab" data-target=".js-pane-upload_img_link">{$aLang.uploadimg_from_link}</a>
                     </li>
                 </ul>

                 <div class="tab-content">
                     <div class="tab-pane active js-pane-upload_img_pc">

                         <br/>

                         <form method="POST" action="" enctype="multipart/form-data" id="block_upload_img_content_pc"
                               onsubmit="return false;" class="js-block-upload-img-content">

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


                             {hook run="uploadimg_source"}

                             <div class="form-group">
                                 <div class="input-group">
                                     <span class="input-group-addon">{$aLang.uploadimg_align}</span>
                                     <select name="align" id="form-image-align" class="form-control">
                                         <option value="">{$aLang.uploadimg_align_no}</option>
                                         <option value="left">{$aLang.uploadimg_align_left}</option>
                                         <option value="right">{$aLang.uploadimg_align_right}</option>
                                         <option value="center">{$aLang.uploadimg_align_center}</option>
                                     </select>
                                 </div>
                             </div>

                             <div class="form-group js-img_width">
                                 <div class="input-group">
                                     <span class="input-group-addon">{$aLang.uploadimg_size_width_max}</span>
                                     <input type="text" name="img_width" value="100" class="form-control"/>
                                     <span class="input-group-addon">%</span>
                                 </div>
                                 <input type="hidden" name="img_width_unit" value="percent" />
                                 <input type="hidden" name="img_width_ref" value="text" />
                                 <input type="hidden" name="img_width_text" value="" />
                                 <small class="control-notice">{$aLang.uploadimg_size_width_max_text}</small>
                             </div>

                             <div class="form-group">
                                 <div class="input-group">
                                     <span class="input-group-addon">{$aLang.uploadimg_title}</span>
                                     <input type="text" name="title" id="form-image-title" value="" class="form-control"/>
                                 </div>
                             </div>

                             {hook run="uploadimg_additional"}

                             <button type="submit" class="btn btn-light btn-normal corner-no" data-dismiss="modal">{$aLang.uploadimg_cancel}</button>
                             <button type="submit" class="btn btn-blue pull-right btn-normal corner-no " onclick="ls.ajaxUploadImg(this,'{$sToLoad}');">
                                 {$aLang.uploadimg_submit}
                             </button>
                         </form>
                     </div>

                     <div class="tab-pane js-pane-upload_img_link">
                         <br/>
                         <form method="POST" action="" enctype="multipart/form-data" id="block_upload_img_content_link"
                               onsubmit="return false;" class="tab-content js-block-upload-img-content">

                             <div class="form-group">
                                 <div class="input-group">
                                     <span class="input-group-addon">{$aLang.topic_link_create_url}</span>
                                     <input type="text" name="img_url" id="img_url" value="http://" class="form-control"/>
                                 </div>
                             </div>

                             <div class="form-group">
                                 <div class="input-group">
                                     <span class="input-group-addon">{$aLang.uploadimg_align}</span>
                                     <select name="align" id="form-image-url-align" class="form-control">
                                         <option value="">{$aLang.uploadimg_align_no}</option>
                                         <option value="left">{$aLang.uploadimg_align_left}</option>
                                         <option value="right">{$aLang.uploadimg_align_right}</option>
                                         <option value="center">{$aLang.uploadimg_align_center}</option>
                                     </select>
                                 </div>
                             </div>

                             <div class="form-group">
                                 <div class="input-group">
                                     <span class="input-group-addon">{$aLang.uploadimg_title}</span>
                                     <input type="text" name="title" id="form-image-url-title" value="" class="form-control"/>
                                 </div>
                             </div>



                             {hook run="uploadimg_link_additional"}

                             <button type="submit" class="btn btn-blue pull-right btn-normal corner-no" onclick="ls.insertImageToEditor(this);">
                                 {$aLang.uploadimg_link_submit_paste}
                             </button>
                             <span class="pull-right pa5">{$aLang._or}</span>
                             <button type="submit" class="btn btn-blue pull-right btn-normal corner-no" onclick="ls.ajaxUploadImg(this,'{$sToLoad}');">
                                 {$aLang.uploadimg_link_submit_load}
                             </button>
                             <button type="submit" class="btn btn-light btn-normal corner-no" data-dismiss="modal">{$aLang.uploadimg_cancel}</button>
                         </form>
                     </div>
                 </div>

             </div>

         </div>
     </div>
 </div>

 <script>
     $(function(){
         $('.js-img_width').each(function(){
             var imgWidthGroup = $('.js-img_width'),
                     textWidth = imgWidthGroup.closest('.content-inner').width();

             imgWidthGroup.find('[name=img_width_text]').val(textWidth);
         });
     });
 </script>
