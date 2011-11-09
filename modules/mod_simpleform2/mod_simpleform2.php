<?php
/**
* ZyX SimpleForm2
*
* @package ZyX SimpleForm2
* @copyright (C) 2010 ZyX allForJoomla.ru
* @url http://www.allForJoomla.ru/
* @authors ZyX <info@litecms.ru>
**/
defined('_JEXEC') or die(':)');
require_once ( JPATH_BASE .DS.'modules'.DS.'mod_simpleform2'.DS.'simpleform2.class.php' );
$id = 'simpleForm2_'.rand(100,999);
$userFunc = $params->get('userCheckFunc','');
$userResultFunc = $params->get('userResultFunc','');
$okText = addslashes($params->get('okText','OK'));
$config =& JFactory::getConfig();
$cache = $params->get('cache',0);
$sysCache = $config->getValue('config.caching');
$script = '
jQuery.noConflict();
jQuery(document).ready(function(){
	jQuery("form#'.$id.'").bind("beforeSubmit",function() {
		return false;
	});
	jQuery("form#'.$id.'").bind("submit",function() {
		if(!document.getElementById("'.$id.'_wrap")){jQuery("#'.$id.'_submit").wrap("<span id=\''.$id.'_wrap\' />");}
		'.$id.'_tmp = jQuery("#'.$id.'_wrap").html();
		jQuery("#'.$id.'_wrap").html("<img src=\"'.JURI::root().'modules/mod_simpleform2/images/loading.gif\" alt=\"Loading...\" title=\"Loading...\" />");
		';
if($userFunc!=''){
	$script.= 'var uResult = '.$userFunc.'("'.$id.'");
		if(uResult!=true){jQuery("#'.$id.'_wrap").html('.$id.'_tmp);alert(uResult);return false;}';
}
$script.= 'jQuery(this).ajaxSubmit(function(data) {
				var key = data.substring(0,1);
				var text = data.substring(1);
				var captcha = jQuery("#captcha_'.$id.'");
				captcha.click();
				if(key=="="){
					';
			if($userResultFunc) $script.= $userResultFunc.'("'.$id.'",true,text);';
			else $script.= 'jQuery("form#'.$id.'").html(text);';
			$script.= '
				}
				else if(key=="!"){
					jQuery("#'.$id.'_wrap").html('.$id.'_tmp);
					';
			if($userResultFunc) $script.= $userResultFunc.'("'.$id.'",false,text);';
			else $script.= 'alert(text);';
			$script.= '
				}
				else{
					jQuery("#'.$id.'_wrap").html('.$id.'_tmp);
					';
			if($userResultFunc) $script.= $userResultFunc.'("'.$id.'",false,text);';
			else $script.= 'alert(text);';
			$script.= '
				}
			}
		);
		return false;
	});
});
';
$styles = 'div.sfCopyr{margin:10px 0;border-top:1px solid #ccc;text-align:right;}'."\n".'div.sfCopyr a{color:#ccc;}';
if($cache==1&&$sysCache==1){
	if(!defined('SIMPLEFORM2')){
		echo '<script type="text/javascript" src="'.JURI::root().'modules/mod_simpleform2/ajax/jquery.js"></script>'."\n";
		echo '<script type="text/javascript" src="'.JURI::root().'modules/mod_simpleform2/ajax/jquery.form.js"></script>'."\n";
	}
	echo '<style type="text/css">
	'.$styles.'
	</style>'."\n";
		echo '<script type="text/javascript">
	'.$script.'
	</script>';
}
else{
	$doc = &JFactory::getDocument();
	if(!defined('SIMPLEFORM2')){
		$doc->addScript(JURI::root().'modules/mod_simpleform2/ajax/jquery.js');
		$doc->addScript(JURI::root().'modules/mod_simpleform2/ajax/jquery.form.js');
	}
	$doc->addScriptDeclaration($script);
	$doc->addStyleDeclaration($styles);
}

$form = new simpleForm2($params->get('simpleCode',''));
$form->set('id',$id);
$form->set('moduleID',$module->id);
$form->set('_key',$params->get('domainKey',''));
$form->render();
defined('SIMPLEFORM2') or define('SIMPLEFORM2',1);
