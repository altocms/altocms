<?php

class ModuleUploader_EntityDriverFile extends Entity {

    public function Exists($sFile) {

        return F::File_Exists($sFile);
    }

    public function Store($sFile, $sDestination = null) {

        if (!$sDestination) {
            $oUser = $this->User_GetUserCurrent();
            if ($oUser) {
                return false;
            }
            $sDirUpload = $this->GetUserFileDir($oUser->getId());
            $sDestination = $this->Uniqname($sDirUpload, strtolower(F::File_GetExtension($sFile)));
        }
        if ($sDestination) {
            if ($sStoredFile = $this->Uploader_Move($sFile, $sDestination, true)) {
                return '[file]' . $sStoredFile;
            }
        }
    }

    public function Delete($sFile) {

        if (strpos($sFile, '*')) {
            $bResult = F::File_DeleteAs($sFile);
        } else {
            $bResult = F::File_Delete($sFile);
        }
        if ($bResult) {
            // if folder is empty then remove it
            if (!F::File_ReadDir($sDir = dirname($sFile))) {
                F::File_RemoveDir($sDir);
            }
        }
        return $bResult;
    }

    /**
     * @param string $sFilePath
     *
     * @return string
     */
    public function Dir2Url($sFilePath) {

        return F::File_Dir2Url($sFilePath);
    }

    /**
     * @param string $sUrl
     *
     * @return bool
     */
    public function Url2Dir($sUrl) {

        if (F::File_LocalUrl($sUrl)) {
            return F::File_Url2Dir($sUrl);
        }
    }


}

// EOF