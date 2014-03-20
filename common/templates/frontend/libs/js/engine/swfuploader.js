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

/**
 * Flash загрузчик
 */
ls.swfuploader = (function ($) {

    this.swfu = null;
    this.swfOptions = {};

    this.initOptions = function (options) {

        this.swfOptions = {
            // Backend Settings
            upload_url: ls.routerUrl('content') + 'photo/upload',
            post_params: {'SSID': SESSION_ID, 'security_key': ls.cfg.security_key},

            prevent_swf_caching : false,

            // File Upload Settings
            file_types: '*.jpg;*.jpe;*.jpeg;*.png;*.gif;*.JPG;*.JPE;*.JPEG;*.PNG;*.GIF',
            file_types_description: 'Images',
            file_upload_limit: '0',

            // Event Handler Settings
            swfupload_loaded_handler: this.handlerReady,
            file_dialog_start_handler: this.handlerFileDialogStart,
            file_queued_handler: this.handlerFileQueued,
            file_queue_error_handler: this.handlerFileQueueError,
            file_dialog_complete_handler: this.handlerFileDialogComplete,
            upload_progress_handler: this.handlerUploadProgress,
            upload_error_handler: this.handlerUploadError,
            upload_success_handler: this.handlerUploadSuccess,
            upload_complete_handler: this.handlerUploadComplete,

            // Button Settings
            button_placeholder_id: 'start-upload',
            button_width: 122,
            button_height: 30,
            //button_text: '<span class="swfupload">' + ls.lang.get('topic_photoset_upload_choose') + '</span>',
            //button_text_style: '.swfupload { color: #777777; font-size: 14px; }',
            button_text_left_padding: 0,
            button_text_top_padding: 0,
            button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
            button_cursor: SWFUpload.CURSOR.HAND,

            // Flash Settings
            flash_url: ls.cfg.assets['swfupload/swfupload.swf'],

            custom_settings: {
            },

            // Debug Settings
            debug: false
        };
        if (options) {
            this.initOptions = $.extend(this.initOptions, options);
        }
        ls.hook.run('ls_swfupload_init_options_after', arguments, this.swfOptions);

    };

    this.loadSwf = function (options) {
        var f = {};

        f.onSwfobject = function(){
            if(window.swfobject && swfobject.swfupload){
                f.onSwfobjectSwfupload();
            }else{
                ls.debug('window.swfobject && swfobject.swfupload is undefined, loading "swfobject/plugin/swfupload.js"...');
                ls.loadAssetScript('swfobject/plugin/swfupload.js', function(){
                    f.onSwfobjectSwfupload();
                });
            }
        }.bind(this);

        f.onSwfobjectSwfupload = function(){
            if(window.SWFUpload){
                f.onSwfupload();
            }else{
                ls.debug('window.SWFUpload is undefined, loading "swfupload/swfupload.js"');
                ls.loadAssetScript('swfupload/swfupload.js', function(){
                    f.onSwfupload();
                });
            }
        }.bind(this);

        f.onSwfupload = function () {
            this.initOptions();
            $(this).trigger('load');
        }.bind(this);


        (function () {
            if (window.swfobject) {
                f.onSwfobjectSwfupload();
            } else {
                ls.debug('window.swfobject is undefined, need to load swfobject/swfobject.js');
            }
        }.bind(this))();
    };


    this.init = function (opt) {
        if (opt) {
            $.extend(true, this.swfOptions, opt);
        }
        var placeholder = $('#' + this.swfOptions.button_placeholder_id);
        var label = placeholder.parent('label');
        if (placeholder.length) {
            var color = placeholder.css('color'), re = /^rgb(a)?\((\d+)\s*,\s*(\d+)\s*,\s*(\d+)/ig;
            var r=re.exec(color);
            if (r) {
                var n = parseInt(r[2]) * 65536 + parseInt(r[3]) * 256 + parseInt(r[4]);
                color = '#' + n.toString(16);
            }
            var style = '.swfupload {color:' + color.toUpperCase() + '; '
                + 'font-size:' + placeholder.css('font-size') + '; '
                + 'font-family:' + placeholder.css('font-family') + '; '
                + 'font-style:' + placeholder.css('font-style') + '; '
                + 'font-weight:' + placeholder.css('font-weight') + '; '
                + 'text-align:' + placeholder.css('text-align') + '; '
                + '}';
            //this.swfOptions.button_text_style = style;
            if (label.length) {
                this.swfOptions.button_width = parseInt(label.outerWidth());
                this.swfOptions.button_text_top_padding = parseInt(label.css('padding-top'));
            }
        }
        this.swfu = new SWFUpload(this.swfOptions);
        if (label.length) {
            $(label).css('padding', '0').click(function(){ return false; });
        }
        return this.swfu;
    };

    this.handlerReady = function () {
        $(this).trigger('eReady');
    };

    this.handlerFileDialogStart = function () {
        $(this).trigger('eFileDialogStart');
    };

    this.handlerFileQueued = function (file) {
        $(this).trigger('eFileQueued', [file]);
    };

    this.handlerFileQueueError = function (file, errorCode, message) {
        $(this).trigger('eFileQueueError', [file, errorCode, message]);
    };

    this.handlerFileDialogComplete = function (numFilesSelected, numFilesQueued) {
        $(this).trigger('eFileDialogComplete', [numFilesSelected, numFilesQueued]);
        if (numFilesQueued > 0) {
            this.startUpload();
        }
    };

    this.handlerUploadProgress = function (file, bytesLoaded) {
        var percent = Math.ceil((bytesLoaded / file.size) * 100);
        $(this).trigger('eUploadProgress', [file, bytesLoaded, percent]);
    };

    this.handlerUploadError = function (file, errorCode, message) {
        $(this).trigger('eUploadError', [file, errorCode, message]);
    };

    this.handlerUploadSuccess = function (file, serverData) {
        $(this).trigger('eUploadSuccess', [file, serverData]);
    };

    this.handlerUploadComplete = function (file) {
        var next = this.getStats().files_queued;
        if (next > 0) {
            this.startUpload();
        }
        $(this).trigger('eUploadComplete', [file, next]);
    };

    return this;
}).call(ls.swfuploader || {}, jQuery);
