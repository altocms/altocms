<div class="row" id="aim-params" style="display: none;">
    <div class="col-md-24">
        <div class="bg bg-warning">
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
                <input type="hidden" name="img_width_unit" value="percent"/>
                <input type="hidden" name="img_width_ref" value="text"/>
                <input type="hidden" name="img_width_text" value=""/>
            </div>
            <div class="form-group mab0">
                <div class="input-group">
                    <span class="input-group-addon">{$aLang.uploadimg_title}</span>
                    <input type="text" name="title" id="form-image-title" value="" class="form-control"/>
                </div>
            </div>
        </div>
    </div>
</div>
<br/>