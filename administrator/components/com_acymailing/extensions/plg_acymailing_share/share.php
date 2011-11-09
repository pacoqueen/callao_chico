<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class plgAcymailingShare extends JPlugin
{
	function plgAcymailingShare(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin =& JPluginHelper::getPlugin('acymailing', 'share');
			$this->params = new JParameter( $plugin->params );
		}
    }
	 function acymailing_getPluginType() {
	 	$onePlugin = null;
	 	$onePlugin->name = JText::sprintf('SOCIAL_SHARE','...');
	 	$onePlugin->function = 'acymailingtagshare_show';
	 	$onePlugin->help = 'plugin-share';
	 	return $onePlugin;
	 }
	 function acymailingtagshare_show(){
		$text = '<table class="adminlist" cellpadding="1">';
		$networks = array();
		$networks['facebook'] = JText::sprintf('SOCIAL_SHARE','Facebook');
		$networks['linkedin'] = JText::sprintf('SOCIAL_SHARE','LinkedIn');
		$networks['twitter'] = JText::sprintf('SOCIAL_SHARE','Twitter');
		$k = 0;
		foreach($networks as $name => $desc){
			$text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="setTag(\'{share:'.$name.'}\');insertTag();" ><td>'.$desc.'</td></tr>';
			$k = 1-$k;
		}
		$text .= '</table>';
		echo $text;
	 }
	function acymailing_replacetags(&$email){
		$match = '#{share:(.*)}#Ui';
		$variables = array('body','altbody');
		$found = false;
		$results = array();
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match,$email->$var,$results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}
		if(!$found) return;
		$archiveLink = acymailing::frontendLink('index.php?option=com_acymailing&ctrl=archive&task=view&mailid='.$email->mailid,$this->params->get('template') == 'component' ? true : false);
		$tags = array();
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $tagname){
				if(isset($tags[$tagname])) continue;
				$arguments = explode('|',$allresults[1][$i]);
				$tag = null;
				$tag->network = $arguments[0];
				for($i=1,$a=count($arguments);$i<$a;$i++){
					$args = explode(':',$arguments[$i]);
					if(isset($args[1])){
						$tag->$args[0] = $args[1];
					}else{
						$tag->$args[0] = true;
					}
				}
				if($tag->network == 'facebook'){
					$tags[$tagname] = '<a target="_blank" href="http://www.facebook.com/sharer.php?u='.urlencode($archiveLink).'&t='.urlencode($email->subject).'" title="'.JText::sprintf('SOCIAL_SHARE','Facebook').'"><img src="'.ACYMAILING_LIVE.$this->params->get('picturefb','media/com_acymailing/images/fbshare.gif').'" /></a>';
				}elseif($tag->network == 'twitter'){
					$text = JText::sprintf('SHARE_TEXT',$archiveLink);
					$tags[$tagname] = '<a target="_blank" href="http://twitter.com/home?status='.urlencode($text).'" title="'.JText::sprintf('SOCIAL_SHARE','Twitter').'"><img src="'.ACYMAILING_LIVE.$this->params->get('picturetwitter','media/com_acymailing/images/twittershare.png').'" /></a>';
				}elseif($tag->network == 'linkedin'){
					$tags[$tagname] = '<a target="_blank" href="http://www.linkedin.com/shareArticle?mini=true&url='.urlencode($archiveLink).'&title='.urlencode($email->subject).'" title="'.JText::sprintf('SOCIAL_SHARE','LinkedIn').'"><img src="'.ACYMAILING_LIVE.$this->params->get('picturelinkedin','media/com_acymailing/images/linkedin.png').'" /></a>';
				}
			}
		}
		$email->body = str_replace(array_keys($tags),$tags,$email->body);
		$email->altbody = str_replace(array_keys($tags),'',$email->altbody);
	}
}//endclass