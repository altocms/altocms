 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if !$sSettingsTinymce}
    {assign var="sSettingsTinymce" value="ls.settings.getTinymce()"}
{/if}
<script type="text/javascript">
    if (!tinymce) {
        ls.loadAssetScript('tinymce_4', function(){
            jQuery(function(){
                tinymce.init({$sSettingsTinymce});
            });
        });
    } else {
        jQuery(function(){
            tinymce.init({$sSettingsTinymce});
        });
    }
</script>
