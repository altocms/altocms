/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */
;var ls = ls || {};

ls.modal = (function ($) {
    "use strict";
    var $that = this;

    this.defaults = {
        title: '<span class="glyphicon glyphicon-exclamation-sign"></span>',
        message: ''
    };

    var parseArguments = function(args) {
        var options, callback;

        if (args.length == 0) {
            return $that.defaults;
        } else if (args.length == 1) {
            options = args[0];
            callback = options.callback ? options.callback : null;
        } else {
            options = args[0];
            callback = args[1];
        }

        if ($.type(options) === 'string') {
            options = { message: options }
        }
        options = $.extend($that.defaults, options);
        if (!options.message) {
            if (options.text) {
                options.message = $('<div>' + options.text + '</div>').text();
            } else if (options.html) {
                options.message = options.html;
            }
        }
        if ($.type(callback) === 'object' || $.type(callback) === 'undefined') {
            if (callback.onConfirm || options.onConfirm) {
                options.onConfirm = callback.onConfirm ? callback.onConfirm : options.onConfirm ;
            } else {
                options.onConfirm = function() {};
            }
            if (callback.onCancel || options.onCancel) {
                options.onCancel = callback.onCancel ? callback.onCancel : options.onCancel ;
            } else {
                options.onCancel = function() {};
            }
            options.callback = null;
        } else if ($.type(callback) === 'function') {
            options.callback = callback;
            options.onConfirm = options.onCancel = null;
        }

        return options;
    };

    /**
     * Alternative alert modal window
     * usage:
     *      ls.confirm('message', function() { });
     *      ls.confirm({title: 'title', message: 'message'}, function() { });
     *      ls.confirm({title: 'title', text: 'message'}, {onConfirm: function(){}});
     *      ls.confirm({title: 'title', html: 'message', onConfirm: function(){} });
     */
    this.alert = function () {
        var onConfirm = function () {},
            options = parseArguments(arguments);

        if (options.callback) {
            onConfirm = options.callback;
        } else if (options.onConfirm) {
            onConfirm = options.onConfirm;
        }
        bootbox.dialog({
            message: options.message,
            title: options.title,
            buttons: {
                ok: {
                    label: 'OK',
                    className: 'btn-primary',
                    callback: onConfirm
                }
            }
        });
    };

    /**
     * Alternative prompt modal window
     * usage:
     *      ls.prompt('title', callback)
     *      ls.prompt({
     *          title: 'title'
     *          value: '...'
     *      }, callback)
     */
    this.prompt = function() {
        var callback = function() {},
            options = parseArguments(arguments);

        if (options.onConfirm) {
            callback = function(value) {
                if (value !== null) {
                    options.onConfirm(value);
                } else {
                    options.onCancel();
                }
            }
        } else if (options.callback) {
            callback = options.callback;
        }

        var win = bootbox.prompt(options.title, callback);
        if (options.message) {
            $(win).find('form').before(options.message);
        }
        if (options.value) {
            $(win).find('input[type=text]').val(options.value);
        }
    };

    /**
     * Alternative confirm modal window
     * usage:
     *      ls.confirm('message', function(confirmed) { } );
     *      ls.confirm({title: 'title', message: 'message'}, function(confirmed) { });
     *      ls.confirm({title: 'title', message: 'message'}, {onConfirm: function(){}, onCancel: function() {} });
     *      ls.confirm({title: 'title', message: 'message', onConfirm: function(){}, onCancel: function() {} });
     *
     * @param {Object} options
     * @param {Function|Object|undefined} callback
     */
    this.confirm = function(options, callback) {
        var onConfirm = function() {},
            onCancel = function() {};

        options = parseArguments(arguments);
        if (options.callback) {
            onConfirm = function() { options.callback(true) };
            onCancel = function() { options.callback(false) };
        } else {
            onConfirm = options.onConfirm;
            onCancel = options.onCancel;
        }
        bootbox.dialog({
            message: options.message,
            title: options.title,
            buttons: {
                cancel: {
                    label: ls.lang.get('text_cancel'),
                    className: 'btn-default',
                    callback: onCancel
                },
                confirm: {
                    label: ls.lang.get('text_confirm'),
                    className: 'btn-primary',
                    callback: onConfirm
                }
            }
        });
    };

    /**
     * Dialog modal window
     *
     * @param options
     */
    this.dialog = function(options) {
        if ($.type(options) === 'string') {
            this.alert(options);
        } else {
            options = parseArguments(arguments);
            bootbox.dialog(options);
        }
    };

    this.show = function(selector) {
        if ($(selector).length) {
            $(selector).modal('show');
        } else {

        }
    };

    this.hide = function(selector) {
        if (!selector) {
            bootbox.hideAll();
        } else if ($(selector).length) {
            $(selector).modal('hide');
        }
    };

    return this;
}).call(ls.modal || {},jQuery);