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

 
class PluginCategories_ModuleCategories extends ModuleORM {


	public function InitConfigMainPreview() {
		Config::Set('plugin.mainpreview.size_images_preview',array_merge(Config::Get('plugin.mainpreview.size_images_preview'),Config::Get('plugin.categories.size_images_preview')));

		Config::Set('plugin.mainpreview.preview_minimal_size_width',Config::Get('plugin.categories.preview_size_w'));
		Config::Set('plugin.mainpreview.preview_minimal_size_height',Config::Get('plugin.categories.preview_size_h'));
	}

	
}