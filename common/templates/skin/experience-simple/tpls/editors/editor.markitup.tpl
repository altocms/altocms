 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{if !$sSettingsMarkitup}
    {assign var="sSettingsMarkitup" value="ls.settings.getMarkitup()"}
{/if}
<script type="text/javascript">
    jQuery(function ($) {
        ls.lang.load({lang_load name="panel_photoset,panel_spoiler,panel_title_h4,panel_title_h5,panel_title_h6,panel_b,panel_i,panel_u,panel_s,panel_url,panel_url_promt,panel_code,panel_video,panel_image,panel_cut,panel_quote,panel_list,panel_list_ul,panel_list_ol,panel_title,panel_clear_tags,panel_video_promt,panel_list_li,panel_image_promt,panel_user,panel_user_promt"});

        var settings = {$sSettingsMarkitup};

        settings.markupSet = settings.markupSet.concat([
            { separator: '---------------' },
            {
                name:       ls.lang.get('panel_br'),
                className   : 'editor-br',
                replaceWith: function(markitup) { if (markitup.selection) return '\n<br/>\n'; else return '\n<br/>\n' }
            }
        ]);

        // Подключаем редактор
        $('.js-editor-markitup').markItUp(settings);
        ls.insertToEditor = function(markup) {
            $.markItUp({ replaceWith: markup });
        };

        {if Config::Get('view.float_editor')}
        $(function(){
            ls.editor.float({
                topStep: {if Config::Get('view.fix_menu')}60{else}0{/if},
                dif: -1,
                css: {
                    borderBottom: '1px'
                },
                textareaClass: '.js-editor-markitup',
                editorClass: '.markItUp',
                headerClass: '.markItUpHeader'
            });
        });
        {/if}
    });
</script>