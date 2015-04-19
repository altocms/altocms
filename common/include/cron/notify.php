#!/usr/bin/env php
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

defined('ALTO_DIR') || define('ALTO_DIR', dirname(dirname(dirname(dirname(__FILE__)))));
set_include_path(get_include_path() . PATH_SEPARATOR . ALTO_DIR);
chdir(ALTO_DIR);

require_once(ALTO_DIR . '/engine/loader.php');
require_once(ALTO_DIR . '/engine/classes/abstract/Cron.class.php');

class CronNotify extends Cron {
    /**
     * Выбираем пул заданий и рассылаем по ним e-mail
     */
    public function Client() {

        $aNotifyTasks = $this->Notify_GetTasksDelayed(Config::Get('module.notify.per_process'));

        if (empty($aNotifyTasks)) {
            $this->Log('No tasks are found.');
            return;
        }

        // * Последовательно загружаем задания на отправку
        $aArrayId = array();
        foreach ($aNotifyTasks as $oTask) {
            $this->Notify_SendTask($oTask);
            $aArrayId[] = $oTask->getTaskId();
        }
        $this->Log('Send notify: ' . count($aArrayId));

        // * Удаляем отработанные задания
        $this->Notify_DeleteTaskByArrayId($aArrayId);
    }
}

$sLockFilePath = Config::Get('sys.cache.dir') . 'notify.lock';
/**
 * Создаем объект крон-процесса,
 * передавая параметром путь к лок-файлу
 */
$oCron = new CronNotify($sLockFilePath);
print $oCron->Exec();

// EOF
