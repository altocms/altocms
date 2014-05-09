<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   LiveStreet Engine Social Networking by Mzhelskiy Maxim
 *   Site: www.livestreet.ru
 *   E-mail: rus.engine@gmail.com
 *----------------------------------------------------------------------------
 */

/**
 * Объект сущности комментариев
 *
 * @package modules.comment
 * @since   1.0
 */
class ModuleComment_EntityComment extends Entity {
    /**
     * Возвращает ID коммента
     *
     * @return int|null
     */
    public function getId() {

        return $this->getProp('comment_id');
    }

    /**
     * Возвращает ID родительского коммента
     *
     * @return int|null
     */
    public function getPid() {

        return $this->getProp('comment_pid');
    }

    /**
     * Возвращает значение left для дерева nested set
     *
     * @return int|null
     */
    public function getLeft() {

        return $this->getProp('comment_left');
    }

    /**
     * Возвращает значение right для дерева nested set
     *
     * @return int|null
     */
    public function getRight() {

        return $this->getProp('comment_right');
    }

    /**
     * Возвращает ID владельца
     *
     * @return int|null
     */
    public function getTargetId() {

        return $this->getProp('target_id');
    }

    /**
     * Возвращает тип владельца
     *
     * @return string|null
     */
    public function getTargetType() {

        return $this->getProp('target_type');
    }

    /**
     * Возвращет ID родителя владельца
     *
     * @return int|null
     */
    public function getTargetParentId() {

        return intval($this->getProp('target_parent_id'));
    }

    /**
     * Возвращает ID пользователя, автора комментария
     *
     * @return int|null
     */
    public function getUserId() {

        return $this->getProp('user_id');
    }

    /**
     * Возвращает текст комментария
     *
     * @return string|null
     */
    public function getText() {

        return $this->getProp('comment_text');
    }

    /**
     * Возвращает дату комментария
     *
     * @return string|null
     */
    public function getDate() {

        return $this->getProp('comment_date');
    }

    /**
     * Возвращает IP пользователя
     *
     * @return string|null
     */
    public function getUserIp() {

        return $this->getProp('comment_user_ip');
    }

    /**
     * Возвращает рейтинг комментария
     *
     * @return string
     */
    public function getRating() {

        return number_format(round($this->getProp('comment_rating'), 2), 0, '.', '');
    }

    /**
     * Возвращает количество проголосовавших
     *
     * @return int|null
     */
    public function getCountVote() {

        return $this->getProp('comment_count_vote');
    }

    /**
     * Возвращает флаг удаленного комментария
     *
     * @return int|null
     */
    public function getDelete() {

        return $this->getProp('comment_delete');
    }

    /**
     * Возвращает флаг опубликованного комментария
     *
     * @return int
     */
    public function getPublish() {

        return $this->getProp('comment_publish') ? 1 : 0;
    }

    /**
     * Возвращает хеш комментария
     *
     * @return string|null
     */
    public function getTextHash() {

        return $this->getProp('comment_text_hash');
    }

    /**
     * Возвращает уровень вложенности комментария
     *
     * @return int|null
     */
    public function getLevel() {

        return $this->getProp('comment_level');
    }

    /**
     * Проверяет является ли комментарий плохим
     *
     * @return bool
     */
    public function isBad() {

        if ($this->getRating() <= Config::Get('module.comment.bad')) {
            return true;
        }
        return false;
    }

    /**
     * Возвращает объект пользователя
     *
     * @return ModuleUser_EntityUser|null
     */
    public function getUser() {

        return $this->getProp('user');
    }

    /**
     * Возвращает объект владельца
     *
     * @return mixed|null
     */
    public function getTarget() {

        return $this->getProp('target');
    }

    /**
     * Возвращает объект голосования
     *
     * @return ModuleVote_EntityVote|null
     */
    public function getVote() {

        return $this->getProp('vote');
    }

    /**
     * Проверяет является ли комментарий избранным у текущего пользователя
     *
     * @return bool|null
     */
    public function getIsFavourite() {

        return $this->getProp('comment_is_favourite');
    }

    /**
     * Возвращает количество избранного
     *
     * @return int|null
     */
    public function getCountFavourite() {

        return $this->getProp('comment_count_favourite');
    }


    /**
     * Устанавливает ID комментария
     *
     * @param int $data
     */
    public function setId($data) {

        $this->setProp('comment_id', $data);
    }

    /**
     * Устанавливает ID родительского комментария
     *
     * @param int $data
     */
    public function setPid($data) {

        $this->setProp('comment_pid', $data);
    }

    /**
     * Устанавливает значени left для дерева nested set
     *
     * @param int $data
     */
    public function setLeft($data) {

        $this->setProp('comment_left', $data);
    }

    /**
     * Устанавливает значени right для дерева nested set
     *
     * @param int $data
     */
    public function setRight($data) {

        $this->setProp('comment_right', $data);
    }

    /**
     * Устанавливает ID владельца
     *
     * @param int $data
     */
    public function setTargetId($data) {

        $this->setProp('target_id', $data);
    }

    /**
     * Устанавливает тип владельца
     *
     * @param string $data
     */
    public function setTargetType($data) {

        $this->setProp('target_type', $data);
    }

    /**
     * Устанавливает ID родителя владельца
     *
     * @param int $data
     */
    public function setTargetParentId($data) {

        $this->setProp('target_parent_id', $data);
    }

    /**
     * Устанавливает ID пользователя
     *
     * @param int $data
     */
    public function setUserId($data) {

        $this->setProp('user_id', $data);
    }

    /**
     * Устанавливает текст комментария
     *
     * @param string $data
     */
    public function setText($data) {

        $this->setProp('comment_text', $data);
        $this->setTextHash(md5($data));
    }

    /**
     * Устанавливает дату комментария
     *
     * @param string $data
     */
    public function setDate($data) {

        $this->setProp('comment_date', $data);
    }

    /**
     * Устанвливает IP пользователя
     *
     * @param string $data
     */
    public function setUserIp($data) {

        $this->setProp('comment_user_ip', $data);
    }

    /**
     * Устанавливает рейтинг комментария
     *
     * @param float $data
     */
    public function setRating($data) {

        $this->setProp('comment_rating', $data);
    }

    /**
     * Устанавливает количество проголосавших
     *
     * @param int $data
     */
    public function setCountVote($data) {

        $this->setProp('comment_count_vote', $data);
    }

    /**
     * Устанавливает флаг удаленности комментария
     *
     * @param int $data
     */
    public function setDelete($data) {

        $this->setProp('comment_delete', $data);
    }

    /**
     * Устанавливает флаг публикации
     *
     * @param int $data
     */
    public function setPublish($data) {

        $this->setProp('comment_publish', $data);
    }

    /**
     * Устанавливает хеш комментария
     *
     * @param string $data
     */
    public function setTextHash($data) {
        $this->setProp('comment_text_hash', $data);
    }

    /**
     * Устанавливает уровень вложенности комментария
     *
     * @param int $data
     */
    public function setLevel($data) {

        $this->setProp('comment_level', $data);
    }

    /**
     * Устаналвает объект пользователя
     *
     * @param ModuleUser_EntityUser $data
     */
    public function setUser($data) {

        $this->setProp('user', $data);
    }

    /**
     * Устанавливает объект владельца
     *
     * @param mixed $data
     */
    public function setTarget($data) {

        $this->setProp('target', $data);
    }

    /**
     * Устанавливает объект голосования
     *
     * @param ModuleVote_EntityVote $data
     */
    public function setVote($data) {

        $this->setProp('vote', $data);
    }

    /**
     * Устанавливает факт нахождения комментария в избранном у текущего пользователя
     *
     * @param bool $data
     */
    public function setIsFavourite($data) {

        $this->setProp('comment_is_favourite', $data);
    }

    /**
     * Устанавливает количество избранного
     *
     * @param int $data
     */
    public function setCountFavourite($data) {

        $this->setProp('comment_count_favourite', $data);
    }

    public function getTargetBlog() {

        if (($oTopic = $this->getTarget()) && ($oBlog = $oTopic->getBlog())) {
            return $oBlog;
        }
        return null;
    }

    /**
     * Сколько секунд осталось до конца редактирования
     *
     * @param bool $bFormat
     *
     * @return int
     */
    public function getEditTime($bFormat = false) {

        if (Config::Get('module.comment.edit.enable') && ($oUser = E::User()) && ($oUser->getId() == $this->getUserId())) {
            $sDateTime = F::DateTimeAdd($this->GetCommentDate(), Config::Get('module.comment.edit.enable'));
            $sNow = date('Y-m-d H:i:s');
            if ($sNow < $sDateTime) {
                $nRest = F::DateDiffSeconds($sNow, $sDateTime);
                if (!$bFormat) {
                    return $nRest;
                }
                if ($nRest < 60) {
                    return sprintf('%2d sec', $nRest);
                }
                $nS = $nRest % 60;
                $nM = ($nRest - $nS) / 60;
                if ($nM < 60) {
                    return sprintf('%2d:%02d', $nM, $nS);
                }
                $nRest = $nM;
                $nM = $nRest % 60;
                $nH = ($nRest - $nM) / 60;
                if ($nH < 24) {
                    return sprintf('%2d:%02d:%02d', $nH, $nM, $nS);
                }
                $nRest = $nH;
                $nH = $nRest % 24;
                $nD = ($nRest - $nH) / 24;
                return sprintf('%3d, %2d:%02d:%02d', $nD, $nH, $nM, $nS);
            }
        }
        return 0;
    }

    /**
     * Может ли комментарий редактироваться
     *
     * @param bool|null $bByAuthor - Check for author of comments or for other users only
     *
     * @return bool
     */
    public function isEditable($bByAuthor = false) {

        if ($this->getTargetType() != 'talk' && ($oUser = $this->User_GetUserCurrent())) {
            if ($bByAuthor === false || is_null($bByAuthor)) {
                // Administrator or user who have rights to edit
                if ($oUser->isAdministrator()) {
                    return true;
                }
                // User who has rights to edit in this blog
                if (($oBlog = $this->getTargetBlog()) && $this->ACL_CheckBlogEditComment($oBlog, $oUser)) {
                    return true;
                }
            }
            if ($bByAuthor === true || is_null($bByAuthor)) {
                // author of comment can edit comment in time limit
                if (($oUser->GetId() == $this->getUserId()) && Config::Get('module.comment.edit.enable') && !$this->getDelete()) {
                    return $this->getEditTime() ? true : false;
                }
            }
        }
        return false;
    }

    /**
     * Может ли комментарий быть удален
     *
     * @return bool
     */
    public function isDeletable() {

        if ($this->getTargetType() != 'talk' && ($oUser = $this->User_GetUserCurrent())) {
            if ($oUser->isAdministrator()) {
                return true;
            }
            if (($oBlog = $this->getTargetBlog()) && $this->ACL_CheckBlogDeleteComment($oBlog, $oUser)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Вышло ли время для голосования
     *
     * @return bool
     */
    public function isVoteExpired() {

        $nTimeLimit = Config::Get('acl.vote.comment.limit_time');
        if (!$nTimeLimit) {
            return false;
        }
        return strtotime($this->getDate()) < time() - F::ToSeconds($nTimeLimit);
    }

}

// EOF