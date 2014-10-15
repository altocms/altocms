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

F::IncludeLib('Jevix/jevix.class.php');

/**
 * Модуль обработки текста на основе типографа Jevix
 * Позволяет вырезать из текста лишние HTML теги и предотвращает различные попытки внедрить в текст JavaScript
 * <pre>
 * $sText=$this->Text_Parser($sTestSource);
 * </pre>
 * Настройки парсинга находятся в конфиге /config/jevix.php
 *
 * @package engine.modules
 * @since   1.0
 */
class ModuleText extends Module {
    /**
     * Объект типографа
     *
     * @var Jevix
     */
    protected $oJevix;

    protected $aLinks = array();

    protected $aCheckTagLinks = array();

    /**
     * Инициализация модуля
     *
     */
    public function Init() {

        // В каких тегах контролируем ссылки
        $this->aCheckTagLinks = array(
            'img' => array(
                'link'        => 'src',                             // какой атрибут контролировать
                'type'        => ModuleMresource::TYPE_IMAGE,       // тип медиа-ресурса
                'restoreFunc' => array($this, '_restoreLocalUrl'),  // функция для восстановления URL
            ),
            'a'   => array(
                'link'        => 'href',
                'type'        => ModuleMresource::TYPE_HREF,
                'restoreFunc' => array($this, '_restoreLocalUrl'),
            ),
        );

        /**
         * Создаем объект типографа и запускаем его конфигурацию
         */
        $this->oJevix = new Jevix();
        $this->JevixConfig();

        foreach($this->aCheckTagLinks as $sTag => $aParams) {
            $this->oJevix->cfgSetTagCallbackFull($sTag, array($this, 'CallbackCheckLinks'));
        }
    }

    /**
     * Конфигурирует типограф
     *
     */
    protected function JevixConfig() {
        // загружаем конфиг
        $this->LoadJevixConfig();
    }

    /**
     * Загружает конфиг Jevix'а
     *
     * @param string $sType     Тип конфига
     * @param bool   $bClear    Очищать предыдущий конфиг или нет
     */
    public function LoadJevixConfig($sType = 'default', $bClear = true) {

        if ($bClear) {
            $this->oJevix->tagsRules = array();
        }
        $aConfig = Config::Get('jevix.' . $sType);
        if (is_array($aConfig)) {
            foreach ($aConfig as $sMethod => $aExec) {
                foreach ($aExec as $aParams) {
                    if (in_array(
                        strtolower($sMethod),
                        array_map('strtolower', array('cfgSetTagCallbackFull', 'cfgSetTagCallback'))
                    )
                    ) {
                        if (isset($aParams[1][0]) && $aParams[1][0] == '_this_') {
                            $aParams[1][0] = $this;
                        }
                    }
                    call_user_func_array(array($this->oJevix, $sMethod), $aParams);
                }
            }
            /**
             * Хардкодим некоторые параметры
             */
            unset($this->oJevix->entities1['&']); // разрешаем в параметрах символ &
            if (Config::Get('view.noindex') && isset($this->oJevix->tagsRules['a'])) {
                $this->oJevix->cfgSetTagParamDefault('a', 'rel', 'nofollow', true);
            }
        }
    }

    /**
     * Возвращает объект Jevix
     *
     * @return Jevix
     */
    public function GetJevix() {

        return $this->oJevix;
    }

    /**
     * Парсинг текста с помощью Jevix
     *
     * @param string $sText     Исходный текст
     * @param array  $aError    Возвращает список возникших ошибок
     *
     * @return string
     */
    public function JevixParser($sText, &$aError = null) {

        // Если конфиг пустой, то загружаем его
        if (!count($this->oJevix->tagsRules)) {
            $this->LoadJevixConfig();
        }
        $sResult = $this->oJevix->parse($sText, $aError);
        return $sResult;
    }

    /**
     * Парсинг текста на предмет видео
     * Находит теги <pre><video></video></pre> и реобразовываетих в видео
     *
     * @param string $sText    Исходный текст
     *
     * @return string
     */
    public function VideoParser($sText) {
        $iWidth = Config::Get('module.image.preset.default.size.width');
        $iHeight = $iWidth / 1.777;

        $sIframeAttr = 'frameborder="0" webkitAllowFullScreen mozallowfullscreen allowfullscreen="allowfullscreen"';
        /**
         * youtube.com
         */
        $sText = preg_replace(
            '/<video>http(?:s|):\/\/(?:www\.|m.|)youtube\.com\/watch\?v=([a-zA-Z0-9_\-]+)(&.+)?<\/video>/Ui',
            '<iframe width="' . $iWidth . '" height="' . $iHeight . '" src="http://www.youtube.com/embed/$1" ' . $sIframeAttr . '></iframe>',
            $sText
        );
        /**
         * youtu.be
         */
        $sText = preg_replace(
            '/<video>http(?:s|):\/\/(?:www\.|m.|)youtu\.be\/([a-zA-Z0-9_\-]+)(&.+)?<\/video>/Ui',
            '<iframe width="' . $iWidth . '" height="' . $iHeight . '" src="http://www.youtube.com/embed/$1" ' . $sIframeAttr . '></iframe>',
            $sText
        );
        /**
         * vimeo.com
         */
        $sText = preg_replace(
            '/<video>http(?:s|):\/\/(?:www\.|)vimeo\.com\/(\d+).*<\/video>/i',
            '<iframe src="http://player.vimeo.com/video/$1" width="' . $iWidth . '" height="' . $iHeight . '" ' . $sIframeAttr . '></iframe>',
            $sText
        );
        /**
         * rutube.ru
         */
        $sText = preg_replace(
            '/<video>http(?:s|):\/\/(?:www\.|)rutube\.ru\/tracks\/(\d+)\.html.*<\/video>/Ui',
            '<iframe src="//rutube.ru/play/embed/$1" width="' . $iWidth . '" height="' . $iHeight . '" ' . $sIframeAttr . '></iframe>',
            $sText
        );

        $sText = preg_replace(
            '/<video>http(?:s|):\/\/(?:www\.|)rutube\.ru\/video\/(\w+)\/?<\/video>/Ui',
            '<iframe src="//rutube.ru/play/embed/$1" width="' . $iWidth . '" height="' . $iHeight . '" ' . $sIframeAttr . '></iframe>',
            $sText
        );
        /**
         * video.yandex.ru - closed
         */
        return $sText;
    }

    /**
     * Парсит текст, применя все парсеры
     *
     * @param string $sText Исходный текст
     *
     * @return string
     */
    public function Parser($sText) {

        if (!is_string($sText)) {
            return '';
        }
        $this->aLinks = array();

        $sResult = $this->FlashParamParser($sText);
        $sResult = $this->JevixParser($sResult);
        $sResult = $this->VideoParser($sResult);

        // Clear links on local resources
        //$sResult = $this->Mresource_NormalizeUrl($sResult, '/', '/');
        //$sResult = $this->Mresource_NormalizeUrl($sResult, '/', '');

        $sResult = $this->CodeSourceParser($sResult);
        return $sResult;
    }

    /**
     * Заменяет все вхождения короткого тега <param/> на длиную версию <param></param>
     * Заменяет все вхождения короткого тега <embed/> на длиную версию <embed></embed>
     *
     * @param string $sText Исходный текст
     *
     * @return string
     */
    protected function FlashParamParser($sText) {

        if (preg_match_all(
            "@(<\s*param\s*name\s*=\s*(?:\"|').*(?:\"|')\s*value\s*=\s*(?:\"|').*(?:\"|'))\s*/?\s*>(?!</param>)@Ui",
            $sText, $aMatch
        )
        ) {
            foreach ($aMatch[1] as $key => $str) {
                $str_new = $str . '></param>';
                $sText = str_replace($aMatch[0][$key], $str_new, $sText);
            }
        }
        if (preg_match_all("@(<\s*embed\s*.*)\s*/?\s*>(?!</embed>)@Ui", $sText, $aMatch)) {
            foreach ($aMatch[1] as $key => $str) {
                $str_new = $str . '></embed>';
                $sText = str_replace($aMatch[0][$key], $str_new, $sText);
            }
        }
        /**
         * Удаляем все <param name="wmode" value="*"></param>
         */
        if (preg_match_all("@(<param\s.*name=(?:\"|')wmode(?:\"|').*>\s*</param>)@Ui", $sText, $aMatch)) {
            foreach ($aMatch[1] as $key => $str) {
                $sText = str_replace($aMatch[0][$key], '', $sText);
            }
        }
        /**
         * А теперь после <object> добавляем <param name="wmode" value="opaque"></param>
         * Решение не фантан, но главное работает :)
         */
        if (preg_match_all("@(<object\s.*>)@Ui", $sText, $aMatch)) {
            foreach ($aMatch[1] as $key => $str) {
                $sText = str_replace(
                    $aMatch[0][$key], $aMatch[0][$key] . '<param name="wmode" value="opaque"></param>', $sText
                );
            }
        }
        return $sText;
    }

    /**
     * Подсветка исходного кода
     *
     * @param string $sText Исходный текст
     *
     * @return mixed
     */
    public function CodeSourceParser($sText) {

        $sText = str_replace("<code>", '<pre class="prettyprint"><code>', $sText);
        $sText = str_replace("</code>", '</code></pre>', $sText);
        return $sText;
    }

    /**
     * Производить резрезание текста по тегу cut.
     * Возвращаем массив вида:
     * <pre>
     * array(
     *        $sTextShort - текст до тега <cut>
     *        $sTextNew   - весь текст за исключением удаленного тега
     *        $sTextCut   - именованное значение <cut>
     * )
     * </pre>
     *
     * @param  string $sText Исходный текст
     *
     * @return array
     */
    public function Cut($sText) {

        $sTextShort = $sText;
        $sTextNew = $sText;
        $sTextCut = null;

        $sTextTemp = str_replace("\r\n", '[<rn>]', $sText);
        $sTextTemp = str_replace("\n", '[<n>]', $sTextTemp);

        if (preg_match("/^(.*)<cut(.*)>(.*)$/Ui", $sTextTemp, $aMatch)) {
            $aMatch[1] = str_replace('[<rn>]', "\r\n", $aMatch[1]);
            $aMatch[1] = str_replace('[<n>]', "\r\n", $aMatch[1]);
            $aMatch[3] = str_replace('[<rn>]', "\r\n", $aMatch[3]);
            $aMatch[3] = str_replace('[<n>]', "\r\n", $aMatch[3]);
            $sTextShort = $aMatch[1];
            $sTextNew = $aMatch[1] . ' <a name="cut"></a> ' . $aMatch[3];
            if (preg_match('/^\s*name\s*=\s*"(.+)"\s*\/?$/Ui', $aMatch[2], $aMatchCut)) {
                $sTextCut = trim($aMatchCut[1]);
            }
        }

        return array($sTextShort, $sTextNew, $sTextCut ? htmlspecialchars($sTextCut) : null);
    }

    /**
     * Обработка тега ls в тексте
     * <pre>
     * <ls user="admin" />
     * </pre>
     *
     * @param string $sTag    Тег на ктором сработал колбэк
     * @param array  $aParams Список параметров тега
     *
     * @return string
     */
    public function CallbackTagLs($sTag, $aParams) {

        $sText = '';
        if (isset($aParams['user'])) {
            if ($oUser = $this->User_GetUserByLogin($aParams['user'])) {
                $sText .= "<a href=\"{$oUser->getUserWebPath()}\" class=\"ls-user\">{$oUser->getLogin()}</a> ";
            }
        }
        return $sText;
    }

    public function _restoreLocalUrl($sUrl) {

        if (substr($sUrl, 0, 1) == '@') {
            $sUrl = '/' . substr($sUrl, 1);
        }
        return $sUrl;
    }

    /**
     * Учет ссылок в тексте
     *
     * @param $sTag
     * @param $aParams
     * @param $sContent
     * @param $sText
     *
     * @return string
     */
    public function CallbackCheckLinks($sTag, $aParams, $sContent, $sText) {

        if (isset($this->aCheckTagLinks[$sTag])) {
            if (isset($aParams[$this->aCheckTagLinks[$sTag]['link']])) {
                $sLinkAttr = $this->aCheckTagLinks[$sTag]['link'];
                $sLink = $this->Mresource_NormalizeUrl($aParams[$sLinkAttr]);
                $nType = $this->aCheckTagLinks[$sTag]['type'];
                $this->aLinks[] = array(
                    'type' => $nType,
                    'link' => $sLink,
                );
                $sText = '<' . $sTag . ' ';
                foreach ($aParams as $sKey => $sVal) {
                    if ($sKey == $sLinkAttr && $this->aCheckTagLinks[$sTag]['restoreFunc']) {
                        $sVal = call_user_func($this->aCheckTagLinks[$sTag]['restoreFunc'], $sLink);
                    }
                    $sText .= $sKey . '="' . $sVal . '" ';
                }
                if (is_null($sContent)) {
                    $sText .= '/>';
                } else {
                    $sText .= '>' . $sContent . '</' . $sTag . '>';
                }
            }
        }
        return $sText;
    }

    public function GetLinks($bUrlOnly = false) {

        if ($bUrlOnly) {
            return F::Array_Column($this->aLinks, 'link');
        } else {
            return $this->aLinks;
        }
    }

    /**
     * Truncates text with word wrapping
     *
     * @param string $sText
     * @param int    $iMaxLen
     *
     * @return string
     */
    public function TruncateText($sText, $iMaxLen) {

        $sResult = $sText;
        if (strpos($sText, '<') === false) {
            // no tags
            $sResult = F::TruncateText($sText, $iMaxLen, '', true);
        } else {
            $iLen = mb_strlen(strip_tags($sText), 'UTF-8');
            if ($iLen > $iMaxLen) {
                if (preg_match_all('/\<\/?\w+[^>]*>/siu', $sResult, $aM, PREG_OFFSET_CAPTURE)) {
                    $aTags = $aM[0];
                    $iOffset = 0;
                    foreach($aTags as $iTagIdx => $aTag) {
                        $iLen = strlen($aTag[0]);
                        $sResult = substr($sResult, 0, $aTag[1] + $iOffset) . substr($sResult, $aTag[1] + $iOffset + $iLen);
                        $iOffset -= $iLen;
                        $aTag['tag'] = $aTag[0];
                        $aTag['pos'] = $aTag[1];
                        $aTag['pair'] = null;
                        if ($aTag['tag'][1] !== '/') {
                            $aTag['open'] = true;
                        } else {
                            $aTag['open'] = false;
                            $sTagName = '<' . substr($aTag['tag'], 2, strlen($aTag['tag']) - 3);
                            // seek open tag
                            foreach($aTags as $iOpenIdx => $aOpenTag) {
                                if (strpos($aOpenTag['tag'], $sTagName) === 0 && !isset($aOpenTag['pair'])) {
                                    // link from open tag to closing
                                    $aTags[$iOpenIdx]['pair'] = $iTagIdx;
                                    // link from close tag to openning
                                    $aTag['pair'] = $iOpenIdx;
                                    break;
                                }
                            }
                        }
                        $aTags[$iTagIdx] = $aTag;
                    }
                    $sResult = F::TruncateText($sResult, $iMaxLen, '', true);
                    $aClosingTags = array();
                    foreach ($aTags as $iIdx => $aTag) {
                        if (strlen($sResult) < $aTag['pos']) {
                            break;
                        }
                        $sResult = substr($sResult, 0, $aTag['pos']) . $aTag['tag'] . substr($sResult, $aTag['pos']);
                        if ($aTag['open']) {
                            // open tag
                            if ($aTag['pair']) {
                                $aClosingTags[$aTag['pair']] = $aTags[$aTag['pair']];
                            }
                        } else {
                            // close tag
                            if (!is_null($aTag['pair']) && isset($aClosingTags[$iIdx])) {
                                unset($aClosingTags[$iIdx]);
                            }
                        }
                    }
                    if ($aClosingTags) {
                        // need to close openned tags
                        ksort($aClosingTags);
                        foreach ($aClosingTags as $aTag) {
                            $sResult .= $aTag['tag'];
                        }
                    }
                }
            }
        }
        return $sResult;
    }
}

// EOF