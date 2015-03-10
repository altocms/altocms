 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{extends file="_profile.tpl"}

{block name="layout_profile_submenu"}
    {include file='menus/menu.profile_created.tpl'}
{/block}

{block name="layout_profile_content"}

<div class="panel panel-default panel-table flat">

    <div class="panel-body">


    {if $aNotes}
        <div class="profile-notes">
            <table class="wall-table">
                {foreach $aNotes as $oNote}
                    <tr class="bob">
                        <td class="pab6 pat6 vac fs-small text-left">
                            <span>
                              <a href="{$oNote->getTargetUser()->getProfileUrl()}" class="tdn">
                                  <img class="bor32" src="{$oNote->getTargetUser()->getAvatarUrl(32)}" alt="{$oNote->getTargetUser()->getDisplayName()}" />
                              </a>
                              <a class="link link-lead" href="{$oNote->getTargetUser()->getProfileUrl()}">{$oNote->getTargetUser()->getDisplayName()}</a>
                            </span>


                        </td>
                        <td class="pab6 pat6 vac fs-small">{$oNote->getText()}</td>
                        <td class="text-muted pab6 pat6 nowrap vac fs-small text-right">{date_format date=$oNote->getDateAdd() format="j F Y"}</td>
                    </tr>
                {/foreach}
            </table>
        </div>
    {else}
        <div class="notice-empty">{$aLang.user_note_list_empty}</div>
    {/if}
    </div>
</div>

    {include file='commons/common.pagination.tpl' aPaging=$aPaging}

{/block}
