<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class SubscriberController extends acymailingController{
	var $allowedInfo = array();
	var $aclCat = 'subscriber';
	function choose(){
		if(!$this->isAllowed('subscriber','view')) return;
		JRequest::setVar( 'layout', 'choose'  );
		return parent::display();
	}
	function export(){
		if(!$this->isAllowed('subscriber','export')) return;
		$cids = JRequest::getVar('cid');
		if(!empty($cids)){
			$_SESSION['acymailing']['exportusers'] = $cids;
			$this->setRedirect(acymailing::completeLink('data&task=export&sessionvalues=1',false,true));
		}else{
			$this->setRedirect(acymailing::completeLink('data&task=export',false,true));
		}
	}
	function store(){
		if(!$this->isAllowed('subscriber','manage')) return;
		JRequest::checkToken() or die( 'Invalid Token' );
		$app =& JFactory::getApplication();
		$subscriberClass = acymailing::get('class.subscriber');
		$status = $subscriberClass->saveForm();
		if($status){
			$app->enqueueMessage(JText::_( 'JOOMEXT_SUCC_SAVED' ), 'message');
		}else{
			$app->enqueueMessage(JText::_( 'ERROR_SAVING' ), 'error');
			if(!empty($subscriberClass->errors)){
				foreach($subscriberClass->errors as $oneError){
					$app->enqueueMessage($oneError, 'error');
				}
			}
		}
	}
	function remove(){
		if(!$this->isAllowed('subscriber','delete')) return;
		JRequest::checkToken() or die( 'Invalid Token' );
		$subscriberIds = JRequest::getVar( 'cid', array(), '', 'array' );
		$subscriberObject = acymailing::get('class.subscriber');
		$num = $subscriberObject->delete($subscriberIds);
		$app =& JFactory::getApplication();
		$app->enqueueMessage(JText::sprintf('SUCC_DELETE_ELEMENTS',$num), 'message');
		JRequest::setVar( 'layout', 'listing'  );
		return parent::display();
	}
}