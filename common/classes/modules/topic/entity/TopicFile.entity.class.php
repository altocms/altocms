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
 * Class ModuleTopic_EntityTopicFile
 *
 * @method string getFileUrl()
 * @method int getFileSize()
 *
 */
class ModuleTopic_EntityTopicFile extends Entity {

    public function getSizeFormat() {

        $iSize = $this->getFileSize();
        $aSizes = array('B', 'KB', 'MB', 'GB', 'TB');
        $i = 0;
        while ($iSize > 1000) {
            $iSize /= 1024;
            $i++;
        }
        return sprintf('%.2f %s', $iSize, $aSizes[$i]);
    }

}

// EOF