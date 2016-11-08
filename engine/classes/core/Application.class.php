<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__);

F::IncludeFile('../abstract/LsObject.class.php');
F::IncludeFile('Router.class.php');

/**
 * Application class of CMS
 *
 * @package engine
 * @since 1.1
 */
class Application extends LsObject {

    static protected $oInstance;

    protected $aParams = array();

    public function __construct() {

    }

    public function __destruct() {

        $this->Done();
    }

    /**
     * @return Application
     */
    static public function getInstance() {

        if (empty(static::$oInstance)) {
            static::$oInstance = new static();
        }
        return static::$oInstance;
    }

    /**
     * @param array $aParams
     *
     * @return Application
     */
    static public function Create($aParams = array()) {

        $oApp = static::getInstance();
        $oApp->Init($aParams);

        return $oApp;
    }

    /**
     * Init application
     *
     * @param array $aParams
     */
    public function Init($aParams = array()) {

        $this->aParams = $aParams;

        if (!defined('DEBUG')) {
            define('DEBUG', 0);
        }

        if (isset($_SERVER['SCRIPT_NAME']) && isset($_SERVER['REQUEST_URI']) && $_SERVER['SCRIPT_NAME'] == $_SERVER['REQUEST_URI']) {
            // для предотвращения зацикливания и ошибки 404
            $_SERVER['REQUEST_URI'] = '/';
        }

        if (is_file('./install/index.php') && !defined('ALTO_INSTALL') && (!isset($_SERVER['HTTP_APP_ENV']) || $_SERVER['HTTP_APP_ENV'] != 'test')) {
            if (isset($_SERVER['REDIRECT_URL'])) {
                $sUrl = trim($_SERVER['REDIRECT_URL'], '/');
            } else {
                $sUrl = F::UrlBase();
                if ($sPath = F::ParseUrl(null, 'path')) {
                    $sUrl .= $sPath;
                } else {
                    $sUrl .= '/';
                }
                $sUrl .= 'install';
            }
            if ($sUrl && $sUrl != 'install' && substr($sUrl, -8) != '/install') {
                // Cyclic redirection to .../install/
                die('URL ' . $sUrl . '/ doesn\'t work on your site. Alto CMS v.' . ALTO_VERSION . ' not installed yet');
            }
            // Try to redirect to .../install/
            F::HttpLocation($sUrl, true);
            exit;
        }
    }

    /**
     * Executes application
     */
    public function Exec() {

        R::getInstance()->Exec();
    }

    /**
     * @return array
     */
    public function GetParams() {

        return $this->aParams;
    }

    /**
     * Done application
     */
    public function Done() {

    }

}

// EOF