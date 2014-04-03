<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

/**
 * Добавляет старые LS-методы для совместимости
 */
class PluginLs_ModuleComment extends PluginLs_Inherit_ModuleComment {

    public function GetTemplateCommentByTarget($iTargetId, $sTargetType) {

        return 'comment.tpl';
    }

}

// EOF