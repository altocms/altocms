{if $oField}
    {* К топику может быть привязано несколько изображений и для каждого *}
    {* будет разный идентификатор по которому они будут различаться в БД *}
    {$sTargetType="{$oField->getFieldType()}-{$oField->getFieldId()}"}

    {* Для режима редактирвония целевым объектом будет топик, а для режима*}
    {* создания объект не определен *}
    {if $sMode == 'add'}{$oTarget=FALSE}{else}{$oTarget=$oTopic}{/if}

    {* Подгрузим текстовки, которые будут отображаться в окне ресайза *}
    <script>
        $(function () {
            ls.lang.load({lang_load name="uploader_single_upload_resize_title,uploader_single_upload_resize_help"});
        })
    </script>

    {* БЛОК ЗАГРУЗКИ ИЗОБРАЖЕНИЯ *}
    <div class              ="js-alto-uploader"
         data-target        ="{$sTargetType}"
         data-target-id     ="{if $sMode == 'add'}0{else}{$_aRequest.topic_id}{/if}"
         data-title         ="uploader_single_upload_resize_title"
         data-help          ="uploader_single_upload_resize_help"
         data-empty         ="{asset file="images/empty_image.png" theme=true}"
         data-preview-crop  ="400fit"
         data-crop          ="yes">

        {* Картинка *}
        {img attr=[
                'src'           => "{asset file="images/empty_image.png" theme=true}",
                'alt'           => "image",
                'class'         => "thumbnail js-uploader-image",
                'target-type'   => $sTargetType,
                'crop'          => '400fit',
                'target-id'     => "{if $oTarget}{$oTarget->getId()}{else}0{/if}"
        ]}

        {* Меню управления картинкой*}
        <div class="info-container"><i class="fa fa-info-circle pull-right js-title-topic" data-original-title="{$oField->getFieldDescription()}"></i></div>
        <div class="uploader-actions">

            {* Кнопка загрузки картинки *}
            <a href="#" onclick="return false;" class="link link-lead link-blue no-corner js-uploader-button-upload">
                <i class="fa fa-upload"></i>&nbsp;{$aLang.uploader_image_upload}
            </a>
            {* Файл для загрузки *}
            <input type="file" name="uploader-upload-image" class="uploader-actions-file js-uploader-file">

        </div>

        {* Кнопка удаления картинки *}
        <div class="uploader-actions">
            <a href="#" class="link link-lead link-clear link-red-blue js-uploader-button-remove"
               {if !($oTarget && $oTarget->getImageUrlByType($sTargetType)) && !$bImageIsTemporary}style="display: none;"{/if}>
                <i class="fa fa-times"></i>&nbsp;{$aLang.uploader_image_delete}
            </a>

        </div>

        {* Форма обрезки картинки при ее загрузке *}
        {include_once file="modals/modal.crop_img.tpl"}
    </div>
{/if}
