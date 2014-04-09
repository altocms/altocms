{extends file="_profile.tpl"}

{block name="layout_profile_content"}

    {$oSession=$oUserProfile->getSession()}
    {$oVote=$oUserProfile->getVote()}

    <script type="text/javascript">
        ls.wall.init({
            login: '{$oUserProfile->getLogin()}'
        });

        jQuery(document).ready(function ($) {
            $("textarea").charCount({
                allowed: 250,
                warning: 0
            });
        });
    </script>
    {if E::IsUser()}
        <form class="wall-submit">
            <div class="form-group">
                <label for="wall-text">{$aLang.wall_add_title}</label>
                <textarea rows="4" id="wall-text" class="form-control js-wall-reply-parent-text"></textarea>
            </div>

            <button type="button" onclick="ls.wall.add(jQuery('#wall-text').val(),0);"
                    class="btn btn-success js-button-wall-submit">{$aLang.wall_add_submit}</button>
        </form>
    {else}
        <div id="wall-note-list-empty" class="text-center alert alert-info wall-note">
            <h5>{$aLang.wall_add_quest}</h5>
        </div>
    {/if}

    {if !count($aWall)}
        <div id="wall-note-list-empty" class="text-center wall-note">
            <h3>{$aLang.wall_list_empty}</h3>
        </div>
    {/if}
    <div id="wall-container" class="comments wall">
        {include file='actions/profile/action.profile.wall_items.tpl'}
    </div>
    {if $iCountWall-count($aWall)}
        <a href="#" onclick="return ls.wall.loadNext();" id="wall-button-next" class="btn btn-success btn-lg btn-block"><span
                    class="wall-more-inner">{$aLang.wall_load_more} (<span
                        id="wall-count-next">{$iCountWall-count($aWall)}</span>)</span></a>
    {/if}

{/block}
