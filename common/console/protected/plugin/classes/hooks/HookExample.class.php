<?php

class PluginExample_HookExample extends Hook {

    /*
     * Регистрация событий на хуки
     */
    public function RegisterHook() {

        /*
         * Хук 'before' - вызывется перед вызовом публичного метода модуля
         *
         * Хук перед методом AddTopic() модуля Topic (файл /classes/modules/topic/Topic.class.php,
         * если этот модуль не переопределен в других плагинах):
         *
         * $this->AddHook('module_topic_addtopic_before', 'beforeAddTopic');
         *
         * Будет вызван метод beforeAddTopic($aVars) класса хука,
         * где $aVars - НЕассоциативный массив аргументов, переданных этой функции.
         *
         * Передача результата в функцию AddTopic() делается путем изменения аргументов
         * по ссылке - например, &$aVars[0]
         */


        /*
         * Хук 'after' - вызывается после вызова публичного метода модуля
         *
         * Хук после метода AddTopic() модуля Topic (файл /classes/modules/topic/Topic.class.php,
         * если этот модуль не переопределен в других плагинах):
         *
         * $this->AddHook('module_topic_addtopic_after', 'afterAddTopic');
         *
         * Будет вызван метод afterAddTopic($Var) класса хука, где $Var - это то,
         * что возвращает AddTopic() (т.е. или false или объект топика $oTopic)
         *
         * Функция должна завершаться при помощи return $Var
         */


        /*
         * Хук на явный вызов
         *
         * $this->AddHook('init_action', 'initAction', -5);
         *
         * Приоритет для вызова хука = -5. Этот приоритет так же можно указывать и в хуках на модели.
         * Будет вызван метод initAction($Var) в том месте движка, где стоит явный вызов E::ModuleHook()->Run('init_action')
         */

        /*
         * Шаблонный хук - вызов метода
         *
         * $this->AddHookTemplate('menu_profile_created_item', 'hookMenuItem');
         *
         * Будет вызван метод hookMenuItem() класса хука и результат (в виде строки)
         * будет отображен в месте вызова хука в шаблоне
         */

        /*
         * Шаблонный хук - файл шаблона
         *
         * $this->AddHookTemplate('menu_profile_created_item', Plugin::GetTemplateDir(__CLASS__) . '/tpls/menu_profile_created_item.tpl');
         *
         * В месте вызова хука в шаблоне отображается результат обработки заданного
         * файла шаблона
         */

    }
}

// EOF
