{if E::IsUser()}
    <div class="modal fade in" id="modal-favourite_tags">
        <div class="modal-dialog">
            <div class="modal-content">

                <header class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                    <h4 class="modal-title">{$aLang.add_favourite_tags}</h4>
                </header>

                <form onsubmit="return ls.favourite.saveTags(this);">
                    <div class="modal-body">
                        <input type="hidden" name="target_type" value="" id="favourite-form-tags-target-type">
                        <input type="hidden" name="target_id" value="" id="favourite-form-tags-target-id">

                        <div class="form-group">
                            <input type="text" name="tags" value="" id="favourite-form-tags-tags"
                                   class="form-control autocomplete-tags-sep">
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" name="" class="btn btn-success">
                        {$aLang.favourite_form_tags_button_save}</button>
                        <button type="submit" name="" class="btn btn-default" data-dismiss="modal" aria-hidden="true">
                        {$aLang.favourite_form_tags_button_cancel}</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
{/if}
