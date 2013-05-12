<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Version: 0.9a
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   LiveStreet Engine Social Networking by Mzhelskiy Maxim
 *   Site: www.livestreet.ru
 *   E-mail: rus.engine@gmail.com
 *----------------------------------------------------------------------------
 */

F::File_IncludeFile(Config::Get('path.root.engine') . '/lib/external/Sphinx/sphinxapi_2.x.php');

/**
 * Модуль для работы с машиной полнотекстового поиска Sphinx
 *
 * @package modules.sphinx
 * @since   1.0
 */
class PluginSphinx_ModuleSphinx extends Module {
    /**
     * Объект сфинкса
     *
     * @var SphinxClient|null
     */
    protected $oSphinx = null;

    /**
     * Инициализация
     *
     */
    public function Init() {
        $this->InitSphinx();
    }

    /**
     * Инициализация сфинкса
     */
    protected function InitSphinx() {

        // * Получаем объект Сфинкса(из Сфинкс АПИ)
        $this->oSphinx = new SphinxClient();
        $sHost = Config::Get('plugin.sphinx.host');
        $nPort = 0;
        if (strpos($sHost, ':')) {
            list($sHost, $nPort) = explode(':', $sHost);
        }
        // * Подключаемся
        $this->oSphinx->SetServer($sHost, intval($nPort));
        $sError = $this->GetLastError();
        if ($sError) {
            $sError .= "\nhost:$sHost";
            $this->LogError($sError);
            return false;
        }

        // * Устанавливаем тип сортировки
        $this->oSphinx->SetSortMode(SPH_SORT_EXTENDED, "@weight DESC, @id DESc");
    }

    /**
     * Возвращает число найденых элементов в зависимоти от их типа
     *
     * @param string $sTerms           Поисковый запрос
     * @param string $sObjType         Тип поиска
     * @param array  $aExtraFilters    Список фильтров
     *
     * @return int
     */
    public function GetNumResultsByType($sTerms, $sObjType = 'topics', $aExtraFilters) {
        $aResults = $this->FindContent($sTerms, $sObjType, 1, 1, $aExtraFilters);
        return $aResults['total_found'];
    }

    /**
     * Непосредственно сам поиск
     *
     * @param string $sQuery           Поисковый запрос
     * @param string $sObjType         Тип поиска
     * @param int    $iOffset          Сдвиг элементов
     * @param int    $iLimit           Количество элементов
     * @param array  $aExtraFilters    Список фильтров
     *
     * @return array
     */
    public function FindContent($sQuery, $sObjType, $iOffset, $iLimit, $aExtraFilters) {

        // * используем кеширование при поиске
        $sExtraFilters = serialize($aExtraFilters);
        $cacheKey = Config::Get('plugin.sphinx.prefix')
            . "searchResult_{$sObjType}_{$sQuery}_{$iOffset}_{$iLimit}_{$sExtraFilters}";
        if (false === ($data = $this->Cache_Get($cacheKey))) {

            // * Параметры поиска
            $this->oSphinx->SetMatchMode(SPH_MATCH_ALL);
            $this->oSphinx->SetLimits($iOffset, $iLimit, 1000);

            // * Устанавливаем атрибуты поиска
            $this->oSphinx->ResetFilters();
            if (!is_null($aExtraFilters)) {
                foreach ($aExtraFilters AS $sAttribName => $sAttribValue) {
                    $this->oSphinx->SetFilter(
                        $sAttribName,
                        (is_array($sAttribValue)) ? $sAttribValue : array($sAttribValue)
                    );
                }
            }

            // * Ищем
            $sIndex = Config::Get('plugin.sphinx.prefix') . $sObjType . 'Index';
            $data = $this->oSphinx->Query($sQuery, $sIndex);
            if (!is_array($data)) {
                // Если false, то, скорее всего, ошибка и ее пишем в лог
                $sError = $this->GetLastError();
                if ($sError) {
                    $sError .= "\nquery:$sQuery\nindex:$sIndex";
                    if ($aExtraFilters) {
                        $sError .= "\nfilters:";
                        foreach ($aExtraFilters as $sAttribName => $sAttribValue) {
                            $sError .= $sAttribName . '=(' . (is_array($sAttribValue) ? join(',', $sAttribValue) : $sAttribValue) . ')';
                        }
                    }
                    $this->LogError($sError);
                }
                return false;
            }
            /**
             * Если результатов нет, то и в кеш писать не стоит...
             * хотя тут момент спорный
             */
            if ($data['total'] > 0) {
                $this->Cache_Set($data, $cacheKey, array(), 60 * 15);
            }
        }
        return $data;
    }

    /**
     * Получить ошибку при последнем обращении к поиску
     *
     * @return string
     */
    public function GetLastError() {
        return mb_convert_encoding($this->oSphinx->GetLastError(), 'UTF-8');
    }

    /**
     * Получаем сниппеты(превью найденых элементов)
     *
     * @param string $sText           Текст
     * @param string $sIndex          Название индекса
     * @param string $sTerms          Поисковый запрос
     * @param string $before_match    Добавляемый текст перед ключом
     * @param string $after_match     Добавляемый текст после ключа
     *
     * @return array
     */
    public function GetSnippet($sText, $sIndex, $sTerms, $before_match, $after_match) {
        $aReturn = $this->oSphinx->BuildExcerpts(
            array($sText),
            Config::Get('plugin.sphinx.prefix') . $sIndex . 'Index', $sTerms,
            array(
                 'before_match' => $before_match,
                 'after_match'  => $after_match,
            )
        );
        return $aReturn[0];
    }

}

// EOF