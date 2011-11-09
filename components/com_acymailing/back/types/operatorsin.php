<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class operatorsinType{
	function operatorsinType(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'IN',JText::_('ACY_IN'));
		$this->values[] = JHTML::_('select.option', 'NOT IN',JText::_('ACY_NOT_IN'));
	}
	function display($map){
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" size="1"', 'value', 'text');
	}
}