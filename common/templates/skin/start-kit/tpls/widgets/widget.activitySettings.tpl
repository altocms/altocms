{if E::IsUser()}
    <section class="panel panel-default widget widget-type-activity">
        <div class="panel-body">

            <header class="widget-header">
                <h3 class="widget-title">{$aLang.stream_block_config_title}</h3>
            </header>

            <div class="widget-content">
                <p class="text-muted">
                    <small>{$aLang.stream_settings_note_filter}</small>
                </p>

                <ul class="list-unstyled activity-settings-filter">
                    {foreach $aStreamEventTypes as $sType=>$aEventType}
                        {if !(Config::Get('module.stream.disable_vote_events') AND substr($sType, 0, 4) == 'vote')}
                            <li class="checkbox">
                                <label>
                                    <input class="streamEventTypeCheckbox"
                                           type="checkbox"
                                           id="strn_et_{$sType}"
                                           {if in_array($sType, $aStreamTypesList)}checked="checked"{/if}
                                           onClick="ls.stream.switchEventType('{$sType}')"/>
                                    {$langKey = "stream_event_type_`$sType`"}
                                    {$aLang.$langKey}
                                </label>
                            </li>
                        {/if}
                    {/foreach}
                </ul>
            </div>

        </div>
    </section>
{/if}
