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
 * Экшен обработки УРЛа вида /error/ т.е. ошибок
 *
 * @package actions
 * @since   1.0
 */
class ActionError extends Action {
    /**
     * Список специфических HTTP ошибок для которых необходимо отдавать header
     *
     * @var array
     */
    protected $aHttpErrors
        = array(
            '404' => array(
                'header' => '404 Not Found',
            ),
        );

    /**
     * Инициализация экшена
     *
     */
    public function Init() {

        /**
         * issue #104, {@see https://github.com/altocms/altocms/issues/104}
         * Проверим, не пришли ли мы в ошибку с логаута, если да, то перейдем на главную,
         * поскольку страница на самом деле есть, но только когда мы авторизованы.
         */
        if (isset($_SERVER['HTTP_REFERER']) && E::ModuleSession()->GetCookie('lgp') === md5(F::RealUrl($_SERVER['HTTP_REFERER']) . 'logout')) {
            return R::Location((string)Config::Get('module.user.logout.redirect'));
        }

        /**
         * Устанавливаем дефолтный евент
         */
        $this->SetDefaultEvent('index');
        /**
         * Запрешаем отображать статистику выполнения
         */
        R::SetIsShowStats(false);
    }

    /**
     * Регистрируем евенты
     *
     */
    protected function RegisterEvent() {
        $this->AddEvent('index', 'EventError');
        $this->AddEventPreg('/^\d{3}$/i', 'EventError');
    }

    /**
     * Вывод ошибки
     *
     */
    protected function EventError() {
        /**
         * Если евент равен одной из ошибок из $aHttpErrors, то шлем браузеру специфичный header
         * Например, для 404 в хидере будет послан браузеру заголовок HTTP/1.1 404 Not Found
         */
        if (array_key_exists($this->sCurrentEvent, $this->aHttpErrors)) {
            E::ModuleMessage()->AddErrorSingle(
                E::ModuleLang()->Get('system_error_' . $this->sCurrentEvent), $this->sCurrentEvent
            );
            $aHttpError = $this->aHttpErrors[$this->sCurrentEvent];
            if (isset($aHttpError['header'])) {
                $sProtocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
                header("{$sProtocol} {$aHttpError['header']}");
            }
        }
        /**
         * Устанавливаем title страницы
         */
        E::ModuleViewer()->AddHtmlTitle(E::ModuleLang()->Get('error'));
        $this->SetTemplateAction('index');
    }
}

// EOF