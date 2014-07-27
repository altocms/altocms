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
 * @package engine.modules
 * @since   1.0.2
 */
class ModuleMenu extends Module {

    public function Init() {

    }

    /**
     * Prepares menu array
     *
     * @param $aMenu
     *
     * @return mixed
     */
    public function Prepare($aMenu) {

        $iLimit = (isset($aMenu['config']['limit']) ? intval($aMenu['config']['limit']) : 0);
        $aParams = (isset($aMenu['config']['params']) ? $aMenu['config']['params'] : array());
        if (isset($aMenu['config']['fill'])) {
            $aFillMode = $aMenu['config']['fill'];
            $aFillMode = F::Array_FlipIntKeys($aFillMode);
            $aItems = array();
            foreach($aFillMode as $sFillFrom => $aFillSet) {
                $aItems = array_merge($aItems, $this->_fillItems($sFillFrom, $aFillSet, $iLimit, $aParams));
            }
            if (sizeof($aItems) > $iLimit) {
                $aItems = array_slice($aItems, 0, $iLimit);
            }
            $aMenu['items'] = $aItems;
        }
        return $aMenu;
    }

    /**
     * Returns item array
     *
     * @param string $sFillFrom
     * @param array  $aFillSet
     * @param int    $iLimit
     * @param array  $aParams
     *
     * @return array
     */
    protected function _fillItems($sFillFrom, $aFillSet, $iLimit, $aParams = array()) {

        $aItems = array();
        if ($sFillFrom == 'blogs') {
            $aItems = $this->_fillItemsByBlogs($aFillSet, $iLimit, $aParams);
        }
        return $aItems;
    }

    /**
     * @param array  $aFillSet
     * @param int    $iLimit
     * @param array  $aParams
     *
     * @return array
     */
    protected function _fillItemsByBlogs($aFillSet, $iLimit, $aParams = array()) {

        $aItems = array();
        $aBlogs = array();
        if ($aFillSet) {
            $aBlogs = $this->Blog_GetBlogsByUrl($aFillSet);
        } else {
            if ($aResult = $this->Blog_GetBlogsRating(1, $iLimit)) {
                $aBlogs = $aResult['collection'];
            }
        }
        if ($aBlogs) {
            foreach($aBlogs as $oBlog) {
                $aItems[$oBlog->getUrl()] = array(
                    'text' => $oBlog->getTitle(),
                    'url' => $oBlog->getLink(),
                );
            }
        }

        return $aItems;
    }

}

// EOF