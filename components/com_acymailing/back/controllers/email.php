<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class EmailController extends acymailingController{
	function test(){
		$this->store();
		$mailHelper = acymailing::get('helper.mailer');
		$user = JFactory::getUser();
		$receiver = $user->email;
		$mailid = acymailing::getCID('mailid');
		$mailHelper->report = false;
		$result = $mailHelper->sendOne($mailid,$receiver);
		acymailing::display($mailHelper->reportMessage, $result ? 'success' : 'error');
		return $this->edit();
	}
	function store(){
		JRequest::checkToken() or die( 'Invalid Token' );
		$oldMailid = acymailing::getCID('mailid');
		$mailClass = acymailing::get('class.mail');
		if($mailClass->saveForm()){
				$data = JRequest::getVar('data');
				$type = @$data['mail']['type'];
				if(!empty($type) AND in_array($type,array('unsub','welcome'))){
					$subject = addslashes($data['mail']['subject']);
					$mailid = JRequest::getInt('mailid');
					if($type == 'unsub'){
						$js = "var mydrop = window.top.document.getElementById('datalistunsubmailid'); ";
						$js .= "var type = 'unsub';";
					}else{ //type=welcome
						$js = "var mydrop = window.top.document.getElementById('datalistwelmailid'); ";
						$js .= "var type = 'welcome';";
					}
					if(empty($oldMailid)){
						$js .= 'var optn = document.createElement("OPTION");';
						$js .= "optn.text = '[$mailid] $subject'; optn.value = '$mailid';";
						$js .= 'mydrop.options.add(optn);';
						$js .= 'lastid = 0; while(mydrop.options[lastid+1]){lastid = lastid+1;} mydrop.selectedIndex = lastid;';
						$js .= 'window.top.changeMessage(type,'.$mailid.');';
					}else{
						$js .= "lastid = 0; notfound = true; while(notfound && mydrop.options[lastid]){if(mydrop.options[lastid].value == $mailid){mydrop.options[lastid].text = '[$mailid] $subject';notfound = false;} lastid = lastid+1;}";
					}
					$doc =& JFactory::getDocument();
					$doc->addScriptDeclaration( $js );
				}
			acymailing::display(JText::_( 'JOOMEXT_SUCC_SAVED' ), 'success');
		}else{
			acymailing::display(JText::_( 'ERROR_SAVING' ), 'error');
		}
	}//endfct store
}//endclass