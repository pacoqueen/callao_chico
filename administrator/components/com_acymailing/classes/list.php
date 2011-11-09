<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class listClass extends acymailingClass{
	var $tables = array('listsub','listcampaign','listmail','list');
	var $pkey = 'listid';
	var $namekey = 'alias';
	var $type = 'list';
	var $newlist = false;
	function getLists($index = ''){
		$query = 'SELECT * FROM '.acymailing::table('list').' WHERE type = \''.$this->type.'\' ORDER BY ordering ASC';
		$this->database->setQuery($query);
		return $this->database->loadObjectList($index);
	}
	function get($listid){
		$query = 'SELECT a.*, b.name as creatorname, b.username, b.email FROM '.acymailing::table('list').' as a LEFT JOIN '.acymailing::table('users',false).' as b on a.userid = b.id WHERE listid = '.intval($listid).' LIMIT 1';
		$this->database->setQuery($query);
		return $this->database->loadObject();
	}
	function saveForm(){
		$app =& JFactory::getApplication();
		$list = null;
		$list->listid = acymailing::getCID('listid');
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		foreach($formData['list'] as $column => $value){
			if($app->isAdmin() OR $this->allowedField('list',$column)){
				acymailing::secureField($column);
				$list->$column = strip_tags($value);
			}
		}
		$list->description = JRequest::getVar('editor_description','','','string',JREQUEST_ALLOWRAW);
		$listid = $this->save($list);
		if(!$listid) return false;
		if(empty($list->listid)){
			$orderClass = acymailing::get('helper.order');
			$orderClass->pkey = 'listid';
			$orderClass->table = 'list';
			$orderClass->groupMap = 'type';
			$orderClass->groupVal = empty($list->type) ? $this->type : $list->type;
			$orderClass->reOrder();
			$this->newlist = true;
		}
		if(!empty($formData['listcampaign'])){
			$affectedLists = array();
			foreach($formData['listcampaign'] as $affectlistid => $receiveme){
				if(!empty($receiveme)){
					$affectedLists[] = $affectlistid;
				}
			}
			$listCampaignClass = acymailing::get('class.listcampaign');
			$listCampaignClass->save($listid,$affectedLists);
		}
		JRequest::setVar( 'listid', $listid);
		return true;
	}
	function save($list){
		if(empty($list->listid)){
			if(empty($list->userid)){
				$user	=& JFactory::getUser();
				$list->userid = $user->id;
			}
			if(empty($list->alias)) $list->alias = $list->name;
		}
		if(isset($list->alias)){
			if(empty($list->alias)) $list->alias = $list->name;
			$list->alias = JFilterOutput::stringURLSafe(trim($list->alias));
		}
		if(empty($list->listid)){
			$status = $this->database->insertObject(acymailing::table('list'),$list);
		}else{
			$status = $this->database->updateObject(acymailing::table('list'),$list,'listid');
		}
		if($status) return empty($list->listid) ? $this->database->insertid() : $list->listid;
		return false;
	}
	function onlyCurrentLanguage($lists){
		$currentLanguage = JFactory::getLanguage();
		$currentLang = strtolower($currentLanguage->getTag());
		$newLists = array();
		foreach($lists as $id => $oneList){
			if($oneList->languages == 'all' OR in_array($currentLang,explode(',',$oneList->languages))){
				$newLists[$id] = $oneList;
			}
		}
		return $newLists;
	}
}