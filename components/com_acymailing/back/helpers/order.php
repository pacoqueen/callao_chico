<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class orderHelper{
	var $table = '';
	var $pkey = '';
	var $groupMap = '';
	var $groupVal = '';
	function order($down = true){
		if($down){
			$sign = '>';
			$dir = 'ASC';
		}else{
			$sign = '<';
			$dir = 'DESC';
		}
		$ids = JRequest::getVar( 'cid', array(), '', 'array' );
		$orders = JRequest::getVar( 'order', array(), '', 'array' );
		$id = (int) $ids[0];
		$pkey = $this->pkey;
		$database =& JFactory::getDBO();
		$query = 'SELECT a.ordering,a.'.$pkey.' FROM '.acymailing::table($this->table).' as b, '.acymailing::table($this->table).' as a';
		$query .= ' WHERE a.ordering '.$sign.' b.ordering AND b.'.$pkey.' = '.$id;
		if(!empty($this->groupMap)) $query .= ' AND a.'.$this->groupMap.' = '.$database->Quote($this->groupVal);
		$query .= ' ORDER BY a.ordering '.$dir.' LIMIT 1';
		$database->setQuery($query);
		$secondElement = $database->loadObject();
		if(empty($secondElement)) return false;
		$firstElement = null;
		$firstElement->$pkey = $id;
		$firstElement->ordering = $secondElement->ordering;
		if($down)$secondElement->ordering--;
		else $secondElement->ordering++;
		$status1 = $database->updateObject(acymailing::table($this->table),$firstElement,$pkey);
		$status2 = $database->updateObject(acymailing::table($this->table),$secondElement,$pkey);
		$status = $status1 && $status2;
		if($status){
			$app =& JFactory::getApplication();
			$app->enqueueMessage(JText::_( 'SUCC_MOVED' ), 'message');
		}
		return $status;
	}
	function save(){
		$app =& JFactory::getApplication();
		$pkey = $this->pkey;
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$order	= JRequest::getVar( 'order', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		$database =& JFactory::getDBO();
		$query = 'SELECT `ordering`,`'.$pkey.'` FROM '.acymailing::table($this->table).' WHERE `'.$pkey.'` NOT IN ('.implode(',',$cid).') ';
		if(!empty($this->groupMap)) $query .= ' AND '.$this->groupMap.' = '.$database->Quote($this->groupVal);
		$query .= ' ORDER BY `ordering` ASC';
		$database->setQuery($query);
		$results = $database->loadObjectList($pkey);
		$oldResults = $results;
		asort($order);
		$newOrder = array();
		while(!empty($order) OR !empty($results)){
			$dbElement = reset($results);
			if(empty($dbElement->ordering) OR (!empty($order) AND reset($order) <= $dbElement->ordering)){
				$newOrder[] = $cid[(int)key($order)];
				unset($order[key($order)]);
			}else{
				$newOrder[] = $dbElement->$pkey;
				unset($results[$dbElement->$pkey]);
			}
		}
		$i = 1;
		$status = true;
		$element = null;
		foreach($newOrder as $val){
			$element->$pkey = $val;
			$element->ordering = $i;
			if(!isset($oldResults[$val]) OR $oldResults[$val]->ordering != $i){
				$status = $database->updateObject(acymailing::table($this->table),$element,$pkey) && $status;
			}
			$i++;
		}
		if($status){
			$app->enqueueMessage(JText::_( 'NEW ORDERING SAVED' ), 'message');
		}else{
			$app->enqueueMessage(JText::_( 'ERROR_ORDERING' ), 'error');
		}
		return $status;
	}
	function reOrder(){
		$db =& JFactory::getDBO();
		$query = 'UPDATE '.acymailing::table($this->table).' SET `ordering` = `ordering`+1';
		if(!empty($this->groupMap)) $query .= ' WHERE '.$this->groupMap.' = '.$db->Quote($this->groupVal);
		$db->setQuery($query);
		$db->query();
		$query = 'SELECT `ordering`,`'.$this->pkey.'` FROM '.acymailing::table($this->table);
		if(!empty($this->groupMap)) $query .= ' WHERE '.$this->groupMap.' = '.$db->Quote($this->groupVal);
		$query .= ' ORDER BY `ordering` ASC';
		$db->setQuery($query);
		$results = $db->loadObjectList();
		$i = 1;
		foreach($results as $oneResult){
			if($oneResult->ordering != $i){
				$oneResult->ordering = $i;
				$db->updateObject( acymailing::table($this->table), $oneResult, $this->pkey);
			}
			$i++;
		}
	}
}