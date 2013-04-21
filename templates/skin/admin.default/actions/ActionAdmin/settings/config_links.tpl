{extends file='./config.tpl'}

{block name="content-body-formcontent"}
    <div class="b-wbox-header">
        <div class="b-wbox-header-title">{$aLang.action.admin.set_links_homepage}</div>
    </div>
    <div class="b-wbox-content">
        <div class="control-group">
            <label class="control-label">{$aLang.action.admin.set_links_homepage_label}</label>

            <div class="controls">
                <label>
                    <input type="radio" name="homepage" value="home" {if $sHomePageSelect == 'home'}checked{/if}/>
                    <span class="help-inline">{$aLang.action.admin.set_links_homepage_home}</span>
                </label>
                <label>
                    <input type="radio" name="homepage" value="index" {if $sHomePageSelect == 'index'}checked{/if}/>
                    <span class="help-inline">{$aLang.action.admin.set_links_homepage_index}</span>
                </label>
                <label>
                    <input type="radio" name="homepage" value="page" {if $sHomePageSelect == 'page'}checked{/if}/>
                    <span class="help-inline">{$aLang.action.admin.set_links_homepage_page}</span>
                    <select name="page_url">
                        {foreach $aPages as $oPage}
                            <option value="{$oPage->GetUrl()}"
                                    {if $oPage->GetUrl()==$sHomePageUrl}selected{/if}
                                    {if !$oPage->GetActive()}disabled="disabled"{/if}>
                                page/{$oPage->GetUrl()} - {$oPage->GetTitle()}
                            </option>
                        {/foreach}
                    </select>
                </label>
                {hook run='admin_select_homepage'}
            </div>
        </div>
    </div>
    <div class="b-wbox-header">
        <div class="b-wbox-header-title">{$aLang.action.admin.set_links_drafts}</div>
    </div>
    <div class="b-wbox-content">
        <div class="control-group">
            <label class="control-label">{$aLang.action.admin.set_links_drafts_enable}</label>

            <div class="controls">
                <label>
                    <input type="radio" name="draft_link" value="on" {if Config::Get('module.topic.draft_link')}checked{/if}/>
                    {$aLang.action.admin.word_yes}
                </label>
                <label>
                    <input type="radio" name="draft_link" value="off" {if !Config::Get('module.topic.draft_link')}checked{/if}/>
                    {$aLang.action.admin.word_no}
                </label>
                {hook run='admin_select_homepage'}
            </div>
        </div>
    </div>
{/block}