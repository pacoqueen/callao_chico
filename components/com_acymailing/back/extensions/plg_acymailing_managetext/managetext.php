<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
defined('_JEXEC') or die('Restricted access');
class plgAcymailingManagetext extends JPlugin
{
	var $foundtags = array();
	function plgAcymailingManagetext(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin =& JPluginHelper::getPlugin('acymailing', 'managetext');
			$this->params = new JParameter( $plugin->params );
		}
    }
    function acymailing_replaceusertagspreview(&$email,&$user){
		return $this->acymailing_replaceusertags($email,$user);
	}
	function acymailing_replaceusertags(&$email,&$user){
		$this->_removetext($email);
		$this->_addfooter($email);
		$this->_ifstatement($email,$user);
	}
	function _ifstatement(&$email,$user){
		if(isset($this->foundtags[$email->mailid])) return;
		$match = '#{if:(.*)}(.*){/if}#Uis';
		$variables = array('subject','body','altbody');
		$found = false;
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match,$email->$var,$results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}
		if(!$found){
			$this->foundtags[$email->mailid] = false;
			return;
		}
		$app =& JFactory::getApplication();
		$tags = array();
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				if(isset($tags[$oneTag])) continue;
				if(!preg_match('#^([^=!<>~]+)(=|!=|<|>|&gt;|&lt;|~)([^=!<>~]+)$#i',$allresults[1][$i],$operators)){
					if($app->isAdmin()) acymailing::display('Operation not found : '.$allresults[1][$i],'error');
					$tags[$oneTag] = $allresults[2][$i];
					continue;
				};
				$operators[1] = trim($operators[1]);
				if(!isset($user->{$operators[1]})){
					if($app->isAdmin()) acymailing::display('User variable not set : '.$operators[1].' in '.$allresults[1][$i],'error');
					$tags[$oneTag] = $allresults[2][$i];
					continue;
				}
				$tags[$oneTag] = '';
				$prop = strtolower($user->{$operators[1]});
				$val = trim(strtolower($operators[3]));
				if($operators[2] == '=' AND $prop == $val){
					$tags[$oneTag] = $allresults[2][$i];
				}elseif($operators[2] == '!=' AND $prop != $val){
					$tags[$oneTag] = $allresults[2][$i];
				}elseif(($operators[2] == '>' || $operators[2] == '&gt;') AND $prop > $val){
					$tags[$oneTag] = $allresults[2][$i];
				}elseif(($operators[2] == '<' || $operators[2] == '&lt;') AND $prop < $val){
					$tags[$oneTag] = $allresults[2][$i];
				}elseif($operators[2] == '~' AND strpos($prop,$val) !== false){
					$tags[$oneTag] = $allresults[2][$i];
				}
			}
		}
		foreach($results as $var => $allresults){
			$email->$var = str_replace(array_keys($tags),$tags,$email->$var);
		}
	}
	function _removetext(&$email){
		$removetext = $this->params->get('removetext','{reg},{/reg},{pub},{/pub}');
		if(!empty($removetext)){
			$removeArray = explode(',',$removetext);
			if(!empty($email->body)) $email->body = str_replace($removeArray,'',$email->body);
			if(!empty($email->altbody)) $email->altbody = str_replace($removeArray,'',$email->altbody);
		}
	}
	function _addfooter(&$email){
		$footer = $this->params->get('footer');
		if(!empty($footer)){
			if(strpos($email->body,'</body>')){
				$email->body = str_replace('</body>','<br/>'.$footer.'</body>',$email->body);
			}else{
				$email->body .= '<br/>'.$footer;
			}
			if(!empty($email->altbody)){
				$email->altbody .= "\n".$footer;
			}
		}
	}
	 function onAcyDisplayActions(&$type){
	 	$type['addqueue'] = JText::_('ADD_QUEUE');
	 	$type['removequeue'] = JText::_('REMOVE_QUEUE');
	 	$db =& JFactory::getDBO();
		$db->setQuery("SELECT `mailid`,`subject`, `type` FROM `#__acymailing_mail` WHERE `type` NOT IN ('notification','autonews') OR `alias` = 'confirmation' ORDER BY `type`,`subject` ASC ");
		$allEmails = $db->loadObjectList();
		$emailsToDisplay = array();
		$typeNews = '';
		foreach($allEmails as $oneMail){
			if($oneMail->type != $typeNews){
				if(!empty($typeNews)) $emailsToDisplay[] = JHTML::_('select.option',  '</OPTGROUP>');
				$typeNews = $oneMail->type;
				if($oneMail->type == 'notification'){
					$label = JText::_('NOTIFICATIONS');
				}elseif($oneMail->type == 'news'){
					$label = JText::_('NEWSLETTERS');
				}elseif($oneMail->type == 'followup'){
					$label = JText::_('FOLLOWUP');
				}elseif($oneMail->type == 'welcome'){
					$label = JText::_('MSG_WELCOME');
				}elseif($oneMail->type == 'unsub'){
					$label = JText::_('MSG_UNSUB');
				}else{
					$label = $oneMail->type;
				}
				$emailsToDisplay[] = JHTML::_('select.option',  '<OPTGROUP>', $label );
			}
			$emailsToDisplay[] = JHTML::_('select.option', $oneMail->mailid, $oneMail->subject.' ('.$oneMail->mailid.')' );
		}
		$emailsToDisplay[] = JHTML::_('select.option',  '</OPTGROUP>');
	 	$addqueue = '<div id="action__num__addqueue">'.JHTML::_('select.genericlist',  $emailsToDisplay, "action[__num__][addqueue][mailid]", 'class="inputbox" size="1"').'<br /><label for="addqueuesenddate__num__">'.JText::_('SEND_DATE').' </label> <input value="{time}" id="addqueuesenddate__num__" name="action[__num__][addqueue][senddate]" /></div>';
	 	$removequeue = '<div id="action__num__removequeue">'.JHTML::_('select.genericlist',  $emailsToDisplay, "action[__num__][removequeue][mailid]", 'class="inputbox" size="1"').'</div>';
	 	return $addqueue.$removequeue;
	 }
	 function onAcyProcessAction_addqueue($cquery,$action,$num){
	 	$action['mailid'] = intval($action['mailid']);
	 	if(empty($action['mailid'])) return 'mailid not valid';
	 	$action['senddate'] = acymailing::replaceDate($action['senddate']);
	 	if(!is_numeric($action['senddate'])) $action['senddate'] = acymailing::getTime($action['senddate']);
	 	if(empty($action['senddate'])) return 'send date not valid';
	 	$query = 'INSERT IGNORE INTO `#__acymailing_queue` (`mailid`,`subid`,`senddate`,`priority`) '.$cquery->getQuery(array($action['mailid'],'sub.`subid`',$action['senddate'],'3'));
	 	$db =& JFactory::getDBO();
	 	$db->setQuery($query);
	 	$db->query();
	 	return JText::sprintf('ADDED_QUEUE',$db->getAffectedRows());
	 }
	 function onAcyProcessAction_removequeue($cquery,$action,$num){
	 	$action['mailid'] = intval($action['mailid']);
		if(empty($action['mailid'])) return 'mailid not valid';
		$query = 'DELETE queueremove.* FROM `#__acymailing_queue` as queueremove ';
		$query .= ' LEFT JOIN `#__acymailing_subscriber` as sub ON queueremove.subid = sub.subid ';
		if(!empty($cquery->leftjoin)) $query .= ' LEFT JOIN '.implode(' LEFT JOIN ',$cquery->leftjoin);
		$query .= ' WHERE queueremove.mailid = '.$action['mailid'];
		if(!empty($cquery->where)) $query .= ' AND ('.implode(') AND (',$cquery->where).')';
	 	$db =& JFactory::getDBO();
	 	$db->setQuery($query);
	 	$db->query();
	 	return JText::sprintf('SUCC_DELETE_ELEMENTS',$db->getAffectedRows());
	 }
}//endclass