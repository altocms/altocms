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
     * @param      $aUsersId
     * @param      $sDate
     * @param      $nUnlim
     * @param null $sComment
     *
     * @return bool
     */
    public function BanUsers($aUsersId, $sDate, $nUnlim, $sComment = null) {

        $this->UnbanUsers($aUsersId);
        foreach($aUsersId as $nUserId) {
            $sql = "
                INSERT INTO ?_adminban
                SET user_id=?d, bandate=?, banline=?, banunlim=?, bancomment=?, banactive=1";
            if ($this->oDb->query($sql, $nUserId, F::Now(), $sDate, $nUnlim ? 1 : 0, $sComment) === false)
                return false;
        }
        return true;
    }

    /**
     * Unban users by id
     *
     * @param $aUsersId
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
     * @param $nCount
     * @param $nCurrPage
     * @param $nPerPage
     *
     * @return array
     */
    public function GetBannedUsersId(&$nCount, $nCurrPage, $nPerPage) {

        $sql = "
            SELECT DISTINCT ab.user_id
            FROM
                ?_adminban AS ab
            WHERE (ab.user_id>0) AND (ab.banunlim>0 OR (Now()<ab.banline AND ab.banactive=1))
            ORDER BY ab.bandate DESC
            LIMIT ?d, ?d
            ";
        $aRows = $this->oDb->selectPage($nCount, $sql, ($nCurrPage - 1) * $nPerPage, $nPerPage);
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
     * @param $iCount
     * @param $iCurrPage
     * @param $iPerPage
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
     * @param $sIp1
     * @param $sIp2
     * @param $dDate
     * @param $nUnlim
     * @param $sComment
     *
     * @return bool
     */
    public function SetBanIp($sIp1, $sIp2, $dDate, $nUnlim, $sComment) {

        $sql = "
            INSERT INTO ?_adminips
                SET
                    ip1=INET_ATON(?),
                    ip2=INET_ATON(?),
                    bandate=?,
                    banline=?,
                    banunlim=?d,
                    bancomment=?,
                    banactive=1
                    ";
        return $this->oDb->query($sql, $sIp1, $sIp2, F::Now(), $dDate, $nUnlim, $sComment) !== false;
    }

    /**
     * Unban range of IPs
     *
     * @param $aIds
     *
     * @return bool
     */
    public function UnsetBanIp($aIds) {

        if (!is_array($aIds)) $aIds = intval($aIds);
        $sql = "
            UPDATE ?_adminips
            SET banactive=0, banunlim=0 WHERE id IN (?a)";
        return $this->oDb->query($sql, $aIds) !== false;
    }

    /**
     * Returns list of invites
     *
     * @param $iCount
     * @param $iCurrPage
     * @param $iPerPage
     *
     * @return array
     */
    public function GetInvites(&$iCount, $iCurrPage, $iPerPage) {

        $sql =
            "SELECT invite_id, invite_code, user_from_id, user_to_id,
                invite_date_add, invite_date_used, invite_used,
                u1.user_login AS from_login,
                u2.user_login AS to_login
              FROM ?_invite AS i
                LEFT JOIN ?_user AS u1 ON i.user_from_id=u1.user_id
                LEFT JOIN ?_user AS u2 ON i.user_to_id=u2.user_id
            ORDER BY invite_id DESC
            LIMIT ?d, ?d";
        if (($aRows = $this->oDb->selectPage($iCount, $sql, ($iCurrPage - 1) * $iPerPage, $iPerPage))) {
            return $aRows;
        }
        return array();
    }

    /**
     * Deletes unused invites
     *
     * @param $aIds
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
            $this->oDb->query($sql, array_keys($aData[0]), array_values($aData));
        }
        if ($aUpdate) {
            $sql = "UPDATE ?_storage SET storage_val=? WHERE storage_key=?";
            foreach($aUpdate as $aItem) {
                $this->oDb->query($sql, $aItem['storage_val'], $aItem['storage_key']);
            }
        }

        return true;
    }

    public function GetCustomConfig($sPrefix = '') {

        if ($sPrefix) {
            $sql = "
                SELECT storage_key, storage_val
                FROM ?_storage
                WHERE storage_key LIKE '" . $sPrefix . "%'";
        } else {
            $sql = "
                SELECT storage_key, storage_val
                FROM ?_storage
            ";
        }
        return $this->oDb->select($sql);
    }

    public function DeleteCustomConfig($sPrefix = '') {

        if ($sPrefix) {
            $sql = "
                DELETE
                FROM ?_storage
                WHERE storage_key LIKE '" . $sPrefix . "%'";
        } else {
            $sql = "
                DELETE
                FROM ?_storage
            ";
        }
        return $this->oDb->query($sql) !== false;
    }

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

    public function DelUnlinkedBlogsForUsers($aBlogIds) {

        $sql = "
            DELETE FROM ?_blog_user
            WHERE blog_id IN (?a)
        ";
        $aResult = $this->oDb->query($sql, $aBlogIds);
        return $aResult;
    }

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

    public function DelUnlinkedBlogsForCommentsOnline($aBlogIds) {

        $sql = "
            DELETE FROM ?_comment_online
            WHERE target_type='topic' AND target_parent_id IN (?a)
        ";
        $aResult = $this->oDb->query($sql, $aBlogIds);
        return $aResult;
    }

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

    public function DelUnlinkedTopicsForCommentsOnline($aTopicsId) {

        $sql = "
            DELETE FROM ?_comment_online
            WHERE target_type='topic' AND target_id IN (?a)
        ";
        $aResult = $this->oDb->query($sql, $aTopicsId);
        return $aResult;
    }

    public function SetAdministrator($nUserId) {

        $sql = "SELECT user_id FROM ?_user_administrator WHERE user_id=?";
        if (!$this->oDb->selectCell($sql, $nUserId)) {
            return $this->oDb->query("INSERT INTO ?_user_administrator (user_id) VALUES(?)", $nUserId) !== false;
        }
        return false;
    }

    public function UnsetAdministrator($nUserId) {

        $sql = "DELETE FROM ?_user_administrator WHERE user_id=?";
        return $this->oDb->query($sql, $nUserId) !== false;
    }

    public function GetNumTopicsWithoutUrl() {

        $sql = "
            SELECT Count(topic_id) as cnt
            FROM ?_topic
            WHERE (Trim(topic_url)='') OR (topic_url IS NULL)";
        return intval($this->oDb->selectCell($sql));
    }

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

    public function GetTopicsDataByUrl($aUrls) {

        $sql = "
            SELECT topic_id, topic_url
            FROM ?_topic
            WHERE topic_url IN (?a)
            ORDER BY topic_date_add ASC
            ";
        return $this->oDb->select($sql, $aUrls);
    }

}

// EOF