/**
 * Опросы
 */

;var ls = ls || {};


ls.poll = (function ($) {
    /**
     * Дефолтные опции
     */
    var defaults = {
        // Роутер голосования
        sRouterVoteUrl: ls.routerUrl('ajax') + 'vote/question/',

        // Максимальное кол-во вариантов ответов
        iMaxItems: 20,

        // Селекторы добавления опроса
        sAddSelector: '.js-poll-add',
        sAddListSelector: '.js-poll-add-list',
        sAddItemSelector: '.js-poll-add-item',
        sAddItemRemoveSelector: '.js-poll-add-item-remove',
        sAddItemInputSelector: '.js-poll-add-item-input',
        sAddButtonSelector: '.js-poll-add-button',

        // Селекторы опроса
        sPollSelector: '.js-poll',
        sPollListSelector: '.js-poll-list',
        sPollItemSelector: '.js-poll-item',
        sPollItemOptionSelector: '.js-poll-item-option',
        sPollButtonVoteSelector: '.js-poll-button-vote',
        sPollButtonAbstainSelector: '.js-poll-button-abstain',

        // Селекторы результата опроса
        sPollResultSelector: '.js-poll-result',
        sPollResultItemSelector: '.js-poll-result-item',
        sPollResultButtonSortSelector: '.js-poll-result-button-sort',

    };

    /**
     * Инициализация
     *
     * @param  {Object} options Опции
     */
    this.init = function (options) {
        var self = this;

        this.options = $.extend({}, defaults, options);

        // Добавление
        $(this.options.sAddSelector).each(function () {
            var pollSet = $(this);

            // Добавление варианта
            pollSet.find(self.options.sAddButtonSelector).on('click', function () {
                self.addItem(pollSet);
                return false;
            }.bind(self));

            // Добавление варианта по нажатию Ctrl + Enter
            pollSet.on('keyup', self.options.sAddItemInputSelector, function (e) {
                var key = e.keyCode || e.which;

                if (e.ctrlKey && key == 13) {
                    self.addItem(pollSet);
                }
            });

            // Удаление
            pollSet.on('click', self.options.sAddItemRemoveSelector, function () {
                self.removeItem(this);
            });
        });

        // Голосование
        $(this.options.sPollSelector).each(function () {
            var pollSet = $(this),
                iPollId = pollSet.data('poll-id');

            // Голосование за вариант
            pollSet.find(self.options.sPollButtonVoteSelector).on('click', function () {
                var iCheckedItemId = pollSet.find(self.options.sPollItemOptionSelector + ':checked').val();

                if (iCheckedItemId) {
                    self.vote(iPollId, iCheckedItemId);
                } else {
                    return false;
                }
            });

            // Воздержаться
            pollSet.find(self.options.sPollButtonAbstainSelector).on('click', function () {
                self.vote(iPollId, -1);
            });

            // Воздержаться
            pollSet.on('click', self.options.sPollResultButtonSortSelector, function () {
                self.toggleSort(pollSet);
            });
        });
    };

    /**
     * Добавляет вариант ответа
     *
     */
    this.addItem = function (poll) {
        var items = poll.find(this.options.sAddItemSelector);

        if (items.length > 1) {
            if (items.length >= this.options.iMaxItems) {
                ls.msg.error(null, ls.lang.get('topic_question_create_answers_error_max'));
                return false;
            }
            var newItem = $(items.last().get(0).outerHTML);
            newItem.appendTo(items.parent());
            items.find('[value=""]').first().focus();
        }
        return false;
    };

    /**
     * Удаляет вариант ответа
     *
     * @param  {Number} oRemoveButton Кнопка удаления
     */
    this.removeItem = function (oRemoveButton) {
        $(oRemoveButton).closest(this.options.sAddItemSelector).remove();
    };

    /**
     * Голосование в опросе
     *
     * @param  {Number} iPollId ID опроса
     * @param  {Number} iItemId ID выбранного пункта
     */
    this.vote = function (iPollId, iItemId) {
        var oParams = {
            idTopic: iPollId,
            idAnswer: iItemId
        };

        ls.ajax(this.options.sRouterVoteUrl, oParams, function (result) {
            if (!result) {
                ls.msg.error(null, 'System error #1001');
            } else if (result.bStateError) {
                ls.msg.error(null, result.sMsg);
            } else {
                var oPoll = $('[data-poll-id=' + iPollId + ']');
                oPoll.html(result.sText);

                ls.msg.notice(null, result.sMsg);

                ls.hook.run('ls_pool_vote_after', [iPollId, iItemId, result], oPoll);
            }
        });
    };

    /**
     * Сортировка результатов
     *
     * @param  {Object} oPoll Блок опроса
     */
    this.toggleSort = function (oPoll) {
        var oButton = oPoll.find(this.options.sPollResultButtonSortSelector),
            oPollResult = oPoll.find(this.options.sPollResultSelector),
            aItems = oPollResult.find(this.options.sPollResultItemSelector),
            sSortType = oButton.hasClass('active') ? 'poll-item-pos' : 'poll-item-count';

        aItems.sort(function (a, b) {
            a = $(a).data(sSortType);
            b = $(b).data(sSortType);

            if (a > b) {
                return -1;
            } else if (a < b) {
                return 1;
            } else {
                return 0;
            }
        });

        oButton.toggleClass('active');
        oPollResult.empty().append(aItems);
    };

    return this;
}).call(ls.poll || {}, jQuery);

$(function(){
    ls.poll.init();
});