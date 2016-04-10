<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

/**
 * Class ModuleCron
 */
class ModuleCron extends \alto\engine\ar\ArModule {

    protected $aHandlers = array();


    public function init() {

        parent::init();
        $this->setHandler('module', array($this, 'CronHandlerModule'));
    }


    public function setHandler($sHandlerType, $xHandlerCallback) {

        $this->aHandlers[$sHandlerType] = $xHandlerCallback;
    }

    /**
     * @param $oTask
     *
     * @return ModuleCron_EntityTask
     */
    public function addTask($oTask) {

        $oTask->save();
        if ($oTask->getActive()) {
            $this->markNextTime($oTask->getNextTime());
        }

        return $oTask;
    }

    /**
     * Add single task
     *
     * @param int|string $xTime
     * @param string $sTitle
     * @param string|callable $xHandler
     * @param array|null $aParams
     * @param string|object|null $sPlugin
     *
     * @return ModuleCron_EntityTask
     */
    public function scheduleTaskAt($xTime, $sTitle, $xHandler, $aParams = array(), $sPlugin = null) {

        /** @var ModuleCron_EntityTask $oTask */
        $oTask = $this->make('Task');
        $oTask
            ->setTitle($sTitle)
            ->setNextTime($xTime)
            ->setExecPeriod(0)
            ->setHandler($xHandler)
            ->setExecParams($aParams)
            ->setPlugin($sPlugin)
        ;
        return $this->addTask($oTask);
    }

    /**
     * @param int|string $xPeriod
     * @param string $sTitle
     * @param string|callable $xHandler
     * @param array|null $aParams
     * @param string|object|null $sPlugin
     *
     * @return ModuleCron_EntityTask
     */
    public function scheduleTaskBy($xPeriod, $sTitle, $xHandler, $aParams = array(), $sPlugin = null) {

        $iPeriod = F::ToSeconds($xPeriod);
        $iNextTime = time() + $iPeriod;

        /** @var ModuleCron_EntityTask $oTask */
        $oTask = $this->make('Task');
        $oTask
            ->setTitle($sTitle)
            ->setNextTime($iNextTime)
            ->setExecPeriod($iPeriod)
            ->setHandler($xHandler)
            ->setExecParams($aParams)
            ->setPlugin($sPlugin)
        ;
        return $this->addTask($oTask);
    }

    /**
     * @param int  $iTime
     * @param bool $bReset
     */
    public function markNextTime($iTime, $bReset = false) {

        $sFile = C::Get('sys.cache.dir') . 'data/cron.dat';
        $sData = F::File_GetContents($sFile);
        $aInfo = F::Unserialize($sData);

        $bChanged = false;
        if (is_null($aInfo) || empty($aInfo['next_time']) || $bReset) {
            $aInfo['next_time'] = $iTime;
            $bChanged = true;
        } else {
            if ($aInfo['next_time'] > $iTime) {
                $aInfo['next_time'] = $iTime;
                $bChanged = true;
            }
        }

        if ($bChanged) {
            F::File_PutContents($sFile, F::Serialize($aInfo));
        }
    }

    public function Run() {

        $this->markNextTime(0, true);
        //$oRequest = $this->find('Task');
        //$oRequest->where('next_time', '!=', null);
        //var_dump($oRequest->getQueryStr(), $oRequest->getQueryParams());exit;
        $oRequest = $this->find('Task')
            ->where(['active' => 1])
            ->andWhere('next_time', '!=', null)
            ->andWhere('next_time', '<=', F::Now())
            ->andWhereBegin()
                ->where(['last_time' => null])
                ->orWhere('last_time', '<', ModuleORM2::Sql('next_time'))
            ->whereEnd()
            ->orderBy('next_time')
        ;
        $aTasks = $oRequest->all();

        foreach($aTasks as $oTask) {
            $this->execTask($oTask);
        }
        /** @var ModuleCron_EntityTask $oTask */
        $oTask = $oRequest->one();
        if ($oTask) {
            $this->markNextTime($oTask->getNextTime(), true);
        } else {
            $this->markNextTime(-1, true);
        }
    }


    public function execTaskById($iTaskId) {

        /** @var ModuleCron_EntityTask $oTask */
        $oTask = $this->find('Task')
            ->where(['active' => 1])
            ->one($iTaskId);
        if ($oTask) {
            $this->execTask($oTask);
        }
    }

    /**
     * @param ModuleCron_EntityTask $oTask
     */
    public function execTask($oTask) {

        if (($sHandlerType = $oTask->getHandlerType()) && isset($this->aHandlers[$sHandlerType])) {
            $oTask->start();
            try {
                call_user_func($this->aHandlers[$sHandlerType], $oTask);
            } catch (\Exception $e) {

            }
            $nElapsedTime = $oTask->done();
            $sCronLog = '[' . $oTask->getId() . '] ' . $oTask->getTitle() . ' [elapsed: ' . $nElapsedTime . ']';
            E::ModuleLogger()->Dump('cron.log', $sCronLog);
        }
    }

    /**
     * @param ModuleCron_EntityTask $oTask
     * 
     * @return mixed|null
     */
    public function CronHandlerModule($oTask) {

        $xCallback = $oTask->getHandlerName();
        $aParams = $oTask->getExecParams();

        if (is_string($xCallback)) {
            return E::getInstance()->_CallModule($xCallback, $aParams);
        } elseif (is_array($xCallback) && count($xCallback) == 2) {
            list($sModule, $sMethod) = $xCallback;
            if (is_string($sMethod) && is_string($sMethod)) {
                return call_user_func_array(array(E::Module($sModule), $sMethod), $aParams);
            }
        }
        return null;
    }

}

// EOF