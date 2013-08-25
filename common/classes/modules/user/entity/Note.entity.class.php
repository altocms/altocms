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
 * Сущность заметки о пользователе
 *
 * @package modules.user
 * @since   1.0
 */
class ModuleUser_EntityNote extends Entity {
    /**
     * Определяем правила валидации
     *
     * @var array
     */
    protected $aValidateRules
        = array(
            array('target_user_id', 'target'),
        );

    /**
     * Инициализация
     */
    public function Init() {
        parent::Init();
        $this->aValidateRules[] = array('text', 'string', 'max' => Config::Get('module.user.usernote_text_max'),
                                        'min'                   => 1, 'allowEmpty' => false);
    }

    /**
     * Валидация пользователя
     *
     * @param string $sValue     Значение
     * @param array  $aParams    Параметры
     *
     * @return bool
     */
    public function ValidateTarget($sValue, $aParams) {
        if ($oUserTarget = $this->User_GetUserById($sValue) and $this->getUserId() != $oUserTarget->getId()) {
            return true;
        }
        return $this->Lang_Get('user_note_target_error');
    }
}

// EOF