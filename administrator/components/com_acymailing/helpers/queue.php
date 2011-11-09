<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class queueHelper{
	var $mailid = 0;
	var $report = true;
	var $send_limit = 0;
	var $finish = false;
	var $error = false;
	var $nbprocess = 0;
	var $start = 0;
	var $stoptime = 0;
	var $successSend =0;
	var $errorSend=0;
	var $consecutiveError=0;
	var $messages = array();
	var $pause = 0;
	var $config;
 	var $listsubClass;
  	var $subClass;
	function queueHelper(){
		$this->config = acymailing::config();
	    $this->subClass = acymailing::get('class.subscriber');
    	$this->listsubClass = acymailing::get('class.listsub');
    	$this->listsubClass->checkAccess = false;
	    $this->listsubClass->sendNotif = false;
	    $this->listsubClass->sendConf = false;
		$this->send_limit = (int) $this->config->get('queue_nbmail',40);
		acymailing::increasePerf();
		@ini_set('default_socket_timeout',10);
		@ignore_user_abort(true);
		$timelimit = ini_get('max_execution_time');
		if(!empty($timelimit)){
			$this->stoptime = time()+$timelimit-4;
		}
		$this->db =& JFactory::getDBO();
	}
	function process(){
		$queueClass = acymailing::get('class.queue');
		$queueElements = $queueClass->getReady($this->send_limit,$this->mailid);
		if(empty($queueElements)){
			$this->finish = true;
			return true;
		}
		if($this->report){
			if(!headers_sent() AND ob_get_level() > 0){
				ob_end_flush();
			}
			$disp = "<div style='position:fixed; top:3px;left:3px;background-color : white;border : 1px solid grey; padding : 3px;'>";
			$disp.= "<span id='divpauseinfo' style='padding:10px;margin:5px;font-size:16px;font-weight:bold;display:none;background-color:black;color:white;'> </span>";
			$disp.= JText::_('SEND_PROCESS').': <span id="counter"/>'.$this->start.'</span> / '. $this->total;
			$disp.= '</div>';
			$disp.= "<div id='divinfo' style='display:none; position:fixed; bottom:3px;left:3px;background-color : white; border : 1px solid grey; padding : 3px;'> </div>";
			$disp .= '<br /><br />';
			$url = JURI::base().'index.php?option=com_acymailing&ctrl=send&tmpl=component&task=continuesend&mailid='.$this->mailid.'&totalsend='.$this->total.'&alreadysent=';
			$disp.= '<script type="text/javascript" language="javascript">';
			$disp.= 'var mycounter = document.getElementById("counter");';
			$disp.= 'var divinfo = document.getElementById("divinfo");
					var divpauseinfo = document.getElementById("divpauseinfo");
					function setInfo(message){ divinfo.style.display = \'block\';divinfo.innerHTML=message; }
					function setPauseInfo(nbpause){ divpauseinfo.style.display = \'\';divpauseinfo.innerHTML=nbpause; }
					function setCounter(val){ mycounter.innerHTML=val;}
					var scriptpause = '.intval($this->pause).';
					function handlePause(){
						setPauseInfo(scriptpause);
						if(scriptpause > 0){
							scriptpause = scriptpause - 1;
							setTimeout(\'handlePause()\',1000);
						}else{
							document.location.href=\''.$url.'\'+mycounter.innerHTML;
						}
					}
					</script>';
			echo $disp;
			if(function_exists('ob_flush')) @ob_flush();
			@flush();
		}//endifreport
		$mailHelper = acymailing::get('helper.mailer');
		$mailHelper->report = false;
		if($this->config->get('smtp_keepalive',0)) $mailHelper->SMTPKeepAlive = true;
		$queueDelete = array();
		$queueUpdate = array();
		$statsAdd = array();
		$actionSubscriber = array();
		$maxTry = (int) $this->config->get('queue_try',0);
		$currentMail = $this->start;
		$this->nbprocess = 0;
		if(count($queueElements) < $this->send_limit){
			$this->finish = true;
		}
		foreach($queueElements as $oneQueue){
			$currentMail++; $this->nbprocess++;
			if($this->report){
				echo '<script type="text/javascript" language="javascript">setCounter('. $currentMail .')</script>';
				if(function_exists('ob_flush')) @ob_flush();
				@flush();
			}
			$result = $mailHelper->sendOne($oneQueue->mailid,$oneQueue);
			$queueDeleteOk = true;
			$otherMessage = '';
			if($result){
				$this->successSend ++;
				$this->consecutiveError = 0;
				$queueDelete[$oneQueue->mailid][] = $oneQueue->subid;
				$statsAdd[$oneQueue->mailid][1][(int)$mailHelper->sendHTML][] = $oneQueue->subid;
				$queueDeleteOk = $this->_deleteQueue($queueDelete);
				$queueDelete = array();
				if($this->nbprocess%10 == 0){
					$this->_statsAdd($statsAdd);
					$this->_queueUpdate($queueUpdate);
					$statsAdd = array();
					$queueUpdate = array();
				}
			}else{
				$this->errorSend ++;
				$newtry = false;
				if(in_array($mailHelper->errorNumber,$mailHelper->errorNewTry)){
					if(empty($maxTry) OR $oneQueue->try < $maxTry-1){
						$newtry = true;
						$otherMessage = JText::sprintf('QUEUE_NEXT_TRY',round($this->config->get('queue_delay')/60));
					}
					if($mailHelper->errorNumber == 1) $this->consecutiveError ++;
				}
				if(!$newtry){
					$queueDelete[$oneQueue->mailid][] = $oneQueue->subid;
					$statsAdd[$oneQueue->mailid][0][(int)@$mailHelper->sendHTML][] = $oneQueue->subid;
					if($mailHelper->errorNumber == 1 AND $this->config->get('bounce_action_maxtry')){
						$queueDeleteOk = $this->_deleteQueue($queueDelete);
						$queueDelete = array();
						$otherMessage .= $this->_subscriberAction($oneQueue->subid);
					}
				}else{
					$queueUpdate[$oneQueue->mailid][] = $oneQueue->subid;
				}
			}
			$messageOnScreen = '['.$oneQueue->mailid.'] '.$mailHelper->reportMessage;
			if(!empty($otherMessage)) $messageOnScreen .= ' => '.$otherMessage;
			$this->_display($messageOnScreen,$result,$currentMail);
			if(!$queueDeleteOk){
				$this->_display(JText::_('QUEUE_DOUBLE',true));
				$this->finish = true;
				break;
			}
			if(!empty($this->stoptime) AND $this->stoptime < time()){
				$this->_display(JText::_('SEND_REFRESH_TIMEOUT',true));
				if($this->nbprocess < count($queueElements)) $this->finish = false;
				break;
			}
			if($this->consecutiveError > 2 AND $this->successSend>3){
				$this->_display(JText::_('SEND_REFRESH_CONNECTION',true));
				break;
			}
			if($this->consecutiveError > 5 OR connection_aborted()){
				$this->finish = true;
				break;
			}
		}
		$this->_deleteQueue($queueDelete);
		$this->_statsAdd($statsAdd);
		$this->_queueUpdate($queueUpdate);
		if($this->config->get('smtp_keepalive',0)) $mailHelper->SmtpClose();
		if(!empty($this->total) AND $currentMail >= $this->total){
			$this->finish = true;
		}
		if($this->consecutiveError>5){
			$this->_handleError();
			return false;
		}
		if($this->report && !$this->finish){
			echo '<script type="text/javascript" language="javascript">handlePause();</script>';


		}
		return true;
	}
	function _deleteQueue($queueDelete){
		if(empty($queueDelete)) return true;
		$status = true;
		foreach($queueDelete as $mailid => $subscribers){
			$nbsub = count($subscribers);
			$query = 'DELETE FROM '.acymailing::table('queue').' WHERE mailid = '.intval($mailid).' AND subid IN ('.implode(',',$subscribers).') LIMIT '.$nbsub;
			$this->db->setQuery($query);
			$this->db->query();
			$nbdeleted = $this->db->getAffectedRows();
			if($nbdeleted != $nbsub){
				$status = false;
			}
		}
		return $status;
	}
	function _statsAdd($statsAdd){
		$time = time();
		if(empty($statsAdd)) return true;
		foreach($statsAdd as $mailid => $infos){
			$mailid = intval($mailid);
			foreach($infos as $status => $infosSub){
				foreach($infosSub as $html => $subscribers){
					$query = 'INSERT IGNORE INTO '.acymailing::table('userstats').' (mailid,subid,html,sent,senddate) VALUES ('.$mailid.','.implode(','.$html.',0,'.$time.'),('.$mailid.',',$subscribers).','.$html.',0,'.$time.')';
					$this->db->setQuery($query);
					$this->db->query();
					if($status){
						$query = 'UPDATE '.acymailing::table('userstats').' SET html = '.$html.',sent = sent +1,senddate = '.$time.' WHERE mailid = '.$mailid.' AND subid IN  ('.implode(',',$subscribers).')';
					}else{
						$query = 'UPDATE '.acymailing::table('userstats').' SET html = '.$html.',senddate = '.$time.', fail = fail +1 WHERE mailid = '.$mailid.' AND subid IN  ('.implode(',',$subscribers).')';
					}
					$this->db->setQuery($query);
					$this->db->query();
				}
			}
			$nbhtml = empty($infos[1][1]) ? 0 : count($infos[1][1]);
			$nbtext = empty($infos[1][0]) ? 0 : count($infos[1][0]);
			$nbfail = 0;
			if(!empty($infos[0][0])) $nbfail += count($infos[0][0]);
			if(!empty($infos[0][1])) $nbfail += count($infos[0][1]);
			$query = 'UPDATE '.acymailing::table('stats').' SET senthtml = senthtml + '.$nbhtml.', senttext = senttext + '.$nbtext.', fail = fail + '.$nbfail.', senddate = '.$time.' WHERE mailid = '.$mailid.' LIMIT 1';
			$this->db->setQuery($query);
			$this->db->query();
			if(!$this->db->getAffectedRows()){
				$query = 'INSERT INTO '.acymailing::table('stats').' (mailid,senthtml,senttext,fail,senddate) VALUES ('.$mailid.','.$nbhtml.', '.$nbtext.', '.$nbfail.', '.$time.')';
				$this->db->setQuery($query);
				$this->db->query();
			}
		}
	}
	function _queueUpdate($queueUpdate){
		if(empty($queueUpdate)) return true;
		$delay = $this->config->get('queue_delay',3600);
		foreach($queueUpdate as $mailid => $subscribers){
			$query = 'UPDATE '.acymailing::table('queue').' SET senddate = senddate + '.$delay.', try = try +1 WHERE mailid = '.$mailid.' AND subid IN ('.implode(',',$subscribers).')';
			$this->db->setQuery($query);
			$this->db->query();
		}
	}
	function _handleError(){
		$this->finish = true;
		$message = JText::_('SEND_STOPED',true);
		$message .= '<br/>';
		$message .= JText::_('SEND_KEPT_ALL',true);
		$message .= '<br/>';
		if($this->report){
			if(empty($this->successSend) AND empty($this->start)){
				$message .= JText::_('SEND_CHECKONE',true);
				$message .= '<br/>';
				$message .= JText::_('SEND_ADVISE_LIMITATION',true);
			}else{
				$message .= JText::_('SEND_REFUSE',true);
				$message .= '<br/>';
				if(!acymailing::level(1)){
					$message .= JText::_('SEND_CONTINUE_COMMERCIAL',true);
				}else{
					$message .= JText::_('SEND_CONTINUE_AUTO',true);
				}
			}
		}
		$this->_display($message);
	}
	function _display($message,$status = '',$num = ''){
		$this->messages[] = strip_tags($message);
		if(!$this->report) return;
		if(!empty($num)){
			$color = $status ? 'green' : 'red';
			echo '<br/>'.$num.' : <font color="'.$color.'">'.$message.'</font>';
		}else{
			echo '<script type="text/javascript" language="javascript">setInfo(\''. $message .'\')</script>';
		}
		if(function_exists('ob_flush')) @ob_flush();
		@flush();
	}
	function _subscriberAction($subid){
		if($this->config->get('bounce_action_maxtry') == 'delete'){
			$this->subClass->delete($subid);
			return ' user '.$subid.' deleted';
		}
    	$listId = 0;
    	if(in_array($this->config->get('bounce_action_maxtry'),array('sub','remove','unsub'))){
    		$status = $this->subClass->getSubscriptionStatus($subid);
    	}
    	$message = '';
		switch($this->config->get('bounce_action_maxtry')){
			case 'sub' :
				$listId = $this->config->get('bounce_action_lists_maxtry');
				if(!empty($listId)){
					$message .= ' user '.$subid.' subscribed to '.$listId;
		            if(empty($status[$listId])){
						$this->listsubClass->addSubscription($subid,array('1' => array($listId)));
		            }elseif($status[$listId]->status != 1){
					 	$this->listsubClass->updateSubscription($subid,array('1' => array($listId)));
		            }
				}
			case 'remove' :
				$unsubLists = array_diff(array_keys($status),array($listId));
				if(!empty($unsubLists)){
					$message .= ' | user '.$subid.' removed from lists '.implode(',',$unsubLists);
					$this->listsubClass->removeSubscription($subid,$unsubLists);
				}else{
					$message .= ' | user '.$subid.' not subscribed';
				}
				break;
			case 'unsub' :
				$unsubLists = array_diff(array_keys($status),array($listId));
				if(!empty($unsubLists)){
					$message .= ' | user '.$subid.' unsubscribed from lists '.implode(',',$unsubLists);
					$this->listsubClass->updateSubscription($subid,array('-1' => $unsubLists));
				}else{
					$message .= ' | user '.$subid.' not subscribed';
				}
				break;
			case 'delete' :
				$message .= ' | user '.$subid.' deleted';
				$this->subClass->delete($subid);
				break;
			case 'block' :
				$message .= ' | user '.$subid.' blocked';
				$this->db->setQuery('UPDATE `#__acymailing_subscriber` SET `enabled` = 0 WHERE `subid` = '.intval($subid));
				$this->db->query();
				$this->db->setQuery('DELETE FROM `#__acymailing_queue` WHERE `subid` = '.intval($subid));
				$this->db->query();
				break;
	      }
		return $message;
	}
}
