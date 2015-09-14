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
 * Модуль обработки текста на основе типографа Jevix/Qevix
 * Позволяет вырезать из текста лишние HTML теги и предотвращает различные попытки внедрить в текст JavaScript
 * <pre>
 * $sText=E::ModuleText()->Parser($sTestSource);
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
     * @var Jevix|Qevix
     */
    protected $oTextParser;

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
                'pairedTag'   => false,                             // короткий тег
            ),
            'a'   => array(
                'link'        => 'href',
                'type'        => ModuleMresource::TYPE_HREF,
                'restoreFunc' => array($this, '_restoreLocalUrl'),
                'pairedTag'   => true,
            ),
        );

        // * Create a typographer and load its configuration
        $this->_createTextParser($this->aCheckTagLinks);

    }

    /**
     * Create a typographer and load its configuration
     */
    protected function _createTextParser($aCheckTagLinks) {

        $sParser = C::Get('module.text.parser');
        if ($sParser && strtolower($sParser) == 'qevix') {
            $this->oTextParser = new Qevix();
            $this->_loadTextParserConfig('qevix');

            foreach($aCheckTagLinks as $sTag => $aParams) {
                $this->oTextParser->cfgSetTagBuildCallback($sTag, array($this, 'CallbackCheckLinks'));
            }
        } else {
            $this->oTextParser = new Jevix();
            $this->_loadTextParserConfig();

            foreach($aCheckTagLinks as $sTag => $aParams) {
                $this->oTextParser->cfgSetTagCallbackFull($sTag, array($this, 'CallbackCheckLinks'));
            }
        }
    }

    /**
     * @param string $sParser
     */
    protected function _loadTextParserConfig($sParser = null) {

        if ($sParser == 'qevix') {
            $this->LoadQevixConfig();
        } else {
            $this->LoadJevixConfig();
        }
    }

    /**
     * Конфигурирует типограф
     *
     * @deprecated
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
            $this->oTextParser->tagsRules = array();
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
                    call_user_func_array(array($this->oTextParser, $sMethod), $aParams);
                }
            }

            // * Хардкодим некоторые параметры
            unset($this->oTextParser->entities1['&']); // разрешаем в параметрах символ &
            if (Config::Get('view.noindex') && isset($this->oTextParser->tagsRules['a'])) {
                $this->oTextParser->cfgSetTagParamDefault('a', 'rel', 'nofollow', true);
            }
        }

        $this->oTextParser->cfgSetTagCallbackFull('ls', array($this, 'CallbackTagLs'));
    }

    /**
     * Загружает конфиг Qevix'а
     *
     * @param string $sType     Тип конфига
     * @param bool   $bClear    Очищать предыдущий конфиг или нет
     */
    public function LoadQevixConfig($sType = 'default', $bClear = true) {

        // Временный костыль по методам, отсутствующим в Qevix
        $aExcludeConfig = array(
            'cfgSetAutoReplace',
            'cfgSetTagParamCombination',
        );

        if ($bClear) {
            $this->oTextParser->tagsRules = array();
        }
        $aConfig = Config::Get('qevix.' . $sType);
        if (is_array($aConfig)) {
            foreach ($aConfig as $sMethod => $aExec) {
                foreach ($aExec as $aParams) {
                    if (!in_array($sMethod, $aExcludeConfig)) {
                        call_user_func_array(array($this->oTextParser, $sMethod), $aParams);
                    }
                }
            }

            // * Хардкодим некоторые параметры
            unset($this->oTextParser->entities1['&']); // разрешаем в параметрах символ &
            if (Config::Get('view.noindex') && isset($this->oTextParser->tagsRules['a'])) {
                $this->oTextParser->cfgSetTagParamDefault('a', 'rel', 'nofollow', true);
            }
        }

        $this->oTextParser->cfgSetTagBuildCallback('ls', array($this, 'CallbackTagLs'));
        if (C::Get('module.text.char.@')) {
            $this->oTextParser->cfgSetSpecialCharCallback('@', array($this, 'CallbackTagAt'));
        }
    }

    /**
     * Возвращает объект Jevix
     *
     * @return Jevix
     */
    public function GetJevix() {

        return $this->oTextParser;
    }

    /**
     * Парсинг текста с помощью Jevix
     *
     * @deprecated
     */
    public function JevixParser($sText, &$aError = null) {

        return $this->TextParser($sText, $aError);
    }

    /**
     * Парсинг текста
     *
     * @param string $sText     Исходный текст
     * @param array  $aError    Возвращает список возникших ошибок
     *
     * @return string
     */
    public function TextParser($sText, &$aError = null) {

        // Если конфиг пустой, то загружаем его
        if (!count($this->oTextParser->tagsRules)) {
            $this->LoadJevixConfig();
        }
        $sResult = $this->oTextParser->parse($sText, $aError);
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

        $aConfig = E::ModuleUploader()->GetConfig('*', 'video');
        if (!empty($aConfig['transform']['max_width'])) {
            $iWidth = intval($aConfig['transform']['max_width']);
        } else {
            $iWidth = 640;
        }
        $nRatio = E::ModuleUploader()->GetConfigAspectRatio('*', 'video');
        if ($nRatio) {
            $iHeight = $iWidth / $nRatio;
        } else {
            $iHeight = intval($aConfig['transform']['max_width']);
        }
        if (!empty($aConfig['transform']['max_height'])) {
            if ($iHeight > intval($aConfig['transform']['max_width'])) {
                $iHeight = intval($aConfig['transform']['max_width']);
            }
        }
        if (!$iHeight) {
            $iHeight = 380;
        }

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
     * Разбирает текст и анализирует его на наличие сниппетов.
     * Если они найдены, то запускает хуки для их обработки.
     *
     * @version 0.1 Базовый функционал
     * @version 0.2 Добавлены блочный и шаблонный сниппеты
     *
     * @param string $sText
     *
     * @return string
     */
    public function SnippetParser($sText) {

        // Массив регулярки для поиска сниппетов
        $aSnippetRegexp = array(
            // Регулярка блочного сниппета. Сначала ищем по ней, а уже потом по непарному тегу
            // alto:name иначе блочный сниппет будет затираться поскульку регулярка одиночного сниппета
            // будет отхватывать первую его часть.
            '~<alto:(\w+)((?:\s+\w+(?:\s*=\s*(?:(?:"[^"]*")|(?:\'[^\']*\')|[^>\s]+))?)*)\s*>([.\s\S\r\n]*)</alto:\1>~Ui',
            // Регулярка строчного сниппета
            '~<alto:(\w+)((?:\s+\w+(?:\s*=\s*(?:(?:"[^"]*")|(?:\'[^\']*\')|[^>\s]+))?)*)\s*\/*>~Ui',
        );

        // Получим массив: сниппетов, их имён и параметров по каждой регклярке
        // Здесь получаем в $aMatches три/четыре массива из которых первым идет массив найденных сниппетов,
        // который позже будет заменён на результат полученный от хука. Вторым массивом идут имена
        // найденных сниппетов, которые будут использоваться для формирвоания имени хука.
        // Третим массивом будут идти параметры сниппетов. Если сниппет блочный, то четвертым параметром
        // будет текст-содержимое блока.
        foreach ($aSnippetRegexp as $sRegExp) {

            if (preg_match_all($sRegExp, $sText, $aMatches)) {

                // Данные для замены сниппетов на полученный код.
                $aReplaceData = array();

                /**
                 * @var int $k Порядковый номер найденного сниппета
                 * @var string $sSnippetName Имя (идентификатор) сниппета
                 */
                foreach ($aMatches[1] as $k => $sSnippetName) {

                    // Получим параметры в виде массива. Вообще-то их может и не быть воовсе,
                    // но мы всё-таки попробуем это сделать...
                    $aParams = array();
                    if (preg_match_all('~([a-zA-Z]+)\s*=\s*[\'"]([^\'"]+)[\'"]~Ui', $aMatches[2][$k], $aMatchesParams)) {
                        foreach ($aMatchesParams[1] as $pk => $sParamName) {
                            $aParams[$sParamName] = @$aMatchesParams[2][$pk];
                        }
                    }

                    // Добавим в параметры текст, который был в топике, вдруг какой-нибудь сниппет
                    // захочет с ним поработать.
                    $aParams['target_text'] = $sText;

                    // Если это блочный сниппет, то добавим в параметры еще и текст блока
                    $aParams['snippet_text'] = isset($aMatches[3][$k]) ? $aMatches[3][$k] : '';

                    // Добавим в параметры имя сниппета
                    $aParams['snippet_name'] = $sSnippetName;

                    // Попытаемся получить результат от обработчика
                    // Может сниппет уже был в обработке, тогда просто возьмем его из кэша
                    $sCacheKey = $sSnippetName . md5(serialize($aParams));
                    if (FALSE === ($sResult = E::ModuleCache()->GetTmp($sCacheKey))) {

                        // Определим тип сниппета, может быть шаблонным, а может и исполняемым
                        // по умолчанию сниппет ссчитаем исполняемым. Если шаблонный, то его
                        // обрабатывает предопределенный хук snippet_template_type
                        $sHookName = 'snippet_' . $sSnippetName;
                        $sHookName = E::ModuleHook()->IsEnabled($sHookName)
                            ? 'snippet_' . $sSnippetName
                            : 'snippet_template_type';

                        // Установим хук
                        E::ModuleHook()->Run($sHookName, array(
                            'params' => &$aParams,
                            'result' => &$sResult,
                        ));

                        // Запишем результат обработки в кэш
                        E::ModuleCache()->SetTmp($sResult, $sCacheKey);

                    }

                    $aReplaceData[$k] = is_string($sResult) ? $sResult : '';

                }

                // Произведем замену. Если обработчиков не было, то сниппеты
                // будут заменены на пустую строку.
                $sText = str_replace(
                    array_values($aMatches[0]),
                    array_values($aReplaceData),
                    $sText
                );

            }

        }

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
        $sResult = $this->SnippetParser($sResult);
        $sResult = $this->TextParser($sResult);
        $sResult = $this->VideoParser($sResult);

        // Clear links on local resources
        //$sResult = E::ModuleMresource()->NormalizeUrl($sResult, '/', '/');
        //$sResult = E::ModuleMresource()->NormalizeUrl($sResult, '/', '');

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
     * @return string
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
     * @param string $sTag     Тег на ктором сработал колбэк
     * @param array  $aParams  Список параметров тега
     * @param string $sContent
     *
     * @return string
     */
    public function CallbackTagLs($sTag, $aParams, $sContent) {

        $sText = '';
        if (isset($aParams['user'])) {
            $sText = $this->CallbackTagAt($aParams['user']);
        }
        return $sText;
    }

    public function CallbackTagAt($sString) {

        $sText = '';
        if ($sString) {
            if ($oUser = E::ModuleUser()->GetUserByLogin($sString)) {
                if (E::ModuleViewer()->TemplateExists('tpls/snippets/snippet.user.tpl')) {
                    // Получим html-код сниппета
                    $aVars = array('oUser' => $oUser);
                    $sText = trim(E::ModuleViewer()->Fetch('tpls/snippets/snippet.user.tpl', $aVars));
                } else {
                    $sText = "<a href=\"{$oUser->getProfileUrl()}\">{$oUser->getDisplayName()}</a> ";
                }
            }
        }
        return $sText;
    }

    /**
     * @param string $sUrl
     *
     * @return string
     */
    public function _restoreLocalUrl($sUrl) {

        if (substr($sUrl, 0, 1) == '@') {
            $sUrl = '/' . substr($sUrl, 1);
        }
        return $sUrl;
    }

    /**
     * Учет ссылок в тексте
     *
     * @param string $sTag
     * @param array  $aParams
     * @param string $sContent
     * @param string $sText
     *
     * @return string
     */
    public function CallbackCheckLinks($sTag, $aParams, $sContent, $sText = null) {

        if (isset($this->aCheckTagLinks[$sTag])) {
            if (isset($aParams[$this->aCheckTagLinks[$sTag]['link']])) {
                $sLinkAttr = $this->aCheckTagLinks[$sTag]['link'];
                $sLink = E::ModuleMresource()->NormalizeUrl($aParams[$sLinkAttr]);
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
                if (is_null($sContent) || empty($this->aCheckTagLinks[$sTag]['pairedTag'])) {
                    $sText = trim($sText) . '>';
                } else {
                    $sText = trim($sText) . '>' . $sContent . '</' . $sTag . '>';
                }
            }
        }
        return $sText;
    }

    /**
     * @param bool $bUrlOnly
     *
     * @return array
     */
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
                        // need to close open tags
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