<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
if(version_compare(JVERSION,'1.6.0','<')){
	class JElementHelp extends JElement
	{
		function fetchElement($name, $value, &$node, $control_name)
		{
			JHTML::_('behavior.modal','a.modal');
			if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php')){
				return 'This module can not work without the AcyMailing Component';
			}
			$lang =& JFactory::getLanguage();
			$lang->load(ACYMAILING_COMPONENT,JPATH_SITE);
			$config =& acymailing::config();
			$level = $config->get('level');
			$link = ACYMAILING_HELPURL.$value.'&level='.$level;
			$text = '<a class="modal" title="'.JText::_('ACY_HELP',true).'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><button onclick="return false">'.JText::_('ACY_HELP').'</button></a>';
			return $text;
		}
	}
}else{
	class JFormFieldHelp extends JFormField
	{
		var $type = 'help';
		function getInput() {
           JHTML::_('behavior.modal','a.modal');
			if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php')){
				return 'This module can not work without the AcyMailing Component';
			}
             $lang =& JFactory::getLanguage();
			$lang->load(ACYMAILING_COMPONENT,JPATH_SITE);
			$config =& acymailing::config();
			$level = $config->get('level');
			$link = ACYMAILING_HELPURL.$this->value.'&level='.$level;
			$text = '<a class="modal" title="'.JText::_('ACY_HELP',true).'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><button onclick="return false">'.JText::_('ACY_HELP').'</button></a>';
			return $text;
		}
	}
}