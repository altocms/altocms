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
 * Модуль системных сообщений
 * Позволяет показывать пользователю сообщения двух видов - об ошибке и об успешном действии.
 * <pre>
 * E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->get('not_access'),E::ModuleLang()->get('error'));
 * </pre>
 *
 * @package engine.modules
 * @since 1.0
 */
class ModuleMessage extends Module {

    /**
     * An array of error messages
     *
     * @var array
     */
    protected $aMsgError = array();

    /**
     * An array of notice messages
     *
     * @var array
     */
    protected $aMsgNotice = array();

    /**
     * An array of notice messages that will be displayed when the next page is loaded
     *
     * @var array
     */
    protected $aMsgNoticeSession = array();

    /**
     * An array of error messages that will be displayed when the next page is loaded
     *
     * @var array
     */
    protected $aMsgErrorSession = array();

    /**
     * Module initialization
     *
     */
    public function init() {

        if (!$this->isInit()) {
            // Load messages from session
            $aNoticeSession = E::ModuleSession()->GetClear('message_notice_session');
            if (is_array($aNoticeSession) && count($aNoticeSession)) {
                $this->aMsgNotice = $aNoticeSession;
            }
            $aErrorSession = E::ModuleSession()->GetClear('message_error_session');
            if (is_array($aErrorSession) && count($aErrorSession)) {
                $this->aMsgError = $aErrorSession;
            }
        }
    }

    /**
     * Assign messages to template variables and save special messages to session
     * (they will be shown in the next page)
     *
     */
    public function shutdown() {

        // Save messages in session
        if ($aMessages = $this->getNoticeSession()) {
            E::ModuleSession()->Set('message_notice_session', $aMessages);
        }
        if ($aMessages = $this->getErrorSession()) {
            E::ModuleSession()->Set('message_error_session', $aMessages);
        }

        E::ModuleViewer()->assign('aMsgNotice', $this->getNotice());
        E::ModuleViewer()->assign('aMsgError', $this->getError());
    }

    /**
     * Add new error message
     *
     * @param string $sMsg        Message
     * @param string $sTitle      Title
     * @param bool   $bUseSession Save message in the session
     */
    public function addError($sMsg, $sTitle = null, $bUseSession = false) {

        if (!$bUseSession) {
            $this->aMsgError[] = array('msg' => $sMsg, 'title' => $sTitle);
        } else {
            $this->aMsgErrorSession[] = array('msg' => $sMsg, 'title' => $sTitle);
        }
    }

    /**
     * Add a single error message (and clear all previous errors)
     *
     * @param string $sMsg        Message
     * @param string $sTitle      Title
     * @param bool   $bUseSession Save message in the session
     */
    public function addErrorSingle($sMsg, $sTitle = null, $bUseSession = false) {

        $this->clearError();
        $this->addError($sMsg, $sTitle, $bUseSession);
    }

    /**
     * Add new notice message
     *
     * @param string $sMsg        Message
     * @param string $sTitle      Title
     * @param bool   $bUseSession Save message in the session
     */
    public function addNotice($sMsg, $sTitle = null, $bUseSession = false) {

        if (!$bUseSession) {
            $this->aMsgNotice[] = array('msg' => $sMsg, 'title' => $sTitle);
        } else {
            $this->aMsgNoticeSession[] = array('msg' => $sMsg, 'title' => $sTitle);
        }
    }

    /**
     * Add a single notice message (and clear all previous notices)
     *
     * @param string $sMsg        Message
     * @param string $sTitle      Title
     * @param bool   $bUseSession Save message in the session
     */
    public function addNoticeSingle($sMsg, $sTitle = null, $bUseSession = false) {

        $this->clearNotice();
        $this->addNotice($sMsg, $sTitle, $bUseSession);
    }

    /**
     * Clear an array of error messages
     *
     */
    public function clearError() {

        $this->aMsgError = array();
        $this->aMsgErrorSession = array();
    }

    /**
     * Clear an array of notice messages
     *
     */
    public function clearNotice() {

        $this->aMsgNotice = array();
        $this->aMsgNoticeSession = array();
    }

    /**
     * Return an array of error messages
     *
     * @return array
     */
    public function getError() {

        return $this->aMsgError;
    }

    /**
     * Return an array of notice messages
     *
     * @return array
     */
    public function getNotice() {

        return $this->aMsgNotice;
    }

    /**
     * Return an array of error messages to be saved in the session
     *
     * @return array
     */
    public function getErrorSession() {

        return $this->aMsgErrorSession;
    }
    /**
     * Return an array of notice messages to be saved in the session
     *
     * @return array
     */
    public function getNoticeSession() {

        return $this->aMsgNoticeSession;
    }

}

// EOF