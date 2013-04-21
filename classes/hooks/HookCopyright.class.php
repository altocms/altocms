<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Version: 0.9a
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

/**
 * Регистрация хука для вывода ссылки копирайта
 *
 * @package hooks
 * @since 1.0
 */
class HookCopyright extends Hook {
    /**
     * Регистрируем хуки
     */
    public function RegisterHook() {
        $this->AddHook('template_copyright', 'CopyrightLink', __CLASS__, -100);
    }

    /**
     * Обработка хука копирайта
     *
     * @return string
     */
    public function CopyrightLink() {
        /**
         * Выводим везде, кроме страницы списка блогов и списка всех комментов
         */
        return '&copy; Powered by <a href="http://altocms.ru">Alto CMS</a>, based on <a href="http://livestreetcms.org">LiveStreet</a>';
    }
}

// EOF