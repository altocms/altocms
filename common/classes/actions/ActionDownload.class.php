<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */
class ActionDownload extends Action {

    protected $oType = null;

    /**
     * Инициализация экшена
     */
    public function Init() {
        /**
         * Проверяем авторизован ли юзер
         */
        $this->oUserCurrent = $this->User_GetUserCurrent();
        $this->SetDefaultEvent('file');
    }

    /**
     * Регистрируем евенты
     */
    protected function RegisterEvent() {
        $this->AddEvent('file', 'EventDownloadFile');
    }


    public function EventDownloadFile() {

        $this->SetTemplate(false);

        $sTopicId = $this->GetParam(0);
        $sFieldId = $this->GetParam(1);

        $this->Security_ValidateSendForm();

        if (!($oTopic = $this->Topic_GetTopicById($sTopicId))) {
            return parent::EventNotFound();
        }

        if (!$this->oType = $this->Topic_getContentType($oTopic->getType())) {
            return parent::EventNotFound();
        }

        if (!($oField = $this->Topic_GetContentFieldById($sFieldId))) {
            return parent::EventNotFound();
        }

        if ($oField->getContentId() != $this->oType->getContentId()) {
            return parent::EventNotFound();
        }

        //получаем объект файла
        $oFile = $oTopic->getFile($oField->getFieldId());
        //получаем объект поля топика, содержащий данные о файле
        $oValue = $oTopic->getField($oField->getFieldId());

        if ($oFile && $oValue) {

            if (preg_match("/^(http:\/\/)/i", $oFile->getFileUrl())) {
                $sFullPath = $oFile->getFileUrl();
                Router::Location($sFullPath);
            } else {
                $sFullPath = Config::Get('path.root.server') . $oFile->getFileUrl();
            }

            $sFilename = $oFile->getFileName();

            /*
            * Обновляем данные
            */
            $aFileObj = array();
            $aFileObj['file_name'] = $oFile->getFileName();
            $aFileObj['file_url'] = $oFile->getFileUrl();
            $aFileObj['file_size'] = $oFile->getFileSize();
            $aFileObj['file_extension'] = $oFile->getFileExtension();
            $aFileObj['file_downloads'] = $oFile->getFileDownloads() + 1;
            $sText = serialize($aFileObj);
            $oValue->setValue($sText);
            $oValue->setValueSource($sText);

            //сохраняем
            $this->Topic_UpdateContentFieldValue($oValue);

            /*
            * Отдаем файл
            */
            header('Content-type: ' . $oFile->getFileExtension());
            header('Content-Disposition: attachment; filename="' . $sFilename . '"');
            //$this->Topic_readfileChunked($sFullPath);
            F::File_PrintChunked($sFullPath);


        } else {
            $this->Message_AddErrorSingle($this->Lang_Get('content_download_file_error'));
            return Router::Action('error');
        }

    }

}

// EOF
