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
     * Массив правил организации LS-блоков
     *
     * @var array
     */
    protected $aBlockRules = array();

    protected $aTemplatesLsMap = array(

        'commons/common.blog_list.tpl' => 'blog_list.tpl',
        'commons/common.editor.tpl' => 'editor.tpl',
        'commons/common.header_top.tpl' => 'header_top.tpl',
        'commons/common.infobox_blog.tpl' => 'infobox.info.blog.tpl',
        'commons/common.messages.tpl' => 'system_message.tpl',
        'commons/common.pagination.tpl' => 'paging.tpl',
        'commons/common.sharer.tpl' => 'sharer.tpl',
        'commons/common.sidebar.tpl' => 'sidebar.tpl',
        'commons/common.statistics_performance.tpl' => 'statistics_performance.tpl',
        'commons/common.toolbar.tpl' => 'toolbar.tpl',
        'commons/common.user_list.tpl' => 'user_list.tpl',
        'commons/common.user_list_avatar.tpl' => 'user_list_avatar.tpl',

        'menus/menu.main_pages.tpl' => 'page_main_menu.tpl',

        'actions/ActionTalk/message.tpl' => 'actions/ActionTalk/read.tpl',
        'actions/talk/action.talk.message.tpl' => 'actions/ActionTalk/read.tpl',
        'actions/ActionProfile/info.tpl' => 'actions/ActionProfile/whois.tpl',
        'actions/profile/action.profile.info.tpl' => 'actions/ActionProfile/whois.tpl',
        'actions/stream/action.stream.follow.tpl' => 'actions/ActionStream/user.tpl',

        'topics/topic.list.tpl' => 'topic_list.tpl',

        'comments/comment.single.tpl' => 'comment.tpl',
        'comments/comment.list.tpl' => 'comment_list.tpl',
        'comments/comment.tree.tpl' => 'comment_tree.tpl',
        'comments/comment.pagination.tpl' => 'comment_paging.tpl',
    );

    protected $aTemplatesAutocreate = array(
        'header.tpl',
        'footer.tpl',
    );

    protected $aAdaptedSkins = array('fortune', 'crisp');

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

        $aResult = $this->DefineWidgetType($sName, $sDir);
        return isset($aResult['type']) ? $aResult['type'] : '';
    }

    /**
     * DEPRECATED
     */
    protected function SortBlocks() {

        return $this->SortWidgets();
    }

    public function DefineWidgetType($sName, $sDir = null, $sPlugin = null) {

        if (strpos($sName, 'widgets/widget.') === 0) {
            $sLsBlockName = str_replace('widgets/widget.', 'blocks/block.', $sName);
            if ($sLsBlockName = $this->TemplateExists(is_null($sDir) ? $sLsBlockName : rtrim($sDir, '/') . '/' . ltrim($sLsBlockName, '/'))) {
                // Если найден шаблон, то считаем, что это шаблонный LS-block
                return array('type' => 'template', 'name' => $sLsBlockName);
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

    /**
     * @param string $sType
     * @param string $sName
     * @param string $sContent
     * @param int    $iTimestamp
     * @param object $oSmarty
     *
     * @return string|void
     */
    public function SmartyDefaultTemplateHandler($sType, $sName, &$sContent, &$iTimestamp, $oSmarty) {

        $sSkin = $this->GetConfigSkin();
        $sResult = parent::SmartyDefaultTemplateHandler($sType, $sName, $sContent, $iTimestamp, $oSmarty);
        if (!$sResult) {
            if ($sType == 'file') {
                if (in_array($sName, $this->aTemplatesAutocreate)) {
                    $sResult = $this->_autocreateOldTemplate($sName);
                    if ($sResult) {
                        $this->_setTemplatePath($sSkin, $sName, $sResult);
                    }
                    return $sResult;
                }

                if ((strpos($sName, 'widgets/widget.') === 0)) {
                    $sFile = Config::Get('path.smarty.template') . str_replace('widgets/widget.', 'blocks/block.', $sName);
                    if (F::File_Exists($sFile)) {
                        if ($sFile) {
                            $this->_setTemplatePath($sSkin, $sName, $sFile);
                        }
                        return $sFile;
                    }
                } elseif (($sName == 'actions/ActionContent/add.tpl') || ($sName == 'actions/content/action.content.add.tpl') || ($sName == 'actions/content/action.content.edit.tpl')) {
                    $sResult = Config::Get('path.smarty.template') . '/actions/ActionContent/add.tpl';
                    if (!is_file($sResult)) {
                        $sResult = Config::Get('path.smarty.template') . '/actions/ActionTopic/add.tpl';
                    }
                    if (!in_array(Config::Get('view.skin'), $this->aAdaptedSkins)) {
                        E::ModuleHook()->AddExecFunction('template_form_add_topic_topic_end', array($this, 'TemplateFormAddTopic'));
                    }
                    $oContentType = $oSmarty->getTemplateVars('oContentType');
                    $oType = $oSmarty->getTemplateVars('oType');
                    if (!$oType && $oContentType) {
                        $oSmarty->assign('oType', $oContentType);
                    }
                } elseif ((strpos($sName, 'forms/view_field') === 0) || (strpos($sName, 'forms/form_field') === 0)) {
                    $sResult = Plugin::GetTemplateDir('PluginLs') . $sName;
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

                if (!$sResult && isset($this->aTemplatesLsMap[$sName])) {
                    $sLsTemplate = $this->aTemplatesLsMap[$sName];
                    $sResult = $this->TemplateExists($sLsTemplate);
                    if ($sResult) {
                        $sResult = F::File_Exists($sLsTemplate, $this->oSmarty->getTemplateDir());
                    } else {
                        $sResult = parent::SmartyDefaultTemplateHandler($sType, $sLsTemplate, $sContent, $iTimestamp, $oSmarty);
                    }
                }

                if (!$sResult) {
                    if (preg_match('~^(tpls/)?actions/([^/]+)/action\.(\w+)\.(.+)$~', $sName, $aMatches)) {
                        $sLsTemplate = 'actions/Action' . ucfirst($aMatches[2]) . '/' . $aMatches[4];
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
                    } elseif ($nPos = strpos($sName, '/emails/ru/email.')) {
                        if (strpos(substr($sName, $nPos + 17), '/')) {
                            $sLsTemplate = str_replace('/emails/ru/email.', '/notify/russian/', $sName);
                        } else {
                            $sLsTemplate = str_replace('/emails/ru/email.', '/notify/russian/notify.', $sName);
                        }
                        if (F::File_Exists($sLsTemplate)) {
                            $sResult = $sLsTemplate;
                        } else {
                            $sResult = $this->SmartyDefaultTemplateHandler($sType, $sLsTemplate, $sContent, $iTimestamp, $oSmarty);
                        }
                    } elseif ($nPos = strpos($sName, '/notify/ru/email.notify.')) {
                        $sLsTemplate = str_replace('/notify/ru/email.notify.', '/notify/russian/notify.', $sName);
                        if (F::File_Exists($sLsTemplate)) {
                            $sResult = $sLsTemplate;
                        } else {
                            $sResult = $this->SmartyDefaultTemplateHandler($sType, $sLsTemplate, $sContent, $iTimestamp, $oSmarty);
                        }
                    } elseif ($nPos = strpos($sName, '/notify/ru/email.shop/')) {
                        $sLsTemplate = str_replace('/notify/ru/email.shop/', '/notify/russian/shop/', $sName);
                        if (F::File_Exists($sLsTemplate)) {
                            $sResult = $sLsTemplate;
                        } else {
                            $sResult = $this->SmartyDefaultTemplateHandler($sType, $sLsTemplate, $sContent, $iTimestamp, $oSmarty);
                        }
                    } elseif ($nPos = strpos($sName, '/email.')) {
                        $sLsTemplate = substr($sName, 0, $nPos) . '/notify.' . substr($sName, $nPos + 7);
                        if (F::File_Exists($sLsTemplate)) {
                            $sResult = $sLsTemplate;
                        } else {
                            $sResult = $this->SmartyDefaultTemplateHandler($sType, $sLsTemplate, $sContent, $iTimestamp, $oSmarty);
                        }
                    } elseif (preg_match('#^menus\/menu\.((content)?\-)?([\w\-\.]+)\.tpl$#i', $sName, $aMatches)) {
                        if (!$aMatches[2]) {
                            $sLsTemplate = 'menu.' . $aMatches[3] . '.tpl';
                        } else {
                            $sLsTemplate = 'menu.' . $aMatches[3] . '.content.tpl';
                        }
                        if ($this->TemplateExists($sLsTemplate, false)) {
                            $sResult = F::File_Exists($sLsTemplate, $this->oSmarty->getTemplateDir());
                        } else {
                            $sResult = $this->SmartyDefaultTemplateHandler($sType, $sLsTemplate, $sContent, $iTimestamp, $oSmarty);
                        }
                    }
                }

                if (!$sResult && ($sNewTemplate = array_search($sName, $this->aTemplatesLsMap))) {
                    $sResult = $this->TemplateExists($sNewTemplate);
                    if ($sResult) {
                        $sResult = F::File_Exists($sNewTemplate, $this->oSmarty->getTemplateDir());
                    } else {
                        $sResult = parent::SmartyDefaultTemplateHandler($sType, $sNewTemplate, $sContent, $iTimestamp, $oSmarty);
                    }
                }
            }
        }

        if ($sResult) {
            $this->_setTemplatePath($sSkin, $sName, $sResult);
        }

        return $sResult;
    }

    /**
     * @param string $sTplName
     *
     * @return string|bool
     */
    protected function _autocreateOldTemplate($sTplName) {

        $sOldTemplatesDir = $this->_getAutocreateOldTemplatesDir();
        if ($sFile = F::File_Exists($sTplName, $sOldTemplatesDir)) {
            return $sFile;
        }

        $aSourceTemplatesDir = array(Config::Get('path.skin.dir') . '/themes/default/layouts');
        if ($sTplName == 'header.tpl' || $sTplName == 'footer.tpl') {
            if ($sSourceFile = F::File_Exists('default.tpl', $aSourceTemplatesDir)) {
                $sSource = F::File_GetContents($sSourceFile);
                $sStr1 = "{hook run='content_begin'}";
                $sStr2 = "{hook run='content_end'}";
                if (stripos($sSource, '<!DOCTYPE') !== false) {
                    $iPos1 = stripos($sSource, $sStr1);
                    $iPos2 = stripos($sSource, $sStr2);
                    $sHeaderSrc = $this->_clearUnclosedBlocks(substr($sSource, 0, $iPos1 + strlen($sStr1)));
                    $sFooterSrc = $this->_clearUnclosedBlocks(substr($sSource, $iPos2));
                    F::File_PutContents($sOldTemplatesDir . 'header.tpl', $sHeaderSrc);
                    F::File_PutContents($sOldTemplatesDir . 'footer.tpl', $sFooterSrc);
                }

            }
        }
        if ($sFile = F::File_Exists($sTplName, $sOldTemplatesDir)) {
            return $sFile;
        }
        return false;
    }

    protected function _clearUnclosedBlocks($sSource) {

        if (preg_match_all('/{block\s+[^}]+}|{\/block}/siu', $sSource, $aM, PREG_OFFSET_CAPTURE)) {
            $iOffset = 0;
            $aOpenBlocks = array();
            $aCloseBlocks = array();
            foreach($aM[0] as $aBlockTag) {
                if (strpos($aBlockTag[0], '/block')) {
                    // close tag
                    if (count($aOpenBlocks)) {
                        array_pop($aOpenBlocks);
                    } else {
                        $aBlockTag['len'] = strlen($aBlockTag[0]);
                        $aCloseBlocks[] = $aBlockTag;
                    }
                } else {
                    // open tag
                    $aBlockTag['len'] = strlen($aBlockTag[0]);
                    $aOpenBlocks[] = $aBlockTag;
                }
            }
            foreach ($aOpenBlocks as $aBlockTag) {
                $sSource = substr_replace($sSource, '', $aBlockTag[1] + $iOffset, $aBlockTag['len']);
                $iOffset -= strlen($aBlockTag[0]);
            }
            foreach ($aCloseBlocks as $aBlockTag) {
                $sSource = substr_replace($sSource, '', $aBlockTag[1] + $iOffset, $aBlockTag['len']);
                $iOffset -= strlen($aBlockTag[0]);
            }
        }
        return $sSource;
    }

    protected function _getAutocreateOldTemplatesDir() {

        $sSubdir = '/ls-src/';
        $sDir = str_replace('/compiled/', $sSubdir, $this->oSmarty->getCompileDir());
        if (!strpos($sDir, $sSubdir)) {
            $sDir .= $sSubdir;
        }
        return $sDir;
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
        $sCompatible = ($oSkin ? $oSkin->GetCompatible() : '');

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