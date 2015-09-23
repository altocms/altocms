<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */


class ModuleSearch_MapperSearch extends Mapper {

    protected function PrepareRegExp($sRegExp) {

        $sRegExpPrim = str_replace('[[:>:]]|[[:<:]]', '[[:space:]]+', $sRegExp);
        $sRegExpPrim = str_replace('|[[:<:]]', '[[:alnum:]]+[[:space:]]+', $sRegExpPrim);
        $sRegExpPrim = str_replace('[[:>:]]|', '[[:space:]]+[[:alnum:]]+', $sRegExpPrim);

        $aRegExp = array('phrase' => $sRegExpPrim, 'words' => $sRegExp);
        if (strpos($sRegExp, '[[:>:]]|[[:<:]]')) {
            $aWords = explode('[[:>:]]|[[:<:]]', $sRegExp, C::Get('module.search.rate.limit'));
            foreach($aWords as $iIndex => $sWord) {
                if (substr($sWord, 0, 7) !== '[[:<:]]') {
                    $aWords[$iIndex] = '[[:<:]]' . $sWord;
                }
                if (substr($sWord, -7) !== '[[:>:]]') {
                    $aWords[$iIndex] .= '[[:>:]]';
                }
            }
        } else {
            $aWords = array();
        }

        $aRates = array(
            'phrase' => (count($aWords) + 1) * C::Val('module.search.rate.phrase', 1),
            'words' => C::Val('module.search.rate.words', 1),
            'title' => C::Val('module.search.rate.title', 1),
        );

        return array('regexp' => $aRegExp, 'words' => $aWords, 'rates' => $aRates);
    }

    /**
     * Поиск текста по топикам
     *
     * @param string $sRegExp
     * @param int    $iCount
     * @param int    $iCurrPage
     * @param int    $iPerPage
     * @param array  $aParams
     *
     * @return array
     */
    public function GetTopicsIdByRegexp($sRegExp, &$iCount, $iCurrPage, $iPerPage, $aParams) {

        $aData = $this->PrepareRegExp($sRegExp);
        $aWeight = array();

        // Обработка возможного фильтра. Пока параметр один - это разрешённые блоги для пользователя
        // но на будущее условия разделены
        if (isset($aParams['aFilter']) && is_array($aParams['aFilter']) && !empty($aParams['aFilter'])) {

            // Если определён список типов/ид. разрешённых блогов
            if (isset($aParams['aFilter']['blog_type']) && is_array($aParams['aFilter']['blog_type']) && !empty($aParams['aFilter']['blog_type'])) {
                $sWhere = '';
                $aBlogTypes = array();
                $aOrClauses = array();
                $aParams['aFilter']['blog_type'] = F::Array_FlipIntKeys($aParams['aFilter']['blog_type'], 0);
                foreach ($aParams['aFilter']['blog_type'] as $sType => $aBlogsId) {
                    if ($aBlogsId) {
                        if ($sType == '*') {
                            $aOrClauses[] = "(t.blog_id IN ('" . join("','", $aBlogsId) . "'))";
                        } else {
                            $aOrClauses[] = "b.blog_type='" . $sType . "' AND t.blog_id IN ('" . join("','", $aBlogsId) . "')";
                        }
                    } else {
                        $aBlogTypes[] = "'" . $sType . "'";
                    }
                }
                if ($aBlogTypes) {
                    $aOrClauses[] = '(b.blog_type IN (' . join(',', $aBlogTypes) . '))';
                }
                if ($aOrClauses) {
                    $sWhere .= ' AND (' . join(' OR ', $aOrClauses ) . ')';
                }
            }
        }

        $aWeight[] = "(LOWER(t.topic_title) REGEXP " . $this->oDb->escape($aData['regexp']['phrase']) . ")*" . ($aData['rates']['phrase'] * $aData['rates']['title']);
        $aWeight[] = "(LOWER(tc.topic_text_source) REGEXP " . $this->oDb->escape($aData['regexp']['phrase']) . ")*" . ($aData['rates']['phrase']);
        foreach($aData['words'] as $sWord) {
            $aWeight[] = "(LOWER(t.topic_title) REGEXP " . $this->oDb->escape($sWord) . ")*" . ($aData['rates']['words'] * $aData['rates']['title']);
            $aWeight[] = "(LOWER(tc.topic_text_source) REGEXP " . $this->oDb->escape($sWord) . ")*" . ($aData['rates']['words']);
        }
        $sWeight = implode('+', $aWeight);
        $aResult = array();

        $sql = "
                SELECT t.topic_id,
                    $sWeight AS weight
                FROM ?_topic AS t
                    INNER JOIN ?_topic_content AS tc ON tc.topic_id=t.topic_id
                    ". (C::Get('module.search.accessible') ? 'INNER JOIN ?_blog AS b ON b.blog_id=t.blog_id' : '')."
                WHERE 
                    (topic_publish=1)
                    " . $sWhere . "
                     AND topic_index_ignore=0
                     AND (
                        (LOWER(t.topic_title) REGEXP ?)
                        OR (LOWER(tc.topic_text_source) REGEXP ?)
                     )
                ORDER BY
                    weight DESC,
                    t.topic_id ASC
                LIMIT ?d, ?d
            ";

        $aRows = $this->oDb->selectPage(
            $iCount, $sql,
            $aData['regexp']['words'],
            $aData['regexp']['words'],
            ($iCurrPage - 1) * $iPerPage, $iPerPage
        );

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
     * @param int    $iCount
     * @param int    $iCurrPage
     * @param int    $iPerPage
     * @param array  $aParams
     *
     * @return array
     */
    public function GetCommentsIdByRegexp($sRegExp, &$iCount, $iCurrPage, $iPerPage, $aParams) {

        $aData = $this->PrepareRegExp($sRegExp);
        $aResult = array();

        $sql = "
                SELECT DISTINCT c.comment_id,
                    CASE WHEN (LOWER(c.comment_text) REGEXP ?) THEN 1 ELSE 0 END weight
                FROM ?_comment AS c
                    INNER JOIN ?_topic AS t ON t.topic_id=c.target_id
                WHERE
                    (comment_delete=0 AND target_type='topic' AND t.topic_index_ignore=0)
                    AND
                        (
                            (LOWER(c.comment_text) REGEXP ?)
                        )
                ORDER BY
                    weight DESC,
                    c.comment_id ASC
                LIMIT ?d, ?d
            ";
        $aRows = $this->oDb->selectPage(
            $iCount, $sql,
            $aData['regexp']['words'],
            $aData['regexp']['words'],
            ($iCurrPage - 1) * $iPerPage, $iPerPage
        );

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
     * @param int    $iCount
     * @param int    $iCurrPage
     * @param int    $iPerPage
     * @param array  $aParams
     *
     * @return array
     */
    public function GetBlogsIdByRegexp($sRegExp, &$iCount, $iCurrPage, $iPerPage, $aParams) {

        $aRegExp = $this->PrepareRegExp($sRegExp);
        $aResult = array();

        $sql = "
                SELECT DISTINCT b.blog_id,
                    CASE WHEN (LOWER(b.blog_title) REGEXP ?) THEN 1 ELSE 0 END weight
                FROM ?_blog AS b
                    INNER JOIN ?_blog_type AS bt ON bt.type_code=b.blog_type
                WHERE
                    bt.index_ignore=0
                    AND
                    (
                        (LOWER(b.blog_title) REGEXP ?)
                        {OR (LOWER(b.blog_description) REGEXP ?)}
                    )
                ORDER BY
                    weight DESC,
                    b.blog_id ASC
                LIMIT ?d, ?d
            ";
        $aRows = $this->oDb->selectPage(
            $iCount, $sql,
            $aRegExp[0],
            $aRegExp[0], (isset($aRegExp[1]) ? $aRegExp[1] : DBSIMPLE_SKIP),
            ($iCurrPage - 1) * $iPerPage, $iPerPage
        );

        if ($aRows) {
            foreach ($aRows as $aRow) {
                $aResult[] = $aRow['blog_id'];
            }
        }
        return $aResult;
    }

}

// EOF