<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class plgAcymailingOnline extends JPlugin
{
	function plgAcymailingOnline(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin =& JPluginHelper::getPlugin('acymailing', 'online');
			$this->params = new JParameter( $plugin->params );
		}
    }
	 function acymailing_getPluginType() {
	 	$onePlugin = null;
	 	$onePlugin->name = JText::_('WEBSITE_LINKS');
	 	$onePlugin->function = 'acymailingtagonline_show';
	 	$onePlugin->help = 'plugin-online';
	 	return $onePlugin;
	 }
	 function acymailingtagonline_show(){
		$others = array();
		$config = acymailing::config();
		$others['readonline'] = array('default'=> JText::_('VIEW_ONLINE',true), 'desc'=>JText::_('VIEW_ONLINE_LINK'));
		if($config->get('forward',true)){
			$others['forward'] = array('default'=> JText::_('FORWARD_FRIEND',true), 'desc'=>JText::_('FORWARD_FRIEND_LINK'));
		}
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
				setOnlineTag();
			}
			function setOnlineTag(){
				setTag('{'+selectedTag+'}'+document.adminForm.tagtext.value+'{/'+selectedTag+'}');
			}
		//-->
		</script>
<?php
		$text = JText::_('FIELD_TEXT').' : <input name="tagtext" size="100px" onchange="setOnlineTag();"><br/><br/>';
		$text .= '<table class="adminlist" cellpadding="1">';
		$k = 0;
		foreach($others as $tagname => $tag){
			$text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="changeTag(\''.$tagname.'\');" id="tr_'.$tagname.'" ><td>'.$tag['desc'].'</td></tr>';
			$k = 1-$k;
		}
		$text .= '</table>';
		echo $text;
	}
	function acymailing_replaceusertagspreview(&$email,&$user){
		return $this->acymailing_replaceusertags($email,$user);
	}
	function acymailing_replaceusertags(&$email,&$user){
	 	$match = '#{(readonline|forward)}(.*){/(readonline|forward)}#Uis';
		$variables = array('body','altbody');
		$found = false;
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match,$email->$var,$results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}
		if(!$found) return;
		$config = acymailing::config();
		$itemId = $config->get('itemid',0);
		$item = empty($itemId) ? '' : '&Itemid='.$itemId;
		$tags = array();
		$tmplview = '';
		$tmplforward = '';
		if(!empty($email->key) AND $this->params->get('addkey','yes') == 'yes'){
			$tmplview .= '&key='.$email->key;
			$tmplforward .= '&key='.$email->key;
		}
		if(!empty($user->key) AND $this->params->get('adduserkey','yes') == 'yes'){
			$tmplview .= '&subid='.$user->subid.'-'.$user->key;
			$tmplforward .= '&subid='.$user->subid.'-'.$user->key;
		}
		if($this->params->get('viewtemplate','standard') == 'notemplate') $tmplview .= '&tmpl=component';
		if($this->params->get('forwardtemplate','standard') == 'notemplate') $tmplforward .= '&tmpl=component';
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				if(isset($tags[$oneTag])) continue;
				if($allresults[1][$i] == 'readonline'){
					$link = acymailing::frontendLink('index.php?option=com_acymailing&ctrl=archive&task=view&mailid='.$email->mailid.$tmplview.$item);
				}elseif($allresults[1][$i] == 'forward'){
					$link = acymailing::frontendLink('index.php?option=com_acymailing&ctrl=archive&task=forward&mailid='.$email->mailid.$tmplforward.$item);
				}
				if(empty($allresults[2][$i])){ $tags[$oneTag] = $link;}
				else{$tags[$oneTag] = '<a style="text-decoration:none;" href="'.$link.'"><span class="acymailing_online">'.$allresults[2][$i].'</span></a>';}
			}
		}
		$email->body = str_replace(array_keys($tags),$tags,$email->body);
		if(!empty($email->altbody)) $email->altbody = str_replace(array_keys($tags),$tags,$email->altbody);
	 }
}//endclass