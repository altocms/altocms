{extends file="_index.tpl"}

{block name="layout_vars"}
    {$sMainMenuItem='settings'}
{/block}

{block name="content-bar"}

{/block}

{block name="content-body"}

    <div class="span12">
    <div class="b-wbox">
    <div class="b-wbox-header">
        <h3 class="b-wbox-header-title">
            {$aLang.plugin.simplerating.admin_title}
        </h3>
    </div>
    <div class="b-wbox-content">
    <div class="b-wbox-content">

    <form method="post" action="" enctype="multipart/form-data" id="simplerating-setting" class="form-vertical uniform">
    <input type="hidden" name="security_ls_key" value="{$LIVESTREET_SECURITY_KEY}"/>


            <div class="control-group">
                <div class="controls">
                    <label>
                        <input type="checkbox"
                               id="rating_enabled"
                               name="rating_enabled"
                               value="1"
                               {if $_aRequest.rating_enabled}checked="checked"{/if}>
                        {$aLang.plugin.simplerating.rating_enabled}
                    </label>
                </div>
            </div>

        <br/><br/>
        <h4>{$aLang.plugin.simplerating.acl_notice}</h4>
        <label for="acl_vote_user_rating" class="control-label">
            {$aLang.plugin.simplerating.acl_vote}
        </label>
        <div class="row">
            <div class="control-group">
                <div class="controls">
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="acl_vote_user_rating" name="acl_vote_user_rating" value="{$_aRequest.acl_vote_user_rating}"  />
                        <span class="help-block">{$aLang.plugin.simplerating.acl_vote_user_rating_notice}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="acl_vote_topic_rating" name="acl_vote_topic_rating" value="{$_aRequest.acl_vote_topic_rating}"  />
                        <span class="help-block">{$aLang.plugin.simplerating.acl_vote_topic_rating_notice}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="acl_vote_blog_rating" name="acl_vote_blog_rating" value="{$_aRequest.acl_vote_blog_rating}"  />
                        <span class="help-block">{$aLang.plugin.simplerating.acl_vote_blog_rating_notice}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="acl_vote_comment_rating" name="acl_vote_comment_rating" value="{$_aRequest.acl_vote_comment_rating}"  />
                        <span class="help-block">{$aLang.plugin.simplerating.acl_vote_comment_rating_notice}</span>
                    </div>

                </div>
            </div>
        </div>

        <br/><br/>
        <h4 for="acl_vote_user_rating" class="control-label">
            {$aLang.plugin.simplerating.user_config}
        </h4>
        <div class="control-group">
            <div class="controls">
                <label>
                    <input type="checkbox"
                           id="user_vote"
                           name="user_vote"
                           value="1"
                           {if $_aRequest.user_vote}checked="checked"{/if}>
                    {$aLang.plugin.simplerating.user_vote}
                </label>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <label>
                    <input type="checkbox"
                           id="user_dislike"
                           name="user_dislike"
                           value="1"
                           {if $_aRequest.user_dislike}checked="checked"{/if}>
                    {$aLang.plugin.simplerating.user_dislike}
                </label>
            </div>
        </div>                <label for="user_notice" class="control-label">
            {$aLang.plugin.simplerating.user_notice}:
        </label>
        <div class="row">
            <div class="control-group">

                <div class="controls">
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="user_remove" name="user_remove" value="{$_aRequest.user_remove}"  />
                        <span class="help-block">{$aLang.plugin.simplerating.user_remove}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="user_add" name="user_add" value="{$_aRequest.user_add}"  />
                        <span class="help-block">{$aLang.plugin.simplerating.user_add}</span>
                    </div>

                </div>
            </div>
        </div>


        <br/><br/>
        <h4 for="acl_vote_comment_rating" class="control-label">
            {$aLang.plugin.simplerating.comment_config}
        </h4>
        <div class="control-group">
            <div class="controls">
                <label>
                    <input type="checkbox"
                           id="comment_vote"
                           name="comment_vote"
                           value="1"
                           {if $_aRequest.comment_vote}checked="checked"{/if}>
                    {$aLang.plugin.simplerating.comment_vote}
                </label>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <label>
                    <input type="checkbox"
                           id="comment_dislike"
                           name="comment_dislike"
                           value="1"
                           {if $_aRequest.comment_dislike}checked="checked"{/if}>
                    {$aLang.plugin.simplerating.comment_dislike}
                </label>
            </div>
        </div>                <label for="comment_notice" class="control-label">
            {$aLang.plugin.simplerating.comment_notice}:
        </label>
        <div class="row">
            <div class="control-group">

                <div class="controls">
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="comment_user_remove" name="comment_user_remove" value="{$_aRequest.comment_user_remove}"  />
                        <span class="help-block">{$aLang.plugin.simplerating.comment_user_remove}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="comment_user_add" name="comment_user_add" value="{$_aRequest.comment_user_add}"  />
                        <span class="help-block">{$aLang.plugin.simplerating.comment_user_add}</span>
                    </div>

                </div>
            </div>
        </div>


        <br/><br/>
        <h4 for="acl_vote_blog_rating" class="control-label">
            {$aLang.plugin.simplerating.blog_config}
        </h4>
        <div class="control-group">
            <div class="control-group">
                <div class="controls">
                    <label>
                        <input type="checkbox"
                               id="blog_vote"
                               name="blog_vote"
                               value="1"
                               {if $_aRequest.blog_vote}checked="checked"{/if}>
                        {$aLang.plugin.simplerating.blog_vote}
                    </label>
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <label>
                        <input type="checkbox"
                               id="blog_dislike"
                               name="blog_dislike"
                               value="1"
                               {if $_aRequest.blog_dislike}checked="checked"{/if}>
                        {$aLang.plugin.simplerating.blog_dislike}
                    </label>
                </div>
            </div>
        </div>                <label for="blog_notice" class="control-label">
            {$aLang.plugin.simplerating.blog_notice}:
        </label>
        <div class="row">
            <div class="control-group">

                <div class="controls">
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="blog_user_remove" name="blog_user_remove" value="{$_aRequest.blog_user_remove}"  />
                        <span class="help-block">{$aLang.plugin.simplerating.blog_user_remove}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="blog_add" name="blog_add" value="{$_aRequest.blog_add}"  />
                        <span class="help-block">{$aLang.plugin.simplerating.blog_add}</span>
                    </div>

                </div>
            </div>
        </div>


        <br/><br/>
        <h4 for="acl_vote_topic_rating" class="control-label">
            {$aLang.plugin.simplerating.topic_config}
        </h4>
        <div class="control-group">
            <div class="controls">
                <label>
                    <input type="checkbox"
                           id="topic_vote"
                           name="topic_vote"
                           value="1"
                           {if $_aRequest.topic_vote}checked="checked"{/if}>
                    {$aLang.plugin.simplerating.topic_vote}
                </label>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <label>
                    <input type="checkbox"
                           id="topic_dislike"
                           name="topic_dislike"
                           value="1"
                           {if $_aRequest.topic_dislike}checked="checked"{/if}>
                    {$aLang.plugin.simplerating.topic_dislike}
                </label>
            </div>
        </div>                <label for="topic_notice" class="control-label">
            {$aLang.plugin.simplerating.topic_notice}:
        </label>
        <div class="row">
            <div class="control-group">

                <div class="controls">
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="topic_user_remove" name="topic_user_remove" value="{$_aRequest.topic_user_remove}"  />
                        <span class="help-block">{$aLang.plugin.simplerating.topic_user_remove}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="topic_user_add" name="topic_user_add" value="{$_aRequest.topic_user_add}"  />
                        <span class="help-block">{$aLang.plugin.simplerating.topic_user_add}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="topic_add" name="topic_add" value="{$_aRequest.topic_add}"  />
                        <span class="help-block">{$aLang.plugin.simplerating.topic_add}</span>
                    </div>

                </div>
            </div>
        </div>


        <br/><br/>
        <h4 for="topic_rating_sum" class="control-label">
            {$aLang.plugin.simplerating.personal_recalc}
        </h4>
        <div class="row">
            <div class="control-group">
                <div class="controls">
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="topic_rating_sum" name="topic_rating_sum" value="{$_aRequest.topic_rating_sum}"  />
                        <span class="help-block">{$aLang.plugin.simplerating.topic_rating_sum}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="count_topic" name="count_topic" value="{$_aRequest.count_topic}"  />
                        <span class="help-block">{$aLang.plugin.simplerating.count_topic}</span>
                    </div>

                </div>
            </div>
        </div>

    <br/><br/><br/><br/>

    <input type="submit" name="submit_rating" value="{$aLang.plugin.simplerating.save}"/>
    <input type="submit" name="cancel" value="{$aLang.plugin.simplerating.cancel}"/>

    </form>
    </div>
    </div>
    </div>
    </div>
{/block}