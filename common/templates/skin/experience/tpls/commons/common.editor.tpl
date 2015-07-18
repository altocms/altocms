 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if !$sImgToLoad}
    {assign var="sImgToLoad" value="topic_text"}
{/if}

 {if !$sTargetType}
     {assign var="sTargetType" value="topic"}
 {/if}

 {if !$bTmp}
     {assign var="bTmp" value="true"}
 {/if}

{include_once file='modals/modal.upload_img.tpl' sToLoad=$sImgToLoad}
{include_once file='modals/modal.insert_img.tpl' sTargetType=$sTargetType bTmp=$bTmp}
{if Config::Get('view.wysiwyg')}
    {include_once file="editors/editor.tinymce.tpl"}
{else}
    {include_once file="editors/editor.markitup.tpl"}
{/if}
