<?php

class Qevix
{
	const NIL = 0x0;
	const PRINATABLE = 0x1;
	const ALPHA = 0x2;
	const NUMERIC = 0x4;
	const PUNCTUATUON = 0x8;
	const SPACE = 0x10;
	const NL = 0x20;
	const TAG_NAME = 0x40;
	const TAG_PARAM_NAME = 0x80;
	const TAG_QUOTE = 0x100;
	const TEXT_QUOTE = 0x200;
	const TEXT_BRACKET = 0x400;
	const SPECIAL_CHAR = 0x800;
	//const = 0x1000;
	//const = 0x2000;
	//const = 0x4000;
	//const = 0x8000;
	const NOPRINT = 0x10000;

	public $tagsRules = array();
	public $entities = array('"'=>'&#34;', "'"=>'&#39;', '<'=>'&#60;', '>'=>'&#62;', '&'=>'&#38;');
	public $quotes = array(array('«', '»'), array('„', '“'));
	public $bracketsALL = array('<'=>'>', '['=>']', '{'=>'}', '('=>')');
	public $bracketsSPC = array('['=>']', '{'=>'}');
	public $dash = "—";
	public $nl = "\n";

	protected $textBuf = array();
	protected $textLen = 0;

	protected $prevPos = -1;
	protected $prevChar = null;
	protected $prevCharOrd = 0;
	protected $prevCharClass = self::NIL;

	protected $curPos = -1;
	protected $curChar = null;
	protected $curCharOrd = 0;
	protected $curCharClass = self::NIL;

	protected $nextPos = -1;
	protected $nextChar = null;
	protected $nextCharOrd = 0;
	protected $nextCharClass = self::NIL;

	protected $curTag = null;
	protected $statesStack = array();
	protected $quotesOpened = 0;
	protected $linkProtocolAllow = array('http','https','ftp');
	protected $specialChars = array();
	protected $isXHTMLMode = false;
	protected $isAutoBrMode = true;
	protected $isAutoLinkMode = true;
	protected $isSpecialCharMode = false;
	protected $typoMode = true;
	protected $br = "<br>";

	protected $errorsList = array();


	/**
	 * Классификация тегов
	 */
	const TAG_ALLOWED = 1; // Тег допустим
	const TAG_PARAM_ALLOWED = 2; // Параметр тега допустим
	const TAG_PARAM_REQUIRED = 3; // Параметр тега является необходимым
	const TAG_SHORT = 4; // Тег короткий
	const TAG_CUT = 5; // Тег необходимо вырезать вместе с его контентом
	const TAG_GLOBAL_ONLY = 6; // Тег может находиться только в "глобальной" области видимости (не быть дочерним к другим)
	const TAG_PARENT_ONLY = 7; // Тег может содержать только другие теги
	const TAG_CHILD_ONLY = 8; // Тег может находиться только внутри других тегов
	const TAG_PARENT = 9; // Тег родитель относительно дочернего тега
	const TAG_CHILD = 10; // Тег дочерний относительно родительского
	const TAG_PREFORMATTED = 11; // Преформатированные теги
	const TAG_PARAM_AUTO_ADD = 12; // Автодобавление параметров со значениями по умолчанию
	const TAG_NO_TYPOGRAPHY = 13; // Тег с отключенным типографированием
	const TAG_EMPTY = 14; // Пустой не короткий тег
	const TAG_NO_AUTO_BR = 15; // Тег в котором не нужна авто-расстановка <br>
	const TAG_BLOCK_TYPE = 16; // Тег после которого нужно удалять один перевод строки
	const TAG_BUILD_CALLBACK = 17; // Тег обрабатывается и строится callback-функцией
	const TAG_EVENT_CALLBACK = 18; // Тег обрабатывается callback-функцией для сбора информации

	/**
	 * Классы символов из symbolclass.php
	 */
	protected $charClasses = array(0=>65536,1=>65536,2=>65536,3=>65536,4=>65536,5=>65536,6=>65536,7=>65536,8=>65536,9=>16,10=>32,11=>65536,12=>65536,13=>32,14=>65536,15=>65536,16=>65536,17=>65536,18=>65536,19=>65536,20=>65536,21=>65536,22=>65536,23=>65536,24=>65536,25=>65536,26=>65536,27=>65536,28=>65536,29=>65536,30=>65536,31=>65536,32=>16,97=>195,98=>195,99=>195,100=>195,101=>195,102=>195,103=>195,104=>195,105=>195,106=>195,107=>195,108=>195,109=>195,110=>195,111=>195,112=>195,113=>195,114=>195,115=>195,116=>195,117=>195,118=>195,119=>195,120=>195,121=>195,122=>195,65=>195,66=>195,67=>195,68=>195,69=>195,70=>195,71=>195,72=>195,73=>195,74=>195,75=>195,76=>195,77=>195,78=>195,79=>195,80=>195,81=>195,82=>195,83=>195,84=>195,85=>195,86=>195,87=>195,88=>195,89=>195,90=>195,48=>197,49=>197,50=>197,51=>197,52=>197,53=>197,54=>197,55=>197,56=>197,57=>197,34=>769,39=>257,46=>9,44=>9,33=>9,63=>9,58=>9,59=>9,60=>1025,62=>1025,91=>1025,93=>1025,123=>1025,125=>1025,40=>1025,41=>1025,64=>2049,35=>2049,36=>2049);

	/**
	 * Установка конфигурации для одного или нескольких тегов
	 *
	 * @param array|string $tags тег(и)
	 * @param int $flag флаг конфигурации
	 * @param mixed $value значение флага
	 * @param boolean $createIfNoExists создать запить о теге, если он ещё не определён
	 */
	protected function _cfgSetTagsFlag($tags, $flag, $value, $createIfNoExists = true)
	{
		$tags = (is_array($tags)) ? $tags : array($tags);

		foreach($tags as $tag)
		{
			if(!isset($this->tagsRules[$tag]) AND !$createIfNoExists) {
				throw new Exception("Тег ".$tag." отсутствует в списке разрешённых тегов");
			}

			$this->tagsRules[$tag][$flag] = $value;
		}
	}

	/**
	 * КОНФИГУРАЦИЯ: Задает список разрешенных тегов
	 *
	 * @param array|string $tags тег(и)
	 */
	public function cfgAllowTags($tags) {
		$this->_cfgSetTagsFlag($tags, self::TAG_ALLOWED, true);
	}

	/**
	 * КОНФИГУРАЦИЯ: Указывает, какие теги считать короткими (<br>, <img>)
	 *
	 * @param array|string $tags тег(и)
	 */
	public function cfgSetTagShort($tags) {
		$this->_cfgSetTagsFlag($tags, self::TAG_SHORT, true, false);
	}

	/**
	 * КОНФИГУРАЦИЯ: Указывает преформатированные теги, в которых нужно всё заменять на HTML сущности
	 *
	 * @param array|string $tags тег(и)
	 */
	public function cfgSetTagPreformatted($tags) {
		$this->_cfgSetTagsFlag($tags, self::TAG_PREFORMATTED, true, false);
	}

	/**
	 * КОНФИГУРАЦИЯ: Указывает теги в которых нужно отключить типографирование текста
	 *
	 * @param array|string $tags тег(и)
	 */
	public function cfgSetTagNoTypography($tags) {
		$this->_cfgSetTagsFlag($tags, self::TAG_NO_TYPOGRAPHY, true, false);
	}

	/**
	 * КОНФИГУРАЦИЯ: Указывает не короткие теги, которые могут быть пустыми и их не нужно из-за этого удалять
	 *
	 * @param array|string $tags тег(и)
	 */
	public function cfgSetTagIsEmpty($tags) {
		$this->_cfgSetTagsFlag($tags, self::TAG_EMPTY, true, false);
	}

	/**
	 * КОНФИГУРАЦИЯ: Указывает теги внутри которых не нужна авторасстановка тегов перевода на новую строку
	 *
	 * @param array|string $tags тег(и)
	 */
	public function cfgSetTagNoAutoBr($tags) {
		$this->_cfgSetTagsFlag($tags, self::TAG_NO_AUTO_BR, true, false);
	}

	/**
	 * КОНФИГУРАЦИЯ: Указывает теги, которые необходимо вырезать вместе с содержимым (style, script, iframe)
	 *
	 * @param array|string $tags тег(и)
	 */
	public function cfgSetTagCutWithContent($tags) {
		$this->_cfgSetTagsFlag($tags, self::TAG_CUT, true);
	}

	/**
 	 * КОНФИГУРАЦИЯ: Указывает теги после которых не нужно добавлять дополнительный перевод строки, например, блочные теги
	 *
 	 * @param array|string $tags тег(и)
 	 */
	public function cfgSetTagBlockType($tags) {
		$this->_cfgSetTagsFlag($tags, self::TAG_BLOCK_TYPE, true, false);
	}

	/**
	 * КОНФИГУРАЦИЯ: Добавляет разрешенные параметры для тегов
	 *
	 * @param string $tag тег
	 * @param string|array $params разрешённые параметры
	 */
	public function cfgAllowTagParams($tag, $params)
	{
		if(!isset($this->tagsRules[$tag])) {
			throw new Exception("Тег ".$tag." отсутствует в списке разрешённых тегов");
		}

		$params = (is_array($params)) ? $params : array($params);

		foreach($params as $key => $value)
		{
			if(is_string($key)) {
				$this->tagsRules[$tag][self::TAG_PARAM_ALLOWED][$key] = $value;
			} else {
				$this->tagsRules[$tag][self::TAG_PARAM_ALLOWED][$value] = '#text';
			}
		}
	}

	/**
	 * КОНФИГУРАЦИЯ: Добавляет обязательные параметры для тега
	 *
	 * @param string $tag тег
	 * @param string|array $params разрешённые параметры
	 */
	public function cfgSetTagParamsRequired($tag, $params)
	{
		if(!isset($this->tagsRules[$tag])) {
			throw new Exception("Тег ".$tag." отсутствует в списке разрешённых тегов");
		}

		$params = (is_array($params)) ? $params : array($params);

		foreach($params as $param)
		{
			$this->tagsRules[$tag][self::TAG_PARAM_REQUIRED][$param] = true;
		}
	}

	/* КОНФИГУРАЦИЯ: Указывает, какие теги являются контейнерами для других тегов
	 *
	 * @param string $tag тег
	 * @param string|array $childs разрешённые дочерние теги
	 * @param boolean $isParentOnly тег является только контейнером других тегов и не может содержать текст
	 * @param boolean $isChildOnly вложенные теги не могут присутствовать нигде кроме указанного тега
	 */
	public function cfgSetTagChilds($tag, $childs, $isParentOnly = false, $isChildOnly = false)
	{
		if(!isset($this->tagsRules[$tag])) {
			throw new Exception("Тег ".$tag." отсутствует в списке разрешённых тегов");
		}

		$childs = (is_array($childs)) ? $childs : array($childs);

		if($isParentOnly) {
			$this->tagsRules[$tag][self::TAG_PARENT_ONLY] = true;
		}

		foreach($childs as $child)
		{
			if(!isset($this->tagsRules[$child])) {
				throw new Exception("Тег ".$child." отсутствует в списке разрешённых тегов");
			}

			$this->tagsRules[$tag][self::TAG_CHILD][$child] = true;
			$this->tagsRules[$child][self::TAG_PARENT][$tag] = true;

			if($isChildOnly) {
				$this->tagsRules[$child][self::TAG_CHILD_ONLY] = true;
			}
		}
	}

	/* КОНФИГУРАЦИЯ: Указывает, какие теги не должны быть дочерними к другим тегам
	 *
	 * @param string|array $tag тег
	 */
	public function cfgSetTagGlobal($tags)
	{
		$this->_cfgSetTagsFlag($tags, self::TAG_GLOBAL_ONLY, true, false);
	}

	/**
	 * КОНФИГУРАЦИЯ: Указывает значения по умолчанию для параметров тега
	 *
	 * @param string $tag тег
	 * @param string $param атрибут
	 * @param string $value значение
	 * @param boolean $isRewrite перезаписывать значение значением по умолчанию
	 */
	public function cfgSetTagParamDefault($tag, $param, $value, $isRewrite = false)
	{
		if(!isset($this->tagsRules[$tag])) {
			throw new Exception("Тег ".$tag." отсутствует в списке разрешённых тегов");
		}

		$this->tagsRules[$tag][self::TAG_PARAM_AUTO_ADD][$param] = array('value'=>$value, 'rewrite'=>$isRewrite);
	}

	/**
	 * КОНФИГУРАЦИЯ: Устанавливает на тег callback-функцию для построения тега
	 * @param string $tag тег
	 * @param mixed $callback функция
	 */
	public function cfgSetTagBuildCallback($tag, $callback)
	{
		if(!isset($this->tagsRules[$tag])) {
			throw new Exception("Тег ".$tag." отсутствует в списке разрешённых тегов");
		}

		$this->tagsRules[$tag][self::TAG_BUILD_CALLBACK] = $callback;
	}

	/**
	 * КОНФИГУРАЦИЯ: Устанавливает на тег callback-функцию для сбора информации
	 * @param string $tag тег
	 * @param mixed $callback функция
	 */
	public function cfgSetTagEventCallback($tag, $callback)
	{
		if(!isset($this->tagsRules[$tag])) {
			throw new Exception("Тег ".$tag." отсутствует в списке разрешённых тегов");
		}

		$this->tagsRules[$tag][self::TAG_EVENT_CALLBACK] = $callback;
	}

	/**
	 * КОНФИГУРАЦИЯ: Устанавливает на строку предварённую спецсимволом callback-функцию
	 * @param string $char спецсимвол
	 * @param mixed $callback функция
	 */
	public function cfgSetSpecialCharCallback($char, $callback)
	{
		if(!is_string($char)) {
			throw new Exception("Параметр \$char метода cfgSetSpecialCharCallback должен быть строкой из одного символа");
		}

		if(mb_strlen($char) != 1) {
			throw new Exception("Параметр \$char метода cfgSetSpecialCharCallback должен быть строкой из одного символа");
		}

		$charClass = $this->getClassByOrd($this->ord($char));

		if(($charClass & self::SPECIAL_CHAR) == self::NIL) {
			throw new Exception("Параметр \$char метода cfgSetSpecialCharCallback отсутствует в списке разрешенных символов");
		}

		$this->isSpecialCharMode = true;

		$this->specialChars[$char] = $callback;
	}

	/**
	 * КОНФИГУРАЦИЯ: Устанавливает список разрешенных протоколов для ссылок (https, http, ftp)
	 *
	 * @param array $protocols Список протоколов
	 */
	public function cfgSetLinkProtocolAllow($protocols)
	{
		$protocols = (is_array($protocols)) ? $protocols : array($protocols);

		$this->linkProtocolAllow = $protocols;
	}

	/**
	 * КОНФИГУРАЦИЯ: Включает или выключает режим XHTML
	 *
	 * @param boolean $isXHTMLMode
	 */
	public function cfgSetXHTMLMode($isXHTMLMode)
	{
		$isXHTMLMode = (bool) $isXHTMLMode;

		$this->br = ($isXHTMLMode) ? '<br/>' : '<br>';
		$this->isXHTMLMode = $isXHTMLMode;
	}

	/**
	 * КОНФИГУРАЦИЯ: Включает или выключает режим автозамены символов переводов строк на тег <br>
	 *
	 * @param boolean $isAutoBrMode
	 */
	public function cfgSetAutoBrMode($isAutoBrMode) {
		$this->isAutoBrMode = (bool) $isAutoBrMode;
	}

	/**
	 * КОНФИГУРАЦИЯ: Включает или выключает режим автоматического определения ссылок
	 *
	 * @param boolean $isAutoLinkMode
	 */
	public function cfgSetAutoLinkMode($isAutoLinkMode) {
		$this->isAutoLinkMode = (bool) $isAutoLinkMode;
	}

	/**
	 * КОНФИГУРАЦИЯ: Задает символ/символы перевода строки в готовом тексте (\n или \r\n)
	 *
	 * @param string $nl - "\n" или "\r\n"
	 */
	public function cfgSetEOL($nl) {
		if(in_array($nl, array("\n", "\r\n"))) {
			$this->nl = $nl;
		}
	}

	/**
	 * Разбивает строку в массив посимвольно
	 *
	 * @param string $string текст
	 */
	protected function strToArray($str)
	{
		preg_match_all('#.#su', $str, $chars); // preg_split работает медленнее
		return $chars[0];
	}

	/**
	 * Запускает парсер
	 *
	 * @param string $text текст
	 * @param array $errors сообщения об ошибках
	 * @return string
	 */
	function parse($text, &$errors)
	{
		$this->prevPos = -1;
		$this->prevChar = null;
		$this->prevCharOrd = 0;
		$this->prevCharClass = self::NIL;

		$this->curPos = -1;
		$this->curChar = null;
		$this->curCharOrd = 0;
		$this->curCharClass = self::NIL;

		$this->nextPos = -1;
		$this->nextChar = null;
		$this->nextCharOrd = 0;
		$this->nextCharClass = self::NIL;

		$this->curTag = null;

		$this->statesStack = array();

		$this->quotesOpened = 0;

		$text = str_replace("\r", "", $text);

		$this->textBuf = $this->strToArray($text);
		$this->textLen = count($this->textBuf);

		$this->errorsList = array();

		$this->movePos(0);

		$content = $this->makeContent();
		$content = ($this->nl != "\n") ? str_replace("\n", $this->nl, $content) : $content;
		$content = trim($content);

		$errors = $this->errorsList;

		return $content;
	}

	/**
	 * Получение следующего символа из входной строки
	 *
	 * @return boolean
	 */
	protected function moveNextPos()
	{
		return $this->movePos($this->curPos+1);
	}

	/**
	 * Получение следующего символа из входной строки
	 *
	 * @return boolean
	 */
	protected function movePrevPos()
	{
		return $this->movePos($this->curPos-1);
	}

	/**
	 * Перемещает указатель на указанную позицию во входной строке и считывание символа
	 *
	 * @param int $position позиция в тексте
	 * @return boolean
	 */
	protected function movePos($position)
	{
		$prevPos = $position - 1;
		$curPos = $position;
		$nextPos = $position + 1;

		$prevPosStatus = ($prevPos < $this->textLen && $prevPos >= 0) ? true : false;

		$this->prevPos = $prevPos;
		$this->prevChar = ($prevPosStatus) ? $this->textBuf[$prevPos] : null;
		$this->prevCharOrd = ($prevPosStatus) ? $this->ord($this->prevChar) : 0;
		$this->prevCharClass = ($prevPosStatus) ? $this->getClassByOrd($this->prevCharOrd) : self::NIL;

		$curPosStatus = ($curPos < $this->textLen && $curPos >= 0) ? true : false;

		$this->curPos = $curPos;
		$this->curChar = ($curPosStatus) ? $this->textBuf[$curPos] : null;
		$this->curCharOrd = ($curPosStatus) ? $this->ord($this->curChar) : 0;
		$this->curCharClass = ($curPosStatus) ? $this->getClassByOrd($this->curCharOrd) : self::NIL;

		$nextPosStatus = ($nextPos < $this->textLen && $nextPos >= 0) ? true : false;

		$this->nextPos = $nextPos;
		$this->nextChar = ($nextPosStatus) ? $this->textBuf[$nextPos] : null;
		$this->nextCharOrd = ($nextPosStatus) ? $this->ord($this->nextChar) : 0;
		$this->nextCharClass = ($nextPosStatus) ? $this->getClassByOrd($this->nextCharOrd) : self::NIL;

		return (!is_null($this->curChar)) ? true : false;
	}

	/**
	 * Сохраняет текущее состояние автомата
	 *
	 */
	protected function saveState()
	{
		$state = array();

		$state['prevPos'] = $this->prevPos;
		$state['prevChar'] = $this->prevChar;
		$state['prevCharOrd'] = $this->prevCharOrd;
		$state['prevCharClass'] = $this->prevCharClass;

		$state['curPos'] = $this->curPos;
		$state['curChar'] = $this->curChar;
		$state['curCharOrd'] = $this->curCharOrd;
		$state['curCharClass'] = $this->curCharClass;

		$state['nextPos'] = $this->nextPos;
		$state['nextChar'] = $this->nextChar;
		$state['nextCharOrd'] = $this->nextCharOrd;
		$state['nextCharClass'] = $this->nextCharClass;

		$this->statesStack[] = $state;
	}

	/**
	 * Восстанавливает последнее сохраненное состояние автомата
	 *
	 */
	protected function restoreState()
	{
		$state = array_pop($this->statesStack);

		$this->prevPos = $state['prevPos'];
		$this->prevChar = $state['prevChar'];
		$this->prevCharOrd = $state['prevCharOrd'];
		$this->prevCharClass = $state['prevCharClass'];

		$this->curPos = $state['curPos'];
		$this->curChar = $state['curChar'];
		$this->curCharOrd = $state['curCharOrd'];
		$this->curCharClass = $state['curCharClass'];

		$this->nextPos = $state['nextPos'];
		$this->nextChar = $state['nextChar'];
		$this->nextCharOrd = $state['nextCharOrd'];
		$this->nextCharClass = $state['nextCharClass'];
	}

	/**
	 * Удаляет последнее сохраненное состояние
	 *
	 */
	protected function removeState()
	{
		$state = array_pop($this->statesStack);
	}

	/**
	 * Проверяет допустимость тега, классификатора тега и других параметров тега
	 *
	 */
	protected function tagsRules()
	{
		$args_list = func_get_args();

		if(count($args_list) == 0) {
			return false;
		}

		$tagsRules =& $this->tagsRules;
		foreach($args_list as $value)
		{
			if(is_null($value) || !isset($tagsRules[$value])) {
				return false;
			}

			$tagsRules =& $tagsRules[$value];
		}

		return true;
	}

	/**
	 * Проверяет точное вхождение символа в текущей позиции
	 *
	 * @param string $char символ
	 * @return boolean
	 */
	protected function matchChar($char)
	{
		return ($this->curChar == $char) ? true : false;
	}

	/**
	 * Проверяет вхождение символа указанного класса в текущей позиции
	 *
	 * @param int $charClass класс символа
	 * @return boolean
	 */
	protected function matchCharClass($charClass)
	{
		return ($this->curCharClass & $charClass) ? true : false;
	}

	/**
	 * Проверяет точное вхождение кода символа в текущей позиции
	 *
	 * @param int $charOrd код символа
	 * @return boolean
	 */
	protected function matchCharOrd($charOrd)
	{
		return ($this->curCharOrd == $charOrd) ? true : false;
	}

	/**
	 * Проверяет точное совпадение строки в текущей позиции
	 *
	 * @param string $str
	 * @return boolean
	 */
	protected function matchStr($str)
	{
		$this->saveState();
		$lenght = mb_strlen($str, 'UTF-8');
		$buffer = '';

		while($lenght-- && $this->curCharClass)
		{
			$buffer .= $this->curChar;
			$this->moveNextPos();
		}

		$this->restoreState();

		return ($buffer == $str) ? true : false;
	}

	/**
	 * Пропускает текст до нахождения указанного символа
	 *
	 * @param string $char символ для поиска
	 * @return boolean
	 */
	protected function skipTextToChar($char)
	{
		while($this->curChar != $char && $this->curCharClass)
		{
			$this->moveNextPos();
		}

		return ($this->curCharClass) ? true : false;
	}

	/**
	 * Пропускает текст до нахождения указанной строки
	 *
	 * @param string $str строка или символ для поиска
	 * @return boolean
	 */
	protected function skipTextToStr($str)
	{
		$chars = $this->strToArray($str);

		while($this->curCharClass)
		{
			if($this->curChar == $chars[0])
			{
				$this->saveState();

				$state = true;
				foreach($chars as $char)
				{
					if($this->curCharClass == self::NIL) {
						$this->removeState();
						return false;
					}

					if($this->curChar != $char) {
						$state = false;
						break;
					}

					$this->moveNextPos();
				}

				$this->restoreState();

				if($state) {
					return true;
				}
			}

			$this->moveNextPos();
		}

		return false;
	}

	/**
	 * Пропускает строку если она начинается с текущей позиции
	 *
	 * $this->skipTextToStr('-->') && $this->skipStr('-->');
	 *
	 * @param string $str строка для пропуска
	 * @return boolean
	 */
	protected function skipStr($str)
	{
		$chars = $this->strToArray($str);

		$this->saveState();

		$state = true;
		foreach($chars as $char)
		{
			if($this->curCharClass == self::NIL) {
				$state = false; break;
			}

			if($this->curChar != $char) {
				$state = false; break;
			}

			$this->moveNextPos();
		}

		if($state) $this->removeState();
		else $this->restoreState();

		return ($state) ? true : false;
	}

	/**
	 * Возвращает класс символа по его коду
	 *
	 * @param int $ord код символа
	 * @return int класс символа
	 */
	protected function getClassByOrd($ord)
	{
		return isset($this->charClasses[$ord]) ? $this->charClasses[$ord] : self::PRINATABLE;
	}

	/**
	 * Пропускает пробелы
	 *
	 * @return int количество пропусков
	 */
	protected function skipSpaces()
	{
		$count = 0;
		while($this->curCharClass & self::SPACE)
		{
			$this->moveNextPos();
			$count++;
		}
		return $count;
	}

	/**
	 * Пропускает символы перевода строк
	 *
	 * @param int $limit лимит пропусков символов перевода строк, при установке в 0 - не лимитируется
	 * @return boolean
	 */
	protected function skipNL($limit=0)
	{
		$count = 0;
		while($this->curCharClass & self::NL)
		{
			if($limit > 0 && $count >= $limit) {
				break;
			}

			$this->moveNextPos();
			$this->skipSpaces();

			$count++;
		}

		return $count;
	}

	/**
	 * Захватывает все последующие символы относящиеся к классу и возвращает их
	 *
	 * @param int $class класс для захвата
	 * @return string
	 */
	protected function grabCharClass($class)
	{
		$result = "";
		while($this->curCharClass & $class)
		{
			$result .= $this->curChar;
			$this->moveNextPos();
		}

		return $result;
	}

	/**
	 * Захватывает все последующие символы НЕ относящиеся к классу и возвращает их
	 *
	 * @param int $class класс для остановки захвата
	 * @return string
	 */
	protected function grabNotCharClass($class)
	{
		$result = "";
		while($this->curCharClass && ($this->curCharClass & $class) == self::NIL)
		{
			$result .= $this->curChar;
			$this->moveNextPos();
		}

		return $result;
	}

	/**
	 * Готовит контент
	 *
	 * @param string|null $parentTag имя родительского тега
	 * @return string
	 */
	protected function makeContent($parentTag = null)
	{
		$content = "";

		$this->skipSpaces();
		$this->skipNL();

		while($this->curCharClass)
		{
			$tagName = null;
			$tagParams = array();
			$tagContent = null;
			$shortTag = false;

			// Если текущий тег это тег без текста - пропускаем символы до "<"
			if($this->tagsRules($this->curTag, self::TAG_PARENT_ONLY) && $this->curChar != '<')
			{
				$this->skipTextToChar('<');
			}

			$this->saveState();

			// Тег в котором есть текст
			if($this->curChar == '<' && $this->matchTag($tagName, $tagParams, $tagContent, $shortTag))
			{
				$content .= $this->makeTag($tagName, $tagParams, $tagContent, $shortTag, $parentTag);

				if($tagName == 'br')
				{
					$this->skipNL(1);
				}
				else if($this->tagsRules($tagName, self::TAG_BLOCK_TYPE))
				{
					$this->skipNL(1);
				}
			}
			// Комментарий <!-- -->
			else if($this->curChar == '<' && $this->matchStr('<!--'))
			{
				$this->skipTextToStr('-->') && $this->skipStr('-->');
			}
			// Конец тега
			else if($this->curChar == '<' && $this->matchTagClose($tagName))
			{
				if(!is_null($this->curTag))
				{
					$this->restoreState();
					return $content;
				}
				else {
					$this->setError('Не ожидалось закрывающего тега '.$tagName);
				}
			}
			// Просто символ "<"
			else if($this->curChar == '<')
			{
				if(!$this->tagsRules($this->curTag, self::TAG_PARENT_ONLY)) {
					$content .= $this->entities['<'];
				}

				$this->moveNextPos();
			}
			// Наверно тут просто текст, формируем его
			else
			{
				$content .= $this->makeText();
			}

			$this->removeState();
		}

		return $content;
	}

	/**
	 * Обработка тега полностью
	 *
	 * @param string $tagName имя тега
	 * @param array $tagParams параметры тега
	 * @param string $tagContent контент тега
	 * @param boolean $shortTag короткий ли тег
	 * @return boolean
	 */
	protected function matchTag(&$tagName, &$tagParams, &$tagContent, &$shortTag)
	{
		$tagName = null;
		$tagParams = array();
		$tagContent = '';
		$shortTag = false;
		$closeTag = null;

		if(!$this->matchTagOpen($tagName, $tagParams, $shortTag)) {
			return false;
		}

		if($shortTag) {
			return true;
		}

		$curTag = $this->curTag;
		$typoMode = $this->typoMode;

		if($this->tagsRules($tagName, self::TAG_NO_TYPOGRAPHY)) {
			$this->typoMode = false;
		}

		$this->curTag = $tagName;

		if($this->tagsRules($tagName, self::TAG_PREFORMATTED))
		{
			$tagContent = $this->makePreformatted($tagName);
		}
		else {
			$tagContent = $this->makeContent($tagName);
		}

		if($this->matchTagClose($closeTag) && ($tagName != $closeTag))
		{
			$this->setError("Неверный закрывающийся тег ".$closeTag.". Ожидалось закрытие ".$tagName."");
		}

		$this->curTag = $curTag;
		$this->typoMode = $typoMode;

		return true;
	}

	/**
	 * Обработка открывающего тега
	 *
	 * @param string $tagName имя тега
	 * @param array $tagParams параметры тега
	 * @param boolean $shortTag короткий ли тег
	 * @return boolean
	 */
	protected function matchTagOpen(&$tagName, &$tagParams, &$shortTag = false)
	{
		if($this->curChar != '<') {
			return false;
		}

		$this->saveState();

		$this->skipSpaces() || $this->moveNextPos();

		$tagName = $this->grabCharClass(self::TAG_NAME);

		$this->skipSpaces();

		if($tagName == '') {
			$this->restoreState();
			return false;
		}

		$tagName = mb_strtolower($tagName, 'UTF-8');

		if($this->curChar != '>' && $this->curChar != '/') {
			$this->matchTagParams($tagParams);
		}

		$shortTag = $this->tagsRules($tagName, self::TAG_SHORT);

		if(!$shortTag && $this->curChar == '/') {
			$this->restoreState();
			return false;
		}

		if($shortTag && $this->curChar == '/') {
			$this->moveNextPos();
		}

		$this->skipSpaces();

		if($this->curChar != '>') {
			$this->restoreState();
			return false;
		}

		$this->removeState();
		$this->moveNextPos();

		return true;
	}

	/**
	 * Обработка закрывающего тега
	 *
	 * @param string $tagName имя тега
	 * @return boolean
	 */
	protected function matchTagClose(&$tagName)
	{
		if($this->curChar != '<') {
			return false;
		}

		$this->saveState();

		$this->skipSpaces() || $this->moveNextPos();

		if($this->curChar != '/') {
			$this->restoreState();
			return false;
		}

		$this->skipSpaces() || $this->moveNextPos();

		$tagName = $this->grabCharClass(self::TAG_NAME);

		$this->skipSpaces();

		if($tagName == '' || $this->curChar != '>') {
			$this->restoreState();
			return false;
		}

		$tagName = mb_strtolower($tagName, 'UTF-8');

		$this->removeState();
		$this->moveNextPos();

		return true;
	}

	/**
	 * Обработка параметров тега
	 *
	 * @param array $params массив параметров
	 * @return boolean
	 */
	protected function matchTagParams(&$params = array())
	{
		$name = null;
		$value = null;

		while($this->matchTagParam($name, $value))
		{
			$params[$name] = $value;
			$name = $value = '';
		}

		return (count($params) > 0) ? true : false;
	}

	/**
	 * Обработка одного параметра тега
	 *
	 * @param string $name имя параметра
	 * @param string $value значение параметра
	 * @return boolean
	 */
	protected function matchTagParam(&$name, &$value)
	{
		$this->saveState();

		$this->skipSpaces();

		$name = $this->grabCharClass(self::TAG_PARAM_NAME);

		if($name == '') {
			$this->removeState();
			return false;
		}

		$this->skipSpaces();

		// Параметр без значения
		if($this->curChar != '=')
		{
			if($this->curChar == '>' || $this->curChar == '/' || $this->curCharClass & self::SPACE)
			{
				$value = $name;

				$this->removeState();
				return true;
			}
			else {
				$this->restoreState();
				return false;
			}
		}
		else {
			$this->moveNextPos();
		}

		$this->skipSpaces();

		if(!$this->matchTagParamValue($value))
		{
			$this->restoreState();
			return false;
		}

		$this->skipSpaces();
		$this->removeState();

		return true;
	}

	/**
	 * Обработка значения параметра тега
	 *
	 * @param string $value значение параметра
	 * @return boolean
	 */
	protected function matchTagParamValue(&$value)
	{
		if($this->curCharClass & self::TAG_QUOTE)
		{
			$quote = $this->curChar;
			$escape = false;

			$this->moveNextPos();

			while($this->curCharClass && ($this->curChar != $quote || $escape === true))
			{
				$value .= (isset($this->entities[$this->curChar])) ? $this->entities[$this->curChar] : $this->curChar;

				// Возможны экранированные кавычки
				$escape = ($this->curChar == '\\') ? true : false;

				$this->moveNextPos();
			}

			if($this->curChar != $quote) {
				return false;
			}

			$this->moveNextPos();
		}
		else
		{
			while($this->curCharClass && ($this->curCharClass & self::SPACE) == self::NIL && $this->curChar != '>')
			{
				$value .= (isset($this->entities[$this->curChar])) ? $this->entities[$this->curChar] : $this->curChar;
				$this->moveNextPos();
			}
		}

		return true;
	}

	/**
	 * Готовит преформатированный контент
	 *
	 * @param string $openTag текущий открывающий тег
	 * @return string
	 */
	protected function makePreformatted($openTag = null)
	{
		$content = "";

		while($this->curCharClass)
		{
			if($this->curChar == '<' && !is_null($openTag))
			{
				$closeTag = '';
				$this->saveState();

				$isClosedTag = $this->matchTagClose($closeTag);

				if($isClosedTag) {
					$this->restoreState();
				}
				else {
					$this->removeState();
				}

				if($isClosedTag && $openTag == $closeTag) {
					break;
				}
			}

			$content .= (isset($this->entities[$this->curChar])) ? $this->entities[$this->curChar] : $this->curChar;

			$this->moveNextPos();
		}

		return $content;
	}

	/**
	 * Готовит тег к печати
	 *
	 * @param string $tagName имя тега
	 * @param array $tagParams параметры тега
	 * @param string $tagContent контент тега
	 * @param boolean $shortTag короткий ли тег
	 * @param string $parentTag имя тега родителя, если есть
	 * @return boolean
	 */
	protected function makeTag($tagName, $tagParams, $tagContent, $shortTag, $parentTag = null)
	{
		$text = "";
		$tagName = mb_strtolower($tagName, 'UTF-8');

		// Тег необходимо вырезать вместе с содержимым
		if($this->tagsRules($tagName, self::TAG_CUT)) {
			return "";
		}

		// Допустим ли тег к использованию
		if(!$this->tagsRules($tagName, self::TAG_ALLOWED)) {
			return ($this->tagsRules($parentTag, self::TAG_PARENT_ONLY)) ? "" : $tagContent;
		}

		// Должен ли тег НЕ быть дочерним к любому другому тегу
		if($this->tagsRules($tagName, self::TAG_GLOBAL_ONLY) && !is_null($parentTag)) {
			return $tagContent;
		}

		// Может ли тег находиться внутри родительского тега
		if($this->tagsRules($parentTag, self::TAG_PARENT_ONLY) && !$this->tagsRules($parentTag, self::TAG_CHILD, $tagName)) {
			return "";
		}

		// Тег может находиться только внутри другого тега
		if($this->tagsRules($tagName, self::TAG_CHILD_ONLY) && !$this->tagsRules($tagName, self::TAG_PARENT, $parentTag)) {
			return $tagContent;
		}

		// Параметры тега
		$tagParamsResult = array();
		foreach($tagParams as $param=>$value)
		{
			$param = mb_strtolower($param, 'UTF-8');
			$value = trim($value);

			if($value == '') {
				continue;
			}

			// Разрешен ли этот атрибут
			$paramAllowedValues = ($this->tagsRules($tagName, self::TAG_PARAM_ALLOWED, $param)) ? $this->tagsRules[$tagName][self::TAG_PARAM_ALLOWED][$param] : false;

			if($paramAllowedValues === false) {
				continue;
			}

			// Параметр есть в списке и это массив возможных значений
			if(is_array($paramAllowedValues) && !in_array($value, $paramAllowedValues))
			{
				$this->setError('Недопустимое значение "'.$value.'" для атрибута "'.$param.'" тега "'.$tagName.'"');
				continue;
			}

			// Параметр есть в списке и это строка представляющая правило
			if(is_string($paramAllowedValues))
			{
				if($paramAllowedValues == '#int')
				{
					if(!preg_match('#^[0-9]+$#iu', $value)) {
						$this->setError('Недопустимое значение "'.$value.'" для атрибута "'.$param.'" тега "'.$tagName.'". Ожидалось число');
						continue;
					}
				}

				else if($paramAllowedValues == '#text')
				{
					// ничего не делаем
				}

				else if($paramAllowedValues == '#link')
				{
					if(preg_match('#javascript:#iu', $value)) {
						$this->setError('Попытка вставить JavaScript в URI');
						continue;
					}

					if(!preg_match('#^[a-z0-9\/\#]#iu', $value)) {
						$this->setError('Первый символ URL должен быть буквой, цифрой, символами слеша или решетки');
						continue;
					}

					$protocols = implode('|', $this->linkProtocolAllow);
					if(!preg_match('#^('.$protocols.'):\/\/#iu', $value) && !preg_match('#^(\/|\#)#iu', $value) && !preg_match('#^mailto:#iu', $value))
					{
						$value = 'http://'.$value;
					}
				}

				else if(strpos($paramAllowedValues, '#regexp') === 0)
				{
					if(preg_match('#^\#regexp\((.*?)\)$#iu', $paramAllowedValues, $match)) {
						if(!preg_match('#^'.$match[1].'$#iu', $value)) {
							$this->setError('Недопустимое значение "'.$value.'" для атрибута "'.$param.'" тега "'.$tagName.'". Ожидалась строка подходящая под регулярное выражение "'.$match[1].'"');
							continue;
						}
					} else {
						$this->setError('Недопустимое значение "'.$value.'" для атрибута "'.$param.'" тега "'.$tagName.'". Ожидалось "'.$paramAllowedValues.'"');
						continue;
					}
				}
				
				else if($paramAllowedValues != $value)
				{
					$this->setError('Недопустимое значение "'.$value.'" для атрибута "'.$param.'" тега "'.$tagName.'". Ожидалось "'.$paramAllowedValues.'"');
					continue;
				}
			}

			$tagParamsResult[$param] = $value;
		}

		// Проверка обязательных параметров тега
		$requiredParams = ($this->tagsRules($tagName, self::TAG_PARAM_REQUIRED)) ? array_keys($this->tagsRules[$tagName][self::TAG_PARAM_REQUIRED]) : array();

		foreach($requiredParams as $requiredParam)
		{
			if(!isset($tagParamsResult[$requiredParam])) {
				return $tagContent;
			}
		}

		// Авто добавляемые параметры
		if($this->tagsRules($tagName, self::TAG_PARAM_AUTO_ADD))
		{
			foreach($this->tagsRules[$tagName][self::TAG_PARAM_AUTO_ADD] as $param => $value)
			{
				if(!isset($tagParamsResult[$param]) || $value['rewrite']) {
					$tagParamsResult[$param] = $value['value'];
				}
			}
		}

		// Удаляем пустые не короткие теги если не сказано другого
		if(!$this->tagsRules($tagName, self::TAG_EMPTY))
		{
			if(!$shortTag && $tagContent == '') {
				return '';
			}
		}

		// Вызываем callback функцию event... перед сборкой тега
		if($this->tagsRules($tagName, self::TAG_EVENT_CALLBACK))
		{
			call_user_func($this->tagsRules[$tagName][self::TAG_EVENT_CALLBACK], $tagName, $tagParamsResult, $tagContent);
		}

		// Вызываем callback функцию, если тег собирается именно так
		if($this->tagsRules($tagName, self::TAG_BUILD_CALLBACK))
		{
			return call_user_func($this->tagsRules[$tagName][self::TAG_BUILD_CALLBACK], $tagName, $tagParamsResult, $tagContent);
		}

		// Собираем тег
		$text .= '<'.$tagName;

		foreach($tagParamsResult as $param => $value)
		{
			$text .= ' '.$param.'="'.$value.'"';
		}

		$text .= ($shortTag && $this->isXHTMLMode) ? '/>' : '>';

		if($this->tagsRules($tagName, self::TAG_PARENT_ONLY)) {
			$text .= "\n";
		}

		if(!$shortTag) {
			$text .= $tagContent.'</'.$tagName.'>';
		}

		if($this->tagsRules($parentTag, self::TAG_PARENT_ONLY)) {
			$text .= "\n";
		}

		if($this->tagsRules($tagName, self::TAG_BLOCK_TYPE)) {
			$text .= "\n";
		}

		if($tagName == 'br') {
			$text .= "\n";
		}

		return $text;
	}

	/**
	 * Проверяет текущую позицию на вхождение тире пригодного для замены
	 *
	 * @param string $dash тире
	 * @return boolean
	 */
	protected function matchDash(&$dash = '')
	{
		if($this->curChar != '-') {
			return false;
		}

		if(($this->prevCharClass & (self::SPACE | self::NL | self::TEXT_BRACKET)) == self::NIL && $this->prevCharClass != self::NIL) {
			return false;
		}

		$this->saveState();

		while($this->nextChar == '-') {
			$this->moveNextPos();
		}

		if(($this->nextCharClass & (self::SPACE | self::NL | self::TEXT_BRACKET)) == self::NIL && $this->nextCharClass != self::NIL) {
			$this->restoreState();
			return false;
		}
		else
		{
			$dash = $this->dash;
			$this->removeState();
			$this->moveNextPos();
			return true;
		}
	}

	/**
	 * Определяет HTML сущности
	 *
	 * @param string $entity сущность
	 * @return boolean
	 */
	protected function matchHTMLEntity(&$entity = '')
	{
		if($this->curChar != '&') {
			return "";
		}

		$this->saveState();
		$this->moveNextPos();

		if($this->curChar == '#')
		{
			$this->moveNextPos();

			$entityCode = $this->grabCharClass(self::NUMERIC);

			if($entityCode == '' || $this->curChar != ';') {
				$this->restoreState();
				return false;
			}
			else {
				$this->removeState();
				$this->moveNextPos();
			}

			$entity = html_entity_decode("&#".$entityCode.";", ENT_COMPAT, 'UTF-8');

			return true;
		}
		else
		{
			$entityName = $this->grabCharClass(self::ALPHA | self::NUMERIC);

			if($entityName == '' || $this->curChar != ';') {
				$this->restoreState();
				return false;
			}
			else {
				$this->removeState();
				$this->moveNextPos();
			}

			$entity = html_entity_decode("&".$entityName.";", ENT_COMPAT, 'UTF-8');

			return true;
		}
	}

	/**
	 * Проверяет текущую позицию на вхождение кавычки пригодной для замены
	 *
	 * @param string $quote кавычка
	 * @return boolean
	 */
	protected function matchQuote(&$quote = '')
	{
		if(($this->curCharClass & self::TEXT_QUOTE) == self::NIL) {
			return false;
		}

		$type = ($this->quotesOpened >= 2) ||
				($this->quotesOpened > 0 &&
				((($this->prevCharClass & (self::SPACE | self::NL | self::TEXT_BRACKET)) == self::NIL && $this->prevCharClass != self::NIL) ||
				 (($this->nextCharClass & (self::SPACE | self::NL | self::TEXT_BRACKET | self::PUNCTUATUON)) || $this->nextCharClass == self::NIL))) ? 'close' : 'open';


		if($type == 'open' && ($this->prevCharClass & (self::SPACE | self::NL | self::TEXT_BRACKET)) == self::NIL && $this->prevCharClass != self::NIL) {
			return false;
		}

		if($type == 'close' && ($this->nextCharClass & (self::SPACE | self::NL | self::TEXT_BRACKET | self::PUNCTUATUON)) == self::NIL && $this->nextCharClass != self::NIL) {
			return false;
		}

		$this->quotesOpened += ($type == 'open') ? 1 : -1;

		$level = ($type == 'open') ? $this->quotesOpened - 1 : $this->quotesOpened;
		$index = ($type == 'open') ? 0 : 1;

		$quote = $this->quotes[$level][$index];

		$this->moveNextPos();

		return true;
	}

	/**
	 * Пытается найти и "сделать" текст
	 *
	 * @return string
	 */
	protected function makeText()
	{
		$text = '';

		while($this->curChar != '<' && $this->curCharClass)
		{
			$brCount = 0;
			$spCount = 0;
			$spResult = null;
			$entity = null;
			$quote = null;
			$dash = null;
			$url = null;

			// Преобразование HTML сущностей
			if($this->curChar == '&' && $this->matchHTMLEntity($entity))
			{
				$text .= (isset($this->entities[$entity])) ? $this->entities[$entity] : $entity;
			}
			// Добавление символов пунктуации
			else if($this->curCharClass & self::PUNCTUATUON)
			{
				$text .= $this->curChar;
				$this->moveNextPos();
			}
			// Преобразование символов тире в длинное тире
			else if($this->typoMode && $this->curChar == '-' && $this->matchDash($dash))
			{
				$text .= $dash;
			}
			// Преобразование кавычек
			else if($this->typoMode && ($this->curCharClass & self::TEXT_QUOTE) && $this->matchQuote($quote))
			{
				$text .= $quote;
			}
			// Преобразование пробельных символов
			else if($this->curCharClass & self::SPACE)
			{
				$this->skipSpaces();

				$text .= ' ';
			}
			// Преобразование символов перевода строк в тег <br>
			else if($this->isAutoBrMode && ($this->curCharClass & self::NL))
			{
				$brCount = $this->skipNL();

				if(!$this->tagsRules($this->curTag, self::TAG_NO_AUTO_BR))
				{
					$br = $this->br."\n";
					$text .= ($brCount == 1) ? $br : $br.$br;
				}
			}
			// Преобразование текста похожего на ссылку в кликабельную ссылку
			else if($this->isAutoLinkMode && ($this->curCharClass & self::ALPHA) && $this->curTag != 'a' && $this->matchURL($url))
			{
				$text .= $this->makeTag('a' , array('href' => $url), $url, false);
			}
			// Вызов callback-функции если строка предварена специальным символом
			else if($this->isSpecialCharMode && ($this->curCharClass & self::SPECIAL_CHAR) && $this->curTag != 'a' && $this->matchSpecialChar($spResult))
			{
				$text .= $spResult;
			}
			// Другие печатные символы
			else if($this->curCharClass & self::PRINATABLE)
			{
				$text .= (isset($this->entities[$this->curChar])) ? $this->entities[$this->curChar] : $this->curChar;
				$this->moveNextPos();
			}
			// Не печатные символы
			else
			{
				$this->moveNextPos();
			}
		}

		return $text;
	}

	/**
	 * Определяет текстовые ссылки
	 *
	 * @param string $url ссылка
	 * @return boolean
	 */
	protected function matchURL(&$url = '')
	{
		if(($this->prevCharClass & (self::SPACE | self::NL | self::TEXT_QUOTE | self::TEXT_BRACKET)) == self::NIL && $this->prevCharClass != self::NIL) {
			return false;
		}

		$this->saveState();

		if($this->matchStr('http://') && in_array('http', $this->linkProtocolAllow)) {}
		else if($this->matchStr('https://') && in_array('https', $this->linkProtocolAllow)) {}
		else if($this->matchStr('ftp://') && in_array('ftp', $this->linkProtocolAllow)) {}
		else if($this->matchStr('www.')) {
			$url = "http://";
		}
		else {
			$this->restoreState();
			return false;
		}

		$openBracket = (($this->prevCharClass & self::TEXT_BRACKET) && isset($this->bracketsALL[$this->prevChar])) ? $this->prevChar : null;
		$closeBracket = (!is_null($openBracket)) ? $this->bracketsALL[$this->prevChar] : null;

		$openedBracket = (!is_null($openBracket)) ? 1 : 0;

		$buffer = "";
		while($this->curCharClass & self::PRINATABLE)
		{
			if($this->curChar == "<") {
				break;
			}
			else if(($this->curCharClass & self::TEXT_QUOTE)) {
				break;
			}
			else if(($this->curCharClass & self::TEXT_BRACKET) && $openedBracket > 0)
			{
				if($this->curChar == $closeBracket && $openedBracket == 1) {
					break;
				}

				if($this->curChar == $openBracket) { $openedBracket += 1; }
				if($this->curChar == $closeBracket) { $openedBracket -= 1; }
			}
			else if($this->curCharClass & self::PUNCTUATUON)
			{
				$this->saveState();
				$punctuatuon = $this->grabCharClass(self::PUNCTUATUON);

				if(($this->curCharClass & self::PRINATABLE) == self::NIL)
				{
					$this->restoreState();
					break;
				}
				else {
					$this->removeState();
					$buffer .= $punctuatuon;

					if(($this->curCharClass & (self::TEXT_QUOTE | self::TEXT_BRACKET))) {
						break;
					}
				}
			}

			$buffer .= $this->curChar;
			$this->moveNextPos();
		}

		if($buffer == "")
		{
			$this->restoreState();
			return false;
		}
		else
		{
			$this->removeState();
		}

		$url = $url.$buffer;

		return true;
	}

	/**
	 * Определяет строки предваренные спецсимволами
	 *
	 * @param string $spResult результат работы callback-функции
	 * @return boolean
	 */
	protected function matchSpecialChar(&$spResult = '')
	{
		if(($this->curCharClass & self::SPECIAL_CHAR) == self::NIL) {
			return false;
		}

		if(!isset($this->specialChars[$this->curChar])) {
			return false;
		}

		if($this->prevCharClass && ($this->prevCharClass & (self::SPACE | self::NL | self::TEXT_BRACKET)) == self::NIL) {
			return false;
		}

		$buffer = "";
		$spChar = $this->curChar;

		$this->saveState();
		$this->moveNextPos();

		if(($this->curCharClass & self::TEXT_BRACKET) && isset($this->bracketsSPC[$this->curChar]))
		{
			$closeBracket = $this->bracketsSPC[$this->curChar];
			$escape = false;

			$this->moveNextPos();

			while($this->curCharClass && ($this->curCharClass & self::NL) == self::NIL && ($this->curChar != $closeBracket || $escape === true))
			{
				if(($this->curCharClass & self::SPACE) && ($this->prevCharClass & self::SPACE))
				{
					$this->skipSpaces();
					continue;
				}

				$buffer .= $this->curChar;

				// Возможны экранированные скобки
				$escape = ($this->curChar == '\\') ? true : false;

				$this->moveNextPos();
			}

			if($this->curChar != $closeBracket) {
				$this->restoreState();
				return false;
			}

			$this->moveNextPos();
		}
		else
		{
			while($this->curCharClass && ($this->curCharClass & (self::SPACE | self::NL | self::TEXT_BRACKET)) == self::NIL)
			{
				if($this->curCharClass & self::PUNCTUATUON)
				{
					$this->saveState();

					$punctuatuon = $this->grabCharClass(self::PUNCTUATUON);

					if($this->curCharClass & (self::SPACE | self::NL | self::TEXT_BRACKET) || $this->curCharClass == self::NIL)
					{
						$this->restoreState();
						break;
					}
					else {
						$this->removeState();
						$buffer .= $punctuatuon;
					}
				}

				$buffer .= $this->curChar;
				$this->moveNextPos();
			}
		}

		$buffer = trim($buffer);

		if($buffer == "")
		{
			$this->restoreState();
			return false;
		}

		$spResult = call_user_func($this->specialChars[$spChar], $buffer);

		if(!$spResult) {
			$this->restoreState();
			return false;
		}

		$this->removeState();

		return true;
	}

	/**
	 * Возвращает код символа по его строковому представлению
	 *
	 * @param string $chr символ
	 * @return int|boolean
	 */
	public static function ord($chr)
	{
		$ord = ord($chr[0]);

		if($ord < 0x80) return $ord;
		if($ord < 0xC2) return false;
		if($ord < 0xE0) return ($ord & 0x1F) <<  6 | (ord($chr[1]) & 0x3F);
		if($ord < 0xF0) return ($ord & 0x0F) << 12 | (ord($chr[1]) & 0x3F) << 6  | (ord($chr[2]) & 0x3F);
		if($ord < 0xF5) return ($ord & 0x0F) << 18 | (ord($chr[1]) & 0x3F) << 12 | (ord($chr[2]) & 0x3F) << 6 | (ord($chr[3]) & 0x3F);

		return false;
	}

	/*
	 * Медленно!
	 *
	public static function ord($chr)
	{
		$result = unpack('N', mb_convert_encoding($chr, 'UCS-4BE', 'UTF-8'));
		return (is_array($result)) ? $result[1] : false;
	}
	*/

	/**
	 * Возвращает строковое представление символа по его коду
	 *
	 * @param string $ord код символа
	 * @return string|boolean
	 */
	public function chr($ord)
	{
		if($ord < 0x80)		return chr($ord);
		if($ord < 0x800) 	return chr(0xC0 | $ord >> 6)  . chr(0x80 | $ord & 0x3F);
		if($ord < 0x10000) 	return chr(0xE0 | $ord >> 12) . chr(0x80 | $ord >> 6  & 0x3F) . chr(0x80 | $ord & 0x3F);
		if($ord < 0x110000) return chr(0xF0 | $ord >> 18) . chr(0x80 | $ord >> 12 & 0x3F) . chr(0x80 | $ord >> 6 & 0x3F) . chr(0x80 | $ord & 0x3F);

		return false;
	}

	/*
	 * Медленно...
	 *
	public function chr($ord)
	{
		return mb_convert_encoding('&#'.intval($ord).';', 'UTF-8', 'HTML-ENTITIES');
	}
	*/

	/**
	 * Устанавливает сообщение об ошибке (для внутренних нужд)
	 *
	 * @param string $message сообщение об ошибке
	 * @return void
	 */
	protected function setError($message)
	{
		$this->errorsList[] = array(
			'message' => $message,
			'position' => $this->curPos
		);
	}

	/**
	 * Получить сообщения список сообщений об ошибке
	 *
	 * @return array
	 */
	public function getError()
	{
		return $this->errorsList;
	}
}
?>
