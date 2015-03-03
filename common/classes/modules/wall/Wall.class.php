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
 * Модуль Wall - записи на стене профиля пользователя
 *
 * @package modules.wall
 * @since   1.0
 */
class ModuleWall extends Module {
    /**
     * Объект маппера
     *
     * @var ModuleWall_MapperWall
     */
    protected $oMapper;
    /**
     * Объект текущего пользователя
     *
     * @var ModuleUser_EntityUser|null
     */
    protected $oUserCurrent;

    protected $aAdditionalData = array('user' => array(), 'wall_user' => array(), 'reply');

    /**
     * Инициализация
     *
     */
    public function Init() {

        $this->oMapper = E::GetMapper(__CLASS__);
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();
    }

    /**
     * Добавление записи на стену
     *
     * @param ModuleWall_EntityWall $oWall    Объект записи на стене
     *
     * @return bool|ModuleWall_EntityWall
     */
    public function AddWall($oWall) {

        if (!$oWall->getDateAdd()) {
            $oWall->setDateAdd(F::Now());
        }
        if (!$oWall->getIp()) {
            $oWall->setIp(F::GetUserIp());
        }
        if ($iId = $this->oMapper->AddWall($oWall)) {
            $oWall->setId($iId);
            /**
             * Обновляем данные у родительской записи
             */
            if ($oPidWall = $oWall->GetPidWall()) {
                $this->UpdatePidWall($oPidWall);
            }
            return $oWall;
        }
        return false;
    }

    /**
     * Обновление записи
     *
     * @param ModuleWall_EntityWall $oWall    Объект записи на стене
     *
     * @return bool
     */
    public function UpdateWall($oWall) {

        return $this->oMapper->UpdateWall($oWall);
    }

    /**
     * Получение списка записей по фильтру
     *
     * @param array $aFilter       Фильтр
     * @param array $aOrder        Сортировка
     * @param int   $iCurrPage     Номер страницы
     * @param int   $iPerPage      Количество элементов на страницу
     * @param array $aAllowData    Список типов дополнительных данных для подгрузки в сообщения стены
     *
     * @return array('collection'=>array,'count'=>int)
     */
    public function GetWall($aFilter, $aOrder, $iCurrPage = 1, $iPerPage = 10, $aAllowData = null) {

        $aResult = array(
            'collection' => $this->oMapper->GetWall($aFilter, $aOrder, $iCount, $iCurrPage, $iPerPage),
            'count'      => $iCount
        );
        if ($aResult['collection']) {
            $aResult['collection'] = $this->GetWallAdditionalData($aResult['collection'], $aAllowData);
        }
        return $aResult;
    }

    /**
     * Возвращает число сообщений на стене по фильтру
     *
     * @param array $aFilter    Фильтр
     *
     * @return int
     */
    public function GetCountWall($aFilter) {

        return $this->oMapper->GetCountWall($aFilter);
    }

    /**
     * Получение записей по ID, без дополнительных данных
     *
     * @param array $aWallId    Список ID сообщений
     *
     * @return array
     */
    public function GetWallsByArrayId($aWallId) {

        if (!is_array($aWallId)) {
            $aWallId = array($aWallId);
        }
        $aWallId = array_unique($aWallId);
        $aWalls = array();
        $aResult = $this->oMapper->GetWallsByArrayId($aWallId);
        foreach ($aResult as $oWall) {
            $aWalls[$oWall->getId()] = $oWall;
        }
        return $aWalls;
    }

    /**
     * Получение записей по ID с дополнительные связаными данными
     *
     * @param array $aWallId    - Список ID сообщений
     * @param array $aAllowData - Список типов дополнительных данных для подгрузки в сообщения стены
     *
     * @return array
     */
    public function GetWallAdditionalData($aWallId, $aAllowData = null) {

        if (!$aWallId) {
            return array();
        }
        if (is_null($aAllowData)) {
            $aAllowData = $this->aAdditionalData;
        }
        $aAllowData = F::Array_FlipIntKeys($aAllowData);
        if (!is_array($aWallId)) {
            $aWallId = array($aWallId);
        }

        $aWalls = $this->GetWallsByArrayId($aWallId);

        // * Формируем ID дополнительных данных, которые нужно получить
        $aUserId = array();
        $aWallUserId = array();
        $aWallReplyId = array();
        foreach ($aWalls as $oWall) {
            if (isset($aAllowData['user'])) {
                $aUserId[] = $oWall->getUserId();
            }
            if (isset($aAllowData['wall_user'])) {
                $aWallUserId[] = $oWall->getWallUserId();
            }

            // * Список последних записей хранится в строке через запятую
            if (isset($aAllowData['reply']) && !$oWall->getPid() && $oWall->getLastReply()) {
                $aReply = explode(',', trim($oWall->getLastReply()));
                $aWallReplyId = array_merge($aWallReplyId, $aReply);
            }
        }

        // * Получаем дополнительные данные
        $aUsers = (isset($aAllowData['user']) && is_array($aAllowData['user']))
            ? E::ModuleUser()->GetUsersAdditionalData($aUserId, $aAllowData['user'])
            : E::ModuleUser()->GetUsersAdditionalData($aUserId);

        $aWallUsers = (isset($aAllowData['wall_user']) && is_array($aAllowData['wall_user']))
            ? E::ModuleUser()->GetUsersAdditionalData($aWallUserId, $aAllowData['wall_user'])
            : E::ModuleUser()->GetUsersAdditionalData($aWallUserId);

        $aWallReply = array();
        if (isset($aAllowData['reply']) && count($aWallReplyId)) {
            $aWallReply = $this->GetWallAdditionalData($aWallReplyId, array('user' => array()));
        }

        // * Добавляем данные к результату
        foreach ($aWalls as $oWall) {
            if (isset($aUsers[$oWall->getUserId()])) {
                $oWall->setUser($aUsers[$oWall->getUserId()]);
            } else {
                $oWall->setUser(null); // или $oWall->setUser(new ModuleUser_EntityUser());
            }
            if (isset($aWallUsers[$oWall->getWallUserId()])) {
                $oWall->setWallUser($aWallUsers[$oWall->getWallUserId()]);
            } else {
                $oWall->setWallUser(null);
            }
            $aReply = array();
            if ($oWall->getLastReply()) {
                $aReplyId = explode(',', trim($oWall->getLastReply()));
                foreach ($aReplyId as $iReplyId) {
                    if (isset($aWallReply[$iReplyId])) {
                        $aReply[] = $aWallReply[$iReplyId];
                    }
                }
            }
            $oWall->setLastReplyWall($aReply);
        }
        return $aWalls;
    }

    /**
     * Получение записи по ID
     *
     * @param int $iId    ID сообщения/записи
     *
     * @return ModuleWall_EntityWall
     */
    public function GetWallById($iId) {

        if (!intval($iId)) {
            return null;
        }
        $aResult = $this->GetWallAdditionalData($iId);
        if (isset($aResult[$iId])) {
            return $aResult[$iId];
        }
        return null;
    }

    /**
     * Обновляет родительские данные у записи - количество ответов и ID последних ответов
     *
     * @param ModuleWall_EntityWall $oWall    Объект записи на стене
     * @param null|int              $iLimit
     */
    public function UpdatePidWall($oWall, $iLimit = null) {

        if (is_null($iLimit)) {
            $iLimit = Config::Get('module.wall.count_last_reply');
        }

        $aResult = $this->GetWall(array('pid' => $oWall->getId()), array('id' => 'desc'), 1, $iLimit, array());
        if ($aResult['count']) {
            $oWall->setCountReply($aResult['count']);
            $aKeys = array_keys($aResult['collection']);
            sort($aKeys, SORT_NUMERIC);
            $oWall->setLastReply(join(',', $aKeys));
        } else {
            $oWall->setCountReply(0);
            $oWall->setLastReply('');
        }
        $this->UpdateWall($oWall);
    }

    /**
     * Удаление сообщения
     *
     * @param ModuleWall_EntityWall $oWall    Объект записи на стене
     */
    public function DeleteWall($oWall) {

        $this->oMapper->DeleteWallsByPid($oWall->getId());
        $this->oMapper->DeleteWallById($oWall->getId());
        if ($oWallParent = $oWall->GetPidWall()) {
            $this->UpdatePidWall($oWallParent);
        }
    }
}

// EOF