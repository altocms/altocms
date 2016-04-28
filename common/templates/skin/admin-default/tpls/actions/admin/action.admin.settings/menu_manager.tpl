{extends file='_index.tpl'}

{block name="content-body"}

    <div class="span12">

        <div class="b-wbox">
            <div class="b-wbox-content nopadding">
                <table class="table menumanager-list">
                    <thead>
                    <tr>
                        <th>{$aLang.action.admin.menu_manager_id}</th>
                        <th>{$aLang.action.admin.menu_manager_description}</th>
                        <th>{$aLang.action.admin.menu_manager_actions}</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$aMenu item=oMenu}
                        {if !$oMenu}{continue}{/if}
                        <tr id="menumanager_{$oMenu->getId()}">
                            <td class="menumanager_id">{$oMenu->getId()|escape:"html"}</td>
                            <td class="menumanager_description">{$oMenu->getDescription()|escape:"html"}</td>
                            <td class="menumanager_actions">
                                <a href="{R::GetLink("admin")}settings-menumanager/edit/{$oMenu->getId()}/"
                                   title="{$aLang.action.admin.menu_manager_edit}"
                                   class="icon icon-note"></a>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

{/block}