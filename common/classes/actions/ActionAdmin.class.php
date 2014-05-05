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
    protected $sMenuItem = '';

    /**
     * Инициализация
     *
     * @return string
     */
    public function Init() {

        if ($this->User_IsAuthorization()) {
            $this->oUserCurrent = $this->User_GetUserCurrent();
        }

        /**
         * Если нет прав доступа - перекидываем на 404 страницу
         * Но нужно это делать через Router::Location, т.к. Viewer может быть уже инициирован
         */
        if (!$this->oUserCurrent || !$this->oUserCurrent->isAdministrator()) {
            //return Router::Action('error', '404');
            return Router::Location('error/404/');
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
        $this->AddEvent('settings-userfields', 'EventUserfields');

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
        $this->AddEvent('tools-recalcblograting', 'EventRecalculateBlogRating');
        $this->AddEvent('tools-checkdb', 'EventCheckDb');

        //поля контента
        $this->AddEvent('settings-contenttypes', 'EventContentTypes');
        $this->AddEvent('settings-contenttypesadd', 'EventContentTypesAdd');
        $this->AddEvent('settings-contenttypesedit', 'EventContentTypesEdit');

        $this->AddEvent('settings-contenttypes-fieldadd', 'EventAddField');
        $this->AddEvent('settings-contenttypes-fieldedit', 'EventEditField');
        $this->AddEvent('settings-contenttypes-fielddelete', 'EventDeleteField');
        $this->AddEvent('ajaxchangeordertypes', 'EventAjaxChangeOrderTypes');
        $this->AddEvent('ajaxchangeorderfields', 'EventAjaxChangeOrderFields');

        $this->AddEvent('ajaxvote', 'EventAjaxVote');
        $this->AddEvent('ajaxsetprofile', 'EventAjaxSetProfile');

        $this->AddEventPreg('/^ajax$/i', '/^config$/i', 'EventAjaxConfig');
    }

    /**
     * @param   int         $nParam
     * @param   string      $sDefault
     * @param   array|null  $aAvail
     *
     * @return mixed
     */
    protected function _getMode($nParam = 0, $sDefault, $aAvail = null) {

        $sKey = Router::GetAction() . '.' . Router::GetActionEvent() . '.' . $nParam;
        $sMode = $this->GetParam($nParam, $this->Session_Get($sKey, $sDefault));
        if (!is_null($aAvail) && !is_array($aAvail)) $aAvail = array($aAvail);
        if (is_null($aAvail) || ($sMode && in_array($sMode, $aAvail))) {
            $this->_saveMode(0, $sMode);
        }
        return $sMode;
    }

    protected function _saveMode($nParam = 0, $sData) {

        $sKey = Router::GetAction() . '.' . Router::GetActionEvent() . '.' . $nParam;
        $this->Session_Set($sKey, $sData);
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

        $aDashboardWidgets = array(
            'admin_dashboard_updates' => array(
                'name' => 'admin_dashboard_updates',
                'key' => 'admin.dashboard.updates',
                'status' => Config::Val('admin.dashboard.updates', true),
                'label' => $this->Lang_Get('action.admin.dashboard_updates_title')
            ),
            'admin_dashboard_news' => array(
                'name' => 'admin_dashboard_news',
                'key' => 'admin.dashboard.news',
                'status' => Config::Val('admin.dashboard.news', true),
                'label' => $this->Lang_Get('action.admin.dashboard_news_title')
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
            Router::Location('admin');
        }
        $this->_setTitle($this->Lang_Get('action.admin.menu_info_dashboard'));
        $this->SetTemplateAction('info/index');

        $this->sMenuItem = $this->_getMode(0, 'index');

        $aData = array('e-alto' => ALTO_VERSION, 'e-uniq' => $this->Security_GetUniqKey());
        $aPlugins = $this->Plugin_GetPluginsList(true);
        foreach ($aPlugins as $oPlugin) {
            $aData['p-' . $oPlugin->GetId()] = $oPlugin->GetVersion();
        }
        $aSkins = $this->Skin_GetSkinsList();
        foreach ($aSkins as $oSkin) {
            $aData['s-' . $oSkin->GetId()] = $oSkin->GetVersion();
        }

        $this->Viewer_Assign('sUpdatesRequest', base64_encode(http_build_query($aData)));
        $this->Viewer_Assign('sUpdatesRefresh', true);
        $this->Viewer_Assign('aDashboardWidgets', $aDashboardWidgets);
    }

    public function EventReport() {

        $this->_setTitle($this->Lang_Get('action.admin.menu_info'));
        $this->SetTemplateAction('info/report');

        if ($sReportMode = F::GetRequest('report', null, 'post')) {
            $this->_EventReportOut($this->_getInfoData(), $sReportMode);
        }

        $this->Viewer_Assign('aInfoData', $this->_getInfoData());
    }

    protected function _getInfoData() {

        $aPlugins = $this->Plugin_GetList(null, false);
        $aActivePlugins = $this->Plugin_GetActivePlugins();
        $aPluginList = array();
        foreach ($aActivePlugins as $sPlugin) {
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

        $aSiteStat = $this->Admin_GetSiteStat();
        $sSmartyVersion = $this->Viewer_GetSmartyVersion();

        $aImgSupport = $this->Img_GetDriversInfo();
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
                'label' => $this->Lang_Get('action.admin.info_versions'),
                'data' => array(
                    'php' => array('label' => $this->Lang_Get('action.admin.info_version_php'), 'value' => PHP_VERSION,),
                    'img' => array('label' => $this->Lang_Get('action.admin.info_version_img'), 'value' => $sImgSupport,),
                    'smarty' => array('label' => $this->Lang_Get('action.admin.info_version_smarty'), 'value' => $sSmartyVersion ? $sSmartyVersion : 'n/a',),
                    'alto' => array('label' => $this->Lang_Get('action.admin.info_version_alto'), 'value' => ALTO_VERSION,),
                )

            ),
            'site' => array(
                'label' => $this->Lang_Get('action.admin.site_info'),
                'data' => array(
                    'url' => array('label' => $this->Lang_Get('action.admin.info_site_url'), 'value' => Config::Get('path.root.url'),),
                    'skin' => array('label' => $this->Lang_Get('action.admin.info_site_skin'), 'value' => Config::Get('view.skin', Config::LEVEL_CUSTOM),),
                    'client' => array('label' => $this->Lang_Get('action.admin.info_site_client'), 'value' => $_SERVER['HTTP_USER_AGENT'],),
                    'empty' => array('label' => '', 'value' => '',),
                ),
            ),
            'plugins' => array(
                'label' => $this->Lang_Get('action.admin.active_plugins'),
                'data' => $aPluginList,
            ),
            'stats' => array(
                'label' => $this->Lang_Get('action.admin.site_statistics'),
                'data' => array(
                    'users' => array('label' => $this->Lang_Get('action.admin.site_stat_users'), 'value' => $aSiteStat['users'],),
                    'blogs' => array('label' => $this->Lang_Get('action.admin.site_stat_blogs'), 'value' => $aSiteStat['blogs'],),
                    'topics' => array('label' => $this->Lang_Get('action.admin.site_stat_topics'), 'value' => $aSiteStat['topics'],),
                    'comments' => array('label' => $this->Lang_Get('action.admin.site_stat_comments'), 'value' => $aSiteStat['comments'],),
                ),
            ),
        );

        return $aInfo;
    }

    protected function _EventReportOut($aInfo, $sMode = 'txt') {

        $this->Security_ValidateSendForm();
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

        $this->_setTitle($this->Lang_Get('action.admin.menu_info_phpinfo'));
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
            $this->Viewer_Assign('aPhpInfo', array('collection' => $aPhpInfo, 'count' => sizeof($aPhpInfo)));
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
            $this->Viewer_Assign('sPhpInfo', $info);
        }
    }

    /**********************************************************************************/

    /**
     * Site settings
     */
    public function EventConfig() {

        $this->_setTitle($this->Lang_Get('action.admin.config_title'));

        $sMode = $this->_getMode(0, 'base');

        if ($sMode == 'links') {
            $this->_eventConfigLinks();
        } elseif ($sMode == 'edit') {
            $this->_eventConfigEdit($sMode);
        } else {
            $this->_eventConfigParams($sMode);
        }
        $this->Viewer_Assign('sMode', $sMode);
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
                $aItem['text'] = $this->Lang_Get($aItem['label']);
                if (isset($aItem['help'])) $aItem['help'] = $this->Lang_Get($aItem['help']);
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
        $this->Viewer_Assign('aFields', $aFields[$sSelectedSection]);
    }

    /**
     * Site settings > Links
     */
    protected function _eventConfigLinks() {

        if ($sHomePage = $this->GetPost('submit_data_save')) {
            $aConfig = array();
            if ($sHomePage = $this->GetPost('homepage')) {
                if ($sHomePage == 'page') {
                    $sHomePage = 'page/' . $this->GetPost('page_url');
                }
                $aConfig = array(
                    'router.config.action_default' => 'homepage',
                    'router.config.homepage' => $sHomePage,
                    'router.config.homepage_select' => '',
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
                } elseif ($sTopicLink == 'ls') {
                    $aConfig['module.topic.url'] = '';
                } elseif ($sTopicLink == 'id') {
                    $aConfig['module.topic.url'] = '%topic_id%';
                } elseif ($sTopicLink == 'day_name') {
                    $aConfig['module.topic.url'] = '%year%/%month%/%day%/%topic_url%/';
                } elseif ($sTopicLink == 'month_name') {
                    $aConfig['module.topic.url'] = '%year%/%month%/%topic_url%/';
                } else {
                    if ($sTopicUrl = $this->GetPost('topic_link_url')) {
                        $aConfig['module.topic.url'] = $sTopicUrl;
                    } else {
                        $aConfig['module.topic.url'] = '';
                    }
                }
            }
            if ($aConfig) {
                Config::WriteCustomConfig($aConfig);
                Router::Location('admin/settings-site/links/');
            }
        }
        if ($this->GetPost('adm_cmd') == 'generate_topics_url') {
            // Генерация URL топиков
            $nRest = $this->Admin_GenerateTopicsUrl();
            if ($nRest > 0) {
                $this->Message_AddNotice($this->Lang_Get('action.admin.set_links_generate_next', array('num' => $nRest)), null, true);
            } elseif ($nRest < 0) {
                $this->Message_AddNotice($this->Lang_Get('action.admin.set_links_generate_done'), null, true);
            } else {
                $this->Message_AddNotice($this->Lang_Get('action.admin.set_links_generate_done'), null, true);
            }
            Router::Location('admin/settings-site/links/');
        }
        $this->SetTemplateAction('settings/links');
        $sHomePage = Config::Get('router.config.homepage');

        $sHomePageUrl = '';
        if (!$sHomePage || $sHomePage == 'index') {
            $sHomePageSelect = 'index';
            $sHomePageUrl = '';
        } elseif (strpos($sHomePage, 'page/') === 0) {
            list ($sHomePageSelect, $sHomePageUrl) = explode('/', $sHomePage, 2);
        } else {
            $sHomePageSelect = $sHomePage;
        }
        $aPages = $this->Page_GetPages();

        $sPermalinkUrl = trim(Config::Get('module.topic.url'), '/');
        if (!$sPermalinkUrl) {
            $sPermalinkMode = 'ls';
        } elseif ($sPermalinkUrl == '%topic_id%') {
            $sPermalinkMode = 'id';
        } elseif ($sPermalinkUrl == '%year%/%month%/%day%/%topic_url%') {
            $sPermalinkMode = 'day_name';
        } elseif ($sPermalinkUrl == '%year%/%month%/%topic_url%') {
            $sPermalinkMode = 'month_name';
        } else {
            $sPermalinkMode = 'custom';
        }

        $this->Viewer_Assign('sHomePageSelect', $sHomePageSelect);
        $this->Viewer_Assign('sHomePageUrl', $sHomePageUrl);
        $this->Viewer_Assign('aPages', $aPages);
        $this->Viewer_Assign('sPermalinkMode', $sPermalinkMode);
        $this->Viewer_Assign('sPermalinkUrl', $sPermalinkUrl);
        $this->Viewer_Assign('nTopicsWithoutUrl', $this->Admin_GetNumTopicsWithoutUrl());
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

            $aConfig['view.img_resize_width'] = intval($this->GetPost('view--img_resize_width'));
            $aConfig['view.img_max_width'] = intval($this->GetPost('view--img_max_width'));
            $aConfig['view.img_max_height'] = intval($this->GetPost('view--img_max_height'));

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
            Router::Location('admin/settings-site/');
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
        $this->Viewer_Assign('nCommentEditTime', $nCommentEditTime);
        $this->Viewer_Assign('sCommentEditUnit', $sCommentEditUnit);
        $this->Viewer_Assign('aTimeUnits', $aUnits);
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
        Router::Location('admin/settings-site/');
    }

    /**********************************************************************************/

    public function EventWidgets() {

        $this->_setTitle($this->Lang_Get('action.admin.widgets_title'));
        $this->SetTemplateAction('site/widgets');

        $sMode = $this->GetParam(0);
        $aWidgets = $this->Widget_GetWidgets(true);

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
        $this->Viewer_Assign('aWidgetsList', $aWidgets);
    }

    public function _eventWidgetsEdit($oWidget) {

        if ($this->GetPost()) {
            $aConfig = array();
            $sPrefix = 'widget.' . $oWidget->GetId() . '.config.';
            if ($xVal = $this->GetPost('widget_group')) {
                $aConfig[$sPrefix . 'group'] = $xVal;
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
            Router::Location('admin/site-widgets');
        }
        $this->_setTitle($this->Lang_Get('action.admin.widget_edit_title'));
        $this->SetTemplateAction('site/widgets_add');
        $this->Viewer_Assign('oWidget', $oWidget);
    }

    public function _eventWidgetsActivate($aWidgets) {

        if ($this->GetPost()) {
            $aConfig = array();
            foreach ($aWidgets as $sWidgetId) {
                $sPrefix = 'widget.' . $sWidgetId . '.config.';
                $aConfig[$sPrefix . 'active'] = true;
            }
            Config::WriteCustomConfig($aConfig);
            Router::Location('admin/site-widgets');
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
            Router::Location('admin/site-widgets');
        }
    }

    /**********************************************************************************/

    public function EventPlugins() {

        $this->_setTitle($this->Lang_Get('action.admin.plugins_title'));
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
                $aPlugins = $this->Plugin_GetActivePlugins();
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
        $sEvent = Router::GetActionEvent();
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
                $this->_eventPluginsDectivate($aPlugins);
            }
            Router::Location('admin/site-plugins/');
        }

        $sMode = $this->GetParam(1, 'all');

        if ($sMode == 'active') {
            $aPlugins = $this->Plugin_GetPluginsList(true);
        } elseif ($sMode == 'inactive') {
            $aPlugins = $this->Plugin_GetPluginsList(false);
        } else {
            $aPlugins = $this->Plugin_GetPluginsList();
        }

        $this->Viewer_Assign('aPluginList', $aPlugins);
        $this->Viewer_Assign('sMode', $sMode);
    }

    protected function _eventPluginsActivate($aPlugins) {

        if (is_array($aPlugins)) {
            // если передан массив, то обрабатываем только первый элемент
            $sPluginId = array_shift($aPlugins);
        } else {
            $sPluginId = (string)$aPlugins;
        }
        return $this->Plugin_Activate($sPluginId);
    }

    protected function _eventPluginsDectivate($aPlugins) {

        if (is_array($aPlugins)) {
            // если передан массив, то обрабатываем только первый элемент
            $sPluginId = array_shift($aPlugins);
        } else {
            $sPluginId = (string)$aPlugins;
        }
        return $this->Plugin_Deactivate($sPluginId);
    }

    protected function _eventPluginsDelete($aPlugins) {

        $this->Plugin_Delete($aPlugins);
    }

    protected function _eventPluginsAdd() {

        if ($aZipFile = $this->GetUploadedFile('plugin_arc')) {
            if ($sPackFile = F::File_MoveUploadedFile($aZipFile['tmp_name'], $aZipFile['name'] . '/' . $aZipFile['name'])) {
                $this->Plugin_UnpackPlugin($sPackFile);
                F::File_RemoveDir(dirname($sPackFile));
            }
        }
        $this->_setTitle($this->Lang_Get('action.admin.plugins_title'));
        $this->SetTemplateAction('site/plugins_add');
        $this->Viewer_Assign('sMode', 'add');
    }

    /**********************************************************************************/

    protected function EventPages() {

        $this->_setTitle($this->Lang_Get('action.admin.pages_title'));
        // * Получаем и загружаем список всех страниц
        $aPages = $this->Page_GetPages();
        if (count($aPages) == 0 && $this->Page_GetCountPage()) {
            $this->Page_SetPagesPidToNull();
            $aPages = $this->Page_GetPages();
        }
        $this->Viewer_Assign('aPages', $aPages);
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
            $this->Security_ValidateSendForm();
            if ($this->Page_DeletePageById($this->GetParam(1))) {
                $this->Message_AddNotice($this->Lang_Get('action.admin.pages_admin_action_delete_ok'). null, true);
                Router::Location('admin/content-pages/');
            } else {
                $this->Message_AddError($this->Lang_Get('action.admin.pages_admin_action_delete_error'), $this->Lang_Get('error'));
            }
        }

        // * Обработка изменения сортировки страницы
        if ($this->GetParam(0) == 'sort') {
            $this->_eventPagesListSort();
        }
        $this->SetTemplateAction('content/pages_list');
    }

    protected function _eventPagesListSort() {

        $this->Security_ValidateSendForm();
        if ($oPage = $this->Page_GetPageById($this->GetParam(1))) {
            $sWay = $this->GetParam(2) == 'down' ? 'down' : 'up';
            $iSortOld = $oPage->getSort();
            if ($oPagePrev = $this->Page_GetNextPageBySort($iSortOld, $oPage->getPid(), $sWay)) {
                $iSortNew = $oPagePrev->getSort();
                $oPagePrev->setSort($iSortOld);
                $this->Page_UpdatePage($oPagePrev);
            } else {
                if ($sWay == 'down') {
                    $iSortNew = $iSortOld - 1;
                } else {
                    $iSortNew = $iSortOld + 1;
                }
            }

            // * Меняем значения сортировки местами
            $oPage->setSort($iSortNew);
            $this->Page_UpdatePage($oPage);
            $this->Page_ReSort();
        }
        Router::Location('admin/content-pages');
    }

    protected function _eventPagesEdit($sMode) {

        $this->_setTitle($this->Lang_Get('action.admin.pages_title'));
        $this->SetTemplateAction('content/pages_add');
        $this->Viewer_Assign('sMode', $sMode);

        // * Обработка создания новой страницы
        if (F::isPost('submit_page_save')) {
            if (!F::GetRequest('page_id')) {
                $this->SubmitAddPage();
            }
        }
        // * Обработка показа страницы для редактирования
        if ($this->GetParam(0) == 'edit') {
            if ($oPageEdit = $this->Page_GetPageById($this->GetParam(1))) {
                if (!F::isPost('submit_page_save')) {
                    $_REQUEST['page_title'] = $oPageEdit->getTitle();
                    $_REQUEST['page_pid'] = $oPageEdit->getPid();
                    $_REQUEST['page_url'] = $oPageEdit->getUrl();
                    $_REQUEST['page_text'] = $oPageEdit->getText();
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
                $this->Viewer_Assign('oPageEdit', $oPageEdit);
            } else {
                $this->Message_AddError($this->Lang_Get('action.admin.pages_edit_notfound'), $this->Lang_Get('error'));
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
            $this->Message_AddError($this->Lang_Get('system_error'));
            return;
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
            $oPageParent = $this->Page_GetPageById(F::GetRequest('page_pid'));
            $oPageEdit->setUrlFull($oPageParent->getUrlFull() . '/' . F::GetRequest('page_url'));
        }
        $oPageEdit->setSeoDescription(F::GetRequest('page_seo_description'));
        $oPageEdit->setSeoKeywords(F::GetRequest('page_seo_keywords'));
        $oPageEdit->setText(F::GetRequest('page_text'));
        $oPageEdit->setTitle(F::GetRequest('page_title'));
        $oPageEdit->setUrl(F::GetRequest('page_url'));
        $oPageEdit->setSort(F::GetRequest('page_sort'));

        // * Обновляем страницу
        if ($this->Page_UpdatePage($oPageEdit)) {
            $this->Page_RebuildUrlFull($oPageEdit);
            $this->Message_AddNotice($this->Lang_Get('action.admin.pages_edit_submit_save_ok'));
            $this->SetParam(0, null);
            $this->SetParam(1, null);
            Router::Location('admin/content-pages/');
        } else {
            $this->Message_AddError($this->Lang_Get('system_error'));
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
        $oPage = Engine::GetEntity('Page');
        $oPage->setActive(F::GetRequest('page_active') ? 1 : 0);
        $oPage->setAutoBr(F::GetRequest('page_auto_br') ? 1 : 0);
        $oPage->setMain(F::GetRequest('page_main') ? 1 : 0);
        $oPage->setDateAdd(F::Now());
        if (F::GetRequest('page_pid') == 0) {
            $oPage->setUrlFull(F::GetRequest('page_url'));
            $oPage->setPid(null);
        } else {
            $oPage->setPid(F::GetRequest('page_pid'));
            $oPageParent = $this->Page_GetPageById(F::GetRequest('page_pid'));
            $oPage->setUrlFull($oPageParent->getUrlFull() . '/' . F::GetRequest('page_url'));
        }
        $oPage->setSeoDescription(F::GetRequest('page_seo_description'));
        $oPage->setSeoKeywords(F::GetRequest('page_seo_keywords'));
        $oPage->setText(F::GetRequest('page_text'));
        $oPage->setTitle(F::GetRequest('page_title'));
        $oPage->setUrl(F::GetRequest('page_url'));
        if (F::GetRequest('page_sort')) {
            $oPage->setSort(F::GetRequest('page_sort'));
        } else {
            $oPage->setSort($this->Page_GetMaxSortByPid($oPage->getPid()) + 1);
        }
        /**
         * Добавляем страницу
         */
        if ($this->Page_AddPage($oPage)) {
            $this->Message_AddNotice($this->Lang_Get('action.admin.pages_create_submit_save_ok'));
            $this->SetParam(0, null);
            Router::Location('admin/content-pages/');
        } else {
            $this->Message_AddError($this->Lang_Get('system_error'));
        }
    }

    /**
     * Проверка полей на корректность
     *
     * @return bool
     */
    protected function CheckPageFields() {

        $this->Security_ValidateSendForm();

        $bOk = true;
        /**
         * Проверяем есть ли заголовок топика
         */
        if (!F::CheckVal(F::GetRequest('page_title', null, 'post'), 'text', 2, 200)) {
            $this->Message_AddError($this->Lang_Get('action.admin.pages_create_title_error'), $this->Lang_Get('error'));
            $bOk = false;
        }
        /**
         * Проверяем есть ли заголовок топика, с заменой всех пробельных символов на "_"
         */
        $pageUrl = preg_replace("/\s+/", '_', (string)F::GetRequest('page_url', null, 'post'));
        $_REQUEST['page_url'] = $pageUrl;
        if (!F::CheckVal(F::GetRequest('page_url', null, 'post'), 'login', 1, 50)) {
            $this->Message_AddError($this->Lang_Get('action.admin.pages_create_url_error'), $this->Lang_Get('error'));
            $bOk = false;
        }
        /**
         * Проверяем на счет плохих УРЛов
         */
        /*if (in_array(F::GetRequest('page_url',null,'post'),$this->aBadPageUrl)) {
            $this->Message_AddError($this->Lang_Get('action.admin.pages_create_url_error_bad').' '.join(',',$this->aBadPageUrl),$this->Lang_Get('error'));
            $bOk=false;
        }*/
        /**
         * Проверяем есть ли содержание страницы
         */
        if (!F::CheckVal(F::GetRequest('page_text', null, 'post'), 'text', 1, 50000)) {
            $this->Message_AddError($this->Lang_Get('action.admin.pages_create_text_error'), $this->Lang_Get('error'));
            $bOk = false;
        }
        /**
         * Проверяем страницу в которую хотим вложить
         */
        if (F::GetRequest('page_pid') != 0 && !($oPageParent = $this->Page_GetPageById(F::GetRequest('page_pid')))) {
            $this->Message_AddError($this->Lang_Get('action.admin.pages_create_parent_page_error'), $this->Lang_Get('error'));
            $bOk = false;
        }
        /**
         * Проверяем сортировку
         */
        if (F::GetRequest('page_sort') && !is_numeric(F::GetRequest('page_sort'))) {
            $this->Message_AddError($this->Lang_Get('action.admin.pages_create_sort_error'), $this->Lang_Get('error'));
            $bOk = false;
        }
        /**
         * Выполнение хуков
         */
        $this->Hook_Run('check_page_fields', array('bOk' => &$bOk));

        return $bOk;
    }


    /**********************************************************************************/

    protected function EventBlogs() {

        $this->_setTitle($this->Lang_Get('action.admin.blogs_title'));
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

        $aResult = $this->Blog_GetBlogsByFilter($aFilter, '', $nPage, Config::Get('admin.items_per_page'));
        $aPaging = $this->Viewer_MakePaging($aResult['count'], $nPage, Config::Get('admin.items_per_page'), 4,
            Router::GetPath('admin') . 'content-blogs/list/' . $sMode);

        $aBlogTypes = $this->Blog_GetBlogTypes();
        $nBlogsTotal = 0;
        foreach ($aBlogTypes as $oBlogType) {
            $nBlogsTotal += $oBlogType->GetBlogsCount();
        }
        $aAllBlogs = $this->Blog_GetBlogs();
        foreach($aAllBlogs as $nBlogId=>$oBlog) {
            $aAllBlogs[$nBlogId] = $oBlog->GetTitle();
        }

        $this->Viewer_Assign('nBlogsTotal', $nBlogsTotal);
        $this->Viewer_Assign('aBlogTypes', $aBlogTypes);
        $this->Viewer_Assign('aBlogs', $aResult['collection']);
        $this->Viewer_Assign('aAllBlogs', $aAllBlogs);

        $this->Viewer_Assign('sMode', $sMode);
        $this->Viewer_Assign('aPaging', $aPaging);
    }

    protected function _eventBlogsDelete() {

        $nBlogId = $this->GetPost('delete_blog_id');
        if (!$nBlogId || !($oBlog = $this->Blog_GetBlogById($nBlogId))) {
            $this->Message_AddError($this->Lang_Get('action.admin.blog_del_error'));
            return false;
        }

        if ($this->GetPost('delete_topics') !== 'delete') {
            // Топики перемещаются в новый блог
            $aTopics = $this->Topic_GetTopicsByBlogId($nBlogId);
            $nNewBlogId = intval($this->GetPost('topic_move_to'));
            if (($nNewBlogId > 0) && is_array($aTopics) && count($aTopics)) {
                if (!$oBlogNew = $this->Blog_GetBlogById($nNewBlogId)) {
                    $this->Message_AddError($this->Lang_Get('blog_admin_delete_move_error'), $this->Lang_Get('error'));
                    return false;
                }
                // * Если выбранный блог является персональным, возвращаем ошибку
                if ($oBlogNew->getType() == 'personal') {
                    $this->Message_AddError($this->Lang_Get('blog_admin_delete_move_personal'), $this->Lang_Get('error'));
                    return false;
                }
                // * Перемещаем топики
                if (!$this->Topic_MoveTopics($nBlogId, $nNewBlogId)) {
                    $this->Message_AddError($this->Lang_Get('action.admin.blog_del_move_error'), $this->Lang_Get('error'));
                    return false;
                }
            } else {
                $this->Message_AddError($this->Lang_Get('action.admin.blog_del_move_error'), $this->Lang_Get('error'));
                return false;
            }
        }

        // * Удаляяем блог
        $this->Hook_Run('blog_delete_before', array('sBlogId' => $nBlogId));
        if ($this->Blog_DeleteBlog($nBlogId)) {
            $this->Hook_Run('blog_delete_after', array('sBlogId' => $nBlogId));
            $this->Message_AddNoticeSingle(
                $this->Lang_Get('blog_admin_delete_success'), $this->Lang_Get('attention'), true
            );
        } else {
            $this->Message_AddNoticeSingle(
                $this->Lang_Get('action.admin.blog_del_error'), $this->Lang_Get('error'), true
            );
        }
        Router::ReturnBack();
    }

    /**********************************************************************************/

    protected function EventTopics() {

        $this->_setTitle($this->Lang_Get('action.admin.topics_title'));
        $this->SetTemplateAction('content/topics_list');

        $sCmd = $this->GetPost('cmd');
        if ($sCmd == 'delete') {
            $this->_topicDelete();
        } else {
            // * Передан ли номер страницы
            $nPage = $this->_getPageNum();
        }

        $aResult = $this->Topic_GetTopicsByFilter(array(), $nPage, Config::Get('admin.items_per_page'));
        $aPaging = $this->Viewer_MakePaging($aResult['count'], $nPage, Config::Get('admin.items_per_page'), 4,
            Router::GetPath('admin') . 'content-topics/');

        $this->Viewer_Assign('aTopics', $aResult['collection']);
        $this->Viewer_Assign('aPaging', $aPaging);

        $this->Lang_AddLangJs(array(
                'topic_delete_confirm_title',
                'topic_delete_confirm_text',
                'topic_delete_confirm',
            ));
    }

    /**********************************************************************************/

    protected function EventComments() {

        $this->_setTitle($this->Lang_Get('action.admin.comments_title'));
        $this->SetTemplateAction('content/comments_list');

        $sCmd = $this->GetPost('cmd');
        if ($sCmd == 'delete') {
            $this->_commentDelete();
        }

        // * Передан ли номер страницы
        $nPage = $this->_getPageNum();

        $aResult = $this->Comment_GetCommentsByFilter(array(), '', $nPage, Config::Get('admin.items_per_page'));
        $aPaging = $this->Viewer_MakePaging($aResult['count'], $nPage, Config::Get('admin.items_per_page'), 4,
            Router::GetPath('admin') . 'content-comments/');

        $this->Viewer_Assign('aComments', $aResult['collection']);
        $this->Viewer_Assign('aPaging', $aPaging);
    }

    /**********************************************************************************/

    /**
     * View and managment of Mresources
     */
    protected function EventMresources() {

        $this->_setTitle($this->Lang_Get('action.admin.mresources_title'));
        $this->SetTemplateAction('content/mresources_list');

        $sCmd = $this->GetPost('cmd');
        if ($sCmd == 'delete') {
            $this->_eventMresourcesDelete();
        }

        // * Передан ли номер страницы
        $nPage = $this->_getPageNum();

        $aFilter = array(
            //'type' => ModuleMresource::TYPE_IMAGE,
        );
        $aCriteria = array(
            'fields' => array('mr.*', 'targets_count'),
            'filter' => $aFilter,
            'limit'  => array(($nPage - 1) * Config::Get('admin.items_per_page'), Config::Get('admin.items_per_page')),
            'with'   => array('user'),
        );
        $aResult = $this->Mresource_GetMresourcesByCriteria($aCriteria);

        $aPaging = $this->Viewer_MakePaging($aResult['count'], $nPage, Config::Get('admin.items_per_page'), 4,
            Router::GetPath('admin') . 'content-mresources/');

        $this->Lang_AddLangJs(
            array(
                 'action.admin.mresource_delete_confirm',
                 'action.admin.mresource_will_be_delete',
            )
        );

        $this->Viewer_Assign('aMresources', $aResult['collection']);
        $this->Viewer_Assign('aPaging', $aPaging);
    }

    /**
     * @return bool
     */
    protected function _eventMresourcesDelete() {

        if ($iMresourceId = $this->GetPost('mresource_id')) {
            if ($this->Mresource_DeleteMresources($iMresourceId)) {
                $this->Message_AddNotice($this->Lang_Get('action.admin.mresource_deleted'));
                return true;
            }
        }
        $this->Message_AddError($this->Lang_Get('action.admin.mresource_not_deleted'));
        return false;
    }

    /**********************************************************************************/

    protected function EventUsers() {

        $this->_setTitle($this->Lang_Get('action.admin.users_title'));
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
            } elseif ($sCmd == 'adm_del_user') {
                if ($this->_eventUsersCmdDelete()) {
                    $nPage = $this->_getPageNum();
                    Router::Location('admin/users-list/' . ($nPage ? 'page' . $nPage : ''));
                } else {
                    Router::ReturnBack();
                }
            } elseif ($sCmd == 'adm_user_message') {
                $this->_eventUsersCmdMessage();
            } elseif ($sCmd == 'adm_user_activate') {
                $this->_eventUsersCmdActivate();
            }
            Router::Location('admin/users-list/');
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
        $this->Viewer_Assign('sMode', $sMode);
        $this->Viewer_Assign('nCountUsers', $this->User_GetCountUsers());
        $this->Viewer_Assign('nCountAdmins', $this->User_GetCountAdmins());
    }

    protected function _eventUsersCmdBan($aUsersId, $nDays, $sComment) {

        if ($aUsersId) {
            if (in_array(E::UserId(), $aUsersId)) {
                $this->Message_AddError($this->Lang_Get('action.admin.cannot_ban_self'), null, true);
                return false;
            }
            if (in_array(1, $aUsersId)) {
                $this->Message_AddError($this->Lang_Get('action.admin.cannot_ban_admin'), null, true);
                return false;
            }
            $aUsers = $this->User_GetUsersByArrayId($aUsersId);
            foreach ($aUsers as $oUser) {
                if ($oUser->isAdministrator()) {
                    $this->Message_AddError($this->Lang_Get('action.admin.cannot_ban_admin'), null, true);
                    return false;
                }
            }
            if ($this->Admin_BanUsers($aUsersId, $nDays, $sComment)) {
                $this->Message_AddNotice($this->Lang_Get('action.admin.action_ok'), null, true);
                return true;
            } else {
                $this->Message_AddError($this->Lang_Get('action.admin.action_err'), null, true);
            }
        }
        return false;
    }

    protected function _eventUsersCmdUnban($aUsersId) {

        if ($aUsersId) {
            $aId = F::Array_Str2ArrayInt($aUsersId, ',', true);
            if ($this->Admin_UnbanUsers($aId)) {
                $this->Message_AddNotice($this->Lang_Get('action.admin.action_ok'), null, true);
                return true;
            } else {
                $this->Message_AddError($this->Lang_Get('action.admin.action_err'), null, true);
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
            if ($this->Admin_SetBanIp($sIp1, $sIp2, $nDays, $sComment)) {
                $this->Message_AddNotice($this->Lang_Get('action.admin.action_ok'), null, true);
                return true;
            }
        }
        $this->Message_AddError($this->Lang_Get('action.admin.action_err'), null, true);
        return false;
    }

    protected function _eventUsersList($sMode) {

        $this->SetTemplateAction('users/list');
        // * Передан ли номер страницы
        $nPage = $this->_getPageNum();

        $aFilter = array();
        $sData = $this->Session_Get('adm_userlist_filter');
        if ($sData) {
            $aFilter = @unserialize($sData);
            if (!is_array($aFilter)) {
                $aFilter = array();
            }
        }

        if ($sMode == 'admins') {
            $aFilter['admin'] = 1;
        }

        $aResult = $this->User_GetUsersByFilter($aFilter, '', $nPage, Config::Get('admin.items_per_page'));
        $aPaging = $this->Viewer_MakePaging($aResult['count'], $nPage, Config::Get('admin.items_per_page'), 4,
            Router::GetPath('admin') . 'users-list/');

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
            } elseif ($sKey == 'admin' || !$xVal) {
                unset($aFilter[$sKey]);
            }
        }
        $this->Viewer_Assign('aUsers', $aResult['collection']);
        $this->Viewer_Assign('aPaging', $aPaging);
        $this->Viewer_Assign('aFilter', $aFilter);
    }

    protected function _eventUsersCmdSetAdministrator() {

        $aUserLogins = F::Str2Array($this->GetPost('user_login_admin'), ',', true);
        if ($aUserLogins)
            foreach ($aUserLogins as $sUserLogin) {
                if (!$sUserLogin || !($oUser = $this->User_GetUserByLogin($sUserLogin))) {
                    $this->Message_AddError($this->Lang_Get('action.admin.user_not_found', array('user' => $sUserLogin)));
                } elseif ($oUser->IsBanned()) {
                    $this->Message_AddError($this->Lang_Get('action.admin.cannot_banned_admin'));
                } elseif ($oUser->IsAdministrator()) {
                    $this->Message_AddError($this->Lang_Get('action.admin.already_added'));
                } else {
                    if ($this->Admin_SetAdministrator($oUser->GetId())) {
                        $this->Message_AddNotice($this->Lang_Get('action.admin.saved_ok'));
                    } else {
                        $this->Message_AddError($this->Lang_Get('action.admin.saved_err'));
                    }
                }
            }
        Router::ReturnBack(true);
    }

    protected function _eventUsersCmdUnsetAdministrator() {

        $aUserLogins = F::Str2Array($this->GetPost('users_list'), ',', true);
        if ($aUserLogins)
            foreach ($aUserLogins as $sUserLogin) {
                if (!$sUserLogin || !($oUser = $this->User_GetUserByLogin($sUserLogin))) {
                    $this->Message_AddError($this->Lang_Get('action.admin.user_not_found', array('user' => $sUserLogin)), 'admins:delete');
                } else {
                    if (mb_strtolower($sUserLogin) == 'admin') {
                        $this->Message_AddError($this->Lang_Get('action.admin.cannot_with_admin'), 'admins:delete');
                    } elseif ($this->Admin_UnsetAdministrator($oUser->GetId())) {
                        $this->Message_AddNotice($this->Lang_Get('action.admin.saved_ok'), 'admins:delete');
                    } else {
                        $this->Message_AddError($this->Lang_Get('action.admin.saved_err'), 'admins:delete');
                    }
                }
            }
        Router::ReturnBack(true);
    }

    protected function _eventUsersProfile() {

        $nUserId = $this->GetParam(1);
        $oUserProfile = $this->User_GetUserById($nUserId);
        if (!$oUserProfile) {
            $this->Message_AddError($this->Lang_Get('action.admin.user_not_found'));
            return;
        }

        $sMode = $this->GetParam(2);
        //$aUserVoteStat = $this->User_GetUserVoteStats($oUserProfile->getId());

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

        $this->Viewer_Assign('sMode', $sMode);
        $this->Viewer_Assign('oUserProfile', $oUserProfile);
        //$this->Viewer_Assign('aUserVoteStat', $aUserVoteStat);
        $this->Viewer_Assign('nParamVoteValue', 1);

    }

    protected function _eventUsersProfileInfo($oUserProfile) {

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
        $this->Session_Set('adm_userlist_filter', serialize($aFilter));
    }

    /**
     * Deletes user
     *
     * @return bool
     */
    protected function _eventUsersCmdDelete() {

        $this->Security_ValidateSendForm();

        $aUsersLogin = F::Str2Array(F::GetRequest('adm_user_list'), ',', true);
        $bResult = true;
        foreach ($aUsersLogin as $iUserId) {
            if ($iUserId == $this->oUserCurrent->GetId()) {
                $this->Message_AddError($this->Lang_Get('action.admin.cannot_del_self'), null, true);
                $bResult = false;
                break;
            } elseif (($oUser = $this->User_GetUserById($iUserId))) {
                if ($oUser->IsAdministrator()) {
                    $this->Message_AddError($this->Lang_Get('action.admin.cannot_del_admin'), null, true);
                    $bResult = false;
                    break;
                } elseif (!F::GetRequest('adm_user_del_confirm') && !F::GetRequest('adm_bulk_confirm')) {
                    $this->Message_AddError($this->Lang_Get('action.admin.cannot_del_confirm'), null, true);
                    $bResult = false;
                    break;
                } else {
                    $this->Admin_DelUser($oUser->GetId());
                    $this->Message_AddNotice($this->Lang_Get('action.admin.user_deleted', Array('user' => $oUser->getLogin())), null, true);
                }
            } else {
                $this->Message_AddError($this->Lang_Get('action.admin.user_not_found'), null, true);
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
        $sText = $this->Text_Parser(F::GetRequest('talk_text'));
        $sDate = date(F::Now());
        $sIp = F::GetUserIp();

        if (($sUsers = $this->GetPost('users_list'))) {
            $aUsers = explode(',', str_replace(' ', '', $sUsers));
        } else {
            $aUsers = array();
        }

        if ($aUsers) {
            if ($bOk && $aUsers) {
                $oTalk = Engine::GetEntity('Talk_Talk');
                $oTalk->setUserId($this->oUserCurrent->getId());
                $oTalk->setUserIdLast($this->oUserCurrent->getId());
                $oTalk->setTitle($sTitle);
                $oTalk->setText($sText);
                $oTalk->setDate($sDate);
                $oTalk->setDateLast($sDate);
                $oTalk->setUserIp($sIp);
                $oTalk = $this->Talk_AddTalk($oTalk);

                // добавляем себя в общий список
                $aUsers[] = $this->oUserCurrent->getLogin();
                // теперь рассылаем остальным
                foreach ($aUsers as $sUserLogin) {
                    if ($sUserLogin && ($oUserRecipient = $this->User_GetUserByLogin($sUserLogin))) {
                        $oTalkUser = Engine::GetEntity('Talk_TalkUser');
                        $oTalkUser->setTalkId($oTalk->getId());
                        $oTalkUser->setUserId($oUserRecipient->GetId());
                        if ($sUserLogin != $this->oUserCurrent->getLogin()) {
                            $oTalkUser->setDateLast(null);
                        } else {
                            $oTalkUser->setDateLast($sDate);
                        }
                        $this->Talk_AddTalkUser($oTalkUser);

                        // Отправляем уведомления
                        if ($sUserLogin != $this->oUserCurrent->getLogin() || F::GetRequest('send_copy_self')) {
                            $oUserToMail = $this->User_GetUserById($oUserRecipient->GetId());
                            $this->Notify_SendTalkNew($oUserToMail, $this->oUserCurrent, $oTalk);
                        }
                    }
                }
            }
        }

        if ($bOk) {
            $this->Message_AddNotice($this->Lang_Get('action.admin.msg_sent_ok'), null, true);
        } else {
            $this->Message_AddError($this->Lang_Get('system_error'), null, true);
        }
    }

    protected function _eventUsersCmdMessageSeparate() {

        $bOk = true;

        $sTitle = F::GetRequest('talk_title');

        $sText = $this->Text_Parser(F::GetRequest('talk_text'));
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
                $oSelfTalk = Engine::GetEntity('Talk_Talk');
                $oSelfTalk->setUserId($this->oUserCurrent->getId());
                $oSelfTalk->setUserIdLast($this->oUserCurrent->getId());
                $oSelfTalk->setTitle($sTitle);
                $oSelfTalk->setText($this->Text_Parser('To: <i>' . $sUsers . '</i>' . "\n\n" . 'Msg: ' . $this->GetPost('talk_text')));
                $oSelfTalk->setDate($sDate);
                $oSelfTalk->setDateLast($sDate);
                $oSelfTalk->setUserIp($sIp);
                if (($oSelfTalk = $this->Talk_AddTalk($oSelfTalk))) {
                    $oTalkUser = Engine::GetEntity('Talk_TalkUser');
                    $oTalkUser->setTalkId($oSelfTalk->getId());
                    $oTalkUser->setUserId($this->oUserCurrent->getId());
                    $oTalkUser->setDateLast($sDate);
                    $this->Talk_AddTalkUser($oTalkUser);

                    // уведомление по e-mail
                    $oUserToMail = $this->oUserCurrent;
                    $this->Notify_SendTalkNew($oUserToMail, $this->oUserCurrent, $oSelfTalk);
                } else {
                    $bOk = false;
                }
            }

            if ($bOk) {
                // теперь рассылаем остальным - каждому отдельное сообщение
                foreach ($aUsers as $sUserLogin) {
                    if ($sUserLogin && $sUserLogin != $this->oUserCurrent->getLogin() && ($oUserRecipient = $this->User_GetUserByLogin($sUserLogin))) {
                        $oTalk = Engine::GetEntity('Talk_Talk');
                        $oTalk->setUserId($this->oUserCurrent->getId());
                        $oTalk->setUserIdLast($this->oUserCurrent->getId());
                        $oTalk->setTitle($sTitle);
                        $oTalk->setText($sText);
                        $oTalk->setDate($sDate);
                        $oTalk->setDateLast($sDate);
                        $oTalk->setUserIp($sIp);
                        if (($oTalk = $this->Talk_AddTalk($oTalk))) {
                            $oTalkUser = Engine::GetEntity('Talk_TalkUser');
                            $oTalkUser->setTalkId($oTalk->getId());
                            $oTalkUser->setUserId($oUserRecipient->GetId());
                            $oTalkUser->setDateLast(null);
                            $this->Talk_AddTalkUser($oTalkUser);

                            // Отправка самому себе, чтобы можно было читать ответ
                            $oTalkUser = Engine::GetEntity('Talk_TalkUser');
                            $oTalkUser->setTalkId($oTalk->getId());
                            $oTalkUser->setUserId($this->oUserCurrent->getId());
                            $oTalkUser->setDateLast($sDate);
                            $this->Talk_AddTalkUser($oTalkUser);

                            // Отправляем уведомления
                            $oUserToMail = $this->User_GetUserById($oUserRecipient->GetId());
                            $this->Notify_SendTalkNew($oUserToMail, $this->oUserCurrent, $oTalk);
                        } else {
                            $bOk = false;
                            break;
                        }
                    }
                }
            }
        }

        if ($bOk) {
            $this->Message_AddNotice($this->Lang_Get('action.admin.msg_sent_ok'), null, true);
        } else {
            $this->Message_AddError($this->Lang_Get('system_error'), null, true);
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
                $oUser = $this->User_GetUserByLogin($sUserLogin);
                $oUser->setActivate(1);
                $oUser->setDateActivate(F::Now());
                $this->User_Update($oUser);
            }
        }
        Router::ReturnBack();
    }

    /**********************************************************************************/

    protected function EventInvites() {

        $this->_setTitle($this->Lang_Get('action.admin.invites_title'));
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
            $iCountInviteAvailable = $this->User_GetCountInviteAvailable($this->oUserCurrent);
        }
        $this->Viewer_Assign('iCountInviteAvailable', $iCountInviteAvailable);
        $this->Viewer_Assign('iCountInviteUsed', $this->User_GetCountInviteUsed($this->oUserCurrent->getId()));
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
        $aResult = $this->Admin_GetInvites($nPage, Config::Get('admin.items_per_page'), $aFilter);
        $aInvites = $aResult['collection'];
        $aCounts = $this->Admin_GetInvitesCount();

        // Формируем постраничность
        $aPaging = $this->Viewer_MakePaging($aResult['count'], $nPage, Config::Get('admin.items_per_page'), 4, Router::GetPath('admin') . 'users-invites');
        $this->Viewer_Assign('aPaging', $aPaging);
        $this->Viewer_Assign('aInvites', $aInvites);
        $this->Viewer_Assign('aCounts', $aCounts);
        $this->Viewer_Assign('sMode', $sMode);
    }

    protected function _eventInvitesDelete() {

        $this->Security_ValidateSendForm();

        $aIds = array();
        foreach ($_POST as $sKey => $sVal) {
            if ((substr($sKey, 0, 7) == 'invite_') && ($nId = intval(substr($sKey, 7)))) {
                $aIds[] = $nId;
            }
        }
        if ($aIds) {
            $nResult = $this->Admin_DeleteInvites($aIds);
            $this->Message_AddNotice($this->Lang_Get('action.admin.invaite_deleted', array('num' => $nResult)));
        }
        Router::ReturnBack(true);
    }

    /**********************************************************************************/

    protected function EventBanlist() {

        $this->_setTitle($this->Lang_Get('action.admin.banlist_title'));
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
        $this->Viewer_Assign('sMode', $sMode);
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
                $aUsers = $this->User_GetUsersByFilter(array('login' => $aUsersLogin), '', 1, 100, array());
                if ($aUsers) {
                    // и их баним
                    $this->_eventUsersCmdBan(array_keys($aUsers['collection']), $nBanDays, $sBanComment);
                }
            } elseif ($sIp) {
                $this->_eventIpsCmdBan($sIp, $nBanDays, $sBanComment);
            }
        } elseif ($sCmd == 'adm_unsetban_ip') {
            $aId = F::Array_Str2ArrayInt($this->GetPost('bans_list'), ',', true);
            $this->Admin_UnsetBanIp($aId);
        } elseif ($sCmd == 'adm_unsetban_user') {
            $aUsersId = F::Array_Str2ArrayInt($this->GetPost('bans_list'), ',', true);
            $this->_eventUsersCmdUnban($aUsersId);
        }
        Router::ReturnBack(true);
    }

    protected function _eventBanlistIds($nPage) {

        $this->SetTemplateAction('users/banlist_ids');

        // Получаем список забаненных юзеров
        $aResult = $this->Admin_GetUsersBanList($nPage, Config::Get('admin.items_per_page'));

        // Формируем постраничность
        $aPaging = $this->Viewer_MakePaging(
            $aResult['count'], $nPage, Config::Get('admin.items_per_page'), 4, Router::GetPath('admin') . 'banlist/ids/'
        );
        $this->Viewer_Assign('aPaging', $aPaging);
        $this->Viewer_Assign('aUserList', $aResult['collection']);
    }

    protected function _eventBanlistIps($nPage) {

        $this->SetTemplateAction('users/banlist_ips');

        // Получаем список забаненных ip-адресов
        $aResult = $this->Admin_GetIpsBanList($nPage, Config::Get('admin.items_per_page'));

        // Формируем постраничность
        $aPaging = $this->Viewer_MakePaging(
            $aResult['count'], $nPage, Config::Get('admin.items_per_page'), 4, Router::GetPath('admin') . 'banlist/ips/'
        );
        $this->Viewer_Assign('aPaging', $aPaging);
        $this->Viewer_Assign('aIpsList', $aResult['collection']);
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

        $this->_setTitle($this->Lang_Get('action.admin.skins_title'));
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

        $aSkins = $this->Skin_GetSkinsList($aFilter);
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
            $this->Viewer_Assign('sSiteSkin', $sAdminSkin);
            $this->Viewer_Assign('sSiteTheme', $sAdminTheme);
        } else {
            $this->Viewer_Assign('sSiteSkin', $sSiteSkin);
            $this->Viewer_Assign('sSiteTheme', $sSiteTheme);
        }

        $this->Viewer_Assign('oActiveSkin', $oActiveSkin);
        $this->Viewer_Assign('aSkins', $aSkins);
        $this->Viewer_Assign('sMode', $sMode);
    }

    protected function _eventSkinActivate($sMode, $sSkin) {

        $aConfig = array('view.skin' => $sSkin);
        Config::WriteCustomConfig($aConfig);
        Router::Location('admin/site-skins/' . $sMode . '/');
    }

    protected function _eventSkinThemeActivate($sMode, $sSkin, $sTheme) {

        $aConfig = array('skin.' . $sSkin . '.config.view.theme' => $sTheme);
        Config::WriteCustomConfig($aConfig);
        Router::Location('admin/site-skins/' . $sMode . '/');
    }

    /**********************************************************************************/

    /**
     * View logs
     */
    protected function EventLogs() {

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
            $this->_setTitle($this->Lang_Get('action.admin.logs_sql_errors_title'));
            $this->SetTemplateAction('logs/sql_errors');
            $this->_eventLogsSqlErrors($sLogTxt);
        } elseif ($this->sCurrentEvent == 'logs-sqllog') {
            $this->_setTitle($this->Lang_Get('action.admin.logs_sql_title'));
            $this->SetTemplateAction('logs/sql_log');
            $this->_eventLogsSql($sLogTxt);
        } else {
            $this->_setTitle($this->Lang_Get('action.admin.logs_errors_title'));
            $this->SetTemplateAction('logs/errors');
            $this->_eventLogsErrors($sLogTxt);
        }

        $this->Viewer_Assign('sLogTxt', $sLogTxt);
    }

    protected function _eventLogsErrorDelete($sLogFile) {

        F::File_Delete($sLogFile);
        //Router::Location();
    }

    protected function _parseLog($sLogTxt) {

        $aLogs = array();
        if (preg_match_all('/\[LOG\:(?<id>[\d\-\.\,]+)\]\[(?<date>[\d\-\s\:]+)\].*\[\[(?<text>.*)\]\]/siuU', $sLogTxt, $aM, PREG_PATTERN_ORDER)) {
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

        $this->Viewer_Assign('aLogs', $aLogs);
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

        $this->Viewer_Assign('aLogs', $aLogs);
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

        $this->Viewer_Assign('aLogs', $aLogs);
    }

    /**********************************************************************************/

    protected function EventReset() {

        $this->_setTitle($this->Lang_Get('action.admin.reset_title'));
        $this->SetTemplateAction('tools/reset');

        $aSettings = array();
        if ($this->GetPost('adm_reset_submit')) {
            $aConfig = array();
            if ($this->GetPost('adm_cache_clear_data')) {
                $this->Cache_Clean();
                $aSettings['adm_cache_clear_data'] = 1;
            }
            if ($this->GetPost('adm_cache_clear_assets')) {
                $this->Viewer_ClearAssetsFiles();
                $aConfig['assets.version'] = time();
                $aSettings['adm_cache_clear_assets'] = 1;
            }
            if ($this->GetPost('adm_cache_clear_smarty')) {
                $this->Viewer_ClearSmartyFiles();
                $aSettings['adm_cache_clear_smarty'] = 1;
            }
            if ($this->GetPost('adm_reset_config_data')) {
                $this->_eventResetCustomConfig();
                $aSettings['adm_reset_config_data'] = 1;
            }

            if ($aConfig) {
                Config::WriteCustomConfig($aConfig);
            }
            $this->Message_AddNotice($this->Lang_Get('action.admin.action_ok'), null, true);

            if ($aSettings) {
                $this->Session_SetCookie('adm_tools_reset', serialize($aSettings));
            } else {
                $this->Session_DelCookie('adm_tools_reset');
            }
            Router::Location('admin/tools-reset/');
        }
        if ($sSettings = $this->Session_GetCookie('adm_tools_reset')) {
            $aSettings = @unserialize($sSettings);
            if (is_array($aSettings)) {
                $this->Viewer_Assign('aSettings', $aSettings);
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

        $this->_setTitle($this->Lang_Get('action.admin.comments_tree_title'));
        $this->SetTemplateAction('tools/comments_tree');
        if (F::isPost('comments_tree_submit')) {
            $this->Security_ValidateSendForm();
            set_time_limit(0);
            $this->Comment_RestoreTree();
            $this->Cache_Clean();

            $this->Message_AddNotice($this->Lang_Get('comments_tree_restored'), $this->Lang_Get('attention'));
            $this->Viewer_Assign('bActionEnable', false);
        } else {
            if (Config::Get('module.comment.use_nested')) {
                $this->Viewer_Assign('sMessage', $this->Lang_Get('action.admin.comments_tree_message'));
                $this->Viewer_Assign('bActionEnable', true);
            } else {
                $this->Viewer_Assign('sMessage', $this->Lang_Get('action.admin.comments_tree_disabled'));
                $this->Viewer_Assign('bActionEnable', false);
            }
        }
    }

    /**********************************************************************************/

    /**
     * Пересчет счетчика избранных
     *
     */
    protected function EventRecalculateFavourites() {

        $this->_setTitle($this->Lang_Get('action.admin.recalcfavourites_title'));
        $this->SetTemplateAction('tools/recalcfavourites');
        if (F::isPost('recalcfavourites_submit')) {
            $this->Security_ValidateSendForm();
            set_time_limit(0);
            $this->Comment_RecalculateFavourite();
            $this->Topic_RecalculateFavourite();
            $this->Cache_Clean();

            $this->Message_AddNotice($this->Lang_Get('action.admin.favourites_recalculated'), $this->Lang_Get('attention'));
            $this->Viewer_Assign('bActionEnable', false);
        } else {
            $this->Viewer_Assign('sMessage', $this->Lang_Get('action.admin.recalcfavourites_message'));
            $this->Viewer_Assign('bActionEnable', true);
        }
    }

    /**********************************************************************************/

    /**
     * Пересчет счетчика голосований
     */
    protected function EventRecalculateVotes() {

        $this->_setTitle($this->Lang_Get('action.admin.recalcvotes_title'));
        $this->SetTemplateAction('tools/recalcvotes');
        if (F::isPost('recalcvotes_submit')) {
            $this->Security_ValidateSendForm();
            set_time_limit(0);
            $this->Topic_RecalculateVote();
            $this->Cache_Clean();

            $this->Message_AddNotice($this->Lang_Get('action.admin.votes_recalculated'), $this->Lang_Get('attention'));
        } else {
            $this->Viewer_Assign('sMessage', $this->Lang_Get('action.admin.recalcvotes_message'));
            $this->Viewer_Assign('bActionEnable', true);
        }
    }

    /**********************************************************************************/

    /**
     * Пересчет количества топиков в блогах
     */
    protected function EventRecalculateTopics() {

        $this->_setTitle($this->Lang_Get('action.admin.recalctopics_title'));
        $this->SetTemplateAction('tools/recalctopics');
        if (F::isPost('recalctopics_submit')) {
            $this->Security_ValidateSendForm();
            set_time_limit(0);
            $this->Blog_RecalculateCountTopic();
            $this->Cache_Clean();

            $this->Message_AddNotice($this->Lang_Get('action.admin.topics_recalculated'), $this->Lang_Get('attention'));
        } else {
            $this->Viewer_Assign('sMessage', $this->Lang_Get('action.admin.recalctopics_message'));
            $this->Viewer_Assign('bActionEnable', true);
        }
    }

    /**
     * Пересчет рейтинга блогов
     */
    protected function EventRecalculateBlogRating() {

        $this->_setTitle($this->Lang_Get('action.admin.recalcblograting_title'));
        $this->SetTemplateAction('tools/recalcblograting');
        if (F::isPost('recalcblograting_submit')) {
            $this->Security_ValidateSendForm();
            set_time_limit(0);
            $this->Rating_RecalculateBlogRating();
            $this->Cache_Clean();

            $this->Message_AddNotice($this->Lang_Get('action.admin.blograting_recalculated'), $this->Lang_Get('attention'));
        } else {
            $this->Viewer_Assign('sMessage', $this->Lang_Get('action.admin.recalcblograting_message'));
            $this->Viewer_Assign('bActionEnable', true);
        }
    }

    /**
     * Контроль БД
     */
    protected function EventCheckDb() {

        $this->_setTitle($this->Lang_Get('action.admin.checkdb_title'));
        $this->SetTemplateAction('tools/checkdb');

        $sMode = $this->getParam(0, 'db');
        if ($sMode == 'blogs') {
            $this->_eventCheckDbBlogs();
        } elseif ($sMode == 'topics') {
            $this->_eventCheckDbTopics();
        }
        $this->Viewer_Assign('sMode', $sMode);
    }

    protected function _eventCheckDbBlogs() {

        $this->SetTemplateAction('tools/checkdb_blogs');
        $sDoAction = F::GetRequest('do_action');
        if ($sDoAction == 'clear_blogs_joined') {
            $aJoinedBlogs = $this->Admin_GetUnlinkedBlogsForUsers();
            if ($aJoinedBlogs) {
                $this->Admin_DelUnlinkedBlogsForUsers(array_keys($aJoinedBlogs));
            }
        } elseif ($sDoAction == 'clear_blogs_co') {
            $aCommentsOnlineBlogs = $this->Admin_GetUnlinkedBlogsForCommentsOnline();
            if ($aCommentsOnlineBlogs) {
                $this->Admin_DelUnlinkedBlogsForCommentsOnline(array_keys($aCommentsOnlineBlogs));
            }
        }
        $aJoinedBlogs = $this->Admin_GetUnlinkedBlogsForUsers();
        $aCommentsOnlineBlogs = $this->Admin_GetUnlinkedBlogsForCommentsOnline();
        $this->Viewer_Assign('aJoinedBlogs', $aJoinedBlogs);
        $this->Viewer_Assign('aCommentsOnlineBlogs', $aCommentsOnlineBlogs);
    }

    protected function _eventCheckDbTopics() {

        $this->SetTemplateAction('tools/checkdb_topics');
        $sDoAction = F::GetRequest('do_action');
        if ($sDoAction == 'clear_topics_co') {
            $aCommentsOnlineBlogs = $this->Admin_GetUnlinkedTopicsForCommentsOnline();
            if ($aCommentsOnlineBlogs) {
                $this->Admin_DelUnlinkedTopicsForCommentsOnline(array_keys($aCommentsOnlineBlogs));
            }
        }
        $aCommentsOnlineTopics = $this->Admin_GetUnlinkedTopicsForCommentsOnline();
        $this->Viewer_Assign('aCommentsOnlineTopics', $aCommentsOnlineTopics);
    }

    /**********************************************************************************/

    /**
     *
     */
    protected function EventLang() {

        $aLanguages = $this->Lang_GetAvailableLanguages();
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
            Router::Location('admin/settings-lang/');
        }

        $this->_setTitle($this->Lang_Get('action.admin.set_title_lang'));
        $this->SetTemplateAction('settings/lang');

        $this->Viewer_Assign('aLanguages', $aLanguages);
        $this->Viewer_Assign('aLangAllow', $aLangAllow);
    }

    /**********************************************************************************/

    /**
     * Типы блогов
     */
    protected function EventBlogTypes() {

        $sMode = $this->getParam(0);
        $this->Viewer_Assign('sMode', $sMode);

        if ($sMode == 'add') {
            return $this->_eventBlogTypesAdd();
        } elseif ($sMode == 'edit') {
            return $this->_eventBlogTypesEdit();
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

        $this->_setTitle($this->Lang_Get('action.admin.blogtypes_menu'));
        $this->SetTemplateAction('settings/blogtypes');

        $aBlogTypes = $this->Blog_GetBlogTypes();
        $aLangList = $this->Lang_GetLangList();

        $this->Viewer_Assign('aBlogTypes', $aBlogTypes);
        $this->Viewer_Assign('aLangList', $aLangList);
    }

    /**
     *
     */
    protected function _eventBlogTypesAdd() {

        $this->_setTitle($this->Lang_Get('action.admin.blogtypes_menu'));
        $this->SetTemplateAction('settings/blogtypes_edit');

        $aLangList = $this->Lang_GetLangList();
        $this->Viewer_Assign('aLangList', $aLangList);

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
        $aContentTypes = $this->Topic_GetContentTypes($aFilter, false);
        $this->Viewer_Assign('aContentTypes', $aContentTypes);
    }

    /**
     *
     */
    protected function _eventBlogTypesEdit() {

        $this->_setTitle($this->Lang_Get('action.admin.blogtypes_menu'));
        $this->SetTemplateAction('settings/blogtypes_edit');

        $nBlogTypeId = intval($this->getParam(1));
        if ($nBlogTypeId) {
            $oBlogType = $this->Blog_GetBlogTypeById($nBlogTypeId);

            $aLangList = $this->Lang_GetLangList();
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

                $_REQUEST['blogtypes_contenttype'] = $oBlogType->GetContentType();
            }
            $this->Viewer_Assign('oBlogType', $oBlogType);
            $this->Viewer_Assign('aLangList', $aLangList);
            $aFilter = array('content_active' => 1);
            $aContentTypes = $this->Topic_GetContentTypes($aFilter, false);
            $this->Viewer_Assign('aContentTypes', $aContentTypes);
        }
    }

    /**
     *
     */
    protected function _eventBlogTypesEditSubmit() {

        $nBlogTypeId = intval($this->getParam(1));
        if ($nBlogTypeId) {
            $oBlogType = $this->Blog_GetBlogTypeById($nBlogTypeId);
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
                $oBlogType->SetContentType($this->GetPost('blogtypes_contenttype'));

                // Установка прав на запись
                $nAclValue = intval($this->GetPost('blogtypes_acl_write'));
                $oBlogType->SetAclWrite($nAclValue);

                // Установка прав на чтение
                $nAclValue = intval($this->GetPost('blogtypes_acl_read'));
                $oBlogType->SetAclRead($nAclValue);

                // Установка прав на комментирование
                $nAclValue = intval($this->GetPost('blogtypes_acl_comment'));
                $oBlogType->SetAclComment($nAclValue);

                $this->Hook_Run('blogtype_edit_validate_before', array('oBlogType' => $oBlogType));
                if ($oBlogType->_Validate()) {
                    if ($this->_updateBlogType($oBlogType)) {
                        Router::Location('admin/settings-blogtypes');
                    }
                } else {
                    $this->Message_AddError($oBlogType->_getValidateError(), $this->Lang_Get('error'));
                }
            } else {
                $this->Message_AddError($this->Lang_Get('action.admin.blogtypes_err_id_notfound'), $this->Lang_Get('error'));
            }
        }
        $this->Viewer_Assign('oBlogType', $oBlogType);
    }

    /**
     *
     */
    protected function _eventBlogTypesAddSubmit() {

        $oBlogType = Engine::GetEntity('Blog_BlogType');
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
        $oBlogType->SetContentType($this->GetPost('blogtypes_contenttype'));

        // Установка прав на запись
        $nAclValue = intval($this->GetPost('blogtypes_acl_write'));
        $oBlogType->SetAclWrite($nAclValue);

        // Установка прав на чтение
        $nAclValue = intval($this->GetPost('blogtypes_acl_read'));
        $oBlogType->SetAclRead($nAclValue);

        // Установка прав на комментирование
        $nAclValue = intval($this->GetPost('blogtypes_acl_comment'));
        $oBlogType->SetAclComment($nAclValue);

        $this->Hook_Run('blogtype_add_validate_before', array('oBlogType' => $oBlogType));
        if ($oBlogType->_Validate()) {
            if ($this->_addBlogType($oBlogType)) {
                Router::Location('admin/settings-blogtypes');
            }
        } else {
            $this->Message_AddError($oBlogType->_getValidateError(), $this->Lang_Get('error'));
            $this->Viewer_Assign('aFormErrors', $oBlogType->_getValidateErrors());
        }
    }

    /**
     * @param $oBlogType
     *
     * @return bool
     */
    protected function _addBlogType($oBlogType) {

        return $this->Blog_AddBlogType($oBlogType);
    }

    /**
     * @param $oBlogType
     *
     * @return bool
     */
    protected function _updateBlogType($oBlogType) {

        return $this->Blog_UpdateBlogType($oBlogType);
    }

    /**
     * @param $nVal
     */
    protected function _eventBlogTypeSetActive($nVal) {

        $aBlogTypes = $this->GetPost('blogtype_sel');
        if (is_array($aBlogTypes) && count($aBlogTypes)) {
            $aBlogTypes = array_keys($aBlogTypes);
            foreach ($aBlogTypes as $nBlogTypeId) {
                $oBlogType = $this->Blog_GetBlogTypeById($nBlogTypeId);
                if ($oBlogType) {
                    $oBlogType->SetActive($nVal);
                    $this->Blog_UpdateBlogType($oBlogType);
                }
            }
        }
        Router::Location('admin/settings-blogtypes');
    }

    /**********************************************************************************/

    /**
     * Права пользователей
     */
    protected function EventUserRights() {

        $this->_setTitle($this->Lang_Get('action.admin.userrights_menu'));
        $this->SetTemplateAction('settings/userrights');

        if ($this->IsPost('submit_type_add')) {
            return $this->_eventUserRightsEditSubmit();
        } else {
            $_REQUEST['userrights_administrator'] = $this->ACL_GetUserRights('blogs', 'administrator');
            $_REQUEST['userrights_moderator'] = $this->ACL_GetUserRights('blogs', 'moderator');
        }
    }

    protected function _eventUserRightsEditSubmit() {

        $aAdmin = $this->GetPost('userrights_administrator');
        $aModer = $this->GetPost('userrights_moderator');
        $aConfig = array(
            'rights' => array(
                'blogs' => array(
                    'administrator' => array(
                        'control_users' => (isset($aAdmin['control_users']) && $aAdmin['control_users']) ? true : false,
                        'edit_blog' => (isset($aAdmin['edit_blog']) && $aAdmin['edit_blog']) ? true : false,
                        'edit_content'   => (isset($aAdmin['edit_content']) && $aAdmin['edit_content']) ? true : false,
                        'delete_content'    => (isset($aAdmin['delete_content']) && $aAdmin['delete_content']) ? true : false,
                        'edit_comment'   => (isset($aAdmin['edit_comment']) && $aAdmin['edit_comment']) ? true : false,
                        'delete_comment'    => (isset($aAdmin['delete_comment']) && $aAdmin['delete_comment']) ? true : false,
                    ),
                    'moderator'     => array(
                        'control_users' => (isset($aModer['control_users']) && $aModer['control_users']) ? true : false,
                        'edit_blog' => (isset($aAdmin['edit_blog']) && $aAdmin['edit_blog']) ? true : false,
                        'edit_content'   => (isset($aModer['edit_content']) && $aModer['edit_content']) ? true : false,
                        'delete_content'    => (isset($aModer['delete_content']) && $aModer['delete_content']) ? true : false,
                        'edit_comment'   => (isset($aModer['edit_comment']) && $aModer['edit_comment']) ? true : false,
                        'delete_comment'    => (isset($aModer['delete_comment']) && $aModer['delete_comment']) ? true : false,
                    ),
                ),
            ),
        );
        Config::WriteCustomConfig($aConfig);
    }

    /**********************************************************************************/

    /**
     * Управление полями пользователя
     *
     */
    protected function EventUserFields() {

        switch (F::GetRequestStr('action')) {
            // * Создание нового поля
            case 'add':
                // * Обрабатываем как ajax запрос (json)
                $this->Viewer_SetResponseAjax('json');
                if (!$this->checkUserField()) {
                    return;
                }
                $oField = Engine::GetEntity('User_Field');
                $oField->setName(F::GetRequestStr('name'));
                $oField->setTitle(F::GetRequestStr('title'));
                $oField->setPattern(F::GetRequestStr('pattern'));
                if (in_array(F::GetRequestStr('type'), $this->User_GetUserFieldTypes())) {
                    $oField->setType(F::GetRequestStr('type'));
                } else {
                    $oField->setType('');
                }

                $iId = $this->User_AddUserField($oField);
                if (!$iId) {
                    $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
                    return;
                }
                // * Прогружаем переменные в ajax ответ
                $this->Viewer_AssignAjax('id', $iId);
                $this->Viewer_AssignAjax('lang_delete', $this->Lang_Get('user_field_delete'));
                $this->Viewer_AssignAjax('lang_edit', $this->Lang_Get('user_field_update'));
                $this->Message_AddNotice($this->Lang_Get('user_field_added'), $this->Lang_Get('attention'));
                break;

            // * Удаление поля
            case 'delete':
                // * Обрабатываем как ajax запрос (json)
                $this->Viewer_SetResponseAjax('json');
                if (!F::GetRequestStr('id')) {
                    $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
                    return;
                }
                $this->User_DeleteUserField(F::GetRequestStr('id'));
                $this->Message_AddNotice($this->Lang_Get('user_field_deleted'), $this->Lang_Get('attention'));
                break;

            // * Изменение поля
            case 'update':
                // * Обрабатываем как ajax запрос (json)
                $this->Viewer_SetResponseAjax('json');
                if (!F::GetRequestStr('id')) {
                    $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
                    return;
                }
                if (!$this->User_UserFieldExistsById(F::GetRequestStr('id'))) {
                    $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
                    return false;
                }
                if (!$this->checkUserField()) {
                    return;
                }
                $oField = Engine::GetEntity('User_Field');
                $oField->setId(F::GetRequestStr('id'));
                $oField->setName(F::GetRequestStr('name'));
                $oField->setTitle(F::GetRequestStr('title'));
                $oField->setPattern(F::GetRequestStr('pattern'));
                if (in_array(F::GetRequestStr('type'), $this->User_GetUserFieldTypes())) {
                    $oField->setType(F::GetRequestStr('type'));
                } else {
                    $oField->setType('');
                }
                if (!$this->User_UpdateUserField($oField)) {
                    $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
                    return;
                }
                $this->Message_AddNotice($this->Lang_Get('user_field_updated'), $this->Lang_Get('attention'));
                break;

            // * Показываем страницу со списком полей
            default:
                // * Загружаем в шаблон JS текстовки
                $this->Lang_AddLangJs(array(
                    'action.admin.user_field_delete_confirm_title',
                    'action.admin.user_field_delete_confirm_text',
                    'action.admin.user_field_admin_title_add',
                    'action.admin.user_field_admin_title_edit',
                    'action.admin.user_field_add',
                    'action.admin.user_field_update',
                ));

                // * Получаем список всех полей
                $this->Viewer_Assign('aUserFields', $this->User_GetUserFields());
                $this->Viewer_Assign('aUserFieldTypes', $this->User_GetUserFieldTypes());
                $this->_setTitle($this->Lang_Get('action.admin.user_fields_title'));
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
            $this->Message_AddError($this->Lang_Get('user_field_error_add_no_title'), $this->Lang_Get('error'));
            return false;
        }
        if (!F::GetRequestStr('name')) {
            $this->Message_AddError($this->Lang_Get('user_field_error_add_no_name'), $this->Lang_Get('error'));
            return false;
        }
        /**
         * Не допускаем дубликатов по имени
         */
        if ($this->User_UserFieldExistsByName(F::GetRequestStr('name'), F::GetRequestStr('id'))) {
            $this->Message_AddError($this->Lang_Get('user_field_error_name_exists'), $this->Lang_Get('error'));
            return false;
        }
        return true;
    }

    /**********************************************************************************/

    protected function EventContentTypes() {

        $this->_setTitle($this->Lang_Get('action.admin.contenttypes_menu'));
        $this->SetTemplateAction('settings/contenttypes');

        // * Получаем список
        $aFilter = array();
        $aTypes = $this->Topic_GetContentTypes($aFilter, false);
        $this->Viewer_Assign('aTypes', $aTypes);

        // * Выключатель
        if (F::GetRequest('toggle') && F::CheckVal(F::GetRequest('content_id'), 'id', 1, 10) && in_array(F::GetRequest('toggle'), array('on', 'off'))) {
            $this->Security_ValidateSendForm();
            if ($oTypeTog = $this->Topic_GetContentTypeById(F::GetRequest('content_id'))) {
                $iToggle = 1;
                if (F::GetRequest('toggle') == 'off') {
                    $iToggle = 0;
                }
                $oTypeTog->setContentActive($iToggle);
                $this->Topic_UpdateContentType($oTypeTog);

                Router::Location('admin/settings-contenttypes/');
            }
        }

        if (F::GetRequest('add')) {
            $this->Message_AddNoticeSingle($this->Lang_Get('action.admin.contenttypes_success'));
        }

        if (F::GetRequest('edit')) {
            $this->Message_AddNoticeSingle($this->Lang_Get('action.admin.contenttypes_success_edit'));
        }

    }

    protected function EventContentTypesAdd() {

        $this->_setTitle($this->Lang_Get('action.admin.contenttypes_add_title'));
        $this->SetTemplateAction('settings/contenttypes_edit');

        // * Вызов хуков
        $this->Hook_Run('topic_type_add_show');

        // * Загружаем переменные в шаблон
        $this->Viewer_AddHtmlTitle($this->Lang_Get('action.admin.contenttypes_add_title'));

        // * Обрабатываем отправку формы
        return $this->SubmitContentTypesAdd();

    }

    protected function SubmitContentTypesAdd() {

        // * Проверяем отправлена ли форма с данными
        if (!F::isPost('submit_type_add')) {
            return false;
        }

        // * Проверка корректности полей формы
        if (!$this->CheckContentFields()) {
            return false;
        }

        $oContentType = Engine::GetEntity('Topic_ContentType');
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

        if ($this->Topic_AddContentType($oContentType)) {
            Router::Location('admin/settings-contenttypes/?add=success');
        }

    }

    protected function EventContentTypesEdit() {

        // * Получаем тип
        if (!$oContentType = $this->Topic_GetContentTypeById($this->GetParam(0))) {
            return parent::EventNotFound();
        }
        $this->Viewer_Assign('oContentType', $oContentType);

        // * Устанавливаем шаблон вывода
        $this->_setTitle($this->Lang_Get('action.admin.contenttypes_edit_title'));
        $this->SetTemplateAction('settings/contenttypes_edit');

        if (F::GetRequest('fieldadd')) {
            $this->Message_AddNoticeSingle($this->Lang_Get('action.admin.contenttypes_success_fieldadd'));
        }
        if (F::GetRequest('fieldedit')) {
            $this->Message_AddNoticeSingle($this->Lang_Get('action.admin.contenttypes_success_fieldedit'));
        }
        if (F::GetRequest('fielddelete')) {
            $this->Message_AddNoticeSingle($this->Lang_Get('action.admin.contenttypes_success_fielddelete'));
        }

        // * Проверяем отправлена ли форма с данными
        if (isset($_REQUEST['submit_type_add'])) {

            // * Обрабатываем отправку формы
            return $this->SubmitContentTypesEdit($oContentType);
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

    }

    protected function SubmitContentTypesEdit($oContentType) {

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

        if ($this->Topic_UpdateContentType($oContentType)) {

            if ($oContentType->getContentUrl() != $sTypeOld) {

                //меняем у уже созданных топиков системный тип
                $this->Topic_ChangeType($sTypeOld, $oContentType->getContentUrl());
            }

            Router::Location('admin/settings-contenttypes/?edit=success');
        }
    }

    public function EventAjaxChangeOrderTypes() {

        // * Устанавливаем формат ответа
        $this->Viewer_SetResponseAjax('json');

        if (!$this->User_IsAuthorization()) {
            $this->Message_AddErrorSingle($this->Lang_Get('need_authorization'), $this->Lang_Get('error'));
            return;
        }
        if (!$this->oUserCurrent->isAdministrator()) {
            $this->Message_AddErrorSingle($this->Lang_Get('need_authorization'), $this->Lang_Get('error'));
            return;
        }
        if (!F::GetRequest('order')) {
            $this->Message_AddErrorSingle($this->Lang_Get('system_error'), $this->Lang_Get('error'));
            return;
        }


        if (is_array(F::GetRequest('order'))) {

            foreach (F::GetRequest('order') as $oOrder) {
                if (is_numeric($oOrder['order']) && is_numeric($oOrder['id']) && $oContentType = $this->Topic_GetContentTypeById($oOrder['id'])) {
                    $oContentType->setContentSort($oOrder['order']);
                    $this->Topic_UpdateContentType($oContentType);
                }
            }

            $this->Message_AddNoticeSingle($this->Lang_Get('action.admin.save_sort_success'));
            return;
        } else {
            $this->Message_AddErrorSingle($this->Lang_Get('system_error'), $this->Lang_Get('error'));
            return;
        }

    }

    public function EventAjaxChangeOrderFields() {

        // * Устанавливаем формат ответа
        $this->Viewer_SetResponseAjax('json');

        if (!$this->User_IsAuthorization()) {
            $this->Message_AddErrorSingle($this->Lang_Get('need_authorization'), $this->Lang_Get('error'));
            return;
        }
        if (!$this->oUserCurrent->isAdministrator()) {
            $this->Message_AddErrorSingle($this->Lang_Get('need_authorization'), $this->Lang_Get('error'));
            return;
        }
        if (!F::GetRequest('order')) {
            $this->Message_AddErrorSingle($this->Lang_Get('system_error'), $this->Lang_Get('error'));
            return;
        }


        if (is_array(F::GetRequest('order'))) {

            foreach (F::GetRequest('order') as $oOrder) {
                if (is_numeric($oOrder['order']) && is_numeric($oOrder['id']) && $oField = $this->Topic_GetContentFieldById($oOrder['id'])) {
                    $oField->setFieldSort($oOrder['order']);
                    $this->Topic_UpdateContentField($oField);
                }
            }

            $this->Message_AddNoticeSingle($this->Lang_Get('action.admin.save_sort_success'));
            return;
        } else {
            $this->Message_AddErrorSingle($this->Lang_Get('system_error'), $this->Lang_Get('error'));
            return;
        }

    }

    /***********************
     ****** Поля ***********
     **********************/
    protected function EventAddField() {

        $this->_setTitle($this->Lang_Get('action.admin.contenttypes_add_field_title'));

        // * Получаем тип
        if (!$oContentType = $this->Topic_GetContentTypeById($this->GetParam(0))) {
            return parent::EventNotFound();
        }

        $this->Viewer_Assign('oContentType', $oContentType);

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

        $oField = Engine::GetEntity('Topic_Field');
        $oField->setFieldType(F::GetRequest('field_type'));
        $oField->setContentId($oContentType->getContentId());
        $oField->setFieldName(F::GetRequest('field_name'));
        $oField->setFieldDescription(F::GetRequest('field_description'));
        $oField->setFieldRequired(F::GetRequest('field_required'));
        if (F::GetRequest('field_type') == 'select') {
            $oField->setOptionValue('select', F::GetRequest('field_values'));
        }

        if ($this->Topic_AddContentField($oField)) {
            Router::Location('admin/settings-contenttypesedit/' . $oContentType->getContentId() . '/?fieldadd=success');
        }

    }

    protected function EventEditField() {

        $this->Viewer_AddHtmlTitle($this->Lang_Get('action.admin.contenttypes_edit_field_title'));

        // * Получаем поле
        if (!$oField = $this->Topic_GetContentFieldById($this->GetParam(0))) {
            return parent::EventNotFound();
        }

        $this->Viewer_Assign('oField', $oField);

        // * Получаем тип
        if (!$oContentType = $this->Topic_GetContentTypeById($oField->getContentId())) {
            return parent::EventNotFound();
        }

        $this->Viewer_Assign('oContentType', $oContentType);

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

    protected function SubmitEditField($oContentType, $oField) {

        // * Проверяем отправлена ли форма с данными
        if (!F::isPost('submit_field')) {
            return false;
        }

        // * Проверка корректности полей формы
        if (!$this->CheckFieldsField($oContentType)) {
            return false;
        }

        $oField->setFieldName(F::GetRequest('field_name'));
        $oField->setFieldDescription(F::GetRequest('field_description'));
        $oField->setFieldRequired(F::GetRequest('field_required'));
        if ($oField->getFieldType() == 'select') {
            $oField->setOptionValue('select', F::GetRequest('field_values'));
        }

        if ($this->Topic_UpdateContentField($oField)) {
            Router::Location('admin/settings-contenttypesedit/' . $oContentType->getContentId() . '/?fieldedit=success');
        }


    }

    protected function EventDeleteField() {

        $this->Security_ValidateSendForm();
        if (!$oField = $this->Topic_GetContentFieldById($this->GetParam(0))) {
            return parent::EventNotFound();
        }
        if (!$oContentType = $this->Topic_GetContentTypeById($oField->getContentId())) {
            return parent::EventNotFound();
        }

        $this->Topic_DeleteField($oField);
        Router::Location('admin/settings-contenttypesedit/' . $oContentType->getContentId() . '/?fielddelete=success');
    }


    /*************************************************************
     *
     */
    protected function CheckContentFields() {

        $this->Security_ValidateSendForm();

        $bOk = true;

        if (!F::CheckVal(F::GetRequest('content_title', null, 'post'), 'text', 2, 200)) {
            $this->Message_AddError($this->Lang_Get('action.admin.contenttypes_type_title_error'), $this->Lang_Get('error'));
            $bOk = false;
        }

        if (!F::CheckVal(F::GetRequest('content_title_decl', null, 'post'), 'text', 2, 200)) {
            $this->Message_AddError($this->Lang_Get('action.admin.contenttypes_type_title_decl_error'), $this->Lang_Get('error'));
            $bOk = false;
        }
        if (!F::CheckVal(F::GetRequest('content_url', null, 'post'), 'login', 2, 50) || in_array(F::GetRequest('content_url', null, 'post'), array_keys(Config::Get('router.page')))) {
            $this->Message_AddError($this->Lang_Get('action.admin.contenttypes_type_url_error'), $this->Lang_Get('error'));
            $bOk = false;
        }

        if (!in_array(F::GetRequest('content_access'), array('1', '2', '4'))) {
            $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
            $bOk = false;
        }

        return $bOk;
    }

    protected function CheckFieldsField($oContentType = null) {

        $this->Security_ValidateSendForm();

        $bOk = true;

        if (!F::CheckVal(F::GetRequest('field_name', null, 'post'), 'text', 2, 100)) {
            $this->Message_AddError($this->Lang_Get('field_name_error'), $this->Lang_Get('error'));
            $bOk = false;
        }

        if (!F::CheckVal(F::GetRequest('field_description', null, 'post'), 'text', 2, 200)) {
            $this->Message_AddError($this->Lang_Get('field_description_error'), $this->Lang_Get('error'));
            $bOk = false;
        }

        if (Router::GetActionEvent() == 'fieldadd') {
            if ($oContentType == 'photoset' && (F::GetRequest('field_type', null, 'post') == 'photoset' || $oContentType->isPhotosetEnable())) {
                $this->Message_AddError($this->Lang_Get('system_error'), $this->Lang_Get('error'));
                $bOk = false;
            }

            if (!in_array(F::GetRequest('field_type', null, 'post'), $this->Topic_GetAvailableFieldTypes())) {
                $this->Message_AddError($this->Lang_Get('field_type_error'), $this->Lang_Get('error'));
                $bOk = false;
            }
        }

        // * Выполнение хуков
        $this->Hook_Run('check_admin_content_fields', array('bOk' => &$bOk));

        return $bOk;
    }

    /**
     * Голосование админа
     */
    public function EventAjaxVote() {

        // * Устанавливаем формат ответа
        $this->Viewer_SetResponseAjax('json');

        if (!E::IsAdmin()) {
            $this->Message_AddErrorSingle($this->Lang_Get('need_authorization'), $this->Lang_Get('error'));
            return;
        }

        $nUserId = $this->GetPost('idUser');
        if (!$nUserId || !($oUser = $this->User_GetUserById($nUserId))) {
            $this->Message_AddErrorSingle($this->Lang_Get('user_not_found'), $this->Lang_Get('error'));
            return;
        }

        $nValue = $this->GetPost('value');

        if (!($oUserVote = $this->Vote_GetVote($oUser->getId(), 'user', $this->oUserCurrent->getId()))) {
            // первичное голосование
            $oUserVote = Engine::GetEntity('Vote');
            $oUserVote->setTargetId($oUser->getId());
            $oUserVote->setTargetType('user');
            $oUserVote->setVoterId($this->oUserCurrent->getId());
            $oUserVote->setDirection($nValue);
            $oUserVote->setDate(F::Now());
            $iVal = (float)$this->Rating_VoteUser($this->oUserCurrent, $oUser, $nValue);
            $oUserVote->setValue($iVal);
            $oUser->setCountVote($oUser->getCountVote() + 1);
            if ($this->Vote_AddVote($oUserVote) && $this->User_Update($oUser)) {
                $this->Viewer_AssignAjax('iRating', $oUser->getRating());
                $this->Viewer_AssignAjax('iSkill', $oUser->getSkill());
                $this->Viewer_AssignAjax('iCountVote', $oUser->getCountVote());

                // * Добавляем событие в ленту
                //$this->Stream_Write($oUserVote->getVoterId(), 'vote_user', $oUser->getId());
                $this->Message_AddNoticeSingle($this->Lang_Get('user_vote_ok'), $this->Lang_Get('attention'));
            } else {
                $this->Message_AddErrorSingle($this->Lang_Get('action.admin.vote_error'), $this->Lang_Get('error'));
            }
        } else {
            // * Повторное голосование админа
            $iNewValue = $oUserVote->getValue() + $nValue;
            $oUserVote->setDirection($iNewValue);
            $oUserVote->setDate(F::Now());
            $iVal = (float)$this->Rating_VoteUser($this->oUserCurrent, $oUser, $nValue);
            $oUserVote->setValue($oUserVote->getValue() + $iVal);
            $oUser->setCountVote($oUser->getCountVote() + 1);
            if ($this->Vote_Update($oUserVote) && $this->User_Update($oUser)) {
                $this->Viewer_AssignAjax('iRating', $oUser->getRating());
                $this->Viewer_AssignAjax('iSkill', $oUser->getSkill());
                $this->Viewer_AssignAjax('iCountVote', $oUser->getCountVote());
                $this->Message_AddNoticeSingle($this->Lang_Get('user_vote_ok'), $this->Lang_Get('attention'));
            } else {
                $this->Message_AddErrorSingle($this->Lang_Get('action.admin.repeat_vote_error'), $this->Lang_Get('error'));
            }
        }
    }

    public function EventAjaxSetProfile() {

        // * Устанавливаем формат ответа
        $this->Viewer_SetResponseAjax('json');

        if (!E::IsAdmin()) {
            $this->Message_AddErrorSingle($this->Lang_Get('need_authorization'), $this->Lang_Get('error'));
            return;
        }

        $nUserId = intval($this->GetPost('user_id'));
        if ($nUserId && ($oUser = $this->User_GetUserById($nUserId))) {
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

            if ($this->User_Update($oUser) !== false) {
                $this->Message_AddNoticeSingle($this->Lang_Get('action.admin.saved_ok'));
            } else {
                $this->Message_AddErrorSingle($this->Lang_Get('action.admin.saved_err'));
            }
        } else {
            $this->Message_AddErrorSingle($this->Lang_Get('user_not_found'), $this->Lang_Get('error'));
        }
    }

    public function EventAjaxConfig() {

        $this->Viewer_SetResponseAjax('json');

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

    /**
     * Выполняется при завершении работы экшена
     *
     */
    public function EventShutdown() {

        // * Загружаем в шаблон необходимые переменные
        $this->Viewer_Assign('sMenuItem', $this->sMenuItem);
        $this->Lang_AddLangJs(array('action.admin.form_choose_file', 'action.admin.form_no_file_selected'));
    }

    protected function _setTitle($sTitle) {

        $this->Viewer_Assign('sPageTitle', $sTitle);
        $this->Viewer_AddHtmlTitle($sTitle);

    }

}

// EOF
