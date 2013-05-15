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
 * Объект сущности гео-объекта
 *
 * @package modules.geo
 * @since   1.0
 */
class ModuleGeo_EntityGeo extends Entity {

    /**
     * Возвращает имя гео-объекта в зависимости от языка
     *
     * @return string
     */
    public function getName() {

        $sName = $this->getProp('name_' . $this->Lang_GetLang());
        if (!$sName) {
            $sName = $this->getProp('name_' . $this->Lang_GetDefaultLang());
            if (!$sName && $this->getProp('name_en')) {
                $sName = $this->getProp('name_en');
            }
            if (!$sName && $this->getProp('name_ru')) {
                $sName = $this->getProp('name_ru');
            }
            if (!$sName && $this->getProp('name')) {
                $sName = $this->getProp('name');
            }
        }
        return $sName;
    }

    /**
     * Возвращает тип гео-объекта
     *
     * @return null|string
     */
    public function getType() {

        if ($this instanceof ModuleGeo_EntityCity) {
            return 'city';
        } elseif ($this instanceof ModuleGeo_EntityRegion) {
            return 'region';
        } elseif ($this instanceof ModuleGeo_EntityCountry) {
            return 'country';
        }
        return null;
    }

    /**
     * Возвращает гео-объект страны
     *
     * @return ModuleGeo_EntityGeo|null
     */
    public function getCountry() {

        if ($this->getType() == 'country') {
            return $this;
        }
        if ($oCountry = $this->getProp('country')) {
            return $oCountry;
        }
        if ($this->getCountryId()) {
            $oCountry = $this->Geo_GetCountryById($this->getCountryId());
            return $this->setProp('country', $oCountry);
        }
        return null;
    }

    /**
     * Возвращает гео-объект региона
     *
     * @return ModuleGeo_EntityGeo|null
     */
    public function getRegion() {

        if ($this->getType() == 'region') {
            return $this;
        }
        if ($oRegion = $this->getProp('region')) {
            return $oRegion;
        }
        if ($this->getRegionId()) {
            $oRegion = $this->Geo_GetRegionById($this->getRegionId());
            return $this->setProp('region', $oRegion);
        }
        return null;
    }

    /**
     * Возвращает гео-объект города
     *
     * @return ModuleGeo_EntityGeo|null
     */
    public function getCity() {

        if ($this->getType() == 'city') {
            return $this;
        }
        if ($oCity = $this->getProp('city')) {
            return $oCity;
        }
        if ($this->getCityId()) {
            $oCity = $this->Geo_GetCityById($this->getCityId());
            return $this->setProp('city', $oCity);
        }
        return null;
    }

}

// EOF