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
/**
 * Объект типа контента
 *
 * @package modules.topic
 * @since 1.0
 */
class ModuleTopic_EntityContent extends Entity {

	protected $aFields=null;

	protected $aExtra=null;

	public function getFields(){
		if(is_null($this->aFields)){
			$aFilter=array();
			$aFilter['content_id']=$this->getContentId();
			$this->aFields= $this->Topic_getContentFields($aFilter);
		}
		return $this->aFields;
	}

	public function setFields($data){
		$this->aFields=$data;
	}

	public function isAllow($sAllow){
		if($this->getExtraValue($sAllow)){
			return $this->getExtraValue($sAllow);
		}
		return false;
	}

	/**
	 * Возвращает сериализованные строку дополнительный данных типа контента
	 *
	 * @return string
	 */
	public function getExtra() {
		return $this->_getDataOne('content_config') ? $this->_getDataOne('content_config') : serialize('');
	}

	/**
	 * Извлекает сериализованные данные
	 */
	protected function extractExtra() {
		if (is_null($this->aExtra)) {
			$this->aExtra=@unserialize($this->getExtra());
		}
	}

	/**
	 * Устанавливает значение нужного параметра
	 *
	 * @param string $sName	Название параметра/данных
	 * @param mixed $data	Данные
	 */
	public function setExtraValue($sName,$data) {
		$this->extractExtra();
		$this->aExtra[$sName]=$data;
		$this->setExtra($this->aExtra);
	}
	/**
	 * Извлекает значение параметра
	 *
	 * @param string $sName	Название параметра
	 * @return null|mixed
	 */
	public function getExtraValue($sName) {
		$this->extractExtra();
		if (isset($this->aExtra[$sName])) {
			return $this->aExtra[$sName];
		}
		return null;
	}

	/**
	 * Устанавливает сериализованную строчку дополнительных данных
	 *
	 * @param string $data
	 */
	public function setExtra($data) {
		$this->_aData['content_config']=serialize($data);
	}

    /**
     * Проверяет доступность на создание текущего типа контента
     */
    public function isAccessible(){
        if ($oUser = $this->User_GetUserCurrent()) {
            if($this->getContentAccess() == ModuleTopic::CONTENT_ACCESS_ONLY_ADMIN && !$oUser->isAdministrator()){
                return false;
            } else {
                return true;
            }
        }
        return false;

    }
	
}

// EOF