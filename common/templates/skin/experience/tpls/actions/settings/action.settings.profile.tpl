 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{extends file="_index.tpl"}

 {block name="layout_vars"}
     {$menu="topics"}
 {/block}


 {block name="layout_content"}
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            ls.lang.load({lang_load name="geo_select_city,geo_select_region"});
            ls.geo.initSelect();
            //ls.userfield.iCountMax = '{Config::Get('module.user.userfield_max_identical')}';
        });
    </script>

    <div class="panel panel-default panel-table raised">

        <div class="panel-body">

            {hook run='settings_profile_begin'}

            <form method="post" enctype="multipart/form-data" class="form-profile">
                {hook run='form_settings_profile_begin'}
                {***************************************************************************************}

                <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}">

                <div class="panel-header">{$aLang.settings_profile_section_base}</div>

                <div class="row">
                    <div class="col-md-4">

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
                                <a href="#" onclick="return false;" class="link link-lead link-blue no-corner js-uploader-button-upload"
                                   data-toggle="file" data-target="#profile-avatar-file">
                                    <i class="fa fa-pencil"></i>&nbsp;{$aLang.settings_profile_avatar_change}
                                </a>

                                {* Кнопка удаления картинки *}
                                <br/>
                                <a href="#" class="link link-lead link-clear link-red-blue js-uploader-button-remove"
                                   {if !$oUserCurrent->hasAvatar()}style="display: none;"{/if}>
                                    <i class="fa fa-times"></i>&nbsp;{$aLang.settings_profile_avatar_delete}
                                </a>

                                {* Файл для загрузки *}
                                <input type="file" name="uploader-upload-image" class="uploader-actions-file js-uploader-file">

                            </div>

                            {* Форма обрезки картинки при ее загрузке *}
                            {include_once file="modals/modal.crop_img.tpl"}

                        </div>

                    </div>


                    <div class="col-md-20">
                        <div class="form-group">

                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">{$aLang.settings_profile_name}</span>
                                    <input type="text"
                                           placeholder="{$aLang.settings_profile_name_notice|ls_lang:"name_max%%{C::Get('module.user.name_max')}"}"
                                           name="profile_name" id="profile_name"
                                           value="{E::User()->getProfileName()|escape:'html'}" class="form-control">
                                </div>
                                {*<small class="control-notice"></small>*}
                            </div>

                            <div class="form-group">
                                <div class="input-group">
                                    <label class="input-group-addon" for="profile-sex">{$aLang.settings_profile_sex}</label>
                                    <select name="profile_sex" id="profile_sex" class="form-control">
                                        <option value="man"
                                                {if E::User()->getProfileSex()=='man'}selected{/if}>{$aLang.settings_profile_sex_man}</option>
                                        <option value="woman"
                                                {if E::User()->getProfileSex()=='woman'}selected{/if}>{$aLang.settings_profile_sex_woman}</option>
                                        <option value="other"
                                                {if E::User()->getProfileSex()=='other'}selected{/if}>{$aLang.settings_profile_sex_other}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12 col-xs-8">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <label class="input-group-addon" for="profile-sex">
                                                <span class="hidden-xs visible-md visible-sm visible-lg">{$aLang.settings_profile_birthday}</span>
                                                <span class="visible-xs hidden-md hidden-sm hidden-lg"><i class="fa fa-child"></i></span>
                                            </label>
                                            <select name="profile_birthday_day" class="form-control">
                                                <option value="">{$aLang.date_day}</option>
                                                {section name=date_day start=1 loop=32 step=1}
                                                    <option value="{$smarty.section.date_day.index}"
                                                            {if $smarty.section.date_day.index==E::User()->getProfileBirthday()|date_format:"%d"}selected{/if}>{$smarty.section.date_day.index}</option>
                                                {/section}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xs-8">
                                    <div class="form-group single">
                                        {*<div class="input-group">*}
                                            <select name="profile_birthday_month" class="form-control">
                                                <option value="">{$aLang.date_month}</option>
                                                {section name=date_month start=1 loop=13 step=1}
                                                    <option value="{$smarty.section.date_month.index}"
                                                            {if $smarty.section.date_month.index==E::User()->getProfileBirthday()|date_format:"%m"}selected{/if}>{$aLang.month_array[$smarty.section.date_month.index][0]}</option>
                                                {/section}
                                            </select>
                                        {*</div>*}
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xs-8">
                                    <div class="form-group single">
                                           <select name="profile_birthday_year" class="form-control">
                                                <option value="">{$aLang.date_year}</option>
                                                {section name=date_year loop=$smarty.now|date_format:"%Y"+1 max=$smarty.now|date_format:"%Y"-2012+130 step=-1}
                                                    <option value="{$smarty.section.date_year.index}"
                                                            {if $smarty.section.date_year.index==E::User()->getProfileBirthday()|date_format:"%Y"}selected{/if}>{$smarty.section.date_year.index}</option>
                                                {/section}
                                            </select>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <textarea placeholder="{$aLang.settings_profile_about}" name="profile_about" id="profile_about" class="form-control"
                              rows="5">{E::User()->getProfileAbout()|escape:'html'}</textarea>
                </div>

                <div class="js-geo-select">
                    <div class="row">
                        <div class="col-md-24">

                            <div class="form-group">
                                <div class="input-group">
                                    <label class="input-group-addon" for="profile-sex">{$aLang.profile_place}</label>
                                    <script>
                                        $(function(){
                                            $('.js-geo-country').on('change', function() {
                                                        $('.js-geo-region-container').hide().find('select option').first().attr('selected', 'selected').trigger('change');
                                                        $('.js-geo-city-container').hide().find('select option').first().attr('selected', 'selected').trigger('change');
                                            });
                                        })
                                    </script>
                                    <select class="js-geo-country form-control" name="geo_country">
                                        <option value="">{$aLang.geo_select_country}</option>
                                        {if $aGeoCountries}
                                            {foreach $aGeoCountries as $oGeoCountry}
                                                <option value="{$oGeoCountry->getId()}"
                                                        {if $oGeoTarget AND $oGeoTarget->getCountryId()==$oGeoCountry->getId()}selected="selected"{/if}>{$oGeoCountry->getName()}</option>
                                            {/foreach}
                                        {/if}
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="col-md-12">
                            <div class="form-group single js-geo-region-container"   {if !$oGeoTarget OR !$oGeoTarget->getCountryId()}style="display:none;"{/if}>

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

                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group single js-geo-city-container" {if !$oGeoTarget OR !$oGeoTarget->getRegionId()}style="display:none;"{/if}>

                                <select class="js-geo-city form-control" name="geo_city">
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
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">{$oField->getTitle()|escape:'html'}</span>
                                <input type="text" class="span6" name="profile_user_field_{$oField->getId()}"
                                       id="profile_user_field_{$oField->getId()}" value="{$oField->getValue()|escape:'html'}"/>
                            </div>
                            {*<small class="control-notice"></small>*}
                        </div>
                    {/foreach}
                {/if}

                <br/><br/>


                <div class="panel-header">{$aLang.settings_profile_section_contacts}</div>

                <div class="mab12">
                    {$aUserFieldContactValues=E::User()->getUserFieldValues(true,array('contact','social'))}
                    <div id="user-field-contact-container">
                        <div class="row user-field-item js-user-field-item mab12" style="display:none;">
                            <div class="col-xs-8 col-lg-8">
                                <select name="profile_user_field_type[]" onchange="ls.userfield.changeFormField(this);" class="form-control hidden-select">
                                    {foreach from=$aUserFieldsContact item=oFieldAll}
                                        <option value="{$oFieldAll->getId()}">{$oFieldAll->getTitle()|escape:'html'}</option>
                                    {/foreach}
                                </select>
                            </div>
                            <div class="col-xs-14 col-lg-14">
                                <input type="text" name="profile_user_field_value[]" value="" class="form-control">
                            </div>
                            <div class="col-xs-2 col-lg-2">
                                <a href="#" class="fa fa-times red mat8" title="{$aLang.user_field_delete}"
                                   onclick="return ls.userfield.removeFormField(this);"></a>
                            </div>
                        </div>
                        {foreach $aUserFieldContactValues as $oField}
                            <div class="row user-field-item js-user-field-item mab12">
                                <div class="col-xs-8 col-lg-8">
                                    <select name="profile_user_field_type[]" onchange="ls.userfield.changeFormField(this);" class="form-control">
                                        {foreach $aUserFieldsContact as $oFieldAll}
                                            <option value="{$oFieldAll->getId()}" {if $oFieldAll->getId()==$oField->getId()}selected="selected"{/if}>
                                                {$oFieldAll->getTitle()|escape:'html'}
                                            </option>
                                        {/foreach}
                                    </select>
                                </div>
                                <div class="col-xs-14 col-lg-14">
                                    <input type="text" name="profile_user_field_value[]" value="{$oField->getValue()|escape:'html'}" class="form-control">
                                </div>
                                <div class="col-xs-2 col-lg-2">
                                    <a href="#" class="fa fa-times red mat8" title="{$aLang.user_field_delete}"
                                       onclick="return ls.userfield.removeFormField(this);"></a>
                                </div>
                            </div>
                        {/foreach}
                    </div>

                    {if $aUserFieldsContact}
                        <button class="btn btn-light btn-big corner-no" onclick="return ls.userfield.addFormField();">
                            <span class="fa fa-sight"></span>
                            {$aLang.user_field_add}
                        </button>
                    {/if}
                </div>

                {***************************************************************************************}
                {hook run='form_settings_profile_end'}

                <button type="submit" name="submit_profile_edit" class="btn btn-blue btn-big corner-no mat8">
                {$aLang.settings_profile_submit}</button>

                <script type="text/javascript">
                    jQuery(function ($) {
                        $('#avatar-upload').file({ name: 'avatar' }).choose(function (e, input) {
                            ls.user.uploadAvatar(null, input);
                        });
                    });
                </script>
            </form>

            {hook run='settings_profile_end'}

        </div>

        <div class="panel-footer">
            {include file='menus/menu.settings.tpl'}
        </div>
    </div>

{/block}