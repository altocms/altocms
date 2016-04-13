<?php

require 'qevix.php';

class QevixTests extends \PHPUnit_Framework_TestCase
{
    private static $qevix = null;

    public static function setUpBeforeClass()
    {
        $qevix = new Qevix();

        // 1. Задает список разрешенных тегов
        $qevix->cfgAllowTags(array('b', 'i', 'u', 'a', 'img', 'ul', 'li', 'ol', 'br', 'code', 'pre', 'div', 'cut', 'video'));

        // 2. Указавает, какие теги считать короткими (<br>, <img>)
        $qevix->cfgSetTagShort(array('br','img','cut', 'video'));

        // 3. Указывает преформатированные теги, в которых нужно всё заменять на HTML сущности
        $qevix->cfgSetTagPreformatted(array('code'));

        // 4. Указывает не короткие теги, которые могут быть пустыми и их не нужно из-за этого удалять
        $qevix->cfgSetTagIsEmpty(array('div'));

        // 5. Указывает теги внутри которых не нужна авто расстановка тегов перевода на новую строку
        $qevix->cfgSetTagNoAutoBr(array('ul', 'ol'));

        // 6. Указывает теги, которые необходимо вырезать вместе с содержимым
        $qevix->cfgSetTagCutWithContent(array('script', 'object', 'iframe', 'style'));

        // 7. Указывает теги, после которых не нужно добавлять дополнительный перевод строки, например, блочные теги
        $qevix->cfgSetTagBlockType(array('ol','ul','code','video'));

        // 8. Добавляет разрешенные параметры для тегов, значение по умолчанию шаблон #text. Разрешенные шаблоны #text, #int, #link, #regexp(...) (Например: "#regexp(\d+(%|px))")
        $qevix->cfgAllowTagParams('a', array('title', 'href' => '#link', 'rel' => '#text', 'target' => array('_blank')));
        $qevix->cfgAllowTagParams('img', array('src' => '#text', 'alt' => '#text', 'title', 'align' => array('right', 'left', 'center'), 'width' => '#int', 'height' => '#int'));
        $qevix->cfgAllowTagParams('video', array('src' => ['#link' => ['youtube.com','vimeo.com']]));


        // 9. Добавляет обязательные параметры для тега
        $qevix->cfgSetTagParamsRequired('a', 'href');
        $qevix->cfgSetTagParamsRequired('img', 'src');
        $qevix->cfgSetTagParamsRequired('video', 'src');

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

        // 18. Задает символ/символы перевода строки. По умполчанию "\n". Разрешено "\n" или "\r\n"
        $qevix->cfgSetEOL("\n");

        // 19. Устанавливает на тег callback-функцию
        $qevix->cfgSetTagBuildCallback('code', [__CLASS__, 'tagCodeBuild']);

        // 20. Устанавливает на строку предварённую спецсимволом (@|#|$) callback-функцию
        $qevix->cfgSetSpecialCharCallback('#', [__CLASS__, 'tagSharpBuild']);
        $qevix->cfgSetSpecialCharCallback('@', [__CLASS__, 'tagAtBuild']);

        static::$qevix = $qevix;
    }

    public static function tearDownAfterClass()
    {
        static::$qevix = null;
    }

    public static function tagCodeBuild($tag, $params, $content)
    {
        return '<pre><code>'.$content.'<code><pre>'."\n";
    }

    public static function tagSharpBuild($string)
    {
        if (!preg_match('#^[\w\_\-\ ]{1,32}$#isu', $string)) {
            return false;
        }

        return '<a href="/search/tag/'.rawurlencode($string).'/">#'.$string.'</a>';
    }

    public static function tagAtBuild($string)
    {
        if (!preg_match('#^[\w\_\-]{1,32}$#isu', $string)) {
            return false;
        }

        return '<a href="/user/'.$string.'/">@'.$string.'</a>';
    }

    public function textsDataProvider()
    {
        return array(
            [
                 '<b>текст текст текст</b>',
                 '<b>текст текст текст</b>'
            ], [
                '<b>текст <b>текст</b> текст</b>',
                '<b>текст <b>текст</b> текст</b>'
            ], [
                '<b>текст текст текст',
                '<b>текст текст текст</b>'
            ], [
                '<b>текст <u>текст текст',
                '<b>текст <u>текст текст</u></b>'
            ], [
                '<u>текст <s>текст</s> текст</u>',
                '<u>текст текст текст</u>'
            ], [
                'текст <script>текст</script> текст',
                'текст текст'
            ], [
                '<code>текст <script>текст</script> текст</code>',
                '<pre><code>текст &#60;script&#62;текст&#60;/script&#62; текст<code><pre>'
            ], [
                'текст <div></div> <b></b> текст',
                'текст <div></div> текст'
            ], [
                'текст http://yandex.ru текст',
                'текст <a href="http://yandex.ru" rel="nofollow">http://yandex.ru</a> текст'
            ], [
                'текст <b>http://yandex.ru</b> текст',
                'текст <b><a href="http://yandex.ru" rel="nofollow">http://yandex.ru</a></b> текст'
            ], [
                'текст http://yandex.ru/?a=href&title=test!..',
                'текст <a href="http://yandex.ru/?a=href&title=test" rel="nofollow">http://yandex.ru/?a=href&title=test</a>!..'
            ], [
                'текст ftp://yandex.ru!..',
                'текст ftp://yandex.ru!..'
            ], [
                'текст <ul><li>текст</li><li>текст</li><b>текст</b><br></ul> текст',
                "текст <ul>\n<li>текст</li>\n<li>текст</li>\n</ul>\n текст"
            ], [
                'текст <li>текст</li> <li>текст</li> текст',
                'текст текст текст текст'
            ], [
                '<b>"текст" текст "текст "текст" текст" "..."</b>',
                '<b>«текст» текст «текст „текст“ текст» «...»</b>'
            ], [
                '<pre>"текст" текст "текст "текст" текст" "..."</pre>',
                '<pre>&#34;текст&#34; текст &#34;текст &#34;текст&#34; текст&#34; &#34;...&#34;</pre>'
            ], [
                'текст &#40; &#41; &#42; &#43; &#44; текст',
                'текст ( ) * + , текст'
            ], [
                'текст - текст',
                'текст — текст'
            ], [
                'текст      текст',
                'текст текст'
            ], [
                'текст #hash... #{tag name} текст',
                'текст <a href="/search/tag/hash/">#hash</a>... <a href="/search/tag/tag%20name/">#tag name</a> текст'
            ], [
                'текст <b>#hashtag #taghash, #htag</b> текст',
                'текст <b><a href="/search/tag/hashtag/">#hashtag</a> <a href="/search/tag/taghash/">#taghash</a>, <a href="/search/tag/htag/">#htag</a></b> текст'
            ], [
                'текст <a href="http://ya.ru">текст</a> текст',
                'текст <a href="http://ya.ru" rel="nofollow">текст</a> текст'
            ], [
                'текст <a href="//ya.ru">текст</a> текст',
                'текст <a href="//ya.ru" rel="nofollow">текст</a> текст'
            ], [
                'текст <a href = "http://ya.ru" title="text" >текст</a> текст',
                'текст <a href="http://ya.ru" title="text" rel="nofollow">текст</a> текст'
            ], [
                'текст <a href = "http://ya.ru" args="test">текст</a> текст',
                'текст <a href="http://ya.ru" rel="nofollow">текст</a> текст'
            ], [
                'текст <a href="http://ya.ru" title = text>текст</a> текст',
                'текст <a href="http://ya.ru" title="text" rel="nofollow">текст</a> текст'
            ], [
                'текст <a href="http://ya.ru" target=_blank title=/">текст</a> текст',
                'текст <a href="http://ya.ru" target="_blank" title="/&#34;" rel="nofollow">текст</a> текст'
            ], [
                'текст <a href="javascript:alert(1)">текст</a> текст',
                'текст текст текст'
            ], [
                '<b>текст текст</b> <cut> <b>текст <cut> текст</b>',
                '<b>текст текст</b> <cut> <b>текст текст</b>'
            ], [
                'текст <video src="http://vimeo.com/1234567890"> текст',
                "текст <video src=\"http://vimeo.com/1234567890\">\n текст"
            ], [
                'текст <video src="http://rutube.ru/1234567890"> текст',
                'текст текст'
            ], [
                'текст <video src="http://youtube.com.exploit.net/1234567890"> текст',
                'текст текст'
            ], [
                'текст <video src="http://youtube.com.exploit.net"> текст',
                'текст текст'
            ], [
                'текст <video src="//youtube.com"> текст',
                "текст <video src=\"//youtube.com\">\n текст"
            ], [
                'текст <video src="//youtube.com/"> текст',
                "текст <video src=\"//youtube.com/\">\n текст"
            ],
        );
    }

    /**
     * @dataProvider textsDataProvider
     */
    public function testParse($text, $expected)
    {
        $result = static::$qevix->parse($text, $errors);
        $this->assertEquals($result, $expected);
    }
}
