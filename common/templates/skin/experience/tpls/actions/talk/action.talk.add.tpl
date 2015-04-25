 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="topics"}
{/block}

{block name="layout_content"}

    {include file='menus/menu.talk.tpl'}

    {include file='actions/talk/action.talk.friends.tpl'}

    {hook run='talk_add_begin'}

<div class="panel panel-default panel-table raised">

    <div class="panel-body">

        <div class="topic" style="display: none;">
            <div class="content bg-warning mar0" id="text_preview"></div>
            <br/>
        </div>

        {include file='commons/common.editor.tpl'
                 sTargetType='talk'
                 bTmp='false'
                 sImgToLoad='talk_text'
                 sSettingsTinymce='ls.settings.getTinymceComment()'
                 sSettingsMarkitup='ls.settings.getMarkitupComment()'}

        <form action="" method="POST" enctype="multipart/form-data" class="wrapper-content">

            {hook run='form_add_talk_begin'}

            <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>

            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-users"></i></span>
                    <input type="text" placeholder="{$aLang.talk_create_users}" class="form-control autocomplete-users-sep" id="talk_users" name="talk_users"
                           value="{$_aRequest.talk_users}"/>

                </div>
            </div>

            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-header"></i></span>
                    <input placeholder="{$aLang.talk_create_title}" type="text" class="form-control" id="talk_title" name="talk_title" value="{$_aRequest.talk_title}"/>

                </div>
            </div>


            <div class="form-group">
                <textarea name="talk_text" id="talk_text" rows="12"
                          class="form-control js-editor-wysiwyg js-editor-markitup">{$_aRequest.talk_text}</textarea>
            </div>

            {hook run='form_add_talk_end'}

            <button type="submit" class="btn btn-blue btn-big corner-no pull-right" name="submit_talk_add">{$aLang.talk_create_submit}</button>
            <button type="submit" class="btn btn-light btn-big corner-no" name="submit_preview"
                    onclick="jQuery('#text_preview').parent().show(); ls.tools.textPreview('#talk_text',false); return false;">{$aLang.topic_create_submit_preview}</button>
        </form>

        {hook run='talk_add_end'}

    </div>
</div>





{/block}
