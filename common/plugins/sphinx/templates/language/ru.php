<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   LiveStreet Engine Social Networking by Mzhelskiy Maxim
 *   Site: www.livestreet.ru
 *   E-mail: rus.engine@gmail.com
 *----------------------------------------------------------------------------
 */

/**
 * Русский языковой файл плагина
 */
return array(
    'conf_title'       => 'Файл конфигурации для Sphinx',
    'conf_description' => '
            1) Создайте файл spinx.conf в папке %%path%%
            2) Скопируйте в него содержимое экрана
            3) Убедитесь, что существуют папки
               %%path%%data/%%prefix%%data/
               и %%path%%logs/%%prefix%%logs/ и программа Sphinx имеет права на запись в них
            4) Запустите полную индексацию командой indexer --all
            5) Запустите Sphinx командой searchd и настройки cron для реиндексации
            ',
);

// EOF