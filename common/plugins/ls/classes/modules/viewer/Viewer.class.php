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
 * Добавляет старые LS-методы для совместимости
 */
class PluginLs_ModuleViewer extends PluginLs_Inherit_ModuleViewer {
    /**
     * DEPRECATED
     */
    public function GetBlocks($bSort = false) {

        $aWidgets = $this->GetWidgets($bSort);
        $aBlocks = array();
        foreach ($aWidgets as $sGroup=>$aWidgetList) {
            foreach($aWidgetList as $oWidget) {
                $aParams = $oWidget->getParams();
                if ($sPlugin = $oWidget->GetPluginId()) {
                    if (!isset($aParams['plugin'])) $aParams['plugin'] = $sPlugin;
                }
                $aBlocks[$sGroup][]=array(
                    'type'     => ($oWidget->getType() == 'exec' ? 'block' : $oWidget->getType()),
                    'name'     => $oWidget->getName(),
                    'params'   => $oWidget->getParams(),
                    'priority' => $oWidget->getPriority(),
                );
            }
        }
        return $aBlocks;
    }

    /**
     * DEPRECATED
     */
    protected function DefineTypeBlock($sName, $sDir = null) {

        return $this->DefineWidgetType($sName, $sDir);
    }

    /**
     * DEPRECATED
     */
    protected function SortBlocks() {

        return $this->SortWidgets();
    }

    protected function DefineWidgetType(&$sName, $sDir = null, $sPlugin = null) {

        if (strpos($sName, 'widgets/widget.') === 0) {
            $sLsBlockName = str_replace('widgets/widget.', 'blocks/block.', $sName);
            if ($sLsBlockName = $this->TemplateExists(is_null($sDir) ? $sLsBlockName : rtrim($sDir, '/') . '/' . ltrim($sLsBlockName, '/'))) {
                // Если найден шаблон, то считаем, что это шаблонный LS-block
                $sName = $sLsBlockName;
                return 'template';
            }
        }
        return parent::DefineWidgetType($sName, $sDir, $sPlugin);
    }

    /**
     * Compatibility with LS-styled templates
     *
     * @param string $sTemplate
     * @param bool   $bException
     *
     * @return mixed
     */
    public function TemplateExists($sTemplate, $bException = false) {

        $sResult = parent::TemplateExists($sTemplate, false);
        if (!$sResult && preg_match('~^actions/([^/]+)/action\.(\w+)\.(.+)$~', $sTemplate, $aMatches)) {
            $sLsTemplate = 'actions/Action' . ucfirst($aMatches[1]) . '/' . $aMatches[3];
            $sResult = parent::TemplateExists($sLsTemplate, false);
        }
        if (!$sResult && $bException) {
            $sResult = parent::TemplateExists($sTemplate, $bException);
        }
        return $sResult;
    }

    public function VarAssign() {

        parent::VarAssign();

        // В Alto CMS по умолчанию используется Smarty-переменная $aWidgets
        $this->Assign('aBlocks', $this->GetBlocks(true));

        // В Smarty 3.x рекомендуется использовать статический класс Config
        $this->Assign('oConfig', Config::getInstance());

        // * Short Engine aliases
        $this->Assign('LS', LS::getInstance());
    }

    protected function InitBlockParams() {

        return $this->InitWidgetParams();
    }

    public function AddBlock($sGroup, $sName, $aParams = array(), $iPriority = 5) {

        return $this->AddWidget($sGroup, $sName, $aParams, $iPriority);
    }

    public function AddBlocks($sGroup, $aBlocks, $ClearWidgets = true) {

        return $this->AddWidgets($sGroup, $aBlocks, $ClearWidgets);
    }

    public function ClearBlocks($sGroup) {

        return $this->ClearWidgets($sGroup);
    }

    public function ClearBlocksAll() {

        return $this->ClearAllWidgets();
    }

    protected function BuildBlocks() {

        $sAction = strtolower(Router::GetAction());
        $sEvent = strtolower(Router::GetActionEvent());
        $sEventName = strtolower(Router::GetActionEventName());
        foreach ($this->aBlockRules as $sName => $aRule) {
            $bUse = false;
            /**
             * Если в правиле не указан список блоков, нам такое не нужно
             */
            if (!array_key_exists('blocks', $aRule)) continue;
            /**
             * Если не задан action для исполнения и нет ни одного шаблона path,
             * или текущий не входит в перечисленные в правиле
             * то выбираем следующее правило
             */
            if (!array_key_exists('action', $aRule) && !array_key_exists('path', $aRule)) continue;
            if (isset($aRule['action'])) {
                if (in_array($sAction, (array)$aRule['action'])) $bUse = true;
                if (array_key_exists($sAction, (array)$aRule['action'])) {
                    /**
                     * Если задан список event`ов и текущий в него не входит,
                     * переходи к следующему действию.
                     */
                    foreach ((array)$aRule['action'][$sAction] as $sEventPreg) {
                        if (substr($sEventPreg, 0, 1) == '/') {
                            /**
                             * Это регулярное выражение
                             */
                            if (preg_match($sEventPreg, $sEvent)) {
                                $bUse = true;
                                break;
                            }
                        } elseif (substr($sEventPreg, 0, 1) == '{') {
                            /**
                             * Это имя event'a (именованный евент, если его нет, то совпадает с именем метода евента в экшене)
                             */
                            if (trim($sEventPreg, '{}') == $sEventName) {
                                $bUse = true;
                                break;
                            }
                        } else {
                            /**
                             * Это название event`a
                             */
                            if ($sEvent == $sEventPreg) {
                                $bUse = true;
                                break;
                            }
                        }
                    }
                }
            }
            /**
             * Если не найдено совпадение по паре Action/Event,
             * переходим к поиску по regexp путей.
             */
            if (!$bUse && isset($aRule['path'])) {
                $sPath = rtrim(Router::GetPathWebCurrent(), '/');
                /**
                 * Проверяем последовательно каждый regexp
                 */
                foreach ((array)$aRule['path'] as $sRulePath) {
                    $sPattern = '~' . str_replace(array('/', '*'), array('\/', '[\w\-]+'), $sRulePath) . '~';
                    if (preg_match($sPattern, $sPath)) {
                        $bUse = true;
                        break 1;
                    }
                }

            }

            if ($bUse) {
                /**
                 * Если задан режим очистки блоков, сначала чистим старые блоки
                 */
                if (isset($aRule['clear'])) {
                    switch (true) {
                        /**
                         * Если установлен в true, значит очищаем все
                         */
                        case  ($aRule['clear'] === true):
                            $this->ClearBlocksAll();
                            break;

                        case is_string($aRule['clear']):
                            $this->ClearBlocks($aRule['clear']);
                            break;

                        case is_array($aRule['clear']):
                            foreach ($aRule['clear'] as $sGroup) {
                                $this->ClearBlocks($sGroup);
                            }
                            break;
                    }
                }
                /**
                 * Добавляем все блоки, указанные в параметре blocks
                 */
                foreach ($aRule['blocks'] as $sGroup => $aBlocks) {
                    foreach ((array)$aBlocks as $sName => $aParams) {
                        /**
                         * Если название блока указывается в параметрах
                         */
                        if (is_int($sName)) {
                            if (is_array($aParams)) {
                                $sName = $aParams['block'];
                            }
                        }
                        /**
                         * Если $aParams не являются массивом, значит передано только имя блока
                         */
                        if (!is_array($aParams)) {
                            $this->AddBlock($sGroup, $aParams);
                        } else {
                            $this->AddBlock(
                                $sGroup, $sName,
                                isset($aParams['params']) ? $aParams['params'] : array(),
                                isset($aParams['priority']) ? $aParams['priority'] : 5
                            );
                        }
                    }
                }
            }
        }
    }

    public function SmartyDefaultTemplateHandler($sType, $sName, &$sContent, &$iTimestamp, $oSmarty) {

        $sResult = parent::SmartyDefaultTemplateHandler($sType, $sName, $sContent, $iTimestamp, $oSmarty);
        if (!$sResult) {
            if ($sType == 'file') {
                if ((strpos($sName, 'widgets/widget.') === 0)) {
                    $sFile = Config::Get('path.smarty.template') . str_replace('widgets/widget.', 'blocks/block.', $sName);
                    if (F::File_Exists($sFile)) {
                        return $sFile;
                    }
                } elseif (($sName == 'actions/ActionContent/add.tpl') || ($sName == 'actions/content/action.content.add.tpl') || ($sName == 'actions/content/action.content.edit.tpl')) {
                    $sResult = Config::Get('path.smarty.template') . '/actions/ActionContent/add.tpl';
                    if (!is_file($sResult)) {
                        $sResult = Config::Get('path.smarty.template') . '/actions/ActionTopic/add.tpl';
                    }
                    $this->Hook_AddExecFunction('template_form_add_topic_topic_end', array($this, 'TemplateFormAddTopic'));
                } elseif ((strpos($sName, 'forms/view_field') === 0) || (strpos($sName, 'forms/form_field') === 0)) {
                    $sResult = Plugin::GetTemplateDir('PluginLs') . $sName;
                } elseif (($sName == 'actions/ActionProfile/info.tpl') || ($sName == 'actions/profile/action.profile.info.tpl')) {
                    $sResult = $this->TemplateExists('actions/ActionProfile/whois.tpl');
                    if ($sResult) {
                        $sResult = F::File_Exists('actions/ActionProfile/whois.tpl', $this->oSmarty->getTemplateDir());
                    } else {
                        $sResult = parent::SmartyDefaultTemplateHandler($sType, 'actions/ActionProfile/whois.tpl', $sContent, $iTimestamp, $oSmarty);
                    }
                } elseif (($sName == 'actions/ActionTalk/message.tpl') || ($sName == 'actions/talk/action.talk.message.tpl')) {
                    $sResult = $this->TemplateExists('actions/ActionTalk/read.tpl');
                    if ($sResult) {
                        $sResult = F::File_Exists('actions/ActionTalk/read.tpl', $this->oSmarty->getTemplateDir());
                    } else {
                        $sResult = parent::SmartyDefaultTemplateHandler($sType, 'actions/ActionTalk/read.tpl', $sContent, $iTimestamp, $oSmarty);
                    }
                } elseif ($sName == 'actions/page/action.page.show.tpl') {
                    if ($this->TemplateExists('actions/page/action.page.page.tpl')) {
                        $sResult = F::File_Exists('actions/page/action.page.page.tpl', $this->oSmarty->getTemplateDir());
                    }
                    if( !$sResult && $this->TemplateExists('actions/ActionPage/page.tpl')) {
                        $sResult = F::File_Exists('actions/ActionPage/page.tpl', $this->oSmarty->getTemplateDir());
                    } else {
                        $sResult = parent::SmartyDefaultTemplateHandler($sType, 'actions/ActionPage/page.tpl', $sContent, $iTimestamp, $oSmarty);
                    }
                }
                if (!$sResult) {
                    if (preg_match('~^actions/([^/]+)/action\.(\w+)\.(.+)$~', $sName, $aMatches)) {
                        $sLsTemplate = 'actions/Action' . ucfirst($aMatches[1]) . '/' . $aMatches[3];
                        if ($this->TemplateExists($sLsTemplate, false)) {
                            $sResult = F::File_Exists($sLsTemplate, $this->oSmarty->getTemplateDir());
                        } else {
                            $sResult = $this->SmartyDefaultTemplateHandler($sType, $sLsTemplate, $sContent, $iTimestamp, $oSmarty);
                        }
                    } elseif (preg_match('~^topic_(\w+)\.tpl$~', $sName)) {
                        $sLsTemplate = 'topic_topic.tpl';
                        if ($this->TemplateExists($sLsTemplate, false)) {
                            $sResult = F::File_Exists($sLsTemplate, $this->oSmarty->getTemplateDir());
                        } else {
                            $sResult = $this->SmartyDefaultTemplateHandler($sType, $sLsTemplate, $sContent, $iTimestamp, $oSmarty);
                        }
                    } elseif (strpos($sName, '/emails/ru/email.')) {
                        $sLsTemplate = str_replace('/emails/ru/email.', '/notify/russian/notify.', $sName);
                        if (F::File_Exists($sLsTemplate)) {
                            $sResult = $sLsTemplate;
                        } else {
                            $sResult = $this->SmartyDefaultTemplateHandler($sType, $sLsTemplate, $sContent, $iTimestamp, $oSmarty);
                        }
                    }
                }

            }
        }
        return $sResult;
    }

    public function TemplateFormAddTopic() {

        return $this->Fetch(Plugin::GetTemplateDir('PluginLs') . 'inc.form_topic_add_end.tpl');
    }

    protected function _initTemplator() {

        parent::_initTemplator();
        $aDirs = $this->oSmarty->getTemplateDir();
        foreach($aDirs as $sDir) {
            if (basename($sDir) == 'tpls') {
                $sParentDir = dirname($sDir);
                if (!in_array($sParentDir, $aDirs)) {
                    $this->oSmarty->addTemplateDir($sParentDir);
                }
                break;
            }
        }
        $sDir = Plugin::GetDir('PluginLs') . '/classes/modules/viewer/plugs/';
        $this->oSmarty->addPluginsDir($sDir);
    }

    protected function _initSkin() {

        $oSkin = $this->Skin_GetSkin($this->GetConfigSkin());
        $sCompatible = $oSkin->GetCompatible();

        if (!$sCompatible || $sCompatible == 'ls') {
            // It's old LS skin
            $aOldJs = Config::Get('assets.ls.head.default.js');
            $aOldCss = Config::Get('assets.ls.head.default.css');
            Config::Set('head.default.js', $aOldJs);
            Config::Set('head.default.css', $aOldCss);
            Config::Set('view.compatible', 'ls');
        } else {
            Config::Set('view.compatible', $sCompatible ? $sCompatible : 'alto');
        }
        parent::_initSkin();
    }

    public function InitAssetFiles() {

        $aSet = Config::Get('head.default');
        if ($aSet['css']) {
            foreach($aSet['css'] as $nIdx => $sName) {
                if (is_string($sName)) {
                    $sName = str_replace('/engine/libs/external/', '/common/plugins/ls/libs/external/', $sName);
                    $sName = str_replace('/engine/lib/external/', '/common/plugins/ls/libs/external/', $sName);
                    $aSet['css'][$nIdx] = $sName;
                }
            }
        }
        Config::Set('head.default', $aSet);
        parent::InitAssetFiles();
    }

}

// EOF