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
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();
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

        E::ModuleSecurity()->ValidateSendForm();

        if (!($oTopic = E::ModuleTopic()->GetTopicById($sTopicId))) {
            return parent::EventNotFound();
        }

        if (!$this->oType = E::ModuleTopic()->GetContentType($oTopic->getType())) {
            return parent::EventNotFound();
        }

        if (!($oField = E::ModuleTopic()->GetContentFieldById($sFieldId))) {
            return parent::EventNotFound();
        }

        if ($oField->getContentId() != $this->oType->getContentId()) {
            return parent::EventNotFound();
        }

        //получаем объект файла
        $oFile = $oTopic->getFieldFile($oField->getFieldId());
        //получаем объект поля топика, содержащий данные о файле
        $oValue = $oTopic->getField($oField->getFieldId());

        if ($oFile && $oValue) {

            if (preg_match("/^(http:\/\/)/i", $oFile->getFileUrl())) {
                $sFullPath = $oFile->getFileUrl();
                R::Location($sFullPath);
            } else {
                $sFullPath = Config::Get('path.root.dir') . $oFile->getFileUrl();
            }

            $sFilename = $oFile->getFileName();

            /*
            * Обновляем данные
            */
            $aFileObj = [];
            $aFileObj['file_name'] = $oFile->getFileName();
            $aFileObj['file_url'] = $oFile->getFileUrl();
            $aFileObj['file_size'] = $oFile->getFileSize();
            $aFileObj['file_extension'] = $oFile->getFileExtension();
            $aFileObj['file_downloads'] = $oFile->getFileDownloads() + 1;
            $sText = serialize($aFileObj);
            $oValue->setValue($sText);
            $oValue->setValueSource($sText);

            //сохраняем
            E::ModuleTopic()->UpdateContentFieldValue($oValue);

            /*
            * Отдаем файл
            */
            header('Content-type: ' . $oFile->getFileExtension());
            header('Content-Disposition: attachment; filename="' . $sFilename . '"');
            F::File_PrintChunked($sFullPath);


        } else {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->get('content_download_file_error'));
            return R::Action('error');
        }

    }

}

// EOF
