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
 * Маппер для работы с БД админпанели
 *
 * @package modules.admin
 * @since 1.0
 */
class ModuleAdmin_MapperAdmin extends Mapper {

    /**
     * Returns site statistics
     *
     * @return array
     */
    public function GetSiteStat() {

        $aResult = array();
        $sql = "SELECT Count(*) FROM ?_user WHERE user_activate>0";
        $aResult['users'] = $this->oDb->selectCell($sql);
        $sql = "SELECT Count(*) FROM ?_blog";
        $aResult['blogs'] = $this->oDb->selectCell($sql);
        $sql = "SELECT Count(*) FROM ?_topic";
        $aResult['topics'] = $this->oDb->selectCell($sql);
        $sql = "SELECT Count(*) FROM ?_comment WHERE target_type='topic'";
        $aResult['comments'] = $this->oDb->selectCell($sql);
        return $aResult;
    }

    /**
     * Ban users by id
     *
     * @param array  $aUsersId
     * @param string $sDate
     * @param bool   $bUnlim
     * @param string $sComment
     *
     * @return bool
     */
    public function BanUsers($aUsersId, $sDate, $bUnlim, $sComment = null) {

        $this->UnbanUsers($aUsersId);
        foreach($aUsersId as $nUserId) {
            $sql = "
                INSERT INTO ?_adminban
                  (user_id, bandate, banline, banunlim, bancomment, banactive)
                  VALUES (?d, ?, ?, ?, ?, 1)
                ";
            if ($this->oDb->query($sql, $nUserId, F::Now(), $sDate, $bUnlim ? 1 : 0, $sComment) === false)
                return false;
        }
        return true;
    }

    /**
     * Unban users by id
     *
     * @param array $aUsersId
     *
     * @return bool
     */
    public function UnbanUsers($aUsersId) {

        $sql = "UPDATE ?_adminban SET banactive=0, banunlim=0 WHERE user_id IN (?a)";
        return $this->oDb->query($sql, $aUsersId) !== false;
    }

    /**
     * Return list of banned users
     *
     * @param int $iCount
     * @param int $iCurrPage
     * @param int $iPerPage
     *
     * @return array
     */
    public function GetBannedUsersId(&$iCount, $iCurrPage, $iPerPage) {

        $sql = "
            SELECT DISTINCT ab.user_id
            FROM
                ?_adminban AS ab
            WHERE (ab.user_id>0) AND (ab.banunlim>0 OR (ab.banline>? AND ab.banactive=1))
            ORDER BY ab.bandate DESC
            LIMIT ?d, ?d
            ";
        $aRows = $this->oDb->selectPage($iCount, $sql, F::Now(), ($iCurrPage - 1) * $iPerPage, $iPerPage);
        $aResult = array();
        if ($aRows)
            foreach($aRows as $aRow) {
                $aResult[] = $aRow['user_id'];
            }
        return $aResult;
    }

    /**
     * Returns list of banned IPs
     *
     * @param int $iCount
     * @param int $iCurrPage
     * @param int $iPerPage
     *
     * @return array
     */
    public function GetIpsBanList(&$iCount, $iCurrPage, $iPerPage) {

        $aReturn = array();
        $sql =
            "SELECT
                ips.id,
                CASE WHEN ips.ip1<>0 THEN INET_NTOA(ips.ip1) ELSE '' END AS `ip1`,
                CASE WHEN ips.ip2<>0 THEN INET_NTOA(ips.ip2) ELSE '' END AS `ip2`,
                ips.bandate, ips.banline, ips.banunlim, ips.bancomment
            FROM
                ?_adminips AS ips
            WHERE banactive=1
            ORDER BY ips.id
            LIMIT ?d, ?d
        ";
        $aRows = $this->oDb->selectPage($iCount, $sql, ($iCurrPage - 1) * $iPerPage, $iPerPage);

        if ($aRows) {
            $aReturn = $aRows;
        }
        return $aReturn;
    }

    /**
     * Ban range of IPs
     *
     * @param string $sIp1
     * @param string $sIp2
     * @param string $sDate
     * @param bool   $bUnlim
     * @param string $sComment
     *
     * @return bool
     */
    public function SetBanIp($sIp1, $sIp2, $sDate, $bUnlim, $sComment) {

        $sql
            = "
            INSERT INTO ?_adminips
                (
                    ip1,
                    ip2,
                    bandate,
                    banline,
                    banunlim,
                    bancomment,
                    banactive
                )
                VALUES (
                    INET_ATON(?:ip1),
                    INET_ATON(?:ip2),
                    ?:bandate,
                    ?:banline,
                    ?:banunlim,
                    ?:bancomment,
                    ?:banactive
                )
                    ";
        $nId = $this->oDb->sqlQuery(
            $sql,
            array(
                ':ip1'        => $sIp1,
                ':ip2'        => $sIp2,
                ':bandate'    => F::Now(),
                ':banline'    => $sDate,
                ':banunlim'   => $bUnlim ? 1 : 0,
                ':bancomment' => $sComment,
                ':banactive'  => 1,
            )
        );
        return $nId ? $nId : false;
    }

    /**
     * Unban range of IPs
     *
     * @param array $aIds
     *
     * @return bool
     */
    public function UnsetBanIp($aIds) {

        if (!is_array($aIds)) $aIds = intval($aIds);
        $sql = "
            UPDATE ?_adminips
            SET banactive=0, banunlim=0
            WHERE id IN (?a)";
        return $this->oDb->query($sql, $aIds) !== false;
    }

    /**
     * Returns list of invites
     *
     * @param int   $iCount
     * @param int   $iCurrPage
     * @param int   $iPerPage
     * @param array $aFilter
     *
     * @return array
     */
    public function GetInvites(&$iCount, $iCurrPage, $iPerPage, $aFilter = array()) {

        $sql =
            "SELECT
                invite_id, invite_code, user_from_id, user_to_id,
                invite_date_add, invite_date_used, invite_used,
                u1.user_login AS from_login,
                u2.user_login AS to_login
            FROM ?_invite AS i
                LEFT JOIN ?_user AS u1 ON i.user_from_id=u1.user_id
                LEFT JOIN ?_user AS u2 ON i.user_to_id=u2.user_id
            WHERE
                1=1
                {AND invite_used=?d}
                {AND invite_used=?d}
            ORDER BY invite_id DESC
            LIMIT ?d, ?d";
        $aRows = $this->oDb->selectPage($iCount, $sql,
            isset($aFilter['used']) ? 1 : DBSIMPLE_SKIP,
            isset($aFilter['unused']) ? 0 : DBSIMPLE_SKIP,
            ($iCurrPage - 1) * $iPerPage,
            $iPerPage
        );
        return $aRows ? $aRows : array();
    }

    /**
     * @return mixed
     */
    public function GetInvitesCount() {

        $sql =
            "SELECT
                COUNT(invite_id) AS cnt,
                SUM(invite_used) AS used,
                SUM(CASE WHEN invite_used=0 THEN 1 ELSE 0 END) AS unused
            FROM ?_invite
            ";
        $aResult = $this->oDb->selectRow($sql);
        $aResult['all'] = $aResult['cnt'];
        return $aResult;
    }

    /**
     * Deletes unused invites
     *
     * @param array $aIds
     *
     * @return bool
     */
    public function DeleteInvites($aIds) {

        // Удаляются только неиспользованные инвайты
        $sql =
            "DELETE FROM ?_invite
            WHERE invite_id IN (?a) AND invite_used=0 AND invite_date_used IS NULL";
        return $this->oDb->query($sql, $aIds) !== false;
    }

    /**
     * Сохранение пользовательских настроек
     *
     * @param   array   $aData
     * @return  bool
     */
    public function UpdateCustomConfig($aData) {

        $sql = "
            SELECT storage_key FROM ?_storage WHERE storage_key IN (?a) LIMIT ?d
        ";
        $aExists = $this->oDb->selectCol($sql, F::Array_Column($aData, 'storage_key'), sizeof($aData));
        $aInsert = array();
        $aUpdate = array();
        foreach($aData as $aItem) {
            if (in_array($aItem['storage_key'], $aExists)) {
                $aUpdate[] = $aItem;
            } else {
                $aInsert[] = $aItem;
            }
        }
        if ($aInsert) {
            $sql = "INSERT INTO ?_storage(?#) VALUES(?a)";
            // multi insert
            $this->oDb->query($sql, array_keys($aInsert[0]), array_values($aInsert));
        }
        if ($aUpdate) {
            $sql = "UPDATE ?_storage SET storage_val=? WHERE storage_key=?";
            foreach($aUpdate as $aItem) {
                $this->oDb->query($sql, $aItem['storage_val'], $aItem['storage_key']);
            }
        }

        return true;
    }

    /**
     * @param string $sPrefix
     *
     * @return mixed
     */
    public function GetCustomConfig($sPrefix = '') {

        if ($sPrefix) {
            $sPrefix = addslashes($sPrefix);
            if (substr($sPrefix, -1) == '.') {
                $sRootPath = substr($sPrefix, 0, strlen($sPrefix) - 1);
            } else {
                $sRootPath = $sPrefix;
                $sPrefix .= '.';
            }
            $sql = "
                SELECT storage_key AS ARRAY_KEY, storage_key, storage_val
                FROM ?_storage
                WHERE
                    storage_key = '" . $sRootPath . "'
                    OR storage_key LIKE '" . $sPrefix . "%'";
        } else {
            $sql = "
                SELECT storage_key AS ARRAY_KEY, storage_key, storage_val
                FROM ?_storage
            ";
        }
        return $this->oDb->select($sql);
    }

    /**
     * @param string $sPrefix
     *
     * @return bool
     */
    public function DeleteCustomConfig($sPrefix = '') {

        if ($sPrefix) {
            $sPrefix = addslashes($sPrefix);
            if (substr($sPrefix, -1) == '.') {
                $sRootPath = substr($sPrefix, 0, strlen($sPrefix) - 1);
            } else {
                $sRootPath = $sPrefix;
                $sPrefix .= '.';
            }
            $sql = "
                DELETE
                FROM ?_storage
                WHERE
                    storage_key = '" . $sRootPath . "'
                    OR storage_key LIKE '" . $sPrefix . "%'";
        } else {
            $sql = "
                DELETE
                FROM ?_storage
            ";
        }
        return $this->oDb->query($sql) !== false;
    }

    /**
     * @return array
     */
    public function GetUnlinkedBlogsForUsers() {

        $sql = "
            SELECT j.blog_id, u.user_login, j.user_id
            FROM ?_blog_user AS j
                LEFT JOIN ?_blog AS b ON b.blog_id=j.blog_id
                LEFT JOIN ?_user AS u ON u.user_id=j.user_id
            WHERE b.blog_id IS NULL";
        $aRows = $this->oDb->query($sql);
        $aResult = array();
        if ($aRows)
            foreach ($aRows as $aRow) {
                $aResult[$aRow['blog_id']][] = $aRow;
            }
        return $aResult;
    }

    /**
     * @param array $aBlogIds
     *
     * @return mixed
     */
    public function DelUnlinkedBlogsForUsers($aBlogIds) {

        $sql = "
            DELETE FROM ?_blog_user
            WHERE blog_id IN (?a)
        ";
        $aResult = $this->oDb->query($sql, $aBlogIds);
        return $aResult;
    }

    /**
     * @return array
     */
    public function GetUnlinkedBlogsForCommentsOnline() {

        $sql = "
            SELECT c.target_parent_id AS blog_id, c.comment_id, c.target_id
            FROM ?_comment_online AS c
                LEFT JOIN ?_topic AS t ON t.topic_id=c.target_id
                LEFT JOIN ?_blog AS b ON b.blog_id=c.target_parent_id
            WHERE c.target_type='topic' AND b.blog_id IS NULL";
        $aRows = $this->oDb->query($sql);
        $aResult = array();
        if ($aRows)
            foreach ($aRows as $aRow) {
                $aResult[$aRow['blog_id']][] = $aRow;
            }
        return $aResult;
    }

    /**
     * @param array $aBlogIds
     *
     * @return mixed
     */
    public function DelUnlinkedBlogsForCommentsOnline($aBlogIds) {

        $sql = "
            DELETE FROM ?_comment_online
            WHERE target_type='topic' AND target_parent_id IN (?a)
        ";
        $aResult = $this->oDb->query($sql, $aBlogIds);
        return $aResult;
    }

    /**
     * @return array
     */
    public function GetUnlinkedTopicsForCommentsOnline() {

        $sql = "
            SELECT *
            FROM ?_comment_online AS c
            WHERE target_type='topic' AND target_id NOT IN (SELECT topic_id FROM ?_topic)
            ";
        $aRows = $this->oDb->query($sql);
        $aResult = array();
        if ($aRows)
            foreach ($aRows as $aRow) {
                $aResult[$aRow['target_id']][] = $aRow;
            }
        return $aResult;
    }

    /**
     * @param array $aTopicsId
     *
     * @return mixed
     */
    public function DelUnlinkedTopicsForCommentsOnline($aTopicsId) {

        $sql = "
            DELETE FROM ?_comment_online
            WHERE target_type='topic' AND target_id IN (?a)
        ";
        $aResult = $this->oDb->query($sql, $aTopicsId);
        return $aResult;
    }

    /**
     * Устанавливает новую роль пользователя
     *
     * @param $oUser
     * @param $iRole
     * @return mixed
     */
    public function UpdateRole($oUser, $iRole) {

        $sql = "UPDATE ?_user SET user_role = ?d WHERE user_id = ?d";
        return $this->oDb->query($sql, $iRole, $oUser->getId());

    }

    /**
     * @return int
     */
    public function GetNumTopicsWithoutUrl() {

        $sql = "
            SELECT Count(topic_id) as cnt
            FROM ?_topic
            WHERE (Trim(topic_url)='') OR (topic_url IS NULL)";
        return intval($this->oDb->selectCell($sql));
    }

    /**
     * @param int $nLimit
     *
     * @return mixed
     */
    public function GetTitleTopicsWithoutUrl($nLimit) {

        $sql = "
            SELECT
                topic_id AS ARRAY_KEY, topic_id, topic_title
            FROM ?_topic
            WHERE (Trim(topic_url)='') OR (topic_url IS NULL)
            ORDER BY topic_date_add ASC
            LIMIT ?d
            ";
        return $this->oDb->select($sql, $nLimit);
    }

    /**
     * @param array $aData
     *
     * @return bool
     */
    public function SaveTopicsUrl($aData) {

        $sql = '';
        foreach ($aData as $nId => $aRec) {
            $sql .= " WHEN $nId THEN '" . $aRec['topic_url'] . "'";
        }
        $sql = "
            UPDATE ?_topic
            SET topic_url=CASE topic_id " . $sql . " ELSE topic_url END
            WHERE topic_id IN (?a)";
        return $this->oDb->query($sql, array_keys($aData)) !== false;
    }

    /**
     * @return mixed
     */
    public function GetDuplicateTopicsUrl() {

        $sql = "
            SELECT Count( topic_id ) AS cnt, topic_url
            FROM ?_topic
            WHERE topic_url > ''
            GROUP BY topic_url
            HAVING cnt >2
            ";
        return $this->oDb->select($sql);
    }

    /**
     * @param array $aUrls
     *
     * @return mixed
     */
    public function GetTopicsDataByUrl($aUrls) {

        $sql = "
            SELECT topic_id, topic_url
            FROM ?_topic
            WHERE topic_url IN (?a)
            ORDER BY topic_date_add ASC
            ";
        return $this->oDb->select($sql, $aUrls);
    }

    /**
     * @param $nUserId
     *
     * @return bool
     */
    public function DelUser($nUserId) {
        $bOk = true;
        // Удаление комментов

        // находим комменты удаляемого юзера и для каждого:
        // нижележащее дерево комментов подтягиваем к родителю удаляемого
        $sql
            = "SELECT comment_id AS ARRAY_KEY, comment_pid, target_type, target_id
                FROM ?_comment
                WHERE user_id=?d";

        $aTargets = array();
        while ($aComments = $this->oDb->select($sql, $nUserId)) {
            if (is_array($aComments) AND sizeof($aComments)) {
                foreach ($aComments AS $sId => $aCommentData) {
                    $this->oDb->transaction();
                    $sql = "UPDATE ?_comment SET comment_pid=?d WHERE comment_pid=?d";
                    @$this->oDb->query($sql, $aCommentData['comment_pid'], $sId);
                    $sql = "DELETE FROM ?_comment WHERE comment_id=?d";
                    @$this->oDb->query($sql, $sId);
                    if (!isset($aTargets[$aCommentData['target_type'] . '_' . $aCommentData['target_id']])) {
                        $aTargets[$aCommentData['target_type'] . '_' . $aCommentData['target_id']] = array(
                            'target_type' => $aCommentData['target_type'],
                            'target_id'   => $aCommentData['target_id'],
                        );
                    }
                    $this->oDb->commit();
                }
            } else {
                break;
            }
        }
        // Обновление числа комментариев
        foreach ($aTargets as $aTarget) {
            E::ModuleTopic()->RecalcCountOfComments($aTarget['target_id']);
        }

        // удаление остального "хозяйства"
        //$this->oDb->transaction();
        $sql = "DELETE FROM ?_topic WHERE user_id=?d";
        @$this->oDb->query($sql, $nUserId);

        $sql = "DELETE FROM ?_blog WHERE user_owner_id=?d";
        @$this->oDb->query($sql, $nUserId);

        $sql = "DELETE FROM ?_vote WHERE user_voter_id=?d";
        @$this->oDb->query($sql, $nUserId);

        $sql = "DELETE FROM ?_blog_user WHERE user_id=?d";
        @$this->oDb->query($sql, $nUserId);

        $sql = "DELETE FROM ?_adminban WHERE user_id=?d";
        @$this->oDb->query($sql, $nUserId);

        $sql = "DELETE FROM ?_talk_user WHERE user_id=?d";
        @$this->oDb->query($sql, $nUserId);

        $sql = "DELETE FROM ?_user WHERE user_id=?d";
        @$this->oDb->query($sql, $nUserId);

        //$this->oDb->commit();

        $bOk = $this->oDb->selectCell("SELECT user_id FROM ?_user WHERE user_id=?d", $nUserId);
        return !$bOk;
    }

}

// EOF