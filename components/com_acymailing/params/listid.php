<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
if(version_compare(JVERSION,'1.6.0','<')){
	class JElementListid extends JElement
	{
		function fetchElement($name, $value, &$node, $control_name)
		{
			include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php');
			$listType = acymailing::get('type.lists');
			array_shift($listType->values);
			return $listType->display($control_name.'[listid]',(int) $value,false);
		}
	}
}else{
	class JFormFieldListid extends JFormField
	{
		var $type = 'listid';
		function getInput() {
			include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php');
			$listType = acymailing::get('type.lists');
			array_shift($listType->values);
			return $listType->display($this->name,(int) $this->value,false);
		}
	}
}