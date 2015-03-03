<?php
/** Запрещаем напрямую через браузер обращение к этому файлу.  */
if (!class_exists('Plugin')) {
    die('Hacking attempt!');
}

/**
 * PluginMenutest.class.php
 * Файл основного класса плагина Correct
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 *
 * @method void Viewer_AppendStyle
 * @method void Viewer_AppendScript
 * @method void Viewer_Assign
 *
 * @version     0.0.1 от 16.11.2014 01:53
 */
class PluginMenutest extends Plugin {

    /** @var array $aDelegates Объявление делегирований */
    protected $aDelegates = array(
        'template' => array(),
    );

    /** @var array $aInherits Объявление переопределений (модули, мапперы и сущности) */
    protected $aInherits = array();

    /**
     * Активация плагина
     * @return bool
     */
    public function Activate() {
        return TRUE;
    }

    /**
     * Деактивация плагина
     * @return bool
     */
    public function Deactivate() {
        $aMenuList = C::Get('menu.data.user.list');
        unset($aMenuList['plugin_menutest_my_menu']);
        C::WriteCustomConfig(array('menu.data.user.list' => $aMenuList));
        C::ResetCustomConfig('menu.data.user.list.plugin_menutest_my_menu');
        return TRUE;
    }

    /**
     * Инициализация плагина
     */
    public function Init() {

    }

}
