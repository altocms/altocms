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
 * Class ModuleTopic_EntityTopic Entity of topic/article
 *
 * @method int getTopicIndexIgnore()
 * @method int getFavourite()
 * @method string GetTopicUrl()
 * @method string GetTopicType()
 *
 * @method setFavourite($oParam)
 * @method setTopicIndexIgnore($bParam)
 * @method setPhotosetMainPhoto($oParam)
 * @method setTopicUrl($sParam)

 * @package modules.topic
 * @since   1.0
 */
class ModuleTopic_EntityTopic extends Entity {

    const INDEX_IGNORE_OFF = 0;
    const INDEX_IGNORE_ON = 1;
    const INDEX_IGNORE_LOCK = 2;

    /**
     * Массив для дополнительных параметров топика, которые сериализуются при его сохранении
     *
     * @var array
     */
    protected $aExtra = null;

    /*
     * Массив значений полей топика
     */
    protected $aValues = null;

    /**
     * Define of validation rule
     */
    public function Init() {

        parent::Init();
        $this->aValidateRules[] = array(
            'topic_title', 'string', 'max' => 200, 'min' => 2,
            'allowEmpty' => false,
            'label' => E::ModuleLang()->Get('topic_create_title'),
            'on' => array('topic'),
        );
        $this->aValidateRules[] = array(
            'question_title', 'string', 'max' => 200, 'min' => 2,
            'allowEmpty' => true,
            'label' => E::ModuleLang()->Get('topic_create_question_title'),
            'on' => array('topic'),
        );
        $this->aValidateRules[] = array(
            'topic_text_source', 'string', 'max' => Config::Get('module.topic.max_length'), 'min' => 2,
            'allowEmpty' => false,
            'label' => E::ModuleLang()->Get('topic_create_text'),
            'on' => array('topic'),
        );
        $this->aValidateRules[] = array(
            'topic_tags', 'tags', 'count' => 15,
            'allowEmpty' => Config::Get('module.topic.allow_empty_tags'),
            'label' => E::ModuleLang()->Get('topic_create_tags'),
            'on' => array('topic'),
        );
        $this->aValidateRules[] = array(
            'blog_id', 'blog_id',
            'on' => array('topic'),
        );
        $this->aValidateRules[] = array(
            'topic_text_source', 'topic_unique',
            'on' => array('topic'),
        );
        $this->aValidateRules[] = array(
            'topic_type', 'topic_type',
            'on' => array('topic'),
        );
        $this->aValidateRules[] = array(
            'link_url', 'url',
            'allowEmpty' => true,
            'label' => E::ModuleLang()->Get('topic_link_create_url'),
            'on' => array('topic'),
        );
    }

    /**
     * Типы ресурсов, загружаемые в профайле пользователя
     *
     * @return array
     */
    protected function _getDefaultMediaTypes() {

        return array('topic', 'photoset');
    }

    /**
     * Проверка типа топика
     *
     * @param string $sValue     Проверяемое значение
     * @param array  $aParams    Параметры
     *
     * @return bool|string
     */
    public function ValidateTopicType($sValue, $aParams) {

        if (E::ModuleTopic()->IsAllowTopicType($sValue)) {
            return true;
        }
        return E::ModuleLang()->Get('topic_create_type_error');
    }

    /**
     * Проверка топика на уникальность
     *
     * @param string $sValue     Проверяемое значение
     * @param array  $aParams    Параметры
     *
     * @return bool|string
     */
    public function ValidateTopicUnique($sValue, $aParams) {

        $this->setTextHash(md5($this->getType() . $sValue . $this->getTitle()));
        if ($oTopicEquivalent = E::ModuleTopic()->GetTopicUnique($this->getUserId(), $this->getTextHash())) {
            if ($iId = $this->getId() and $oTopicEquivalent->getId() == $iId) {
                return true;
            }
            return E::ModuleLang()->Get('topic_create_text_error_unique');
        }
        return true;
    }

    /**
     * Валидация ID блога
     *
     * @param string $sValue     Проверяемое значение
     * @param array  $aParams    Параметры
     *
     * @return bool|string
     */
    public function ValidateBlogId($sValue, $aParams) {

        if ($sValue == 0) {
            return true; // персональный блог
        }
        if (E::ModuleBlog()->GetBlogById((string)$sValue)) {
            return true;
        }
        return E::ModuleLang()->Get('topic_create_blog_error_unknown');
    }

    /**
     * @param int $iFieldId
     *
     * @return mixed
     */
    public function getField($iFieldId) {

        if (isset($this->aValues[$iFieldId])) {
            return $this->aValues[$iFieldId];
        }
        return null;
    }

    /**
     * Обрабатывает поле ссылки
     *
     * @param int $iFieldId
     * @param bool $bHtml
     *
     * @return null|string
     */
    public function getFieldLink($iFieldId, $bHtml = false) {

        if ($this->getField($iFieldId)) {
            if ($bHtml) {
                if (strpos($this->getField($iFieldId)->getValue(), 'http://') !== 0) {
                    return 'http://' . $this->getField($iFieldId)->getValue();
                }
            }
            return $this->getField($iFieldId)->getValue();
        }
        return null;
    }

    /**
     * @param $data
     */
    public function setTopicValues($data) {

        $this->aValues = $data;
    }

    /**
     * @return ModuleTopic_EntityContentType
     */
    public function getContentType() {

        $oContentType = $this->getProp('_content_type');
        if (is_null($oContentType)) {
            $oContentType = E::ModuleTopic()->GetContentType($this->getType());
            $this->setProp('_content_type', $oContentType);
        }
        return $oContentType;
    }

    /**
     * Возвращает ID топика
     *
     * @return int|null
     */
    public function getId() {

        return $this->getProp('topic_id');
    }

    /**
     * Возвращает ID блога
     *
     * @return int|null
     */
    public function getBlogId() {

        return $this->getProp('blog_id');
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
     * Возвращает тип топика
     *
     * @return string|null
     */
    public function getType() {

        return $this->getProp('topic_type');
    }

    /**
     * Возвращает заголовок топика
     *
     * @return string|null
     */
    public function getTitle() {

        return $this->getProp('topic_title');
    }

    /**
     * Возвращает текст топика
     *
     * @return string|null
     */
    public function getText() {

        return $this->getProp('topic_text');
    }

    /**
     * Возвращает короткий текст топика (до ката)
     *
     * @return string|null
     */
    public function getTextShort() {

        return $this->getProp('topic_text_short');
    }

    /**
     * Возвращает исходный текст топика, без примененя парсера тегов
     *
     * @return string|null
     */
    public function getTextSource() {

        return $this->getProp('topic_text_source');
    }

    /**
     * Возвращает сериализованную строку дополнительных данных топика
     *
     * @return string
     */
    public function getExtra() {

        $sResult = $this->getProp('topic_extra');
        return !is_null($sResult) ? $sResult : serialize('');
    }

    /**
     * Возвращает строку со списком тегов через запятую
     *
     * @return string|null
     */
    public function getTags() {

        return $this->getProp('topic_tags');
    }

    /**
     * Возвращает дату создания топика
     *
     * @return string|null
     */
    public function getDateAdd() {

        return $this->getProp('topic_date_add');
    }

    /**
     * Возвращает дату редактирования топика
     *
     * @return string|null
     */
    public function getDateEdit() {

        return $this->getProp('topic_date_edit');
    }

    public function getDateShow() {

        return $this->getProp('topic_date_show');
    }

    /**
     * Gets topic date publication
     *
     * @return null|string
     */
    public function getDate() {

        $sDate = $this->getProp('_date');
        if (is_null($sDate)) {
            $sDate = $this->getDateShow();
            if (is_null($sDate)) {
                $sDate = $this->getDateAdd();
            }
            $this->setProp('_date', $sDate);
        }
        return $sDate;
    }

    /**
     * Возвращает IP пользователя
     *
     * @return string|null
     */
    public function getUserIp() {

        return $this->getProp('topic_user_ip');
    }

    /**
     * Возвращает статус опубликованности топика
     *
     * @return int|null
     */
    public function getPublish() {

        return $this->getProp('topic_publish');
    }

    /**
     * Возвращает статус опубликованности черновика
     *
     * @return int|null
     */
    public function getPublishDraft() {

        return $this->getProp('topic_publish_draft');
    }

    /**
     * Возвращает статус публикации топика на главной странице
     *
     * @return int|null
     */
    public function getPublishIndex() {

        return $this->getProp('topic_publish_index');
    }

    /**
     * Возвращает рейтинг топика
     *
     * @return string
     */
    public function getRating() {

        return number_format(round($this->getProp('topic_rating'), 2), 0, '.', '');
    }

    /**
     * Возвращает число проголосовавших за топик
     *
     * @return int|null
     */
    public function getCountVote() {

        return $this->getProp('topic_count_vote');
    }

    /**
     * Возвращает число проголосовавших за топик положительно
     *
     * @return int|null
     */
    public function getCountVoteUp() {

        return $this->getProp('topic_count_vote_up');
    }

    /**
     * Возвращает число проголосовавших за топик отрицательно
     *
     * @return int|null
     */
    public function getCountVoteDown() {

        return $this->getProp('topic_count_vote_down');
    }

    /**
     * Возвращает число воздержавшихся при голосовании за топик
     *
     * @return int|null
     */
    public function getCountVoteAbstain() {

        return $this->getProp('topic_count_vote_abstain');
    }

    /**
     * Возвращает число прочтений топика
     *
     * @return int|null
     */
    public function getCountRead() {

        return $this->getProp('topic_count_read');
    }

    /**
     * Возвращает количество комментариев к топику
     *
     * @return int|null
     */
    public function getCountComment() {

        return $this->getProp('topic_count_comment');
    }

    /**
     * Возвращает текст ката
     *
     * @return string|null
     */
    public function getCutText() {

        return $this->getProp('topic_cut_text');
    }

    /**
     * Возвращает статус запрета комментировать топик
     *
     * @return int|null
     */
    public function getForbidComment() {

        return $this->getProp('topic_forbid_comment');
    }

    /**
     * Возвращает хеш топика для проверки топика на уникальность
     *
     * @return string|null
     */
    public function getTextHash() {

        return $this->getProp('topic_text_hash');
    }

    /**
     * Возвращает массив тегов
     *
     * @param bool $bTextOnly
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
                    'topic_tag_id'   => -$nI,
                    'topic_id'       => $this->getId(),
                    'user_id'        => $this->getUserId(),
                    'blog_id'        => $this->getBlogId(),
                    'topic_tag_text' => $sText,
                );
            }
            return E::GetEntityRows('Topic_TopicTag', $aData);
        }
        return array();
    }

    /**
     * Возвращает количество новых комментариев в топике для текущего пользователя
     *
     * @return int|null
     */
    public function getCountCommentNew() {

        return $this->getProp('count_comment_new');
    }

    /**
     * Возвращает дату прочтения топика для текущего пользователя
     *
     * @return string|null
     */
    public function getDateRead() {

        return $this->getProp('date_read');
    }

    /**
     * Возвращает объект пользователя, автора топик
     *
     * @return ModuleUser_EntityUser|null
     */
    public function getUser() {

        if (!$this->getProp('user')) {
            $this->setProp('user', E::ModuleUser()->GetUserById($this->getUserId()));
        }
        return $this->getProp('user');
    }

    /**
     * Returns blog object of the topic
     *
     * @return ModuleBlog_EntityBlog|null
     */
    public function getBlog() {

        $oBlog = $this->getProp('blog');
        if (!$oBlog) {
            $iBlogId = $this->getBlogId();
            if ($iBlogId) {
                $oBlog = E::ModuleBlog()->GetBlogById($iBlogId);
            } elseif ($iBlogId === 0 || $iBlogId === '0') {
                $oUser = $this->getUser();
                $oBlog = $oUser->getBlog();
            }
            $this->setProp('blog', $oBlog);
        }
        return $oBlog;
    }

    /**
     * Возвращает полный URL до топика
     *
     * @param   string|null $sUrlMask - еcли передан параметр, то формирует URL по этой маске
     * @param   bool        $bFullUrl - возвращать полный путь (или относительный, если false)
     *
     * @return  string
     */
    public function getLink($sUrlMask = null, $bFullUrl = true) {

        $sKey = '_url-' . ($sUrlMask ? $sUrlMask : '') . ($bFullUrl ? '-1' : '-0');
        $sUrl = $this->getProp($sKey);
        if (!is_null($sUrl)) {
            return $sUrl;
        }

        if (!$sUrlMask) {
            $sUrlMask = R::GetTopicUrlMask();
        }
        if (!$sUrlMask) {
            // формирование URL по умолчанию в LS-стиле
            if ($this->getBlog()->getType() == 'personal') {
                $sUrl = R::GetPath('blog') . $this->getId() . '.html';
            } else {
                $sUrl = $this->getBlog()->getLink() . $this->getId() . '.html';
            }
            $this->setProp($sKey, $sUrl);
            return $sUrl;
        }
        // ЧПУ по маске
        $sCreateDate = strtotime($this->GetDateAdd());
        $aReplace = array(
            '%year%'       => date('Y', $sCreateDate),
            '%month%'      => date('m', $sCreateDate),
            '%day%'        => date('d', $sCreateDate),
            '%hour%'       => date('H', $sCreateDate),
            '%minute%'     => date('i', $sCreateDate),
            '%second%'     => date('s', $sCreateDate),
            '%topic_type%' => $this->GetTopicType(),
            '%topic_id%'   => $this->GetId(),
            '%topic_url%'  => $this->GetTopicUrl(),
            '%login%'      => $this->GetUser()->GetLogin(),
            '%blog_url%'   => $this->GetBlog()->GetUrl(),
        );
        if (substr($sUrlMask, -1) == '%') {
            $sUrlMask .= '/';
        }

        $sUrl = strtr($sUrlMask, $aReplace);
        if ($bFullUrl) {
            if ($sUrl[0] == '/') {
                $sUrl = F::File_RootUrl() . substr($sUrl, 1);
            } else {
                $sUrl = F::File_RootUrl() . $sUrl;
            }
        }
        $this->setProp($sKey, $sUrl);

        return $sUrl;
    }

    /**
     * Alias for getLink()
     *
     * @param string $sUrlMask
     * @param bool   $bFullUrl
     *
     * @return mixed
     */
    public function getUrl($sUrlMask = null, $bFullUrl = true) {

        return $this->getLink($sUrlMask, $bFullUrl);
    }

    /**
     * Транслитерация заголовка топика
     *
     * @return string
     */
    public function GetTitleTranslit() {

        return F::TranslitUrl($this->getTitle());
    }

    /**
     * @return string
     */
    public function MakeTopicUrl() {

        $sUrl = $this->GetTitleTranslit();
        if (preg_match('/^\d+$/', $sUrl)) {
            $sUrl = 't' . $sUrl;
        }
        return $sUrl;
    }

    /**
     * Возвращает короткий постоянный URL
     *
     * @return string
     */
    public function getUrlShort() {

        return R::GetPath('t') . $this->getId() . '/';
    }

    /**
     * Возвращает полный URL до страницы редактировани топика
     *
     * @return string
     */
    public function getUrlEdit() {

        return R::GetPath('content') . 'edit/' . $this->getId() . '/';
    }

    /**
     * Возвращает объект голосования за топик текущим пользователем
     *
     * @return ModuleVote_EntityVote|null
     */
    public function getVote() {

        return $this->getProp('vote');
    }

    /**
     * Возвращает статус голосовал ли пользователь в топике-опросе
     *
     * @return bool|null
     */
    public function getUserQuestionIsVote() {

        return $this->getProp('user_question_is_vote');
    }

    /**
     * Проверяет находится ли данный топик в избранном у текущего пользователя
     *
     * @return bool
     */
    public function getIsFavourite() {

        if ($this->getFavourite()) {
            return true;
        }
        return false;
    }

    /**
     * Проверяет разрешение на удаление топика у текущего пользователя
     *
     * @return bool
     */
    public function getIsAllowDelete() {

        if ($oUser = E::ModuleUser()->GetUserCurrent()) {
            return E::ModuleACL()->IsAllowDeleteTopic($this, $oUser);
        }
        return false;
    }

    /**
     * Проверяет разрешение на редактирование топика у текущего пользователя
     *
     * @return bool
     */
    public function getIsAllowEdit() {

        if ($oUser = E::ModuleUser()->GetUserCurrent()) {
            return E::ModuleACL()->IsAllowEditTopic($this, $oUser);
        }
        return false;
    }

    /**
     * Проверяет разрешение на какое-либо действие для топика у текущего пользователя
     *
     * @return bool
     */
    public function getIsAllowAction() {

        if (E::ModuleUser()->GetUserCurrent()) {
            return $this->getIsAllowEdit() || $this->getIsAllowDelete();
        }
        return false;
    }

    /**
     * Возвращает количество добавивших топик в избранное
     *
     * @return int|null
     */
    public function getCountFavourite() {

        return $this->getProp('topic_count_favourite');
    }

    /**
     * Возвращает объект подписки на новые комментарии к топику
     *
     * @return ModuleSubscribe_EntitySubscribe|null
     */
    public function getSubscribeNewComment() {

        if (!($oUserCurrent = E::ModuleUser()->GetUserCurrent())) {
            return null;
        }
        return E::ModuleSubscribe()->GetSubscribeByTargetAndMail(
            'topic_new_comment', $this->getId(), $oUserCurrent->getMail()
        );
    }

    /**
     * Возвращает объект трекинга на новые комментарии к топику
     *
     * @return ModuleSubscribe_EntitySubscribe|null
     */
    public function getTrackNewComment() {

        if (!($oUserCurrent = E::ModuleUser()->GetUserCurrent())) {
            return null;
        }
        return E::ModuleSubscribe()->GetTrackByTargetAndUser('topic_new_comment', $this->getId(), $oUserCurrent->getId());
    }

    /**
     * Возвращает объект файла, привязанного к топику
     *
     * @param int $nId
     *
     * @return ModuleTopic_EntityTopicFile|null
     */
    public function getFieldFile($nId) {

        if ($this->getField($nId)) {
            return E::GetEntity(
                'ModuleTopic_EntityTopicFile', unserialize($this->getField($nId)->getValueSource())
            );
        }
        return null;
    }

    /**
     * @return string
     */
    public function getDraftUrl() {

        return $this->GetUrl() . '?draft=' . $this->GetUserId() . ':' . $this->getTextHash();
    }

    /**
     * Строит список ресурсов, связанных с топиком
     */
    public function BuildMresourcesList() {

        $aResources = array();
        $aPhotos = $this->getPhotosetPhotos();
        if ($aPhotos) {
            foreach($aPhotos as $oMresource) {
                $aResources[] = $oMresource;
            }
        }
        $aTextLinks = $this->GetTextLinks();
        if ($aTextLinks) {
            foreach($aTextLinks as $aLink) {
                /** @var ModuleMresource_EntityMresourceRel $oMresource */
                $oMresource = E::GetEntity('Mresource_MresourceRel');
                $oMresource->setUrl(E::ModuleMresource()->NormalizeUrl($aLink['link']));
                $oMresource->setType($aLink['type']);
                $oMresource->setUserId($this->getUserId());
                $aResources[] = $oMresource;
                // if image is derived from another image then add original mresource
                if ($oMresource->isDerivedImage()) {
                    // get original path and make one more mresorce
                    $sOriginal = $oMresource->GetOriginalPathUrl();

                    $oMresource = E::GetEntity('Mresource_MresourceRel');
                    $oMresource->setUrl(E::ModuleMresource()->NormalizeUrl($sOriginal));
                    $oMresource->setType($aLink['type']);
                    $oMresource->setUserId($this->getUserId());
                    $aResources[] = $oMresource;
                }
            }
        }
        return E::ModuleMresource()->BuildMresourceHashList($aResources);
    }

    /**
     * @param array|string $xData
     */
    public function setTextLinks($xData) {

        if (!is_array($xData)) {
            $xData = array((string)$xData);
        }
        $this->setExtraValue('intext_links', $xData);
    }

    /**
     * @return array
     */
    public function getTextLinks() {

        return (array)$this->getExtraValue('intext_links');
    }

    /* ****************************************************************************************************************
     * методы расширения типов топика
     * ****************************************************************************************************************
     */

    /**
     * Извлекает сериализованные данные топика
     */
    protected function extractExtra() {

        if (is_null($this->aExtra)) {
            $this->aExtra = @unserialize($this->getExtra());
        }
    }

    /**
     * Устанавливает значение нужного параметра
     *
     * @param string $sName    Название параметра/данных
     * @param mixed  $data     Данные
     */
    protected function setExtraValue($sName, $data) {

        $this->extractExtra();
        $this->aExtra[$sName] = $data;
        $this->setExtra($this->aExtra);
    }

    /**
     * Извлекает значение параметра
     *
     * @param string $sName    Название параметра
     *
     * @return null|mixed
     */
    protected function getExtraValue($sName) {

        $this->extractExtra();
        if (isset($this->aExtra[$sName])) {
            return $this->aExtra[$sName];
        }
        return null;
    }

    /* *** ******************** *** */
    /* *** SOURCE LINK OF TOPIC *** */

    /**
     * Returns URL of topic's source link
     *
     * @param bool $bShort    Shorten URL
     *
     * @return null|string
     */
    public function getSourceLink($bShort = false) {

        if ($sUrl = $this->getExtraValue('url')) {
            if ($bShort) {
                $sUrl = htmlspecialchars($sUrl);
                if (preg_match('/^https?:\/\/(.*)$/i', $sUrl, $aMatch)) {
                    $sUrl = $aMatch[1];
                }
                $sUrlShort = substr($sUrl, 0, 30);
                if (strlen($sUrlShort) != strlen($sUrl)) {
                    return $sUrlShort . '...';
                }
                return $sUrl;
            } else {
                if (!preg_match('/^https?:\/\/(.*)$/i', $sUrl, $aMatch)) {
                    $sUrl = 'http://' . $sUrl;
                }
            }
            return $sUrl;
        }
        return null;
    }

    /**
     * Set URL of topic's source link
     *
     * @param string $data
     */
    public function setSourceLink($data = null) {

        if($data && $data !== '')
            $this->setExtraValue('url', strip_tags($data));
    }

    /**
     * Возвращает количество переходов по ссылке в топике-ссылке
     *
     * @return int
     */
    public function getSourceLinkCountJump() {

        return (int)$this->getExtraValue('count_jump');
    }

    /**
     * Устанавливает количество переходов по ссылке в топике-ссылке
     *
     * @param string $data
     */
    public function setSourceLinkCountJump($data) {

        $this->setExtraValue('count_jump', $data);
    }

    /* *** ********************** *** */
    /* *** QUESTIONNAIRE OF TOPIC *** */

    /**
     * Устанавливает вопрос
     *
     * @param string $data
     */
    public function setQuestionTitle($data) {

        $this->setExtraValue('question_title', $data);
    }

    /**
     * Возвращает вопрос, если вопрос не указан - заголовок топика
     *
     * @return int|null
     */
    public function getQuestionTitle() {

        if ($this->getExtraValue('question_title')) {
            return $this->getExtraValue('question_title');
        }
        return $this->getTitle();
    }

    /**
     * Добавляет вариант ответа в опросе топика
     *
     * @param string $data
     */
    public function addQuestionAnswer($data) {

        $this->extractExtra();
        $this->aExtra['answers'][] = array('text' => $data, 'count' => 0);
        $this->setExtra($this->aExtra);
    }

    /**
     * Очищает варианты ответа в опросе топика
     */
    public function clearQuestionAnswer() {

        $this->setExtraValue('answers', array());
    }

    /**
     * Возвращает варианты ответа в опросе топика
     *
     * @param bool $bSortVote
     *
     * @return array|null
     */
    public function getQuestionAnswers($bSortVote = false) {

        $aAnswers = $this->getExtraValue('answers');
        if ($aAnswers && $bSortVote) {
            uasort(
                $aAnswers, create_function(
                    '$a,$b',
                    "if (\$a['count'] == \$b['count']) { return 0; } return (\$a['count'] < \$b['count']) ? 1 : -1;"
                )
            );
        }
        return $aAnswers ? $aAnswers : array();
    }

    /**
     * Увеличивает количество ответов на данный вариант в опросе топика
     *
     * @param int $sIdAnswer  ID варианта ответа
     */
    public function increaseQuestionAnswerVote($sIdAnswer) {

        if ($aAnswers = $this->getQuestionAnswers()) {
            if (isset($aAnswers[$sIdAnswer])) {
                $aAnswers[$sIdAnswer]['count']++;
                $this->aExtra['answers'] = $aAnswers;
                $this->setExtra($this->aExtra);
            }
        }
    }

    /**
     * Возвращает максимально количество ответов на вариант в опросе топика
     *
     * @return int
     */
    public function getQuestionAnswerMax() {

        $aAnswers = $this->getQuestionAnswers();
        $iMax = 0;
        foreach ($aAnswers as $aAns) {
            if ($aAns['count'] > $iMax) {
                $iMax = $aAns['count'];
            }
        }
        return $iMax;
    }

    /**
     * Возвращает в процентах количество проголосовавших за конкретный вариант
     *
     * @param int $sIdAnswer ID варианта
     *
     * @return int|string
     */
    public function getQuestionAnswerPercent($sIdAnswer) {

        if ($aAnswers = $this->getQuestionAnswers()) {
            if (isset($aAnswers[$sIdAnswer])) {
                $iCountAll = $this->getQuestionCountVote() - $this->getQuestionCountVoteAbstain();
                if (!$iCountAll == 0) {
                    return number_format(round($aAnswers[$sIdAnswer]['count'] * 100 / $iCountAll, 1), 1, '.', '');
                }
            }
        }
        return 0;
    }

    /**
     * Возвращает общее число принявших участие в опросе в опросе топика
     *
     * @return int
     */
    public function getQuestionCountVote() {

        return (int)$this->getExtraValue('count_vote');
    }

    /**
     * Устанавливает общее число принявших участие в опросе в опросе топика
     *
     * @param int $data
     */
    public function setQuestionCountVote($data) {

        $this->setExtraValue('count_vote', $data);
    }

    /**
     * Возвращает число воздержавшихся от участия в опросе
     *
     * @return int
     */
    public function getQuestionCountVoteAbstain() {

        return (int)$this->getExtraValue('count_vote_abstain');
    }

    /**
     * Устанавливает число воздержавшихся от участия в опросе
     *
     * @param int $data
     *
     * @return mixed
     */
    public function setQuestionCountVoteAbstain($data) {

        $this->setExtraValue('count_vote_abstain', $data);
    }

    /* *** ********************** *** */
    /* *** PHOTOSET OF TOPIC      *** */

    /**
     * Возвращает фотографии из фотосета топика
     *
     * @param int|null $iFromId    ID с которого начинать  выборку
     * @param int|null $iCount     Количество
     *
     * @return ModuleTopic_EntityTopicPhoto[]
     */
    public function getPhotosetPhotos($iFromId = null, $iCount = null) {

        $iPhotosCount = $this->getPhotosetCount();
        if (!$iPhotosCount) {
            return array();
        }

        $aPhotos = E::ModuleMresource()->GetMresourcesRelByTarget('photoset', $this->getId());
        $aResult = array();
        if ($aPhotos && ($iFromId || $iCount)) {
            $iCntSet = -1;
            // Сравнение должно идти по ид. ресурса, а не по ид.
            // картинки
            foreach($aPhotos as $oPhoto) {
                if ($oPhoto->getMresourceId() >= $iFromId) {
                    if (++$iCntSet >= $iCount) {
                        break;
                    }
                    $aResult[$oPhoto->getMresourceId()] = $oPhoto;
                }
            }
        } else {
            $aResult = $aPhotos;
        }
        return $aResult;
    }

    /**
     * Возвращает количество фотографий в фотосете топика
     *
     * @return int
     */
    public function getPhotosetCount() {

        return (int)$this->getExtraValue('count_photo');
    }

    /**
     * Return main photo of photoset
     *
     * @return ModuleMresource_EntityMresourceRel|null
     */
    public function getPhotosetMainPhoto() {

        // Топика ещё нет, вернём дефолтное значение (null)
        if (!$this->getId()) {
            return NULL;
        }

        $sPropKey = '_photoset_cover';
        $oPhotosetCover = $this->getProp($sPropKey);
        if ($oPhotosetCover === null) {
            $aImages = $this->getLoadedImages();
            foreach($aImages as $oImage) {
                if ($oImage->getType() & ModuleMresource::TYPE_PHOTO_PRIMARY) {
                    $oPhotosetCover = $oImage;
                    break;
                }
            }
            if ($oPhotosetCover) {
                $this->setProp($sPropKey, $oPhotosetCover);
            } else {
                $this->setProp($sPropKey, false);
            }
        }
        return $oPhotosetCover ? $oPhotosetCover : null;
    }

    /**
     * @param null|string|int $xSize
     *
     * @return null|string
     */
    public function getPhotosetMainPhotoLink($xSize = null) {

        $oImage = $this->getPhotosetMainPhoto();
        if ($oImage) {
            if ($xSize) {
                return E::ModuleUploader()->ResizeTargetImage($oImage->getImageUrl(), $xSize);
            }
            return $oImage->getImageUrl();
        }
        return null;
    }

    /**
     * Old style compatibility
     *
     * @param null $xSize
     *
     * @return null|string
     */
    public function getPhotosetMainPhotoUrl($xSize = null) {

        return $this->getPhotosetMainPhotoLink($xSize);
    }

    /**
     * Returns ID of main photo in photoset
     *
     * @return int|null
     */
    public function getPhotosetMainPhotoId() {

        $oImage = $this->getPhotosetMainPhoto();
        if ($oImage) {
            return $oImage->getMresourceId();
        }

        return $this->getExtraValue('main_photo_id');
    }

    /**
     * Возвращает массив ID фотографий в фотосете топика
     *
     * @return array|null
     */
    public function getPhotosId() {

        return $this->getExtraValue('photos_id');
    }

    /**
     * Устанавливает ID главной фото в топике-фотосете
     *
     * @param int $iPhotoId
     */
    public function setPhotosetMainPhotoId($iPhotoId) {

        $this->setExtraValue('main_photo_id', $iPhotoId);
        $this->setProp('_photoset_photos', null);
    }

    /**
     * Устанавливает количество фотографий в топике-фотосете
     *
     * @param int $iCount
     */
    public function setPhotosetCount($iCount) {

        $this->setExtraValue('count_photo', $iCount);
        $this->setProp('_photoset_photos', null);
    }

    /**
     * @param $aPhotosId
     */
    public function setPhotosId($aPhotosId) {

        $this->setExtraValue('photos_id', $aPhotosId);
        $this->setProp('_photoset_photos', null);
    }

    /* *** ********************** *** */
    /* *** OTHERS                 *** */

    /**
     * Флаг игнорирования индексации установлен вручную
     *
     * @return bool
     */
    public function getIndexIgnoreLock() {

        return intval($this->getTopicIndexIgnore()) == self::INDEX_IGNORE_LOCK;
    }

    /**
     * @param string $sMode
     *
     * @return string
     */
    public function getTopicTypeTemplate($sMode) {

        $oContentType = $this->getContentType();
        return $oContentType->getTemplate($sMode);
    }

    /**
     * If the topic is published
     *
     * @return bool
     */
    public function isPublished() {

        return (bool)$this->getPublish();
    }

    public function isVoteInfoShow() {

        $bResult = $this->getVote() || E::UserId()==$this->getUserId() || (strtotime($this->getDateAdd())<time()-Config::Get('acl.vote.topic.limit_time'));
        return $bResult;
    }

    public function isShowPhotoset() {
        /** @var ModuleTopic_EntityContentType $oContentType */
        $oContentType = $this->getContentType();
        return $this->getShowPhotoset() && $oContentType && $oContentType->isAllow('photoset') && $this->getPhotosetCount();
    }

    public function isShowPoll() {
        /** @var ModuleTopic_EntityContentType $oContentType */
        $oContentType = $this->getContentType();
        return $oContentType && $oContentType->isAllow('poll') && $this->getQuestionAnswers();
    }

    public function isShowSourceLink() {
        /** @var ModuleTopic_EntityContentType $oContentType */
        $oContentType = $this->getContentType();
        return $oContentType && $oContentType->isAllow('link') && $this->getSourceLink();
    }

    public function getShowPhotoset() {
        // По умолчанию включаем фотосет. поскольку при переносе топиков
        // и переходе со старых версий в экстре такой записи нет
        return ($this->getExtraValue('show_photoset')===null) ? TRUE : (bool)$this->getExtraValue('show_photoset');
    }

    public function getSingleImage($nId, $sSize) {

        if ($this->getField($nId)) {

            return E::ModuleUploader()->GetTargetImageUrl(
                $this->getField($nId)->getFieldType() . '-' . $nId,
                $this->getField($nId)->getTargetId(),
                $sSize);

        }
        return null;
    }

    //***************************************************************************************************************

    /**
     * Устанваливает ID топика
     *
     * @param int $data
     */
    public function setId($data) {

        $this->setProp('topic_id', $data);
    }

    /**
     * Устанавливает ID блога
     *
     * @param int $data
     */
    public function setBlogId($data) {

        if (is_numeric($data)) {
            $data = intval($data);
        } else {
            $data = null;
        }
        $this->setProp('blog_id', $data);
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
     * Устанавливает тип топика
     *
     * @param string $data
     */
    public function setType($data) {

        $this->setProp('topic_type', $data);
    }

    /**
     * Устанавливает заголовок топика
     *
     * @param string $data
     */
    public function setTitle($data) {

        $this->setProp('topic_title', $data);
    }

    /**
     * Устанавливает текст топика
     *
     * @param string $data
     */
    public function setText($data) {

        $this->setProp('topic_text', $data);
    }

    /**
     * Устанавливает сериализованную строчку дополнительных данных
     *
     * @param string $data
     */
    public function setExtra($data) {

        $this->setProp('topic_extra', serialize($data));
    }

    /**
     * Устанавливает короткий текст топика до ката
     *
     * @param string $data
     */
    public function setTextShort($data) {

        $this->setProp('topic_text_short', $data);
    }

    /**
     * Устаналивает исходный текст топика
     *
     * @param string $data
     */
    public function setTextSource($data) {

        $this->setProp('topic_text_source', $data);
    }

    /**
     * Устанавливает список тегов в виде строки
     *
     * @param string $data
     */
    public function setTags($data) {

        $this->setProp('topic_tags', $data);
    }

    /**
     * Устанавливает дату создания топика
     *
     * @param string $data
     */
    public function setDateAdd($data) {

        $this->setProp('topic_date_add', $data);
    }

    /**
     * Устанавливает дату редактирования топика
     *
     * @param string $data
     */
    public function setDateEdit($data) {

        $this->setProp('topic_date_edit', $data);
    }

    public function setDateShow($data) {

        $this->setProp('topic_date_show', $data);
    }

    /**
     * Устанавливает IP пользователя
     *
     * @param string $data
     */
    public function setUserIp($data) {

        $this->setProp('topic_user_ip', $data);
    }

    /**
     * Устанавливает флаг публикации топика
     *
     * @param string $data
     */
    public function setPublish($data) {

        $this->setProp('topic_publish', $data);
    }

    /**
     * Устанавливает флаг публикации черновика
     *
     * @param string $data
     */
    public function setPublishDraft($data) {

        $this->setProp('topic_publish_draft', $data);
    }

    /**
     * Устанавливает флаг публикации на главной странице
     *
     * @param string $data
     */
    public function setPublishIndex($data) {

        $this->setProp('topic_publish_index', $data);
    }

    /**
     * Устанавливает рейтинг топика
     *
     * @param string $data
     */
    public function setRating($data) {

        $this->setProp('topic_rating', $data);
    }

    /**
     * Устанавливает количество проголосовавших
     *
     * @param int $data
     */
    public function setCountVote($data) {

        $this->setProp('topic_count_vote', $data);
    }

    /**
     * Устанавливает количество проголосовавших в плюс
     *
     * @param int $data
     */
    public function setCountVoteUp($data) {

        $this->setProp('topic_count_vote_up', $data);
    }

    /**
     * Устанавливает количество проголосовавших в минус
     *
     * @param int $data
     */
    public function setCountVoteDown($data) {

        $this->setProp('topic_count_vote_down', $data);
    }

    /**
     * Устанавливает число воздержавшихся
     *
     * @param int $data
     */
    public function setCountVoteAbstain($data) {

        $this->setProp('topic_count_vote_abstain', $data);
    }

    /**
     * Устанавливает число прочтения топика
     *
     * @param int $data
     */
    public function setCountRead($data) {

        $this->setProp('topic_count_read', $data);
    }

    /**
     * Устанавливает количество комментариев
     *
     * @param int $data
     */
    public function setCountComment($data) {

        $this->setProp('topic_count_comment', $data);
    }

    /**
     * Устанавливает текст ката
     *
     * @param string $data
     */
    public function setCutText($data) {

        $this->setProp('topic_cut_text', $data);
    }

    /**
     * Устанавливает флаг запрета коментирования топика
     *
     * @param int $data
     */
    public function setForbidComment($data) {

        $this->setProp('topic_forbid_comment', $data ? 1 : 0);
    }

    /**
     * Устанавливает хеш топика
     *
     * @param string $data
     */
    public function setTextHash($data) {

        $this->setProp('topic_text_hash', $data);
    }

    /**
     * Устанавливает объект пользователя
     *
     * @param ModuleUser_EntityUser $data
     */
    public function setUser($data) {

        $this->setProp('user', $data);
    }

    /**
     * Устанавливает объект блога
     *
     * @param ModuleBlog_EntityBlog $data
     */
    public function setBlog($data) {

        $this->setProp('blog', $data);
    }

    /**
     * Устанавливает факт голосования пользователя в топике-опросе
     *
     * @param int $data
     */
    public function setUserQuestionIsVote($data) {

        $this->setProp('user_question_is_vote', $data);
    }

    /**
     * Устанавливает объект голосования за топик
     *
     * @param ModuleVote_EntityVote $data
     */
    public function setVote($data) {

        $this->setProp('vote', $data);
    }

    /**
     * Устанавливает количество новых комментариев
     *
     * @param int $data
     */
    public function setCountCommentNew($data) {

        $this->setProp('count_comment_new', $data);
    }

    /**
     * Устанавливает дату прочтения топика текущим пользователем
     *
     * @param string $data
     */
    public function setDateRead($data) {

        $this->setProp('date_read', $data);
    }

    /**
     * Устанавливает количество пользователей, добавивших топик в избранное
     *
     * @param int $data
     */
    public function setCountFavourite($data) {

        $this->setProp('topic_count_favourite', $data);
    }

    public function setIndexIgnoreLock() {

        $this->setTopicIndexIgnore(self::INDEX_IGNORE_LOCK);
    }

    public function setShowPhotoset($data){
        $this->setExtraValue('show_photoset', (bool)$data);
    }

    /**
     * Can this topic be edited by the user?
     *
     * @param ModuleUser_EntityUser $oUser
     *
     * @return bool
     */
    public function CanEditedBy($oUser) {

        return E::ModuleACL()->IsAllowEditTopic($this, $oUser);
    }

    /**
     * Can this topic be deleted by the user?
     *
     * @param ModuleUser_EntityUser $oUser
     *
     * @return bool
     */
    public function CanDeletedBy($oUser) {

        return E::ModuleACL()->IsAllowDeleteTopic($this, $oUser);
    }

    /**
     * Creates RSS item for the topic
     *
     * @return ModuleRss_EntityRssItem
     */
    public function CreateRssItem() {

        $aRssItemData = array(
            'title' => $this->getTitle(),
            'description' => $this->getText(),
            'link' => $this->getUrl(),
            'author' => $this->getUser() ? $this->getUser()->getMail() : '',
            'guid' => $this->getUrlShort(),
            'comments' => $this->getUrl() . '#comments',
            'pub_date' => $this->getDateShow() ? date('r', strtotime($this->getDateShow())) : '',
        );
        $oRssItem = E::GetEntity('ModuleRss_EntityRssItem', $aRssItemData);

        return $oRssItem;
    }

    /**
     * @return ModuleMresource_EntityMresourceRel[]
     */
    public function getImages() {

        $aResult = array();
        $aMediaTypes = $this->getMediaResources();
        if ($aMediaTypes) {
            foreach($aMediaTypes as $sType => $aMedia) {
                /** @var ModuleMresource_EntityMresourceRel $oMedia */
                foreach($aMedia as $iMediaId => $oMedia) {
                    if ($oMedia->isImage()) {
                        $aResult[$iMediaId] = $oMedia;
                    }
                }
            }
        }
        return $aResult;
    }

    protected function _findImages($aImages, $sFilter, $iValue = null) {

        if (empty($aImages)) {
            return array();
        }
        $aResult = array();
        if ($sFilter === 'height-more' || $sFilter === 'width-more') {
            if ($sFilter === 'height-more') {
                $sMethod = 'getSizeHeight';
            } else {
                $sMethod = 'getSizeWidth';
            }
            foreach($aImages as $xKey => $oImage) {
                if ($oImage->$sMethod() > $iValue) {
                    $aResult[$xKey] = $oImage;
                }
            }
        } elseif ($sFilter === 'height-less' || $sFilter === 'width-less') {
            if ($sFilter === 'height-more') {
                $sMethod = 'getSizeHeight';
            } else {
                $sMethod = 'getSizeWidth';
            }
            foreach($aImages as $xKey => $oImage) {
                if ($oImage->$sMethod() < $iValue) {
                    $aResult[$xKey] = $oImage;
                }
            }
        } elseif ($sFilter === 'height-max' || $sFilter === 'height-min') {
            $aSorted = array();
            foreach($aImages as $xKey => $oImage) {
                $aSorted[$oImage->getSizeHeight()][$xKey] = $oImage;
            }
            if ($sFilter === 'height-max') {
                $iSize = max(array_keys($aSorted));
            } else {
                $iSize = min(array_keys($aSorted));
            }
            $aResult = $aSorted[$iSize];
        } elseif ($sFilter === 'width-max' || $sFilter === 'width-min') {
            $aSorted = array();
            foreach($aImages as $xKey => $oImage) {
                $aSorted[$oImage->getSizeWidth()][$xKey] = $oImage;
            }
            if ($sFilter === 'width-max') {
                $iSize = max(array_keys($aSorted));
            } else {
                $iSize = min(array_keys($aSorted));
            }
            $aResult = $aSorted[$iSize];
        } else {
            $aResult = $aImages;
        }

        return $aResult;
    }

    /**
     * @param $aImages
     * @param $aFilter
     *
     * @return array
     */
    protected function _filterImages($aImages, $aFilter) {

        $aResult = $aImages;
        $aMinMax = array('height-max', 'height-min', 'width-max', 'width-min');
        foreach($aFilter as $sFilterName => $xFilterValue) {
            if (in_array($xFilterValue, $aMinMax, true)) {
                $aResult = $this->_findImages($aResult, $xFilterValue, true);
            } else {
                $aResult = $this->_findImages($aResult, $sFilterName, $xFilterValue);
            }
        }
        return $aResult;
    }

    /**
     * Returns images loaded in topic by filter
     * Filter parameters:
     *      'width-more' => N - width more then N px
     *      'width-less' => N - width less then N px
     *      'height-more' => N - height more then N px
     *      'height-less' => N - height less then N px
     *      'width-max' => true - images width max width
     *      'width-min' => true - images width min width
     *      'height-max' => true - images width max height
     *      'height-min' => true - images width min height
     *
     *
     * @param array $aFilter
     *
     * @return ModuleMresource_EntityMresourceRel[]
     */
    public function getLoadedImages($aFilter = array()) {

        $sPropKey = '_loaded_images' . serialize($aFilter);
        $aResult = $this->getProp($sPropKey);
        if ($aResult === null) {
            $aResult = array();
            if (!empty($aFilter)) {
                $aImages = $this->getLoadedImages();
                if (!empty($aImages)) {
                    $aResult = $this->_filterImages($aImages, $aFilter);
                }
            } else {
                $aMedia = $this->getImages();
                if ($aMedia) {
                    foreach($aMedia as $iMediaId => $oMedia) {
                        if ($oMedia->isFile()) {
                            $aResult[$iMediaId] = $oMedia;
                        }
                    }
                }
            }
            $this->setProp($sPropKey, $aResult);
        }
        return $aResult;
    }

    /**
     * @param array $aFilter
     *
     * @return ModuleMresource_EntityMresourceRel|null
     */
    public function selectImage($aFilter = array()) {

        $aImages = $this->getLoadedImages($aFilter);
        if (empty($aImages)) {
            return null;
        }
        return reset($aImages);
    }

}

// EOF