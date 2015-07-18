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
 * Объект сущности избрнного
 *
 * @package modules.favourite
 * @since   1.0
 */
class ModuleFavourite_EntityFavourite extends Entity {
    /**
     * Возвращает ID владельца
     *
     * @return int|null
     */
    public function getTargetId() {

        return $this->getProp('target_id');
    }

    /**
     * Возвращает ID пользователя
     *
     * @return int|null
     */
    public function getUserId() {

        return $this->getProp('user_id');
    }

    /**
     * Возвращает флаг публикации владельца
     *
     * @return int|null
     */
    public function getTargetPublish() {

        return $this->getProp('target_publish');
    }

    /**
     * Возвращает тип владельца
     *
     * @return string|null
     */
    public function getTargetType() {

        return $this->getProp('target_type');
    }

    /**
     * Возващает список тегов
     *
     * @param bool $bTextOnly
     *
     * @return array
     */
    public function getTagsArray($bTextOnly = true) {

        if ($sTags = $this->getTags()) {
            if ($bTextOnly) {
                return explode(',', $sTags);
            }
            $aTexts = explode(',', $sTags);
            $aData = array();
            foreach ($aTexts as $nI => $sText) {
                $aData[] = array(
                    'favourite_tag_id'   => -$nI,
                    'favourite_tag_text' => $sText,
                );
            }
            return E::GetEntityRows('Favourite_Tag', $aData);
        }
        return array();
    }

    /**
     * Устанавливает ID владельца
     *
     * @param int $data
     */
    public function setTargetId($data) {

        $this->setProp('target_id', $data);
    }

    /**
     * Устанавливает ID пользователя
     *
     * @param int $data
     */
    public function setUserId($data) {

        $this->setProp('user_id', $data);
    }

    /**
     * Устанавливает статус публикации для владельца
     *
     * @param int $data
     */
    public function setTargetPublish($data) {

        $this->setProp('target_publish', $data);
    }

    /**
     * Устанавливает тип владельца
     *
     * @param string $data
     */
    public function setTargetType($data) {

        $this->setProp('target_type', $data);
    }

}

// EOF