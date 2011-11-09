<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class subscriberClass extends acymailingClass{
	var $tables = array('listsub','userstats','queue','history','subscriber');
	var $pkey = 'subid';
	var $namekey = 'email';
	var $restrictedFields = array('subid','key','confirmed','enabled','ip','userid','created');
	var $errors = array();
	var $checkVisitor = true;
	var $sendConf = true;
	var $requireId = false;
	var $newUser = null;
	var $confirmationSent = false;
	var $sendNotif = true;
	var $sendWelcome = true;
	var $recordHistory = false;
	function save($subscriber){
		$app =& JFactory::getApplication();
		$config = acymailing::config();
		if(isset($subscriber->email)){
			$subscriber->email = strtolower($subscriber->email);
			$userHelper = acymailing::get('helper.user');
			if(!$userHelper->validEmail($subscriber->email)){
				echo "<script>alert('".JText::_('VALID_EMAIL',true)."'); window.history.go(-1);</script>";
				exit;
			}
		}
		if(empty($subscriber->subid)){
			$my = JFactory::getUser();
			if($this->checkVisitor && !$app->isAdmin() && (int) $config->get('allow_visitor',1) != 1 && (empty($my->id) OR strtolower($my->email) != $subscriber->email)){
				echo "<script> alert('".JText::_('ONLY_LOGGED',true)."'); window.history.go(-1);</script>\n";
				exit;
			}
			if(empty($subscriber->email)) return false;
			$subscriber->subid = $this->subid($subscriber->email);
		}
		if(empty($subscriber->subid)){
			if(empty($subscriber->created)) $subscriber->created = time();
			if(empty($subscriber->ip)){
				$ipClass = acymailing::get('helper.user');
				$subscriber->ip = $ipClass->getIP();
			}
			if(empty($subscriber->name)) $subscriber->name = ucwords(str_replace(array('.','_','-'),' ',substr($subscriber->email,0,strpos($subscriber->email,'@'))));
			$subscriber->key = md5(substr($subscriber->email,0,strpos($subscriber->email,'@')).time());
			$notifyUsers = $config->get('notification_created');
			if(!$app->isAdmin() && !empty($notifyUsers) && $this->sendConf){
				$mailer = acymailing::get('helper.mailer');
				$mailer->report = false;
				$mailer->autoAddUser = true;
				$mailer->checkConfirmField = false;
				foreach($subscriber as $map => $value){
					$mailer->addParam('user:'.$map,$value);
				}
				$mailer->addParamInfo();
				$allUsers = explode(',',$notifyUsers);
				foreach($allUsers as $oneUser){
					$mailer->sendOne('notification_created',$oneUser);
				}
			}
			$status = $this->database->insertObject(acymailing::table('subscriber'),$subscriber);
		}else{
			if(count((array)$subscriber) > 1){
				$status = $this->database->updateObject(acymailing::table('subscriber'),$subscriber,'subid');
			}else{
				$status = true;
			}
		}
		if(!$status) return false;
		$subid = empty($subscriber->subid) ? $this->database->insertid() : $subscriber->subid;
		if(!$app->isAdmin()){
			$filterClass = acymailing::get('class.filter');
			$filterClass->subid = $subid;
			$filterClass->trigger((empty($subscriber->subid) ? 'subcreate' : 'subchange'));
		}
		JPluginHelper::importPlugin('acymailing');
		$dispatcher = &JDispatcher::getInstance();
		if(empty($subscriber->subid)){
			$subscriber->subid = $subid;
			$resultsTrigger = $dispatcher->trigger('onAcyUserCreate',array($subscriber));
			$this->recordHistory = true;
			$action = 'created';
		}else{
			$resultsTrigger = $dispatcher->trigger('onAcyUserModify',array($subscriber));
			$action = 'modified';
		}
		if($this->recordHistory){
			$historyClass = acymailing::get('class.acyhistory');
			$historyClass->insert($subscriber->subid,$action);
			$this->recordHistory = false;
		}
		if(!$app->isAdmin() AND $this->sendConf){
			$this->sendConf($subid);
		}
		return $subid;
	}
	function sendConf($subid){
		if($this->confirmationSent) return false;
		$myuser = $this->get($subid);
		$config = acymailing::config();
		if(empty($myuser->confirmed) && $config->get('require_confirmation',false)){
			$mailClass = acymailing::get('helper.mailer');
			$mailClass->checkConfirmField = false;
			$mailClass->checkEnabled = false;
			$mailClass->report = $config->get('confirm_message',0);
			$mailClass->sendOne('confirmation',$myuser);
			$this->confirmationSent = true;
			return true;
		}
		return false;
	}
	function subid($email){
		if(is_numeric($email)){
			$cond = ' userid = '.$email;
		}else{
			$cond = 'email = '.$this->database->Quote(trim($email));
		}
		$this->database->setQuery('SELECT subid FROM '.acymailing::table('subscriber').' WHERE '.$cond);
		return $this->database->loadResult();
	}
	function get($subid){
		$column = is_numeric($subid) ? 'subid' : 'email';
		$this->database->setQuery('SELECT * FROM '.acymailing::table('subscriber').' WHERE '.$column.' = '.$this->database->Quote(trim($subid)).' LIMIT 1');
		return $this->database->loadObject();
	}
	function getFull($subid){
		$column = is_numeric($subid) ? 'subid' : 'email';
		$this->database->setQuery('SELECT b.*, a.* FROM '.acymailing::table('subscriber').' as a LEFT JOIN '.acymailing::table('users',false).' as b on a.userid = b.id WHERE '.$column.' = '.$this->database->Quote(trim($subid)).' LIMIT 1');
		return $this->database->loadObject();
	}
	function getSubscription($subid,$index = ''){
		$query = 'SELECT a.*, b.* FROM '.acymailing::table('list').' as b ';
		$query .= 'LEFT JOIN '.acymailing::table('listsub').' as a on a.listid = b.listid AND a.subid = '.intval($subid);
		$query .= ' WHERE b.type = \'list\'';
		$query .= ' ORDER BY b.ordering ASC';
		$this->database->setQuery($query);
		return $this->database->loadObjectList($index);
	}
	function getSubscriptionStatus($subid,$listids = null){
		$query = 'SELECT status,listid FROM '.acymailing::table('listsub').' WHERE subid = '.intval($subid);
		if($listids !== null){
			JArrayHelper::toInteger($listids, array(0));
			$query .= ' AND listid IN ('.implode(',',$listids).')';
		}
		$this->database->setQuery($query);
		return $this->database->loadObjectList('listid');
	}
	function checkFields(&$data,&$subscriber){
		$app =& JFactory::getApplication();
		foreach($data as $column => $value){
			$column = trim(strtolower($column));
			if($app->isAdmin() OR !in_array($column,$this->restrictedFields)){
				acymailing::secureField($column);
				if(is_array($value)){
					if(isset($value['day']) || isset($value['month']) || isset($value['year'])){
						$value = (empty($value['year']) ? '0000' :intval($value['year'])).'-'.(empty($value['month']) ? '00' : intval($value['month'])).'-'.(empty($value['day']) ? '00' : intval($value['day']));
					}else{
						$value = implode(',',$value);
					}
				}
				$subscriber->$column = strip_tags($value);
				if(!is_numeric($subscriber->$column) AND !preg_match('%^(?:[\x09\x0A\x0D\x20-\x7E]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})*$%xs', $subscriber->$column)){
					$subscriber->$column = utf8_encode($subscriber->$column);
				}
			}
		}
	}
	function saveForm(){
		$app =& JFactory::getApplication();
		$config = acymailing::config();
		$allowUserModifications = (bool) ($config->get('allow_modif','data') == 'all') || $app->isAdmin();
		$allowSubscriptionModifications = (bool) ($config->get('allow_modif','data') != 'none') || $app->isAdmin();
		$subscriber = null;
		$subscriber->subid = acymailing::getCID('subid');
		if(!$app->isAdmin() && !empty($subscriber->subid)){
			$user = $this->identify();
			$allowUserModifications = true;
			$allowSubscriptionModifications = true;
			if($user->subid != $subscriber->subid){
				die('You are not allowed to modify this user');
			}
		}
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		if(!empty($formData['subscriber'])){
			$this->checkFields($formData['subscriber'],$subscriber);
		}
		if(empty($subscriber->subid)){
			if(empty($subscriber->email)){
				echo "<script>alert('".JText::_('VALID_EMAIL',true)."'); window.history.go(-1);</script>";
				exit;
			}
			$existSubscriber = $this->get($subscriber->email);
			if(!empty($existSubscriber->subid)){
				if($app->isAdmin()){
					$this->errors[] = 'A user already exists with the e-mail '.$subscriber->email;
					$this->errors[] = 'You can <a href="'.acymailing::completeLink('subscriber&task=edit&subid='.$existSubscriber->subid).'">edit this user</a>';
					return false;
				}else{
					$subscriber->subid = $existSubscriber->subid;
					$subscriber->confirmed = $existSubscriber->confirmed;
				}
			}
		}
		$this->recordHistory = true;
		$this->newUser = empty($subscriber->subid) ? true : false;
		if(empty($subscriber->subid) OR $allowUserModifications){
			$subid = $this->save($subscriber);
			$allowSubscriptionModifications = true;
		}else{
			$subid = $subscriber->subid;
			if(isset($subscriber->confirmed) && empty($subscriber->confirmed)) $this->sendConf($subid);
		}
		JRequest::setVar( 'subid', $subid);
		if(empty($subid)) return false;
		if(!$app->isAdmin() && isset($subscriber->accept) && $subscriber->accept == 0) $formData['masterunsub'] = 1;
		if(empty($formData['listsub'])) return true;
		if(!$allowSubscriptionModifications){
			$mailClass = acymailing::get('helper.mailer');
			$mailClass->checkConfirmField = false;
			$mailClass->checkEnabled = false;
			$mailClass->report = false;
			$mailClass->sendOne('modif',$subid);
			$this->requireId = true;
			return false;
		}
		return $this->saveSubscription($subid,$formData['listsub']);
	}
	function saveSubscription($subid,$formlists){
		$addlists = array();
		$removelists = array();
		$updatelists = array();
		$listids = array_keys($formlists);
		$currentSubscription = $this->getSubscriptionStatus($subid,$listids);
		foreach($formlists as $listid => $oneList){
			if(empty($oneList['status'])){
				if(isset($currentSubscription[$listid])) $removelists[] = $listid;
				continue;
			}
			if($this->confirmationSent && $oneList['status'] == 1) $oneList['status'] = 2;
			if(!isset($currentSubscription[$listid])){
				if($oneList['status'] != -1) $addlists[$oneList['status']][] = $listid;
				continue;
			}
			if($currentSubscription[$listid]->status == $oneList['status']) continue;
			$updatelists[$oneList['status']][] = $listid;
		}
		$listsubClass = acymailing::get('class.listsub');
		$status = true;
		if(!empty($updatelists)) $status = $listsubClass->updateSubscription($subid,$updatelists) && $status;
		if(!empty($removelists)) $status = $listsubClass->removeSubscription($subid,$removelists) && $status;
		if(!empty($addlists)) $status = $listsubClass->addSubscription($subid,$addlists) && $status;
		return $status;
	}
	function confirmSubscription($subid){
		$historyClass = acymailing::get('class.acyhistory');
		$historyClass->insert($subid,'confirmed');
		$this->database->setQuery('UPDATE '.acymailing::table('subscriber').' SET `confirmed` = 1 WHERE `subid` = '.intval($subid).' LIMIT 1');
		$this->database->query();
		$this->database->setQuery('SELECT `listid` FROM '.acymailing::table('listsub').' WHERE `status` = 2 AND `subid` = '.intval($subid));
		$listids = $this->database->loadResultArray();
		if(empty($listids)) return;
		$listsubClass = acymailing::get('class.listsub');
		$listsubClass->sendConf = $this->sendWelcome;
		$listsubClass->sendNotif = $this->sendNotif;
		$listsubClass->updateSubscription($subid,array(1 => $listids));
	}
	function identify($onlyvalue = false){
		$app =& JFactory::getApplication();
		$subid = JRequest::getInt("subid",0);
		$key = JRequest::getString("key",'');
		if(empty($subid) OR empty($key)){
			$user = JFactory::getUser();
			if(!empty($user->id)){
				$userIdentified = $this->get($user->email);
				return $userIdentified;
			}
			if(!$onlyvalue){
				$app->enqueueMessage(JText::_('ASK_LOG'),'error');
			}
			return false;
		}
		$this->database->setQuery('SELECT * FROM '.acymailing::table('subscriber').' WHERE `subid` = '.$this->database->Quote($subid).' AND `key` = '.$this->database->Quote($key).' LIMIT 1');
		$userIdentified = $this->database->loadObject();
		if(empty($userIdentified)){
			if(!$onlyvalue) $app->enqueueMessage(JText::_('INVALID_KEY'),'error');
			return false;
		}
		return $userIdentified;
	}
}