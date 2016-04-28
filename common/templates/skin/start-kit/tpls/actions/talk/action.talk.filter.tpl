<section class="panel panel-default widget widget-type-foldable widget-type-talk-search">
    <div class="panel-body">

        <header class="widget-header">
            <a href="#" class="link-dotted"
               onclick="jQuery('#block_talk_search_content').toggle(); return false;">{$aLang.talk_filter_title}</a>
        </header>

        <div class="widget-content" id="block_talk_search_content"
             {if $_aRequest.submit_talk_filter}style="display:block;" {/if}>
            <form action="{R::GetLink("talk")}" method="GET" name="talk_filter_form">
                <div class="form-group">
                    <label for="talk_filter_addressee">{$aLang.talk_filter_label_addressee}</label>
                    <input type="text" id="talk_filter_addressee" name="addressee" value="{$_aRequest.addressee}"
                           class="form-control js-autocomplete-users-sep"/>

                    <p class="help-block">
                        <small>{$aLang.talk_filter_notice_sender}</small>
                    </p>
                </div>

                <div class="form-group">
                    <label for="talk_filter_keyword">{$aLang.talk_filter_label_keyword}</label>
                    <input type="text" id="talk_filter_keyword" name="keyword" value="{$_aRequest.keyword}"
                           class="form-control"/>

                    <p class="help-block">
                        <small>{$aLang.talk_filter_notice_keyword}</small>
                    </p>
                </div>

                <div class="form-group">
                    <label for="talk_filter_keyword_text">{$aLang.talk_filter_label_keyword_text}</label>
                    <input type="text" id="talk_filter_keyword_text" name="keyword_text"
                           value="{$_aRequest.keyword_text}" class="form-control"/>

                    <p class="help-block">
                        <small>{$aLang.talk_filter_notice_keyword}</small>
                    </p>
                </div>

                <div class="form-group">
                    <label for="talk_filter_start">{$aLang.talk_filter_label_date}</label>

                    <div class="form-inline">
                        <div class="form-group">
                            <input type="text" id="talk_filter_start" name="start" value="{$_aRequest.start}"
                                   class="form-control date-picker" readonly="readonly"/>
                        </div>
                        &mdash;
                        <div class="form-group">
                            <input type="text" id="talk_filter_end" name="end" value="{$_aRequest.end}"
                                   class="form-control date-picker" readonly="readonly"/>
                        </div>
                    </div>
                </div>

                <div class="checkbox">
                    <label for="talk_filter_favourite">
                        <input type="checkbox" {if $_aRequest.favourite}checked="checked" {/if} name="favourite"
                               value="1" id="talk_filter_favourite"/>
                        {$aLang.talk_filter_label_favourite}
                    </label>
                </div>
                <br/>

                <input type="submit" name="submit_talk_filter" value="{$aLang.talk_filter_submit}"
                       class="btn btn-success"/>
                <input type="submit" name="" value="{$aLang.talk_filter_submit_clear}" class="btn btn-default"
                       onclick="return ls.talk.clearFilter();"/>
            </form>
        </div>

    </div>
</section>
