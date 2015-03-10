 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}
 <div class="info-container"><i class="fa fa-info-circle pull-right js-title-topic" data-original-title="{$aLang.topic_create_tags_notice}"></i></div>
<div class="form-group">
    <div class="input-group">
        <span class="input-group-addon"><i class="fa fa-tags"></i></span>
        <input type="text" id="topic_field_tags" name="topic_field_tags" value="{$_aRequest.topic_field_tags}"
               class="form-control autocomplete-tags-sep"/>
    </div>
    <div class="row">
        {if !Config::Get('view.wysiwyg')}
            <div class="col-xs-24">
                <a class="link link-lead link-blue control-twice pull-righy" href="#"
                   onclick="$('.tags-about').slideToggle(100);
                   $(this).toggleClass('active');
                   return false;">{$aLang.topic_create_text_notice}</a>
            </div>
        {/if}
    </div>
</div>
