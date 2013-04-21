{extends file='_index.tpl'}

{block name="content-body"}
    <div class="span12">

        <div class="btn-group">
            <a href="#" class="btn btn-primary disabled"><i class="icon-plus-sign"></i></a>
        </div>

        <div class="btn-group">
            <a href="{router page='admin'}config/base/" class="btn {if $sMode=='base'}active{/if}">
                {$aLang.action.admin.settings_base}
            </a>
            <a href="{router page='admin'}config/edit/" class="btn {if $sMode=='edit'}active{/if}">
                {$aLang.action.admin.settings_edit}
            </a>
            <a href="{router page='admin'}config/links/" class="btn {if $sMode=='links'}active{/if}">
                {$aLang.action.admin.settings_links}
            </a>
            <a href="{router page='admin'}config/sys/" class="btn {if $sMode=='sys'}active{/if}">
                {$aLang.action.admin.settings_sys}
            </a>
            <a href="{router page='admin'}config/cssjs/" class="btn {if $sMode=='cssjs'}active{/if}">
                {$aLang.action.admin.settings_cssjs}
            </a>
            <!-- a href="{router page='admin'}config/acl/" class="btn {if $sMode=='acl'}active{/if}">
                {$aLang.action.admin.settings_acl}
            </a -->
        </div>

        <form action="" method="POST" class="form-horizontal uniform" enctype="multipart/form-data">
            <input type="hidden" name="security_ls_key" value="{$ALTO_SECURITY_KEY}"/>
            <div class="b-wbox">
                <div class="b-wbox-content nopadding">
                    {block name="content-body-formcontent"}
                    {/block}
                </div>
            </div>

            <div class="navbar navbar-inner">
                <input type="submit" name="submit_data_save" value="{$aLang.action.admin.save}"
                       class="btn btn-primary pull-right"/>
            </div>
        </form>
    </div>
{/block}