{extends file='./blogs.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a class="btn btn-default {if $sMode=='all' || $sMode==''}active{/if}" href="{router page='admin'}content-mresources/list/all/">
            {$aLang.target_type_all}
        </a>
        {foreach $aTargetTypes as $sTargetType}
            <a class="btn btn-default {if $sMode==$sTargetType}active{/if}"
               href="{router page='admin'}content-mresources/list/{$sTargetType}/">
                {if (strpos($sTargetType, 'single-image-uploader') === 0)}
                    {$sTargetTypeTitle = str_replace('single-image-uploader', $aLang['target_type_single-image-uploader'], $sTargetType)}
                {else}
                    {if (strpos($sTargetType, 'plugin.') === 0)}
                        {$sTargetTypeTitle=E::ModuleLang()->Get("{$sTargetType}")}
                    {else}
                        {$sTargetTypeTitle=$aLang["target_type_{$sTargetType}"]}
                    {/if}
                {/if}
                {$sTargetTypeTitle|escape}
                {if !$sTargetTypeTitle}{$sTargetType|escape}{/if}
            </a>
        {/foreach}
    </div>
{/block}

{block name="content-body"}
    <div class="span12">

        <div class="b-wbox">
            <div class="b-wbox-content nopadding">
                <table class="table table-striped table-condensed mresources-list">
                    <thead>
                    <tr>
                        <th class="span1">ID</th>
                        <th>Date</th>
                        <th>User</th>
                        <th>Url</th>
                        <th>Preview</th>
                        <th>Targets</th>
                        <th></th>
                    </tr>
                    </thead>

                    <tbody>
                    {foreach $aMresources as $oMresource}
                        {$oUser = $oMresource->getUser()}
                        <tr>
                            <td class="number">{$oMresource->GetId()}</td>
                            <td class="center">
                                {$oMresource->GetDateAdd()}
                            </td>
                            <td class="name">
                                {if $oUser}
                                    <a href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>
                                {/if}
                            </td>
                            <td class="name">
                                {if $oMresource->IsLink()}
                                    <i class="icon icon-globe"></i>
                                {elseif $oMresource->IsType(ModuleMresource::TYPE_IMAGE)}
                                    <i class="icon icon-picture"></i>
                                {elseif $oMresource->IsType(ModuleMresource::TYPE_IMAGE)}
                                    <i class="icon icon-stop"></i>
                                {/if}
                                {$oMresource->GetPathUrl()}
                            </td>
                            <td>
                                {if $oMresource->IsGraphicFile() && $oMresource->GetImgUrl(100)}
                                    <img src="{$oMresource->GetImgUrl(100)}" alt="" class="i-img-preview-100x100"/>
                                {/if}
                            </td>
                            <td class="center">
                                {$oMresource->GetTargetsCount()}
                            </td>
                            <td>
                                {if !$oMresource->GetTargetsCount()}
                                    <a href="#" title="{$aLang.action.admin.delete}" class="tip-top i-block"
                                       onclick="return admin.confirmDelete('{$oMresource->getId()}', '{$oMresource->GetImgUrl(100)}');">
                                        <i class="icon icon-trash"></i>
                                    </a>
                                {else}
                                    <!-- i class="icon icon-trash disabled"></i -->
                                {/if}
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>

        {include file="inc.paging.tpl"}

    </div>
    <!-- modal -->
    <div class="modal fade in" id="modal-mresource_delete">
        <div class="modal-dialog">
            <div class="modal-content">

                <header class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h3>{$aLang.action.admin.mresource_delete_confirm}</h3>
                </header>

                <form action="" method="POST" class="uniform">
                    <div class="modal-body">
                        <p></p>

                        <p>{$aLang.action.admin.mresource_will_be_delete}</p>

                        <input type="hidden" name="cmd" value="delete"/>
                        <input type="hidden" name="mresource_id" value=""/>
                        <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
                        <input type="hidden" name="return-path" value="{Router::Url('link')}"/>
                    </div>

                    <div class="modal-footer">
                        <button class="btn" data-dismiss="modal" aria-hidden="true">{$aLang.text_cancel}</button>
                        <button type="submit" class="btn btn-primary">{$aLang.action.admin.delete}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /modal -->
    <script>
        var admin = admin || { };

        admin.confirmDelete = function(id, imgUrl) {
            var modal = $('#modal-mresource_delete');
            var form = modal.find('form');
            form.find('h3').text(ls.lang.get('action.admin.mresource_delete_confirm'));
            form.find('form p:first').html('<img src="' + imgUrl + '">');
            form.find('[name=mresource_id]').val(id);

            modal.modal('show');
            return false;
        }

    </script>
{/block}
