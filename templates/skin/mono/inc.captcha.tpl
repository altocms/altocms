<script type="text/javascript">
    var ls = ls || { };
    ls.user = ls.user || { };

    ls.user.captchaReset = function (img) {
        var img = $('.captcha-image');
        var btn = $('.captcha-update');
        btn.addClass('active');
        $(img).load(function () {
            setTimeout(function () {
                btn.removeClass('active');
            }, 1000);
        });
        img.prop('src', '{router page='captcha'}/?&n=' + Math.random());
    }
</script>

<p><label for="popup-registration-captcha">{$aLang.registration_captcha}</label>
<div class="captcha-group">
    <img src="{router page='captcha'}" onclick="ls.user.captchaReset();" class="captcha-image"/>
    <input type="text" name="captcha" id="popup-registration-captcha" value="" maxlength="3"
           class="input-text captcha-value input-width-100 js-ajax-validate"/>

    <div class="captcha-update js-tip-help" title="{$aLang.registration_captcha_update}"
         onclick="ls.user.captchaReset();"></div>
</div>
<small class="validate-error-hide validate-error-field-captcha"></small>
</p>
