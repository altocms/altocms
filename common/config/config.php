<?php
/*-------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *-------------------------------------------------------
 */

/********************************************************
 * ATTENTION! Don't touch this file!
 *
 * All changes of settings you need to do in application
 * configuration file, which usually placed here:
 * /app/config/config.php
 ********************************************************/

/********************************************************
 * ВНИМАНИЕ! Не вносите изменения в этот файл!
 *
 * Все изменения настроек нужно выполнять в файле
 * конфигурации приложения, который обычно находится здесь:
 * /app/config/config.php
 ********************************************************/

/**
 * Настройки HTML вида
 */
$config['view']['skin']             = 'start-kit';                  // скин
$config['view']['theme']            = 'default';                    // тема
$config['view']['name']             = 'Your Site Name';             // название сайта
$config['view']['description']      = 'Description your site';      // meta tag description
$config['view']['keywords']         = 'site, google, internet';     // meta tag keywords
$config['view']['wysiwyg']          = false;    // использовать или нет визуальный редактор
$config['view']['noindex']          = true;     // "прятать" или нет ссылки от поисковиков, оборачивая их в тег <noindex> и добавляя rel="nofollow"
$config['view']['img_resize_width'] = 570;      // до какого размера в пикселях ужимать картинку по ширине при загрузки её в топики и комменты
$config['view']['img_max_width']    = 5000;     // максимальная ширина загружаемых изображений в пикселях
$config['view']['img_max_height']   = 5000;     // максимальная высота загружаемых изображений в пикселях
$config['view']['img_max_size_url'] = 500;      // максимальный размер картинки в kB для загрузки по URL

/**
 * Настройки СЕО для вывода топиков
 */
$config['seo']['description_words_count'] = 20;               // количество слов из топика для вывода в метатег description

/**
 * Настройка основных блоков
 */
$config['block']['stream']['row'] = 20;                       // сколько записей выводить в блоке "Прямой эфир"
$config['block']['stream']['show_tip'] = true;                // выводить или нет всплывающие сообщения в блоке "Прямой эфир"
$config['block']['blogs']['row']  = 10;                       // сколько записей выводить в блоке "Блоги"
$config['block']['tags']['tags_count'] = 70;                  // сколько тегов выводить в блоке "теги"
$config['block']['tags']['personal_tags_count'] = 70;         // сколько тегов пользователя выводить в блоке "теги"

/**
 * Настройка пагинации
 */
$config['pagination']['pages']['count'] = 9;                  // количество ссылок на другие страницы в пагинации


/**
 * Настройка путей
 * Если необходимо установить движек в директорию(не корень сайта) то следует сделать так:
 * $config['path']['root']['web']    = 'http://'.$_SERVER['HTTP_HOST'].'/subdir';
 * $config['path']['root']['server'] = $_SERVER['DOCUMENT_ROOT'].'/subdir';
 * и возможно придётся увеличить значение $config['path']['offset_request_url'] на число вложенных директорий,
 * например, для директории первой вложенности www.site.ru/community/ поставить значение равное 1
 *
 * Как правило полный путь до папки или файла содержит в названии 'dir'
 * URL-путь содержит в названии 'url'
 */
if (isset($_SERVER['HTTP_HOST'])) {
    $config['path']['root']['url']      = 'http://'. $_SERVER['HTTP_HOST'] . '/';   // полный WEB адрес сайта
} else {
    // for CLI scripts. or you can append "HTTP_HOST=http://yoursite.url" before script run command
    $config['path']['root']['url']      = null;
}
$config['path']['root']['dir'] = ALTO_DIR . '/';

/**
 * Параметры сервера для статики. По умолчанию совпадают с основным сервером
 */
$config['path']['static']['url']        = '___path.root.url___';        // Полный URL до static-сервера
$config['path']['static']['dir']        = '___path.root.dir___';        // Полный путь до static-сервера в файловой системе

$config['path']['root']['engine_lib']   = '___path.root.web___/engine/libs/';  // Путь до библиотек в файловой системе

$config['path']['uploads']['root']      = '/uploads';                          // папка для загрузки файлов
$config['path']['uploads']['images']    = '___path.uploads.root___/images/';
$config['path']['uploads']['files']     = '___path.uploads.root___/files/';

$config['path']['offset_request_url']   = 0;                                   // иногда помогает если сервер использует внутренние реврайты

$config['path']['tmp']['dir']           = '___path.root.dir___/_tmp/';           // путь к папке для временных файлов
$config['path']['runtime']['dir']       = '___path.root.dir___/_run/';           // путь к папке для runtime-файлов
$config['path']['runtime']['url']       = '___path.root.url___/_run/';              // путь к папке для runtime-файлов

$config['path']['templates']['dir']     = '___path.dir.common___/templates/';
$config['path']['frontend']['dir']      = '___path.dir.common___/templates/frontend/';
$config['path']['frontend']['url']      = '___path.root.url___/common/templates/frontend/';

$config['path']['skins']['dir']         = '___path.templates.dir___/skin/';             // путь к папке для скинов
$config['path']['skin']['dir']          = '___path.skins.dir___/___view.skin___/';      // путь к папке текущего скина
$config['path']['skin']['url']          = '___path.root.url___common/templates/skin/___view.skin___/';    // URL-путь к папке текущего скина
$config['path']['skin']['assets']['url']= '___path.runtime.url___assets/skin/___view.skin___/';
$config['path']['skin']['assets']['dir']= '___path.runtime.dir___assets/skin/___view.skin___/';

/**
 * Следующие параметры определяем для совместимости с LS
 * LS-compatible
 */
$config['path']['root']['web']          = '___path.root.url___';        // Определяем для совместимости с LS
$config['path']['root']['server']       = '___path.root.dir___';        // Определяем для совместимости с LS
$config['path']['static']['root']       = '___path.static.url___';      // Определяем для совместимости с LS
$config['path']['root']['engine']       = '___path.dir.engine___/';     // Определяем для совместимости с LS
$config['path']['static']['skin']       = '___path.skin.url___/';       // Определяем для совместимости с LS

/**
 * Настройки шаблонизатора Smarty
 */
$config['path']['smarty']['template'] = '___path.skins.dir___/___view.skin___/';
$config['path']['smarty']['compiled'] = '___path.tmp.dir___/templates/___view.skin___-___view.theme___/compiled/';
$config['path']['smarty']['cache']    = '___path.tmp.dir___/templates/___view.skin___-___view.theme___/cache/';
$config['path']['smarty']['plug']     = '___path.dir.engine___/classes/modules/viewer/plugs/';

$config['smarty']['compile_check']          = true;   // Проверять или нет файлы шаблона на изменения перед компиляцией, false может значительно увеличить быстродействие, но потребует ручного удаления кеша при изменения шаблона
$config['smarty']['force_compile']          = false;  // Принудительно компилировать шаблоны (отменяет действие 'compile_check')
$config['smarty']['merge_compiled_includes']= false;  // Слияние скомпилированных шаблонов (увеличивает скорость рендеринга при большом числе подшаблонов)
$config['smarty']['cache_lifetime']         = false;  // Кеширование отрендеренных шаблонов

/**
 * Настройки плагинов
 */
$config['sys']['plugins']['activation_file'] = 'plugins.dat'; // файл со списком активных плагинов в каталоге /plugins/

/**
 * Настройки куков
 */
$config['sys']['cookie']['host'] = null;                    // хост для установки куков
$config['sys']['cookie']['path'] = '/';                     // путь для установки куков
$config['sys']['cookie']['time'] = 60 * 60 * 24 * 3;        // время жизни куки когда пользователь остается залогиненым на сайте, 3 дня

/**
 * Настройки сессий
 */
$config['sys']['session']['standart'] = true;                               // Использовать или нет стандартный механизм сессий
$config['sys']['session']['name']     = 'PHPSESSID';                        // название сессии
$config['sys']['session']['timeout']  = null;                               // Тайм-аут сессии в секундах
$config['sys']['session']['host']     = '___sys.cookie.host___';            // хост сессии в куках
$config['sys']['session']['path']     = '___sys.cookie.path___';            // путь сессии в куках
/**
 * Настройки почтовых уведомлений
 */
$config['sys']['mail']['type']             = 'mail';                        // Какой тип отправки использовать
$config['sys']['mail']['from_email']       = 'admin@admin.adm';             // Мыло с которого отправляются все уведомления
$config['sys']['mail']['from_name']        = 'Почтовик ___view.name___';    // Имя с которого отправляются все уведомления
$config['sys']['mail']['charset']          = 'UTF-8';                // Какую кодировку использовать в письмах
$config['sys']['mail']['smtp']['host']     = 'localhost';            // Настройки SMTP - хост
$config['sys']['mail']['smtp']['port']     = 25;                     // Настройки SMTP - порт
$config['sys']['mail']['smtp']['user']     = '';                     // Настройки SMTP - пользователь
$config['sys']['mail']['smtp']['password'] = '';                     // Настройки SMTP - пароль
$config['sys']['mail']['smtp']['secure']   = '';                     // Настройки SMTP - протокол шифрования: tls, ssl
$config['sys']['mail']['smtp']['auth']     = true;                   // Использовать авторизацию при отправке
$config['sys']['mail']['include_comment']  = true;                   // Включает в уведомление о новых комментах текст коммента
$config['sys']['mail']['include_talk']     = true;                   // Включает в уведомление о новых личных сообщениях текст сообщения

/**
 * Настройки кеширования
 */
/*
 * Режим автокеширования
 *
 * Если true и задан тип кеширования, то кеширование работает всегда
 * Если false и задан тип кеширования, то кеширование работает только по запросу
 */
$config['sys']['cache']['use']    = false;
/*
 * Доступные типы (виды) кеширования
 */
$config['sys']['cache']['backends'] = array(
    'file'   => 'File',         // файловое кеширование
    'memory' => 'Memcached',    // используется Memcached
    'xcache' => 'Xcache',       // используется XCache
    'tmp'    => 'Tmp',          // используется временное хранилище в памяти
);

/*
 * Разрешить принудительное кеширование в модулях. Если разрешено, то в модулях можно программно задать
 * принудительное кеширование, даже если тип кеширования задан false
 *
 * Возможные значения:
 *  - array(...)    - разрешено принудительное кеширование заданных видов
 *  - true          - разрешено принудительное кеширование любого вида
 *  - false         - запрещено принудительное кеширование
 */
$config['sys']['cache']['force']    = array('file', 'tmp');

/*
 * Тип кеширования:
 *      file    - файловое
 *      memory  - используется Memcached
 *      xcache  - используется XCache
 */
$config['sys']['cache']['type']   = 'file';                         // тип кеширования по умолчанию
$config['sys']['cache']['dir']    = '___path.tmp.dir___/cache/';    // каталог для файлового кеша
$config['sys']['cache']['prefix'] = 'alto_cache';                   // префикс кеширования, чтоб можно было на одной машине держать несколько сайтов с общим кешевым хранилищем
$config['sys']['cache']['directory_level'] = 1;         // уровень вложенности директорий файлового кеша
$config['sys']['cache']['solid']  = true;               // Настройка использования раздельного и монолитного кеша для отдельных операций
/*
 * Задержка "протухания" кеша при конкурирующих запросах (сек)
 *
 * Если установлено в 0 или false, то поддержка конкурирующих запросов отключена
 */
$config['sys']['cache']['concurrent_delay']    = 0; //60;

/**
 * Настройки логирования
 */
$config['sys']['logs']['dir']            = '___path.tmp.dir___/logs/';       // папка для логов
$config['sys']['logs']['file']           = 'log.log';       // файл общего лога
$config['sys']['logs']['sql_query']      = false;            // логировать или нет SQL запросы
$config['sys']['logs']['sql_query_file'] = 'sql_query.log'; // файл лога SQL запросов
$config['sys']['logs']['sql_error']      = true;            // логировать или нет ошибки SQl
$config['sys']['logs']['sql_error_file'] = 'sql_error.log'; // файл лога ошибок SQL
$config['sys']['logs']['cron']           = true;            // логировать или нет cron скрипты
$config['sys']['logs']['cron_file']      = 'cron.log';      // файл лога запуска крон-процессов
$config['sys']['logs']['profiler']       = false;           // логировать или нет профилирование процессов
$config['sys']['logs']['profiler_file']  = 'profiler.log';  // файл лога профилирования процессов

$config['sys']['logs']['error_file']        = 'error.log';  // файл лога ошибок
$config['sys']['logs']['error_extinfo']     = false;        // выводить ли дополнительную информацию в лог ошибок
$config['sys']['logs']['error_callstack']   = false;        // выводить стек вызовов в лог ошибок
$config['sys']['logs']['error_norepeat']    = true;         // не повторять вывод одинаковых ошибок

$config['sys']['logs']['hacker_console']  = false;          // позволяет удобно выводить логи дебага через функцию dump(), использя "хакерскую" консоль Дмитрия Котерова

$config['sys']['logs']['size_for_rotate'] = 1000000;        // максимальный размер для ротации логов (если 0 - без ротации)

/*
 * Параметры для определения IP-адрес посетителя:
 *   'trusted'          - ключи переменной $_SERVER, где нужно искать IP-адрес
 *   'non_trusted'      - ключи переменной $_SERVER, где НЕ нужно искать IP-адрес
 *   'multi_backward'   - если в $_SERVER[<key>] несколько адресов, то выбираем с конца
 *   'exclude'          - список исключаемых IP-адресов
 *   'exclude_server'   - IP-адрес посетителя не может совпадать с IP-адресом сервера
 *   'exclude_private'  - исключать IP частных сетей
 *   'default'          - если IP так и не определен
 */
$config['sys']['ip']['trusted'] = array('REMOTE_ADDR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_VIA');
$config['sys']['ip']['non_trusted'] = array();
$config['sys']['ip']['backward'] = true;
$config['sys']['ip']['exclude'] = array('127.0.0.1', 'fe80::1', '::1');
$config['sys']['ip']['exclude_server'] = true;
$config['sys']['ip']['exclude_private'] = true;
$config['sys']['ip']['default'] = '127.0.0.1';

/**
 * Общие настройки
 */
$config['general']['close']['mode']     = false; // использовать закрытый режим работы сайта, сайт будет доступен только авторизованным пользователям
$config['general']['close']['actions']  = array('login', 'registration', 'captcha'); // enabled actions in closed mode
$config['general']['rss_editor_mail']   = '___sys.mail.from_email___'; // мыло редактора РСС
$config['general']['reg']['invite']     = false; // использовать режим регистрации по приглашению или нет. Если использовать, то регистрация будет доступна ТОЛЬКО по приглашениям!
$config['general']['reg']['activation'] = false; // использовать активацию при регистрации или нет

/**
 * Настройки ACL(Access Control List — список контроля доступа)
 */
$config['acl']['create']['blog']['rating']                =  1;     // порог рейтинга при котором юзер может создать коллективный блог
$config['acl']['create']['comment']['rating']             = -10;    // порог рейтинга при котором юзер может добавлять комментарии
$config['acl']['create']['comment']['limit_time']         =  10;    // время в секундах между постингом комментариев, если 0 то ограничение по времени не будет работать
$config['acl']['create']['comment']['limit_time_rating']  = -1;     // рейтинг, выше которого перестаёт действовать ограничение по времени на постинг комментов. Не имеет смысла при $config['acl']['create']['comment']['limit_time']=0
$config['acl']['create']['topic']['limit_time']           =  240;   // время в секундах между созданием записей, если 0 то ограничение по времени не будет работать
$config['acl']['create']['topic']['limit_time_rating']    =  5;     // рейтинг, выше которого перестаёт действовать ограничение по времени на создание записей
$config['acl']['create']['topic']['limit_rating']         =  -20;   // порог рейтинга при котором юзер может создавать топики (учитываются любые блоги, включая персональные), как дополнительная защита от спама/троллинга
$config['acl']['create']['talk']['limit_time']          =  300;     // время в секундах между отправкой инбоксов, если 0 то ограничение по времени не будет работать
$config['acl']['create']['talk']['limit_time_rating']   =  1;       // рейтинг, выше которого перестаёт действовать ограничение по времени на отправку инбоксов
$config['acl']['create']['talk_comment']['limit_time']        =  10;// время в секундах между отправкой инбоксов, если 0 то ограничение по времени не будет работать
$config['acl']['create']['talk_comment']['limit_time_rating'] =  5; // рейтинг, выше которого перестаёт действовать ограничение по времени на отправку инбоксов
$config['acl']['create']['wall']['limit_time']          = 20;   // рейтинг, выше которого перестаёт действовать ограничение по времени на отправку сообщений на стену
$config['acl']['create']['wall']['limit_time_rating']   = 0;    // рейтинг, выше которого перестаёт действовать ограничение по времени на отправку сообщений на стену
$config['acl']['vote']['comment']['rating']             = -3;   // порог рейтинга при котором юзер может голосовать за комментарии
$config['acl']['vote']['blog']['rating']                = -5;   // порог рейтинга при котором юзер может голосовать за блог
$config['acl']['vote']['topic']['rating']               = -7;   // порог рейтинга при котором юзер может голосовать за топик
$config['acl']['vote']['user']['rating']                = -1;   // порог рейтинга при котором юзер может голосовать за пользователя
$config['acl']['vote']['topic']['limit_time']           = 60*60*24*20; // ограничение времени голосования за топик
$config['acl']['vote']['comment']['limit_time']         = 60*60*24*5;  // ограничение времени голосования за комментарий

/**
 * Настройки модулей
 */
// Модуль Blog
$config['module']['blog']['per_page']        = 20;                  // Число блогов на страницу
$config['module']['blog']['users_per_page']  = 20;                  // Число пользователей блога на страницу
$config['module']['blog']['personal_good']   = -5;                  // Рейтинг топика в персональном блоге ниже которого он считается плохим
$config['module']['blog']['collective_good'] = -3;                  // рейтинг топика в коллективных блогах ниже которого он считается плохим
$config['module']['blog']['index_good']      =  8;                  // Рейтинг топика выше которого(включительно) он попадает на главную
$config['module']['blog']['encrypt']         = 'alto';              // Ключ XXTEA шифрования идентификаторов в ссылках приглашения в блоги
$config['module']['blog']['avatar_size'] = array(100,64,48,24,0);   // Список размеров аватаров у блога. 0 - исходный размер

// Модуль Topic
$config['module']['topic']['new_time']   = 60*60*24*1;              // Время в секундах в течении которого топик считается новым
$config['module']['topic']['per_page']   = 10;                      // Число топиков на одну страницу
$config['module']['topic']['max_length'] = 15000;                   // Максимальное количество символов в одном топике
$config['module']['topic']['link_max_length'] = 500;                // Максимальное количество символов в одном топике-ссылке
$config['module']['topic']['question_max_length'] = 500;            // Максимальное количество символов в одном топике-опросе
$config['module']['topic']['allow_empty_tags'] = true;              // Разрешать или нет не заполнять теги
$config['module']['topic']['max_filesize_limit'] = 5*1024*1024;     // максимальный размер загружаемого файла в байтах (по умолчанию 5мб)
$config['module']['topic']['upload_mime_types'] = array('zip','rar','gz','mp3','png', 'doc', 'docx', 'pdf','djv','djvu'); //расширения файлов, которые можно прикреплять к топикам
$config['module']['topic']['draft_link'] = false;                   // разрешить показывать черновик по прямой ссылке

// Модуль Upload
$config['module']['upload']['max_filesize'] = '5M';     // максимальный размер загружаемого файла в байтах (по умолчанию 5МБ)
$config['module']['upload']['file_extensions'] = array('gif','png','jpg','jpeg'); //расширения файлов, которые можно загружать + 'module.topic.upload_mime_types'

/*
 * Настройка ЧПУ топика
 * Допустимые параметры:
 *      %year%       - год топика
 *      %month%      - месяц
 *      %day%        - день
 *      %hour%       - час
 *      %minute%     - минуты
 *      %second%     - секунды (54)
 *      %login%      - логин автора топика (admin)
 *      %blog_url%   - url коллективного блога (для личных блогов будет заменен на логин автора)
 *      %topic_type% - тип топика
 *      %topic_id%   - id топика
 *      %topic_url%  - относительный URL топика
 *
 * В шаблоне обязательно должен быть %topic_id% или %topic_url%
 */
$config['module']['topic']['url'] = '%topic_id%.html';          // постоянная ссылка на топик (permalink)

// Модуль User
$config['module']['user']['per_page']    = 15;                  // Число юзеров на страницу на странице статистики и в профиле пользователя
$config['module']['user']['friend_on_profile']    = 15;         // Ограничение на вывод числа друзей пользователя на странице его профиля
$config['module']['user']['friend_notice']['delete'] = false;   // Отправить talk-сообщение в случае удаления пользователя из друзей
$config['module']['user']['friend_notice']['accept'] = false;   // Отправить talk-сообщение в случае одобрения заявки на добавление в друзья
$config['module']['user']['friend_notice']['reject'] = false;   // Отправить talk-сообщение в случае отклонения заявки на добавление в друзья
$config['module']['user']['avatar_size'] = array(100,64,48,24,0); // Список размеров аватаров у пользователя. 0 - исходный размер

$config['module']['user']['login']['min_size'] = 3;             // Минимальное количество символов в логине
$config['module']['user']['login']['max_size'] = 30;            // Максимальное количество символов в логине
$config['module']['user']['login']['charset'] = '0-9a-z_\-';    // Допустимые в логине пользователя символы
$config['module']['user']['login']['disabled'] = array('admin', 'administrator', 'moderator', 'new');  // недопустимые имена логинов

$config['module']['user']['profile_url'] = 'profile/%login%';   // ссылка на профиль пользователя
$config['module']['user']['profile_photo_size'] = '240x340';    // размер фотопрофиля по умолчанию
$config['module']['user']['profile_avatar_size'] = 100;         // размер аватара по умолчанию

$config['module']['user']['time_active'] = 60*60*24*7;          // Число секунд с момента последнего посещения пользователем сайта, в течение которых он считается активным
$config['module']['user']['usernote_text_max'] = 250;           // Максимальный размер заметки о пользователе
$config['module']['user']['usernote_per_page'] = 20;            // Число заметок на одну страницу
$config['module']['user']['userfield_max_identical'] = 2;       // Максимальное число контактов одного типа
$config['module']['user']['profile_photo_width'] = 250;         // ширина квадрата фотографии в профиле, px
$config['module']['user']['name_max'] = 30;                     // максимальная длинна имени в профиле пользователя
$config['module']['user']['captcha_use_registration'] = true;   // проверять поле капчи при регистрации пользователя
$config['module']['user']['max_session_history'] = 50;          // число хранимых сессий пользователя, если 0, то хранятся все сессии

// Модуль Comment
$config['module']['comment']['per_page'] = 20;          // Число комментариев на одну страницу(это касается только полного списка комментариев прямого эфира)
$config['module']['comment']['bad']      = -5;          // Рейтинг комментария, начиная с которого он будет скрыт
$config['module']['comment']['max_tree'] = 7;           // Максимальная вложенность комментов при отображении
$config['module']['comment']['use_nested'] = false;     // Использовать или нет nested set при выборке комментов, увеличивает производительность при большом числе комментариев + позволяет делать постраничное разбиение комментов
$config['module']['comment']['nested_per_page'] = 0;    // Число комментов на одну страницу в топике, актуально только при use_nested = true
$config['module']['comment']['nested_page_reverse'] = true;     // Определяет порядок вывода страниц. true - последние комментарии на первой странице, false - последние комментарии на последней странице
$config['module']['comment']['favourite_target_allow'] = array('topic'); // Список типов комментов, которые разрешено добавлять в избранное
$config['module']['comment']['edit']['enable'] = '5 minutes';   // В течение какого времени можно редактировать комментарии
$config['module']['comment']['edit']['rest_time'] = true;       // Показывать ли оставшееся время для редактирования комментария
$config['module']['comment']['min_length'] = 2;             // Min length of comments
$config['module']['comment']['max_length'] = 16000;         // Max length of comments (0 - no limit)

// Модуль Talk
$config['module']['talk']['per_page']   = 30;       // Число приватных сообщений на одну страницу
$config['module']['talk']['encrypt']    = 'alto';   // Ключ XXTEA шифрования идентификаторов в ссылках
$config['module']['talk']['max_users']  = 15;       // Максимальное число адресатов в одном личном сообщении

// Модуль Lang
$config['module']['lang']['delete_undefined'] = true;   // Если установлена true, то модуль будет автоматически удалять из языковых конструкций переменные вида %%var%%, по которым не была произведена замена

// Модуль Notify
$config['module']['notify']['delayed']      = false;    // Указывает на необходимость использовать режим отложенной рассылки сообщений на email
$config['module']['notify']['insert_single']= false;    // Если опция установлена в true, систему будет собирать записи заданий удаленной публикации, для вставки их в базу единым INSERT
$config['module']['notify']['per_process']  = 10;       // Количество отложенных заданий, обрабатываемых одним крон-процессом
$config['module']['notify']['dir']          = 'emails'; // Относительный (относительно папки скина) путь до папки с шаблонами писем
$config['module']['notify']['prefix']       = 'email.'; // Префикс шаблонов емэйлов

// Модуль Image
$config['module']['image']['default']['watermark_use']        = false;
$config['module']['image']['default']['watermark_type']       = 'text';
$config['module']['image']['default']['watermark_position']   = '0,24';
$config['module']['image']['default']['watermark_text']       = '(c) Alto CMS';
$config['module']['image']['default']['watermark_font']       = 'arial';
$config['module']['image']['default']['watermark_font_color'] = '255,255,255';
$config['module']['image']['default']['watermark_font_size']  = '10';
$config['module']['image']['default']['watermark_font_alfa']  = '0';
$config['module']['image']['default']['watermark_back_color'] = '0,0,0';
$config['module']['image']['default']['watermark_back_alfa']  = '40';
$config['module']['image']['default']['watermark_image']      = false;
$config['module']['image']['default']['watermark_min_width']  = 200;
$config['module']['image']['default']['watermark_min_height'] = 130;
$config['module']['image']['default']['round_corner']         = false;
$config['module']['image']['default']['round_corner_radius']  = '18';
$config['module']['image']['default']['round_corner_rate']    = '40';
$config['module']['image']['default']['path']['watermarks']   = '___path.dir.libs___/LiveImage/watermarks/';
$config['module']['image']['default']['path']['fonts']        = '___path.dir.libs___/LiveImage/fonts/';
$config['module']['image']['default']['jpg_quality']          = 95;  // Число от 0 до 100

$config['module']['image']['foto']['watermark_use']  = false;
$config['module']['image']['foto']['round_corner']   = false;

$config['module']['image']['topic']['watermark_use']  = false;
$config['module']['image']['topic']['round_corner']   = false;

// Параметры для загружаемых изображений по умолчанию
$config['module']['image']['preset']['default'] = array(
    'driver' => 'Gmagick,Imagick,GD', // 'GD', 'Imagick' or 'Gmagick', or several libs separated by comma
    'jpg_quality' => 70,
    'watermark' => array(
        'use' => false,
        'type' => 'text',
    ),
    'size' => array(
        'width' => 1200,
        'height' => 700,
    ),
);
$config['module']['image']['autoresize'] = true;

// Модуль Security
$config['module']['security']['hash']  = 'alto_security_key'; // "примесь" к строке, хешируемой в качестве security-кода
$config['module']['security']['randomkey']  = false;    // генерация случайных ключей во время одной сессии
$config['module']['security']['password_len']  = 6;     // длина пароля

$config['module']['userfeed']['count_default'] = 10;    // Число топиков в ленте по умолчанию

$config['module']['stream']['count_default'] = 20;      // Число топиков в ленте по умолчанию
$config['module']['stream']['disable_vote_events'] = false;

// Модуль Wall - стена
$config['module']['wall']['count_last_reply'] = 3;      // Число последних ответов на сообщени на стене для отображения в ленте
$config['module']['wall']['per_page'] = 10;             // Число сообщений на стене на одну страницу
$config['module']['wall']['text_max'] = 250;            // Ограничение на максимальное количество символов в одном сообщении на стене
$config['module']['wall']['text_min'] = 1;              // Ограничение на минимальное количество символов в одном сообщении на стене

// Модуль Rating
$config['module']['rating']['blog']['topic_rating_sum'] =0.18;  //Коэффициент суммы рейтинга топиков в блоге для расчета рейтинга блога
$config['module']['rating']['blog']['count_users']      =0.2;   //Коэффициент количества подписчиков в блоге для расчета рейтинга блога
$config['module']['rating']['blog']['topic_count']      =0.15;  //Коэффициент количества топиков в блоге для расчета рейтинга блога

/**
 * Настройка топика-фотосета
 */
$config['module']['image']['photoset']['jpg_quality'] = 100;        // настройка модуля Image, качество обработки фото
$config['module']['topic']['photoset']['photo_max_size'] = 6*1024;  // максимально допустимый размер фото, Kb
$config['module']['topic']['photoset']['count_photos_min'] = 2;     // минимальное количество фоток
$config['module']['topic']['photoset']['count_photos_max'] = 30;    // максимальное количество фоток
$config['module']['topic']['photoset']['per_page'] = 20;            // число фоток для одновременной загрузки
$config['module']['topic']['photoset']['size'] = array(             // список размеров превью, которые необходимо делать при загрузке фото
    array(
        'w'    => 1000,
        'h'    => null,
        'crop' => false,
    ),
    array(
        'w'    => 500,
        'h'    => null,
        'crop' => false,
    ),
    array(
        'w'    => 100,
        'h'    => 65,
        'crop' => true,
    ),
    array(
        'w'    => 50,
        'h'    => 50,
        'crop' => true,
    )
);

// Какие модули должны быть загружены на старте
$config['module']['_autoLoad_'] = array('Hook','Cache','Security','Session','User');

/**
 * Настройка базы данных
 */
$config['db']['params']['host']   = 'localhost';
$config['db']['params']['port']   = '3306';
$config['db']['params']['user']   = 'root';
$config['db']['params']['pass']   = '';
$config['db']['params']['type']   = 'mysqli';    // mysql, mypdo, postgresql, mssql, sqlite, ibase
$config['db']['params']['dbname'] = 'alto';

$config['db']['params']['lazy'] = true; // "ленивое" подключение к базе

/**
 * Настройка таблиц базы данных
 */
$config['db']['table']['prefix'] = 'prefix_';

/*
 * Можно не объявлять таблицы, если их названия совпадают с именами в SQL-запросах,
 * тогда в запросах достаточно подставлять в качестве имени таблицы ?_table_name
 * Либо можно явно определить имя таблицы и использовать так: Config::Get('db.table.user)
 *
$config['db']['table']['user']                  = '___db.table.prefix___user';
*/

$config['db']['tables']['engine'] = 'InnoDB';  // InnoDB или MyISAM
/**
 * Настройка memcache
 */
$config['memcache']['servers'][0]['host'] = 'localhost';
$config['memcache']['servers'][0]['port'] = '11211';
$config['memcache']['servers'][0]['persistent'] = true;
$config['memcache']['compression'] = true;

/**
 * Настройки роутинга
 */
$config['router']['rewrite'] = array();

// Правила реврайта для REQUEST_URI
$config['router']['uri'] = array(
    //'~^(uploads/images/[\w\/\_\-\.]+\.(png|jpg|jpeg|gif)\/?$)~i' => 'img/$1',
    '~^_run/assets/([\w\-\.]+/.+)$~i' => 'asset/$1',
    // запрет обработки статичных файлов с заданными расширениями
    /* допустимые значения:
     *  - @ignore   - запрос игнорируется и его обработка прекращается
     *  - @die(msg) - обработка запроса прекращается с выдачей сообщения msg
     *  - @404      - обработка прекращается с выдачей кода 404
     */
    '~.+\.(js|css|png|jpg|gif|swf|ico|pdf|mov|fla|zip|rar)\/?$~i' => '@404',
);

// Распределение action
$config['router']['page']['error']         = 'ActionError';
$config['router']['page']['registration']  = 'ActionRegistration';
$config['router']['page']['profile']       = 'ActionProfile';
$config['router']['page']['my']            = 'ActionMy';
$config['router']['page']['blog']          = 'ActionBlog';
$config['router']['page']['page']          = 'ActionPage';
$config['router']['page']['index']         = 'ActionIndex';
$config['router']['page']['content']       = 'ActionContent';
$config['router']['page']['filter']        = 'ActionFilter';
$config['router']['page']['download']      = 'ActionDownload';
$config['router']['page']['login']         = 'ActionLogin';
$config['router']['page']['people']        = 'ActionPeople';
$config['router']['page']['settings']      = 'ActionSettings';
$config['router']['page']['tag']           = 'ActionTag';
$config['router']['page']['talk']          = 'ActionTalk';
$config['router']['page']['rss']           = 'ActionRss';
$config['router']['page']['blogs']         = 'ActionBlogs';
$config['router']['page']['search']        = 'ActionSearch';
$config['router']['page']['admin']         = 'ActionAdmin';
$config['router']['page']['ajax']          = 'ActionAjax';
$config['router']['page']['feed']          = 'ActionUserfeed';
$config['router']['page']['stream']        = 'ActionStream';
$config['router']['page']['subscribe']     = 'ActionSubscribe';
$config['router']['page']['img']           = 'ActionImg';

// Глобальные настройки роутинга
$config['router']['config']['action_default']   = 'homepage';
$config['router']['config']['action_not_found'] = 'error';

$config['router']['config']['homepage']   = 'index';

// Автоопределение роутинга экшенов
$config['router']['config']['autodefine'] = true;

/**
 * Подключаемые ресурсы - js- и css-файлы
 * <local_path|URL>
 * <local_path|URL> => <parameters_array>
 *
 * Параметры:
 *      'asset' - указывает на один набор при слиянии файлов
 *      'name'  - "каноническое" имя файла
 *      'place' - место размещения (только для js)
 *      'prepare' - файл готовится, но не включается в HTML
 */
$config['head']['default']['js'] = array(
    /* Vendor libs */
    '___path.frontend.dir___/libs/vendor/html5shiv.min.js' => array('browser' => 'lt IE 9'),
    '___path.frontend.dir___/libs/vendor/jquery-1.10.2.min.js' => array('name' => 'jquery', 'asset' => 'mini'),
    '___path.frontend.dir___/libs/vendor/jquery-migrate-1.2.1.min.js' => array('asset' => 'mini'),
    '___path.frontend.dir___/libs/vendor/jquery-ui/js/jquery-ui-1.10.2.custom.min.js' => array('asset' => 'mini'),
    '___path.frontend.dir___/libs/vendor/jquery-ui/js/localization/jquery-ui-datepicker-ru.js',
    '___path.frontend.dir___/libs/vendor/jquery.browser.js',
    '___path.frontend.dir___/libs/vendor/jquery.scrollto.js',
    '___path.frontend.dir___/libs/vendor/jquery.rich-array.min.js',
    '___path.frontend.dir___/libs/vendor/jquery.form.js',
    //'___path.frontend.dir___/libs/vendor/jquery.jqplugin.js',
    '___path.frontend.dir___/libs/vendor/jquery.cookie.js',
    '___path.frontend.dir___/libs/vendor/jquery.serializejson.js',
    '___path.frontend.dir___/libs/vendor/jquery.file.js',
    '___path.frontend.dir___/libs/vendor/jquery.placeholder.min.js',
    '___path.frontend.dir___/libs/vendor/jquery.charcount.js',
    '___path.frontend.dir___/libs/vendor/jquery.imagesloaded.js',
    '___path.frontend.dir___/libs/vendor/jcrop/jquery.Jcrop.js',
    '___path.frontend.dir___/libs/vendor/markitup/jquery.markitup.js',
    '___path.frontend.dir___/libs/vendor/notifier/jquery.notifier.js',
    '___path.frontend.dir___/libs/vendor/prettify/prettify.js',
    '___path.frontend.dir___/libs/vendor/nprogress/nprogress.js',
    '___path.frontend.dir___/libs/vendor/syslabel/syslabel.js',
    '___path.frontend.dir___/libs/vendor/prettyphoto/js/jquery.prettyphoto.js',
    '___path.frontend.dir___/libs/vendor/rowgrid/jquery.row-grid.min.js' => array('asset' => 'mini'),
    '___path.frontend.dir___/libs/vendor/parsley/parsley.js',
    '___path.frontend.dir___/libs/vendor/parsley/i18n/messages.ru.js',
    //'___path.frontend.dir___/libs/vendor/bootbox/bootbox.min.js' => array('asset' => 'mini'),
    '___path.frontend.dir___/libs/vendor/bootbox/bootbox.js',

    '___path.frontend.dir___/libs/vendor/swfobject/swfobject.js',

    /* swfupload */
    '___path.frontend.dir___/libs/vendor/swfobject/plugin/swfupload.js' => array(
        'name'    => 'swfobject/plugin/swfupload.js',
        'prepare' => true
    ),
    '___path.frontend.dir___/libs/vendor/swfupload/swfupload.js'        => array(
        'name'    => 'swfupload/swfupload.js',
        'prepare' => true
    ),
    '___path.frontend.dir___/libs/vendor/swfupload/swfupload.swf'       => array(
        'name'     => 'swfupload/swfupload.swf',
        'prepare'  => true,
        'compress' => false,
        'merge'    => false
    ),

    /* markitUp */
    '___path.frontend.dir___/libs/vendor/markitup/jquery.markitup.js'       => array(
        'dir_from' => '___path.frontend.dir___/libs/vendor/markitup/',
        'name'     => 'markitup',
    ),

    /* tinyMCE */
    '___path.frontend.dir___/libs/vendor/tinymce_4/tinymce.min.js'       => array(
        'dir_from' => '___path.frontend.dir___/libs/vendor/tinymce_4/',
        'name'     => 'tinymce_4',
        'compress' => false,
        'merge'    => false,
    ),
    '___path.frontend.dir___/libs/vendor/tinymce_4/plugins/*'       => array(
        'dir_from'  => '___path.frontend.dir___/libs/vendor/tinymce_4/',
        'prepare'   => true,
        'compress'  => false,
        'merge'     => false,
    ),
    '___path.frontend.dir___/libs/vendor/tinymce_4/langs/*'       => array(
        'dir_from' => '___path.frontend.dir___/libs/vendor/tinymce_4/',
        'prepare'  => true,
        'compress' => false,
        'merge'    => false,
    ),
    '___path.frontend.dir___/libs/vendor/tinymce_4/skins/*'       => array(
        'dir_from' => '___path.frontend.dir___/libs/vendor/tinymce_4/',
        'prepare'  => true,
        'compress' => false,
        'merge'    => false,
    ),
    '___path.frontend.dir___/libs/vendor/tinymce_4/themes/*'       => array(
        'dir_from' => '___path.frontend.dir___/libs/vendor/tinymce_4/',
        'prepare'  => true,
        'compress' => false,
        'merge'    => false,
    ),

    /* Core */
    '___path.frontend.dir___/libs/js/core/main.js',
    '___path.frontend.dir___/libs/js/core/modal.js',
    '___path.frontend.dir___/libs/js/core/hook.js',

    '___path.frontend.dir___/bootstrap-3/js/bootstrap.min.js' => array('name' => 'bootstrap'),

    /* Engine */
    '___path.frontend.dir___/libs/js/engine/favourite.js',
    '___path.frontend.dir___/libs/js/engine/widgets.js',
    '___path.frontend.dir___/libs/js/engine/pagination.js',
    '___path.frontend.dir___/libs/js/engine/editor.js',
    '___path.frontend.dir___/libs/js/engine/talk.js',
    '___path.frontend.dir___/libs/js/engine/vote.js',
    '___path.frontend.dir___/libs/js/engine/poll.js',
    '___path.frontend.dir___/libs/js/engine/subscribe.js',
    '___path.frontend.dir___/libs/js/engine/geo.js',
    '___path.frontend.dir___/libs/js/engine/wall.js',
    '___path.frontend.dir___/libs/js/engine/usernote.js',
    '___path.frontend.dir___/libs/js/engine/comments.js',
    '___path.frontend.dir___/libs/js/engine/blog.js',
    '___path.frontend.dir___/libs/js/engine/user.js',
    '___path.frontend.dir___/libs/js/engine/userfeed.js',
    '___path.frontend.dir___/libs/js/engine/stream.js',
    '___path.frontend.dir___/libs/js/engine/swfuploader.js',
    '___path.frontend.dir___/libs/js/engine/photoset.js',
    '___path.frontend.dir___/libs/js/engine/toolbar.js',
    '___path.frontend.dir___/libs/js/engine/settings.js',
    '___path.frontend.dir___/libs/js/engine/topic.js',
    //'___path.frontend.dir___/libs/js/engine/admin.js',
    '___path.frontend.dir___/libs/js/engine/userfield.js',
    '___path.frontend.dir___/libs/js/engine/init.js',
);

//потенциально проблемные файлы выводим в футере
$config['footer']['default']['js'] = array(
    'http://yandex.st/share/share.js',
);

$config['head']['default']['css'] = array(
    // Framework styles
    '___path.frontend.dir___/libs/css/reset.css',
    '___path.frontend.dir___/libs/css/helpers.css',
    '___path.frontend.dir___/libs/css/text.css',
    '___path.frontend.dir___/libs/css/dropdowns.css',
    '___path.frontend.dir___/libs/css/buttons.css',
    '___path.frontend.dir___/libs/css/forms.css',
    '___path.frontend.dir___/libs/css/navs.css',
    '___path.frontend.dir___/libs/css/modals.css',
    //'___path.frontend.dir___/libs/css/tooltip.css',
    '___path.frontend.dir___/libs/css/popover.css',
    '___path.frontend.dir___/libs/css/alerts.css',
    '___path.frontend.dir___/libs/css/toolbar.css',
    '___path.frontend.dir___/libs/vendor/nprogress/nprogress.css',
    '___path.frontend.dir___/libs/vendor/syslabel/syslabel.css',
    '___path.frontend.dir___/libs/vendor/prettyphoto/css/prettyphoto.css',
);

/**
 * Параметры компрессии css-файлов
 */
$config['compress']['css']['merge'] = true;         // указывает на необходимость слияния файлов по указанным блокам.
$config['compress']['css']['use']   = false;        // указывает на необходимость компрессии файлов. Компрессия используется только в активированном режиме слияния файлов.
$config['compress']['css']['force']  = false;       // если заданно 'compress.css.merge', то слияние выполняется, даже если результирующий файл есть
$config['compress']['css']['csstidy']['case_properties']     = 1;
$config['compress']['css']['csstidy']['merge_selectors']     = 0;
$config['compress']['css']['csstidy']['optimise_shorthands'] = 1;
$config['compress']['css']['csstidy']['remove_last_;']       = true;
$config['compress']['css']['csstidy']['css_level']           = 'CSS2.1';
$config['compress']['css']['csstidy']['template']            = 'highest_compression';
/**
 * Параметры компрессии js-файлов
 */
$config['compress']['js']['merge']  = false;         // указывает на необходимость слияния файлов по указанным блокам.
$config['compress']['js']['use']    = false;         // указывает на необходимость компрессии файлов. Компрессия используется только в активированном режиме слияния файлов.
$config['compress']['js']['force']  = false;        // если заданно 'compress.js.merge', то слияние выполняется, даже если результирующий файл есть

/**
 * "Примеси" ("соли") для повышения безопасности хешируемых данных
 */
$config['security']['salt_sess']  = '123456789012345678901234567890';
$config['security']['salt_pass']  = 'qwertyuiopqwertyuiopqwertyuiop';
$config['security']['salt_auth']  = '1234567890qwertyuiopasdfghjkl0';

$config['security']['user_session_key']  = 'user_key';

/**
 * Локализация
 */
// * Языковые настройки
$config['lang']['allow'] = array('ru', 'en');                               // какие языки доступны на сайте; если не задано или задан только один язык, то настройки мультиязычности игнорируются
$config['lang']['aliases'] = array(                                         // набор алиасов для совместимости LS
    'ru' => 'russian',
    'en' => 'english',
);
// Настройки мультиязычного сайта
$config['lang']['in_url'] = true;                                           // проверка языка в URL
$config['lang']['in_get'] = true;                                           // проверка языка в GET-параметре: 'lang=ru'
//$config['lang']['in_get'] = 'language';                                   // то же, но задает параметр: 'language=ru'
//$config['lang']['save'] = '1 year';                                         // сохранение языка в куки, задает время хранения; если 0 (или false), то не сохраняется

$config['lang']['default'] = 'ru';                                          // язык, который будет использоваться на сайте по умолчанию, если не наййдены тексты для текущего языка
$config['lang']['current'] = 'ru';                                          // основной язык сайта

// Массив текстовок, которые необходимо прогружать на страницу в виде JS хеша, позволяет использовать текстовки внутри js
$config['lang']['load_to_js'] = array(
    'text_yes',
    'text_no',
    'text_confirm',
    'text_cancel',
    'topic_delete_confirm_title',
    'topic_delete_confirm_text',
);

// пути до языковых файлов
$config['lang']['paths']    = array(
    '___path.dir.common___/templates/language',
    '___path.dir.app___/templates/language',
);

/**
 * Установка локали и временной зоны
 */
//$config['i18n']['locale'] = 'ru_RU.UTF-8';                                // Задается локаль, если не задана здесь, то берется из описания языка
//$config['i18n']['timezone'] = 'Europe/Moscow';                            // Задается временная зона, если не задана здесь, то берется из описания языка

/*
 * Какие еще конфиг-файлы требуется загрузить сразу после загрузки этого
 *
 * Значения конфиг-файлов загружаются в секцию, соответствующую имени файла
 */
$config['config_load'] = array(
    'assets',       // Наборы подключаемых css- и js-файлов
    'classes',      // Определения классов
    'jevix',        // Настройки типографа текста Jevix
    'widgets',      // Виджеты
);

return $config;

// EOF
