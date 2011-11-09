<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class listsType{
	function listsType(){
		$query = 'SELECT name,listid,color,description FROM '.acymailing::table('list').' WHERE type = \'list\' ORDER BY ordering ASC';
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$this->data = $db->loadObjectList('listid');
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0', JText::_('ALL_LISTS') );
		foreach($this->data as $onelist){
			$this->values[] = JHTML::_('select.option', $onelist->listid, $onelist->name );
		}
	}
	function display($map,$value,$js = true){
		$onchange = $js ? 'onchange="document.adminForm.submit( );"' : '';
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" '.$onchange, 'value', 'text', (int) $value,str_replace(array('[',']'),array('_',''),$map) );
	}
	function getData(){
		return $this->data;
	}
}