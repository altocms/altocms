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
 * Модуль для работы с топиками
 *
 * @package modules.topic
 * @since   1.0
 */
class ModuleTopic extends Module {

    const CONTENT_ACCESS_ALL = 1;           // Уровень доступа для всех зарегистрированных
    const CONTENT_ACCESS_ONLY_ADMIN = 2;    // Уровень доступа только для админов

    /**
     * Объект маппера
     *
     * @var ModuleTopic_MapperTopic
     */
    protected $oMapper;

    // LS-compatibility //
    protected $oMapperTopic;

    /**
     * Объект текущего пользователя
     *
     * @var ModuleUser_EntityUser
     */
    protected $oUserCurrent = null;

    /**
     * Список типов топика
     *
     * @var array
     */
    protected $aTopicTypes = array(); //'topic','link','question','photoset'

    /**
     * Список полей
     *
     * @var array
     */
    protected $aFieldTypes = array('input', 'textarea', 'photoset', 'link', 'select', 'date', 'file');

    /**
     * Массив объектов типов топика
     *
     * @var array
     */
    protected $aTopicTypesObjects = array();

    protected $aAdditionalData
        = array(
            'user' => array(), 'blog' => array('owner' => array(), 'relation_user'),
            'vote', 'favourite', 'fields', 'comment_new',
        );

    protected $aAdditionalDataContentType = array('fields' => array());

    protected $aTopicsFilter = array('topic_publish' => 1);

    /**
     * Инициализация
     *
     */
    public function Init() {

        $this->oMapperTopic = $this->oMapper = E::GetMapper(__CLASS__);
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();
        $this->aTopicTypesObjects = $this->getContentTypes(array('content_active' => 1));
        $this->aTopicTypes = array_keys($this->aTopicTypesObjects);
    }

    /**
     * Получает доступные типы контента
     *
     * @param      $aFilter
     * @param null $aAllowData
     *
     * @return array
     */
    public function getContentTypes($aFilter, $aAllowData = null) {

        if (is_null($aAllowData)) {
            $aAllowData = $this->aAdditionalDataContentType;
        }
        $sCacheKey = 'content_types_' . serialize(array($aFilter, $aAllowData)) ;
        if (false === ($data = E::ModuleCache()->Get($sCacheKey, 'tmp,'))) {
            $data = $this->oMapper->getContentTypes($aFilter);
            $aTypesId = array();
            foreach ($data as $oType) {
                $aTypesId[] = $oType->getContentId();
            }
            if (isset($aAllowData['fields'])) {
                $aTopicFieldValues = $this->GetFieldsByArrayId($aTypesId);
            }

            foreach ($data as $oType) {
                if (isset($aTopicFieldValues[$oType->getContentId()])) {
                    $oType->setFields($aTopicFieldValues[$oType->getContentId()]);
                }
            }
            E::ModuleCache()->Set($data, $sCacheKey, array('content_update', 'content_new'), 'P1D', 'tmp,');
        }
        return $data;
    }

    /*
     * Возвращает доступные типы контента
     *
     * @return ModuleTopic_EntityContentType
     */
    public function getContentType($sType) {

        if (in_array($sType, $this->aTopicTypes)) {
            return $this->aTopicTypesObjects[$sType];
        }
        return null;

    }

    /**
     * Возвращает доступные для создания пользователем типы контента
     *
     * @param ModuleUser_EntityUser $oUser
     *
     * @return ModuleTopic_EntityContentType[]
     */
    public function GetAllowContentTypeByUserId($oUser) {

        if ($oUser && ($oUser->isAdministrator() || $oUser->isModerator())) {
            // Для админа и модератора доступны все активные типы контента
            /** @var ModuleTopic_EntityContentType[] $aContentTypes */
            $aContentTypes = E::ModuleTopic()->GetContentTypes(array('content_active' => 1));

            return $aContentTypes;
        }

        // Получим все блоги пользователя
        $aBlogs = E::ModuleBlog()->GetBlogsAllowByUser($oUser, false);

        // Добавим персональный блог пользователю
        if ($oUser) {
            $aBlogs[] = E::ModuleBlog()->GetPersonalBlogByUserId($oUser->getId());
        }

        // Получим типы контента
        /** @var ModuleTopic_EntityContentType[] $aContentTypes */
        $aContentTypes = E::ModuleTopic()->GetContentTypes(array('content_active' => 1));

        $aAllowContentTypes = array();

        /** @var ModuleBlog_EntityBlog $oBlog */
        foreach($aBlogs as $oBlog) {
            // Пропускаем блог, если в него нельзя добавлять топики
            if (!E::ModuleACL()->CanAddTopic($oUser, $oBlog)) {
                continue;
            }

            if ($aContentTypes) {
                foreach ($aContentTypes as $k=>$oContentType) {
                    if ($oBlog->IsContentTypeAllow($oContentType->getContentUrl())) {
                        $aAllowContentTypes[] = $oContentType;
                        // Удалим, что бы повторное не проверять, ведь в каком-то
                        // блоге пользвоателя этот тип контента уже разрешён
                        unset($aContentTypes[$k]);
                    }
                }
            }
        }

        return $aAllowContentTypes;
    }

    /**
     * Получить тип контента по id
     *
     * @param string $nId
     *
     * @return ModuleTopic_EntityContentType|null
     */
    public function GetContentTypeById($nId) {

        if (false === ($data = E::ModuleCache()->Get("content_type_{$nId}"))) {
            $data = $this->oMapper->getContentTypeById($nId);
            E::ModuleCache()->Set($data, "content_type_{$nId}", array('content_update', 'content_new'), 60 * 60 * 24 * 1);
        }
        return $data;
    }

    /**
     * Получить тип контента по url
     *
     * @param string $sUrl
     *
     * @return ModuleTopic_EntityContentType|null
     */
    public function GetContentTypeByUrl($sUrl) {

        if (false === ($data = E::ModuleCache()->Get("content_type_{$sUrl}"))) {
            $data = $this->oMapper->getContentTypeByUrl($sUrl);
            E::ModuleCache()->Set($data, "content_type_{$sUrl}", array('content_update', 'content_new'), 'P1D');
        }
        return $data;
    }

    /**
     * TODO: Задание типа контента по умолчанию в админке
     *
     * @return mixed|null
     */
    public function GetContentTypeDefault() {

        $aTypes = $this->getContentTypes(array('content_active' => 1));
        if ($aTypes) {
            return reset($aTypes);
        }
        return null;
    }

    /**
     * заменить системный тип контента у уже созданных топиков
     *
     * @param string $sTypeOld
     * @param string $sTypeNew
     *
     * @return bool
     */
    public function ChangeType($sTypeOld, $sTypeNew) {

        return $this->oMapper->changeType($sTypeOld, $sTypeNew);
    }

    /**
     * Добавляет тип контента
     *
     * @param ModuleTopic_EntityContentType $oType    Объект типа контента
     *
     * @return ModuleTopic_EntityContentType|bool
     */
    public function AddContentType($oType) {

        if ($nId = $this->oMapper->AddContentType($oType)) {
            $oType->setContentId($nId);
            //чистим зависимые кеши
            E::ModuleCache()->CleanByTags(array('content_new', 'content_update'));
            return $oType;
        }
        return false;
    }

    /**
     * Обновляет топик
     *
     * @param ModuleTopic_EntityContentType $oType    Объект типа контента
     *
     * @return bool
     */
    public function UpdateContentType($oType) {

        if ($this->oMapper->UpdateContentType($oType)) {

            //чистим зависимые кеши
            E::ModuleCache()->CleanByTags(array('content_new', 'content_update', 'topic_update'));
            E::ModuleCache()->Delete("content_type_{$oType->getContentId()}");
            return true;
        }
        return false;
    }

    /**
     * Обновляет топик
     *
     * @param ModuleTopic_EntityContentType $oContentType    Объект типа контента
     *
     * @return bool
     */
    public function DeleteContentType($oContentType) {

        $aFilter = array(
            'topic_type' => $oContentType->getContentUrl(),
        );
        $iCount = $this->GetCountTopicsByFilter($aFilter);
        if (!$iCount && $this->oMapper->DeleteContentType($oContentType->getId())) {

            //чистим зависимые кеши
            E::ModuleCache()->CleanByTags(array('content_new', 'content_update', 'topic_update'));
            E::ModuleCache()->Delete("content_type_{$oContentType->getId()}");
            return true;
        }
        return false;
    }

    /**
     * Получает доступные поля для типа контента
     *
     * @param array $aFilter
     *
     * @return ModuleTopic_EntityField[]
     */
    public function getContentFields($aFilter) {

        $sCacheKey = serialize($aFilter);
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->getContentFields($aFilter);
            E::ModuleCache()->Set($data, $sCacheKey, array('content_update', 'content_new'), 'P1D');
        }
        return $data;
    }

    /**
     * Добавляет поле
     *
     * @param ModuleTopic_EntityField $oField    Объект поля
     *
     * @return ModuleTopic_EntityField|bool
     */
    public function AddContentField($oField) {

        if ($nId = $this->oMapper->AddContentField($oField)) {
            $oField->setFieldId($nId);
            //чистим зависимые кеши
            E::ModuleCache()->CleanByTags(array('content_new', 'content_update', 'field_new', 'field_update'));
            return $oField;
        }
        return false;
    }

    /**
     * Обновляет топик
     *
     * @param ModuleTopic_EntityField $oField    Объект поля
     *
     * @return bool
     */
    public function UpdateContentField($oField) {

        if ($this->oMapper->UpdateContentField($oField)) {

            //чистим зависимые кеши
            E::ModuleCache()->CleanByTags(array('content_new', 'content_update', 'field_new', 'field_update'));
            E::ModuleCache()->Delete("content_field_{$oField->getFieldId()}");
            return true;
        }
        return false;
    }

    /**
     * Получить поле контента по id
     *
     * @param string $nId
     *
     * @return ModuleTopic_EntityField
     */
    public function GetContentFieldById($nId) {

        if (false === ($data = E::ModuleCache()->Get("content_field_{$nId}"))) {
            $data = $this->oMapper->getContentFieldById($nId);
            E::ModuleCache()->Set(
                $data, "content_field_{$nId}", array('content_new', 'content_update', 'field_new', 'field_update'),
                'P1D'
            );
        }
        return $data;
    }

    /**
     * Удаляет поле
     *
     * @param ModuleTopic_EntityField|int $xField
     *
     * @return bool
     */
    public function DeleteField($xField) {

        if (is_object($xField)) {
            $iContentFieldId = $xField->getFieldId();
        } else {
            $iContentFieldId = intval($xField);
        }
        // * Если топик успешно удален, удаляем связанные данные
        if ($bResult = $this->oMapper->DeleteField($iContentFieldId)) {

            // * Чистим зависимые кеши
            E::ModuleCache()->CleanByTags(array('field_update', 'content_update'));
            E::ModuleCache()->Delete("content_field_{$iContentFieldId}");

            return true;
        }

        return false;
    }


    /**
     * Добавление поля к топику
     *
     * @param ModuleTopic_EntityContentValues $oValue    Объект поля топика
     *
     * @return int
     */
    public function AddTopicValue($oValue) {

        return $this->oMapper->AddTopicValue($oValue);
    }

    /**
     * Обновляет значение поля топика
     *
     * @param ModuleTopic_EntityContentValues $oValue    Объект поля
     *
     * @return bool
     */
    public function UpdateContentFieldValue($oValue) {

        if ($this->oMapper->UpdateContentFieldValue($oValue)) {
            //чистим зависимые кеши
            E::ModuleCache()->CleanByTags(array('topic_update'));
            return true;
        }
        return false;
    }

    /**
     * Получает количество значений у конкретного поля
     *
     * @param $sFieldId
     * @return int|bool
     */
    public function GetFieldValuesCount($sFieldId) {

        return $this->oMapper->GetFieldValuesCount($sFieldId);
    }

    /**
     * Возвращает список типов топика
     *
     * @return string[]
     */
    public function GetTopicTypes() {

        return $this->aTopicTypes;
    }

    /**
     * Добавляет новый тип топика
     *
     * @param string $sType    Новый тип
     *
     * @return bool
     */
    public function AddTopicType($sType) {

        if (!in_array($sType, $this->aTopicTypes)) {
            $this->aTopicTypes[] = $sType;
            return true;
        }
        return false;
    }

    /**
     * Проверяет разрешен ли данный тип топика
     *
     * @param string $sType    Тип
     *
     * @return bool
     */
    public function IsAllowTopicType($sType) {

        return in_array($sType, $this->aTopicTypes);
    }

    /**
     * Возвращает список полей
     *
     * @return array
     */
    public function GetAvailableFieldTypes() {

        return $this->aFieldTypes;
    }

    /**
     * Добавляет новый тип поля
     *
     * @param string $sType    Новый тип
     *
     * @return bool
     */
    public function AddFieldType($sType) {

        if (!in_array($sType, $this->aFieldTypes)) {
            $this->aFieldTypes[] = $sType;
            return true;
        }
        return false;
    }

    /**
     * Получает дополнительные данные(объекты) для топиков по их ID
     *
     * @param array|int  $aTopicId    Список ID топиков
     * @param array|null $aAllowData  Список типов дополнительных данных, которые нужно подключать к топикам
     *
     * @return ModuleTopic_EntityTopic[]
     */
    public function GetTopicsAdditionalData($aTopicId, $aAllowData = null) {

        if (!is_array($aTopicId)) {
            $aTopicId = array($aTopicId);
        }

        // * Получаем "голые" топики
        $aTopics = $this->GetTopicsByArrayId($aTopicId);
        if (!$aTopics) {
            return array();
        }

        if (is_null($aAllowData)) {
            $aAllowData = $this->aAdditionalData;
        }
        $aAllowData = F::Array_FlipIntKeys($aAllowData);

        // * Формируем ID дополнительных данных, которые нужно получить
        $aUserId = array();
        $aBlogId = array();
        $aTopicId = array();
        $aPhotoMainId = array();

        /** @var ModuleTopic_EntityTopic $oTopic */
        foreach ($aTopics as $oTopic) {
            if (isset($aAllowData['user'])) {
                $aUserId[] = $oTopic->getUserId();
            }
            if (isset($aAllowData['blog'])) {
                $aBlogId[] = $oTopic->getBlogId();
            }

            $aTopicId[] = $oTopic->getId();
            if ($oTopic->getPhotosetMainPhotoId()) {
                $aPhotoMainId[] = $oTopic->getPhotosetMainPhotoId();
            }
        }
        if ($aUserId) {
            $aUserId = array_unique($aUserId);
        }
        if ($aBlogId) {
            $aBlogId = array_unique($aBlogId);
        }
        /**
         * Получаем дополнительные данные
         */
        $aTopicsVote = array();
        $aFavouriteTopics = array();
        $aTopicsQuestionVote = array();
        $aTopicsRead = array();

        $aUsers = isset($aAllowData['user']) && is_array($aAllowData['user'])
            ? E::ModuleUser()->GetUsersAdditionalData($aUserId, $aAllowData['user'])
            : E::ModuleUser()->GetUsersAdditionalData($aUserId);

        $aBlogs = isset($aAllowData['blog']) && is_array($aAllowData['blog'])
            ? E::ModuleBlog()->GetBlogsAdditionalData($aBlogId, $aAllowData['blog'])
            : E::ModuleBlog()->GetBlogsAdditionalData($aBlogId);

        if (isset($aAllowData['vote']) && $this->oUserCurrent) {
            $aTopicsVote = E::ModuleVote()->GetVoteByArray($aTopicId, 'topic', $this->oUserCurrent->getId());
            $aTopicsQuestionVote = $this->GetTopicsQuestionVoteByArray($aTopicId, $this->oUserCurrent->getId());
        }

        if (isset($aAllowData['favourite']) && $this->oUserCurrent) {
            $aFavouriteTopics = $this->GetFavouriteTopicsByArray($aTopicId, $this->oUserCurrent->getId());
        }

        if (isset($aAllowData['fields'])) {
            $aTopicFieldValues = $this->GetTopicValuesByArrayId($aTopicId);
        }

        if (isset($aAllowData['comment_new']) && $this->oUserCurrent) {
            $aTopicsRead = $this->GetTopicsReadByArray($aTopicId, $this->oUserCurrent->getId());
        }

        $aPhotosetMainPhotos = $this->GetTopicPhotosByArrayId($aPhotoMainId);

        // * Добавляем данные к результату - списку топиков
        /** @var ModuleTopic_EntityTopic $oTopic */
        foreach ($aTopics as $oTopic) {
            if (isset($aUsers[$oTopic->getUserId()])) {
                $oTopic->setUser($aUsers[$oTopic->getUserId()]);
            } else {
                $oTopic->setUser(null); // или $oTopic->setUser(new ModuleUser_EntityUser());
            }
            if (isset($aBlogs[$oTopic->getBlogId()])) {
                $oTopic->setBlog($aBlogs[$oTopic->getBlogId()]);
            } else {
                $oTopic->setBlog(null); // или $oTopic->setBlog(new ModuleBlog_EntityBlog());
            }
            if (isset($aTopicsVote[$oTopic->getId()])) {
                $oTopic->setVote($aTopicsVote[$oTopic->getId()]);
            } else {
                $oTopic->setVote(null);
            }
            if (isset($aFavouriteTopics[$oTopic->getId()])) {
                $oTopic->setFavourite($aFavouriteTopics[$oTopic->getId()]);
            } else {
                $oTopic->setFavourite(null);
            }
            if (isset($aTopicsQuestionVote[$oTopic->getId()])) {
                $oTopic->setUserQuestionIsVote(true);
            } else {
                $oTopic->setUserQuestionIsVote(false);
            }
            if (isset($aTopicFieldValues[$oTopic->getId()])) {
                $oTopic->setTopicValues($aTopicFieldValues[$oTopic->getId()]);
            } else {
                $oTopic->setTopicValues(false);
            }
            if (isset($aTopicsRead[$oTopic->getId()])) {
                $oTopic->setCountCommentNew(
                    $oTopic->getCountComment() - $aTopicsRead[$oTopic->getId()]->getCommentCountLast()
                );
                $oTopic->setDateRead($aTopicsRead[$oTopic->getId()]->getDateRead());
            } else {
                $oTopic->setCountCommentNew(0);
                $oTopic->setDateRead(F::Now());
            }
            if (isset($aPhotosetMainPhotos[$oTopic->getPhotosetMainPhotoId()])) {
                $oTopic->setPhotosetMainPhoto($aPhotosetMainPhotos[$oTopic->getPhotosetMainPhotoId()]);
            } else {
                $oTopic->setPhotosetMainPhoto(null);
            }
        }
        return $aTopics;
    }

    /**
     * Добавляет топик
     *
     * @param ModuleTopic_EntityTopic $oTopic    Объект топика
     *
     * @return ModuleTopic_EntityTopic|bool
     */
    public function AddTopic($oTopic) {

        if ($nId = $this->oMapper->AddTopic($oTopic)) {
            $oTopic->setId($nId);
            if ($oTopic->getPublish() && $oTopic->getTags()) {
                $aTags = explode(',', $oTopic->getTags());
                foreach ($aTags as $sTag) {
                    /** @var ModuleTopic_EntityTopicTag $oTag */
                    $oTag = E::GetEntity('Topic_TopicTag');
                    $oTag->setTopicId($oTopic->getId());
                    $oTag->setUserId($oTopic->getUserId());
                    $oTag->setBlogId($oTopic->getBlogId());
                    $oTag->setText($sTag);
                    $this->AddTopicTag($oTag);
                }
            }
            $this->processTopicFields($oTopic, 'add');

            $this->UpdateMresources($oTopic);

            //чистим зависимые кеши
            E::ModuleCache()->CleanByTags(
                array('topic_new', "topic_update_user_{$oTopic->getUserId()}", "topic_new_blog_{$oTopic->getBlogId()}")
            );
            return $oTopic;
        }
        return false;
    }

    /**
     * Добавление тега к топику
     *
     * @param ModuleTopic_EntityTopicTag $oTopicTag    Объект тега топика
     *
     * @return int
     */
    public function AddTopicTag($oTopicTag) {

        return $this->oMapper->AddTopicTag($oTopicTag);
    }

    /**
     * Удаляет теги у топика
     *
     * @param   int|array $aTopicsId  ID топика
     *
     * @return  bool
     */
    public function DeleteTopicTagsByTopicId($aTopicsId) {

        return $this->oMapper->DeleteTopicTagsByTopicId($aTopicsId);
    }

    /**
     * Удаляет значения полей у топика
     *
     * @param int|array $aTopicsId    ID топика
     *
     * @return bool
     */
    public function DeleteTopicValuesByTopicId($aTopicsId) {

        return $this->oMapper->DeleteTopicValuesByTopicId($aTopicsId);
    }

    /**
     * Удаляет топик.
     * Если тип таблиц в БД InnoDB, то удалятся всё связи по топику(комменты,голосования,избранное)
     *
     * @param ModuleTopic_EntityTopic|int $oTopicId Объект топика или ID
     *
     * @return bool
     */
    public function DeleteTopic($oTopicId) {

        if ($oTopicId instanceof ModuleTopic_EntityTopic) {
            $oTopic = $oTopicId;
            $iTopicId = $oTopic->getId();
            $iUserId = $oTopic->getUserId();
        } else {
            $iTopicId = intval($oTopicId);
            $oTopic = $this->GetTopicById($iTopicId);
            if (!$oTopic) {
                return false;
            }
            $iUserId = $oTopic->getUserId();
        }
        $oTopicId = null;

        $oBlog = $oTopic->GetBlog();
        // * Если топик успешно удален, удаляем связанные данные
        if ($bResult = $this->oMapper->DeleteTopic($iTopicId)) {
            $bResult = $this->DeleteTopicAdditionalData($iTopicId);
            $this->DeleteTopicValuesByTopicId($iTopicId);
            $this->DeleteTopicTagsByTopicId($iTopicId);
            $this->DeleteMresources($oTopic);
            if ($oBlog) {
                // Блог может быть удален до удаления топика
                E::ModuleBlog()->RecalculateCountTopicByBlogId($oBlog->GetId());
            }
        }

        // * Чистим зависимые кеши
        E::ModuleCache()->CleanByTags(array('topic_update', 'topic_update_user_' . $iUserId));
        E::ModuleCache()->Delete("topic_{$iTopicId}");

        return $bResult;
    }

    /**
     * Delete array of topics
     *
     * @param ModuleTopic_EntityTopic|int|ModuleTopic_EntityTopic[]|int[] $xTopics
     *
     * @return bool
     */
    public function DeleteTopics($xTopics) {

        if (is_int($xTopics) || is_object($xTopics)) {
            return $this->DeleteTopic($xTopics);
        }

        if (is_array($xTopics)) {
            if (count($xTopics) == 1) {
                return $this->DeleteTopic(reset($xTopics));
            }
            if (!is_object(reset($xTopics))) {
                // there are IDs in param
                $aTopics = $this->GetTopicsAdditionalData($xTopics);
            } else {
                // there are topic objects in param
                $aTopics = $xTopics;
            }
            if ($aTopics) {
                $aTopicsId = array();
                $aBlogId = array();
                $aUserId = array();
                foreach ($aTopics as $oTopic) {
                    $aTopicsId[] = $oTopic->getId();
                    $aBlogId[] = $oTopic->getBlogId();
                    $aUserId[] = $oTopic->getUserId();
                }
                if ($bResult = $this->oMapper->DeleteTopic($aTopicsId)) {
                    $bResult = $this->DeleteTopicAdditionalData($aTopicsId);
                    $this->DeleteTopicValuesByTopicId($aTopicsId);
                    $this->DeleteTopicTagsByTopicId($aTopicsId);
                    $this->DeleteMresources($aTopics);
                    E::ModuleBlog()->RecalculateCountTopicByBlogId($aBlogId);
                }

                // * Чистим зависимые кеши
                $aCacheTags = array('topic_update');
                foreach($aUserId as $iUserId) {
                    $aCacheTags[] = 'topic_update_user_' . $iUserId;
                }
                E::ModuleCache()->CleanByTags($aCacheTags);
                foreach($aTopicsId as $iTopicId) {
                    E::ModuleCache()->Delete('topic_' . $iTopicId);
                }

                return $bResult;
            }
        }
        return false;
    }

    /**
     * Удаление топиков по массиву ID пользователей
     *
     * @param int[] $aUsersId
     *
     * @return bool
     */
    public function DeleteTopicsByUsersId($aUsersId) {

        $aFilter = array(
            'user_id' => $aUsersId,
        );
        $aTopicsId = $this->oMapper->GetAllTopics($aFilter);

        if ($bResult = $this->oMapper->DeleteTopic($aTopicsId)) {
            $bResult = $this->DeleteTopicAdditionalData($aTopicsId);
        }

        // * Чистим зависимые кеши
        $aTags = array('topic_update');
        foreach ($aUsersId as $nUserId) {
            $aTags[] = 'topic_update_user_' . $nUserId;
        }
        E::ModuleCache()->CleanByTags($aTags);
        if ($aTopicsId) {
            // * Чистим зависимые кеши
            $aCacheTags = array('topic_update');
            foreach($aUsersId as $iUserId) {
                $aCacheTags[] = 'topic_update_user_' . $iUserId;
            }
            E::ModuleCache()->CleanByTags($aCacheTags);
            foreach($aTopicsId as $iTopicId) {
                E::ModuleCache()->Delete('topic_' . $iTopicId);
            }
        }

        return $bResult;
    }

    /**
     * Удаляет свзяанные с топиком данные
     *
     * @param   int|array $aTopicId   ID топика или массив ID
     *
     * @return  bool
     */
    public function DeleteTopicAdditionalData($aTopicId) {

        if (!is_array($aTopicId)) {
            $aTopicId = array(intval($aTopicId));
        }

        // * Удаляем контент топика
        $this->DeleteTopicContentByTopicId($aTopicId);
        /**
         * Удаляем комментарии к топику.
         * При удалении комментариев они удаляются из избранного,прямого эфира и голоса за них
         */
        E::ModuleComment()->DeleteCommentByTargetId($aTopicId, 'topic');
        /**
         * Удаляем топик из избранного
         */
        $this->DeleteFavouriteTopicByArrayId($aTopicId);
        /**
         * Удаляем топик из прочитанного
         */
        $this->DeleteTopicReadByArrayId($aTopicId);
        /**
         * Удаляем голосование к топику
         */
        E::ModuleVote()->DeleteVoteByTarget($aTopicId, 'topic');
        /**
         * Удаляем теги
         */
        $this->DeleteTopicTagsByTopicId($aTopicId);
        /**
         * Удаляем фото у топика фотосета
         */
        if ($aPhotos = $this->getPhotosByTopicId($aTopicId)) {
            foreach ($aPhotos as $oPhoto) {
                $this->deleteTopicPhoto($oPhoto);
            }
        }
        /**
         * Чистим зависимые кеши
         */
        E::ModuleCache()->CleanByTags(array('topic_update'));
        foreach ($aTopicId as $nTopicId) {
            E::ModuleCache()->Delete("topic_{$nTopicId}");
        }
        return true;
    }

    /**
     * Обновляет топик
     *
     * @param ModuleTopic_EntityTopic $oTopic    Объект топика
     *
     * @return bool
     */
    public function UpdateTopic($oTopic) {

        // * Получаем топик ДО изменения
        $oTopicOld = $this->GetTopicById($oTopic->getId());
        $oTopic->setDateEdit(F::Now());
        if ($this->oMapper->UpdateTopic($oTopic)) {
            // * Если топик изменил видимость (publish) или локацию (BlogId) или список тегов
            if ($oTopicOld && (($oTopic->getPublish() != $oTopicOld->getPublish())
                || ($oTopic->getBlogId() != $oTopicOld->getBlogId())
                || ($oTopic->getTags() != $oTopicOld->getTags())
            )) {
                // * Обновляем теги
                $this->DeleteTopicTagsByTopicId($oTopic->getId());
                if ($oTopic->getPublish() && $oTopic->getTags()) {
                    $aTags = explode(',', $oTopic->getTags());
                    foreach ($aTags as $sTag) {
                        /** @var ModuleTopic_EntityTopicTag $oTag */
                        $oTag = E::GetEntity('Topic_TopicTag');
                        $oTag->setTopicId($oTopic->getId());
                        $oTag->setUserId($oTopic->getUserId());
                        $oTag->setBlogId($oTopic->getBlogId());
                        $oTag->setText($sTag);
                        $this->AddTopicTag($oTag);
                    }
                }
            }
            if ($oTopicOld && ($oTopic->getPublish() != $oTopicOld->getPublish())) {
                // * Обновляем избранное
                $this->SetFavouriteTopicPublish($oTopic->getId(), $oTopic->getPublish());
                // * Удаляем комментарий топика из прямого эфира
                if ($oTopic->getPublish() == 0) {
                    E::ModuleComment()->DeleteCommentOnlineByTargetId($oTopic->getId(), 'topic');
                }
                // * Изменяем видимость комментов
                E::ModuleComment()->SetCommentsPublish($oTopic->getId(), 'topic', $oTopic->getPublish());
            }

            if (R::GetAction() == 'content') {
                $this->processTopicFields($oTopic, 'update');
            }

            $this->UpdateMresources($oTopic);

            // чистим зависимые кеши
            E::ModuleCache()->CleanByTags(array('topic_update', "topic_update_user_{$oTopic->getUserId()}"));
            E::ModuleCache()->Delete("topic_{$oTopic->getId()}");
            return true;
        }
        return false;
    }

    /**
     * Удаление контента топика по его номеру
     *
     * @param   int|array $aTopicsId   - ID топика или массив ID
     *
     * @return  bool
     */
    public function DeleteTopicContentByTopicId($aTopicsId) {

        return $this->oMapper->DeleteTopicContentByTopicId($aTopicsId);
    }

    /**
     * Получить топик по ID
     *
     * @param int $iTopicId    ID топика
     *
     * @return ModuleTopic_EntityTopic|null
     */
    public function GetTopicById($iTopicId) {

        if (!intval($iTopicId)) {
            return null;
        }
        $aTopics = $this->GetTopicsAdditionalData($iTopicId);
        if (isset($aTopics[$iTopicId])) {
            return $aTopics[$iTopicId];
        }
        return null;
    }

    /**
     * Получить топик по URL
     *
     * @param string $sUrl
     *
     * @return ModuleTopic_EntityTopic|null
     */
    public function GetTopicByUrl($sUrl) {

        $iTopicId = $this->GetTopicIdByUrl($sUrl);
        if ($iTopicId) {
            return $this->GetTopicById($iTopicId);
        }
        return null;
    }

    /**
     * Returns topic ID by URL if it exists
     *
     * @param string $sUrl
     *
     * @return int
     */
    public function GetTopicIdByUrl($sUrl) {

        $sCacheKey = 'topic_url_' . $sUrl;
        if (false === ($iTopicId = E::ModuleCache()->Get($sCacheKey))) {
            $iTopicId = $this->oMapper->GetTopicIdByUrl($sUrl);
            if ($iTopicId) {
                E::ModuleCache()->Set($iTopicId, $sCacheKey, array("topic_update_{$iTopicId}"), 'P30D');
            } else {
                E::ModuleCache()->Set(null, $sCacheKey, array('topic_update', 'topic_new'), 'P30D');
            }
        }

        return $iTopicId;
    }

    /**
     * Получить топики по похожим URL
     *
     * @param string $sUrl
     *
     * @return ModuleTopic_EntityTopic[]
     */
    public function GetTopicsLikeUrl($sUrl) {

        $aTopicsId = $this->oMapper->GetTopicsIdLikeUrl($sUrl);
        if ($aTopicsId) {
            return $this->GetTopicsByArrayId($aTopicsId);
        }
        return array();
    }

    /**
     * Проверяет URL топика на совпадения и, если нужно, делает его уникальным
     *
     * @param string $sUrl
     *
     * @return string
     */
    public function CorrectTopicUrl($sUrl) {

        $iOnDuplicateUrl = Config::Val('module.topic.on_duplicate_url', 1);
        if ($iOnDuplicateUrl) {
            // Получаем список топиков с похожим URL
            $aTopics = $this->GetTopicsLikeUrl($sUrl);
            if ($aTopics) {
                $aExistUrls = array();
                foreach ($aTopics as $oTopic) {
                    $aExistUrls[] = $oTopic->GetTopicUrl();
                }
                $nNum = count($aTopics) + 1;
                $sNewUrl = $sUrl . '-' . $nNum;
                while (in_array($sNewUrl, $aExistUrls)) {
                    $sNewUrl = $sUrl . '-' . (++$nNum);
                }
                $sUrl = $sNewUrl;
            }
        }
        return $sUrl;
    }

    /**
     * Получить список топиков по списку ID
     *
     * @param array $aTopicsId    Список ID топиков
     *
     * @return int|array
     */
    public function GetTopicsByArrayId($aTopicsId) {

        if (!$aTopicsId) {
            return array();
        }
        if (Config::Get('sys.cache.solid')) {
            return $this->GetTopicsByArrayIdSolid($aTopicsId);
        }

        if (!is_array($aTopicsId)) {
            $aTopicsId = array($aTopicsId);
        }
        $aTopicsId = array_unique($aTopicsId);
        $aTopics = array();
        $aTopicIdNotNeedQuery = array();

        // * Делаем мульти-запрос к кешу
        $aCacheKeys = F::Array_ChangeValues($aTopicsId, 'topic_');
        if (false !== ($data = E::ModuleCache()->Get($aCacheKeys))) {

            // * проверяем что досталось из кеша
            foreach ($aCacheKeys as $iIndex => $sKey) {
                /** @var ModuleTopic_EntityTopic[] $data */
                if (array_key_exists($sKey, $data)) {
                    if ($data[$sKey]) {
                        $aTopics[$data[$sKey]->getId()] = $data[$sKey];
                    } else {
                        $aTopicIdNotNeedQuery[] = $aTopicsId[$iIndex];
                    }
                }
            }
        }

        // * Смотрим каких топиков не было в кеше и делаем запрос в БД
        $aTopicIdNeedQuery = array_diff($aTopicsId, array_keys($aTopics));
        $aTopicIdNeedQuery = array_diff($aTopicIdNeedQuery, $aTopicIdNotNeedQuery);
        $aTopicIdNeedStore = $aTopicIdNeedQuery;

        if ($aTopicIdNeedQuery) {
            if ($data = $this->oMapper->GetTopicsByArrayId($aTopicIdNeedQuery)) {
                foreach ($data as $oTopic) {
                    // * Добавляем к результату и сохраняем в кеш
                    $aTopics[$oTopic->getId()] = $oTopic;
                    E::ModuleCache()->Set($oTopic, "topic_{$oTopic->getId()}", array(), 60 * 60 * 24 * 4);
                    $aTopicIdNeedStore = array_diff($aTopicIdNeedStore, array($oTopic->getId()));
                }
            }
        }

        // * Сохраняем в кеш запросы не вернувшие результата
        foreach ($aTopicIdNeedStore as $nId) {
            E::ModuleCache()->Set(null, "topic_{$nId}", array(), 60 * 60 * 24 * 4);
        }

        // * Сортируем результат согласно входящему массиву
        $aTopics = F::Array_SortByKeysArray($aTopics, $aTopicsId);

        return $aTopics;
    }

    /**
     * Получить список топиков по списку ID, но используя единый кеш
     *
     * @param array $aTopicsId    Список ID топиков
     *
     * @return ModuleTopic_EntityTopic[]
     */
    public function GetTopicsByArrayIdSolid($aTopicsId) {

        if (!is_array($aTopicsId)) {
            $aTopicsId = array($aTopicsId);
        }
        $aTopicsId = array_unique($aTopicsId);
        $aTopics = array();
        $s = join(',', $aTopicsId);
        if (false === ($data = E::ModuleCache()->Get("topic_id_{$s}"))) {
            $data = $this->oMapper->GetTopicsByArrayId($aTopicsId);
            foreach ($data as $oTopic) {
                $aTopics[$oTopic->getId()] = $oTopic;
            }
            E::ModuleCache()->Set($aTopics, "topic_id_{$s}", array("topic_update"), 60 * 60 * 24 * 1);
            return $aTopics;
        }
        return $data;
    }

    /**
     * Получает список топиков из избранного
     *
     * @param  int $nUserId      ID пользователя
     * @param  int $iCurrPage    Номер текущей страницы
     * @param  int $iPerPage     Количество элементов на страницу
     *
     * @return array('collection'=>array,'count'=>int)
     */
    public function GetTopicsFavouriteByUserId($nUserId, $iCurrPage, $iPerPage) {

        $aCloseTopics = array();
        /**
         * Получаем список идентификаторов избранных записей
         */
        $data = ($this->oUserCurrent && $nUserId == $this->oUserCurrent->getId())
            ? E::ModuleFavourite()->GetFavouritesByUserId($nUserId, 'topic', $iCurrPage, $iPerPage, $aCloseTopics)
            : E::ModuleFavourite()->GetFavouriteOpenTopicsByUserId($nUserId, $iCurrPage, $iPerPage);

        // * Получаем записи по переданому массиву айдишников
        if ($data['collection']) {
            $data['collection'] = $this->GetTopicsAdditionalData($data['collection']);
        }

        if ($data['collection'] && !E::IsAdmin()) {
            $aAllowBlogTypes = E::ModuleBlog()->GetOpenBlogTypes();
            if ($this->oUserCurrent) {
                $aClosedBlogs = E::ModuleBlog()->GetAccessibleBlogsByUser($this->oUserCurrent);
            } else {
                $aClosedBlogs = array();
            }
            foreach ($data['collection'] as $iId=>$oTopic) {
                $oBlog = $oTopic->getBlog();
                if ($oBlog) {
                    if (!in_array($oBlog->getType(), $aAllowBlogTypes) && !in_array($oBlog->getId(), $aClosedBlogs)) {
                        $oTopic->setTitle('...');
                        $oTopic->setText(E::ModuleLang()->Get('acl_cannot_show_content'));
                        $oTopic->setTextShort(E::ModuleLang()->Get('acl_cannot_show_content'));
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Возвращает число топиков в избранном
     *
     * @param  int $nUserId    ID пользователя
     *
     * @return int
     */
    public function GetCountTopicsFavouriteByUserId($nUserId) {

        $aCloseTopics = array();
        return ($this->oUserCurrent && $nUserId == $this->oUserCurrent->getId())
            ? E::ModuleFavourite()->GetCountFavouritesByUserId($nUserId, 'topic', $aCloseTopics)
            : E::ModuleFavourite()->GetCountFavouriteOpenTopicsByUserId($nUserId);
    }

    /**
     * Список топиков по фильтру
     *
     * @param  array      $aFilter       Фильтр
     * @param  int        $iPage         Номер страницы
     * @param  int        $iPerPage      Количество элементов на страницу
     * @param  array|null $aAllowData    Список типов данных для подгрузки в топики
     *
     * @return array('collection'=>array,'count'=>int)
     */
    public function GetTopicsByFilter($aFilter, $iPage = 1, $iPerPage = 10, $aAllowData = null) {

        if (!is_numeric($iPage) || $iPage <= 0) {
            $iPage = 1;
        }

        $sCacheKey = 'topic_filter_' . serialize($aFilter) . "_{$iPage}_{$iPerPage}";
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = array(
                'collection' => $this->oMapper->GetTopics($aFilter, $iCount, $iPage, $iPerPage),
                'count'      => $iCount
            );
            E::ModuleCache()->Set($data, $sCacheKey, array('topic_update', 'topic_new'), 'P1D');
        }
        if ($data['collection']) {
            $data['collection'] = $this->GetTopicsAdditionalData($data['collection'], $aAllowData);
        }
        return $data;
    }

    /**
     * Количество топиков по фильтру
     *
     * @param array $aFilter    Фильтр
     *
     * @return int
     */
    public function GetCountTopicsByFilter($aFilter) {

        $sTmpCacheKey = 'get_count_topics_by_' . serialize($aFilter) . '_' . E::UserId();
        if (FALSE === ($iResult = E::ModuleCache()->GetTmp($sTmpCacheKey))) {

            $iResult = 0;
            if (isset($aFilter['blog_type'])) {
                $aBlogsType = (array)$aFilter['blog_type'];
                unset($aFilter['blog_type']);
                if (isset($aBlogsType['*'])) {
                    $aBlogsId = $aBlogsType['*'];
                    unset($aBlogsType['*']);
                } else {
                    $aBlogsId = array();
                }
                $sCacheKey = 'topic_count_by_blog_type_' . serialize($aFilter);
                if (false === ($aData = E::ModuleCache()->Get($sCacheKey))) {
                    $aData = $this->oMapper->GetCountTopicsByBlogtype($aFilter);
                    E::ModuleCache()->Set($aData, $sCacheKey, array('topic_update', 'topic_new', 'blog_update', 'blog_new'), 'P1D');
                }

                if ($aData) {
                    foreach($aBlogsType as $sBlogType) {
                        if (isset($aData[$sBlogType])) {
                            $iResult += $aData[$sBlogType];
                        }
                    }
                }
                if ($aBlogsId) {
                    $aFilter['blog_id'] = $aBlogsId;
                    $aFilter['blog_type_exclude'] = $aBlogsType;
                    $iCount = $this->GetCountTopicsByFilter($aFilter);
                    $iResult += $iCount;
                }
                return $iResult;
            } else {
                $sCacheKey = 'topic_count_' . serialize($aFilter);
                if (false === ($iResult = E::ModuleCache()->Get($sCacheKey))) {
                    $iResult = $this->oMapper->GetCountTopics($aFilter);
                    E::ModuleCache()->Set($iResult, $sCacheKey, array('topic_update', 'topic_new'), 'P1D');
                }
            }
            E::ModuleCache()->SetTmp($iResult, $sTmpCacheKey);
        }
        return $iResult ? $iResult : 0;
    }

    /**
     * Количество черновиков у пользователя
     *
     * @param int $nUserId    ID пользователя
     *
     * @return int
     */
    public function GetCountDraftTopicsByUserId($nUserId) {

        return $this->GetCountTopicsByFilter(
            array(
                 'user_id'       => $nUserId,
                 'topic_publish' => 0
            )
        );
    }

    /**
     * @param array $aFilter
     */
    public function SetTopicsFilter($aFilter) {

        $this->aTopicsFilter = $aFilter;
    }

    /**
     * @return array
     */
    public function GetTopicsFilter() {

        return $this->aTopicsFilter;
    }

    /**
     * Return filter for topic list by name and params
     *
     * @param string $sFilterName
     * @param array  $aParams
     *
     * @return array
     */
    public function GetNamedFilter($sFilterName, $aParams = array()) {

        $aFilter = $this->GetTopicsFilter();
        switch ($sFilterName) {
            case 'good': // Filter for good topics
                $aFilter['topic_rating']  = array(
                        'value'         => empty($aParams['rating']) ? 0 : intval($aParams['rating']),
                        'type'          => 'top',
                        'publish_index' => 1,
                    );
                break;
            case 'bad': // Filter for good topics
                $aFilter['topic_rating']  = array(
                        'value'         => empty($aParams['rating']) ? 0 : intval($aParams['rating']),
                        'type'          => 'down',
                        'publish_index' => 1,
                    );
                break;
            case 'new': // Filter for new topics
                $sDate = date('Y-m-d H:00:00', time() - Config::Get('module.topic.new_time'));
                $aFilter['topic_new'] = $sDate;
                break;
            case 'all': // Filter for ALL topics
            case 'new_all': // Filter for ALL new topics
                // Nothing others
                break;
            case 'discussed': //
                if (!empty($aParams['period'])) {
                    if (is_numeric($aParams['period'])) {
                        // количество последних секунд
                        $sPeriod = date('Y-m-d H:00:00', time() - intval($aParams['period']));
                    } else {
                        $sPeriod = $aParams['period'];
                    }
                    $aFilter['topic_date_more'] = $sPeriod;
                }
                if (!isset($aFilter['order'])) {
                    $aFilter['order'] = array();
                }
                $aFilter['order'][] = 't.topic_count_comment DESC';
                $aFilter['order'][] = 't.topic_date_show DESC';
                $aFilter['order'][] = 't.topic_id DESC';
                break;
            case 'top':
                if (!empty($aParams['period'])) {
                    if (is_numeric($aParams['period'])) {
                        // количество последних секунд
                        $sPeriod = date('Y-m-d H:00:00', time() - intval($aParams['period']));
                    } else {
                        $sPeriod = $aParams['period'];
                    }
                    $aFilter['topic_date_more'] = $sPeriod;
                }
                if (!isset($aFilter['order'])) {
                    $aFilter['order'] = array();
                }
                $aFilter['order'][] = 't.topic_rating DESC';
                $aFilter['order'][] = 't.topic_date_show DESC';
                $aFilter['order'][] = 't.topic_id DESC';
                break;
            default:
                // Nothing others
        }

        if (!empty($aParams['blog_id'])) {
            $aFilter['blog_id'] = intval($aParams['blog_id']);
        } else {
            $aFilter['blog_type'] = empty($aParams['personal']) ? E::ModuleBlog()->GetOpenBlogTypes() : 'personal';

            // If a user is authorized then adds blogs on which it is subscribed
            if (E::IsUser() && !empty($aParams['accessible']) && empty($aParams['personal'])) {
                $aOpenBlogs = E::ModuleBlog()->GetAccessibleBlogsByUser(E::User());
                if (count($aOpenBlogs)) {
                    $aFilter['blog_type']['*'] = $aOpenBlogs;
                }
            }
        }
        if (isset($aParams['personal']) && $aParams['personal'] === false && $aFilter['blog_type'] && is_array($aFilter['blog_type'])) {
            if (false !== ($iKey = array_search('personal', $aFilter['blog_type']))) {
                unset($aFilter['blog_type'][$iKey]);
            }
        }
        if (!empty($aParams['topic_type'])) {
            $aFilter['topic_type'] = $aParams['topic_type'];
        }
        if (!empty($aParams['user_id'])) {
            $aFilter['user_id'] = $aParams['user_id'];
        }
        if (isset($aParams['topic_published'])) {
            $aFilter['topic_publish'] = ($aParams['topic_published'] ? 1 : 0);
        }

        return $aFilter;
    }

    /**
     * Получает список хороших топиков для вывода на главную страницу (из всех блогов, как коллективных так и персональных)
     *
     * @param  int  $iPage          Номер страницы
     * @param  int  $iPerPage       Количество элементов на страницу
     * @param  bool $bAddAccessible Указывает на необходимость добавить в выдачу топики,
     *                              из блогов доступных пользователю. При указании false,
     *                              в выдачу будут переданы только топики из общедоступных блогов.
     *
     * @return array
     */
    public function GetTopicsGood($iPage, $iPerPage, $bAddAccessible = true) {

        $aFilter = $this->GetNamedFilter('good', array('accessible' => $bAddAccessible, 'rating' => Config::Get('module.blog.index_good')));
        return $this->GetTopicsByFilter($aFilter, $iPage, $iPerPage);
    }

    /**
     * Получает список новых топиков, ограничение новизны по дате из конфига
     *
     * @param  int  $iPage          Номер страницы
     * @param  int  $iPerPage       Количество элементов на страницу
     * @param  bool $bAddAccessible Указывает на необходимость добавить в выдачу топики,
     *                              из блогов доступных пользователю. При указании false,
     *                              в выдачу будут переданы только топики из общедоступных блогов.
     *
     * @return array
     */
    public function GetTopicsNew($iPage, $iPerPage, $bAddAccessible = true) {

        $aFilter = $this->GetNamedFilter('new', array('accessible' => $bAddAccessible));
        return $this->GetTopicsByFilter($aFilter, $iPage, $iPerPage);
    }

    /**
     * Получает список ВСЕХ новых топиков
     *
     * @param  int  $iPage          Номер страницы
     * @param  int  $iPerPage       Количество элементов на страницу
     * @param  bool $bAddAccessible Указывает на необходимость добавить в выдачу топики,
     *                              из блогов доступных пользователю. При указании false,
     *                              в выдачу будут переданы только топики из общедоступных блогов.
     *
     * @return array
     */
    public function GetTopicsNewAll($iPage, $iPerPage, $bAddAccessible = true) {

        $aFilter = $this->GetNamedFilter('all', array('accessible' => $bAddAccessible));
        return $this->GetTopicsByFilter($aFilter, $iPage, $iPerPage);
    }

    /**
     * Получает список ВСЕХ обсуждаемых топиков
     *
     * @param  int        $iPage          Номер страницы
     * @param  int        $iPerPage       Количество элементов на страницу
     * @param  int|string $sPeriod        Период в виде секунд или конкретной даты
     * @param  bool       $bAddAccessible Указывает на необходимость добавить в выдачу топики,
     *                                    из блогов доступных пользователю. При указании false,
     *                                    в выдачу будут переданы только топики из общедоступных блогов.
     *
     * @return array
     */
    public function GetTopicsDiscussed($iPage, $iPerPage, $sPeriod = null, $bAddAccessible = true) {

        $aFilter = $this->GetNamedFilter('discussed', array('period' => $sPeriod, 'accessible' => $bAddAccessible));
        return $this->GetTopicsByFilter($aFilter, $iPage, $iPerPage);
    }

    /**
     * Получает список ВСЕХ рейтинговых топиков
     *
     * @param  int        $iPage          Номер страницы
     * @param  int        $iPerPage       Количество элементов на страницу
     * @param  int|string $sPeriod        Период в виде секунд или конкретной даты
     * @param  bool       $bAddAccessible Указывает на необходимость добавить в выдачу топики,
     *                                    из блогов доступных пользователю. При указании false,
     *                                    в выдачу будут переданы только топики из общедоступных блогов.
     *
     * @return array
     */
    public function GetTopicsTop($iPage, $iPerPage, $sPeriod = null, $bAddAccessible = true) {

        $aFilter = $this->GetNamedFilter('top', array('period' => $sPeriod, 'accessible' => $bAddAccessible));
        return $this->GetTopicsByFilter($aFilter, $iPage, $iPerPage);
    }

    /**
     * Получает заданое число последних топиков
     *
     * @param int $nCount    Количество
     *
     * @return array
     */
    public function GetTopicsLast($nCount) {

        $aFilter = $this->GetNamedFilter('default', array('accessible' => true));
        return $this->GetTopicsByFilter($aFilter, 1, $nCount);
    }

    /**
     * список топиков из персональных блогов
     *
     * @param int        $iPage        Номер страницы
     * @param int        $iPerPage     Количество элементов на страницу
     * @param string     $sShowType    Тип выборки топиков
     * @param string|int $sPeriod      Период в виде секунд или конкретной даты
     *
     * @return array
     */
    public function GetTopicsPersonal($iPage, $iPerPage, $sShowType = 'good', $sPeriod = null) {

        switch ($sShowType) {
            case 'good':
                $aFilter = $this->GetNamedFilter('good', array('personal' => true, 'rating' => Config::Get('module.blog.personal_good'), 'period' => $sPeriod));
                break;
            case 'bad':
                $aFilter = $this->GetNamedFilter('bad', array('personal' => true, 'rating' => Config::Get('module.blog.personal_good'), 'period' => $sPeriod));
                break;
            case 'new':
                $aFilter = $this->GetNamedFilter('new', array('personal' => true, 'period' => $sPeriod));
                break;
            case 'all':
            case 'newall':
                $aFilter = $this->GetNamedFilter('all', array('personal' => true, 'period' => $sPeriod));
                break;
            case 'discussed':
                $aFilter = $this->GetNamedFilter('discussed', array('personal' => true, 'period' => $sPeriod));
                break;
            case 'top':
                $aFilter = $this->GetNamedFilter('top', array('personal' => true, 'period' => $sPeriod));
                break;
            default:
                $aFilter = $this->GetNamedFilter('default', array('personal' => true, 'period' => $sPeriod));
                break;
        }
        return $this->GetTopicsByFilter($aFilter, $iPage, $iPerPage);
    }

    /**
     * Получает число новых топиков в персональных блогах
     *
     * @return int
     */
    public function GetCountTopicsPersonalNew() {

        $aFilter = $this->GetNamedFilter('new', array('personal' => true));
        return $this->GetCountTopicsByFilter($aFilter);
    }

    /**
     * Получает список топиков по юзеру
     *
     * @param int|object $xUser Пользователь
     * @param int $bPublished   Флаг публикации топика
     * @param int $iPage        Номер страницы
     * @param int $iPerPage     Количество элементов на страницу
     *
     * @return array
     */
    public function GetTopicsPersonalByUser($xUser, $bPublished, $iPage, $iPerPage) {

        $iUserId = (is_object($xUser) ? $xUser->getId() : intval($xUser));
        $aFilter = $this->GetNamedFilter('default', array(
            'user_id' => $iUserId,
            'topic_published' => $bPublished,
        ));

        return $this->GetTopicsByFilter($aFilter, $iPage, $iPerPage);
    }

    /**
     * Возвращает количество топиков которые создал юзер
     *
     * @param int|object $xUser Пользователь
     * @param bool $bPublished  Флаг публикации топика
     *
     * @return array
     */
    public function GetCountTopicsPersonalByUser($xUser, $bPublished) {

        $iUserId = (is_object($xUser) ? $xUser->getId() : intval($xUser));
        $aFilter = $this->GetNamedFilter('default', array(
            'user_id' => $iUserId,
            'topic_published' => $bPublished,
        ));

        $sCacheKey = 'topic_count_user_' . serialize($aFilter);
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetCountTopics($aFilter);
            E::ModuleCache()->Set($data, $sCacheKey, array("topic_update_user_{$iUserId}"), 'P1D');
        }
        return $data;
    }

    /**
     * Получает список топиков из указанного блога
     *
     * @param  int|array $nBlogId       - ID блога | массив ID блогов
     * @param  int       $iPage         - Номер страницы
     * @param  int       $iPerPage      - Количество элементов на страницу
     * @param  array     $aAllowData    - Список типов данных для подгрузки в топики
     * @param  bool      $bIdsOnly      - Возвращать только ID или список объектов
     *
     * @return array
     */
    public function GetTopicsByBlogId($nBlogId, $iPage = 0, $iPerPage = 0, $aAllowData = array(), $bIdsOnly = true) {

        $aFilter = array('blog_id' => $nBlogId);

        if (!$aTopics = $this->GetTopicsByFilter($aFilter, $iPage, $iPerPage, $aAllowData)) {
            return false;
        }

        return ($bIdsOnly)
            ? array_keys($aTopics['collection'])
            : $aTopics;
    }

    /**
     * Список топиков из коллективных блогов
     *
     * @param int    $iPage        Номер страницы
     * @param int    $iPerPage     Количество элементов на страницу
     * @param string $sShowType    Тип выборки топиков
     * @param string $sPeriod      Период в виде секунд или конкретной даты
     *
     * @return array
     */
    public function GetTopicsCollective($iPage, $iPerPage, $sShowType = 'good', $sPeriod = null) {

        switch ($sShowType) {
            case 'good':
                $aFilter = $this->GetNamedFilter('good', array('accessible' => true, 'personal' => false, 'rating' => Config::Get('module.blog.collective_good'), 'period' => $sPeriod));
                break;
            case 'bad':
                $aFilter = $this->GetNamedFilter('bad', array('accessible' => true, 'personal' => false, 'rating' => Config::Get('module.blog.collective_good'), 'period' => $sPeriod));
                break;
            case 'new':
                $aFilter = $this->GetNamedFilter('new', array('accessible' => true, 'personal' => false, 'period' => $sPeriod));
                break;
            case 'all':
            case 'newall':
                $aFilter = $this->GetNamedFilter('all', array('accessible' => true, 'personal' => false, 'period' => $sPeriod));
                break;
            case 'discussed':
                $aFilter = $this->GetNamedFilter('discussed', array('accessible' => true, 'personal' => false, 'period' => $sPeriod));
                break;
            case 'top':
                $aFilter = $this->GetNamedFilter('top', array('accessible' => true, 'personal' => false, 'period' => $sPeriod));
                break;
            default:
                $aFilter = $this->GetNamedFilter('default', array('accessible' => true, 'personal' => false, 'period' => $sPeriod));
                break;
        }

        return $this->GetTopicsByFilter($aFilter, $iPage, $iPerPage);
    }

    /**
     * Получает число новых топиков в коллективных блогах
     *
     * @return int
     */
    public function GetCountTopicsCollectiveNew() {

        $aFilter = $this->GetNamedFilter('new', array('accessible' => true, 'personal' => false));
        return $this->GetCountTopicsByFilter($aFilter);
    }

    /**
     * Получает топики по рейтингу и дате
     *
     * @param string $sDate     Дата
     * @param int    $iLimit    Количество
     *
     * @return array
     */
    public function GetTopicsRatingByDate($sDate, $iLimit = 20) {
        /**
         * Получаем список блогов, топики которых нужно исключить из выдачи
         */
        $aCloseBlogs = ($this->oUserCurrent)
            ? E::ModuleBlog()->GetInaccessibleBlogsByUser($this->oUserCurrent)
            : E::ModuleBlog()->GetInaccessibleBlogsByUser();

        $sCacheKey = "topic_rating_{$sDate}_{$iLimit}_" . serialize($aCloseBlogs);
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetTopicsRatingByDate($sDate, $iLimit, $aCloseBlogs);
            E::ModuleCache()->Set($data, $sCacheKey, array('topic_update'), 'P3D');
        }
        if ($data) {
            $data = $this->GetTopicsAdditionalData($data);
        }
        return $data;
    }

    /**
     * Список топиков из блога
     *
     * @param ModuleBlog_EntityBlog $oBlog        Объект блога
     * @param int                   $iPage        Номер страницы
     * @param int                   $iPerPage     Количество элементов на страницу
     * @param string                $sShowType    Тип выборки топиков
     * @param string                $sPeriod      Период в виде секунд или конкретной даты
     *
     * @return array
     */
    public function GetTopicsByBlog($oBlog, $iPage, $iPerPage, $sShowType = 'good', $sPeriod = null) {

        $iBlogId = (is_object($oBlog) ? $oBlog->getId() : intval($oBlog));
        switch ($sShowType) {
            case 'good':
                $aFilter = $this->GetNamedFilter('good', array('blog_id' => $iBlogId, 'rating' => Config::Get('module.blog.collective_good'), 'period' => $sPeriod));
                break;
            case 'bad':
                $aFilter = $this->GetNamedFilter('bad', array('blog_id' => $iBlogId, 'rating' => Config::Get('module.blog.collective_good'), 'period' => $sPeriod));
                break;
            case 'new':
                $aFilter = $this->GetNamedFilter('new', array('blog_id' => $iBlogId, 'period' => $sPeriod));
                break;
            case 'all':
            case 'newall':
                $aFilter = $this->GetNamedFilter('all', array('blog_id' => $iBlogId, 'period' => $sPeriod));
                break;
            case 'discussed':
                $aFilter = $this->GetNamedFilter('discussed', array('blog_id' => $iBlogId, 'period' => $sPeriod));
                break;
            case 'top':
                $aFilter = $this->GetNamedFilter('top', array('blog_id' => $iBlogId, 'period' => $sPeriod));
                break;
            default:
                $aFilter = $this->GetNamedFilter('default', array('blog_id' => $iBlogId, 'period' => $sPeriod));
                break;
        }
        return $this->GetTopicsByFilter($aFilter, $iPage, $iPerPage);
    }

    /**
     * Получает число новых топиков из блога
     *
     * @param ModuleBlog_EntityBlog $oBlog Объект блога
     *
     * @return int
     */
    public function GetCountTopicsByBlogNew($oBlog) {

        $iBlogId = (is_object($oBlog) ? $oBlog->getId() : intval($oBlog));
        $aFilter = $this->GetNamedFilter('new', array('blog_id' => $iBlogId));
        return $this->GetCountTopicsByFilter($aFilter);
    }

    /**
     * Получает список топиков по тегу
     *
     * @param  string $sTag           Тег
     * @param  int    $iPage          Номер страницы
     * @param  int    $iPerPage       Количество элементов на страницу
     * @param  bool   $bAddAccessible Указывает на необходимость добавить в выдачу топики,
     *                                из блогов доступных пользователю. При указании false,
     *                                в выдачу будут переданы только топики из общедоступных блогов.
     *
     * @return array
     */
    public function GetTopicsByTag($sTag, $iPage, $iPerPage, $bAddAccessible = true) {

        $aCloseBlogs = ($this->oUserCurrent && $bAddAccessible)
            ? E::ModuleBlog()->GetInaccessibleBlogsByUser($this->oUserCurrent)
            : E::ModuleBlog()->GetInaccessibleBlogsByUser();

        $sCacheKey = "topic_tag_{$sTag}_{$iPage}_{$iPerPage}_" . serialize($aCloseBlogs);
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = array(
                'collection' => $this->oMapper->GetTopicsByTag($sTag, $aCloseBlogs, $iCount, $iPage, $iPerPage),
                'count'      => $iCount
            );
            E::ModuleCache()->Set($data, $sCacheKey, array('topic_update', 'topic_new'), 'P1D');
        }
        if ($data['collection']) {
            $data['collection'] = $this->GetTopicsAdditionalData($data['collection']);
        }
        return $data;
    }

    /**
     * Получает список топиков по типам
     *
     * @param  int    $iPage          Номер страницы
     * @param  int    $iPerPage       Количество элементов на страницу
     * @param  string $sType
     * @param  bool   $bAddAccessible Указывает на необходимость добавить в выдачу топики,
     *                                из блогов доступных пользователю. При указании false,
     *                                в выдачу будут переданы только топики из общедоступных блогов.
     *
     * @return array
     */
    public function GetTopicsByType($iPage, $iPerPage, $sType, $bAddAccessible = true) {

        $aFilter = $this->GetNamedFilter('default', array('accessible' => $bAddAccessible, 'topic_type' => $sType));
        return $this->GetTopicsByFilter($aFilter, $iPage, $iPerPage);
    }

    /**
     * Получает список тегов топиков
     *
     * @param int   $nLimit           Количество
     * @param array $aExcludeTopic    Список ID топиков для исключения
     *
     * @return array
     */
    public function GetTopicTags($nLimit, $aExcludeTopic = array()) {

        $sCacheKey = "tag_{$nLimit}_" . serialize($aExcludeTopic);
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetTopicTags($nLimit, $aExcludeTopic);
            E::ModuleCache()->Set($data, $sCacheKey, array('topic_update', 'topic_new'), 'P1D');
        }
        return $data;
    }

    /**
     * Получает список тегов из топиков открытых блогов (open,personal)
     *
     * @param  int      $nLimit     - Количество
     * @param  int|null $nUserId    - ID пользователя, чью теги получаем
     *
     * @return array
     */
    public function GetOpenTopicTags($nLimit, $nUserId = null) {

        $sCacheKey = "tag_{$nLimit}_{$nUserId}_open";
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetOpenTopicTags($nLimit, $nUserId);
            E::ModuleCache()->Set($data, $sCacheKey, array('topic_update', 'topic_new'), 'P1D');
        }
        return $data;
    }

    /**
     * Увеличивает у топика число комментов
     *
     * @param int $nTopicId    ID топика
     *
     * @return bool
     */
    public function increaseTopicCountComment($nTopicId) {

        $bResult = $this->oMapper->increaseTopicCountComment($nTopicId);
        if ($bResult) {
            E::ModuleCache()->Delete("topic_{$nTopicId}");
            E::ModuleCache()->CleanByTags(array('topic_update'));
        }
        return $bResult;
    }

    /**
     * @param $nTopicId
     *
     * @return bool
     */
    public function RecalcCountOfComments($nTopicId) {

        $bResult = $this->oMapper->RecalcCountOfComments($nTopicId);
        if ($bResult) {
            E::ModuleCache()->Delete("topic_{$nTopicId}");
            E::ModuleCache()->CleanByTags(array('topic_update'));
        }
        return $bResult;
    }

    /**
     * Получает привязку топика к ибранному (добавлен ли топик в избранное у юзера)
     *
     * @param int $nTopicId    ID топика
     * @param int $nUserId     ID пользователя
     *
     * @return ModuleFavourite_EntityFavourite
     */
    public function GetFavouriteTopic($nTopicId, $nUserId) {

        return E::ModuleFavourite()->GetFavourite($nTopicId, 'topic', $nUserId);
    }

    /**
     * Получить список избранного по списку айдишников
     *
     * @param array $aTopicsId    Список ID топиков
     * @param int   $nUserId      ID пользователя
     *
     * @return ModuleFavourite_EntityFavourite[]
     */
    public function GetFavouriteTopicsByArray($aTopicsId, $nUserId) {

        return E::ModuleFavourite()->GetFavouritesByArray($aTopicsId, 'topic', $nUserId);
    }

    /**
     * Получить список избранного по списку айдишников, но используя единый кеш
     *
     * @param array $aTopicsId    Список ID топиков
     * @param int   $nUserId      ID пользователя
     *
     * @return array
     */
    public function GetFavouriteTopicsByArraySolid($aTopicsId, $nUserId) {

        return E::ModuleFavourite()->GetFavouritesByArraySolid($aTopicsId, 'topic', $nUserId);
    }

    /**
     * Добавляет топик в избранное
     *
     * @param ModuleFavourite_EntityFavourite $oFavouriteTopic    Объект избранного
     *
     * @return bool
     */
    public function AddFavouriteTopic($oFavouriteTopic) {

        return E::ModuleFavourite()->AddFavourite($oFavouriteTopic);
    }

    /**
     * Удаляет топик из избранного
     *
     * @param ModuleFavourite_EntityFavourite $oFavouriteTopic    Объект избранного
     *
     * @return bool
     */
    public function DeleteFavouriteTopic($oFavouriteTopic) {

        return E::ModuleFavourite()->DeleteFavourite($oFavouriteTopic);
    }

    /**
     * Устанавливает переданный параметр публикации таргета (топика)
     *
     * @param  int  $nTopicId    - ID топика
     * @param  bool $bPublish    - Флаг публикации топика
     *
     * @return bool
     */
    public function SetFavouriteTopicPublish($nTopicId, $bPublish) {

        return E::ModuleFavourite()->SetFavouriteTargetPublish($nTopicId, 'topic', $bPublish);
    }

    /**
     * Удаляет топики из избранного по списку
     *
     * @param  array $aTopicsId    Список ID топиков
     *
     * @return bool
     */
    public function DeleteFavouriteTopicByArrayId($aTopicsId) {

        return E::ModuleFavourite()->DeleteFavouriteByTargetId($aTopicsId, 'topic');
    }

    /**
     * Получает список тегов по первым буквам тега
     *
     * @param string $sTag      - Тэг
     * @param int    $nLimit    - Количество
     *
     * @return bool
     */
    public function GetTopicTagsByLike($sTag, $nLimit) {

        $sCacheKey = "tag_like_{$sTag}_{$nLimit}";
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetTopicTagsByLike($sTag, $nLimit);
            E::ModuleCache()->Set($data, $sCacheKey, array("topic_update", "topic_new"), 60 * 60 * 24 * 3);
        }
        return $data;
    }

    /**
     * Обновляем/устанавливаем дату прочтения топика, если читаем его первый раз то добавляем
     *
     * @param ModuleTopic_EntityTopicRead $oTopicRead    Объект факта чтения топика
     *
     * @return bool
     */
    public function SetTopicRead($oTopicRead) {

        if ($this->GetTopicRead($oTopicRead->getTopicId(), $oTopicRead->getUserId())) {
            return $this->UpdateTopicRead($oTopicRead);
        } else {
            return $this->AddTopicRead($oTopicRead);
        }
    }

    /**
     * @param ModuleTopic_EntityTopicRead $oTopicRead
     *
     * @return bool
     */
    public function AddTopicRead($oTopicRead) {

        $xResult = $this->oMapper->AddTopicRead($oTopicRead);
        E::ModuleCache()->Delete("topic_read_{$oTopicRead->getTopicId()}_{$oTopicRead->getUserId()}");
        E::ModuleCache()->CleanByTags(array("topic_read_user_{$oTopicRead->getUserId()}"));

        return $xResult;
    }

    /**
     * @param ModuleTopic_EntityTopicRead $oTopicRead
     *
     * @return int
     */
    public function UpdateTopicRead($oTopicRead) {

        $xResult = $this->oMapper->UpdateTopicRead($oTopicRead);
        E::ModuleCache()->Delete("topic_read_{$oTopicRead->getTopicId()}_{$oTopicRead->getUserId()}");
        E::ModuleCache()->CleanByTags(array("topic_read_user_{$oTopicRead->getUserId()}"));

        return $xResult;
    }

    /**
     * Получаем дату прочтения топика юзером
     *
     * @param int $iTopicId    - ID топика
     * @param int $iUserId     - ID пользователя
     *
     * @return ModuleTopic_EntityTopicRead|null
     */
    public function GetTopicRead($iTopicId, $iUserId) {

        $data = $this->GetTopicsReadByArray(array($iTopicId), $iUserId);
        if (isset($data[$iTopicId])) {
            return $data[$iTopicId];
        }
        return null;
    }

    /**
     * Удаляет записи о чтении записей по списку идентификаторов
     *
     * @param  array|int $aTopicsId    Список ID топиков
     *
     * @return bool
     */
    public function DeleteTopicReadByArrayId($aTopicsId) {

        if (!is_array($aTopicsId)) {
            $aTopicsId = array($aTopicsId);
        }
        return $this->oMapper->DeleteTopicReadByArrayId($aTopicsId);
    }

    /**
     * Получить список просмотром/чтения топиков по списку айдишников
     *
     * @param array $aTopicsId    - Список ID топиков
     * @param int   $iUserId      - ID пользователя
     *
     * @return ModuleTopic_EntityTopicRead[]
     */
    public function GetTopicsReadByArray($aTopicsId, $iUserId) {

        if (!$aTopicsId) {
            return array();
        }
        if (Config::Get('sys.cache.solid')) {
            return $this->GetTopicsReadByArraySolid($aTopicsId, $iUserId);
        }
        if (!is_array($aTopicsId)) {
            $aTopicsId = array($aTopicsId);
        }
        $aTopicsId = array_unique($aTopicsId);
        $aTopicsRead = array();
        $aTopicIdNotNeedQuery = array();

        // * Делаем мульти-запрос к кешу
        $aCacheKeys = F::Array_ChangeValues($aTopicsId, 'topic_read_', '_' . $iUserId);
        if (false !== ($data = E::ModuleCache()->Get($aCacheKeys))) {
            // * проверяем что досталось из кеша
            foreach ($aCacheKeys as $iIndex => $sKey) {
                /** @var ModuleTopic_EntityTopicRead[] $data */
                if (array_key_exists($sKey, $data)) {
                    if ($data[$sKey]) {
                        $aTopicsRead[$data[$sKey]->getTopicId()] = $data[$sKey];
                    } else {
                        $aTopicIdNotNeedQuery[] = $aTopicsId[$iIndex];
                    }
                }
            }
        }

        // * Смотрим каких топиков не было в кеше и делаем запрос в БД
        $aTopicIdNeedQuery = array_diff($aTopicsId, array_keys($aTopicsRead));
        $aTopicIdNeedQuery = array_diff($aTopicIdNeedQuery, $aTopicIdNotNeedQuery);
        $aTopicIdNeedStore = $aTopicIdNeedQuery;

        if ($aTopicIdNeedQuery) {
            if ($data = $this->oMapper->GetTopicsReadByArray($aTopicIdNeedQuery, $iUserId)) {
                /** @var ModuleTopic_EntityTopicRead $oTopicRead */
                foreach ($data as $oTopicRead) {
                    // * Добавляем к результату и сохраняем в кеш
                    $aTopicsRead[$oTopicRead->getTopicId()] = $oTopicRead;
                    E::ModuleCache()->Set(
                        $oTopicRead, "topic_read_{$oTopicRead->getTopicId()}_{$oTopicRead->getUserId()}", array(),
                        60 * 60 * 24 * 4
                    );
                    $aTopicIdNeedStore = array_diff($aTopicIdNeedStore, array($oTopicRead->getTopicId()));
                }
            }
        }

        // * Сохраняем в кеш запросы не вернувшие результата
        foreach ($aTopicIdNeedStore as $sId) {
            E::ModuleCache()->Set(null, "topic_read_{$sId}_{$iUserId}", array(), 60 * 60 * 24 * 4);
        }

        // * Сортируем результат согласно входящему массиву
        $aTopicsRead = F::Array_SortByKeysArray($aTopicsRead, $aTopicsId);

        return $aTopicsRead;
    }

    /**
     * Получить список просмотров/чтения топиков по списку ID, но используя единый кеш
     *
     * @param array $aTopicsId    - Список ID топиков
     * @param int   $nUserId      - ID пользователя
     *
     * @return array
     */
    public function GetTopicsReadByArraySolid($aTopicsId, $nUserId) {

        if (!is_array($aTopicsId)) {
            $aTopicsId = array($aTopicsId);
        }
        $aTopicsId = array_unique($aTopicsId);
        $aTopicsRead = array();

        $sCacheKey = "topic_read_{$nUserId}_id_" . join(',', $aTopicsId);
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetTopicsReadByArray($aTopicsId, $nUserId);
            /** @var ModuleTopic_EntityTopicRead $oTopicRead */
            foreach ($data as $oTopicRead) {
                $aTopicsRead[$oTopicRead->getTopicId()] = $oTopicRead;
            }
            E::ModuleCache()->Set($aTopicsRead, $sCacheKey, array("topic_read_user_{$nUserId}"), 'P1D');
            return $aTopicsRead;
        }
        return $data;
    }

    /**
     * Возвращает список полей по списку ID топиков
     *
     * @param array $aTopicId    Список ID топиков
     *
     * @return array
     * @TODO рефакторинг + solid
     */
    public function GetTopicValuesByArrayId($aTopicId) {

        if (!$aTopicId) {
            return array();
        }
        if (!is_array($aTopicId)) {
            $aTopicId = array($aTopicId);
        }
        $aTopicId = array_unique($aTopicId);
        $aValues = array();
        $s = join(',', $aTopicId);
        if (false === ($data = E::ModuleCache()->Get("topic_values_{$s}"))) {
            $data = $this->oMapper->GetTopicValuesByArrayId($aTopicId);
            foreach ($data as $oValue) {
                $aValues[$oValue->getTargetId()][$oValue->getFieldId()] = $oValue;
            }
            E::ModuleCache()->Set($aValues, "topic_values_{$s}", array('topic_new', 'topic_update'), 60 * 60 * 24 * 1);
            return $aValues;
        }
        return $data;
    }

    /**
     * Возвращает список полей по списку id типов контента
     *
     * @param array $aTypesId    Список ID типов контента
     *
     * @return array
     * @TODO рефакторинг + solid
     */
    public function GetFieldsByArrayId($aTypesId) {

        if (!$aTypesId) {
            return array();
        }
        if (!is_array($aTypesId)) {
            $aTypesId = array($aTypesId);
        }
        $aTypesId = array_unique($aTypesId);
        $aFields = array();
        $s = join(',', $aTypesId);
        if (false === ($data = E::ModuleCache()->Get("topic_fields_{$s}"))) {
            $data = $this->oMapper->GetFieldsByArrayId($aTypesId);
            foreach ($data as $oField) {
                $aFields[$oField->getContentId()][$oField->getFieldId()] = $oField;
            }
            E::ModuleCache()->Set($aFields, "topic_fields_{$s}", array("field_update"), 60 * 60 * 24 * 1);
            return $aFields;
        }
        return $data;
    }

    /**
     * Проверяет голосовал ли юзер за топик-вопрос
     *
     * @param int $nTopicId    ID топика
     * @param int $nUserId     ID пользователя
     *
     * @return ModuleTopic_EntityTopicQuestionVote|null
     */
    public function GetTopicQuestionVote($nTopicId, $nUserId) {

        $data = $this->GetTopicsQuestionVoteByArray(array($nTopicId), $nUserId);
        if (isset($data[$nTopicId])) {
            return $data[$nTopicId];
        }
        return null;
    }

    /**
     * Получить список голосований в топике-опросе по списку ID
     *
     * @param array $aTopicsId    - Список ID топиков
     * @param int   $iUserId      - ID пользователя
     *
     * @return array
     */
    public function GetTopicsQuestionVoteByArray($aTopicsId, $iUserId) {

        if (!$aTopicsId) {
            return array();
        }
        if (Config::Get('sys.cache.solid')) {
            return $this->GetTopicsQuestionVoteByArraySolid($aTopicsId, $iUserId);
        }
        if (!is_array($aTopicsId)) {
            $aTopicsId = array($aTopicsId);
        }
        $aTopicsId = array_unique($aTopicsId);
        $aTopicsQuestionVote = array();
        $aTopicIdNotNeedQuery = array();

        // * Делаем мульти-запрос к кешу
        $aCacheKeys = F::Array_ChangeValues($aTopicsId, 'topic_question_vote_', '_' . $iUserId);
        if (false !== ($data = E::ModuleCache()->Get($aCacheKeys))) {
            // * проверяем что досталось из кеша
            foreach ($aCacheKeys as $iIndex => $sKey) {
                /** @var ModuleTopic_EntityTopicQuestionVote[] $data */
                if (array_key_exists($sKey, $data)) {
                    if ($data[$sKey]) {
                        $aTopicsQuestionVote[$data[$sKey]->getTopicId()] = $data[$sKey];
                    } else {
                        $aTopicIdNotNeedQuery[] = $aTopicsId[$iIndex];
                    }
                }
            }
        }

        // * Смотрим каких топиков не было в кеше и делаем запрос в БД
        $aTopicIdNeedQuery = array_diff($aTopicsId, array_keys($aTopicsQuestionVote));
        $aTopicIdNeedQuery = array_diff($aTopicIdNeedQuery, $aTopicIdNotNeedQuery);
        $aTopicIdNeedStore = $aTopicIdNeedQuery;

        if ($aTopicIdNeedQuery) {
            if ($data = $this->oMapper->GetTopicsQuestionVoteByArray($aTopicIdNeedQuery, $iUserId)) {
                foreach ($data as $oTopicVote) {
                    // * Добавляем к результату и сохраняем в кеш
                    $aTopicsQuestionVote[$oTopicVote->getTopicId()] = $oTopicVote;
                    E::ModuleCache()->Set(
                        $oTopicVote, "topic_question_vote_{$oTopicVote->getTopicId()}_{$oTopicVote->getVoterId()}", array(),
                        60 * 60 * 24 * 4
                    );
                    $aTopicIdNeedStore = array_diff($aTopicIdNeedStore, array($oTopicVote->getTopicId()));
                }
            }
        }

        // * Сохраняем в кеш запросы не вернувшие результата
        foreach ($aTopicIdNeedStore as $sId) {
            E::ModuleCache()->Set(null, "topic_question_vote_{$sId}_{$iUserId}", array(), 60 * 60 * 24 * 4);
        }

        // * Сортируем результат согласно входящему массиву
        $aTopicsQuestionVote = F::Array_SortByKeysArray($aTopicsQuestionVote, $aTopicsId);

        return $aTopicsQuestionVote;
    }

    /**
     * Получить список голосований в топике-опросе по списку ID, но используя единый кеш
     *
     * @param array $aTopicsId    - Список ID топиков
     * @param int   $nUserId      - ID пользователя
     *
     * @return array
     */
    public function GetTopicsQuestionVoteByArraySolid($aTopicsId, $nUserId) {

        if (!is_array($aTopicsId)) {
            $aTopicsId = array($aTopicsId);
        }
        $aTopicsId = array_unique($aTopicsId);
        $aTopicsQuestionVote = array();

        $sCacheKey = "topic_question_vote_{$nUserId}_id_" . join(',', $aTopicsId);
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = $this->oMapper->GetTopicsQuestionVoteByArray($aTopicsId, $nUserId);
            foreach ($data as $oTopicVote) {
                $aTopicsQuestionVote[$oTopicVote->getTopicId()] = $oTopicVote;
            }
            E::ModuleCache()->Set($aTopicsQuestionVote, $sCacheKey, array("topic_question_vote_user_{$nUserId}"), 'P1D');
            return $aTopicsQuestionVote;
        }
        return $data;
    }

    /**
     * Добавляет факт голосования за топик-вопрос
     *
     * @param ModuleTopic_EntityTopicQuestionVote $oTopicQuestionVote    Объект голосования в топике-опросе
     *
     * @return bool
     */
    public function AddTopicQuestionVote(ModuleTopic_EntityTopicQuestionVote $oTopicQuestionVote) {

        $xResult = $this->oMapper->AddTopicQuestionVote($oTopicQuestionVote);
        E::ModuleCache()->Delete(
            "topic_question_vote_{$oTopicQuestionVote->getTopicId()}_{$oTopicQuestionVote->getVoterId()}"
        );
        E::ModuleCache()->CleanByTags(array("topic_question_vote_user_{$oTopicQuestionVote->getVoterId()}"));
        return $xResult;
    }

    /**
     * Получает топик по уникальному хешу(текст топика)
     *
     * @param int    - $nUserId
     * @param string - $sHash
     *
     * @return ModuleTopic_EntityTopic|null
     */
    public function GetTopicUnique($nUserId, $sHash) {

        $sId = $this->oMapper->GetTopicUnique($nUserId, $sHash);
        return $this->GetTopicById($sId);
    }

    /**
     * Рассылает уведомления о новом топике подписчикам блога
     *
     * @param ModuleBlog_EntityBlog   $oBlog         Объект блога
     * @param ModuleTopic_EntityTopic $oTopic        Объект топика
     * @param ModuleUser_EntityUser   $oUserTopic    Объект пользователя
     */
    public function SendNotifyTopicNew($oBlog, $oTopic, $oUserTopic) {

        $aBlogUsersResult = E::ModuleBlog()->GetBlogUsersByBlogId(
            $oBlog->getId(), null, null
        ); // нужно постранично пробегаться по всем
        /** @var ModuleBlog_EntityBlogUser[] $aBlogUsers */
        $aBlogUsers = $aBlogUsersResult['collection'];
        foreach ($aBlogUsers as $oBlogUser) {
            if ($oBlogUser->getUserId() == $oUserTopic->getId()) {
                continue;
            }
            E::ModuleNotify()->SendTopicNewToSubscribeBlog($oBlogUser->getUser(), $oTopic, $oBlog, $oUserTopic);
        }
        //отправляем создателю блога
        if ($oBlog->getOwnerId() != $oUserTopic->getId()) {
            E::ModuleNotify()->SendTopicNewToSubscribeBlog($oBlog->getOwner(), $oTopic, $oBlog, $oUserTopic);
        }
    }

    /**
     * Возвращает список последних топиков пользователя, опубликованных не более чем $iTimeLimit секунд назад
     *
     * @param  int   $nUserId        ID пользователя
     * @param  int   $iTimeLimit     Число секунд
     * @param  int   $iCountLimit    Количество
     * @param  array $aAllowData     Список типов данных для подгрузки в топики
     *
     * @return array
     */
    public function GetLastTopicsByUserId($nUserId, $iTimeLimit, $iCountLimit = 1, $aAllowData = array()) {

        $aFilter = array(
            'topic_publish' => 1,
            'user_id'       => $nUserId,
            'topic_new'     => date('Y-m-d H:i:s', time() - $iTimeLimit),
        );
        $aTopics = $this->GetTopicsByFilter($aFilter, 1, $iCountLimit, $aAllowData);

        return $aTopics;
    }

    /**
     * Перемещает топики в другой блог
     *
     * @param  array $aTopicsId    - Список ID топиков
     * @param  int   $nBlogId      - ID блога
     *
     * @return bool
     */
    public function MoveTopicsByArrayId($aTopicsId, $nBlogId) {

        E::ModuleCache()->CleanByTags(array("topic_update", "topic_new_blog_{$nBlogId}"));
        if ($res = $this->oMapper->MoveTopicsByArrayId($aTopicsId, $nBlogId)) {
            // перемещаем теги
            $this->oMapper->MoveTopicsTagsByArrayId($aTopicsId, $nBlogId);
            // меняем target parent у комментов
            E::ModuleComment()->UpdateTargetParentByTargetId($nBlogId, 'topic', $aTopicsId);
            // меняем target parent у комментов в прямом эфире
            E::ModuleComment()->UpdateTargetParentByTargetIdOnline($nBlogId, 'topic', $aTopicsId);
            return $res;
        }
        return false;
    }

    /**
     * Перемещает топики в другой блог
     *
     * @param  int $nBlogId       ID старого блога
     * @param  int $nBlogIdNew    ID нового блога
     *
     * @return bool
     */
    public function MoveTopics($nBlogId, $nBlogIdNew) {

        if ($bResult = $this->oMapper->MoveTopics($nBlogId, $nBlogIdNew)) {
            // перемещаем теги
            $this->oMapper->MoveTopicsTags($nBlogId, $nBlogIdNew);
            // меняем target parent у комментов
            E::ModuleComment()->MoveTargetParent($nBlogId, 'topic', $nBlogIdNew);
            // меняем target parent у комментов в прямом эфире
            E::ModuleComment()->MoveTargetParentOnline($nBlogId, 'topic', $nBlogIdNew);
            return $bResult;
        }
        E::ModuleCache()->CleanByTags(
            array("topic_update", "blog_update", "topic_new_blog_{$nBlogId}", "topic_new_blog_{$nBlogIdNew}")
        );
        E::ModuleCache()->Delete("blog_{$nBlogId}");
        E::ModuleCache()->Delete("blog_{$nBlogIdNew}");

        return false;
    }

    /**
     * Save uploaded image into store
     *
     * @param string                $sImageFile
     * @param ModuleUser_EntityUser $oUser
     * @param string                $sType
     * @param array                 $aOptions
     *
     * @return bool
     */
    protected function _saveTopicImage($sImageFile, $oUser, $sType, $aOptions = array()) {

        $sExtension = F::File_GetExtension($sImageFile, true);
        $aConfig = E::ModuleUploader()->GetConfig($sImageFile, 'images.' . $sType);
        if ($aOptions) {
            $aConfig['transform'] = F::Array_Merge($aConfig['transform'], $aOptions);
        }
        // Check whether to save the original
        if (isset($aConfig['original']['save']) && $aConfig['original']['save']) {
            $sSuffix = (isset($aConfig['original']['suffix']) ? $aConfig['original']['suffix'] : '-original');
            $sOriginalFile = F::File_Copy($sImageFile, $sImageFile . $sSuffix . '.' . $sExtension);
        } else {
            $sSuffix = '';
            $sOriginalFile = null;
        }
        // Transform image before saving
        $sFileTmp = E::ModuleImg()->TransformFile($sImageFile, $aConfig['transform']);
        if ($sFileTmp) {
            $sDirUpload = E::ModuleUploader()->GetUserImageDir($oUser->getId(), true, $sType);
            $sFileImage = E::ModuleUploader()->Uniqname($sDirUpload, $sExtension);
            if ($oStoredFile = E::ModuleUploader()->Store($sFileTmp, $sFileImage)) {
                if ($sOriginalFile) {
                    E::ModuleUploader()->Move($sOriginalFile, $oStoredFile->GetFile() . $sSuffix . '.' . $sExtension);
                }
                return $oStoredFile->GetUrl();
            }
        }
        return false;
    }

    /**
     * @param array                 $aFile
     * @param ModuleUser_EntityUser $oUser
     * @param array                 $aOptions
     *
     * @return string|bool
     */
    public function UploadTopicImageFile($aFile, $oUser, $aOptions = array()) {

        if ($sFileTmp = E::ModuleUploader()->UploadLocal($aFile)) {
            return $this->_saveTopicImage($sFileTmp, $oUser, 'topic', $aOptions);
        }
        return false;
    }

    /**
     * Загрузка изображений по переданному URL
     *
     * @param  string                $sUrl    URL изображения
     * @param  ModuleUser_EntityUser $oUser
     * @param array                 $aOptions
     *
     * @return string|int
     */
    public function UploadTopicImageUrl($sUrl, $oUser, $aOptions = array()) {

        if ($sFileTmp = E::ModuleUploader()->UploadRemote($sUrl)) {
            return $this->_saveTopicImage($sFileTmp, $oUser, 'topic', $aOptions);
        }
        return false;
    }

    /**
     * Возвращает список фотографий к топику-фотосет по списку ID фоток
     *
     * @param array|int $aPhotosId    Список ID фото
     *
     * @return array
     */
    public function GetTopicPhotosByArrayId($aPhotosId) {

        if (!$aPhotosId) {
            return array();
        }
        if (!is_array($aPhotosId)) {
            $aPhotosId = array($aPhotosId);
        }
        $aPhotosId = array_unique($aPhotosId);
        $aPhotos = array();
        $s = join(',', $aPhotosId);
        if (false === ($data = E::ModuleCache()->Get("photoset_photo_id_{$s}"))) {
            $data = $this->oMapper->GetTopicPhotosByArrayId($aPhotosId);
            foreach ($data as $oPhoto) {
                $aPhotos[$oPhoto->getId()] = $oPhoto;
            }
            E::ModuleCache()->Set($aPhotos, "photoset_photo_id_{$s}", array("photoset_photo_update"), 'P1D');
            return $aPhotos;
        }
        return $data;
    }

    /**
     * Добавить к топику изображение
     *
     * @param ModuleTopic_EntityTopicPhoto $oPhoto    Объект фото к топику-фотосету
     *
     * @return ModuleTopic_EntityTopicPhoto|bool
     */
    public function AddTopicPhoto($oPhoto) {

        if ($nId = $this->oMapper->AddTopicPhoto($oPhoto)) {
            $oPhoto->setId($nId);
            E::ModuleCache()->CleanByTags(array('photoset_photo_update'));
            return $oPhoto;
        }
        return false;
    }

    /**
     * Получить изображение из фотосета по его ID
     *
     * @param int $iPhotoId    ID фото
     *
     * @return ModuleTopic_EntityTopicPhoto|null
     */
    public function getTopicPhotoById($iPhotoId) {

        $aPhotos = $this->GetTopicPhotosByArrayId($iPhotoId);
        if (isset($aPhotos[$iPhotoId])) {
            return $aPhotos[$iPhotoId];
        }
        return null;
    }

    /**
     * Получить список изображений из фотосета по ID топика
     *
     * @param int|array $aTopicId - ID топика
     * @param int       $iFromId  - ID с которого начинать выборку
     * @param int       $iCount   - Количество
     *
     * @return array
     */
    public function getPhotosByTopicId($aTopicId, $iFromId = null, $iCount = null) {

        return $this->oMapper->getPhotosByTopicId($aTopicId, $iFromId, $iCount);
    }

    /**
     * Получить список изображений из фотосета по временному коду
     *
     * @param string $sTargetTmp    Временный ключ
     *
     * @return array
     */
    public function getPhotosByTargetTmp($sTargetTmp) {

        return $this->oMapper->getPhotosByTargetTmp($sTargetTmp);
    }

    /**
     * Получить число изображений из фотосета по id топика
     *
     * @param int $nTopicId - ID топика
     *
     * @return int
     */
    public function getCountPhotosByTopicId($nTopicId) {

        return $this->oMapper->getCountPhotosByTopicId($nTopicId);
    }

    /**
     * Получить число изображений из фотосета по id топика
     *
     * @param string $sTargetTmp - Временный ключ
     *
     * @return int
     */
    public function getCountPhotosByTargetTmp($sTargetTmp) {

        return $this->oMapper->getCountPhotosByTargetTmp($sTargetTmp);
    }

    /**
     * Обновить данные по изображению
     *
     * @param ModuleTopic_EntityTopicPhoto $oPhoto Объект фото
     */
    public function UpdateTopicPhoto($oPhoto) {

        E::ModuleCache()->CleanByTags(array('photoset_photo_update'));
        $this->oMapper->updateTopicPhoto($oPhoto);
    }

    /**
     * Удалить изображение
     *
     * @param ModuleTopic_EntityTopicPhoto $oPhoto - Объект фото
     */
    public function DeleteTopicPhoto($oPhoto) {

        $this->oMapper->deleteTopicPhoto($oPhoto->getId());

        $sFile = E::ModuleUploader()->Url2Dir($oPhoto->getPath());
        E::ModuleImg()->Delete($sFile);
        E::ModuleCache()->CleanByTags(array('photoset_photo_update'));
    }

    /**
     * Загрузить изображение
     *
     * @param array $aFile - Элемент массива $_FILES
     *
     * @return string|bool
     */
    public function UploadTopicPhoto($aFile) {

        if ($sFileTmp = E::ModuleUploader()->UploadLocal($aFile)) {
            return $this->_saveTopicImage($sFileTmp, $this->oUserCurrent, 'photoset');
        }
        return false;
    }

    /**
     * Returns upload error
     *
     * @return mixed
     */
    public function UploadPhotoError() {

        return E::ModuleUploader()->GetErrorMsg();
    }

    /**
     * Пересчитывает счетчик избранных топиков
     *
     * @return bool
     */
    public function RecalculateFavourite() {

        return $this->oMapper->RecalculateFavourite();
    }

    /**
     * Пересчитывает счетчики голосований
     *
     * @return bool
     */
    public function RecalculateVote() {

        return $this->oMapper->RecalculateVote();
    }

    /**
     * Алиас для корректной работы ORM
     *
     * @param array $aTopicId - Список ID топиков
     *
     * @return array|int
     */
    public function GetTopicItemsByArrayId($aTopicId) {

        return $this->GetTopicsByArrayId($aTopicId);
    }

    /**
     * Порционная отдача файла
     *
     * @param $sFilename
     *
     * @return bool
     */
    public function readfileChunked($sFilename) {

        F::File_PrintChunked($sFilename);
    }

    /**
     * Обработка дополнительных полей топика
     *
     * @param ModuleTopic_EntityTopic $oTopic
     * @param string $sType
     *
     * @return bool
     */
    public function processTopicFields($oTopic, $sType = 'add') {

        /** @var ModuleTopic_EntityContentValues $aValues */
        $aValues = array();

        if ($sType == 'update') {
            // * Получаем существующие значения
            if ($aData = $this->GetTopicValuesByArrayId(array($oTopic->getId()))) {
                $aValues = $aData[$oTopic->getId()];
            }
            // * Чистим существующие значения
            E::ModuleTopic()->DeleteTopicValuesByTopicId($oTopic->getId());
        }

        if ($oType = E::ModuleTopic()->GetContentTypeByUrl($oTopic->getType())) {

            //получаем поля для данного типа
            if ($aFields = $oType->getFields()) {
                foreach ($aFields as $oField) {
                    $sData = null;
                    if (isset($_REQUEST['fields'][$oField->getFieldId()]) || isset($_FILES['fields_' . $oField->getFieldId()]) || $oField->getFieldType() == 'single-image-uploader') {

                        //текстовые поля
                        if (in_array($oField->getFieldType(), array('input', 'textarea', 'select'))) {
                            $sData = E::ModuleText()->Parser($_REQUEST['fields'][$oField->getFieldId()]);
                        }
                        //поле ссылки
                        if ($oField->getFieldType() == 'link') {
                            $sData = $_REQUEST['fields'][$oField->getFieldId()];
                        }

                        //поле даты
                        if ($oField->getFieldType() == 'date') {
                            if (isset($_REQUEST['fields'][$oField->getFieldId()])) {

                                if (F::CheckVal($_REQUEST['fields'][$oField->getFieldId()], 'text', 6, 10)
                                    && substr_count($_REQUEST['fields'][$oField->getFieldId()], '.') == 2
                                ) {
                                    list($d, $m, $y) = explode('.', $_REQUEST['fields'][$oField->getFieldId()]);
                                    if (@checkdate($m, $d, $y)) {
                                        $sData = $_REQUEST['fields'][$oField->getFieldId()];
                                    }
                                }
                            }
                        }

                        //поле с файлом
                        if ($oField->getFieldType() == 'file') {
                            //если указано удаление файла
                            if (F::GetRequest('topic_delete_file_' . $oField->getFieldId())) {
                                if ($oTopic->getFieldFile($oField->getFieldId())) {
                                    @unlink(Config::Get('path.root.dir') . $oTopic->getFieldFile($oField->getFieldId())->getFileUrl());
                                    //$oTopic->setValueField($oField->getFieldId(),'');
                                    $sData = null;
                                }
                            } else {
                                //если удаление файла не указано, уже ранее залит файл^ и нового файла не загружалось
                                if ($sType == 'update' && isset($aValues[$oField->getFieldId()])) {
                                    $sData = $aValues[$oField->getFieldId()]->getValueSource();
                                }
                            }

                            if (isset($_FILES['fields_' . $oField->getFieldId()]) && is_uploaded_file( $_FILES['fields_' . $oField->getFieldId()]['tmp_name'])) {
                                $iMaxFileSize = F::MemSize2Int(Config::Get('module.uploader.files.default.file_maxsize'));
                                $aFileExtensions = Config::Get('module.uploader.files.default.file_extensions');
                                if (!$iMaxFileSize || filesize($_FILES['fields_' . $oField->getFieldId()]['tmp_name']) <= $iMaxFileSize) {
                                    $aPathInfo = pathinfo($_FILES['fields_' . $oField->getFieldId()]['name']);

                                    if (!$aFileExtensions || in_array(strtolower($aPathInfo['extension']), $aFileExtensions)) {
                                        $sFileTmp = $_FILES['fields_' . $oField->getFieldId()]['tmp_name'];
                                        $sDirSave = Config::Get('path.uploads.root') . '/files/' . E::ModuleUser()->GetUserCurrent()->getId() . '/' . F::RandomStr(16);
                                        mkdir(Config::Get('path.root.dir') . $sDirSave, 0777, true);
                                        if (is_dir(Config::Get('path.root.dir') . $sDirSave)) {

                                            $sFile = $sDirSave . '/' . F::RandomStr(10) . '.' . strtolower($aPathInfo['extension']);
                                            $sFileFullPath = Config::Get('path.root.dir') . $sFile;
                                            if (copy($sFileTmp, $sFileFullPath)) {
                                                //удаляем старый файл
                                                if ($oTopic->getFieldFile($oField->getFieldId())) {
                                                    $sOldFile = Config::Get('path.root.dir') . $oTopic->getFieldFile($oField->getFieldId())->getFileUrl();
                                                    F::File_Delete($sOldFile);
                                                }

                                                $aFileObj = array();
                                                $aFileObj['file_hash'] = F::RandomStr(32);
                                                $aFileObj['file_name'] = E::ModuleText()->Parser($_FILES['fields_' . $oField->getFieldId()]['name']);
                                                $aFileObj['file_url'] = $sFile;
                                                $aFileObj['file_size'] = $_FILES['fields_' . $oField->getFieldId()]['size'];
                                                $aFileObj['file_extension'] = $aPathInfo['extension'];
                                                $aFileObj['file_downloads'] = 0;
                                                $sData = serialize($aFileObj);

                                                F::File_Delete($sFileTmp);
                                            }
                                        }
                                    } else {
                                        $sTypes = implode(', ', $aFileExtensions);
                                        E::ModuleMessage()->AddError(E::ModuleLang()->Get('topic_field_file_upload_err_type', array('types' => $sTypes)), null, true);
                                    }
                                } else {
                                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('topic_field_file_upload_err_size', array('size' => $iMaxFileSize)), null, true);
                                }
                                F::File_Delete($_FILES['fields_' . $oField->getFieldId()]['tmp_name']);
                            }
                        }

                        // Поле с изображением
                        if ($oField->getFieldType() == 'single-image-uploader') {
                            $sTargetType = $oField->getFieldType(). '-' . $oField->getFieldId();
                            $iTargetId = $oTopic->getId();

                            // 1. Удалить значение target_tmp
                            // Нужно затереть временный ключ в ресурсах, что бы в дальнейшем картнка не
                            // воспринималась как временная.
                            if ($sTargetTmp = E::ModuleSession()->GetCookie(ModuleUploader::COOKIE_TARGET_TMP)) {
                                // 2. Удалить куку.
                                // Если прозошло сохранение вновь созданного топика, то нужно
                                // удалить куку временной картинки. Если же сохранялся уже существующий топик,
                                // то удаление куки ни на что влиять не будет.
                                E::ModuleSession()->DelCookie(ModuleUploader::COOKIE_TARGET_TMP);

                                // 3. Переместить фото

                                $sNewPath = E::ModuleUploader()->GetUserImageDir(E::UserId(), true, false);
                                $aMresourceRel = E::ModuleMresource()->GetMresourcesRelByTargetAndUser($sTargetType, 0, E::UserId());

                                if ($aMresourceRel) {
                                    $oResource = array_shift($aMresourceRel);
                                    $sOldPath = $oResource->GetFile();

                                    $oStoredFile = E::ModuleUploader()->Store($sOldPath, $sNewPath);
                                    /** @var ModuleMresource_EntityMresource $oResource */
                                    $oResource = E::ModuleMresource()->GetMresourcesByUuid($oStoredFile->getUuid());
                                    if ($oResource) {
                                        $oResource->setUrl(E::ModuleMresource()->NormalizeUrl(E::ModuleUploader()->GetTargetUrl($sTargetType, $iTargetId)));
                                        $oResource->setType($sTargetType);
                                        $oResource->setUserId(E::UserId());
                                        // 4. В свойство поля записать адрес картинки
                                        $sData = $oResource->getMresourceId();
                                        $oResource = array($oResource);
                                        E::ModuleMresource()->UnlinkFile($sTargetType, 0, $oTopic->getUserId());
                                        E::ModuleMresource()->AddTargetRel($oResource, $sTargetType, $iTargetId);
                                    }
                                }
                            } else {
                                // Топик редактируется, просто обновим поле
                                $aMresourceRel = E::ModuleMresource()->GetMresourcesRelByTargetAndUser($sTargetType, $iTargetId, E::UserId());
                                if ($aMresourceRel) {
                                    $oResource = array_shift($aMresourceRel);
                                    $sData = $oResource->getMresourceId();
                                } else {
                                    $sData = false;
//                                    $this->DeleteField($oField);
                                }
                            }


                        }

                        E::ModuleHook()->Run('content_field_proccess', array('sData' => &$sData, 'oField' => $oField, 'oTopic' => $oTopic, 'aValues' => $aValues, 'sType' => &$sType));

                        //Добавляем поле к топику.
                        if ($sData) {
                            /** @var ModuleTopic_EntityContentValues $oValue */
                            $oValue = E::GetEntity('Topic_ContentValues');
                            $oValue->setTargetId($oTopic->getId());
                            $oValue->setTargetType('topic');
                            $oValue->setFieldId($oField->getFieldId());
                            $oValue->setFieldType($oField->getFieldType());
                            $oValue->setValue($sData);
                            $oValue->setValueSource(in_array($oField->getFieldType(), array('file', 'single-image-uploader'))
                                ? $sData
                                : $_REQUEST['fields'][$oField->getFieldId()]);

                            $this->AddTopicValue($oValue);

                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Updates mresources of topic
     *
     * @param ModuleTopic_EntityTopic $oTopic
     */
    public function UpdateMresources($oTopic) {

        $this->AttachTmpPhotoToTopic($oTopic);

        /** Следующий блок не работает поскольку $aList получает список фотосета и $aMresources весь
         * фотосет и доп.поля, фотосеты остаются, а доп.поля удаляются, потому закомментирован */

        /*
        // Получаем список ресурсов (хеш-таблицу)
        $aList = $oTopic->BuildMresourcesList();

        // Читаем список ресурсов из базы
        $aMresources = E::ModuleMresource()->GetMresourcesRelByTarget(array('topic', 'photoset'), $oTopic->GetId());

        // Строим список ID ресурсов для удаления
        $aDeleteResources = array();
        foreach ($aMresources as $oMresource) {
            if (isset($aList[$oMresource->GetHash()])) {
                // Если сохраненный ресурс есть в хеш-таблице, то чистим соответствующий хеш
                unset($aList[$oMresource->GetHash()]);
            } else {
                // Если ресурса нет в хеш-таблице, то это прентендент на удаление
                $aDeleteResources[$oMresource->GetId()] = $oMresource->getMresourceId();
            }
        }
        // В списке остались только новые ресурсы
        if ($aList) {
            E::ModuleMresource()->AddTargetRel($aList, 'topic', $oTopic->GetId());
        }
        if ($aDeleteResources) {
            E::ModuleMresource()->DeleteMresources(array_values($aDeleteResources));
            E::ModuleMresource()->DeleteMresourcesRel(array_keys($aDeleteResources));
        }
        */
    }

    /**
     * Delete MResources associated with topic(s)
     *
     * @param ModuleTopic_EntityTopic[]|ModuleTopic_EntityTopic $aTopics
     */
    public function DeleteMresources($aTopics) {

        if (!is_array($aTopics)) {
            $aTopics = array($aTopics);
        }
        /** @var ModuleTopic_EntityTopic $oTopic */
        foreach ($aTopics as $oTopic) {
            E::ModuleMresource()->DeleteMresourcesRelByTarget('topic', $oTopic->GetId());
        }
    }

    /**
     * @param ModuleTopic_EntityTopic $oTopic
     * @param null $sTargetTmp
     * @return bool
     */
    public function AttachTmpPhotoToTopic($oTopic, $sTargetTmp = null) {

        if (is_null($sTargetTmp)) {
            $sTargetTmp = E::ModuleSession()->GetCookie(ModuleUploader::COOKIE_TARGET_TMP);
        }

        E::ModuleMresource()->ResetTmpRelById($sTargetTmp, $oTopic->getId());
        return $this->oMapper->attachTmpPhotoToTopic($oTopic, $sTargetTmp);
    }

}

// EOF