<script>
    $(function(){
        $('.captcha-image').prop('src', ls.routerUrl('captcha') + 'registration/?n=' + Math.random());
    })
</script>
<div class="form-group">
    <label for="input-registration-captcha" class="captcha">{$aLang.registration_captcha}</label>
    <img src="" onclick="this.src='{router page='captcha'}registration/?n='+Math.random();"
         class="captcha-image"/>
    <input type="text" name="captcha" id="input-registration-captcha" value=""
           maxlength="3" class="form-control captcha-input js-ajax-validate" required/>

    <p class="help-block">
        <small class="text-danger validate-error-hide validate-error-field-captcha"></small>
    </p>
</div>