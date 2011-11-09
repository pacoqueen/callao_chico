<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class listsViewLists extends JView
{
	function display($tpl = null)
	{
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function listing(){
		global $Itemid;
		$app =& JFactory::getApplication();
		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();
		if(empty($menu) AND !empty($Itemid)){
			$menus->setActive($Itemid);
			$menu	= $menus->getItem($Itemid);
		}
		$pathway	=& $app->getPathway();
		$pathway->addItem(JText::_('MAILING_LISTS'));
		$listsClass = acymailing::get('class.list');
		$allLists = $listsClass->getLists();
		if(acymailing::level(1)){
			$allLists = $listsClass->onlyCurrentLanguage($allLists);
		}
		$myItem = empty($Itemid) ? '' : '&Itemid='.$Itemid;
		$this->assignRef('rows',$allLists);
		$this->assignRef('item',$myItem);
	}
}