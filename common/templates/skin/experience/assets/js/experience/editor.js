/*!
 * Тема оформления Experience v.1.0  для Alto CMS
 * @licence     CC Attribution-ShareAlike
 */


$(function () {

    if (tinyMCE && ls.settings && ls.settings.presets.tinymce) {
        var cssUrl = ls.getAssetUrl('template-tinymce.css');
        if (cssUrl) {
            ls.settings.presets.tinymce.default['content_css'] = cssUrl;
        }
    }

});