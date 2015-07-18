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
 * @package modules.rss
 * @since   1.1
 */
class ModuleRss_EntityRss extends Entity {

    public function __construct() {

        $this->AddRssAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
        $this->AddRssAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
    }

    /**
     * Appends RSS attributes
     *
     * @param string $sAttrName
     * @param string $sAttrValue
     *
     * @return $this
     */
    public function AddRssAttribute($sAttrName, $sAttrValue) {

        $this->appendProp('rss', $sAttrName, $sAttrValue);

        return $this;
    }

    /**
     * @param ModuleRss_EntityRssChannel $oChannel
     *
     * @return ModuleRss_EntityRss
     */
    public function AddChannel($oChannel) {

        $this->appendProp('channels', $oChannel);

        return $this;
    }

    public function getChannels() {

        return (array)$this->getProp('channels');
    }

    /**
     * Returns RSS attributes
     *
     * @return array
     */
    public function getRssAttributes() {

        return (array)$this->getProp('rss');
    }

    /**
     * Returns RSS attributes as string
     *
     * @return string
     */
    public function getRssAttributesStr() {

        $sResult = '';
        if ($aAttributes = $this->getRssAttributes()) {
            foreach($aAttributes as $sKey => $sVal) {
                if ($sResult) {
                    $sResult .= ' ';
                }
                $sResult .= $sKey . '="' . str_replace('"', '&quot;', $sVal) . '"';
            }
        }

        return $sResult;
    }

}

// EOF