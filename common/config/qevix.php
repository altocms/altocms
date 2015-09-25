<?php
/*
 * jevix.php
 *
 * Tags for Jevix configuration
 */

return array(
    'default' => array(
        // Разрешённые теги
        'cfgAllowTags' => array(
            // вызов метода с параметрами
            array(
                array('p', 'ls', 'cut', 'a', 'img', 'i', 'b', 'u', 's', 'small', 'video', 'em', 'strong', 'nobr', 'li',
                      'ol', 'ul', 'sup', 'abbr', 'sub', 'acronym', 'h4', 'h5', 'h6', 'br', 'hr', 'pre', 'code',
                      'codeline', 'object', 'param', 'embed', 'blockquote', 'iframe', 'table', 'tbody', 'thead', 'th',
                      'tr', 'td', 'div'),
            ),
        ),
        // Коротие теги типа
        'cfgSetTagShort' => array(
            array(
                array('br', 'img', 'hr', 'cut', 'ls')
            ),
        ),
        // Преформатированные теги
        'cfgSetTagPreformatted'     => array(
            array(
                array('pre', 'code', 'codeline', 'video')
            ),
        ),
        // Разрешённые параметры тегов
        'cfgAllowTagParams' => array(
            // вызов метода
            array(
                'img',
                array('src',
                      'title',
                      'data-rel',
                      'data-src',
                      'alt'    => '#text',
                      'align'  => array('right', 'left', 'center', 'middle'),
                      'width'  => '#regexp(\d+(%|px)?)',
                      'height' => '#int',
                      'hspace' => '#int',
                      'vspace' => '#int',
                      'class'  => '#text',
                )
            ),
            // следующий вызов метода
            array(
                'a',
                array('title',
                      'href',
                      'rel' => '#text',
                      'name' => '#text',
                      'target' => array('_blank'),
                      'class' => array('ls-user', 'topic-photoset-item'),
                      'data-alto-role' => '#text',
                      'data-api' => '#text',
                )
            ),
            // и т.д.
            array(
                'cut',
                array('name')
            ),
            array(
                'object',
                array('width' => '#int', 'height' => '#int', 'type'  => '#text',
                      'data'  => array('#domain' => array(
                              'youtube.com', 'rutube.ru', 'vimeo.com', 'player.vimeo.com',
                          )),
                )
            ),
            array(
                'param',
                array('name' => '#text', 'value' => '#text')
            ),
            array(
                'embed',
                array('src' => array('#domain' => array(
                            'youtube.com', 'rutube.ru', 'vimeo.com', 'player.vimeo.com', 'static.googleusercontent.com',
                        )),
                    'type'              => '#text',
                    'allowscriptaccess' => '#text',
                    'allowfullscreen'   => '#text',
                    'width'             => '#int',
                    'height'            => '#int',
                    'flashvars'         => '#text',
                    'wmode'             => '#text',
                )
            ),
            array(
                'acronym',
                array('title')
            ),
            array(
                'abbr',
                array('title')
            ),
            array(
                'iframe',
                array('width' => '#int',
                      'height' => '#int',
                      'src'   => array('#domain' => array(
                          'youtube.com', 'rutube.ru', 'vimeo.com', 'player.vimeo.com', 'vk.com', 'vkontakte.ru',
                          'slideshare.net', 'mixcloud.com', 'soundcloud.com', 'maps.google.ru', 'issuu.com',
                      )))
            ),
            array(
                'ls',
                array('user' => '#text')
            ),
            array(
                'th',
                array('colspan' => '#int', 'rowspan' => '#int', 'height'  => '#int', 'width' => '#int',
                      'align' => array('right', 'left', 'center', 'justify'),
                )
            ),
            array(
                'td',
                array('colspan' => '#int', 'rowspan' => '#int', 'height'  => '#int', 'width' => '#int',
                      'align' => array('right', 'left', 'center', 'justify'),
                )
            ),
            array(
                'table',
                array('border' => '#int', 'cellpadding' => '#int', 'cellspacing' => '#int', 'height' => '#int', 'width' => '#int',
                      'align'  => array('right', 'left', 'center'),
                )
            ),
            array(
                'div',
                array('class'=> array('alto-photoset', 'alto-photoset js-topic-photoset-list', 'spoiler', 'spoiler-title', 'spoiler-slider', 'spoiler-text'), 'data-width')
            ),
        ),
        // Параметры тегов являющиеся обязательными
        'cfgSetTagParamsRequired' => array(
            array(
                'img',
                'src'
            ),
        ),
        // Теги которые необходимо вырезать из текста вместе с контентом
        'cfgSetTagCutWithContent' => array(
            array(
                array('script', 'style')
            ),
        ),
        // Вложенные теги
        'cfgSetTagChilds' => array(
            array(
                'ul',
                array('li'),
                false,
                true
            ),
            array(
                'ol',
                array('li'),
                false,
                true
            ),
            array(
                'object',
                'param',
                false,
                true
            ),
            array(
                'object',
                'embed',
                false,
                false
            ),
            array(
                'table',
                array('tr', 'tbody', 'thead'),
                false,
                true
            ),
            array(
                'tbody',
                array('tr', 'td'),
                false,
                true
            ),
            array(
                'thead',
                array('tr', 'th'),
                false,
                true
            ),
            array(
                'tr',
                array('td', 'th'),
                false,
                true
            ),
        ),
        // Если нужно оставлять пустые не короткие теги
        'cfgSetTagIsEmpty' => array(
            array(
                array('param', 'embed', 'a', 'iframe')
            ),
        ),
        // Не нужна авто-расстановка <br>
        'cfgSetTagNoAutoBr' => array(
            array(
                array('ul', 'ol', 'object', 'table', 'tr', 'tbody', 'thead')
            )
        ),
        // Теги с обязательными параметрами
        'cfgSetTagParamDefault' => array(
            array(
                'embed',
                'wmode',
                'opaque',
                true
            ),
        ),
        // Отключение авто-добавления <br>
        'cfgSetAutoBrMode' => array(
            array(
                true
            )
        ),
        // Автозамена
        'cfgSetAutoReplace' => array(
            array('+/-', '(c)', '(с)', '(C)', '(С)', '(r)', '(R)'),
            array('±', '©', '©', '©', '©', '®', '®'),
        ),
        // Список допустимых протоколов для ссылок
        'cfgSetLinkProtocolAllow' => array(
            array(
                array('http', 'https', 'ftp')
            )
        ),
        'cfgSetTagNoTypography' => array(
            array(
                array('code', 'video', 'object')
            ),
        ),
        // Теги, после которых необходимо пропускать одну пробельную строку
        'cfgSetTagBlockType' => array(
            array(
                array('h4', 'h5', 'h6', 'ol', 'ul', 'blockquote', 'pre', 'table', 'iframe', 'p')
            )
        ),
    ),

    // настройки для обработки текста в результатах поиска
    'search'  => array(
        // Разрешённые теги
        'cfgAllowTags' => array(
            // вызов метода с параметрами
            array(
                array('span'),
            ),
        ),
        // Разрешённые параметры тегов
        'cfgAllowTagParams' => array(
            array(
                'span',
                array('class' => '#text')
            ),
        ),
    ),
);

// EOF