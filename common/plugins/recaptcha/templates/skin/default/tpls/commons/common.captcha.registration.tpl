<div class="form-group">
    <div class="g-recaptcha"
         {if Config::Get('plugin.recaptcha.dark_theme')}data-theme="dark"{/if}
         {if Config::Get('plugin.recaptcha.audio')}data-type="audio"{/if}
         data-sitekey="{Config::Get('plugin.recaptcha.public_key')}"></div>
</div>