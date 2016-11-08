<?php

class CmdPlugin extends LSC {
    protected $_name;

    /*
     * Выводим помощь о команде
     */
    public function getHelp() {
        return <<<EOD
USAGE:
  ls plugin new <plugin-name>
EOD;
    }

    /*
     * Подкоманда создания нового плагина
     */
    public function actionNew($aArgs) {
        include_once '../../../engine/include/functions/Main.php';

        // Передано ли имя нового плагина
        if (!isset($aArgs[0])) {
            die("The plugin name is not specified.\n");
        }

        $this->_name = $aArgs[0];

        $path = strtr($this->_name, '/\\', DIRECTORY_SEPARATOR);
        $path = Config::Get('path.root.server') . '/common/plugins/' . $path;
        if (strpos($path, DIRECTORY_SEPARATOR) === false) {
            $path = '.' . DIRECTORY_SEPARATOR . $path;
        }

        $dir = rtrim(realpath(dirname($path)), '\\/');
        if ($dir === false || !is_dir($dir)) {
            die("The directory '$path' is not valid. Please make sure the parent directory exists.\n");
        }

        $sourceDir = realpath(dirname(__FILE__) . '/../protected/plugin');
        if ($sourceDir === false) {
            die("\nUnable to locate the source directory.\n");
        }

        // Создаем массив файлов для функции копирования
        $aList = $this->buildFileList($sourceDir, $path);

        // Парсим имена плагинов и пересоздаем массив
        foreach ($aList as $sName => $aFile) {
            $sTarget  = str_replace('Example', AltoFunc_Main::StrCamelize($this->_name), $aFile['target']);
            $sTarget  = str_replace('example', strtolower($this->_name), $sTarget);
            $sNewName = str_replace('Example', AltoFunc_Main::StrCamelize($this->_name), $sName);
            $sNewName = str_replace('example', AltoFunc_Main::StrUnderscore(AltoFunc_Main::StrCamelize($this->_name)), $sNewName);
            if ($sName != $sNewName) {
                unset($aList[$sName]);
            }

            $aFile['target']              = $sTarget;
            $aList[$sNewName]             = $aFile;
            $aList[$sNewName]['callback'] = array($this, 'generatePlugin');
        }

        // Копируем файлы
        $this->copyFiles($aList);
        echo "\nYour plugin has been created successfully under {$path}.\n";
    }

    /*
     * Парсер выражений в исходниках эталонного плагина
     */
    public function generatePlugin($source, $params) {

        $content = file_get_contents($source);
        if (basename($source) == 'plugin.xml') {
            $content = str_replace('<id>example</id>', '<id>' . AltoFunc_Main::StrUnderscore(AltoFunc_Main::StrCamelize($this->_name)) . '</id>', $content);
        }
        $content = str_replace('Example', AltoFunc_Main::StrCamelize($this->_name), $content);
        $content = str_replace('example', strtolower($this->_name), $content);

        return $content;
    }
}
