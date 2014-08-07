<div class="modal fade in" id="modal-blog_delete">
    <div class="modal-dialog">
        <div class="modal-content">

            <header class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h3>{$aLang.blog_admin_delete_title}</h3>
            </header>

            <form action="" method="POST" class="uniform">
                <div class="modal-body">
                    <p>
                        {$aLang.action.admin.blog_del_confirm_text}
                        <strong id="blog_delete_name"></strong>
                    </p>

                    <p>
                        {$aLang.action.admin.blog_del_confirm_topics}
                        <strong id="blog_delete_topics"></strong>
                    </p>

                    <div id="blog_delete_choose">
                        <p>{$aLang.action.admin.blog_del_topics_choose}</p>

                        <p>
                            <label>
                                <input type="radio" name="delete_topics" value="delete" checked>
                                {$aLang.blog_delete_clear}
                            </label>
                            <label>
                                <input type="radio" name="delete_topics" value="move">
                                {$aLang.blog_admin_delete_move}
                            </label>
                            <select name="topic_move_to" id="topic_move_to" class="input-wide" style="display: none;">
                                <option value=""></option>
                                {foreach $aAllBlogs as $nBlogId=>$sBlogTitle}
                                    <option value="{$nBlogId}">{$sBlogTitle|escape:'html'}</option>
                                {/foreach}
                            </select>
                        </p>
                    </div>
                </div>

                <div class="modal-footer">
                    <input type="hidden" name="cmd" value="delete_blog"/>
                    <input type="hidden" name="delete_blog_id" value=""/>
                    <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
                    <input type="hidden" name="return-path" value="{Router::Url('link')}"/>
                    <button type="submit" class="btn" data-dismiss="modal"
                            aria-hidden="true">{$aLang.text_cancel}</button>
                    <button type="submit" class="btn btn-primary">{$aLang.action.admin.blog_delete}</button>
                </div>
            </form>
        </div>
    </div>
</div>
