<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class listHelper{
	var $sendNotif = true;
	var $sendConf = true;
	var $survey = '';
	function subscribe($subid,$listids){
		$app =& JFactory::getApplication();
		if(acymailing::level(3)){
			$campaignClass = acymailing::get('helper.campaign');
			$campaignClass->start($subid,$listids);
		}

		JPluginHelper::importPlugin('acymailing');
		$dispatcher = &JDispatcher::getInstance();
		$resultsTrigger = $dispatcher->trigger('onAcySubscribe',array($subid,$listids));
	}//endfct
	function unsubscribe($subid,$listids){
		$app =& JFactory::getApplication();
		if(acymailing::level(3)){
			$campaignClass = acymailing::get('helper.campaign');
			$campaignClass->stop($subid,$listids);
		}
		$config = acymailing::config();
		static $alreadySent = false;
		if($this->sendNotif AND !$alreadySent AND $config->get('notification_unsub') AND !$app->isAdmin()){
			$alreadySent = true;
			$mailer = acymailing::get('helper.mailer');
			$mailer->report = false;
			$mailer->autoAddUser = true;
			$mailer->checkConfirmField = false;
			$userClass = acymailing::get('class.subscriber');
			$subscriber = $userClass->get($subid);
			$ipClass = acymailing::get('helper.user');
			$mailer->addParam('survey',$this->survey);
			$mailer->addParamInfo();
			$subscriber->ip = $ipClass->getIP();
			foreach($subscriber as $fieldname => $value) $mailer->addParam('user:'.$fieldname,$value);
			$allUsers = explode(',',$config->get('notification_unsub'));
			foreach($allUsers as $oneUser){
				$mailer->sendOne('notification_unsub',$oneUser);
			}
		}
		$db =& JFactory::getDBO();
		if($this->sendConf AND !$app->isAdmin()){
			$db->setQuery('SELECT DISTINCT `unsubmailid` FROM '.acymailing::table('list').' WHERE `listid` IN ('.implode(',',$listids).') AND `published` = 1  AND `unsubmailid` > 0');
			$messages = $db->loadResultArray();
			if(!empty($messages)){
				$config = acymailing::config();
				$mailHelper = acymailing::get('helper.mailer');
				$mailHelper->report = $config->get('unsub_message',true);
				$mailHelper->checkAccept = false;
				foreach($messages as $mailid){
					$mailHelper->sendOne($mailid,$subid);
				}
			}
		}//end only frontend
		$db->setQuery('DELETE  FROM '.acymailing::table('queue').' WHERE `subid` = '.(int) $subid.' AND `mailid` IN (SELECT `mailid` FROM '.acymailing::table('listmail').' WHERE `listid` IN ('.implode(',',$listids).'))');
		$db->query();
		JPluginHelper::importPlugin('acymailing');
		$dispatcher = &JDispatcher::getInstance();
		$resultsTrigger = $dispatcher->trigger('onAcyUnsubscribe',array($subid,$listids));
	}
}//endclass