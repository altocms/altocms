{extends file='./_settings.tpl'}
{block name="content-body-formcontent"}
<div class="panel-heading">
  <h3 class="panel-title">{$aLang.action.admin.set_section_edit}</h3>
</div>
<div class="panel-body">
<div class="form-group">
  <label class="col-sm-4 control-label">{$aLang.action.admin.set_view_wysiwyg}</label>
  <div class="col-sm-2">
    <label>
    <input type="checkbox" name="view--wysiwyg" value="1" {if Config::Get('view.wysiwyg')}checked{/if}/>
    </label>
  </div>
</div>
<div class="form-group">
  <label class="col-sm-4 control-label">{$aLang.action.admin.set_view_noindex}</label>
  <div class="col-sm-2">
    <label>
    <input type="checkbox" name="view--noindex" value="1" {if Config::Get('view.noindex')}checked{/if}/>
    </label>
  </div>
</div>
<div class="form-group">
  <label class="col-sm-4 control-label">{$aLang.action.admin.set_view_img_resize_width}</label>
  <div class="col-sm-2">
    <div class="input-group">
      <input class="form-control" type="text" name="view--img_resize_width" value="{Config::Get('module.image.preset.default.size.width')}" />
      <span class="input-group-addon">px</span>
    </div>
  </div>
</div>
<div class="form-group">
  <label class="col-sm-4 control-label">{$aLang.action.admin.set_view_img_max_width}</label>
  <div class="col-sm-2">
    <div class="input-group">
      <input class="form-control" type="text" name="view--img_max_width" value="{Config::Get('view.img_max_width')}" />
      <span class="input-group-addon">px</span>
    </div>
  </div>
</div>
<div class="form-group">
  <label class="col-sm-4 control-label">{$aLang.action.admin.set_view_img_max_height}</label>
  <div class="col-sm-2">
    <div class="input-group">
      <input class="form-control" type="text" name="view--img_max_height" value="{Config::Get('view.img_max_height')}" />
      <span class="input-group-addon">px</span>
    </div>
  </div>
</div>
<div class="form-group">
  <label class="col-sm-4 control-label">{$aLang.action.admin.set_topic_max_height}</label>
  <div class="col-sm-2">
    <div class="input-group">
      <input class="form-control" type="text" name="module--topic--max_length" value="{Config::Get('module.topic.max_length')}" />
      <span class="input-group-addon">.....</span>
    </div>
  </div>
</div>
<div class="form-group">
  <label class="col-sm-4 control-label">{$aLang.action.admin.set_topic_tag_required}</label>
  <div class="col-sm-2">
    <label>
    <input type="checkbox" name="tag_required" value="on" {if !Config::Get('module.topic.allow_empty_tags')}checked{/if}/>
    </label>
  </div>
</div>

<div class="form-group">

  <label class="col-sm-4 control-label">{$aLang.action.admin.set_edit_comment}</label>

  <div class="col-sm-4">
    <label {if !$nCommentEditTime}class="checked"{/if}>
    <input type="radio" name="edit_comment" value="off" {if !$nCommentEditTime}checked{/if}/>
    {$aLang.action.admin.set_edit_comment_disabled}
    </label>
    <label {if $nCommentEditTime}class="checked"{/if}>
    <input type="radio" name="edit_comment" value="on" {if $nCommentEditTime}checked{/if}/>
    {$aLang.action.admin.set_edit_comment_enabled}
    </label>
  </div>

  <div class="col-sm-2" style="margin-top: 20px;">
    <div class="input-group">
      <div class="input-group-btn">
        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><span class="selector">{$sCommentEditUnit}</span> <span class="caret"></span></button>
        <ul class="dropdown-menu">
              {foreach $aTimeUnits as $sUnit}
              <li><a href="#" onclick="admin.selectDropdown(this); return false;">{$sUnit.name}</a></li>
              {/foreach}
        </ul>
      </div><!-- /btn-group -->
      <input type="text" class="form-control" name="edit_comment_time" value="{$nCommentEditTime}">
    </div><!-- /form-group -->

    <input type="hidden" name="edit_comment_unit" value="{$sCommentEditUnit}">
  </div>

</div>
</div>
<script>
    var admin = admin || { };

    admin.selectDropdown = function(el){
        var v = $(el).text();
        $('[name=edit_comment_unit]').val(v);
        $(el).parents('.form-group').find('.dropdown-toggle .selector').text(v);
        console.log($(el).siblings('.dropdown-toggle'));
    };
</script>


{/block}