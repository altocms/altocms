 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike  http://site.creatime.org/experience/*}

{extends file="./action.login.index.tpl"}

{block name="layout_content" prepend}

    <div class="text-center page-header">
        <h3>{$aLang.password_reminder}</h3>
        <p>{$aLang.password_reminder_send_password}</p>
        <p>{$aLang.password_reminder_send_password_txt}</p>
    </div>

{/block}
