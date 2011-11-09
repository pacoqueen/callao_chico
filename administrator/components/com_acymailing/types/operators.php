<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class operatorsType{
	function operatorsType(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '<OPTGROUP>',JText::_('ACY_NUMERIC'));
		$this->values[] = JHTML::_('select.option', '=','=');
		$this->values[] = JHTML::_('select.option', '!=','!=');
		$this->values[] = JHTML::_('select.option', '>','>');
		$this->values[] = JHTML::_('select.option', '<','<');
		$this->values[] = JHTML::_('select.option', '>=','>=');
		$this->values[] = JHTML::_('select.option', '<=','<=');
		$this->values[] = JHTML::_('select.option', '</OPTGROUP>');
		$this->values[] = JHTML::_('select.option', '<OPTGROUP>',JText::_('ACY_STRING'));
		$this->values[] = JHTML::_('select.option', 'BEGINS',JText::_('ACY_BEGINS_WITH'));
		$this->values[] = JHTML::_('select.option', 'END',JText::_('ACY_ENDS_WITH'));
		$this->values[] = JHTML::_('select.option', 'CONTAINS',JText::_('ACY_CONTAINS'));
		$this->values[] = JHTML::_('select.option', 'LIKE','LIKE');
		$this->values[] = JHTML::_('select.option', 'NOT LIKE','NOT LIKE');
		$this->values[] = JHTML::_('select.option', '</OPTGROUP>');
		$this->values[] = JHTML::_('select.option', '<OPTGROUP>',JText::_('OTHER'));
		$this->values[] = JHTML::_('select.option', 'IS NULL','IS NULL');
		$this->values[] = JHTML::_('select.option', 'IS NOT NULL','IS NOT NULL');
		$this->values[] = JHTML::_('select.option', '</OPTGROUP>');
	}
	function display($map){
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" size="1"', 'value', 'text');
	}
}