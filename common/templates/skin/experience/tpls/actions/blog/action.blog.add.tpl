 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{extends file="_index.tpl"}

{block name="layout_vars"}
    {if $sEvent=='add'}
        {$menu_content="create"}
    {/if}
{/block}

{block name="layout_content"}
    {if $sEvent!='add'}
        {include file='menus/menu.blog_edit.tpl'}
    {/if}

<div class="panel panel-default content-write raised">
    <div class="panel-body">



    {include file='commons/common.editor.tpl' sImgToLoad='blog_description' sSettingsTinymce='ls.settings.getTinymceComment()' sSettingsMarkitup='ls.settings.getMarkitupComment()'}

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            ls.lang.load({lang_load name="blog_create_type_open_notice,blog_create_type_close_notice"});
            ls.blog.loadInfoType($('#blog_type').val());
        });
    </script>

    <form method="post" enctype="multipart/form-data" class="wrapper-content">
        {hook run='form_add_blog_begin'}

        <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>

        {* ЗАГОЛОВОК *}
        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-header"></i></span>
                <input type="text" id="blog_title" name="blog_title" value="{$_aRequest.blog_title}" class="form-control"/>
            </div>
            <small class="control-notice">{$aLang.blog_create_title_notice}</small>
        </div>
        {* URL БЛОГА *}
        <div class="form-group">
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-globe"></i></span>
                <input type="text" id="blog_url" name="blog_url" value="{$_aRequest.blog_url}" class="form-control" {if $_aRequest.blog_id AND !E::IsAdmin()}disabled{/if} />
            </div>
            <small class="control-notice">{$aLang.blog_create_url_notice}</small>
        </div>

        <div class="row">
            <div class="col-sm-14">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-comment-o"></i></span>
                        <select name="blog_type" id="blog_type" class="form-control"
                                onChange="ls.blog.loadInfoType(jQuery(this).val());">
                            {foreach $aBlogTypes as $oBlogType}
                                <option value="{$oBlogType->getTypeCode()}"
                                        {if $_aRequest.blog_type=={$oBlogType->getTypeCode()}}selected{/if}>
                                    {$oBlogType->getName()}
                                </option>
                            {/foreach}
                        </select>
                        </div>
                </div>
            </div>
            <div class="col-sm-10">
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-times"></i></span>
                        <input type="text" id="blog_limit_rating_topic" name="blog_limit_rating_topic" value="{$_aRequest.blog_limit_rating_topic}" class="form-control" />
                    </div>
                    <small class="control-notice">{$aLang.blog_create_rating_notice}</small>
                </div>
            </div>
        </div>


        {* ОПИСАНИЕ БЛОГА *}
        <div class="form-group">
            <div class="input-group fill-width">
                <textarea name="blog_description"
                          placeholder="{$aLang.blog_create_description}"
                          id="blog_description" rows="3"
                          class="form-control">{$_aRequest.blog_description}</textarea>
            </div>
            <small class="control-notice">{$aLang.blog_create_description_notice}</small>
        </div>



        <p>
            {if $oBlogEdit AND $oBlogEdit->getAvatar()}

        <div class="avatar-edit">
            {foreach Config::Get('module.blog.avatar_size') as $iSize}
                {if $iSize}<img src="{$oBlogEdit->getAvatarPath({$iSize})}">{/if}
            {/foreach}

            <div class="checkbox">
                <label for="topic_delete_file_{$iFieldId}">
                    <input type="checkbox" id="avatar_delete"
                           name="avatar_delete"
                           class="avatar_delete mal0"
                           value="on"> {$aLang.blog_create_avatar_delete}
                </label>
            </div>
        </div>
        {/if}

        <div class="form-group">
            <div class="input-group">
                <label class="input-group-addon" for="avatar">{$aLang.blog_create_avatar}</label>
                <div class="fileinput fileinput-new input-group" data-provides="fileinput">
                    <div class="form-control" data-trigger="fileinput">
                        <i class="fa fa-file fileinput-exists"></i>
                        <span class="fileinput-filename"></span>
                    </div>
                <span class="input-group-addon btn btn-default btn-file">
                    <span class="fileinput-new">{$aLang.select}</span>
                    <span class="fileinput-exists">{$aLang.change}</span>
                    <input class="form-control" type="file" name="avatar" id="avatar">
                </span>
                    <a href="#" class="input-group-addon btn btn-default fileinput-exists" data-dismiss="fileinput">{$aLang.remove}</a>
                </div>
            </div>
        </div>

        </p>


        {hook run='form_add_blog_end'}
        <br/>

        <button type="submit" name="submit_blog_add" class="btn btn-success">{$aLang.blog_create_submit}</button>
    </form>

    </div>
</div>
{/block}
