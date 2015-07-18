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
 * @package actions
 * @since 0.9
 */
class ActionAdmin extends Action {
    /**
     * Текущий пользователь
     *
     * @var ModuleUser_EntityUser|null
     */
    protected $oUserCurrent = null;
    /**
     * Главное меню
     *
     * @var string
     */
    protected $sMainMenuItem = '';

    protected $sMenuItem = '';

    /**
     * Инициализация
     *
     * @return string
     */
    public function Init() {

        if (E::ModuleUser()->IsAuthorization()) {
            $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();
        }

        /**
         * Если нет прав доступа - перекидываем на 404 страницу
         * Но нужно это делать через Router::Location, т.к. Viewer может быть уже инициирован
         */
        if (!$this->oUserCurrent || !$this->oUserCurrent->isAdministrator()) {
            R::Location('error/404/');
        }
        $this->SetDefaultEvent('info-dashboard');
    }

    /**
     * Регистрация евентов
     */
    protected function RegisterEvent() {

        $this->AddEvent('info-dashboard', 'EventDashboard');
        $this->AddEvent('info-report', 'EventReport');
        $this->AddEvent('info-phpinfo', 'EventPhpinfo');

        $this->AddEvent('content-pages', 'EventPages');
        $this->AddEvent('content-blogs', 'EventBlogs');
        $this->AddEvent('content-topics', 'EventTopics');
        $this->AddEvent('content-comments', 'EventComments');
        $this->AddEvent('content-mresources', 'EventMresources');

        $this->AddEvent('users-list', 'EventUsers');
        $this->AddEvent('users-banlist', 'EventBanlist');
        $this->AddEvent('users-invites', 'EventInvites');

        $this->AddEvent('settings-site', 'EventConfig');
        $this->AddEvent('settings-lang', 'EventLang');
        $this->AddEvent('settings-blogtypes', 'EventBlogTypes');
        $this->AddEvent('settings-userrights', 'EventUserRights');
        $this->AddEvent('settings-userfields', 'EventUserFields');
        $this->AddEvent('settings-menumanager', 'EventMenuManager');

        $this->AddEvent('site-skins', 'EventSkins');
        $this->AddEvent('site-widgets', 'EventWidgets');
        $this->AddEvent('site-plugins', 'EventPlugins');

        $this->AddEvent('logs-error', 'EventLogs');
        $this->AddEvent('logs-sqlerror', 'EventLogs');
        $this->AddEvent('logs-sqllog', 'EventLogs');

        $this->AddEvent('tools-reset', 'EventReset');
        $this->AddEvent('tools-commentstree', 'EventCommentsTree');
        $this->AddEvent('tools-recalcfavourites', 'EventRecalculateFavourites');
        $this->AddEvent('tools-recalcvotes', 'EventRecalculateVotes');
        $this->AddEvent('tools-recalctopics', 'EventRecalculateTopics');
        if (C::Get('rating.enabled')) {
            $this->AddEvent('tools-recalcblograting', 'EventRecalculateBlogRating');
        }
        $this->AddEvent('tools-checkdb', 'EventCheckDb');

        //поля контента
        $this->AddEvent('settings-contenttypes', 'EventContentTypes');

        $this->AddEvent('settings-contenttypes-fieldadd', 'EventAddField');
        $this->AddEvent('settings-contenttypes-fieldedit', 'EventEditField');
        $this->AddEvent('settings-contenttypes-fielddelete', 'EventDeleteField');

        $this->AddEvent('ajaxchangeordertypes', 'EventAjaxChangeOrderTypes');
        $this->AddEvent('ajaxchangeorderfields', 'EventAjaxChangeOrderFields');

        $this->AddEvent('ajaxvote', 'EventAjaxVote');
        $this->AddEvent('ajaxsetprofile', 'EventAjaxSetProfile');

        $this->AddEventPreg('/^ajax$/i', '/^config$/i', 'EventAjaxConfig');
        $this->AddEventPreg('/^ajax$/i', '/^user$/i', '/^add$/i', 'EventAjaxUserAdd');
        $this->AddEventPreg('/^ajax$/i', '/^user$/i', '/^invite$/i', 'EventAjaxUserList');

        // Аякс для меню
        $this->AddEvent('ajaxchangeordermenu', 'EventAjaxChangeOrderMenu');
        $this->AddEvent('ajaxchangemenutext', 'EventAjaxChangeMenuText');
        $this->AddEvent('ajaxchangemenulink', 'EventAjaxChangeMenuLink');
        $this->AddEvent('ajaxmenuitemremove', 'EventAjaxRemoveItem');
        $this->AddEvent('ajaxmenuitemdisplay', 'EventAjaxDisplayItem');
    }

    /**
     * @param   int         $nParam
     * @param   string      $sDefault
     * @param   array|null  $aAvail
     *
     * @return mixed
     */
    protected function _getMode($nParam = 0, $sDefault, $aAvail = null) {

        $sKey = R::GetAction() . '.' . R::GetActionEvent() . '.' . $nParam;
        $sMode = $this->GetParam($nParam, E::ModuleSession()->Get($sKey, $sDefault));
        if (!is_null($aAvail) && !is_array($aAvail)) $aAvail = array($aAvail);
        if (is_null($aAvail) || ($sMode && in_array($sMode, $aAvail))) {
            $this->_saveMode(0, $sMode);
        }
        return $sMode;
    }

    protected function _saveMode($nParam = 0, $sData) {

        $sKey = R::GetAction() . '.' . R::GetActionEvent() . '.' . $nParam;
        E::ModuleSession()->Set($sKey, $sData);
    }

    protected function _getPageNum($nNumParam = null) {

        $nPage = 1;
        if (!is_null($nNumParam) && preg_match("/^page(\d+)$/i", $this->GetParam(intval($nNumParam)), $aMatch)) {
            $nPage = $aMatch[1];
        } elseif (preg_match("/^page(\d+)$/i", $this->GetLastParam(), $aMatch)) {
            $nPage = $aMatch[1];
        }
        return $nPage;
    }

    /**********************************************************************************
     ************************ РЕАЛИЗАЦИЯ ЭКШЕНА ***************************************
     **********************************************************************************
     */

    public function EventDashboard() {

        $this->sMainMenuItem = 'info';

        $aDashboardWidgets = array(
            'admin_dashboard_updates' => array(
                'name' => 'admin_dashboard_updates',
                'key' => 'admin.dashboard.updates',
                'status' => Config::Val('admin.dashboard.updates', true),
                'label' => E::ModuleLang()->Get('action.admin.dashboard_updates_title')
            ),
            'admin_dashboard_news' => array(
                'name' => 'admin_dashboard_news',
                'key' => 'admin.dashboard.news',
                'status' => Config::Val('admin.dashboard.news', true),
                'label' => E::ModuleLang()->Get('action.admin.dashboard_news_title')
            ),
        );

        if ($this->IsPost('widgets')) {
            $aWidgets = F::Array_FlipIntKeys($this->GetPost('widgets'));
            $aConfig = array();
            foreach ($aDashboardWidgets as $aDashboardWidget) {
                if (isset($aWidgets[$aDashboardWidget['name']])) {
                    $aConfig[$aDashboardWidget['key']] = 1;
                } else {
                    $aConfig[$aDashboardWidget['key']] = 0;
                }
            }
            Config::WriteCustomConfig($aConfig);
            R::Location('admin');
        }
        $this->_setTitle(E::ModuleLang()->Get('action.admin.menu_info_dashboard'));
        $this->SetTemplateAction('info/index');

        $this->sMenuItem = $this->_getMode(0, 'index');

        $aData = array('e-alto' => ALTO_VERSION, 'e-uniq' => E::ModuleSecurity()->GetUniqKey());
        $aPlugins = E::ModulePlugin()->GetPluginsList(true);
        foreach ($aPlugins as $oPlugin) {
            $aData['p-' . $oPlugin->GetId()] = $oPlugin->GetVersion();
        }
        $aSkins = E::ModuleSkin()->GetSkinsList();
        foreach ($aSkins as $oSkin) {
            $aData['s-' . $oSkin->GetId()] = $oSkin->GetVersion();
        }

        E::ModuleViewer()->Assign('sUpdatesRequest', base64_encode(http_build_query($aData)));
        E::ModuleViewer()->Assign('sUpdatesRefresh', true);
        E::ModuleViewer()->Assign('aDashboardWidgets', $aDashboardWidgets);
    }

    /**
     *
     */
    public function EventReport() {

        $this->sMainMenuItem = 'info';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.menu_info'));
        $this->SetTemplateAction('info/report');

        if ($sReportMode = F::GetRequest('report', null, 'post')) {
            $this->_EventReportOut($this->_getInfoData(), $sReportMode);
        }

        E::ModuleViewer()->Assign('aInfoData', $this->_getInfoData());
    }

    protected function _getInfoData() {

        $aPlugins = E::ModulePlugin()->GetList(null, false);
        $aActivePlugins = E::ModulePlugin()->GetActivePlugins();
        $aPluginList = array();
        foreach ($aActivePlugins as $aPlugin) {
            if (is_array($aPlugin)) {
                $sPlugin = $aPlugin['id'];
            } else {
                $sPlugin = (string)$aPlugin;
            }
            if (isset($aPlugins[$sPlugin])) {
                $oPluginEntity = $aPlugins[$sPlugin];
                $sPluginName = $oPluginEntity->GetName();
                $aPluginInfo = array(
                    'item' => $sPlugin,
                    'label' => $sPluginName,
                );
                if ($sVersion = $oPluginEntity->GetVersion()) {
                    $aPluginInfo['value'] = 'v.' . $sVersion;
                }
                $sPluginClass = 'Plugin' . ucfirst($sPlugin);
                if (class_exists($sPluginClass) && method_exists($sPluginClass, 'GetUpdateInfo')) {
                    $oPlugin = new $sPluginClass;
                    $aPluginInfo['.html'] = ' - ' . $oPlugin->GetUpdateInfo();
                }
                $aPluginList[$sPlugin] = $aPluginInfo;
            }
        }

        $aSiteStat = E::ModuleAdmin()->GetSiteStat();
        $sSmartyVersion = E::ModuleViewer()->GetSmartyVersion();

        $aImgSupport = E::ModuleImg()->GetDriversInfo();
        $sImgSupport = '';
        if ($aImgSupport) {
            foreach ($aImgSupport as $sDriver => $sVersion) {
                if ($sImgSupport) {
                    $sImgSupport .= '; ';
                }
                $sImgSupport .= $sDriver . ': ' . $sVersion;
            }
        } else {
            $sImgSupport = 'none';
        }

        $aInfo = array(
            'versions' => array(
                'label' => E::ModuleLang()->Get('action.admin.info_versions'),
                'data' => array(
                    'php' => array('label' => E::ModuleLang()->Get('action.admin.info_version_php'), 'value' => PHP_VERSION,),
                    'img' => array('label' => E::ModuleLang()->Get('action.admin.info_version_img'), 'value' => $sImgSupport,),
                    'smarty' => array('label' => E::ModuleLang()->Get('action.admin.info_version_smarty'), 'value' => $sSmartyVersion ? $sSmartyVersion : 'n/a',),
                    'alto' => array('label' => E::ModuleLang()->Get('action.admin.info_version_alto'), 'value' => ALTO_VERSION,),
                )

            ),
            'site' => array(
                'label' => E::ModuleLang()->Get('action.admin.site_info'),
                'data' => array(
                    'url' => array('label' => E::ModuleLang()->Get('action.admin.info_site_url'), 'value' => Config::Get('path.root.url'),),
                    'skin' => array('label' => E::ModuleLang()->Get('action.admin.info_site_skin'), 'value' => Config::Get('view.skin', Config::LEVEL_CUSTOM),),
                    'client' => array('label' => E::ModuleLang()->Get('action.admin.info_site_client'), 'value' => $_SERVER['HTTP_USER_AGENT'],),
                    'empty' => array('label' => '', 'value' => '',),
                ),
            ),
            'plugins' => array(
                'label' => E::ModuleLang()->Get('action.admin.active_plugins'),
                'data' => $aPluginList,
            ),
            'stats' => array(
                'label' => E::ModuleLang()->Get('action.admin.site_statistics'),
                'data' => array(
                    'users' => array('label' => E::ModuleLang()->Get('action.admin.site_stat_users'), 'value' => $aSiteStat['users'],),
                    'blogs' => array('label' => E::ModuleLang()->Get('action.admin.site_stat_blogs'), 'value' => $aSiteStat['blogs'],),
                    'topics' => array('label' => E::ModuleLang()->Get('action.admin.site_stat_topics'), 'value' => $aSiteStat['topics'],),
                    'comments' => array('label' => E::ModuleLang()->Get('action.admin.site_stat_comments'), 'value' => $aSiteStat['comments'],),
                ),
            ),
        );

        return $aInfo;
    }

    protected function _EventReportOut($aInfo, $sMode = 'txt') {

        E::ModuleSecurity()->ValidateSendForm();
        $sMode = strtolower($sMode);
        $aParams = array(
            'filename' => $sFileName = str_replace(array('.', '/'), '_', str_replace(array('http://', 'https://'), '', Config::Get('path.root.url'))) . '.' . $sMode,
            'date' => F::Now(),
        );

        if ($sMode == 'xml') {
            $this->_reportXml($aInfo, $aParams);
        } else {
            $this->_reportTxt($aInfo, $aParams);
        }
        exit;
    }

    protected function _reportTxt($aInfo, $aParams) {

        $sText = '[report]' . "\n";
        foreach ($aParams as $sKey => $sVal) {
            $sText .= $sKey . ' = ' . $sVal . "\n";
        }
        $sText .= "\n";

        foreach ($aInfo as $sSectionKey => $aSection) {
            if (F::GetRequest('adm_report_' . $sSectionKey)) {
                $sText .= '[' . $sSectionKey . '] ; ' . $aSection['label'] . "\n";
                foreach ($aSection['data'] as $sItemKey => $aItem) {
                    $sText .= $sItemKey . ' = ' . $aItem['value'] . '; ' . $aItem['label'] . "\n";
                }
                $sText .= "\n";
            }
        }
        $sText .= "; EOF\n";

        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $aParams['filename'] . '"');
        echo $sText;
        exit;
    }

    protected function _reportXml($aInfo, $aParams) {

        $sText = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<report';
        foreach ($aParams as $sKey => $sVal) {
            $sText .= ' ' . $sKey . '="' . $sVal . '"';
        }
        $sText .= ">\n";
        foreach ($aInfo as $sSectionKey => $aSection) {
            if (F::GetRequest('adm_report_' . $sSectionKey)) {
                $nLevel = 1;
                $sText .= str_repeat(' ', $nLevel * 2) . '<' . $sSectionKey . ' label="' . $aSection['label'] . '">' . "\n";
                $nLevel += 1;
                foreach ($aSection['data'] as $sItemKey => $aItem) {
                    $sText .= str_repeat(' ', $nLevel * 2) . '<' . $sItemKey . ' label="' . $aItem['label'] . '">';
                    if (is_array($aItem['value'])) {

                        $sText .= "\n" . str_repeat(' ', $nLevel * 2) . '</' . $sItemKey . '>' . "\n";
                    } else {
                        $sText .= $aItem['value'];
                    }
                    $sText .= '</' . $sItemKey . '>' . "\n";
                }
                $nLevel -= 1;
                $sText .= str_repeat(' ', $nLevel * 2) . '</' . $sSectionKey . '>' . "\n";
            }
        }

        $sText .= '</report>';

        header('Content-Type: text/xml; charset=utf-8', true);
        header('Content-Disposition: attachment; filename="' . $aParams['filename'] . '"', true);
        echo $sText;
        exit;
    }

    public function EventPhpinfo() {

        $this->sMainMenuItem = 'info';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.menu_info_phpinfo'));
        $this->SetTemplateAction('info/phpinfo');

        $this->_phpInfo(1);
    }

    protected function _phpInfo($nMode = 0) {

        if ($nMode) {
            ob_start();
            phpinfo(-1);

            $sPhpinfo = preg_replace(
                array('#^.*<body>(.*)</body>.*$#ms', '#<h2>PHP License</h2>.*$#ms',
                    '#<h1>Configuration</h1>#', "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#',
                    "#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%',
                    '#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a>'
                        . '<h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
                    '#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
                    '#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
                    "# +#", '#<tr>#', '#</tr>#'),
                array('$1', '', '', '', '</$1>' . "\n", '<', ' ', ' ', ' ', '', ' ',
                    '<h2>PHP Configuration</h2>' . "\n" . '<tr><td>PHP Version</td><td>$2</td></tr>' .
                        "\n" . '<tr><td>PHP Egg</td><td>$1</td></tr>',
                    '<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
                    '<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" .
                        '<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'),
                ob_get_clean());
            $aSections = explode('<h2>', strip_tags($sPhpinfo, '<h2><th><td>'));
            unset($aSections[0]);

            $aPhpInfo = array();
            foreach ($aSections as $sSection) {
                $n = substr($sSection, 0, strpos($sSection, '</h2>'));
                preg_match_all(
                    '#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#',
                    $sSection, $aMatches, PREG_SET_ORDER);
                foreach ($aMatches as $m) {
                    if (!isset($m[2])) $m[2] = '';
                    $aPhpInfo[$n][$m[1]] = (!isset($m[3]) || $m[2] == $m[3]) ? $m[2] : array_slice($m, 2);
                }
            }
            E::ModuleViewer()->Assign('aPhpInfo', array('collection' => $aPhpInfo, 'count' => sizeof($aPhpInfo)));
        } else {
            ob_start();
            phpinfo();
            $phpinfo = ob_get_contents();
            ob_end_clean();
            $phpinfo = str_replace("\n", ' ', $phpinfo);
            $info = '';
            if (preg_match('|<style\s*[\w="/]*>(.*)<\/style>|imu', $phpinfo, $match)) $info .= $match[0];
            if (preg_match('|<body\s*[\w="/]*>(.*)<\/body>|imu', $phpinfo, $match)) $info .= $match[1];
            if (!$info) $info = $phpinfo;
            E::ModuleViewer()->Assign('sPhpInfo', $info);
        }
    }

    /**********************************************************************************/

    /**
     * Site settings
     */
    public function EventConfig() {

        $this->sMainMenuItem = 'settings';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.config_title'));

        $sMode = $this->_getMode(0, 'base');

        if ($sMode == 'links') {
            $this->_eventConfigLinks();
        } elseif ($sMode == 'edit') {
            $this->_eventConfigEdit($sMode);
        } else {
            $this->_eventConfigParams($sMode);
        }
        E::ModuleViewer()->Assign('sMode', $sMode);
    }

    /**
     * Site settings > Config parameters
     *
     * @param   string  $sSelectedSection
     */
    protected function _eventConfigParams($sSelectedSection) {

        $this->SetTemplateAction('settings/params');

        $aFields = F::IncludeFile(Config::Get('path.dir.config') . 'actions/admin.settings.php', false, true);
        foreach ($aFields as $nSec => $aSection) {
            foreach ($aSection as $nKey => $aItem) {
                $aItem['text'] = E::ModuleLang()->Get($aItem['label']);
                if (isset($aItem['help'])) $aItem['help'] = E::ModuleLang()->Get($aItem['help']);
                if (isset($aItem['config'])) {
                    $aItem['value'] = Config::Get($aItem['config'], Config::LEVEL_CUSTOM);
                    $aItem['config'] = str_replace('.', '--', $aItem['config']);
                    if (!isset($aItem['valtype']) && isset($aItem['type']) && $aItem['type'] == 'checkbox') {
                        $aItem['valtype'] = 'boolean';
                    }
                }
                if (isset($aItem['type']) && $aItem['type'] == 'password') {
                    $aItem['valtype'] = 'string';
                }
                $aFields[$nSec][$nKey] = $aItem;
            }
        }
        if (($aData = $this->GetPost()) && isset($aFields[$sSelectedSection])) {
            $this->_eventConfigSave($aFields[$sSelectedSection], $aData);
        }
        if (!isset($aFields[$sSelectedSection])) {
            $sSelectedSection = F::Array_FirstKey($aFields);
            $this->_saveMode(0, $sSelectedSection);
        }
        E::ModuleViewer()->Assign('aFields', $aFields[$sSelectedSection]);
    }

    /**
     * Site settings > Links
     */
    protected function _eventConfigLinks() {

        if ($sHomePage = $this->GetPost('submit_data_save')) {
            $aConfig = array();
            $sHomePageSelect = '';
            if ($sHomePage = $this->GetPost('homepage')) {
                if ($sHomePage == 'page') {
                    $sHomePage = $this->GetPost('page_url');
                    $sHomePageSelect = 'page';
                } elseif($sHomePage == 'other') {
                    $sHomePage = $this->GetPost('other_url');
                    $sHomePageSelect = 'other';
                }
                $aConfig = array(
                    'router.config.action_default' => 'homepage',
                    'router.config.homepage' => $sHomePage,
                    'router.config.homepage_select' => $sHomePageSelect,
                );
            }
            if ($sDraftLink = $this->GetPost('draft_link')) {
                if ($sDraftLink == 'on') {
                    $aConfig['module.topic.draft_link'] = true;
                } else {
                    $aConfig['module.topic.draft_link'] = false;
                }
            }
            if ($sTopicLink = $this->GetPost('topic_link')) {
                $aConfig['module.topic.url_mode'] = $sTopicLink;
                if ($sTopicLink == 'alto') {
                    $aConfig['module.topic.url'] = '%topic_id%.html';
                } elseif ($sTopicLink == 'friendly') {
                    $aConfig['module.topic.url'] = '%topic_url%.html';
                } elseif ($sTopicLink == 'ls') {
                    $aConfig['module.topic.url'] = '';
                } elseif ($sTopicLink == 'id') {
                    $aConfig['module.topic.url'] = '%topic_id%';
                } elseif ($sTopicLink == 'day_name') {
                    $aConfig['module.topic.url'] = '%year%/%month%/%day%/%topic_url%/';
                } elseif ($sTopicLink == 'month_name') {
                    $aConfig['module.topic.url'] = '%year%/%month%/%topic_url%/';
                } else {
                    if ($sTopicUrl = trim($this->GetPost('topic_link_url'))) {
                        if ($sTopicUrl[0] == '/') {
                            $sTopicUrl = substr($sTopicUrl, 1);
                        }
                        $aConfig['module.topic.url'] = strtolower($sTopicUrl);
                    } else {
                        $aConfig['module.topic.url'] = '';
                    }
                }
            }
            if ($aConfig) {
                Config::WriteCustomConfig($aConfig);
                R::Location('admin/settings-site/links/');
            }
        }
        if ($this->GetPost('adm_cmd') == 'generate_topics_url') {
            // Генерация URL топиков
            $nRest = E::ModuleAdmin()->GenerateTopicsUrl();
            if ($nRest > 0) {
                E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.set_links_generate_next', array('num' => $nRest)), null, true);
            } elseif ($nRest < 0) {
                E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.set_links_generate_done'), null, true);
            } else {
                E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.set_links_generate_done'), null, true);
            }
            R::Location('admin/settings-site/links/');
        }
        $this->SetTemplateAction('settings/links');
        $sHomePage = Config::Get('router.config.homepage');
        $sHomePageSelect = Config::Get('router.config.homepage_select');

        $aPages = E::ModulePage()->GetPages();
        $sHomePageUrl = '';
        if (!$sHomePage || $sHomePage == 'index') {
            $sHomePageSelect = 'index';
            $sHomePageUrl = '';
        } elseif ($sHomePageSelect == 'page') {
            foreach($aPages as $oPage) {
                if ($oPage->getUrl() == $sHomePage) {
                    $sHomePageUrl = $oPage->getUrlPath();
                }
            }
        } elseif ($sHomePageSelect == 'other') {
            $sHomePageUrl = $sHomePage;
        } elseif(!$sHomePageSelect) {
            $sHomePageSelect = $sHomePage;
        }

        $sPermalinkUrl = trim(Config::Get('module.topic.url'), '/');
        if (!$sPermalinkUrl) {
            $sPermalinkMode = 'ls';
        } elseif ($sPermalinkUrl == '%topic_id%') {
            $sPermalinkMode = 'id';
        } elseif ($sPermalinkUrl == '%year%/%month%/%day%/%topic_url%') {
            $sPermalinkMode = 'day_name';
        } elseif ($sPermalinkUrl == '%year%/%month%/%topic_url%') {
            $sPermalinkMode = 'month_name';
        } elseif ($sPermalinkUrl == '%topic_id%.html') {
            $sPermalinkMode = 'alto';
        } elseif ($sPermalinkUrl == '%topic_url%.html') {
            $sPermalinkMode = 'friendly';
        } else {
            $sPermalinkMode = 'custom';
        }

        E::ModuleViewer()->Assign('sHomePageSelect', $sHomePageSelect);
        E::ModuleViewer()->Assign('sHomePageUrl', $sHomePageUrl);
        E::ModuleViewer()->Assign('aPages', $aPages);
        E::ModuleViewer()->Assign('sPermalinkMode', $sPermalinkMode);
        E::ModuleViewer()->Assign('sPermalinkUrl', $sPermalinkUrl);
        E::ModuleViewer()->Assign('nTopicsWithoutUrl', E::ModuleAdmin()->GetNumTopicsWithoutUrl());
    }

    /**
     * Site settings > Edit
     */
    protected function _eventConfigEdit() {

        $aUnits = array(
            'S' => array('name' => 'seconds'),
            'M' => array('name' => 'minutes'),
            'H' => array('name' => 'hours'),
            'D' => array('name' => 'days'),
        );

        if ($this->GetPost('submit_data_save')) {
            $aConfig = array();
            if ($this->GetPost('view--wysiwyg')) {
                $aConfig['view.wysiwyg'] = true;
            } else {
                $aConfig['view.wysiwyg'] = false;
            }
            if ($this->GetPost('view--noindex')) {
                $aConfig['view.noindex'] = true;
            } else {
                $aConfig['view.noindex'] = false;
            }

            //$aConfig['view.img_resize_width'] = intval($this->GetPost('view--img_resize_width'));
            //$aConfig['view.img_max_width'] = intval($this->GetPost('view--img_max_width'));
            //$aConfig['view.img_max_height'] = intval($this->GetPost('view--img_max_height'));
            $aConfig['module.uploader.images.default.max_width'] = intval($this->GetPost('view--img_max_width'));
            $aConfig['module.uploader.images.default.max_height'] = intval($this->GetPost('view--img_max_height'));

            if ($this->GetPost('tag_required')) {
                $aConfig['module.topic.allow_empty_tags'] = false;
            } else {
                $aConfig['module.topic.allow_empty_tags'] = true;
            }
            if ($nVal = intval($this->GetPost('module--topic--max_length'))) {
                $aConfig['module.topic.max_length'] = $nVal;
            }
            $aConfig['module.comment.edit.enable'] = false;
            if ($this->GetPost('edit_comment') == 'on') {
                $nEditTime = intval($this->GetPost('edit_comment_time'));
                if ($nEditTime) {
                    $sEditUnit = '';
                    if ($this->GetPost('edit_comment_unit')) {
                        foreach ($aUnits as $sKey => $sUnit) {
                            if ($sUnit['name'] == $this->GetPost('edit_comment_unit')) {
                                $sEditUnit = $sKey;
                                break;
                            }
                        }
                    }
                    if (!$sEditUnit) $sEditUnit = 'S';
                    if ($sEditUnit == 'D') $nEditTime = F::ToSeconds('P' . $nEditTime . 'D');
                    else $nEditTime = F::ToSeconds('PT' . $nEditTime . $sEditUnit);
                    $aConfig['module.comment.edit.enable'] = $nEditTime;
                }
            }

            Config::WriteCustomConfig($aConfig);
            R::Location('admin/settings-site/');
            exit;
        }
        $this->SetTemplateAction('settings/edit');
        $nCommentEditTime = F::ToSeconds(Config::Get('module.comment.edit.enable'));
        if ($nCommentEditTime) {
            $sCommentEditUnit = $aUnits['S']['name'];
            if (($nCommentEditTime % 60) == 0) {
                $nCommentEditTime = $nCommentEditTime / 60;
                $sCommentEditUnit = $aUnits['M']['name'];
                if (($nCommentEditTime % 60) == 0) {
                    $nCommentEditTime = $nCommentEditTime / 60;
                    $sCommentEditUnit = $aUnits['H']['name'];
                    if (($nCommentEditTime % 24) == 0) {
                        $nCommentEditTime = $nCommentEditTime / 24;
                        $sCommentEditUnit = $aUnits['D']['name'];
                    }
                }
            }
        } else {
            $sCommentEditUnit = $aUnits['S']['name'];
        }
        E::ModuleViewer()->Assign('nCommentEditTime', $nCommentEditTime);
        E::ModuleViewer()->Assign('sCommentEditUnit', $sCommentEditUnit);
        E::ModuleViewer()->Assign('aTimeUnits', $aUnits);
    }

    /**
     * Сохраняет пользовательские настройки
     *
     * @param $aFields
     * @param $aData
     */
    protected function _eventConfigSave($aFields, $aData) {

        $aConfig = array();
        foreach ($aFields as $aParam) {
            if (isset($aParam['config'])) {
                if (isset($aData[$aParam['config']])) {
                    $sVal = $aData[$aParam['config']];
                } else {
                    $sVal = '';
                }
                if (($sVal === '') && isset($aParam['default'])) {
                    $sVal = $aParam['default'];
                }
                if (isset($aParam['valtype'])) {
                    settype($sVal, $aParam['valtype']);
                }
                $aConfig[str_replace('--', '.', $aParam['config'])] = $sVal;
            }
        }
        if ($aConfig) {
            Config::WriteCustomConfig($aConfig);
        }
        R::Location('admin/settings-site/');
    }

    /**********************************************************************************/

    public function EventWidgets() {

        $this->sMainMenuItem = 'site';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.widgets_title'));
        $this->SetTemplateAction('site/widgets');

        $sMode = $this->GetParam(0);
        $aWidgets = E::ModuleWidget()->GetWidgets(true);

        if ($sMode == 'edit') {
            $sWidgetId = $this->GetParam(1);
            if (isset($aWidgets[$sWidgetId])) {
                $this->_eventWidgetsEdit($aWidgets[$sWidgetId]);
            }
        } elseif (($sCmd = $this->GetPost('widget_action')) && ($aWidgets = $this->GetPost('widget_sel'))) {
            $aWidgets = array_keys($aWidgets);
            if ($sCmd == 'activate') {
                $this->_eventWidgetsActivate($aWidgets);
            } elseif ($sCmd == 'deactivate') {
                $this->_eventWidgetsDeactivate($aWidgets);
            }
        }
        E::ModuleViewer()->Assign('aWidgetsList', $aWidgets);
    }

    public function _eventWidgetsEdit($oWidget) {

        if ($this->GetPost()) {
            $aConfig = array();
            $sPrefix = 'widget.' . $oWidget->GetId() . '.config.';
            if ($xVal = $this->GetPost('widget_group')) {
                $aConfig[$sPrefix . 'wgroup'] = $xVal;
            }

            $aConfig[$sPrefix . 'active'] = (bool)$this->GetPost('widget_active');

            $xVal = strtolower($this->GetPost('widget_priority'));
            $aConfig[$sPrefix . 'priority'] = ($xVal == 'top' ? 'top' : intval($xVal));

            if ($this->GetPost('widget_display') == 'period') {
                if ($sFrom = $this->GetPost('widget_period_from')) {
                    $aConfig[$sPrefix . 'display.date_from'] = date('Y-m-d', strtotime($sFrom));;
                }
                if ($sUpto = $this->GetPost('widget_period_upto')) {
                    $aConfig[$sPrefix . 'display.date_upto'] = date('Y-m-d', strtotime($sUpto));;
                }
            }

            $xVal = strtolower($this->GetPost('widget_visitors'));
            $aConfig[$sPrefix . 'visitors'] = (in_array($xVal, array('users', 'admins')) ? $xVal : null);

            Config::WriteCustomConfig($aConfig);
            R::Location('admin/site-widgets');
        }
        $this->_setTitle(E::ModuleLang()->Get('action.admin.widget_edit_title'));
        $this->SetTemplateAction('site/widgets_add');
        E::ModuleViewer()->Assign('oWidget', $oWidget);
    }

    public function _eventWidgetsActivate($aWidgets) {

        if ($this->GetPost()) {
            $aConfig = array();
            foreach ($aWidgets as $sWidgetId) {
                $sPrefix = 'widget.' . $sWidgetId . '.config.';
                $aConfig[$sPrefix . 'active'] = true;
            }
            Config::WriteCustomConfig($aConfig);
            R::Location('admin/site-widgets');
        }
    }

    public function _eventWidgetsDeactivate($aWidgets) {

        if ($this->GetPost()) {
            $aConfig = array();
            foreach ($aWidgets as $sWidgetId) {
                $sPrefix = 'widget.' . $sWidgetId . '.config.';
                $aConfig[$sPrefix . 'active'] = false;
            }
            Config::WriteCustomConfig($aConfig);
            R::Location('admin/site-widgets');
        }
    }

    /**********************************************************************************/

    public function EventPlugins() {

        $this->sMainMenuItem = 'site';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.plugins_title'));
        $this->SetTemplateAction('site/plugins');

        if ($this->GetParam(0) == 'add') {
            return $this->_EventPluginsAdd();
        } elseif ($this->GetParam(0) == 'config') {
            $this->sMenuSubItemSelect = 'config';
            $this->PluginDelBlock('right', 'AdminInfo');
            return $this->_eventPluginsConfig();
        } else {
            $sParam = $this->GetParam(0, 'list');
            if ($sParam != 'list') {
                $aPlugins = E::ModulePlugin()->GetActivePlugins();
                if (in_array($sParam, $aPlugins)) {
                    return $this->_EventPluginsExternalAdmin(0);
                }
            }
            return $this->_eventPluginsList();
        }
    }

    protected function _eventPluginsConfig() {

        $this->PluginDelBlock('right', 'AdminInfo');
        $sPluginCode = $this->getParam(1);
        $oPlugin = $this->PluginAceadminpanel_Plugin_GetPlugin($sPluginCode);
        if ($oPlugin) {
            $sClass = $oPlugin->GetAdminClass();
            return $this->EventPluginsExec($sClass);
        } else {
            return false;
        }
    }

    protected function _EventPluginsMenu() {

        $this->PluginDelBlock('right', 'AdminInfo');
        $sEvent = R::GetActionEvent();
        if (isset($this->aExternalEvents[$sEvent])) {
            return $this->EventPluginsExec($this->aExternalEvents[$sEvent]);
        }
    }

    protected function _eventPluginsList() {

        if ($this->GetPost('plugin_action') == 'delete' && ($aSelectedPlugins = $this->GetPost('plugin_sel'))) {
            // Удаление плагинов
            $this->_eventPluginsDelete($aSelectedPlugins);
        } elseif ($sAction = $this->GetPost('plugin_action')) {
            $aPlugins = $this->GetPost('plugin_sel');
            if ($sAction == 'activate') {
                $this->_eventPluginsActivate($aPlugins);
            } elseif ($sAction == 'deactivate') {
                $this->_eventPluginsDeactivate($aPlugins);
            }
            R::Location('admin/site-plugins/');
        }

        $sMode = $this->GetParam(1, 'all');

        if ($sMode == 'active') {
            $aPlugins = E::ModulePlugin()->GetPluginsList(true);
        } elseif ($sMode == 'inactive') {
            $aPlugins = E::ModulePlugin()->GetPluginsList(false);
        } else {
            $aPlugins = E::ModulePlugin()->GetPluginsList();
        }

        E::ModuleViewer()->Assign('aPluginList', $aPlugins);
        E::ModuleViewer()->Assign('sMode', $sMode);
    }

    protected function _eventPluginsActivate($aPlugins) {

        if (is_array($aPlugins)) {
            // если передан массив, то обрабатываем только первый элемент
            $sPluginId = array_shift($aPlugins);
        } else {
            $sPluginId = (string)$aPlugins;
        }
        return E::ModulePlugin()->Activate($sPluginId);
    }

    protected function _eventPluginsDeactivate($aPlugins) {

        if (is_array($aPlugins)) {
            // если передан массив, то обрабатываем только первый элемент
            $sPluginId = array_shift($aPlugins);
        } else {
            $sPluginId = (string)$aPlugins;
        }
        return E::ModulePlugin()->Deactivate($sPluginId);
    }

    protected function _eventPluginsDelete($aPlugins) {

        E::ModulePlugin()->Delete($aPlugins);
    }

    protected function _eventPluginsAdd() {

        if ($aZipFile = $this->GetUploadedFile('plugin_arc')) {
            if ($sPackFile = F::File_MoveUploadedFile($aZipFile['tmp_name'], $aZipFile['name'] . '/' . $aZipFile['name'])) {
                E::ModulePlugin()->UnpackPlugin($sPackFile);
                F::File_RemoveDir(dirname($sPackFile));
            }
        }
        $this->_setTitle(E::ModuleLang()->Get('action.admin.plugins_title'));
        $this->SetTemplateAction('site/plugins_add');
        E::ModuleViewer()->Assign('sMode', 'add');
    }

    /**********************************************************************************/

    protected function EventPages() {

        $this->sMainMenuItem = 'content';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.pages_title'));
        // * Получаем и загружаем список всех страниц
        $aPages = E::ModulePage()->GetPages();
        if (count($aPages) == 0 && E::ModulePage()->GetCountPage()) {
            E::ModulePage()->SetPagesPidToNull();
            $aPages = E::ModulePage()->GetPages();
        }
        E::ModuleViewer()->Assign('aPages', $aPages);
        if ($this->GetParam(0) == 'add') {
            $this->_eventPagesEdit('add');
        } elseif ($this->GetParam(0) == 'edit') {
            $this->_eventPagesEdit('edit');
        } else {
            $this->_eventPagesList();
        }
    }

    protected function _eventPagesList() {

        // * Обработка удаления страницы
        if ($this->GetParam(0) == 'delete') {
            E::ModuleSecurity()->ValidateSendForm();
            if (E::ModulePage()->DeletePageById($this->GetParam(1))) {
                E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.pages_admin_action_delete_ok'). null, true);
                R::Location('admin/content-pages/');
            } else {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.pages_admin_action_delete_error'), E::ModuleLang()->Get('error'));
            }
        }

        // * Обработка изменения сортировки страницы
        if ($this->GetParam(0) == 'sort') {
            $this->_eventPagesListSort();
        }
        $this->SetTemplateAction('content/pages_list');
    }

    protected function _eventPagesListSort() {

        E::ModuleSecurity()->ValidateSendForm();
        if ($oPage = E::ModulePage()->GetPageById($this->GetParam(1))) {
            $sWay = $this->GetParam(2) == 'down' ? 'down' : 'up';
            $iSortOld = $oPage->getSort();
            if ($oPagePrev = E::ModulePage()->GetNextPageBySort($iSortOld, $oPage->getPid(), $sWay)) {
                $iSortNew = $oPagePrev->getSort();
                $oPagePrev->setSort($iSortOld);
                E::ModulePage()->UpdatePage($oPagePrev);
            } else {
                if ($sWay == 'down') {
                    $iSortNew = $iSortOld - 1;
                } else {
                    $iSortNew = $iSortOld + 1;
                }
            }

            // * Меняем значения сортировки местами
            $oPage->setSort($iSortNew);
            E::ModulePage()->UpdatePage($oPage);
            E::ModulePage()->ReSort();
        }
        R::Location('admin/content-pages');
    }

    protected function _eventPagesEdit($sMode) {

        $this->_setTitle(E::ModuleLang()->Get('action.admin.pages_title'));
        $this->SetTemplateAction('content/pages_add');
        E::ModuleViewer()->Assign('sMode', $sMode);

        // * Обработка создания новой страницы
        if (F::isPost('submit_page_save')) {
            if (!F::GetRequest('page_id')) {
                $this->SubmitAddPage();
            }
        }
        // * Обработка показа страницы для редактирования
        if ($this->GetParam(0) == 'edit') {
            if ($oPageEdit = E::ModulePage()->GetPageById($this->GetParam(1))) {
                if (!F::isPost('submit_page_save')) {
                    $_REQUEST['page_title'] = $oPageEdit->getTitle();
                    $_REQUEST['page_pid'] = $oPageEdit->getPid();
                    $_REQUEST['page_url'] = $oPageEdit->getUrl();
                    $_REQUEST['page_text'] = $oPageEdit->getTextSource();
                    $_REQUEST['page_seo_keywords'] = $oPageEdit->getSeoKeywords();
                    $_REQUEST['page_seo_description'] = $oPageEdit->getSeoDescription();
                    $_REQUEST['page_active'] = $oPageEdit->getActive();
                    $_REQUEST['page_main'] = $oPageEdit->getMain();
                    $_REQUEST['page_sort'] = $oPageEdit->getSort();
                    $_REQUEST['page_auto_br'] = $oPageEdit->getAutoBr();
                    $_REQUEST['page_id'] = $oPageEdit->getId();
                } else {
                    // * Если отправили форму с редактированием, то обрабатываем её
                    $this->SubmitEditPage($oPageEdit);
                }
                E::ModuleViewer()->Assign('oPageEdit', $oPageEdit);
            } else {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.pages_edit_notfound'), E::ModuleLang()->Get('error'));
                $this->SetParam(0, null);
            }
        }
    }

    /**
     * Обработка отправки формы при редактировании страницы
     *
     * @param $oPageEdit
     */
    protected function SubmitEditPage($oPageEdit) {

        // * Проверяем корректность полей
        if (!$this->CheckPageFields()) {
            return;
        }
        if ($oPageEdit->getId() == F::GetRequest('page_pid')) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'));
            return;
        }
        
        // * Проверяем есть ли страница с указанным URL
        if ($oPageEdit->getUrlFull() != F::GetRequest('page_url')) {
            if (E::ModulePage()->GetPageByUrlFull(F::GetRequest('page_url'))) {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.page_url_exist'), E::ModuleLang()->Get('error'));
                return;
            }
        }

        // * Обновляем свойства страницы
        $oPageEdit->setActive(F::GetRequest('page_active') ? 1 : 0);
        $oPageEdit->setAutoBr(F::GetRequest('page_auto_br') ? 1 : 0);
        $oPageEdit->setMain(F::GetRequest('page_main') ? 1 : 0);
        $oPageEdit->setDateEdit(F::Now());
        if (F::GetRequest('page_pid') == 0) {
            $oPageEdit->setUrlFull(F::GetRequest('page_url'));
            $oPageEdit->setPid(null);
        } else {
            $oPageEdit->setPid(F::GetRequest('page_pid'));
            $oPageParent = E::ModulePage()->GetPageById(F::GetRequest('page_pid'));
            $oPageEdit->setUrlFull($oPageParent->getUrlFull() . '/' . F::GetRequest('page_url'));
        }
        $oPageEdit->setSeoDescription(F::GetRequest('page_seo_description'));
        $oPageEdit->setSeoKeywords(F::GetRequest('page_seo_keywords'));
        $oPageEdit->setText(E::ModuleText()->SnippetParser(F::GetRequest('page_text')));
        $oPageEdit->setTextSource(F::GetRequest('page_text'));
        $oPageEdit->setTitle(F::GetRequest('page_title'));
        $oPageEdit->setUrl(F::GetRequest('page_url'));
        $oPageEdit->setSort(F::GetRequest('page_sort'));

        // * Обновляем страницу
        if (E::ModulePage()->UpdatePage($oPageEdit)) {
            E::ModulePage()->RebuildUrlFull($oPageEdit);
            E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.pages_edit_submit_save_ok'));
            $this->SetParam(0, null);
            $this->SetParam(1, null);
            R::Location('admin/content-pages/');
        } else {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'));
        }
    }

    /**
     * Обработка отправки формы добавления новой страницы
     *
     */
    protected function SubmitAddPage() {

        // * Проверяем корректность полей
        if (!$this->CheckPageFields()) {
            return;
        }
        // * Заполняем свойства
        $oPage = E::GetEntity('Page');
        $oPage->setActive(F::GetRequest('page_active') ? 1 : 0);
        $oPage->setAutoBr(F::GetRequest('page_auto_br') ? 1 : 0);
        $oPage->setMain(F::GetRequest('page_main') ? 1 : 0);
        $oPage->setDateAdd(F::Now());
        if (F::GetRequest('page_pid') == 0) {
            $oPage->setUrlFull(F::GetRequest('page_url'));
            $oPage->setPid(null);
        } else {
            $oPage->setPid(F::GetRequest('page_pid'));
            $oPageParent = E::ModulePage()->GetPageById(F::GetRequest('page_pid'));
            $oPage->setUrlFull($oPageParent->getUrlFull() . '/' . F::GetRequest('page_url'));
        }
        $oPage->setSeoDescription(F::GetRequest('page_seo_description'));
        $oPage->setSeoKeywords(F::GetRequest('page_seo_keywords'));
        $oPage->setText(E::ModuleText()->SnippetParser(F::GetRequest('page_text')));
        $oPage->setTextSource(F::GetRequest('page_text'));
        $oPage->setTitle(F::GetRequest('page_title'));
        $oPage->setUrl(F::GetRequest('page_url'));
        if (F::GetRequest('page_sort')) {
            $oPage->setSort(F::GetRequest('page_sort'));
        } else {
            $oPage->setSort(E::ModulePage()->GetMaxSortByPid($oPage->getPid()) + 1);
        }
        
        // * Проверяем есть ли страница с таким URL
        if (E::ModulePage()->GetPageByUrlFull($oPage->getUrlFull())) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.page_url_exist'), E::ModuleLang()->Get('error'));
            return;
        }

        /**
         * Добавляем страницу
         */
        if (E::ModulePage()->AddPage($oPage)) {
            E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.pages_create_submit_save_ok'));
            $this->SetParam(0, null);
            R::Location('admin/content-pages/');
        } else {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'));
        }
    }

    /**
     * Проверка полей на корректность
     *
     * @return bool
     */
    protected function CheckPageFields() {

        E::ModuleSecurity()->ValidateSendForm();

        $bOk = true;
        /**
         * Проверяем есть ли заголовок топика
         */
        if (!F::CheckVal(F::GetRequest('page_title', null, 'post'), 'text', 2, 200)) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.pages_create_title_error'), E::ModuleLang()->Get('error'));
            $bOk = false;
        }
        /**
         * Проверяем есть ли заголовок топика, с заменой всех пробельных символов на "_"
         */
        $pageUrl = preg_replace("/\s+/", '_', (string)F::GetRequest('page_url', null, 'post'));
        $_REQUEST['page_url'] = $pageUrl;
        if (!F::CheckVal(F::GetRequest('page_url', null, 'post'), 'login', 1, 50)) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.pages_create_url_error'), E::ModuleLang()->Get('error'));
            $bOk = false;
        }
        /**
         * Проверяем на счет плохих УРЛов
         */
        /*if (in_array(F::GetRequest('page_url',null,'post'),$this->aBadPageUrl)) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.pages_create_url_error_bad').' '.join(',',$this->aBadPageUrl),E::ModuleLang()->Get('error'));
            $bOk=false;
        }*/
        /**
         * Проверяем есть ли содержание страницы
         */
        if (!F::CheckVal(F::GetRequest('page_text', null, 'post'), 'text', 1, 50000)) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.pages_create_text_error'), E::ModuleLang()->Get('error'));
            $bOk = false;
        }
        /**
         * Проверяем страницу в которую хотим вложить
         */
        if (F::GetRequest('page_pid') != 0 && !($oPageParent = E::ModulePage()->GetPageById(F::GetRequest('page_pid')))) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.pages_create_parent_page_error'), E::ModuleLang()->Get('error'));
            $bOk = false;
        }
        /**
         * Проверяем сортировку
         */
        if (F::GetRequest('page_sort') && !is_numeric(F::GetRequest('page_sort'))) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.pages_create_sort_error'), E::ModuleLang()->Get('error'));
            $bOk = false;
        }
        /**
         * Выполнение хуков
         */
        E::ModuleHook()->Run('check_page_fields', array('bOk' => &$bOk));

        return $bOk;
    }


    /**********************************************************************************/

    protected function EventBlogs() {

        $this->sMainMenuItem = 'content';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.blogs_title'));
        $this->SetTemplateAction('content/blogs_list');

        $sMode = 'all';

        $sCmd = $this->GetPost('cmd');
        if ($sCmd == 'delete_blog') {
            $this->_eventBlogsDelete();
        }

        // * Передан ли номер страницы
        $nPage = $this->_getPageNum();

        if ($this->GetParam(1) && !strstr($this->GetParam(1), 'page')) $sMode = $this->GetParam(1);

        $aFilter = array();
        if ($sMode && $sMode != 'all') {
            $aFilter['type'] = $sMode;
        }

        $aResult = E::ModuleBlog()->GetBlogsByFilter($aFilter, '', $nPage, Config::Get('admin.items_per_page'));
        $aPaging = E::ModuleViewer()->MakePaging($aResult['count'], $nPage, Config::Get('admin.items_per_page'), 4,
            R::GetPath('admin') . 'content-blogs/list/' . $sMode);

        $aBlogTypes = E::ModuleBlog()->GetBlogTypes();
        $nBlogsTotal = 0;
        foreach ($aBlogTypes as $oBlogType) {
            $nBlogsTotal += $oBlogType->GetBlogsCount();
        }
        $aAllBlogs = E::ModuleBlog()->GetBlogs();
        foreach($aAllBlogs as $nBlogId=>$oBlog) {
            $aAllBlogs[$nBlogId] = $oBlog->GetTitle();
        }

        E::ModuleViewer()->Assign('nBlogsTotal', $nBlogsTotal);
        E::ModuleViewer()->Assign('aBlogTypes', $aBlogTypes);
        E::ModuleViewer()->Assign('aBlogs', $aResult['collection']);
        E::ModuleViewer()->Assign('aAllBlogs', $aAllBlogs);

        E::ModuleViewer()->Assign('sMode', $sMode);
        E::ModuleViewer()->Assign('aPaging', $aPaging);
    }

    protected function _eventBlogsDelete() {

        $nBlogId = $this->GetPost('delete_blog_id');
        if (!$nBlogId || !($oBlog = E::ModuleBlog()->GetBlogById($nBlogId))) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.blog_del_error'));
            return false;
        }

        if ($this->GetPost('delete_topics') !== 'delete') {
            // Топики перемещаются в новый блог
            $aTopics = E::ModuleTopic()->GetTopicsByBlogId($nBlogId);
            $nNewBlogId = intval($this->GetPost('topic_move_to'));
            if (($nNewBlogId > 0) && is_array($aTopics) && count($aTopics)) {
                if (!$oBlogNew = E::ModuleBlog()->GetBlogById($nNewBlogId)) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('blog_admin_delete_move_error'), E::ModuleLang()->Get('error'));
                    return false;
                }
                // * Если выбранный блог является персональным, возвращаем ошибку
                if ($oBlogNew->getType() == 'personal') {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('blog_admin_delete_move_personal'), E::ModuleLang()->Get('error'));
                    return false;
                }
                // * Перемещаем топики
                if (!E::ModuleTopic()->MoveTopics($nBlogId, $nNewBlogId)) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.blog_del_move_error'), E::ModuleLang()->Get('error'));
                    return false;
                }
            } else {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.blog_del_move_error'), E::ModuleLang()->Get('error'));
                return false;
            }
        }

        // * Удаляяем блог
        E::ModuleHook()->Run('blog_delete_before', array('sBlogId' => $nBlogId));
        if (E::ModuleBlog()->DeleteBlog($nBlogId)) {
            E::ModuleHook()->Run('blog_delete_after', array('sBlogId' => $nBlogId));
            E::ModuleMessage()->AddNoticeSingle(
                E::ModuleLang()->Get('blog_admin_delete_success'), E::ModuleLang()->Get('attention'), true
            );
        } else {
            E::ModuleMessage()->AddNoticeSingle(
                E::ModuleLang()->Get('action.admin.blog_del_error'), E::ModuleLang()->Get('error'), true
            );
        }
        R::ReturnBack();
    }

    /**********************************************************************************/

    protected function EventTopics() {

        $this->sMainMenuItem = 'content';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.topics_title'));
        $this->SetTemplateAction('content/topics_list');

        $sCmd = $this->GetPost('cmd');
        if ($sCmd == 'delete') {
            $this->_topicDelete();
        } else {
            // * Передан ли номер страницы
            $nPage = $this->_getPageNum();
        }

        $aResult = E::ModuleTopic()->GetTopicsByFilter(array(), $nPage, Config::Get('admin.items_per_page'));
        $aPaging = E::ModuleViewer()->MakePaging($aResult['count'], $nPage, Config::Get('admin.items_per_page'), 4,
            R::GetPath('admin') . 'content-topics/');

        E::ModuleViewer()->Assign('aTopics', $aResult['collection']);
        E::ModuleViewer()->Assign('aPaging', $aPaging);

        E::ModuleLang()->AddLangJs(array(
                'topic_delete_confirm_title',
                'topic_delete_confirm_text',
                'topic_delete_confirm',
            ));
    }

    /**********************************************************************************/

    protected function EventComments() {

        $this->sMainMenuItem = 'content';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.comments_title'));
        $this->SetTemplateAction('content/comments_list');

        $sCmd = $this->GetPost('cmd');
        if ($sCmd == 'delete') {
            $this->_commentDelete();
        }

        // * Передан ли номер страницы
        $nPage = $this->_getPageNum();

        $aResult = E::ModuleComment()->GetCommentsByFilter(array(), '', $nPage, Config::Get('admin.items_per_page'));
        $aPaging = E::ModuleViewer()->MakePaging($aResult['count'], $nPage, Config::Get('admin.items_per_page'), 4,
            R::GetPath('admin') . 'content-comments/');

        E::ModuleViewer()->Assign('aComments', $aResult['collection']);
        E::ModuleViewer()->Assign('aPaging', $aPaging);
    }

    /**********************************************************************************/

    /**
     * View and managment of Mresources
     */
    protected function EventMresources() {

        $this->sMainMenuItem = 'content';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.mresources_title'));
        $this->SetTemplateAction('content/mresources_list');

        $sCmd = $this->GetPost('cmd');
        if ($sCmd == 'delete') {
            $this->_eventMresourcesDelete();
        }

        $sMode = $this->_getMode(1, 'all');

        // * Передан ли номер страницы
        $nPage = $this->_getPageNum();

        $aFilter = array(
            //'type' => ModuleMresource::TYPE_IMAGE,
        );

        if ($sMode &&  $sMode != 'all') {
            $aFilter = array('target_type' => $sMode);
        }

        $aCriteria = array(
            'fields' => array('mr.*', 'targets_count'),
            'filter' => $aFilter,
            'limit'  => array(($nPage - 1) * Config::Get('admin.items_per_page'), Config::Get('admin.items_per_page')),
            'with'   => array('user'),
        );

        $aResult = E::ModuleMresource()->GetMresourcesByCriteria($aCriteria);
        $aResult['count'] = E::ModuleMresource()->GetMresourcesCountByTarget($sMode);

        $aPaging = E::ModuleViewer()->MakePaging($aResult['count'], $nPage, Config::Get('admin.items_per_page'), 4,
            R::GetPath('admin') . 'content-mresources/list/' . $sMode . '/');

        E::ModuleLang()->AddLangJs(
            array(
                 'action.admin.mresource_delete_confirm',
                 'action.admin.mresource_will_be_delete',
            )
        );

        $aTargetTypes = E::ModuleMresource()->GetTargetTypes();

        E::ModuleViewer()->Assign('aMresources', $aResult['collection']);
        E::ModuleViewer()->Assign('aTargetTypes', $aTargetTypes);
        E::ModuleViewer()->Assign('sMode', $sMode);
        if (strpos($sMode, 'single-image-uploader') === 0) {
            $sMode = str_replace('single-image-uploader', E::ModuleLang()->Get('target_type_single-image-uploader'), $sMode);
        } else {
            if (strpos($sMode, 'plugin.') === 0) {
                $sMode = E::ModuleLang()->Get($sMode);
            } else {
                $sLabelKey = 'target_type_' . $sMode;
                if (($sLabel = E::ModuleLang()->Get($sLabelKey)) == mb_strtoupper($sLabelKey)) {
                    /** @var ModuleTopic_EntityContentType $oContentType */
                    $oContentType = E::ModuleTopic()->GetContentTypeByUrl($sMode);
                    if ($oContentType) {
                        $sLabel = $oContentType->getContentTitleDecl();
                    }
                }
                $sMode = $sLabel;
            }

        }
        E::ModuleViewer()->Assign('sPageSubMenu', $sMode);
        E::ModuleViewer()->Assign('aPaging', $aPaging);
    }

    /**
     * @return bool
     */
    protected function _eventMresourcesDelete() {

        if ($iMresourceId = $this->GetPost('mresource_id')) {
            if (E::ModuleMresource()->DeleteMresources($iMresourceId)) {
                E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.mresource_deleted'));
                return true;
            }
        }
        E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.mresource_not_deleted'));
        return false;
    }

    /**********************************************************************************/

    protected function EventUsers() {

        $this->sMainMenuItem = 'users';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.users_title'));
        $this->SetTemplateAction('users/users');

        $sMode = $this->_getMode(0, 'list', array('list', 'admins'));

        if (($sCmd = $this->GetPost('adm_user_cmd'))) {
            if ($sCmd == 'adm_ban_user') {
                $sUsersList = $this->GetPost('adm_user_list');
                $nBanDays = $this->GetPost('ban_days');
                $sBanComment = $this->GetPost('ban_comment');

                $sIp = $this->GetPost('user_ban_ip1');
                if ($sIp) {
                    $sIp .= $this->GetPost('user_ban_ip2', '0')
                        . '.' . $this->GetPost('user_ban_ip3', '0')
                        . '.' . $this->GetPost('user_ban_ip4', '0');
                }

                if ($sUsersList) {
                    $aUsersId = F::Array_Str2Array($sUsersList);
                    $this->_eventUsersCmdBan($aUsersId, $nBanDays, $sBanComment);
                } elseif ($sIp) {
                    $this->_eventIpsCmdBan($sIp, $nBanDays, $sBanComment);
                }
            } elseif ($sCmd == 'adm_unban_user') {
                $aUsersId = $this->GetPost('adm_user_list');
                $this->_eventUsersCmdUnban($aUsersId);
            } elseif ($sCmd == 'adm_user_setadmin') {
                $this->_eventUsersCmdSetAdministrator();
            } elseif ($sCmd == 'adm_user_unsetadmin') {
                $this->_eventUsersCmdUnsetAdministrator();
            } elseif ($sCmd == 'adm_user_setmoderator') {
                $this->_eventUsersCmdSetModerator();
            } elseif ($sCmd == 'adm_user_unsetmoderator') {
                $this->_eventUsersCmdUnsetModerator();
            } elseif ($sCmd == 'adm_del_user') {
                if ($this->_eventUsersCmdDelete()) {
                    $nPage = $this->_getPageNum();
                    R::Location('admin/users-list/' . ($nPage ? 'page' . $nPage : ''));
                } else {
                    R::ReturnBack();
                }
            } elseif ($sCmd == 'adm_user_message') {
                $this->_eventUsersCmdMessage();
            } elseif ($sCmd == 'adm_user_activate') {
                $this->_eventUsersCmdActivate();
            }
            R::Location('admin/users-list/');
        }

        if ($this->GetPost('adm_userlist_filter')) {
            $this->_eventUsersFilter();
        }

        if ($sMode == 'profile') {
            // админ-профиль юзера
            return $this->_eventUsersProfile();
        } elseif ($this->GetParam(0) == 'admins' && $this->GetParam(1) == 'del') {
            $this->EventUsersDelAdministrator();
        } else {
            $this->_eventUsersList($sMode);
        }
        E::ModuleViewer()->Assign('sMode', $sMode);
        E::ModuleViewer()->Assign('nCountUsers', E::ModuleUser()->GetCountUsers());
        E::ModuleViewer()->Assign('nCountAdmins', E::ModuleUser()->GetCountAdmins());
        E::ModuleViewer()->Assign('nCountModerators', E::ModuleUser()->GetCountModerators());
    }

    protected function _eventUsersCmdBan($aUsersId, $nDays, $sComment) {

        if ($aUsersId) {
            if (in_array(E::UserId(), $aUsersId)) {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.cannot_ban_self'), null, true);
                return false;
            }
            if (in_array(1, $aUsersId)) {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.cannot_ban_admin'), null, true);
                return false;
            }
            $aUsers = E::ModuleUser()->GetUsersByArrayId($aUsersId);
            foreach ($aUsers as $oUser) {
                if ($oUser->isAdministrator()) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.cannot_ban_admin'), null, true);
                    return false;
                }
            }
            if (E::ModuleAdmin()->BanUsers($aUsersId, $nDays, $sComment)) {
                E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.action_ok'), null, true);
                return true;
            } else {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.action_err'), null, true);
            }
        }
        return false;
    }

    protected function _eventUsersCmdUnban($aUsersId) {

        if ($aUsersId) {
            $aId = F::Array_Str2ArrayInt($aUsersId, ',', true);
            if (E::ModuleAdmin()->UnbanUsers($aId)) {
                E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.action_ok'), null, true);
                return true;
            } else {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.action_err'), null, true);
            }
        }
        return false;
    }

    protected function _eventIpsCmdBan($sIp, $nDays, $sComment) {

        $aIp = explode('.', $sIp) + array(0, 0, 0, 0);
        if ($aIp[0] < 1 || $aIp[0] > 254) {
            // error - first part cannot be empty
        } else {
            $sIp1 = '';
            foreach ($aIp as $sPart) {
                $n = intval($sPart);
                if ($n < 0 || $n >= 255) $n = 0;
                if ($sIp1) $sIp1 .= '.';
                $sIp1 .= $n;
            }
            $sIp2 = '';
            foreach ($aIp as $sPart) {
                $n = intval($sPart);
                if ($n <= 0 || $n >= 255) $n = 255;
                if ($sIp2) $sIp2 .= '.';
                $sIp2 .= $n;
            }
            if (E::ModuleAdmin()->SetBanIp($sIp1, $sIp2, $nDays, $sComment)) {
                E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.action_ok'), null, true);
                return true;
            }
        }
        E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.action_err'), null, true);
        return false;
    }

    protected function _eventUsersList($sMode) {

        $this->SetTemplateAction('users/list');
        // * Передан ли номер страницы
        $nPage = $this->_getPageNum();

        $aFilter = array();
        $sData = E::ModuleSession()->Get('adm_userlist_filter');
        if ($sData) {
            $aFilter = @unserialize($sData);
            if (!is_array($aFilter)) {
                $aFilter = array();
            }
        }

        if ($sMode == 'admins') {
            $aFilter['admin'] = 1;
        }


        if ($sMode == 'moderators') {
            $aFilter['moderator'] = 1;
        }

        $aResult = E::ModuleUser()->GetUsersByFilter($aFilter, '', $nPage, Config::Get('admin.items_per_page'));
        $aPaging = E::ModuleViewer()->MakePaging($aResult['count'], $nPage, Config::Get('admin.items_per_page'), 4,
            R::GetPath('admin') . 'users-list/');

        foreach ($aFilter as $sKey => $xVal) {
            if ($sKey == 'ip') {
                if (!$xVal || ($xVal == '*.*.*.*') || ($xVal == '0.0.0.0')) {
                    unset($aFilter[$sKey]);
                } else {
                    $aIp = explode('.', $xVal) + array('*', '*', '*', '*');
                    foreach ($aIp as $n => $sPart) {
                        if ($sPart == '*') {
                            $aIp[$n] = '';
                        } else {
                            $aIp[$n] = $sPart;
                        }
                    }
                    $aFilter[$sKey] = $aIp;
                }
            } elseif ($sKey == 'moderator' || !$xVal) {
                unset($aFilter[$sKey]);
            } elseif ($sKey == 'admin' || !$xVal) {
                unset($aFilter[$sKey]);
            }
        }
        E::ModuleViewer()->Assign('aUsers', $aResult['collection']);
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        E::ModuleViewer()->Assign('aFilter', $aFilter);
    }

    protected function _eventUsersCmdSetAdministrator() {

        $aUserLogins = F::Str2Array($this->GetPost('user_login_admin'), ',', true);
        if ($aUserLogins)
            foreach ($aUserLogins as $sUserLogin) {
                if (!$sUserLogin || !($oUser = E::ModuleUser()->GetUserByLogin($sUserLogin))) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.user_not_found', array('user' => $sUserLogin)));
                } elseif ($oUser->IsBanned()) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.cannot_banned_admin'));
                } elseif ($oUser->IsAdministrator()) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.already_added'));
                } else {
                    if (E::ModuleAdmin()->SetAdministrator($oUser->GetId())) {
                        E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.saved_ok'));
                    } else {
                        E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.saved_err'));
                    }
                }
            }
        R::ReturnBack(true);
    }

    protected function _eventUsersCmdUnsetAdministrator() {

        $aUserLogins = F::Str2Array($this->GetPost('users_list'), ',', true);
        if ($aUserLogins)
            foreach ($aUserLogins as $sUserLogin) {
                if (!$sUserLogin || !($oUser = E::ModuleUser()->GetUserByLogin($sUserLogin))) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.user_not_found', array('user' => $sUserLogin)), 'admins:delete');
                } else {
                    if (mb_strtolower($sUserLogin) == 'admin') {
                        E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.cannot_with_admin'), 'admins:delete');
                    } elseif (E::ModuleAdmin()->UnsetAdministrator($oUser->GetId())) {
                        E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.saved_ok'), 'admins:delete');
                    } else {
                        E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.saved_err'), 'admins:delete');
                    }
                }
            }
        R::ReturnBack(true);
    }

    protected function _eventUsersCmdSetModerator() {

        $aUserLogins = F::Str2Array($this->GetPost('user_login_moderator'), ',', true);
        if ($aUserLogins)
            foreach ($aUserLogins as $sUserLogin) {
                if (!$sUserLogin || !($oUser = E::ModuleUser()->GetUserByLogin($sUserLogin))) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.user_not_found', array('user' => $sUserLogin)));
                } elseif ($oUser->IsBanned()) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.cannot_banned_admin'));
                } elseif ($oUser->IsModerator()) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.already_added'));
                } else {
                    if (E::ModuleAdmin()->SetModerator($oUser->GetId())) {
                        E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.saved_ok'));
                    } else {
                        E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.saved_err'));
                    }
                }
            }
        R::ReturnBack(true);
    }

    protected function _eventUsersCmdUnsetModerator() {

        $aUserLogins = F::Str2Array($this->GetPost('users_list'), ',', true);
        if ($aUserLogins)
            foreach ($aUserLogins as $sUserLogin) {
                if (!$sUserLogin || !($oUser = E::ModuleUser()->GetUserByLogin($sUserLogin))) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.user_not_found', array('user' => $sUserLogin)), 'admins:delete');
                } else {
                    if (mb_strtolower($sUserLogin) == 'admin') {
                        E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.cannot_with_admin'), 'admins:delete');
                    } elseif (E::ModuleAdmin()->UnsetModerator($oUser->GetId())) {
                        E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.saved_ok'), 'admins:delete');
                    } else {
                        E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.saved_err'), 'admins:delete');
                    }
                }
            }
        R::ReturnBack(true);
    }

    protected function _eventUsersProfile() {

        $nUserId = $this->GetParam(1);
        $oUserProfile = E::ModuleUser()->GetUserById($nUserId);
        if (!$oUserProfile) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.user_not_found'));
            return;
        }

        $sMode = $this->GetParam(2);
        $aUserVoteStat = E::ModuleVote()->GetUserVoteStats($oUserProfile->getId());

        if ($sMode == 'topics') {
            $this->EventUsersProfileTopics($oUserProfile);
        } elseif ($sMode == 'blogs') {
            $this->EventUsersProfileBlogs($oUserProfile);
        } elseif ($sMode == 'comments') {
            $this->EventUsersProfileComments($oUserProfile);
        } elseif ($sMode == 'voted') {
            $this->EventUsersProfileVotedBy($oUserProfile);
        } elseif ($sMode == 'votes') {
            $this->EventUsersProfileVotesFor($oUserProfile);
        } elseif ($sMode == 'ips') {
            $this->EventUsersProfileIps($oUserProfile);
        } else {
            $sMode = 'info';
            $this->_eventUsersProfileInfo($oUserProfile);
        }

        E::ModuleViewer()->Assign('sMode', $sMode);
        E::ModuleViewer()->Assign('oUserProfile', $oUserProfile);
        E::ModuleViewer()->Assign('aUserVoteStat', $aUserVoteStat);
        E::ModuleViewer()->Assign('nParamVoteValue', 1);

    }

    /**
     * @param ModuleUser_EntityUser $oUserProfile
     */
    protected function _eventUsersProfileInfo($oUserProfile) {

        /** @var ModuleUser_EntityUser[] $aUsersFriend */
        $aUsersFriend = E::ModuleUser()->GetUsersFriend($oUserProfile->getId(), 1, 10);
        /** @var ModuleUser_EntityUser[] $aUserInvite */
        $aUsersInvite = E::ModuleUser()->GetUsersInvite($oUserProfile->getId());
        $oUserInviteFrom = E::ModuleUser()->GetUserInviteFrom($oUserProfile->getId());
        $aBlogsOwner = E::ModuleBlog()->GetBlogsByOwnerId($oUserProfile->getId());
        $aBlogsModeration = E::ModuleBlog()->GetBlogUsersByUserId($oUserProfile->getId(), ModuleBlog::BLOG_USER_ROLE_MODERATOR);
        $aBlogsAdministration = E::ModuleBlog()->GetBlogUsersByUserId($oUserProfile->getId(), ModuleBlog::BLOG_USER_ROLE_ADMINISTRATOR);
        $aBlogsUser = E::ModuleBlog()->GetBlogUsersByUserId($oUserProfile->getId(), ModuleBlog::BLOG_USER_ROLE_USER);
        $aBlogsBanUser = E::ModuleBlog()->GetBlogUsersByUserId($oUserProfile->getId(), ModuleBlog::BLOG_USER_ROLE_BAN);
        $aLastTopicList = E::ModuleTopic()->GetLastTopicsByUserId($oUserProfile->getId(), Config::Get('acl.create.topic.limit_time')*1000000, 3);
        $iCountTopicsByUser = E::ModuleTopic()->GetCountTopicsByFilter(array('user_id' => $oUserProfile->getId()));
        $iCountCommentsByUser = E::ModuleComment()->GetCountCommentsByUserId($oUserProfile->getId(), 'topic');

        E::ModuleViewer()->Assign('aUsersFriend', isset($aUsersFriend['collection'])?$aUsersFriend['collection']:false);
        E::ModuleViewer()->Assign('aUsersInvite', $aUsersInvite);
        E::ModuleViewer()->Assign('oUserInviteFrom', $oUserInviteFrom);
        E::ModuleViewer()->Assign('aBlogsOwner', $aBlogsOwner);
        E::ModuleViewer()->Assign('aBlogsModeration', $aBlogsModeration);
        E::ModuleViewer()->Assign('aBlogsAdministration', $aBlogsAdministration);
        E::ModuleViewer()->Assign('aBlogsUser', $aBlogsUser);
        E::ModuleViewer()->Assign('aBlogsBanUser', $aBlogsBanUser);
        E::ModuleViewer()->Assign('iCountTopicsByUser', $iCountTopicsByUser);
        E::ModuleViewer()->Assign('iCountCommentsByUser', $iCountCommentsByUser);
        E::ModuleViewer()->Assign('aLastTopicList', isset($aLastTopicList['collection'])?$aLastTopicList['collection']:false);

        $this->SetTemplateAction('users/profile_info');
    }

    protected function _eventUsersFilter() {

        $aFilter = array();

        if (($sUserLogin = $this->GetPost('user_filter_login'))) {
            $aFilter['login'] = $sUserLogin;
        } else {
            $aFilter['login'] = null;
        }

        if (($sUserEmail = $this->GetPost('user_filter_email'))) {
            $aFilter['email'] = $sUserEmail;
        } else {
            $aFilter['email'] = null;
        }

        $aUserFilterIp = array('*', '*', '*', '*');
        if (is_numeric($n = $this->GetPost('user_filter_ip1')) && $n < 256) {
            $aUserFilterIp[0] = $n;
        }
        if (is_numeric($n = $this->GetPost('user_filter_ip2')) && $n < 256) {
            $aUserFilterIp[1] = $n;
        }
        if (is_numeric($n = $this->GetPost('user_filter_ip3')) && $n < 256) {
            $aUserFilterIp[2] = $n;
        }
        if (is_numeric($n = $this->GetPost('user_filter_ip4')) && $n < 256) {
            $aUserFilterIp[3] = $n;
        }

        $sUserFilterIp = implode('.', $aUserFilterIp);
        if ($sUserFilterIp != '*.*.*.*') {
            $aFilter['ip'] = $sUserFilterIp;
        } else {
            $aFilter['ip'] = null;
        }

        if (($sDate = F::GetRequest('user_filter_regdate'))) {
            if (preg_match('/(\d{4})(\-(\d{1,2})){0,1}(\-(\d{1,2})){0,1}/', $sDate, $aMatch)) {
                if (isset($aMatch[1])) {
                    $sUserRegDate = $aMatch[1];
                    if (isset($aMatch[3])) {
                        $sUserRegDate .= '-' . sprintf('%02d', $aMatch[3]);
                        if (isset($aMatch[5])) {
                            $sUserRegDate .= '-' . sprintf('%02d', $aMatch[5]);
                        }
                    }
                }
            }
            if ($sUserRegDate) {
                $aFilter['regdate'] = $sUserRegDate;
            } else {
                $aFilter['regdate'] = null;
            }
        }
        E::ModuleSession()->Set('adm_userlist_filter', serialize($aFilter));
    }

    /**
     * Deletes user
     *
     * @return bool
     */
    protected function _eventUsersCmdDelete() {

        E::ModuleSecurity()->ValidateSendForm();

        $aUsersId = F::Str2Array(F::GetRequest('adm_user_list'), ',', true);
        $bResult = true;
        foreach ($aUsersId as $iUserId) {
            if ($iUserId == $this->oUserCurrent->GetId()) {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.cannot_del_self'), null, true);
                $bResult = false;
                break;
            } elseif (($oUser = E::ModuleUser()->GetUserById($iUserId))) {
                if ($oUser->IsAdministrator()) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.cannot_del_admin'), null, true);
                    $bResult = false;
                    break;
                } elseif (!F::GetRequest('adm_user_del_confirm') && !F::GetRequest('adm_bulk_confirm')) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.cannot_del_confirm'), null, true);
                    $bResult = false;
                    break;
                } else {
                    E::ModuleAdmin()->DelUser($oUser->GetId());
                    E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.user_deleted', Array('user' => $oUser->getLogin())), null, true);
                }
            } else {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.user_not_found'), null, true);
                $bResult = false;
                break;
            }
        }
        return $bResult;
    }

    protected function _eventUsersCmdMessage() {

        if ($this->GetPost('send_common_message') == 'yes') {
            $this->_eventUsersCmdMessageCommon();
        } else {
            $this->_eventUsersCmdMessageSeparate();
        }
    }

    protected function _eventUsersCmdMessageCommon() {

        $bOk = true;

        $sTitle = $this->GetPost('talk_title');
        $sText = E::ModuleText()->Parser(F::GetRequest('talk_text'));
        $sDate = date(F::Now());
        $sIp = F::GetUserIp();

        if (($sUsers = $this->GetPost('users_list'))) {
            $aUsers = explode(',', str_replace(' ', '', $sUsers));
        } else {
            $aUsers = array();
        }

        if ($aUsers) {
            if ($bOk && $aUsers) {
                $oTalk = E::GetEntity('Talk_Talk');
                $oTalk->setUserId($this->oUserCurrent->getId());
                $oTalk->setUserIdLast($this->oUserCurrent->getId());
                $oTalk->setTitle($sTitle);
                $oTalk->setText($sText);
                $oTalk->setDate($sDate);
                $oTalk->setDateLast($sDate);
                $oTalk->setUserIp($sIp);
                $oTalk = E::ModuleTalk()->AddTalk($oTalk);

                // добавляем себя в общий список
                $aUsers[] = $this->oUserCurrent->getLogin();
                // теперь рассылаем остальным
                foreach ($aUsers as $sUserLogin) {
                    if ($sUserLogin && ($oUserRecipient = E::ModuleUser()->GetUserByLogin($sUserLogin))) {
                        $oTalkUser = E::GetEntity('Talk_TalkUser');
                        $oTalkUser->setTalkId($oTalk->getId());
                        $oTalkUser->setUserId($oUserRecipient->GetId());
                        if ($sUserLogin != $this->oUserCurrent->getLogin()) {
                            $oTalkUser->setDateLast(null);
                        } else {
                            $oTalkUser->setDateLast($sDate);
                        }
                        E::ModuleTalk()->AddTalkUser($oTalkUser);

                        // Отправляем уведомления
                        if ($sUserLogin != $this->oUserCurrent->getLogin() || F::GetRequest('send_copy_self')) {
                            $oUserToMail = E::ModuleUser()->GetUserById($oUserRecipient->GetId());
                            E::ModuleNotify()->SendTalkNew($oUserToMail, $this->oUserCurrent, $oTalk);
                        }
                    }
                }
            }
        }

        if ($bOk) {
            E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.msg_sent_ok'), null, true);
        } else {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), null, true);
        }
    }

    protected function _eventUsersCmdMessageSeparate() {

        $bOk = true;

        $sTitle = F::GetRequest('talk_title');

        $sText = E::ModuleText()->Parser(F::GetRequest('talk_text'));
        $sDate = date(F::Now());
        $sIp = F::GetUserIp();

        if (($sUsers = $this->GetPost('users_list'))) {
            $aUsers = explode(',', str_replace(' ', '', $sUsers));
        } else {
            $aUsers = array();
        }

        if ($aUsers) {
            // Если указано, то шлем самому себе со списком получателей
            if (F::GetRequest('send_copy_self')) {
                $oSelfTalk = E::GetEntity('Talk_Talk');
                $oSelfTalk->setUserId($this->oUserCurrent->getId());
                $oSelfTalk->setUserIdLast($this->oUserCurrent->getId());
                $oSelfTalk->setTitle($sTitle);
                $oSelfTalk->setText(E::ModuleText()->Parser('To: <i>' . $sUsers . '</i>' . "\n\n" . 'Msg: ' . $this->GetPost('talk_text')));
                $oSelfTalk->setDate($sDate);
                $oSelfTalk->setDateLast($sDate);
                $oSelfTalk->setUserIp($sIp);
                if (($oSelfTalk = E::ModuleTalk()->AddTalk($oSelfTalk))) {
                    $oTalkUser = E::GetEntity('Talk_TalkUser');
                    $oTalkUser->setTalkId($oSelfTalk->getId());
                    $oTalkUser->setUserId($this->oUserCurrent->getId());
                    $oTalkUser->setDateLast($sDate);
                    E::ModuleTalk()->AddTalkUser($oTalkUser);

                    // уведомление по e-mail
                    $oUserToMail = $this->oUserCurrent;
                    E::ModuleNotify()->SendTalkNew($oUserToMail, $this->oUserCurrent, $oSelfTalk);
                } else {
                    $bOk = false;
                }
            }

            if ($bOk) {
                // теперь рассылаем остальным - каждому отдельное сообщение
                foreach ($aUsers as $sUserLogin) {
                    if ($sUserLogin && $sUserLogin != $this->oUserCurrent->getLogin() && ($oUserRecipient = E::ModuleUser()->GetUserByLogin($sUserLogin))) {
                        $oTalk = E::GetEntity('Talk_Talk');
                        $oTalk->setUserId($this->oUserCurrent->getId());
                        $oTalk->setUserIdLast($this->oUserCurrent->getId());
                        $oTalk->setTitle($sTitle);
                        $oTalk->setText($sText);
                        $oTalk->setDate($sDate);
                        $oTalk->setDateLast($sDate);
                        $oTalk->setUserIp($sIp);
                        if (($oTalk = E::ModuleTalk()->AddTalk($oTalk))) {
                            $oTalkUser = E::GetEntity('Talk_TalkUser');
                            $oTalkUser->setTalkId($oTalk->getId());
                            $oTalkUser->setUserId($oUserRecipient->GetId());
                            $oTalkUser->setDateLast(null);
                            E::ModuleTalk()->AddTalkUser($oTalkUser);

                            // Отправка самому себе, чтобы можно было читать ответ
                            $oTalkUser = E::GetEntity('Talk_TalkUser');
                            $oTalkUser->setTalkId($oTalk->getId());
                            $oTalkUser->setUserId($this->oUserCurrent->getId());
                            $oTalkUser->setDateLast($sDate);
                            E::ModuleTalk()->AddTalkUser($oTalkUser);

                            // Отправляем уведомления
                            $oUserToMail = E::ModuleUser()->GetUserById($oUserRecipient->GetId());
                            E::ModuleNotify()->SendTalkNew($oUserToMail, $this->oUserCurrent, $oTalk);
                        } else {
                            $bOk = false;
                            break;
                        }
                    }
                }
            }
        }

        if ($bOk) {
            E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.msg_sent_ok'), null, true);
        } else {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), null, true);
        }
    }

    protected function _eventUsersCmdActivate() {

        if (($sUsers = $this->GetPost('users_list'))) {
            $aUsers = explode(',', str_replace(' ', '', $sUsers));
        } else {
            $aUsers = array();
        }
        if ($aUsers) {
            foreach ($aUsers as $sUserLogin) {
                $oUser = E::ModuleUser()->GetUserByLogin($sUserLogin);
                $oUser->setActivate(1);
                $oUser->setDateActivate(F::Now());
                E::ModuleUser()->Update($oUser);
            }
        }
        R::ReturnBack();
    }

    /**********************************************************************************/

    protected function EventInvites() {

        $this->sMainMenuItem = 'users';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.invites_title'));
        $this->SetTemplateAction('users/invites_list');

        $sMode = $this->GetParam(0);
        if ($sMode == 'add') {
            $this->_eventInvitesAdd();
        } else {
            $this->_eventInvitesList($sMode);
        }

        if ($this->oUserCurrent->isAdministrator()) {
            $iCountInviteAvailable = -1;
        } else {
            $iCountInviteAvailable = E::ModuleUser()->GetCountInviteAvailable($this->oUserCurrent);
        }
        E::ModuleViewer()->Assign('iCountInviteAvailable', $iCountInviteAvailable);
        E::ModuleViewer()->Assign('iCountInviteUsed', E::ModuleUser()->GetCountInviteUsed($this->oUserCurrent->getId()));
    }

    protected function _eventInvitesList($sMode) {

        if (F::GetRequest('action', null, 'post') == 'delete') {
            $this->_eventInvitesDelete();
        }

        $nPage = $this->_getPageNum();

        if ($sMode == 'used') {
            $aFilter = array(
                'used' => true,
            );
        } elseif ($sMode == 'unused') {
            $aFilter = array(
                'unused' => true,
            );
        } else {
            $sMode = 'all';
            $aFilter = array();
        }
        // Получаем список инвайтов
        $aResult = E::ModuleAdmin()->GetInvites($nPage, Config::Get('admin.items_per_page'), $aFilter);
        $aInvites = $aResult['collection'];
        $aCounts = E::ModuleAdmin()->GetInvitesCount();

        // Формируем постраничность
        $aPaging = E::ModuleViewer()->MakePaging($aResult['count'], $nPage, Config::Get('admin.items_per_page'), 4, R::GetPath('admin') . 'users-invites');
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        E::ModuleViewer()->Assign('aInvites', $aInvites);
        E::ModuleViewer()->Assign('aCounts', $aCounts);
        E::ModuleViewer()->Assign('sMode', $sMode);
    }

    protected function _eventInvitesDelete() {

        E::ModuleSecurity()->ValidateSendForm();

        $aIds = array();
        foreach ($_POST as $sKey => $sVal) {
            if ((substr($sKey, 0, 7) == 'invite_') && ($nId = intval(substr($sKey, 7)))) {
                $aIds[] = $nId;
            }
        }
        if ($aIds) {
            $nResult = E::ModuleAdmin()->DeleteInvites($aIds);
            E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.invaite_deleted', array('num' => $nResult)));
        }
        R::ReturnBack(true);
    }

    /**********************************************************************************/

    protected function EventBanlist() {

        $this->sMainMenuItem = 'users';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.banlist_title'));
        $sMode = $this->_getMode(0, 'ids');
        $nPage = $this->_getPageNum();

        if ($sCmd = $this->GetPost('adm_user_cmd')) {
            $this->_eventBanListCmd($sCmd);
        }
        if ($sMode == 'ips') {
            $this->_eventBanlistIps($nPage);
        } else {
            $sMode = 'ids';
            $this->_eventBanlistIds($nPage);
        }
        E::ModuleViewer()->Assign('sMode', $sMode);
    }

    protected function _eventBanListCmd($sCmd) {

        if ($sCmd == 'adm_ban_user') {
            $sUsersList = $this->GetPost('user_login');
            $nBanDays = $this->GetPost('ban_days');
            $sBanComment = $this->GetPost('ban_comment');

            $sIp = $this->GetPost('user_ban_ip1');
            if ($sIp) {
                $sIp .= '.' . $this->GetPost('user_ban_ip2', '0')
                    . '.' . $this->GetPost('user_ban_ip3', '0')
                    . '.' . $this->GetPost('user_ban_ip4', '0');
            }

            if ($sUsersList) {
                // здесь получаем логины юзеров
                $aUsersLogin = F::Array_Str2Array($sUsersList);
                // по логинам получаем список юзеров
                $aUsers = E::ModuleUser()->GetUsersByFilter(array('login' => $aUsersLogin), '', 1, 100, array());
                if ($aUsers) {
                    // и их баним
                    $this->_eventUsersCmdBan(array_keys($aUsers['collection']), $nBanDays, $sBanComment);
                }
            } elseif ($sIp) {
                $this->_eventIpsCmdBan($sIp, $nBanDays, $sBanComment);
            }
        } elseif ($sCmd == 'adm_unsetban_ip') {
            $aId = F::Array_Str2ArrayInt($this->GetPost('bans_list'), ',', true);
            E::ModuleAdmin()->UnsetBanIp($aId);
        } elseif ($sCmd == 'adm_unsetban_user') {
            $aUsersId = F::Array_Str2ArrayInt($this->GetPost('bans_list'), ',', true);
            $this->_eventUsersCmdUnban($aUsersId);
        }
        R::ReturnBack(true);
    }

    protected function _eventBanlistIds($nPage) {

        $this->SetTemplateAction('users/banlist_ids');

        // Получаем список забаненных юзеров
        $aResult = E::ModuleAdmin()->GetUsersBanList($nPage, Config::Get('admin.items_per_page'));

        // Формируем постраничность
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $nPage, Config::Get('admin.items_per_page'), 4, R::GetPath('admin') . 'banlist/ids/'
        );
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        E::ModuleViewer()->Assign('aUserList', $aResult['collection']);
    }

    protected function _eventBanlistIps($nPage) {

        $this->SetTemplateAction('users/banlist_ips');

        // Получаем список забаненных ip-адресов
        $aResult = E::ModuleAdmin()->GetIpsBanList($nPage, Config::Get('admin.items_per_page'));

        // Формируем постраничность
        $aPaging = E::ModuleViewer()->MakePaging(
            $aResult['count'], $nPage, Config::Get('admin.items_per_page'), 4, R::GetPath('admin') . 'banlist/ips/'
        );
        E::ModuleViewer()->Assign('aPaging', $aPaging);
        E::ModuleViewer()->Assign('aIpsList', $aResult['collection']);
    }

    /**********************************************************************************/

    protected function _getSkinFromConfig($sSkin) {

        $sSkinTheme = null;
        if (F::File_Exists($sFile = Config::Get('path.skins.dir') . $sSkin . '/settings/config/config.php')) {
            $aSkinConfig = F::IncludeFile($sFile, false, true);
            if (isset($aSkinConfig['view']) && isset($aSkinConfig['view']['theme'])) {
                $sSkinTheme = $aSkinConfig['view']['theme'];
            } elseif (isset($aSkinConfig['view.theme'])) {
                $sSkinTheme = $aSkinConfig['view.theme'];
            }
        }
        return $sSkinTheme;
    }

    protected function EventSkins() {

        $this->sMainMenuItem = 'site';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.skins_title'));
        $this->SetTemplateAction('site/skins');

        // Определяем скин и тему основного сайта (не админки)
        $sSiteSkin = Config::Get('view.skin', Config::LEVEL_CUSTOM);
        $sSiteTheme = Config::Get('skin.' . $sSiteSkin . '.config.view.theme');

        // Определяем скин и тему админки
        $sAdminSkin = Config::Get('view.skin');
        $sAdminTheme = Config::Get('skin.' . $sAdminSkin . '.config.view.theme');

        if (!$sSiteTheme && ($sSkinTheme = $this->_getSkinFromConfig($sSiteSkin))) {
            $sSiteTheme = $sSkinTheme;
        }

        if (!$sAdminTheme && ($sSkinTheme = $this->_getSkinFromConfig($sAdminSkin))) {
            $sAdminTheme = $sSkinTheme;
        }

        $sMode = $this->GetParam(0);
        if ($sMode == 'adm') {
            $aFilter = array('type' => 'adminpanel');
        } elseif ($sMode == 'all') {
            $aFilter = array('type' => '');
        } else {
            $sMode = 'site';
            $aFilter = array('type' => 'site');
        }
        if ($this->GetPost('submit_skins_del')) {
            // Удаление плагинов
            $this->_eventSkinsDelete($sMode);
        } elseif ($sSkin = $this->GetPost('skin_activate')) {
            $this->_eventSkinActivate($sMode, $sSkin);
        } elseif (($sSkin = $this->GetPost('skin')) && ($sTheme = $this->GetPost('theme_activate'))) {
            $this->_eventSkinThemeActivate($sMode, $sSkin, $sTheme);
        }

        $aSkins = E::ModuleSkin()->GetSkinsList($aFilter);
        $oActiveSkin = null;
        foreach ($aSkins as $sKey => $oSkin) {
            if ($sMode == 'adm') {
                if ($sKey == $sAdminSkin) {
                    $oActiveSkin = $oSkin;
                    unset($aSkins[$sKey]);
                }
            } else {
                if ($sKey == $sSiteSkin) {
                    $oActiveSkin = $oSkin;
                    unset($aSkins[$sKey]);
                }
            }
        }

        if ($sMode == 'adm') {
            E::ModuleViewer()->Assign('sSiteSkin', $sAdminSkin);
            E::ModuleViewer()->Assign('sSiteTheme', $sAdminTheme);
        } else {
            E::ModuleViewer()->Assign('sSiteSkin', $sSiteSkin);
            E::ModuleViewer()->Assign('sSiteTheme', $sSiteTheme);
        }

        E::ModuleViewer()->Assign('oActiveSkin', $oActiveSkin);
        E::ModuleViewer()->Assign('aSkins', $aSkins);
        E::ModuleViewer()->Assign('sMode', $sMode);
    }

    protected function _eventSkinActivate($sMode, $sSkin) {

        $aConfig = array('view.skin' => $sSkin);
        Config::WriteCustomConfig($aConfig);
        R::Location('admin/site-skins/' . $sMode . '/');
    }

    protected function _eventSkinThemeActivate($sMode, $sSkin, $sTheme) {

        $aConfig = array('skin.' . $sSkin . '.config.view.theme' => $sTheme);
        Config::WriteCustomConfig($aConfig);
        R::Location('admin/site-skins/' . $sMode . '/');
    }

    /**********************************************************************************/

    /**
     * View logs
     */
    protected function EventLogs() {

        $this->sMainMenuItem = 'logs';

        if ($this->sCurrentEvent == 'logs-sqlerror') {
            $sLogFile = Config::Get('sys.logs.dir') . Config::Get('sys.logs.sql_error_file');
        } elseif ($this->sCurrentEvent == 'logs-sqllog') {
            $sLogFile = Config::Get('sys.logs.dir') . Config::Get('sys.logs.sql_query_file');
        } else {
            $sLogFile = Config::Get('sys.logs.dir') . F::ERROR_LOGFILE;
        }

        if (!is_null($this->GetPost('submit_logs_del'))) {
            $this->_eventLogsErrorDelete($sLogFile);
        }

        $sLogTxt = F::File_GetContents($sLogFile);
        if ($this->sCurrentEvent == 'logs-sqlerror') {
            $this->_setTitle(E::ModuleLang()->Get('action.admin.logs_sql_errors_title'));
            $this->SetTemplateAction('logs/sql_errors');
            $this->_eventLogsSqlErrors($sLogTxt);
        } elseif ($this->sCurrentEvent == 'logs-sqllog') {
            $this->_setTitle(E::ModuleLang()->Get('action.admin.logs_sql_title'));
            $this->SetTemplateAction('logs/sql_log');
            $this->_eventLogsSql($sLogTxt);
        } else {
            $this->_setTitle(E::ModuleLang()->Get('action.admin.logs_errors_title'));
            $this->SetTemplateAction('logs/errors');
            $this->_eventLogsErrors($sLogTxt);
        }

        E::ModuleViewer()->Assign('sLogTxt', $sLogTxt);
    }

    protected function _eventLogsErrorDelete($sLogFile) {

        F::File_Delete($sLogFile);
    }

    protected function _parseLog($sLogTxt) {

        $aLogs = array();
        if (preg_match_all('/\[LOG\:(?<id>[\d\-\.\,A-F]+)\]\[(?<date>[\d\-\s\:]+)\].*\[\[(?<text>.*)\]\]/siuU', $sLogTxt, $aM, PREG_PATTERN_ORDER)) {
            if (preg_last_error() == PREG_BACKTRACK_LIMIT_ERROR) {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.logs_too_long'), null);
            }
            foreach ($aM[0] as $nRec => $sVal) {
                $aRec = array(
                    'id' => $aM['id'][$nRec],
                    'date' => $aM['date'][$nRec],
                    'text' => $aM['text'][$nRec],
                );
                array_unshift($aLogs, $aRec);
            }
        } else {
            $aTmp = array();
            // Текст кривой, поэтому будем так
            $aParts = explode('[LOG:', $sLogTxt);
            if ($aParts) {
                foreach ($aParts as $sPart) {
                    if ($sPart) {
                        $aRec = array('id' => '', 'date' => '', 'text' => $sPart);
                        $nPos = strpos($sPart, ']');
                        if ($nPos) {
                            $aRec['id'] = substr($sPart, 0, $nPos);
                            $aRec['text'] = substr($aRec['text'], $nPos+1);
                        }
                        if (preg_match('/^\[(\d{4}\-\d{2}\-\d{2}\s\d{2}\:\d{2}\:\d{2})\]/', $aRec['text'])) {
                            $aRec['date'] = substr($aRec['text'], 1, 19);
                            $aRec['text'] = substr($aRec['text'], 21);
                        }
                        $nPos = strpos($aRec['text'], '[END:' . $aRec['id'] . ']');
                        if ($nPos) {
                            $aRec['text'] = substr($aRec['text'], 0, $nPos);
                        }
                        if (preg_match('/\[\[(.*)\]\]/siuU', $aRec['text'], $aM)) {
                            $aRec['text'] = trim($aM[1]);
                        }
                        $aTmp[] = $aRec;
                    }
                }
            }
            $aLogs = array_reverse($aTmp);
        }
        return $aLogs;
    }

    /**
     * Runtime errors of engine
     *
     * @param $sLogTxt
     */
    protected function _eventLogsErrors($sLogTxt) {

        $aLogs = $this->_parseLog($sLogTxt);
        foreach ($aLogs as $nRec => $aRec) {
            if ($n = strpos($aRec['text'], '---')) {
                $aRec['text'] = nl2br(trim(substr($aRec['text'], 0, $n)));
            } else {
                $aRec['text'] = nl2br(trim($aRec['text']));
            }
            $aLogs[$nRec] = $aRec;
        }

        E::ModuleViewer()->Assign('aLogs', $aLogs);
    }

    protected function _eventLogsSqlErrors($sLogTxt) {

        $aLogs = $this->_parseLog($sLogTxt);
        foreach ($aLogs as $nRec => $aRec) {
            if ($n = strpos($aRec['text'], '---')) {
                $aRec['info'] = trim(substr($aRec['text'], $n + 3));
                $aRec['sql'] = '';
                if (strpos($aRec['info'], 'Array') !== false && preg_match('/\[query\]\s*\=\>(.*)\[context\]\s*\=\>(.*)$/siuU', $aRec['info'], $aM)) {
                    $aRec['sql'] = trim($aM[1]);
                }
                $aRec['text'] = trim(substr($aRec['text'], 0, $n));
            } else {
                $aRec['info'] = '';
                $aRec['sql'] = '';
                $aRec['text'] = trim($aRec['text']);
            }
            $aLogs[$nRec] = $aRec;
        }

        E::ModuleViewer()->Assign('aLogs', $aLogs);
    }

    protected function _eventLogsSql($sLogTxt) {

        $aLogs = $this->_parseLog($sLogTxt);
        foreach ($aLogs as $nRec => $aRec) {
            if (preg_match('/--\s(\d+)\s(\ws);(.*)$/U', $aRec['text'], $aM, PREG_OFFSET_CAPTURE)) {
                $aRec['text'] = trim(substr($aRec['text'], 0, $aM[0][1]));
                $aRec['time'] = $aM[1][0] . ' ' . $aM[2][0];
                $aRec['result'] = trim($aM[3][0]);
                if (($n = strpos($aRec['result'], 'returned')) !== false) {
                    $aRec['result'] = trim(substr($aRec['result'], 8));
                }
            } else {
                $aRec['text'] = trim($aRec['text']);
                $aRec['time'] = 'unknown';
                $aRec['result'] = '';
            }
            $aLogs[$nRec] = $aRec;
        }

        E::ModuleViewer()->Assign('aLogs', $aLogs);
    }

    /**********************************************************************************/

    protected function EventReset() {

        $this->sMainMenuItem = 'tools';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.reset_title'));
        $this->SetTemplateAction('tools/reset');

        $aSettings = array();
        if ($this->GetPost('adm_reset_submit')) {
            $aConfig = array();
            if ($this->GetPost('adm_cache_clear_data')) {
                E::ModuleCache()->Clean();
                $aSettings['adm_cache_clear_data'] = 1;
            }
            if ($this->GetPost('adm_cache_clear_assets')) {
                E::ModuleViewer()->ClearAssetsFiles();
                $aConfig['assets.version'] = time();
                $aSettings['adm_cache_clear_assets'] = 1;
            }
            if ($this->GetPost('adm_cache_clear_smarty')) {
                E::ModuleViewer()->ClearSmartyFiles();
                $aSettings['adm_cache_clear_smarty'] = 1;
            }
            if ($this->GetPost('adm_reset_config_data')) {
                $this->_eventResetCustomConfig();
                $aSettings['adm_reset_config_data'] = 1;
            }

            if ($aConfig) {
                Config::WriteCustomConfig($aConfig);
            }
            E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.action_ok'), null, true);

            if ($aSettings) {
                E::ModuleSession()->SetCookie('adm_tools_reset', serialize($aSettings));
            } else {
                E::ModuleSession()->DelCookie('adm_tools_reset');
            }
            R::Location('admin/tools-reset/');
        }
        if ($sSettings = E::ModuleSession()->GetCookie('adm_tools_reset')) {
            $aSettings = @unserialize($sSettings);
            if (is_array($aSettings)) {
                E::ModuleViewer()->Assign('aSettings', $aSettings);
            }
        }
    }

    /**
     * Сброс кастомного конфига
     */
    protected function _eventResetCustomConfig() {

        Config::ResetCustomConfig();
    }

    /**********************************************************************************/

    /**
     * Перестроение дерева комментариев, актуально при $config['module']['comment']['use_nested'] = true;
     *
     */
    protected function EventCommentsTree() {

        $this->sMainMenuItem = 'tools';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.comments_tree_title'));
        $this->SetTemplateAction('tools/comments_tree');
        if (F::isPost('comments_tree_submit')) {
            E::ModuleSecurity()->ValidateSendForm();
            set_time_limit(0);
            E::ModuleComment()->RestoreTree();
            E::ModuleCache()->Clean();

            E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('comments_tree_restored'), E::ModuleLang()->Get('attention'));
            E::ModuleViewer()->Assign('bActionEnable', false);
        } else {
            if (Config::Get('module.comment.use_nested')) {
                E::ModuleViewer()->Assign('sMessage', E::ModuleLang()->Get('action.admin.comments_tree_message'));
                E::ModuleViewer()->Assign('bActionEnable', true);
            } else {
                E::ModuleViewer()->Assign('sMessage', E::ModuleLang()->Get('action.admin.comments_tree_disabled'));
                E::ModuleViewer()->Assign('bActionEnable', false);
            }
        }
    }

    /**********************************************************************************/

    /**
     * Пересчет счетчика избранных
     *
     */
    protected function EventRecalculateFavourites() {

        $this->sMainMenuItem = 'tools';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.recalcfavourites_title'));
        $this->SetTemplateAction('tools/recalcfavourites');
        if (F::isPost('recalcfavourites_submit')) {
            E::ModuleSecurity()->ValidateSendForm();
            set_time_limit(0);
            E::ModuleComment()->RecalculateFavourite();
            E::ModuleTopic()->RecalculateFavourite();
            E::ModuleCache()->Clean();

            E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.favourites_recalculated'), E::ModuleLang()->Get('attention'));
            E::ModuleViewer()->Assign('bActionEnable', false);
        } else {
            E::ModuleViewer()->Assign('sMessage', E::ModuleLang()->Get('action.admin.recalcfavourites_message'));
            E::ModuleViewer()->Assign('bActionEnable', true);
        }
    }

    /**********************************************************************************/

    /**
     * Пересчет счетчика голосований
     */
    protected function EventRecalculateVotes() {

        $this->sMainMenuItem = 'tools';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.recalcvotes_title'));
        $this->SetTemplateAction('tools/recalcvotes');
        if (F::isPost('recalcvotes_submit')) {
            E::ModuleSecurity()->ValidateSendForm();
            set_time_limit(0);
            E::ModuleTopic()->RecalculateVote();
            E::ModuleCache()->Clean();

            E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.votes_recalculated'), E::ModuleLang()->Get('attention'));
        } else {
            E::ModuleViewer()->Assign('sMessage', E::ModuleLang()->Get('action.admin.recalcvotes_message'));
            E::ModuleViewer()->Assign('bActionEnable', true);
        }
    }

    /**********************************************************************************/

    /**
     * Пересчет количества топиков в блогах
     */
    protected function EventRecalculateTopics() {

        $this->sMainMenuItem = 'tools';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.recalctopics_title'));
        $this->SetTemplateAction('tools/recalctopics');
        if (F::isPost('recalctopics_submit')) {
            E::ModuleSecurity()->ValidateSendForm();
            set_time_limit(0);
            E::ModuleBlog()->RecalculateCountTopic();
            E::ModuleCache()->Clean();

            E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.topics_recalculated'), E::ModuleLang()->Get('attention'));
        } else {
            E::ModuleViewer()->Assign('sMessage', E::ModuleLang()->Get('action.admin.recalctopics_message'));
            E::ModuleViewer()->Assign('bActionEnable', true);
        }
    }

    /**
     * Пересчет рейтинга блогов
     */
    protected function EventRecalculateBlogRating() {

        $this->sMainMenuItem = 'tools';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.recalcblograting_title'));
        $this->SetTemplateAction('tools/recalcblograting');
        if (F::isPost('recalcblograting_submit')) {
            E::ModuleSecurity()->ValidateSendForm();
            set_time_limit(0);
            E::ModuleRating()->RecalculateBlogRating();
            E::ModuleCache()->Clean();

            E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.blograting_recalculated'), E::ModuleLang()->Get('attention'));
        } else {
            E::ModuleViewer()->Assign('sMessage', E::ModuleLang()->Get('action.admin.recalcblograting_message'));
            E::ModuleViewer()->Assign('bActionEnable', true);
        }
    }

    /**
     * Контроль БД
     */
    protected function EventCheckDb() {

        $this->sMainMenuItem = 'tools';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.checkdb_title'));
        $this->SetTemplateAction('tools/checkdb');

        $sMode = $this->getParam(0, 'db');
        if ($sMode == 'blogs') {
            $this->_eventCheckDbBlogs();
        } elseif ($sMode == 'topics') {
            $this->_eventCheckDbTopics();
        }
        E::ModuleViewer()->Assign('sMode', $sMode);
    }

    protected function _eventCheckDbBlogs() {

        $this->SetTemplateAction('tools/checkdb_blogs');
        $sDoAction = F::GetRequest('do_action');
        if ($sDoAction == 'clear_blogs_joined') {
            $aJoinedBlogs = E::ModuleAdmin()->GetUnlinkedBlogsForUsers();
            if ($aJoinedBlogs) {
                E::ModuleAdmin()->DelUnlinkedBlogsForUsers(array_keys($aJoinedBlogs));
            }
        } elseif ($sDoAction == 'clear_blogs_co') {
            $aCommentsOnlineBlogs = E::ModuleAdmin()->GetUnlinkedBlogsForCommentsOnline();
            if ($aCommentsOnlineBlogs) {
                E::ModuleAdmin()->DelUnlinkedBlogsForCommentsOnline(array_keys($aCommentsOnlineBlogs));
            }
        }
        $aJoinedBlogs = E::ModuleAdmin()->GetUnlinkedBlogsForUsers();
        $aCommentsOnlineBlogs = E::ModuleAdmin()->GetUnlinkedBlogsForCommentsOnline();
        E::ModuleViewer()->Assign('aJoinedBlogs', $aJoinedBlogs);
        E::ModuleViewer()->Assign('aCommentsOnlineBlogs', $aCommentsOnlineBlogs);
    }

    protected function _eventCheckDbTopics() {

        $this->SetTemplateAction('tools/checkdb_topics');
        $sDoAction = F::GetRequest('do_action');
        if ($sDoAction == 'clear_topics_co') {
            $aCommentsOnlineBlogs = E::ModuleAdmin()->GetUnlinkedTopicsForCommentsOnline();
            if ($aCommentsOnlineBlogs) {
                E::ModuleAdmin()->DelUnlinkedTopicsForCommentsOnline(array_keys($aCommentsOnlineBlogs));
            }
        }
        $aCommentsOnlineTopics = E::ModuleAdmin()->GetUnlinkedTopicsForCommentsOnline();
        E::ModuleViewer()->Assign('aCommentsOnlineTopics', $aCommentsOnlineTopics);
    }

    /**********************************************************************************/

    /**
     *
     */
    protected function EventLang() {

        $this->sMainMenuItem = 'settings';

        $aLanguages = E::ModuleLang()->GetAvailableLanguages();
        $aAllows = (array)Config::Get('lang.allow');
        if (!$aAllows) $aAllows = array(Config::Get('lang.current'));
        if (!$aAllows) $aAllows = array(Config::Get('lang.default'));
        if (!$aAllows) $aAllows = array('ru');
        $aLangAllow = array();
        if ($sLang = Config::Get('lang.current')) {
            $n = array_search($sLang, $aAllows);
            if ($n !== false && isset($aLanguages[$sLang])) {
                $aLangAllow[$sLang] = $aLanguages[$sLang];
                $aLangAllow[$sLang]['current'] = true;
                unset($aAllows[$n]);
                unset($aLanguages[$sLang]);
            }
        }
        foreach($aAllows as $sLang) {
            if (isset($aLanguages[$sLang])) {
                $aLangAllow[$sLang] = $aLanguages[$sLang];
                $aLangAllow[$sLang]['current'] = false;
                unset($aLanguages[$sLang]);
            }
        }

        if ($this->GetPost('submit_data_save')) {
            $aConfig = array();

            // добавление новых языков в список используемых
            $aAddLangs = $this->GetPost('lang_allow');
            if ($aAddLangs) {
                $aAliases = (array)Config::Get('lang.aliases');
                foreach($aAddLangs as $sLang) {
                    if (isset($aLanguages[$sLang])) {
                        $aLangAllow[$sLang] = $aLanguages[$sLang];
                        if (!isset($aAliases[$sLang]) && isset($aLanguages[$sLang]['name'])) {
                            $aAliases[$sLang] = strtolower($aLanguages[$sLang]['name']);
                        }
                    }
                }
                $aConfig['lang.allow'] = array_keys($aLangAllow);
                $aConfig['lang.aliases'] = $aAliases;
            }

            // смена текущего языка
            $sCurrent = $this->GetPost('lang_current');
            if ($sCurrent && isset($aLangAllow[$sCurrent])) {
                $aConfig['lang.current'] = $sCurrent;
            }

            // исключение языков из списка используемых
            $sExclude = $this->GetPost('lang_exclude');
            if ($sExclude) {
                $aExclude = array_unique(F::Array_Str2Array($sExclude, ',', true));
                if ($aExclude) {
                    foreach($aExclude as $sLang) {
                        if (isset($aLangAllow[$sLang]) && sizeof($aLangAllow) > 1) {
                            unset($aLangAllow[$sLang]);
                        }
                    }
                    $aConfig['lang.allow'] = array_keys($aLangAllow);
                }
            }

            if ($aConfig) {
                Config::WriteCustomConfig($aConfig);
            }
            R::Location('admin/settings-lang/');
        }

        $this->_setTitle(E::ModuleLang()->Get('action.admin.set_title_lang'));
        $this->SetTemplateAction('settings/lang');

        E::ModuleViewer()->Assign('aLanguages', $aLanguages);
        E::ModuleViewer()->Assign('aLangAllow', $aLangAllow);
    }

    /**********************************************************************************/

    /**
     * Типы блогов
     */
    protected function EventBlogTypes() {

        $this->sMainMenuItem = 'settings';

        $sMode = $this->getParam(0);
        E::ModuleViewer()->Assign('sMode', $sMode);

        if ($sMode == 'add') {
            return $this->_eventBlogTypesAdd();
        } elseif ($sMode == 'edit') {
            return $this->_eventBlogTypesEdit();
        } elseif ($sMode == 'delete') {
            return $this->_eventBlogTypesDelete();
        } elseif ($this->GetPost('blogtype_action') == 'activate') {
            return $this->_eventBlogTypeSetActive(1);
        } elseif ($this->GetPost('blogtype_action') == 'deactivate') {
            return $this->_eventBlogTypeSetActive(0);
        }
        return $this->_eventBlogTypesList();
    }

    /**
     *
     */
    protected function _eventBlogTypesList() {

        $this->_setTitle(E::ModuleLang()->Get('action.admin.blogtypes_menu'));
        $this->SetTemplateAction('settings/blogtypes');

        $aBlogTypes = E::ModuleBlog()->GetBlogTypes();
        $aLangList = E::ModuleLang()->GetLangList();

        E::ModuleViewer()->Assign('aBlogTypes', $aBlogTypes);
        E::ModuleViewer()->Assign('aLangList', $aLangList);

        E::ModuleLang()->AddLangJs(array(
                'action.admin.blogtypes_del_confirm_title',
                'action.admin.blogtypes_del_confirm_text',
            ));
    }

    /**
     *
     */
    protected function _eventBlogTypesEdit() {

        $this->_setTitle(E::ModuleLang()->Get('action.admin.blogtypes_menu'));
        $this->SetTemplateAction('settings/blogtypes_edit');

        $nBlogTypeId = intval($this->getParam(1));
        if ($nBlogTypeId) {
            /** @var ModuleBlog_EntityBlogType $oBlogType */
            $oBlogType = E::ModuleBlog()->GetBlogTypeById($nBlogTypeId);

            $aLangList = E::ModuleLang()->GetLangList();
            if ($this->IsPost('submit_type_add')) {
                return $this->_eventBlogTypesEditSubmit();
            } else {
                $_REQUEST['blogtypes_typecode'] = $oBlogType->GetTypeCode();
                $_REQUEST['blogtypes_allow_add'] = $oBlogType->IsAllowAdd();
                $_REQUEST['blogtypes_min_rating'] = $oBlogType->GetMinRateAdd();
                $_REQUEST['blogtypes_max_num'] = $oBlogType->GetMaxNum();
                $_REQUEST['blogtypes_show_title'] = $oBlogType->IsShowTitle();
                $_REQUEST['blogtypes_index_content'] = !$oBlogType->IsIndexIgnore();
                $_REQUEST['blogtypes_membership'] = $oBlogType->GetMembership();
                $_REQUEST['blogtypes_min_rate_write'] = $oBlogType->GetMinRateWrite();
                $_REQUEST['blogtypes_min_rate_read'] = $oBlogType->GetMinRateRead();
                $_REQUEST['blogtypes_min_rate_comment'] = $oBlogType->GetMinRateComment();
                $_REQUEST['blogtypes_active'] = $oBlogType->IsActive();
                $_REQUEST['blogtypes_candelete'] = $oBlogType->CanDelete();
                $_REQUEST['blogtypes_norder'] = $oBlogType->GetNorder();
                $_REQUEST['blogtypes_active'] = $oBlogType->IsActive();

                if ($oBlogType->GetAclWrite() & ModuleBlog::BLOG_USER_ACL_GUEST) {
                    $_REQUEST['blogtypes_acl_write'] = ModuleBlog::BLOG_USER_ACL_GUEST;
                } elseif ($oBlogType->GetAclWrite() & ModuleBlog::BLOG_USER_ACL_USER) {
                    $_REQUEST['blogtypes_acl_write'] = ModuleBlog::BLOG_USER_ACL_USER;
                } elseif ($oBlogType->GetAclWrite() & ModuleBlog::BLOG_USER_ACL_MEMBER) {
                    $_REQUEST['blogtypes_acl_write'] = ModuleBlog::BLOG_USER_ACL_MEMBER;
                } else {
                    $_REQUEST['blogtypes_acl_write'] = 0;
                }

                if ($oBlogType->GetAclRead() & ModuleBlog::BLOG_USER_ACL_GUEST) {
                    $_REQUEST['blogtypes_acl_read'] = ModuleBlog::BLOG_USER_ACL_GUEST;
                } elseif ($oBlogType->GetAclRead() & ModuleBlog::BLOG_USER_ACL_USER) {
                    $_REQUEST['blogtypes_acl_read'] = ModuleBlog::BLOG_USER_ACL_USER;
                } elseif ($oBlogType->GetAclRead() & ModuleBlog::BLOG_USER_ACL_MEMBER) {
                    $_REQUEST['blogtypes_acl_read'] = ModuleBlog::BLOG_USER_ACL_MEMBER;
                } else {
                    $_REQUEST['blogtypes_acl_read'] = 0;
                }

                if ($oBlogType->GetAclComment() & ModuleBlog::BLOG_USER_ACL_GUEST) {
                    $_REQUEST['blogtypes_acl_comment'] = ModuleBlog::BLOG_USER_ACL_GUEST;
                } elseif ($oBlogType->GetAclComment() & ModuleBlog::BLOG_USER_ACL_USER) {
                    $_REQUEST['blogtypes_acl_comment'] = ModuleBlog::BLOG_USER_ACL_USER;
                } elseif ($oBlogType->GetAclComment() & ModuleBlog::BLOG_USER_ACL_MEMBER) {
                    $_REQUEST['blogtypes_acl_comment'] = ModuleBlog::BLOG_USER_ACL_MEMBER;
                } else {
                    $_REQUEST['blogtypes_acl_comment'] = 0;
                }

                $_REQUEST['blogtypes_name'] = $oBlogType->GetProp('type_name');
                $_REQUEST['blogtypes_description'] = $oBlogType->GetProp('type_description');
                foreach ($aLangList as $sLang) {
                    $_REQUEST['blogtypes_title'][$sLang] = $oBlogType->GetTitle($sLang);
                }

//                $_REQUEST['blogtypes_contenttype'] = $oBlogType->GetContentType();
                foreach ($oBlogType->getContentTypes() as $oContentType) {
                    $_REQUEST['blogtypes_contenttype'][] = $oContentType->GetId();
                }

            }
            E::ModuleViewer()->Assign('oBlogType', $oBlogType);
            E::ModuleViewer()->Assign('aLangList', $aLangList);
            $aFilter = array('content_active' => 1);
            $aContentTypes = E::ModuleTopic()->GetContentTypes($aFilter, false);
            E::ModuleViewer()->Assign('aContentTypes', $aContentTypes);
        }
    }

    /**
     *
     */
    protected function _eventBlogTypesEditSubmit() {

        $nBlogTypeId = intval($this->getParam(1));
        if ($nBlogTypeId) {
            /** @var ModuleBlog_EntityBlogType $oBlogType */
            $oBlogType = E::ModuleBlog()->GetBlogTypeById($nBlogTypeId);
            if ($oBlogType) {
                $oBlogType->_setValidateScenario('update');

                $oBlogType->setProp('type_name', $this->GetPost('blogtypes_name'));
                $oBlogType->setProp('type_description', $this->GetPost('blogtypes_description'));

                $oBlogType->SetAllowAdd($this->GetPost('blogtypes_allow_add') ? 1 : 0);
                $oBlogType->SetMinRateAdd($this->GetPost('blogtypes_min_rating'));
                $oBlogType->SetMaxNum($this->GetPost('blogtypes_max_num'));
                $oBlogType->SetAllowList($this->GetPost('blogtypes_show_title'));
                $oBlogType->SetIndexIgnore($this->GetPost('blogtypes_index_content') ? 0 : 1);
                $oBlogType->SetMembership(intval($this->GetPost('blogtypes_membership')));
                $oBlogType->SetMinRateWrite($this->GetPost('blogtypes_min_rate_write'));
                $oBlogType->SetMinRateRead($this->GetPost('blogtypes_min_rate_read'));
                $oBlogType->SetMinRateComment($this->GetPost('blogtypes_min_rate_comment'));
                $oBlogType->SetActive($this->GetPost('blogtypes_active'));

                // Теперь здесь null будет всегда...
//                $oBlogType->SetContentType($this->GetPost('blogtypes_contenttype'));
                $oBlogType->SetContentType(NULL);
                $aBlogContentypes = (array)$this->GetPost('blogtypes_contenttype');
                if (!$aBlogContentypes) {
                    $oBlogType->setContentTypes(array());
                } else {
                    $oBlogType->setContentTypes(array_unique(array_keys($this->GetPost('blogtypes_contenttype'))));
                }

                // Установка прав на запись
                $nAclValue = intval($this->GetPost('blogtypes_acl_write'));
                $oBlogType->SetAclWrite($nAclValue);

                // Установка прав на чтение
                $nAclValue = intval($this->GetPost('blogtypes_acl_read'));
                $oBlogType->SetAclRead($nAclValue);

                // Установка прав на комментирование
                $nAclValue = intval($this->GetPost('blogtypes_acl_comment'));
                $oBlogType->SetAclComment($nAclValue);

                E::ModuleHook()->Run('blogtype_edit_validate_before', array('oBlogType' => $oBlogType));
                if ($oBlogType->_Validate()) {
                    if ($this->_updateBlogType($oBlogType)) {
                        R::Location('admin/settings-blogtypes');
                    }
                } else {
                    E::ModuleMessage()->AddError($oBlogType->_getValidateError(), E::ModuleLang()->Get('error'));
                }
            } else {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.blogtypes_err_id_notfound'), E::ModuleLang()->Get('error'));
            }
        }
        E::ModuleViewer()->Assign('oBlogType', $oBlogType);
    }

    /**
     *
     */
    protected function _eventBlogTypesAdd() {

        $this->_setTitle(E::ModuleLang()->Get('action.admin.blogtypes_menu'));
        $this->SetTemplateAction('settings/blogtypes_edit');

        $aLangList = E::ModuleLang()->GetLangList();
        E::ModuleViewer()->Assign('aLangList', $aLangList);

        if ($this->IsPost('submit_type_add')) {
            return $this->_eventBlogTypesAddSubmit();
        }
        $_REQUEST['blogtypes_show_title'] = true;
        $_REQUEST['blogtypes_index_content'] = true;
        $_REQUEST['blogtypes_allow_add'] = true;
        $_REQUEST['blogtypes_min_rating'] = Config::Get('acl.create.blog.rating');
        $_REQUEST['blogtypes_min_rate_comment'] = Config::Get('acl.create.comment.rating');

        $_REQUEST['blogtypes_acl_write'] = array(
            'notmember' => ModuleBlog::BLOG_USER_ROLE_NOTMEMBER,
        );
        $_REQUEST['blogtypes_acl_read'] = array(
            'notmember' => ModuleBlog::BLOG_USER_ROLE_NOTMEMBER,
        );
        $_REQUEST['blogtypes_acl_comment'] = array(
            'notmember' => ModuleBlog::BLOG_USER_ROLE_NOTMEMBER,
        );
        $_REQUEST['blogtypes_contenttypes'] = '';
        $aFilter = array('content_active' => 1);
        $aContentTypes = E::ModuleTopic()->GetContentTypes($aFilter, false);
        E::ModuleViewer()->Assign('aContentTypes', $aContentTypes);
    }

    /**
     *
     */
    protected function _eventBlogTypesAddSubmit() {
        /** @var ModuleBlog_EntityBlogType $oBlogType */
        $oBlogType = E::GetEntity('Blog_BlogType');
        $oBlogType->_setValidateScenario('add');

        $sTypeCode = $this->GetPost('blogtypes_typecode');
        $oBlogType->SetTypeCode($sTypeCode);
        $oBlogType->setProp('type_name', $this->GetPost('blogtypes_name'));
        $oBlogType->setProp('type_description', $this->GetPost('blogtypes_description'));

        $oBlogType->SetAllowAdd($this->GetPost('blogtypes_allow_add') ? 1 : 0);
        $oBlogType->SetMinRateAdd($this->GetPost('blogtypes_min_rating'));
        $oBlogType->SetMaxNum($this->GetPost('blogtypes_max_num'));
        $oBlogType->SetAllowList($this->GetPost('blogtypes_show_title'));
        $oBlogType->SetIndexIgnore(!(bool)$this->GetPost('blogtypes_index_content'));
        $oBlogType->SetMembership(intval($this->GetPost('blogtypes_membership')));
        $oBlogType->SetMinRateWrite($this->GetPost('blogtypes_min_rate_write'));
        $oBlogType->SetMinRateRead($this->GetPost('blogtypes_min_rate_read'));
        $oBlogType->SetMinRateComment($this->GetPost('blogtypes_min_rate_comment'));
        $oBlogType->SetActive($this->GetPost('blogtypes_active'));

//        $oBlogType->SetContentType($this->GetPost('blogtypes_contenttype'));
        $oBlogType->SetContentType(NULL);
        $aBlogContentypes = (array)$this->GetPost('blogtypes_contenttype');
        if (!$aBlogContentypes) {
            $oBlogType->setContentTypes(array());
        } else {
            $oBlogType->setContentTypes(array_unique(array_keys($this->GetPost('blogtypes_contenttype'))));
        }

        // Установка прав на запись
        $nAclValue = intval($this->GetPost('blogtypes_acl_write'));
        $oBlogType->SetAclWrite($nAclValue);

        // Установка прав на чтение
        $nAclValue = intval($this->GetPost('blogtypes_acl_read'));
        $oBlogType->SetAclRead($nAclValue);

        // Установка прав на комментирование
        $nAclValue = intval($this->GetPost('blogtypes_acl_comment'));
        $oBlogType->SetAclComment($nAclValue);

        E::ModuleHook()->Run('blogtype_add_validate_before', array('oBlogType' => $oBlogType));
        if ($oBlogType->_Validate()) {
            if ($this->_addBlogType($oBlogType)) {
                R::Location('admin/settings-blogtypes');
            }
        } else {
            E::ModuleMessage()->AddError($oBlogType->_getValidateError(), E::ModuleLang()->Get('error'));
            E::ModuleViewer()->Assign('aFormErrors', $oBlogType->_getValidateErrors());
        }
        return true;
    }

    /**
     * @return bool
     */
    protected function _eventBlogTypesDelete() {

        $iBlogTypeId = intval($this->getParam(1));
        if ($iBlogTypeId && ($oBlogType = E::ModuleBlog()->GetBlogTypeById($iBlogTypeId))) {

            if ($oBlogType->GetBlogsCount()) {
                E::ModuleMessage()->AddErrorSingle(
                    E::ModuleLang()->Get('action.admin.blogtypes_del_err_notempty', array('count' => $oBlogType->GetBlogsCount())),
                    E::ModuleLang()->Get('action.admin.blogtypes_del_err'),
                    true
                );
            } else {
                $sName = $oBlogType->getTypeCode() . ' - ' . htmlentities($oBlogType->getName());
                if ($this->_deleteBlogType($oBlogType)) {
                    E::ModuleMessage()->AddNoticeSingle(
                        E::ModuleLang()->Get('action.admin.blogtypes_del_success', array('name' => $sName)),
                        null,
                        true
                    );
                } else {
                    E::ModuleMessage()->AddErrorSingle(
                        E::ModuleLang()->Get('action.admin.blogtypes_del_err_text', array('name' => $sName)),
                        E::ModuleLang()->Get('action.admin.blogtypes_del_err'),
                        true
                    );
                }
            }
        }

        R::Location('admin/settings-blogtypes');
    }

    /**
     * @param $oBlogType
     *
     * @return bool
     */
    protected function _addBlogType($oBlogType) {

        return E::ModuleBlog()->AddBlogType($oBlogType);
    }

    /**
     * @param $oBlogType
     *
     * @return bool
     */
    protected function _updateBlogType($oBlogType) {

        return E::ModuleBlog()->UpdateBlogType($oBlogType);
    }

    /**
     * @param $oBlogType
     *
     * @return bool
     */
    protected function _deleteBlogType($oBlogType) {

        return E::ModuleBlog()->DeleteBlogType($oBlogType);
    }

    /**
     * @param $nVal
     */
    protected function _eventBlogTypeSetActive($nVal) {

        $aBlogTypes = $this->GetPost('blogtype_sel');
        if (is_array($aBlogTypes) && count($aBlogTypes)) {
            $aBlogTypes = array_keys($aBlogTypes);
            foreach ($aBlogTypes as $nBlogTypeId) {
                $oBlogType = E::ModuleBlog()->GetBlogTypeById($nBlogTypeId);
                if ($oBlogType) {
                    $oBlogType->SetActive($nVal);
                    E::ModuleBlog()->UpdateBlogType($oBlogType);
                }
            }
        }
        R::Location('admin/settings-blogtypes');
    }

    /**********************************************************************************/

    /**
     * Права пользователей
     */
    protected function EventUserRights() {

        $this->sMainMenuItem = 'settings';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.userrights_menu'));
        $this->SetTemplateAction('settings/userrights');

        if ($this->IsPost('submit_type_add')) {
            return $this->_eventUserRightsEditSubmit();
        } else {
            $_REQUEST['userrights_administrator'] = E::ModuleACL()->GetUserRights('blogs', 'administrator');
            $_REQUEST['userrights_moderator'] = E::ModuleACL()->GetUserRights('blogs', 'moderator');
        }
    }

    protected function _eventUserRightsEditSubmit() {

        $aAdmin = $this->GetPost('userrights_administrator');
        $aModer = $this->GetPost('userrights_moderator');
        $aConfig = array();
        $aConfig['rights.blogs.administrator'] = array(
            'control_users'  => (isset($aAdmin['control_users'])  && $aAdmin['control_users'])  ? true : false,
            'edit_blog'      => (isset($aAdmin['edit_blog'])      && $aAdmin['edit_blog'])      ? true : false,
            'edit_content'   => (isset($aAdmin['edit_content'])   && $aAdmin['edit_content'])   ? true : false,
            'delete_content' => (isset($aAdmin['delete_content']) && $aAdmin['delete_content']) ? true : false,
            'edit_comment'   => (isset($aAdmin['edit_comment'])   && $aAdmin['edit_comment'])   ? true : false,
            'delete_comment' => (isset($aAdmin['delete_comment']) && $aAdmin['delete_comment']) ? true : false,
        );
        $aConfig['rights.blogs.moderator'] = array(
            'control_users'  => (isset($aModer['control_users'])  && $aModer['control_users'])  ? true : false,
            'edit_blog'      => (isset($aModer['edit_blog'])      && $aModer['edit_blog'])      ? true : false,
            'edit_content'   => (isset($aModer['edit_content'])   && $aModer['edit_content'])   ? true : false,
            'delete_content' => (isset($aModer['delete_content']) && $aModer['delete_content']) ? true : false,
            'edit_comment'   => (isset($aModer['edit_comment'])   && $aModer['edit_comment'])   ? true : false,
            'delete_comment' => (isset($aModer['delete_comment']) && $aModer['delete_comment']) ? true : false,
        );
        Config::WriteCustomConfig($aConfig);
    }

    /**********************************************************************************/

    public function EventAjaxChangeOrderMenu() {

        // * Устанавливаем формат ответа
        E::ModuleViewer()->SetResponseAjax('json');

        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        if (!$this->oUserCurrent->isAdministrator()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        if (!F::GetRequest('order')) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }
        if (!F::GetRequest('menu_id')) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        $this->_prepareMenus();

        /** @var ModuleMenu_EntityMenu $oMenu */
        $oMenu = E::ModuleMenu()->GetMenu(F::GetRequest('menu_id'));

        if (is_array(F::GetRequest('order')) && $oMenu) {

            $aData = array();
            $aAllowedData = array_keys(Config::Get("menu.data.{$oMenu->getId()}.items"));
            foreach (F::GetRequest('order') as $oOrder) {
                if (!($sId = (isset($oOrder['id'])?$oOrder['id']:FALSE))) {
                    continue;
                }
                if (!in_array($sId, $aAllowedData)) {
                    continue;
                }
                $aData[]=$sId;
            }

            if ($aData) {
                $sMenuKey = "menu.data.{$oMenu->getId()}";
                $aMenu = C::Get($sMenuKey);
                $aMenu['init']['fill']['list'] = $aData;
                Config::WriteCustomConfig(array($sMenuKey => $aMenu), false);
            }


            E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('action.admin.save_sort_success'));
            return;
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

    }

    public function EventAjaxChangeMenuText() {

        // * Устанавливаем формат ответа
        E::ModuleViewer()->SetResponseAjax('json');

        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        if (!$this->oUserCurrent->isAdministrator()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        if (!F::GetRequest('menu_id')) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        if (!F::GetRequest('item_id')) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        if (!F::GetRequest('text')) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        $this->_prepareMenus();

        /** @var ModuleMenu_EntityMenu $oMenu */
        $oMenu = E::ModuleMenu()->GetMenu(F::GetRequest('menu_id'));

        /** @var ModuleMenu_EntityItem $oItem */
        $oItem = $oMenu->GetItemById(F::GetRequest('item_id'));
        if ($oItem) {
            // Удалим старую текстовку из конфига
            $sMenuListKey = 'menu.data.' . F::GetRequest('menu_id');
            $aMenu = C::Get($sMenuListKey);
            if ($aMenu && isset($aMenu['list'][F::GetRequest('item_id')]['text']) && ($sText = trim(F::GetRequest('text')))) {
                $aMenu['list'][F::GetRequest('item_id')]['text'] = $sText;
                C::WriteCustomConfig(array($sMenuListKey => $aMenu), false);
                E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('action.admin.menu_manager_save_text_ok'));
                E::ModuleViewer()->AssignAjax('text', $sText);
                return;
            }
        }

        E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
        return;

    }

    public function EventAjaxChangeMenuLink() {

        // * Устанавливаем формат ответа
        E::ModuleViewer()->SetResponseAjax('json');

        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        if (!$this->oUserCurrent->isAdministrator()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        if (!F::GetRequest('menu_id')) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        if (!F::GetRequest('item_id')) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        if (!F::GetRequest('text')) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        $this->_prepareMenus();

        /** @var ModuleMenu_EntityMenu $oMenu */
        $oMenu = E::ModuleMenu()->GetMenu(F::GetRequest('menu_id'));

        /** @var ModuleMenu_EntityItem $oItem */
        $oItem = $oMenu->GetItemById(F::GetRequest('item_id'));

        if ($oItem) {
            // Удалим старую текстовку из конфига
            $sMenuListKey = 'menu.data.' . F::GetRequest('menu_id');
            $aMenu = C::Get($sMenuListKey);
            if ($aMenu && isset($aMenu['list'][F::GetRequest('item_id')]['link']) && ($sText = trim(F::GetRequest('text')))) {
                $aMenu['list'][F::GetRequest('item_id')]['link'] = $sText;
                C::WriteCustomConfig(array($sMenuListKey => $aMenu), false);
                E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('action.admin.menu_manager_save_link_ok'));
                E::ModuleViewer()->AssignAjax('text', $sText);
                return;
            }
        }

        E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
        return;

    }

    public function EventAjaxRemoveItem() {

        // * Устанавливаем формат ответа
        E::ModuleViewer()->SetResponseAjax('json');

        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        if (!$this->oUserCurrent->isAdministrator()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        if (!F::GetRequest('menu_id')) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        if (!F::GetRequest('item_id')) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        $this->_prepareMenus();

        /** @var ModuleMenu_EntityMenu $oMenu */
        $oMenu = E::ModuleMenu()->GetMenu(F::GetRequest('menu_id'));

        /** @var ModuleMenu_EntityItem $oItem */
        $oItem = $oMenu->GetItemById(F::GetRequest('item_id'));
        if ($oItem) {
            $aAllowedData = array_values(Config::Get("menu.data.{$oMenu->getId()}.init.fill.list"));
            if (count($aAllowedData) > 1 && isset($aAllowedData[0]) && $aAllowedData[0] == '*') {
                unset($aAllowedData[0]);
            }
            if (is_array($aAllowedData) && isset($aAllowedData[0]) && $aAllowedData[0] == '*') {
                $aAllowedData = array_keys(Config::Get("menu.data.{$oMenu->getId()}.list"));
            }


            $aAllowedData = array_flip($aAllowedData);
            if (isset($aAllowedData[$oItem->getId()])) {
                unset($aAllowedData[$oItem->getId()]);
                $aAllowedData = array_flip($aAllowedData);
                if (!$aAllowedData) {
                    $aAllowedData = array(F::RandomStr(12));
                }

                $sMenuKey = "menu.data.{$oMenu->getId()}";
                $aMenu = C::Get($sMenuKey);
                $aMenu['init']['fill']['list'] = $aAllowedData;
                Config::WriteCustomConfig(array($sMenuKey => $aMenu), false);

                E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('action.admin.menu_manager_remove_link_ok'));
                return;
            }

        }

        E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
        return;

    }

    public function EventAjaxDisplayItem() {

        // * Устанавливаем формат ответа
        E::ModuleViewer()->SetResponseAjax('json');

        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        if (!$this->oUserCurrent->isAdministrator()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        if (!F::GetRequest('menu_id')) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        if (!F::GetRequest('item_id')) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

        $this->_prepareMenus();

        /** @var ModuleMenu_EntityMenu $oMenu */
        $oMenu = E::ModuleMenu()->GetMenu(F::GetRequest('menu_id'));

        /** @var ModuleMenu_EntityItem $oItem */
        $oItem = $oMenu->GetItemById(F::GetRequest('item_id'));
        if ($oItem) {
            $aAllowedData = array_values(Config::Get("menu.data.{$oMenu->getId()}.init.fill.list"));
            if (count($aAllowedData) > 1 && isset($aAllowedData[0]) && $aAllowedData[0] == '*') {
                unset($aAllowedData[0]);
            }
            if (is_array($aAllowedData) && isset($aAllowedData[0]) && $aAllowedData[0] == '*') {
                $aAllowedData = array_keys(Config::Get("menu.data.{$oMenu->getId()}.list"));
            }


            $aAllowedData = array_flip($aAllowedData);
            if (isset($aAllowedData[$oItem->getId()])) {

                $bDisplay = Config::Get("menu.data.{$oMenu->getId()}.list.{$oItem->getId()}.display");
                if (is_null($bDisplay)) {
                    $bDisplay = FALSE;
                } else {
                    $bDisplay = !$bDisplay;
                }


                if ($bDisplay) {
                    E::ModuleViewer()->AssignAjax('class', 'icon-eye-open');
                } else {
                    E::ModuleViewer()->AssignAjax('class', 'icon-eye-close');
                }

                $sMenuKey = "menu.data.{$oMenu->getId()}";
                $aMenu = C::Get($sMenuKey);
                $aMenu['list'][$oItem->getId()]['display'] = $bDisplay;
                Config::WriteCustomConfig(array($sMenuKey => $aMenu), false);

                E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('action.admin.menu_manager_display_link_ok'));

                return;
            }

        }

        E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
        return;

    }

    protected function _eventMenuEdit() {

        // * Получаем тип
        $sMenuId = $this->GetParam(1);


        if (!$oMenu = E::ModuleMenu()->GetMenu($sMenuId)) {
            return parent::EventNotFound();
        }

        E::ModuleViewer()->Assign('oMenu', $oMenu);

        if (strpos($oMenu->getId(), 'submenu_') === 0) {
            E::ModuleViewer()->Assign('isSubMenu', E::ModuleLang()->Get('action.admin.menu_manager_submenu'));
        }

        // * Устанавливаем шаблон вывода
        $this->_setTitle(E::ModuleLang()->Get('action.admin.menu_manager_edit_menu'));
        $this->SetTemplateAction('settings/menumanager_edit');

        // * Проверяем отправлена ли форма с данными
        if (getRequestPost('submit_add_new_item')) {

            if (!(($sItemLink = trim(F::GetRequestStr('menu-item-link'))) && ($sItemTitle = trim(F::GetRequestStr('menu-item-title'))))) {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('menu_manager_item_add_error'), E::ModuleLang()->Get('error'));
                return null;
            }

            $sRoot = F::GetRequest('menu-item-place');
            if ($sRoot == 'root_item') {
                $sItemName = F::RandomStr(10);

                // Добавим имя в объявление
                $aAllowedData = array_values(Config::Get("menu.data.{$oMenu->getId()}.init.fill.list"));
                if (count($aAllowedData) > 1 && isset($aAllowedData[0]) && $aAllowedData[0] == '*') {
                    unset($aAllowedData[0]);
                }
                if (is_array($aAllowedData) && isset($aAllowedData[0]) && $aAllowedData[0] == '*') {
                    $aAllowedData = array_keys(Config::Get("menu.data.{$oMenu->getId()}.list"));
                }

                $aNewItems = array_merge(
                    $aAllowedData,
                    array($sItemName)
                );

                $sMenuKey = "menu.data.{$oMenu->getId()}";
                $aMenu = C::Get($sMenuKey);
                $aMenu['init']['fill']['list'] = $aNewItems;

                // Добавим имя в список
                $aNewItemConfig = array(
                    $sItemName => array(
                    'text'        => $sItemTitle,
                    'link'        => $sItemLink,
                    'active'      => false,
                    )
                );
                $aNewItemConfig = array_merge(
                    Config::Get("menu.data.{$oMenu->getId()}.list"),
                    $aNewItemConfig
                );

                $aMenu['list'] = $aNewItemConfig;
                Config::WriteCustomConfig(array($sMenuKey => $aMenu), false);


                R::Location("admin/settings-menumanager/edit/{$sMenuId}");

                return null;

            } elseif ($sRoot) {

                // Разрешенные идентификаторы меню
                $aAllowedData = array_values(Config::Get("menu.data.{$oMenu->getId()}.init.fill.list"));
                if (count($aAllowedData) > 1 && isset($aAllowedData[0]) && $aAllowedData[0] == '*') {
                    unset($aAllowedData[0]);
                }
                if (is_array($aAllowedData) && isset($aAllowedData[0]) && $aAllowedData[0] == '*') {
                    $aAllowedData = array_keys(Config::Get("menu.data.{$oMenu->getId()}.list"));
                }
                if (!in_array($sRoot, $aAllowedData)) {
                    E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('menu_manager_item_add_error'), E::ModuleLang()->Get('error'));
                    return null;
                }

                // Проверим есть ли подменю для этого элемента?
                $sSubMenuName = Config::Get("menu.data.{$oMenu->getId()}.list.{$sRoot}.submenu");
                if (!$sSubMenuName) {
                    $sSubMenuName = 'submenu_' . F::RandomStr(10);
                    // Сохраним указатель на подменю
                    $sMenuListKey = "menu.data.{$oMenu->getId()}";
                    $aMenu = C::Get($sMenuListKey);
                    if ($aMenu) {
                        $aMenu['list'][$sRoot]['submenu'] = $sSubMenuName;
                        C::WriteCustomConfig(array($sMenuListKey => $aMenu), false);
                    }
                    // Сохраним само пордменю
                    $aSubmenu = array(
                        'init'        => array(
                            'fill' => array(
                                'list' => array('*'),
                            ),
                        ),
                        'list'        => array(),
                    );
                    Config::WriteCustomConfig(array("menu.data.{$sSubMenuName}" => $aSubmenu), false);
                }

                // Добавим новый элемент в подменю
                $sItemName = F::RandomStr(10);

                // Добавим имя в объявление
                $sMenuKey = "menu.data.{$sSubMenuName}";
                $aMenu = C::Get($sMenuKey);

                $aAllowedData = isset($aMenu['init']['fill']['list']) ? array_values($aMenu['init']['fill']['list']) : array();
                if (is_array($aAllowedData) && isset($aAllowedData[0]) && $aAllowedData[0] == '*') {
                    $aAllowedData = isset($aMenu['list']) ? array_keys($aMenu['list']) : array();
                }
                if (count($aAllowedData) > 1 && isset($aAllowedData[0]) && $aAllowedData[0] == '*') {
                    unset($aAllowedData[0]);
                }
                $aNewItems = array_merge(
                    $aAllowedData,
                    array($sItemName)
                );


                $aMenu['init']['fill']['list'] = $aNewItems;

                // Добавим имя в список
                $aNewItemConfig = array(
                    $sItemName => array(
                        'text'        => $sItemTitle,
                        'link'        => $sItemLink,
                        'active'      => false,
                    )
                );
                $aNewItemConfig = array_merge(
                    isset($aMenu['list']) ? $aMenu['list'] : array(),
                    $aNewItemConfig
                );
                $aMenu['list'] = $aNewItemConfig;
                Config::WriteCustomConfig(array($sMenuKey => $aMenu), false);


                R::Location("admin/settings-menumanager/edit/{$sMenuId}");

                return null;

            }


            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('menu_manager_item_add_error'), E::ModuleLang()->Get('error'));
            return null;
        }

        return null;
    }

    protected function _eventMenuReset() {

        // * Получаем тип
        $sMenuId = $this->GetParam(1);

        if (!$oMenu = E::ModuleMenu()->GetMenu($sMenuId)) {
            return parent::EventNotFound();
        }

        Config::ResetCustomConfig("menu.data.{$sMenuId}");

        // Это подменю, удалим его
        if (strpos($oMenu->getId(), 'submenu_') === 0) {
            $aMenus = Config::Get('menu.data');
            $bFound = false;
            foreach ($aMenus as $k=>$v) {
                foreach ($v['list'] as $sItemKey => $aItemData) {
                    if (isset($aItemData['submenu']) && $aItemData['submenu'] == $sMenuId) {
                        $sMenuListKey = 'menu.data.' . $k;
                        $aMenu = C::Get($sMenuListKey);
                        if ($aMenu && isset($aMenu['list'][$sItemKey]['submenu'])) {
                            $aMenu['list'][$sItemKey]['submenu'] = '';
                            C::WriteCustomConfig(array($sMenuListKey => $aMenu), false);
                            $bFound = true;
                            break;
                        }
                    }
                }
                if ($bFound) {
                    break;
                }
            }

            R::Location("admin/settings-menumanager/");
        }



        R::Location("admin/settings-menumanager/edit/{$sMenuId}");

        return FALSE;

    }

    private function _prepareMenus() {
        // Какая-то странность, что хук окончания инициализации вьювера
        // выполняется после экшена админки, поэтому меню остается не
        // проинициализировано и приходится это делать вручную в этом
        // экшене, но ничего страшного, повторно всё равно не
        // проинициализируется
        $aMenus = Config::Get('menu.data');
        $bChanged = false;
        if ($aMenus && is_array($aMenus)) {

            foreach($aMenus as $sMenuId => $aMenu) {
                if (isset($aMenu['init']['fill'])) {
                    $aMenus[$sMenuId] = E::ModuleMenu()->Prepare($sMenuId, $aMenu);
                    $bChanged = true;
                }
            }
            if ($bChanged) {
                Config::Set('menu.data', null);
                Config::Set('menu.data', $aMenus);
            }
        }
    }

    /**
     * Обработчик экшена менеджера меню
     *
     * @return bool|null|string
     */
    protected function EventMenuManager() {

        // Активная вкладка главного меню
        $this->sMainMenuItem = 'settings';

        $this->_prepareMenus();

        // Получим страницу, на которой находится пользователь
        $sMode = $this->getParam(0);

        // В зависимости от страницы запускаем нужный обработчик
        if ($sMode == 'edit') {
            return $this->_eventMenuEdit();
        } else if ($sMode == 'reset') {
            return $this->_eventMenuReset();
        } else {

            // Получим те меню, которые можно редактировать ползователю.
            $aMenu = E::ModuleMenu()->GetMenusByArrayId(Config::Get('module.menu.admin'));

            // Заполним вьювер
            E::ModuleViewer()->Assign(array(
                'aMenu' => $aMenu,
                'sMode' => $sMode,
            ));


            // Установим заголовок страницы
            $this->_setTitle(E::ModuleLang()->Get('action.admin.menu_manager'));


            // Установми страницу вывода
            $this->SetTemplateAction('settings/menu_manager');
        }

    }

    /**********************************************************************************/

    /**
     * Управление полями пользователя
     *
     */
    protected function EventUserFields() {

        $this->sMainMenuItem = 'settings';

        switch (F::GetRequestStr('action')) {
            // * Создание нового поля
            case 'add':
                // * Обрабатываем как ajax запрос (json)
                E::ModuleViewer()->SetResponseAjax('json');
                if (!$this->checkUserField()) {
                    return;
                }
                $oField = E::GetEntity('User_Field');
                $oField->setName(F::GetRequestStr('name'));
                $oField->setTitle(F::GetRequestStr('title'));
                $oField->setPattern(F::GetRequestStr('pattern'));
                if (in_array(F::GetRequestStr('type'), E::ModuleUser()->GetUserFieldTypes())) {
                    $oField->setType(F::GetRequestStr('type'));
                } else {
                    $oField->setType('');
                }

                $iId = E::ModuleUser()->AddUserField($oField);
                if (!$iId) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                    return;
                }
                // * Прогружаем переменные в ajax ответ
                E::ModuleViewer()->AssignAjax('id', $iId);
                E::ModuleViewer()->AssignAjax('lang_delete', E::ModuleLang()->Get('user_field_delete'));
                E::ModuleViewer()->AssignAjax('lang_edit', E::ModuleLang()->Get('user_field_update'));
                E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('user_field_added'), E::ModuleLang()->Get('attention'));
                break;

            // * Удаление поля
            case 'delete':
                // * Обрабатываем как ajax запрос (json)
                E::ModuleViewer()->SetResponseAjax('json');
                if (!F::GetRequestStr('id')) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                    return;
                }
                E::ModuleUser()->DeleteUserField(F::GetRequestStr('id'));
                E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('user_field_deleted'), E::ModuleLang()->Get('attention'));
                break;

            // * Изменение поля
            case 'update':
                // * Обрабатываем как ajax запрос (json)
                E::ModuleViewer()->SetResponseAjax('json');
                if (!F::GetRequestStr('id')) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                    return;
                }
                if (!E::ModuleUser()->UserFieldExistsById(F::GetRequestStr('id'))) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                    return false;
                }
                if (!$this->checkUserField()) {
                    return;
                }
                $oField = E::GetEntity('User_Field');
                $oField->setId(F::GetRequestStr('id'));
                $oField->setName(F::GetRequestStr('name'));
                $oField->setTitle(F::GetRequestStr('title'));
                $oField->setPattern(F::GetRequestStr('pattern'));
                if (in_array(F::GetRequestStr('type'), E::ModuleUser()->GetUserFieldTypes())) {
                    $oField->setType(F::GetRequestStr('type'));
                } else {
                    $oField->setType('');
                }
                if (!E::ModuleUser()->UpdateUserField($oField)) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                    return;
                }
                E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('user_field_updated'), E::ModuleLang()->Get('attention'));
                break;

            // * Показываем страницу со списком полей
            default:
                // * Загружаем в шаблон JS текстовки
                E::ModuleLang()->AddLangJs(array(
                    'action.admin.user_field_delete_confirm_title',
                    'action.admin.user_field_delete_confirm_text',
                    'action.admin.user_field_admin_title_add',
                    'action.admin.user_field_admin_title_edit',
                    'action.admin.user_field_add',
                    'action.admin.user_field_update',
                ));

                // * Получаем список всех полей
                E::ModuleViewer()->Assign('aUserFields', E::ModuleUser()->GetUserFields());
                E::ModuleViewer()->Assign('aUserFieldTypes', E::ModuleUser()->GetUserFieldTypes());
                $this->_setTitle(E::ModuleLang()->Get('action.admin.user_fields_title'));
                $this->SetTemplateAction('settings/userfields');
        }
    }

    /**
     * Проверка поля пользователя на корректность из реквеста
     *
     * @return bool
     */
    public function checkUserField() {

        if (!F::GetRequestStr('title')) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('user_field_error_add_no_title'), E::ModuleLang()->Get('error'));
            return false;
        }
        if (!F::GetRequestStr('name')) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('user_field_error_add_no_name'), E::ModuleLang()->Get('error'));
            return false;
        }
        /**
         * Не допускаем дубликатов по имени
         */
        if (E::ModuleUser()->UserFieldExistsByName(F::GetRequestStr('name'), F::GetRequestStr('id'))) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('user_field_error_name_exists'), E::ModuleLang()->Get('error'));
            return false;
        }
        return true;
    }

    /**********************************************************************************/

    protected function EventContentTypes() {

        $this->sMainMenuItem = 'settings';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.contenttypes_menu'));
        $this->SetTemplateAction('settings/contenttypes');

        $sMode = $this->getParam(0);
        E::ModuleViewer()->Assign('sMode', $sMode);

        E::ModuleLang()->AddLangJs(array(
            'action.admin.contenttypes_del_confirm_title',
            'action.admin.contenttypes_del_confirm_text',
        ));

        if ($sMode == 'add') {
            return $this->_eventContentTypesAdd();
        } elseif ($sMode == 'edit') {
            return $this->_eventContentTypesEdit();
        } elseif ($sMode == 'delete') {
            return $this->_eventContentTypesDelete();
        }

        // * Получаем список
        $aFilter = array();
        $aTypes = E::ModuleTopic()->GetContentTypes($aFilter, false);
        E::ModuleViewer()->Assign('aTypes', $aTypes);

        // * Выключатель
        if (F::GetRequest('toggle') && F::CheckVal(F::GetRequest('content_id'), 'id', 1, 10) && in_array(F::GetRequest('toggle'), array('on', 'off'))) {
            E::ModuleSecurity()->ValidateSendForm();
            if ($oTypeTog = E::ModuleTopic()->GetContentTypeById(F::GetRequest('content_id'))) {
                $iToggle = 1;
                if (F::GetRequest('toggle') == 'off') {
                    $iToggle = 0;
                }
                $oTypeTog->setContentActive($iToggle);
                E::ModuleTopic()->UpdateContentType($oTypeTog);

                R::Location('admin/settings-contenttypes/');
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    protected function _eventContentTypesAdd() {

        $this->_setTitle(E::ModuleLang()->Get('action.admin.contenttypes_add_title'));
        $this->SetTemplateAction('settings/contenttypes_edit');

        // * Вызов хуков
        E::ModuleHook()->Run('topic_type_add_show');

        // * Загружаем переменные в шаблон
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('action.admin.contenttypes_add_title'));

        // * Обрабатываем отправку формы
        return $this->_eventContentTypesAddSubmit();

    }

    /**
     * @return bool
     */
    protected function _eventContentTypesAddSubmit() {

        // * Проверяем отправлена ли форма с данными
        if (!F::isPost('submit_type_add')) {
            return false;
        }

        // * Проверка корректности полей формы
        if (!$this->CheckContentFields()) {
            return false;
        }

        $oContentType = E::GetEntity('Topic_ContentType');
        $oContentType->setContentTitle(F::GetRequest('content_title'));
        $oContentType->setContentTitleDecl(F::GetRequest('content_title_decl'));
        $oContentType->setContentUrl(F::GetRequest('content_url'));
        $oContentType->setContentCandelete('1');
        $oContentType->setContentAccess(F::GetRequest('content_access'));
        $aConfig = F::GetRequest('config');
        if (is_array($aConfig)) {
            $oContentType->setExtraValue('photoset', isset($aConfig['photoset']) ? 1 : 0);
            $oContentType->setExtraValue('link', isset($aConfig['link']) ? 1 : 0);
            $oContentType->setExtraValue('question', isset($aConfig['question']) ? 1 : 0);
        } else {
            $oContentType->setExtra('');
        }

        if (E::ModuleTopic()->AddContentType($oContentType)) {
            E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('action.admin.contenttypes_success_add'), null, true);
            R::Location('admin/settings-contenttypes/');
        }
        return false;
    }

    protected function _eventContentTypesEdit() {

        // * Получаем тип
        $iContentTypeById = intval($this->GetParam(1));
        if (!$iContentTypeById || !($oContentType = E::ModuleTopic()->GetContentTypeById($iContentTypeById))) {
            return parent::EventNotFound();
        }
        E::ModuleViewer()->Assign('oContentType', $oContentType);

        // * Устанавливаем шаблон вывода
        $this->_setTitle(E::ModuleLang()->Get('action.admin.contenttypes_edit_title'));
        $this->SetTemplateAction('settings/contenttypes_edit');

        // * Проверяем отправлена ли форма с данными
        if ($this->isPost('submit_type_add')) {

            // * Обрабатываем отправку формы
            return $this->_eventContentTypesEditSubmit($oContentType);
        } else {
            $_REQUEST['content_id'] = $oContentType->getContentId();
            $_REQUEST['content_title'] = $oContentType->getContentTitle();
            $_REQUEST['content_title_decl'] = $oContentType->getContentTitleDecl();
            $_REQUEST['content_url'] = $oContentType->getContentUrl();
            $_REQUEST['content_candelete'] = $oContentType->getContentCandelete();
            $_REQUEST['content_access'] = $oContentType->getContentAccess();
            $_REQUEST['config']['photoset'] = $oContentType->getExtraValue('photoset');
            $_REQUEST['config']['question'] = $oContentType->getExtraValue('question');
            $_REQUEST['config']['link'] = $oContentType->getExtraValue('link');
        }
        return null;
    }

    protected function _eventContentTypesEditSubmit($oContentType) {

        // * Проверяем отправлена ли форма с данными
        if (!F::isPost('submit_type_add')) {
            return false;
        }

        // * Проверка корректности полей формы
        if (!$this->CheckContentFields()) {
            return false;
        }

        $sTypeOld = $oContentType->getContentUrl();

        $oContentType->setContentTitle(F::GetRequest('content_title'));
        $oContentType->setContentTitleDecl(F::GetRequest('content_title_decl'));
        $oContentType->setContentUrl(F::GetRequest('content_url'));
        $oContentType->setContentAccess(F::GetRequest('content_access'));
        $aConfig = F::GetRequest('config');
        if (is_array($aConfig)) {
            $oContentType->setExtraValue('photoset', isset($aConfig['photoset']) ? 1 : 0);
            $oContentType->setExtraValue('link', isset($aConfig['link']) ? 1 : 0);
            $oContentType->setExtraValue('question', isset($aConfig['question']) ? 1 : 0);
        } else {
            $oContentType->setExtra('');
        }

        if (E::ModuleTopic()->UpdateContentType($oContentType)) {

            if ($oContentType->getContentUrl() != $sTypeOld) {

                //меняем у уже созданных топиков системный тип
                E::ModuleTopic()->ChangeType($sTypeOld, $oContentType->getContentUrl());
            }

            E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('action.admin.contenttypes_success_edit'), null, true);
            R::Location('admin/settings-contenttypes/');
        }
        return false;
    }

    protected function _eventContentTypesDelete() {

        // * Получаем тип
        $iContentTypeById = intval($this->GetParam(1));
        if (!$iContentTypeById || !($oContentType = E::ModuleTopic()->GetContentTypeById($iContentTypeById))) {
            return parent::EventNotFound();
        }

        if ($oContentType->getContentCandelete()) {
            $aFilter = array(
                'topic_type' => $oContentType->getContentUrl(),
            );
            $iCountTopic = E::ModuleTopic()->GetCountTopicsByFilter($aFilter);
            if ($iCountTopic) {
                E::ModuleMessage()->AddErrorSingle(
                    E::ModuleLang()->Get('action.admin.contenttypes_del_err_notempty', array('count' => $iCountTopic)),
                    E::ModuleLang()->Get('action.admin.contenttypes_del_err_text', array('name' => '')),
                    true
                );
                R::Location('admin/settings-contenttypes/');
            } elseif (E::ModuleTopic()->DeleteContentType($oContentType)) {
                E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('action.admin.contenttypes_success_edit'), null, true);
                R::Location('admin/settings-contenttypes/');
            }
        }
        return false;
    }

    public function EventAjaxChangeOrderTypes() {

        // * Устанавливаем формат ответа
        E::ModuleViewer()->SetResponseAjax('json');

        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        if (!$this->oUserCurrent->isAdministrator()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        if (!F::GetRequest('order')) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }


        if (is_array(F::GetRequest('order'))) {

            foreach (F::GetRequest('order') as $oOrder) {
                if (is_numeric($oOrder['order']) && is_numeric($oOrder['id']) && $oContentType = E::ModuleTopic()->GetContentTypeById($oOrder['id'])) {
                    $oContentType->setContentSort($oOrder['order']);
                    E::ModuleTopic()->UpdateContentType($oContentType);
                }
            }

            E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('action.admin.save_sort_success'));
            return;
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

    }

    public function EventAjaxChangeOrderFields() {

        // * Устанавливаем формат ответа
        E::ModuleViewer()->SetResponseAjax('json');

        if (!E::ModuleUser()->IsAuthorization()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        if (!$this->oUserCurrent->isAdministrator()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }
        if (!F::GetRequest('order')) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }


        if (is_array(F::GetRequest('order'))) {

            foreach (F::GetRequest('order') as $oOrder) {
                if (is_numeric($oOrder['order']) && is_numeric($oOrder['id']) && $oField = E::ModuleTopic()->GetContentFieldById($oOrder['id'])) {
                    $oField->setFieldSort($oOrder['order']);
                    E::ModuleTopic()->UpdateContentField($oField);
                }
            }

            E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('action.admin.save_sort_success'));
            return;
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            return;
        }

    }

    /***********************
     ****** Поля ***********
     **********************/
    protected function EventAddField() {

        $this->sMainMenuItem = 'settings';

        $this->_setTitle(E::ModuleLang()->Get('action.admin.contenttypes_add_field_title'));

        // * Получаем тип
        if (!$oContentType = E::ModuleTopic()->GetContentTypeById($this->GetParam(0))) {
            return parent::EventNotFound();
        }

        E::ModuleViewer()->Assign('oContentType', $oContentType);

        // * Устанавливаем шаблон вывода
        $this->SetTemplateAction('settings/contenttypes_fieldadd');

        // * Обрабатываем отправку формы
        return $this->SubmitAddField($oContentType);

    }

    protected function SubmitAddField($oContentType) {

        // * Проверяем отправлена ли форма с данными
        if (!F::isPost('submit_field')) {
            return false;
        }

        // * Проверка корректности полей формы
        if (!$this->CheckFieldsField($oContentType)) {
            return false;
        }

        $oField = E::GetEntity('Topic_Field');
        $oField->setFieldType(F::GetRequest('field_type'));
        $oField->setContentId($oContentType->getContentId());
        $oField->setFieldName(F::GetRequest('field_name'));
        $oField->setFieldDescription(F::GetRequest('field_description'));
        $oField->setFieldRequired(F::GetRequest('field_required'));
        if (F::GetRequest('field_type') == 'select') {
            $oField->setOptionValue('select', F::GetRequest('field_values'));
        }

        if (E::ModuleTopic()->AddContentField($oField)) {
            E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('action.admin.contenttypes_success_fieldadd'), null, true);
            R::Location('admin/settings-contenttypes/edit/' . $oContentType->getContentId() . '/');
        }
        return false;
    }

    protected function EventEditField() {

        $this->sMainMenuItem = 'settings';

        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('action.admin.contenttypes_edit_field_title'));

        // * Получаем поле
        if (!$oField = E::ModuleTopic()->GetContentFieldById($this->GetParam(0))) {
            return parent::EventNotFound();
        }

        E::ModuleViewer()->Assign('oField', $oField);

        // * Получаем тип
        if (!$oContentType = E::ModuleTopic()->GetContentTypeById($oField->getContentId())) {
            return parent::EventNotFound();
        }

        E::ModuleViewer()->Assign('oContentType', $oContentType);

        // * Устанавливаем шаблон вывода
        $this->SetTemplateAction('settings/contenttypes_fieldadd');

        // * Проверяем отправлена ли форма с данными
        if (isset($_REQUEST['submit_field'])) {

            // * Обрабатываем отправку формы
            return $this->SubmitEditField($oContentType, $oField);
        } else {
            $_REQUEST['field_id'] = $oField->getFieldId();
            $_REQUEST['field_type'] = $oField->getFieldType();
            $_REQUEST['field_name'] = $oField->getFieldName();
            $_REQUEST['field_description'] = $oField->getFieldDescription();
            $_REQUEST['field_required'] = $oField->getFieldRequired();
            $_REQUEST['field_values'] = $oField->getFieldValues();
        }

    }

    /**
     * Редактирование поля контента
     *
     * @param ModuleTopic_EntityContentType $oContentType
     * @param ModuleTopic_EntityField $oField
     * @return bool
     */
    protected function SubmitEditField($oContentType, $oField) {

        // * Проверяем отправлена ли форма с данными
        if (!F::isPost('submit_field')) {
            return false;
        }

        // * Проверка корректности полей формы
        if (!$this->CheckFieldsField($oContentType)) {
            return false;
        }

        if (!E::ModuleTopic()->GetFieldValuesCount($oField->getFieldId())) {
            // Нет ещё ни одного значения этого поля, тогда можно сменить ещё и тип
            $oField->setFieldType(F::GetRequest('field_type'));
        }
        $oField->setFieldName(F::GetRequest('field_name'));
        $oField->setFieldDescription(F::GetRequest('field_description'));
        $oField->setFieldRequired(F::GetRequest('field_required'));
        if ($oField->getFieldType() == 'select') {
            $oField->setOptionValue('select', F::GetRequest('field_values'));
        }

        if (E::ModuleTopic()->UpdateContentField($oField)) {
            E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('action.admin.contenttypes_success_fieldedit'), null, true);
            R::Location('admin/settings-contenttypes/edit/' . $oContentType->getContentId() . '/');
        }
        return false;
    }

    protected function EventDeleteField() {

        $this->sMainMenuItem = 'settings';

        E::ModuleSecurity()->ValidateSendForm();
        $iContentFieldId = intval($this->GetParam(0));
        if (!$iContentFieldId) {
            return parent::EventNotFound();
        }

        $oField = E::ModuleTopic()->GetContentFieldById($iContentFieldId);
        if ($oField) {
            $oContentType = E::ModuleTopic()->GetContentTypeById($oField->getContentId());
        } else {
            $oContentType = null;
        }

        if (E::ModuleTopic()->DeleteField($iContentFieldId)) {
            E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('action.admin.contenttypes_success_fielddelete'), null, true);
            if ($oContentType) {
                R::Location('admin/settings-contenttypes/edit/' . $oContentType->getContentId() . '/');
            } else {
                R::Location('admin/settings-contenttypes/');
            }
        }
        return false;
    }


    /*************************************************************
     *
     */
    protected function CheckContentFields() {

        E::ModuleSecurity()->ValidateSendForm();

        $bOk = true;

        if (!F::CheckVal(F::GetRequest('content_title', null, 'post'), 'text', 2, 200)) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.contenttypes_type_title_error'), E::ModuleLang()->Get('error'));
            $bOk = false;
        }

        if (!F::CheckVal(F::GetRequest('content_title_decl', null, 'post'), 'text', 2, 200)) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.contenttypes_type_title_decl_error'), E::ModuleLang()->Get('error'));
            $bOk = false;
        }
        if (!F::CheckVal(F::GetRequest('content_url', null, 'post'), 'login', 2, 50) || in_array(F::GetRequest('content_url', null, 'post'), array_keys(Config::Get('router.page')))) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.contenttypes_type_url_error'), E::ModuleLang()->Get('error'));
            $bOk = false;
        }

        if (!in_array(F::GetRequest('content_access'), array('1', '2', '4'))) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
            $bOk = false;
        }

        return $bOk;
    }

    protected function CheckFieldsField($oContentType = null) {

        E::ModuleSecurity()->ValidateSendForm();

        $bOk = true;

        if (!F::CheckVal(F::GetRequest('field_name', null, 'post'), 'text', 2, 100)) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.contenttypes_field_name_error'), E::ModuleLang()->Get('error'));
            $bOk = false;
        }

        if (!F::CheckVal(F::GetRequest('field_description', null, 'post'), 'text', 2, 200)) {
            E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.contenttypes_field_description_error'), E::ModuleLang()->Get('error'));
            $bOk = false;
        }

        if (R::GetActionEvent() == 'fieldadd') {
            if ($oContentType == 'photoset' && (F::GetRequest('field_type', null, 'post') == 'photoset' || $oContentType->isPhotosetEnable())) {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('system_error'), E::ModuleLang()->Get('error'));
                $bOk = false;
            }

            if (!in_array(F::GetRequest('field_type', null, 'post'), E::ModuleTopic()->GetAvailableFieldTypes())) {
                E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.contenttypes_field_type_error'), E::ModuleLang()->Get('error'));
                $bOk = false;
            }
        }

        // * Выполнение хуков
        E::ModuleHook()->Run('check_admin_content_fields', array('bOk' => &$bOk));

        return $bOk;
    }

    /**
     * Голосование админа
     */
    public function EventAjaxVote() {

        // * Устанавливаем формат ответа
        E::ModuleViewer()->SetResponseAjax('json');

        if (!E::IsAdmin()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }

        $nUserId = $this->GetPost('idUser');
        if (!$nUserId || !($oUser = E::ModuleUser()->GetUserById($nUserId))) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('user_not_found'), E::ModuleLang()->Get('error'));
            return;
        }

        $nValue = $this->GetPost('value');

        $oUserVote = E::GetEntity('Vote');
        $oUserVote->setTargetId($oUser->getId());
        $oUserVote->setTargetType('user');
        $oUserVote->setVoterId($this->oUserCurrent->getId());
        $oUserVote->setDirection($nValue);
        $oUserVote->setDate(F::Now());
        $iVal = (float)E::ModuleRating()->VoteUser($this->oUserCurrent, $oUser, $nValue);
        $oUserVote->setValue($iVal);
        $oUser->setCountVote($oUser->getCountVote() + 1);
        if (E::ModuleVote()->AddVote($oUserVote) && E::ModuleUser()->Update($oUser)) {
            E::ModuleViewer()->AssignAjax('iRating', $oUser->getRating());
            E::ModuleViewer()->AssignAjax('iSkill', $oUser->getSkill());
            E::ModuleViewer()->AssignAjax('iCountVote', $oUser->getCountVote());
            E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('user_vote_ok'), E::ModuleLang()->Get('attention'));
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('action.admin.vote_error'), E::ModuleLang()->Get('error'));
        }

    }

    public function EventAjaxSetProfile() {

        // * Устанавливаем формат ответа
        E::ModuleViewer()->SetResponseAjax('json');

        if (!E::IsAdmin()) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('need_authorization'), E::ModuleLang()->Get('error'));
            return;
        }

        $nUserId = intval($this->GetPost('user_id'));
        if ($nUserId && ($oUser = E::ModuleUser()->GetUserById($nUserId))) {
            $sData = $this->GetPost('profile_about');
            if (!is_null($sData)) {
                $oUser->setProfileAbout($sData);
            }
            $sData = $this->GetPost('profile_site');
            if (!is_null($sData)) {
                $oUser->setUserProfileSite(trim($sData));
            }
            $sData = $this->GetPost('profile_email');
            if (!is_null($sData)) {
                $oUser->setMail(trim($sData));
            }

            if (E::ModuleUser()->Update($oUser) !== false) {
                E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('action.admin.saved_ok'));
            } else {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('action.admin.saved_err'));
            }
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('user_not_found'), E::ModuleLang()->Get('error'));
        }
    }

    public function EventAjaxConfig() {

        E::ModuleViewer()->SetResponseAjax('json');

        if ($sKeys = $this->GetPost('keys')) {
            if (!is_array($sKeys)) {
                $aKeys = F::ArrayToStr($sKeys);
            } else {
                $aKeys = (array)$sKeys;
            }
            $aConfig = array();
            foreach ($aKeys as $sKey) {
                $sValue = $this->GetPost($sKey);
                $aConfig[str_replace('--', '.', $sKey)] = $sValue;
            }
            Config::WriteCustomConfig($aConfig);
        }
    }

    public function EventAjaxUserAdd() {

        E::ModuleViewer()->SetResponseAjax('json');

        if ($this->IsPost()) {
            Config::Set('module.user.captcha_use_registration', false);

            $oUser = E::GetEntity('ModuleUser_EntityUser');
            $oUser->_setValidateScenario('registration');

            // * Заполняем поля (данные)
            $oUser->setLogin($this->GetPost('user_login'));
            $oUser->setMail($this->GetPost('user_mail'));
            $oUser->setPassword($this->GetPost('user_password'));
            $oUser->setPasswordConfirm($this->GetPost('user_password'));
            $oUser->setDateRegister(F::Now());
            $oUser->setIpRegister('');
            $oUser->setActivate(1);

            if ($oUser->_Validate()) {
                E::ModuleHook()->Run('registration_validate_after', array('oUser' => $oUser));
                $oUser->setPassword($oUser->getPassword(), true);
                if (E::ModuleUser()->Add($oUser)) {
                    E::ModuleHook()->Run('registration_after', array('oUser' => $oUser));

                    // Подписываем пользователя на дефолтные события в ленте активности
                    E::ModuleStream()->SwitchUserEventDefaultTypes($oUser->getId());

                    if ($this->IsPost('user_setadmin')) {
                        E::ModuleAdmin()->SetAdministrator($oUser->GetId());
                    }
                }
                E::ModuleMessage()->AddNoticeSingle(E::ModuleLang()->Get('registration_ok'));
            } else {
                E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('error'));
                E::ModuleViewer()->AssignAjax('aErrors', $oUser->_getValidateErrors());
            }
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'));
        }
    }

    public function EventAjaxUserList() {

        E::ModuleViewer()->SetResponseAjax('json');

        if ($this->IsPost()) {
            $sList = trim($this->GetPost('invite_listmail'));
            if ($aList = F::Array_Str2Array($sList, "\n", true)) {
                $iSentCount = 0;
                foreach($aList as $iKey => $sMail) {
                    if (F::CheckVal($sMail, 'mail')) {
                        $oInvite = E::ModuleUser()->GenerateInvite($this->oUserCurrent);
                        if (E::ModuleNotify()->SendInvite($this->oUserCurrent, $sMail, $oInvite)) {
                            unset($aList[$iKey]);
                            $iSentCount++;
                        }
                    }
                }

                E::ModuleMessage()->AddNotice(E::ModuleLang()->Get('action.admin.invaite_mail_done', array('num' => $iSentCount)), null, true);
                if ($aList) {
                    E::ModuleMessage()->AddError(E::ModuleLang()->Get('action.admin.invaite_mail_err', array('num' => count($aList))), null, true);
                }
            }
        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'));
        }

    }

    /**
     * Выполняется при завершении работы экшена
     *
     */
    public function EventShutdown() {

        // * Загружаем в шаблон необходимые переменные
        E::ModuleViewer()->Assign('sMainMenuItem', $this->sMainMenuItem);
        E::ModuleViewer()->Assign('sMenuItem', $this->sMenuItem);
        E::ModuleLang()->AddLangJs(array('action.admin.form_choose_file', 'action.admin.form_no_file_selected'));
    }

    protected function _setTitle($sTitle) {

        E::ModuleViewer()->Assign('sPageTitle', $sTitle);
        E::ModuleViewer()->AddHtmlTitle($sTitle);

    }

}

// EOF
