<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

/**
 * @package engine.modules
 * @since   1.0.2
 */
class ModuleMenu extends Module {

    public function Init() {

    }

    /**
     * Возвращает кэшированные элементы меню
     *
     * @param string $sMenuId Идентификатор меню
     * @param array $aMenu Конфиг меню
     * @return ModuleMenu_EntityItem[]
     */
    protected function GetCachedItems($sMenuId, $aMenu) {

        // Нужно обновлять кэш каждый раз, когда изменился конфиг, для этого возьмем
        // хэш от сериализованного массива настроек и запишем его как имя кэша, а в теги
        // добавим идентификатор этого меню. И если кэша не будет, то на всякий случай
        // очистим по тегу.

        $sCacheKey = md5(serialize($aMenu));
        if (FALSE === ($data = $this->Cache_Get($sCacheKey))) {
            $this->Cache_CleanByTags(array($sMenuId));

            return array();
        }

        return $data;

    }

    /**
     * Подготавливает меню для вывода, заполняя его из указанных в
     * конфиге параметров.
     *
     * @param string $sMenuId Ид. меню, как оно указано в конфиге, например "main" для $config['view']['menu']['main']
     * @param array $aMenu Конфигурация самого меню
     *
     * @return string
     */
    public function Prepare($sMenuId, $aMenu) {

        // Если тип меню не находится в списке разрешенных, то его не обрабатываем
        // Плагины же могут расширить этот список и переопределить данный метод.
//        if (!in_array($sMenuId, Config::Get('menu.allowed'))) {
//            return FALSE;
//        }

        // Почему-то при сохранении конфига добавляется пустой элемент массива с
        // числовым индексом
        if (isset($aMenu['list'][0]))
            unset($aMenu['list'][0]);

        // Тут возникает два варианта, либо есть закэширвоанные элеемнты меню,
        // либо их нет. Если есть, то вернем их
        /** @var ModuleMenu_EntityItem[] $aCashedItems */
        $aCashedItems = $this->GetCachedItems($sMenuId, $aMenu);
        if ($aCashedItems) {
            $aMenu['items'] = $aCashedItems;

            return $aMenu;
        }

        // Получим разрешенное количество элементов меню. Имеет смысл только для динамического
        // заполнения списка меню.
        /** @var int $iTotal */
        $iTotal = (isset($aMenu['init']['total'])
            ? intval($aMenu['init']['total'])
            : Config::Get('module.menu.default_length'));


        // Получим список режимов заполнения меню
        /** @var string[] $aFillMode */
        if (!$aFillMode = (isset($aMenu['init']['fill']) ? $aMenu['init']['fill'] : FALSE)) {
            return FALSE;
        }

        // Проверим корректность переданного режима заполнения
        if (is_array($aFillMode) && $aModeName = array_keys($aFillMode)) {

            // Проверим режимы на наличие их обработчиков
            foreach ($aModeName as $sModeName) {
                // Если нет метода для обработки этого режима заполнения, то
                // удалим его за ненадобностью
                if (!method_exists($this, $this->_getProcessMethodName($sModeName))) {
                    unset($aFillMode[$sModeName]);
                }
            }

            // Если валидных режимов заполнения не осталось, то завершимся
            // и сбросим кэш, ведь очевидно, что меню пустое :(
            if (empty($aFillMode)) {
                $this->Cache_Delete('menu_' . $sMenuId);

                return FALSE;
            }

        }


        // Заполняем элементы меню указанным способом
        $aItems = array();
        foreach ($aFillMode as $sModeName => $aFillSet) {
            $aItems = array_merge(
                $aItems,
                call_user_func_array(
                    array($this, $this->_getProcessMethodName($sModeName)),
                    array($aFillSet, $aMenu)
                )
            );
        }


        // Проверим количество элементов меню по допустимому максимальному количеству
        if (sizeof($aItems) > $iTotal) {
            $aItems = array_slice($aItems, 0, $iTotal);
        }

        // Кэшируем результат
        $this->Cache_Set(
            $aItems,
            md5(serialize($aMenu)),
            array($sMenuId),
            isset($aMenu['init']['cache']) ? $aMenu['init']['cache'] : 'P30D'
        );

        // Добавим сформированные данные к конфигу меню
        $aMenu['items'] = $aItems;


        return $aMenu;
    }

    /**
     * Возвращает меню по его идентификатору
     *
     * @param $sMenuId
     * @return bool
     */
    public function GetMenu($sMenuId) {

        // Настройки меню
        $aMenuConfig = Config::Get('menu.data.' . $sMenuId);

        // Из них возьмем сами сформированные меню
        if (isset($aMenuConfig['items'])) {
            return Engine::GetEntity('Menu_Menu', array(
                'id'          => $sMenuId,
                'items'       => $aMenuConfig['items'],
                'description' => isset($aMenuConfig['description']) ? $aMenuConfig['description'] : '',
            ));
        }

        return FALSE;
    }

    /**
     * Получает все меню сайта
     *
     * @return ModuleMenu_EntityMenu[]
     */
    public function GetMenus() {
        /** @var string[] $aMenuId */
        $aMenuId = array_keys(Config::Get('menu.data'));

        return $this->GetMenusByArrayId($aMenuId);
    }

    /**
     * Получает все меню сайта
     *
     * @param string[] $aMenuId
     * @return ModuleMenu_EntityMenu[]
     */
    public function GetMenusByArrayId($aMenuId) {

        if (!is_array($aMenuId)) {
            $aMenuId = array($aMenuId);
        }

        /** @var ModuleMenu_EntityMenu[] $aResult */
        $aResult = array();
        if ($aMenuId) {
            foreach ($aMenuId as $sMenuId) {
                $aResult[] = $this->GetMenu($sMenuId);
            }
        }

        return $aResult;
    }

    /**
     * Сохраняем меню
     *
     * @param ModuleMenu_EntityMenu $oMenu
     */
    public function SaveMenu($oMenu) {

        // Установим объект для дальнейшего использования
        Config::Set("menu.data.{$oMenu->getId()}.items", $oMenu->GetItems());

        // И конфиг сохраним
        $aNewConfigData = array();
        /** @var ModuleMenu_EntityItem $oMenuItem */
        foreach ($oMenu->GetItems() as $oMenuItem) {
            $aNewConfigData[$oMenuItem->getId()] = $oMenuItem->getItemConfig();
        }
        Config::WriteCustomConfig(array("menu.data.{$oMenu->getId()}.list" => $aNewConfigData));
        Config::Set("menu.data.{$oMenu->getId()}.list", $aNewConfigData);

    }

    /**
     * Подготавливает меню для вывода, заполняя его из указанных в
     * конфиге параметров. Синоним {@see Prepare}
     *
     * @param string $sMenuId Ид. меню, как оно указано в конфиге, например "main" для $config['view']['menu']['main']
     * @param array $aMenu Конфигурация самого меню
     *
     * @return string
     */
    public function CreateMenu($sMenuId, $aMenu) {
        return $this->Prepare($sMenuId, $sMenuId);
    }

    /**
     * Возвращает имя метода обработки режима заполнения меню
     *
     * @param string $sModeName Название режима заполнения
     * @return string
     */
    private function _getProcessMethodName($sModeName) {
        return 'Process' . F::StrCamelize($sModeName) . 'Mode';
    }

    /**
     * Создает элемент меню по конфигурационным параметрам
     * <pre>
     * 'index' => array(
     *      'text'      => '{{topic_title}}', // Текст из языкового файла
     *      'link'      => '___path.root.url___', // динамическая подстановка из конфига
     *      'active'    => 'blog.hello',
     *      'options' => array( // любые опции
     *                      'type' => 'special',
     *                      'icon_class' => 'fa fa-file-text-o',
     *                  ),
     *      'submenu' => array(
     *          // массив подменю
     *      ),
     *      'on'        => array('index', 'blog'), // где показывать
     *      'off'       => array('admin/*', 'settings/*', 'profile/*', 'talk/*', 'people/*'), // где НЕ показывать
     *      'display'   => true,  // true - выводить, false - не выводить
     *  ),
     * </pre>
     * @param $sItemId
     * @param $aItemConfig
     *
     * @return ModuleMenu_EntityItem
     */
    public function CreateMenuItem($sItemId, $aItemConfig) {

        if (is_string($aItemConfig)) {
            return $aItemConfig;
        }

        return Engine::GetEntity('Menu_Item',
            array_merge(
                array('item_id' => $sItemId, 'item_config' => $aItemConfig),
                isset($aItemConfig['title']) ? array('item_title' => $aItemConfig['title']) : array(),
                isset($aItemConfig['text']) ? array('item_text' => $aItemConfig['text']) : array(),
                isset($aItemConfig['link']) ? array('item_url' => $aItemConfig['link']) : array(),
                isset($aItemConfig['active']) ? array('item_active' => $aItemConfig['active']) : array(),
                isset($aItemConfig['description']) ? array('item_description' => $aItemConfig['description']) : array(),
                isset($aItemConfig['type']) ? array('item_active' => $aItemConfig['type']) : array(),
                isset($aItemConfig['submenu']) ? array('item_submenu' => $aItemConfig['submenu']) : array(),
                isset($aItemConfig['on']) ? array('item_on' => $aItemConfig['on']) : array(),
                isset($aItemConfig['off']) ? array('item_off' => $aItemConfig['off']) : array(),
                isset($aItemConfig['display']) ? array('item_display' => $aItemConfig['display']) : array(),
                isset($aItemConfig['show']) ? array('item_show' => $aItemConfig['show']) : array(),
                isset($aItemConfig['options']) ? array('item_options' => Engine::GetEntity('Menu_ItemOptions', $aItemConfig['options'])) : array()
            )
        );
    }



    /******************************************************************************
     *          МЕТОДЫ ЗАПОЛНЕНИЯ МЕНЮ
     ******************************************************************************/

    /**
     * Обработчик формирования меню в режиме list
     *
     * @param string[] $aFillSet Набор элементов меню
     * @param array $aMenu Само меню
     * @return array
     */
    public function ProcessListMode($aFillSet, $aMenu) {

        // Результирующий набор меню
        $aItems = array();

        //
        if (isset($aFillSet[0]) && $aFillSet[0] == '*') {
            $aFillSet = isset($aMenu['list']) ? array_keys($aMenu['list']) : array();
        }

        // Добавим в вывод только нужные элементы меню
        foreach ($aFillSet as $sItemId) {
            if (isset($aMenu['list'][$sItemId])) {
                /** @var ModuleMenu_EntityItem $oMenuItem */
                $oMenuItem = $this->CreateMenuItem($sItemId, $aMenu['list'][$sItemId]);

                // Это не хук, добавим флаг режима заполнения
                if (!is_string($oMenuItem)) {
                    $oMenuItem->setMenuMode('list');
                }

                // Это хук
                if (is_string($oMenuItem)) {
                    $aItems[$sItemId] = $oMenuItem;
                    continue;
                }

                $aItems[$sItemId] = $oMenuItem;

            }
        }


        return $aItems;

    }

    /**
     * Обработчик формирования меню в режиме blogs
     *
     * @param string[] $aFillSet Набор элементов меню
     * @param array $aMenu Само меню
     * @return array
     */
    public function ProcessBlogsMode($aFillSet, $aMenu = NULL) {

        /** @var ModuleMenu_EntityItem[] $aItems */
        $aItems = array();

        /** @var ModuleBlog_EntityBlog[] $aBlogs */
        $aBlogs = array();

        if ($aFillSet) {
            $aBlogs = $this->Blog_GetBlogsByUrl($aFillSet['items']);
        } else {
            if ($aResult = $this->Blog_GetBlogsRating(1, $aFillSet['limit'])) {
                $aBlogs = $aResult['collection'];
            }
        }

        if ($aBlogs) {
            foreach ($aBlogs as $oBlog) {
                $aItems[$oBlog->getUrl()] = $this->CreateMenuItem($oBlog->getUrl(), array(
                    'title'   => $oBlog->getTitle(),
                    'link'    => $oBlog->getUrlFull(),
                    'active'  => (Config::Get('router.rewrite.blog') ? Config::Get('router.rewrite.blog') : 'blog') . '.' . $oBlog->getUrl(),
                    'submenu' => array(),
                    'on'      => array('{*}'),
                    'off'     => NULL,
                    'display' => TRUE,
                    'options' => array(
                        'image_url' => $oBlog->getAvatarUrl(Config::Get('module.menu.blog_logo_size')),
                    ),
                ));
            }
        }

        return $aItems;

    }


    /******************************************************************************
     *          МЕТОДЫ ПРОВЕРКИ
     ******************************************************************************/

    /**
     * Вызывается по строке "is_user"
     * @return bool
     */
    public function IsUser() {
        return E::IsUser();
    }

    /**
     * Вызывается по строке "is_admin"
     * @return bool
     */
    public function IsAdmin() {
        return E::IsAdmin();
    }

    /**
     * Вызывается по строке "is_not_admin"
     * @return bool
     */
    public function IsNotAdmin() {
        return E::IsNotAdmin();
    }

    /**
     * Вызывается по строке "user_id_is"
     * @param $iUserId
     * @return bool
     */
    public function UserIdIs($iUserId) {
        return E::UserId() == $iUserId;
    }

    /**
     * Вызывается по строке "user_id_not_is"
     * @param $iUserId
     * @return bool
     */
    public function UserIdNotIs($iUserId) {
        return E::UserId() != $iUserId;
    }

    /**
     * Вызывается по строке "check_plugin"
     * @param $aPlugins
     * @return bool
     */
    public function CheckPlugin($aPlugins) {
        if (is_string($aPlugins)) {
            $aPlugins = array($aPlugins);
        }

        $bResult = FALSE;
        foreach ($aPlugins as $sPluginName) {
            $bResult = $bResult || E::ActivePlugin($sPluginName);
            if ($bResult) {
                break;
            }
            continue;
        }

        return $bResult;
    }

    /**
     * Вызывается по строке "compare_action"
     * @param $aActionName
     * @return bool
     */
    public function CompareAction($aActionName) {

        if (is_string($aActionName)) {
            $aActionName = array($aActionName);
        }

        return in_array(Router::GetAction(), $aActionName);

    }

    /**
     * Вызывается по строке "not_action"
     * @param $aActionName
     * @return bool
     */
    public function NotAction($aActionName) {

        if (is_string($aActionName)) {
            $aActionName = array($aActionName);
        }

        return !in_array(Router::GetAction(), $aActionName);

    }

    /**
     * Вызывается по строке "not_event"
     * @param $aEventName
     * @return bool
     */
    public function NotEvent($aEventName) {

        if (is_string($aEventName)) {
            $aEventName = array($aEventName);
        }

        return !in_array(Router::GetActionEvent(), $aEventName);

    }

    /**
     * Вызывается по строке "new_talk"
     * @return bool
     */
    public function NewTalk() {

        return (int)$this->Talk_GetCountTalkNew(E::UserId());

    }

    /**
     * Вызывается по строке "new_talk_string"
     * @return bool
     */
    public function NewTalkString() {

        $iCount = (int)$this->Talk_GetCountTalkNew(E::UserId());
        if ($iCount) {
            return '+' . $iCount;
        }

        return '';
    }

    /**
     * Вызывается по строке "user_avatar_url"
     * @return bool
     */
    public function UserAvatarUrl($sSize) {

        if (E::IsUser()) {
            return E::User()->getAvatarUrl($sSize);
        }

        return '';

    }

    /**
     * Вызывается по строке "user_name"
     * @return bool
     */
    public function UserName() {

        if (E::IsUser()) {
            return E::User()->getDisplayName();
        }

        return '';

    }

    /**
     * Вызывается по строке "compare_param"
     * @param $iParam
     * @param $sParamData
     * @return bool
     */
    public function CompareParam($iParam, $sParamData) {

        return Router::GetParam($iParam) == $sParamData;

    }

    /**
     * Вызывается по строке "topic_kind"
     * @param $sTopicType
     * @internal param $iParam
     * @internal param $sParamData
     * @return bool
     */
    public function TopicKind($sTopicType) {

        if (is_null(Router::GetActionEvent())) {
            return 'good' == $sTopicType;
        }

        return Router::GetActionEvent() == $sTopicType;

    }

    /**
     * Вызывается по строке "new_topics_count"
     * @internal param $iParam
     * @internal param $sParamData
     * @return bool
     */
    public function NewTopicsCount() {

        $iCountTopicsCollectiveNew = $this->Topic_GetCountTopicsCollectiveNew();
        $iCountTopicsPersonalNew = $this->Topic_GetCountTopicsPersonalNew();

        return $iCountTopicsCollectiveNew + $iCountTopicsPersonalNew;

    }

    /**
     * Вызывается по строке "no_new_topics"
     * @internal param $iParam
     * @internal param $sParamData
     * @return bool
     */
    public function NoNewTopics() {

        $iCountTopicsCollectiveNew = $this->Topic_GetCountTopicsCollectiveNew();
        $iCountTopicsPersonalNew = $this->Topic_GetCountTopicsPersonalNew();

        return $iCountTopicsCollectiveNew + $iCountTopicsPersonalNew == 0;

    }

}