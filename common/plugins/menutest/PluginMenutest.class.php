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

        return true;
    }

    /**
     * Деактивация плагина
     * @return bool
     */
    public function Deactivate() {

        $oMenu = E::ModuleMenu()->GetMenu('user');
        $oMenu->RemoveItemById('plugin.menutest.my_menu', true);
        E::ModuleMenu()->SaveMenu($oMenu);

        return true;
    }

    /**
     * Инициализация плагина
     */
    public function Init() {

        return true;
    }

}

// EOF