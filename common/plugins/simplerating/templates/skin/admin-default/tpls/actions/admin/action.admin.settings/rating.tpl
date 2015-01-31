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

    <form method="post" action="" enctype="multipart/form-data" id="branding-setting" class="form-vertical uniform">
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

        <br/><br/><br/>
        <div class="row">
            <div class="control-group">
                <label for="acl_vote_user_simplerating" class="control-label">
                    {$aLang.plugin.simplerating.acl_vote}:
                </label>
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
        <div class="row">
            <div class="control-group">
                <label for="user_notice" class="control-label">
                    {$aLang.plugin.simplerating.user_notice}:
                </label>
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
        <div class="row">
            <div class="control-group">
                <label for="comment_notice" class="control-label">
                    {$aLang.plugin.simplerating.comment_notice}:
                </label>
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
        <div class="row">
            <div class="control-group">
                <label for="blog_notice" class="control-label">
                    {$aLang.plugin.simplerating.blog_notice}:
                </label>
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
        <div class="row">
            <div class="control-group">
                <label for="topic_notice" class="control-label">
                    {$aLang.plugin.simplerating.topic_notice}:
                </label>
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
        <div class="row">
            <div class="control-group">
                <label for="topic_rating_sum" class="control-label">
                    {$aLang.plugin.simplerating.personal_recalc}:
                </label>
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