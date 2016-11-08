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
 * E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('not_access'),E::ModuleLang()->Get('error'));
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
    public function Init() {

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
    public function Shutdown() {

        // Save messages in session
        if ($aMessages = $this->GetNoticeSession()) {
            E::ModuleSession()->Set('message_notice_session', $aMessages);
        }
        if ($aMessages = $this->GetErrorSession()) {
            E::ModuleSession()->Set('message_error_session', $aMessages);
        }

        E::ModuleViewer()->Assign('aMsgNotice', $this->GetNotice());
        E::ModuleViewer()->Assign('aMsgError', $this->GetError());
    }

    /**
     * Add new error message
     *
     * @param string $sMsg        Message
     * @param string $sTitle      Title
     * @param bool   $bUseSession Save message in the session
     */
    public function AddError($sMsg, $sTitle = null, $bUseSession = false) {

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
    public function AddErrorSingle($sMsg, $sTitle = null, $bUseSession = false) {

        $this->ClearError();
        $this->AddError($sMsg, $sTitle, $bUseSession);
    }

    /**
     * Add new notice message
     *
     * @param string $sMsg        Message
     * @param string $sTitle      Title
     * @param bool   $bUseSession Save message in the session
     */
    public function AddNotice($sMsg, $sTitle = null, $bUseSession = false) {

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
    public function AddNoticeSingle($sMsg, $sTitle = null, $bUseSession = false) {

        $this->ClearNotice();
        $this->AddNotice($sMsg, $sTitle, $bUseSession);
    }

    /**
     * Clear an array of error messages
     *
     */
    public function ClearError() {

        $this->aMsgError = array();
        $this->aMsgErrorSession = array();
    }

    /**
     * Clear an array of notice messages
     *
     */
    public function ClearNotice() {

        $this->aMsgNotice = array();
        $this->aMsgNoticeSession = array();
    }

    /**
     * Return an array of error messages
     *
     * @return array
     */
    public function GetError() {

        return $this->aMsgError;
    }

    /**
     * Return an array of notice messages
     *
     * @return array
     */
    public function GetNotice() {

        return $this->aMsgNotice;
    }

    /**
     * Return an array of error messages to be saved in the session
     *
     * @return array
     */
    public function GetErrorSession() {

        return $this->aMsgErrorSession;
    }
    /**
     * Return an array of notice messages to be saved in the session
     *
     * @return array
     */
    public function GetNoticeSession() {

        return $this->aMsgNoticeSession;
    }

}

// EOF