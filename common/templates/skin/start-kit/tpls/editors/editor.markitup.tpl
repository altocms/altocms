{if !$sSettingsMarkitup}
    {assign var="sSettingsMarkitup" value="ls.settings.getMarkitup()"}
{/if}
<script type="text/javascript">
    jQuery(function ($) {
        ls.lang.load({lang_load name="panel_title_h4,panel_title_h5,panel_title_h6,panel_b,panel_i,panel_u,panel_s,panel_url,panel_url_promt,panel_code,panel_video,panel_image,panel_cut,panel_quote,panel_list,panel_list_ul,panel_list_ol,panel_title,panel_clear_tags,panel_video_promt,panel_list_li,panel_image_promt,panel_user,panel_user_promt,panel_photoset,panel_spoiler"});
        // Подключаем редактор
        var settings = {$sSettingsMarkitup};
        $('.js-editor-markitup').markItUp(settings);
        ls.insertToEditor = function(markup) {
            $.markItUp({ replaceWith: markup });
        };

        {if Config::Get('view.float_editor')}
        ls.editor.float({
            topStep: {if Config::Get('view.header.top') == 'fixed'}60{else}0{/if},
            dif: 0,
            textareaClass: '.js-editor-markitup',
            editorClass: '.markItUp',
            headerClass: '.markItUpHeader'
        });
        {/if}
    });
</script>
