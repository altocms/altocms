 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="topics"}
{/block}

{block name="layout_pre_content"}
    {include file="actions/people/action.people.pre.tpl"}
{/block}

{block name="layout_content"}
    <div id="users-list-search" style="display:none;"></div>

    <div id="users-list-original">
        {include file='commons/common.user_list.tpl' aUsersList=$aUsersRegister bUsersUseOrder=true sUsersRootPage="{router page='people'}new/" bUsersUseOrder=false}
    </div>

    {include file='commons/common.pagination.tpl' aPaging=$aPaging}

{/block}
