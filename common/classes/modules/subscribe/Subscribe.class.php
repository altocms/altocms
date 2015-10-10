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
 * Модуль Subscribe - подписки пользователей
 *
 * @package modules.subscribe
 * @since   1.0
 */
class ModuleSubscribe extends Module {

    /**
     * Объект маппера
     *
     * @var ModuleSubscribe_MapperSubscribe
     */
    protected $oMapper;
    /**
     * Объект текущего пользователя
     *
     * @var ModuleUser_EntityUser|null
     */
    protected $oUserCurrent;
    /**
     * Список доступных объектов подписок с параметрами
     * На данный момент допустим параметр allow_for_guest=>1 - указывает на возможность создавать подписку для гостя
     *
     * @var array
     */
    protected $aTargetTypes
        = array(
            'topic_new_comment' => array(),
        );

    /**
     * Инициализация
     *
     */
    public function Init() {

        $this->oMapper = E::GetMapper(__CLASS__);
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();
    }

    /**
     * Возвращает список типов объектов
     *
     * @return array
     */
    public function GetTargetTypes() {

        return $this->aTargetTypes;
    }

    /**
     * Добавляет в разрешенные новый тип
     *
     * @param string $sTargetType    Тип
     * @param array  $aParams        Параметры
     *
     * @return bool
     */
    public function AddTargetType($sTargetType, $aParams = array()) {

        if (!array_key_exists($sTargetType, $this->aTargetTypes)) {
            $this->aTargetTypes[$sTargetType] = $aParams;
            return true;
        }
        return false;
    }

    /**
     * Проверяет разрешен ли данный тип в подписке
     *
     * @param string $sTargetType    Тип
     *
     * @return bool
     */
    public function IsAllowTargetType($sTargetType) {

        return in_array($sTargetType, array_keys($this->aTargetTypes));
    }

    /**
     * Проверка объекта подписки
     *
     * @param string $sTargetType    Тип
     * @param int    $iTargetId      ID владельца
     * @param int    $iStatus        Статус подписки
     *
     * @return bool
     */
    public function CheckTarget($sTargetType, $iTargetId, $iStatus = null) {

        $sMethod = 'CheckTarget' . F::StrCamelize($sTargetType);
        if (method_exists($this, $sMethod)) {
            return $this->$sMethod($iTargetId, $iStatus);
        }
        return false;
    }

    /**
     * Возвращает URL страницы с объектом подписки
     * Актуально при переходе по ссылки с отпиской от рассылки и последующим редиректом
     *
     * @param string $sTargetType    Тип
     * @param int    $iTargetId      ID владельца
     *
     * @return bool
     */
    public function GetUrlTarget($sTargetType, $iTargetId) {

        $sMethod = 'GetUrlTarget' . F::StrCamelize($sTargetType);
        if (method_exists($this, $sMethod)) {
            return $this->$sMethod($iTargetId);
        }
        return false;
    }

    /**
     * Проверка на подписку для гостей
     *
     * @param string $sTargetType Тип
     *
     * @return bool
     */
    public function IsAllowTargetForGuest($sTargetType) {

        if ($this->IsAllowTargetType($sTargetType)) {
            if (isset($this->aTargetTypes[$sTargetType]['allow_for_guest'])
                && $this->aTargetTypes[$sTargetType]['allow_for_guest']
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Добавляет подписку в БД
     *
     * @param ModuleSubscribe_EntitySubscribe $oSubscribe    Объект подписки
     *
     * @return ModuleSubscribe_EntitySubscribe|bool
     */
    public function AddSubscribe($oSubscribe) {

        if ($sId = $this->oMapper->AddSubscribe($oSubscribe)) {
            $oSubscribe->setId($sId);
            return $oSubscribe;
        }
        return false;
    }

    /**
     * Создает подписку, если уже есть, то возвращает существующую
     *
     * @param string $sTargetType    Тип
     * @param string $sTargetId      ID владельца
     * @param string $sMail          Емайл
     *
     * @return ModuleSubscribe_EntitySubscribe|bool
     */
    public function AddSubscribeSimple($sTargetType, $sTargetId, $sMail, $sUserId = null) {

        if (!$sMail) {
            return false;
        }
        if (!($oSubscribe = E::ModuleSubscribe()->GetSubscribeByTargetAndMail($sTargetType, $sTargetId, $sMail))) {
            /** @var ModuleSubscribe_EntitySubscribe $oSubscribe */
            $oSubscribe = E::GetEntity('Subscribe');
            $oSubscribe->setTargetType($sTargetType);
            $oSubscribe->setTargetId($sTargetId);
            $oSubscribe->setMail($sMail);
            $oSubscribe->setDateAdd(date('Y-m-d H:i:s'));
            $oSubscribe->setKey(F::RandomStr(32));
            $oSubscribe->setIp(F::GetUserIp());
            $oSubscribe->setStatus(1);
            /**
             * Если только для авторизованных, то добавляем user_id
             */
            if ($sUserId && !$this->IsAllowTargetForGuest($sTargetType)) {
                $oSubscribe->setUserId($sUserId);
            }
            E::ModuleSubscribe()->AddSubscribe($oSubscribe);
        }
        return $oSubscribe;
    }

    /**
     * Добавляет трекинг в БД
     *
     * @param ModuleSubscribe_EntityTrack $oTrack    Объект подписки
     *
     * @return ModuleSubscribe_EntityTrack|bool
     */
    public function AddTrack($oTrack) {

        if ($sId = $this->oMapper->AddTrack($oTrack)) {
            $oTrack->setId($sId);
            return $oTrack;
        }
        return false;
    }

    /**
     * Создает подписку, если уже есть, то возвращает существующую
     *
     * @param string $sTargetType    Тип
     * @param string $sTargetId      ID владельца
     * @param string $sUserId        ID юзера
     *
     * @return ModuleSubscribe_EntityTrack|bool
     */
    public function AddTrackSimple($sTargetType, $sTargetId, $sUserId) {

        if (!$sUserId) {
            return false;
        }
        if (!($oTrack = E::ModuleSubscribe()->GetTrackByTargetAndUser($sTargetType, $sTargetId, $sUserId))) {
            /** @var ModuleSubscribe_EntityTrack $oTrack */
            $oTrack = E::GetEntity('ModuleSubscribe_EntityTrack');
            $oTrack->setTargetType($sTargetType);
            $oTrack->setTargetId($sTargetId);
            $oTrack->setUserId($sUserId);
            $oTrack->setDateAdd(date('Y-m-d H:i:s'));
            $oTrack->setKey(F::RandomStr(32));
            $oTrack->setIp(F::GetUserIp());
            $oTrack->setStatus(1);
            E::ModuleSubscribe()->AddTrack($oTrack);
        }
        return $oTrack;
    }

    /**
     * Обновление подписки
     *
     * @param ModuleSubscribe_EntitySubscribe $oSubscribe    Объект подписки
     *
     * @return int
     */
    public function UpdateSubscribe($oSubscribe) {

        return $this->oMapper->UpdateSubscribe($oSubscribe);
    }

    /**
     * Смена емайла в подписках
     *
     * @param string   $sMailOld Старый емайл
     * @param string   $sMailNew Новый емайл
     * @param int|null $iUserId  Id пользователя
     *
     * @return int
     */
    public function ChangeSubscribeMail($sMailOld, $sMailNew, $iUserId = null) {

        return $this->oMapper->ChangeSubscribeMail($sMailOld, $sMailNew, $iUserId);
    }

    /**
     * Обновление трекинга
     *
     * @param ModuleSubscribe_EntityTrack $oTrack    Объект подписки
     *
     * @return int
     */
    public function UpdateTrack($oTrack) {

        return $this->oMapper->UpdateTrack($oTrack);
    }

    /**
     * Возвращает список подписок по фильтру
     *
     * @param array $aFilter      Фильтр
     * @param array $aOrder       Сортировка
     * @param int   $iCurrPage    Номер страницы
     * @param int   $iPerPage     Количество элементов на страницу
     *
     * @return array('collection'=>array,'count'=>int)
     */
    public function GetSubscribes($aFilter, $aOrder, $iCurrPage, $iPerPage) {

        return array('collection' => $this->oMapper->GetSubscribes($aFilter, $aOrder, $iCount, $iCurrPage, $iPerPage),
                     'count'      => $iCount);
    }

    /**
     * Возвращает список треков по фильтру
     *
     * @param array $aFilter      Фильтр
     * @param array $aOrder       Сортировка
     * @param int   $iCurrPage    Номер страницы
     * @param int   $iPerPage     Количество элементов на страницу
     *
     * @return array('collection'=>array,'count'=>int)
     */
    public function GetTracks($aFilter, $aOrder, $iCurrPage, $iPerPage) {

        return array('collection' => $this->oMapper->GetTracks($aFilter, $aOrder, $iCount, $iCurrPage, $iPerPage),
                     'count'      => $iCount);
    }

    /**
     * Возвращает подписку по объекту подписки и емайлу
     *
     * @param string $sTargetType    Тип
     * @param int    $iTargetId      ID владельца
     * @param string $sMail          Емайл
     *
     * @return ModuleSubscribe_EntitySubscribe|null
     */
    public function GetSubscribeByTargetAndMail($sTargetType, $iTargetId, $sMail) {

        $aRes = $this->GetSubscribes(
            array('target_type' => $sTargetType, 'target_id' => $iTargetId, 'mail' => $sMail), array(), 1, 1
        );
        if (isset($aRes['collection'][0])) {
            return $aRes['collection'][0];
        }
        return null;
    }

    /**
     * Возвращает подписку по ключу
     *
     * @param string $sKey    Ключ
     *
     * @return ModuleSubscribe_EntitySubscribe|null
     */
    public function GetSubscribeByKey($sKey) {

        $aRes = $this->GetSubscribes(array('key' => $sKey), array(), 1, 1);
        if (isset($aRes['collection'][0])) {
            return $aRes['collection'][0];
        }
        return null;
    }

    /**
     * Возвращает трекинг по объекту подписки и идентификатору юзера
     *
     * @param string $sTargetType    Тип
     * @param int    $iTargetId      ID владельца
     * @param string $sUserId        ID юзера
     *
     * @return ModuleSubscribe_EntityTrack|null
     */
    public function GetTrackByTargetAndUser($sTargetType, $iTargetId, $sUserId) {

        $aRes = $this->GetTracks(
            array('target_type' => $sTargetType, 'target_id' => $iTargetId, 'user_id' => $sUserId), array(), 1, 1
        );
        if (isset($aRes['collection'][0])) {
            return $aRes['collection'][0];
        }
        return null;
    }

    /**
     * Производит отправку писем по подписчикам подписки
     *
     * @param int    $sTargetType     Тип объекта подписки
     * @param int    $iTargetId       ID объекта подписки
     * @param string $sTemplate       Имя шаблона письма, например, mail.tpl
     * @param string $sTitle          Заголовок письма
     * @param array  $aParams         Параметра для передачи в шаблон письма
     * @param array  $aExcludeMail    Список емайлов на которые НЕ нужно отправлять
     * @param string $sPluginName     Название или класс плагина для корректной отправки
     */
    public function Send($sTargetType, $iTargetId, $sTemplate, $sTitle, $aParams = array(), $aExcludeMail = array(), $sPluginName = null) {

        $iPage = 1;
        $aSubscribes = E::ModuleSubscribe()->GetSubscribes(
            array('target_type'  => $sTargetType, 'target_id' => $iTargetId, 'status' => 1,
                  'exclude_mail' => $aExcludeMail), array(), $iPage, 20
        );
        while ($aSubscribes['collection']) {
            $iPage++;
            /** @var ModuleSubscribe_EntitySubscribe $oSubscribe */
            foreach ($aSubscribes['collection'] as $oSubscribe) {
                $aParams['sSubscribeKey'] = $oSubscribe->getKey();
                E::ModuleNotify()->Send(
                    $oSubscribe->getMail(),
                    $sTemplate,
                    $sTitle,
                    $aParams,
                    $sPluginName
                );
            }
            $aSubscribes = E::ModuleSubscribe()->GetSubscribes(
                array('target_type' => $sTargetType, 'target_id' => $iTargetId, 'status' => 1,
                      'exclude_mail' => $aExcludeMail), array(), $iPage, 20
            );
        }
    }

    /**
     * Проверка объекта подписки с типом "topic_new_comment"
     * Название метода формируется автоматически
     *
     * @param int $iTargetId    ID владельца
     * @param int $iStatus      Статус
     *
     * @return bool
     */
    public function CheckTargetTopicNewComment($iTargetId, $iStatus) {

        if ($oTopic = E::ModuleTopic()->GetTopicById($iTargetId)) {
            /**
             * Топик может быть в закрытом блоге, поэтому необходимо разрешить подписку только если пользователь в нем состоит
             * Отписываться разрешаем с любого топика
             */
            if ($iStatus == 1 && $oTopic->getBlog()->IsPrivate()) {
                if (!$this->oUserCurrent
                    || !($oTopic->getBlog()->getOwnerId() == $this->oUserCurrent->getId()
                        || E::ModuleBlog()->GetBlogUserByBlogIdAndUserId($oTopic->getBlogId(), $this->oUserCurrent->getId()))
                ) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Возвращает URL на страницы объекта подписки с типом "topic_new_comment"
     * Название метода формируется автоматически
     *
     * @param int $iTargetId    ID топика
     *
     * @return string|bool
     */
    public function GetUrlTargetTopicNewComment($iTargetId) {

        if (($oTopic = E::ModuleTopic()->GetTopicById($iTargetId)) && $oTopic->getPublish()) {
            return $oTopic->getUrl();
        }
        return false;
    }
}

// EOF