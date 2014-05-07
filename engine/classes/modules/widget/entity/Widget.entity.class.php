<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

class ModuleWidget_EntityWidget extends Entity {

    public function __construct($aParam = null) {

        parent::__construct($aParam);
        if ($this->GetName()) {
            // задается идентификатор виджета
            $this->_checkId();
        }
        if (is_null($this->GetPriority())) {
            $this->SetPriority(0);
        }
        if ($this->GetId()) {
            $aCfgData = Config::Get('widget.' . $this->GetId() . '.config');
            if ($aCfgData) {
                $aCfgData = F::Array_Merge($this->_getData(), $aCfgData);
                $this->_setData($aCfgData);
            }
        }
    }

    /**
     * Проверка идентификатора виджета, если не задан, то берется из хеша
     */
    protected function _checkId() {

        if (!$this->isProp('id')) {
            $sId = $this->GetHash();
            $this->setProp('id', $sId);
            return $sId;
        }
    }

    /**
     * Преобразует значение свойства в массив
     */
    protected function getPropArray($sKey, $sSeparateChar = ',') {

        $xVal = $this->getProp($sKey);
        return F::Val2Array($xVal, $sSeparateChar);
    }

    protected function _setDisplay($sLabel, $sDate) {

        $sDate = date('Y-m-d', strtotime($sDate));
        $aData = $this->GetDisplay();
        if (!is_array($aData)) {
            $aData = array($sLabel => $sDate);
        } else {
            $aData[$sLabel] = $sDate;
        }
        $this->setProp('display', $aData);
    }

    /**
     * С какой даты показывать виджет
     *
     * @param $sDate
     */
    public function SetDisplayFrom($sDate) {

        $this->_setDisplay('date_from', $sDate);
    }

    /**
     * До какой даты показывать виджет
     *
     * @param $sDate
     */
    public function SetDisplayUpto($sDate) {

        $this->_setDisplay('date_upto', $sDate);
    }

    /**
     * Задать параметр виджета
     *
     * @param   string  $sKey
     * @param   mixed   $xVal
     */
    public function SetParam($sKey, $xVal) {

        $aParams = $this->GetParams();
        $aParams[$sKey] = $xVal;
        $this->SetParams($aParams);
    }

    public function SetParams($xVal) {

        $this->setProp('params', (array)$xVal);
    }


    public function GetParams() {

        return (array)$this->getProp('params');
    }

    public function GetParam($sKey) {

        $aParams = $this->GetParams();
        if (isset($aParams[$sKey])) {
            return $aParams[$sKey];
        } else {
            return null;
        }
    }

    /**
     * Returns priority of widget
     *
     * @return int|string
     */
    public function GetPriority() {

        $xResult = $this->getProp('priority');
        if (is_numeric($xResult) || is_null($xResult)) {
            return intval($xResult);
        }
        return strtolower($xResult);
    }

    /**
     * Returns order of widget
     *
     * @return int
     */
    public function GetOrder() {

        return intval($this->getProp('order'));
    }

    /**
     * Returns widget's ID. If ID does not exist it will be created
     *
     * @return string
     */
    public function GetId() {

        $sId = $this->getProp('id');
        if (!$sId) {
            $sId = $this->_checkId();
        }
        return $sId;
    }

    /**
     * Returns group of widget
     *
     * @return mixed|null
     */
    public function GetGroup() {

        $sGroup = $this->getProp('wgroup');
        if (!$sGroup) {
            $sGroup = $this->getProp('group');
        }
        return $sGroup;
    }

    public function GetHash() {

        return md5($this->GetPluginId() . '.' . $this->GetName());
    }

    public function GetPluginId() {

        $sResult = $this->getProp('plugin');
        if (is_null($sResult)) {
            /* LS-compatible */
            $sResult = $this->GetParam('plugin');
        }
        return $sResult;
    }

    public function GetDir() {

        $sDir = $this->GetParam('dir');
        if ($sPlugin = $this->GetPluginId()) {
            $sDir = F::File_NormPath(Plugin::GetTemplatePath($sPlugin) . '/' . $sDir);
        }
        return $sDir;
    }

    public function GetIncludePaths() {

        $xResult = $this->getPropArray('on');
        return $xResult;
    }

    public function GetExcludePaths() {

        $xResult = $this->getPropArray('off');
        return $xResult;
    }

    public function GetName() {

        return $this->getProp('name');
    }

    public function GetActions() {

        return (array)$this->getProp('action');
    }

    public function GetDisplay() {

        return $this->getProp('display', true);
    }

    /**
     * Период действия виджета
     */
    public function GetPeriod() {

        $xData = $this->GetDisplay();
        if (is_array($xData)) {
            return $xData;
        }
        return null;
    }

    public function GetDateFrom() {

        $aData = $this->GetPeriod();
        if (isset($aData['date_from'])) {
            return $aData['date_from'];
        }
        return null;
    }

    public function GetDateUpto() {

        $aData = $this->GetPeriod();
        if (isset($aData['date_upto'])) {
            return $aData['date_upto'];
        }
        return null;
    }

    /**
     * Является ли виджет активным
     *
     * @return bool
     */
    public function isActive() {

        return (bool)$this->getProp('active', true);
    }

    /**
     * Установлен ли приоритет в значение 'top'
     *
     * @return bool
     */
    public function isTop() {

        return $this->GetPriority() === 'top';
    }

    /**
     * Нужно ли отображать этот виджет
     *
     * @param   bool    $bCheckDateOnly
     *
     * @return  bool
     */
    public function isDisplay($bCheckDateOnly = false) {

        $xDisplay = $this->GetDisplay();
        $bResult = (bool)$xDisplay && $this->isActive();
        if ($bResult && is_array($xDisplay)) {
            foreach ($xDisplay as $sParamName => $sParamValue) {
                if ($sParamName == 'date_from' && $sParamValue) {
                    $bResult = $bResult && (date('Y-m-d H:i:s') >= $sParamValue);
                } elseif ($sParamName == 'date_upto' && $sParamValue) {
                    $bResult = $bResult && (date('Y-m-d H:i:s') <= $sParamValue);
                }
            }
        }
        if ($bCheckDateOnly) {
            return $bResult;
        } else {
            return $bResult && $this->isAction() && $this->isCondition();
        }
    }

    /**
     * Текущий экшен соответсвует заданным экшенам виджета
     * Если экшены виджета не заданы, то всегда возвращается true
     *
     * @return bool
     */
    public function isAction() {

        $aActions = $this->getActions();
        // если экшены не заданны, то соответствует любому экшену
        if (!$aActions) {
            return true;
        }

        $sCurrentAction = strtolower(Router::GetAction());
        $sCurrentEvent = strtolower(Router::GetActionEvent());
        $sCurrentEventName = strtolower(Router::GetActionEventName());
        foreach ($aActions as $sAction => $aEvents) {
            // приводим к виду action=>array(events)
            if (is_int($sAction) && !is_array($aEvents)) {
                $sAction = (string)$aEvents;
                $aEvents = array();
            }
            if ($sAction == $sCurrentAction) {
                if (!$aEvents) {
                    return true;
                }
            }
            foreach ($aEvents as $sEventPreg) {
                if ((substr($sEventPreg, 0, 1) == '/') && preg_match($sEventPreg, $sCurrentEvent)) {
                    // * Это регулярное выражение
                    return true;
                } elseif ((substr($sEventPreg, 0, 1) == '{') && (trim($sEventPreg, '{}') == $sCurrentEventName)) {
                    // * Это имя event'a (именованный евент, если его нет, то совпадает с именем метода евента в экшене)
                    return true;
                } elseif ($sEventPreg == $sCurrentEvent) {
                    // * Это название event`a
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Условия показа виджета
     *
     * @return bool
     */
    public function isCondition() {

        $bResult = true;
        $sCondition = $this->GetCondition();
        if (is_string($sCondition) && $sCondition > '') {
            try {
                extract($this->GetParams(), EXTR_SKIP);
                $bResult = (bool)eval('return ' . $sCondition . ';');
            } catch (Exceprion $oException) {
                $bResult = false;
            }
        }
        if ($bResult && ($sVisitors = $this->GetVisitors())) {
            if ($sVisitors == 'users') {
                $bResult = E::IsUser();
            } elseif ($sVisitors == 'admins') {
                $bResult = E::IsAdmin();
            }
        }
        return $bResult;
    }

    /**
     * Кому показывать виджет
     *
     * @return mixed|null
     */
    public function GetVisitors() {

        $sVisitors = $this->getProp('visitors');
        return $sVisitors;
    }
}

// EOF