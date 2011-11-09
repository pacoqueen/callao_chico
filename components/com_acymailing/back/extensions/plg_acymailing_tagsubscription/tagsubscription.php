<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class plgAcymailingTagsubscription extends JPlugin
{
	function plgAcymailingTagsubscription(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin =& JPluginHelper::getPlugin('acymailing', 'tagsubscription');
			$this->params = new JParameter( $plugin->params );
		}
	}
	 function acymailing_getPluginType() {
	 	$onePlugin = null;
	 	$onePlugin->name = JText::_('SUBSCRIPTION');
	 	$onePlugin->function = 'acymailingtagsubscription_show';
	 	$onePlugin->help = 'plugin-tagsubscription';
	 	return $onePlugin;
	 }
	function onAcyDisplayActions(&$type){
	 	$type['list'] = JText::_('ACYMAILING_LIST');
		$status = array();
		$status[] = JHTML::_('select.option',1,JText::_('SUBSCRIBE_TO'));
		$status[] = JHTML::_('select.option',0,JText::_('REMOVE_FROM'));
		$lists = $this->_getLists();
		$otherlists = array();
		if(acymailing::level(3)){
			$db =& JFactory::getDBO();
			$db->setQuery('SELECT b.listid, b.name FROM #__acymailing_listcampaign as a LEFT JOIN #__acymailing_list as b on a.listid = b.listid GROUP BY b.listid ORDER BY b.ordering ASC');
			$otherlists = $db->loadObjectList('listid');
		}
		$listsdrop = array();
		foreach($lists as $oneList){
			$listsdrop[] = JHTML::_('select.option',$oneList->listid,$oneList->name);
			if(!empty($otherlists[$oneList->listid])) $listsdrop[] = JHTML::_('select.option',$oneList->listid.'_campaign',$otherlists[$oneList->listid]->name.' + '.JText::_('CAMPAIGN'));
		}
	 	return '<div id="action__num__list">'.JHTML::_('select.genericlist',   $status, "action[__num__][list][status]", 'class="inputbox" size="1"', 'value', 'text').' '.JHTML::_('select.genericlist',   $listsdrop, "action[__num__][list][selectedlist]", 'class="inputbox" size="1"', 'value', 'text').'</div>';
	 }
	 function _getLists(){
	 	if(!empty($this->allLists)) return $this->allLists;
	 	$list = acymailing::get('class.list');
		$this->allLists = $list->getLists();
		return $this->allLists;
	 }
	 function onAcyDisplayFilters(&$type){
	 	$type['list'] = JText::_('ACYMAILING_LIST');
	 	$status = acymailing::get('type.statusfilterlist');
		$lists = $this->_getLists();
		$listsdrop = array();
		foreach($lists as $oneList){
			$listsdrop[] = JHTML::_('select.option',$oneList->listid,$oneList->name);
		}
	 	$filter = '<div id="filter__num__list">'.$status->display("filter[__num__][list][status]",1,false).' '.JHTML::_('select.genericlist',   $listsdrop, "filter[__num__][list][selectedlist]", 'class="inputbox" size="1"', 'value', 'text');
	 	$filter .= '<br /><input name="filter[__num__][list][subdateinf]" /> < '.JText::_('SUBSCRIPTION_DATE').' < <input name="filter[__num__][list][subdatesup]" /></div>';
	 	return $filter;
	 }
	 function onAcyProcessFilter_list(&$query,$filter,$num){
	 	$otherconditions = '';
	 	if(!empty($filter['subdateinf'])){
  			$filter['subdateinf'] = acymailing::replaceDate($filter['subdateinf']);
			if(!is_numeric($filter['subdateinf'])) $filter['subdateinf'] = strtotime($filter['subdateinf']);
			if(!empty($filter['subdateinf'])) $otherconditions .= ' AND list'.$num.'.subdate > '.$filter['subdateinf'];
	 	}
	 	if(!empty($filter['subdatesup'])){
	 		$filter['subdatesup'] = acymailing::replaceDate($filter['subdatesup']);
			if(!is_numeric($filter['subdatesup'])) $filter['subdatesup'] = strtotime($filter['subdatesup']);
			if(!empty($filter['subdatesup'])) $otherconditions .= ' AND list'.$num.'.subdate < '.$filter['subdatesup'];
	 	}
	 	$query->leftjoin['list'.$num] = '#__acymailing_listsub AS list'.$num.' ON sub.subid = list'.$num.'.subid AND list'.$num.'.listid = '.intval($filter['selectedlist']).$otherconditions;
		if($filter['status'] == -2){
			$query->where[] = 'list'.$num.'.listid IS NULL';
		}else{
			$query->where[] = 'list'.$num.'.status = '.intval($filter['status']);
		}
	 }
	function onAcyProcessAction_list($cquery,$action,$num){
		$listid = $action['selectedlist'];
		$listClass = acymailing::get('class.list');
		if(is_numeric($listid)){
			if(empty($action['status'])){
				$query = 'DELETE listremove.* FROM '.acymailing::table('listsub').' as listremove ';
				$query .= ' LEFT JOIN #__acymailing_subscriber as sub ON listremove.subid = sub.subid ';
				if(!empty($cquery->leftjoin)) $query .= ' LEFT JOIN '.implode(' LEFT JOIN ',$cquery->leftjoin);
				$query .= ' WHERE listremove.listid = '.$listid;
				if(!empty($cquery->where)) $query .= ' AND ('.implode(') AND (',$cquery->where).')';
			}else{
				$query = 'INSERT IGNORE INTO '.acymailing::table('listsub').' (listid,subid,subdate,status) ';
				$query .= $cquery->getQuery(array($listid,'sub.subid',time(),1));
			}
			$cquery->db->setQuery($query);
			$cquery->db->query();
			$nbsubscribed = $cquery->db->getAffectedRows();
			$myList = $listClass->get($listid);
			if(empty($action['status'])){
				return JText::sprintf('IMPORT_REMOVE',$nbsubscribed,$myList->name);
			}else{
				return JText::sprintf('IMPORT_SUBSCRIBE_CONFIRMATION',$nbsubscribed,$myList->name);
			}
		}
		$listid = intval($listid);
		$myList = $listClass->get($listid);
		if(empty($action['status'])){
			$query = 'SELECT listremove.`subid` FROM #__acymailing_listsub as listremove';
			$query .= ' LEFT JOIN #__acymailing_subscriber as sub ON listremove.subid = sub.subid ';
			if(!empty($cquery->leftjoin)) $query .= ' LEFT JOIN '.implode(' LEFT JOIN ',$cquery->leftjoin);
			$query .= ' WHERE listremove.listid = '.$listid;
			if(!empty($cquery->where)) $query .= ' AND ('.implode(') AND (',$cquery->where).')';
		}else{
			$query = 'SELECT sub.`subid` FROM #__acymailing_subscriber as sub';
			$query .= ' LEFT JOIN #__acymailing_listsub as listsubscribe ON listsubscribe.subid = sub.subid AND listsubscribe.listid = '.$listid;
			if(!empty($cquery->leftjoin)) $query .= ' LEFT JOIN '.implode(' LEFT JOIN ',$cquery->leftjoin);
			$query .= ' WHERE listsubscribe.subid IS NULL';
			if(!empty($cquery->where)) $query .= ' AND ('.implode(') AND (',$cquery->where).')';
		}
		$cquery->db->setQuery($query);
		$subids = $cquery->db->loadResultArray();
		if(!empty($subids)){
			$listsubClass = acymailing::get('class.listsub');
		    $listsubClass->checkAccess = false;
		    $listsubClass->sendNotif = false;
		    $listsubClass->sendConf = false;
			foreach($subids as $subid){
				if(empty($action['status'])) $listsubClass->removeSubscription($subid,array($listid));
				else $listsubClass->addSubscription($subid,array('1' => array($listid)));
			}
		}
		$nbsubscribed = count($subids);
		if(empty($action['status'])){
			return JText::sprintf('IMPORT_REMOVE',$nbsubscribed,$myList->name);
		}else{
			return JText::sprintf('IMPORT_SUBSCRIBE_CONFIRMATION',$nbsubscribed,$myList->name);
		}
	}
	 function acymailingtagsubscription_show(){
		$others = array();
		$others['unsubscribe'] = array('name'=> JText::_('UNSUBSCRIBE_LINK'),'default'=>JText::_('UNSUBSCRIBE',true));
		$others['modify'] = array('name'=> JText::_('MODIFY_SUBSCRIPTION_LINK'), 'default'=>JText::_('MODIFY_SUBSCRIPTION',true));
		$others['confirm'] = array('name'=> JText::_('CONFIRM_SUBSCRIPTION_LINK'), 'default'=>JText::_('CONFIRM_SUBSCRIPTION',true));
?>
		<script language="javascript" type="text/javascript">
		<!--
			var selectedTag = '';
			function changeTag(tagName){
				selectedTag = tagName;
				defaultText = new Array();
<?php
				$k = 0;
				foreach($others as $tagname => $tag){
					echo "document.getElementById('tr_$tagname').className = 'row$k';";
					echo "defaultText['$tagname'] = '".$tag['default']."';";
				}
				$k = 1-$k;
?>
				document.getElementById('tr_'+tagName).className = 'selectedrow';
				document.adminForm.tagtext.value = defaultText[tagName];
				setSubscriptionTag();
			}
			function setSubscriptionTag(){
				setTag('{'+selectedTag+'}'+document.adminForm.tagtext.value+'{/'+selectedTag+'}');
			}
		//-->
		</script>
<?php
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration("window.addEvent('domready', function(){ changeTag('unsubscribe'); });");
		$text = JText::_('FIELD_TEXT').' : <input name="tagtext" size="100px" onchange="setSubscriptionTag();"><br/><br/>';
		$text .= '<table class="adminlist" cellpadding="1">';
		$k = 0;
		foreach($others as $tagname => $tag){
			$text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="changeTag(\''.$tagname.'\');" id="tr_'.$tagname.'" ><td>'.$tag['name'].'</td></tr>';
			$k = 1-$k;
		}
		$text .= '</table>';
		echo $text;
	 }
	function acymailing_replaceusertagspreview(&$email,&$user){
		return $this->acymailing_replaceusertags($email,$user);
	}
	function acymailing_replaceusertags(&$email,&$user){
		$match = '#(?:{|%7B)(modify|confirm|unsubscribe)(?:}|%7D)(.*)(?:{|%7B)/(modify|confirm|unsubscribe)(?:}|%7D)#Uis';
		$variables = array('subject','body','altbody');
		$found = false;
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match,$email->$var,$results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}
		if(!$found) return;
		$tags = array();
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				if(isset($tags[$oneTag])) continue;
				$tags[$oneTag] = $this->replaceSubscriptionTag($allresults,$i,$user,$email);
			}
		}
		foreach(array_keys($results) as $var){
			$email->$var = str_replace(array_keys($tags),$tags,$email->$var);
		}
	}
	function replaceSubscriptionTag(&$allresults,$i,&$user,&$email){
		if(empty($user->subid)){
			return '';
		}
		if(empty($user->key)){
			$user->key = md5(substr($user->email,0,strpos($user->email,'@')).time());
			$db =& JFactory::getDBO();
			$db->setQuery('UPDATE '.acymailing::table('subscriber').' SET `key`= '.$db->Quote($user->key).' WHERE subid = '.(int) $user->subid.' LIMIT 1');
			$db->query();
		}
		$config = acymailing::config();
		$itemId = $config->get('itemid',0);
		$item = empty($itemId) ? '' : '&Itemid='.$itemId;
		if($allresults[1][$i] == 'confirm'){ //confirm your subscription link
			$myLink = acymailing::frontendLink('index.php?option=com_acymailing&ctrl=user&task=confirm&subid='.$user->subid.'&key='.$user->key.$item,(bool) $this->params->get('confirmtemplate',false));
			if(empty($allresults[2][$i])) return $myLink;
			return '<a target="_blank" href="'.$myLink.'">'.$allresults[2][$i].'</a>';
		}elseif($allresults[1][$i] == 'modify'){ //modify your subscription link
			$myLink = acymailing::frontendLink('index.php?option=com_acymailing&ctrl=user&task=modify&subid='.$user->subid.'&key='.$user->key.$item,(bool) $this->params->get('modifytemplate',false));
			if(empty($allresults[2][$i])) return $myLink;
			return '<a style="text-decoration:none;" target="_blank" href="'.$myLink.'"><span class="acymailing_unsub">'.$allresults[2][$i].'</span></a>';
		}//unsubscribe link
		$myLink = acymailing::frontendLink('index.php?option=com_acymailing&ctrl=user&task=optout&mailid='.$email->mailid.'&subid='.$user->subid.'&key='.$user->key.$item,(bool) $this->params->get('unsubscribetemplate',false));
		if(empty($allresults[2][$i])) return $myLink;
		return '<a style="text-decoration:none;" target="_blank" href="'.$myLink.'"><span class="acymailing_unsub">'.$allresults[2][$i].'</span></a>';
	}
}//endclass