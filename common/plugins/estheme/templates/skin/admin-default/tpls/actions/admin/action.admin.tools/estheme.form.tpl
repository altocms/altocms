{* ОСНОВНЫЕ ЦВЕТА *}
<h4>{$aLang.plugin.estheme.form_main_colors}</h4>
{*<label class="control-label">{$aLang.plugin.estheme.form_main_colors_notice}</label>*}
<div class="row">
    <div class="control-group">
        {* ОСНОВНОЙ ЦВЕТ САЙТА *}
        {include file="{$sPartsPath}part.color-picker.tpl" id="color_main_color" grid="2" notice="form_main_colors_main"}
        {* СВЕТЛЕЕ ОСНОВНОГО *}
        {include file="{$sPartsPath}part.color-picker.tpl" id="color_main_light" grid="2" notice="form_main_colors_light"}
        {* ТЕМНЕЕ ОСНОВНОГО *}
        {include file="{$sPartsPath}part.color-picker.tpl" id="color_main_dark" grid="2" notice="form_main_colors_dark"}
        {* ЕЩЁ ТЕМНЕЕ ОСНОВНОГО *}
        {include file="{$sPartsPath}part.color-picker.tpl" id="color_main_dark_2" grid="2" notice="form_main_colors_dark_2"}
        {* ЦВЕТ ШРИФТА ТЕКСТА САЙТА *}
        {include file="{$sPartsPath}part.color-picker.tpl" id="color_main_font" grid="2" notice="form_main_colors_font"}
        {* ЦВЕТ АКТИВНОЙ ССЫЛКИ САЙТА *}
        {include file="{$sPartsPath}part.color-picker.tpl" id="color_main_active_link" grid="2" notice="form_main_colors_active_link"}
    </div>
</div>
<div class="row">
    <div class="control-group">
        {include file="{$sPartsPath}part.color-picker.tpl" id="color_other_gray" grid="2" notice="form_other_colors_gray"}
        {include file="{$sPartsPath}part.color-picker.tpl" id="color_other_blue" grid="2" notice="form_other_colors_blue"}
        {include file="{$sPartsPath}part.color-picker.tpl" id="color_other_light_blue" grid="2" notice="form_other_colors_light_blue"}
        {include file="{$sPartsPath}part.color-picker.tpl" id="color_other_red" grid="2" notice="form_other_colors_red"}
        {include file="{$sPartsPath}part.color-picker.tpl" id="color_other_green" grid="2" notice="form_other_colors_green"}
        {include file="{$sPartsPath}part.color-picker.tpl" id="color_other_orange" grid="2" notice="form_other_colors_orange"}
    </div>
</div>


<br/>

{* ОСНОВНАЯ МЕТРИКА САЙТА *}
<h4>{$aLang.plugin.estheme.form_main_metrics}</h4>
{*<label class="control-label">{$aLang.plugin.estheme.form_main_metrix_notice}</label>*}
<div class="row">
    <div class="control-group">
        {include file="{$sPartsPath}part.pixel.tpl" id="metrics_main_width" grid="2" notice="form_main_metrics_main_width"}
        {include file="{$sPartsPath}part.pixel.tpl" id="metrics_main_menu_main_height" grid="2" notice="form_main_metrics_main_menu_main_height"}
        {include file="{$sPartsPath}part.pixel.tpl" id="metrics_main_menu_content_height" grid="2" notice="form_main_metrics_main_menu_content_height"}
        {include file="{$sPartsPath}part.pixel.tpl" id="metrics_main_font_size" grid="2" notice="form_main_metrics_main_font_size"}
        {include file="{$sPartsPath}part.pixel.tpl" id="metrics_main_font_size_small" grid="2" notice="form_main_metrics_main_font_size_small"}

    </div>
</div>
<div class="row">
    <div class="control-group">
        {include file="{$sPartsPath}part.pixel.tpl" id="metrics_main_h1" grid="2" notice="form_main_metrics_main_h1"}
        {include file="{$sPartsPath}part.pixel.tpl" id="metrics_main_h2" grid="2" notice="form_main_metrics_main_h2"}
        {include file="{$sPartsPath}part.pixel.tpl" id="metrics_main_h3" grid="2" notice="form_main_metrics_main_h3"}
        {include file="{$sPartsPath}part.pixel.tpl" id="metrics_main_h4" grid="2" notice="form_main_metrics_main_h4"}
        {include file="{$sPartsPath}part.pixel.tpl" id="metrics_main_h5" grid="2" notice="form_main_metrics_main_h5"}
        {include file="{$sPartsPath}part.pixel.tpl" id="metrics_main_h6" grid="2" notice="form_main_metrics_main_h6"}
    </div>
</div>