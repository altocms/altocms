 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<div class="form-group">
    <div class="input-group">
        <span class="input-group-addon"><i class="fa fa-tags"></i></span>
        <input type="text" id="topic_field_tags" name="topic_field_tags" value="{$_aRequest.topic_field_tags}"
               class="form-control autocomplete-tags-sep"/>
    </div>
    <div class="row">
        <div class="col-xs-{if !Config::Get('view.wysiwyg')}18{else}24{/if}">
            <small class="control-notice control-twice">{$aLang.topic_create_tags_notice}</small>
        </div>
    </div>
</div>
