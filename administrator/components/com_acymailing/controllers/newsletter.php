<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class NewsletterController extends acymailingController{
	var $aclCat = 'newsletters';
	function replacetags(){
		if(!$this->isAllowed($this->aclCat,'manage')) return;
		$this->store();
		return $this->edit();
	}
	function copy(){
		if(!$this->isAllowed($this->aclCat,'manage')) return;
		JRequest::checkToken() or die( 'Invalid Token' );
		$cids = JRequest::getVar( 'cid', array(), '', 'array' );
		$db =& JFactory::getDBO();
		$time = time();
		$my =& JFactory::getUser();
		$creatorId = intval($my->id);
		foreach($cids as $oneMailid){
			$query = 'INSERT INTO `#__acymailing_mail` (`subject`, `body`, `altbody`, `published`, `created`, `fromname`, `fromemail`, `replyname`, `replyemail`, `type`, `visible`, `userid`, `alias`, `attach`, `html`, `tempid`, `key`, `frequency`, `params`)';
			$query .= " SELECT CONCAT('copy_',`subject`), `body`, `altbody`, `published`, '.$time.', `fromname`, `fromemail`, `replyname`, `replyemail`, `type`, `visible`, '.$creatorId.', `alias`, `attach`, `html`, `tempid`, ".$db->Quote(md5(rand(1000,999999))).', `frequency`, `params` FROM `#__acymailing_mail` WHERE `mailid` = '.(int) $oneMailid;
			$db->setQuery($query);
			$db->query();
			$newMailid = $db->insertid();
			$db->setQuery('INSERT IGNORE INTO `#__acymailing_listmail` (`listid`,`mailid`) SELECT `listid`,'.$newMailid.' FROM `#__acymailing_listmail` WHERE `mailid` = '.(int) $oneMailid);
			$db->query();
		}
		return $this->listing();
	}
	function store(){
		if(!$this->isAllowed($this->aclCat,'manage')) return;
		JRequest::checkToken() or die( 'Invalid Token' );
		$app =& JFactory::getApplication();
		$mailClass = acymailing::get('class.mail');
		$status = $mailClass->saveForm();
		if($status){
			$app->enqueueMessage(JText::_( 'JOOMEXT_SUCC_SAVED' ), 'message');
		}else{
			$app->enqueueMessage(JText::_( 'ERROR_SAVING' ), 'error');
			if(!empty($mailClass->errors)){
				foreach($mailClass->errors as $oneError){
					$app->enqueueMessage($oneError, 'error');
				}
			}
		}
	}
	function unschedule(){
		if(!$this->isAllowed($this->aclCat,'schedule')) return;
		$mailid = acymailing::getCID('mailid');
		(JRequest::checkToken() && !empty($mailid)) or die( 'Invalid Token' );
		$mail = null;
		$mail->mailid = $mailid;
		$mail->senddate = 0;
		$mail->published = 0;
		$mailClass = acymailing::get('class.mail');
		$mailClass->save($mail);
		$app =& JFactory::getApplication();
		$app->enqueueMessage(JText::_('SUCC_UNSCHED'));
		return $this->preview();
	}
	function remove(){
		if(!$this->isAllowed($this->aclCat,'delete')) return;
		JRequest::checkToken() or die( 'Invalid Token' );
		$cids = JRequest::getVar( 'cid', array(), '', 'array' );
		$class = acymailing::get('class.mail');
		$num = $class->delete($cids);
		$app =& JFactory::getApplication();
		$app->enqueueMessage(JText::sprintf('SUCC_DELETE_ELEMENTS',$num), 'message');
		return $this->listing();
	}
	function savepreview(){
		$this->store();
		return $this->preview();
	}
	function preview(){
		JRequest::setVar( 'layout', 'preview'  );
		JRequest::setVar('hidemainmenu',1);
		return parent::display();
	}
	function sendtest(){
		$this->_sendtest();
		return $this->preview();
	}
	function _sendtest(){
		JRequest::checkToken() or die( 'Invalid Token' );
		$mailid = acymailing::getCID('mailid');
		$receiver_type = JRequest::getVar('receiver_type','','','string');
		if(empty($mailid) OR empty($receiver_type)) return false;
		$mailer = acymailing::get('helper.mailer');
		$mailer->forceVersion = JRequest::getVar('test_html',1,'','int');
		$mailer->autoAddUser = true;
		$mailer->SMTPDebug = 1;
		$mailer->checkConfirmField = false;
		$receivers = array();
		if($receiver_type == 'user'){
			$user = JFactory::getUser();
			$receivers[] = $user->email;
		}elseif($receiver_type == 'other'){
			$receivers[] = JRequest::getVar('test_email','','','string');
		}else{
			$gid = substr($receiver_type,strpos($receiver_type,'_')+1);
			if(empty($gid)) return false;
			$db =& JFactory::getDBO();
			$db->setQuery('SELECT email from '.acymailing::table('users',false).' WHERE gid = '.intval($gid));
			$receivers = $db->loadResultArray();
		}
		if(empty($receivers)){
			$app =& JFactory::getApplication();
			$app->enqueueMessage(JText::_('NO_SUBSCRIBER'), 'notice');
			return false;
		}
		$result = true;
		foreach($receivers as $receiver){
			$result = $mailer->sendOne($mailid,$receiver) && $result;
		}
		return $result;
	}
}