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

        <div class="form-group">
            <label for="blog_title">{$aLang.blog_create_title}</label>
            <input type="text" id="blog_title" name="blog_title" value="{$_aRequest.blog_title}" class="form-control"/>

            <p class="help-block">
                <small>{$aLang.blog_create_title_notice}</small>
            </p>
        </div>

        <div class="form-group">
            <label for="blog_url">{$aLang.blog_create_url}</label>
            <input type="text" id="blog_url" name="blog_url" value="{$_aRequest.blog_url}" class="form-control"
                   {if $_aRequest.blog_id AND !E::IsAdmin()}disabled{/if} />

            <p class="help-block">
                <small>{$aLang.blog_create_url_notice}</small>
            </p>
        </div>

        <div class="form-group">
            <label for="blog_type">{$aLang.blog_create_type}</label>
            <select name="blog_type" id="blog_type" class="form-control"
                    onChange="ls.blog.loadInfoType(jQuery(this).val());">
                {foreach $aBlogTypes as $oBlogType}
                    <option value="{$oBlogType->getTypeCode()}"
                            {if $_aRequest.blog_type=={$oBlogType->getTypeCode()}}selected{/if}>
                        {$oBlogType->getName()}
                    </option>
                {/foreach}
            </select>

            <p class="help-block">
                <small>{$aLang.blog_create_type_open_notice}</small>
            </p>
        </div>

        <div class="form-group">
            <label for="blog_description">{$aLang.blog_create_description}</label>
            <textarea name="blog_description" id="blog_description" rows="3"
                      class="form-control">{$_aRequest.blog_description}</textarea>

            <p class="help-block">
                <small>{$aLang.blog_create_description_notice}</small>
            </p>
        </div>

        <div class="form-group">
            <label for="blog_limit_rating_topic">{$aLang.blog_create_rating}</label>
            <input type="text" id="blog_limit_rating_topic" name="blog_limit_rating_topic"
                   value="{$_aRequest.blog_limit_rating_topic}" class="form-control"/>

            <p class="help-block">
                <small>{$aLang.blog_create_rating_notice}</small>
            </p>
        </div>

        <p>
            {if $oBlogEdit AND $oBlogEdit->getAvatar()}

        <div class="avatar-edit">
            {foreach Config::Get('module.blog.avatar_size') as $iSize}
                {if $iSize}<img src="{$oBlogEdit->getAvatarPath({$iSize})}">{/if}
            {/foreach}

            <div class="checkbox">
                <label><input type="checkbox" id="avatar_delete" name="avatar_delete" value="on"
                              class="input-checkbox">{$aLang.blog_create_avatar_delete}</label>
            </div>
        </div>
        {/if}

        <div class="form-group">
            <label for="avatar">{$aLang.blog_create_avatar}</label>
            <input type="file" name="avatar" id="avatar">
        </div>
        </p>

        {hook run='form_add_blog_end'}
        <br/>

        <button type="submit" name="submit_blog_add" class="btn btn-success">{$aLang.blog_create_submit}</button>
    </form>
{/block}
