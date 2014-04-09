<div class="modal fade in" id="modal-blog_delete">
    <div class="modal-dialog">
        <div class="modal-content">

            <header class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{$aLang.blog_admin_delete_title}</h4>
            </header>

            <div class="modal-body">
                <form action="{router page='blog'}delete/{$oBlog->getId()}/" method="POST">
                    <div class="form-group">
                        <label for="topic_move_to">{$aLang.blog_admin_delete_move}</label>
                        <select name="topic_move_to" id="topic_move_to" class="form-control">
                            <option value="-1">{$aLang.blog_delete_clear}</option>
                            {if $aBlogs}
                                <optgroup label="{$aLang.blogs}">
                                    {foreach $aBlogs as $oBlogDelete}
                                        <option value="{$oBlogDelete->getId()}">{$oBlogDelete->getTitle()|escape:'html'}</option>
                                    {/foreach}
                                </optgroup>
                            {/if}
                        </select>
                    </div>

                    <input type="hidden" name="security_key" value="{$ALTO_SECURITY_KEY}"/>
                    <button type="submit" class="btn btn-success">{$aLang.blog_delete}</button>
                </form>
            </div>

        </div>
    </div>
</div>
