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
            {$aLang.plugin.rating.admin_title}
        </h3>
    </div>
    <div class="b-wbox-content">
    <div class="b-wbox-content">

    <form method="post" action="" enctype="multipart/form-data" id="rating-setting" class="form-vertical uniform">
    <input type="hidden" name="security_ls_key" value="{$LIVESTREET_SECURITY_KEY}"/>


            <div class="control-group">
                <div class="controls">
                    <label>
                        <input type="checkbox"
                               id="rating_enabled"
                               name="rating_enabled"
                               value="1"
                               {if $_aRequest.rating_enabled}checked="checked"{/if}>
                        {$aLang.plugin.rating.rating_enabled}
                    </label>
                </div>
            </div>

        <br/><br/>
        <h4>{$aLang.plugin.rating.acl_notice}</h4>
        <label for="acl_vote_user_rating" class="control-label">
            {$aLang.plugin.rating.acl_vote}
        </label>
        <div class="row">
            <div class="control-group">

                <div class="controls">
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="acl_vote_user_rating" name="acl_vote_user_rating" value="{$_aRequest.acl_vote_user_rating}"  />
                        <span class="help-block">{$aLang.plugin.rating.acl_vote_user_rating_notice}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="acl_vote_topic_rating" name="acl_vote_topic_rating" value="{$_aRequest.acl_vote_topic_rating}"  />
                        <span class="help-block">{$aLang.plugin.rating.acl_vote_topic_rating_notice}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="acl_vote_blog_rating" name="acl_vote_blog_rating" value="{$_aRequest.acl_vote_blog_rating}"  />
                        <span class="help-block">{$aLang.plugin.rating.acl_vote_blog_rating_notice}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="acl_vote_comment_rating" name="acl_vote_comment_rating" value="{$_aRequest.acl_vote_comment_rating}"  />
                        <span class="help-block">{$aLang.plugin.rating.acl_vote_comment_rating_notice}</span>
                    </div>

                </div>
            </div>
        </div>


        <br/><br/>
        <h4 for="acl_vote_user_rating" class="control-label">
            {$aLang.plugin.rating.user_config}
        </h4>
        <div class="control-group">
            <div class="controls">
                <label>
                    <input type="checkbox"
                           id="user_vote"
                           name="user_vote"
                           value="1"
                           {if $_aRequest.user_vote}checked="checked"{/if}>
                    {$aLang.plugin.rating.user_vote}
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
                    {$aLang.plugin.rating.user_dislike}
                </label>
            </div>
        </div>
        <div class="row">
            <div class="control-group">
                <div class="controls">
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="user_min_change" name="user_min_change" value="{$_aRequest.user_min_change}"  />
                        <span class="help-block">{$aLang.plugin.rating.user_min_change}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="user_max_change" name="user_max_change" value="{$_aRequest.user_max_change}"  />
                        <span class="help-block">{$aLang.plugin.rating.user_max_change}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="user_max_rating" name="user_max_rating" value="{$_aRequest.user_max_rating}"  />
                        <span class="help-block">{$aLang.plugin.rating.user_max_rating}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="user_right_border" name="user_right_border" value="{$_aRequest.user_right_border}"  />
                        <span class="help-block">{$aLang.plugin.rating.user_right_border}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="user_left_border" name="user_left_border" value="{$_aRequest.user_left_border}"  />
                        <span class="help-block">{$aLang.plugin.rating.user_left_border}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="user_left_divider" name="user_left_divider" value="{$_aRequest.user_left_divider}"  />
                        <span class="help-block">{$aLang.plugin.rating.user_left_divider}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="user_mid_divider" name="user_mid_divider" value="{$_aRequest.user_mid_divider}"  />
                        <span class="help-block">{$aLang.plugin.rating.user_mid_divider}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="user_right_divider" name="user_right_divider" value="{$_aRequest.user_right_divider}"  />
                        <span class="help-block">{$aLang.plugin.rating.user_right_divider}</span>
                    </div>

                </div>
            </div>
        </div>

        <br/><br/>
        <h4 for="acl_vote_blog_rating" class="control-label">
            {$aLang.plugin.rating.blog_config}
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
                            {$aLang.plugin.rating.blog_vote}
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
                            {$aLang.plugin.rating.blog_dislike}
                        </label>
                    </div>
                </div>
            </div>
        <div class="row">
                <div class="controls">
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="blog_min_change" name="blog_min_change" value="{$_aRequest.blog_min_change}"  />
                        <span class="help-block">{$aLang.plugin.rating.blog_min_change}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="blog_max_change" name="blog_max_change" value="{$_aRequest.blog_max_change}"  />
                        <span class="help-block">{$aLang.plugin.rating.blog_max_change}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="blog_max_rating" name="blog_max_rating" value="{$_aRequest.blog_max_rating}"  />
                        <span class="help-block">{$aLang.plugin.rating.blog_max_rating}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="blog_right_border" name="blog_right_border" value="{$_aRequest.blog_right_border}"  />
                        <span class="help-block">{$aLang.plugin.rating.blog_right_border}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="blog_left_border" name="blog_left_border" value="{$_aRequest.blog_left_border}"  />
                        <span class="help-block">{$aLang.plugin.rating.blog_left_border}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="blog_left_divider" name="blog_left_divider" value="{$_aRequest.blog_left_divider}"  />
                        <span class="help-block">{$aLang.plugin.rating.blog_left_divider}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="blog_mid_divider" name="blog_mid_divider" value="{$_aRequest.blog_mid_divider}"  />
                        <span class="help-block">{$aLang.plugin.rating.blog_mid_divider}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="blog_right_divider" name="blog_right_divider" value="{$_aRequest.blog_right_divider}"  />
                        <span class="help-block">{$aLang.plugin.rating.blog_right_divider}</span>
                    </div>

                </div>
        </div>

        <br/><br/>
        <h4 for="acl_vote_comment_rating" class="control-label">
            {$aLang.plugin.rating.comment_config}
        </h4>
        <div class="control-group">
            <div class="controls">
                <label>
                    <input type="checkbox"
                           id="comment_vote"
                           name="comment_vote"
                           value="1"
                           {if $_aRequest.comment_vote}checked="checked"{/if}>
                    {$aLang.plugin.rating.comment_vote}
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
                    {$aLang.plugin.rating.comment_dislike}
                </label>
            </div>
        </div>
        <div class="row">
            <div class="control-group">
                <div class="controls">
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="comment_min_change" name="comment_min_change" value="{$_aRequest.comment_min_change}"  />
                        <span class="help-block">{$aLang.plugin.rating.comment_min_change}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="comment_max_change" name="comment_max_change" value="{$_aRequest.comment_max_change}"  />
                        <span class="help-block">{$aLang.plugin.rating.comment_max_change}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="comment_max_rating" name="comment_max_rating" value="{$_aRequest.comment_max_rating}"  />
                        <span class="help-block">{$aLang.plugin.rating.comment_max_rating}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="comment_right_border" name="comment_right_border" value="{$_aRequest.comment_right_border}"  />
                        <span class="help-block">{$aLang.plugin.rating.comment_right_border}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="comment_left_border" name="comment_left_border" value="{$_aRequest.comment_left_border}"  />
                        <span class="help-block">{$aLang.plugin.rating.comment_left_border}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="comment_left_divider" name="comment_left_divider" value="{$_aRequest.comment_left_divider}"  />
                        <span class="help-block">{$aLang.plugin.rating.comment_left_divider}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="comment_mid_divider" name="comment_mid_divider" value="{$_aRequest.comment_mid_divider}"  />
                        <span class="help-block">{$aLang.plugin.rating.comment_mid_divider}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="comment_right_divider" name="comment_right_divider" value="{$_aRequest.comment_right_divider}"  />
                        <span class="help-block">{$aLang.plugin.rating.comment_right_divider}</span>
                    </div>

                </div>
            </div>
        </div>

        <br/><br/>
        <h4 for="acl_vote_topic_rating" class="control-label">
            {$aLang.plugin.rating.topic_config}
        </h4>
        <div class="control-group">
            <div class="controls">
                <label>
                    <input type="checkbox"
                           id="topic_vote"
                           name="topic_vote"
                           value="1"
                           {if $_aRequest.topic_vote}checked="checked"{/if}>
                    {$aLang.plugin.rating.topic_vote}
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
                    {$aLang.plugin.rating.topic_dislike}
                </label>
            </div>
        </div>
        <div class="row">
            <div class="control-group">
                <div class="controls">
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="topic_min_change" name="topic_min_change" value="{$_aRequest.topic_min_change}"  />
                        <span class="help-block">{$aLang.plugin.rating.topic_min_change}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="topic_max_change" name="topic_max_change" value="{$_aRequest.topic_max_change}"  />
                        <span class="help-block">{$aLang.plugin.rating.topic_max_change}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="topic_max_rating" name="topic_max_rating" value="{$_aRequest.topic_max_rating}"  />
                        <span class="help-block">{$aLang.plugin.rating.topic_max_rating}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="topic_right_border" name="topic_right_border" value="{$_aRequest.topic_right_border}"  />
                        <span class="help-block">{$aLang.plugin.rating.topic_right_border}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="topic_left_border" name="topic_left_border" value="{$_aRequest.topic_left_border}"  />
                        <span class="help-block">{$aLang.plugin.rating.topic_left_border}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="topic_left_divider" name="topic_left_divider" value="{$_aRequest.topic_left_divider}"  />
                        <span class="help-block">{$aLang.plugin.rating.topic_left_divider}</span>
                    </div>
                    <div class="col-md-2">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="topic_mid_divider" name="topic_mid_divider" value="{$_aRequest.topic_mid_divider}"  />
                        <span class="help-block">{$aLang.plugin.rating.topic_mid_divider}</span>
                    </div>
                    <div class="col-md-2">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="topic_right_divider" name="topic_right_divider" value="{$_aRequest.topic_right_divider}"  />
                        <span class="help-block">{$aLang.plugin.rating.topic_right_divider}</span>
                    </div>
                    <div class="col-md-2 clearfix">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="topic_auth_coef" name="topic_auth_coef" value="{$_aRequest.topic_auth_coef}"  />
                        <span class="help-block">{$aLang.plugin.rating.topic_auth_coef}</span>
                    </div>

                </div>
            </div>
        </div>

        <br/><br/>
        <h4 for="acl_vote_rating_rating" class="control-label">
            {$aLang.plugin.rating.rating_config}
        </h4>
        <div class="control-group">
            <div class="controls">
                <label>
                    <input type="checkbox"
                           id="rating_vote"
                           name="rating_vote"
                           value="1"
                           {if $_aRequest.rating_vote}checked="checked"{/if}>
                    {$aLang.plugin.rating.rating_vote}
                </label>
            </div>
        </div>
        <div class="row">
            <div class="control-group">
                <div class="controls">
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="rating_topic_border_1" name="rating_topic_border_1" value="{$_aRequest.rating_topic_border_1}"  />
                        <span class="help-block">{$aLang.plugin.rating.rating_topic_border_1}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="rating_topic_border_2" name="rating_topic_border_2" value="{$_aRequest.rating_topic_border_2}"  />
                        <span class="help-block">{$aLang.plugin.rating.rating_topic_border_2}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="rating_topic_border_3" name="rating_topic_border_3" value="{$_aRequest.rating_topic_border_3}"  />
                        <span class="help-block">{$aLang.plugin.rating.rating_topic_border_3}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="rating_topic_k1" name="rating_topic_k1" value="{$_aRequest.rating_topic_k1}"  />
                        <span class="help-block">{$aLang.plugin.rating.rating_topic_k1}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="rating_topic_k2" name="rating_topic_k2" value="{$_aRequest.rating_topic_k2}"  />
                        <span class="help-block">{$aLang.plugin.rating.rating_topic_k2}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="rating_topic_k3" name="rating_topic_k3" value="{$_aRequest.rating_topic_k3}"  />
                        <span class="help-block">{$aLang.plugin.rating.rating_topic_k3}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="rating_topic_k4" name="rating_topic_k4" value="{$_aRequest.rating_topic_k4}"  />
                        <span class="help-block">{$aLang.plugin.rating.rating_topic_k4}</span>
                    </div>
                </div>
            </div>
        </div>
        <br/><br/>
        <h4 for="topic_rating_sum" class="control-label">
            {$aLang.plugin.rating.personal_recalc}
        </h4>
        <div class="row">
            <div class="control-group">
                <div class="controls">
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="topic_rating_sum" name="topic_rating_sum" value="{$_aRequest.topic_rating_sum}"  />
                        <span class="help-block">{$aLang.plugin.rating.topic_rating_sum}</span>
                    </div>
                    <div class="col-md-3">
                        <input autocomplete="off" class="input-wide" placeholder="0" type="text" id="count_topic" name="count_topic" value="{$_aRequest.count_topic}"  />
                        <span class="help-block">{$aLang.plugin.rating.count_topic}</span>
                    </div>

                </div>
            </div>
        </div>

    <br/><br/><br/><br/>

    <input type="submit" name="submit_rating" value="{$aLang.plugin.rating.save}"/>
    <input type="submit" name="cancel" value="{$aLang.plugin.rating.cancel}"/>

    </form>
    </div>
    </div>
    </div>
    </div>
{/block}