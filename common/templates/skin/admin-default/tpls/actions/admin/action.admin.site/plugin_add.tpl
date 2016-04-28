{extends file='_index.tpl'}

{block name="content-body"}

<div class="span12">
    <div class="btn-group">
        <a href="{R::GetLink("admin")}site-plugins/add/" class="btn btn-primary tip-top"
           title="{$aLang.action.admin.plugin_load}"><i class="icon icon-plus"></i></a>
    </div>

    <div class="btn-group">
        <a class="btn {if $sMode=='all' || $sMode==''}active{/if}" href="{R::GetLink("admin")}site-plugins/list/all/">
            {$aLang.action.admin.all_plugins}
        </a>
        <a class="btn {if $sMode=='active'}active{/if}" href="{R::GetLink("admin")}site-plugins/list/active/">
            {$aLang.action.admin.active_plugins}
        </a>
        <a class="btn {if $sMode=='inactive'}active{/if}" href="{R::GetLink("admin")}site-plugins/list/inactive/">
            {$aLang.action.admin.inactive_plugins}
        </a>
    </div>

    <form action="{R::GetLink("admin")}plugins/" method="post" id="form_plugins_list">
        <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>

        <div class="b-wbox">
            <div class="b-wbox-content nopadding">
            </div>
        </div>
    </form>
</div>

{/block}
