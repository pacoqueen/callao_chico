<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class plgAcymailingTagsubscriber extends JPlugin
{
	function plgAcymailingTagsubscriber(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin =& JPluginHelper::getPlugin('acymailing', 'tagsubscriber');
			$this->params = new JParameter( $plugin->params );
		}
    }
	 function acymailing_getPluginType() {
	 	$onePlugin = null;
	 	$onePlugin->name = JText::_('SUBSCRIBER_SUBSCRIBER');
	 	$onePlugin->function = 'acymailingtagsubscriber_show';
	 	$onePlugin->help = 'plugin-tagsubscriber';
	 	return $onePlugin;
	 }
	 function acymailingtagsubscriber_show(){
	 	$descriptions['subid'] = JText::_('SUBSCRIBER_ID');
	 	$descriptions['email'] = JText::_('SUBSCRIBER_EMAIL');
	 	$descriptions['name'] = JText::_('SUBSCRIBER_NAME');
	 	$descriptions['userid'] = JText::_('SUBSCRIBER_USERID');
	 	$descriptions['ip'] = JText::_('SUBSCRIBER_IP');
	 	$descriptions['created'] = JText::_('SUBSCRIBER_CREATED');
		$text = '<table class="adminlist" cellpadding="1">';
		$db =& JFactory::getDBO();
		$tableInfos = $db->getTableFields(acymailing::table('subscriber'));
		$others = array();
		$others['{subtag:name|part:first|ucfirst}'] = array('name'=> JText::_('SUBSCRIBER_FIRSTPART'), 'desc'=>JText::_('SUBSCRIBER_FIRSTPART').' '.JText::_('SUBSCRIBER_FIRSTPART_DESC'));
		$others['{subtag:name|part:last|ucfirst}'] = array('name'=> JText::_('SUBSCRIBER_LASTPART'), 'desc'=>JText::_('SUBSCRIBER_LASTPART').' '.JText::_('SUBSCRIBER_LASTPART_DESC'));
		$k = 0;
		$fields = reset($tableInfos);
		foreach($fields as $fieldname => $oneField){
			if(!isset($descriptions[$fieldname]) AND $oneField != 'varchar') continue;
			$type = '';
			if($fieldname == 'created') $type = '|type:time';
			$text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="setTag(\'{subtag:'.$fieldname.$type.'}\');insertTag();" ><td>'.$fieldname.'</td><td>'.@$descriptions[$fieldname].'</td></tr>';
			$k = 1-$k;
		}
		foreach($others as $tagname => $tag){
			$text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="setTag(\''.$tagname.'\');insertTag();" ><td>'.$tag['name'].'</td><td>'.$tag['desc'].'</td></tr>';
			$k = 1-$k;
		}
		$text .= '</table>';
		echo $text;
	 }
	function acymailing_replaceusertagspreview(&$email,&$user){
		return $this->acymailing_replaceusertags($email,$user);
	}
	function acymailing_replaceusertags(&$email,&$user){
		$match = '#(?:{|%7B)subtag:(.*)(?:}|%7D)#Ui';
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
				$tags[$oneTag] = $this->replaceSubTag($allresults,$i,$user);
			}
		}
		foreach(array_keys($results) as $var){
			$email->$var = str_replace(array_keys($tags),$tags,$email->$var);
		}
	}
	function replaceSubTag(&$allresults,$i,&$user){
		$arguments = explode('|',strip_tags($allresults[1][$i]));
		$field = $arguments[0];
		unset($arguments[0]);
		$mytag = null;
		$mytag->default = '';
		if(!empty($arguments)){
			foreach($arguments as $onearg){
				$args = explode(':',$onearg);
				if(isset($args[1])){
					$mytag->$args[0] = $args[1];
				}else{
					$mytag->$args[0] = 1;
				}
			}
		}
		$replaceme = (isset($user->$field) && strlen($user->$field) > 0) ? $user->$field : $mytag->default;
		if(!empty($mytag->part)){
			$parts = explode(' ',$replaceme);
			if($mytag->part == 'last'){
				$replaceme = count($parts)>1 ? end($parts) : '';
			}else{
				$replaceme = reset($parts);
			}
		}
		if(!empty($mytag->type)){
			if($mytag->type == 'date'){
				$replaceme = acymailing::getDate(strtotime($replaceme));
			}elseif($mytag->type == 'time'){
				$replaceme = acymailing::getDate($replaceme);
			}
		}
		if(!empty($mytag->lower)) $replaceme = strtolower($replaceme);
		if(!empty($mytag->ucwords)) $replaceme = ucwords($replaceme);
		if(!empty($mytag->ucfirst)) $replaceme = ucfirst($replaceme);
		if(!empty($mytag->urlencode)) $replaceme = urlencode($replaceme);
		return $replaceme;
	}
	function onAcyDisplayFilters(&$type){
		$db =& JFactory::getDBO();
		$fields = reset($db->getTableFields('#__acymailing_subscriber'));
		if(empty($fields)) return;
		$field = array();
		foreach($fields as $oneField => $fieldType){
			$field[] = JHTML::_('select.option',$oneField,$oneField);
		}
		$type['acymailingfield'] = JText::_('ACYMAILING_FIELD');
		$operators = acymailing::get('type.operators');
		$return = '<div id="filter__num__acymailingfield">'.JHTML::_('select.genericlist',   $field, "filter[__num__][acymailingfield][map]", 'class="inputbox" size="1"', 'value', 'text');
		$return.= ' '.$operators->display("filter[__num__][acymailingfield][operator]").' <input class="inputbox" type="text" name="filter[__num__][acymailingfield][value]" size="50" value=""></div>';
	 	return $return;
	 }
	  function onAcyProcessFilter_acymailingfield(&$query,$filter,$num){
	  	if($filter['map'] == 'created' AND !is_numeric($filter['value'])){
	  		$filter['value'] = acymailing::replaceDate($filter['value']);
			if(!is_numeric($filter['value'])) $filter['value'] = strtotime($filter['value']);
		}
	 	$query->where[] = $query->convertQuery('sub',$filter['map'],$filter['operator'],$filter['value']);
	 }
	 function onAcyDisplayActions(&$type){
	 	$type['acymailingfield'] = JText::_('ACYMAILING_FIELD');
	 	$status = array();
		$status[] = JHTML::_('select.option','confirm',JText::_('CONFIRM_USERS'));
		$status[] = JHTML::_('select.option','enable',JText::_('ENABLE_USERS'));
		$status[] = JHTML::_('select.option','block',JText::_('BLOCK_USERS'));
		$status[] = JHTML::_('select.option','delete',JText::_('DELETE_USERS'));
	 	return '<div id="action__num__acymailingfield">'.JHTML::_('select.genericlist',   $status, "action[__num__][acymailingfield][action]", 'class="inputbox" size="1"', 'value', 'text').'</div>';
	 }
	 function onAcyProcessAction_acymailingfield($cquery,$action,$num){
		$subClass = acymailing::get('class.subscriber');
	 	if($action['action'] == 'confirm'){
	 		$cquery->where[] = 'sub.confirmed = 0';
			$cquery->db->setQuery($cquery->getQuery(array('sub.subid')));
			$allSubids = $cquery->db->loadResultArray();
			if(!empty($allSubids)){
				$subClass->sendConf = false;
				$subClass->sendWelcome = false;
				$subClass->sendNotif = false;
				foreach($allSubids as $oneId){
					$subClass->confirmSubscription($oneId);
				}
			}
			return JText::sprintf('NB_CONFIRMED',count($allSubids));
	 	}
	 	if($action['action'] == 'enable'){
			$query = 'UPDATE #__acymailing_subscriber as sub';
			if(!empty($cquery->leftjoin)) $query .= ' LEFT JOIN '.implode(' LEFT JOIN ',$cquery->leftjoin);
			$query .= " SET sub.enabled = 1";
			if(!empty($cquery->where)) $query .= ' WHERE ('.implode(') AND (',$cquery->where).')';
			$cquery->db->setQuery($query);
			$cquery->db->query();
			$nbAffected = $cquery->db->getAffectedRows();
			return JText::sprintf('NB_ENABLED',$nbAffected);
	 	}
	 	if($action['action'] == 'block'){
	 		$query = 'UPDATE #__acymailing_subscriber as sub';
			if(!empty($cquery->leftjoin)) $query .= ' LEFT JOIN '.implode(' LEFT JOIN ',$cquery->leftjoin);
			$query .= " SET sub.enabled = 0";
			if(!empty($cquery->where)) $query .= ' WHERE ('.implode(') AND (',$cquery->where).')';
			$cquery->db->setQuery($query);
			$cquery->db->query();
			$nbAffected = $cquery->db->getAffectedRows();
			return JText::sprintf('NB_BLOCKED',$nbAffected);
	 	}
	 	if($action['action'] == 'delete'){
 			$query = $cquery->getQuery(array('sub.subid'));
			$cquery->db->setQuery($query);
			$allSubids = $cquery->db->loadResultArray();
			$nbAffected = $subClass->delete($allSubids);
			return JText::sprintf('IMPORT_DELETE',$nbAffected);
	 	}
	 	return 'Filter AcyMailingField error, action not found : '.$action['action'];
	}
}//endclass