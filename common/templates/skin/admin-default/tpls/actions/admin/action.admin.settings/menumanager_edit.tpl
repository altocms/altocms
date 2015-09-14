{extends file='_index.tpl'}

{block name="content-bar"}
    <script>
            var addForm = {
                show: function(){
                    var form = $('#new_menu_item');

                    form.slideDown('100');

                    return false;
                }
            };
    </script>

        <div class="btn-group">
            <a href="#" class="btn btn-primary tip-top"
               onclick="return addForm.show();"
               title="{$aLang.action.admin.menu_manager_actions_add}"><i class="icon icon-plus"></i></a>

            {if $isSubMenu}
                <a href="{router page='admin'}settings-menumanager/reset/{$oMenu->getId()}/" class="btn btn-danger tip-top"
                   title=""><i class="icon icon-remove"></i>&nbsp;{$aLang.action.admin.menu_manager_actions_remove_submenu}</a>
            {else}
                <a href="{router page='admin'}settings-menumanager/reset/{$oMenu->getId()}/" class="btn btn-primary tip-top"
                   title=""><i class="icon icon-reload"></i>&nbsp;{$aLang.action.admin.menu_manager_actions_reset}</a>
            {/if}
        </div>

    <div class="b-wbox" style="display: none;" id="new_menu_item">
        <div class="b-wbox-content nopadding">


            <div class="b-wbox-header">
                <div class="b-wbox-header-title">{$aLang.action.admin.menu_manager_actions_create}</div>
            </div>

            <form action="" method="post">

                <table>
                    <tr>
                        <td>
                            <div class="b-wbox-content">
                                <div class="control-group">
                                    <label for="menu-item-title" class="control-label">{$aLang.action.admin.menu_manager_item_title}</label>
                                    <div class="controls"><input type="text" id="menu-item-title"  name="menu-item-title" value=""></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="b-wbox-content">
                                <div class="control-group">
                                    <label for="menu-item-link" class="control-label">{$aLang.action.admin.menu_manager_item_link}</label>
                                    <div class="controls"><input type="text" id="menu-item-link"  name="menu-item-link" value=""></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="control-group">
                                <label for="menu-item-place" class="control-label">
                                    {$aLang.action.admin.menu_manager_item_place}:
                                </label>

                                <div class="controls">
                                    <select name="menu-item-place" id="menu-item-place" class="input-text">
                                        <option value="root_item" selected>{$aLang.action.admin.menu_manager_item_root}</option>
                                        {if $aItems=$oMenu->getItems()}
                                            {foreach from=$aItems item=oItem}
                                                {if !$oItem}{continue}{/if}
                                                {if is_string($oItem)}{continue}{/if}
                                                {if $oItem->getMenuMode() != 'list'}{continue}{/if}
                                                <option id="new_list_item_{$oItem->getId()}" value="{$oItem->getId()}">{$aLang.action.admin.menu_manager_as_submenu} "{$oItem->getText()}"</option>
                                            {/foreach}
                                        {/if}
                                    </select>
                                </div>
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-primary btn-inline" name="submit_add_new_item" value="1" type="submit">{$aLang.action.admin.menu_manager_as_save}</button>
                        </td>
                    </tr>
                </table>
            </form>

        </div>
    </div>
{/block}


{block name="content-body"}
    <div class="b-wbox">
        <div class="b-wbox-content">


        <div class="b-wbox-header">
            <div class="b-wbox-header-title">{if $isSubMenu}{$isSubMenu}{else}{$oMenu->getDescription()}{/if}</div>
        </div>
        <div class="b-wbox-header">
            <h6>&nbsp;&nbsp;&nbsp;{$aLang.action.admin.menu_manager_edit_instruction}</h6>
            <h6>&nbsp;&nbsp;&nbsp;{$aLang.action.admin.menu_manager_edit_instruction_1}</h6>
        </div>

    <script>

        var fixHelper = function (e, ui) {
            ui.children().each(function () {
                $(this).width($(this).width());
            });
            return ui;
        };

        var sortSave = function (e, ui) {
            var tr = $('#sortable tbody.content tr');
            if (tr.length > 0) {
                var order = [];
                $.each(tr, function (index, value) {
                    order.push({ 'id': $(value).data('id')});
                });
                var data = {
                    'order': order,
                    'menu_id': "{$oMenu->getId()}"
                };

                ls.progressStart();
                ls.ajax(aRouter['admin'] + 'ajaxchangeordermenu/', data, function (response) {
                    ls.progressDone();
                    if (!response.bStateError) {
                        ls.msg.notice(response.sMsgTitle, response.sMsg);
                    } else {
                        ls.msg.error(response.sMsgTitle, response.sMsg);
                    }
                });
            }
        };

        $(function () {
            //$("#sortable tbody.content").sortable({
            //    helper: fixHelper
            //});
            $("#sortable tbody.content").disableSelection();

            $("#sortable tbody.content").sortable({
                helper: fixHelper,
                stop: sortSave
            });
        });

        var saveText = function (link) {
            var $link = $(link);
            var data = {
                'menu_id': "{$oMenu->getId()}",
                'item_id': $link.data('item_id'),
                'text': $link.prev().val()
            };

            ls.progressStart();
            ls.ajax(aRouter['admin'] + 'ajaxchangemenutext/', data, function (response) {
                ls.progressDone();
                if (!response.bStateError) {
                    ls.msg.notice(response.sMsgTitle, response.sMsg);
                    $link.parent().prev().find('span').text(response.text);
                } else {
                    ls.msg.error(response.sMsgTitle, response.sMsg);
                }
                $link.parent().hide().prev().show(); return false;
            });
        };

        var saveLink = function (link) {
            var $link = $(link);
            var data = {
                'menu_id': "{$oMenu->getId()}",
                'item_id': $link.data('item_id'),
                'text': $link.prev().val()
            };

            ls.progressStart();
            ls.ajax(aRouter['admin'] + 'ajaxchangemenulink/', data, function (response) {
                ls.progressDone();
                if (!response.bStateError) {
                    ls.msg.notice(response.sMsgTitle, response.sMsg);
                    $link.parent().prev().find('span').text(response.text);
                } else {
                    ls.msg.error(response.sMsgTitle, response.sMsg);
                }
                $link.parent().hide().prev().show(); return false;
            });
        };

        var removeItem = function (link) {
            var $link = $(link);
            var data = {
                'menu_id': "{$oMenu->getId()}",
                'item_id': $link.data('item_id')
            };

            ls.progressStart();
            ls.ajax(aRouter['admin'] + 'ajaxmenuitemremove/', data, function (response) {
                ls.progressDone();
                if (!response.bStateError) {
                    ls.msg.notice(response.sMsgTitle, response.sMsg);
                    $link.parents('tr').remove();
                    $('#new_list_item_' + data.item_id).remove();
                    $('select').trigger('refresh');
                } else {
                    ls.msg.error(response.sMsgTitle, response.sMsg);
                }
            });
            return false;
        };

        var changeDisplayItem = function (link) {
            var $link = $(link);
            var data = {
                'menu_id': "{$oMenu->getId()}",
                'item_id': $link.data('item_id')
            };
            ls.progressStart();
            ls.ajax(aRouter['admin'] + 'ajaxmenuitemdisplay/', data, function (response) {
                ls.progressDone();
                if (!response.bStateError) {
                    ls.msg.notice(response.sMsgTitle, response.sMsg);
                    $link.removeClass('icon-eye-close').removeClass('icon-eye-open').addClass(response.class);
                } else {
                    ls.msg.error(response.sMsgTitle, response.sMsg);
                }
            });
            return false;
        };

    </script>
    <div class="span12">

    <table class="table menumanager-list" id="sortable">
        <thead>
        <tr>
            <th>{$aLang.action.admin.menu_manager_id}</th>
            <th>{$aLang.action.admin.menu_manager_title}</th>
            <th>{$aLang.action.admin.menu_manager_link}</th>
            <th>{$aLang.action.admin.menu_manager_submenu}</th>
            <th>{$aLang.action.admin.menu_manager_actions}</th>
            <th></th>
        </tr>
        </thead>
        <tbody class="content">
        {if $aItems=$oMenu->getItems()}
            {foreach $aItems as $k=>$oItem}

                {if $oItem==''}
                    <tr id="menumanager_{$k}" data-id="{$k}">
                        <td class="menumanager_id">{$k|escape:"html"}</td>
                        <td class="menumanager_text" colspan="5">
                            {$aLang.action.admin.menu_manager_hook}
                        </td>
                    </tr>
                    {continue}
                {/if}
                {if $oItem->getMenuMode() != 'list'}{continue}{/if}


                <tr id="menumanager_{$oItem->getId()}" data-id="{$oItem->getId()}">
                    <td class="menumanager_id">{$oItem->getId()|escape:"html"}</td>
                    <td class="menumanager_text">
                        <span>
                            <span>{$oItem->getText()}</span>
                            <a href="#"
                               onclick="$(this).parent().hide().next().show(); return false;"
                               class="icon icon-note"></a>
                        </span>

                        <span class="real-input" style="display: none;">
                            <input type="text"
                                   value="{$oItem->getText()|escape:"html"}"
                                   name="menumanager_text_{$oItem->getId()}"
                                   id="menumanager_text_{$oItem->getId()}"
                                    />
                           <a href="#"
                              data-item_id="{$oItem->getId()}"
                              onclick="return saveText(this);"
                              class="icon icon-save"></a>
                        </span>
                    </td>
                    <td class="menumanager_link">
                         <span>
                            <span>{$oItem->getLink()}</span>
                            <a href="#"
                               onclick="$(this).parent().hide().next().show(); return false;"
                               class="icon icon-note"></a>
                        </span>

                        <span class="real-input" style="display: none;">
                            <input type="text"
                                   value="{$oItem->getLink()|escape:"html"}"
                                   name="menumanager_link_{$oItem->getId()}"
                                   id="menumanager_link_{$oItem->getId()}"
                                    />
                           <a href="#"
                              data-item_id="{$oItem->getId()}"
                              onclick="return saveLink(this);"
                              class="icon icon-save"></a>
                        </span>
                    </td>
                    <td class="menumanager_submenu">
                        {if $oItem->getItemSubmenu()}
                            <a href="{router page='admin'}settings-menumanager/edit/{$oItem->getItemSubmenu()}/"
                               class="">{$aLang.action.admin.menu_manager_edit_submenu}</a>
                        {/if}
                    </td>
                    <td class="menumanager_actions">
                        <a href="#"
                           onclick="return removeItem(this)"
                           data-item_id="{$oItem->getId()}"
                           title="{$aLang.action.admin.menu_manager_remove}"
                           class="icon icon-remove"></a>
                        <a href="#"
                           onclick="return changeDisplayItem(this)"
                           data-item_id="{$oItem->getId()}"
                           title="{$aLang.action.admin.menu_manager_change_display}"
                           class="icon {if $oItem->getDisplay()}icon-eye-open{else}icon-eye-close{/if}"></a>
                    </td>
                </tr>
            {/foreach}
        {else}
            <tr>
                <td>
                    {$aLang.action.admin.menu_manager_no_items}
                </td>
            </tr>
        {/if}
        </tbody>
    </table>

    </div>

        </div>
    </div>
{/block}