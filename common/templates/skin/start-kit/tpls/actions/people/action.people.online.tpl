{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="people"}
{/block}

{block name="layout_content"}

    <div class="page-header">
        <div class=" header">{$aLang.people}</div>
    </div>

    {include file='commons/common.user_list.tpl' aUsersList=$aUsersLast}

{/block}
