{**
 * Блог
 *
 * bCloseBlog    true если блог закрытый
 *
 * @styles css/blog.css
 * @scripts <framework>/js/livestreet/blog.js
 *}

{extends file='[layouts]layout.base.tpl'}

{block name='layout_content'}
    {$oUserOwner = $oBlog->getOwner()}
    {$oVote = $oBlog->getVote()}
    <script>
        jQuery(function ($) {
            ls.lang.load({lang_load name="blog_fold_info,blog_expand_info"});
        });
    </script>
{* Подключаем модальное окно удаления блога если пользователь админ *}
    {if $oUserCurrent and $oUserCurrent->isAdministrator()}
        {include file='modals/modal.blog_delete.tpl'}
    {/if}
    <div class="blog">
        <header class="blog-header">
            {* Аватар *}
            <img src="{$oBlog->getAvatarPath(48)}" alt="avatar" class="avatar"/>

            <ul>
                <li class="blog-title">
                    {* Заголовок *}
                    <h2>
                        {if $oBlog->getType() == 'close'}
                            <i title="{$aLang.blog_closed}" class="icon icon-lock"></i>
                        {/if}

                        {$oBlog->getTitle()|escape:'html'}
                    </h2>

                    <a href="{router page='rss'}blog/{$oBlog->getUrl()}/" class="rss">RSS</a>
                </li>

                {* Вступить/покинуть блог *}
                {if $oUserCurrent and $oUserCurrent->getId() != $oBlog->getOwnerId()}
                    <li>
                        <a href="#" onclick="ls.blog.toggleJoin(this,{$oBlog->getId()}); return false;"
                           class="link-dotted">
                            {if $oBlog->getUserIsJoin()}
                                {$aLang.blog_leave}
                            {else}
                                {$aLang.blog_join}
                            {/if}
                        </a>
                    </li>
                {/if}

                <li>
                    {* Голосование за блог *}
                    <div data-vote-type="blog"
                         data-vote-id="{$oBlog->getId()}"
                         class="vote js-vote
							{if $oBlog->getRating() > 0}
								vote-count-positive
							{elseif $oBlog->getRating() < 0}
								vote-count-negative
							{/if} 

							{if $oVote}
								voted

								{if $oVote->getDirection() > 0}
									voted-up
								{elseif $oVote->getDirection() < 0}
									voted-down
								{/if}
							{/if}">
                        <div class="vote-count count js-vote-rating"
                             title="{$aLang.blog_vote_count}: {$oBlog->getCountVote()}">{$oBlog->getRating()}</div>
                        {*<a href="#" class="vote-item vote-down js-vote-down"><i></i></a><a href="#" class="vote-item vote-up js-vote-up"><i></i></a>*}
                    </div>
                </li>
            </ul>
        </header>


        {* Информация о блоге *}
        <div class="blog-content" id="blog-more-content" style="display: none;">
            <div class="blog-description text">
                {$oBlog->getDescription()}
            </div>


            <div class="blog-info">
                {hook run='blog_info_begin' oBlog=$oBlog}

                <strong>{$aLang.blog_user_administrators} ({$iCountBlogAdministrators}):</strong>

                {* Создатель блога *}
                <a href="{$oUserOwner->getUserWebPath()}" class="user"><i
                            class="icon-user"></i>{$oUserOwner->getLogin()}</a>

                {* Список администраторов блога *}
                {if $aBlogAdministrators}
                    {foreach $aBlogAdministrators as $oBlogUser}
                        {$oUser = $oBlogUser->getUser()}
                        <a href="{$oUser->getUserWebPath()}" class="user"><i class="icon-user"></i>{$oUser->getLogin()}
                        </a>
                    {/foreach}
                {/if}
                <br/>


                {* Список модераторов блога *}
                <strong>{$aLang.blog_user_moderators} ({$iCountBlogModerators}):</strong>

                {if $aBlogModerators}
                    {foreach $aBlogModerators as $oBlogUser}
                        {$oUser = $oBlogUser->getUser()}
                        <a href="{$oUser->getUserWebPath()}" class="user"><i class="icon-user"></i>{$oUser->getLogin()}
                        </a>
                    {/foreach}
                {else}
                    {$aLang.blog_user_moderators_empty}
                {/if}
                <br/>


                {* Список подписавшихся пользователей *}
                <strong>{$aLang.blog_user_readers} ({$iCountBlogUsers}):</strong>

                {if $aBlogUsers}
                    {foreach $aBlogUsers as $oBlogUser}
                        {$oUser = $oBlogUser->getUser()}
                        <a href="{$oUser->getUserWebPath()}" class="user"><i class="icon-user"></i>{$oUser->getLogin()}
                        </a>
                    {/foreach}

                {* Если пользователей слишком много, то показываем ссылку на страницу со всеми пользователями *}
                    {if count($aBlogUsers) < $iCountBlogUsers}
                        <br/>
                        <a href="{$oBlog->getUrlFull()}users/">{$aLang.blog_user_readers_all}</a>
                    {/if}
                {else}
                    {$aLang.blog_user_readers_empty}
                {/if}

                {hook run='blog_info_end' oBlog=$oBlog}
            </div>

            {* Управление *}
            <ul class="actions">
                {* Администрирование *}
                {if $oUserCurrent and ($oUserCurrent->getId() == $oBlog->getOwnerId() or $oUserCurrent->isAdministrator() or $oBlog->getUserIsAdministrator() )}
                    <li>
                        <a href="{router page='blog'}edit/{$oBlog->getId()}/" title="{$aLang.blog_edit}"
                           class="edit">{$aLang.blog_edit}</a>

                        {if $oUserCurrent->isAdministrator()}
                            <a href="#" title="{$aLang.blog_delete}" data-type="modal-toggle"
                               data-option-target="modal-blog-delete" class="delete">{$aLang.blog_delete}</a>
                        {else}
                            <a href="{router page='blog'}delete/{$oBlog->getId()}/?security_ls_key={$ALTO_SECURITY_KEY}"
                               title="{$aLang.blog_delete}"
                               onclick="return confirm('{$aLang.blog_admin_delete_confirm}');">{$aLang.blog_delete}</a>
                        {/if}
                    </li>
                {/if}
            </ul>
        </div>


        {* Кнопка показывающая/скрывающая информацию о блоге *}
        <footer class="blog-footer">
            <a href="#" class="blog-more" id="blog-more"
               onclick="return ls.blog.toggleInfo()">{$aLang.blog_expand_info}</a>
        </footer>
    </div>
    {hook run='blog_info' oBlog=$oBlog}

    {include file='nav.blog.tpl'}

    {if $bCloseBlog}
        {$aLang.blog_close_show}
    {else}
        {include file='topics/topic_list.tpl'}
    {/if}
{/block}
