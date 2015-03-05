<script>
    jQuery(function () {
        jQuery('#js-alto-image-manager').altoImageManager(false);
    });
</script>
<div id="js-alto-image-manager" class="modal fade in">
    <div class="modal-dialog">
        <div class="modal-content">

            <header class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">{$aLang.insertimg} <span id="aim-pages-container"></span></h4>
                <ul class="image-categories-nav list-unstyled list-inline list-no-border pull-right">
                    <li id="backTopics" style="display: none;">
                        <a class="image-categories-nav-back-topics" href="#">
                            <i class="fa fa-chevron-left"></i>
                        </a>
                    </li>
                    <li id="backTalks" style="display: none;">
                        <a class="image-categories-nav-back-talks" href="#">
                            <i class="fa fa-chevron-left"></i>
                        </a>
                    </li>
                    <li>
                        <a class="image-categories-nav-refresh" href="#">
                            <i class="fa fa-refresh"></i>
                        </a>
                    </li>
                    <li>
                        <a class="image-categories-nav-trigger" href="#">
                            <i class="fa fa-eye-slash"></i>
                        </a>
                    </li>
                </ul>
            </header>

            <div class="modal-body">

                <script id="aim-pages-template" type="template/javascript">
                    &nbsp;-&nbsp; {$aLang.insertimg_page} %page% {$aLang.insertimg_from} %pages%
                </script>

                <div class="row">
                    <div class="col-md-8 image-categories-tree">

                        <div id="image-categories-tree-container">

                        </div>
                    </div>
                    <div class="col-md-16">
                        <div id="image-container">
                            {include "modals/insert_img/inject.pc.tpl"}
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>