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

        $this->AddHook('module_menu_preparemenus_after', 'CodeHook');
    }


    public function CodeHook() {

        // Если пользоватль авторизован и у него не заполнено поле о себе, то
        if (E::IsUser() && trim(E::User()->getProfileAbout()) == '') {

            // Получим меню пользователя
            /** @var ModuleMenu_EntityMenu $oMenu */
            $oMenu = E::ModuleMenu()->GetMenu('user');

            // Проверим, может в этой теме меню не объектное
            if ($oMenu && !$oMenu->GetItemById('plugin_menutest_my_menu')) {

                // Создадим элемент меню
                $oMenuItem = E::ModuleMenu()->CreateMenuItem('plugin_menutest_my_menu', array(
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
                $oMenu->AddItem('first', $oMenuItem);

                // Сохраним
                E::ModuleMenu()->SaveMenu($oMenu);
            }
        }

    }

}
