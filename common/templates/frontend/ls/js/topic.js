var ls = ls || {};

/**
 * Опросы
 */
ls.topic = (function ($) {

	this.preview = function(form, preview) {
		form=$('#'+form);
		preview=$('#'+preview);
		var url = aRouter['ajax']+'preview/topic/';
		ls.hook.marker('previewBefore');
		ls.ajaxSubmit(url, form, function(result) {
			if (result.bStateError) {
				ls.msg.error(null, result.sMsg);
			} else {
				preview.show().html(result.sText);
				ls.hook.run('ls_topic_preview_after',[form, preview, result]);
			}
		});
	};

	this.insertImageToEditor = function(sUrl,sAlign,sTitle) {
		sAlign=sAlign=='center' ? 'class="image-center"' : 'align="'+sAlign+'"';
		$.markItUp({replaceWith: '<img src="'+sUrl+'" title="'+sTitle+'" '+sAlign+' />'} );
		$('#window_upload_img').find('input[type="text"]').val('');
		$('#window_upload_img').jqmHide();
		return false;
	};

    this.toggleAddTools = function(element) {
        $(element).slideDown();
    }

	return this;
}).call(ls.topic || {},jQuery);

/**
 * Topic URL
 */
ls.topic = (function ($) {

    this.editUrl = function(button) {
        var parent = $(button).parents('p').first();
        if (!parent.length) {
            parent = $(button).parent();
        }
        parent.find('.b-topic_url_demo-edit').hide();
        parent.find('[name=topic_url]').show().focus();
   };

    this.shortUrl = function(url) {
        prompt('URL:', url);
        return false;
    };

    return this;
}).call(ls.topic || {},jQuery);
