{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="people"}
{/block}

{block name="layout_content"}

    <div class="page-header">
        <h1>{$aLang.people}</h1>
    </div>

    {include file='commons/common.user_list.tpl' aUsersList=$aUsersLast}

{/block}
