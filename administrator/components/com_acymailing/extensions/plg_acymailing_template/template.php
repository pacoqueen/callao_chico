<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class plgAcymailingTemplate extends JPlugin
{
	var $templates = array();
	var $tags = array();
	var $headerstyles = array();
	var $others = array();
	var $stylesheets = array();
	var $templateClass = '';
	var $config;
	function plgAcymailingTemplate(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin =& JPluginHelper::getPlugin('acymailing', 'template');
			$this->params = new JParameter( $plugin->params );
		}
		$this->config = acymailing::config();
		if(version_compare(PHP_VERSION, '5.0.0', '>=') && class_exists('DOMDocument') && function_exists('mb_convert_encoding')){
			require_once(ACYMAILING_FRONT.'inc'.DS.'emogrifier'.DS.'emogrifier.php');
		}
	}
	function acymailing_replaceusertagspreview(&$email,&$user){
		return $this->acymailing_replaceusertags($email,$user,false);
	}
	function _applyTemplate(&$email,$addbody){
		if(empty($email->tempid)) return;
		if(!isset($this->templates[$email->tempid])){
			$this->templates[$email->tempid] = array();
			if(empty($this->templateClass)){
				$this->templateClass = acymailing::get('class.template');
			}
			$template = $this->templateClass->get($email->tempid);
			if(!empty($template->styles) OR !empty($template->stylesheet)) $this->stylesheets[$email->tempid] = $this->templateClass->buildCSS($template->styles,$template->stylesheet);
			if(!empty($template->styles)){
				foreach($template->styles as $class => $style){
					if(empty($style)) continue;
					if(preg_match('#^tag_(.*)$#',$class,$result)){
						$this->tags[$email->tempid]['#< *'.$result[1].'((?:(?!style).)*)>#Ui'] = '<'.$result[1].' style="'.$style.'" $1>';
						if(strpos($style,'!important')) $this->headerstyles[$email->tempid][] = $result[1].'{ '.str_replace('!important','',$style).' }';
					}elseif($class == 'color_bg'){
						$this->others[$email->tempid][$class] = $style;
					}else{
						$this->templates[$email->tempid]['class="'.$class.'"'] = 'style="'.$style.'"';
					}
				}
			}
		}
		if($addbody AND !strpos($email->body,'</body>')){
			$before = '<html><head>'."\n";
			$before .= '<meta http-equiv="Content-Type" content="text/html; charset='.strtolower($this->config->get('charset')).'">'."\n";
			$before .= '<title>'.$email->subject.'</title>'."\n";
			if(!empty($this->headerstyles[$email->tempid])){
				$before .= '<style type="text/css">'."\n";
				$before .= implode("\n",$this->headerstyles[$email->tempid])."\n";
				$before .= '</style>'."\n";
			}
			$before .= '</head>'."\n".'<body';
			if(!empty($this->others[$email->tempid]['color_bg'])) $before .= ' bgcolor="'.$this->others[$email->tempid]['color_bg'].'" ';
			$before .= '>'."\n";
			$email->body = $before.$email->body.'</body>'."\n".'</html>';
		}
		if(!empty($this->templates[$email->tempid])){
			$email->body = str_replace(array_keys($this->templates[$email->tempid]),$this->templates[$email->tempid],$email->body);
		}
		if(!empty($this->tags[$email->tempid])){
			$email->body = preg_replace(array_keys($this->tags[$email->tempid]),$this->tags[$email->tempid],$email->body);
		}
		if(!empty($this->stylesheets[$email->tempid]) AND class_exists('Emogrifier')){
			$emogrifier = new Emogrifier($email->body,$this->stylesheets[$email->tempid]);
			$email->body = $emogrifier->emogrify();
			if(!$addbody AND strpos($email->body,'<!DOCTYPE') !== false){
				$email->body = preg_replace('#<\!DOCTYPE.*<body([^>]*)>#Usi','',$email->body);
				$email->body = preg_replace('#</body>.*$#si','',$email->body);
			}
		}
	}
	function acymailing_replaceusertags(&$email,&$user,$addbody = true){
		if(!$email->sendHTML) return;
		$this->_applyTemplate($email,$addbody);
		$email->body = preg_replace('#< *(tr|td|table)([^>]*)(style="[^"]*)background-image *: *url\(\'?([^)\']*)\'?\);?#Ui','<$1 background="$4" $2 $3',$email->body);
		$email->body = acymailing::absoluteURL($email->body);
		$email->body = preg_replace('#< *img([^>]*)(style="[^"]*)(float *: *)(right|left|top|bottom|middle)#Ui','<img$1 align="$4" hspace="5" $2$3$4',$email->body);
	 }//endfct
}//endclass