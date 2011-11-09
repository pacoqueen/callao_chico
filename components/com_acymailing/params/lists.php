<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
if(version_compare(JVERSION,'1.6.0','<')){
	class JElementLists extends JElement
	{
		function fetchElement($name, $value, &$node, $control_name)
		{
			JHTML::_('behavior.modal','a.modal');
			$link = 'index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=chooselist&amp;task='.$name.'&amp;values='.$value.'&amp;control='.$control_name;
			$text = '<input class="inputbox" id="'.$control_name.$name.'" name="'.$control_name.'['.$name.']" type="text" size="20" value="'.$value.'">';
			$text .= '<a class="modal" id="link'.$control_name.$name.'" title="'.JText::_('Select one or several Lists').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}"><button onclick="return false">'.JText::_('Select').'</button></a>';
			return $text;
		}
	}
}else{
	class JFormFieldLists extends JFormField
	{
		var $type = 'lists';
		function getInput() {
			JHTML::_('behavior.modal','a.modal');
			$link = 'index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=chooselist&amp;task='.$this->name.'&amp;values='.$this->value.'&amp;control=';
			$text = '<input class="inputbox" id="'.$this->name.'" name="'.$this->name.'" type="text" size="20" value="'.$this->value.'">';
			$text .= '<a class="modal" id="link'.$this->name.'" title="'.JText::_('Select one or several Lists').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}"><button onclick="return false">'.JText::_('Select').'</button></a>';
			return $text;
		}
	}
}