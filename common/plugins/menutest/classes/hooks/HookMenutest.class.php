<?php

/**
 * HookMenutest.class.php
 * Файл хука плагина Menutest
 *
 * @version     1.1
 */
class PluginMenutest_HookMenutest extends Hook {

    /**
     * Регистрация хуков
     */
    public function RegisterHook() {

        $this->AddHook('module_menu_createmenu_after', 'CodeHook');
    }


    /**
     * Метод, который вызывается после создания меню
     */
    public function CodeHook() {

        // Получим результат выполнения метода-источника хука
        /** @var ModuleMenu_EntityMenu $oMenu */
        $oMenu = $this->GetSourceResult();
        // Убеждаемся, что было создано нужное нам меню - меню пользователя
        if ($oMenu && $oMenu->getId() == 'user') {
            // Если пользователь авторизован и у него не заполнено поле о себе, то
            if (E::IsUser() && trim(E::User()->getProfileAbout()) == '') {
                // Проверим, есть ли в меню нужный элемент
                if (!$oMenu->GetItemById('plugin.menutest.my_menu')) {

                    // Создадим элемент меню
                    $oMenuItem = E::ModuleMenu()->CreateMenuItem('plugin.menutest.my_menu', array(
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
                    $oMenu->AddItem($oMenuItem, 'first');

                    // Сохраним
                    E::ModuleMenu()->SaveMenu($oMenu);
                }
            }
        }
    }

}

// EOF