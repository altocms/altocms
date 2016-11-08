<?php
/**
 * Конфиг
 */

/* Переопределить имеющуюся переменную в конфиге:
 *    Переопределение роутера на наш новый Action: добавляем свой урл  http://domain.com/example
 *      $aConfig['$root$']['router']['page']['example'] = 'PluginExample_ActionExample';
 *      или Config::Set('router.page.example', 'PluginExample_ActionExample');
 *
 *
 * Добавить новую переменную:
 *    $aConfig['per_page'] = 15;
 *    Эта переменная будет доступна в плагине как Config::Get('plugin.example.per_page')
 */

$aConfig = array(

);

return $aConfig;
