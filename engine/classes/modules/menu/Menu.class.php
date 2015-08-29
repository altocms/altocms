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

    protected $aPreparedMenu = array();

    public function Init() {

    }

    protected function _getCacheKey($sMenuId, $aMenu) {

        $sCacheKey = $sMenuId . '-' . md5(serialize($aMenu))
            . ((isset($aMenu['init']['user_cache']) && $aMenu['init']['user_cache']) ? ('_' . E::UserId()) : '');

        return 'menu_' . $sCacheKey;
    }

    /**
     * Возвращает кэшированные элементы меню
     *
     * @param string $sMenuId Идентификатор меню
     * @param array $aMenu Конфиг меню
     *
     * @return ModuleMenu_EntityItem[]
     */
    protected function GetCachedItems($sMenuId, $aMenu) {

        // Нужно обновлять кэш каждый раз, когда изменился конфиг, для этого возьмем
        // хэш от сериализованного массива настроек и запишем его как имя кэша, а в теги
        // добавим идентификатор этого меню. И если кэша не будет, то на всякий случай
        // очистим по тегу.

        $sCacheKey = $this->_getCacheKey($sMenuId, $aMenu);
        if (FALSE === ($data = E::ModuleCache()->Get($sCacheKey, ',file'))) {
            $this->ClearMenuCache($sMenuId);

            return array();
        }

        return $data;
    }

    /**
     * Подготавливет все меню для вывода
     */
    public function PrepareMenus() {

        $aMenus = Config::Get('menu.data');
        if ($aMenus && is_array($aMenus)) {

            foreach($aMenus as $sMenuId => $aMenu) {
                if (!isset($this->aPreparedMenu[$sMenuId])) {
                    if (isset($aMenu['init']['fill'])) {
                        $aPreparedMenu = $this->Prepare($sMenuId, $aMenu);
                    } else {
                        $aPreparedMenu = $aMenu;
                    }
                    $this->SetPreparedMenu($sMenuId, $aPreparedMenu);
                }
            }
        }
    }

    /**
     * @param string $sMenuId
     * @param array  $aMenu
     */
    public function SetPreparedMenu($sMenuId, $aMenu) {

        $this->aPreparedMenu[$sMenuId] = $aMenu;
    }

    /**
     * @param string $sMenuId
     *
     * @return null|array
     */
    public function GetPreparedMenu($sMenuId) {

        if (isset($this->aPreparedMenu[$sMenuId])) {
            return $this->aPreparedMenu[$sMenuId];
        }
        if ($aMenu = Config::Get('menu.data.' . $sMenuId)) {
            $aPreparedMenu = $this->Prepare($sMenuId, $aMenu);
            $this->SetPreparedMenu($sMenuId, $aPreparedMenu);
            return $aPreparedMenu;
        }
        return null;
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

        // Проверим меню на разрешённые экшены
        if (isset($aMenu['actions']) && !R::AllowAction($aMenu['actions'])) {
            return array();
        }

        // Если тип меню не находится в списке разрешенных, то его не обрабатываем
        // Плагины же могут расширить этот список и переопределить данный метод.
//        if (!in_array($sMenuId, Config::Get('menu.allowed'))) {
//            return FALSE;
//        }

        // Почему-то при сохранении конфига добавляется пустой элемент массива с
        // числовым индексом
        if (isset($aMenu['list'][0]))
            unset($aMenu['list'][0]);

        // Тут возникает два варианта, либо есть закэшированные эелемнты меню,
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
                $this->ClearMenuCache($sMenuId);

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

        // Кэшируем результат, если нужно
        if (!(isset($aMenu['init']['cache']) && $aMenu['init']['cache'] == false)) {
            $sCacheKey = $this->_getCacheKey($sMenuId, $aMenu);
            E::ModuleCache()->Set(
                $aItems,
                $sCacheKey,
                array('menu_' . $sMenuId, 'menu'),
                isset($aMenu['init']['cache']) ? $aMenu['init']['cache'] : 'P30D',
                ',file'
            );
        }

        // Добавим сформированные данные к конфигу меню
        $aMenu['items'] = $aItems;


        return $aMenu;
    }

    /**
     * Возвращает меню по его идентификатору
     *
     * @param $sMenuId
     *
     * @return ModuleMenu_EntityMenu|bool
     */
    public function GetMenu($sMenuId) {

        // Настройки меню
        //$aMenuData = Config::Get('menu.data.' . $sMenuId);
        $aMenuData = $this->GetPreparedMenu($sMenuId);

        // Из них возьмем сами сформированные меню
        if (isset($aMenuData['items'])) {
            return E::GetEntity('Menu_Menu', array(
                'id'          => $sMenuId,
                'items'       => $aMenuData['items'],
                'description' => isset($aMenuData['description']) ? $aMenuData['description'] : '',
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
     *
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
        //Config::Set("menu.data.{$oMenu->getId()}.items", $oMenu->GetItems());
        $this->aPreparedMenu[$oMenu->getId()] = $oMenu->GetItems();

        // И конфиг сохраним
        $aNewConfigData = array();
        /** @var ModuleMenu_EntityItem $oMenuItem */
        foreach ($oMenu->GetItems() as $sMenuId => $oMenuItem) {
            $aNewConfigData[$sMenuId] = $oMenuItem ? $oMenuItem->getItemConfig() : "";
        }

        Config::Set("menu.data.{$oMenu->getId()}.list", null);
        Config::Set("menu.data.{$oMenu->getId()}.list", $aNewConfigData);
        Config::WriteCustomConfig(array("menu.data.{$oMenu->getId()}.list" => $aNewConfigData));
    }

    /**
     * Сбрасывет сохраненное меню в исходное состояние
     *
     * @param ModuleMenu_EntityMenu| string $xMenu
     */
    public function ResetMenu($xMenu) {

        if (is_object($xMenu)) {
            $sMenuId = $xMenu->getId();
        } else {
            $sMenuId = (string)$xMenu;
        }
        Config::ResetCustomConfig("menu.data.{$sMenuId}");
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

        return $this->Prepare($sMenuId, $aMenu);
    }

    /**
     * Возвращает имя метода обработки режима заполнения меню
     *
     * @param string $sModeName Название режима заполнения
     *
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

        return E::GetEntity('Menu_Item',
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
                isset($aItemConfig['options']) ? array('item_options' => E::GetEntity('Menu_ItemOptions', $aItemConfig['options'])) : array()
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
     *
     * @return array
     */
    public function ProcessListMode($aFillSet, $aMenu) {

        // Результирующий набор меню
        $aItems = array();

        if (!$aFillSet) {
            return $aItems;
        }

        //
        if (isset($aFillSet[0]) && $aFillSet[0] == '*') {
            $aFillSet = (isset($aMenu['list']) && $aMenu['list']) ? array_keys($aMenu['list']) : array();
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
     *
     * @return array
     */
    public function ProcessInsertImageMode($aFillSet, $aMenu = NULL) {

        /** @var ModuleMenu_EntityItem[] $aItems */
        $aItems = array();

        // Только пользователь может смотреть своё дерево изображений
//        if (!E::IsUser()) {
//            return $aItems;
//        }

        $sTopicId = getRequestStr('topic_id', getRequestStr('target_id', FALSE));
        if ($sTopicId && !E::ModuleTopic()->GetTopicById($sTopicId)) {
            $sTopicId = FALSE;
        }

        /** @var ModuleMresource_EntityMresourceCategory[] $aResources Категории объектов пользователя */
        $aCategories = E::ModuleMresource()->GetImageCategoriesByUserId(isset($aMenu['uid']) ? $aMenu['uid'] : E::UserId(), $sTopicId);

        // Получим категорию топиков для пользователя
        if ($aTopicsCategory = E::ModuleMresource()->GetTopicsImageCategory(isset($aMenu['uid']) ? $aMenu['uid'] : E::UserId())) {
            foreach ($aTopicsCategory as $oTopicsCategory) {
                $aCategories[] = $oTopicsCategory;
            }
        }

        // Временные изображения
//        if ($oTmpTopicCategory = E::ModuleMresource()->GetCurrentTopicImageCategory(isset($aMenu['uid']) ? $aMenu['uid'] : E::UserId(), false)) {
//            $aCategories[] = $oTmpTopicCategory;
//        }

        if ($sTopicId && $oCurrentTopicCategory = E::ModuleMresource()->GetCurrentTopicImageCategory(isset($aMenu['uid']) ? $aMenu['uid'] : E::UserId(), $sTopicId)) {
            $aCategories[] = $oCurrentTopicCategory;
        }

        if (!isset($aMenu['protect']) && (!isset($aMenu['uid']) || $aMenu['uid'] == E::UserId())) {
            if ($oTalksCategory = E::ModuleMresource()->GetTalksImageCategory(isset($aMenu['uid']) ? $aMenu['uid'] : E::UserId())) {
                $aCategories[] = $oTalksCategory;
            }
        }

        if ($oCommentsCategory = E::ModuleMresource()->GetCommentsImageCategory(isset($aMenu['uid']) ? $aMenu['uid'] : E::UserId())) {
            $aCategories[] = $oCommentsCategory;
        }

        if ($aCategories) {
            /** @var ModuleMresource_EntityMresourceCategory $oCategory */
            foreach ($aCategories as $oCategory) {
                $aItems['menu_insert_' . $oCategory->getId()] = $this->CreateMenuItem('menu_insert_' . $oCategory->getId(), array(
                    'text'    => $oCategory->getLabel() . '<span>' . $oCategory->getCount() . '</span>',
                    'link'    => '#',
                    'active'  => FALSE,
                    'submenu' => array(),
                    'display' => TRUE,
                    'options' => array(
                        'link_class' => '',
                        'link_url'   => '#',
                        'class'      => 'category-show category-show-' . $oCategory->getId(),
                        'link_data'  => array(
                            'category' => $oCategory->getId(),
                        ),
                    ),
                ));
            }
        }

        return $aItems;

    }

    /**
     * Обработчик формирования меню в режиме blogs
     *
     * @param string[] $aFillSet Набор элементов меню
     * @param array $aMenu Само меню
     *
     * @return array
     */
    public function ProcessBlogsMode($aFillSet, $aMenu = NULL) {

        /** @var ModuleMenu_EntityItem[] $aItems */
        $aItems = array();

        /** @var ModuleBlog_EntityBlog[] $aBlogs */
        $aBlogs = array();

        if ($aFillSet) {
            $aBlogs = E::ModuleBlog()->GetBlogsByUrl($aFillSet['items']);
        } else {
            if ($aResult = E::ModuleBlog()->GetBlogsRating(1, $aFillSet['limit'])) {
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
     *
     * @return bool
     */
    public function IsUser() {

        return E::IsUser();
    }

    /**
     * Вызывается по строке "is_admin"
     *
     * @return bool
     */
    public function IsAdmin() {

        return E::IsAdmin();
    }

    /**
     * Вызывается по строке "is_not_admin"
     *
     * @return bool
     */
    public function IsNotAdmin() {

        return E::IsNotAdmin();
    }

    /**
     * Вызывается по строке "user_id_is"
     *
     * @param $iUserId
     *
     * @return bool
     */
    public function UserIdIs($iUserId) {

        return E::UserId() == $iUserId;
    }

    /**
     * Вызывается по строке "user_id_not_is"
     *
     * @param $iUserId
     *
     * @return bool
     */
    public function UserIdNotIs($iUserId) {

        return E::UserId() != $iUserId;
    }

    /**
     * Вызывается по строке "check_plugin"
     *
     * @param $aPlugins
     *
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
     *
     * @param $aActionName
     *
     * @return bool
     */
    public function CompareAction($aActionName) {

        if (is_string($aActionName)) {
            $aActionName = array($aActionName);
        }

        return in_array(R::GetAction(), $aActionName);

    }

    /**
     * Вызывается по строке "not_action"
     *
     * @param $aActionName
     *
     * @return bool
     */
    public function NotAction($aActionName) {

        if (is_string($aActionName)) {
            $aActionName = array($aActionName);
        }

        return !in_array(R::GetAction(), $aActionName);

    }

    /**
     * Вызывается по строке "not_event"
     *
     * @param $aEventName
     *
     * @return bool
     */
    public function NotEvent($aEventName) {

        if (is_string($aEventName)) {
            $aEventName = array($aEventName);
        }

        return !in_array(R::GetActionEvent(), $aEventName);

    }

    /**
     * Вызывается по строке "new_talk"
     *
     * @param bool $sTemplate
     *
     * @return bool
     */
    public function NewTalk($sTemplate = false) {

        $sKeyString = 'menu_new_talk_' . E::UserId() . '_' . (string)$sTemplate;

        if (FALSE === ($sData = E::ModuleCache()->GetTmp($sKeyString))) {

            $iValue = (int)E::ModuleTalk()->GetCountTalkNew(E::UserId());
            if ($sTemplate && $iValue) {
                $sData = str_replace('{{new_talk_count}}', $iValue, $sTemplate);
            } else {
                $sData = $iValue ? $iValue : '';
            }

            E::ModuleCache()->SetTmp($sData, $sKeyString);
        }

        return $sData;

    }

    /**
     * Вызывается по строке "new_talk_string"
     *
     * @param string $sIcon
     *
     * @return bool
     */
    public function NewTalkString($sIcon = '') {

        $iCount = $this->NewTalk();
        if ($iCount) {
            return $sIcon . '+' . $iCount;
        }

        return $sIcon ? ($sIcon . '0') : '';
    }

    /**
     * Вызывается по строке "user_avatar_url"
     *
     * @return bool
     */
    public function UserAvatarUrl($sSize) {

        if ($oUser = E::User()) {
            return $oUser->getAvatarUrl($sSize);
        }

        return '';

    }

    /**
     * Вызывается по строке "user_name"
     *
     * @return bool
     */
    public function UserName() {

        if ($oUser = E::User()) {
            return $oUser->getDisplayName();
        }

        return '';

    }

    /**
     * Вызывается по строке "compare_param"
     *
     * @param $iParam
     * @param $sParamData
     *
     * @return bool
     */
    public function CompareParam($iParam, $sParamData) {

        return R::GetParam($iParam) == $sParamData;

    }

    /**
     * Вызывается по строке "topic_kind"
     *
     * @param $sTopicType
     *
     * @internal param $iParam
     * @internal param $sParamData
     *
     * @return bool
     */
    public function TopicKind($sTopicType) {

        if (R::GetAction() != 'index') {
            return false;
        }

        if (is_null(R::GetActionEvent())) {
            return 'good' == $sTopicType;
        }

        return R::GetActionEvent() == $sTopicType;

    }

    /**
     * Вызывается по строке "new_topics_count"
     *
     * @param string $newClass
     *
     * @internal param $iParam
     * @internal param $sParamData
     *
     * @return bool
     */
    public function NewTopicsCount($newClass = '') {

        $sKeyString = 'menu_new_topics_count_' . E::UserId() . '_' . $newClass;

        if (FALSE === ($sData = E::ModuleCache()->GetTmp($sKeyString))) {

            $iCountTopicsCollectiveNew = E::ModuleTopic()->GetCountTopicsCollectiveNew();
            $iCountTopicsPersonalNew = E::ModuleTopic()->GetCountTopicsPersonalNew();

            if ($newClass) {
                $sData = '<span class="' . $newClass . '"> +' . ($iCountTopicsCollectiveNew + $iCountTopicsPersonalNew) . '</span>';
            } else {
                $sData =  $iCountTopicsCollectiveNew + $iCountTopicsPersonalNew;
            }

            E::ModuleCache()->SetTmp($sData, $sKeyString);

        }

        return $sData;

    }

    /**
     * Вызывается по строке "no_new_topics"
     *
     * @internal param $iParam
     * @internal param $sParamData
     *
     * @return bool
     */
    public function NoNewTopics() {

        $sKeyString = 'menu_no_new_topics';

        if (FALSE === ($xData = E::ModuleCache()->GetTmp($sKeyString))) {

            $iCountTopicsCollectiveNew = E::ModuleTopic()->GetCountTopicsCollectiveNew();
            $iCountTopicsPersonalNew = E::ModuleTopic()->GetCountTopicsPersonalNew();

            $xData = $iCountTopicsCollectiveNew + $iCountTopicsPersonalNew == 0;

            E::ModuleCache()->SetTmp($xData, $sKeyString);

        }

        return $xData;

    }


    /**
     * Вызывается по строке "user_rating"
     *
     * @param string $sIcon
     * @param string $sNegativeClass
     *
     * @return bool
     */
    public function UserRating($sIcon = '', $sNegativeClass='') {

        if (!C::Get('rating.enabled')) {
            return '';
        }

        if (E::IsUser()) {
            $fRating = number_format(E::User()->getRating(), C::Get('view.rating_length'));
            if ($sNegativeClass && $fRating < 0) {
                $fRating = '<span class="'. $sNegativeClass .'">' . $fRating . '</span>';
            }
            return $sIcon . $fRating;
        }

        return '';

    }


    /**
     * Вызывается по строке "count_track"
     *
     * @param string $sIcon
     *
     * @return bool
     */
    public function CountTrack($sIcon = '') {

        if (!E::IsUser()) {
            return '';
        }

        $sKeyString = 'menu_count_track_' . E::User()->getId() . '_' . $sIcon;

        if (FALSE === ($xData = E::ModuleCache()->GetTmp($sKeyString))) {

            $sCount = E::ModuleUserfeed()->GetCountTrackNew(E::User()->getId());
            $xData = $sIcon . ($sCount ? '+' . $sCount : '0');

            E::ModuleCache()->SetTmp($xData, $sKeyString);

        }

        return $xData;

    }

    /**
     * Возвращает количество сообщений для пользователя
     *
     * @param bool $sTemplate
     *
     * @return int|mixed|string
     */
    public function CountMessages($sTemplate = false) {

        if (!E::IsUser()) {
            return '';
        }

        $sKeyString = 'menu_count_messages_' . E::UserId() . '_' . (string)$sTemplate;

        if (FALSE === ($sData = E::ModuleCache()->GetTmp($sKeyString))) {

            $iValue = (int)$this->CountTrack() + (int)$this->NewTalk();
            if ($sTemplate && $iValue) {
                $sData = str_replace('{{count_messages}}', $iValue, $sTemplate);
            } else {
                $sData = $iValue ? $iValue : '';
            }

            E::ModuleCache()->SetTmp($sData, $sKeyString);
        }

        return $sData;

    }

    public function ClearMenuCache($sMenuId) {

        E::ModuleCache()->CleanByTags(array('menu_' . $sMenuId), ',file');
    }

    public function ClearAllMenuCache() {

        E::ModuleCache()->CleanByTags(array('menu'), ',file');
    }

}

// EOF