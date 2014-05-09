{extends file="_index.tpl"}

{block name="layout_content"}
    {include file='menus/menu.blog_edit.tpl'}

    {if $aBlogUsers}
        <form method="post" enctype="multipart/form-data" class="mb-20">
            <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>

            <table class="table table-users">
                <thead>
                <tr>
                    <th class="cell-name">{$aLang.blog_admin_users}</th>
                    <th class="ta-c">{$aLang.blog_admin_users_administrator}</th>
                    <th class="ta-c">{$aLang.blog_admin_users_moderator}</th>
                    <th class="ta-c">{$aLang.blog_admin_users_reader}</th>
                    <th class="ta-c">{$aLang.blog_admin_users_bun}</th>
                </tr>
                </thead>

                <tbody>
                {foreach $aBlogUsers as $oBlogUser}
                    {$oUser=$oBlogUser->getUser()}
                    <tr>
                        <td class="cell-name">
                            <a href="{$oUser->getProfileUrl()}">
                                <img src="{$oUser->getAvatarUrl(24)}" alt="avatar" class="avatar"/>
                            </a>

                            <p class="username word-wrap">
                                <a href="{$oUser->getProfileUrl()}">{$oUser->getDisplayName()}</a>
                            </p>
                        </td>

                            <td class="ta-c">
                                {if ($oUser->getId()==E::UserId() AND $oBlogUser->getIsAdministrator()) OR $oUser->getId()!=E::UserId()}
                                <input type="radio" name="user_rank[{$oUser->getId()}]"
                                                    value="administrator"
                                                    {if $oBlogUser->getIsAdministrator()}checked{/if} />
                                {/if}
                            </td>
                            <td class="ta-c">
                                {if ($oUser->getId()==E::UserId() AND $oBlogUser->getIsModerator()) OR $oUser->getId()!=E::UserId()}
                                <input type="radio" name="user_rank[{$oUser->getId()}]" value="moderator"
                                                    {if $oBlogUser->getIsModerator()}checked{/if} />
                                {/if}
                            </td>
                            <td class="ta-c">
                                {if $oUser->getId() != E::UserId()}
                                <input type="radio" name="user_rank[{$oUser->getId()}]" value="reader"
                                                    {if $oBlogUser->getUserRole()==$BLOG_USER_ROLE_USER}checked{/if} />
                                {/if}
                            </td>
                            <td class="ta-c">
                                {if $oUser->getId() != E::UserId()}
                                <input type="radio" name="user_rank[{$oUser->getId()}]" value="ban"
                                                    {if $oBlogUser->getUserRole()==$BLOG_USER_ROLE_BAN}checked{/if} />
                                {/if}
                            </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>

            <button type="submit" name="submit_blog_admin" class="btn btn-success">
                {$aLang.blog_admin_users_submit}
            </button>
        </form>
        {include file='commons/common.pagination.tpl' aPaging=$aPaging}
    {else}
        {$aLang.blog_admin_users_empty}
    {/if}

{/block}
