{**
 * Избранные комментарии пользователя
 *}

{extends file='[layouts]layout.user.tpl'}

{block name='layout_user_page_title'}{$aLang.user_menu_profile_favourites}{/block}

{block name='layout_content'}
    {include file='nav.user.favourite.tpl'}
    {include file='comment_list.tpl'}
{/block}
