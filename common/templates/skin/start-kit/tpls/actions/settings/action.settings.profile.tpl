{extends file="_index.tpl"}

{block name="layout_content"}

    {include file='menus/menu.settings.tpl'}
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            ls.lang.load({lang_load name="geo_select_city,geo_select_region"});
            ls.geo.initSelect();
            //ls.userfield.iCountMax = '{Config::Get('module.user.userfield_max_identical')}';
        });
    </script>
    {hook run='settings_profile_begin'}
    <form method="post" enctype="multipart/form-data" class="form-profile">
        <div class="wrapper-content">

            {hook run='form_settings_profile_begin'}

            <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}">

            <fieldset>
                <legend>{$aLang.settings_profile_section_base}</legend>

                <div class="row">
                    <div class="col-lg-8">

                        <div class="form-group">
                            <label for="profile_name">{$aLang.settings_profile_name}</label>
                            <input type="text" name="profile_name" id="profile_name"
                                   value="{E::User()->getProfileName()|escape:'html'}" class="form-control">

                            <p class="help-block">
                                <small>{$aLang.settings_profile_name_notice|ls_lang:"name_max%%{C::Get('module.user.name_max')}"}</small>
                            </p>
                        </div>

                        <div class="form-group">
                            <label for="profile_sex">{$aLang.settings_profile_sex}</label>
                            <select name="profile_sex" id="profile_sex" class="form-control">
                                <option value="man"
                                        {if E::User()->getProfileSex()=='man'}selected{/if}>{$aLang.settings_profile_sex_man}</option>
                                <option value="woman"
                                        {if E::User()->getProfileSex()=='woman'}selected{/if}>{$aLang.settings_profile_sex_woman}</option>
                                <option value="other"
                                        {if E::User()->getProfileSex()=='other'}selected{/if}>{$aLang.settings_profile_sex_other}</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="">{$aLang.settings_profile_birthday}</label>

                            <div class="row">
                                <div class="col-sm-4 col-lg-4">
                                    <select name="profile_birthday_day" class="form-control">
                                        <option value="">{$aLang.date_day}</option>
                                        {section name=date_day start=1 loop=32 step=1}
                                            <option value="{$smarty.section.date_day.index}"
                                                    {if $smarty.section.date_day.index==E::User()->getProfileBirthday()|date_format:"%d"}selected{/if}>{$smarty.section.date_day.index}</option>
                                        {/section}
                                    </select>
                                    <br/>
                                </div>
                                <div class="col-sm-4 col-lg-4">
                                    <select name="profile_birthday_month" class="form-control">
                                        <option value="">{$aLang.date_month}</option>
                                        {section name=date_month start=1 loop=13 step=1}
                                            <option value="{$smarty.section.date_month.index}"
                                                    {if $smarty.section.date_month.index==E::User()->getProfileBirthday()|date_format:"%m"}selected{/if}>{$aLang.month_array[$smarty.section.date_month.index][0]}</option>
                                        {/section}
                                    </select>
                                    <br/>
                                </div>
                                <div class="col-sm-4 col-lg-4">
                                    <select name="profile_birthday_year" class="form-control">
                                        <option value="">{$aLang.date_year}</option>
                                        {section name=date_year loop=$smarty.now|date_format:"%Y"+1 max=$smarty.now|date_format:"%Y"-2012+130 step=-1}
                                            <option value="{$smarty.section.date_year.index}"
                                                    {if $smarty.section.date_year.index==E::User()->getProfileBirthday()|date_format:"%Y"}selected{/if}>{$smarty.section.date_year.index}</option>
                                        {/section}
                                    </select>
                                    <br/>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="pull-right settings-avatar-change">

                            {* БЛОК ЗАГРУЗКИ ИЗОБРАЖЕНИЯ *}
                            <div class              ="js-alto-uploader tac settings-avatar-change"
                                 data-target        ="profile_avatar"
                                 data-target-id     ="{E::User()->getId()}"
                                 data-title         ="{$aLang.settings_profile_avatar_resize_title}"
                                 data-help          ="{$aLang.settings_profile_avatar_resize_text}"
                                 data-aspect-ratio  ="{E::ModuleUploader()->GetConfigAspectRatio('*', 'profile_avatar')}"
                                 data-empty         ="{E::User()->getDefaultAvatarUrl('large')}"
                                 data-preview-crop  ="100x100"
                                 data-crop          ="yes">

                                {* Картинка фона блога *}
                                <img style="width: 100%; display: block; margin-bottom: 8px;"
                                     src="{E::User()->getAvatarUrl('large')}"
                                     id="profile-avatar-image"
                                     class="profile-avatar js-uploader-image"/>

                                {* Меню управления картинкой фона блога *}
                                <div class="uploader-actions profile-avatar-menu">

                                    {* Кнопка загрузки картинки *}
                                    <a href="#" onclick="return false;" class="btn btn-default btn-xs js-uploader-button-upload"
                                       data-toggle="file" data-target="#profile-avatar-file">
                                        {$aLang.settings_profile_avatar_change}
                                    </a>

                                    {* Кнопка удаления картинки *}
                                    <br/>
                                    <a href="#" class="link-dotted js-uploader-button-remove"
                                       {if !$oUserCurrent->hasAvatar()}style="display: none;"{/if}>
                                        {$aLang.settings_profile_avatar_delete}
                                    </a>

                                    {* Файл для загрузки *}
                                    <input type="file" name="uploader-upload-image" class="uploader-actions-file js-uploader-file">

                                </div>

                                {* Форма обрезки картинки при ее загрузке *}
                                {include_once file="modals/modal.crop_img.tpl"}

                            </div>

                        </div>

                    </div>
                </div>

                <div class="form-group">
                    <label for="profile_about">{$aLang.settings_profile_about}</label>
                    <textarea name="profile_about" id="profile_about" class="form-control"
                              rows="5">{E::User()->getProfileAbout()|escape:'html'}</textarea>
                </div>

                <div class="js-geo-select">
                    <div class="row">
                        <div class="col-sm-6 col-lg-6">

                            <div class="form-group">
                                <label for="">{$aLang.profile_place}</label>
                                <select class="js-geo-country form-control" name="geo_country">
                                    <option value="">{$aLang.geo_select_country}</option>
                                    {if $aGeoCountries}
                                        {foreach $aGeoCountries as $oGeoCountry}
                                            <option value="{$oGeoCountry->getId()}"
                                                    {if $oGeoTarget AND $oGeoTarget->getCountryId()==$oGeoCountry->getId()}selected="selected"{/if}>{$oGeoCountry->getName()}</option>
                                        {/foreach}
                                    {/if}
                                </select>
                                <br/>

                                <select class="js-geo-region form-control" name="geo_region"
                                        {if !$oGeoTarget OR !$oGeoTarget->getCountryId()}style="display:none;"{/if}>
                                    <option value="">{$aLang.geo_select_region}</option>
                                    {if $aGeoRegions}
                                        {foreach $aGeoRegions as $oGeoRegion}
                                            <option value="{$oGeoRegion->getId()}"
                                                    {if $oGeoTarget AND $oGeoTarget->getRegionId()==$oGeoRegion->getId()}selected="selected"{/if}>{$oGeoRegion->getName()}</option>
                                        {/foreach}
                                    {/if}
                                </select>
                                <br/>

                                <select class="js-geo-city form-control" name="geo_city"
                                        {if !$oGeoTarget OR !$oGeoTarget->getRegionId()}style="display:none;"{/if}>
                                    <option value="">{$aLang.geo_select_city}</option>
                                    {if $aGeoCities}
                                        {foreach $aGeoCities as $oGeoCity}
                                            <option value="{$oGeoCity->getId()}"
                                                    {if $oGeoTarget AND $oGeoTarget->getCityId()==$oGeoCity->getId()}selected="selected"{/if}>{$oGeoCity->getName()}</option>
                                        {/foreach}
                                    {/if}
                                </select>
                            </div>

                        </div>
                    </div>
                </div>

                {$aUserFieldValues=E::User()->getUserFieldValues(false,'')}
                {if count($aUserFieldValues)}
                    {foreach $aUserFieldValues as $oField}
                        <label for="profile_user_field_{$oField->getId()}">{$oField->getTitle()|escape:'html'}</label>
                        <input type="text" class="span6" name="profile_user_field_{$oField->getId()}"
                               id="profile_user_field_{$oField->getId()}" value="{$oField->getValue()|escape:'html'}"/>
                    {/foreach}
                {/if}
            </fieldset>
        </div>

        <div class="wrapper-content wrapper-content-dark">
            <fieldset>
                <legend>
                    {$aLang.settings_profile_section_contacts}
                </legend>

                {$aUserFieldContactValues=E::User()->getUserFieldValues(true,array('contact','social'))}
                <div id="user-field-contact-container">
                    <div class="row user-field-item js-user-field-item" style="display:none;">
                        <div class="col-sm-6 col-lg-6">
                            <select name="profile_user_field_type[]" onchange="ls.userfield.changeFormField(this);" class="form-control">
                                {foreach from=$aUserFieldsContact item=oFieldAll}
                                    <option value="{$oFieldAll->getId()}">{$oFieldAll->getTitle()|escape:'html'}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="col-sm-5 col-lg-5">
                            <input type="text" name="profile_user_field_value[]" value="" class="form-control">
                        </div>
                        <div class="col-sm-1 col-lg-1">
                            <a href="#" class="glyphicon glyphicon-remove" title="{$aLang.user_field_delete}"
                               onclick="return ls.userfield.removeFormField(this);"></a>
                        </div>
                    </div>
                    {foreach $aUserFieldContactValues as $oField}
                        <div class="row user-field-item js-user-field-item">
                            <div class="col-sm-6 col-lg-6">
                            <select name="profile_user_field_type[]" onchange="ls.userfield.changeFormField(this);" class="form-control">
                                {foreach $aUserFieldsContact as $oFieldAll}
                                    <option value="{$oFieldAll->getId()}" {if $oFieldAll->getId()==$oField->getId()}selected="selected"{/if}>
                                        {$oFieldAll->getTitle()|escape:'html'}
                                    </option>
                                {/foreach}
                            </select>
                            </div>
                            <div class="col-sm-5 col-lg-5">
                            <input type="text" name="profile_user_field_value[]" value="{$oField->getValue()|escape:'html'}" class="form-control">
                            </div>
                            <div class="col-sm-1 col-lg-1">
                            <a href="#" class="glyphicon glyphicon-remove" title="{$aLang.user_field_delete}"
                               onclick="return ls.userfield.removeFormField(this);"></a>
                            </div>
                        </div>
                    {/foreach}
                </div>
                {if $aUserFieldsContact}
                    <button class="btn btn-default btn-xs" onclick="return ls.userfield.addFormField();">
                        <span class="glyphicon glyphicon-plus-sign"></span>
                        {$aLang.user_field_add}
                    </button>
                {/if}
            </fieldset>
        </div>

        <div class="wrapper-content">
            <script type="text/javascript">
                jQuery(function ($) {
                    $('#avatar-upload').file({ name: 'avatar' }).choose(function (e, input) {
                        ls.user.uploadAvatar(null, input);
                    });
                });
            </script>

            {hook run='form_settings_profile_end'}

            <button type="submit" name="submit_profile_edit" class="btn btn-success">
                {$aLang.settings_profile_submit}
            </button>
        </div>
    </form>
    {hook run='settings_profile_end'}

{/block}
