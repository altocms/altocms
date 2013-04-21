{extends file='_index.tpl'}

{block name="vars"}
    {$menu="people"}
{/block}

{block name="content"}

{include file='user_list.tpl' aUsersList=$aUsersLast}

{/block}