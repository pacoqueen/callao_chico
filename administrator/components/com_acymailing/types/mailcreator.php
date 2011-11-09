<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class mailcreatorType{
	var $type = 'news';
	function load(){
		$query = 'SELECT b.name,a.userid,count(distinct a.mailid) as total FROM '.acymailing::table('mail').' as a';
		$query .=' LEFT JOIN '.acymailing::table('users',false).' as b on a.userid = b.id';
		$query .= ' WHERE a.type = \''.$this->type.'\' GROUP BY a.userid ORDER BY b.name ASC';
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$creators = $db->loadObjectList();
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0', JText::_('ALL_CREATORS') );
		foreach($creators as $oneCreator){
			if(!empty($oneCreator->userid)) $this->values[] = JHTML::_('select.option', $oneCreator->userid, $oneCreator->name.' ( '.$oneCreator->total.' )' );
		}
	}
	function display($map,$value){
		$this->load();
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', (int) $value );
	}
}