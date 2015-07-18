<script type="text/javascript">
    $(function () {
        $('.js-tags-help-button').click(function () {
            $(this).toggleClass('active');
            $('.js-tags-help-text').slideToggle();
            return false;
        });
    });
</script>

<a href="#" class="btn btn-default pull-right js-tags-help-button">
    <span title="{$aLang.blog_closed}" class="glyphicon glyphicon-question-sign text-muted"></span>
    {$aLang.topic_create_text_notice}
</a>

<div class="clearfix"></div>

<dl class="help clearfix js-tags-help-text">
    <div class="help-arrow i-right"></div>

    <dt class="help-col help-wide">
        <h4>{$aLang.tags_help_special}</h4>
    </dt>

    <dd class="help-col help-wide">
        <div class="help-item">
            <h5><a href="#" class="link-dotted js-tags-help-link">&lt;cut&gt;</a></h5>
            {$aLang.tags_help_special_cut}
        </div>
        <div class="help-item">
            <h5><a href="#" class="link-dotted js-tags-help-link">&lt;cut
                    name="{$aLang.tags_help_special_cut_name_example_name}"&gt;</a></h5>
            {$aLang.tags_help_special_cut_name}
        </div>
        <div class="help-item">
            <h5><a href="#" class="link-dotted js-tags-help-link" data-insert="&lt;video&gt;&lt;/video&gt;">&lt;video&gt;http://...&lt;/video&gt;</a>
            </h5>
            {$aLang.tags_help_special_video}
        </div>
        <div class="help-item">
            <h5><a href="#" class="link-dotted js-tags-help-link" data-insert="&lt;ls user=&quot;&quot; /&gt;">&lt;ls
                    user="{$aLang.tags_help_special_ls_user_example_user}" /&gt;</a></h5>
            {$aLang.tags_help_special_ls_user}
        </div>
    </dd>
    <br/>

    <dt class="help-col help-wide">
        <h4>{$aLang.tags_help_standart}</h4>
    </dt>

    <dd class="help-col help-left">
        <div class="help-item">
            <h5><a href="#" class="link-dotted js-tags-help-link">&lt;h4&gt;&lt;/h4&gt;</a></h5>
            <h5><a href="#" class="link-dotted js-tags-help-link">&lt;h5&gt;&lt;/h5&gt;</a></h5>
            <h5><a href="#" class="link-dotted js-tags-help-link">&lt;h6&gt;&lt;/h6&gt;</a></h5>
            {$aLang.tags_help_standart_h}
        </div>
        <div class="help-item">
            <h5><a href="#" class="link-dotted js-tags-help-link">&lt;img src="" /&gt;</a></h5>
            {$aLang.tags_help_standart_img}
        </div>
        <div class="help-item">
            <h5><a href="#" class="link-dotted js-tags-help-link" data-insert="&lt;a href=&quot;&quot;&gt;&lt;/a&gt;">&lt;a
                    href="http://..."&gt;{$aLang.tags_help_standart_a_example_href}&lt;/a&gt;</a></h5>
            {$aLang.tags_help_standart_a}
        </div>
        <div class="help-item">
            <h5><a href="#" class="link-dotted js-tags-help-link">&lt;b&gt;&lt;/b&gt;</a></h5>
            {$aLang.tags_help_standart_b}
        </div>
        <div class="help-item">
            <h5><a href="#" class="link-dotted js-tags-help-link">&lt;i&gt;&lt;/i&gt;</a></h5>
            {$aLang.tags_help_standart_i}
        </div>
        <div class="help-item">
            <h5><a href="#" class="link-dotted js-tags-help-link">&lt;s>&lt;/s&gt;</a></h5>
            {$aLang.tags_help_standart_s}
        </div>
        <div class="help-item">
            <h5><a href="#" class="link-dotted js-tags-help-link">&lt;u&gt;&lt;/u&gt;</a></h5>
            {$aLang.tags_help_standart_u}
        </div>
    </dd>

    <dd class="help-col help-right">
        <div class="help-item">
            <h5><a href="#" class="link-dotted js-tags-help-link">&lt;hr /&gt;</a></h5>
            {$aLang.tags_help_standart_hr}
        </div>
        <div class="help-item">
            <h5><a href="#" class="link-dotted js-tags-help-link">&lt;blockquote&gt;&lt;/blockquote&gt;</a></h5>
            {$aLang.tags_help_standart_blockquote}
        </div>
        <div class="help-item">
            <h5><a href="#" class="link-dotted js-tags-help-link">&lt;table>&lt;/table&gt;</a></h5>
            <h5><a href="#" class="link-dotted js-tags-help-link">&lt;th>&lt;/th&gt;</a></h5>
            <h5><a href="#" class="link-dotted js-tags-help-link">&lt;td>&lt;/td&gt;</a></h5>
            <h5><a href="#" class="link-dotted js-tags-help-link">&lt;tr>&lt;/tr&gt;</a></h5>
            {$aLang.tags_help_standart_table}
        </div>
        <div class="help-item">
            <h5><a href="#" class="link-dotted js-tags-help-link">&lt;ul&gt;&lt;/ul&gt;</a></h5>
            <h5><a href="#" class="link-dotted js-tags-help-link">&lt;li&gt;&lt;/li&gt;</a></h5>
            {$aLang.tags_help_standart_ul}
        </div>
        <div class="help-item">
            <h5><a href="#" class="link-dotted js-tags-help-link">&lt;ol&gt;&lt;/ol&gt;</a></h5>
            <h5><a href="#" class="link-dotted js-tags-help-link">&lt;li&gt;&lt;/li&gt;</a></h5>
            {$aLang.tags_help_standart_ol}
            &lt;ol&gt;.
        </div>
    </dd>
</dl>
