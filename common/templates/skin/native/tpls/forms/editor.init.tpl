{**
 * Инициализация редактора
 *
 * sEditorType - тип
 * sEditorSelector - селектор textarea с редактором
 *
 * Настройки редакторов храняться в файле templates/framework/js/core/settings.js
 *
 * TODO: Исправить повторное подключение скрипта
 * TODO: Локализация TinyMCE
 *}

{* Дефолтный тип редактора *}
{if ! $sEditorType}
	{$sEditorType = 'default'}
{/if}

{* Дефолтный селектор редактора *}
{if ! $sEditorSelector}
	{$sEditorSelector = '.js-editor'}
{/if}
<script>
    var ls = ls || { }
    ls.editor = ls.editor || { }

    ls.editor.selector = '{$sEditorSelector}';
    ls.editor.type = '{$sEditorType}';
</script>
{* Инициализация *}
{if Config::Get('view.wysiwyg')}
	{* WYSIWYG редактор *}

	{hookb run='editor_init_wysiwyg' sEditorType=$sEditorType sEditorSelector=$sEditorSelector}

		<script>
			jQuery(function($) {
                var settings = $.extend(
                        ls.settings.get('tinymce_' + ls.editor.type), {
                            selector : ls.editor.selector,
                            language : '{Config::Get('lang.current')}'
                        });
                tinyMCE.init(settings);
			});
		</script>
	{/hookb}
{else}
	{* Markup редактор *}

	{hookb run='editor_init_markup' sEditorType=$sEditorType sEditorSelector=$sEditorSelector}
		{include file='modals/modal.upload_image.tpl'}

		<script>
			jQuery(function($) {
                var settings = ls.settings.get('markitup_' + ls.editor.type);
				ls.lang.load({lang_load name="panel_b,panel_i,panel_u,panel_s,panel_url,panel_url_promt,panel_code,panel_video,panel_image,panel_cut,panel_quote,panel_list,panel_list_ul,panel_list_ol,panel_title,panel_clear_tags,panel_video_promt,panel_list_li,panel_image_promt,panel_user,panel_user_promt"});

				$(ls.editor.selector).markItUp(settings);
			});
		</script>
	{/hookb}
{/if}