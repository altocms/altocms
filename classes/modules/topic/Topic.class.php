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

    /**
	 * Уровень доступа для всех зарегистрированных
	 */
	const CONTENT_ACCESS_ALL = 1;
	/**
	 * Уровень доступа только для админов
	 */
	const CONTENT_ACCESS_ONLY_ADMIN = 2;

    /**
     * Объект маппера
     *
     * @var ModuleTopic_MapperTopic
     */
    protected $oMapperTopic;
    /**
     * Объект текущего пользователя
     *
     * @var ModuleUser_EntityUser|null
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

    /**
     * Инициализация
     *
     */
    public function Init() {
        $this->oMapperTopic = Engine::GetMapper(__CLASS__);
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
            $aAllowData = array('fields' => array());
        }
        $s = serialize($aFilter);
        if (false === ($data = $this->Cache_Get("content_types_{$s}"))) {
            $data = $this->oMapperTopic->getContentTypes($aFilter);
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
     * @return ModuleTopic_EntityContent|null
     */
    public function GetContentTypeById($nId) {
        if (false === ($data = $this->Cache_Get("content_type_{$nId}"))) {
            $data = $this->oMapperTopic->getContentTypeById($nId);
            $this->Cache_Set($data, "content_type_{$nId}", array('content_update', 'content_new'), 60 * 60 * 24 * 1);
        }
        return $data;
    }

    /**
     * Получить тип контента по url
     *
     * @param string $sUrl
     *
     * @return ModuleTopic_EntityContent|null
     */
    public function GetContentTypeByUrl($sUrl) {
        if (false === ($data = $this->Cache_Get("content_type_{$sUrl}"))) {
            $data = $this->oMapperTopic->getContentTypeByUrl($sUrl);
            $this->Cache_Set($data, "content_type_{$sUrl}", array('content_update', 'content_new'), 'P1D');
        }
        return $data;
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
        return $this->oMapperTopic->changeType($sTypeOld, $sTypeNew);
    }

    /**
     * Добавляет тип контента
     *
     * @param ModuleTopic_EntityContent $oType    Объект типа контента
     *
     * @return ModuleTopic_EntityContent|bool
     */
    public function AddContentType(ModuleTopic_EntityContent $oType) {
        if ($sId = $this->oMapperTopic->AddContentType($oType)) {
            $oType->setContentId($sId);
            //чистим зависимые кеши
            $this->Cache_Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('content_new', 'content_update'));
            return $oType;
        }
        return false;
    }

    /**
     * Обновляет топик
     *
     * @param ModuleTopic_EntityContent $oType    Объект типа контента
     *
     * @return bool
     */
    public function UpdateContentType(ModuleTopic_EntityContent $oType) {

        if ($this->oMapperTopic->UpdateContentType($oType)) {

            //чистим зависимые кеши
            $this->Cache_Clean(
                Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('content_new', 'content_update', 'topic_update')
            );
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
        $s = serialize($aFilter);
        if (false === ($data = $this->Cache_Get("content_fields_{$s}"))) {
            $data = $this->oMapperTopic->getContentFields($aFilter);
            $this->Cache_Set($data, "content_fields_{$s}", array('content_update', 'content_new'), 60 * 60 * 24 * 1);
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
    public function AddContentField(ModuleTopic_EntityField $oField) {
        if ($sId = $this->oMapperTopic->AddContentField($oField)) {
            $oField->setFieldId($sId);
            //чистим зависимые кеши
            $this->Cache_Clean(
                Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                array('content_new', 'content_update', 'field_new', 'field_update')
            );
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
    public function UpdateContentField(ModuleTopic_EntityField $oField) {

        if ($this->oMapperTopic->UpdateContentField($oField)) {

            //чистим зависимые кеши
            $this->Cache_Clean(
                Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                array('content_new', 'content_update', 'field_new', 'field_update')
            );
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
     * @return ModuleTopic_EntityField|null
     */
    public function GetContentFieldById($nId) {
        if (false === ($data = $this->Cache_Get("content_field_{$nId}"))) {
            $data = $this->oMapperTopic->getContentFieldById($nId);
            $this->Cache_Set(
                $data, "content_field_{$nId}", array('content_new', 'content_update', 'field_new', 'field_update'),
                60 * 60 * 24 * 1
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
        /**
         * Чистим зависимые кеши
         */
        $this->Cache_Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('field_update'));
        $this->Cache_Delete("content_field_{$oField->getFieldId()}");
        /**
         * Если топик успешно удален, удаляем связанные данные
         */
        if ($bResult = $this->oMapperTopic->DeleteField($oField)) {
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
    public function AddTopicValue(ModuleTopic_EntityContentValues $oValue) {
        return $this->oMapperTopic->AddTopicValue($oValue);
    }

    /**
     * Обновляет значение поля топика
     *
     * @param ModuleTopic_EntityContentValues $oValue    Объект поля
     *
     * @return bool
     */
    public function UpdateContentFieldValue(ModuleTopic_EntityContentValues $oValue) {
        if ($this->oMapperTopic->UpdateContentFieldValue($oValue)) {
            //чистим зависимые кеши
            $this->Cache_Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('topic_update'));
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
            $aAllowData = array('user' => array(), 'blog' => array('owner' => array(), 'relation_user'), 'vote',
                                'favourite', 'fields', 'comment_new');
        }
        func_array_simpleflip($aAllowData);
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
            if ($oTopic->getPhotosetMainPhotoId())	{
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
        $aUsers = isset($aAllowData['user']) && is_array($aAllowData['user']) ? $this->User_GetUsersAdditionalData(
            $aUserId, $aAllowData['user']
        ) : $this->User_GetUsersAdditionalData($aUserId);
        $aBlogs = isset($aAllowData['blog']) && is_array($aAllowData['blog']) ? $this->Blog_GetBlogsAdditionalData(
            $aBlogId, $aAllowData['blog']
        ) : $this->Blog_GetBlogsAdditionalData($aBlogId);
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
    public function AddTopic(ModuleTopic_EntityTopic $oTopic) {
        if ($sId = $this->oMapperTopic->AddTopic($oTopic)) {
            $oTopic->setId($sId);
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
            $this->processTopicFields($oTopic,'add');
            //чистим зависимые кеши
            $this->Cache_Clean(
                Zend_Cache::CLEANING_MODE_MATCHING_TAG,
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
    public function AddTopicTag(ModuleTopic_EntityTopicTag $oTopicTag) {
        return $this->oMapperTopic->AddTopicTag($oTopicTag);
    }

    /**
     * Удаляет теги у топика
     *
     * @param   int|array   $aTopicsId  ID топика
     *
     * @return  bool
     */
    public function DeleteTopicTagsByTopicId($aTopicsId) {
        return $this->oMapperTopic->DeleteTopicTagsByTopicId($aTopicsId);
    }

    /**
     * Удаляет значения полей у топика
     *
     * @param int $sTopicId    ID топика
     *
     * @return bool
     */
    public function DeleteTopicValuesByTopicId($sTopicId) {
        return $this->oMapperTopic->DeleteTopicValuesByTopicId($sTopicId);
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
            $nTopicId = $oTopicId->getId();
        } else {
            $nTopicId = intval($oTopicId);
        }
        /**
         * Если топик успешно удален, удаляем связанные данные
         */
        if ($bResult = $this->oMapperTopic->DeleteTopic($nTopicId)) {
            $bResult = $this->DeleteTopicAdditionalData($nTopicId);
        }

        // * Чистим зависимые кеши
        if (($oTopicId instanceof ModuleTopic_EntityTopic) && $oTopicId->getUserId()) {
            $this->Cache_Clean(
                Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("topic_update_user_{$oTopicId->getUserId()}")
            );
        }
        $this->Cache_Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('topic_update'));
        $this->Cache_Delete("topic_{$nTopicId}");
        return $bResult;
    }

    public function DeleteTopicsByUsersId($aUsersId) {
        $aFilter = array(
            'user_id' => $aUsersId,
        );
        $aTopicsId = $this->oMapperTopic->GetAllTopics($aFilter);

        if ($bResult = $this->oMapperTopic->DeleteTopic($aTopicsId)) {
            $bResult = $this->DeleteTopicAdditionalData($aTopicsId);
        }

        // * Чистим зависимые кеши
        foreach ($aUsersId as $nUserId) {
            $this->Cache_Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("topic_update_user_{$nUserId}"));
        }
        $this->Cache_Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('topic_update'));
        foreach ($aTopicsId as $nTopicId) {
            $this->Cache_Delete("topic_{$nTopicId}");
        }
        return $bResult;
    }

    /**
     * Удаляет свзяанные с топиком данные
     *
     * @param   int|array   $aTopicsId   ID топика или массив ID
     *
     * @return  bool
     */
    public function DeleteTopicAdditionalData($aTopicsId) {
        if (!is_array($aTopicsId)) {
            $aTopicsId = array(intval($aTopicsId));
        }

        // * Удаляем контент топика
        $this->DeleteTopicContentByTopicId($aTopicsId);
        /**
         * Удаляем комментарии к топику.
         * При удалении комментариев они удаляются из избранного,прямого эфира и голоса за них
         */
        $this->Comment_DeleteCommentByTargetId($aTopicsId, 'topic');
        /**
         * Удаляем топик из избранного
         */
        $this->DeleteFavouriteTopicByArrayId($aTopicsId);
        /**
         * Удаляем топик из прочитанного
         */
        $this->DeleteTopicReadByArrayId($aTopicsId);
        /**
         * Удаляем голосование к топику
         */
        $this->Vote_DeleteVoteByTarget($aTopicsId, 'topic');
        /**
         * Удаляем теги
         */
        $this->DeleteTopicTagsByTopicId($aTopicsId);
        /**
         * Удаляем фото у топика фотосета
         */
        if ($aPhotos = $this->getPhotosByTopicId($aTopicsId)) {
            foreach ($aPhotos as $oPhoto) {
                $this->deleteTopicPhoto($oPhoto);
            }
        }
        /**
         * Чистим зависимые кеши
         */
        $this->Cache_Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('topic_update'));
        foreach ($aTopicsId as $nTopicId) {
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
    public function UpdateTopic(ModuleTopic_EntityTopic $oTopic) {
        // * Получаем топик ДО изменения
        $oTopicOld = $this->GetTopicById($oTopic->getId());
        $oTopic->setDateEdit(F::Now());
        if ($this->oMapperTopic->UpdateTopic($oTopic)) {
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
                /**
                 * Обновляем избранное
                 */
                $this->SetFavouriteTopicPublish($oTopic->getId(), $oTopic->getPublish());
                /**
                 * Удаляем комментарий топика из прямого эфира
                 */
                if ($oTopic->getPublish() == 0) {
                    $this->Comment_DeleteCommentOnlineByTargetId($oTopic->getId(), 'topic');
                }
                /**
                 * Изменяем видимость комментов
                 */
                $this->Comment_SetCommentsPublish($oTopic->getId(), 'topic', $oTopic->getPublish());
            }
            $this->processTopicFields($oTopic,'update');
            // чистим зависимые кеши
            $this->Cache_Clean(
                Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                array('topic_update', "topic_update_user_{$oTopic->getUserId()}")
            );
            $this->Cache_Delete("topic_{$oTopic->getId()}");
            return true;
        }
        return false;
    }

    /**
     * Удаление контента топика по его номеру
     *
     * @param   int|array   $aTopicsId   - ID топика или массив ID
     *
     * @return  bool
     */
    public function DeleteTopicContentByTopicId($aTopicsId) {
        return $this->oMapperTopic->DeleteTopicContentByTopicId($aTopicsId);
    }

    /**
     * Получить топик по айдишнику
     *
     * @param int $sId    ID топика
     *
     * @return ModuleTopic_EntityTopic|null
     */
    public function GetTopicById($sId) {
        if (!is_numeric($sId)) {
            return null;
        }
        $aTopics = $this->GetTopicsAdditionalData($sId);
        if (isset($aTopics[$sId])) {
            return $aTopics[$sId];
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
        $nTopicId = $this->oMapperTopic->GetTopicIdByUrl($sUrl);
        return $this->GetTopicById($nTopicId);
    }

    /**
     * Получить топики по похожим URL
     *
     * @param string $sUrl
     *
     * @return ModuleTopic_EntityTopic|null
     */
    public function GetTopicsLikeUrl($sUrl) {
        $aTopicsId = $this->oMapperTopic->GetTopicsIdLikeUrl($sUrl);
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
            foreach($aTopics as $oTopic) {
                $aExistUrls[] = $oTopic->GetTopicUrl();
            }
            $nNum = count($aTopics) + 1;
            $sNewUrl = $sUrl . '-' . $nNum;
            while(in_array($sNewUrl, $aExistUrls)) {
                $sNewUrl = $sUrl . '-' . (++$nNum);
            }
            $sUrl = $sNewUrl;
        }
        return $sUrl;
    }

    /**
     * Получить список топиков по списку айдишников
     *
     * @param array $aTopicId    Список ID топиков
     *
     * @return int|array
     */
    public function GetTopicsByArrayId($aTopicId) {
        if (!$aTopicId) {
            return array();
        }
        if (Config::Get('sys.cache.solid')) {
            return $this->GetTopicsByArrayIdSolid($aTopicId);
        }

        if (!is_array($aTopicId)) {
            $aTopicId = array($aTopicId);
        }
        $aTopicId = array_unique($aTopicId);
        $aTopics = array();
        $aTopicIdNotNeedQuery = array();
        /**
         * Делаем мульти-запрос к кешу
         */
        $aCacheKeys = func_build_cache_keys($aTopicId, 'topic_');
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
        $aTopicIdNeedQuery = array_diff($aTopicId, array_keys($aTopics));
        $aTopicIdNeedQuery = array_diff($aTopicIdNeedQuery, $aTopicIdNotNeedQuery);
        $aTopicIdNeedStore = $aTopicIdNeedQuery;
        if ($data = $this->oMapperTopic->GetTopicsByArrayId($aTopicIdNeedQuery)) {
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
        foreach ($aTopicIdNeedStore as $sId) {
            $this->Cache_Set(null, "topic_{$sId}", array(), 60 * 60 * 24 * 4);
        }
        /**
         * Сортируем результат согласно входящему массиву
         */
        $aTopics = func_array_sort_by_keys($aTopics, $aTopicId);
        return $aTopics;
    }

    /**
     * Получить список топиков по списку айдишников, но используя единый кеш
     *
     * @param array $aTopicId    Список ID топиков
     *
     * @return array
     */
    public function GetTopicsByArrayIdSolid($aTopicId) {
        if (!is_array($aTopicId)) {
            $aTopicId = array($aTopicId);
        }
        $aTopicId = array_unique($aTopicId);
        $aTopics = array();
        $s = join(',', $aTopicId);
        if (false === ($data = $this->Cache_Get("topic_id_{$s}"))) {
            $data = $this->oMapperTopic->GetTopicsByArrayId($aTopicId);
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
     * @param  int    $sUserId      ID пользователя
     * @param  int    $iCurrPage    Номер текущей страницы
     * @param  int    $iPerPage     Количество элементов на страницу
     *
     * @return array('collection'=>array,'count'=>int)
     */
    public function GetTopicsFavouriteByUserId($sUserId, $iCurrPage, $iPerPage) {
        $aCloseTopics = array();
        /**
         * Получаем список идентификаторов избранных записей
         */
        $data = ($this->oUserCurrent && $sUserId == $this->oUserCurrent->getId())
            ? $this->Favourite_GetFavouritesByUserId($sUserId, 'topic', $iCurrPage, $iPerPage, $aCloseTopics)
            : $this->Favourite_GetFavouriteOpenTopicsByUserId($sUserId, $iCurrPage, $iPerPage);
        /**
         * Получаем записи по переданому массиву айдишников
         */
        $data['collection'] = $this->GetTopicsAdditionalData($data['collection']);
        return $data;
    }

    /**
     * Возвращает число топиков в избранном
     *
     * @param  int $sUserId    ID пользователя
     *
     * @return int
     */
    public function GetCountTopicsFavouriteByUserId($sUserId) {
        $aCloseTopics = array();
        return ($this->oUserCurrent && $sUserId == $this->oUserCurrent->getId())
            ? $this->Favourite_GetCountFavouritesByUserId($sUserId, 'topic', $aCloseTopics)
            : $this->Favourite_GetCountFavouriteOpenTopicsByUserId($sUserId);
    }

    /**
     * Список топиков по фильтру
     *
     * @param  array        $aFilter       Фильтр
     * @param  int          $iPage         Номер страницы
     * @param  int          $iPerPage      Количество элементов на страницу
     * @param  array|null   $aAllowData    Список типов данных для подгрузки в топики
     *
     * @return array('collection'=>array,'count'=>int)
     */
    public function GetTopicsByFilter($aFilter, $iPage = 1, $iPerPage = 10, $aAllowData = null) {
        if (!is_numeric($iPage) || $iPage <= 0) {
            $iPage = 1;
        }
        $s = serialize($aFilter);
        if (false === ($data = $this->Cache_Get("topic_filter_{$s}_{$iPage}_{$iPerPage}"))) {
            $data = array(
                'collection' => $this->oMapperTopic->GetTopics($aFilter, $iCount, $iPage, $iPerPage),
                'count'      => $iCount
            );
            $this->Cache_Set(
                $data, "topic_filter_{$s}_{$iPage}_{$iPerPage}", array('topic_update', 'topic_new'), 60 * 60 * 24 * 3
            );
        }
        $data['collection'] = $this->GetTopicsAdditionalData($data['collection'], $aAllowData);
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
        $s = serialize($aFilter);
        if (false === ($data = $this->Cache_Get("topic_count_{$s}"))) {
            $data = $this->oMapperTopic->GetCountTopics($aFilter);
            $this->Cache_Set($data, "topic_count_{$s}", array('topic_update', 'topic_new'), 60 * 60 * 24 * 1);
        }
        return $data;
    }

    /**
     * Количество черновиков у пользователя
     *
     * @param int $iUserId    ID пользователя
     *
     * @return int
     */
    public function GetCountDraftTopicsByUserId($iUserId) {
        return $this->GetCountTopicsByFilter(
            array(
                 'user_id'       => $iUserId,
                 'topic_publish' => 0
            )
        );
    }

    /**
     * Получает список хороших топиков для вывода на главную страницу(из всех блогов, как коллективных так и персональных)
     *
     * @param  int    $iPage          Номер страницы
     * @param  int    $iPerPage       Количество элементов на страницу
     * @param  bool   $bAddAccessible Указывает на необходимость добавить в выдачу топики,
     *                                из блогов доступных пользователю. При указании false,
     *                                в выдачу будут переданы только топики из общедоступных блогов.
     *
     * @return array
     */
    public function GetTopicsGood($iPage, $iPerPage, $bAddAccessible = true) {
        $aFilter = array(
            'blog_type'     => array(
                'personal',
                'open'
            ),
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
     * @param  int    $iPage          Номер страницы
     * @param  int    $iPerPage       Количество элементов на страницу
     * @param  bool   $bAddAccessible Указывает на необходимость добавить в выдачу топики,
     *                                из блогов доступных пользователю. При указании false,
     *                                в выдачу будут переданы только топики из общедоступных блогов.
     *
     * @return array
     */
    public function GetTopicsNew($iPage, $iPerPage, $bAddAccessible = true) {
        $sDate = date("Y-m-d H:00:00", time() - Config::Get('module.topic.new_time'));
        $aFilter = array(
            'blog_type'     => array(
                'personal',
                'open',
            ),
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
     * @param  int    $iPage          Номер страницы
     * @param  int    $iPerPage       Количество элементов на страницу
     * @param  bool   $bAddAccessible Указывает на необходимость добавить в выдачу топики,
     *                                из блогов доступных пользователю. При указании false,
     *                                в выдачу будут переданы только топики из общедоступных блогов.
     *
     * @return array
     */
    public function GetTopicsNewAll($iPage, $iPerPage, $bAddAccessible = true) {
        $aFilter = array(
            'blog_type'     => array(
                'personal',
                'open',
            ),
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
     * @param  int          $iPage          Номер страницы
     * @param  int          $iPerPage       Количество элементов на страницу
     * @param  int|string   $sPeriod        Период в виде секунд или конкретной даты
     * @param  bool         $bAddAccessible Указывает на необходимость добавить в выдачу топики,
     *                                из блогов доступных пользователю. При указании false,
     *                                в выдачу будут переданы только топики из общедоступных блогов.
     *
     * @return array
     */
    public function GetTopicsDiscussed($iPage, $iPerPage, $sPeriod = null, $bAddAccessible = true) {
        if (is_numeric($sPeriod)) {
            // количество последних секунд
            $sPeriod = date("Y-m-d H:00:00", time() - $sPeriod);
        }

        $aFilter = array(
            'blog_type'     => array(
                'personal',
                'open',
            ),
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
     * @param  int          $iPage          Номер страницы
     * @param  int          $iPerPage       Количество элементов на страницу
     * @param  int|string   $sPeriod        Период в виде секунд или конкретной даты
     * @param  bool         $bAddAccessible Указывает на необходимость добавить в выдачу топики,
     *                                из блогов доступных пользователю. При указании false,
     *                                в выдачу будут переданы только топики из общедоступных блогов.
     *
     * @return array
     */
    public function GetTopicsTop($iPage, $iPerPage, $sPeriod = null, $bAddAccessible = true) {
        if (is_numeric($sPeriod)) {
            // количество последних секунд
            $sPeriod = date("Y-m-d H:00:00", time() - $sPeriod);
        }

        $aFilter = array(
            'blog_type'     => array(
                'personal',
                'open',
            ),
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
     * @param int $iCount    Количество
     *
     * @return array
     */
    public function GetTopicsLast($iCount) {
        $aFilter = array(
            'blog_type'     => array(
                'personal',
                'open',
            ),
            'topic_publish' => 1,
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
        $aReturn = $this->GetTopicsByFilter($aFilter, 1, $iCount);
        if (isset($aReturn['collection'])) {
            return $aReturn['collection'];
        }
        return false;
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
            $sPeriod = date("Y-m-d H:00:00", time() - $sPeriod);
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
                $aFilter['topic_new'] = date("Y-m-d H:00:00", time() - Config::Get('module.topic.new_time'));
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
        $sDate = date("Y-m-d H:00:00", time() - Config::Get('module.topic.new_time'));
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
     * @param int $sUserId     ID пользователя
     * @param int $iPublish    Флаг публикации топика
     * @param int $iPage       Номер страницы
     * @param int $iPerPage    Количество элементов на страницу
     *
     * @return array
     */
    public function GetTopicsPersonalByUser($sUserId, $iPublish, $iPage, $iPerPage) {
        $aFilter = array(
            'topic_publish' => $iPublish,
            'user_id'       => $sUserId,
            'blog_type'     => array('open', 'personal'),
        );
        /**
         * Если пользователь смотрит свой профиль, то добавляем в выдачу
         * закрытые блоги в которых он состоит
         */
        if ($this->oUserCurrent && $this->oUserCurrent->getId() == $sUserId) {
            $aFilter['blog_type'][] = 'close';
        }
        return $this->GetTopicsByFilter($aFilter, $iPage, $iPerPage);
    }

    /**
     * Возвращает количество топиков которые создал юзер
     *
     * @param int $sUserId     ID пользователя
     * @param int $iPublish    Флаг публикации топика
     *
     * @return array
     */
    public function GetCountTopicsPersonalByUser($sUserId, $iPublish) {
        $aFilter = array(
            'topic_publish' => $iPublish,
            'user_id'       => $sUserId,
            'blog_type'     => array('open', 'personal'),
        );
        /**
         * Если пользователь смотрит свой профиль, то добавляем в выдачу
         * закрытые блоги в которых он состоит
         */
        if ($this->oUserCurrent && $this->oUserCurrent->getId() == $sUserId) {
            $aFilter['blog_type'][] = 'close';
        }
        $s = serialize($aFilter);
        if (false === ($data = $this->Cache_Get("topic_count_user_{$s}"))) {
            $data = $this->oMapperTopic->GetCountTopics($aFilter);
            $this->Cache_Set($data, "topic_count_user_{$s}", array("topic_update_user_{$sUserId}"), 60 * 60 * 24);
        }
        return $data;
    }

    /**
     * Получает список топиков из указанного блога
     *
     * @param  int|array    $iBlogId       - ID блога | массив ID блогов
     * @param  int          $iPage         Номер страницы
     * @param  int          $iPerPage      Количество элементов на страницу
     * @param  array        $aAllowData    Список типов данных для подгрузки в топики
     * @param  bool         $bIdsOnly      Возвращать только ID или список объектов
     *
     * @return array
     */
    public function GetTopicsByBlogId($iBlogId, $iPage = 0, $iPerPage = 0, $aAllowData = array(), $bIdsOnly = true) {
        $aFilter = array('blog_id' => $iBlogId);

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
            $sPeriod = date("Y-m-d H:00:00", time() - $sPeriod);
        }
        $aFilter = array(
            'blog_type'     => array(
                'open',
            ),
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
                $aFilter['topic_new'] = date("Y-m-d H:00:00", time() - Config::Get('module.topic.new_time'));
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
        $sDate = date("Y-m-d H:00:00", time() - Config::Get('module.topic.new_time'));
        $aFilter = array(
            'blog_type'     => array(
                'open',
            ),
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

        $s = serialize($aCloseBlogs);

        if (false === ($data = $this->Cache_Get("topic_rating_{$sDate}_{$iLimit}_{$s}"))) {
            $data = $this->oMapperTopic->GetTopicsRatingByDate($sDate, $iLimit, $aCloseBlogs);
            $this->Cache_Set($data, "topic_rating_{$sDate}_{$iLimit}_{$s}", array('topic_update'), 60 * 60 * 24 * 2);
        }
        $data = $this->GetTopicsAdditionalData($data);
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
            $sPeriod = date("Y-m-d H:00:00", time() - $sPeriod);
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
                $aFilter['topic_new'] = date("Y-m-d H:00:00", time() - Config::Get('module.topic.new_time'));
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
        $sDate = date("Y-m-d H:00:00", time() - Config::Get('module.topic.new_time'));
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

        $s = serialize($aCloseBlogs);
        if (false === ($data = $this->Cache_Get("topic_tag_{$sTag}_{$iPage}_{$iPerPage}_{$s}"))) {
            $data = array('collection' => $this->oMapperTopic->GetTopicsByTag(
                $sTag, $aCloseBlogs, $iCount, $iPage, $iPerPage
            ), 'count'                 => $iCount);
            $this->Cache_Set(
                $data, "topic_tag_{$sTag}_{$iPage}_{$iPerPage}_{$s}", array('topic_update', 'topic_new'),
                60 * 60 * 24 * 2
            );
        }
        $data['collection'] = $this->GetTopicsAdditionalData($data['collection']);
        return $data;
    }

    /**
     * Получает список топиков по типам
     *
     * @param  int    $iPage          Номер страницы
     * @param  int    $iPerPage       Количество элементов на страницу
     * @param  bool   $bAddAccessible Указывает на необходимость добавить в выдачу топики,
     *                                из блогов доступных пользователю. При указании false,
     *                                в выдачу будут переданы только топики из общедоступных блогов.
     *
     * @return array
     */
    public function GetTopicsByType(
        $iPage, $iPerPage, $sType, $bAddAccessible = true, $aTypes = array('personal', 'open')
    ) {
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
     * @param int   $iLimit           Количество
     * @param array $aExcludeTopic    Список ID топиков для исключения
     *
     * @return array
     */
    public function GetTopicTags($iLimit, $aExcludeTopic = array()) {
        $s = serialize($aExcludeTopic);
        if (false === ($data = $this->Cache_Get("tag_{$iLimit}_{$s}"))) {
            $data = $this->oMapperTopic->GetTopicTags($iLimit, $aExcludeTopic);
            $this->Cache_Set($data, "tag_{$iLimit}_{$s}", array('topic_update', 'topic_new'), 60 * 60 * 24 * 3);
        }
        return $data;
    }

    /**
     * Получает список тегов из топиков открытых блогов (open,personal)
     *
     * @param  int      $iLimit     Количество
     * @param  int|null $iUserId    ID пользователя, чью теги получаем
     *
     * @return array
     */
    public function GetOpenTopicTags($iLimit, $iUserId = null) {
        if (false === ($data = $this->Cache_Get("tag_{$iLimit}_{$iUserId}_open"))) {
            $data = $this->oMapperTopic->GetOpenTopicTags($iLimit, $iUserId);
            $this->Cache_Set(
                $data, "tag_{$iLimit}_{$iUserId}_open", array('topic_update', 'topic_new'), 60 * 60 * 24 * 3
            );
        }
        return $data;
    }

    /**
     * Увеличивает у топика число комментов
     *
     * @param int $sTopicId    ID топика
     *
     * @return bool
     */
    public function increaseTopicCountComment($sTopicId) {
        $this->Cache_Delete("topic_{$sTopicId}");
        $this->Cache_Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("topic_update"));
        return $this->oMapperTopic->increaseTopicCountComment($sTopicId);
    }

    /**
     * Получает привязку топика к ибранному(добавлен ли топик в избранное у юзера)
     *
     * @param int $sTopicId    ID топика
     * @param int $sUserId     ID пользователя
     *
     * @return ModuleFavourite_EntityFavourite
     */
    public function GetFavouriteTopic($sTopicId, $sUserId) {
        return $this->Favourite_GetFavourite($sTopicId, 'topic', $sUserId);
    }

    /**
     * Получить список избранного по списку айдишников
     *
     * @param array $aTopicId    Список ID топиков
     * @param int   $sUserId     ID пользователя
     *
     * @return array
     */
    public function GetFavouriteTopicsByArray($aTopicId, $sUserId) {
        return $this->Favourite_GetFavouritesByArray($aTopicId, 'topic', $sUserId);
    }

    /**
     * Получить список избранного по списку айдишников, но используя единый кеш
     *
     * @param array $aTopicId    Список ID топиков
     * @param int   $sUserId     ID пользователя
     *
     * @return array
     */
    public function GetFavouriteTopicsByArraySolid($aTopicId, $sUserId) {
        return $this->Favourite_GetFavouritesByArraySolid($aTopicId, 'topic', $sUserId);
    }

    /**
     * Добавляет топик в избранное
     *
     * @param ModuleFavourite_EntityFavourite $oFavouriteTopic    Объект избранного
     *
     * @return bool
     */
    public function AddFavouriteTopic(ModuleFavourite_EntityFavourite $oFavouriteTopic) {
        return $this->Favourite_AddFavourite($oFavouriteTopic);
    }

    /**
     * Удаляет топик из избранного
     *
     * @param ModuleFavourite_EntityFavourite $oFavouriteTopic    Объект избранного
     *
     * @return bool
     */
    public function DeleteFavouriteTopic(ModuleFavourite_EntityFavourite $oFavouriteTopic) {
        return $this->Favourite_DeleteFavourite($oFavouriteTopic);
    }

    /**
     * Устанавливает переданный параметр публикации таргета (топика)
     *
     * @param  int    $sTopicId    ID топика
     * @param  int    $iPublish    Флаг публикации топика
     *
     * @return bool
     */
    public function SetFavouriteTopicPublish($sTopicId, $iPublish) {
        return $this->Favourite_SetFavouriteTargetPublish($sTopicId, 'topic', $iPublish);
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
     * @param string $sTag      Тэг
     * @param int    $iLimit    Количество
     *
     * @return bool
     */
    public function GetTopicTagsByLike($sTag, $iLimit) {
        if (false === ($data = $this->Cache_Get("tag_like_{$sTag}_{$iLimit}"))) {
            $data = $this->oMapperTopic->GetTopicTagsByLike($sTag, $iLimit);
            $this->Cache_Set($data, "tag_like_{$sTag}_{$iLimit}", array("topic_update", "topic_new"), 60 * 60 * 24 * 3);
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
    public function SetTopicRead(ModuleTopic_EntityTopicRead $oTopicRead) {
        if ($this->GetTopicRead($oTopicRead->getTopicId(), $oTopicRead->getUserId())) {
            $this->Cache_Delete("topic_read_{$oTopicRead->getTopicId()}_{$oTopicRead->getUserId()}");
            $this->Cache_Clean(
                Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("topic_read_user_{$oTopicRead->getUserId()}")
            );
            $this->oMapperTopic->UpdateTopicRead($oTopicRead);
        } else {
            $this->Cache_Delete("topic_read_{$oTopicRead->getTopicId()}_{$oTopicRead->getUserId()}");
            $this->Cache_Clean(
                Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("topic_read_user_{$oTopicRead->getUserId()}")
            );
            $this->oMapperTopic->AddTopicRead($oTopicRead);
        }
        return true;
    }

    /**
     * Получаем дату прочтения топика юзером
     *
     * @param int $sTopicId    ID топика
     * @param int $sUserId     ID пользователя
     *
     * @return ModuleTopic_EntityTopicRead|null
     */
    public function GetTopicRead($sTopicId, $sUserId) {
        $data = $this->GetTopicsReadByArray($sTopicId, $sUserId);
        if (isset($data[$sTopicId])) {
            return $data[$sTopicId];
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
        return $this->oMapperTopic->DeleteTopicReadByArrayId($aTopicsId);
    }

    /**
     * Получить список просмотром/чтения топиков по списку айдишников
     *
     * @param array $aTopicId    Список ID топиков
     * @param int   $sUserId     ID пользователя
     *
     * @return array
     */
    public function GetTopicsReadByArray($aTopicId, $sUserId) {
        if (!$aTopicId) {
            return array();
        }
        if (Config::Get('sys.cache.solid')) {
            return $this->GetTopicsReadByArraySolid($aTopicId, $sUserId);
        }
        if (!is_array($aTopicId)) {
            $aTopicId = array($aTopicId);
        }
        $aTopicId = array_unique($aTopicId);
        $aTopicsRead = array();
        $aTopicIdNotNeedQuery = array();
        /**
         * Делаем мульти-запрос к кешу
         */
        $aCacheKeys = func_build_cache_keys($aTopicId, 'topic_read_', '_' . $sUserId);
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
        $aTopicIdNeedQuery = array_diff($aTopicId, array_keys($aTopicsRead));
        $aTopicIdNeedQuery = array_diff($aTopicIdNeedQuery, $aTopicIdNotNeedQuery);
        $aTopicIdNeedStore = $aTopicIdNeedQuery;
        if ($data = $this->oMapperTopic->GetTopicsReadByArray($aTopicIdNeedQuery, $sUserId)) {
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
            $this->Cache_Set(null, "topic_read_{$sId}_{$sUserId}", array(), 60 * 60 * 24 * 4);
        }
        /**
         * Сортируем результат согласно входящему массиву
         */
        $aTopicsRead = func_array_sort_by_keys($aTopicsRead, $aTopicId);
        return $aTopicsRead;
    }

    /**
     * Получить список просмотром/чтения топиков по списку айдишников, но используя единый кеш
     *
     * @param array $aTopicId    Список ID топиков
     * @param int   $sUserId     ID пользователя
     *
     * @return array
     */
    public function GetTopicsReadByArraySolid($aTopicId, $sUserId) {
        if (!is_array($aTopicId)) {
            $aTopicId = array($aTopicId);
        }
        $aTopicId = array_unique($aTopicId);
        $aTopicsRead = array();
        $s = join(',', $aTopicId);
        if (false === ($data = $this->Cache_Get("topic_read_{$sUserId}_id_{$s}"))) {
            $data = $this->oMapperTopic->GetTopicsReadByArray($aTopicId, $sUserId);
            foreach ($data as $oTopicRead) {
                $aTopicsRead[$oTopicRead->getTopicId()] = $oTopicRead;
            }
            $this->Cache_Set(
                $aTopicsRead, "topic_read_{$sUserId}_id_{$s}", array("topic_read_user_{$sUserId}"), 60 * 60 * 24 * 1
            );
            return $aTopicsRead;
        }
        return $data;
    }

    /**
     * Возвращает список полей по списку id топиков
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
            $data = $this->oMapperTopic->GetTopicValuesByArrayId($aTopicId);
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
            $data = $this->oMapperTopic->GetFieldsByArrayId($aTypesId);
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
     * @param int $sTopicId    ID топика
     * @param int $sUserId     ID пользователя
     *
     * @return ModuleTopic_EntityTopicQuestionVote|null
     */
    public function GetTopicQuestionVote($sTopicId, $sUserId) {
        $data = $this->GetTopicsQuestionVoteByArray($sTopicId, $sUserId);
        if (isset($data[$sTopicId])) {
            return $data[$sTopicId];
        }
        return null;
    }

    /**
     * Получить список голосований в топике-опросе по списку айдишников
     *
     * @param array $aTopicId    Список ID топиков
     * @param int   $sUserId     ID пользователя
     *
     * @return array
     */
    public function GetTopicsQuestionVoteByArray($aTopicId, $sUserId) {
        if (!$aTopicId) {
            return array();
        }
        if (Config::Get('sys.cache.solid')) {
            return $this->GetTopicsQuestionVoteByArraySolid($aTopicId, $sUserId);
        }
        if (!is_array($aTopicId)) {
            $aTopicId = array($aTopicId);
        }
        $aTopicId = array_unique($aTopicId);
        $aTopicsQuestionVote = array();
        $aTopicIdNotNeedQuery = array();
        /**
         * Делаем мульти-запрос к кешу
         */
        $aCacheKeys = func_build_cache_keys($aTopicId, 'topic_question_vote_', '_' . $sUserId);
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
        $aTopicIdNeedQuery = array_diff($aTopicId, array_keys($aTopicsQuestionVote));
        $aTopicIdNeedQuery = array_diff($aTopicIdNeedQuery, $aTopicIdNotNeedQuery);
        $aTopicIdNeedStore = $aTopicIdNeedQuery;
        if ($data = $this->oMapperTopic->GetTopicsQuestionVoteByArray($aTopicIdNeedQuery, $sUserId)) {
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
            $this->Cache_Set(null, "topic_question_vote_{$sId}_{$sUserId}", array(), 60 * 60 * 24 * 4);
        }
        /**
         * Сортируем результат согласно входящему массиву
         */
        $aTopicsQuestionVote = func_array_sort_by_keys($aTopicsQuestionVote, $aTopicId);
        return $aTopicsQuestionVote;
    }

    /**
     * Получить список голосований в топике-опросе по списку айдишников, но используя единый кеш
     *
     * @param array $aTopicId    Список ID топиков
     * @param int   $sUserId     ID пользователя
     *
     * @return array
     */
    public function GetTopicsQuestionVoteByArraySolid($aTopicId, $sUserId) {
        if (!is_array($aTopicId)) {
            $aTopicId = array($aTopicId);
        }
        $aTopicId = array_unique($aTopicId);
        $aTopicsQuestionVote = array();
        $s = join(',', $aTopicId);
        if (false === ($data = $this->Cache_Get("topic_question_vote_{$sUserId}_id_{$s}"))) {
            $data = $this->oMapperTopic->GetTopicsQuestionVoteByArray($aTopicId, $sUserId);
            foreach ($data as $oTopicVote) {
                $aTopicsQuestionVote[$oTopicVote->getTopicId()] = $oTopicVote;
            }
            $this->Cache_Set(
                $aTopicsQuestionVote, "topic_question_vote_{$sUserId}_id_{$s}",
                array("topic_question_vote_user_{$sUserId}"), 60 * 60 * 24 * 1
            );
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
        $this->Cache_Clean(
            Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            array("topic_question_vote_user_{$oTopicQuestionVote->getVoterId()}")
        );
        return $this->oMapperTopic->AddTopicQuestionVote($oTopicQuestionVote);
    }

    /**
     * Получает топик по уникальному хешу(текст топика)
     *
     * @param int    $sUserId
     * @param string $sHash
     *
     * @return ModuleTopic_EntityTopic|null
     */
    public function GetTopicUnique($sUserId, $sHash) {
        $sId = $this->oMapperTopic->GetTopicUnique($sUserId, $sHash);
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
     * @param  int      $sUserId        ID пользователя
     * @param  int      $iTimeLimit     Число секунд
     * @param  int      $iCountLimit    Количество
     * @param  array    $aAllowData     Список типов данных для подгрузки в топики
     *
     * @return array
     */
    public function GetLastTopicsByUserId($sUserId, $iTimeLimit, $iCountLimit = 1, $aAllowData = array()) {
        $aFilter = array(
            'topic_publish' => 1,
            'user_id'       => $sUserId,
            'topic_new'     => date("Y-m-d H:i:s", time() - $iTimeLimit),
        );
        $aTopics = $this->GetTopicsByFilter($aFilter, 1, $iCountLimit, $aAllowData);

        return $aTopics;
    }

    /**
     * Перемещает топики в другой блог
     *
     * @param  array  $aTopics    Список ID топиков
     * @param  int    $sBlogId    ID блога
     *
     * @return bool
     */
    public function MoveTopicsByArrayId($aTopics, $sBlogId) {
        $this->Cache_Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("topic_update", "topic_new_blog_{$sBlogId}"));
        if ($res = $this->oMapperTopic->MoveTopicsByArrayId($aTopics, $sBlogId)) {
            // перемещаем теги
            $this->oMapperTopic->MoveTopicsTagsByArrayId($aTopics, $sBlogId);
            // меняем target parent у комментов
            $this->Comment_UpdateTargetParentByTargetId($sBlogId, 'topic', $aTopics);
            // меняем target parent у комментов в прямом эфире
            $this->Comment_UpdateTargetParentByTargetIdOnline($sBlogId, 'topic', $aTopics);
            return $res;
        }
        return false;
    }

    /**
     * Перемещает топики в другой блог
     *
     * @param  int $sBlogId       ID старого блога
     * @param  int $sBlogIdNew    ID нового блога
     *
     * @return bool
     */
    public function MoveTopics($sBlogId, $sBlogIdNew) {
        $this->Cache_Clean(
            Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            array("topic_update", "topic_new_blog_{$sBlogId}", "topic_new_blog_{$sBlogIdNew}")
        );
        if ($res = $this->oMapperTopic->MoveTopics($sBlogId, $sBlogIdNew)) {
            // перемещаем теги
            $this->oMapperTopic->MoveTopicsTags($sBlogId, $sBlogIdNew);
            // меняем target parent у комментов
            $this->Comment_MoveTargetParent($sBlogId, 'topic', $sBlogIdNew);
            // меняем target parent у комментов в прямом эфире
            $this->Comment_MoveTargetParentOnline($sBlogId, 'topic', $sBlogIdNew);
            return $res;
        }
        return false;
    }

    /**
     * Загрузка изображений при написании топика
     *
     * @param  array                 $aFile    Массив $_FILES
     * @param  ModuleUser_EntityUser $oUser    Объект пользователя
     *
     * @return string|bool
     */
    public function UploadTopicImageFile($aFile, $oUser) {
        if (!is_array($aFile) || !isset($aFile['tmp_name'])) {
            return false;
        }

        $sFileTmp = Config::Get('sys.cache.dir') . func_generator();
        if (!move_uploaded_file($aFile['tmp_name'], $sFileTmp)) {
            return false;
        }
        $sDirUpload = $this->Image_GetIdDir($oUser->getId());
        $aParams = $this->Image_BuildParams('topic');

        if ($sFileImage = $this->Image_Resize(
            $sFileTmp, $sDirUpload, func_generator(6), Config::Get('view.img_max_width'),
            Config::Get('view.img_max_height'), Config::Get('view.img_resize_width'), null, true, $aParams
        )
        ) {
            @unlink($sFileTmp);
            return $this->Image_GetWebPath($sFileImage);
        }
        @unlink($sFileTmp);
        return false;
    }

    /**
     * Загрузка изображений по переданному URL
     *
     * @param  string                $sUrl    URL изображения
     * @param  ModuleUser_EntityUser $oUser
     *
     * @return string|int
     */
    public function UploadTopicImageUrl($sUrl, $oUser) {
        /**
         * Проверяем, является ли файл изображением
         */
        if (!@getimagesize($sUrl)) {
            return ModuleImage::UPLOAD_IMAGE_ERROR_TYPE;
        }
        /**
         * Открываем файловый поток и считываем файл поблочно,
         * контролируя максимальный размер изображения
         */
        $oFile = fopen($sUrl, 'r');
        if (!$oFile) {
            return ModuleImage::UPLOAD_IMAGE_ERROR_READ;
        }

        $iMaxSizeKb = Config::Get('view.img_max_size_url');
        $iSizeKb = 0;
        $sContent = '';
        while (!feof($oFile) && $iSizeKb < $iMaxSizeKb) {
            $sContent .= fread($oFile, 1024 * 1);
            $iSizeKb++;
        }
        /**
         * Если конец файла не достигнут,
         * значит файл имеет недопустимый размер
         */
        if (!feof($oFile)) {
            return ModuleImage::UPLOAD_IMAGE_ERROR_SIZE;
        }
        fclose($oFile);
        /**
         * Создаем tmp-файл, для временного хранения изображения
         */
        $sFileTmp = Config::Get('sys.cache.dir') . func_generator();

        $fp = fopen($sFileTmp, 'w');
        fwrite($fp, $sContent);
        fclose($fp);

        $sDirSave = $this->Image_GetIdDir($oUser->getId());
        $aParams = $this->Image_BuildParams('topic');
        /**
         * Передаем изображение на обработку
         */
        if ($sFileImg = $this->Image_Resize(
            $sFileTmp, $sDirSave, func_generator(), Config::Get('view.img_max_width'),
            Config::Get('view.img_max_height'), Config::Get('view.img_resize_width'), null, true, $aParams
        )
        ) {
            @unlink($sFileTmp);
            return $this->Image_GetWebPath($sFileImg);
        }

        @unlink($sFileTmp);
        return ModuleImage::UPLOAD_IMAGE_ERROR;
    }

    /**
     * Возвращает список фотографий к топику-фотосет по списку id фоток
     *
     * @param array $aPhotoId    Список ID фото
     *
     * @return array
     */
    public function GetTopicPhotosByArrayId($aPhotoId) {
        if (!$aPhotoId) {
            return array();
        }
        if (!is_array($aPhotoId)) {
            $aPhotoId = array($aPhotoId);
        }
        $aPhotoId = array_unique($aPhotoId);
        $aPhotos = array();
        $s = join(',', $aPhotoId);
        if (false === ($data = $this->Cache_Get("photoset_photo_id_{$s}"))) {
            $data = $this->oMapperTopic->GetTopicPhotosByArrayId($aPhotoId);
            foreach ($data as $oPhoto) {
                $aPhotos[$oPhoto->getId()] = $oPhoto;
            }
            $this->Cache_Set($aPhotos, "photoset_photo_id_{$s}", array("photoset_photo_update"), 60 * 60 * 24 * 1);
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
    public function addTopicPhoto($oPhoto) {
        if ($sId = $this->oMapperTopic->addTopicPhoto($oPhoto)) {
            $oPhoto->setId($sId);
            $this->Cache_Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("photoset_photo_update"));
            return $oPhoto;
        }
        return false;
    }

    /**
     * Получить изображение из фотосета по его id
     *
     * @param int $sId    ID фото
     *
     * @return ModuleTopic_EntityTopicPhoto|null
     */
    public function getTopicPhotoById($sId) {
        $aPhotos = $this->GetTopicPhotosByArrayId($sId);
        if (isset($aPhotos[$sId])) {
            return $aPhotos[$sId];
        }
        return null;
    }

    /**
     * Получить список изображений из фотосета по id топика
     *
     * @param int      $iTopicId    ID топика
     * @param int|null $iFromId     ID с которого начинать выборку
     * @param int|null $iCount      Количество
     *
     * @return array
     */
    public function getPhotosByTopicId($iTopicId, $iFromId = null, $iCount = null) {
        return $this->oMapperTopic->getPhotosByTopicId($iTopicId, $iFromId, $iCount);
    }

    /**
     * Получить список изображений из фотосета по временному коду
     *
     * @param string $sTargetTmp    Временный ключ
     *
     * @return array
     */
    public function getPhotosByTargetTmp($sTargetTmp) {
        return $this->oMapperTopic->getPhotosByTargetTmp($sTargetTmp);
    }

    /**
     * Получить число изображений из фотосета по id топика
     *
     * @param int $iTopicId    ID топика
     *
     * @return int
     */
    public function getCountPhotosByTopicId($iTopicId) {
        return $this->oMapperTopic->getCountPhotosByTopicId($iTopicId);
    }

    /**
     * Получить число изображений из фотосета по id топика
     *
     * @param string $sTargetTmp    Временный ключ
     *
     * @return int
     */
    public function getCountPhotosByTargetTmp($sTargetTmp) {
        return $this->oMapperTopic->getCountPhotosByTargetTmp($sTargetTmp);
    }

    /**
     * Обновить данные по изображению
     *
     * @param ModuleTopic_EntityTopicPhoto $oPhoto Объект фото
     */
    public function updateTopicPhoto($oPhoto) {
        $this->Cache_Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("photoset_photo_update"));
        $this->oMapperTopic->updateTopicPhoto($oPhoto);
    }

    /**
     * Удалить изображение
     *
     * @param ModuleTopic_EntityTopicPhoto $oPhoto    Объект фото
     */
    public function deleteTopicPhoto($oPhoto) {
        $this->Cache_Clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array("photoset_photo_update"));
        $this->oMapperTopic->deleteTopicPhoto($oPhoto->getId());

        $this->Image_RemoveFile($this->Image_GetServerPath($oPhoto->getWebPath()));
        $aSizes = Config::Get('module.topic.photoset.size');
        // Удаляем все сгенерированные миниатюры основываясь на данных из конфига.
        foreach ($aSizes as $aSize) {
            $sSize = $aSize['w'];
            if ($aSize['crop']) {
                $sSize .= 'crop';
            }
            $this->Image_RemoveFile($this->Image_GetServerPath($oPhoto->getWebPath($sSize)));
        }
    }

    /**
     * Загрузить изображение
     *
     * @param array $aFile    Массив $_FILES
     *
     * @return string|bool
     */
    public function UploadTopicPhoto($aFile) {
        if (!is_array($aFile) || !isset($aFile['tmp_name'])) {
            return false;
        }

        $sFileName = func_generator(10);
        $sPath = Config::Get('path.uploads.images') . '/topic/' . date('Y/m/d') . '/';

        if (!is_dir(Config::Get('path.root.server') . $sPath)) {
            mkdir(Config::Get('path.root.server') . $sPath, 0755, true);
        }

        $sFileTmp = Config::Get('path.root.server') . $sPath . $sFileName;
        if (!move_uploaded_file($aFile['tmp_name'], $sFileTmp)) {
            return false;
        }


        $aParams = $this->Image_BuildParams('photoset');

        $oImage = $this->Image_CreateImageObject($sFileTmp);
        /**
         * Если объект изображения не создан,
         * возвращаем ошибку
         */
        if ($sError = $oImage->get_last_error()) {
            // Вывод сообщения об ошибки, произошедшей при создании объекта изображения
            $this->Message_AddError($sError, $this->Lang_Get('error'));
            @unlink($sFileTmp);
            return false;
        }
        /**
         * Превышает максимальные размеры из конфига
         */
        if (($oImage->get_image_params('width') > Config::Get('view.img_max_width'))
            || ($oImage->get_image_params('height') > Config::Get('view.img_max_height'))
        ) {
            $this->Message_AddError($this->Lang_Get('topic_photoset_error_size'), $this->Lang_Get('error'));
            @unlink($sFileTmp);
            return false;
        }
        /**
         * Добавляем к загруженному файлу расширение
         */
        $sFile = $sFileTmp . '.' . $oImage->get_image_params('format');
        rename($sFileTmp, $sFile);

        $aSizes = Config::Get('module.topic.photoset.size');
        foreach ($aSizes as $aSize) {
            // * Для каждого указанного в конфиге размера генерируем картинку
            $sNewFileName = $sFileName . '_' . $aSize['w'];
            $oImage = $this->Image_CreateImageObject($sFile);
            if ($aSize['crop']) {
                $this->Image_CropProportion($oImage, $aSize['w'], $aSize['h'], true);
                $sNewFileName .= 'crop';
            }
            $this->Image_Resize(
                $sFile, $sPath, $sNewFileName, Config::Get('view.img_max_width'), Config::Get('view.img_max_height'),
                $aSize['w'], $aSize['h'], true, $aParams, $oImage
            );
        }
        return $this->Image_GetWebPath($sFile);
    }

    /**
     * Пересчитывает счетчик избранных топиков
     *
     * @return bool
     */
    public function RecalculateFavourite() {
        return $this->oMapperTopic->RecalculateFavourite();
    }

    /**
     * Пересчитывает счетчики голосований
     *
     * @return bool
     */
    public function RecalculateVote() {
        return $this->oMapperTopic->RecalculateVote();
    }

    /**
     * Алиас для корректной работы ORM
     *
     * @param array $aTopocId    Список ID топиков
     *
     * @return array
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
    public function processTopicFields($oTopic,$sType='add'){

        $aValues=array();

        if($sType=='update'){
            /*
             * Получаем существующие значения
             */
            if($aData=$this->GetTopicValuesByArrayId(array($oTopic->getId()))){
                $aValues=$aData[$oTopic->getId()];
            }

            /*
             * Чистим существующие значения
             */
            $this->Topic_DeleteTopicValuesByTopicId($oTopic->getId());
        }

		if($oType=$this->Topic_getContentTypeByUrl($oTopic->getType())) {
            
			//получаем поля для данного типа
			if($aFields=$oType->getFields()){

				foreach($aFields as $oField){

					$sData=null;

					if(isset($_REQUEST['fields'][$oField->getFieldId()]) || isset($_FILES['fields_'.$oField->getFieldId()])){

						//текстовые поля
						if(in_array($oField->getFieldType(),array('input','textarea','select'))){
							$sData=$this->Text_Parser($_REQUEST['fields'][$oField->getFieldId()]);
						}
						//поле ссылки
						if($oField->getFieldType()=='link'){
							$sData=$_REQUEST['fields'][$oField->getFieldId()];
						}

						//поле даты
						if($oField->getFieldType()=='date') {
							if(isset($_REQUEST['fields'][$oField->getFieldId()])){

								if(func_check($_REQUEST['fields'][$oField->getFieldId()],'text',6,10) && substr_count($_REQUEST['fields'][$oField->getFieldId()],'.')==2) {
									list($d,$m,$y)=explode('.',$_REQUEST['fields'][$oField->getFieldId()]);
									if(@checkdate($m,$d,$y)) {
										$sData=$_REQUEST['fields'][$oField->getFieldId()];
									}
								}

							}

						}

						//поле с файлом
						if($oField->getFieldType()=='file'){
                            //если указано удаление файла
							if(getRequest('topic_delete_file_'.$oField->getFieldId())){
								if($oTopic->getFile($oField->getFieldId())){
									@unlink(Config::Get('path.root.server').$oTopic->getFile($oField->getFieldId())->getFileUrl());
									//$oTopic->setValueField($oField->getFieldId(),'');
                                    $sData=null;
								}
							} else {
                                //если удаление файла не указано, уже ранее залит файл^ и нового файла не загружалось
                                if($sType=='update' && isset($aValues[$oField->getFieldId()])){
                                    $sData=$aValues[$oField->getFieldId()]->getValueSource();
                                }
                            }

							if (isset($_FILES['fields_'.$oField->getFieldId()]) and is_uploaded_file($_FILES['fields_'.$oField->getFieldId()]['tmp_name'])) {

								if (filesize($_FILES['fields_'.$oField->getFieldId()]['tmp_name'])<=Config::Get('module.topic.max_filesize_limit')) {
									$aPathInfo=pathinfo($_FILES['fields_'.$oField->getFieldId()]['name']);

									if (in_array(strtolower($aPathInfo['extension']),Config::Get('module.topic.upload_mime_types'))) {
										$sFileTmp=$_FILES['fields_'.$oField->getFieldId()]['tmp_name'];
										$sDirSave=Config::Get('path.uploads.root').'/files/'.$this->User_GetUserCurrent()->getId().'/'.func_generator(16);
										mkdir(Config::Get('path.root.server').$sDirSave,0777,true);
										if(is_dir(Config::Get('path.root.server').$sDirSave)){

											$sFile=$sDirSave.'/'.func_generator(10).'.'.strtolower($aPathInfo['extension']);
											$sFileFullPath=Config::Get('path.root.server').$sFile;
											if (copy($sFileTmp,$sFileFullPath)) {
												//удаляем старый файл
												if($oTopic->getFile($oField->getFieldId())){
													@unlink(Config::Get('path.root.server').$oTopic->getFile($oField->getFieldId())->getFileUrl());
												}

												$aFileObj=array();
												$aFileObj['file_hash']=func_generator(32);
												$aFileObj['file_name']=$this->Text_Parser($_FILES['fields_'.$oField->getFieldId()]['name']);
												$aFileObj['file_url']=$sFile;
												$aFileObj['file_size']=$_FILES['fields_'.$oField->getFieldId()]['size'];
												$aFileObj['file_extension']=$aPathInfo['extension'];
												$aFileObj['file_downloads']=0;
												$sData=serialize($aFileObj);

												@unlink($sFileTmp);
											}
										}
									}
								}


							}
							@unlink($_FILES['fields_'.$oField->getFieldId()]['tmp_name']);
						}

                        $this->Hook_Run('content_field_proccess',array('sData'=>&$sData,'oField'=>$oField,'oTopic'=>$oTopic,'aValues'=>$aValues,'sType'=>&$sType));

						//Добавляем поле к топику.
						if($sData){

							$oValue=Engine::GetEntity('Topic_ContentValues');
							$oValue->setTargetId($oTopic->getId());
							$oValue->setTargetType('topic');
							$oValue->setFieldId($oField->getFieldId());
							$oValue->setFieldType($oField->getFieldType());
							$oValue->setValue($sData);
							$oValue->setValueSource(($oField->getFieldType()=='file')?$sData:$_REQUEST['fields'][$oField->getFieldId()]);

							$this->Topic_AddTopicValue($oValue);

						}

					}

				}
			}
		}
    }

}

// EOF