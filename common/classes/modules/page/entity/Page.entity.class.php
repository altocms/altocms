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

class ModulePage_EntityPage extends Entity {

    public function getId() {

        return $this->getProp('page_id');
    }

    public function getPid() {

        return $this->getProp('page_pid');
    }

    public function getUrl() {

        return $this->getProp('page_url');
    }

    public function getUrlFull() {

        return $this->getProp('page_url_full');
    }

    public function getTitle() {

        return $this->getProp('page_title');
    }

    public function getText() {

        return $this->getProp('page_text');
    }

    public function getDateAdd() {

        return $this->getProp('page_date_add');
    }

    public function getDateEdit() {

        return $this->getProp('page_date_edit');
    }

    public function getSeoKeywords() {

        return $this->getProp('page_seo_keywords');
    }

    public function getSeoDescription() {

        return $this->getProp('page_seo_description');
    }

    public function getActive() {

        return $this->getProp('page_active');
    }

    public function getMain() {

        return $this->getProp('page_main');
    }

    public function getSort() {

        return $this->getProp('page_sort');
    }

    public function getAutoBr() {

        return $this->getProp('page_auto_br');
    }

    public function getLevel() {

        return $this->getProp('level');
    }


    public function setId($data) {

        $this->setProp('page_id', $data);
    }

    public function setPid($data) {

        $this->setProp('page_pid', $data);
    }

    public function setUrl($data) {

        $this->setProp('page_url', $data);
    }

    public function setUrlFull($data) {

        $this->setProp('page_url_full', $data);
    }

    public function setTitle($data) {

        $this->setProp('page_title', $data);
    }

    public function setText($data) {

        $this->setProp('page_text', $data);
    }

    public function setDateAdd($data) {

        $this->setProp('page_date_add', $data);
    }

    public function setDateEdit($data) {

        $this->setProp('page_date_edit', $data);
    }

    public function setSeoKeywords($data) {

        $this->setProp('page_seo_keywords', $data);
    }

    public function setSeoDescription($data) {

        $this->setProp('page_seo_description', $data);
    }

    public function setActive($data) {

        $this->setProp('page_active', $data);
    }

    public function setMain($data) {

        $this->setProp('page_main', $data);
    }

    public function setSort($data) {

        $this->setProp('page_sort', $data);
    }

    public function setAutoBr($data) {

        $this->setProp('page_auto_br', $data);
    }
}

// EOF