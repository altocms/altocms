/**
 * Опросы
 */

;var ls = ls || {};


ls.poll = (function ($) {
    var $that = this;
    /**
     * Дефолтные опции
     */
    var defaults = {
        // Роутер голосования
        voteUrl: ls.routerUrl('ajax') + 'vote/poll/',

        // Максимальное кол-во вариантов ответов
        iMaxItems: 20,

        selectors: {
            // Селекторы добавления опроса
            pollEdit:               '.js-poll-edit',
            addButton:              '.js-poll-add-button',

            // Селекторы опроса
            poll:                   '.js-poll',
            pollList:               '.js-poll-list',
            pollItem:               '.js-poll-item',

            // Селекторы результата опроса
            result:                 '.js-poll-result',
            resultItem:             '.js-poll-result-item',
            resultSort:             '.js-poll-result-sort'
        }
    };

    /**
     * Инициализация
     *
     * @param  {Object} options Опции
     */
    this.init = function (options) {
        this.options = $.extend({}, defaults, options);

        // Добавление
        $(this.options.selectors.pollEdit).each(function () {
            var pollSet = $(this);

            // Добавление варианта
            pollSet.find($that.options.selectors.addButton).on('click', function () {
                $that.addItem(pollSet);
                return false;
            }.bind($that));

            // Добавление варианта по нажатию Ctrl + Enter
            pollSet.on('keyup', 'input[type=text],textarea', function (e) {
                var key = e.keyCode || e.which;

                if (e.ctrlKey && key == 13) {
                    $that.addItem(pollSet);
                }
            });

        });
    };

    /**
     * Добавляет вариант ответа
     *
     */
    this.addItem = function (poll) {
        var items = poll.find(this.options.selectors.pollItem);

        if (items.length > 1) {
            if (items.length >= this.options.iMaxItems) {
                ls.msg.error(null, ls.lang.get('topic_question_create_answers_error_max'));
                return false;
            }
            var newItem = $(items.last().get(0).outerHTML);
            newItem.appendTo(items.parent()).find('input[type=text],textarea').val('');
            items.find('[value=""]').first().focus();
        }
        return false;
    };

    /**
     * Удаляет вариант ответа
     *
     * @param  {Number} button Кнопка удаления
     */
    this.removeItem = function (button) {
        $(button).closest(this.options.selectors.pollItem).remove();
        return false;
    };

    /**
     * Голосование в опросе
     *
     * @param  {Object} button
     * @param  {Number} value ID выбранного пункта
     */
    this.vote = function (button, value) {
        var poll = $(button).closest($that.options.selectors.poll),
            selected = poll.find(this.options.selectors.pollItem + ' [type=radio]:checked'),
            pollId = 0,
            params = { };

        if (!poll.length) {
            return false;
        }
        pollId = parseInt(poll.data('poll-id'));

        if (arguments.length < 2) {
            if (selected.length) {
                value = selected.val();
            } else {
                value = null;
            }
        }
        if (!pollId || value === null) {
            return false;
        }
        params = {
            idTopic: pollId,
            idAnswer: value
        };

        ls.progressStart();
        ls.ajax(this.options.voteUrl, params, function (result) {
            ls.progressDone();
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                var html = $(result.sText);
                poll.html($(result.sText).html());

                ls.msg.notice(null, result.sMsg);

                ls.hook.run('ls_pool_vote_after', [pollId, value, result], poll);
            }
        });
    };

    /**
     * Сортировка результатов
     *
     * @param  {Object} button
     */
    this.toggleSort = function (button) {
        var poll = $(button).closest($that.options.selectors.poll),
            pollResult = poll.find(this.options.selectors.result),
            items = pollResult.find(this.options.selectors.resultItem),
            sortType = 'poll-item-pos',
            sortDirect = -1;

        button = $(button);
        if ($(button).hasClass('active')) {
            sortType = 'poll-item-pos';
            sortDirect = -1;
        } else {
            sortType = 'poll-item-count';
            sortDirect = 1;
        }
        items.sort(function (item1, item2) {
            var val1 = $(item1).data(sortType),
                val2 = $(item2).data(sortType);

            if (val1 > val2) {
                return -1 * sortDirect;
            } else if (val1 < val2) {
                return sortDirect;
            } else {
                return 0;
            }
        });

        button.toggleClass('active');
        pollResult.empty().append(items);
    };

    return this;
}).call(ls.poll || {}, jQuery);

$(function(){
    ls.poll.init();
});