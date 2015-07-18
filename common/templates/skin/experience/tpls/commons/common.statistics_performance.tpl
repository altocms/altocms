 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{if $bIsShowStatsPerformance}
    <div id="stat-performance">
        <div class="container">

            <br/><br/>

            {hook run='statistics_performance_begin'}



            <div class="row">
                    <div class="col-md-4 col-md-offset-2">
                        <h4>Database</h4>
                        query: <strong>{$aStatsPerformance.sql.count}</strong><br/>
                        time: <strong>{$aStatsPerformance.sql.time}</strong>
                    </div>
                    <div class="col-md-4">
                        <h4>Cache - {$aStatsPerformance.cache.mode}</h4>
                        query: <strong>{$aStatsPerformance.cache.count}</strong><br/>
                        &mdash; set: <strong>{$aStatsPerformance.cache.count_set}</strong><br/>
                        &mdash; get: <strong>{$aStatsPerformance.cache.count_get}</strong><br/>
                        time: <strong>{$aStatsPerformance.cache.time}</strong>
                    </div>
                        <div class="col-md-4">
                        <h4>Viewer</h4>
                        total time: <strong>{$aStatsPerformance.viewer.total}</strong><br/>
                        &mdash; preprocess time: <strong>{$aStatsPerformance.viewer.preproc}</strong><br/>
                        &mdash; render calls: <strong>{$aStatsPerformance.viewer.count}</strong><br/>
                        &mdash; render time: <strong>{$aStatsPerformance.viewer.time}</strong><br/>
                    </div>
                            <div class="col-md-4">
                        <h4>PHP - {$smarty.const.PHP_VERSION}</h4>
                        time load modules: <strong>{$aStatsPerformance.engine.time_load_module}</strong><br/>
                        included files: <br/>
                        &mdash; count: <strong>{$aStatsPerformance.engine.files_count}</strong><br/>
                        &mdash; time: <strong>{$aStatsPerformance.engine.files_time}</strong><br/>
                        full time:
                        <strong>{$aStatsPerformance.engine.full_time}{if $aStatsPerformance.engine.exec_time} / {$aStatsPerformance.engine.exec_time}{/if}</strong>
                    </div>
                        <div class="col-md-4">
                        <h4>Memory</h4>
                        memory limit: <strong>{$aStatsPerformance.memory.limit}</strong><br/>
                        memory usage: <strong>{$aStatsPerformance.memory.usage}</strong><br/>
                        peak usage: <strong>{$aStatsPerformance.memory.peak}</strong>
                    </div>
                    {hook run='statistics_performance_item'}
            </div>

            {hook run='statistics_performance_end'}

            <br/><br/>

        </div>
    </div>
{/if}
