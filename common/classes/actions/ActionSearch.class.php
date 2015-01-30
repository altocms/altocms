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
 * @since   0.9
 */
F::File_IncludeLib('Jevix/jevix.class.php');

class ActionSearch extends Action {

    protected $aReq;
    protected $sPatternW = '[\wа-яА-Я\.\*-]'; // символ слова
    protected $sPatternB = '[^\wа-яА-Я\.\*-]'; // граница слова
    protected $sPatternX = '[^\s\wа-яА-Я\*-]'; // запрещеные символы без *
    protected $sPatternXA = '[^\s\wа-яА-Я-]'; // запрещеные символы, в т.ч. *
    protected $nModeOutList;
    protected $nShippetLength;
    protected $nShippetMaxLength;
    protected $nShippetOffset;
    protected $sSnippetBeforeMatch;
    protected $sSnippetAfterMatch;
    protected $sSnippetBeforeFragment;
    protected $sSnippetAfterFragment;
    protected $nSnippetMaxFragments;

    protected $bSearchStrict = true; // Строгий поиск
    protected $bSkipAllTags = true; // Не искать в тегах

    protected $oJevix = null; // придется выборочно "чистить" HTML-текст

    protected $bLogEnable = false;
    protected $oUser = null;
    protected $oLogs = null;

    protected $aConfig = array();

    /**
     * Инициализация
     */
    public function Init() {

        $this->SetDefaultEvent('index');
        $this->Viewer_AddHtmlTitle($this->Lang_Get('search'));

        $this->nModeOutList = Config::Get('module.search.out_mode');

        $this->nShippetLength = Config::Get('module.search.snippet.length');
        $this->nShippetMaxLength = Config::Get('module.search.snippet.max_length');
        if (($this->nShippetMaxLength > 0) && ($this->nShippetMaxLength < $this->nShippetLength)) {
            $this->nShippetMaxLength = $this->nShippetLength;
        }

        $this->sSnippetBeforeMatch = Config::Get('module.search.snippet.before_match');
        $this->sSnippetAfterMatch = Config::Get('module.search.snippet.after_match');
        $this->sSnippetBeforeFragment = Config::Get('module.search.snippet.before_fragment');
        $this->sSnippetAfterFragment = Config::Get('module.search.snippet.after_fragment');
        $this->nSnippetMaxFragments = Config::Get('module.search.snippet.max_fragments');

        $this->sPatternW = Config::Get('module.search.char_pattern');
        $this->sPatternB = '[^' . mb_substr($this->sPatternW, 1); // '[^\wа-яА-Я\.\*-]';    // граница слова
        $this->sPatternX = '[^\s' . mb_substr($this->sPatternW, 1); // '[^\s\wа-яА-Я\*-]';  // запрещеные символы без *
        $this->sPatternXA
            = '[^\s\*' . mb_substr($this->sPatternW, 1); // '[^\s\wа-яА-Я-]';               // запрещеные символы, в т.ч. *

        $this->bSearchStrict = Config::Get('module.search.strict_search');
        $this->bSkipAllTags = Config::Get('module.search.skip_all_tags');

        $this->nItemsPerPage = Config::Get('module.search.items_per_page');

        mb_internal_encoding('UTF-8');

        $this->oJevix = new Jevix();
        // Разрешённые теги
        if ($this->nModeOutList == 'snippet') {
            $this->oJevix->cfgAllowTags(array('a', 'img', 'object', 'param', 'embed'));
        } else {
            $this->oJevix->cfgAllowTags(array('a', 'img', 'object', 'param', 'embed'));
        }
        // Коротие теги типа
        $this->oJevix->cfgSetTagShort(array('img'));
        // Разрешённые параметры тегов
        $this->oJevix->cfgAllowTagParams(
            'img',
            array('src', 'alt' => '#text', 'title', 'align' => array('right', 'left', 'center'), 'width' => '#int',
                  'height'     => '#int', 'hspace' => '#int', 'vspace' => '#int')
        );
        $this->oJevix->cfgAllowTagParams('a', array('title', 'href', 'rel'));
        $this->oJevix->cfgAllowTagParams('object', array('width' => '#int', 'height' => '#int', 'data' => '#link'));
        $this->oJevix->cfgAllowTagParams('param', array('name' => '#text', 'value' => '#text'));
        $this->oJevix->cfgAllowTagParams(
            'embed',
            array('src' => '#image', 'type' => '#text', 'allowscriptaccess' => '#text', 'allowfullscreen' => '#text',
                  'width' => '#int', 'height' => '#int', 'flashvars' => '#text', 'wmode' => '#text')
        );
        // Параметры тегов являющиеся обязательными
        $this->oJevix->cfgSetTagParamsRequired('img', 'src');
        $this->oJevix->cfgSetTagParamsRequired('a', 'href');
        // Теги которые необходимо вырезать из текста вместе с контентом
        $this->oJevix->cfgSetTagCutWithContent(array('script', 'iframe', 'style'));
        // Вложенные теги
        $this->oJevix->cfgSetTagChilds('object', 'param', false, true);
        $this->oJevix->cfgSetTagChilds('object', 'embed', false, false);
        // Отключение авто-добавления <br>
        $this->oJevix->cfgSetAutoBrMode(true);

        $this->SetTemplateAction('index');
    }

    /**
     * Регистрация событий
     *
     * @return void
     */
    protected function RegisterEvent() {

        $this->AddEvent('index', 'EventIndex');
        $this->AddEvent('opensearch', 'EventOpensearch');

        $this->AddEvent('topics', 'EventTopics');
        $this->AddEvent('comments', 'EventComments');
        $this->AddEvent('blogs', 'EventBlogs');
    }

    /**
     * Протоколирование запросов
     *
     * @param array|null $aVars
     */
    public function OutLog($aVars = null) {

        if (!$this->bLogEnable) {
            return;
        }

        if (!($sLogFile = Config::Get('module.search.logs.file'))) {
            $sLogFile = 'search.log';
        }
        if (!$this->oUser) {
            if (($sUserId = $this->Session_Get('user_id'))) {
                $this->oUser = $this->User_GetUserById($sUserId);
            }
        }
        if (!$this->oUser) {
            $sUserLogin = '*anonymous*';
        } else {
            $sUserLogin = $this->oUser->GetLogin();
        }

        $path = R::GetPathWebCurrent();
        $uri = $_SERVER['REQUEST_URI'];

        $sStrLog = 'user=>"' . $sUserLogin . '" ip=>"' . $_SERVER['REMOTE_ADDR'] . '"' . "\n" .
            str_repeat(' ', 22) . 'path=>' . $path . '"' . "\n" .
            str_repeat(' ', 22) . 'uri=>' . $uri . '"';
        if (is_array($aVars) && sizeof($aVars)) {
            foreach ($aVars as $key => $val) {
                $sStrLog .= "\n" . str_repeat(' ', 22) . $key . '=>"' . $val . '"';
            }
        }

        $this->Logger_Dump($sLogFile, $sStrLog);
    }

    /**
     * Преобразование RegExp-а к стандарту PHP
     *
     * @return string
     */
    protected function PreparePattern() {

        if ($this->bSearchStrict) {
            $sRegexp = $this->aReq['regexp'];
            $sRegexp = str_replace('[[:>:]]', $this->sPatternB, $sRegexp);
            $sRegexp = str_replace('[[:<:]]', $this->sPatternB, $sRegexp);

            $sRegexp = '/' . $sRegexp . '/iusxSU';
        } else {
            $sRegexp = '/' . $this->aReq['regexp'] . '/iusxSU';
        }
        return $sRegexp;
    }

    /**
     * "Подсветка" текста
     *
     * @param string $sText
     *
     * @return string
     */
    protected function TextHighlite($sText) {

        $sRegexp = $this->PreparePattern();
        if ($this->bSearchStrict) {
            $sText = preg_replace($sRegexp, $this->sSnippetBeforeMatch . '\\0' . $this->sSnippetAfterMatch, $sText);
        } else {
            $sText = preg_replace(
                $this->aReq['regexp'], $this->sSnippetBeforeMatch . '\\0' . $this->sSnippetAfterMatch, $sText
            );
        }
        return $sText;
    }

    /**
     * Создание фрагмента для сниппета
     *
     * @param string $sText
     * @param array  $aSet
     * @param int    $nPos
     * @param int    $nLen
     *
     * @return string
     */
    protected function MakeSnippetFragment($sText, $aSet, $nPos, $nLen) {

        $nLenWord = $nLen;
        $nLenText = mb_strlen($sText);

        $this->nShippetOffset = floor(($this->nShippetLength - $nLenWord) / 2);

        // начало фрагмена
        if ($nPos < $this->nShippetOffset) {
            $nFragBegin = 0;
        } else {
            $nFragBegin = $nPos - $this->nShippetOffset;
        }

        // конец фрагмента
        if ($nPos + $nLenWord + $this->nShippetOffset > $nLenText) {
            $nFragEnd = $nLenText;
        } else {
            $nFragEnd = $nPos + $nLenWord + $this->nShippetOffset;
        }

        // Выравнивание по границе слов
        $sPattern = '/' . $this->sPatternW . '+$/uisxSXU';
        if (($nFragBegin > 0) && preg_match($sPattern, mb_substr($sText, 0, $nFragBegin), $m, PREG_OFFSET_CAPTURE)) {
            $nFragBegin -= mb_strlen($m[0][0]);
        }

        if (($nFragEnd < $nLenText) && preg_match($sPattern, mb_substr($sText, $nFragEnd), $m, PREG_OFFSET_CAPTURE)) {
            $nFragEnd += mb_strlen($m[0][0]) + $m[0][1];
        }

        // Обрезание по максимальной длине
        if (($this->nShippetMaxLength > 0) && (($nOver = $nFragEnd - $nFragBegin - $this->nShippetMaxLength) > 0)) {
            $nFragBegin -= floor($nOver / 2);
            if ($nFragBegin > $nPos) {
                $nFragBegin = $nPos;
            }
            $nFragEnd = $nFragBegin + $this->nShippetMaxLength;
            if ($nFragEnd < $nPos + $nLenWord) {
                $nFragEnd = $nPos + $nLenWord;
            }
        }

        $sFragment = '';

        // * Укладываем слова из одного сета в один фрагмент
        $begin = $nFragBegin;
        foreach ($aSet as $word) {
            $pos = $word['pos'];
            $sFragment .= str_replace('>', '&gt;', str_replace('<', '&lt;', mb_substr($sText, $begin, $pos - $begin)));
            $sFragment .= $this->sSnippetBeforeMatch . $word['txt'] . $this->sSnippetAfterMatch;
            $begin = $pos + $word['len'];
        }
        $sFragment .= str_replace('>', '&gt;', str_replace('<', '&lt;', mb_substr($sText, $begin, $nFragEnd - $begin)));

        $sFragment = (($nFragBegin > 0) ? '&hellip;' : '') . $sFragment . (($nFragEnd < $nLenText) ? '&hellip;' : '');
        $sFragment = str_replace('&lt;br/&gt;', '', $sFragment);
        return $sFragment;
    }

    /**
     * Создание сниппета
     *
     * @param string $sText
     *
     * @return string
     */
    protected function MakeSnippet($sText) {

        $aError = array();
        $sRegexp = $this->PreparePattern();
        // * Если задано, то вырезаем все теги
        if ($this->bSkipAllTags) {
            $sText = strip_tags($sText);
        } else {
            $sText = $this->oJevix->parse($sText, $aError);
            $sText = str_replace('<br/>', '', $sText);
        }

        $sText = str_replace(' ', '  ', $sText);
        if (mb_preg_match_all($sRegexp, $sText, $matches, PREG_OFFSET_CAPTURE)) {
            // * Создаем набор фрагментов текста
            $sSnippet = '';
            $aFragmentSets = array();
            $nFragmentSetsCount = -1;
            $nCount = 0;
            $aLastSet = array();
            $nLastLen = 0;
            foreach ($matches[0] as $match) {
                $sFrTxt = $match[0];
                $nFrPos = $match[1];
                $nFrLen = mb_strlen($sFrTxt);
                // Создаем сеты фрагментов, чтобы близлежащие слова попали в один сет
                if (($nFragmentSetsCount == -1) || $nLastLen == 0) {
                    $aLastSet = array('txt' => $sFrTxt, 'pos' => $nFrPos, 'len' => $nFrLen);
                    $nLastLen = $nFrPos + $nFrLen;
                    $aFragmentSets[++$nFragmentSetsCount][] = $aLastSet;
                } else {
                    if (($nFrPos + $nFrLen - $aLastSet['pos']) < $this->nShippetLength) {
                        $aFragmentSets[$nFragmentSetsCount][] = array('txt' => $sFrTxt, 'pos' => $nFrPos,
                                                                      'len' => $nFrLen);
                        $nLastLen = $nFrPos + $nFrLen - $aLastSet['pos'];
                    } else {
                        $aLastSet = array('txt' => $sFrTxt, 'pos' => $nFrPos, 'len' => $nFrLen);
                        $nLastLen = $nFrPos + $nFrLen;
                        $aFragmentSets[++$nFragmentSetsCount][] = $aLastSet;
                    }
                }
            }

            foreach ($aFragmentSets as $aSet) {
                $nLen = 0;
                foreach ($aSet as $aWord) {
                    if ($nLen == 0) {
                        $nLen = $aWord['len'];
                        $nPos = $aWord['pos'];
                    } else {
                        $nLen = $aWord['pos'] + $aWord['len'] - $nPos;
                    }
                }

                $aFragments[] = $this->MakeSnippetFragment($sText, $aSet, $nPos, $nLen);
                if (($this->nSnippetMaxFragments > 0) && ((++$nCount) >= $this->nSnippetMaxFragments)) {
                    break;
                }
            }
            foreach ($aFragments as $sFragment) {
                $sSnippet .= $this->sSnippetBeforeFragment . $sFragment . $this->sSnippetAfterFragment;
            }
        } else {
            if (mb_strlen($sText) > $this->nShippetMaxLength) {
                $sSnippet = mb_substr($sText, 0, $this->nShippetMaxLength) . '&hellip;';
            } else {
                $sSnippet = $sText;
            }
        }
        return $sSnippet;
    }

    /**
     * Обработка основного события
     *
     */
    public function EventIndex() {

        $sEvent = R::GetActionEvent();

        if ($sEvent == 'comments') {
            return $this->EventComments();
        } elseif ($sEvent == 'blogs') {
            return $this->EventBlogs();
        } elseif ($sEvent == 'topics') {
            return $this->EventTopics();
        } else {
            $this->SetTemplateAction('index');
        }
    }

    /**
     * Поддержка OpenSearch
     *
     */
    public function EventOpensearch() {

        header('Content-type: text/xml; charset=utf-8');
        $sOutText
            = '<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
                    <ShortName>' . str_replace('http://', '', Config::Get('path.root.url')) . '</ShortName>
                    <Image height="16" width="16" type="image/vnd.microsoft.icon">' . Config::Get('path.static.skin') . '/images/favicon.ico</Image>
                    <InputEncoding>utf-8</InputEncoding>
                    <Url type="text/html" method="get" template="http://site.ru/search/?q={searchTerms}"/>
                </OpenSearchDescription>
        ';
        echo $sOutText;
        exit;
    }

    /**
     * Поиск топиков
     */
    public function EventTopics() {

        $this->aReq = $this->PrepareRequest('topics');
        $this->OutLog();
        if ($this->aReq['regexp']) {
            $aResult = $this->Search_GetTopicsIdByRegexp(
                $this->aReq['regexp'], $this->aReq['iPage'],
                $this->nItemsPerPage, $this->aReq['params']
            );

            $aTopics = array();
            if ($aResult['count'] > 0) {
                $aTopicsFound = $this->Topic_GetTopicsAdditionalData($aResult['collection']);

                // * Подсветка поисковой фразы в тексте или формирование сниппета
                foreach ($aTopicsFound AS $oTopic) {
                    if ($oTopic && $oTopic->getBlog()) {
                        if ($this->nModeOutList == 'short') {
                            $oTopic->setTextShort($this->TextHighlite($oTopic->getTextShort()));
                        } elseif ($this->nModeOutList == 'full') {
                            $oTopic->setTextShort($this->TextHighlite($oTopic->getText()));
                        } else {
                            $oTopic->setTextShort($this->MakeSnippet($oTopic->getText()));
                        }
                        $oTopic->setBlogTitle($oTopic->getBlog()->getTitle());
                        $aTopics[] = $oTopic;
                    }
                }
            }
        } else {
            $aResult['count'] = 0;
            $aTopics = array();
        }

        if ($this->bLogEnable) {
            $this->oLogs->RecordAdd(
                'search', array('q' => $this->aReq['q'], 'result' => 'topics:' . $aResult['count'])
            );
            $this->oLogs->RecordEnd('search', true);
        }

        $aPaging = $this->Viewer_MakePaging(
            $aResult['count'], $this->aReq['iPage'], $this->nItemsPerPage, 4,
            Config::Get('path.root.url') . '/search/topics', array('q' => $this->aReq['q'])
        );

        $this->SetTemplateAction('results');

        // *  Отправляем данные в шаблон
        $this->Viewer_AddHtmlTitle($this->aReq['q']);
        $this->Viewer_Assign('bIsResults', $aResult['count']);
        $this->Viewer_Assign('aReq', $this->aReq);
        $this->Viewer_Assign('aRes', $aResult);
        $this->Viewer_Assign('aTopics', $aTopics);
        $this->Viewer_Assign('aPaging', $aPaging);
    }

    /**
     * Поиск комментариев
     */
    public function EventComments() {

        $this->aReq = $this->PrepareRequest('comments');

        $this->OutLog();
        if ($this->aReq['regexp']) {
            $aResult = $this->Search_GetCommentsIdByRegexp(
                $this->aReq['regexp'], $this->aReq['iPage'],
                $this->nItemsPerPage, $this->aReq['params']
            );

            if ($aResult['count'] == 0) {
                $aComments = array();
            } else {

                // * Получаем объекты по списку идентификаторов
                $aComments = $this->Comment_GetCommentsAdditionalData($aResult['collection']);

                //подсветка поисковой фразы
                foreach ($aComments AS $oComment) {
                    if ($this->nModeOutList != 'snippet') {
                        $oComment->setText($this->TextHighlite($oComment->getText()));
                    } else {
                        $oComment->setText($this->MakeSnippet($oComment->getText()));
                    }
                }
            }
        } else {
            $aResult['count'] = 0;
            $aComments = array();
        }

        // * Логгируем результаты, если требуется
        if ($this->bLogEnable) {
            $this->oLogs->RecordAdd(
                'search', array('q' => $this->aReq['q'], 'result' => 'comments:' . $aResult['count'])
            );
            $this->oLogs->RecordEnd('search', true);
        }

        $aPaging = $this->Viewer_MakePaging(
            $aResult['count'], $this->aReq['iPage'], $this->nItemsPerPage, 4,
            Config::Get('path.root.url') . '/search/comments', array('q' => $this->aReq['q'])
        );

        $this->SetTemplateAction('results');

        // *  Отправляем данные в шаблон
        $this->Viewer_AddHtmlTitle($this->aReq['q']);
        $this->Viewer_Assign('bIsResults', $aResult['count']);
        $this->Viewer_Assign('aReq', $this->aReq);
        $this->Viewer_Assign('aRes', $aResult);
        $this->Viewer_Assign('aComments', $aComments);
        $this->Viewer_Assign('aPaging', $aPaging);
    }

    /**
     * Поиск блогов
     */
    public function EventBlogs() {

        $this->aReq = $this->PrepareRequest('blogs');

        $this->OutLog();
        if ($this->aReq['regexp']) {
            $aResult = $this->Search_GetBlogsIdByRegexp(
                $this->aReq['regexp'], $this->aReq['iPage'],
                $this->nItemsPerPage, $this->aReq['params']
            );
            $aBlogs = array();

            if ($aResult['count'] > 0) {
                // * Получаем объекты по списку идентификаторов
                $aBlogs = $this->Blog_GetBlogsAdditionalData($aResult['collection']);
                //подсветка поисковой фразы
                foreach ($aBlogs AS $oBlog) {
                    if ($this->nModeOutList != 'snippet') {
                        $oBlog->setDescription($this->TextHighlite($oBlog->getDescription()));
                    } else {
                        $oBlog->setDescription($this->MakeSnippet($oBlog->getDescription()));
                    }
                }
            }
        } else {
            $aResult['count'] = 0;
            $aBlogs = array();
        }

        // * Логгируем результаты, если требуется
        if ($this->bLogEnable) {
            $this->oLogs->RecordAdd(
                'search',
                array('q' => $this->aReq['q'], 'result' => 'blogs:' . $aResult['count'])
            );
            $this->oLogs->RecordEnd('search', true);
        }

        $aPaging = $this->Viewer_MakePaging(
            $aResult['count'], $this->aReq['iPage'], $this->nItemsPerPage, 4,
            Config::Get('path.root.url') . '/search/blogs', array('q' => $this->aReq['q'])
        );

        $this->SetTemplateAction('results');

        // *  Отправляем данные в шаблон
        $this->Viewer_AddHtmlTitle($this->aReq['q']);
        $this->Viewer_Assign('bIsResults', $aResult['count']);
        $this->Viewer_Assign('aReq', $this->aReq);
        $this->Viewer_Assign('aRes', $aResult);
        $this->Viewer_Assign('aBlogs', $aBlogs);
        $this->Viewer_Assign('aPaging', $aPaging);
    }

    /**
     * Разбор запроса
     */
    protected function PrepareRequest($sType = null) {

        $sRequest = trim(F::GetRequest('q'));

        // * Иногда ломается кодировка, напр., если ввели поиск в адресной строке браузера
        // * Пытаемся восстановить по основной кодировке браузера
        if (!mb_check_encoding($sRequest)) {
            list($sCharset) = explode(',', $_SERVER['HTTP_ACCEPT_CHARSET']);
            $sQueryString = mb_convert_encoding($_SERVER['QUERY_STRING'], 'UTF-8', $sCharset);
            $sRequest = mb_convert_encoding($sRequest, 'UTF-8', $sCharset);
        }
        if ($sRequest) {
            // Две звездочки подряд меняем на одну
            $sRequest = preg_replace('/(\*{2,})/', '*', $sRequest);
            // Две пробела подряд меняем на один
            $sRequest = preg_replace('/(\s{2,})/', ' ', $sRequest);
            // Последовательность звездочек и пробелов, начинающаяся со звездочки
            $sRequest = preg_replace('/\*[\*\s]{2,}/', '* ', $sRequest);
            // Последовательность звездочек и пробелов, начинающаяся с пробела
            $sRequest = preg_replace('/\s[\*\s]{2,}/', ' *', $sRequest);
        }

        $aReq['q'] = $sRequest;
        $aReq['regexp'] = preg_quote(trim(mb_strtolower($aReq['q'])));

        // * Проверка длины запроса
        if (!F::CheckVal(
            $aReq['regexp'], 'text', Config::Get('module.search.min_length_req'),
            Config::Get('module.search.max_length_req')
        )
        ) {
            $this->Message_AddError(
                $this->Lang_Get(
                    'search_err_length', array('min' => Config::Get('module.search.min_length_req'),
                                               'max' => Config::Get('module.search.max_length_req'))
                )
            );
            $aReq['regexp'] = '';
        }

        /*
         * Проверка длины каждого слова в запросе
         * Хотя бы одно слово должно быть больше минимальной длины
         * Слова меньше минимальной длины исключаем из поиска
         */
        if ($aReq['regexp']) {
            $aWords = explode(' ', $aReq['regexp']);
            $nErr = 0;
            $sStr = '';
            foreach ($aWords as $sWord) {
                if (!F::CheckVal(
                    $sWord, 'text', Config::Get('module.search.min_length_req'),
                    Config::Get('module.search.max_length_req')
                )
                ) {
                    $nErr += 1;
                } else {
                    if ($sStr) {
                        $sStr .= ' ';
                    }
                    $sStr .= $sWord;
                }
            }
            if ($nErr == sizeof($aWords)) {
                $this->Message_AddError(
                    $this->Lang_Get(
                        'search_err_length_word', array('min' => Config::Get('module.search.min_length_req'),
                                                        'max' => Config::Get('module.search.max_length_req'))
                    )
                );
                $aReq['regexp'] = '';
            } else {
                $aReq['regexp'] = $sStr;
            }
        }

        // * Если все нормально, формируем выражение для поиска
        if ($aReq['regexp']) {
            if ($this->bSearchStrict) {
                $aReq['regexp'] = str_replace('\\*', '*', $aReq['regexp']);
                /*
                 * Проверка на "лишние" символы, оставляем только "слова"
                 * На месте "небукв" оставляем пробелы
                 */
                $aReq['regexp'] = preg_replace('/' . $this->sPatternXA . '/iusxSU', ' ', $aReq['regexp']);
                $aReq['regexp'] = trim(preg_replace('/(\s{2,})/', ' ', $aReq['regexp']));
                // * Если после "чистки" что-то осталось, то продолжаем дальше
                if ($aReq['regexp']) {
                    $aReq['regexp'] = str_replace('* *', '|', $aReq['regexp']);
                    $aReq['regexp'] = str_replace('* ', '|[[:<:]]', $aReq['regexp']);
                    $aReq['regexp'] = str_replace(' *', '[[:>:]]|', $aReq['regexp']);
                    $aReq['regexp'] = str_replace(' ', '[[:>:]]|[[:<:]]', $aReq['regexp']);

                    if (mb_substr($aReq['regexp'], 0, 1) == '*') {
                        $aReq['regexp'] = mb_substr($aReq['regexp'], 1);
                    } else {
                        $aReq['regexp'] = '[[:<:]]' . $aReq['regexp'];
                    }

                    if (mb_substr($aReq['regexp'], -1) == '*') {
                        $aReq['regexp'] = mb_substr($aReq['regexp'], 0, mb_strlen($aReq['regexp']) - 1);
                    } else {
                        $aReq['regexp'] = $aReq['regexp'] . '[[:>:]]';
                    }
                }
            } else {
                $aReq['regexp'] = preg_replace('/' . $this->sPatternXA . '/uU', '', $aReq['regexp']);
                $aReq['regexp'] = trim(preg_replace('/(\s{2,})/', ' ', $aReq['regexp']));
                $aReq['regexp'] = str_replace(' ', '|', $aReq['regexp']);
            }
        }

        $aReq['params']['bSkipTags'] = false;
        if ($sType) {
            $aReq['sType'] = $sType;
        } else {
            $aReq['sType'] = 'topics';
        }
        // * Определяем текущую страницу вывода результата
        $aReq['iPage'] = intval(preg_replace('#^page(\d+)$#', '\1', $this->getParam(0)));
        if (!$aReq['iPage']) {
            $aReq['iPage'] = 1;
        }
        // *  Передача данных в шаблонизатор
        $this->Viewer_Assign('aReq', $aReq);

        return $aReq;
    }

    public function EventShutdown() {
    }

}

// EOF