(function ($) {
    $(document).ready(function () {
        /*==Left Navigation Accordion ==*/
        if ($.fn.dcAccordion) {
            $('#nav-accordion').dcAccordion({
                eventType: 'click',
                autoClose: true,
                saveState: true,
                disableLink: true,
                speed: 'slow',
                showCount: false,
                autoExpand: true,
                classExpand: 'dcjq-current-parent'
            });
        }

    });

})(jQuery);

(function ($) {
    $(function () {
        $('input, .form-control').styler();
    })
})(jQuery)

$(function() {
  //bootstrap WYSIHTML5 - text editor
    $(".textarea").wysihtml5();
});