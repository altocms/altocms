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
 * Объект маппера для работы с БД
 *
 * @package modules.geo
 * @since   1.0
 */
class ModuleGeo_MapperGeo extends Mapper {
    /**
     * Добавляет связь объекта с гео-объектом в БД
     *
     * @param ModuleGeo_EntityTarget $oTarget    Объект связи с владельцем
     *
     * @return ModuleGeo_EntityTarget|bool
     */
    public function AddTarget($oTarget) {

        $sql = "INSERT INTO ?_geo_target(?#) VALUES(?a)";
        if ($this->oDb->query($sql, $oTarget->getKeyProps(), $oTarget->getValProps())) {
            return true;
        }
        return false;
    }

    /**
     * Возвращает список связей по фильтру
     *
     * @param array $aFilter      Фильтр
     * @param int   $iCount       Возвращает количество элементов
     * @param int   $iCurrPage    Номер страницы
     * @param int   $iPerPage     Количество элементов на страницу
     *
     * @return array
     */
    public function GetTargets($aFilter, &$iCount, $iCurrPage, $iPerPage) {

        if (isset($aFilter['target_id']) && !is_array($aFilter['target_id'])) {
            $aFilter['target_id'] = array($aFilter['target_id']);
        }

        $sql
            = "SELECT
					*
				FROM
					?_geo_target
				WHERE
					1 = 1
					{ AND geo_type = ? }
					{ AND geo_id = ?d }
					{ AND target_type = ? }
					{ AND target_id IN ( ?a ) }
					{ AND country_id = ?d }
					{ AND region_id = ?d }
					{ AND city_id = ?d }
				ORDER BY target_id DESC
				LIMIT ?d, ?d ;
					";
        $aResult = array();
        $aRows = $this->oDb->selectPage(
            $iCount, $sql,
            isset($aFilter['geo_type']) ? $aFilter['geo_type'] : DBSIMPLE_SKIP,
            isset($aFilter['geo_id']) ? $aFilter['geo_id'] : DBSIMPLE_SKIP,
            isset($aFilter['target_type']) ? $aFilter['target_type'] : DBSIMPLE_SKIP,
            (isset($aFilter['target_id']) && count($aFilter['target_id'])) ? $aFilter['target_id'] : DBSIMPLE_SKIP,
            isset($aFilter['country_id']) ? $aFilter['country_id'] : DBSIMPLE_SKIP,
            isset($aFilter['region_id']) ? $aFilter['region_id'] : DBSIMPLE_SKIP,
            isset($aFilter['city_id']) ? $aFilter['city_id'] : DBSIMPLE_SKIP,
            ($iCurrPage - 1) * $iPerPage, $iPerPage
        );
        if ($aRows) {
            $aResult = E::GetEntityRows('ModuleGeo_EntityTarget', $aRows);
        }
        return $aResult;
    }

    /**
     * Возвращает список стран сгруппированных по количеству использований в данном типе объектов
     *
     * @param string $sTargetType    Тип владельца
     * @param int    $iLimit         Количество элементов
     *
     * @return array
     */
    public function GetGroupCountriesByTargetType($sTargetType, $iLimit) {

        $sql
            = "
			SELECT
				t.count,
				g.*
			FROM (
					SELECT
						count(*) as count,
						country_id
					FROM
						?_geo_target
					WHERE target_type = ? AND country_id IS NOT NULL
					GROUP BY country_id ORDER BY count DESC LIMIT 0, ?d
				) as t
				JOIN ?_geo_country as g on t.country_id=g.id
			ORDER BY g.name_ru
		";
        $aResult = array();
        if ($aRows = $this->oDb->select($sql, $sTargetType, $iLimit)) {
            $aResult = E::GetEntityRows('ModuleGeo_EntityCountry', $aRows);
        }
        return $aResult;
    }

    /**
     * Возвращает список городов сгруппированных по количеству использований в данном типе объектов
     *
     * @param string $sTargetType    Тип владельца
     * @param int    $iLimit         Количество элементов
     *
     * @return array
     */
    public function GetGroupCitiesByTargetType($sTargetType, $iLimit) {

        $sql
            = "
			SELECT
				t.count,
				g.*
			FROM (
					SELECT
						count(*) as count,
						city_id
					FROM
						?_geo_target
					WHERE target_type = ? AND city_id IS NOT NULL
					GROUP BY city_id ORDER BY count DESC LIMIT 0, ?d
				) as t
				JOIN ?_geo_city as g on t.city_id=g.id
			ORDER BY g.name_ru
		";
        $aResult = array();
        if ($aRows = $this->oDb->select($sql, $sTargetType, $iLimit)) {
            $aResult = E::GetEntityRows('ModuleGeo_EntityCity', $aRows);
        }
        return $aResult;
    }

    /**
     * Удаляет связи по фильтру
     *
     * @param array $aFilter    Фильтр
     *
     * @return bool|int
     */
    public function DeleteTargets($aFilter) {

        if (!$aFilter) {
            return false;
        }
        $sql
            = "DELETE
				FROM
					?_geo_target
				WHERE
					1 = 1
					{ AND geo_type = ? }
					{ AND geo_id = ?d }
					{ AND target_type = ? }
					{ AND target_id = ?d }
					{ AND country_id = ?d }
					{ AND region_id = ?d }
					{ AND city_id = ?d }
				";
        return $this->oDb->query(
            $sql,
            isset($aFilter['geo_type']) ? $aFilter['geo_type'] : DBSIMPLE_SKIP,
            isset($aFilter['geo_id']) ? $aFilter['geo_id'] : DBSIMPLE_SKIP,
            isset($aFilter['target_type']) ? $aFilter['target_type'] : DBSIMPLE_SKIP,
            isset($aFilter['target_id']) ? $aFilter['target_id'] : DBSIMPLE_SKIP,
            isset($aFilter['country_id']) ? $aFilter['country_id'] : DBSIMPLE_SKIP,
            isset($aFilter['region_id']) ? $aFilter['region_id'] : DBSIMPLE_SKIP,
            isset($aFilter['city_id']) ? $aFilter['city_id'] : DBSIMPLE_SKIP
        );
    }

    /**
     * Возвращает список стран по фильтру
     *
     * @param array $aFilter      Фильтр
     * @param array $aOrder       Сортировка
     * @param int   $iCount       Возвращает количество элементов
     * @param int   $iCurrPage    Номер страницы
     * @param int   $iPerPage     Количество элементов на страницу
     *
     * @return array
     */
    public function GetCountries($aFilter, $aOrder, &$iCount, $iCurrPage, $iPerPage) {

        $aOrderAllow = array('id', 'name_ru', 'name_en', 'sort');
        $sOrder = '';
        foreach ($aOrder as $key => $value) {
            if (!in_array($key, $aOrderAllow)) {
                unset($aOrder[$key]);
            } elseif (in_array($value, array('asc', 'desc'))) {
                $sOrder .= " {$key} {$value},";
            }
        }
        $sOrder = trim($sOrder, ',');
        if ($sOrder == '') {
            $sOrder = ' id desc ';
        }

        $sql
            = "SELECT
					gc.id AS ARRAY_KEY, gc.*
				FROM
					?_geo_country AS gc
				WHERE
					1 = 1
					{ AND id = ?d }
					{ AND id IN (?a) }
					{ AND name_ru = ? }
					{ AND name_ru LIKE ? }
					{ AND name_en = ? }
					{ AND name_en LIKE ? }
					{ AND code = ? }
					{ AND code IN (?a) }
				ORDER BY {$sOrder}
				LIMIT ?d, ?d ;
					";
        $aResult = array();
        $aRows = $this->oDb->selectPage(
            $iCount, $sql,
            (isset($aFilter['id']) && !is_array($aFilter['id'])) ? $aFilter['id'] : DBSIMPLE_SKIP,
            (isset($aFilter['id']) && is_array($aFilter['id'])) ? $aFilter['id'] : DBSIMPLE_SKIP,
            isset($aFilter['name_ru']) ? $aFilter['name_ru'] : DBSIMPLE_SKIP,
            isset($aFilter['name_ru_like']) ? $aFilter['name_ru_like'] : DBSIMPLE_SKIP,
            isset($aFilter['name_en']) ? $aFilter['name_en'] : DBSIMPLE_SKIP,
            isset($aFilter['name_en_like']) ? $aFilter['name_en_like'] : DBSIMPLE_SKIP,
            (isset($aFilter['code']) && !is_array($aFilter['code']))? $aFilter['code'] : DBSIMPLE_SKIP,
            (isset($aFilter['code']) && is_array($aFilter['code']))? $aFilter['code'] : DBSIMPLE_SKIP,
            ($iCurrPage - 1) * $iPerPage, $iPerPage
        );
        if ($aRows) {
            $aResult = E::GetEntityRows('ModuleGeo_EntityCountry', $aRows);
        }
        return $aResult;
    }

    /**
     * Возвращает список стран по фильтру
     *
     * @param array $aFilter      Фильтр
     * @param array $aOrder       Сортировка
     * @param int   $iCount       Возвращает количество элементов
     * @param int   $iCurrPage    Номер страницы
     * @param int   $iPerPage     Количество элементов на страницу
     *
     * @return array
     */
    public function GetRegions($aFilter, $aOrder, &$iCount, $iCurrPage, $iPerPage) {

        $aOrderAllow = array('id', 'name_ru', 'name_en', 'sort', 'country_id');
        $sOrder = '';
        foreach ($aOrder as $key => $value) {
            if (!in_array($key, $aOrderAllow)) {
                unset($aOrder[$key]);
            } elseif (in_array($value, array('asc', 'desc'))) {
                $sOrder .= " {$key} {$value},";
            }
        }
        $sOrder = trim($sOrder, ',');
        if ($sOrder == '') {
            $sOrder = ' id desc ';
        }

        if (isset($aFilter['country_id']) && !is_array($aFilter['country_id'])) {
            $aFilter['country_id'] = array($aFilter['country_id']);
        }

        $sql
            = "SELECT
					gr.id AS ARRAY_KEY, gr.*
				FROM
					?_geo_region AS gr
				WHERE
					1 = 1
					{ AND id = ?d }
					{ AND id IN (?a) }
					{ AND name_ru = ? }
					{ AND name_ru LIKE ? }
					{ AND name_en = ? }
					{ AND name_en LIKE ? }
					{ AND country_id IN ( ?a ) }
				ORDER BY {$sOrder}
				LIMIT ?d, ?d ;
					";
        $aResult = array();
        $aRows = $this->oDb->selectPage(
            $iCount, $sql,
            (isset($aFilter['id']) && !is_array($aFilter['id'])) ? $aFilter['id'] : DBSIMPLE_SKIP,
            (isset($aFilter['id']) && is_array($aFilter['id'])) ? $aFilter['id'] : DBSIMPLE_SKIP,
            isset($aFilter['name_ru']) ? $aFilter['name_ru'] : DBSIMPLE_SKIP,
            isset($aFilter['name_ru_like']) ? $aFilter['name_ru_like'] : DBSIMPLE_SKIP,
            isset($aFilter['name_en']) ? $aFilter['name_en'] : DBSIMPLE_SKIP,
            isset($aFilter['name_en_like']) ? $aFilter['name_en_like'] : DBSIMPLE_SKIP,
            (isset($aFilter['country_id']) && count($aFilter['country_id'])) ? $aFilter['country_id'] : DBSIMPLE_SKIP,
            ($iCurrPage - 1) * $iPerPage, $iPerPage
        );
        if ($aRows) {
            $aResult = E::GetEntityRows('ModuleGeo_EntityRegion', $aRows);
        }
        return $aResult;
    }

    /**
     * Возвращает список стран по фильтру
     *
     * @param array $aFilter      Фильтр
     * @param array $aOrder       Сортировка
     * @param int   $iCount       Возвращает количество элементов
     * @param int   $iCurrPage    Номер страницы
     * @param int   $iPerPage     Количество элементов на страницу
     *
     * @return array
     */
    public function GetCities($aFilter, $aOrder, &$iCount, $iCurrPage, $iPerPage) {

        $aOrderAllow = array('id', 'name_ru', 'name_en', 'sort', 'country_id', 'region_id');
        $sOrder = '';
        foreach ($aOrder as $key => $value) {
            if (!in_array($key, $aOrderAllow)) {
                unset($aOrder[$key]);
            } elseif (in_array($value, array('asc', 'desc'))) {
                $sOrder .= " {$key} {$value},";
            }
        }
        $sOrder = trim($sOrder, ',');
        if ($sOrder == '') {
            $sOrder = ' id desc ';
        }

        if (isset($aFilter['country_id']) && !is_array($aFilter['country_id'])) {
            $aFilter['country_id'] = array($aFilter['country_id']);
        }
        if (isset($aFilter['region_id']) && !is_array($aFilter['region_id'])) {
            $aFilter['region_id'] = array($aFilter['region_id']);
        }

        $sql
            = "SELECT
					gc.id AS ARRAY_KEY, gc.*
				FROM
					?_geo_city as gc
				WHERE
					1 = 1
					{ AND id = ?d }
					{ AND id IN (?a) }
					{ AND name_ru = ? }
					{ AND name_ru LIKE ? }
					{ AND name_en = ? }
					{ AND name_en LIKE ? }
					{ AND country_id IN ( ?a ) }
					{ AND region_id IN ( ?a ) }
				ORDER BY {$sOrder}
				LIMIT ?d, ?d ;
					";
        $aResult = array();
        $aRows = $this->oDb->selectPage(
            $iCount, $sql,
            (isset($aFilter['id']) && !is_array($aFilter['id'])) ? $aFilter['id'] : DBSIMPLE_SKIP,
            (isset($aFilter['id']) && is_array($aFilter['id'])) ? $aFilter['id'] : DBSIMPLE_SKIP,
            isset($aFilter['name_ru']) ? $aFilter['name_ru'] : DBSIMPLE_SKIP,
            isset($aFilter['name_ru_like']) ? $aFilter['name_ru_like'] : DBSIMPLE_SKIP,
            isset($aFilter['name_en']) ? $aFilter['name_en'] : DBSIMPLE_SKIP,
            isset($aFilter['name_en_like']) ? $aFilter['name_en_like'] : DBSIMPLE_SKIP,
            (isset($aFilter['country_id']) && count($aFilter['country_id'])) ? $aFilter['country_id'] : DBSIMPLE_SKIP,
            (isset($aFilter['region_id']) && count($aFilter['region_id'])) ? $aFilter['region_id'] : DBSIMPLE_SKIP,
            ($iCurrPage - 1) * $iPerPage, $iPerPage
        );
        if ($aRows) {
            $aResult = E::GetEntityRows('ModuleGeo_EntityCity', $aRows);
        }
        return $aResult;
    }
}

// EOF