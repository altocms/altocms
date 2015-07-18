{extends file="_index.tpl"}

{block name="layout_content"}

    {include file='menus/menu.settings.tpl'}

    {hook run='settings_tuning_begin'}
    <form action="{router page='settings'}tuning/" method="POST" enctype="multipart/form-data" class="wrapper-content">
        {hook run='form_settings_tuning_begin'}

        <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>

        <fieldset>
            <legend>{$aLang.settings_tuning_notice}</legend>

            <div class="checkbox">
                <label>
                    <input {if E::User()->getSettingsNoticeNewTopic()}checked{/if} type="checkbox"
                           id="settings_notice_new_topic" name="settings_notice_new_topic"
                           value="1"/> {$aLang.settings_tuning_notice_new_topic}
                </label>
            </div>
            <div class="checkbox">
                <label>
                    <input {if E::User()->getSettingsNoticeNewComment()}checked{/if} type="checkbox"
                           id="settings_notice_new_comment" name="settings_notice_new_comment"
                           value="1"/> {$aLang.settings_tuning_notice_new_comment}
                </label>
            </div>
            <div class="checkbox">
                <label>
                    <input {if E::User()->getSettingsNoticeNewTalk()}checked{/if} type="checkbox"
                           id="settings_notice_new_talk" name="settings_notice_new_talk"
                           value="1"/> {$aLang.settings_tuning_notice_new_talk}
                </label>
            </div>
            <div class="checkbox">
                <label>
                    <input {if E::User()->getSettingsNoticeReplyComment()}checked{/if} type="checkbox"
                           id="settings_notice_reply_comment" name="settings_notice_reply_comment"
                           value="1"/> {$aLang.settings_tuning_notice_reply_comment}
                </label>
            </div>
            <div class="checkbox">
                <label>
                    <input {if E::User()->getSettingsNoticeNewFriend()}checked{/if} type="checkbox"
                           id="settings_notice_new_friend" name="settings_notice_new_friend"
                           value="1"/> {$aLang.settings_tuning_notice_new_friend}
                </label>
            </div>
        </fieldset>

        <br/>
        <br/>

        <fieldset>
            <legend>{$aLang.settings_tuning_general}</legend>

            <div class="form-group">
                <label>{$aLang.settings_tuning_general_timezone}</label>
                <select name="settings_general_timezone" class="form-control">
                    {foreach $aTimezoneList as $sTimezone}
                        <option value="{$sTimezone}"
                                {if $_aRequest.settings_general_timezone==$sTimezone}selected="selected"{/if}>{$aLang.timezone_list[$sTimezone]}</option>
                    {/foreach}
                </select>
            </div>
        </fieldset>

        <br/>
        <br/>

        {hook run='form_settings_tuning_end'}

        <button type="submit" name="submit_settings_tuning"
                class="btn btn-success">{$aLang.settings_profile_submit}</button>
    </form>
    {hook run='settings_tuning_end'}

{/block}
