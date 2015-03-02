<?php

/**
 * Estheme.class.php
 * Файл модуля Estheme плагина Estheme
 *
 * @author      Андрей Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2015, Андрей Воронов
 *              Является частью плагина Estheme
 * @version     0.0.1 от 27.02.2015 08:56
 */
class PluginEstheme_ModuleEstheme extends Module {
    /**
     * Маппер модуля
     * @var
     */
    protected $oEsthemeMapper;

    /**
     * Текущий пользователь
     * @var ModuleUser_EntityUser
     */
    protected $oUserCurrent = NULL;

    /**
     * Инициализация модуля
     */
    public function Init() {

        // Получение текущего пользователя
//        $this->oUserCurrent = $this->User_GetUserCurrent();

        // Получение мапперов
        $this->oEsthemeMapper = Engine::GetMapper('PluginEstheme_ModuleEstheme', 'Estheme');
    }

    /**
     * Возвращает данные о сохраняемых параметрах в формате
     * конфигурационный_параметр => array(дефолтное_значение, имя_less_параметра)
     *
     * @return array
     */
    public function GetProcessData() {
        return array(
            'color.main.color'                 => array('#333333', 'dark-blue'),
            'color.main.light'                 => array('#4d4d4d', 'dark-blue-l-10'),
            'color.main.dark'                  => array('#1a1a1a', 'dark-blue-d-10'),
            'color.main.dark_2'                => array('#0d0d0d', 'main-gray'),
            'color.main.font'                  => array('#333333', 'body-font-color'),
            'color.main.active_link'           => array('#1a1a1a', 'active-link-color'),

            'color.other.gray'                 => array('#555555', 'gray'),
            'color.other.blue'                 => array('#4b8bbc', 'blue'),
            'color.other.light_blue'           => array('#669cc6', 'light_blue'),
            'color.other.red'                  => array('#c43a3a', 'red'),
            'color.other.green'                => array('#57a839', 'green'),
            'color.other.orange'               => array('#e68f12', 'orange'),

            'metrics.main.width'               => array('1000', 'site-width', '{{value}}px'),
            'metrics.main.menu_main_height'    => array('52', 'navbar-main-height', '{{value}}px'),
            'metrics.main.menu_content_height' => array('46', 'navbar-content-height', '{{value}}px'),
            'metrics.main.font_size'           => array('14', 'font-size-base', '{{value}}px'),
            'metrics.main.font_size_small'     => array('13', 'font-size-small', '{{value}}px'),
            'metrics.main.h1'                  => array('24', 'font-size-h1', '{{value}}px'),
            'metrics.main.h2'                  => array('22', 'font-size-h2', '{{value}}px'),
            'metrics.main.h3'                  => array('22', 'font-size-h3', '{{value}}px'),
            'metrics.main.h4'                  => array('18', 'font-size-h4', '{{value}}px'),
            'metrics.main.h5'                  => array('16', 'font-size-h5', '{{value}}px'),
            'metrics.main.h6'                  => array('14', 'font-size-h6', '{{value}}px'),
        );
    }

    /**
     * Компилирует тему
     *
     * @param array $aParams Передаваемые параметры
     * @return bool
     */
    public function CompileTheme($aParams, $bDownload = FALSE) {

        if (!E::User()) {
            return FALSE;
        }

        F::IncludeFile(__DIR__ . '/../../../libs/Less.php');

        try {
            $sMapPath = C::Get('path.skins.dir') . 'experience-simple/themes/custom/css/theme.custom.css.map';
            $options = array(
                'sourceMap'        => TRUE,
                'sourceMapWriteTo' => $sMapPath,
                'sourceMapURL'     => E::ModuleViewerAsset()->AssetFileUrl($sMapPath),
                'cache_dir'        => __DIR__ . '/../../../libs/cache'
            );

            if ($bDownload) {
                $options = array_merge($options, array('compress' => TRUE));
            }


            $sCssFileName = Less_Cache::Get(
                array(C::Get('path.skins.dir') . 'experience-simple/themes/custom/less/theme.less' => C::Get('path.root.web')),
                $options,
                $aParams
            );

            $sCompiledStyle = file_get_contents(__DIR__ . '/../../../libs/cache/' . $sCssFileName);
            if (!$bDownload || E::IsAdmin()) {
                F::File_PutContents(C::Get('path.skins.dir') . 'experience-simple/themes/custom/css/theme.custom.css', $sCompiledStyle);
            } else {
                $sPath = C::Get('plugin.estheme.path_for_download') . E::UserId() . '/theme.custom.css';
                F::File_PutContents($sPath, $sCompiledStyle);
            }

        } catch (Exception $e) {
            E::ModuleMessage()->AddErrorSingle($e->getMessage());
        }

    }
}