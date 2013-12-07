<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

/**
 * @package modules.mresource
 * @since   1.0
 */
class ModuleMresource_MapperMresource extends Mapper {

    public function Add($oMresource) {

        $aParams = array(
            ':date_add' => F::Now(),
            ':user_id' => $oMresource->GetUserId(),
            ':link' => $oMresource->IsLink() ? 1 : 0,
            ':type' => $oMresource->GetType(),
            ':path_url' => $oMresource->GetPathUrl(),
            ':path_file' => $oMresource->GetPathFile(),
            ':hash_url' => $oMresource->GetHashUrl(),
            ':hash_file' => $oMresource->GetHashFile(),
            ':storage' => $oMresource->GetStorage(),
            ':uuid' => $oMresource->GetUuid(),
        );
        $sql = "
            SELECT mresource_id
            FROM ?_mresource
            WHERE
                storage = ?:storage AND uuid = ?:uuid
            LIMIT 1
            ";
        $nId = $this->oDb->sqlSelectCell($sql, $aParams);
        if (!$nId) {
            $sql = "
            INSERT INTO ?_mresource
            SET
                date_add = ?:date_add,
                user_id = ?d:user_id,
                link = ?d:link,
                type = ?d:type,
                path_url = ?:path_url,
                path_file = ?:path_file,
                hash_url = ?:hash_url,
                hash_file = ?:hash_file,
                storage = ?:storage,
                uuid = ?:uuid
        ";
            $nId = $this->oDb->sqlQuery($sql, $aParams);
        }
        return $nId ? $nId : false;
    }

    public function AddTargetRel($oMresource) {

        $nId = $this->oDb->sqlQuery(
            "INSERT INTO ?_mresource_target
            SET
                mresource_id = ?d:id,
                target_type = ?:target_type,
                target_id = ?d:target_id,
                date_add = ?:date_add,
                description = ?:description,
                target_tmp = ?:target_tmp,
                incount = ?d:incount
            ON DUPLICATE KEY UPDATE
                incount=incount+?d:incount
            ",
            array(
                 ':id' => $oMresource->GetMresourceId(),
                 ':target_type' => $oMresource->GetTargetType(),
                 ':target_id' => $oMresource->GetTargetId(),
                 ':date_add' => F::Now(),
                 ':description' => $oMresource->GetDescription(),
                 ':target_tmp' => $oMresource->GetTargetTmp(),
                 ':incount' => $oMresource->GetIncount() ? $oMresource->GetIncount() : 1,
            )
        );
        return $nId ? $nId : false;
    }

    protected function _getMresourcesRelByCriteria($aCriteria) {

        $aFilter = (isset($aCriteria['filter']) ? $aCriteria['filter'] : array());
        if (isset($aCriteria['fields'])) {
            if (is_array($aCriteria['filter'])) {
                $sFields = implode(',', $aCriteria['filter']);
            } else {
                $sFields = (string)$aCriteria['filter'];
            }
        } else {
            $sFields = 'mrt.*, mr.*';
        }
        $oSql = $this->oDb->sql("
            SELECT
                id AS ARRAY_KEY,
                $sFields
            FROM ?_mresource_target AS mrt
                INNER JOIN ?_mresource AS mr ON mr.mresource_id=mrt.mresource_id
            WHERE
                1=1
                {AND mrt.id=?d:id}
                {AND mrt.id IN (?a:ids)}
                {AND mrt.mresource_id=(?d:mresource_id)}
                {AND mrt.mresource_id IN (?a:mresource_ids)}
                {AND mrt.target_type=?:target_type}
                {AND mrt.target_id=?d:target_id}
                {AND mr.user_id=(?d:user_id)}
                {AND mr.link=(?d:link)}
                {AND (mr.type & ?d:type)>0}
                {AND mr.hash_url=?:hash_url}
                {AND mr.hash_file=?:hash_file}
        ");
        $aRows = $oSql->bind(
            array(
                 ':id' => (isset($aFilter['id']) && !is_array($aFilter['id'])) ? $aFilter['id'] : DBSIMPLE_SKIP,
                 ':ids' => (isset($aFilter['id']) && is_array($aFilter['id'])) ? $aFilter['id'] : DBSIMPLE_SKIP,
                 ':mresource_id' => (isset($aFilter['mresource_id']) && !is_array($aFilter['mresource_id'])) ? $aFilter['mresource_id'] : DBSIMPLE_SKIP,
                 ':mresource_ids' => (isset($aFilter['mresource_id']) && is_array($aFilter['mresource_id'])) ? $aFilter['mresource_id'] : DBSIMPLE_SKIP,
                 ':target_type' => isset($aFilter['target_type']) ? $aFilter['target_type'] : DBSIMPLE_SKIP,
                 ':target_id' => isset($aFilter['target_id']) ? $aFilter['target_id'] : DBSIMPLE_SKIP,
                 ':user_id' => isset($aFilter['user_id']) ? $aFilter['user_id'] : DBSIMPLE_SKIP,
                 ':link' => isset($aFilter['link']) ? $aFilter['link'] : DBSIMPLE_SKIP,
                 ':type' => isset($aFilter['type']) ? $aFilter['type'] : DBSIMPLE_SKIP,
                 ':hash_url' => isset($aFilter['hash_url']) ? $aFilter['hash_url'] : DBSIMPLE_SKIP,
                 ':hash_file' => isset($aFilter['hash_file']) ? $aFilter['hash_file'] : DBSIMPLE_SKIP,
            )
        )->select();
        return array('data' => $aRows ? $aRows : array());
    }

    public function GetMresourcesByCriteria($aCriteria) {

        $aFilter = (isset($aCriteria['filter']) ? $aCriteria['filter'] : array());
        $aParams = array();
        $aUuidFilter = array();
        if (isset($aFilter['id']) && !isset($aFilter['mresource_id'])) {
            $aFilter['mresource_id'] = $aFilter['id'];
        }
        if (isset($aFilter['storage_uuid'])) {
            if (is_array($aFilter['storage_uuid'])) {
                $nUniqUid = 0;
                foreach ($aFilter['storage_uuid'] as $nCnt => $aStorageUuid) {
                    if ($aStorageUuid['storage']) {
                        $aUuidFilter[] = '(storage=?:storage' . $nCnt . ' AND uuid=?:uuid' . $nCnt . ')';
                        $aParams[':storage' . $nCnt] = $aStorageUuid['storage'];
                        $aParams[':uuid' . $nCnt] = $aStorageUuid['uuid'];
                        $nUniqUid++;
                    } else {
                        $aUuidFilter[] = '(uuid=?:uuid' . $nCnt . ')';
                        $aParams[':uuid' . $nCnt] = $aStorageUuid['uuid'];
                    }
                }
                if (sizeof($aFilter['storage_uuid']) == $nUniqUid && !isset($aCriteria['limit'])) {
                    $aCriteria['limit'] = $nUniqUid;
                }
                unset($aFilter['storage_uuid']);
            }
        }
        if (isset($aFilter['mresource_id']) && !isset($aCriteria['limit'])) {
            if (is_array($aFilter['mresource_id'])) {
                $aCriteria['limit'] = count($aFilter['mresource_id']);
            } else {
                $aCriteria['limit'] = 1;
            }
        }
        list($nOffset, $nLimit) = $this->_prepareLimit($aCriteria);

        // Формируем строку лимита и автосчетчик общего числа записей
        if ($nOffset !== false && $nLimit !== false) {
            $sSqlLimit = 'LIMIT ' . $nOffset . ', ' . $nLimit;
        } elseif ($nLimit != false) {
            $sSqlLimit = 'LIMIT ' . $nLimit;
        } else {
            $sSqlLimit = '';
        }

        if (isset($aCriteria['order'])) {
            $sOrder = $aCriteria['order'];
        } else {
            $sOrder = 'mresource_id DESC';
        }
        if ($sOrder) {
            $sSqlOrder = 'ORDER BY ' . $sOrder;
        }

        $bTargetsCount = false;
        if (isset($aCriteria['fields'])) {
            if (is_array($aCriteria['fields'])) {
                if ($sKey = array_search('targets_count', $aCriteria['fields'])) {
                    $bTargetsCount = true;
                    unset($aCriteria['fields'][$sKey]);
                }
                $sFields = implode(',', $aCriteria['fields']);
            } else {
                $sFields = (string)$aCriteria['fields'];
            }
        } else {
            $sFields = 'mr.*';
        }
        if ($bTargetsCount) {
            $sFields .= ', 0 AS targets_count';
        }

        if ($aUuidFilter) {
            $sUuidFilter = '1=1 AND (' . implode(' OR ', $aUuidFilter) . ')';
        } else {
            $sUuidFilter = '1=1';
        }

        $oSql = $this->oDb->sql("
            SELECT
                mresource_id AS ARRAY_KEY,
                $sFields
            FROM ?_mresource AS mr
            WHERE
                $sUuidFilter
                {AND mr.mresource_id=?d:mresource_id}
                {AND mr.mresource_id IN (?a:mresource_ids)}
                {AND mr.user_id=?d:user_id}
                {AND mr.user_id IN (?a:user_ids)}
                {AND mr.link=?d:link}
                {AND (mr.type & ?d:type)>0}
                {AND mr.hash_url=?:hash_url}
                {AND mr.hash_url IN (?a:hash_url_a)}
                {AND mr.hash_file=?:hash_file}
                {AND mr.hash_file IN (?:hash_file_a)}
            $sSqlOrder
            $sSqlLimit
        ");
        $aParams = array_merge(
            $aParams,
            array(
                 ':mresource_id' => (isset($aFilter['mresource_id']) && !is_array($aFilter['mresource_id'])) ? $aFilter['mresource_id'] : DBSIMPLE_SKIP,
                 ':mresource_ids' => (isset($aFilter['mresource_id']) && is_array($aFilter['mresource_id'])) ? $aFilter['mresource_id'] : DBSIMPLE_SKIP,
                 ':user_id' => (isset($aFilter['user_id']) && !is_array($aFilter['user_id'])) ? $aFilter['user_id'] : DBSIMPLE_SKIP,
                 ':user_ids' => (isset($aFilter['user_id']) && is_array($aFilter['user_id'])) ? $aFilter['user_id'] : DBSIMPLE_SKIP,
                 ':link' => isset($aFilter['link']) ? ($aFilter['link'] ? 1 : 0) : DBSIMPLE_SKIP,
                 ':type' => isset($aFilter['type']) ? $aFilter['type'] : DBSIMPLE_SKIP,
                 ':hash_url' => (isset($aFilter['hash_url']) && !is_array($aFilter['hash_url'])) ? $aFilter['hash_url'] : DBSIMPLE_SKIP,
                 ':hash_url_a' => (isset($aFilter['hash_url']) && is_array($aFilter['hash_url'])) ? $aFilter['hash_url'] : DBSIMPLE_SKIP,
                 ':hash_file' => (isset($aFilter['hash_file']) && !is_array($aFilter['hash_file'])) ? $aFilter['hash_file'] : DBSIMPLE_SKIP,
                 ':hash_file_a' => (isset($aFilter['hash_file']) && is_array($aFilter['hash_file'])) ? $aFilter['hash_file'] : DBSIMPLE_SKIP
            )
        );
        $aRows = $oSql->bind($aParams)->select();

        if ($aRows && $bTargetsCount) {
            $aId = array_keys($aRows);
            $sql = "
                SELECT mresource_id AS ARRAY_KEY, COUNT(*) AS cnt1, SUM(incount) AS cnt2
                FROM ?_mresource_target
                WHERE mresource_id IN (?a)
                GROUP BY mresource_id
            ";
            $aCnt = $this->oDb->select($sql, $aId);
            if ($aCnt) {
                foreach($aCnt as $nId=>$aRow) {
                    if (isset($aRows[$nId])) {
                        $aRows[$nId]['targets_count'] = max($aRow['cnt1'], $aRow['cnt2']);
                    }
                }
            }
        }
        return array(
            'data' => $aRows ? $aRows : array(),
            'total' => -1,
        );
    }

    public function GetMresourcesByFilter($aFilter, $nPage, $nPerPage) {

        $aCriteria = array(
            'filter' => $aFilter,
            'limit'  => array(($nPage - 1) * $nPerPage, $nPerPage),
        );
        $aData = $this->GetMresourcesByCriteria($aCriteria);
        if ($aData['data']) {
            $aData['data'] = Engine::GetEntityRows('Mresource', $aData['data']);
        }
        return $aData;
    }

    public function GetMresourcesIdByUrl($aUrls, $nUserId = null) {

        if (!is_array($aUrls)) {
            $aUrls = array($aUrls);
        } else {
            $aUrls = array_unique($aUrls);
        }
        $aHash = array();
        foreach($aUrls as $sLink) {
            $aHash[] = md5($this->Mresource_NormalizeUrl($sLink));
        }
        return $this->GetMresourcesIdByHashUrl($aHash, $nUserId);
    }

    public function GetMresourcesIdByHashUrl($aHashUrls, $nUserId = null) {

        if (!is_array($aHashUrls)) {
            $aHashUrls = array($aHashUrls);
        }
        $aCritera = array(
            'filter' => array(
                'hash_url' => $aHashUrls,
            ),
            'fields' => 'mr.mresource_id'
        );
        if ($nUserId) {
            $aCritera['filter']['user_id'] = $nUserId;
        }
        $aData = $this->GetMresourcesByCriteria($aCritera);
        if ($aData['data']) {
            return F::Array_Column($aData['data'], 'mresource_id');
        }
        return array();
    }

    public function GetMresourcesByUrl($aUrls, $nUserId = null) {

        if (!is_array($aUrls)) {
            $aUrls = array($aUrls);
        }
        $aUrlHashs = array();
        foreach ($aUrls as $nI => $sUrl) {
            $aUrlHashs = md5($sUrl);
        }
        return $this->GetMresourcesByHashUrl($aUrlHashs, $nUserId);
    }

    public function GetMresourcesByHashUrl($aUrlHashs, $nUserId = null) {

        if (!is_array($aUrlHashs)) {
            $aUrlHashs = array($aUrlHashs);
        }
        $aCritera = array(
            'filter' => array(
                'hash_url' => $aUrlHashs,
            ),
            'fields' => array(
                'mr.*',
                'targets_count',
            ),
        );
        if ($nUserId) {
            $aCritera['filter']['user_id'] = $nUserId;
        }
        $aData = $this->GetMresourcesByCriteria($aCritera);
        $aResult = array();
        if ($aData['data']) {
            foreach($aData['data'] as $nI => $aRow) {
                $aResult[$nI] = Engine::GetEntity('Mresource', $aRow);
            }
        }
        return $aResult;
    }

    public function GetMresourcesById($aId) {

        $aCriteria = array(
            'filter' => array(
                'id' => $aId,
            ),
            'fields' => array(
                'mr.*',
                'targets_count',
            ),
        );

        $aData = $this->GetMresourcesByCriteria($aCriteria);
        $aResult = array();
        if ($aData['data']) {
            $aResult = Engine::GetEntityRows('Mresource', $aData['data']);
        }
        return $aResult;
    }

    /**
     * @param array|string $aStorageUuid
     *
     * @return array|ModuleMresource_EntityMresource
     */
    public function GetMresourcesByUuid($aStorageUuid) {

        $aCriteria = array(
            'filter' => array(
                'storage_uuid' => array(),
            ),
            'fields' => array(
                'mr.*',
                'targets_count',
            ),
        );
        if (!is_array($aStorageUuid)) {
            $aStorageUuid = array($aStorageUuid);
        }
        foreach ($aStorageUuid as $sUuid) {
            if (substr($sUuid, 0, 1) == '[' && ($n = strpos($sUuid, ']'))) {
                $sStorage = substr($sUuid, 1, $n - 1);
                $sUuid = substr($sUuid, $n + 1);
            } else {
                $sStorage = null;
            }
            $aCriteria['filter']['storage_uuid'][] = array('storage' => $sStorage, 'uuid' => $sUuid);
        }

        $aData = $this->GetMresourcesByCriteria($aCriteria);

        return $aData['data'] ? Engine::GetEntityRows('Mresource', $aData['data']) : array();
    }

    /**
     * Returns media resources' relation entities by target
     *
     * @param $aId
     *
     * @return array
     */
    public function GetMresourcesRelById($aId) {

        $aCriteria = array(
            'filter' => array(
                'id' => $aId,
            ),
        );

        $aData = $this->_getMresourcesRelByCriteria($aCriteria);
        $aResult = array();
        if ($aData['data']) {
            foreach($aData['data'] as $nI => $aRow) {
                $aResult[$nI] = Engine::GetEntity('Mresource_MresourceRel', $aRow);
            }
        }
        return $aResult;
    }

    /**
     * Returns media resources' relation entities by target
     *
     * @param $sTargetType
     * @param $nTargetId
     *
     * @return array
     */
    public function GetMresourcesRelByTarget($sTargetType, $nTargetId) {

        $aCriteria = array(
            'filter' => array(
                'target_type' => $sTargetType,
                'target_id' => $nTargetId,
            ),
        );

        $aData = $this->_getMresourcesRelByCriteria($aCriteria);
        $aResult = array();
        if ($aData['data']) {
            foreach($aData['data'] as $nI => $aRow) {
                $aResult[$nI] = Engine::GetEntity('Mresource_MresourceRel', $aRow);
            }
        }
        return $aResult;
    }

    /**
     * Deletes media resources by ID
     *
     * @param $aId
     *
     * @return bool
     */
    public function DeleteMresources($aId) {

        if (is_array($aId)) {
            $aId = $this->_arrayId($aId);
            $nId = 0;
            $nLimit = count($aId);
        } else {
            $nId = intval($aId);
            $aId = array();
            $nLimit = 1;
        }
        if (!count($aId) && !$nId) {
            return;
        }
        $sql = "
            DELETE FROM ?_mresource
            WHERE
                1=1
                {AND mresource_id=?d}
                {AND mresource_id IN (?a)}
            LIMIT ?d
        ";
        $xResult = $this->oDb->query(
            $sql,
            $nId ? $nId : DBSIMPLE_SKIP,
            count($aId) ? $aId : DBSIMPLE_SKIP,
            $nLimit
        );
        return $xResult !== false;
    }

    /**
     * Deletes media resources' relations by rel ID
     *
     * @param $aId
     *
     * @return bool
     */
    public function DeleteMresourcesRel($aId) {

        if (is_array($aId)) {
            $aId = $this->_arrayId($aId);
            $nId = 0;
            $nLimit = count($aId);
        } else {
            $nId = intval($aId);
            $aId = array();
            $nLimit = 1;
        }
        if (!count($aId) && !$nId) {
            return;
        }
        $sql = "
            DELETE FROM ?_mresource_target
            WHERE
                1=1
                {AND id=?d}
                {AND id IN (?a)}
            LIMIT ?d
        ";
        $xResult = $this->oDb->query(
            $sql,
            $nId ? $nId : DBSIMPLE_SKIP,
            count($aId) ? $aId : DBSIMPLE_SKIP,
            $nLimit
        );
        return $xResult !== false;
    }

    /**
     * Deletes media resources' relations by target
     *
     * @param $sTargetType
     * @param $nTargetId
     *
     * @return bool
     */
    public function DeleteTargetRel($sTargetType, $nTargetId) {

        $sql = "
            DELETE FROM ?_mresource_target
            WHERE
                target_type=?
                AND
                target_id=?d
        ";
        $xResult = $this->oDb->query(
            $sql,
            $sTargetType,
            $nTargetId
        );
        return $xResult !== false;
    }

}

// EOF
