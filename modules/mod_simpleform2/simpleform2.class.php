<?php
/**
 * SimpleForm2
 *
 * @version 1.0.7
 * @package SimpleForm2
 * @author ZyX (allforjoomla.ru)
 * @copyright (C) 2010 by ZyX (http://www.allforjoomla.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 *
 * If you fork this to create your own project,
 * please make a reference to allforjoomla.ru someplace in your code
 * and provide a link to http://www.allforjoomla.ru
 **/
defined('_JEXEC') or die(':)');

class simpleForm2 extends JObject{
	var $code = '';
	var $elements = array();
	var $attachments = array();
	var $id = null;
	var $_key = '';
	var $hasCaptcha = false;
	var $hasSubmit = false;
	var $side = 'backend';
	var $moduleID = null;
	var $template = 'default';
	var $defaultError = '%s';
	
	function simpleForm2($simpleCode,$isBackend=false){
		if($simpleCode=='') return false;
		if(!$isBackend) $this->side = 'frontend';
		$result = $this->parse($simpleCode);
		return $result;
	}
	
	function parse($code){
		$this->code = $code;
		$paramNames = array('regex','label','error','onclick','onchange','value','type','class','required','multiple','width','height','extensions','maxsize','color','background');
		$optionParamNames = array('label','value','selected','onclick','onchange');
		$params2mask = array('regex','label','error','onclick','onchange','value');
		foreach($params2mask as $param2mask){
			$this->code = preg_replace("/({[^}]+)(".$param2mask.")\=[\'\"](.*?)(?=[\'\"] )[\'\"]/sie",'"\\1\\2=\"".base64_encode("\\3")."\""',$this->code);
		}
		preg_match_all("/{element (.*?)(?=[\/ \'\"]})(?:[ \'\"]}(.*?)(?={\/element}))?/is",$this->code,$matches);
		if(!is_array($matches[1])||count($matches[1])==0){
			$this->setError(JText::_('No elements found in code'));
			return false;
		}
		foreach($matches[1] as $key=>$paramsText){
			$name = md5(serialize($paramsText)).$key;
			$elem = new simpleForm2Element($name,$name);
			$elem->code = $matches[0][$key];
			preg_match_all("/(".implode('|',$paramNames).")=[\'\"]([^\'\"]+)/is",$paramsText,$matchesP);
			if(!is_array($matchesP[1])||count($matchesP[1])==0){
				$this->setError(JText::_('Element without parameters found'));
				return false;
			}
			foreach($matchesP[1] as $keyP=>$paramName){
				if(in_array($paramName,$paramNames)){
					$elem->$paramName = $matchesP[2][$keyP];
					if(in_array($paramName,$params2mask)) $elem->$paramName = base64_decode($elem->$paramName);
				}
			}
			$elem->required = (bool)($elem->required=='required');
			$elem->multiple = (bool)($elem->multiple=='multiple');
			if(isset($elem->value)) $elem->values[] = $elem->value;
			preg_match_all("/{option (.*?)(?=})/is",$matches[2][$key],$matchesO);
			if(is_array($matchesO[1])&&count($matchesO[1])>0){
				$paramsText = null;
				foreach($matchesO[1] as $keyO=>$paramsText){
					preg_match_all("/(".implode('|',$optionParamNames).")=[\'\"]([^\'\"]+)/is",$paramsText,$matchesOP);
					if(is_array($matchesOP[1])&&count($matchesOP[1])>0){
						$option = new stdclass;
						foreach($matchesOP[1] as $keyP=>$paramName){
							if(in_array($paramName,$optionParamNames)){
								$option->$paramName = $matchesOP[2][$keyP];
								if(in_array($paramName,$params2mask)) $option->$paramName = base64_decode($option->$paramName);
								$option->selected = (bool)(@$option->selected=='selected');
							}
						}
						$option->code = $matchesO[0][$keyO].'}';
						$elem->values[] = $option->value;
						$elem->options[] = $option;
					}
				}
				$elem->code.= '{/element}';
			}
			else $elem->code.= '/}';
			if($elem->type=='captcha'){
				if(!isset($elem->color)||!preg_match("/\#?[0-9ABCDEFabcdef]{6}/",$elem->color)) $elem->color = '';
				if(!isset($elem->background)||!preg_match("/\#?[0-9ABCDEFabcdef]{6}/",$elem->background)) $elem->background = '';
				$elem->required = true;
				$session =& JFactory::getSession();
				$elem->values[] = $session->get('easyform2.captcha', null);
				if($this->hasCaptcha) $elem = null;
				$this->hasCaptcha = true;
			}
			else if($elem->type=='submit'){
				if($this->hasSubmit) $elem = null;
				$this->hasSubmit = true;
			}
			else if($elem->type=='file'){
				$exts = array();
				if($elem->extensions!=''){
					$tmpExts = explode(',',$elem->extensions);
					if(is_array($tmpExts)&&count($tmpExts)>0){
						foreach($tmpExts as $tmpExt){
							$tmpExt = trim($tmpExt);
							if(preg_match('/^[a-zA-Z0-9]{2,4}$/',$tmpExt)) $exts[] = $tmpExt;
						}
					}
				}
				$elem->extensions = $exts;
				$maxSize = 0;
				if($elem->maxsize!=''){
					$measure = strtolower(substr($elem->maxsize,-2));
					$size = (int)substr($elem->maxsize,0,-2);
					if($size>0&&($measure=='kb'||$measure=='mb')){
						if($measure=='mb') $maxSize = $size*1024*1024;
						else $maxSize = $size*1024;
					}
				}
				$elem->maxsize = $maxSize;
			}
			if($elem) $this->elements[] = $elem;
		}
		return true;
	}
	
	function checkDomain(){
		$URI=&JURI::getInstance();$key=$this->_key;$m=str_replace('www.','',$URI->getHost()).':ZyX_SF2';$e=5;$n='159378341817953177';$s=5;$coded='';$max=strlen($m);$packets=ceil($max/$s);for($i=0;$i<$packets;$i++){$packet=substr($m, $i*$s, $s);$code='0';for($j=0; $j<$s; $j++){$code=@bcadd($code, bcmul(ord($packet[$j]), bcpow('256',$j)));}$code=bcpowmod($code, $e, $n);$coded.=$code.' ';}$coded=str_replace(' ','-',trim($coded));return ($key==$coded);
	}
	
	function render(){
		if(count($this->elements)==0) return false;
		$id = $this->id;
		$code = $this->code;
		$form = '';
		$uri = &JURI::getInstance();
		$formBegin = '<form method="post" action="'.JURI::root().'modules/mod_simpleform2/engine.php" id="'.$id.'" name="'.$id.'" enctype="multipart/form-data" class="simpleForm">';
		$formBegin.= '<input type="hidden" name="moduleID" value="'.$this->moduleID.'" />';
		$formBegin.= '<input type="hidden" name="task" value="sendForm" />';
		$formBegin.= '<input type="hidden" name="Itemid" value="'.JRequest::getInt( 'Itemid').'" />';
		$formBegin.= '<input type="hidden" name="url" value="'.$uri->toString().'" />';
		$formEnd = '</form>'."\n";
		foreach($this->elements as $elem){
			$code = preg_replace('`'.preg_quote($elem->code,'`').'`', $this->renderElement($elem), $code, 1);
		}
		if(!preg_match('/\{form\}/i',$code)) $code = '{form}'.$code;
		if(!preg_match('/\{\/form\}/i',$code)) $code.= '{/form}';
		$code = str_replace(array('{form}','{/form}'),array($formBegin,$formEnd),$code);
		$code.= ($this->checkDomain()?'':base64_decode('PGRpdiBzdHlsZT0iYm9yZGVyLXRvcDoxcHggc29saWQgI2NjYzt0ZXh0LWFsaWduOnJpZ2h0OyI+PGEgdGFyZ2V0PSJfYmxhbmsiIHRpdGxlPSJzaW1wbGVGb3JtMiIgaHJlZj0iaHR0cDovL3d3dy5hbGxmb3Jqb29tbGEucnUiIHN0eWxlPSJ2aXNpYmlsaXR5OnZpc2libGU7ZGlzcGxheTppbmxpbmU7Y29sb3I6I2NjYzsiPnNpbXBsZUZvcm0yPC9hPjwvZGl2Pg=='));
		echo $code;
	}
	
	function processRequest($request){
		if(count($this->elements)==0){
			$this->setError(JText::_('No elements found in code'));
			return false;
		}
		$result = '';
		foreach($this->elements as $elem){
			if($elem->check($this,$request)!==true){
				$error = $elem->getError();
				$this->setError(($error?$error:sprintf($this->defaultError,$elem->label)));
				return false;
			}
			if(count($elem->requests)) $result.= $this->getTemplate('mail_form_item',array('label'=>$elem->label,'value'=>implode(', ',$elem->requests)));
		}
		return $result;
	}
	
	function renderElement($elem){
		$result = $elem->code;
		$result = preg_replace("/{\/?element(.*?)(?=})}/i",'',$result);
		$name = $elem->name;
		$id = $elem->id;
		$class = @$elem->class;
		$default = @$elem->value;
		$label = '';
		if($elem->label!='') $label = '<label for="'.$elem->id.'">'.$elem->label.($elem->required?' <span>*</span>':'').'</label> ';
		switch($elem->type){
			case 'text':
				$onchange = @$elem->onchange;
				$result.= '<input type="text" name="'.$name.'" id="'.$id.'"'.($class?' class="'.$class.'"':'').($onchange?' onchange="'.$onchange.'"':'').' value="'.$default.'" />';
			break;
			case 'textarea':
				$onchange = @$elem->onchange;
				$result.= '<textarea name="'.$name.'" id="'.$id.'"'.($class?' class="'.$class.'"':'').($onchange?' onchange="'.$onchange.'"':'').'>'.$default.'</textarea>';
			break;
			case 'select':
				$multi = @$elem->multiple;
				$onchange = @$elem->onchange;
				$result = '<select'.($multi?' multiple="multiple"':'').' name="'.$name.($multi?'[]':'').'" id="'.$id.'"'.($class?' class="'.$class.'"':'').($onchange?' onchange="'.$onchange.'"':'').'>'.$result;
				foreach($elem->options as $option){
					$optionCode = '<option value="'.$option->value.'"'.($option->selected?' selected="selected"':'').'>'.$option->label.'</option>';
					$result = str_replace($option->code,$optionCode,$result);
				}
				$result.= '</select>';
			break;
			case 'radio':
				foreach($elem->options as $option){
					$id = md5($name.'_'.$option->label);
					$onclick = @$option->onclick;
					$optionCode = '<input type="radio" name="'.$name.'" id="'.$id.'" value="'.$option->value.'"'.($class?' class="'.$class.'"':'').($onclick?' onclick="'.$onclick.'"':'').($option->selected?' checked="checked"':'').' /><label for="'.$id.'">'.$option->label.'</label>';
					$result = str_replace($option->code,$optionCode,$result);
				}
			break;
			case 'button':
				$default = @$elem->value;
				$onclick = @$elem->onclick;
				$result.= '<input type="button"'.($class?' class="'.$class.'"':'').($onclick?' onclick="'.$onclick.'"':'').' value="'.$default.'" />';
			break;
			case 'submit':
				$default = @$elem->value;
				$id = $this->id.'_submit';
				$result.= '<input'.($class?' class="'.$class.'"':'').' type="submit" value="'.$default.'" id="'.$id.'" />';
			break;
			case 'reset':
				$default = @$elem->value;
				$onclick = @$elem->onclick;
				$result.= '<input type="reset"'.($name?' name="'.$name.'"':'').($class?' class="'.$class.'"':'').($onclick?' onclick="'.$onclick.'"':'').' value="'.$default.'" />';
			break;
			case 'checkbox':
				$default = @$elem->value;
				$single = false;
				if(count($elem->options)==0){
					$elem->options = array($elem);
					$single = true;
				}
				foreach($elem->options as $option){
					$elid = $id;
					if(!$single){
						$elid = md5($name.'_'.$option->label);
						$default = @$option->value;
					}
					$onclick = @$option->onclick;
					$optionCode = '<input type="checkbox" name="'.$name.(!$single?'[]':'').'" id="'.$elid.'"'.($class?' class="'.$class.'"':'').($onclick?' onclick="'.$onclick.'"':'').($option->selected?' checked="checked"':'').' value="'.$default.'" />';
					if($single) $result.= $optionCode;
					else{
						$optionCode.= ' <label for="'.$elid.'">'.$option->label.'</label>';
						$result = str_replace($option->code,$optionCode,$result);
					}
				}
			break;
			case 'captcha':
				$default = @$elem->value;
				$urlAdd = array();
				$urlAdd[] = 'moduleID='.$this->moduleID;
				$urlAdd[] = 'rand='.rand(1,99999);
				$onclick = 'this.src=\''.JURI::root().'modules/mod_simpleform2/engine.php?task=captcha'.(count($urlAdd)?'&'.implode('&',$urlAdd):'').'&rand=\'+Math.random();';
				$result.= '<img id="captcha_'.$this->id.'" src="'.JURI::root().'modules/mod_simpleform2/engine.php?task=captcha'.(count($urlAdd)?'&'.implode('&',$urlAdd):'').'" alt="'.JText::_('Click to refresh').'" title="'.JText::_('Click to refresh').'" onclick="'.$onclick.'"'.($class?' class="'.$class.'"':'').' style="cursor:pointer;" />
				<div><input type="text" name="'.$name.'" id="'.$id.'"'.($class?' class="'.$class.'"':'').' value="'.$default.'" /></div>';
			break;
			case 'file':
				$onchange = @$elem->onchange;
				$result.= '<input type="file" name="'.$name.'" id="'.$id.'"'.($class?' class="'.$class.'"':'').($onchange?' onchange="'.$onchange.'"':'').' />';
			break;
		}
		if($label!='') $result = $label.$result;
		return $result;
	}
	
	function getUserIp() { 
		if (getenv('REMOTE_ADDR')) $ip = getenv('REMOTE_ADDR'); 
		elseif(getenv('HTTP_X_FORWARDED_FOR')) $ip = getenv('HTTP_X_FORWARDED_FOR'); 
		else $ip = getenv('HTTP_CLIENT_IP');
		return $ip;
	}
	
	function getTemplate($tmpl,$vars){
		global $mainframe;
		$tPath = JPATH_BASE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.'mod_simpleform2'.DS.$tmpl.'.php';
		$bPath = JPATH_BASE.DS.'modules'.DS.'mod_simpleform2'.DS.'tmpl'.DS.$tmpl.'.php';
		if(file_exists($tPath)) $tmplPath = $tPath;
		else $tmplPath = $bPath;
		unset($tmpl);
		unset($tPath);
		unset($bPath);
		extract($vars);
		ob_start();
		include($tmplPath);
		$content = ob_get_clean();
		return $content;
	}
}

class simpleForm2Element extends JObject{
	var $code = null;
	var $name = null;
	var $id = null;
	var $label = '';
	var $value = null;
	var $values = array();
	var $regex = null;
	var $error = null;
	var $type = null;
	var $requests = array();
	var $options = array();
	var $required = false;
	var $multiple = false;
	
	function simpleForm2Element($name,$id){
		$this->name = $name;
		$this->id = $id;
	}
	
	function check(&$form,$request){
		$checkVal = $this->getParam($request,$this->name,null);
		if(in_array($this->type,array('text','textarea'))){
			$checkVal = trim($checkVal);
			if(($this->required&&$checkVal=='')||($this->regex!=''&&!preg_match($this->regex,$checkVal))){
				$this->setError($this->error);
				return false;
			}
			$this->requests[] = $checkVal;
		}
		else if(in_array($this->type,array('select','radio','checkbox'))){
			if(is_array($checkVal)){
				$has = array_intersect($checkVal,$this->values);
				if($this->required&&count($has)==0||(count($checkVal)>0&&count($has)==0)){
					$this->setError($this->error);
					return false;
				}
				$this->requests = $checkVal;
			}
			else if(is_null($checkVal)){
				$this->requests[] = '';
				if($this->required){
					$this->setError($this->error);
					return false;
				}
			}
			else{
				$checkVal = trim($checkVal);
				if(($this->required&&$checkVal=='')||(count($this->values)>0&&!in_array($checkVal,$this->values))){
					$this->setError($this->error);
					return false;
				}
				$this->requests[] = $checkVal;
			}
		}
		else if(in_array($this->type,array('button','submit','reset'))){
			
		}
		else if($this->type=='captcha'){
			$session =& JFactory::getSession();
			$session->set('easyform2.captcha', null);
			$checkVal = trim($checkVal);
			if($checkVal==''||!in_array($checkVal,$this->values)){
				$this->setError($this->error);
				return false;
			}
		}
		else if($this->type=='file'){
			$fileData = $_FILES[$this->name];
			if($this->required&&!is_file($fileData['tmp_name'])){
				$this->setError($this->error);
				return false;
			}
			else if(!is_file($fileData['tmp_name'])) return true;
			if($this->maxsize>0&&$fileData['size']>$this->maxsize){
				$fSize = round($fileData['size']/1024,2);
				$error = sprintf(JText::_('File size is too big'),$fileData['name'].' ('.$fSize.'Kb)',round($this->maxsize/1024,2).'Kb');
				$this->setError($error);
				return false;
			}
			if(count($this->extensions)>0){
				$match = false;
				foreach($this->extensions as $ext){
					if(preg_match("/\.".$ext."$/",$fileData['name'])){
						$match = true;
						break;
					}
				}
				if(!$match){
					$this->setError(sprintf(JText::_('File extension is forbidden'),$fileData['name'],implode(', ',$this->extensions)));
					return false;
				}
			}
			$file = new stdclass;
			$file->file = $fileData['tmp_name'];
			$file->name = $fileData['name'];
			$form->attachments[] = $file;
		}
		
		return true;
	}
	
	function getParam( &$arr, $name, $def=null, $mask=0 ){
		static $noHtmlFilter	= null;
		static $safeHtmlFilter	= null;
	
		$var = JArrayHelper::getValue( $arr, $name, $def, '' );
	
		if (!($mask & 1) && is_string($var)) {
			$var = trim($var);
		}
	
		if ($mask & 2) {
			if (is_null($safeHtmlFilter)) {
				$safeHtmlFilter = & JFilterInput::getInstance(null, null, 1, 1);
			}
			$var = $safeHtmlFilter->clean($var, 'none');
		} elseif ($mask & 4) {
			$var = $var;
		} else {
			if (is_null($noHtmlFilter)) {
				$noHtmlFilter = & JFilterInput::getInstance(/* $tags, $attr, $tag_method, $attr_method, $xss_auto */);
			}
			$var = $noHtmlFilter->clean($var, 'none');
		}
		return $var;
	}
}
if(!function_exists('bcpowmod')){
	function bcpowmod($m,$e,$n) {
		$r="";
		while ($e!="0") {
			$t=bcmod($e,"4096");
			$r=substr("000000000000".decbin(intval($t)),-12).$r;
			$e=bcdiv($e,"4096");
		}
		$r=preg_replace("!^0+!","",$r);
		if ($r=="") $r="0";
		$m=bcmod($m,$n);
		$erb=strrev($r);
		$q="1";
		$a[0]=$m;
		for ($i=1;$i<strlen($erb);$i++) {
			$a[$i]=bcmod(bcmul($a[$i-1],$a[$i-1]),$n);
		}
		for ($i=0;$i<strlen($erb);$i++) {
			if ($erb[$i]=="1") {
				$q=bcmod(bcmul($q,$a[$i]),$n);
			}
		}
		return($q);
	}
}