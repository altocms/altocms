{if !$sImgToLoad}
    {assign var="sImgToLoad" value="topic_text"}
{/if}
{include_once file='modals/modal.upload_img.tpl' sToLoad=$sImgToLoad}

{if Config::Get('view.wysiwyg')}
    {include_once file="editors/editor.tinymce.tpl"}
{else}
    {include_once file="editors/editor.markitup.tpl"}
{/if}
