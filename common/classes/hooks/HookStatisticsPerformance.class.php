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
 * Регистрация хука для вывода статистики производительности
 *
 * @package hooks
 * @since   1.0
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
        $aStats['memory']['limit'] = F::MemSizeFormat(F::MemSize2Int(ini_get('memory_limit')), 3);
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