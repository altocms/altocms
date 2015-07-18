<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

F::IncludeLib('less.php/Less.php');

class ModuleLess extends Module {

    /**
     * Инициализация модуля
     *
     */
    public function Init() {

    }

    /**
     * Компилирует файл less и возвращает текст css-файла
     *
     * @param $aFile
     * @param $sCacheDir
     * @param $sMapPath
     * @param $aParams
     * @param $bCompress
     * @return string
     */
    public function CompileFile($aFile, $sCacheDir, $sMapPath, $aParams, $bCompress) {

        if (!($sMapPath && $sCacheDir && $aFile)) {
            return '';
        }

        try {
            $options = array(
                'sourceMap'        => TRUE,
                'sourceMapWriteTo' => $sMapPath,
                'sourceMapURL'     => E::ModuleViewerAsset()->AssetFileUrl($sMapPath),
                'cache_dir'        => $sCacheDir
            );

            if ($bCompress) {
                $options = array_merge($options, array('compress' => TRUE));
            }


            $sCssFileName = Less_Cache::Get(
                $aFile,
                $options,
                $aParams
            );

            return file_get_contents($sCacheDir . $sCssFileName);

        } catch (Exception $e) {
            E::ModuleMessage()->AddErrorSingle($e->getMessage());
        }

        return '';

    }

}