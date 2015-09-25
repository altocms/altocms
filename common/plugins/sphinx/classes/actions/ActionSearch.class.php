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
 * Экшен обработки поиска по сайту через поисковый движок Sphinx
 *
 * @package actions
 * @since   1.0
 */
class PluginSphinx_ActionSearch extends ActionPlugin {

    /**
     * Допустимые типы поиска с параметрами
     *
     * @var array
     */
    protected $sTypesEnabled
        = array(
            'topics'   => array('topic_publish' => 1),
            'comments' => array('comment_delete' => 0)
        );
    /**
     * Массив результата от Сфинкса
     *
     * @var null|array
     */
    protected $aSphinxRes = null;
    /**
     * Поиск вернул результат или нет
     *
     * @var bool
     */
    protected $bIsResults = false;

    /**
     * Инициализация
     */
    public function Init() {

        $this->SetDefaultEvent('index');
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('search'));
    }

    /**
     * Регистрация евентов
     */
    protected function RegisterEvent() {

        $this->AddEvent('index', 'EventIndex');
        $this->AddEvent('topics', 'EventTopics');
        $this->AddEvent('comments', 'EventComments');
        $this->AddEvent('opensearch', 'EventOpenSearch');
    }

    /**
     * Отображение формы поиска
     */
    function EventIndex() {
    }

    /**
     * Обработка стандарта для браузеров Open Search
     */
    function EventOpenSearch() {

        Router::SetIsShowStats(false);
        E::ModuleViewer()->Assign('sAdminMail', Config::Get('sys.mail.from_email'));
    }

    /**
     * Поиск топиков
     *
     */
    function EventTopics() {
        /**
         * Ищем
         */
        $aReq = $this->PrepareRequest();
        $aRes = $this->PrepareResults($aReq, Config::Get('module.topic.per_page'));
        if (false === $aRes) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'));
            return Router::Action('error');
        }
        /**
         * Если поиск дал результаты
         */
        if ($this->bIsResults) {
            /**
             * Получаем топик-объекты по списку идентификаторов
             */
            $aTopics = E::ModuleTopic()->GetTopicsAdditionalData(array_keys($this->aSphinxRes['matches']));
            /**
             * Конфигурируем парсер jevix
             */
            E::ModuleText()->LoadJevixConfig('search');
            /**
             *  Делаем сниппеты
             */
            foreach ($aTopics AS $oTopic) {
                /**
                 * Т.к. текст в сниппетах небольшой, то можно прогнать через парсер
                 */
                $oTopic->setTextShort(
                    E::ModuleText()->TextParser(
                        E::ModuleSphinx()->GetSnippet(
                            $oTopic->getText(),
                            'topics',
                            $aReq['q'],
                            '<span class="searched-item">',
                            '</span>'
                        )
                    )
                );
            }
            /**
             *  Отправляем данные в шаблон
             */
            E::ModuleViewer()->Assign('bIsResults', true);
            E::ModuleViewer()->Assign('aRes', $aRes);
            E::ModuleViewer()->Assign('aTopics', $aTopics);
        }
    }

    /**
     * Поиск комментариев
     *
     */
    function EventComments() {
        /**
         * Ищем
         */
        $aReq = $this->PrepareRequest();
        $aRes = $this->PrepareResults($aReq, Config::Get('module.comment.per_page'));
        if (false === $aRes) {
            E::ModuleMessage()->AddErrorSingle(E::ModuleLang()->Get('system_error'));
            return Router::Action('error');
        }
        /**
         * Если поиск дал результаты
         */
        if ($this->bIsResults) {
            /**
             *  Получаем топик-объекты по списку идентификаторов
             */
            $aComments = E::ModuleComment()->GetCommentsAdditionalData(array_keys($this->aSphinxRes['matches']));
            /**
             * Конфигурируем парсер jevix
             */
            E::ModuleText()->LoadJevixConfig('search');
            /**
             * Делаем сниппеты
             */
            foreach ($aComments AS $oComment) {
                $oComment->setText(
                    E::ModuleText()->TextParser(
                        E::ModuleSphinx()->GetSnippet(
                            htmlspecialchars($oComment->getText()),
                            'comments',
                            $aReq['q'],
                            '<span class="searched-item">',
                            '</span>'
                        )
                    )
                );
            }
            /**
             *  Отправляем данные в шаблон
             */
            E::ModuleViewer()->Assign('aRes', $aRes);
            E::ModuleViewer()->Assign('aComments', $aComments);
        }
    }

    /**
     * Подготовка запроса на поиск
     *
     * @return array
     */
    protected function PrepareRequest() {

        $aReq['q'] = F::GetRequestStr('q');
        if (!F::CheckVal($aReq['q'], 'text', 2, 255)) {
            /**
             * Если запрос слишком короткий перенаправляем на начальную страницу поиска
             * Хотя тут лучше показывать юзеру в чем он виноват
             */
            Router::Location(Router::GetPath('search'));
        }
        $aReq['sType'] = strtolower(Router::GetActionEvent());
        /**
         * Определяем текущую страницу вывода результата
         */
        $aReq['iPage'] = intval(preg_replace('#^page([1-9]\d{0,5})$#', '\1', $this->getParam(0)));
        if (!$aReq['iPage']) {
            $aReq['iPage'] = 1;
        }
        /**
         *  Передача данных в шаблонизатор
         */
        E::ModuleViewer()->Assign('aReq', $aReq);
        return $aReq;
    }

    /**
     * Поиск и формирование результата
     *
     * @param array $aReq
     * @param int   $iLimit
     *
     * @return array|bool
     */
    protected function PrepareResults($aReq, $iLimit) {
        /**
         *  Количество результатов по типам
         */
        foreach ($this->sTypesEnabled as $sType => $aExtra) {
            $aRes['aCounts'][$sType] = intval(E::ModuleSphinx()->GetNumResultsByType($aReq['q'], $sType, $aExtra));
        }
        if ($aRes['aCounts'][$aReq['sType']] == 0) {
            /**
             *  Объектов необходимого типа не найдено
             */
            unset($this->sTypesEnabled[$aReq['sType']]);
            /**
             * Проверяем отсальные типы
             */
            foreach (array_keys($this->sTypesEnabled) as $sType) {
                if ($aRes['aCounts'][$sType]) {
                    Router::Location(Router::GetPath('search') . $sType . '/?q=' . $aReq['q']);
                }
            }
        } elseif (($aReq['iPage'] - 1) * $iLimit <= $aRes['aCounts'][$aReq['sType']]) {
            /**
             * Ищем
             */
            $this->aSphinxRes = E::ModuleSphinx()->FindContent(
                $aReq['q'],
                $aReq['sType'],
                ($aReq['iPage'] - 1) * $iLimit,
                $iLimit,
                $this->sTypesEnabled[$aReq['sType']]
            );
            /**
             * Возможно демон Сфинкса не доступен
             */
            if (false === $this->aSphinxRes) {
                return false;
            }

            $this->bIsResults = true;
            /**
             * Формируем постраничный вывод
             */
            $aPaging = E::ModuleViewer()->MakePaging(
                $aRes['aCounts'][$aReq['sType']],
                $aReq['iPage'],
                $iLimit,
                Config::Get('pagination.pages.count'),
                Router::GetPath('search') . $aReq['sType'],
                array(
                     'q' => $aReq['q']
                )
            );
            E::ModuleViewer()->Assign('aPaging', $aPaging);
        }

        $this->SetTemplateAction('results');
        E::ModuleViewer()->AddHtmlTitle($aReq['q']);
        E::ModuleViewer()->Assign('bIsResults', $this->bIsResults);
        return $aRes;
    }
}

// EOF