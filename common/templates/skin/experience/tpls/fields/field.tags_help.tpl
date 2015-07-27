 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

 <script>
     $(function(){
         // Help-tags link
         $('.js-tags-help-link').on('click', function () {
             var text = $(this).text();
             $.markItUp({
                 replaceWith: function(m) {
                     return (m.selectionOuter || m.selection) + text
                 }
             });
             return false;
         });
     })
 </script>

<div class="bg-warning tags-about" style="display: none;">

    <h5>{$aLang.tags_help_special}</h5>

    <dl class="dl-horizontal dl-clear">
        <dt><a class="link link-lead link-blue js-tags-help-link" href="#">&lt;cut&gt;</a></dt>
        <dd>{$aLang.tags_help_special_cut}</dd>
    </dl>

    <dl class="dl-horizontal dl-clear">
        <dt><a class="link link-lead link-blue js-tags-help-link" href="#">&lt;cut name="{$aLang.tags_help_special_cut_name_example_name}"&gt;</a></dt>
        <dd>{$aLang.tags_help_special_cut_name}</dd>
    </dl>

    <dl class="dl-horizontal dl-clear">
        <dt><a class="link link-lead link-blue js-tags-help-link" href="#">&lt;video&gt;http://...&lt;/video&gt;</a></dt>
        <dd>{$aLang.tags_help_special_video}</dd>
    </dl>

    <dl class="dl-horizontal dl-clear">
        <dt><a class="link link-lead link-blue js-tags-help-link" href="#">&lt;ls user="{$aLang.tags_help_special_ls_user_example_user}" /&gt;</a></dt>
        <dd>{$aLang.tags_help_special_ls_user}</dd>
    </dl>




    <h5>{$aLang.tags_help_standart}</h5>

    <dl class="dl-horizontal dl-clear">
        <dt>
            <a class="link link-lead link-blue  js-tags-help-link" href="#">&lt;h4&gt;&lt;/h4&gt;</a><br/>
            <a class="link link-lead link-blue  js-tags-help-link" href="#">&lt;h5&gt;&lt;/h5&gt;</a><br/>
            <a class="link link-lead link-blue  js-tags-help-link" href="#">&lt;h6&gt;&lt;/h6&gt;</a>
        </dt>
        <dd>{$aLang.tags_help_standart_h}</dd>
    </dl>

    <dl class="dl-horizontal dl-clear">
        <dt><a class="link link-lead link-blue js-tags-help-link" href="#">&lt;img src="" /&gt</a></dt>
        <dd>{$aLang.tags_help_standart_img}</dd>
    </dl>

    <dl class="dl-horizontal dl-clear">
        <dt><a class="link link-lead link-blue js-tags-help-link" href="#">&lt;a href="http://..."&gt;Ссылка&lt;/a&gt;</a></dt>
        <dd>{$aLang.tags_help_standart_a}</dd>
    </dl>

    <dl class="dl-horizontal dl-clear">
        <dt><a class="link link-lead link-blue js-tags-help-link" href="#">&lt;b&gt;&lt;/b&gt;</a></dt>
        <dd>{$aLang.tags_help_standart_b}</dd>
    </dl>

    <dl class="dl-horizontal dl-clear">
        <dt><a class="link link-lead link-blue js-tags-help-link" href="#">&lt;i&gt;&lt;/i&gt;</a></dt>
        <dd>{$aLang.tags_help_standart_i}</dd>
    </dl>

    <dl class="dl-horizontal dl-clear">
        <dt><a class="link link-lead link-blue js-tags-help-link" href="#">&lt;s&gt;&lt;/s&gt;</a></dt>
        <dd>{$aLang.tags_help_standart_s}</dd>
    </dl>

    <dl class="dl-horizontal dl-clear">
        <dt><a class="link link-lead link-blue js-tags-help-link" href="#">&lt;u&gt;&lt;/u&gt;</a></dt>
        <dd>{$aLang.tags_help_standart_u}</dd>
    </dl>

    <dl class="dl-horizontal dl-clear">
        <dt><a class="link link-lead link-blue js-tags-help-link" href="#">&lt;hr /&gt;</a></dt>
        <dd>{$aLang.tags_help_standart_hr}</dd>
    </dl>

    <dl class="dl-horizontal dl-clear">
        <dt><a class="link link-lead link-blue js-tags-help-link" href="#">&lt;blockquote&gt;&lt;/blockquote&gt;</a></dt>
        <dd>{$aLang.tags_help_standart_blockquote}</dd>
    </dl>

    <dl class="dl-horizontal dl-clear">
        <dt>
            <a class="link link-lead link-blue  js-tags-help-link" href="#">&lt;table&gt;&lt;/table&gt;</a><br/>
            <a class="link link-lead link-blue  js-tags-help-link" href="#">&lt;th&gt;&lt;/th&gt;</a>,
            <a class="link link-lead link-blue  js-tags-help-link" href="#">&lt;td&gt;&lt;/td&gt;</a>,
            <a class="link link-lead link-blue  js-tags-help-link" href="#">&lt;tr&gt;&lt;/tr&gt;</a>
        </dt>
        <dd>{$aLang.tags_help_standart_table}</dd>
    </dl>

    <dl class="dl-horizontal dl-clear">
        <dt>
            <a class="link link-lead link-blue js-tags-help-link" href="#">&lt;ul&gt;&lt;/ul&gt;</a><br/>
            <a class="link link-lead link-blue js-tags-help-link" href="#">&lt;li&gt;&lt;/li&gt;</a>
        </dt>
        <dd>{$aLang.tags_help_standart_ul}</dd>
    </dl>

    <dl class="dl-horizontal dl-clear">
        <dt>
            <a class="link link-lead link-blue js-tags-help-link" href="#">&lt;ol&gt;&lt;/ol&gt;</a><br/>
            <a class="link link-lead link-blue js-tags-help-link" href="#">&lt;li&gt;&lt;/li&gt;</a>
        </dt>
        <dd>{$aLang.tags_help_standart_ol}</dd>
    </dl>

</div>