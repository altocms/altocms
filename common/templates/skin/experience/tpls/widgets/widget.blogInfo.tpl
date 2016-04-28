 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<!-- Блок сайдбара -->
<div class="panel panel-default sidebar raised widget-blog-info">
    <div class="panel-body">
        <div class="panel-header">
            <i class="fa fa-comment-o"></i>{$aLang.widget_blog_info}
        </div>

        <div class="panel-content" id="widget_blog_info">

        </div>
    </div>
    <div class="panel-footer">
        <a href="{R::GetLink("blogs")}" class="link link-dual link-lead link-clear">
            <i class="fa fa-comments-o"></i>{$aLang.all_blogs}
        </a>
    </div>
</div>

<!-- Блок сайдбара -->
<div class="panel panel-default sidebar raised widget-blog-info-mark">
    <div class="panel-body">
        <div class="panel-header">
            <i class="fa fa-exclamation"></i>{$aLang.widget_blog_info_note}
        </div>

        <div class="panel-content">
            {$aLang.widget_blog_info_note_text}
        </div>
    </div>
</div>