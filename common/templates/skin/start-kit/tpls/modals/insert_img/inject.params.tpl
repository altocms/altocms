<div class="row" id="aim-params" style="display: none;">
    <div class="col-md-12 bg bg-warning">
        <div class="row">
            <div class="form-group col-xs-6">
                <label for="form-image-align">{$aLang.uploadimg_align}</label>
                <select name="align" id="form-image-align" class="form-control">
                    <option value="">{$aLang.uploadimg_align_no}</option>
                    <option value="left">{$aLang.uploadimg_align_left}</option>
                    <option value="right">{$aLang.uploadimg_align_right}</option>
                    <option value="center">{$aLang.uploadimg_align_center}</option>
                </select>
            </div>

            <div class="form-group col-xs-6 js-img_width">
                <label>{$aLang.insertimg_size}</label>
                <div class="input-group">
                    <input type="text" name="img_width" value="100" class="form-control"/>
                    <span class="input-group-addon">%</span>
                </div>
                <input type="hidden" name="img_width_unit" value="percent" />
                <input type="hidden" name="img_width_ref" value="text" />
                <input type="hidden" name="img_width_text" value="" />
            </div>

            <div class="form-group col-xs-12">
                <label for="form-image-title">{$aLang.uploadimg_title}</label>
                <textarea  name="title" id="form-image-title"  class="form-control" rows="2"></textarea>
            </div>
        </div>
    </div>
    <br/>
    <br/>
</div>