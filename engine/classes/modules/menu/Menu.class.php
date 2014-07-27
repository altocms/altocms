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
        if (isset($aMenu['config']['fill_from'])) {
            $aFillMode = $aMenu['config']['fill_from'];
            $aFillMode = F::Array_FlipIntKeys($aFillMode);
            $aItems = array();
            foreach($aFillMode as $sFillFrom => $aFillSet) {
                $aItems = array_merge($aItems, $this->_fillItems($sFillFrom, $aFillSet, $iLimit, $aMenu));
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
     * @param array  $aMenu
     *
     * @return array
     */
    protected function _fillItems($sFillFrom, $aFillSet, $iLimit, $aMenu) {

        $aItems = array();
        if ($sFillFrom == 'blogs') {
            $aItems = $this->_fillItemsFromBlogs($aFillSet, $iLimit, $aMenu);
        } elseif ($sFillFrom == 'list') {
            $aItems = $this->_fillItemsFromList($aFillSet, $iLimit, $aMenu);
        }
        return $aItems;
    }

    /**
     * @param array  $aFillSet
     * @param int    $iLimit
     * @param array  $aMenu
     *
     * @return array
     */
    protected function _fillItemsFromBlogs($aFillSet, $iLimit, $aMenu) {

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

    /**
     * @param array  $aFillSet
     * @param int    $iLimit
     * @param array  $aMenu
     *
     * @return array
     */
    protected function _fillItemsFromList($aFillSet, $iLimit, $aMenu) {

        $aItems = array();
        if (isset($aMenu['items']) && is_array($aMenu['items'])) {
            $aFillSet = array_flip($aFillSet);
            foreach($aMenu['items'] as $sKey => $aItem) {
                if (isset($aFillSet[$sKey])) {
                    $aItems[$sKey] = $aItem;
                }
            }
        }

        return $aItems;
    }

}

// EOF