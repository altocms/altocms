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
 * Абстрактный класс мапера
 * Вся задача маппера сводится в выполнению запроса к базе данных (или либому другому источнику данных)
 * и возвращения результата в модуль.
 *
 * @package engine
 * @since 1.0
 */
abstract class Mapper extends LsObject {

    const CRITERIA_CALC_TOTAL_SKIP = 0;
    const CRITERIA_CALC_TOTAL_AUTO = 1;
    const CRITERIA_CALC_TOTAL_FORCE = 2;
    const CRITERIA_CALC_TOTAL_ONLY = 3;

    /**
     * Объект подключения к базе данных
     *
     * @var DbSimple_Generic_Database
     */
    protected $oDb;

    /**
     * Передаем коннект к БД
     *
     * @param DbSimple_Generic_Database $oDb
     */
    public function __construct($oDb) {
        $this->oDb = $oDb;
    }

    protected function _arrayId($aIds) {
        if (!is_array($aIds)) {
            $aIds = array(intval($aIds));
        } else {
            foreach ($aIds as $n => $nId)
                $aIds[$n] = intval($nId);
        }
        array_unique($aIds);
        return $aIds;
    }

}

// EOF