/**
 * Лента
 */

;var ls = ls || {};

ls.userfeed = (function ($) {
    var $that = this;

    this.isBusy = false;

    this.options = {
        selectors: {
            userList:   '.js-userfeed-userlist',
            friendList: '.js-userfeed-friendlist',
            blogList:   '.js-userfeed-bloglist',
            inputField: '.js-userfeed-input',
            item:       '.js-userfeed-item',
            itemEmpty:  '.js-userfeed-item-empty',
            getMore:    '.js-userfeed-getmore',
            topics:     '.js-userfeed-topics'
        },
        elements: {
            userItem: function (element) {
                return ls.stream.options.elements.userItem(element);
            }
        }
    };

    /**
     * Init
     */
    this.init = function () {

        $(this.options.selectors.inputField).keydown(function (event) {
            event.which == 13 && ls.userfeed.appendUser();
        });

        $(this.options.selectors.userList).on('change', 'input[type=checkbox]', function () {
            $that.changeItemCheckbox(this, 'user');
        });
        $(this.options.selectors.friendList).on('change', 'input[type=checkbox]', function () {
            $that.changeItemCheckbox(this, 'user');
        });
        $(this.options.selectors.blogList).on('change', 'input[type=checkbox]', function () {
            $that.changeItemCheckbox(this, 'blog');
        });

        $(this.options.selectors.getMore).on('click', function () {
            $that.getMore(this);
            return false;
        });
    };

    /**
     * Change user's checkbox
     *
     * @param checkbox
     * @param type
     */
    this.changeItemCheckbox = function(checkbox, type) {
        var item,
            itemId,
            dataKey = type + '-id';
        checkbox = $(checkbox);
        itemId = checkbox.data(dataKey);
        if (!itemId) {
            item = checkbox.parents($that.options.selectors.item);
            itemId = item.data(dataKey);
        }
        if (!itemId) {
            item.find('[data-' + dataKey + ']').first().each(function(){
                itemId = $(this).data(dataKey);
            });
        }

        if (itemId) {
            if (checkbox.prop('checked')) {
                $that.subscribe(type, itemId);
            } else {
                $that.unsubscribe(type, itemId);
            }
        }
    };

    /**
     * Subscribe to user/blog
     *
     * @param {string}  type
     * @param {int}     id
     */
    this.subscribe = function (type, id) {
        var url = ls.routerUrl('feed') + 'subscribe/',
            params = {'type': type, 'id': id};

        ls.progressStart();
        ls.ajax(url, params, function (response) {
            ls.progressDone();
            if (!response) {
                ls.msg.error(null, 'System error #1001');
            } else if (response.bStateError) {
                ls.msg.error(response.sMsgTitle ? response.sMsgTitle : 'Error', response.sMsg);
            } else if (response.sMsg) {
                ls.msg.notice(response.sMsgTitle, response.sMsg);
            }
            ls.hook.run('ls_userfeed_subscribe_after', [type, id, response]);
        });
    };

    /**
     * Unsubscribe
     *
     * @param type
     * @param id
     */
    this.unsubscribe = function (type, id) {
        var url = ls.routerUrl('feed') + 'unsubscribe/',
            params = {'type': type, 'id': id};

        ls.progressStart();
        ls.ajax(url, params, function (response) {
            ls.progressDone();
            if (!response) {
                ls.msg.error(null, 'System error #1001');
            } else if (response.bStateError) {
                ls.msg.error(response.sMsgTitle ? response.sMsgTitle : 'Error', response.sMsg);
            } else if (response.sMsg) {
                ls.msg.notice(response.sMsgTitle ? response.sMsgTitle : '', response.sMsg);
            }
            ls.hook.run('ls_userfeed_unsubscribe_after', [type, id, response]);
        });
    };

    this.appendUser = function () {
        var $that = this,
            sLogin = $(this.options.selectors.inputField).val();

        if (!sLogin) return;

        ls.progressStart();
        ls.ajax(ls.routerUrl('feed') + 'subscribeByLogin/', {'login': sLogin}, function (response) {
            ls.progressDone();
            if (!response) {
                ls.msg.error(null, 'System error #1001');
            } else if (response.bStateError) {
                ls.msg.error(response.sMsgTitle, response.sMsg);
            } else {
                var item = $($that.options.selectors.itemEmpty)
                    .clone()
                    .appendTo($that.options.selectors.userList),
                    checkbox = item.find('input[type=checkbox]').prop('checked', true);
                $($that.options.selectors.inputField).autocomplete('close').val('');
                item.removeClass($that.options.selectors.itemEmpty.substr(1))
                    .addClass($that.options.selectors.item.substr(1))
                    .find('a').each(function(){
                    var a = $(this),
                        img = a.find('img');
                    a.prop('href', response.user_profile_url)
                        .prop('title', response.user_name);
                    if (img.length) {
                        img.prop('src', response.user_avatar)
                    } else {
                        a.text(response.user_name);
                    }
                });
                item.slideDown().data('user-id', response.user_id);
                checkbox.on('change', function(){
                    $that.changeItemCheckbox(this);
                });
            }
        });
    };

    this.getMore = function (button) {
        var lastId,
            url,
            params,
            feedType;
        button = $(button);
        lastId = button.data('last-id');
        feedType = button.data('type');
        if (this.isBusy || !lastId) {
            return;
        }
        this.isBusy = true;

        url = ls.routerUrl('feed') + 'get_more/';
        params = {'last_id': lastId, type: feedType};

        //button.addClass('loading');

        ls.progressStart();
        ls.ajax(url, params, function (response) {
            ls.progressDone();
            if (!response) {
                ls.msg.error(null, 'System error #1001');
            } else if (response.bStateError) {
                ls.msg.error(null, response.sMsg);
            } else {
                if (response.sMsg) {
                    ls.msg.notice(response.sMsgTitle ? response.sMsgTitle : '', response.sMsg);
                }
                if (response.topics_count) {
                    $($that.options.selectors.topics).append(response.result);
                    button.data('last-id', response.iUserfeedLastId);
                }
                if (!response.topics_count) {
                    button.hide();
                }
            }
            button.removeClass('loading');
            ls.hook.run('ls_userfeed_get_more_after', [lastId, response]);
            this.isBusy = false;
        }.bind(this));
    };

    $(function(){
        ls.userfeed.init();
    });

    return this;
}).call(ls.userfeed || {}, jQuery);
