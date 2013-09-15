{**
 * Блок с кнопкой добавления блога
 *
 * @styles css/widgets.css
 *}

{extends file='./_aside.base.tpl'}

{block name='block_type'}blog-add{/block}

{block name='block_options'}
    {if ! $oUserCurrent}
        {$bBlockNotShow = true}
    {/if}
{/block}

{block name='block_content'}
    <p>{$aLang.topic_add_title}</p>
    <a href="{router page='content'}topic/add/" class="btn-primary" data-toggle="modal"
       data-target="#modal-write">{$aLang.topic_add}</a>
{/block}
