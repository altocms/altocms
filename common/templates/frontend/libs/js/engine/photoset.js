/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   LiveStreet Engine Social Networking by Mzhelskiy Maxim
 *   Site: www.livestreet.ru
 *   E-mail: rus.engine@gmail.com
 *----------------------------------------------------------------------------
 */
;
var ls = ls || {};

ls.photoset = ( function ($) {

    this.idLast = 0;
    this.isLoading = false;
    this.swfu;

    /**
     * Инициализация
     */
    this.init = function () {
        var self = this;

        $('#photoset-upload-file').on('change', function (e) {
            self.upload();
        });
    };

    this.initSwfUpload = function (opt) {
        opt = opt || {};
        opt.button_placeholder_id = 'photoset-upload-place';
        opt.post_params.ls_photoset_target_tmp = $.cookie('ls_photoset_target_tmp') ? $.cookie('ls_photoset_target_tmp') : 0;

        var uploadButton = $('#photoset-upload-button');
        opt.button_width = uploadButton.outerWidth();
        opt.button_height = uploadButton.outerHeight();

        $(ls.swfuploader).unbind('load').bind('load', function () {
            this.swfu = ls.swfuploader.init(opt);

            $(this.swfu).bind('eReady', function(e){
                ls.log('[ready]', $(e.target.movieElement));
                $(e.target.movieElement).parent().mouseenter(function(e){
                    e.stopPropagation();
                    uploadButton.focus();
                });
                $(e.target.movieElement).parent().mouseleave(function(e){
                    e.stopPropagation();
                    uploadButton.blur();
                });
            });
            $(this.swfu).bind('eFileQueued', this.swfHandlerFileQueued);
            $(this.swfu).bind('eFileQueueError', this.swfHandlerFileQueueError);
            $(this.swfu).bind('eUploadProgress', this.swfHandlerUploadProgress);
            $(this.swfu).bind('eUploadSuccess', this.swfHandlerUploadSuccess);
            $(this.swfu).bind('eUploadComplete', this.swfHandlerUploadComplete);
            $(this.swfu).bind('eUploadError', this.swfHandlerUploadError);
        }.bind(this));

        ls.swfuploader.loadSwf();
    }

    this.swfHandlerFileQueued = function (e, file) {
        ls.photoset.updateProgress(file.index, file.name, 0, 0);
    }

    this.swfHandlerFileQueueError = function (e, file, errorCode, message) {
        ls.msg.error('Error: ' + errorCode, message);
        ls.photoset.cancelProgress(file.index);
    }

    this.swfHandlerUploadProgress = function (e, file, bytesLoaded, percent) {
        ls.photoset.updateProgress(file.index, file.name, bytesLoaded, percent);
    }

    this.swfHandlerUploadSuccess = function (e, file, serverData) {
        ls.photoset.addPhoto(file.index, jQuery.parseJSON(serverData));
    }

    this.swfHandlerUploadComplete = function (e, file, next) {
        if (next > 0) {
        }
    }

    this.swfHandlerUploadError = function (e, file, errorCode, message) {
        ls.msg.error('Error: ' + errorCode, message);
    }

    this._progressId = function(index) {

        return '#photoset-upload-progress-' + index;
    }

    this._itemId = function(id) {

        return '#photoset_photo_' + id;
    }

    this.updateProgress = function(index, filename, bytes, percent) {

        var id = this._progressId(index);
        var photoProgress = $(id);
        if (!photoProgress.length) {
            photoProgress = $('.js-photoset-upload-progress')
                .clone()
                .removeClass('js-photoset-upload-progress')
                .prop('id', id.substr(1))
                .appendTo('#swfu_images')
                .show();
        }
        if (filename) {
            photoProgress.find('.js-photoset-upload-filename').text(filename);
        }
        if (percent > 100) {
            percent = 100;
        }
        photoProgress
            .find('.progress-bar')
            .prop('aria-valuenow', (percent < 0) ? 100 : percent)
            .css('width', ((percent < 0) ? 100 : percent) + '%')
            .text((percent < 0) ? '' : (percent + '%'));
    }

    this.removeProgress = function (index) {

        $(this._progressId(index)).remove();
    }

    this.cancelProgress = function(index) {

        this.updateProgress(index, null, null, -1);
        $(this._progressId(index)).find('.progress-bar').addClass('progress-bar-danger');
    }

    this.addPhoto = function (index, response) {
        if (!response) {
            ls.msg.error(null, 'System error #1001');
        } else if (response.bStateError) {
            ls.msg.error(response.sMsgTitle, response.sMsg);
        } else {
            var html = $(this._itemId('ID')).get(0).outerHTML;
            if (html) {
                html = $(html.replace('ID', response.id)).show();
                html.find('img').prop('src', response.file);
                $(this._progressId(index)).replaceWith(html);
                ls.msg.notice(response.sMsgTitle, response.sMsg);
            }
        }
        $('#modal-photoset_upload').modal('hide');
    }

    this.deletePhoto = function (id) {
        ls.modal.confirm('', ls.lang.get('topic_photoset_photo_delete_confirm'), {
            onConfirm: function() {
                $(this._itemId(id)).css('opacity',.5).find('input, textarea').css('disabled', true);
                ls.progressStart();
                ls.ajaxPost(ls.routerUrl('content') + 'photo/delete', {'id': id}, function (result) {
                    ls.progressDone();
                    if (!result) {
                        ls.msg.error(null, 'System error #1001');
                    } else if (result.bStateError) {
                        ls.msg.error(result.sMsgTitle, result.sMsg);
                    } else {
                        $(this._itemId(id)).remove();
                        ls.msg.notice(result.sMsgTitle, result.sMsg);
                    }
                });
            }
        });
    }

    this.setPreview = function (id) {
        $('#topic_main_photo').val(id);

        $('.marked-as-preview').each(function (index, el) {
            $(el).removeClass('marked-as-preview');
            var tmpId = $(el).attr('id').slice($(el).attr('id').lastIndexOf('_') + 1);
            $('#photo_preview_state_' + tmpId).html('<a href="javascript:ls.photoset.setPreview(' + tmpId + ')" class="mark-as-preview link-dotted">' + ls.lang.get('topic_photoset_mark_as_preview') + '</a>');
        });
        $(this._itemId(id)).addClass('marked-as-preview');
        $('#photo_preview_state_' + id).html(ls.lang.get('topic_photoset_is_preview'));
    }

    this.setPreviewDescription = function (id) {
        var text = $(this._itemId(id)).find('text').value();
        ls.ajaxPost(ls.routerUrl('content') + 'photo/description', {'id': id, 'text': text}, function (result) {
                if (!result) {
                    ls.msg.error(null, 'System error #1001');
                } else if (result.bStateError) {
                    ls.msg.error(null, result.sMsg);
                } else {
                    ls.msg.error('Error', 'Please try again later');
                }
            }
        )
    }

    this.getMore = function (topic_id) {
        if (this.isLoading) return;
        this.isLoading = true;

        ls.ajaxGet(ls.routerUrl('content') + 'photo/getmore', {'topic_id': topic_id, 'last_id': this.idLast}, function (result) {
            this.isLoading = false;
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                if (result.photos) {
                    $.each(result.photos, function (index, photo) {
                        var image = '<li><a class="photoset-image" href="' + photo.path + '" rel="[photoset]" title="' + photo.description + '"><img src="' + photo.path_thumb + '" alt="' + photo.description + '" /></a></li>';
                        $('#topic-photo-images').append(image);
                        this.idLast = photo.id;
                        $('.photoset-image').unbind('click');
                        $('.photoset-image').prettyPhoto({
                            social_tools: '',
                            show_title: false,
                            slideshow: false,
                            deeplinking: false
                        });
                    }.bind(this));
                }
                if (!result.bHaveNext || !result.photos) {
                    $('#topic-photo-more').remove();
                }
            } else {
                ls.msg.error('Error', 'Please try again later');
            }
        }.bind(this));
    }

    this.upload = function () {
        //ls.photoset.addPhotoEmpty();

        var input = $('#photoset-upload-file');
        var form = $('<form method="post" enctype="multipart/form-data">' +
            '<input type="hidden" name="is_iframe" value="true" />' +
            '<input type="hidden" name="ALTO_AJAX" value="1" />' +
            '<input type="hidden" name="topic_id" value="' + input.data('topic-id') + '" />' +
            '</form>').hide().appendTo('body');

        input.clone(true).insertAfter(input);
        input.appendTo(form);

        ls.ajaxSubmit(ls.routerUrl('content') + 'photo/upload/', form, function (result) {
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(result.sMsgTitle, result.sMsg);
            } else {
                ls.photoset.addPhoto(result);
            }
            form.remove();
        });
    }

    this.showForm = function() {

    }

    return this;
}).call(ls.photoset || {}, jQuery);