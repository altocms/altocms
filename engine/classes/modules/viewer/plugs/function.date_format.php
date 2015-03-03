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
 * Плагин для смарти.
 * Позволяет получать дату с возможностью склонения
 * формы слова и поддержкой мультиязычноти.
 *
 * Список ключей параметров:
 *        date*          [string]
 *        format*        [string]
 *        declination*   [int]
 *        now*           [int]    Количество секунд, в течении которых событие имеет статус "Только что"
 *        day*           [string] Указывает на необходимость замены "Сегодня", "Вчера", "Завтра".
 *                                В указанном формате 'day' будет заменено на соответствующее значение.
 *        minutes_back*  [int]    Количество минут, в течении которых событие имеет статус "... минут назад"
 *        hours_back*    [int]    Количество часов, в течении которых событие имеет статус "... часов назад"
 *        tz*            [float]  Временная зона
 *        notz*          [bool]   Не учитывать зону
 *
 * (* - параметр является необязательным)
 *
 * @param   array  $aParams
 * @param   Smarty $oSmarty
 *
 * @return  string
 */
function smarty_function_date_format($aParams, &$oSmarty) {

    $sFormatDefault = 'd F Y, H:i'; //  формат даты по умолчанию
    $iDeclinationDefault = 1; //  индекс склонения по умолчанию
    /**
     * Текущая дата и сдвиг времени для пользователя
     */
    $iTz = false;
    if (!isset($aParams['notz'])) {
        if (isset($aParams['tz'])) {
            $iTz = $aParams['tz'];
        }
        if ($iTz === false) {
            if (($oUserCurrent = E::ModuleUser()->GetUserCurrent()) && $oUserCurrent->getSettingsTimezone()) {
                $iTz = $oUserCurrent->getSettingsTimezone();
            }
        }
    }
    if ($iTz !== false) {
        $iDiff = (date('I') + $iTz - (strtotime(date('Y-m-d H:i:s')) - strtotime(gmdate('Y-m-d H:i:s'))) / 3600) * 3600;
    } else {
        $iDiff = 0; // пользователю показываем время от зоны из основного конфига
    }
    $iNow = time() + $iDiff;
    /**
     * Определяем дату
     */
    $sDate = (empty($aParams['date'])) ? time() : $aParams['date'];
    $iDeclination = (!isset($aParams['declination'])) ? $iDeclinationDefault : $aParams['declination'];
    $sFormat = (empty($aParams['format'])) ? $sFormatDefault : $aParams['format'];
    /**
     * Если указан другой язык, подгружаем его
     */
    if (isset($aParams['lang']) && $aParams['lang'] != E::ModuleLang()->GetLang()) {
        E::ModuleLang()->SetLang($aParams['lang']);
    }

    $aMonth = E::ModuleLang()->Get('month_array');
    $iDate = (preg_match("/^\d+$/", $sDate)) ? $sDate : strtotime($sDate);
    $iDate += $iDiff;

    /**
     * Если указана необходимость выполнять проверку на NOW
     */
    if (isset($aParams['now'])) {
        if ($iDate + $aParams['now'] > $iNow) {
            return E::ModuleLang()->Get('date_now');
        }
    }

    /**
     * Если указана необходимость на проверку minutes back
     */
    if (isset($aParams['minutes_back'])) {
        require_once('modifier.declension.php');

        $iTimeDelta = round(($iNow - $iDate) / 60);
        if ($iTimeDelta < $aParams['minutes_back']) {
            return ($iTimeDelta != 0)
                ? smarty_modifier_declension(
                    $iTimeDelta,
                    E::ModuleLang()->Get('date_minutes_back', array('minutes' => $iTimeDelta)),
                    E::ModuleLang()->GetLang()
                )
                : E::ModuleLang()->Get('date_minutes_back_less');
        }
    }

    /**
     * Если указана необходимость на проверку minutes back
     */
    if (isset($aParams['hours_back'])) {
        require_once('modifier.declension.php');

        $iTimeDelta = round(($iNow - $iDate) / (60 * 60));
        if ($iTimeDelta < $aParams['hours_back']) {
            return ($iTimeDelta != 0)
                ? smarty_modifier_declension(
                    $iTimeDelta,
                    E::ModuleLang()->Get('date_hours_back', array('hours' => $iTimeDelta)),
                    E::ModuleLang()->GetLang()
                )
                : E::ModuleLang()->Get('date_hours_back_less');
        }
    }

    /**
     * Если указана необходимость автоподстановки "Сегодня", "Вчера", "Завтра".
     */
    if (isset($aParams['day']) && $aParams['day']) {
        switch (date('Y-m-d', $iDate)) {
            /**
             * Если дата совпадает с сегодняшней
             */
            case date('Y-m-d'):
                $sDay = E::ModuleLang()->Get('date_today');
                break;
            /**
             * Если дата совпадает со вчерашней
             */
            case date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"))):
                $sDay = E::ModuleLang()->Get('date_yesterday');
                break;
            /**
             * Если дата совпадает с завтрашней
             */
            case date('Y-m-d', mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"))):
                $sDay = E::ModuleLang()->Get('date_tomorrow');
                break;

            default:
                $sDay = null;
        }
        if ($sDay) {
            $sFormat = str_replace("day", preg_replace("#(\w{1})#", '\\\${1}', $sDay), $aParams['day']);
            return date($sFormat, $iDate);
        }
    }

    /**
     * Определяем нужное текстовое значение названия месяца
     */
    $iMonth = date("n", $iDate);
    $sMonth = isset($aMonth[$iMonth])
        ? $aMonth[$iMonth]
        : "";

    /**
     * Если не найден индекс склонения, берем склонене по умолчанию.
     * Если индекс по умолчанию также не определен, берем первое значение в массиве.
     */
    if (is_array($sMonth)) {
        $sMonth = isset($sMonth[$iDeclination])
            ? $sMonth[$iDeclination]
            : $sMonth[$iDeclinationDefault];
    }

    $sFormat = preg_replace("~(?<!\\\\)F~U", preg_replace('~(\w{1})~u', '\\\${1}', $sMonth), $sFormat);

    return date($sFormat, $iDate);
}

// EOF