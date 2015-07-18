<section class="panel panel-default widget">
    <div class="panel-body">

        <header class="widget-header">
            <h3 class="widget-title">{$aLang.blog_admin_user_add_header}</h3>
        </header>

        <div class="widget-content">
            <form onsubmit="return ls.blog.addInvite({$oBlogEdit->getId()});">
                <p class="text-muted">
                    <small>{$aLang.blog_admin_user_add_label}</small>
                </p>
                <div class="input-group">
                    <input type="text" id="blog_admin_user_add" name="add" class="form-control autocomplete-users-sep"/>
                        <span class="input-group-btn">
                            <button class="btn btn-default">{$aLang.blog_admin_user_invite}</button>
                        </span>
                </div>
            </form>

            <h4>{$aLang.blog_admin_user_invited}:</h4>

            <div id="invited_list_block">
                {if $aBlogUsersInvited}
                    <ul id="invited_list" class="list-unstyled text-muted">
                        {foreach $aBlogUsersInvited as $oBlogUser}
                            {$oUser=$oBlogUser->getUser()}
                            <li id="blog-invite-remove-item-{$oBlogEdit->getId()}-{$oUser->getId()}">
                                <a href="{$oUser->getProfileUrl()}" class="user">{$oUser->getDisplayName()}</a> -
                                <a href="#" onclick="return ls.blog.repeatInvite({$oUser->getId()}, {$oBlogEdit->getId()});"
                                   class="actions-edit">{$aLang.blog_user_invite_readd}</a> |
                                <a href="#" onclick="return ls.blog.removeInvite({$oUser->getId()}, {$oBlogEdit->getId()});"
                                   class="actions-delete">{$aLang.blog_user_invite_remove}</a>
                            </li>
                        {/foreach}
                    </ul>
                {/if}

                <span id="blog-invite-empty" class="text-muted"
                      {if $aBlogUsersInvited}style="display: none"{/if}>{$aLang.blog_admin_user_add_empty}</span>
            </div>
        </div>
    </div>
</section>
