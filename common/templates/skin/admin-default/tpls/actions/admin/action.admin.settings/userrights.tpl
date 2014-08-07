{extends file='_index.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a href="#" class="btn btn-primary disabled"><i class="icon icon-plus"></i></a>
    </div>
{/block}

{block name="content-body"}

    <form method="POST" enctype="multipart/form-data" class="form-horizontal uniform">
    <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>

    <div class="b-wbox">
    <div class="b-wbox-header">
        <div class="b-wbox-header-title">{$aLang.action.admin.userrights_blogs_title}</div>
    </div>
    <div class="b-wbox-content nopadding">

        <div class="control-group">
            <label class="control-label">
                {$aLang.blog_user_administrators}:
            </label>

            <div class="controls">
                <label>
                    <input type="checkbox" name="userrights_administrator[control_users]" value="1"
                           {if $_aRequest.userrights_administrator.control_users}checked{/if}/>
                    {$aLang.action.admin.userrights_blogs_control_users}
                </label>
                <label>
                    <input type="checkbox" name="userrights_administrator[edit_blog]" value="1"
                           {if $_aRequest.userrights_administrator.edit_blog}checked{/if}/>
                    {$aLang.action.admin.userrights_blogs_edit_blog}
                </label>
                <label>
                    <input type="checkbox" name="userrights_administrator[edit_content]" value="1"
                           {if $_aRequest.userrights_administrator.edit_content}checked{/if}/>
                    {$aLang.action.admin.userrights_blogs_edit_content}
                </label>
                <label>
                    <input type="checkbox" name="userrights_administrator[delete_content]" value="1"
                           {if $_aRequest.userrights_administrator.delete_content}checked{/if}/>
                    {$aLang.action.admin.userrights_blogs_delete_content}
                </label>
                <label>
                    <input type="checkbox" name="userrights_administrator[edit_comment]" value="1"
                           {if $_aRequest.userrights_administrator.edit_comment}checked{/if}/>
                    {$aLang.action.admin.userrights_blogs_edit_comment}
                </label>
                <label>
                    <input type="checkbox" name="userrights_administrator[delete_comment]" value="1"
                           {if $_aRequest.userrights_administrator.delete_comment}checked{/if}/>
                    {$aLang.action.admin.userrights_blogs_delete_comment}
                </label>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">
                {$aLang.blog_user_moderators}:
            </label>

            <div class="controls">
                <label>
                    <input type="checkbox" name="userrights_moderator[control_users]" value="1"
                           {if $_aRequest.userrights_moderator.control_users}checked{/if}/>
                    {$aLang.action.admin.userrights_blogs_control_users}
                </label>
                <label>
                    <input type="checkbox" name="userrights_moderator[edit_blog]" value="1"
                           {if $_aRequest.userrights_moderator.edit_blog}checked{/if}/>
                    {$aLang.action.admin.userrights_blogs_edit_blog}
                </label>
                <label>
                    <input type="checkbox" name="userrights_moderator[edit_content]" value="1"
                           {if $_aRequest.userrights_moderator.edit_content}checked{/if}/>
                    {$aLang.action.admin.userrights_blogs_edit_content}
                </label>
                <label>
                    <input type="checkbox" name="userrights_moderator[delete_content]" value="1"
                           {if $_aRequest.userrights_moderator.delete_content}checked{/if}/>
                    {$aLang.action.admin.userrights_blogs_delete_content}
                </label>
                <label>
                    <input type="checkbox" name="userrights_moderator[edit_comment]" value="1"
                           {if $_aRequest.userrights_moderator.edit_comment}checked{/if}/>
                    {$aLang.action.admin.userrights_blogs_edit_comment}
                </label>
                <label>
                    <input type="checkbox" name="userrights_moderator[delete_comment]" value="1"
                           {if $_aRequest.userrights_moderator.delete_comment}checked{/if}/>
                    {$aLang.action.admin.userrights_blogs_delete_comment}
                </label>
            </div>
        </div>

    </div>

    <div class="b-wbox-content nopadding">
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"
                    name="submit_type_add">{$aLang.action.admin.save}</button>
            {if $sEvent=='add'}
                <p><span class="help-block">{$aLang.action.admin.content_afteradd}</span></p>
            {/if}
        </div>
    </div>
    </div>
    </form>

{/block}