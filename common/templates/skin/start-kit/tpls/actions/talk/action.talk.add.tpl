{extends file="_index.tpl"}

{block name="layout_content"}

    {include file='menus/menu.talk.tpl'}

    {include file='actions/talk/action.talk.friends.tpl'}

    {hook run='talk_add_begin'}

    <div class="topic" style="display: none;">
        <div class="content" id="text_preview"></div>
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
            <label for="talk_users">{$aLang.talk_create_users}</label>
            <input type="text" class="form-control autocomplete-users-sep" id="talk_users" name="talk_users"
                   value="{$_aRequest.talk_users}"/>
        </div>

        <div class="form-group">
            <label for="talk_title">{$aLang.talk_create_title}</label>
            <input type="text" class="form-control" id="talk_title" name="talk_title" value="{$_aRequest.talk_title}"/>
        </div>

        <div class="form-group">
            <label for="talk_text">{$aLang.talk_create_text}</label>
            <textarea name="talk_text" id="talk_text" rows="12"
                      class="form-control js-editor-wysiwyg js-editor-markitup">{$_aRequest.talk_text}</textarea>
        </div>

        {hook run='form_add_talk_end'}

        <button type="submit" class="btn btn-success" name="submit_talk_add">{$aLang.talk_create_submit}</button>
        <button type="submit" class="btn btn-default" name="submit_preview"
                onclick="jQuery('#text_preview').parent().show(); ls.tools.textPreview('#talk_text',false); return false;">{$aLang.topic_create_submit_preview}</button>
    </form>

    {hook run='talk_add_end'}

{/block}
