<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 */

class ModuleSearch extends Module {

    /** @var ModuleSearch_MapperSearch */
    protected $oMapper;

    /** @var ModuleUser_EntityUser */
    protected $oUserCurrent;

    public function Init() {

        $this->oMapper = E::GetMapper(__CLASS__);
        $this->oUserCurrent = E::ModuleUser()->GetUserCurrent();
    }

    /**
     * Получает список топиков по регулярному выражению (поиск)
     *
     * @param $sRegexp
     * @param $iPage
     * @param $iPerPage
     * @param array $aParams
     * @param bool $bAccessible
     *
     * @return array
     */
    public function GetTopicsIdByRegexp($sRegexp, $iPage, $iPerPage, $aParams = array(), $bAccessible = false) {

        $s = md5(serialize($sRegexp) . serialize($aParams));
        $sCacheKey = 'search_topics_' . $s . '_' . $iPage . '_' . $iPerPage;
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            if ($bAccessible) {
                $aParams['aFilter'] = E::ModuleTopic()->GetNamedFilter(FALSE, array('accessible' => true));
            }
            $data = array(
                'collection' => $this->oMapper->GetTopicsIdByRegexp($sRegexp, $iCount, $iPage, $iPerPage, $aParams),
                'count'      => $iCount,
            );
            E::ModuleCache()->Set($data, $sCacheKey, array('topic_update', 'topic_new'), 'PT1H');
        }
        return $data;
    }

    /**
     * Получает список комментариев по регулярному выражению (поиск)
     *
     * @param       $sRegexp
     * @param       $iPage
     * @param       $iPerPage
     * @param array $aParams
     *
     * @return array
     */
    public function GetCommentsIdByRegexp($sRegexp, $iPage, $iPerPage, $aParams = array()) {

        $s = md5(serialize($sRegexp) . serialize($aParams));
        $sCacheKey = 'search_comments_' . $s . '_' . $iPage . '_' . $iPerPage;
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = array(
                'collection' => $this->oMapper->GetCommentsIdByRegexp($sRegexp, $iCount, $iPage, $iPerPage, $aParams),
                'count'      => $iCount,
            );
            E::ModuleCache()->Set($data, $sCacheKey, array('topic_update', 'comment_new'), 'PT1H');
        }
        return $data;
    }

    /**
     * Получает список блогов по регулярному выражению (поиск)
     *
     * @param       $sRegexp
     * @param       $iPage
     * @param       $iPerPage
     * @param array $aParams
     *
     * @return array
     */
    public function GetBlogsIdByRegexp($sRegexp, $iPage, $iPerPage, $aParams = array()) {

        $s = md5(serialize($sRegexp) . serialize($aParams));
        $sCacheKey = 'search_blogs_' . $s . '_' . $iPage . '_' . $iPerPage;
        if (false === ($data = E::ModuleCache()->Get($sCacheKey))) {
            $data = array(
                'collection' => $this->oMapper->GetBlogsIdByRegexp($sRegexp, $iCount, $iPage, $iPerPage, $aParams),
                'count'      => $iCount);
            E::ModuleCache()->Set($data, $sCacheKey, array('blog_update', 'blog_new'), 'PT1H');
        }
        return $data;
    }

}

// EOF