<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   LiveStreet Engine Social Networking by Mzhelskiy Maxim
 *   Site: www.livestreet.ru
 *   E-mail: rus.engine@gmail.com
 *----------------------------------------------------------------------------
 */

/**
 * Объект сущности тега для избранного
 *
 * @package modules.favourite
 * @since   1.0
 */
class ModuleFavourite_EntityTag extends Entity {

    public function getLink() {

        $oUser = $this->getProp('user');
        if (!$oUser) {
            $oUser = E::User();
        }
        if ($oUser) {
            return $oUser->getProfileUrl() . 'favourites/topics/tag/' . F::UrlEncode($this->getText(), true) . '/';
        }
        return '';
    }

}

// EOF