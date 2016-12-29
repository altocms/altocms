{extends file='./_settings.tpl'}

{block name="content-body-formcontent"}
    <div class="b-wbox-header">
        <div class="b-wbox-header-title">{$aLang.action.admin.set_links_homepage}</div>
    </div>
    <div>
        <div class="control-group">
            <label class="control-label">{$aLang.action.admin.set_links_homepage_label}</label>

            <div class="controls">
                <label {if $sHomePageSelect == 'home'}class="checked"{/if}>
                    <input type="radio" name="homepage" value="home" {if $sHomePageSelect == 'home'}checked{/if}/>
                    {$aLang.action.admin.set_links_homepage_home}
                </label>
                <label {if $sHomePageSelect == 'index'}class="checked"{/if}>
                    <input type="radio" name="homepage" value="index" {if $sHomePageSelect == 'index'}checked{/if}/>
                    {$aLang.action.admin.set_links_homepage_index}
                </label>
                <label {if $sHomePageSelect == 'page'}class="checked"{/if}>
                    <input type="radio" name="homepage" value="page" {if $sHomePageSelect == 'page'}checked{/if}/>
                    {$aLang.action.admin.set_links_homepage_page}
                    <select name="page_url">
                        {foreach $aPages as $oPage}
                            <option value="{$oPage->getUrlFull()}"
                                    {if $oPage->getUrlFull()==$sHomePage}selected{/if}
                                    {if !$oPage->GetActive()}disabled="disabled"{/if}>
                                    {$oPage->getUrlPath()} :: {$oPage->GetTitle()}
                            </option>
                        {/foreach}
                    </select>
                </label>
                <label {if $sHomePageSelect == 'other'}class="checked"{/if}>
                    <input type="radio" name="homepage" value="other" {if $sHomePageSelect == 'other'}checked{/if}/>
                    {$aLang.action.admin.set_links_homepage_other}
                    <input name="other_url" value="{$sHomePageUrl}" />
                </label>
                {hook run='admin_select_homepage'}
            </div>
        </div>
    </div>
    <div class="b-wbox-header">
        <div class="b-wbox-header-title">{$aLang.action.admin.set_links_topics}</div>
    </div>
    <div>
        <div class="control-group">
            <label class="control-label">{$aLang.action.admin.set_links_topics_mode}</label>

            <div class="controls">
            <div class="-box">
                <label {if $sPermalinkMode == 'alto'}class="checked"{/if}>
                    <span class="span6">
                        <input type="radio" name="topic_link" value="alto" {if $sPermalinkMode == 'alto'}checked{/if}/>
                        {$aLang.action.admin.set_links_topics_mode_alto}
                    </span>
                    <span class="span6 b-topic-url-demo">
                        {Config::Get('path.root.url')}123.html
                    </span>
                    <span id="topic_link_alto" style="display: none;">%topic_id%.html</span>
                </label>

                <label {if $sPermalinkMode == 'friendly'}class="checked"{/if}>
                    <span class="span6">
                        <input type="radio" name="topic_link" value="friendly" {if $sPermalinkMode == 'friendly'}checked{/if}/>
                        {$aLang.action.admin.set_links_topics_mode_friendly}
                    </span>
                    <span class="span6 b-topic-url-demo">
                        {Config::Get('path.root.url')}sample-topic.html
                    </span>
                    <span id="topic_link_friendly" style="display: none;">%topic_url%.html</span>
                </label>

                <label {if $sPermalinkMode == 'friendly_id'}class="checked"{/if}>
                    <span class="span6">
                        <input type="radio" name="topic_link" value="friendly_id" {if $sPermalinkMode == 'friendly_id'}checked{/if}/>
                        {$aLang.action.admin.set_links_topics_mode_friendly_id}
                    </span>
                    <span class="span6 b-topic-url-demo">
                        {Config::Get('path.root.url')}123-sample-topic.html
                    </span>
                    <span id="topic_link_friendly_id" style="display: none;">%topic_url%.html</span>
                </label>

                <label {if $sPermalinkMode == 'ls'}class="checked"{/if}>
                    <span class="span6">
                        <input type="radio" name="topic_link" value="ls" {if $sPermalinkMode == 'ls'}checked{/if}/>
                        {$aLang.action.admin.set_links_topics_mode_ls}
                    </span>
                    <span class="span6 b-topic-url-demo">
                        {Config::Get('path.root.url')}blog/blog-name/123.html
                    </span>
                    <span id="topic_link_ls" style="display: none;">blog/%blog_url%/%topic_id%.html</span>
                </label>

                <label {if $sPermalinkMode == 'id'}class="checked"{/if}>
                    <span class="span6">
                        <input type="radio" name="topic_link" value="id" {if $sPermalinkMode == 'id'}checked{/if}/>
                        {$aLang.action.admin.set_links_topics_mode_id}
                    </span>
                    <span class="span6 b-topic-url-demo">
                        {Config::Get('path.root.url')}123
                    </span>
                    <span id="topic_link_id" style="display: none;">%topic_id%</span>
                </label>

                <label {if $sPermalinkMode == 'day_name'}class="checked"{/if}>
                    <span class="span6">
                        <input type="radio" name="topic_link" value="day_name" {if $sPermalinkMode == 'day_name'}checked{/if}/>
                        {$aLang.action.admin.set_links_topics_mode_day_name}
                    </span>
                    <span class="span6 b-topic-url-demo">
                        {Config::Get('path.root.url')}2013/04/28/sample-topic/
                    </span>
                    <span id="topic_link_day_name" style="display: none;">%year%/%month%/%day%/%topic_url%</span>
                </label>

                <label {if $sPermalinkMode == 'month_name'}class="checked"{/if}>
                    <span class="span6">
                        <input type="radio" name="topic_link" value="month_name" {if $sPermalinkMode == 'month_name'}checked{/if}/>
                        {$aLang.action.admin.set_links_topics_mode_month_name}
                    </span>
                    <span class="span6 b-topic-url-demo">
                        {Config::Get('path.root.url')}2013/04/sample-topic/
                    </span>
                    <span id="topic_link_month_name" style="display: none;">%year%/%month%/%topic_url%</span>
                </label>

                <label {if $sPermalinkMode == 'custom'}class="checked"{/if}>
                    <span class="span6">
                        <input type="radio" name="topic_link" value="custom" {if $sPermalinkMode == 'custom'}checked{/if}/>
                        {$aLang.action.admin.set_links_topics_mode_custom}
                    </span>
                    <span class="span6">
                        <input type="text" name="topic_link_url" value="{$sPermalinkUrl}" />
                    </span>
                </label>
            </div>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">
                <span class="link-dotted" onclick="$('#topic_link_help').slideToggle();">{$aLang.action.admin.set_links_topics_mode_help_title}</span>
            </label>

            <div class="controls">
                <div class="well" style="display: none;" id="topic_link_help">{$aLang.action.admin.set_links_topics_mode_help_text|nl2br}</div>
            </div>
        </div>

    </div>
    <div class="b-wbox-header">
        <div class="b-wbox-header-title">{$aLang.action.admin.set_links_drafts}</div>
    </div>
    <div>
        <div class="control-group">
            <label class="control-label">{$aLang.action.admin.set_links_drafts_enable}</label>

            <div class="controls">
                <label {if Config::Get('module.topic.draft_link')}class="checked"{/if}>
                    <input type="radio" name="draft_link" value="on" {if Config::Get('module.topic.draft_link')}checked{/if}/>
                    {$aLang.action.admin.word_yes}
                </label>
                <label {if !Config::Get('module.topic.draft_link')}class="checked"{/if}>
                    <input type="radio" name="draft_link" value="off" {if !Config::Get('module.topic.draft_link')}checked{/if}/>
                    {$aLang.action.admin.word_no}
                </label>
            </div>
        </div>
    </div>

<script>
    $(function () {
        $('input[name=topic_link]').change(function () {
            var s = $('#topic_link_' + $(this).val());
            if (s.length && s.text()) {
                $('input[name=topic_link_url]').val(s.text());
            }
            if ($(this).val() == 'custom') {
                $('input[name=topic_link_url]').focus();
            }
        });
    });
</script>
{/block}

{block name="content-body" append}
    <div class="span12">

        <form action="" method="POST" class="form-horizontal uniform" enctype="multipart/form-data">
            <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
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
                       class="btn btn-default pull-right"/>
            </div>
        </form>
    </div>
{/block}