<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class contentType{
	var $onclick = 'updateTag();';
	function contentType(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', "|type:title",JText::_('TITLE_ONLY'));
		$this->values[] = JHTML::_('select.option', "|type:intro",JText::_('INTRO_ONLY'));
		$this->values[] = JHTML::_('select.option', "|type:text",JText::_('FIELD_TEXT'));
		$this->values[] = JHTML::_('select.option', "|type:full",JText::_('FULL_TEXT'));
	}
	function display($map,$value){
		return JHTML::_('select.radiolist', $this->values, $map , 'size="1" onclick="'.$this->onclick.'"', 'value', 'text', $value);
	}
}