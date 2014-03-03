/*
 *
 */
jQuery(function(){
    var modal_write = $('#modal_write');
    if (modal_write.length) {
        ls.log(modal_write.find('.write-item-type-poll, .write-item-type-link, .write-item-type-photoset'));
        modal_write.find('.write-item-type-poll, .write-item-type-link, .write-item-type-photoset').each(function(){
            $(this.remove());
        });
    }
});