<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class plgAcymailingTagcbuser extends JPlugin
{
	function plgAcymailingTagcbuser(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin =& JPluginHelper::getPlugin('acymailing', 'tagcbuser');
			$this->params = new JParameter( $plugin->params );
		}
    }
	 function acymailing_getPluginType() {
	 	$onePlugin = null;
	 	$onePlugin->name = JText::_('CB User');
	 	$onePlugin->function = 'acymailingtagcb_show';
	 	$onePlugin->help = 'plugin-tagcbuser';
	 	return $onePlugin;
	 }
	function onAcyDisplayFilters($type){
		$db =& JFactory::getDBO();
		$fields = reset($db->getTableFields('#__comprofiler'));
		if(empty($fields)) return;
		$cbfield = array();
		foreach($fields as $oneField => $fieldType){
			$cbfield[] = JHTML::_('select.option',$oneField,$oneField);
		}
		$type['cbfield'] = JText::_('CB_FIELD');
		$operators = acymailing::get('type.operators');
		$return = '<div id="filter__num__cbfield">'.JHTML::_('select.genericlist',   $cbfield, "filter[__num__][cbfield][map]", 'class="inputbox" size="1"', 'value', 'text');
		$return.= ' '.$operators->display("filter[__num__][cbfield][operator]").' <input class="inputbox" type="text" name="filter[__num__][cbfield][value]" size="50" value=""></div>';
	 	return $return;
	 }
	  function onAcyProcessFilter_cbfield(&$query,$filter,$num){
	 	$query->leftjoin['cbfield'] = '#__comprofiler AS cbfield ON cbfield.id = sub.userid';
	 	$query->where[] = $query->convertQuery('cbfield',$filter['map'],$filter['operator'],$filter['value']);
	 }
	 function acymailingtagcb_show(){
		$text = '<table class="adminlist" cellpadding="1">';
		$db =& JFactory::getDBO();
		$tableInfos = $db->getTableFields(acymailing::table('comprofiler',false));
		$db->setQuery('SELECT name,type FROM #__comprofiler_fields');
		$fieldType = $db->loadObjectList('name');
		$k = 0;
		$fields = reset($tableInfos);
		$text .= '<tr style="cursor:pointer" class="row1" onclick="setTag(\'{cbtag:thumb}\');insertTag();" ><td>Thumb Avatar</td></tr>';
		foreach($fields as $fieldname => $oneField){
			$type = '';
			if(strpos(strtolower($oneField),'date') !== false) $type = '|type:date';
			if(!empty($fieldType[$fieldname]) AND $fieldType[$fieldname]->type == 'image') $type = '|type:image';
			$text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="setTag(\'{cbtag:'.$fieldname.$type.'}\');insertTag();" ><td>'.$fieldname.'</td></tr>';
			$k = 1-$k;
		}
		$text .= '</table>';
		echo $text;
	 }
	function acymailing_replaceusertagspreview(&$email,&$user){
		return $this->acymailing_replaceusertags($email,$user);
	}
	function acymailing_replaceusertags(&$email,&$user){
		$match = '#{cbtag:(.*)}#Ui';
		$variables = array('subject','body','altbody');
		$found = false;
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match,$email->$var,$results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}
		if(!$found) return;
		$values = null;
		if(!empty($user->userid)){
			$db= JFactory::getDBO();
			$db->setQuery('SELECT * FROM '.acymailing::table('comprofiler',false).' WHERE user_id = '.$user->userid.' LIMIT 1');
			$values = $db->loadObject();
		}
		$tags = array();
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				if(isset($tags[$oneTag])) continue;
				$arguments = explode('|',$allresults[1][$i]);
				$field = $arguments[0];
				unset($arguments[0]);
				$mytag = null;
				$mytag->default = $this->params->get('default_'.$field,'');
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
				$replaceme = isset($values->$field) ? $values->$field : $mytag->default;
				if(!empty($mytag->type)){
					if($mytag->type == 'date'){
						if(empty($mytag->format)) $mytag->format = JText::_('DATE_FORMAT_LC3');
						$replaceme = acymailing::getDate(strtotime($replaceme),$mytag->format);
					}
					if($mytag->type == 'image' AND !empty($replaceme)){
						$replaceme = '<img src="'.ACYMAILING_LIVE.'images/comprofiler/'.$replaceme.'"/>';
					}
				}
				if($field == 'thumb'){
					$replaceme = '<img src="'.ACYMAILING_LIVE.'images/comprofiler/tn'.$values->avatar.'"/>';
				}elseif($field == 'avatar'){
					$replaceme = '<img src="'.ACYMAILING_LIVE.'images/comprofiler/'.$values->avatar.'"/>';
				}
				if(!empty($mytag->lower)) $replaceme = strtolower($replaceme);
				if(!empty($mytag->ucwords)) $replaceme = ucwords($replaceme);
				if(!empty($mytag->ucfirst)) $replaceme = ucfirst($replaceme);
				if(!empty($mytag->urlencode)) $replaceme = urlencode($replaceme);
				$tags[$oneTag] = $replaceme;
			}
		}
		foreach($results as $var => $allresults){
			$email->$var = str_replace(array_keys($tags),$tags,$email->$var);
		}
	 }//endfct
}//endclass