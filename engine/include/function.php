<?php
/*-------------------------------------------------------
*
*   LiveStreet Engine Social Networking
*   Copyright © 2008 Mzhelskiy Maxim
*
*--------------------------------------------------------
*
*   Official site: www.livestreet.ru
*   Contact e-mail: rus.engine@gmail.com
*
*   GNU General Public License, version 2:
*   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*
---------------------------------------------------------
*/

/**
 * DEPRECATED FUNCTIONS
 */
function isAjaxRequest() {
    return F::AjaxRequest();
}

function func_check($sValue, $sParam, $iMin = 1, $iMax = 100) {
    return F::CheckVal($sValue, $sParam, $iMin, $iMax);
}

function func_getIp() {
    return F::GetUserIp();
}

function func_header_location($sLocation) {
    F::HttpLocation($sLocation);
}

function func_rmdir($sPath) {
    return F::File_RemoveDir($sPath);
}

function func_array_change_value($array, $sBefore = '', $sAfter = '') {
    return F::Array_ChangeValues($array, $sBefore, $sAfter);
}

function func_array_simpleflip(&$arr, $sDefValue = 1) {
    $arr = F::Array_FlipIntKeys($arr, $sDefValue);
}

function func_build_cache_keys($array, $sBefore = '', $sAfter = '') {
    return F::Array_ChangeValues($array, $sBefore, $sAfter);
}

function func_array_sort_by_keys($array, $aKeys) {
    return F::Array_SortByKeysArray($array, $aKeys);
}

function func_array_merge_assoc($aArr1, $aArr2) {
    return F::Array_Merge($aArr1, $aArr2);
}

function func_underscore($sStr) {
    return F::StrUnderscore($sStr);
}

function func_camelize($sStr) {
    return F::StrCamelize($sStr);
}

function func_list_plugins($bAll = false) {
    return F::GetPluginsList($bAll);
}

function func_stripslashes(&$data) {
    F::StripSlashes($data);
}

function func_htmlspecialchars(&$data) {
    F::HtmlSpecialChars($data);
}

function func_convert_entity_to_array(Entity $oEntity, $aMethods = null, $sPrefix = '') {
    return $oEntity->ToArray($aMethods, $sPrefix);
}

function func_text_words($sText, $iCountWords) {
    return F::CatText($sText, $iCountWords);
}

function getRequest($sName, $default = null, $sType = null) {
    return F::GetRequest($sName, $default, $sType);
}

function getRequestStr($sName, $default = null, $sType = null) {
    return F::GetRequestStr($sName, $default, $sType);
}

function isPost($sName) {
    return F::isPost($sName);
}

function getRequestPost($sName, $default = null) {
    return F::GetPost($sName, $default);
}

function getRequestPostStr($sName, $default = null) {
    return F::GetRequestStr($sName, $default, 'post');
}


/**
 * OLD FUNCTIONS
 */

/**
 * функция вывода отладочных сообщений через "хакерскую" консоль Дмитрия Котерова
 */
function dump($msg) {
    if (Config::Get('sys.logs.hacker_console') && !isAjaxRequest()) {
        if (!class_exists('Debug_HackerConsole_Main')) {
            require_once Config::Get('path.root.server') . "/engine/lib/external/HackerConsole/Main.php";
            new Debug_HackerConsole_Main(true);
        }
        call_user_func(array('Debug_HackerConsole_Main', 'out'), $msg);
    } else {
        //var_dump($msg);
    }
}

/**
 * генерирует случайную последовательность символов
 *
 * @param int $iLength
 * @return string
 */
function func_generator($iLength = 10) {
    if ($iLength > 32) {
        $iLength = 32;
    }
    return F::RandomStr($iLength);
}

/**
 * Шифрование
 *
 * @param int $sData
 * @return string
 */
function func_encrypt($sData) {
    return md5($sData);
}

/**
 * Создаёт каталог по полному пути
 *
 * @param string $sBasePath
 * @param string $sNewDir
 */
function func_mkdir($sBasePath, $sNewDir) {
    $sDirToCheck = rtrim($sBasePath, '/') . '/' . $sNewDir;
    return F::File_CheckDir($sDirToCheck);
}

// EOF