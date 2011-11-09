<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class statusfilterType{
	function statusfilterType(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0', JText::_('ALL_STATUS') );
		$this->values[] = JHTML::_('select.option',  '<OPTGROUP>', JText::_( 'ACCEPT_REFUSE' ) );
		$this->values[] = JHTML::_('select.option', '1', JText::_('ACCEPT_EMAIL') );
		$this->values[] = JHTML::_('select.option', '-1', JText::_('REFUSE_EMAIL') );
		$this->values[] = JHTML::_('select.option',  '</OPTGROUP>');
		$this->values[] = JHTML::_('select.option',  '<OPTGROUP>', JText::_( 'SUBSCRIPTION' ) );
		$this->values[] = JHTML::_('select.option', '2', JText::_('PENDING_SUBSCRIPTION') );
		$this->values[] = JHTML::_('select.option',  '</OPTGROUP>');
		$this->values[] = JHTML::_('select.option',  '<OPTGROUP>', JText::_( 'ENABLED_DISABLED' ) );
		$this->values[] = JHTML::_('select.option', '3', JText::_('ENABLED') );
		$this->values[] = JHTML::_('select.option', '-3', JText::_('DISABLED') );
		$this->values[] = JHTML::_('select.option',  '</OPTGROUP>');
	}
	function display($map,$value){
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', (int) $value );
	}
}