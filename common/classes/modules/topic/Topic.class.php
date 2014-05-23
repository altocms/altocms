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

    /**
     * Инициализация
     *
     */
    public function Init() {

        $this->oMapperTopic = $this->oMapper = Engine::GetMapper(__CLASS__);
        $this->oUserCurrent = $this->User_GetUserCurrent();
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
        $s = serialize($aFilter);
        if (false === ($data = $this->Cache_Get("content_types_{$s}"))) {
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
            $this->Cache_Set($data, "content_types_{$s}", array('content_update', 'content_new'), 60 * 60 * 24 * 1);
        }
        return $data;
    }

    /*
     * Возвращает доступные типы контента
     */
    public function getContentType($sType) {

        if (in_array($sType, $this->aTopicTypes)) {
            return $this->aTopicTypesObjects[$sType];
        }
        return null;

    }

    /**
     * Получить тип контента по id
     *
     * @param string $nId
     *
     * @return ModuleTopic_EntityContentType|null
     */
    public function GetContentTypeById($nId) {

        if (false === ($data = $this->Cache_Get("content_type_{$nId}"))) {
            $data = $this->oMapper->getContentTypeById($nId);
            $this->Cache_Set($data, "content_type_{$nId}", array('content_update', 'content_new'), 60 * 60 * 24 * 1);
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

        if (false === ($data = $this->Cache_Get("content_type_{$sUrl}"))) {
            $data = $this->oMapper->getContentTypeByUrl($sUrl);
            $this->Cache_Set($data, "content_type_{$sUrl}", array('content_update', 'content_new'), 'P1D');
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
            $this->Cache_CleanByTags(array('content_new', 'content_update'));
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
            $this->Cache_CleanByTags(array('content_new', 'content_update', 'topic_update'));
            $this->Cache_Delete("content_type_{$oType->getContentId()}");
            return true;
        }
        return false;
    }

    /**
     * Получает доступные поля для типа контента
     *
     * @param $aFilter
     *
     * @return array
     */
    public function getContentFields($aFilter) {

        $sCacheKey = serialize($aFilter);
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = $this->oMapper->getContentFields($aFilter);
            $this->Cache_Set($data, $sCacheKey, array('content_update', 'content_new'), 'P1D');
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
            $this->Cache_CleanByTags(array('content_new', 'content_update', 'field_new', 'field_update'));
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
            $this->Cache_CleanByTags(array('content_new', 'content_update', 'field_new', 'field_update'));
            $this->Cache_Delete("content_field_{$oField->getFieldId()}");
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

        if (false === ($data = $this->Cache_Get("content_field_{$nId}"))) {
            $data = $this->oMapper->getContentFieldById($nId);
            $this->Cache_Set(
                $data, "content_field_{$nId}", array('content_new', 'content_update', 'field_new', 'field_update'),
                'P1D'
            );
        }
        return $data;
    }

    /**
     * Удаляет поле
     *
     * @param $oField
     *
     * @return bool
     */
    public function DeleteField($oField) {

        // * Чистим зависимые кеши
        $this->Cache_CleanByTags(array('field_update'));
        $this->Cache_Delete("content_field_{$oField->getFieldId()}");

        // * Если топик успешно удален, удаляем связанные данные
        if ($bResult = $this->oMapper->DeleteField($oField)) {
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

        return $this->oMapperTopic->AddTopicValue($oValue);
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
            $this->Cache_CleanByTags(array('topic_update'));
            return true;
        }
        return false;
    }

    /**
     * Возвращает список типов топика
     *
     * @return array
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
     * @param array      $aTopicId    Список ID топиков
     * @param array|null $aAllowData  Список типов дополнительных данных, которые нужно подключать к топикам
     *
     * @return array
     */
    public function GetTopicsAdditionalData($aTopicId, $aAllowData = null) {

        if (is_null($aAllowData)) {
            $aAllowData = $this->aAdditionalData;
        }
        $aAllowData = F::Array_FlipIntKeys($aAllowData);
        if (!is_array($aTopicId)) {
            $aTopicId = array($aTopicId);
        }
        /**
         * Получаем "голые" топики
         */
        $aTopics = $this->GetTopicsByArrayId($aTopicId);
        /**
         * Формируем ID дополнительных данных, которые нужно получить
         */
        $aUserId = array();
        $aBlogId = array();
        $aTopicId = array();
        $aPhotoMainId = array();
        foreach ($aTopics as $oTopic) {
            if (isset($aAllowData['user'])) {
                $aUserId[] = $oTopic->getUserId();
            }
            if (isset($aAllowData['blog'])) {
                $aBlogId[] = $oTopic->getBlogId();
            }
            //if ($oTopic->getType()=='question')	{
            $aTopicId[] = $oTopic->getId();
            //}
            if ($oTopic->getPhotosetMainPhotoId()) {
                $aPhotoMainId[] = $oTopic->getPhotosetMainPhotoId();
            }
        }
        /**
         * Получаем дополнительные данные
         */
        $aTopicsVote = array();
        $aFavouriteTopics = array();
        $aTopicsQuestionVote = array();
        $aTopicsRead = array();

        $aUsers = isset($aAllowData['user']) && is_array($aAllowData['user'])
            ? $this->User_GetUsersAdditionalData($aUserId, $aAllowData['user'])
            : $this->User_GetUsersAdditionalData($aUserId);

        $aBlogs = isset($aAllowData['blog']) && is_array($aAllowData['blog'])
            ? $this->Blog_GetBlogsAdditionalData($aBlogId, $aAllowData['blog'])
            : $this->Blog_GetBlogsAdditionalData($aBlogId);

        if (isset($aAllowData['vote']) && $this->oUserCurrent) {
            $aTopicsVote = $this->Vote_GetVoteByArray($aTopicId, 'topic', $this->oUserCurrent->getId());
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
        /**
         * Добавляем данные к результату - списку топиков
         */
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
                    $oTag = Engine::GetEntity('Topic_TopicTag');
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
            $this->Cache_CleanByTags(
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
     * @param int $sTopicId    ID топика
     *
     * @return bool
     */
    public function DeleteTopicValuesByTopicId($sTopicId) {

        return $this->oMapper->DeleteTopicValuesByTopicId($sTopicId);
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
            $nTopicId = $oTopic->getId();
            $nUserId = $oTopic->getUserId();
        } else {
            $nTopicId = intval($oTopicId);
            $oTopic = $this->GetTopicById($nTopicId);
            $nUserId = $oTopic->getUserId();
        }
        $oTopicId = null;

        $oBlog = $oTopic->GetBlog();
        // * Если топик успешно удален, удаляем связанные данные
        if ($bResult = $this->oMapper->DeleteTopic($nTopicId)) {
            $bResult = $this->DeleteTopicAdditionalData($nTopicId);
            $this->DeleteMresources($oTopic);
            $this->Blog_RecalculateCountTopicByBlogId($oBlog->GetId());
        }

        // * Чистим зависимые кеши
        $this->Cache_CleanByTags(array('topic_update', 'topic_update_user_' . $nUserId));
        $this->Cache_Delete("topic_{$nTopicId}");

        return $bResult;
    }

    /**
     * Delete array of topics
     *
     * @param $xTopics
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
                $aTopicId = array();
                $aBlogId = array();
                $aUserId = array();
                foreach ($aTopics as $oTopic) {
                    $aTopicId[] = $oTopic->getId();
                    $aBlogId[] = $oTopic->getBlogId();
                    $aUserId[] = $oTopic->getUserId();
                }
                if ($bResult = $this->oMapper->DeleteTopic($aTopicId)) {
                    $bResult = $this->DeleteTopicAdditionalData($aTopicId);
                    $this->DeleteMresources($aTopics);
                    $this->Blog_RecalculateCountTopicByBlogId($aBlogId);
                }

                // * Чистим зависимые кеши
                $aCacheTags = array('topic_update');
                foreach($aUserId as $iUserId) {
                    $aCacheTags[] = 'topic_update_user_' . $iUserId;
                }
                $this->Cache_CleanByTags($aCacheTags);
                foreach($aTopicId as $iTopicId) {
                    $this->Cache_Delete('topic_' . $iTopicId);
                }

                return $bResult;
            }
        }
    }

    /**
     * Удаление топиков по массиву ID пользователей
     *
     * @param $aUsersId
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
        $this->Cache_CleanByTags($aTags);
        if ($aTopicsId) {
            $aTags = array();
            foreach ($aTopicsId as $nTopicId) {
                $aTags[] = 'topic_' . $nTopicId;
            }
            $this->Cache_Delete("topic_{$nTopicId}");
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
        $this->Comment_DeleteCommentByTargetId($aTopicId, 'topic');
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
        $this->Vote_DeleteVoteByTarget($aTopicId, 'topic');
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
        $this->Cache_CleanByTags(array('topic_update'));
        foreach ($aTopicId as $nTopicId) {
            $this->Cache_Delete("topic_{$nTopicId}");
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
            // * Если топик изменил видимость(publish) или локацию (BlogId) или список тегов
            if (($oTopic->getPublish() != $oTopicOld->getPublish()) || ($oTopic->getBlogId() != $oTopicOld->getBlogId())
                || ($oTopic->getTags() != $oTopicOld->getTags())
            ) {
                // * Обновляем теги
                $this->DeleteTopicTagsByTopicId($oTopic->getId());
                if ($oTopic->getPublish() && $oTopic->getTags()) {
                    $aTags = explode(',', $oTopic->getTags());
                    foreach ($aTags as $sTag) {
                        $oTag = Engine::GetEntity('Topic_TopicTag');
                        $oTag->setTopicId($oTopic->getId());
                        $oTag->setUserId($oTopic->getUserId());
                        $oTag->setBlogId($oTopic->getBlogId());
                        $oTag->setText($sTag);
                        $this->AddTopicTag($oTag);
                    }
                }
            }
            if ($oTopic->getPublish() != $oTopicOld->getPublish()) {
                // * Обновляем избранное
                $this->SetFavouriteTopicPublish($oTopic->getId(), $oTopic->getPublish());
                // * Удаляем комментарий топика из прямого эфира
                if ($oTopic->getPublish() == 0) {
                    $this->Comment_DeleteCommentOnlineByTargetId($oTopic->getId(), 'topic');
                }
                // * Изменяем видимость комментов
                $this->Comment_SetCommentsPublish($oTopic->getId(), 'topic', $oTopic->getPublish());
            }

            if (Router::GetAction() == 'content') {
                $this->processTopicFields($oTopic, 'update');
            }

            $this->UpdateMresources($oTopic);

            // чистим зависимые кеши
            $this->Cache_CleanByTags(array('topic_update', "topic_update_user_{$oTopic->getUserId()}"));
            $this->Cache_Delete("topic_{$oTopic->getId()}");
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
     * @param int $nId    ID топика
     *
     * @return ModuleTopic_EntityTopic|null
     */
    public function GetTopicById($nId) {

        if (!intval($nId)) {
            return null;
        }
        $aTopics = $this->GetTopicsAdditionalData($nId);
        if (isset($aTopics[$nId])) {
            return $aTopics[$nId];
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

        $nTopicId = $this->GetTopicIdByUrl($sUrl);
        if ($nTopicId) {
            return $this->GetTopicById($nTopicId);
        }
        return null;
    }

    /**
     * Returns topic ID of it exists
     *
     * @param string $sUrl
     *
     * @return int
     */
    public function GetTopicIdByUrl($sUrl) {

        $nTopicId = $this->oMapper->GetTopicIdByUrl($sUrl);
        return $nTopicId;
    }

    /**
     * Получить топики по похожим URL
     *
     * @param string $sUrl
     *
     * @return ModuleTopic_EntityTopic|null
     */
    public function GetTopicsLikeUrl($sUrl) {

        $aTopicsId = $this->oMapper->GetTopicsIdLikeUrl($sUrl);
        return $this->GetTopicsByArrayId($aTopicsId);
    }

    /**
     * Проверяет URL топика на совпадения и, если нужно, делает его уникальным
     *
     * @param $sUrl
     *
     * @return string
     */
    public function CorrectTopicUrl($sUrl) {

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
        /**
         * Делаем мульти-запрос к кешу
         */
        $aCacheKeys = F::Array_ChangeValues($aTopicsId, 'topic_');
        if (false !== ($data = $this->Cache_Get($aCacheKeys))) {
            /**
             * проверяем что досталось из кеша
             */
            foreach ($aCacheKeys as $sValue => $sKey) {
                if (array_key_exists($sKey, $data)) {
                    if ($data[$sKey]) {
                        $aTopics[$data[$sKey]->getId()] = $data[$sKey];
                    } else {
                        $aTopicIdNotNeedQuery[] = $sValue;
                    }
                }
            }
        }
        /**
         * Смотрим каких топиков не было в кеше и делаем запрос в БД
         */
        $aTopicIdNeedQuery = array_diff($aTopicsId, array_keys($aTopics));
        $aTopicIdNeedQuery = array_diff($aTopicIdNeedQuery, $aTopicIdNotNeedQuery);
        $aTopicIdNeedStore = $aTopicIdNeedQuery;
        if ($data = $this->oMapper->GetTopicsByArrayId($aTopicIdNeedQuery)) {
            foreach ($data as $oTopic) {
                /**
                 * Добавляем к результату и сохраняем в кеш
                 */
                $aTopics[$oTopic->getId()] = $oTopic;
                $this->Cache_Set($oTopic, "topic_{$oTopic->getId()}", array(), 60 * 60 * 24 * 4);
                $aTopicIdNeedStore = array_diff($aTopicIdNeedStore, array($oTopic->getId()));
            }
        }
        /**
         * Сохраняем в кеш запросы не вернувшие результата
         */
        foreach ($aTopicIdNeedStore as $nId) {
            $this->Cache_Set(null, "topic_{$nId}", array(), 60 * 60 * 24 * 4);
        }
        /**
         * Сортируем результат согласно входящему массиву
         */
        $aTopics = F::Array_SortByKeysArray($aTopics, $aTopicsId);
        return $aTopics;
    }

    /**
     * Получить список топиков по списку ID, но используя единый кеш
     *
     * @param array $aTopicsId    Список ID топиков
     *
     * @return array
     */
    public function GetTopicsByArrayIdSolid($aTopicsId) {

        if (!is_array($aTopicsId)) {
            $aTopicsId = array($aTopicsId);
        }
        $aTopicsId = array_unique($aTopicsId);
        $aTopics = array();
        $s = join(',', $aTopicsId);
        if (false === ($data = $this->Cache_Get("topic_id_{$s}"))) {
            $data = $this->oMapper->GetTopicsByArrayId($aTopicsId);
            foreach ($data as $oTopic) {
                $aTopics[$oTopic->getId()] = $oTopic;
            }
            $this->Cache_Set($aTopics, "topic_id_{$s}", array("topic_update"), 60 * 60 * 24 * 1);
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
            ? $this->Favourite_GetFavouritesByUserId($nUserId, 'topic', $iCurrPage, $iPerPage, $aCloseTopics)
            : $this->Favourite_GetFavouriteOpenTopicsByUserId($nUserId, $iCurrPage, $iPerPage);

        // * Получаем записи по переданому массиву айдишников
        if ($data['collection']) {
            $data['collection'] = $this->GetTopicsAdditionalData($data['collection']);
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
            ? $this->Favourite_GetCountFavouritesByUserId($nUserId, 'topic', $aCloseTopics)
            : $this->Favourite_GetCountFavouriteOpenTopicsByUserId($nUserId);
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
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = array(
                'collection' => $this->oMapper->GetTopics($aFilter, $iCount, $iPage, $iPerPage),
                'count'      => $iCount
            );
            $this->Cache_Set($data, $sCacheKey, array('topic_update', 'topic_new'), 'P1D');
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

        $sCacheKey = "topic_count_" . serialize($aFilter);
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = $this->oMapper->GetCountTopics($aFilter);
            $this->Cache_Set($data, $sCacheKey, array('topic_update', 'topic_new'), 'P1D');
        }
        return $data;
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
     * Получает список хороших топиков для вывода на главную страницу(из всех блогов, как коллективных так и персональных)
     *
     * @param  int  $iPage          Номер страницы
     * @param  int  $iPerPage       Количество элементов на страницу
     * @param  bool $bAddAccessible Указывает на необходимость добавить в выдачу топики,
     *                                из блогов доступных пользователю. При указании false,
     *                                в выдачу будут переданы только топики из общедоступных блогов.
     *
     * @return array
     */
    public function GetTopicsGood($iPage, $iPerPage, $bAddAccessible = true) {

        $aFilter = array(
            'blog_type'     => $this->Blog_GetOpenBlogTypes(),
            'topic_publish' => 1,
            'topic_rating'  => array(
                'value'         => Config::Get('module.blog.index_good'),
                'type'          => 'top',
                'publish_index' => 1,
            )
        );
        /**
         * Если пользователь авторизирован, то добавляем в выдачу
         * закрытые блоги в которых он состоит
         */
        if ($this->oUserCurrent && $bAddAccessible) {
            $aOpenBlogs = $this->Blog_GetAccessibleBlogsByUser($this->oUserCurrent);
            if (count($aOpenBlogs)) {
                $aFilter['blog_type']['close'] = $aOpenBlogs;
            }
        }

        return $this->GetTopicsByFilter($aFilter, $iPage, $iPerPage);
    }

    /**
     * Получает список новых топиков, ограничение новизны по дате из конфига
     *
     * @param  int  $iPage          Номер страницы
     * @param  int  $iPerPage       Количество элементов на страницу
     * @param  bool $bAddAccessible Указывает на необходимость добавить в выдачу топики,
     *                                из блогов доступных пользователю. При указании false,
     *                                в выдачу будут переданы только топики из общедоступных блогов.
     *
     * @return array
     */
    public function GetTopicsNew($iPage, $iPerPage, $bAddAccessible = true) {

        $sDate = date('Y-m-d H:00:00', time() - Config::Get('module.topic.new_time'));
        $aFilter = array(
            'blog_type'     => $this->Blog_GetOpenBlogTypes(),
            'topic_publish' => 1,
            'topic_new'     => $sDate,
        );
        /**
         * Если пользователь авторизирован, то добавляем в выдачу
         * закрытые блоги в которых он состоит
         */
        if ($this->oUserCurrent && $bAddAccessible) {
            $aOpenBlogs = $this->Blog_GetAccessibleBlogsByUser($this->oUserCurrent);
            if (count($aOpenBlogs)) {
                $aFilter['blog_type']['close'] = $aOpenBlogs;
            }
        }
        return $this->GetTopicsByFilter($aFilter, $iPage, $iPerPage);
    }

    /**
     * Получает список ВСЕХ новых топиков
     *
     * @param  int  $iPage          Номер страницы
     * @param  int  $iPerPage       Количество элементов на страницу
     * @param  bool $bAddAccessible Указывает на необходимость добавить в выдачу топики,
     *                                из блогов доступных пользователю. При указании false,
     *                                в выдачу будут переданы только топики из общедоступных блогов.
     *
     * @return array
     */
    public function GetTopicsNewAll($iPage, $iPerPage, $bAddAccessible = true) {

        $aFilter = array(
            'blog_type'     => $this->Blog_GetOpenBlogTypes(),
            'topic_publish' => 1,
        );
        /**
         * Если пользователь авторизирован, то добавляем в выдачу
         * закрытые блоги в которых он состоит
         */
        if ($this->oUserCurrent && $bAddAccessible) {
            $aOpenBlogs = $this->Blog_GetAccessibleBlogsByUser($this->oUserCurrent);
            if (count($aOpenBlogs)) {
                $aFilter['blog_type']['close'] = $aOpenBlogs;
            }
        }
        return $this->GetTopicsByFilter($aFilter, $iPage, $iPerPage);
    }

    /**
     * Получает список ВСЕХ обсуждаемых топиков
     *
     * @param  int        $iPage          Номер страницы
     * @param  int        $iPerPage       Количество элементов на страницу
     * @param  int|string $sPeriod        Период в виде секунд или конкретной даты
     * @param  bool       $bAddAccessible Указывает на необходимость добавить в выдачу топики,
     *                                из блогов доступных пользователю. При указании false,
     *                                в выдачу будут переданы только топики из общедоступных блогов.
     *
     * @return array
     */
    public function GetTopicsDiscussed($iPage, $iPerPage, $sPeriod = null, $bAddAccessible = true) {

        if (is_numeric($sPeriod)) {
            // количество последних секунд
            $sPeriod = date('Y-m-d H:00:00', time() - $sPeriod);
        }

        $aFilter = array(
            'blog_type'     => $this->Blog_GetOpenBlogTypes(),
            'topic_publish' => 1
        );
        if ($sPeriod) {
            $aFilter['topic_date_more'] = $sPeriod;
        }
        $aFilter['order'] = ' t.topic_count_comment desc, t.topic_id desc ';
        /**
         * Если пользователь авторизирован, то добавляем в выдачу
         * закрытые блоги в которых он состоит
         */
        if ($this->oUserCurrent && $bAddAccessible) {
            $aOpenBlogs = $this->Blog_GetAccessibleBlogsByUser($this->oUserCurrent);
            if (count($aOpenBlogs)) {
                $aFilter['blog_type']['close'] = $aOpenBlogs;
            }
        }
        return $this->GetTopicsByFilter($aFilter, $iPage, $iPerPage);
    }

    /**
     * Получает список ВСЕХ рейтинговых топиков
     *
     * @param  int        $iPage          Номер страницы
     * @param  int        $iPerPage       Количество элементов на страницу
     * @param  int|string $sPeriod        Период в виде секунд или конкретной даты
     * @param  bool       $bAddAccessible Указывает на необходимость добавить в выдачу топики,
     *                                из блогов доступных пользователю. При указании false,
     *                                в выдачу будут переданы только топики из общедоступных блогов.
     *
     * @return array
     */
    public function GetTopicsTop($iPage, $iPerPage, $sPeriod = null, $bAddAccessible = true) {

        if (is_numeric($sPeriod)) {
            // количество последних секунд
            $sPeriod = date('Y-m-d H:00:00', time() - $sPeriod);
        }

        $aFilter = array(
            'blog_type'     => $this->Blog_GetOpenBlogTypes(),
            'topic_publish' => 1
        );
        if ($sPeriod) {
            $aFilter['topic_date_more'] = $sPeriod;
        }
        $aFilter['order'] = array('t.topic_rating desc', 't.topic_id desc');
        /**
         * Если пользователь авторизирован, то добавляем в выдачу
         * закрытые блоги в которых он состоит
         */
        if ($this->oUserCurrent && $bAddAccessible) {
            $aOpenBlogs = $this->Blog_GetAccessibleBlogsByUser($this->oUserCurrent);
            if (count($aOpenBlogs)) {
                $aFilter['blog_type']['close'] = $aOpenBlogs;
            }
        }
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

        $aOpenBlogTypes = $this->Blog_GetOpenBlogTypes();
        $aFilter = array(
            'blog_type'     => $aOpenBlogTypes,
            'topic_publish' => 1,
        );
        $aOpenTopics = $this->GetTopicsByFilter($aFilter, 1, $nCount);
        /**
         * Если пользователь авторизирован, то добавляем в выдачу
         * закрытые блоги в которых он состоит
         */
        $aCloseTopics = array();
        if ($this->oUserCurrent) {
            $aBlogsId = $this->Blog_GetAccessibleBlogsByUser($this->oUserCurrent);
            if (count($aBlogsId)) {
                $aFilter = array(
                    'blog_id'     => $aBlogsId,
                    'topic_publish' => 1,
                );
                $aCloseTopics = $this->GetTopicsByFilter($aFilter, 1, $nCount);
            }
        }
        $aResult = array();
        if (isset($aOpenTopics['collection'])) {
            $aResult = $aOpenTopics['collection'];
        }
        if (isset($aCloseTopics['collection'])) {
            $aResult = F::Array_Merge($aResult, $aCloseTopics['collection']);
        }
        if ($aResult) {
            uasort($aResult, array($this, '_compareByDate'));
            if (sizeof($aResult) > $nCount) {
                $aResult = array_slice($aResult, 0, $nCount, true);
            }
        }
        return $aResult;
    }

    public function _compareByDate($oTopics1, $oTopic2) {

        if ($oTopics1->getDate() == $oTopic2->getDate()) {
            return ($oTopics1->getId() > $oTopic2->getId()) ? -1 : 1;
        }
        return ($oTopics1->getDate() > $oTopic2->getDate()) ? -1 : 1;
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

        if (is_numeric($sPeriod)) {
            // количество последних секунд
            $sPeriod = date('Y-m-d H:00:00', time() - $sPeriod);
        }
        $aFilter = array(
            'blog_type'     => array(
                'personal',
            ),
            'topic_publish' => 1,
        );
        if ($sPeriod) {
            $aFilter['topic_date_more'] = $sPeriod;
        }
        switch ($sShowType) {
            case 'good':
                $aFilter['topic_rating'] = array(
                    'value' => Config::Get('module.blog.personal_good'),
                    'type'  => 'top',
                );
                break;
            case 'bad':
                $aFilter['topic_rating'] = array(
                    'value' => Config::Get('module.blog.personal_good'),
                    'type'  => 'down',
                );
                break;
            case 'new':
                $aFilter['topic_new'] = date('Y-m-d H:00:00', time() - Config::Get('module.topic.new_time'));
                break;
            case 'newall':
                // нет доп фильтра
                break;
            case 'discussed':
                $aFilter['order'] = array('t.topic_count_comment desc', 't.topic_id desc');
                break;
            case 'top':
                $aFilter['order'] = array('t.topic_rating desc', 't.topic_id desc');
                break;
            default:
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

        $sDate = date('Y-m-d H:00:00', time() - Config::Get('module.topic.new_time'));
        $aFilter = array(
            'blog_type'     => array(
                'personal',
            ),
            'topic_publish' => 1,
            'topic_new'     => $sDate,
        );
        return $this->GetCountTopicsByFilter($aFilter);
    }

    /**
     * Получает список топиков по юзеру
     *
     * @param int $nUserId     ID пользователя
     * @param int $iPublish    Флаг публикации топика
     * @param int $iPage       Номер страницы
     * @param int $iPerPage    Количество элементов на страницу
     *
     * @return array
     */
    public function GetTopicsPersonalByUser($nUserId, $iPublish, $iPage, $iPerPage) {

        $aFilter = array(
            'topic_publish' => $iPublish,
            'user_id'       => $nUserId,
            'blog_type'     => $this->Blog_GetOpenBlogTypes(),
        );
        /**
         * Если пользователь смотрит свой профиль, то добавляем в выдачу
         * закрытые блоги в которых он состоит
         */
        if ($this->oUserCurrent && $this->oUserCurrent->getId() == $nUserId) {
            $aFilter['blog_type'][] = 'close';
        }
        return $this->GetTopicsByFilter($aFilter, $iPage, $iPerPage);
    }

    /**
     * Возвращает количество топиков которые создал юзер
     *
     * @param int  $nUserId     ID пользователя
     * @param bool $bPublish    Флаг публикации топика
     *
     * @return array
     */
    public function GetCountTopicsPersonalByUser($nUserId, $bPublish) {

        $aFilter = array(
            'topic_publish' => $bPublish ? 1 : 0,
            'user_id'       => $nUserId,
            'blog_type'     => $this->Blog_GetOpenBlogTypes(),
        );
        /**
         * Если пользователь смотрит свой профиль, то добавляем в выдачу
         * закрытые блоги в которых он состоит
         */
        if ($this->oUserCurrent && $this->oUserCurrent->getId() == $nUserId) {
            $aFilter['blog_type'][] = 'close';
        }

        $sCacheKey = 'topic_count_user_' . serialize($aFilter);
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = $this->oMapper->GetCountTopics($aFilter);
            $this->Cache_Set($data, $sCacheKey, array("topic_update_user_{$nUserId}"), 'P1D');
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

        if (is_numeric($sPeriod)) {
            // количество последних секунд
            $sPeriod = date('Y-m-d H:00:00', time() - $sPeriod);
        }
        $aOpenBlogTypes = $this->Blog_GetOpenBlogTypes();
        if (false !== ($nKey = array_search('personal', $aOpenBlogTypes))) {
            unset($aOpenBlogTypes[$nKey]);
        }
        $aFilter = array(
            'blog_type'     => $aOpenBlogTypes,
            'topic_publish' => 1,
        );
        if ($sPeriod) {
            $aFilter['topic_date_more'] = $sPeriod;
        }
        switch ($sShowType) {
            case 'good':
                $aFilter['topic_rating'] = array(
                    'value' => Config::Get('module.blog.collective_good'),
                    'type'  => 'top',
                );
                break;
            case 'bad':
                $aFilter['topic_rating'] = array(
                    'value' => Config::Get('module.blog.collective_good'),
                    'type'  => 'down',
                );
                break;
            case 'new':
                $aFilter['topic_new'] = date('Y-m-d H:00:00', time() - Config::Get('module.topic.new_time'));
                break;
            case 'newall':
                // нет доп фильтра
                break;
            case 'discussed':
                $aFilter['order'] = array('t.topic_count_comment desc', 't.topic_id desc');
                break;
            case 'top':
                $aFilter['order'] = array('t.topic_rating desc', 't.topic_id desc');
                break;
            default:
                break;
        }
        /**
         * Если пользователь авторизирован, то добавляем в выдачу
         * закрытые блоги в которых он состоит
         */
        if ($this->oUserCurrent) {
            $aOpenBlogs = $this->Blog_GetAccessibleBlogsByUser($this->oUserCurrent);
            if (count($aOpenBlogs)) {
                $aFilter['blog_type']['close'] = $aOpenBlogs;
            }
        }
        return $this->GetTopicsByFilter($aFilter, $iPage, $iPerPage);
    }

    /**
     * Получает число новых топиков в коллективных блогах
     *
     * @return int
     */
    public function GetCountTopicsCollectiveNew() {

        $sDate = date('Y-m-d H:00:00', time() - Config::Get('module.topic.new_time'));
        $aOpenBlogTypes = $this->Blog_GetOpenBlogTypes();
        if (false !== ($nKey = array_search('personal', $aOpenBlogTypes))) {
            unset($aOpenBlogTypes[$nKey]);
        }
        $aFilter = array(
            'blog_type'     => $aOpenBlogTypes,
            'topic_publish' => 1,
            'topic_new'     => $sDate,
        );
        /**
         * Если пользователь авторизирован, то добавляем в выдачу
         * закрытые блоги в которых он состоит
         */
        if ($this->oUserCurrent) {
            $aOpenBlogs = $this->Blog_GetAccessibleBlogsByUser($this->oUserCurrent);
            if (count($aOpenBlogs)) {
                $aFilter['blog_type']['close'] = $aOpenBlogs;
            }
        }
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
            ? $this->Blog_GetInaccessibleBlogsByUser($this->oUserCurrent)
            : $this->Blog_GetInaccessibleBlogsByUser();

        $sCacheKey = "topic_rating_{$sDate}_{$iLimit}_" . serialize($aCloseBlogs);
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = $this->oMapper->GetTopicsRatingByDate($sDate, $iLimit, $aCloseBlogs);
            $this->Cache_Set($data, $sCacheKey, array('topic_update'), 'P3D');
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

        if (is_numeric($sPeriod)) {
            // количество последних секунд
            $sPeriod = date('Y-m-d H:00:00', time() - $sPeriod);
        }
        $aFilter = array(
            'topic_publish' => 1,
            'blog_id'       => $oBlog->getId(),
        );
        if ($sPeriod) {
            $aFilter['topic_date_more'] = $sPeriod;
        }
        switch ($sShowType) {
            case 'good':
                $aFilter['topic_rating'] = array(
                    'value' => Config::Get('module.blog.collective_good'),
                    'type'  => 'top',
                );
                break;
            case 'bad':
                $aFilter['topic_rating'] = array(
                    'value' => Config::Get('module.blog.collective_good'),
                    'type'  => 'down',
                );
                break;
            case 'new':
                $aFilter['topic_new'] = date('Y-m-d H:00:00', time() - Config::Get('module.topic.new_time'));
                break;
            case 'newall':
                // нет доп фильтра
                break;
            case 'discussed':
                $aFilter['order'] = array('t.topic_count_comment desc', 't.topic_id desc');
                break;
            case 'top':
                $aFilter['order'] = array('t.topic_rating desc', 't.topic_id desc');
                break;
            default:
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

        $sDate = date('Y-m-d H:00:00', time() - Config::Get('module.topic.new_time'));
        $aFilter = array(
            'topic_publish' => 1,
            'blog_id'       => $oBlog->getId(),
            'topic_new'     => $sDate,

        );
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
            ? $this->Blog_GetInaccessibleBlogsByUser($this->oUserCurrent)
            : $this->Blog_GetInaccessibleBlogsByUser();

        $sCacheKey = "topic_tag_{$sTag}_{$iPage}_{$iPerPage}_" . serialize($aCloseBlogs);
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = array(
                'collection' => $this->oMapper->GetTopicsByTag($sTag, $aCloseBlogs, $iCount, $iPage, $iPerPage),
                'count'      => $iCount
            );
            $this->Cache_Set($data, $sCacheKey, array('topic_update', 'topic_new'), 'P1D');
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

        $aTypes = $this->Blog_GetAllowBlogTypes($this->oUserCurrent, 'list', true);
        $aFilter = array(
            'blog_type'     => $aTypes,
            'topic_publish' => 1,
            'topic_type'    => $sType
        );
        /**
         * Если пользователь авторизирован, то добавляем в выдачу
         * закрытые блоги в которых он состоит
         */
        if ($this->oUserCurrent && $bAddAccessible) {
            $aOpenBlogs = $this->Blog_GetAccessibleBlogsByUser($this->oUserCurrent);
            if (count($aOpenBlogs)) {
                $aFilter['blog_type']['close'] = $aOpenBlogs;
            }
        }
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
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = $this->oMapper->GetTopicTags($nLimit, $aExcludeTopic);
            $this->Cache_Set($data, $sCacheKey, array('topic_update', 'topic_new'), 'P1D');
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
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = $this->oMapper->GetOpenTopicTags($nLimit, $nUserId);
            $this->Cache_Set($data, $sCacheKey, array('topic_update', 'topic_new'), 'P1D');
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
            $this->Cache_Delete("topic_{$nTopicId}");
            $this->Cache_CleanByTags(array('topic_update'));
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
            $this->Cache_Delete("topic_{$nTopicId}");
            $this->Cache_CleanByTags(array('topic_update'));
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

        return $this->Favourite_GetFavourite($nTopicId, 'topic', $nUserId);
    }

    /**
     * Получить список избранного по списку айдишников
     *
     * @param array $aTopicsId    Список ID топиков
     * @param int   $nUserId      ID пользователя
     *
     * @return array
     */
    public function GetFavouriteTopicsByArray($aTopicsId, $nUserId) {

        return $this->Favourite_GetFavouritesByArray($aTopicsId, 'topic', $nUserId);
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

        return $this->Favourite_GetFavouritesByArraySolid($aTopicsId, 'topic', $nUserId);
    }

    /**
     * Добавляет топик в избранное
     *
     * @param ModuleFavourite_EntityFavourite $oFavouriteTopic    Объект избранного
     *
     * @return bool
     */
    public function AddFavouriteTopic($oFavouriteTopic) {

        return $this->Favourite_AddFavourite($oFavouriteTopic);
    }

    /**
     * Удаляет топик из избранного
     *
     * @param ModuleFavourite_EntityFavourite $oFavouriteTopic    Объект избранного
     *
     * @return bool
     */
    public function DeleteFavouriteTopic($oFavouriteTopic) {

        return $this->Favourite_DeleteFavourite($oFavouriteTopic);
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

        return $this->Favourite_SetFavouriteTargetPublish($nTopicId, 'topic', $bPublish);
    }

    /**
     * Удаляет топики из избранного по списку
     *
     * @param  array $aTopicsId    Список ID топиков
     *
     * @return bool
     */
    public function DeleteFavouriteTopicByArrayId($aTopicsId) {

        return $this->Favourite_DeleteFavouriteByTargetId($aTopicsId, 'topic');
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
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = $this->oMapper->GetTopicTagsByLike($sTag, $nLimit);
            $this->Cache_Set($data, $sCacheKey, array("topic_update", "topic_new"), 60 * 60 * 24 * 3);
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
            $this->Cache_Delete("topic_read_{$oTopicRead->getTopicId()}_{$oTopicRead->getUserId()}");
            $this->Cache_CleanByTags(array("topic_read_user_{$oTopicRead->getUserId()}"));
            $this->oMapper->UpdateTopicRead($oTopicRead);
        } else {
            $this->Cache_Delete("topic_read_{$oTopicRead->getTopicId()}_{$oTopicRead->getUserId()}");
            $this->Cache_CleanByTags(array("topic_read_user_{$oTopicRead->getUserId()}"));
            $this->oMapper->AddTopicRead($oTopicRead);
        }
        return true;
    }

    /**
     * Получаем дату прочтения топика юзером
     *
     * @param int $nTopicId    - ID топика
     * @param int $nUserId     - ID пользователя
     *
     * @return ModuleTopic_EntityTopicRead|null
     */
    public function GetTopicRead($nTopicId, $nUserId) {

        $data = $this->GetTopicsReadByArray($nTopicId, $nUserId);
        if (isset($data[$nTopicId])) {
            return $data[$nTopicId];
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
     * @param int   $nUserId      - ID пользователя
     *
     * @return array
     */
    public function GetTopicsReadByArray($aTopicsId, $nUserId) {

        if (!$aTopicsId) {
            return array();
        }
        if (Config::Get('sys.cache.solid')) {
            return $this->GetTopicsReadByArraySolid($aTopicsId, $nUserId);
        }
        if (!is_array($aTopicsId)) {
            $aTopicsId = array($aTopicsId);
        }
        $aTopicsId = array_unique($aTopicsId);
        $aTopicsRead = array();
        $aTopicIdNotNeedQuery = array();
        /**
         * Делаем мульти-запрос к кешу
         */
        $aCacheKeys = F::Array_ChangeValues($aTopicsId, 'topic_read_', '_' . $nUserId);
        if (false !== ($data = $this->Cache_Get($aCacheKeys))) {
            /**
             * проверяем что досталось из кеша
             */
            foreach ($aCacheKeys as $sValue => $sKey) {
                if (array_key_exists($sKey, $data)) {
                    if ($data[$sKey]) {
                        $aTopicsRead[$data[$sKey]->getTopicId()] = $data[$sKey];
                    } else {
                        $aTopicIdNotNeedQuery[] = $sValue;
                    }
                }
            }
        }
        /**
         * Смотрим каких топиков не было в кеше и делаем запрос в БД
         */
        $aTopicIdNeedQuery = array_diff($aTopicsId, array_keys($aTopicsRead));
        $aTopicIdNeedQuery = array_diff($aTopicIdNeedQuery, $aTopicIdNotNeedQuery);
        $aTopicIdNeedStore = $aTopicIdNeedQuery;
        if ($data = $this->oMapper->GetTopicsReadByArray($aTopicIdNeedQuery, $nUserId)) {
            foreach ($data as $oTopicRead) {
                /**
                 * Добавляем к результату и сохраняем в кеш
                 */
                $aTopicsRead[$oTopicRead->getTopicId()] = $oTopicRead;
                $this->Cache_Set(
                    $oTopicRead, "topic_read_{$oTopicRead->getTopicId()}_{$oTopicRead->getUserId()}", array(),
                    60 * 60 * 24 * 4
                );
                $aTopicIdNeedStore = array_diff($aTopicIdNeedStore, array($oTopicRead->getTopicId()));
            }
        }
        /**
         * Сохраняем в кеш запросы не вернувшие результата
         */
        foreach ($aTopicIdNeedStore as $sId) {
            $this->Cache_Set(null, "topic_read_{$sId}_{$nUserId}", array(), 60 * 60 * 24 * 4);
        }
        /**
         * Сортируем результат согласно входящему массиву
         */
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
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = $this->oMapper->GetTopicsReadByArray($aTopicsId, $nUserId);
            foreach ($data as $oTopicRead) {
                $aTopicsRead[$oTopicRead->getTopicId()] = $oTopicRead;
            }
            $this->Cache_Set($aTopicsRead, $sCacheKey, array("topic_read_user_{$nUserId}"), 'P1D');
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
        if (false === ($data = $this->Cache_Get("topic_values_{$s}"))) {
            $data = $this->oMapper->GetTopicValuesByArrayId($aTopicId);
            foreach ($data as $oValue) {
                $aValues[$oValue->getTargetId()][$oValue->getFieldId()] = $oValue;
            }
            $this->Cache_Set($aValues, "topic_values_{$s}", array('topic_new', 'topic_update'), 60 * 60 * 24 * 1);
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
        if (false === ($data = $this->Cache_Get("topic_fields_{$s}"))) {
            $data = $this->oMapper->GetFieldsByArrayId($aTypesId);
            foreach ($data as $oField) {
                $aFields[$oField->getContentId()][$oField->getFieldId()] = $oField;
            }
            $this->Cache_Set($aFields, "topic_fields_{$s}", array("field_update"), 60 * 60 * 24 * 1);
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
     * @param int   $nUserId      - ID пользователя
     *
     * @return array
     */
    public function GetTopicsQuestionVoteByArray($aTopicsId, $nUserId) {

        if (!$aTopicsId) {
            return array();
        }
        if (Config::Get('sys.cache.solid')) {
            return $this->GetTopicsQuestionVoteByArraySolid($aTopicsId, $nUserId);
        }
        if (!is_array($aTopicsId)) {
            $aTopicsId = array($aTopicsId);
        }
        $aTopicsId = array_unique($aTopicsId);
        $aTopicsQuestionVote = array();
        $aTopicIdNotNeedQuery = array();
        /**
         * Делаем мульти-запрос к кешу
         */
        $aCacheKeys = F::Array_ChangeValues($aTopicsId, 'topic_question_vote_', '_' . $nUserId);
        if (false !== ($data = $this->Cache_Get($aCacheKeys))) {
            /**
             * проверяем что досталось из кеша
             */
            foreach ($aCacheKeys as $sValue => $sKey) {
                if (array_key_exists($sKey, $data)) {
                    if ($data[$sKey]) {
                        $aTopicsQuestionVote[$data[$sKey]->getTopicId()] = $data[$sKey];
                    } else {
                        $aTopicIdNotNeedQuery[] = $sValue;
                    }
                }
            }
        }
        /**
         * Смотрим каких топиков не было в кеше и делаем запрос в БД
         */
        $aTopicIdNeedQuery = array_diff($aTopicsId, array_keys($aTopicsQuestionVote));
        $aTopicIdNeedQuery = array_diff($aTopicIdNeedQuery, $aTopicIdNotNeedQuery);
        $aTopicIdNeedStore = $aTopicIdNeedQuery;
        if ($data = $this->oMapper->GetTopicsQuestionVoteByArray($aTopicIdNeedQuery, $nUserId)) {
            foreach ($data as $oTopicVote) {
                /**
                 * Добавляем к результату и сохраняем в кеш
                 */
                $aTopicsQuestionVote[$oTopicVote->getTopicId()] = $oTopicVote;
                $this->Cache_Set(
                    $oTopicVote, "topic_question_vote_{$oTopicVote->getTopicId()}_{$oTopicVote->getVoterId()}", array(),
                    60 * 60 * 24 * 4
                );
                $aTopicIdNeedStore = array_diff($aTopicIdNeedStore, array($oTopicVote->getTopicId()));
            }
        }
        /**
         * Сохраняем в кеш запросы не вернувшие результата
         */
        foreach ($aTopicIdNeedStore as $sId) {
            $this->Cache_Set(null, "topic_question_vote_{$sId}_{$nUserId}", array(), 60 * 60 * 24 * 4);
        }
        /**
         * Сортируем результат согласно входящему массиву
         */
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
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = $this->oMapper->GetTopicsQuestionVoteByArray($aTopicsId, $nUserId);
            foreach ($data as $oTopicVote) {
                $aTopicsQuestionVote[$oTopicVote->getTopicId()] = $oTopicVote;
            }
            $this->Cache_Set($aTopicsQuestionVote, $sCacheKey, array("topic_question_vote_user_{$nUserId}"), 'P1D');
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

        $this->Cache_Delete(
            "topic_question_vote_{$oTopicQuestionVote->getTopicId()}_{$oTopicQuestionVote->getVoterId()}"
        );
        $this->Cache_CleanByTags(array("topic_question_vote_user_{$oTopicQuestionVote->getVoterId()}"));
        return $this->oMapper->AddTopicQuestionVote($oTopicQuestionVote);
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

        $aBlogUsersResult = $this->Blog_GetBlogUsersByBlogId(
            $oBlog->getId(), null, null
        ); // нужно постранично пробегаться по всем
        $aBlogUsers = $aBlogUsersResult['collection'];
        foreach ($aBlogUsers as $oBlogUser) {
            if ($oBlogUser->getUserId() == $oUserTopic->getId()) {
                continue;
            }
            $this->Notify_SendTopicNewToSubscribeBlog($oBlogUser->getUser(), $oTopic, $oBlog, $oUserTopic);
        }
        //отправляем создателю блога
        if ($oBlog->getOwnerId() != $oUserTopic->getId()) {
            $this->Notify_SendTopicNewToSubscribeBlog($oBlog->getOwner(), $oTopic, $oBlog, $oUserTopic);
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

        $this->Cache_CleanByTags(array("topic_update", "topic_new_blog_{$nBlogId}"));
        if ($res = $this->oMapper->MoveTopicsByArrayId($aTopicsId, $nBlogId)) {
            // перемещаем теги
            $this->oMapper->MoveTopicsTagsByArrayId($aTopicsId, $nBlogId);
            // меняем target parent у комментов
            $this->Comment_UpdateTargetParentByTargetId($nBlogId, 'topic', $aTopicsId);
            // меняем target parent у комментов в прямом эфире
            $this->Comment_UpdateTargetParentByTargetIdOnline($nBlogId, 'topic', $aTopicsId);
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
            $this->Comment_MoveTargetParent($nBlogId, 'topic', $nBlogIdNew);
            // меняем target parent у комментов в прямом эфире
            $this->Comment_MoveTargetParentOnline($nBlogId, 'topic', $nBlogIdNew);
            return $bResult;
        }
        $this->Cache_CleanByTags(
            array("topic_update", "topic_new_blog_{$nBlogId}", "topic_new_blog_{$nBlogIdNew}")
        );
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

        $sFileTmp = $this->Img_TransformFile($sImageFile, $sType, $aOptions);
        if ($sFileTmp) {
            $sDirUpload = $this->Uploader_GetUserImageDir($oUser->getId());
            $sFileImage = $this->Uploader_Uniqname($sDirUpload, F::File_GetExtension($sFileTmp, true));
            if ($xStoredFile = $this->Uploader_Store($sFileTmp, $sFileImage)) {
                if (is_object($xStoredFile)) {
                    return $xStoredFile->GetUrl();
                } else {
                    return $this->Uploader_Dir2Url($xStoredFile);
                }
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

        if ($sFileTmp = $this->Uploader_UploadLocal($aFile)) {
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

        if ($sFileTmp = $this->Uploader_UploadRemote($sUrl)) {
            return $this->_saveTopicImage($sFileTmp, $oUser, 'topic', $aOptions);
        }
        return false;
    }

    /**
     * Возвращает список фотографий к топику-фотосет по списку ID фоток
     *
     * @param array $aPhotosId    Список ID фото
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
        if (false === ($data = $this->Cache_Get("photoset_photo_id_{$s}"))) {
            $data = $this->oMapper->GetTopicPhotosByArrayId($aPhotosId);
            foreach ($data as $oPhoto) {
                $aPhotos[$oPhoto->getId()] = $oPhoto;
            }
            $this->Cache_Set($aPhotos, "photoset_photo_id_{$s}", array("photoset_photo_update"), 'P1D');
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
            $this->Cache_CleanByTags(array('photoset_photo_update'));
            return $oPhoto;
        }
        return false;
    }

    /**
     * Получить изображение из фотосета по его ID
     *
     * @param int $nId    ID фото
     *
     * @return ModuleTopic_EntityTopicPhoto|null
     */
    public function getTopicPhotoById($nId) {

        $aPhotos = $this->GetTopicPhotosByArrayId($nId);
        if (isset($aPhotos[$nId])) {
            return $aPhotos[$nId];
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

        $this->Cache_CleanByTags(array('photoset_photo_update'));
        $this->oMapper->updateTopicPhoto($oPhoto);
    }

    /**
     * Удалить изображение
     *
     * @param ModuleTopic_EntityTopicPhoto $oPhoto - Объект фото
     */
    public function DeleteTopicPhoto($oPhoto) {

        $this->oMapper->deleteTopicPhoto($oPhoto->getId());

        $sFile = $this->Uploader_Url2Dir($oPhoto->getPath());
        $this->Img_Delete($sFile);
        $this->Cache_CleanByTags(array('photoset_photo_update'));
    }

    /**
     * Загрузить изображение
     *
     * @param array $aFile - Элемент массива $_FILES
     *
     * @return string|bool
     */
    public function UploadTopicPhoto($aFile) {

        if ($sFileTmp = $this->Uploader_UploadLocal($aFile)) {
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

        return $this->Uploader_GetErrorMsg();
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

    /*
     * Обработка дополнительных полей топика
     *
     * @param $oTopic
     *
     * @return bool
     */
    public function processTopicFields($oTopic, $sType = 'add') {

        $aValues = array();

        if ($sType == 'update') {
            // * Получаем существующие значения
            if ($aData = $this->GetTopicValuesByArrayId(array($oTopic->getId()))) {
                $aValues = $aData[$oTopic->getId()];
            }
            // * Чистим существующие значения
            $this->Topic_DeleteTopicValuesByTopicId($oTopic->getId());
        }

        if ($oType = $this->Topic_GetContentTypeByUrl($oTopic->getType())) {

            //получаем поля для данного типа
            if ($aFields = $oType->getFields()) {
                foreach ($aFields as $oField) {
                    $sData = null;
                    if (isset($_REQUEST['fields'][$oField->getFieldId()]) || isset($_FILES['fields_' . $oField->getFieldId()])) {

                        //текстовые поля
                        if (in_array($oField->getFieldType(), array('input', 'textarea', 'select'))) {
                            $sData = $this->Text_Parser($_REQUEST['fields'][$oField->getFieldId()]);
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
                                if ($oTopic->getFile($oField->getFieldId())) {
                                    @unlink(Config::Get('path.root.dir') . $oTopic->getFile($oField->getFieldId())->getFileUrl());
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
                                if (filesize($_FILES['fields_' . $oField->getFieldId()]['tmp_name']) <= Config::Get('module.topic.max_filesize_limit')) {
                                    $aPathInfo = pathinfo($_FILES['fields_' . $oField->getFieldId()]['name']);

                                    if (in_array(
                                        strtolower($aPathInfo['extension']),
                                        Config::Get('module.topic.upload_mime_types')
                                    )
                                    ) {
                                        $sFileTmp = $_FILES['fields_' . $oField->getFieldId()]['tmp_name'];
                                        $sDirSave = Config::Get('path.uploads.root') . '/files/' . $this->User_GetUserCurrent()->getId() . '/' . F::RandomStr(16);
                                        mkdir(Config::Get('path.root.dir') . $sDirSave, 0777, true);
                                        if (is_dir(Config::Get('path.root.dir') . $sDirSave)) {

                                            $sFile = $sDirSave . '/' . F::RandomStr(10) . '.' . strtolower($aPathInfo['extension']);
                                            $sFileFullPath = Config::Get('path.root.dir') . $sFile;
                                            if (copy($sFileTmp, $sFileFullPath)) {
                                                //удаляем старый файл
                                                if ($oTopic->getFile($oField->getFieldId())) {
                                                    @unlink(
                                                        Config::Get('path.root.dir') . $oTopic->getFile($oField->getFieldId())->getFileUrl());
                                                }

                                                $aFileObj = array();
                                                $aFileObj['file_hash'] = F::RandomStr(32);
                                                $aFileObj['file_name'] = $this->Text_Parser($_FILES['fields_' . $oField->getFieldId()]['name']);
                                                $aFileObj['file_url'] = $sFile;
                                                $aFileObj['file_size'] = $_FILES['fields_' . $oField->getFieldId()]['size'];
                                                $aFileObj['file_extension'] = $aPathInfo['extension'];
                                                $aFileObj['file_downloads'] = 0;
                                                $sData = serialize($aFileObj);

                                                @unlink($sFileTmp);
                                            }
                                        }
                                    }
                                }
                            }
                            @unlink($_FILES['fields_' . $oField->getFieldId()]['tmp_name']);
                        }

                        $this->Hook_Run('content_field_proccess', array('sData' => &$sData, 'oField' => $oField, 'oTopic' => $oTopic, 'aValues' => $aValues, 'sType' => &$sType));

                        //Добавляем поле к топику.
                        if ($sData) {
                            $oValue = Engine::GetEntity('Topic_ContentValues');
                            $oValue->setTargetId($oTopic->getId());
                            $oValue->setTargetType('topic');
                            $oValue->setFieldId($oField->getFieldId());
                            $oValue->setFieldType($oField->getFieldType());
                            $oValue->setValue($sData);
                            $oValue->setValueSource(($oField->getFieldType() == 'file') ? $sData : $_REQUEST['fields'][$oField->getFieldId()]);

                            $this->Topic_AddTopicValue($oValue);

                        }
                    }
                }
            }
        }
    }

    /**
     * Updates mresources of topic
     *
     * @param ModuleTopic_EntityTopic $oTopic
     */
    public function UpdateMresources($oTopic) {

        $this->oMapper->attachTmpPhotoToTopic($oTopic);

        // Получаем список ресурсов (хеш-таблицу)
        $aList = $oTopic->BuildMresourcesList();

        // Читаем список ресурсов из базы
        $aMresources = $this->Mresource_GetMresourcesRelByTarget('topic', $oTopic->GetId());

        // Строим список ID ресурсов для удаления
        $aDeleteRelId = array();
        foreach ($aMresources as $oMresource) {
            if (isset($aList[$oMresource->GetHash()])) {
                // Если сохраненный ресурс есть в хеш-таблице, то чистим соответствующий хеш
                unset($aList[$oMresource->GetHash()]);
            } else {
                // Если ресурса нет в хеш-таблице, то это прентендент на удаление
                $aDeleteRelId[] = $oMresource->GetId();
            }
        }
        // В списке остались только новые ресурсы
        if ($aList) {
            $this->Mresource_AddTargetRel($aList, 'topic', $oTopic->GetId());
        }
        if ($aDeleteRelId) {
            $this->Mresource_DeleteMresourcesRel($aDeleteRelId);
        }
    }

    /**
     * Delete MResources associated with topic(s)
     *
     * @param $aTopics
     */
    public function DeleteMresources($aTopics) {

        if (!is_array($aTopics)) {
            $aTopics = array($aTopics);
        }
        foreach ($aTopics as $oTopic) {
            $this->Mresource_DeleteMresourcesRelByTarget('topic', $oTopic->GetId());
        }
    }

}

// EOF