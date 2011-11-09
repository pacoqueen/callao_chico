<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class ListController extends acymailingController{
	var $pkey = 'listid';
	var $table = 'list';
	var $groupMap = 'type';
	var $groupVal = 'list';
	var $aclCat = 'lists';
	function store(){
		if(!$this->isAllowed($this->aclCat,'manage')) return;
		JRequest::checkToken() or die( 'Invalid Token' );
		$app =& JFactory::getApplication();
		$listClass = acymailing::get('class.list');
		$status = $listClass->saveForm();
		if($status){
			$app->enqueueMessage(JText::_( 'JOOMEXT_SUCC_SAVED' ), 'message');
			if($listClass->newlist){
				$listid = JRequest::getInt('listid');
				$app->enqueueMessage('<a href="index.php?option=com_acymailing&ctrl=filter&listid='.$listid.'">'.JText::sprintf( 'SUBSCRIBE_LIST').'</a>', 'message');
			}
		}else{
			$app->enqueueMessage(JText::_( 'ERROR_SAVING' ), 'error');
			if(!empty($listClass->errors)){
				foreach($listClass->errors as $oneError){
					$app->enqueueMessage($oneError, 'error');
				}
			}
		}
	}
	function remove(){
		if(!$this->isAllowed($this->aclCat,'delete')) return;
		$app =& JFactory::getApplication();
		JRequest::checkToken() or die( 'Invalid Token' );
		$listIds = JRequest::getVar( 'cid', array(), '', 'array' );
		$subscriberObject = acymailing::get('class.list');
		$num = $subscriberObject->delete($listIds);
		$app->enqueueMessage(JText::sprintf('SUCC_DELETE_ELEMENTS',$num), 'message');
		JRequest::setVar( 'layout', 'listing'  );
		return parent::display();
	}
}