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
 * Class ModuleCron_EntityTask
 *
 * @method ModuleCron_EntityTask setTitle(string $sParam)
 * @method ModuleCron_EntityTask setHandlerType($sParam)
 * @method ModuleCron_EntityTask setExecPeriod($xParam)
 * @method ModuleCron_EntityTask setExecCount($iParam)
 *
 * @method string getTitle()
 * @method int    getNextTime()
 * @method string getHandlerType()
 * @method array  getHandlerName()
 * @method int    getExecCount()
 *
 * @since 1.2
 */
class ModuleCron_EntityTask extends \alto\engine\ar\EntityRecord {

    const EXEC_TIME_ROUND = 100;

    protected $nTimerStart = 0.0;
    protected $nTimerStop = 0.0;
    protected $nElapsedTime = 0.0;

    /**
     * @param $xTime
     *
     * @return ModuleCron_EntityTask
     */
    public function setNextTime($xTime) {

        if (!is_int($xTime)) {
            $iNextTime = strtotime($xTime);
        } else {
            $iNextTime = (int)$xTime;
        }
        $this->setProp('next_time', date('Y-m-d H:i:s', $iNextTime));

        return $this;
    }

    /**
     * @param $xHandler
     *
     * @return ModuleCron_EntityTask
     */
    public function setHandler($xHandler) {

        $sHandlerType = 'module';
        $xHandlerName = $xHandler;
        if (is_array($xHandler) && (count($xHandler) == 1)) {
            $xVal = reset($xHandler);
            $xKey = key($xHandler);
            if (!is_numeric($xKey)) {
                $sHandlerType = $xKey;
                $xHandlerName = $xVal;
            }
        }
        if ($sHandlerType === 'module') {
            if (is_array($xHandlerName) && count($xHandlerName) == 2) {
                list($sModule, $sMethod) = $xHandlerName;
                if (is_object($sModule)) {
                    $sModule = get_class($sModule);
                }
                $xHandlerName = array($sModule, $sMethod);
            } elseif (!is_string($xHandlerName)) {
                $sHandlerType = 'unknown';
            }
        }
        $this->setHandlerType($sHandlerType);
        $this->setHandlerName($xHandlerName);

        return $this;
    }

    /**
     * @param string|array $xHandlerName
     *
     * @return ModuleCron_EntityTask
     */
    public function setHandlerName($xHandlerName) {

        $this->setProp('handler_name', json_encode($xHandlerName));

        return $this;
    }

    /**
     * @param object|string $xPlugin
     *
     * @return ModuleCron_EntityTask
     */
    public function setPlugin($xPlugin) {

        $sPluginName = Plugin::GetPluginName($xPlugin);
        $this->setProp('plugin', $sPluginName);

        return $this;
    }


    /**
     * @param $aParams
     *
     * @return ModuleCron_EntityTask
     */
    public function setExecParams($aParams) {

        $this->setProp('exec_params', json_encode($aParams));

        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getHandlerHame() {

        $sData = $this->getProp('handler_name');
        if ($sData) {
            return json_decode($sData);
        }
        return null;
    }

    /**
     * @return mixed|null
     */
    public function getExecParams() {

        $sData = $this->getProp('exec_params');
        if ($sData) {
            return json_decode($sData);
        }
        return null;
    }


    /**
     * Start timer
     *
     * @param int|float|null $nTimer
     *
     * @return ModuleCron_EntityTask
     */
    public function start($nTimer = null) {

        if (is_numeric($nTimer)) {
            $this->nTimerStart = (float)$nTimer;
        } else {
            $this->nTimerStart = microtime(true);
        }

        return $this;
    }

    /**
     * Stop timer
     *
     * @param int|float|null $nTimer
     *
     * @return ModuleCron_EntityTask
     */
    public function stop($nTimer = null) {

        if (is_numeric($nTimer)) {
            $this->nTimerStop = (float)$nTimer;
        } else {
            $this->nTimerStop = microtime(true);
        }

        return $this;
    }

    /**
     * @param int|float|null $nElapsedTime
     *
     * @return float
     */
    public function done($nElapsedTime = null) {

        if ($this->nTimerStart) {
            $this->setLastTime($this->nTimerStart);
        } else {
            $this->setLastTime(time());
        }
        if (!$this->nTimerStop) {
            $this->stop();
        }

        // Save exec time
        if ($this->nTimerStart) {
            if (!is_numeric($nElapsedTime)) {
                $this->nElapsedTime = $this->nTimerStop - $this->nTimerStart;
            } else {
                $this->nElapsedTime = $nElapsedTime;
            }
            $nTime = round($this->nElapsedTime * self::EXEC_TIME_ROUND) + $this->getExecTime();
            $this->setExecTime($nTime);
        }

        // Increase exec count
        $iCount = $this->getExecCount() + 1;
        $this->setExecCount($iCount);
        if ((int)$this->execLimit() <= $iCount) {
            $this->setActive(0);
        }

        // If exec period = 0 then it's single launch
        $iPeriod = $this->getExecPeriod();
        if (!$iPeriod) {
            $this->setActive(0);
        } else {
            // Set next time of launch
            $this->setNextTime(time() + $iPeriod);
        }

        return $this->nElapsedTime;
    }

}

// EOF