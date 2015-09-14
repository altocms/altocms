{extends file='./_settings.tpl'}

{block name="content-body-formcontent"}
    <div class="b-wbox-header">
        <div class="b-wbox-header-title">{$aLang.action.admin.set_section_edit}</div>
    </div>
        <div class="control-group">
            <label class="control-label">{$aLang.action.admin.set_view_wysiwyg}</label>

            <div class="controls">
                <label>
                    <input type="checkbox" name="view--wysiwyg" value="1" {if Config::Get('view.wysiwyg')}checked{/if}/>
                </label>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">{$aLang.action.admin.set_view_noindex}</label>

            <div class="controls">
                <label>
                    <input type="checkbox" name="view--noindex" value="1" {if Config::Get('view.noindex')}checked{/if}/>
                </label>
            </div>
        </div>

        <!-- div class="control-group">
            <label class="control-label">{$aLang.action.admin.set_view_img_resize_width}</label>

            <div class="controls">
                <div class="input-group">
                    <input type="text" name="view--img_resize_width" value="{Config::Get('view.img_resize_width')}" />
                    <span class="input-group-addon">px</span>
                </div>
            </div>
        </div -->

        <div class="control-group">
            <label class="control-label">{$aLang.action.admin.set_view_img_max_width}</label>

            <div class="controls">
                <div class="input-group">
                    <input type="text" name="view--img_max_width" value="{Config::Get('module.uploader.images.default.max_width')}" />
                    <span class="input-group-addon">px</span>
                </div>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">{$aLang.action.admin.set_view_img_max_height}</label>

            <div class="controls">
                <div class="input-group">
                    <input type="text" name="view--img_max_height" value="{Config::Get('module.uploader.images.default.max_height')}" />
                    <span class="input-group-addon">px</span>
                </div>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">{$aLang.action.admin.set_topic_max_height}</label>

            <div class="controls">
                <div class="input-group">
                    <input type="text" name="module--topic--max_length" value="{Config::Get('module.topic.max_length')}" />
                    <span class="input-group-addon"></span>
                </div>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">{$aLang.action.admin.set_topic_tag_required}</label>

            <div class="controls">
                <label>
                    <input type="checkbox" name="tag_required" value="on" {if !Config::Get('module.topic.allow_empty_tags')}checked{/if}/>
                </label>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">{$aLang.action.admin.set_edit_comment}</label>

            <div class="controls">
                <label {if !$nCommentEditTime}class="checked"{/if}>
                    <input type="radio" name="edit_comment" value="off" {if !$nCommentEditTime}checked{/if}/>
                    {$aLang.action.admin.set_edit_comment_disabled}
                </label>
                <label {if $nCommentEditTime}class="checked"{/if}>
                    <input type="radio" name="edit_comment" value="on" {if $nCommentEditTime}checked{/if}/>
                    {$aLang.action.admin.set_edit_comment_enabled}

					<div class="input-group-inline">
                    <div class="input-group">
                    <div class="input-append">
                    <input type="text" name="edit_comment_time" value="{$nCommentEditTime}" style="width: 40px;" />
                    <span class="btn-group input-group-btn">
                        <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="content">{$sCommentEditUnit}</span> <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            {foreach $aTimeUnits as $sUnit}
                                <li><a href="#" onclick="admin.selectDropdown(this); return false;">{$sUnit.name}</a></li>
                            {/foreach}
                        </ul>
                    </span>
                    </div>
					</div>
					</div>
                    <input type="hidden" name="edit_comment_unit" value="{$sCommentEditUnit}">
                </label>
            </div>
        </div>

<script>
    var admin = admin || { };

    admin.selectDropdown = function(el){
        var v = $(el).text();
        $('[name=edit_comment_unit]').val(v);
        $(el).parents('.btn-group').find('.dropdown-toggle .content').text(v);
        //console.log($(el).siblings('.dropdown-toggle'));
    };
</script>
{/block}