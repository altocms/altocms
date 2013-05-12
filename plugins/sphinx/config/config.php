<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Version: 0.9a
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

/*
 * Хост для доступа к Sphinx
 *
 * Формат:
 *      - [hostname:]port[:protocol]    - для соединения через TCP/IP
 *      - /unix/socket/path             - для соединения через Unix-socket
 *
 * Примеры:
 *      localhost
 *      127.0.0.1
 *      localhost:9312
 *      192.168.0.1:9312
 *      /var/run/searchd.sock
 *      9312
 */
$config['host'] = '127.0.0.1:9312';

/*
 * Сокет для подключения к базе
 *
 * Примеры:
 *      /var/lib/mysql/mysql.sock       - on Linux
 *      /var/run/mysqld/mysqld.sock     - on Debian
 *      /tmp/mysql.sock                 - on FreeBSD
 *
 * Нужно оставлять пустым, если соединение через TCP/IP
 */

$config['db_socket'] = '';

/*
 * Префикс для данных, чтобы на одном сервере можно было обслуживать несколько сайтов
 * Он должен быть уникальным для КАЖДОГО сайта на сервере
 */
$config['prefix'] = Config::Get('db.params.dbname') . '_' . Config::Get('db.table.prefix');

/*
 * Путь к рабочей папке Sphinx
 *
 * Примеры:
 *      /var/lib/sphinxsearch/     - on Debian
 *      C:/sphinx/                 - on Windows
 *
 * Необходимо создать папку перед ее использованием
 */
$config['path'] = 'path/to/spnix/workfolder/';

return $config;


// EOF