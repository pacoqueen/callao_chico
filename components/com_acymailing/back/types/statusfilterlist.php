<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class statusfilterlistType{
	function statusfilterlistType(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '-2', JText::_('NO_SUBSCRIPTION') );
		$this->values[] = JHTML::_('select.option', '-1', JText::_('UNSUBSCRIBERS') );
		$this->values[] = JHTML::_('select.option', '2', JText::_('PENDING_SUBSCRIPTION') );
		$this->values[] = JHTML::_('select.option', '1', JText::_('SUBSCRIBERS') );
	}
	function display($map,$value,$submit = true){
		$onChange = $submit ? 'onchange="document.adminForm.submit( );"' : '';
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" '.$onChange, 'value', 'text', (int) $value );
	}
}