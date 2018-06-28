<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on LiveStreet Engine Social Networking by Mzhelskiy Maxim
 * Official site: www.livestreet.ru
 *----------------------------------------------------------------------------
 */

/**
 * Русский языковой файл.
 * Содержит текстовки инсталлятора.
 */
return array(
    'config_file_not_exists'           => 'Файл %%path%% не существует.',
    'config_file_not_writable'         => 'Файл %%path%% недосупен для записи.',

    'error_db_invalid'                 => 'Невозможно выбрать или создать базу данных',
    'error_db_connection_invalid'      => 'Не удалось подключиться к базе данных. Проверьте корректность введенных вами настроек.',
    'error_db_saved'                   => 'Не удалось сохранить данные в базе.',
    'error_db_no_data'                 => 'Не удалось получить данные из базы.',

    'error_local_config_invalid'       => 'Файл локальной конфигурации <strong>/app/config/config.local.php</strong> не найден.',

    'site_name_invalid'                => 'Указано недопустимое название сайта.',
    'site_description_invalid'         => 'Указано недопустимое описание сайта.',
    'site_keywords_invalid'            => 'Указано недопустимые ключевые слова.',
    'skin_name_invalid'                => 'Указано недопустимое имя шаблона.',
    'mail_sender_invalid'              => 'Указано недопустимый e-mail.',
    'mail_name_invalid'                => 'Указано недопустимое имя отправителя уведомлений.',
    'lang_current_invalid'             => 'Указан недопустимый язык.',
    'lang_default_invalid'             => 'Указан недопустимый язык по-умолчанию.',
    'admin_login_invalid'              => 'Логин администратора введен не верно.',
    'admin_mail_invalid'               => 'E-mail администратора введен не верно.',
    'admin_password_invalid'           => 'Пароль администратора введен не верно.',
    'admin_repassword_invalid'         => 'Подтверждение пароля не совпадает с самим паролем.',

    'ok_db_created'                    => 'База данных успешно создана. Данные записаны в конфигурационный файл.',

    'yes'                              => 'Да',
    'no'                               => 'Нет',
    'next'                             => 'Дальше',
    'prev'                             => 'Назад',

    'valid_mysql_server'               => 'Для работы Alto CMS необходим сервер MySQL версии не ниже 5.1',

    'install_title'                    => 'Установка Alto CMS',
    'step'                             => 'Шаг',

    'start_paragraph'                  => '<p>Добро пожаловать в установку Alto CMS. Ознакомьтесь с результатами и следуйте подсказкам.</p>
	                    <p><b>Внимание.</b> Для успешной инсталяции вы должны переименовать файл
	                    /app/config/config.local.php.txt в /app/config/config.local.php и дать этому файлу права на запись.</p>
	                    <p><b>Внимание.</b> Папки /_tmp, /_run, /uploads должны иметь права на запись.</p>',

    'php_params'                       => 'Основные настройки PHP',
    'php_params_version'               => 'PHP версии не ниже ' . ALTO_PHP_REQUIRED,
    'php_params_safe_mode'             => 'Safe mode выключен',
    'php_params_utf8'                  => 'Поддержка UTF8 в PCRE',
    'php_params_mbstring'              => 'Поддержка Mbstring',
    'php_params_simplexml'             => 'Поддержка SimpleXML',
    'php_params_graphic_packages'      => 'Графические пакеты (Gmagick, Imagick или GD)',

    'local_config'                     => 'Локальная конфигурация',
    'local_file_is_writable'           => 'Файл %%file%% существует и доступен для записи',
    'local_folder_is_writable'         => 'Файл %%folder%% существует и доступен для записи',
    'local_config_file'                => 'Файл /app/config/config.local.php существует и доступен для записи',
    'local_temp_dir'                   => 'Папка /_tmp существует и доступна для записи',
    'local_runtime_dir'                => 'Папка /_run существует и доступна для записи',
    'local_uploads_dir'                => 'Папка /uploads существует и доступна для записи',
    'local_plugins_dir'                => 'Папка /app/plugins существует и доступна для записи',
    'local_plugins_dat'                => 'Файл /app/plugins/plugins.dat существует и доступен для записи',

    'db_params'                        => 'Настройка базы данных',
    'db_params_host'                   => 'Имя сервера БД',
    'db_params_host_notice'            => 'Если не знаете, то оставьте, как есть',
    'db_params_port'                   => 'Порт сервера БД',
    'db_params_port_notice'            => 'В большинстве случаев правильным решение будет оставить 3306',
    'db_params_name'                   => 'Название базы данных',
    'db_params_create'                 => 'Создать базу данных',
    'db_params_convert'                => 'Конвертировать базу LiveStreet 0.5.1 в 1.0.3',
    'db_params_convert_from_10'        => 'Конвертировать базу LiveStreet 1.0 в 1.0.3',
    'db_params_convert_to_alto'        => 'Конвертировать базу LiveStreet 1.0.3 в Alto CMS 1.0',
    'db_params_convert_to_11'          => 'Конвертировать базу Alto CMS 1.0.x в Alto CMS 1.1',
    'db_params_convert_from_alto_097'  => 'Конвертировать базу Alto CMS 0.9.7 в Alto CMS 1.0',
    'db_params_user'                   => 'Имя пользователя',
    'db_params_user_notice'            => 'Имя и пароль пользователя для доступа к базе данных узнайте у своего хостера',
    'db_params_password'               => 'Пароль',
    'db_params_prefix'                 => 'Префикс таблиц',
    'db_params_prefix_notice'          => 'Указанный префикс будет приставлен к названию всех таблиц',
    'db_params_engine'                 => 'Tables engine',
    'db_params_engine_notice'          => 'Рекомендуется использовать InnoDB',

    'error_table_select'               => 'Ошибка запроса на выборку данных из таблицы %%table%%',
    'error_database_converted_already' => 'Конвертация отменена, так как структура базы данных соответствует версии 1.0',

    'admin_params'                     => 'Настройка данных администратора',
    'admin_params_login'               => 'Логин',
    'admin_params_mail'                => 'E-mail',
    'admin_params_pass'                => 'Пароль',
    'admin_params_repass'              => 'Еще раз',
    'admin_params_skip'                => 'Пропустить установку администратора',
    'admin_params_skip_txt'            => 'Если Вы конвертировали базу данных, где администратор уже задан, Вы можете пропустить этот шаг',

    'end_paragraph'                    => 'Поздравляем! Alto CMS успешно установлена.<br /><br />
	                    Для обеспечения безопасности работы Вашего сайта удалите папку <strong>install</strong>.<br /><br />
	                    Настроить сайт Вы можете в разделе администрирования сайта.<br /><br />
	                    <a href="../">Перейти на главную страницу</a><br /><br />',
    'extend_mode'                      => 'Расширенный режим',

    'view_params'                      => 'Настройки HTML вида',
    'view_params_name'                 => 'Название сайта',
    'view_params_description'          => 'Описание сайта',
    'view_params_keywords'             => 'Ключевые слова',
    'view_params_skin'                 => 'Название шаблона',

    'mail_params'                      => 'Настройки почтовых уведомлений',
    'mail_params_sender'               => 'E-mail с которого отправляются уведомления',
    'mail_params_name'                 => 'Имя от которого отправляются уведомления',

    'general_params'                   => 'Общие настройки',
    'general_params_close'             => 'Использовать закрытый режим работы сайта',
    'general_params_active'            => 'Использовать активацию при регистрации',
    'general_params_invite'            => 'Использовать режим регистрации по приглашению',

    'language_params'                  => 'Языковые настройки',
    'language_params_current'          => 'Текущий язык',
    'language_params_default'          => 'Язык, который будет использоваться по умолчанию',

    'finish_paragraph'                 => 'Поздравляем! Alto CMS успешно установлена.<br />
                        Для обеспечения безопасности работы системы, удалите папку <strong>install</strong>.<br /><br />
                        <a href="../">Перейти на главную страницу</a>',
);

// EOF