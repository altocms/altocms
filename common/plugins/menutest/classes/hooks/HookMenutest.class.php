<?php

/**
 * HookMenutest.class.php
 * Файл хука плагина Menutest
 *
 * @version     0.0.1 от 16.11.2014 23:07
 */
class PluginMenutest_HookMenutest extends Hook {

    /**
     * Регистрация хуков
     */
    public function RegisterHook() {

        $this->AddHook('render_init_done', 'CodeHook');

    }


    public function CodeHook() {

        // Если пользоватль авторизован и у него не заполнено поле о себе, то
        if (E::IsUser() && trim(E::User()->getProfileAbout()) == '') {

            // Получим меню пользователя
            /** @var ModuleMenu_EntityMenu $oMenu */
            $oMenu = $this->Menu_GetMenu('user');

            // Проверим, может в этой теме меню не объектное
            if ($oMenu) {

                // Создадим элемент меню
                $oMenuItem = $this->Menu_CreateMenuItem(F::RandomStr(5), array(
                    'text'    => '{{plugin.menutest.empty_about}}',
                    'link'    => E::User()->getProfileUrl() . 'settings/',
                    'display' => array(
                        'not_event' => array('settings'),
                    ),
                    'options' => array(
                        'class' => 'btn right create',
                    ),
                ));

                // Добавим в меню
                $oMenu->AddItem('last', $oMenuItem);

                // Сохраним
                $this->Menu_SaveMenu($oMenu);
            }
        }

    }

}
