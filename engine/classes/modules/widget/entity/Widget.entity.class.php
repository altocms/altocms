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
 * @method setPriority(int|string $xPriority)
 * @method setName(string $sName)
 * @method setType(string $sType)
 * @method setTemplate(string $sTemplate)
 * @method setOrder(int $iOrder)
 *
 * @method getCondition()
 * @method getPlugin()
 */
class ModuleWidget_EntityWidget extends Entity {

    public function __construct($aParam = null) {

        parent::__construct($aParam);

        if ($sName = $this->getName()) {
            // задается идентификатор виджета
            $this->_checkId();
        }
        if (is_null($this->getPriority())) {
            $this->setPriority(0);
        }
        if ($this->getId()) {
            $aCfgData = Config::Get('widget.' . $this->getId() . '.config');
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
            $aTypeData = E::ModuleViewer()->DefineWidgetType($sName, $this->getDir(), $this->getPluginId());
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
            $sId = $this->getHash();
            $this->setProp('id', $sId);
        }
        return $this->getProp('id');
    }

    /**
     * @param string $sKey
     * @param mixed|null $xDefault
     *
     * @return mixed|null
     */
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

    /**
     * @param string $sLabel
     * @param string $sDate
     */
    protected function _setDisplay($sLabel, $sDate) {

        $sDate = date('Y-m-d', strtotime($sDate));
        $aData = $this->getDisplay();
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
     * @param string $sDate
     */
    public function SetDisplayFrom($sDate) {

        $this->_setDisplay('date_from', $sDate);
    }

    /**
     * До какой даты показывать виджет
     *
     * @param string $sDate
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
    public function setParam($sKey, $xVal) {

        $aParams = $this->getParams();
        $aParams[$sKey] = $xVal;
        $this->SetParams($aParams);
    }

    /**
     * Sets widget parameters
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
    public function getParams() {

        return (array)parent::getProp('params');
    }

    /**
     * Returns widget parameter by key
     *
     * @param string $sKey
     *
     * @return mixed
     */
    public function getParam($sKey) {

        $aParams = $this->getParams();
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
    public function getPriority() {

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
    public function getOrder() {

        return intval($this->getProp('order'));
    }

    /**
     * Returns widget's ID. If ID does not exist it will be created
     *
     * @return string
     */
    public function getId() {

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
    public function getGroup() {

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
    public function getHash() {

        return md5($this->getPluginId() . '.' . $this->getName());
    }

    /**
     * Returns plugin ID
     *
     * @return mixed|null
     */
    public function getPluginId() {

        $sResult = $this->getProp('plugin');
        if (is_null($sResult)) {
            /* LS-compatible */
            $sResult = $this->getParam('plugin');
        }
        return $sResult;
    }

    /**
     * Returns dir of template widget
     *
     * @return mixed
     */
    public function getDir() {

        $sDir = $this->getProp('_dir');
        if (is_null($sDir)) {
            $sDir = $this->getParam('dir');
            if ($sPlugin = $this->getPluginId()) {
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
    public function getIncludePaths() {

        $xResult = $this->getPropArray('on');

        return $xResult;
    }

    /**
     * Returns exclude paths
     *
     * @return mixed
     */
    public function getExcludePaths() {

        $xResult = $this->getPropArray('off');

        return $xResult;
    }

    /**
     * Returns type of widget
     *
     * @return mixed|null
     */
    public function getType() {

        $sType = $this->getProp('type');
        if (is_null($sType) && ($sName = $this->getName())) {
            $aTypeData = E::ModuleViewer()->DefineWidgetType($sName, $this->getDir(), $this->getPluginId());
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
    public function getName() {

        return $this->getProp('name');
    }

    /**
     * Returns property 'action'
     *
     * @return array
     */
    public function getActions() {

        return (array)$this->getProp('action');
    }

    /**
     * Returns property 'display'
     *
     * @return mixed|null
     */
    public function getDisplay() {

        return $this->getProp('display', true);
    }

    /**
     * Период действия виджета
     */
    public function getPeriod() {

        $xData = $this->getDisplay();
        if (is_array($xData)) {
            return $xData;
        }
        return null;
    }

    public function getDateFrom() {

        $aData = $this->getPeriod();
        if (isset($aData['date_from'])) {
            return $aData['date_from'];
        }
        return null;
    }

    public function getDateUpto() {

        $aData = $this->getPeriod();
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

        return $this->getPriority() === 'top';
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
            $xDisplay = $this->getDisplay();
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
            $sCondition = $this->getCondition();
            if (is_string($sCondition) && $sCondition > '') {
                try {
                    extract($this->getParams(), EXTR_SKIP);
                    $bResult = (bool)eval('return ' . $sCondition . ';');
                } catch (Exception $oException) {
                    $bResult = false;
                }
            }
            if ($bResult && ($sVisitors = $this->getVisitors())) {
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
    public function getVisitors() {

        $sVisitors = $this->getProp('visitors');
        return $sVisitors;
    }
}

// EOF