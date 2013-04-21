{extends file='_index.tpl'}

{block name="content-body"}
    <div class="span12">
        <div class="b-wbox">
            <div class="b-wbox-content">
                {$sMessage}
            </div>
        </div>
    </div>

    {if $bActionEnable}
        <form action="" method="post">
            <input type="hidden" name="security_ls_key" value="{$ALTO_SECURITY_KEY}"/>

            <div class="span12 b-form-actions">

                <div class="navbar navbar-inner">
                    <input type="submit" name="comments_tree_submit" value="{$aLang.action.admin.execute}"
                           class="btn btn-primary pull-right"/>
                </div>
            </div>
        </form>
    {/if}
{/block}