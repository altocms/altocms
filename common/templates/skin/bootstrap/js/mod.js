/**
 * For template ls-bootstrap
 */

/* Для коодинацйии скролла для якорей комментов
 /*-------------------------------------------------------------------------------------------------*/
function comment_scroll_top_all() {
    var url = window.location.href;

    if (url.indexOf('#comment')>0) {
        var number = url.substring(url.indexOf('#comment')+8);
        var num_comm = $('#comment' + number);

        setTimeout(function() {
            $('html,body').scrollTop((num_comm.offset().top - 100));
        },300);
    }
}

function comment_scroll_top_one() {
    var url = window.location.href;

    if (url.indexOf('#comment')>0) {
        var number = url.substring(url.indexOf('#comment')+8);
        var num_comm = $('#comment' + number);

        setTimeout(function() {
            $('html,body').scrollTop((num_comm.offset().top - 50));
        },300);
    }
}

