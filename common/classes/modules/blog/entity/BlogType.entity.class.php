<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

/**
 * @package modules.blog
 * @since 1.0
 */

/**
 * Class ModuleBlog_EntityBlogType
 *
 * @method setAllowAdd($xParam)
 * @method setMinRateAdd($xParam)
 * @method setMaxNum($xParam)
 * @method setAllowList($xParam)
 * @method setIndexIgnore($xParam)
 * @method setMembership($xParam)
 * @method setMinRateWrite($xParam)
 * @method setMinRateRead($xParam)
 * @method setMinRateComment($xParam)
 * @method setActive($xParam)
 * @method setContentType($xParam)
 * @method setAclWrite($xParam)
 * @method setAclRead($xParam)
 * @method setAclComment($xParam)
 *
 * @method float getMinRateAdd()
 * @method float getMinRateList()
 * @method float getMinRateWrite()
 * @method float getMinRateRead()
 * @method float getMinRateComment()
 * @method string getTypeName()
 */
class ModuleBlog_EntityBlogType extends Entity {

    protected $aDefaults = array(
        'type_name' => '{{blogtypes_type_%%type_code%%_name}}',
        'type_description' => '{{blogtypes_type_%%type_code%%_description}}'
    );

    public function Init() {

        parent::Init();
        $this->aValidateRules[] = array(
            'type_code',                        // поле
            'string', 'min' => 2, 'max' => 10,  // проверка на значение
            'allowEmpty' => false,              // может ли быть пустым
            'label' => E::ModuleLang()->Get('action.admin.blogtypes_typecode'),
            'on' => array('add'),               // сценарий
        );
    }

    protected function _getPropLangText($sLangKey, $sPropKey, $sDefault, $sLang = null) {

        $sValue = null;
        if ($sPropKey) {
            if ($sLang) {
                $sPropKey .= '_' . $sLang;
            }
            // Пытаемся получить значение <key>_<lang> (типа name_ru)
            $sValue = $this->getProp($sPropKey, null);
        }
        if (is_null($sValue)) {
            $sValue = E::ModuleLang()->Get(str_replace('%%type_code%%', $this->getTypeCode(), $sLangKey));
            if (!$sValue) {
                $sValue = $sDefault;
            }
            if ($sValue && $sPropKey) {
                $this->setProp($sPropKey, $sValue);
            }
        }
        return $sValue;
    }

    public function getProp($sKey, $xDefault = NULL) {

        if ($sKey == 'type_name' || $sKey == 'type_description') {
            $sValue = parent::getProp($sKey);
            if (!$sValue) {
                $sValue = str_replace('%%type_code%%', $this->getTypeCode(), $this->aDefaults[$sKey]);
            }
            return $sValue;
        }
        return parent::getProp($sKey, $xDefault);
    }

    public function GetName($sLang = null) {

        return $this->getLangTextProp('type_name', $sLang);
    }

    public function GetTitle($sLang = null) {

        return $this->_getPropLangText('blogtypes_type_%%type_code%%_title', 'title', '', $sLang);
    }

    public function GetDescription($sLang = null) {

        return $this->getLangTextProp('type_description', $sLang);
    }

    /**
     * Права на чтение
     *
     * @param   int $nMask
     *
     * @return  int
     */
    public function GetAclRead($nMask = null) {

        return $this->getPropMask('acl_read', $nMask);
    }

    /**
     * Права на запись
     *
     * @param   int $nMask
     *
     * @return  int
     */
    public function GetAclWrite($nMask = null) {

        return $this->getPropMask('acl_write', $nMask);
    }

    /**
     * Права на комментирование
     *
     * @param   int $nMask
     *
     * @return  int
     */
    public function GetAclComment($nMask = null) {

        return $this->getPropMask('acl_comment', $nMask);
    }

    /**
     * Членство в блоге
     *
     * @param   int $nMask
     *
     * @return  int
     */
    public function GetMembership($nMask = null) {

        return $this->getPropMask('membership', $nMask);
    }

    /**
     * Возвращает индекс (порядок) сортировки
     *
     * @return int
     */
    public function GetNorder() {
        return intval($this->getProp('norder'));
    }

    public function IsShowTitle() {
        return (bool)$this->getProp('allow_list');
    }

    public function IsIndexIgnore() {
        return (bool)$this->getProp('index_ignore');
    }

    public function IsActive() {
        return (bool)$this->getProp('active');
    }

    public function CanDelete() {
        if (is_null($xVal = $this->getProp('candelete'))) {
            return true;
        }
        return (bool)$xVal;
    }

    public function IsAllowAdd() {
        return (bool)$this->getProp('allow_add');
    }

    /**
     * @param ModuleUser_EntityUser $oUser
     *
     * @return bool
     */
    public function AllowAddByUser($oUser) {

        if ($this->IsAllowAdd()) {
            return $oUser->getRating() > $this->getMinRateAdd();
        }
        return false;
    }

    /**
     * Приватный блог
     *
     * По умолчанию это блог, который могут читать только участники
     *
     * @return bool
     */
    public function IsPrivate() {

        return !((bool)$this->GetAclRead(ModuleBlog::BLOG_USER_ACL_GUEST | ModuleBlog::BLOG_USER_ACL_USER));
    }

    /**
     * Блог только для чтения
     *
     * По умолчанию это блог, в который могут писать только участники
     *
     * @return bool
     */
    public function IsReadOnly() {

        return !((bool)$this->GetAclWrite(ModuleBlog::BLOG_USER_ACL_GUEST | ModuleBlog::BLOG_USER_ACL_USER));
    }

    /**
     * Скрытый блог
     *
     * По умолчанию это приватный блог, который не показывается в списках
     *
     * @return bool
     */
    public function IsHidden() {

        return $this->IsPrivate() && !$this->IsShowTitle();
    }

    /**
     * Checks if allows requires content type in this blog type
     *
     * @param $xContentType
     *
     * @return bool
     */
    public function IsContentTypeAllow($xContentType) {

        if (!$xContentType) {
            return true;
        }

        if (is_object($xContentType)) {
            $sContentTypeName = $xContentType->getContentUrl();
        } else {
            $sContentTypeName = (string)$xContentType;
        }

        $aAllowContentTypes = $this->getContentTypes();
        if (!$aAllowContentTypes) {
            // Если типы контента не заданы явно, то разрешены любые
            return true;
        }

        foreach ($aAllowContentTypes as $oAllowType) {
            if ($sContentTypeName == $oAllowType->getContentUrl()) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Возвращает типы контента для данного типа блога
     *
     * @return ModuleTopic_EntityContentType[]
     */
    public function getContentTypes() {

        if ($this->getProp('content_types')) {
            return $this->getProp('content_types');
        }

        return array();

    }

    /**
     * Устанавливает типы контента для типа блога
     *
     * @param ModuleTopic_EntityContentType[] $aData
     */
    public function setContentTypes($aData) {
        $this->setProp('content_types', $aData);
    }

    /**
     * Получает количество блогов у этого типа
     *
     * @return int
     */
    public function getBlogsCount() {
        return $this->getProp('blogs_count');
    }

    /**
     * Устанавливает количество блогов у этого типа
     *
     * @param int $aData
     */
    public function setBlogsCount($aData) {
        $this->setProp('blogs_count', $aData);
    }

    /**
     * Получает кодовое название типа блога
     *
     * @return string
     */
    public function getTypeCode() {
        return $this->getProp('type_code');
    }

    /**
     * Устанавливает кодовое название типа блога
     *
     * @param string $aData
     */
    public function setTypeCode($aData) {
        $this->setProp('type_code', $aData);
    }

    /**
     * Получает код типа блога
     *
     * @return string
     */
    public function getId() {
        return $this->getProp('id');
    }
}

// EOF