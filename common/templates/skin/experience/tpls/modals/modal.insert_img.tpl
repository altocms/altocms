<div id="js-alto-image-manager" class="modal fade in">
    <div class="modal-dialog">
        <div class="modal-content">

            <header class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{$aLang.insertimg}</h4>
            </header>

            <div class="modal-body">

                <div class="row">
                    <div class="col-md-8 image-categories-tree">
                        <div id="image-categories-tree-container">

                        </div>
                    </div>
                    <div class="col-md-16">
                        <div id="image-container">
                            {$aLang.select_category}
                        </div>
                    </div>
                </div>

                <br/>
                <div class="clearfix">
                    <button id="images-next-page" class="refresh-tree btn pull-right btn-default btn-sm" disabled >
                        {$aLang.next_page}
                    </button>

                    <button id="images-prev-page" class="btn btn-default btn-sm pull-right" disabled >
                        {$aLang.prev_page}
                    </button>
                </div>


            </div>

        </div>
    </div>
</div>