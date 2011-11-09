<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class listsmailType{
	var $type = 'news';
	function load(){
		$query = 'SELECT b.name,a.listid,count(distinct a.mailid) as totalmail FROM '.acymailing::table('mail').' as c';
		$query .= ' LEFT JOIN '.acymailing::table('listmail').' as a ON a.mailid = c.mailid';
		$query .= ' LEFT JOIN '.acymailing::table('list').' as b on a.listid = b.listid';
		$query .= ' WHERE c.type = \''.$this->type.'\' GROUP BY a.listid ORDER BY b.ordering ASC';
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$lists = $db->loadObjectList();
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0', JText::_('ALL_LISTS') );
		foreach($lists as $onelist){
			if(empty($onelist->listid)) continue;
			$this->values[] = JHTML::_('select.option', $onelist->listid, $onelist->name.' ( '.$onelist->totalmail.' )' );
		}
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', (int) $value );
	}
}