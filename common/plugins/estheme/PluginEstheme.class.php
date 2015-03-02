<?php
/** Запрещаем напрямую через браузер обращение к этому файлу.  */
if (!class_exists('Plugin')) {
    die('Hacking attempt!');
}

/**
 * PluginEstheme.class.php
 * Файл основного класса плагина Estheme
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2015, Андрей Воронов
 *              Является частью плагина Estheme        
 *
 * @method void Viewer_AppendStyle
 * @method void Viewer_AppendScript
 * @method void Viewer_Assign
 *
 * @version     0.0.1 от 27.02.2015 08:56
 */

class PluginEstheme extends Plugin {

    /** @var array $aDelegates Объявление делегирований */
    protected $aDelegates = array(
        'template' => array(),
    );

    /** @var array $aInherits Объявление переопределений (модули, мапперы и сущности) */
    protected $aInherits = array(
        'actions' => array(
            'ActionAdmin',
        ),
        'modules' => array(),
        'entity' => array(),
    );

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
        return TRUE;
    }

    /**
     * Инициализация плагина
     */
    public function Init() {
        $this->Viewer_Assign("sTemplatePathEstheme", Plugin::GetTemplatePath(__CLASS__));
        E::ModuleViewer()->AppendStyle(Plugin::GetTemplateDir(__CLASS__)."assets/css/style.min.css");

        E::ModuleViewer()->AppendScript(Plugin::GetTemplateDir(__CLASS__)."assets/js/develop/jquery.color.js");
        E::ModuleViewer()->AppendScript(Plugin::GetTemplateDir(__CLASS__)."assets/js/develop/colorPicker.js");
        E::ModuleViewer()->AppendScript(Plugin::GetTemplateDir(__CLASS__)."assets/js/develop/esTheme.js");
    }

}
