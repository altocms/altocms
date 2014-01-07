{extends file='_index.tpl'}

{block name="content-body"}
    <div class="span12">
        <form action="" method="POST" class="form-horizontal uniform" enctype="multipart/form-data">
            <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
            <input type="hidden" name="submit_data_save" value="1">
            <input type="hidden" name="lang_exclude" value="">

            <div class="b-wbox">
                <div class="b-wbox-header">
                    <div class="b-wbox-header-title">{$aLang.action.admin.set_lang_used}</div>
                </div>
                <div class="b-wbox-content nopadding">
                    <ul class="b-set-lang-allow">
                        {foreach $aLangAllow as $sLang=>$aLng}
                            <li class="b-set-lang-allow-item lang-allow-{$sLang}">
                                {if !$aLng.current}
                                <div class="b-set-lang-exclude pull-right" data-lang="{$sLang}">
                                    <i class="icon icon-remove-sign"></i>
                                </div>
                                {/if}
                                <label class="{if $aLng.current}b-set-lang-current{/if} input-radio">
                                    <input type="radio" name="lang_current" value="{$sLang}"
                                           {if $aLng.current}checked{/if}/>
                                    {$sLang} - {$aLng.name} - {$aLng.native}
                                </label>
                            </li>
                        {/foreach}
                    </ul>
                    <div class="form-actions">
                        <button class="b-set-lang-button btn btn-default">
                            <i class="icon icon-plus-sign"></i>
                            {$aLang.action.admin.include}
                        </button>
                        <button class="b-set-lang-change btn btn-primary">
                            <i class="icon icon-ok-circle"></i>
                            {$aLang.action.admin.save}
                        </button>
                    </div>
                </div>
            </div>

            <div class="b-wbox" style="display: none;">
                <div class="b-wbox-header">
                    <div class="b-set-lang-close"><i class="icon icon-remove"></i></div>
                    <div class="b-wbox-header-title">{$aLang.action.admin.set_lang_avail}</div>
                </div>
                <div class="b-wbox-content nopadding">
                    <ul class="b-set-lang-avail -box">
                        {foreach $aLanguages as $sLang=>$aLng}
                            <li class="b-set-lang-avail-item">
                                <label class="checkbox">
                                    <input type="checkbox" name="lang_allow[]" value="{$sLang}"/>
                                    {$sLang} - {$aLng.name} - {$aLng.native}
                                </label>
                            </li>
                        {/foreach}
                    </ul>
                </div>
            </div>
        </form>
    </div>

    <script>
        $(function () {
            $('.b-set-lang-button').click(function () {
                var section = $('.b-set-lang-avail').parents('.b-wbox');
                section.toggle();
                $(this).toggleClass('active');
                return false;
            });

            $('.b-set-lang-close').click(function () {
                var section = $('.b-set-lang-avail').parents('.b-wbox');
                section.hide();
                $('.b-set-lang-button').removeClass('active');
                return false;
            });

            $('.b-set-lang-exclude').click(function(){
                var lang = $(this).data('lang');
                $('.lang-allow-' + lang).hide();
                $('[name=lang_exclude]').val($('[name=lang_exclude]').val() + ',' + lang);
            });
        });
    </script>
{/block}