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

;var ls = ls || {};

ls.userfield = ( function ($) {
    "use strict";
    var $that = this;

    /**
     *
     */
    this.init = function() {
        this.fieldsConainer = $('#user-field-contact-container');
        this.iCountMax = 2;
    };

    /**
     *
     * @returns {boolean}
     */
    this.addFormField = function () {
        var item = this.fieldsConainer.find('.js-user-field-item:first').clone(),
            value = '';
        /**
         * Находим доступный тип контакта
         */
        item.find('select').find('option').each(function (k, v) {
            if (this.getCountFormField($(v).val()) < this.iCountMax) {
                value = $(v).val();
                return false;
            }
        }.bind(this));

        if (value) {
            item.find('select').val(value);
            item.find('[type=text]').val('');
            $(this.fieldsConainer).append(item.show());
        } else {
            ls.msg.error('', ls.lang.get('settings_profile_field_error_max', {count: this.iCountMax}));
        }
        return false;
    };

    /**
     *
     * @param button
     */
    this.changeFormField = function (button) {
        var iCount = $that.getCountFormField($(button).val());
        if (iCount > $that.iCountMax) {
            ls.msg.error('', ls.lang.get('settings_profile_field_error_max', {count: $that.iCountMax}));
        }
    };

    /**
     *
     * @param button
     * @returns {boolean}
     */
    this.removeFormField = function (button) {
        $(button).parents('.js-user-field-item').first().detach();
        return false;
    };

    /**
     *
     * @param value
     * @returns {number}
     */
    this.getCountFormField = function (value) {
        var iCount = 0;
        $(this.fieldsConainer).find('.js-user-field-item:visible select').each(function (k, v) {
            if (value == $(v).val()) {
                iCount++;
            }
        });
        return iCount;
    };

    $(function() {
        ls.userfield.init();
    });

    return this;
}).call(ls.userfield || {}, jQuery);