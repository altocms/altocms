<?php
/** Запрещаем напрямую через браузер обращение к этому файлу.  */
if (!class_exists('Plugin')) {
    die('Hacking attempt!');
}

/**
 * PluginRating.class.php
 * Файл основного класса плагина Rating
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 *              Является частью плагина Rating
 *
 * @version     0.0.1 от 01.30.2015 15:45
 */
class PluginRating extends Plugin {

    /** @var array $aDelegates Объявление делегирований */
    public $aDelegates = array(
        'template' => array(
            'widgets/widget.blogs_top.tpl' => '_blog/widget.blogs_top.tpl',
        )
    );

    /** @var array $aInherits Объявление переопределений (модули, мапперы и сущности) */
    protected $aInherits = array(
        'module' => array('ModuleRating'),
        'action' => array('ActionAdmin'),
    );

    /**
     * Активация плагина
     * @return bool
     */
    public function Activate() {
        // Включим систему рейтинга сразу
        $aData['rating.enabled'] = TRUE;
        Config::WriteCustomConfig($aData);
        return TRUE;
    }

    /**
     * Деактивация плагина
     * @return bool
     */
    public function Deactivate() {

        // Отключим настройку использования рейтинга в хранилище
        $aData['rating.enabled'] = FALSE;
        Config::WriteCustomConfig($aData);

        return TRUE;
    }


    // Инициализация плагина
    public function Init() {
        E::ModuleViewer()->AppendStyle(Plugin::GetTemplateDir(__CLASS__)."assets/css/rating.css");
        return TRUE;
    }
}