{extends file="_profile.tpl"}

{block name="layout_profile_submenu"}
    {include file='menus/menu.profile_created.tpl'}
{/block}

{block name="layout_profile_content"}

    {if $aNotes}
        <div class="profile-notes">
            <table class="table table-profile-notes" cellspacing="0">
                {foreach $aNotes as $oNote}
                    <tr>
                        <td class="cell-username"><a href="{$oNote->getTargetUser()->getProfileUrl()}"
                                                     class="user">{$oNote->getTargetUser()->getDisplayName()}</a></td>
                        <td class="cell-note">{$oNote->getText()}</td>
                        <td class="small text-muted cell-date">{date_format date=$oNote->getDateAdd() format="j F Y"}</td>
                    </tr>
                {/foreach}
            </table>
        </div>
    {else}
        <div class="notice-empty">{$aLang.user_note_list_empty}</div>
    {/if}

    {include file='paging.tpl' aPaging=$aPaging}

{/block}
