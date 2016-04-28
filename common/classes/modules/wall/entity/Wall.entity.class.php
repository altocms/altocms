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
 * Сущность записи на стене
 *
 * @package modules.wall
 * @since   1.0
 */
class ModuleWall_EntityWall extends Entity {
    /**
     * Определяем правила валидации
     *
     * @var array
     */
    protected $aValidateRules
        = array(
            array('pid', 'pid', 'on' => array('', 'add')),
            array('user_id', 'time_limit', 'on' => array('add')),
        );

    /**
     * Инициализация
     */
    public function Init() {

        parent::Init();
        $this->aValidateRules[] = array(
            'text',
            'string',
            'max'        => Config::Get('module.wall.text_max'),
            'min'        => Config::Get('module.wall.text_min'),
            'allowEmpty' => false,
            'on'         => array('', 'add')
        );
    }

    /**
     * Проверка на ограничение по времени
     *
     * @param string $sValue     Проверяемое значение
     * @param array  $aParams    Параметры
     *
     * @return bool|string
     */
    public function ValidateTimeLimit($sValue, $aParams) {

        if ($oUser = E::ModuleUser()->GetUserById($this->getUserId())) {
            if (E::ModuleACL()->CanAddWallTime($oUser, $this)) {
                return true;
            }
        }
        return E::ModuleLang()->get('wall_add_time_limit');
    }

    /**
     * Валидация родительского сообщения
     *
     * @param string $sValue     Проверяемое значение
     * @param array  $aParams    Параметры
     *
     * @return bool|string
     */
    public function ValidatePid($sValue, $aParams) {

        if (!$sValue) {
            $this->setPid(null);
            return true;
        } elseif ($oParentWall = $this->GetPidWall()) {
            /**
             * Если отвечаем на сообщение нужной стены и оно корневое, то все ОК
             */
            if ($oParentWall->getWallUserId() == $this->getWallUserId() and !$oParentWall->getPid()) {
                return true;
            }
        }
        return E::ModuleLang()->get('wall_add_pid_error');
    }

    /**
     * Возвращает родительскую запись
     *
     * @return ModuleWall_EntityWall|null
     */
    public function GetPidWall() {

        if ($this->getPid()) {
            return E::ModuleWall()->GetWallById($this->getPid());
        }
        return null;
    }

    /**
     * Проверка на возможность удаления сообщения
     *
     * @return bool
     */
    public function isAllowDelete() {

        if ($oUserCurrent = E::ModuleUser()->GetUserCurrent()) {
            if ($oUserCurrent->getId() == $this->getWallUserId() or $oUserCurrent->isAdministrator()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Возвращает пользователя, которому принадлежит стена
     *
     * @return ModuleUser_EntityUser|null
     */
    public function getWallUser() {

        if (!$this->getProp('wall_user')) {
            $this->_aData['wall_user'] = E::ModuleUser()->GetUserById($this->getWallUserId());
        }
        return $this->getProp('wall_user');
    }

    /**
     * Возвращает URL стены
     *
     * @return string
     */
    public function getLink() {

        return $this->getWallUser()->getProfileUrl() . 'wall/';
    }

    /**
     * @deprecated
     * @return string
     */
    public function getUrlWall() {

        return $this->getLink();
    }

    /**
     * Creates RSS item for the wall record
     *
     * @return ModuleRss_EntityRssItem
     */
    public function CreateRssItem() {

        $aRssItemData = array(
            'title' => 'Wall of ' . $this->getWallUser()->getDisplayName() . ' (record #' . $this->getId() . ')',
            'description' => $this->getText(),
            'link' => $this->getLink(),
            'author' => $this->getWallUser() ? $this->getWallUser()->getMail() : '',
            'guid' => $this->getLink(),
            'pub_date' => $this->getDateAdd() ? date('r', strtotime($this->getDateAdd())) : '',
        );
        $oRssItem = E::GetEntity('ModuleRss_EntityRssItem', $aRssItemData);

        return $oRssItem;
    }

}

// EOF