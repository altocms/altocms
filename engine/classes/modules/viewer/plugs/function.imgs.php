<?php

/**
 * Выводит изображение и прикрепляет его ко временному объекту
 *
 * @param $aParams
 * @param Smarty $oSmarty
 * @return string
 */
function smarty_function_imgs($aParams, &$oSmarty = NULL) {

    // Пропущен тип объекта
    if (!isset($aParams['target-type'])) {
        trigger_error("img: missing 'target-type' parameter", E_USER_WARNING);

        return '';
    }

    // Пропущен идентификатор объекта
    if (!isset($aParams['target-id'])) {
        trigger_error("img: missing 'target-id' parameter", E_USER_WARNING);

        return '';
    }


    // Получим тип объекта
    $sTargetType = $aParams['target-type'];
    unset($aParams['target-type']);

    // Получим ид объекта
    $iTargetId = intval($aParams['target-id']);
    unset($aParams['target-id']);

    // Получим параметры обрезки объекта
    $sCrop = isset($aParams['crop']) ? $aParams['crop'] : FALSE;
    unset($aParams['crop']);

    // Получим ид объекта
    $sTemplate = isset($aParams['template']) ? $aParams['template'] : FALSE;
    unset($aParams['template']);


    // Получим изображение по временному ключу, или создадим этот ключ
    $aParams['src'] = array();
    if (($sTargetTmp = E::ModuleSession()->GetCookie(ModuleUploader::COOKIE_TARGET_TMP)) && E::IsUser()) {
        // Продлим куку
        E::ModuleSession()->SetCookie(ModuleUploader::COOKIE_TARGET_TMP, $sTargetTmp, 'P1D', FALSE);
    } else {
        // Куки нет, это значит, что пользователь первый раз создает этот тип
        // и старой картинки просто нет
        if ($iTargetId == '0') {
            E::ModuleSession()->SetCookie(ModuleUploader::COOKIE_TARGET_TMP, F::RandomStr(), 'P1D', FALSE);
        } else {
            E::ModuleSession()->DelCookie(ModuleUploader::COOKIE_TARGET_TMP);
        }
    }

    // Получим предыдущее изображение и если оно было, установим в качестве текущего
    // Получим и удалим все ресурсы
    $aMresourceRel = E::ModuleMresource()->GetMresourcesRelByTargetAndUser($sTargetType, $iTargetId, E::UserId());
    if ($aMresourceRel && is_array($aMresourceRel)) {
        /** @var ModuleMresource_EntityMresource $oResource */
        foreach ($aMresourceRel as $oMresource) {
            if ($sCrop) {
                $aParams['src'][$oMresource->getMresourceId()] = array(
                    'url' => E::ModuleUploader()->ResizeTargetImage($oMresource->GetUrl(), $sCrop),
                    'cover' => $oMresource->IsCover()
                );
            } else {
                $aParams['src'][$oMresource->getMresourceId()] = array(
                    'url' => $oMresource->GetUrl(),
                    'cover' => $oMresource->IsCover()
                );
            }
            $oSmarty->assign("bHasImage", TRUE);
        }
    }

    // Создадим массив картинок
    $sItems = '';
    if ($aParams['src']) {
        foreach ($aParams['src'] as $sID => $aData) {
            $sItems .= str_replace(
                array('ID', 'uploader_item_SRC', 'PHOTOSET-IS-COVER'),
                array($sID, $aData['url'], $aData['cover'] ? 'photoset-is-cover' : ''),
                $sTemplate
            );
        }
    }

    return $sItems;

}

// EOF