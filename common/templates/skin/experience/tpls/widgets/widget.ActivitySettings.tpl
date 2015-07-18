 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if E::IsUser()}

    <script>
        $(function(){
            $('.activity-settings-filter input:checkbox').off('ifChanged').on('ifChanged', function(e) {
                $(this).trigger('change');
            })
        })
    </script>

<div class="panel panel-default sidebar raised widget widget-userfeed widget-userfeed-settings">
    <div class="panel-body">
        <div class="panel-header">
            <i class="fa fa-bars"></i>
            {$aLang.stream_block_config_title}
        </div>

        <div class="widget-content">
            <p class="text-muted">
                <small>{$aLang.stream_settings_note_filter}</small>
            </p>

            <ul class="list-unstyled activity-settings-filter">
                {foreach $aStreamEventTypes as $sType=>$aEventType}
                    {if !(Config::Get('module.stream.disable_vote_events') AND substr($sType, 0, 4) == 'vote')}
                        <li class="checkbox pal0">
                            <label>
                                <input class="streamEventTypeCheckbox"
                                       type="checkbox"
                                       id="strn_et_{$sType}"
                                       {if in_array($sType, $aStreamTypesList)}checked="checked"{/if}
                                       onchange="ls.stream.switchEventType('{$sType}')"/>&nbsp;
                                {$langKey = "stream_event_type_$sType"}
                                {$aLang.$langKey}
                            </label>
                        </li>
                    {/if}
                {/foreach}
            </ul>
        </div>

    </div>
</div>
{/if}