<div id="aim-page-pc" class="row">
    <div class="col-md-24">
        <form method="POST" action="" enctype="multipart/form-data" onsubmit="return false;">

            {include file="tpls/modals/insert_img/inject.params.tpl"}

            {include file="tpls/modals/insert_img/inject.multiuploader.tpl"}

            {hook run="uploadimg_additional"}

        </form>
    </div>

</div>