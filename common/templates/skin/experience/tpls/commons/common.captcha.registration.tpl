<div class="form-group captcha-input">
    <div class="input-group">
        <label for="input-registration-captcha" class="input-group-addon">
            <img src="" onclick="this.src='{router page='captcha'}registration/?n='+Math.random();" class="captcha-image"/>
        </label>
        <input type="text" name="captcha" id="input-registration-captcha" value=""
               maxlength="3" class="form-control captcha-input js-ajax-validate" required/>
    </div>
    <small class="text-danger validate-error-hide validate-error-field-captcha"></small>
</div>