<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class ToggleController extends JController{
	var $allowedTablesColumn = array();
	var $deleteColumns = array();
	function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerDefaultTask('toggle');
		$this->allowedTablesColumn['list']= array('published'=>'listid','visible'=>'listid');
		$this->allowedTablesColumn['subscriber']= array('confirmed'=>'subid','html'=>'subid','enabled'=>'subid');
		$this->allowedTablesColumn['template']=array('published'=>'tempid','premium'=>'tempid');
		$this->allowedTablesColumn['mail']=array('published'=>'mailid','visible'=>'mailid');
		$this->allowedTablesColumn['listsub']=array('status'=>'listid,subid');
		$this->allowedTablesColumn['plugins']=array('published'=>'id');
		$this->allowedTablesColumn['followup']=array('add'=>'mailid','update'=>'mailid');
		$this->allowedTablesColumn['filter']=array('published'=>'filid');
		$this->allowedTablesColumn['fields']=array('published'=>'fieldid','required'=>'fieldid','frontcomp'=>'fieldid','backend'=>'fieldid','listing'=>'fieldid');
		$this->deleteColumns['queue']=array('subid','mailid');
		$this->deleteColumns['filter']=array('filid','filid');
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );
		header( 'Pragma: no-cache' );
	}
	function toggle(){
		$completeTask = JRequest::getCmd('task');
		$task = substr($completeTask,0,strpos($completeTask,'_'));
		$elementId = substr($completeTask,strpos($completeTask,'_') +1);
		$value =  JRequest::getVar('value','0','','int');
		$table =  JRequest::getVar('table','','','word');
		$pkey = $this->allowedTablesColumn[$table][$task];
		if(empty($pkey)) exit;
		$function = $table.$task;
		if(method_exists($this,$function)){
			$this->$function($elementId,$value);
		}else{
			$db	=& JFactory::getDBO();
			$db->setQuery('UPDATE '.acymailing::table($table).' SET '.$task.' = '.$value.' WHERE '.$pkey.' = '.intval($elementId).' LIMIT 1');
			$db->query();
		}
		$toggleClass = acymailing::get('helper.toggle');
		$extra = JRequest::getVar('extra',array(),'','array');
		if(!empty($extra)){
			foreach($extra as $key => $val){
				$extra[$key] = urldecode($val);
			}
		}
		echo $toggleClass->toggle(JRequest::getCmd('task',''),$value,$table,$extra);
		exit;
	}
	function followupadd($mailid,$value){
		$mailClass = acymailing::get('class.mail');
		$followup = $mailClass->get($mailid);
		if(empty($followup->mailid)){
			echo 'Could not load mailid '.$mailid;
			exit;
		}
		$listmailClass = acymailing::get('class.listmail');
		$mycampaign = $listmailClass->getCampaign($followup->mailid);
		if(empty($mycampaign->listid)){
			echo 'Could not get the attached campaign';
			exit;
		}
		$config = acymailing::config();
		$db =& JFactory::getDBO();
		$query = 'INSERT IGNORE INTO `#__acymailing_queue` (`mailid`,`senddate`,`priority`,`subid`) ';
		$query .= 'SELECT '.$followup->mailid.', b.`subdate` + '.$followup->senddate.' , '.(int) $config->get('priority_followup',2).', b.`subid` ';
		$query .= 'FROM `#__acymailing_listsub` as b';
		$query .=' WHERE b.`status` = 1 AND b.`listid` = '.$mycampaign->listid.' AND b.`subdate` > '.(time() - $followup->senddate);
		$db->setQuery($query);
		$db->query();
		$nbinserted = $db->getAffectedRows();
		if(!empty($nbupdated)){
			$campaignHelper = acymailing::get('helper.campaign');
			$campaignHelper->updateUnsubdate($mycampaign->listid,$followup->senddate);
		}
		echo JText::sprintf('ADDED_QUEUE',$nbinserted);
		exit;
	}
	function followupupdate($mailid,$value){
		$mailClass = acymailing::get('class.mail');
		$followup = $mailClass->get($mailid);
		if(empty($followup->mailid)){
			echo 'Could not load mailid '.$mailid;
			exit;
		}
		$listmailClass = acymailing::get('class.listmail');
		$mycampaign = $listmailClass->getCampaign($followup->mailid);
		if(empty($mycampaign->listid)){
			echo 'Could not get the attached campaign';
			exit;
		}
		$db =& JFactory::getDBO();
		$query = 'UPDATE #__acymailing_queue as a ';
		$query.= 'LEFT JOIN #__acymailing_listsub as b ON a.subid = b.subid AND b.listid = '.$mycampaign->listid;
		$query .= ' SET a.`senddate` = b.`subdate` + '.$followup->senddate;
		$query .= ' WHERE a.mailid = '.$followup->mailid;
		$db->setQuery($query);
		$db->query();
		$nbupdated = $db->getAffectedRows();
		if(!empty($nbupdated)){
			$campaignHelper = acymailing::get('helper.campaign');
			$campaignHelper->updateUnsubdate($mycampaign->listid,$followup->senddate);
		}
		echo JText::sprintf('NB_EMAILS_UPDATED',$nbupdated);
		exit;
	}
	function delete(){
		list($value1,$value2) = explode('_',JRequest::getCmd('value'));
		$table =  JRequest::getVar('table','','','word');
		if(empty($table)) exit;
		$function = 'delete'.$table;
		if(method_exists($this,$function)){
			$this->$function($value1,$value2);
			exit;
		}
		list($key1,$key2) = $this->deleteColumns[$table];
		if(empty($key1) OR empty($key2) OR empty($value1) OR empty($value2)) exit;
		$db	=& JFactory::getDBO();
		$db->setQuery('DELETE FROM '.acymailing::table($table).' WHERE '.$key1.' = '.intval($value1).' AND '.$key2.' = '.intval($value2));
		$db->query();
		exit;
	}
	function deleteconfig($namekey,$val){
		$config = acymailing::config();
		$newConfig = null;
		$newConfig->$namekey = $val;
		$config->save($newConfig);
	}
	function deletefollowup($campaignid,$mailid){
		$mailClass = acymailing::get('class.mail');
		$mailClass->delete((int) $mailid);
	}
	function deleteMail($mailid,$attachid){
		$mailid = intval($mailid);
		if(empty($mailid)) return false;
		$db	=& JFactory::getDBO();
		$db->setQuery('SELECT attach FROM '.acymailing::table('mail').' WHERE mailid = '.$mailid.' LIMIT 1');
		$attachment = $db->loadResult();
		if(empty($attachment)) return;
		$attach = unserialize($attachment);
		unset($attach[$attachid]);
		$attachdb = serialize($attach);
		$db->setQuery('UPDATE '.acymailing::table('mail').' SET attach = '.$db->Quote($attachdb).' WHERE mailid = '.$mailid.' LIMIT 1');
		return $db->query();
	}
	function subscriberconfirmed($subid,$value){
		if(!empty($value)){
			$subscriberClass = acymailing::get('class.subscriber');
			$subscriberClass->confirmSubscription($subid);
		}else{
			$db	=& JFactory::getDBO();
			$db->setQuery('UPDATE '.acymailing::table('subscriber').' SET confirmed = '.$value.' WHERE subid = '.intval($subid).' LIMIT 1');
			$db->query();
		}
	}
	function listsubstatus($ids,$status){
		list($listid,$subid) = explode('_',$ids);
		$listid = (int) $listid;
		$subid = (int) $subid;
		if(empty($subid) OR empty($listid)) exit;
		$listSubClass = acymailing::get('class.listsub');
		$lists = array();
		$lists[$status] = array($listid);
		if($listSubClass->updateSubscription($subid,$lists)) return;
		echo 'error while updating the subscription';
	}
	function pluginspublished($id,$publish){
		$db	=& JFactory::getDBO();
		if(version_compare(JVERSION,'1.6.0','<')){
			$db->setQuery('UPDATE '.acymailing::table('plugins',false).' SET `published` = '.intval($publish).' WHERE `id` = '.intval($id).' AND (`folder` = \'acymailing\' OR `name` LIKE \'%acymailing%\' OR `element` LIKE \'%acymailing%\') LIMIT 1');
		}else{
			$db->setQuery('UPDATE `#__extensions` SET `enabled` = '.intval($publish).' WHERE `extension_id` = '.intval($id).' AND (`folder` = \'acymailing\' OR `name` LIKE \'%acymailing%\' OR `element` LIKE \'%acymailing%\') LIMIT 1');
		}
		$db->query();
	}
}