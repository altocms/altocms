{if !$sSettingsMarkitup}
    {assign var="sSettingsMarkitup" value="ls.settings.getMarkitup()"}
{/if}
<script type="text/javascript">
    jQuery(function ($) {
        ls.lang.load({lang_load name="panel_b,panel_i,panel_u,panel_s,panel_url,panel_url_promt,panel_code,panel_video,panel_image,panel_cut,panel_quote,panel_list,panel_list_ul,panel_list_ol,panel_title,panel_clear_tags,panel_video_promt,panel_list_li,panel_image_promt,panel_user,panel_user_promt"});
        // Подключаем редактор
        var settings = {$sSettingsMarkitup};
        $('.js-editor-markitup').markItUp(settings);
        ls.insertToEditor = function(markup) {
            $.markItUp({ replaceWith: markup });
        }
    });
</script>
