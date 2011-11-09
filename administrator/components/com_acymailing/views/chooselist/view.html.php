<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class chooselistViewchooselist extends JView
{
	function display($tpl = null)
	{
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function listing(){
		$listClass = acymailing::get('class.list');
		$rows = $listClass->getLists();
		$selectedLists = JRequest::getVar('values','','','string');
		if(strtolower($selectedLists) == 'all'){
			foreach($rows as $id => $oneRow){
				$rows[$id]->selected = true;
			}
		}elseif(!empty($selectedLists)){
			$selectedLists = explode(',',$selectedLists);
			foreach($rows as $id => $oneRow){
				if(in_array($oneRow->listid,$selectedLists)){
					$rows[$id]->selected = true;
				}
			}
		}
		$fieldName = JRequest::getString('task');
		$controlName = JRequest::getString('control','params');
		$this->assignRef('rows',$rows);
		$this->assignRef('selectedLists',$selectedLists);
		$this->assignRef('fieldName',$fieldName);
		$this->assignRef('controlName',$controlName);
	}
	function customfields(){
		$fieldsClass = acymailing::get('class.fields');
		$fake=null;
		$rows = $fieldsClass->getFields('module',$fake);
		$selected = JRequest::getVar('values','','','string');
		$selectedvalues = explode(',',$selected);
		foreach($rows as $id => $oneRow){
			if(in_array($oneRow->namekey,$selectedvalues)){
				$rows[$id]->selected = true;
			}
		}
		$this->assignRef('fieldsClass',$fieldsClass);
		$this->assignRef('rows',$rows);
		$controlName = JRequest::getString('control','params');
		$this->assignRef('controlName',$controlName);
	}
}