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
    var $that = this;
    this.idLast = 0;
    this.isLoading = false;
    this.swfu = null;

    /**
     * Инициализация
     */
    this.init = function () {
        var self = this;

        $('#photoset-upload-file').on('change', function (e) {
            self.upload();
        });
    };

    /**
     *
     * @param opt
     */
    this.initSwfUpload = function (opt) {
        opt = opt || {};
        opt.button_placeholder_id = 'photoset-upload-place';
        if ($.cookie('ls_photoset_target_tmp')) {
            opt.post_params.ls_photoset_target_tmp = $.cookie('ls_photoset_target_tmp');
        }

        var uploadButton = $('#photoset-upload-button');
        var fakeButton = uploadButton.clone().css({position: 'absolute', top: -1000, left:-1000}).appendTo('body').show();
        opt.button_width = fakeButton.outerWidth();
        opt.button_height = fakeButton.outerHeight();
        fakeButton.remove();

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
    };

    /**
     *
     * @param e
     * @param file
     */
    this.swfHandlerFileQueued = function (e, file) {
        ls.photoset.updateProgress(file.index, file.name, 0, 0);
    };

    /**
     *
     * @param e
     * @param file
     * @param errorCode
     * @param message
     */
    this.swfHandlerFileQueueError = function (e, file, errorCode, message) {
        ls.msg.error('Error: ' + errorCode, message);
        ls.photoset.cancelProgress(file.index);
    };

    /**
     *
     * @param e
     * @param file
     * @param bytesLoaded
     * @param percent
     */
    this.swfHandlerUploadProgress = function (e, file, bytesLoaded, percent) {
        ls.photoset.updateProgress(file.index, file.name, bytesLoaded, percent);
    };

    /**
     *
     * @param e
     * @param file
     * @param serverData
     */
    this.swfHandlerUploadSuccess = function (e, file, serverData) {
        ls.photoset.addPhoto(file.index, jQuery.parseJSON(serverData));
    };

    /**
     *
     * @param e
     * @param file
     * @param next
     */
    this.swfHandlerUploadComplete = function (e, file, next) {
        if (next > 0) {
        }
    };

    /**
     *
     * @param e
     * @param file
     * @param errorCode
     * @param message
     */
    this.swfHandlerUploadError = function (e, file, errorCode, message) {
        ls.msg.error('Error: ' + errorCode, message);
    };

    /**
     *
     * @param index
     * @returns {string}
     * @private
     */
    this._progressId = function(index) {
        return '#photoset-upload-progress-' + index;
    };

    /**
     *
     * @param id
     * @returns {string}
     * @private
     */
    this._itemId = function(id) {
        return '#photoset_photo_' + id;
    };

    /**
     *
     * @param index
     * @param filename
     * @param bytes
     * @param percent
     */
    this.updateProgress = function(index, filename, bytes, percent) {

        var id = $that._progressId(index);
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
    };

    /**
     *
     * @param index
     */
    this.removeProgress = function (index) {

        $($that._progressId(index)).remove();
    };

    /**
     *
     * @param index
     */
    this.cancelProgress = function(index) {

        this.updateProgress(index, null, null, -1);
        $($that._progressId(index)).find('.progress-bar').addClass('progress-bar-danger');
    };

    /**
     *
     * @param index
     * @param response
     */
    this.addPhoto = function (index, response) {
        if (!response) {
            ls.msg.error(null, 'System error #1001');
        } else if (response.bStateError) {
            ls.msg.error(response.sMsgTitle, response.sMsg);
        } else {
            var html = $(ls.photoset._itemId('ID')).get(0).outerHTML;
            if (html) {
                html = $(html.replace(/ID/g, response.id)).show();
                html.find('img').prop('src', response.file);
                $($that._progressId(index)).replaceWith(html);
                ls.msg.notice(response.sMsgTitle, response.sMsg);
            }
        }
        $('#modal-photoset_upload').modal('hide');
    };

    /**
     * @param id
     */
    this.deletePhoto = function (id) {
        var photo = $(this._itemId(id)),
            img = photo.length ? photo.find('img') : null,
            html = ls.lang.get('topic_photoset_photo_delete_confirm');

        if (!photo.length) {
            return false;
        }
        if (img.length) {
            html = '<img src="' + img.prop('src') + '" align="left" />' + html + '<div class="clearfix"></div> ';
        }

        ls.modal.confirm(html, {
            onConfirm: function() {
                $(ls.photoset._itemId(id)).css('opacity',.5).find('input, textarea').css('disabled', true);
                ls.progressStart();
                ls.ajaxPost(ls.routerUrl('content') + 'photo/delete', {'id': id}, function (response) {
                    ls.progressDone();
                    if (!response) {
                        ls.msg.error(null, 'System error #1001');
                    } else if (response.bStateError) {
                        ls.msg.error(response.sMsgTitle, response.sMsg);
                    } else {
                        $(ls.photoset._itemId(id)).remove();
                        ls.msg.notice(response.sMsgTitle, response.sMsg);
                    }
                });
            }
        });
    };

    /**
     * @param id
     */
    this.setPreview = function (id) {

        $('#topic_main_photo').val(id);

        $('.marked-as-preview').each(function (index, el) {
            $(el).removeClass('marked-as-preview');
            var tmpId = $(el).attr('id').slice($(el).attr('id').lastIndexOf('_') + 1);
            $('#photo_preview_state_' + tmpId).html('<a href="javascript:ls.photoset.setPreview(' + tmpId + ')" class="mark-as-preview link-dotted">' + ls.lang.get('topic_photoset_mark_as_preview') + '</a>');
        });
        $(ls.photoset._itemId(id)).addClass('marked-as-preview');
        $('#photo_preview_state_' + id).html(ls.lang.get('topic_photoset_is_preview'));
    };

    /**
     *
     * @param id
     */
    this.setPreviewDescription = function (id) {

        var text = $(ls.photoset._itemId(id)).find('textarea').val();
        ls.progressStart();
        ls.ajaxPost(ls.routerUrl('content') + 'photo/description', {'id': id, 'text': text}, function (response) {
                ls.progressDone();
                if (!response) {
                    ls.msg.error(null, 'System error #1001');
                } else if (response.bStateError) {
                    ls.msg.error('Error', response.sMsg ? response.sMsg : 'Please try again later');
                } else if (response.sMsg) {
                    ls.msg.notice('', response.sMsg);
                }
            }
        )
    };

    /**
     *
     * @param topic_id
     */
    this.getMore = function (topic_id) {

        if (this.isLoading) return;
        this.isLoading = true;

        ls.ajaxGet(ls.routerUrl('content') + 'photo/getmore', {'topic_id': topic_id, 'last_id': this.idLast}, function (response) {
            this.isLoading = false;
            if (!response) {
                ls.msg.error(null, 'System error #1001');
            } else if (response.bStateError) {
                if (response.photos) {
                    $.each(response.photos, function (index, photo) {
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
                if (!response.bHaveNext || !response.photos) {
                    $('#topic-photo-more').remove();
                }
            } else {
                ls.msg.error('Error', 'Please try again later');
            }
        }.bind(this));
    };

    /**
     *
     */
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

        ls.ajaxSubmit(ls.routerUrl('content') + 'photo/upload/', form, function (response) {
            if (!response) {
                ls.msg.error(null, 'System error #1001');
            } else if (response.bStateError) {
                ls.msg.error(response.sMsgTitle, response.sMsg);
            } else {
                ls.photoset.addPhoto(response);
            }
            form.remove();
        });
    };

    /**
     *
     */
    this.showForm = function() {

    };

    return this;
}).call(ls.photoset || {}, jQuery);