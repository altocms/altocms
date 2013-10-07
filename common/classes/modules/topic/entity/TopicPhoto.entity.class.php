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
 * Объект сущности аттача
 *
 * @package modules.topic
 * @since   1.0
 */
class ModuleTopic_EntityTopicPhoto extends Entity {
    /**
     * Возвращает ID объекта
     *
     * @return int|null
     */
    public function getId() {

        return $this->getProp('id');
    }

    /**
     * Возвращает ID топика
     *
     * @return int|null
     */
    public function getTopicId() {

        return $this->getProp('topic_id');
    }

    /**
     * Возвращает ключ временного владельца
     *
     * @return string|null
     */
    public function getTargetTmp() {

        return $this->getProp('target_tmp');
    }

    /**
     * Возвращает описание фото
     *
     * @return string|null
     */
    public function getDescription() {

        return $this->getProp('description');
    }

    /**
     * Вовзращает полный веб путь до файла
     *
     * @return string|null
     */
    public function getPath() {

        return $this->getProp('path');
    }

    /**
     * Возвращает полный веб путь до фото определенного размера
     *
     * @param string|null $sWidth    Размер фото, например, '100' или '150crop'
     *
     * @return null|string
     */
    public function getWebPath($sWidth = null) {

        if ($sUrl = $this->getPath()) {
            if ($sWidth) {
                $sResizedUrl = '';
                $aPathInfo = pathinfo($sUrl);

                if (E::ActivePlugin('ls')) {
                    // Включена совместимость с LS
                    $sResizedUrl = $aPathInfo['dirname'] . '/' . $aPathInfo['filename'] . '_' . $sWidth . '.'
                        . $aPathInfo['extension'];
                    if (F::File_LocalUrl($sResizedUrl) && !F::File_Exists(F::File_Url2Dir($sResizedUrl))) {
                        $sResizedUrl = '';
                    }
                }

                if (!$sResizedUrl) {
                    $nSize = intval($sWidth);
                    $bCrop = strpos($sWidth, 'crop');
                    if ($nSize) {
                        if ($bCrop) {
                            $sResizedUrl = $sUrl . '-' . $nSize . 'x' . $nSize . '-crop.' . $aPathInfo['extension'];
                        } else {
                            $sResizedUrl = $sUrl . '-' . $nSize . 'x' . $nSize . '.' . $aPathInfo['extension'];
                        }
                    }
                }
            }
        }
        return F::File_NormPath($sResizedUrl ? $sResizedUrl : $sUrl);
    }

    /**
     * Устанавливает ID топика
     *
     * @param int $iTopicId
     */
    public function setTopicId($iTopicId) {

        $this->setProp('topic_id', $iTopicId);
    }

    /**
     * Устанавливает ключ временного владельца
     *
     * @param string $sTargetTmp
     */
    public function setTargetTmp($sTargetTmp) {

        $this->setProp('target_tmp', $sTargetTmp);
    }

    /**
     * Устанавливает описание фото
     *
     * @param string $sDescription
     */
    public function setDescription($sDescription) {

        $this->setProp('description', $sDescription);
    }

    public function buildMresource() {

        $oMresource = Engine::GetEntity('Mresource_MresourceRel');
        $oMresource->SetLink(false);
        $oMresource->SetType(ModuleMresource::TYPE_IMAGE | ModuleMresource::TYPE_PHOTO);
        $oMresource->SetUrl($this->Mresource_NormalizeUrl($this->GetPath()));
        return $oMresource;
    }

}

// EOF