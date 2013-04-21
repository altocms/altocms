{extends file="_index.tpl"}

{block name="content-body"}
    <div class="span12">
        <ul class="nav nav-list">
            <li><a href="{router page="admin"}checkdb/blogs/">{$aLang.action.admin.checkdb_deleted_blogs}</a></li>
            <li><a href="{router page="admin"}checkdb/topics/">{$aLang.action.admin.checkdb_deleted_topics}</a></li>
            {hook run='admin_action_db_item'}
        </ul>
    </div>
{/block}

