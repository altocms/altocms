<?php

require('qevix.php');

$qevix = new Qevix();

//Конфигурация

// 1. Задает список разрешенных тегов
$qevix->cfgAllowTags(array('b', 'i', 'u', 'a', 'img', 'ul', 'li', 'ol', 'br', 'code', 'pre', 'div', 'cut'));

// 2. Указавает, какие теги считать короткими (<br>, <img>)
$qevix->cfgSetTagShort(array('br','img','cut'));

// 3. Указывает преформатированные теги, в которых нужно всё заменять на HTML сущности
$qevix->cfgSetTagPreformatted(array('code'));

// 4. Указывает не короткие теги, которые могут быть пустыми и их не нужно из-за этого удалять
$qevix->cfgSetTagIsEmpty(array('div'));

// 5. Указывает теги внутри которых не нужна авто расстановка тегов перевода на новую строку
$qevix->cfgSetTagNoAutoBr(array('ul', 'ol'));

// 6. Указывает теги, которые необходимо вырезать вместе с содержимым
$qevix->cfgSetTagCutWithContent(array('script', 'object', 'iframe', 'style'));

// 7. Указывает теги, после которых не нужно добавлять дополнительный перевод строки, например, блочные теги
$qevix->cfgSetTagBlockType(array('ol','ul','code'));

// 8. Добавляет разрешенные параметры для тегов, значение по умолчанию шаблон #text. Разрешенные шаблоны #text, #int, #link
$qevix->cfgAllowTagParams('a', array('title', 'href' => '#link', 'rel' => '#text', 'target' => array('_blank')));
$qevix->cfgAllowTagParams('img', array('src' => '#text', 'alt' => '#text', 'title', 'align' => array('right', 'left', 'center'), 'width' => '#int', 'height' => '#int'));


// 9. Добавляет обязательные параметры для тега
$qevix->cfgSetTagParamsRequired('img', 'src');
$qevix->cfgSetTagParamsRequired('a', 'href');

// 10. Указывает, какие теги являются контейнерами для других тегов
$qevix->cfgSetTagChilds('ul', 'li', true, true);
$qevix->cfgSetTagChilds('ol', 'li', true, true);

// 11. Указывает, какие теги не должны быть дочерними к другим тегам
$qevix->cfgSetTagGlobal('cut');

// 12. Устанавливаем атрибуты тегов, которые будут добавлятся автоматически
$qevix->cfgSetTagParamDefault('a', 'rel', 'nofollow', true);
$qevix->cfgSetTagParamDefault('img', 'alt', '');

// 13. Указывает теги, в которых нужно отключить типографирование текста
$qevix->cfgSetTagNoTypography(array('code', 'pre'));

// 14. Устанавливает список разрешенных протоколов для ссылок (https, http, ftp)
$qevix->cfgSetLinkProtocolAllow(array('http','https'));

// 15. Включает или выключает режим XHTML
$qevix->cfgSetXHTMLMode(false);

// 16. Включает или выключает режим автозамены символов переводов строк на тег <br>
$qevix->cfgSetAutoBrMode(true);

// 17. Включает или выключает режим автоматического определения ссылок
$qevix->cfgSetAutoLinkMode(true);

// 18. Устанавливает на тег callback-функцию
$qevix->cfgSetTagBuildCallback('code', 'tag_code_build');

// 19. Устанавливает на строку предварённую спецсимволом (@|#|$) callback-функцию
$qevix->cfgSetSpecialCharCallback('#', 'tag_sharp_build');
$qevix->cfgSetSpecialCharCallback('@', 'tag_at_build');

// 18. Устанавливает на тег событие
$qevix->cfgSetTagEventCallback('code', 'tag_code_event');

function tag_code_build($tag, $params, $content)
{
	return '<pre><code>'.$content.'<code><pre>'."\n";
}

function tag_code_event($tag, $params, $content)
{
	// Что-то делаем...
}

function tag_sharp_build($string)
{
	if(!preg_match('#^[\w\_\-\ ]{1,32}$#isu', $string)) {
		return false;
	}

	return '<a href="/search/tag/'.rawurlencode($string).'/">#'.$string.'</a>';
}

function tag_at_build($string)
{
	if(!preg_match('#^[\w\_\-]{1,32}$#isu', $string)) {
		return false;
	}
	
	return '<a href="/user/'.$string.'/">@'.$string.'</a>';
}

//Парсинг

// #1
$text = '<b>текст текст текст</b>';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('<b>текст текст текст</b>')."<br><br>";

// #2
$text = '<b>текст <b>текст</b> текст</b>';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('<b>текст <b>текст</b> текст</b>')."<br><br>";

// #3
$text = '<b>текст текст текст';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('<b>текст текст текст</b>')."<br><br>";

// #4
$text = '<b>текст <u>текст текст';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('<b>текст <u>текст текст</u></b>')."<br><br>";

// #5
$text = '<u>текст <s>текст</s> текст</u>';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('<u>текст текст текст</u>')."<br><br>";

// #6
$text = 'текст <script>текст</script> текст';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('текст текст')."<br><br>";


// #7
$text = '<code>текст <script>текст</script> текст</code>';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('<pre><code>текст &#60;script&#62;текст&#60;/script&#62; текст<code><pre>')."<br><br>";

// #8
$text = 'текст <div></div> <b></b> текст';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('текст <div></div> текст')."<br><br>";

// #9
$text = 'текст http://yandex.ru текст';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('текст <a href="http://yandex.ru" rel="nofollow">http://yandex.ru</a> текст')."<br><br>";

// #10
$text = 'текст <b>http://yandex.ru</b> текст';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('текст <b><a href="http://yandex.ru" rel="nofollow">http://yandex.ru</a></b> текст')."<br><br>";

// #11
$text = 'текст http://yandex.ru/?a=href&title=test!..';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('текст <a href="http://yandex.ru/?a=href&title=test" rel="nofollow">http://yandex.ru/?a=href&title=test</a>!..')."<br><br>";

// #12
$text = 'текст ftp://yandex.ru!..';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('текст ftp://yandex.ru!..')."<br><br>";

// #13
$text = 'текст 

<ul>
  <li>текст</li>
  <li>текст</li>
  <b>текст</b>
  <br>
</ul>

текст';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('текст <br> <br> <ul> <li>текст</li> <li>текст</li> </ul> <br> текст')."<br><br>";

// #14
$text = 'текст 
<li>текст</li>
<li>текст</li>
текст';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('текст <br> текст<br> текст<br> текст')."<br><br>";

// #15
$text = '<b>"текст" текст "текст "текст" текст" "..."</b>';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('<b>«текст» текст «текст „текст“ текст» «...»</b>')."<br><br>";

// #16
$text = '<pre>"текст" текст "текст "текст" текст" "..."</pre>';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('<pre>&#34;текст&#34; текст &#34;текст &#34;текст&#34; текст&#34; &#34;...&#34;</pre>')."<br><br>";

// #17
$text = 'текст &#40; &#41; &#42; &#43; &#44; текст';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('текст ( ) * + , текст')."<br><br>";

// #18
$text = 'текст - текст';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('текст — текст')."<br><br>";

// #19
$text = 'текст #hash... #{tag name} текст';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('текст <a href="/search/tag/hash/">#hash</a>... <a href="/search/tag/tag%20name/">#tag name</a> текст')."<br><br>";

// #20
$text = 'текст <b>#hashtag #taghash, #htag</b> текст';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('текст <b><a href="/search/tag/hashtag/">#hashtag</a> <a href="/search/tag/taghash/">#taghash</a>, <a href="/search/tag/htag/">#htag</a></b> текст')."<br><br>";

// #21
$text = 'текст <a href="http://ya.ru">текст</a> текст';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('текст <a href="http://ya.ru" rel="nofollow">текст</a> текст')."<br><br>";

// #22
$text = 'текст <a href = "http://ya.ru" title="text" >текст</a> текст';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('текст <a href="http://ya.ru" title="text" rel="nofollow">текст</a> текст')."<br><br>";

// #23
$text = 'текст <a href = "http://ya.ru" args="test">текст</a> текст';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('текст <a href="http://ya.ru" rel="nofollow">текст</a> текст')."<br><br>";

// #24
$text = 'текст <a href="http://ya.ru" title = text>текст</a> текст';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('текст <a href="http://ya.ru" title="text" rel="nofollow">текст</a> текст')."<br><br>";

// #25
$text = 'текст <a href="http://ya.ru" target=_blank title=/">текст</a> текст';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('текст <a href="http://ya.ru" target="_blank" title="/&#34;" rel="nofollow">текст</a> текст')."<br><br>";

// #26
$text = 'текст <a href="javascript:alert(1)">текст</a> текст';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('текст текст текст')."<br><br>";

// #27
$text = '<b>текст текст</b> <cut> <b>текст <cut> текст</b>';

$result = $qevix->parse($text, $errors);
echo "строка: ".htmlspecialchars($text)."<br>";
echo "результат: ".htmlspecialchars($result)."<br>";
echo "предполагалось: ".htmlspecialchars('<b>текст текст</b> <cut> <b>текст текст</b>')."<br><br>";

?>