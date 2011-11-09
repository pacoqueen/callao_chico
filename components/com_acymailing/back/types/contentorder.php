<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class contentorderType{
	var $onclick = 'updateTag();';
	function contentorderType(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', "|order:id,DESC",JText::_('ACY_ID'));
		$this->values[] = JHTML::_('select.option', "|order:ordering,ASC",JText::_('ACY_ORDERING'));
		$this->values[] = JHTML::_('select.option', "|order:created,DESC",JText::_('CREATED_DATE'));
		$this->values[] = JHTML::_('select.option', "|order:modified,DESC",JText::_('MODIFIED_DATE'));
		$this->values[] = JHTML::_('select.option', "|order:title,ASC",JText::_('FIELD_TITLE'));
	}
	function display($map,$value){
		return JHTML::_('select.genericlist', $this->values, $map , 'size="1" onclick="'.$this->onclick.'"', 'value', 'text', (string) $value);
	}
}