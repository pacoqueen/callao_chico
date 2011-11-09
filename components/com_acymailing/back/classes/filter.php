<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class filterClass extends acymailingClass{
	var $tables = array('filter');
	var $pkey = 'filid';
	var $report = array();
	var $subid;
	function trigger($triggerName){
		if(!acymailing::level(3)) return;
		$config = acymailing::config();
		if(!$config->get('triggerfilter_'.$triggerName)) return;
		$this->database->setQuery("SELECT * FROM `#__acymailing_filter` WHERE `trigger` LIKE '%".$this->database->getEscaped($triggerName,true)."%'");
		$filters = $this->database->loadObjectList();
		if(empty($filters)){
			$newconfig = null;
			$name = 'triggerfilter_'.$triggerName;
			$newconfig->$name = 0;
			$config->save($newconfig);
			return;
		}
		foreach($filters as $oneFilter){
			if(empty($oneFilter->published)) continue;
			if(!empty($oneFilter->filter)) $oneFilter->filter = unserialize($oneFilter->filter);
			if(!empty($oneFilter->action)) $oneFilter->action = unserialize($oneFilter->action);
			$this->execute($oneFilter->filter,$oneFilter->action);
		}
	}
	function execute($filters,$actions){
		JPluginHelper::importPlugin('acymailing');
		$this->dispatcher = &JDispatcher::getInstance();
		$query = new acyQuery();
		if(!empty($this->subid)){
			$subArray = explode(',',trim($this->subid,','));
			JArrayHelper::toInteger($subArray);
			$query->where[] = 'sub.subid IN ('.implode(',',$subArray).')';
		}
		if(!empty($filters['type'])){
			foreach($filters['type'] as $num => $oneType){
				if(empty($oneType)) continue;
				$this->dispatcher->trigger('onAcyProcessFilter_'.$oneType,array(&$query,$filters[$num][$oneType],$num));
			}
		}
		foreach($actions['type'] as $num => $oneType){
			if(empty($oneType)) continue;
			$this->report = array_merge($this->report,$this->dispatcher->trigger('onAcyProcessAction_'.$oneType,array(&$query,$actions[$num][$oneType],$num)));
		}
	}
	function saveForm(){
	    $filter = null;
		$filter->filid = acymailing::getCID('filid');
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		foreach($formData['filter'] as $column => $value){
			acymailing::secureField($column);
			$filter->$column = strip_tags($value);
		}
		$config = acymailing::config();
		$alltriggers = array_keys((array)JRequest::getVar('trigger'));
		$filter->trigger = implode(',',$alltriggers);
		$newConfig = null;
		foreach($alltriggers as $oneTrigger){
			$name = 'triggerfilter_'.$oneTrigger;
			if($config->get($name)) continue;
			$newConfig->$name = 1;
		}
		if(!empty($newConfig)) $config->save($newConfig);
		$data = array('action','filter');
		foreach($data as $oneData){
			$filter->$oneData = array();
			$formData = JRequest::getVar($oneData);
			foreach($formData['type'] as $num => $oneType){
				if(empty($oneType)) continue;
				$filter->{$oneData}['type'][$num] = $oneType;
				$filter->{$oneData}[$num][$oneType] = $formData[$num][$oneType];
			}
			$filter->$oneData = serialize($filter->$oneData);
		}
    	$filid = $this->save($filter);
    	if(!$filid) return false;
	    JRequest::setVar( 'filid', $filid);
	    return true;
	}
	function get($filid){
		$query = 'SELECT a.* FROM #__acymailing_filter as a WHERE a.`filid` = '.intval($filid).' LIMIT 1';
		$this->database->setQuery($query);
		$filter = $this->database->loadObject();
		if(!empty($filter->filter)){
			$filter->filter = unserialize($filter->filter);
		}
		if(!empty($filter->action)){
			$filter->action = unserialize($filter->action);
		}
		if(!empty($filter->trigger)){
			$filter->trigger = array_flip(explode(',',$filter->trigger));
		}
		return $filter;
	}
}
class acyQuery{
	var $leftjoin = array();
	var $where = array();
	var $from = '#__acymailing_subscriber as sub';
	function acyQuery(){
		$this->db = JFactory::getDBO();
	}
	function getQuery($select = array()){
		$query = '';
		if(!empty($select)) $query .= ' SELECT '.implode(',',$select);
		if(!empty($this->from)) $query .= ' FROM '.$this->from;
		if(!empty($this->leftjoin)) $query .= ' LEFT JOIN '.implode(' LEFT JOIN ',$this->leftjoin);
		if(!empty($this->where)) $query .= ' WHERE ('.implode(') AND (',$this->where).')';
		return $query;
	}
	function convertQuery($as,$column,$operator,$value){
		if($operator == 'CONTAINS'){
			$operator = 'LIKE';
			$value = '%'.$value.'%';
		}elseif($operator == 'BEGINS'){
			$operator = 'LIKE';
			$value = $value.'%';
		}elseif($operator == 'END'){
			$operator = 'LIKE';
			$value = '%'.$value;
		}elseif(!in_array($operator,array('IS NULL','IS NOT NULL','NOT LIKE','LIKE','=','!=','>','<','>=','<='))){
			die('Operator not safe : '.$operator);
		}
		 if(strpos($value,'{time}') !== false){
		 	$value = acymailing::replaceDate($value);
		 	$value = strftime('%Y-%m-%d %H:%M:%S',$value);
		 }
		$value = $this->db->Quote($value);
		if(in_array($operator,array('IS NULL','IS NOT NULL'))){
			$value = '';
		}
		return $as.'.`'.acymailing::secureField($column).'` '.$operator.' '.$value;
	}
}