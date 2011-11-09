<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php')){
	echo 'This module can not work without the AcyMailing Component';
	return;
};
$doc =& JFactory::getDocument();
$config = acymailing::config();
switch($params->get('redirectmode','0')){
  case 1 :
    $redirectUrl = acymailing::completeLink('lists',false,true);
    $redirectUrlUnsub = $redirectUrl;
    break;
  case 2 :
    $redirectUrl = $params->get('redirectlink');
    $redirectUrlUnsub = $params->get('redirectlinkunsub');
    break;
  default :
  	if(isset($_SERVER["REQUEST_URI"])){
  		$requestUri = $_SERVER["REQUEST_URI"];
  	}else{
		$requestUri = $_SERVER['PHP_SELF'];
		if (!empty($_SERVER['QUERY_STRING'])) $requestUri = rtrim($requestUri,'/').'?'.$_SERVER['QUERY_STRING'];
  	}
	$redirectUrl = ((empty($_SERVER['HTTPS']) OR strtolower($_SERVER['HTTPS']) != "on" ) ? 'http://' : 'https://').$_SERVER["HTTP_HOST"].$requestUri;
	$redirectUrlUnsub = $redirectUrl;
	if($params->get('effect','normal') == 'mootools-box') $redirectUrlUnsub = $redirectUrl = '';
}
$formName = acymailing::getModuleFormName();
if($params->get('effect') == 'mootools-box' AND JRequest::getString('tmpl') != 'component'){
	$mootoolsButton = $params->get('mootoolsbutton','');
	if(empty($mootoolsButton)){
		$lang =& JFactory::getLanguage();
		$lang->load(ACYMAILING_COMPONENT,JPATH_SITE);
		$mootoolsButton = JText::_('SUBSCRIBE');
	}
	$mootoolsIntro = $params->get('mootoolsintro','');
	$moduleCSS = $config->get('css_module','default');
	if(!empty($moduleCSS)){
		$doc->addStyleSheet( ACYMAILING_CSS.'module_'.$moduleCSS.'.css' );
	}
	JHTML::_('behavior.modal','a.modal');
	require(JModuleHelper::getLayoutPath('mod_acymailing','popup'));
	return;
}
acymailing::initModule($params->get('includejs','header'),$params);
$userClass = acymailing::get('class.subscriber');
$identifiedUser = null;
if($params->get('loggedin',1)){
	$connectedUser =& JFactory::getUser();
	if(!empty($connectedUser->email)){
		$identifiedUser = $userClass->get($connectedUser->email);
	}
}
$visibleLists = trim($params->get('lists','None'));
$hiddenLists = trim($params->get('hiddenlists','All'));
$visibleListsArray = array();
$hiddenListsArray = array();
$listsClass = acymailing::get('class.list');
if(empty($identifiedUser->subid)){
	$allLists = $listsClass->getLists('listid');
}else{
	$allLists = $userClass->getSubscription($identifiedUser->subid,'listid');
}
if(acymailing::level(1)){
	$allLists = $listsClass->onlyCurrentLanguage($allLists);
}
if(acymailing::level(3)){
	$my = JFactory::getUser();
	foreach($allLists as $listid => $oneList){
		if(!$allLists[$listid]->published) continue;
		if(!acymailing::isAllowed($oneList->access_sub)){
			$allLists[$listid]->published = false;
		}
	}
}
if(strpos($visibleLists,',') OR is_numeric($visibleLists)){
	$allvisiblelists = explode(',',$visibleLists);
	foreach($allLists as $oneList){
		if($oneList->published AND in_array($oneList->listid,$allvisiblelists)) $visibleListsArray[] = $oneList->listid;
	}
}elseif(strtolower($visibleLists) == 'all'){
	foreach($allLists as $oneList){
		if($oneList->published){$visibleListsArray[] = $oneList->listid;}
	}
}
if(strpos($hiddenLists,',') OR is_numeric($hiddenLists)){
	$allhiddenlists = explode(',',$hiddenLists);
	foreach($allLists as $oneList){
		if($oneList->published AND in_array($oneList->listid,$allhiddenlists)) $hiddenListsArray[] = $oneList->listid;
	}
}elseif(strtolower($hiddenLists) == 'all'){
	$visibleListsArray = array();
	foreach($allLists as $oneList){
		if(!empty($oneList->published)){$hiddenListsArray[] = $oneList->listid;}
	}
}
if(!empty($visibleListsArray) AND !empty($hiddenListsArray)){
	$visibleListsArray =  array_diff($visibleListsArray, $hiddenListsArray);
}
$visibleLists = $params->get('dropdown',0) ? '' : implode(',',$visibleListsArray);
$hiddenLists = implode(',',$hiddenListsArray);
if(!empty($identifiedUser->subid)){
	$countSub = 0;
	$countUnsub = 0;
	foreach($visibleListsArray as $idOneList){
		if($allLists[$idOneList]->status == -1) $countSub++;
		elseif($allLists[$idOneList]->status == 1) $countUnsub++;
	}
	foreach($hiddenListsArray as $idOneList){
		if($allLists[$idOneList]->status == -1) $countSub++;
		elseif($allLists[$idOneList]->status == 1) $countUnsub++;
	}
}
$checkedLists = $params->get('listschecked','All');
if(strtolower($checkedLists) == 'all'){ $checkedListsArray = $visibleListsArray;}
elseif(strpos($checkedLists,',') OR is_numeric($checkedLists)){ $checkedListsArray = explode(',',$checkedLists);}
else{ $checkedListsArray = array();}
$nameCaption = $params->get('nametext',JText::_('NAMECAPTION'));
$emailCaption = $params->get('emailtext',JText::_('EMAILCAPTION'));
$displayOutside = $params->get('displayfields',0);
$displayInline = ($params->get('displaymode','vertical') == 'vertical') ? false : true;
$displayedFields = $params->get('customfields','name,email');
$fieldsToDisplay = explode(',',$displayedFields);
$extraFields = array();
if(acymailing::level(3)){
	$fieldsClass = acymailing::get('class.fields');
	$fieldsClass->prefix = 'user_';
	$fieldsClass->suffix = '_'.$formName;
	$extraFields = $fieldsClass->getFields('module',$identifiedUser);
	$newOrdering = array();
	$requiredFields = array();
	$validMessages = array();
	foreach($extraFields as $fieldnamekey => $oneField){
		if(in_array($fieldnamekey,$fieldsToDisplay)) $newOrdering[] = $fieldnamekey;
		if(in_array($oneField->type,array('text','date')) AND $params->get('fieldsize') AND (empty($extraFields[$fieldnamekey]->options['size']) || $params->get('fieldsize') < $extraFields[$fieldnamekey]->options['size'])){
			$extraFields[$fieldnamekey]->options['size'] = $params->get('fieldsize');
		}
		if(strlen($params->get($fieldnamekey.'text')) > 1){
			$extraFields[$fieldnamekey]->fieldname = $params->get($fieldnamekey.'text');
		}
		if(in_array($oneField->namekey,array('name','email'))) continue;
		if(!empty($oneField->required)){
			$requiredFields[] = $fieldnamekey;
			if(!empty($oneField->options['errormessage'])){
				$validMessages[] = addslashes($fieldsClass->trans($oneField->options['errormessage']));
			}else{
				$validMessages[] = addslashes(JText::sprintf('FIELD_VALID',$fieldsClass->trans($oneField->fieldname)));
			}
		}
	}
	$fieldsToDisplay = $newOrdering;
	if(!empty($requiredFields)){
		$js = "<!--
		acymailing['reqFields".$formName."'] = Array('".implode("','",$requiredFields)."');
		acymailing['validFields".$formName."'] = Array('".implode("','",$validMessages)."');
		//-->";
		if($params->get('includejs','header') == 'header'){
			$doc->addScriptDeclaration( $js );
		}else{
			echo "<script type=\"text/javascript\">
					$js
					</script>";
		}
	}
}
if(!in_array('email',$fieldsToDisplay)) $fieldsToDisplay[] = 'email';
if($params->get('effect') == 'mootools-slide'){
	$mootoolsButton = $params->get('mootoolsbutton','');
	if(empty($mootoolsButton)) $mootoolsButton = JText::_('SUBSCRIBE');
	$mootoolsIntro = $params->get('mootoolsintro','');
	JHTML::_('behavior.mootools');
	$js = "<!--
			window.addEvent('domready', function(){
				var mySlide = new Fx.Slide('acymailing_fulldiv_$formName');
				mySlide.hide();
				$('acymailing_togglemodule_$formName').addEvent('click', function(e){
					e = new Event(e);
					if(mySlide.wrapper.offsetHeight == 0){
						$('acymailing_togglemodule_$formName').className = 'acymailing_togglemodule acyactive';
					}else{
						$('acymailing_togglemodule_$formName').className = 'acymailing_togglemodule';
					}
					mySlide.toggle();
					e.stop();
				});
			});
			//-->";
	if($params->get('includejs','header') == 'header'){
			$doc->addScriptDeclaration( $js );
	}else{
		echo "<script type=\"text/javascript\">
				$js
				</script>";
	}
}
if($params->get('overlay',0)){
	JHTML::_('behavior.tooltip');
}
if($params->get('displaymode') == 'tableless'){
	require(JModuleHelper::getLayoutPath('mod_acymailing','tableless'));
}else{
	require(JModuleHelper::getLayoutPath('mod_acymailing'));
}
