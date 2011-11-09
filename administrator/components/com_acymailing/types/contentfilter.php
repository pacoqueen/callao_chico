<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class contentfilterType{
	var $onclick = 'updateTag();';
	function contentfilterType(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', "",JText::_('ACY_ALL'));
		$this->values[] = JHTML::_('select.option', "|filter:created",JText::_('ONLY_NEW_CREATED'));
		$this->values[] = JHTML::_('select.option', "|filter:modify",JText::_('ONLY_NEW_MODIFIED'));
	}
	function display($map,$value){
		return JHTML::_('select.genericlist', $this->values, $map , 'size="1" onclick="'.$this->onclick.'"', 'value', 'text', (string) $value);
	}
}