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

class ActionSearch extends Action {

    protected $aReq;
    protected $sPatternW = '[\wа-яА-Я\.\/\*\-]'; // символ слова
    protected $sPatternB = '[^\wа-яА-Я\.\/\*\-]'; // граница слова
    protected $sPatternX = '[^\s\wа-яА-Я\/\*\-]'; // запрещеные символы без *
    protected $sPatternXA = '[^\s\wа-яА-Я\/\-]'; // запрещеные символы, в т.ч. *
    protected $sPatternUrl;
    protected $sModeOutList;
    protected $nShippetLength;
    protected $nShippetMaxLength;
    protected $sSnippetBeforeMatch;
    protected $sSnippetAfterMatch;
    protected $sSnippetBeforeFragment;
    protected $sSnippetAfterFragment;
    protected $nSnippetMaxFragments;

    protected $bSearchStrict = true; // Строгий поиск
    protected $bSkipAllTags = true; // Не искать в тегах

    /** @var ITextParser */
    protected $oTextParser = null; // придется выборочно "чистить" HTML-текст

    protected $bLogEnable = false;
    protected $oUser = null;
    /** @var ModuleLogger_EntityLog */
    protected $oLogs = null;

    protected $aConfig = array();

    /** @var int */
    protected $iItemsPerPage = 25;
    /**
     * Инициализация
     */
    public function Init() {

        $this->SetDefaultEvent('index');

        $this->sModeOutList = C::Get('module.search.out_mode');

        $this->nShippetLength = C::Get('module.search.snippet.length');
        $this->nShippetMaxLength = C::Get('module.search.snippet.max_length');
        if (($this->nShippetMaxLength > 0) && ($this->nShippetMaxLength < $this->nShippetLength)) {
            $this->nShippetMaxLength = $this->nShippetLength;
        }

        $this->sSnippetBeforeMatch = C::Get('module.search.snippet.before_match');
        $this->sSnippetAfterMatch = C::Get('module.search.snippet.after_match');
        $this->sSnippetBeforeFragment = C::Get('module.search.snippet.before_fragment');
        $this->sSnippetAfterFragment = C::Get('module.search.snippet.after_fragment');
        $this->nSnippetMaxFragments = C::Get('module.search.snippet.max_fragments');

        $this->sPatternW = C::Get('module.search.char_pattern');
        $this->sPatternB = '[^' . mb_substr($this->sPatternW, 1); // '[^\wа-яА-Я\.\*-]';    // граница слова
        $this->sPatternX = '[^\s' . mb_substr($this->sPatternW, 1); // '[^\s\wа-яА-Я\*-]';  // запрещеные символы без *
        $this->sPatternXA = '[^\s\*' . mb_substr($this->sPatternW, 1); // '[^\s\wа-яА-Я-]';               // запрещеные символы, в т.ч. *

        $this->sPatternUrl = "([a-z]+\:\/\/)?"; // SCHEME
        $this->sPatternUrl .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass
        $this->sPatternUrl .= "([a-z0-9-.]*)\.([a-z]{2,4})"; // Host or IP
        $this->sPatternUrl .= "(\:[0-9]{2,5})?"; // Port
        $this->sPatternUrl .= "(\/([a-z0-9+\$_-]\.?)+)*\/?"; // Path
        $this->sPatternUrl .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query
        $this->sPatternUrl .= "(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor

        $this->bSearchStrict = C::Get('module.search.strict_search');
        $this->bSkipAllTags = C::Get('module.search.skip_all_tags');

        $this->iItemsPerPage = C::Get('module.search.items_per_page');

        mb_internal_encoding('UTF-8');

        $this->oTextParser = ModuleText::newTextParser();
        // Разрешённые теги
        if ($this->sModeOutList === 'snippet') {
            $this->oTextParser->cfgAllowTags(array('a', 'img', 'object', 'param', 'embed'));
        } else {
            $this->oTextParser->cfgAllowTags(array('a', 'img', 'object', 'param', 'embed'));
        }
        // Коротие теги типа
        $this->oTextParser->cfgSetTagShort(array('img'));
        // Разрешённые параметры тегов
        $this->oTextParser->cfgAllowTagParams(
            'img',
            array('src', 'alt' => '#text', 'title', 'align' => array('right', 'left', 'center'), 'width' => '#int',
                  'height'     => '#int', 'hspace' => '#int', 'vspace' => '#int')
        );
        $this->oTextParser->cfgAllowTagParams('a', array('title', 'href', 'rel'));
        $this->oTextParser->cfgAllowTagParams('object', array('width' => '#int', 'height' => '#int', 'data' => '#link'));
        $this->oTextParser->cfgAllowTagParams('param', array('name' => '#text', 'value' => '#text'));
        $this->oTextParser->cfgAllowTagParams(
            'embed',
            array('src' => '#image', 'type' => '#text', 'allowscriptaccess' => '#text', 'allowfullscreen' => '#text',
                  'width' => '#int', 'height' => '#int', 'flashvars' => '#text', 'wmode' => '#text')
        );
        // Параметры тегов являющиеся обязательными
        $this->oTextParser->cfgSetTagParamsRequired('img', 'src');
        $this->oTextParser->cfgSetTagParamsRequired('a', 'href');
        // Теги которые необходимо вырезать из текста вместе с контентом
        $this->oTextParser->cfgSetTagCutWithContent(array('script', 'iframe', 'style'));
        // Вложенные теги
        $this->oTextParser->cfgSetTagChilds('object', 'param', false, true);
        $this->oTextParser->cfgSetTagChilds('object', 'embed', false, false);
        // Отключение авто-добавления <br>
        $this->oTextParser->cfgSetAutoBrMode(true);

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

    protected function _statKey() {

        return 'module.search.last_queries.ip_' . F::GetUserIp();
    }

    /**
     * @param array $aLastSearchQueries
     */
    protected function _statSave($aLastSearchQueries) {

        $sData = F::Serialize($aLastSearchQueries, true);
        if (C::Get('module.search.limit.check_ip')) {
            if (E::ModuleCache()->CacheTypeAvailable('memory')) {
                $sCacheType = 'memory';
            } else {
                $sCacheType = 'file';
            }
            E::ModuleCache()->Set($sData, $this->_statKey(), array(), false, ',' . $sCacheType);
        } else {
            E::ModuleSession()->Set('last_search_queries', $sData);
        }
    }

    /**
     * @return array
     */
    protected function _statLoad() {

        if (C::Get('module.search.limit.check_ip')) {
            if (E::ModuleCache()->CacheTypeAvailable('memory')) {
                $sCacheType = 'memory';
            } else {
                $sCacheType = 'file';
            }
            $sData = E::ModuleCache()->Get($this->_statKey(), $sCacheType . ',');
        } else {
            $sData = E::ModuleSession()->Get('last_search_queries');
        }
        if (false === $sData) {
            $aLastSearchQueries = array();
        } else {
            $aLastSearchQueries = F::Unserialize($sData, array());
        }
        return $aLastSearchQueries;
    }

    /**
     * @return bool
     */
    protected function _checkLimits() {

        $iLimitQueries = (int)(C::Get('module.search.limit.queries'));
        $iLimitPeriod = F::ToSeconds(C::Get('module.search.limit.period'));
        $iLimitInterval = F::ToSeconds(C::Get('module.search.limit.interval'));

        if (!F::GetRequest('q') || !$iLimitQueries || !$iLimitPeriod) {
            return true;
        }

        $aLastSearchQueries = $this->_statLoad();
        $iCount = 0;
        if (count($aLastSearchQueries) > 0) {
            $iTimeLimit = time() - $iLimitPeriod;
            foreach($aLastSearchQueries as $iIndex => $aQuery) {
                if ($aQuery['time'] >= $iTimeLimit) {
                    ++$iCount;
                }
            }
            $aLastQuery = end($aLastSearchQueries);
        } else {
            $aLastQuery = null;
        }
        if (count($aLastSearchQueries) > $iLimitQueries) {
            $aLastSearchQueries = array_slice($aLastSearchQueries, -$iLimitQueries);
        }
        $aLastSearchQueries[] = array(
            'time' => time(),
            'query' => F::GetRequest('q'),
        );

        $this->_statSave($aLastSearchQueries);

        if ($iCount > $iLimitQueries) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('search_err_frequency', array('num' => $iLimitQueries, 'sec' => $iLimitPeriod)));
            return false;
        }
        if (!empty($aLastQuery['time']) && $iLimitInterval && ($aLastQuery['time'] > time() - $iLimitInterval)) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('search_err_interval', array('sec' => $iLimitInterval)));
            return false;
        }

        return true;
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

        if (!($sLogFile = C::Get('module.search.logs.file'))) {
            $sLogFile = 'search.log';
        }
        if (!$this->oUser) {
            if (($sUserId = E::ModuleSession()->Get('user_id'))) {
                $this->oUser = E::ModuleUser()->GetUserById($sUserId);
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
        if (is_array($aVars) && count($aVars)) {
            foreach ($aVars as $key => $val) {
                $sStrLog .= "\n" . str_repeat(' ', 22) . $key . '=>"' . $val . '"';
            }
        }

        E::ModuleLogger()->Dump($sLogFile, $sStrLog);
    }

    /**
     * Преобразование RegExp-а к стандарту PHP
     *
     * @return string
     */
    protected function _preparePattern() {

        if ($this->bSearchStrict) {
            $sRegexp = $this->aReq['regexp'];
            $aWords = explode('|', $sRegexp);
            foreach($aWords as $iIndex => $sWord) {
                if (substr($sWord, 0, 7) == '[[:<:]]') {
                    $sWord = substr($sWord, 7);
                } else {
                    $sWord = '[\p{L}\p{Nd}]+' . $sWord;
                }
                if (substr($sWord, -7) == '[[:>:]]') {
                    $sWord = substr($sWord, 0, strlen($sWord) - 7);
                } else {
                    $sWord = $sWord . '[\p{L}\p{Nd}]+';
                }
                $aWords[$iIndex] = $sWord;
            }

            if (count($aWords) == 1) {
                $sRegexp = reset($aWords);
            } else {
                $sRegexp = implode('|', $aWords);
            }
        } else {
            $sRegexp = $this->aReq['regexp'];
        }

        $sRegexp = preg_replace('/\s+/', '\\s+', $sRegexp);
        $sRegexp = '/' . $sRegexp . '/iusxSU';

        return $sRegexp;
    }

    /**
     * "Подсветка" текста
     *
     * @param string $sText
     *
     * @return string
     */
    protected function _textHighlite($sText) {

        $sRegexp = $this->_preparePattern();
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
    protected function _makeSnippetFragment($sText, $aSet, $nPos, $nLen) {

        $nLenWord = $nLen;
        $nLenText = mb_strlen($sText);

        $nShippetOffset = floor(($this->nShippetLength - $nLenWord) / 2);

        // начало фрагмена
        if ($nPos < $nShippetOffset) {
            $nFragBegin = 0;
        } else {
            $nFragBegin = $nPos - $nShippetOffset;
        }

        // конец фрагмента
        if ($nPos + $nLenWord + $nShippetOffset > $nLenText) {
            $nFragEnd = $nLenText;
        } else {
            $nFragEnd = $nPos + $nLenWord + $nShippetOffset;
        }

        // Выравнивание по границе слов
        $sPattern = '/' . $this->sPatternW . '+$/uisxSXU';
        if (($nFragBegin > 0) && mb_preg_match($sPattern, mb_substr($sText, 0, $nFragBegin), $m, PREG_OFFSET_CAPTURE)) {
            $nFragBegin -= mb_strlen($m[0][0]);
        }

        $sPattern = '/^' . $this->sPatternW . '+/uisxSXU';
        if (($nFragEnd < $nLenText) && mb_preg_match($sPattern, mb_substr($sText, $nFragEnd), $m, PREG_OFFSET_CAPTURE)) {
            $nFragEnd += mb_strlen($m[0][0]) + $m[0][1];
        }

        // Обрезание по максимальной длине
        if (($this->nShippetMaxLength > 0) && (($nOver = $nFragEnd - $nFragBegin - $this->nShippetMaxLength) > 0)) {
            $nFragBegin -= floor($nOver / 2);
            if ($nFragBegin < 0) {
                $nFragBegin = 0;
            }
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
        $iBegin = $nFragBegin;
        foreach ($aSet as $aWord) {
            $iWordPos = $aWord['pos'];
            $sFragment .= str_replace('>', '&gt;', str_replace('<', '&lt;', mb_substr($sText, $iBegin, $iWordPos - $iBegin)));
            $sFragment .= $this->sSnippetBeforeMatch . $aWord['txt'] . $this->sSnippetAfterMatch;
            $iBegin = $iWordPos + $aWord['len'];
        }
        $sFragment .= str_replace('>', '&gt;', str_replace('<', '&lt;', mb_substr($sText, $iBegin, $nFragEnd - $iBegin)));

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
    protected function _makeSnippet($sText) {

        $aError = array();
        $sRegexp = $this->_preparePattern();
        // * Если задано, то вырезаем все теги
        if ($this->bSkipAllTags) {
            $sText = strip_tags($sText);
        } else {
            $sText = $this->oTextParser->parse($sText, $aError);
            $sText = str_replace('<br/>', '', $sText);
        }

        //$sText = str_replace(' ', '  ', $sText);
        if (mb_preg_match_all($sRegexp, $sText, $aMatches, PREG_OFFSET_CAPTURE)) {
            // * Создаем набор фрагментов текста
            $sSnippet = '';
            $aFragmentSets = array();
            $nFragmentSetsCount = -1;
            $nCount = 0;
            $aLastSet = array();
            $nLastLen = 0;
            foreach ($aMatches[0] as $aMatch) {
                $sFrTxt = $aMatch[0];
                $nFrPos = $aMatch[1];
                $nFrLen = mb_strlen($sFrTxt);
                // Создаем сеты фрагментов, чтобы близлежащие слова попали в один сет
                if (($nFragmentSetsCount == -1) || $nLastLen == 0) {
                    $aLastSet = array('txt' => $sFrTxt, 'pos' => $nFrPos, 'len' => $nFrLen);
                    $nLastLen = $nFrPos + $nFrLen;
                    $aFragmentSets[++$nFragmentSetsCount][] = $aLastSet;
                } else {
                    if (($nFrPos + $nFrLen - $aLastSet['pos']) < $this->nShippetLength) {
                        $aFragmentSets[$nFragmentSetsCount][] = array(
                            'txt' => $sFrTxt,
                            'pos' => $nFrPos,
                            'len' => $nFrLen,
                            );
                        $nLastLen = $nFrPos + $nFrLen - $aLastSet['pos'];
                    } else {
                        $aLastSet = array('txt' => $sFrTxt, 'pos' => $nFrPos, 'len' => $nFrLen);
                        $nLastLen = $nFrPos + $nFrLen;
                        $aFragmentSets[++$nFragmentSetsCount][] = $aLastSet;
                    }
                }
            }

            $aFragments = array();
            $nPos = 0;
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

                $aFragments[] = $this->_makeSnippetFragment($sText, $aSet, $nPos, $nLen);
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
     * @return mixed|null
     */
    public function ExecEvent() {

        if (!$this->_checkLimits()) {
            return $this->OverLimit();
        }
        return parent::ExecEvent();
    }

    /**
     * Обработка основного события
     *
     */
    public function EventIndex() {

        $sEvent = R::GetActionEvent();

        if ((!$sEvent || $sEvent =='index') && F::GetRequestStr('q', null, 'get')) {
            $sEvent = 'topics';
        }
        if ($sEvent == 'comments') {
            return $this->EventComments();
        } elseif ($sEvent == 'blogs') {
            return $this->EventBlogs();
        } elseif ($sEvent == 'topics') {
            return $this->EventTopics();
        } else {
            $this->SetTemplateAction('index');
            $this->_addHtmlTitle();
        }
    }

    /**
     * Поддержка OpenSearch
     *
     */
    public function EventOpensearch() {

        header('Content-type: text/xml; charset=utf-8');
        echo E::ModuleViewer()->Fetch("tpls/actions/search/action.search.opensearch.tpl");
        exit;

    }

    /**
     * Поиск топиков
     */
    public function EventTopics() {

        $this->aReq = $this->_prepareRequest('topics');
        $this->OutLog();
        if (!empty($this->aReq['regexp'])) {
            $aResult = E::ModuleSearch()->GetTopicsIdByRegexp(
                $this->aReq['regexp'], $this->aReq['iPage'],
                $this->iItemsPerPage, $this->aReq['params'],
                C::Get('module.search.accessible')
            );

            $aTopics = array();
            if ($aResult['count'] > 0) {
                $aTopicsFound = E::ModuleTopic()->GetTopicsAdditionalData($aResult['collection']);

                // * Подсветка поисковой фразы в тексте или формирование сниппета
                foreach ($aTopicsFound AS $oTopic) {
                    if ($oTopic && $oTopic->getBlog()) {
                        if ($this->sModeOutList == 'short') {
                            $oTopic->setTextShort($this->_textHighlite($oTopic->getTextShort()));
                        } elseif ($this->sModeOutList == 'full') {
                            $oTopic->setTextShort($this->_textHighlite($oTopic->getText()));
                        } else {
                            $oTopic->setTextShort($this->_makeSnippet($oTopic->getText()));
                        }
                        $oTopic->setBlogTitle($oTopic->getBlog()->getTitle());
                        $aTopics[] = $oTopic;
                    }
                }
            }
            if ($this->bLogEnable) {
                $this->oLogs->RecordAdd(
                    'search', array('q' => $this->aReq['q'], 'result' => 'topics:' . $aResult['count'])
                );
                $this->oLogs->RecordEnd('search', true);
            }

            $aPaging = E::ModuleViewer()->MakePaging(
                $aResult['count'], $this->aReq['iPage'], $this->iItemsPerPage, 4,
                C::Get('path.root.url') . '/search/topics', array('q' => $this->aReq['q'])
            );

        } else {
            $aResult['count'] = 0;
            $aTopics = array();
            $aPaging = array();
        }

        $this->SetTemplateAction('results');

        $aRes = array(
            'aCounts' => array(
                'topics' => $aResult['count'],
                'comments' => null,
            ),
        );

        // *  Отправляем данные в шаблон
        $this->_addHtmlTitle();
        E::ModuleViewer()->Assign('bIsResults', !empty($aResult['count']));
        E::ModuleViewer()->Assign('aRes', $aRes);
        E::ModuleViewer()->Assign('aTopics', $aTopics);
        E::ModuleViewer()->Assign('aPaging', $aPaging);
    }

    /**
     * Поиск комментариев
     */
    public function EventComments() {

        $this->aReq = $this->_prepareRequest('comments');

        $this->OutLog();
        if (!empty($this->aReq['regexp'])) {
            $aResult = E::ModuleSearch()->GetCommentsIdByRegexp(
                $this->aReq['regexp'], $this->aReq['iPage'],
                $this->iItemsPerPage, $this->aReq['params']
            );

            if ($aResult['count'] == 0) {
                $aComments = array();
            } else {

                // * Получаем объекты по списку идентификаторов
                $aComments = E::ModuleComment()->GetCommentsAdditionalData($aResult['collection']);

                //подсветка поисковой фразы
                foreach ($aComments AS $oComment) {
                    if ($this->sModeOutList != 'snippet') {
                        $oComment->setText($this->_textHighlite($oComment->getText()));
                    } else {
                        $oComment->setText($this->_makeSnippet($oComment->getText()));
                    }
                }
            }
            // * Логгируем результаты, если требуется
            if ($this->bLogEnable) {
                $this->oLogs->RecordAdd(
                    'search', array('q' => $this->aReq['q'], 'result' => 'comments:' . $aResult['count'])
                );
                $this->oLogs->RecordEnd('search', true);
            }

            $aPaging = E::ModuleViewer()->MakePaging(
                $aResult['count'], $this->aReq['iPage'], $this->iItemsPerPage, 4,
                C::Get('path.root.url') . '/search/comments', array('q' => $this->aReq['q'])
            );

        } else {
            $aResult['count'] = 0;
            $aComments = array();
            $aPaging = array();
        }

        $this->SetTemplateAction('results');

        $aRes = array(
            'aCounts' => array(
                'topics' => null,
                'comments' => $aResult['count'],
            ),
        );

        // *  Отправляем данные в шаблон
        $this->_addHtmlTitle();
        E::ModuleViewer()->Assign('bIsResults', !empty($aResult['count']));
        E::ModuleViewer()->Assign('aRes', $aRes);
        E::ModuleViewer()->Assign('aComments', $aComments);
        E::ModuleViewer()->Assign('aPaging', $aPaging);
    }

    /**
     * Поиск блогов
     */
    public function EventBlogs() {

        $this->aReq = $this->_prepareRequest('blogs');

        $this->OutLog();
        if (!empty($this->aReq['regexp'])) {
            $aResult = E::ModuleSearch()->GetBlogsIdByRegexp(
                $this->aReq['regexp'], $this->aReq['iPage'],
                $this->iItemsPerPage, $this->aReq['params']
            );
            $aBlogs = array();

            if ($aResult['count'] > 0) {
                // * Получаем объекты по списку идентификаторов
                $aBlogs = E::ModuleBlog()->GetBlogsAdditionalData($aResult['collection']);
                //подсветка поисковой фразы
                foreach ($aBlogs AS $oBlog) {
                    if ($this->sModeOutList !== 'snippet') {
                        $oBlog->setDescription($this->_textHighlite($oBlog->getDescription()));
                    } else {
                        $oBlog->setDescription($this->_makeSnippet($oBlog->getDescription()));
                    }
                }
            }
            // * Логгируем результаты, если требуется
            if ($this->bLogEnable) {
                $this->oLogs->RecordAdd(
                    'search',
                    array('q' => $this->aReq['q'], 'result' => 'blogs:' . $aResult['count'])
                );
                $this->oLogs->RecordEnd('search', true);
            }

            $aPaging = E::ModuleViewer()->MakePaging(
                $aResult['count'], $this->aReq['iPage'], $this->iItemsPerPage, 4,
                C::Get('path.root.url') . '/search/blogs', array('q' => $this->aReq['q'])
            );

        } else {
            $aResult['count'] = 0;
            $aBlogs = array();
            $aPaging = array();
        }

        $this->SetTemplateAction('results');

        // *  Отправляем данные в шаблон
        $this->_addHtmlTitle();
        E::ModuleViewer()->Assign('bIsResults', $aResult['count']);
        E::ModuleViewer()->Assign('aRes', $aResult);
        E::ModuleViewer()->Assign('aBlogs', $aBlogs);
        E::ModuleViewer()->Assign('aPaging', $aPaging);
    }

    /**
     * Content of tag <title>
     */
    protected function _addHtmlTitle() {

        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('search'));
        if (!empty($this->aReq['q'])) {
            E::ModuleViewer()->AddHtmlTitle($this->aReq['q']);
        }
    }

    /**
     * Разбор запроса
     *
     * @param null $sType
     *
     * @return mixed
     */
    protected function _prepareRequest($sType = null) {

        $sRequest = trim(F::GetRequest('q'));
        if (!$sRequest) {
            $iMin = C::Get('module.search.min_length_req');
            if (!$iMin || $iMin < 1) {
                $iMin = 1;
            }
            E::ModuleMessage()->AddError(
                E::ModuleLang()->Get(
                    'search_err_length', array('min' => $iMin,
                                               'max' => C::Get('module.search.max_length_req'))
                )
            );
            $aReq = array('regexp' => '', 'q' => '');

            return $aReq;
        }

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

        $iCountWords = preg_match_all('/("[^"]*"|[\S]+)/', $sRequest, $aM);
        $aWords = $aM[0];
        if ($iMaxWords = C::Get('module.search.limit.max_words')) {
            if ($iCountWords > $iMaxWords) {
                $aM[0] = array_slice($aM[0], 0, $iMaxWords);
                E::ModuleMessage()->AddNotice(
                    E::ModuleLang()->Get(
                        'search_err_count_words', array('max' => $iMaxWords)
                    ), true
                );
            }
        }

        foreach($aWords as $iIdx => $sWord) {
            if (preg_match('/^' . $this->sPatternUrl . '/', $sWord)) {
                $aWords[$iIdx] = '"' . $sWord . '"';
            }
        }

        $sRequest = implode(' ', $aWords);

        $aReq['q'] = $sRequest;
        $aReq['regexp'] = preg_quote(trim(mb_strtolower($aReq['q'])), '/');

        // * Проверка длины запроса
        if (!F::CheckVal(
            $aReq['regexp'], 'text', C::Get('module.search.min_length_req'),
            C::Get('module.search.max_length_req')
        )) {
            E::ModuleMessage()->AddError(
                E::ModuleLang()->Get(
                    'search_err_length', array('min' => C::Get('module.search.min_length_req'),
                                               'max' => C::Get('module.search.max_length_req'))
                )
            );
            $aReq['regexp'] = '';
        }

        // Save quoted substrings
        $aQuoted = array();
        if (preg_match_all('/"(\\\\\*)?([^"]+)(\\\\\*)?"/U', $aReq['regexp'], $aMatches)) {
            foreach($aMatches[2] as $sStr) {
                $sSubstKey = 'begin-' . md5($sStr) . '-end';
                $aQuoted[0][] = $sSubstKey;
                $aQuoted[1][] = $sStr;
            }
            $aReq['regexp'] = str_replace($aQuoted[1], $aQuoted[0], $aReq['regexp']);
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
                    $sWord, 'text', C::Get('module.search.min_length_req'),
                    C::Get('module.search.max_length_req')
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
            if ($nErr == count($aWords)) {
                E::ModuleMessage()->AddError(
                    E::ModuleLang()->Get(
                        'search_err_length_word', array('min' => C::Get('module.search.min_length_req'),
                                                        'max' => C::Get('module.search.max_length_req'))
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
                if (strlen($aReq['regexp']) > 0) {
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

        // Restore quoted substrings
        if ($aReq['regexp'] && !empty($aQuoted[0])) {
            $aReq['regexp'] = str_replace($aQuoted[0], $aQuoted[1], $aReq['regexp']);
        }

        $aReq['params']['bSkipTags'] = false;
        if ($sType) {
            $aReq['sType'] = $sType;
        } else {
            $aReq['sType'] = 'topics';
        }
        // * Определяем текущую страницу вывода результата
        $aReq['iPage'] = (int)(preg_replace('#^page(\d+)$#', '\1', $this->getParam(0)));
        if (!$aReq['iPage']) {
            $aReq['iPage'] = 1;
        }

        return $aReq;
    }

    public function OverLimit() {

        $this->aReq = $this->_prepareRequest();

        return null;
    }

    public function EventShutdown() {
        // *  Передача данных в шаблонизатор
        E::ModuleViewer()->Assign('aReq', $this->aReq);
    }

}

// EOF