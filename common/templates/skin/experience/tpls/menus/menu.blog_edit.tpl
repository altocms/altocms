 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

<div class="panel panel-default panel-search raised">

    <div class="panel-body">
        <div class="panel-header">
            {$aLang.blog_admin}: <a class="link link-lead link-dark link-clear" href="{$oBlogEdit->getUrlFull()}">{$oBlogEdit->getTitle()|escape:'html'}</a>
        </div>

    </div>

    <div class="panel-footer">
        <a class="small link link-light-gray link-clear link-lead {if $sMenuItemSelect=='profile'}active{/if}" href="{router page='blog'}edit/{$oBlogEdit->getId()}/">
            <i class="fa fa-pencil"></i>&nbsp;{$aLang.blog_admin_profile}
        </a>
        <a class="small link link-light-gray link-clear link-lead {if $sMenuItemSelect=='admin'}active{/if}" href="{router page='blog'}admin/{$oBlogEdit->getId()}/">
            <i class="fa fa-users"></i>&nbsp;{$aLang.blog_admin_users}
        </a>

        {hook run='menu_blog_edit_admin_item'}

    </div>

    {hook run='menu_blog_edit'}

</div>