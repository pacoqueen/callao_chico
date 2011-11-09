<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class SubController extends JController{
	function notask(){
		$this->setRedirect(urldecode(JRequest::getVar('redirect','','','string')),'Please enable the Javascript to be able to subscribe','notice');
		return false;
	}
	function display(){
		$moduleId = JRequest::getInt('formid');
		if(empty($moduleId)) return;
		$db =& JFactory::getDBO();
	 	$db->setQuery('SELECT * FROM #__modules WHERE id = '.$moduleId.' AND `module` LIKE \'%acymailing%\' LIMIT 1');
	 	$module = $db->loadObject();
	 	if(empty($module)){ echo 'No module found'; exit; }
		$module->user  	= substr( $module->module, 0, 4 ) == 'mod_' ?  0 : 1;
		$module->name = $module->user ? $module->title : substr( $module->module, 4 );
		$module->style = null;
		$module->module = preg_replace('/[^A-Z0-9_\.-]/i', '', $module->module);
		$params = array();
		echo JModuleHelper::renderModule($module, $params);
	}
	function optin(){
		acymailing::checkRobots();
		$my =& JFactory::getUser();
		$config = acymailing::config();
		$userClass = acymailing::get('class.subscriber');
		$redirectUrl = urldecode(JRequest::getVar('redirect','','','string'));
		$user = null;
		$formData = JRequest::getVar( 'user', array(), '', 'array' );
		if(!empty($formData)){
			$userClass->checkFields($formData,$user);
		}
		$allowUserModifications = (bool) ($config->get('allow_modif','data') == 'all');
		$allowSubscriptionModifications = (bool) ($config->get('allow_modif','data') != 'none');
		if(empty($user->email)){
			$connectedUser = $userClass->identify(true);
			if(!empty($connectedUser->email)){
				$user->email = $connectedUser->email;
				$allowUserModifications = true;
				$allowSubscriptionModifications = true;
			}
		}
		$user->email =  trim($user->email);
		$userHelper = acymailing::get('helper.user');
		if(empty($user->email) || !$userHelper->validEmail($user->email)){
			echo "<script>alert('".JText::_('VALID_EMAIL',true)."'); window.history.go(-1);</script>";
			exit;
		}
		$alreadyExists = $userClass->get($user->email);
		if(!empty($alreadyExists->subid)){
			if(!empty($alreadyExists->userid)) unset($user->name);
			$user->subid = $alreadyExists->subid;
			$currentSubscription = $userClass->getSubscriptionStatus($alreadyExists->subid);
		}else{
			$allowSubscriptionModifications = true;
			$allowUserModifications = true;
			$currentSubscription = array();
		}
		$user->accept = 1;
		if($allowUserModifications){
			$userClass->recordHistory = true;
			$user->subid = $userClass->save($user);
		}
		$myuser = $userClass->get($user->subid);
		if(empty($myuser->subid)){
			echo "<script>alert('Could not save the user'); window.history.go(-1);</script>";
			exit;
		}
		if(empty($myuser->accept)){
			$myuser->accept = 1;
			$userClass->save($myuser);
		}
		$statusAdd = (empty($myuser->confirmed) AND $config->get('require_confirmation',false)) ? 2 : 1;
		$addlists = array();
		$updatelists = array();
		$removelists = array();

		$hiddenlistsstring = JRequest::getVar('hiddenlists','','','string');
		if(!empty($hiddenlistsstring)){
			$hiddenlists = explode(',',$hiddenlistsstring);
			JArrayHelper::toInteger($hiddenlists);
			foreach($hiddenlists as $id => $idOneList){
				if(!isset($currentSubscription[$idOneList])){
					$addlists[$statusAdd][] = $idOneList;
					continue;
				}
				if($currentSubscription[$idOneList]->status == $statusAdd || $currentSubscription[$idOneList]->status == 1) continue;
				$updatelists[$statusAdd][] = $idOneList;
			}
		}
		$visibleSubscription = JRequest::getVar('subscription','','','array');
		if(!empty($visibleSubscription)){
			foreach($visibleSubscription as $idOneList){
				if(empty($idOneList)) continue;
				if(!isset($currentSubscription[$idOneList])){
					$addlists[$statusAdd][] = $idOneList;
					continue;
				}
				if($currentSubscription[$idOneList]->status == $statusAdd || $currentSubscription[$idOneList]->status == 1) continue;
				$updatelists[$statusAdd][] = $idOneList;
			}
		}
		$visiblelistsstring = JRequest::getVar('visiblelists','','','string');
		if(!empty($visiblelistsstring)){
			$visiblelist = explode(',',$visiblelistsstring);
			JArrayHelper::toInteger($visiblelist);
			foreach($visiblelist as $idList){
				if(!in_array($idList,$visibleSubscription) AND !empty($currentSubscription[$idList]) AND $currentSubscription[$idList]->status != '-1'){
					$updatelists['-1'][] = $idList;
				}
			}
		}
		$listsubClass = acymailing::get('class.listsub');
		$status = true;
		$updateMessage = false;
		$insertMessage = false;
		if($allowSubscriptionModifications){
			if(!empty($updatelists)){
				$status = $listsubClass->updateSubscription($myuser->subid,$updatelists) && $status;
				$updateMessage = true;
			}
			if(!empty($addlists)){
				$status = $listsubClass->addSubscription($myuser->subid,$addlists) && $status;
				$insertMessage = true;
			}
		}else{
			$mailClass = acymailing::get('helper.mailer');
			$mailClass->checkConfirmField = false;
			$mailClass->checkEnabled = false;
			$mailClass->report = false;
			$mailClass->sendOne('modif',$myuser->subid);
		}
		if($config->get('subscription_message',1)){
			$app =& JFactory::getApplication();
			if($allowSubscriptionModifications){
				if($statusAdd == 2){
					if(empty($redirectUrl)) acymailing::display(JText::_('CONFIRMATION_SENT'),'info');
					else $app->enqueueMessage(JText::_('CONFIRMATION_SENT'));
				}else{
					if($insertMessage){
						if(empty($redirectUrl)) acymailing::display(JText::_('SUBSCRIPTION_OK'),'info');
						else $app->enqueueMessage(JText::_('SUBSCRIPTION_OK'));
					}elseif($updateMessage){
						if(empty($redirectUrl)) acymailing::display(JText::_('SUBSCRIPTION_UPDATED_OK'),'info');
						else $app->enqueueMessage(JText::_('SUBSCRIPTION_UPDATED_OK'));
					}else{
						if(empty($redirectUrl)) acymailing::display(JText::_('ALREADY_SUBSCRIBED'),'info');
						else $app->enqueueMessage(JText::_('ALREADY_SUBSCRIBED'));
					}
				}
			}else{
				if(empty($redirectUrl)) acymailing::display(JText::_('IDENTIFICATION_SENT'),'warning');
				else $app->enqueueMessage(JText::_( 'IDENTIFICATION_SENT' ), 'notice');
			}
		}
		$this->_closepop($redirectUrl);
		$this->setRedirect($redirectUrl);
		return true;
	}
	function _closepop($redirectUrl){
		if(empty($redirectUrl) OR !JRequest::getInt('closepop')) return;
		echo '<script type="text/javascript" language="javascript">
			       	try{
			       		window.parent.document.getElementById(\'sbox-window\').close();
			      	}catch(err){
			      		try{
			      			window.parent.SqueezeBox.close();
			      		}catch(err){ }
			      	}
			      	window.parent.document.location.href=\''.str_replace('&amp;','&',$redirectUrl).'\';
				</script>';
		$app =& JFactory::getApplication();
		$messages = $app->getMessageQueue();
		if(!empty($messages)){
			$session =& JFactory::getSession();
			$session->set('application.queue', $messages);
		}
		exit;
	}
	function optout(){
		acymailing::checkRobots();
		$config = acymailing::config();
		$app =& JFactory::getApplication();
		$userClass = acymailing::get('class.subscriber');
		$my =& JFactory::getUser();
		$redirectUrl = urldecode(JRequest::getString('redirectunsub'));
		if(!empty($redirectUrl)) $this->setRedirect($redirectUrl);
		$formData = JRequest::getVar( 'user', array(), '', 'array' );
		$email = trim(strip_tags($formData['email']));
		if(empty($email)){
			if(!empty($my->email)){
				$email = $my->email;
			}
		}
		$userHelper = acymailing::get('helper.user');
		if(empty($email) || !$userHelper->validEmail($email)){
			echo "<script>alert('".JText::_('VALID_EMAIL',true)."'); window.history.go(-1);</script>";
			exit;
		}
		$alreadyExists = $userClass->get($email);
		if(empty($alreadyExists->subid)){
			if(empty($redirectUrl)) acymailing::display(JText::sprintf('NOT_IN_LIST',$email),'warning');
			else $app->enqueueMessage(JText::sprintf('NOT_IN_LIST',$email),'notice');
			return $this->_closepop($redirectUrl);
		}
		if($config->get('allow_modif','data') == 'none' AND (empty($my->email) || $my->email != $email)){
			$mailClass = acymailing::get('helper.mailer');
			$mailClass->checkConfirmField = false;
			$mailClass->checkEnabled = false;
			$mailClass->report = false;
			$mailClass->sendOne('modif',$alreadyExists->subid);
			if(empty($redirectUrl)) acymailing::display(JText::_( 'IDENTIFICATION_SENT' ),'warning');
			else $app->enqueueMessage(JText::_( 'IDENTIFICATION_SENT' ), 'notice');
			return $this->_closepop($redirectUrl);
		}
		$visibleSubscription = JRequest::getVar('subscription','','','array');
		$currentSubscription = $userClass->getSubscriptionStatus($alreadyExists->subid);
		$hiddenSubscription = explode(',',JRequest::getVar('hiddenlists','','','string'));
		$updatelists = array();
		$removeSubscription = array_merge($visibleSubscription,$hiddenSubscription);
		foreach($removeSubscription as $idList){
			if(!empty($currentSubscription[$idList]) AND $currentSubscription[$idList]->status != '-1'){
				$updatelists[-1][] = $idList;
			}
		}
		if(!empty($updatelists)){
			$listsubClass = acymailing::get('class.listsub');
			$listsubClass->updateSubscription($alreadyExists->subid,$updatelists);
			if($config->get('unsubscription_message',1)){
				if(empty($redirectUrl)) acymailing::display(JText::_('UNSUBSCRIPTION_OK'),'info');
				else $app->enqueueMessage(JText::_('UNSUBSCRIPTION_OK'));
			}
		}elseif($config->get('unsubscription_message',1)){
			if(empty($redirectUrl)) acymailing::display(JText::_('UNSUBSCRIPTION_NOT_IN_LIST'),'info');
			else $app->enqueueMessage(JText::_('UNSUBSCRIPTION_NOT_IN_LIST'));
		}
		return $this->_closepop($redirectUrl);
	}
}