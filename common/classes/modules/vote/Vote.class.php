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
 * Модуль для работы с голосованиями
 *
 * @package modules.vote
 * @since   1.0
 */
class ModuleVote extends Module {
    /**
     * Объект маппера
     *
     * @var ModuleVote_MapperVote
     */
    protected $oMapper;

    /**
     * Инициализация
     *
     */
    public function Init() {
        $this->oMapper = Engine::GetMapper(__CLASS__);
    }

    /**
     * Добавляет голосование
     *
     * @param ModuleVote_EntityVote $oVote    Объект голосования
     *
     * @return bool
     */
    public function AddVote(ModuleVote_EntityVote $oVote) {
        if (!$oVote->getIp()) {
            $oVote->setIp(F::GetUserIp());
        }
        if ($this->oMapper->AddVote($oVote)) {
            $this->Cache_Delete("vote_{$oVote->getTargetType()}_{$oVote->getTargetId()}_{$oVote->getVoterId()}");
            $this->Cache_Clean(
                Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                array("vote_update_{$oVote->getTargetType()}_{$oVote->getVoterId()}")
            );
            return true;
        }
        return false;
    }

    /**
     * Получает голосование
     *
     * @param int    $sTargetId      ID владельца
     * @param string $sTargetType    Тип владельца
     * @param int    $sUserId        ID пользователя
     *
     * @return ModuleVote_EntityVote|null
     */
    public function GetVote($sTargetId, $sTargetType, $sUserId) {
        $data = $this->GetVoteByArray($sTargetId, $sTargetType, $sUserId);
        if (isset($data[$sTargetId])) {
            return $data[$sTargetId];
        }
        return null;
    }

    /**
     * Получить список голосований по списку айдишников
     *
     * @param array  $aTargetId      Список ID владельцев
     * @param string $sTargetType    Тип владельца
     * @param int    $sUserId        ID пользователя
     *
     * @return array
     */
    public function GetVoteByArray($aTargetId, $sTargetType, $sUserId) {
        if (!$aTargetId) {
            return array();
        }
        if (Config::Get('sys.cache.solid')) {
            return $this->GetVoteByArraySolid($aTargetId, $sTargetType, $sUserId);
        }
        if (!is_array($aTargetId)) {
            $aTargetId = array($aTargetId);
        }
        $aTargetId = array_unique($aTargetId);
        $aVote = array();
        $aIdNotNeedQuery = array();

        // * Делаем мульти-запрос к кешу
        $aCacheKeys = F::Array_ChangeValues($aTargetId, "vote_{$sTargetType}_", '_' . $sUserId);
        if (false !== ($data = $this->Cache_Get($aCacheKeys))) {
            // * проверяем что досталось из кеша
            foreach ($aCacheKeys as $sValue => $sKey) {
                if (array_key_exists($sKey, $data)) {
                    if ($data[$sKey]) {
                        $aVote[$data[$sKey]->getTargetId()] = $data[$sKey];
                    } else {
                        $aIdNotNeedQuery[] = $sValue;
                    }
                }
            }
        }
        // * Смотрим, каких элементов не было в кеше, и делаем запрос в БД
        $aIdNeedQuery = array_diff($aTargetId, array_keys($aVote));
        $aIdNeedQuery = array_diff($aIdNeedQuery, $aIdNotNeedQuery);
        $aIdNeedStore = $aIdNeedQuery;
        if ($data = $this->oMapper->GetVoteByArray($aIdNeedQuery, $sTargetType, $sUserId)) {
            foreach ($data as $oVote) {
                // * Добавляем к результату и сохраняем в кеш
                $aVote[$oVote->getTargetId()] = $oVote;
                $this->Cache_Set(
                    $oVote, "vote_{$oVote->getTargetType()}_{$oVote->getTargetId()}_{$oVote->getVoterId()}", array(),
                    'P7D'
                );
                $aIdNeedStore = array_diff($aIdNeedStore, array($oVote->getTargetId()));
            }
        }
        // * Сохраняем в кеш запросы не вернувшие результата
        foreach ($aIdNeedStore as $sId) {
            $this->Cache_Set(null, "vote_{$sTargetType}_{$sId}_{$sUserId}", array(), 'P7D');
        }

        // * Сортируем результат согласно входящему массиву
        $aVote = F::Array_SortByKeysArray($aVote, $aTargetId);
        return $aVote;
    }

    /**
     * Получить список голосований по списку айдишников, но используя единый кеш
     *
     * @param array  $aTargetId      Список ID владельцев
     * @param string $sTargetType    Тип владельца
     * @param int    $sUserId        ID пользователя
     *
     * @return array
     */
    public function GetVoteByArraySolid($aTargetId, $sTargetType, $sUserId) {
        if (!is_array($aTargetId)) {
            $aTargetId = array($aTargetId);
        }
        $aTargetId = array_unique($aTargetId);
        $aVote = array();
        $s = join(',', $aTargetId);
        if (false === ($data = $this->Cache_Get("vote_{$sTargetType}_{$sUserId}_id_{$s}"))) {
            $data = $this->oMapper->GetVoteByArray($aTargetId, $sTargetType, $sUserId);
            foreach ($data as $oVote) {
                $aVote[$oVote->getTargetId()] = $oVote;
            }
            $this->Cache_Set(
                $aVote, "vote_{$sTargetType}_{$sUserId}_id_{$s}",
                array("vote_update_{$sTargetType}_{$sUserId}", "vote_update_{$sTargetType}"),
                'P1D'
            );
            return $aVote;
        }
        return $data;
    }

    /**
     * Удаляет голосование из базы по списку идентификаторов таргета
     *
     * @param  array|int $aTargetId      Список ID владельцев
     * @param  string    $sTargetType    Тип владельца
     *
     * @return bool
     */
    public function DeleteVoteByTarget($aTargetId, $sTargetType) {
        if (!is_array($aTargetId)) {
            $aTargetId = array($aTargetId);
        }
        $aTargetId = array_unique($aTargetId);
        $bResult = $this->oMapper->DeleteVoteByTarget($aTargetId, $sTargetType);
        // * Чистим зависимые кеши
        $this->Cache_Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("vote_update_{$sTargetType}"));
        return $bResult;
    }

    /**
     * Обновляет голосование
     *
     * @param $oVote
     *
     * @return bool
     */
    public function Update($oVote) {
        if ($this->oMapper->Update($oVote)) {
            $this->Cache_Delete("vote_{$oVote->getTargetType()}_{$oVote->getTargetId()}_{$oVote->getVoterId()}");
            $this->Cache_Clean(
                Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                array("vote_update_{$oVote->getTargetType()}_{$oVote->getVoterId()}")
            );
            return true;
        }
        return false;
    }

    /**
     * Получить статистику по юзерам
     * cnt_topics_p / cnt_topics_m - Количество голосований за топик +/-
     * sum_topics_p / sum_topics_m - Количество голосований за топик +/-
     * cnt_comments_p / cnt_comments_m - Количество голосований за комментарий +/-
     * sum_comments_p / sum_comments_m - Количество голосований за комментарий +/-
     * cnt_user_p / cnt_user_m - Количество голосований за пользователя +/-
     * sum_user_p / sum_user_m - Количество голосований за пользователя +/-
     *
     * @param string|int $sUserId Ид. пользователя
     * @return array
     */
    public function GetUserVoteStats($sUserId) {

        if (false === ($aResult = $this->Cache_Get('user_vote_stats'))) {
            $aResult = $this->oMapper->GetUserVoteStats($sUserId);
            $this->Cache_Set($aResult, 'user_vote_stats', array(
                "vote_update_topic_{$sUserId}",
                "vote_update_comment_{$sUserId}",
                "vote_update_user_{$sUserId}"
            ));

        }

        return $aResult;

    }

}

// EOF