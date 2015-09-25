<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */


interface ITextParser {

    public function loadConfig($sType = 'default', $bClear = true);

    public function tagBuilder($sTag, $xCallBack);

    public function parse($sText, &$aErrors);
}

// EOF