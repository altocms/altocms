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

class PluginCategories_ModuleCategories_EntityCategory extends EntityORM {

	protected $sPrimaryKey = 'category_id';
	protected $aBlogRel = null;

	protected $aRelations=array(

	);

	public function getUrl(){
		return Router::GetPath('category').$this->getCategoryUrl();
	}

	protected function getRelation() {
		if (is_null($this->aBlogRel)) {
			$aRelations=$this->PluginCategories_Categories_GetItemsByFilter(array('category_id'=>$this->getCategoryId()),'PluginCategories_ModuleCategories_EntityCategoryRel');
			$this->aBlogRel=array();
			foreach($aRelations as $oRel) {
				$this->aBlogRel[]=$oRel->getBlogId();
			}
		}
	}

	public function getBlogIds() {
		$this->getRelation();
		return $this->aBlogRel;
	}

	public function getTopics($type='new',$iPage=1,$iPerPage=3) {

		$aIds=$this->getBlogIds();

		if(!is_null($aIds) && is_array($aIds) && count($aIds)){

			$aFilter=array(
				'blog_type' => array(
					'open',
				),
				'topic_publish' => 1,
				'blog_id'=>$aIds
			);

			if($type=='popular'){
				$aFilter['topic_rating']  = array(
					'value' => Config::Get('module.blog.index_good'),
					'type'  => 'top',
					'publish_index'  => 1,
				);
			}
			$aReturn = $this->Topic_GetTopicsByFilter($aFilter,$iPage,$iPerPage,array('user','blog'));
			return $aReturn['collection'];
			
		}
		
		return array();
	}

}
?>