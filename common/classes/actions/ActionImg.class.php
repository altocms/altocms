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
 * @since 1.0
 */
class ActionImg extends Action {

    /**
     * Инициализация
     *
     */
    public function Init() {

        $this->SetDefaultEvent('uploads');
    }

    protected function RegisterEvent() {

        $this->AddEvent('uploads', 'EventUploads');
    }

    public function EventUploads() {

        // Раз оказались здесь, то нет соответствующего изображения. Пробуем его создать
        $sUrl = F::File_RootUrl() . '/' . $this->sCurrentEvent . '/' . implode('/', $this->GetParams());
        $sFile = $this->Img_Duplicate(F::File_Url2Dir($sUrl));

        // Если файл успешно создан, то выводим его
        if ($sFile) {
            if (headers_sent()) {
                Router::Location($sUrl);
            } else {
                header_remove();
                $this->Img_RenderFile($sFile);
                exit;
            }
        }
    }
}

// EOF