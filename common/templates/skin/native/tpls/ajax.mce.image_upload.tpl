<div id="mce-plugin-altoimage-form" class="js-topic-image-upload-form">
    <div>
        <label for="mce-plugin-altoimage-image" class="form-input-file mce-btn"
               style="display: block; text-align: center;">
            <button>
                {$aLang.uploadimg_choose_file}
            </button>
            <input type="file" name="img_file" id="mce-plugin-altoimage-image" class="js-topic-image-upload-file"/>
        </label>
    </div>
    <br/>

    <div class="b-topic-image-wrapper">
        <img src="" alt="" class="js-ajax-image-upload-image" align="center"/>
    </div>
    <div class="b-topic-image-tools">
        <div class="b-topic-image-tools-align">
            <h6>{$aLang.uploadimg_align}</h6>
            <label class="b-image-align i-left">
                <input type="radio" name="image_align" id="image_align_left" value="left"/>
                {$aLang.uploadimg_align_left}
            </label>
            <label class="b-image-align i-bottom">
                <input type="radio" name="image_align" id="image_align_no" value="bottom" checked/>
                {$aLang.uploadimg_align_no}
            </label>
            <label class="b-image-align i-right">
                <input type="radio" name="image_align" id="image_align_right" value="right"/>
                {$aLang.uploadimg_align_right}
            </label>
        </div>
        <div class="b-topic-image-tools-size">
            <h6>{$aLang.uploadimg_size}</h6>
            <input type="text" name="image_width" class="i-image-size" readonly />
            <strong>x</strong>
            <input type="text" name="image_height" class="i-image-size" readonly />
        </div>
        <div class="b-topic-image-tools-title">
            <h6>{$aLang.uploadimg_title}</h6>
            <input type="text" name="image_title" />
        </div>
    </div>

</div>
