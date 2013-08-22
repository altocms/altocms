{extends file='_index.tpl'}

{block name="content-bar"}
    <div class="btn-group">
        <a href="{router page='admin'}blogtypes/" class="btn"><i class="icon-chevron-left"></i></a>
    </div>
{/block}

{block name="content-body"}
    <form method="POST" enctype="multipart/form-data" class="form-horizontal uniform">
    <input type="hidden" name="security_ls_key" value="{$ALTO_SECURITY_KEY}"/>

    <div class="b-wbox">
    <div class="b-wbox-header">
        {if $sMode=='add'}
            <div class="b-wbox-header-title">{$aLang.action.admin.blogtypes_add_title}</div>
        {elseif $sMode=='edit'}
            <div class="b-wbox-header-title">{$aLang.action.admin.blogtypes_edit_title}
                : {$oBlogType->getName()|escape:'html'}</div>
        {/if}
    </div>
    <div class="b-wbox-header">
        <div class="b-wbox-header-title">{$aLang.action.admin.blogtypes_opt_title}</div>
    </div>
    <div class="b-wbox-content nopadding">

        <div class="control-group {if $aFormErrors.type_code}error{/if}">
            <label for="blogtypes_typecode" class="control-label">
                {$aLang.action.admin.blogtypes_typecode}
            </label>

            <div class="controls">
                <input type="text" name="blogtypes_typecode"
                       value="{$_aRequest.blogtypes_typecode}"
                       class="input-text" {if $sMode!='add'}readonly{/if}/>
                <span class="help-block">{$aLang.action.admin.blogtypes_typecode_notice}</span>
            </div>
        </div>

        <div class="control-group">
            {foreach $aLangList as $sLang}
                <label for="blogtypes_name" class="control-label">
                    {if $sLang@first}
                        {$aLang.action.admin.blogtypes_name}
                    {/if}
                    {if count($aLangList)>0}
                        [
                        <strong>{$sLang}</strong>
                        ]
                    {/if}
                </label>
                <div class="controls">
                    <input type="text" name="blogtypes_name[{$sLang}]"
                           value="{$_aRequest.blogtypes_name.$sLang}" class="input-text" readonly/>
                </div>
            {/foreach}
        </div>

        <div class="control-group">
            <label for="blogtypes_allow_add" class="control-label">
                {$aLang.action.admin.blogtypes_allow_add}
            </label>

            <div class="controls">
                <label>
                    <input type="radio" name="blogtypes_allow_add" value="0"
                           {if !$_aRequest.blogtypes_allow_add}checked{/if}/>
                    {$aLang.action.admin.word_no}
                </label>
                <label>
                    <input type="radio" name="blogtypes_allow_add" value="1"
                           {if $_aRequest.blogtypes_allow_add}checked{/if}/>
                    {$aLang.action.admin.word_yes}
                </label>
                <span class="help-block">{$aLang.action.admin.blogtypes_allow_add_notice}</span>
            </div>

            <label for="blogtypes_min_rating" class="control-label">
                {$aLang.action.admin.blogtypes_min_rating}
            </label>

            <div class="controls">
                <input type="text" name="blogtypes_min_rating"
                       value="{$_aRequest.blogtypes_min_rating}" class="input-text"/>
                <span class="help-block">{$aLang.action.admin.blogtypes_min_rating_notice}</span>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">
                {$aLang.action.admin.blogtypes_membership}
            </label>

            <div class="controls">
                <select name="blogtypes_membership">
                    <option value="{ModuleBlog::BLOG_USER_JOIN_NONE}" {if $_aRequest.blogtypes_membership==ModuleBlog::BLOG_USER_JOIN_NONE}selected{/if}>
                        {$aLang.action.admin.blog_membership_none}
                    </option>
                    <option value="{ModuleBlog::BLOG_USER_JOIN_FREE}" {if $_aRequest.blogtypes_membership==ModuleBlog::BLOG_USER_JOIN_FREE}selected{/if}>
                        {$aLang.action.admin.blog_membership_free}
                    </option>
                    <option value="{ModuleBlog::BLOG_USER_JOIN_REQUEST}" {if $_aRequest.blogtypes_membership==ModuleBlog::BLOG_USER_JOIN_REQUEST}selected{/if}>
                        {$aLang.action.admin.blog_membership_request}
                    </option>
                    <option value="{ModuleBlog::BLOG_USER_JOIN_INVITE}" {if $_aRequest.blogtypes_membership==ModuleBlog::BLOG_USER_JOIN_INVITE}selected{/if}>
                        {$aLang.action.admin.blog_membership_invite}
                    </option>
                </select>
                <span class="help-block">{$aLang.action.admin.blogtypes_membership_notice}</span>
            </div>
        </div>
    </div>

    <div class="b-wbox-header">
        <div class="b-wbox-header-title">{$aLang.action.admin.blogtypes_acl_title}</div>
    </div>
    <div class="b-wbox-content nopadding">
        <div class="control-group">
            <label class="control-label">
                {$aLang.action.admin.blogtypes_acl_write}:
            </label>

            <div class="controls">
                <select name="blogtypes_acl_write">
                    <option value="" {if !$_aRequest.blogtypes_acl_write}selected{/if}>
                        {$aLang.action.admin.blogtypes_acl_nobody}
                    </option>
                    <option value="{ModuleBlog::BLOG_USER_ACL_MEMBER}"
                            {if $_aRequest.blogtypes_acl_write==ModuleBlog::BLOG_USER_ACL_MEMBER}selected{/if}>
                        {$aLang.action.admin.blogtypes_acl_members}
                    </option>
                    <option value="{ModuleBlog::BLOG_USER_ACL_USER}"
                            {if $_aRequest.blogtypes_acl_write==ModuleBlog::BLOG_USER_ACL_USER}selected{/if}>
                        {$aLang.action.admin.blogtypes_acl_users}
                    </option>
                </select>
                <span id="blogtypes_acl_write_rate">
                    {$aLang.action.admin.blogtypes_with_rating_from}
                    <input type="text" name="blogtypes_min_rate_write" value="{$_aRequest.blogtypes_min_rate_write}"
                           class="input-text i-inline"/>
                </span>
                <span class="help-block">{$aLang.action.admin.blogtypes_acl_write_notice}</span>

            </div>

        </div>

        <div class="control-group">
            <label class="control-label">
                {$aLang.action.admin.blogtypes_acl_read}:
            </label>

            <div class="controls">
                <select name="blogtypes_acl_read">
                    <option value="" {if !$_aRequest.blogtypes_acl_read}selected{/if}>
                        {$aLang.action.admin.blogtypes_acl_nobody}
                    </option>
                    <option value="{ModuleBlog::BLOG_USER_ACL_MEMBER}"
                            {if $_aRequest.blogtypes_acl_read==ModuleBlog::BLOG_USER_ACL_MEMBER}selected{/if}>
                        {$aLang.action.admin.blogtypes_acl_members}
                    </option>
                    <option value="{ModuleBlog::BLOG_USER_ACL_USER}"
                            {if $_aRequest.blogtypes_acl_read==ModuleBlog::BLOG_USER_ACL_USER}selected{/if}>
                        {$aLang.action.admin.blogtypes_acl_users}
                    </option>
                    <option value="{ModuleBlog::BLOG_USER_ACL_GUEST}"
                            {if $_aRequest.blogtypes_acl_read==ModuleBlog::BLOG_USER_ACL_GUEST}selected{/if}>
                        {$aLang.action.admin.blogtypes_acl_guests}
                    </option>
                </select>
                <span id="blogtypes_acl_read_rate">
                    {$aLang.action.admin.blogtypes_with_rating_from}
                    <input type="text" name="blogtypes_min_rate_read" value="{$_aRequest.blogtypes_min_rate_read}"
                           class="input-text i-inline"/>
                </span>
                <span class="help-block">{$aLang.action.admin.blogtypes_acl_read_notice}</span>

            </div>
        </div>

        <div class="control-group">
            <label class="control-label">
                {$aLang.action.admin.blogtypes_acl_comment}:
            </label>

            <div class="controls">
                <select name="blogtypes_acl_comment">
                    <option value="" {if !$_aRequest.blogtypes_acl_comment}selected{/if}>
                        {$aLang.action.admin.blogtypes_acl_nobody}
                    </option>
                    <option value="{ModuleBlog::BLOG_USER_ACL_MEMBER}"
                            {if $_aRequest.blogtypes_acl_comment==ModuleBlog::BLOG_USER_ACL_MEMBER}selected{/if}>
                        {$aLang.action.admin.blogtypes_acl_members}
                    </option>
                    <option value="{ModuleBlog::BLOG_USER_ACL_USER}"
                            {if $_aRequest.blogtypes_acl_comment==ModuleBlog::BLOG_USER_ACL_USER}selected{/if}>
                        {$aLang.action.admin.blogtypes_acl_users}
                    </option>
                </select>
                <span id="blogtypes_acl_comment_rate">
                    {$aLang.action.admin.blogtypes_with_rating_from}
                    <input type="text" name="blogtypes_min_rate_comment" value="{$_aRequest.blogtypes_min_rate_comment}"
                           class="input-text i-inline"/>
                </span>
                <span class="help-block">{$aLang.action.admin.blogtypes_acl_comment_notice}</span>

            </div>

        </div>

        <div class="control-group">
            <label class="control-label">{$aLang.action.admin.widget_active}</label>

            <div class="controls">
                <label>
                    <input type="radio" name="blogtypes_active" value="1"
                           {if $_aRequest.blogtypes_active}checked="checked"{/if}> {$aLang.action.admin.word_yes}
                </label>
                <label>
                    <input type="radio" name="blogtypes_active" value="0"
                           {if !$_aRequest.blogtypes_active}checked="checked"{/if}> {$aLang.action.admin.word_no}
                </label>
            </div>
        </div>

    </div>

    <div class="b-wbox-header">
        <div class="b-wbox-header-title">{$aLang.action.admin.blogtypes_content_title}</div>
    </div>
    <div class="b-wbox-content nopadding">
        <div class="control-group">
            <label class="control-label">
                {$aLang.action.admin.blogtypes_show_title}
            </label>

            <div class="controls">
                <label>
                    <input type="radio" name="blogtypes_show_title" value="0"
                           {if !$_aRequest.blogtypes_show_title}checked{/if}/>
                    {$aLang.action.admin.word_no}
                </label>
                <label>
                    <input type="radio" name="blogtypes_show_title" value="1"
                           {if $_aRequest.blogtypes_show_title}checked{/if}/>
                    {$aLang.action.admin.word_yes}
                </label>
                <span class="help-block">{$aLang.action.admin.blogtypes_show_title_notice}</span>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">
                {$aLang.action.admin.blogtypes_index_content}
            </label>

            <div class="controls">
                <label>
                    <input type="radio" name="blogtypes_index_content" value="0"
                           {if !$_aRequest.blogtypes_index_content}checked{/if}/>
                    {$aLang.action.admin.word_no}
                </label>
                <label>
                    <input type="radio" name="blogtypes_index_content" value="1"
                           {if $_aRequest.blogtypes_index_content}checked{/if}/>
                    {$aLang.action.admin.word_yes}
                </label>
                <span class="help-block">{$aLang.action.admin.blogtypes_index_content_notice}</span>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">
                {$aLang.action.admin.blogtypes_contenttypes}:
            </label>

            <div class="controls">
                <select name="blogtypes_contenttype">
                    <option value="">
                        {$aLang.action.admin.blogtypes_contenttypes_any}
                    </option>
                    {foreach $aContentTypes as $sContentName=>$oContentType}
                        <option value="{$sContentName}"
                                {if $_aRequest.blogtypes_contenttype==$sContentName}selected{/if}>
                            {$sContentName}
                        </option>
                    {/foreach}
                </select>
                <span class="help-block">{$aLang.action.admin.blogtypes_contenttypes_notice}</span>

            </div>
        </div>
    </div>

    <div class="b-wbox-content nopadding">
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"
                    name="submit_type_add">{$aLang.action.admin.contenttypes_submit}</button>
            {if $sEvent=='add'}
                <p><span class="help-block">{$aLang.action.admin.contenttypes_afteradd}</span></p>
            {/if}
        </div>
    </div>
    </div>
    </form>
    <script>
        var ls = ls || { };
        ls.admin = ls.admin || { };
        ls.admin.blogTypeAclSelect = function (element) {
            var elRate = $('#' + $(element).attr('name') + '_rate');
            console.log(elRate);
            if ($(element).val() <= 1) {
                elRate.css('visibility', 'hidden');
            } else {
                elRate.css('visibility', '');
            }
        }

        $(function () {
            $('select[name^=blogtypes_acl]').each(function () {
                ls.admin.blogTypeAclSelect(this);
                $(this).change(function () {
                    ls.admin.blogTypeAclSelect(this);
                });
            });
        });
    </script>
{/block}