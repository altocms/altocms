<?php

/**
 * Выводит изображение и прикрепляет его ко временному объекту
 *
 * @param $aParams
 * @param Smarty $oSmarty
 * @return string
 */
function smarty_function_img($aParams, &$oSmarty = NULL) {

    // Пропущен тип объекта
    if (!isset($aParams['attr']['target-type'])) {
        trigger_error("img: missing 'target-type' parameter", E_USER_WARNING);

        return '';
    }

    // Пропущен идентификатор объекта
    if (!isset($aParams['attr']['target-id'])) {
        trigger_error("img: missing 'target-id' parameter", E_USER_WARNING);

        return '';
    }


    // Получим тип объекта
    $sTargetType = $aParams['attr']['target-type'];
    unset($aParams['attr']['target-type']);

    // Получим ид объекта
    $sTargetId = $aParams['attr']['target-id'];
    unset($aParams['attr']['target-id']);

    // Получим ид объекта
    $sCrop = isset($aParams['attr']['crop']) ? $aParams['attr']['crop'] : FALSE;
    unset($aParams['attr']['crop']);


    // Получим изображение по временному ключу, или создадим этот ключ
    if (($sTargetTmp = E::Session_GetCookie('uploader_target_tmp')) && E::IsUser()) {

        // Продлим куку
        E::Session_SetCookie('uploader_target_tmp', $sTargetTmp, 'P1D', FALSE);
        // Получим предыдущее изображение и если оно было, установим в качестве текущего
        // Получим и удалим все ресурсы
        $aMresourceRel = E::Mresource_GetMresourcesRelByTargetAndUser($sTargetType, $sTargetId, E::UserId());
        if ($aMresourceRel) {
            /** @var ModuleMresource_EntityMresource $oResource */
            $oMresource = array_shift($aMresourceRel);
            if ($oMresource) {
                if ($sCrop) {
                    $aParams['attr']['src'] = E::Uploader_ResizeTargetImage($oMresource->GetUrl(), $sCrop);
                } else {
                    $aParams['attr']['src'] = $oMresource->GetUrl();
                }
                $oSmarty->assign("bImageIsTemporary", TRUE);
            }
        }

    } else {

        // Куки нет, это значит, что пользователь первый раз создает этот тип
        // и старой картинки просто нет
        if ($sTargetId == '0') {
            E::Session_SetCookie('uploader_target_tmp', F::RandomStr(), 'P1D', FALSE);
        } else {
            E::Session_DelCookie('uploader_target_tmp');
            $sImage = E::Uploader_GetTargetImageUrl($sTargetId, $sTargetType, $sCrop);
            if ($sImage) {
                $aParams['attr']['src'] = $sImage;
                $oSmarty->assign("bImageIsTemporary", TRUE);
            }
        }

    }


    // Формируем строку атрибутов изображения
    $sAttr = '';
    if (isset($aParams['attr']) && is_array($aParams['attr'])) {
        foreach ($aParams['attr'] as $sAttrName => $sAttrValue) {
            $sAttr .= ' ' . $sAttrName . '="' . $sAttrValue . '"';
        }
    }


    // Сформируем тег изображения
    $sImageTag = '<img ' . $sAttr . '/>';


    return $sImageTag;

}