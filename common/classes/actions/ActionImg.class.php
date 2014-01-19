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

    const USER_AVATAR_SIZE = 100;
    const USER_PHOTO_SIZE = 250;
    const BLOG_AVATAR_SIZE = 100;

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

    /**
     * Makes image with new size
     */
    public function EventUploads() {

        // Раз оказались здесь, то нет соответствующего изображения. Пробуем его создать
        $sUrl = F::File_RootUrl() . '/' . $this->sCurrentEvent . '/' . implode('/', $this->GetParams());
        $sFile = F::File_Url2Dir($sUrl);
        $sNewFile = $this->Img_Duplicate($sFile);

        if (!$sNewFile) {
            if (preg_match('/\-(\d+)x(\d+)\.[a-z]{3}$/i', $sFile, $aMatches)) {
                $nSize = $aMatches[1];
            } else {
                $nSize = 0;
            }
            if (strpos(basename($sFile), 'avatar_blog') === 0) {
                // Запрашивается аватар блога
                $sNewFile = $this->Img_AutoresizeSkinImage($sFile, 'avatar_blog', $nSize ? $nSize : self::BLOG_AVATAR_SIZE);
            } elseif (strpos(basename($sFile), 'avatar') === 0) {
                // Запрашивается аватар
                $sNewFile = $this->Img_AutoresizeSkinImage($sFile, 'avatar', $nSize ? $nSize : self::USER_AVATAR_SIZE);
            } elseif (strpos(basename($sFile), 'user_photo') === 0) {
                // Запрашивается фото
                $sNewFile = $this->Img_AutoresizeSkinImage($sFile, 'user_photo', $nSize ? $nSize : self::USER_PHOTO_SIZE);
            }
        }

        // Если файл успешно создан, то выводим его
        if ($sNewFile) {
            if (headers_sent($sFile, $nLine)) {
                Router::Location($sUrl . '?rnd=' . uniqid());
            } else {
                header_remove();
                $this->Img_RenderFile($sNewFile);
                exit;
            }
        }
        F::HttpHeader('404 Not Found');
        exit;
    }

}

// EOF