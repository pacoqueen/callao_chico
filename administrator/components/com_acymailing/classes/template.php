<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class templateClass extends acymailingClass{
  var $tables = array('template');
  var $pkey = 'tempid';
  var $namekey = 'alias';
  var $templateNames = array();
  function get($tempid){
    $this->database->setQuery('SELECT * FROM '.acymailing::table('template').' WHERE tempid = '.intval($tempid).' LIMIT 1');
    $template = $this->database->loadObject();
    return $this->_prepareTemplate($template);
  }
	function copy(){
		JRequest::checkToken() or die( 'Invalid Token' );
		$cids = JRequest::getVar( 'cid', array(), '', 'array' );
		$db =& JFactory::getDBO();
		$time = time();
		foreach($cids as $oneId){
			$query = 'INSERT IGNORE INTO `#__acymailing_template` (`name`, `description`, `body`, `altbody`, `created`, `published`, `premium`, `ordering`, `namekey`, `styles`)';
			$query .= ' SELECT CONCAT(`name`,\'_copy\'), `description`, `body`, `altbody`, '.$time.', `published`, `premium`, `ordering`, CONCAT(`namekey`,\'copy'.$time.'\'), `styles` FROM `#__acymailing_template` WHERE `tempid` = '.(int) $oneId;
			$db->setQuery($query);
			$db->query();
		}
		$orderClass = acymailing::get('helper.order');
		$orderClass->pkey = 'tempid';
		$orderClass->table = 'template';
		$orderClass->reOrder();
		return $this->listing();
	}
  function getDefault(){
    $this->database->setQuery('SELECT * FROM '.acymailing::table('template').' WHERE premium = 1 AND published = 1 ORDER BY ordering ASC LIMIT 1');
    $template = $this->database->loadObject();
    return $this->_prepareTemplate($template);
  }
  function _prepareTemplate($template){
  	if(!isset($template->styles)) return $template;
    if(empty($template->styles)){
      $template->styles = array();
    }else{
      $template->styles = unserialize($template->styles);
    }
    return $template;
  }
  function saveForm(){
	$app =& JFactory::getApplication();
    $template = null;
    $template->tempid = acymailing::getCID('tempid');
    $formData = JRequest::getVar( 'data', array(), '', 'array' );
    foreach($formData['template'] as $column => $value){
      acymailing::secureField($column);
      $template->$column = strip_tags($value);
    }
    $styles = JRequest::getVar('styles',array(),'','array');
    foreach($styles as $class => $oneStyle){
		$styles[$class] = str_replace('"',"'",$oneStyle);
      if(empty($oneStyle)) unset($styles[$class]);
    }
    $newStyles = JRequest::getVar('otherstyles',array(),'','array');
    if(!empty($newStyles)){
    	foreach($newStyles['classname'] as $id => $className){
    		if(!empty($className) AND $className != JText::_('CLASS_NAME') AND !empty($newStyles['style'][$id]) AND $newStyles['style'][$id] != JText::_('CSS_STYLE')){
    			$styles[$className] = str_replace('"',"'",$newStyles['style'][$id]);
    		}
    	}
    }
    $template->styles = serialize($styles);

    $template->body = JRequest::getVar('editor_body','','','string',JREQUEST_ALLOWRAW);
    if(!empty($styles['color_bg'])){
    	$pat1 = '#^([^<]*<[^>]*background-color:)([^;">]{1,30})#i';
    	$found = false;
    	if(preg_match($pat1,$template->body)){
    		$template->body = preg_replace($pat1,'$1'.$styles['color_bg'],$template->body);
    		$found = true;
    	}
    	$pat2 = '#^([^<]*<[^>]*bgcolor=")([^;">]{1,10})#i';
    	if(preg_match($pat2,$template->body)){
    		$template->body = preg_replace($pat2,'$1'.$styles['color_bg'],$template->body);
    		$found = true;
    	}
    	if(!$found){
    		$template->body = '<div style="background-color:'.$styles['color_bg'].';" width="100%">'.$template->body.'</div>';
    	}
    }
	$pregreplace = array();
	$pregreplace['#<tr([^>"]*>([^<]*<td[^>]*>[ \n\s]*<img[^>]*>[ \n\s]*</ *td[^>]*>[ \n\s]*)*</ *tr)#Uis'] = '<tr style="line-height: 0px;" $1';
	$pregreplace['#<td(((?!style|>).)*>[ \n\s]*<img[^>]*>[ \n\s]*</ *td)#Uis'] = '<td style="line-height: 0px;" $1';
	$newbody = preg_replace(array_keys($pregreplace),$pregreplace,$template->body);
	if(!empty($newbody)) $template->body = $newbody;
    $template->description = JRequest::getVar('editor_description','','','string',JREQUEST_ALLOWRAW);
    $tempid = $this->save($template);
    if(!$tempid) return false;
    if(empty($template->tempid)){
      $orderClass = acymailing::get('helper.order');
      $orderClass->pkey = 'tempid';
      $orderClass->table = 'template';
      $orderClass->reOrder();
    }
    JRequest::setVar( 'tempid', $tempid);
    return true;
  }
  function save($element){
    if(empty($element->tempid)){
      if(empty($element->namekey)) $element->namekey = time().JFilterOutput::stringURLSafe($element->name);
    }else{
    	 if(file_exists(ACYMAILING_MEDIA.'css'.DS.'template_'.intval($element->tempid).'.css')){
    	 	jimport('joomla.filesystem.file');
    	 	if(!JFile::delete(ACYMAILING_MEDIA.'css'.DS.'template_'.intval($element->tempid).'.css')){
    	 		echo acymailing::display('Could not delete the file '.ACYMAILING_MEDIA.'css'.DS.'template_'.intval($element->tempid).'.css','error');
    	 	}
    	 }
    }
    if(!empty($element->styles) AND !is_string($element->styles))  $element->styles = serialize($element->styles);
    return parent::save($element);
  }
	function detecttemplates($folder){
		$allFiles = JFolder::files($folder);
		if(!empty($allFiles)){
			foreach($allFiles as $oneFile){
				if(preg_match('#^.*(html|htm)$#i',$oneFile)){
					if($this->installtemplate($folder.DS.$oneFile)) return true;
				}
			}
		}
		$status = false;
		$allFolders = JFolder::folders($folder);
		if(!empty($allFolders)){
			foreach($allFolders as $oneFolder){
				$status = $this->detecttemplates($folder.DS.$oneFolder) || $status;
			}
		}
		return $status;
	}
	function buildCSS($styles,$stylesheet){
		$inline = '';
		if(!empty($styles)){
			foreach($styles as $class => $style){
				if(preg_match('#^tag_(.*)$#',$class,$result)){
					if(!empty($style))	$inline.= $result[1].' { '.$style.' } '."\n";
				}elseif($class != 'color_bg'){
					if(!empty($style)) $inline.= '.'.$class.' {'.$style.'} '."\n";
				}else{
					if(!empty($style)) $inline.= 'body{background-color:'.$style.'} '."\n";
				}
			}
		}
		if(version_compare(PHP_VERSION, '5.0.0', '>=') && class_exists('DOMDocument') && function_exists('mb_convert_encoding')){
			$inline .= 'a img{ border:0px; text-decoration:none;} '."\n";
			$inline .= $stylesheet;
		}
		return $inline;
	}
  function installtemplate($filepath){
		$fileContent = file_get_contents($filepath);
		$newTemplate = null;
		$newTemplate->name = trim(preg_replace('#[^a-z0-9]#i',' ',substr(dirname($filepath),strpos($filepath,'_template'))));
		if(preg_match('#< *title[^>]*>(.*)< */ *title *>#Uis',$fileContent,$results)) $newTemplate->name = $results[1];
		$newFolder =preg_replace('#[^a-z0-9]#i','_',strtolower($newTemplate->name));
		$newTemplateFolder = $newFolder;
		$i = 1;
		while(is_dir(ACYMAILING_TEMPLATE.$newTemplateFolder)){
			$newTemplateFolder = $newFolder.'_'.$i;
			$i++;
		}
		$newTemplate->namekey = rand(0,10000).$newTemplateFolder;
		$moveResult = JFolder::move(dirname($filepath),ACYMAILING_TEMPLATE.$newTemplateFolder);
		if($moveResult !== true){
			acymailing::display(array('Error moving folder from '.dirname($filepath).' to '.ACYMAILING_TEMPLATE.$newTemplateFolder,$moveResult),'error');
			return false;
		}
		if(!file_exists(ACYMAILING_TEMPLATE.$newTemplateFolder.DS.'index.html')){
			$indexFile = '<html><body bgcolor="#FFFFFF"></body></html>';
			JFile::write(ACYMAILING_TEMPLATE.$newTemplateFolder.DS.'index.html',$indexFile);
		}
		$fileContent = preg_replace('#(src|background)[ ]*=[ ]*\"(?!(https?://|/))(?:\.\./|\./)?#','$1="media/com_acymailing/templates/'.$newTemplateFolder.'/',$fileContent);
		if(preg_match('#< *body[^>]*>(.*)< */ *body *>#Uis',$fileContent,$results)){ $newTemplate->body = $results[1];}else{$newTemplate->body = $fileContent;}
		if(preg_match('#< *style[^>]*>(.*)< */ *style *>#Uis',$fileContent,$results)){
			$newTemplate->stylesheet = preg_replace('#(<!--|-->)#s','',$results[1]);
			if(preg_match('#body *\{[^\}]*background-color:([^;]*);#Uis',$newTemplate->stylesheet,$backgroundresults)){
				$newTemplate->styles['color_bg'] = trim($backgroundresults[1]);
				$newTemplate->stylesheet = preg_replace('#(body *\{[^\}]*)background-color:[^;]*;#Uis','$1',$newTemplate->stylesheet);
			}
			$quickstyle = array('tag_h1' => 'h1','tag_h2' => 'h2', 'tag_h3' => 'h3','tag_h4' => 'h4','tag_h5' => 'h5','tag_h6' => 'h6','tag_a' => 'a','tag_ul' => 'ul','tag_li' => 'li','acymailing_unsub' => '\.acymailing_unsub','acymailing_online' => '\.acymailing_online','acymailing_title' => '\.acymailing_title','acymailing_content' => '\.acymailing_content','acymailing_readmore' => '\.acymailing_readmore');
			foreach($quickstyle as $styledb => $oneStyle){
				if(preg_match('#'.$oneStyle.' *{([^}]*)}#Uis',$newTemplate->stylesheet,$quickstyleresults)){
					$newTemplate->styles[$styledb] = trim(str_replace(array("\n","\r","\t","\s"),' ',$quickstyleresults[1]));
					$newTemplate->stylesheet = preg_replace('#'.$oneStyle.' *{[^}]*}#Uis','',$newTemplate->stylesheet);
				}
			}
		}
		if(!empty($newTemplate->styles['color_bg'])){
	    	$pat1 = '#^([^<]*<[^>]*background-color:)([^;">]{1,10})#i';
	    	$found = false;
	    	if(preg_match($pat1,$newTemplate->body)){
	    		$newTemplate->body = preg_replace($pat1,'$1'.$newTemplate->styles['color_bg'],$newTemplate->body);
	    		$found = true;
	    	}
	    	$pat2 = '#^([^<]*<[^>]*bgcolor=")([^;">]{1,10})#i';
	    	if(preg_match($pat2,$newTemplate->body)){
	    		$newTemplate->body = preg_replace($pat2,'$1'.$newTemplate->styles['color_bg'],$newTemplate->body);
	    		$found = true;
	    	}
	    	if(!$found){
	    		$newTemplate->body = '<div style="background-color:'.$newTemplate->styles['color_bg'].';" width="100%">'.$newTemplate->body.'</div>';
	    	}
    	}
    	$allFiles = JFolder::files(ACYMAILING_TEMPLATE.$newTemplateFolder);
    	$thumbnail = '';
    	$acypict = acymailing::get('helper.acypict');
    	$acypict->maxHeight = 190;
    	$acypict->maxWidth = 170;
    	foreach($allFiles as $oneFile){
    		if(preg_match('#(thumbnail|screenshot|muestra)#i',$oneFile)){
				if($acypict->available()){
					$newPict = $acypict->generateThumbnail(ACYMAILING_TEMPLATE.$newTemplateFolder.DS.$oneFile);
					if($newPict) $oneFile = basename($newPict['file']);
				}
    			$newTemplate->description = '<img src="media/com_acymailing/templates/'.$newTemplateFolder.'/'.$oneFile.'" />';
    			break;
    		}
    	}
		static $order = 0;
		if(empty($order)){
			$this->database->setQuery('SELECT MAX(ordering) FROM `#__acymailing_template`');
			$order =$this->database->loadResult();
		}
		$order++;
		$newTemplate->ordering = $order;
		$this->save($newTemplate);
		$this->templateNames[] = $newTemplate->name;
		return true;
  }
}