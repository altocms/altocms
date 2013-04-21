<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Version: 0.9a
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */


class ModuleSearch_MapperSearch extends Mapper
{

    protected function PrepareRegExp($sRegExp)
    {
        $sRegExpPrim = str_replace('[[:>:]]|[[:<:]]', '[[:space:]]+', $sRegExp);
        $sRegExpPrim = str_replace('|[[:<:]]', '[[:alnum:]]+[[:space:]]+', $sRegExpPrim);
        $sRegExpPrim = str_replace('[[:>:]]|', '[[:space:]]+[[:alnum:]]+', $sRegExpPrim);
        $aRegExp = array($sRegExpPrim, $sRegExp);
        return $aRegExp;
    }

    /**
     * Поиск текста по топикам
     *
     * @param string $sRegExp
     * @param int $iCount
     * @param int $iCurrPage
     * @param int $iPerPage
     * @param array $aParams
     * @return array
     */
    public function GetTopicsIdByRegexp($sRegExp, &$iCount, $iCurrPage, $iPerPage, $aParams)
    {
        $aRegExp = $this->PrepareRegExp($sRegExp);
        $aResult = array();
        if (!$aParams['bSkipTags']) {
            $sql = "
                SELECT DISTINCT t.topic_id, 
                    CASE WHEN (LOWER(t.topic_title) REGEXP ?) THEN 1 ELSE 0 END +
                    CASE WHEN (LOWER(tc.topic_text_source) REGEXP ?) THEN 1 ELSE 0 END AS weight
                FROM " . Config::Get('db.table.topic') . " AS t
                    INNER JOIN " . Config::Get('db.table.topic_content') . " AS tc ON tc.topic_id=t.topic_id
                WHERE 
                    (topic_publish=1) 
                    AND ((LOWER(t.topic_title) REGEXP ?)
                        {OR (LOWER(t.topic_title) REGEXP ?)}
                        OR (LOWER(tc.topic_text_source) REGEXP ?)
                        {OR (LOWER(tc.topic_text_source) REGEXP ?)}
                    )
                ORDER BY
                    weight DESC,
                    t.topic_id ASC
                LIMIT ?d, ?d
            ";
            $aRows = $this->oDb->selectPage($iCount, $sql,
                $aRegExp[0],
                $aRegExp[0],
                $aRegExp[0], (isset($aRegExp[1]) ? $aRegExp[1] : DBSIMPLE_SKIP),
                $aRegExp[0], (isset($aRegExp[1]) ? $aRegExp[1] : DBSIMPLE_SKIP),
                ($iCurrPage - 1) * $iPerPage, $iPerPage);
        }

        if ($aRows) {
            foreach ($aRows as $aRow) {
                $aResult[] = $aRow['topic_id'];
            }
        }
        return $aResult;
    }

    /**
     * Поиск текста по комментариям
     *
     * @param string $sRegExp
     * @param int $iCount
     * @param int $iCurrPage
     * @param int $iPerPage
     * @param array $aParams
     * @return array
     */
    public function GetCommentsIdByRegexp($sRegExp, &$iCount, $iCurrPage, $iPerPage, $aParams)
    {
        $aRegExp = $this->PrepareRegExp($sRegExp);
        $aResult = array();
        if (!$aParams['bSkipTags']) {
            $sql = "
                SELECT DISTINCT c.comment_id,
                    CASE WHEN (LOWER(c.comment_text) REGEXP ?) THEN 1 ELSE 0 END weight
                FROM " . Config::Get('db.table.comment') . " AS c
                WHERE
                    (comment_delete=0 AND target_type='topic')
                    AND
                        (
                            (LOWER(c.comment_text) REGEXP ?)
                            {OR (LOWER(c.comment_text) REGEXP ?)}
                        )
                ORDER BY
                    weight DESC,
                    c.comment_id ASC
                LIMIT ?d, ?d
            ";
            $aRows = $this->oDb->selectPage($iCount, $sql,
                $aRegExp[0],
                $aRegExp[0], (isset($aRegExp[1]) ? $aRegExp[1] : DBSIMPLE_SKIP),
                ($iCurrPage - 1) * $iPerPage, $iPerPage);
        }

        if ($aRows) {
            foreach ($aRows as $aRow) {
                $aResult[] = $aRow['comment_id'];
            }
        }
        return $aResult;
    }

    /**
     * Поиск текста по блогам
     *
     * @param string $sRegExp
     * @param int $iCount
     * @param int $iCurrPage
     * @param int $iPerPage
     * @param array $aParams
     * @return array
     */
    public function GetBlogsIdByRegexp($sRegExp, &$iCount, $iCurrPage, $iPerPage, $aParams)
    {
        $aRegExp = $this->PrepareRegExp($sRegExp);
        $aResult = array();
        if (!$aParams['bSkipTags']) {
            $sql = "
                SELECT DISTINCT b.blog_id,
                    CASE WHEN (LOWER(b.blog_title) REGEXP ?) THEN 1 ELSE 0 END weight
                FROM " . Config::Get('db.table.blog') . " AS b
                WHERE
                    (
                        (LOWER(b.blog_title) REGEXP ?)
                        {OR (LOWER(b.blog_description) REGEXP ?)}
                    )
                ORDER BY
                    weight DESC,
                    b.blog_id ASC
                LIMIT ?d, ?d
            ";
            $aRows = $this->oDb->selectPage($iCount, $sql,
                $aRegExp[0],
                $aRegExp[0], (isset($aRegExp[1]) ? $aRegExp[1] : DBSIMPLE_SKIP),
                ($iCurrPage - 1) * $iPerPage, $iPerPage);
        }

        if ($aRows) {
            foreach ($aRows as $aRow) {
                $aResult[] = $aRow['blog_id'];
            }
        }
        return $aResult;
    }

}

// EOF