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
class ModuleBlog_EntityBlogType extends Entity {

    public function Init() {

        parent::Init();
        $this->aValidateRules[] = array(
            'type_code',                        // поле
            'string', 'min' => 2, 'max' => 10,  // проверка на значение
            'allowEmpty' => false,              // может ли быть пустым
            'label' => $this->Lang_Get('action.admin.blogtypes_typecode'),
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
            $sValue = $this->Lang_Get(str_replace('%%type_code%%', $this->getTypeCode(), $sLangKey));
            if (!$sValue) {
                $sValue = $sDefault;
            }
            if ($sValue && $sPropKey) {
                $this->setProp($sPropKey, $sValue);
            }
        }
        return $sValue;
    }

    public function GetName($sLang = null) {
        return $this->_getPropLangText('blogtypes_type_%%type_code%%_name', 'name', $this->getTypeCode(), $sLang);
    }

    public function GetTitle($sLang = null) {
        return $this->_getPropLangText('blogtypes_type_%%type_code%%_title', 'title', '', $sLang);
    }

    public function GetDescription($sLang = null) {
        return $this->_getPropLangText('blogtypes_type_%%type_code%%_description', 'description', '', $sLang);
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

    public function IsCanDelete() {
        return (bool)$this->getProp('candelete');
    }

    public function IsAllowAdd() {
        return (bool)$this->getProp('allow_add');
    }

    public function AllowAddByUser($oUser) {
        if ($this->IsAllowAdd()) {
            $oUser->getRating() > $this->GetMinRating();
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
}

// EOF