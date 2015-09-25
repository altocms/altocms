
## Документации по конфигурации

### cfgAllowTags

cfgAllowTags — Задает список разрешенных тегов

`cfgAllowTags($tags);`

**Параметры**
* $tags — (array|string) список разрешенных тегов

**Пример использования**
```php
$qevix->cfgAllowTags(array('b', 'i', 'u', 'a', 'img', 'ul', 'li', 'ol', 'br', 'code'));
```

### cfgSetTagShort

cfgSetTagShort — Указывает какие теги считать короткими

`$qevix->cfgSetTagShort($tags)`

**Параметры**
* $tags — (array|string) тег(и)

**Пример использования**
```php
$qevix->cfgSetTagShort(array('br','img'));
```

### cfgSetTagPreformatted

cfgSetTagPreformatted — Указывает преформатированные теги, в которых нужно всё заменять на HTML сущности

`$qevix->cfgSetTagPreformatted($tags)`

**Параметры**
* $tags — (array|string) тег(и)  

**Пример использования**
```php
$qevix->cfgSetTagPreformatted(array('code'));
```

### cfgSetTagNoTypography

cfgSetTagNoTypography — Указывает теги в которых нужно отключить типографирование текста

`$qevix->cfgSetTagNoTypography($tags)`

**Параметры**
* $tags — (array|string) тег(и)

**Пример использования**
```php
$qevix->cfgSetTagNoTypography(array('code'));
```

### cfgSetTagIsEmpty

cfgSetTagIsEmpty — Указывает не короткие теги, которые могут быть пустыми и их не нужно из-за этого удалять

`$qevix->cfgSetTagIsEmpty($tags)`

**Параметры**
* $tags — (array|string) тег(и)

**Пример использования**
```php
$qevix->cfgSetTagIsEmpty(array('div'));
```

### cfgSetTagNoAutoBr

cfgSetTagNoAutoBr — Указывает теги внутри, которых не нужна авто-расстановка тегов перевода на новую строку

`$qevix->cfgSetTagNoAutoBr($tags)`

**Параметры**
* $tags — (array|string) тег(и)

**Пример использования**
```php
$qevix->cfgSetTagNoAutoBr(array('ul', 'ol'));
```

### cfgSetTagCutWithContent

cfgSetTagCutWithContent — Указывает теги, которые необходимо вырезать вместе с содержимым

`$qevix->cfgSetTagCutWithContent($tags)`

**Параметры**
* $tags — (array|string) тег(и)

**Пример использования**
```php
$qevix->cfgSetTagCutWithContent(array('script', 'object', 'iframe', 'style'));
```

### cfgSetTagBlockType

cfgSetTagBlockType — Указывает теги после, которых не нужно добавлять дополнительный перевод строки, например, блочные теги

`$qevix->cfgSetTagBlockType($tags)`

**Параметры**
* $tags — (array|string) тег(и)

**Пример использования**
```php
$qevix->cfgSetTagBlockType(array('ol','ul','code'));
```

### cfgAllowTagParams

cfgAllowTagParams — Добавляет разрешенные параметры для тегов. Значение по умолчанию - шаблон #text. Разрешенные шаблоны #text, #int, #link, #regexp(...).
Например, шаблон с регулярным выражением может выглядеть так: "#regexp(\d+(%|px))"

`$qevix->cfgAllowTagParams($tag, $params)`

**Параметры**
* $tag — (string) тег
* $params — (string|array) разрешённые параметры

**Пример использования**
```php
$qevix->cfgAllowTagParams('a', array('title', 'href' => '#link', 'rel' => '#text', 'target' => array('_blank')));
$qevix->cfgAllowTagParams('img', array('src' => '#text', 'alt' => '#text', 'title', 'align' => array('right', 'left', 'center'), 'width' => '#int', 'height' => '#int'));
```

### cfgSetTagParamsRequired

cfgSetTagParamsRequired — Добавляет обязательные параметры для тега

`$qevix->cfgSetTagParamsRequired($tag, $params)`

**Параметры**
* $tag — (string) тег
* $params — (string|array) разрешённые параметры

**Пример использования**
```php
$qevix->cfgSetTagParamsRequired('img', 'src');
$qevix->cfgSetTagParamsRequired('a', 'href');
```

### cfgSetTagChilds

cfgSetTagChilds — Указывает какие теги являются контейнерами для других тегов

`$qevix->cfgSetTagChilds($tag, $childs, $isParentOnly = false, $isChildOnly = false)`

**Параметры**
* $tag — (string) тег
* $childs — (string|array) разрешённые дочерние теги
* $isParentOnly — (boolean) тег является только контейнером других тегов и не может содержать текст
* $isChildOnly — (boolean) дочерние теги не могут присутствовать нигде кроме указанного тега

**Пример использования**
```php
$qevix->cfgSetTagChilds('ul', 'li', true, true);
$qevix->cfgSetTagChilds('ol', 'li', true, true);
```

### cfgSetTagGlobal

cfgSetTagGlobal — Указывает какие теги не должны быть дочерними к другим тегам

`$qevix->cfgSetTagGlobal($tags)`

**Параметры**
* $tags — (string|array) тег(и)

**Пример использования**
```php
$qevix->cfgSetTagGlobal('cut');
```

### cfgSetTagParamDefault

cfgSetTagParamDefault — Указывает значения по умолчанию для параметров тега

`$qevix->cfgSetTagParamDefault($tag, $param, $value, $isRewrite = false)`

**Параметры**
* $tag — (string) тег
* $param — (string) атрибут тега
* $value — (string) значение арибута
* $isRewrite — (boolean) перезаписывать значение значением по умолчанию

**Пример использования**
```php
$qevix->cfgSetTagParamDefault('a', 'rel', 'nofollow', true);
$qevix->cfgSetTagParamDefault('img', 'alt', '');
```

### cfgSetTagBuildCallback

cfgSetTagBuildCallback — Устанавливает на тег callback-функцию для ручной сборки тега

`$qevix->cfgSetTagBuildCallback($tag, $callback)`

**Параметры**
* $tag — (string) тег
* $callback — (mixed) функция

**Пример использования**
```php
$qevix->cfgSetTagBuildCallback('code', 'tag_code_build');

function tag_code_build($tag, $params, $content)
{
	return '<pre><code>'.$content.'<code><pre>'."\n";
}
```

### cfgSetTagEventCallback

cfgSetTagEventCallback — Устанавливает на тег callback-функцию для сбора информации. В отличие от callback-функций установленных с помощью cfgSetTagBuildCallback, этот обработчик не вносит изменения в текст, а может быть использован для сбора какой-либо информации об используемых тегах.
Например, можно посчитать, какое количество изображений в тексте и сформировать массив из их URL для последующего использования в meta-описании станицы.
Для сбора информации, теги должны быть разрешены в cfgAllowTags.

`$qevix->cfgSetTagEventCallback($tag, $callback)`

**Параметры**
* $tag — (string) тег
* $callback — (mixed) функция

**Пример использования**
```php
$qevix->cfgSetTagEventCallback('img', 'tag_img_event');

$meta_img_src = array();

function tag_img_event($tag, $params, $content)
{
	global $meta_img_src;

	$meta_img_src[] = $params['src'];
}
```

### cfgSetSpecialCharCallback

cfgSetSpecialCharCallback — Устанавливает на строку предваренную спецсимволом callback-функцию. По умолчанию Qevix работает с тремя спец. символами #, @, $. Но вы можете сгенерировать свой набор, воспользовавшись генератором классов символов symbolclass.php.
 Как можно догадаться, эта настройка позволяет получить хештег (#tagname) или имя пользователя (@username), или ключевое слово ($keyword) и оформить его в виде ссылки или того, что вам нужно.

`cfgSetSpecialCharCallback($char, $callback)`

**Параметры**
* $char — (string) спецсимвол #, @ или $
* $callback — (mixed) функция

**Пример использования**
```php
$qevix->cfgSetSpecialCharCallback('#', 'tag_sharp_build');
$qevix->cfgSetSpecialCharCallback('@', 'tag_at_build');

function tag_sharp_build($string)
{
	if(!preg_match('#^[\w\_\-\ ]{1,32}$#isu', $string)) {
		return false;
	}

	return '<a href="/tag/'.rawurlencode($string).'/">#'.$string.'</a>';
}

function tag_at_build($string)
{
	if(!preg_match('#^[\w\_\-\ ]{1,32}$#isu', $string)) {
		return false;
	}

	return '<a href="/user/'.$string.'/">@'.$string.'</a>';
}
```

Вы можете сами отслеживать, какие символы могут входить в строку.
Qevix поддерживает два варианта обработки специальных строк #tag, #[tag] или #{tag string}.
Вариант со скобками может содержать пробелы, но вы можете их запретить в своей callback-функции и такие строки обрабатываться не будут.

### cfgSetLinkProtocolAllow

cfgSetLinkProtocolAllow — Устанавливает список разрешенных протоколов для ссылок. По умолчанию разрешены http, https, ftp

`cfgSetLinkProtocolAllow($protocols)`

**Параметры**
* $protocols — (array) список протоколов

**Пример использования**
```php
$qevix->cfgSetLinkProtocolAllow(array('http','https'));
```

### cfgSetXHTMLMode

cfgSetXHTMLMode — Включает или выключает режим XHTML. По умолчанию выключен.

`cfgSetXHTMLMode($isXHTMLMode)`

**Параметры**
* $isXHTMLMode — (boolean) Включить XHTML формат тегов установив в True;

**Пример использования**
```php
$qevix->cfgSetXHTMLMode(true);
```

### cfgSetAutoBrMode

cfgSetAutoBrMode — Включает или выключает режим автозамены символов перевода строки на тег br. По умолчанию включен.

`cfgSetAutoBrMode($isAutoBrMode)`

**Параметры**
* $isAutoBrMode — (boolean) Включить авторасстановку тегов переда строки установив в True;

**Пример использования**
```php
$qevix->cfgSetAutoBrMode(true);
```

### cfgSetAutoLinkMode

cfgSetAutoLinkMode — Включает или выключает режим автоматического определения ссылок. По умолчанию режим включен.

`cfgSetAutoLinkMode($isAutoLinkMode)`

**Параметры**
* $isAutoLinkMode — (boolean) Включить автоопределение ссылок установив в True;

**Пример использования**
```php
$qevix->cfgSetAutoLinkMode(true);
```

### cfgSetEOL

cfgSetEOL — Задает символ/символы перевода строки для текста на выходе. По умолчанию используется только символ перевода строки (LF) "\n", можно задать (CR+LF) "\r\n".

`cfgSetEOL($nl)`

**Параметры**
* $nl — "\n" или "\r\n";

**Пример использования**
```php
$qevix->cfgSetEOL("\r\n");
```
