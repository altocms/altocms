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

    {if Config::Get('view.float_editor')}
    $(function(){
        ls.editor.float({
            topStep: {if Config::Get('view.fix_menu')}43{else}0{/if},
            dif: 0,
            textareaClass: '.js-editor-wysiwyg',
            editorClass: '.mce-tinymce',
            headerClass: '.mce-toolbar-grp'
        });
    });
    {/if}
</script>
