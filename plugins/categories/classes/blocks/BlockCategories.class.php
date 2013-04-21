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
 * Регистрация хука
 *
 */
class PluginCategories_BlockCategories extends Block {
    /**
     * Запуск обработки
     */
    public function Exec() {
        /**
         * Получаем категории
         */
        $aCategories=$this->PluginCategories_Categories_GetItemsByFilter(array(),'PluginCategories_ModuleCategories_EntityCategory');
		$this->Viewer_Assign("aCategories",$aCategories);
    }
}
// EOF