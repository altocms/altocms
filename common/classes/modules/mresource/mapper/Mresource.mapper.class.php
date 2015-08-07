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

    /**
     * @param ModuleMresource_EntityMresource $oMresource
     *
     * @return int|bool
     */
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
            (
                date_add,
                user_id,
                link,
                type,
                path_url,
                path_file,
                hash_url,
                hash_file,
                storage,
                uuid
            )
            VALUES (
                ?:date_add,
                ?d:user_id,
                ?d:link,
                ?d:type,
                ?:path_url,
                ?:path_file,
                ?:hash_url,
                ?:hash_file,
                ?:storage,
                ?:uuid
            )
            ";
            $nId = $this->oDb->sqlQuery($sql, $aParams);
        }
        return $nId ? $nId : false;
    }

    /**
     * @param string $sTargetTmp
     * @param int    $iTargetId
     *
     * @return bool
     */
    public function ResetTmpRelById($sTargetTmp, $iTargetId) {

        return $this->oDb->query(
          'UPDATE ?_mresource_target SET target_tmp = null, target_id = ?d  where target_tmp = ?',
          $iTargetId,
          $sTargetTmp
        ) !== false;
    }

    /**
     * @param ModuleMresource_EntityMresource $oMresource
     *
     * @return bool
     */
    public function AddTargetRel($oMresource) {

        $aParams = array(
            ':id' => $oMresource->GetMresourceId(),
            ':target_type' => $oMresource->GetTargetType(),
            ':target_id' => $oMresource->GetTargetId(),
            ':date_add' => F::Now(),
            ':description' => $oMresource->GetDescription(),
            ':target_tmp' => $oMresource->GetTargetTmp(),
            ':incount' => $oMresource->GetIncount() ? $oMresource->GetIncount() : 1,
        );
        $sql = "
            SELECT mresource_id
            FROM ?_mresource_target
            WHERE
                target_type = ?:target_type
                AND target_id = ?d:target_id
                AND mresource_id = ?d:id
            LIMIT 1
        ";
        if ($iId = $this->oDb->sqlSelectCell($sql, $aParams)) {
            $sql = "
                UPDATE ?_mresource_target
                SET incount=incount+?d:incount
                WHERE mresource_id = ?d:id
            ";
            if ($this->oDb->sqlQuery($sql, $aParams)) {
                return $iId;
            }
        } else {
            $sql = "
                INSERT INTO ?_mresource_target
                (
                    mresource_id,
                    target_type,
                    target_id,
                    date_add,
                    description,
                    target_tmp,
                    incount
                )
                VALUES (
                    ?d:id,
                    ?:target_type,
                    ?d:target_id,
                    ?:date_add,
                    ?:description,
                    ?:target_tmp,
                    ?d:incount
                )
            ";
            if ($iId = $this->oDb->sqlQuery($sql, $aParams)) {
                return $iId ? $iId : false;
            }
        }
        return false;
    }

    /**
     * @param array $aCriteria
     *
     * @return array
     */
    protected function _getMresourcesRelByCriteria($aCriteria) {

        $aFilter = (isset($aCriteria['filter']) ? $aCriteria['filter'] : array());
        if (isset($aCriteria['fields'])) {
            if (is_array($aCriteria['fields'])) {
                $sFields = implode(',', $aCriteria['fields']);
            } else {
                $sFields = (string)$aCriteria['fields'];
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
                {AND mrt.target_type IN (?a:target_types)}
                {AND mrt.target_id=?d:target_id}
                {AND mrt.target_id IN (?a:target_ids)}
                {AND mr.user_id=?d:user_id}
                {AND mr.user_id IN (?a:user_ids)}
                {AND mr.link=(?d:link)}
                {AND (mr.type & ?d:type)>0}
                {AND mr.hash_url=?:hash_url}
                {AND mr.hash_file=?:hash_file}
                {AND mrt.target_tmp=?:target_tmp}
            ORDER BY mr.sort DESC, mr.mresource_id ASC
        ");
        $aParams = array(
                ':id' => (isset($aFilter['id']) && !is_array($aFilter['id'])) ? $aFilter['id'] : DBSIMPLE_SKIP,
                ':ids' => (isset($aFilter['id']) && is_array($aFilter['id'])) ? $aFilter['id'] : DBSIMPLE_SKIP,
                ':mresource_id' => (isset($aFilter['mresource_id']) && !is_array($aFilter['mresource_id'])) ? $aFilter['mresource_id'] : DBSIMPLE_SKIP,
                ':mresource_ids' => (isset($aFilter['mresource_id']) && is_array($aFilter['mresource_id'])) ? $aFilter['mresource_id'] : DBSIMPLE_SKIP,
                ':target_type' => (isset($aFilter['target_type']) && !is_array($aFilter['target_type'])) ? $aFilter['target_type'] : DBSIMPLE_SKIP,
                ':target_types' => (isset($aFilter['target_type']) && is_array($aFilter['target_type'])) ? $aFilter['target_type'] : DBSIMPLE_SKIP,
                ':target_id' => (isset($aFilter['target_id']) && !is_array($aFilter['target_id'])) ? $aFilter['target_id'] : DBSIMPLE_SKIP,
                ':target_ids' => (isset($aFilter['target_id']) && is_array($aFilter['target_id'])) ? $aFilter['target_id'] : DBSIMPLE_SKIP,
                ':user_id' => (isset($aFilter['user_id']) && !is_array($aFilter['user_id'])) ? $aFilter['user_id'] : DBSIMPLE_SKIP,
                ':user_ids' => (isset($aFilter['user_id']) && is_array($aFilter['user_id'])) ? $aFilter['user_id'] : DBSIMPLE_SKIP,
                ':link' => isset($aFilter['link']) ? $aFilter['link'] : DBSIMPLE_SKIP,
                ':type' => isset($aFilter['type']) ? $aFilter['type'] : DBSIMPLE_SKIP,
                ':hash_url' => isset($aFilter['hash_url']) ? $aFilter['hash_url'] : DBSIMPLE_SKIP,
                ':hash_file' => isset($aFilter['hash_file']) ? $aFilter['hash_file'] : DBSIMPLE_SKIP,
                ':target_tmp' => isset($aFilter['target_tmp']) ? $aFilter['target_tmp'] : DBSIMPLE_SKIP,
            );
        $aRows = $oSql->bind($aParams)->select();
        return array('data' => $aRows ? $aRows : array());
    }

    /**
     * @param array $aCriteria
     *
     * @return array
     */
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
        if (isset($aFilter['mresource_target_tmp'])) {
            $aUuidFilter[] = '(target_tmp=?:target_tmp)';
            $aParams[':uuid'] = $aFilter['mresource_target_tmp'];
        }
        if (isset($aFilter['target_type'])) {
            if (!is_array($aFilter['target_type'])) {
                $aFilter['target_type'] = array($aFilter['target_type']);
            }
            $aUuidFilter[] = '(target_type IN (?a:target_type))';
            $aParams[':target_type'] = $aFilter['target_type'];
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
            $sOrder = 'sort DESC, mresource_id ASC';
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

        if (!isset($aFilter['target_type'])) {
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
        } else
        $oSql = $this->oDb->sql("
            SELECT
                mr.mresource_id AS ARRAY_KEY,
                mrt.target_type,
                mrt.target_id,
                $sFields
            FROM ?_mresource AS mr, ?_mresource_target as mrt
            WHERE
                $sUuidFilter
                AND mrt.mresource_id = mr.mresource_id
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
                {AND mrt.target_type IN (?a:target_type)}
                {AND mrt.target_id = ?d:target_id}
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
                 ':hash_file_a' => (isset($aFilter['hash_file']) && is_array($aFilter['hash_file'])) ? $aFilter['hash_file'] : DBSIMPLE_SKIP,
                 ':target_type' => isset($aFilter['target_type']) ? $aFilter['target_type'] : DBSIMPLE_SKIP,
                 ':target_id' => (isset($aFilter['target_id']) && !is_array($aFilter['target_id'])) ? $aFilter['target_id'] : DBSIMPLE_SKIP
            )
        );
        $aRows = $oSql->bind($aParams)->select();

        if ($aRows && $bTargetsCount) {
            $aId = array_keys($aRows);
            $sql = "
                SELECT
                  mresource_id AS ARRAY_KEY,
                  COUNT(*) AS cnt1,
                  SUM(incount) AS cnt2
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

    /**
     * @param $aFilter
     * @param $nPage
     * @param $nPerPage
     *
     * @return array
     */
    public function GetMresourcesByFilter($aFilter, $nPage, $nPerPage) {

        $aCriteria = array(
            'filter' => $aFilter,
            'limit'  => array(($nPage - 1) * $nPerPage, $nPerPage),
        );
        $aData = $this->GetMresourcesByCriteria($aCriteria);
        if ($aData['data']) {
            $aData['data'] = E::GetEntityRows('Mresource', $aData['data']);
        }
        return $aData;
    }

    /**
     * @param string[] $aUrls
     * @param int|null $nUserId
     *
     * @return array
     */
    public function GetMresourcesIdByUrl($aUrls, $nUserId = null) {

        if (!is_array($aUrls)) {
            $aUrls = array($aUrls);
        } else {
            $aUrls = array_unique($aUrls);
        }
        $aHash = array();
        foreach($aUrls as $sLink) {
            $aHash[] = md5(E::ModuleMresource()->NormalizeUrl($sLink));
        }
        return $this->GetMresourcesIdByHashUrl($aHash, $nUserId);
    }

    /**
     * @param string[] $aHashUrls
     * @param int|null $nUserId
     *
     * @return array
     */
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

    /**
     * @param string[] $aUrls
     * @param int|null $nUserId
     *
     * @return array
     */
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

    /**
     * @param string[] $aUrlHashs
     * @param int|null $nUserId
     *
     * @return array
     */
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
            $aResult = E::GetEntityRows('Mresource', $aData['data']);
        }
        return $aResult;
    }

    /**
     * @param int[] $aId
     *
     * @return ModuleMresource_EntityMresource[]
     */
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
            $aResult = E::GetEntityRows('Mresource', $aData['data']);
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

        return $aData['data'] ? E::GetEntityRows('Mresource', $aData['data']) : array();
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
            $aResult = E::GetEntityRows('Mresource_MresourceRel', $aData['data']);
        }
        return $aResult;
    }

    /**
     * Returns media resources' relation entities by target
     *
     * @param string    $sTargetType
     * @param int|array $xTargetId
     *
     * @return ModuleMresource_EntityMresourceRel[]
     */
    public function GetMresourcesRelByTarget($sTargetType, $xTargetId) {

        $aCriteria = array(
            'filter' => array(
                'target_type' => $sTargetType,
                'target_id' => $xTargetId,
            ),
        );

        $aData = $this->_getMresourcesRelByCriteria($aCriteria);
        $aResult = array();
        if ($aData['data']) {
            $aResult = E::GetEntityRows('Mresource_MresourceRel', $aData['data']);
        }
        return $aResult;
    }

    /**
     * Returns media resources' relation entities by target
     *
     * @param string|array  $xTargetType
     * @param int|array $xTargetId
     * @param int|array $xUserId
     *
     * @return ModuleMresource_EntityMresourceRel[]
     */
    public function GetMresourcesRelByTargetAndUser($xTargetType, $xTargetId, $xUserId) {

        if (is_array($xTargetType)) {
            $aCriteria = array(
                'filter' => array(
                    'target_types' => $xTargetType,
                ),
            );
        } else {
            $aCriteria = array(
                'filter' => array(
                    'target_type' => $xTargetType,
                ),
            );
        }

        if (!is_null($xTargetId)) {
            $aCriteria['filter']['target_id'] = $xTargetId;
        }
        if (!is_null($xUserId)) {
            $aCriteria['filter']['user_id'] = $xUserId;
        }
        $aData = $this->_getMresourcesRelByCriteria($aCriteria);
        $aResult = array();
        if ($aData['data']) {
            $aResult = E::GetEntityRows('Mresource_MresourceRel', $aData['data']);
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
     * @param string $sTargetType
     * @param int    $iTargetId
     *
     * @return bool
     */
    public function DeleteTargetRel($sTargetType, $iTargetId) {

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
            $iTargetId
        );
        return $xResult !== false;
    }

    /**
     * Получает все типы целей
     *
     * @return string[]
     */
    public function GetTargetTypes() {

        return $this->oDb->selectCol("select DISTINCT target_type from ?_mresource_target");
    }

    /**
     * Получает количество ресурсов по типу
     *
     * @param string $sTargetType
     *
     * @return int
     */
    public function GetMresourcesCountByTarget($sTargetType) {

        if ($sTargetType == 'all') {
            $aRow =  $this->oDb->selectRow("SELECT COUNT(*) AS count FROM ?_mresource");
        } else {
            if (!is_array($sTargetType)) {
                $sTargetType = array($sTargetType);
            }
            $aRow =  $this->oDb->selectRow("
              SELECT
                COUNT(*) AS count
              FROM ?_mresource_target t, ?_mresource m
                WHERE
              m.mresource_id = t.mresource_id
              AND t.target_type IN ( ?a )", $sTargetType);
        }


        return isset($aRow['count'])?$aRow['count']:0;
    }

    /**
     * Получает количество ресурсов по типу и пользователю
     *
     * @param string $sTargetType
     * @param int    $iUserId
     *
     * @return int
     */
    public function GetMresourcesCountByTargetAndUserId($sTargetType, $iUserId) {

        if ($sTargetType == 'all') {
            $aRow =  $this->oDb->selectRow("select count(t.target_type) as count from ?_mresource_target t, ?_mresource m  where m.user_id = ?d and m.mresource_id = t.mresource_id", $iUserId);
        } else {
            if (!is_array($sTargetType)) {
                $sTargetType = array($sTargetType);
            }
            $aRow =  $this->oDb->selectRow("select count(t.target_type) as count from ?_mresource_target t, ?_mresource m  where m.user_id = ?d and m.mresource_id = t.mresource_id and t.target_type in ( ?a )", $iUserId, $sTargetType);
        }


        return isset($aRow['count'])?$aRow['count']:0;
    }

    /**
     * Получает количество ресурсов по типу и ид.
     *
     * @param string $sTargetType
     * @param int    $iTargetId
     * @param int    $iUserId
     *
     * @return int
     */
    public function GetMresourcesCountByTargetIdAndUserId($sTargetType, $iTargetId, $iUserId){

        $sql = "select
                  count(t.target_type) as count
                from
                  ?_mresource_target t, ?_mresource m
                where
                  m.user_id = ?d
                  and m.mresource_id = t.mresource_id
                  and t.target_id = ?d
                  and t.target_type = ?";

        $aRow =  $this->oDb->selectRow($sql, $iUserId, $iTargetId, $sTargetType);


        return isset($aRow['count'])?$aRow['count']:0;
    }

    /**
     * Обновляет параметры ресурса
     *
     * @param ModuleMresource_EntityMresource $oResource
     *
     * @return bool
     */
    public function UpdateParams($oResource){

        $sql = "UPDATE ?_mresource SET params = ? WHERE mresource_id = ?d";
        return $this->oDb->query($sql, $oResource->getParams(), $oResource->getMresourceId());

    }

    /**
     * @param ModuleMresource_EntityMresource $oResource
     * @return mixed
     */
    public function UpdateMresouceUrl($oResource){
        $sql = "UPDATE ?_mresource SET
                  uuid = ?,
                  path_url = ?,
                  hash_url = ?,
                  path_file = ?,
                  hash_file = ?
                WHERE mresource_id = ?d";
        return $this->oDb->query($sql,
            $oResource->getUuid(),
            $oResource->getPathUrl(),
            $oResource->getHashUrl(),
            $oResource->getPathFile(),
            $oResource->getHashFile(),
            $oResource->getMresourceId()
        );
    }

    /**
     * Обновляет тип ресурса
     *
     * @param ModuleMresource_EntityMresource $oResource
     *
     * @return bool
     */
    public function UpdateType($oResource){

        $sql = "UPDATE ?_mresource SET type = ?d WHERE mresource_id = ?d";
        return $this->oDb->query($sql, $oResource->getType(), $oResource->getMresourceId());

    }

    /**
     * Устанавливает главное изображение фотосета
     *
     * @param ModuleMresource_EntityMresource $oResource
     * @param string                          $sTargetType
     * @param int                             $iTargetId
     *
     * @return bool
     */
    public function UpdatePrimary($oResource, $sTargetType, $iTargetId){

        $sql = "UPDATE ?_mresource SET type = ?d WHERE mresource_id IN (
          SELECT mresource_id FROM ?_mresource_target WHERE target_type = ? AND target_id = ?d
        )";
        $bResult = $this->oDb->query($sql, ModuleMresource::TYPE_PHOTO, $sTargetType, $iTargetId);

        $bResult = ($bResult !== false && $this->UpdateType($oResource));

        return $bResult;
    }

    /**
     * Устанавливает новый порядок сортировки изображений
     *
     * @param $aOrder
     * @param $sTargetType
     * @param $iTargetId
     *
     * @return mixed
     */
    public function UpdateSort($aOrder, $sTargetType, $iTargetId) {

        $sData = '';
        foreach ($aOrder as $sId => $iSort) {
            $sData .= " WHEN mresource_id = '$sId' THEN '$iSort' ";
        }

        $sql ="UPDATE ?_mresource SET sort = (CASE $sData END) WHERE
                mresource_id
              IN (
                SELECT
                  mresource_id
                FROM
                  ?_mresource_target
                WHERE
                  target_type = ? AND target_id = ?d)";


        return $this->oDb->query($sql, $sTargetType, $iTargetId);

    }

    /**
     * Возвращает категории изображения для пользователя
     *
     * @param int $iUserId
     * @param int $sTopicId
     *
     * @return mixed
     */
    public function GetImageCategoriesByUserId($iUserId, $sTopicId){

        $sql = "SELECT
                  IF(
                    ISNULL(t.target_tmp),
                    IF((t.target_type LIKE 'topic%' AND t.target_id = ?d), 'current',
                      IF(t.target_type = 'profile_avatar' OR t.target_type = 'profile_photo', 'user', t.target_type)),
                    'tmp') AS ttype
                  , count(t.target_id) AS count
                FROM
                  ?_mresource_target as t, ?_mresource as m
                WHERE
                  t.mresource_id = m.mresource_id
                  AND m.user_id = ?d
                  AND t.target_type IN ( ?a )

                GROUP  BY
                  ttype                ORDER BY
                 m.date_add desc";

        return $this->oDb->select($sql, (int)$sTopicId, $iUserId, array(
            'current',
            'tmp',
            'blog_avatar',
            'profile_avatar',
            'profile_photo'
        ));

    }

    /**
     * Возвращает категории изображения для пользователя
     * @param $iUserId
     *
     * @return mixed
     */
    public function GetAllImageCategoriesByUserId($iUserId){

        $sql = "SELECT
                  IF(
                    ISNULL(t.target_tmp),
                    IF((t.target_type LIKE 'topic%'), 'topic',
                      IF(t.target_type = 'profile_avatar' OR t.target_type = 'profile_photo', 'user', t.target_type)),
                    'tmp') AS ttype
                  , count(t.target_id) AS count
                FROM
                  ?_mresource_target as t, ?_mresource as m
                WHERE
                  t.mresource_id = m.mresource_id
                  AND (m.type & (?d | ?d | ?d))
                  AND m.user_id = ?d

                GROUP  BY
                  ttype                ORDER BY
                 m.date_add desc";

        return $this->oDb->select($sql,
            ModuleMresource::TYPE_IMAGE,
            ModuleMresource::TYPE_PHOTO,
            ModuleMresource::TYPE_PHOTO_PRIMARY,
            $iUserId);

    }

    /**
     * Возвращает категории изображения для пользователя
     * @param $iUserId
     *
     * @return mixed
     */
    public function GetCountImagesByUserId($iUserId){

        $sql = "SELECT
                  count(mresource_id) AS count
                FROM
                  ?_mresource
                WHERE
                  user_id = ?d
                  AND (type & (?d | ?d | ?d))";

        if ($aRow = $this->oDb->selectRow($sql,
            $iUserId,
            ModuleMresource::TYPE_IMAGE,
            ModuleMresource::TYPE_PHOTO,
            ModuleMresource::TYPE_PHOTO_PRIMARY)) {
            return intval($aRow['count']);
        }
        return 0;

    }

    /**
     * @param $iUserId
     * @param $sTopicId
     *
     * @return mixed
     */
    public function GetCurrentTopicResourcesId($iUserId, $sTopicId) {

        $sql = "select r.mresource_id FROM
                  (SELECT
                  t.mresource_id

                FROM ?_mresource_target AS t, ?_mresource AS m
                WHERE t.mresource_id = m.mresource_id
                      AND (m.type & (?d | ?d | ?d))
                      AND m.user_id = ?d
                      AND ({1 = ?d AND t.target_tmp IS NOT NULL}{1 = ?d AND t.target_tmp IS NULL} AND ((t.target_type in ( ?a ) || t.target_type LIKE 'single-image-uploader%')  AND t.target_id = ?d))

                GROUP BY t.mresource_id  ORDER BY
                 m.date_add desc) as r";
        $aData = $this->oDb->selectCol($sql,
            ModuleMresource::TYPE_IMAGE,
            ModuleMresource::TYPE_PHOTO,
            ModuleMresource::TYPE_PHOTO_PRIMARY,
            $iUserId,
            $sTopicId == FALSE ? 1 : DBSIMPLE_SKIP,
            $sTopicId != FALSE ? 1 : DBSIMPLE_SKIP,
            array(
            'photoset',
            'topic'
        ), (int)$sTopicId);

        return $aData;
    }

    /**
     * Получает ид. топиков с картинками
     *
     * @param array $aFilter
     * @param int $iCount
     * @param int $iCurrPage
     * @param int $iPerPage
     *
     * @return array
     */
    public function GetTopicInfo($aFilter, &$iCount, $iCurrPage, $iPerPage) {

        $sql = "SELECT
                  COUNT(DISTINCT t.topic_id) AS cnt
                FROM
                  ?_mresource m
                  LEFT JOIN ?_mresource_target mt ON m.mresource_id = mt.mresource_id
                  LEFT JOIN ?_topic t ON t.topic_id = mt.target_id
                  LEFT JOIN ?_blog b ON b.blog_id = t.blog_id
                WHERE
                  (m.user_id = ?d)
                  {AND (m.type & ?d)}
                  AND (mt.target_id <> 0)
                  {AND (mt.target_type IN (?a) OR mt.target_type LIKE 'single-image-uploader%')}
                  {AND (t.topic_publish = ?d) AND (t.topic_date_show <= NOW())}
                  {AND t.topic_index_ignore = ?d}
                  {AND (t.topic_type = ?)}
                  {AND t.topic_type IN (?a)}
                  {AND b.blog_type = 'personal' OR t.blog_id IN ( ?a )}
                ";
        $iCount = $this->oDb->selectCell($sql,
            $aFilter['user_id'],
            isset($aFilter['mresource_type']) ? $aFilter['mresource_type'] : DBSIMPLE_SKIP,
            isset($aFilter['target_type']) ? $aFilter['target_type'] : DBSIMPLE_SKIP,
            isset($aFilter['topic_publish']) ? $aFilter['topic_publish'] : DBSIMPLE_SKIP,
            isset($aFilter['topic_index_ignore']) ? $aFilter['topic_index_ignore'] : DBSIMPLE_SKIP,
            (isset($aFilter['topic_type']) && !is_array($aFilter['topic_type'])) ? $aFilter['topic_type'] : DBSIMPLE_SKIP,
            (isset($aFilter['topic_type']) && is_array($aFilter['topic_type'])) ? $aFilter['topic_type'] : DBSIMPLE_SKIP,
            isset($aFilter['blog_id']) ? $aFilter['blog_id'] : DBSIMPLE_SKIP
        );

        $sql = "SELECT
                  t.topic_id AS id,
                  count(DISTINCT m.mresource_id) AS count
                FROM
                  ?_mresource m
                  LEFT JOIN ?_mresource_target mt ON m.mresource_id = mt.mresource_id
                  LEFT JOIN ?_topic t ON t.topic_id = mt.target_id
                  LEFT JOIN ?_blog b ON b.blog_id = t.blog_id
                WHERE
                  (m.user_id = ?d)
                  {AND (m.type & ?d)}
                  AND (mt.target_id <> 0)
                  {AND (mt.target_type IN (?a) OR mt.target_type LIKE 'single-image-uploader%')}
                  {AND (t.topic_publish = ?d) AND (t.topic_date_show <= NOW())}
                  {AND t.topic_index_ignore = ?d}
                  {AND (t.topic_type = ?)}
                  {AND t.topic_type IN (?a)}
                  {AND b.blog_type = 'personal' OR t.blog_id IN ( ?a )}
                GROUP BY t.topic_id
                LIMIT ?d, ?d
                ";
        $aRows = $this->oDb->select($sql,
            $aFilter['user_id'],
            isset($aFilter['mresource_type']) ? $aFilter['mresource_type'] : DBSIMPLE_SKIP,
            isset($aFilter['target_type']) ? $aFilter['target_type'] : DBSIMPLE_SKIP,
            isset($aFilter['topic_publish']) ? $aFilter['topic_publish'] : DBSIMPLE_SKIP,
            isset($aFilter['topic_index_ignore']) ? $aFilter['topic_index_ignore'] : DBSIMPLE_SKIP,
            (isset($aFilter['topic_type']) && !is_array($aFilter['topic_type'])) ? $aFilter['topic_type'] : DBSIMPLE_SKIP,
            (isset($aFilter['topic_type']) && is_array($aFilter['topic_type'])) ? $aFilter['topic_type'] : DBSIMPLE_SKIP,
            isset($aFilter['blog_id']) ? $aFilter['blog_id'] : DBSIMPLE_SKIP,
            ($iCurrPage - 1) * $iPerPage, $iPerPage
        );

        $aResult = array();
        if ($aRows) {
            foreach ($aRows as $aRow) {
                $aResult[$aRow['id']] = $aRow['count'];
            }
        }
        return $aResult;
    }

    public function GetCountImagesByTopicType($aFilter) {

        $sql = "SELECT
                  t.topic_type AS id,
                  count(DISTINCT m.mresource_id) AS count
                FROM
                  ?_mresource m
                  LEFT JOIN ?_mresource_target mt ON m.mresource_id = mt.mresource_id
                  LEFT JOIN ?_topic t ON t.topic_id = mt.target_id
                  LEFT JOIN ?_blog b ON b.blog_id = t.blog_id
                WHERE
                  (m.user_id = ?d)
                  {AND (m.type & ?d)}
                  AND (mt.target_id <> 0)
                  {AND (mt.target_type IN (?a) OR mt.target_type LIKE 'single-image-uploader%')}
                  {AND (t.topic_publish = ?d) AND (t.topic_date_show <= NOW())}
                  {AND b.blog_type = 'personal' OR t.blog_id IN ( ?a )}
                  {AND t.topic_index_ignore = ?d}
                GROUP BY t.topic_type
                ";
        $aRows = $this->oDb->select($sql,
            $aFilter['user_id'],
            isset($aFilter['mresource_type']) ? $aFilter['mresource_type'] : DBSIMPLE_SKIP,
            isset($aFilter['target_type']) ? $aFilter['target_type'] : DBSIMPLE_SKIP,
            isset($aFilter['topic_publish']) ? $aFilter['topic_publish'] : DBSIMPLE_SKIP,
            isset($aFilter['blog_id']) ? $aFilter['blog_id'] : DBSIMPLE_SKIP,
            isset($aFilter['topic_index_ignore']) ? $aFilter['topic_index_ignore'] : DBSIMPLE_SKIP
        );
        if ($aRows) {
            return $aRows;
        }
        return array();
    }

    /**
     * Получает ид. писем пользователя
     *
     * @param int $iUserId
     * @param int $iCount
     * @param int $iCurrPage
     * @param int $iPerPage
     *
     * @return array
     */
    public function GetTalkInfo($iUserId, &$iCount, $iCurrPage, $iPerPage) {

        $sql = "SELECT
                  t.target_id        AS talk_id,
                  count(t.target_id) AS count
                FROM ?_mresource_target t, ?_mresource m
                WHERE
                  m.mresource_id = t.mresource_id
                  AND m.user_id = ?d
                  AND t.target_type IN ( ?a )
                GROUP BY talk_id
                ORDER BY m.date_add desc
                LIMIT ?d, ?d";

        $aResult = array();

        if ($aRows = $this->oDb->selectPage($iCount, $sql, $iUserId, array('talk'), ($iCurrPage - 1) * $iPerPage, $iPerPage)) {
            foreach ($aRows as $aRow) {
                $aResult[$aRow['talk_id']] = $aRow['count'];
            }
        }

        return $aResult;
    }

}

// EOF
