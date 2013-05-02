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
        <div class="b-wbox-header-title">{$aLang.action.admin.set_links_topics}</div>
    </div>
    <div class="b-wbox-content">
        <div class="control-group">
            <label class="control-label">{$aLang.action.admin.set_links_topics_mode}</label>

            <div class="controls">
                <label>
                    <span class="span4">
                        <input type="radio" name="topic_link" value="ls" {if $sPermalinkMode == 'ls'}checked{/if}/>
                        {$aLang.action.admin.set_links_topics_mode_ls}
                    </span>
                    <span class="span8 b-topic-url-demo">
                    </span>
                </label>
                <label>
                    <span class="span4">
                        <input type="radio" name="topic_link" value="id" {if $sPermalinkMode == 'id'}checked{/if}/>
                        {$aLang.action.admin.set_links_topics_mode_id}
                    </span>
                    <span class="span8 b-topic-url-demo">
                        {Config::Get('path.root.url')}123
                    </span>
                </label>
                <label>
                    <span class="span4">
                        <input type="radio" name="topic_link" value="day_name" {if $sPermalinkMode == 'day_name'}checked{/if}/>
                        {$aLang.action.admin.set_links_topics_mode_day_name}
                    </span>
                    <span class="span8 b-topic-url-demo">
                        {Config::Get('path.root.url')}2013/04/28/sample-topic/
                    </span>
                </label>
                <label>
                    <span class="span4">
                        <input type="radio" name="topic_link" value="month_name" {if $sPermalinkMode == 'month_name'}checked{/if}/>
                        {$aLang.action.admin.set_links_topics_mode_month_name}
                    </span>
                    <span class="span8 b-topic-url-demo">
                        {Config::Get('path.root.url')}2013/04/sample-topic/
                    </span>
                </label>
                <label>
                    <span class="span4">
                        <input type="radio" name="topic_link" value="custom" {if $sPermalinkMode == 'custom'}checked{/if}/>
                        {$aLang.action.admin.set_links_topics_mode_custom}
                    </span>
                    <span class="span8">
                        <input type="text" name="topic_link_url" value="{$sPermalinkUrl}" />
                    </span>
                </label>
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
            </div>
        </div>
    </div>

{/block}

{block name="content-body" append}
    <div class="span12">

        <form action="" method="POST" class="form-horizontal uniform" enctype="multipart/form-data">
            <input type="hidden" name="security_ls_key" value="{$ALTO_SECURITY_KEY}"/>
            <input type="hidden" name="adm_cmd" value="generate_topics_url"/>
            <div class="b-wbox">
                <div class="b-wbox-content nopadding">
                    <div class="b-wbox-header">
                        <div class="b-wbox-header-title">{$aLang.action.admin.set_links_generate}</div>
                    </div>
                    <div class="b-wbox-content">
                        <p>
                            {$aLang.action.admin.set_links_generate_text}
                        </p>
                        <p>
                            {$aLang.action.admin.set_links_generate_count}
                            <strong>{$nTopicsWithoutUrl}</strong>
                        </p>
                    </div>
                </div>
            </div>

            <div class="navbar navbar-inner">
                <input type="submit" name="submit_data_save" value="{$aLang.action.admin.set_links_generate_button}"
                       class="btn pull-right"/>
            </div>
        </form>
    </div>
{/block}