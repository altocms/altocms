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
 * Class ModuleWidget_EntityWidget
 *
 * @method setPriority($xPriority)
 * @method setName($sName)
 * @method setType($sType)
 * @method setTemplate($sTemplate)
 * @method setOrder($iOrder)
 *
 * @method getCondition()
 * @method GetTemplate()
 */
class ModuleWidget_EntityWidget extends Entity {

    public function __construct($aParam = null) {

        parent::__construct($aParam);

        if ($sName = $this->GetName()) {
            // задается идентификатор виджета
            $this->_checkId();
        }
        if (is_null($this->GetPriority())) {
            $this->SetPriority(0);
        }
        if ($this->GetId()) {
            $aCfgData = Config::Get('widget.' . $this->GetId() . '.config');
            if ($aCfgData) {
                $aCfgData = F::Array_Merge($this->getAllProps(), $aCfgData);
                $this->setProps($aCfgData);
            }
        }

        /* LS-compatible */
        if (!$this->getParam('plugin') && ($sPluginId = $this->getPluginId())) {
            $this->setParam('plugin', $sPluginId);
        }

        if ($sName && is_null($this->getType())) {
            $aTypeData = E::ModuleViewer()->DefineWidgetType($sName, $this->GetDir(), $this->GetPluginId());
            if (isset($aTypeData['type'])) {
                $this->setType($aTypeData['type']);

                if ($aTypeData['type'] == 'template' && !empty($aTypeData['name'])) {
                    $this->setTemplate($aTypeData['name']);
                    $this->setName($aTypeData['name']);
                }
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
        }
        return $this->getProp('id');
    }

    public function getProp($sKey, $xDefault = null) {

        if (parent::isProp($sKey)) {
            return parent::getProp($sKey, $xDefault);
        }
        $xResult = $this->getParam($sKey);
        return !is_null($xResult) ? $xResult : $xDefault;
    }

    /**
     * Преобразует значение свойства в массив
     *
     * @param string $sKey
     * @param string $sSeparateChar
     *
     * @return array
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
     * @param string  $sKey
     * @param mixed   $xVal
     */
    public function SetParam($sKey, $xVal) {

        $aParams = $this->GetParams();
        $aParams[$sKey] = $xVal;
        $this->SetParams($aParams);
    }

    /**
     * Sets widget parametrs
     *
     * @param array $xVal
     */
    public function SetParams($xVal) {

        $this->setProp('params', (array)$xVal);
    }


    /**
     * Returns widget parameters
     *
     * @return array
     */
    public function GetParams() {

        return (array)parent::getProp('params');
    }

    /**
     * Returns widget parameter by key
     *
     * @param string $sKey
     *
     * @return mixed
     */
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

    /**
     * Returns hash of widget
     *
     * @return string
     */
    public function GetHash() {

        return md5($this->GetPluginId() . '.' . $this->GetName());
    }

    /**
     * Returns plugin ID
     *
     * @return mixed|null
     */
    public function GetPluginId() {

        $sResult = $this->getProp('plugin');
        if (is_null($sResult)) {
            /* LS-compatible */
            $sResult = $this->GetParam('plugin');
        }
        return $sResult;
    }

    /**
     * Returns dir of template widget
     *
     * @return mixed
     */
    public function GetDir() {

        $sDir = $this->getProp('_dir');
        if (is_null($sDir)) {
            $sDir = $this->GetParam('dir');
            if ($sPlugin = $this->GetPluginId()) {
                $sDir = F::File_NormPath(Plugin::GetTemplateDir($sPlugin) . '/' . $sDir);
            }
            $this->setProp('_dir', $sDir);
        }
        return $sDir;
    }

    /**
     * Returns include paths
     *
     * @return mixed
     */
    public function GetIncludePaths() {

        $xResult = $this->getPropArray('on');

        return $xResult;
    }

    /**
     * Returns exclude paths
     *
     * @return mixed
     */
    public function GetExcludePaths() {

        $xResult = $this->getPropArray('off');

        return $xResult;
    }

    /**
     * Returns type of widget
     *
     * @return mixed|null
     */
    public function GetType() {

        $sType = $this->getProp('type');
        if (is_null($sType) && ($sName = $this->getName())) {
            $aTypeData = E::ModuleViewer()->DefineWidgetType($sName, $this->GetDir(), $this->GetPluginId());
            if (isset($aTypeData['type'])) {
                $sType = $aTypeData['type'];
                $this->setType($sType);

                if ($aTypeData['type'] == 'template' && !empty($aTypeData['name'])) {
                    $this->setTemplate($aTypeData['name']);
                    $this->setName($aTypeData['name']);
                }
                $this->setProp('type', $sType);
            }
        }
        return $sType;
    }


    public function getTemplate() {

        $sTemplate = $this->getProp('template');
        if (is_null($sTemplate) && ($this->getType() === 'template')) {
            if ($this->getPlugin()) {
                $sTemplate = Plugin::GetTemplateFile($this->getPlugin(), $this->getName());
            } else {
                $sTemplate = $this->getName();
            }
            $this->setTemplate($sTemplate);
        }

        return $sTemplate;
    }

    /**
     * Returns name of widget
     *
     * @return mixed|null
     */
    public function GetName() {

        return $this->getProp('name');
    }

    /**
     * Returns property 'action'
     *
     * @return array
     */
    public function GetActions() {

        return (array)$this->getProp('action');
    }

    /**
     * Returns property 'display'
     *
     * @return mixed|null
     */
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

        $sPropKey = '_is_display_' . ($bCheckDateOnly ? '1' : '0');
        $bResult = $this->getProp($sPropKey);
        if (is_null($bResult)) {
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
            if (!$bCheckDateOnly) {
                $bResult = ($bResult && $this->isAction() && $this->isCondition());
            }
            $this->setProp($sPropKey, $bResult);
        }
        return $bResult;
    }

    /**
     * Текущий экшен соответсвует заданным экшенам виджета
     * Если экшены виджета не заданы, то всегда возвращается true
     *
     * @return bool
     */
    public function isAction() {

        $bResult = $this->getProp('_is_action');
        if (is_null($bResult)) {
            $aActions = $this->getActions();
            if (!$aActions) {
                return true;
            }
            $bResult = R::AllowAction($aActions);
            $this->setProp('_is_action', $bResult);
        }

        return $bResult;
    }

    /**
     * Условия показа виджета
     *
     * @return bool
     */
    public function isCondition() {

        $bResult = $this->getProp('_is_condition');
        if (is_null($bResult)) {
            $bResult = true;
            $sCondition = $this->GetCondition();
            if (is_string($sCondition) && $sCondition > '') {
                try {
                    extract($this->GetParams(), EXTR_SKIP);
                    $bResult = (bool)eval('return ' . $sCondition . ';');
                } catch (Exception $oException) {
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
            $this->setProp('_is_condition', $bResult);
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