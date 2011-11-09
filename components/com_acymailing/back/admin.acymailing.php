<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php')){
	echo "Could not load helper file";
	return;
}
if(defined('JDEBUG') AND JDEBUG) acymailing::displayErrors();
$taskGroup = JRequest::getCmd('ctrl',JRequest::getCmd('gtask','dashboard'));
$config =& acymailing::config();
$doc =& JFactory::getDocument();
$cssBackend = $config->get('css_backend','default');
if(!empty($cssBackend)){
	$doc->addStyleSheet( ACYMAILING_CSS.'component_'.$cssBackend.'.css' );
}
JHTML::_('behavior.tooltip');
$bar = & JToolBar::getInstance('toolbar');
$bar->addButtonPath(ACYMAILING_BUTTON);
if($taskGroup != 'update'){
	$app =& JFactory::getApplication();
	if(!$config->get('installcomplete')){
		$app->redirect(acymailing::completeLink('update&task=install',false,true));
	}
}
$lang =& JFactory::getLanguage();
$lang->load(ACYMAILING_COMPONENT,JPATH_SITE);
include(ACYMAILING_CONTROLLER.$taskGroup.'.php');
$className = ucfirst($taskGroup).'Controller';
$classGroup = new $className();
JRequest::setVar( 'view', $classGroup->getName() );
$classGroup->execute( JRequest::getCmd('task','listing'));
$classGroup->redirect();
if(JRequest::getString('tmpl') !== 'component'){
	echo acymailing::footer();
	if(acymailing::isAllowed($config->get('acl_subscriber_manage','all'))) JSubMenuHelper::addEntry(JText::_('USERS'), 'index.php?option=com_acymailing&ctrl=subscriber',$taskGroup == 'subscriber');
	if(acymailing::isAllowed($config->get('acl_lists_manage','all'))) JSubMenuHelper::addEntry(JText::_('LISTS'), 'index.php?option=com_acymailing&ctrl=list',$taskGroup == 'list');
	if(acymailing::isAllowed($config->get('acl_newsletters_manage','all'))) JSubMenuHelper::addEntry(JText::_('NEWSLETTERS'), 'index.php?option=com_acymailing&ctrl=newsletter',$taskGroup == 'newsletter');
	if(acymailing::level(2)){
		if(acymailing::isAllowed($config->get('acl_autonewsletters_manage','all'))) JSubMenuHelper::addEntry(JText::_('AUTONEWSLETTERS'), 'index.php?option=com_acymailing&ctrl=autonews',$taskGroup == 'autonews');
	}
	if(acymailing::level(3)){
		if(acymailing::isAllowed($config->get('acl_campaign_manage','all'))) JSubMenuHelper::addEntry(JText::_('CAMPAIGN'), 'index.php?option=com_acymailing&ctrl=campaign',$taskGroup == 'campaign' );
	}
	if(acymailing::isAllowed($config->get('acl_templates_manage','all'))) JSubMenuHelper::addEntry(JText::_('ACY_TEMPLATES'), 'index.php?option=com_acymailing&ctrl=template',$taskGroup == 'template');
	if(acymailing::isAllowed($config->get('acl_queue_manage','all'))) JSubMenuHelper::addEntry(JText::_('QUEUE'), 'index.php?option=com_acymailing&ctrl=queue',$taskGroup == 'queue');
	if(acymailing::isAllowed($config->get('acl_statistics_manage','all'))) JSubMenuHelper::addEntry(JText::_('STATISTICS'), 'index.php?option=com_acymailing&ctrl=stats',$taskGroup == 'stats');
	if(acymailing::isAllowed($config->get('acl_configuration_manage','all'))) JSubMenuHelper::addEntry(JText::_('CONFIGURATION'), 'index.php?option=com_acymailing&ctrl=config',$taskGroup == 'config');
}