var ls = ls || {};

/**
 * Голосование
 */
ls.vote = (function ($) {
    "use strict";

    /**
     * Дефолтные опции
     */
    var defaults = {
        // Селекторы
        selectors: {
            vote: '.js-vote',
            up: '.js-vote-up',
            down: '.js-vote-down',
            abstain: '.js-vote-abstain',
            count: '.js-vote-count',
            rating: '.js-vote-rating'
        },

        // Классы
        classes: {
            voted: 'voted',
            plus: 'voted-up',
            minus: 'voted-down',
            positive: 'vote-count-positive',
            negative: 'vote-count-negative',
            voted_zero: 'voted-zero',
            zero: 'vote-count-zero',
            not_voted: 'not-voted'
        },

        type: {
            comment: {
                url: ls.routerUrl('ajax') + 'vote/comment/',
                targetName: 'idComment'
            },
            topic: {
                url: ls.routerUrl('ajax') + 'vote/topic/',
                targetName: 'idTopic'
            },
            blog: {
                url: ls.routerUrl('ajax') + 'vote/blog/',
                targetName: 'idBlog'
            },
            user: {
                url: ls.routerUrl('ajax') + 'vote/user/',
                targetName: 'idUser'
            }
        }
    };

    /**
     * Инициализация
     *
     * @param  {Object} options Опции
     */
    this.init = function (options) {
        var self = this;

        this.options = $.extend({}, defaults, options);

        $(this.options.selectors.vote).each(function () {
            var voteSection = $(this);

            var voteOptions = {
                vote: voteSection,
                up: voteSection.find(self.options.selectors.up),
                down: voteSection.find(self.options.selectors.down),
                abstain: voteSection.find(self.options.selectors.abstain),
                count: voteSection.find(self.options.selectors.count),
                rating: voteSection.find(self.options.selectors.rating),
                id: voteSection.data('target-id'),
                type: voteSection.data('target-type')
            };

            // Плюс
            voteOptions.up.on('click', function (e) {
                self.vote(voteOptions.type, voteOptions.id, 1, voteOptions);
                e.preventDefault();
            });

            // Минус
            voteOptions.down.on('click', function (e) {
                self.vote(voteOptions.type, voteOptions.id, -1, voteOptions);
                e.preventDefault();
            });

            // Воздержаться
            voteOptions.abstain.on('click', function (e) {
                self.vote(voteOptions.type, voteOptions.id, 0, voteOptions);
                e.preventDefault();
            });
        });
    };

    /**
     * Голосование
     *
     * @param  {String} targetType
     * @param  {Number} targetId
     * @param  {Number} voteValue
     * @param  {Object} voteOption
     */
    this.vote = function (targetType, targetId, voteValue, voteOption) {
        var target = this.options.type[targetType];

        if (!target) {
            return false;
        }

        var params = {
            value: voteValue
        };
        params[target.targetName] = targetId;

        ls.progressStart();
        ls.ajax(target.url, params, function (response) {
            ls.progressDone();
            var args = [targetType, targetId, voteValue, voteOption, response];
            this.onVote.apply(this, args);
        }.bind(this));

        return false;
    };

    /**
     * Коллбэк вызываемый при успешном голосовании
     *
     * @param  {String} targetType
     * @param  {Number} targetId
     * @param  {Number} voteValue
     * @param  {Object} voteOptions
     * @param  {Object} response
     */
    this.onVote = function (targetType, targetId, voteValue, voteOptions, response) {

        if (!response) {
            ls.msg.error(null, 'System error #1001');
        } else if (response.bStateError) {
            ls.msg.error(null, response.sMsg);
        } else {
            ls.msg.notice(null, response.sMsg);

            voteOptions.vote
                .addClass(this.options.classes.voted)
                .removeClass(this.options.classes.negative + ' ' + this.options.classes.positive + ' ' + this.options.classes.not_voted + ' ' + this.options.classes.zero);

            if (voteValue > 0) {
                voteOptions.vote.addClass(this.options.classes.plus);
            } else if (voteValue < 0) {
                voteOptions.vote.addClass(this.options.classes.minus);
            } else if (voteValue == 0) {
                voteOptions.vote.addClass(this.options.classes.voted_zero);
            }

            if (voteOptions.count.length > 0 && response.iCountVote) {
                voteOptions.count.text(parseInt(response.iCountVote));
            }

            response.iRating = parseFloat(response.iRating);

            if (response.iRating > 0) {
                voteOptions.vote.addClass(this.options.classes.positive);
                voteOptions.rating.text('+' + response.iRating);
            } else if (response.iRating < 0) {
                voteOptions.vote.addClass(this.options.classes.negative);
                voteOptions.rating.text(response.iRating);
            } else if (response.iRating == 0) {
                voteOptions.vote.addClass(this.options.classes.zero);
                voteOptions.rating.text(0);
            }

            var method = 'onVote' + ls.tools.ucfirst(targetType);

            if (typeof this[method] == 'function') {
                this[method].apply(this, [targetType, targetId, voteValue, voteOptions, response]);
            }
        }
    };

    /**
     * Голосование за топик
     *
     * @param  {String} targetType     Тип голосования
     * @param  {Number} targetId ID объекта
     * @param  {Number} voteValue    Значение
     * @param  {Object} voteOptions     Переменные текущего голосования
     * @param  {Object} response    Объект возвращемый сервером
     */
    this.onVoteTopic = function (targetType, targetId, voteValue, voteOptions, response) {
        voteOptions.vote.addClass('js-tooltip-vote-topic').tooltip('enter');
    };

    $(function(){
        ls.vote.init();
    });

    return this;
}).call(ls.vote || {}, jQuery);