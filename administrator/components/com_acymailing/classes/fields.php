<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class fieldsClass extends acymailingClass{
	var $tables = array('fields');
	var $pkey = 'fieldid';
	var $errors = array();
	var $prefix = 'field_';
	var $suffix = '';
	var $excludeValue = array();
	function getFields($area,&$user){
		$where = array();
		$where[] = 'a.`published` = 1';
		if($area == 'backend'){
			$where[] = 'a.`backend` = 1';
			$where[] = 'a.`core` = 0';
		}elseif($area == 'frontcomp'){
			$where[] = 'a.`frontcomp` = 1';
		}elseif($area != 'module'){
			return false;
		}
		$this->database->setQuery('SELECT * FROM `#__acymailing_fields` as a WHERE '.implode(' AND ',$where).' ORDER BY a.`ordering` ASC');
		$fields = $this->database->loadObjectList('namekey');
		foreach($fields as $namekey => $field){
			if(!empty($fields[$namekey]->options)){
				$fields[$namekey]->options = unserialize($fields[$namekey]->options);
			}
			if(!empty($field->value)){
				$fields[$namekey]->value = $this->explodeValues($fields[$namekey]->value);
			}
			if(empty($user->subid)) $user->$namekey = $field->default;
		}
		return $fields;
	}
	function getFieldName($field){
		return '<label for="'.$this->prefix.$field->namekey.$this->suffix.'">'.$this->trans($field->fieldname).'</label>';
	}
	function trans($name){
		if(preg_match('#^[A-Z_]*$#',$name)){
			return JText::_($name);
		}
		return $name;
	}
	function listing($field,$value){
		return $value;
	}
	function explodeValues($values){
		$allValues = explode("\n",$values);
		$returnedValues = array();
		foreach($allValues as $id => $oneVal){
			$line = explode('::',trim($oneVal));
			$var = @$line[0];
			$val = @$line[1];
			if(strlen($val)<1) continue;
			$obj = null;
			$obj->value = $val;
			for($i=2;$i<count($line);$i++){
				$obj->{$line[$i]} = 1;
			}
			$returnedValues[$var] = $obj;
		}
		return $returnedValues;
	}
}