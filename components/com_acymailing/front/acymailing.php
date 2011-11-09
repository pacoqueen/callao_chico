<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
jimport('joomla.application.component.controller');
jimport( 'joomla.application.component.view');
include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php');
$view =  JRequest::getCmd('view');
if(!empty($view) AND !JRequest::getCmd('ctrl')){
	JRequest::setVar('ctrl',$view);
	$layout =  JRequest::getCmd('layout');
	if(!empty($layout)){
		JRequest::setVar('task',$layout);
	}
}
$taskGroup = JRequest::getCmd('ctrl',JRequest::getCmd('gtask','lists'));
global $Itemid;
if(empty($Itemid)){
	$urlItemid = JRequest::getInt('Itemid');
	if(!empty($urlItemid)) $Itemid = $urlItemid;
}
$doc =& JFactory::getDocument();
$doc->addScript(ACYMAILING_JS.'acymailing.js');
$config =& acymailing::config();
$cssFrontend = $config->get('css_frontend','default');
if(!empty($cssFrontend)){
	$doc->addStyleSheet( ACYMAILING_CSS.'component_'.$cssFrontend.'.css' );
}
if(!include(ACYMAILING_CONTROLLER_FRONT.$taskGroup.'.php')){
	return JError::raiseError( 404, 'Page not found : '.$taskGroup );
}
$className = ucfirst($taskGroup).'Controller';
$classGroup = new $className();
JRequest::setVar( 'view', $classGroup->getName() );
$classGroup->execute( JRequest::getCmd('task'));
$classGroup->redirect();
if(JRequest::getString('tmpl') !== 'component' AND !in_array(JRequest::getCmd('task'),array('unsub','saveunsub','optout'))){
	echo acymailing::footer();
}