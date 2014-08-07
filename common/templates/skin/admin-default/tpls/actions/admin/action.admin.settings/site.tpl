{extends file='_index.tpl'}

{block name="content-bar"}
<div class="col-md-12">
    <a href="#" class="btn btn-primary pull-right disabled"><i class="ion-plus-round"></i></a>
<ul class="nav nav-tabs atlass">
    <li class="{if $sMode=='base'}active{/if}">
        <a href="{router page='admin'}settings-site/base/">
            {$aLang.action.admin.settings_base}
        </a>
	</li>
    <li class="{if $sMode=='edit'}active{/if}">
        <a href="{router page='admin'}settings-site/edit/">
            {$aLang.action.admin.settings_edit}
        </a>
	</li>
    <li class="{if $sMode=='links'}active{/if}">
        <a href="{router page='admin'}settings-site/links/">
            {$aLang.action.admin.settings_links}
        </a>
	</li>
	<li class="{if $sMode=='sys'}active{/if}">
        <a href="{router page='admin'}settings-site/sys/">
            {$aLang.action.admin.settings_sys}
        </a>
	</li>
	<li class="{if $sMode=='cssjs'}active{/if}">
        <a href="{router page='admin'}settings-site/cssjs/">
            {$aLang.action.admin.settings_cssjs}
        </a>
	</li>
        <!-- a href="{router page='admin'}settings-site/acl/" class="btn {if $sMode=='acl'}active{/if}">
                {$aLang.action.admin.settings_acl}
            </a -->
  </ul>
</div>
{/block}

{block name="content-body"}
    <form action="" method="POST" class="form-horizontal" enctype="multipart/form-data">
        <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>

        <div class="panel panel-default">
            <div class="panel-body">
                {block name="content-body-formcontent"}
                {/block}
            </div>
            <div class="panel-footer clearfix">
                <input type="submit" name="submit_data_save" value="{$aLang.action.admin.save}"
                       class="btn btn-primary pull-right"/>
            </div>
        </div>

    </form>
{/block}