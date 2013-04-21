<?php
/*-------------------------------------------------------
*
*   LiveStreet Engine Social Networking
*   Copyright © 2008 Mzhelskiy Maxim
*
*--------------------------------------------------------
*
*   Official site: www.livestreet.ru
*   Contact e-mail: rus.engine@gmail.com
*
*   GNU General Public License, version 2:
*   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*
---------------------------------------------------------
*/

/**
 * Регистрация хука для вывода статистики производительности
 *
 * @package hooks
 * @since 1.0
 */
class HookStatisticsPerformance extends Hook {
    /**
     * Регистрируем хуки
     */
    public function RegisterHook() {
        $this->AddHook('template_body_end', 'Statistics', __CLASS__, -1000);
    }

    /**
     * Обработка хука перед закрывающим тегом body
     *
     * @return string
     */
    public function Statistics() {
        $oEngine = Engine::getInstance();
        /**
         * Получаем статистику по БД, кешу и проч.
         */
        $aStats = $oEngine->getStats();
        $aStats['cache']['mode'] = (Config::Get('sys.cache.use') ? Config::Get('sys.cache.type') : 'off');
        $aStats['cache']['time'] = round($aStats['cache']['time'], 5);
        $aStats['memory']['limit'] = ini_get('memory_limit');
        $aStats['memory']['usage'] = F::MemSizeFormat(memory_get_usage(), 3);
        $aStats['memory']['peak'] = F::MemSizeFormat(memory_get_peak_usage(true), 3);
        $aStats['viewer']['count'] = ModuleViewer::GetRenderCount();
        $aStats['viewer']['time'] = round(ModuleViewer::GetRenderTime(), 3);
        $aStats['viewer']['preproc'] = round(ModuleViewer::GetPreprocessingTime(), 3);
        $aStats['viewer']['total'] = round(ModuleViewer::GetTotalTime(), 3);

        $this->Viewer_Assign('aStatsPerformance', $aStats);
        $this->Viewer_Assign('bIsShowStatsPerformance', Router::GetIsShowStats());
        /**
         * В ответ рендерим шаблон статистики
         */
        return $this->Viewer_Fetch('statistics_performance.tpl');
    }
}

// EOF