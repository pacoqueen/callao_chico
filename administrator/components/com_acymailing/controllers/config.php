<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class ConfigController extends acymailingController{
	function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerDefaultTask('display');
	}
	function save(){
		$this->store();
		return $this->cancel();
	}
	function apply(){
		$this->store();
		return $this->display();
	}
	function listing(){
		if(!$this->isAllowed('configuration','manage')) return;
		return $this->display();
	}
	function store(){
		if(!$this->isAllowed('configuration','manage')) return;
		$app =& JFactory::getApplication();
		JRequest::checkToken() or die( 'Invalid Token' );
		$formData = JRequest::getVar( 'config', array(), '', 'array' );
		 $aclcats = JRequest::getVar( 'aclcat', array(), '', 'array' );
		 if(!empty($aclcats)){
		 	if(JRequest::getString('acl_configuration','all') != 'all' && !acymailing::isAllowed($formData['acl_configuration_manage'])){
		 		$app->enqueueMessage(JText::_( 'ACL_WRONG_CONFIG' ), 'notice');
		 		unset($formData['acl_configuration_manage']);
		 	}
		 	$deleteAclCats = array();
			$unsetVars = array('save','create','modify','delete','fields','export','import','view','send','schedule','bounce','test');
		 	foreach($aclcats as $oneCat){
		 		if(JRequest::getString('acl_'.$oneCat) == 'all'){
		 			foreach($unsetVars as $oneVar){
		 				unset($formData['acl_'.$oneCat.'_'.$oneVar]);
		 			}
		 			$deleteAclCats[] = $oneCat;
		 		}
		 	}
		 }
		$config =& acymailing::config();
		$status = $config->save($formData);
	 	if(!empty($deleteAclCats)){
			$db =& JFactory::getDBO();
	 		$db->setQuery("DELETE FROM `#__acymailing_config` WHERE `namekey` LIKE 'acl_".implode("%' OR `namekey` LIKE 'acl_",$deleteAclCats)."%'");
	 		$db->query();
	 	}
		if($status){
			$app->enqueueMessage(JText::_( 'JOOMEXT_SUCC_SAVED' ), 'message');
		}else{
			$app->enqueueMessage(JText::_( 'ERROR_SAVING' ), 'error');
		}
		$config->load();
	}
	function test(){
		if(!$this->isAllowed('configuration','manage')) return;
		$app =& JFactory::getApplication();
		$this->store();
		$config = acymailing::config();
		$user	=& JFactory::getUser();
		$mailClass = acymailing::get('helper.mailer');
		$addedName = $config->get('add_names',true) ? $mailClass->cleanText($user->name) : '';
		$mailClass->AddAddress($user->email,$addedName);
		$mailClass->Subject = 'Test e-mail from '.ACYMAILING_LIVE;
		$mailClass->Body = JText::_('TEST_EMAIL');
		$mailClass->SMTPDebug = 1;
		$result = $mailClass->send();
		if(!$result){
			$bounce = $config->get('bounce_email');
			if(!empty($bounce)){
				$app->enqueueMessage(JText::sprintf('ADVICE_BOUNCE',$bounce),'notice');
			}elseif($config->get('mailer_method') == 'smtp' AND !$config->get('smtp_auth') AND strlen($config->get('smtp_password')) > 1){
				$app->enqueueMessage(JText::_('ADVICE_SMTP_AUTH'),'notice');
			}elseif((strpos(ACYMAILING_LIVE,'localhost') OR strpos(ACYMAILING_LIVE,'127.0.0.1')) AND $config->get('mailer_method') != 'smtp'){
				$app->enqueueMessage(JText::_('ADVICE_LOCALHOST'),'notice');
			}
		}
		return $this->display();
	}
	function plgtrigger(){
		$pluginToTrigger = JRequest::getCmd('plg');
		$pluginType = JRequest::getCmd('plgtype','acymailing');
		if(version_compare(JVERSION,'1.6.0','<')){
			$path   = JPATH_PLUGINS.DS.$pluginType.DS.$pluginToTrigger.'.php';
		}else{
			$path   = JPATH_PLUGINS.DS.$pluginType.DS.$pluginToTrigger.DS.$pluginToTrigger.'.php';
		}
       if (!file_exists( $path )){
       		acymailing::display('Plugin not found: '.$path,'error');
       		return;
       }
		require_once( $path );
		$className = 'plg'.$pluginType.$pluginToTrigger;
		if(!class_exists($className)){
			acymailing::display('Class not found: '.$className,'error');
       		return;
		}
		$dispatcher =& JDispatcher::getInstance();
		$instance = new $className($dispatcher, array('name'=>$pluginToTrigger,'type'=>$pluginType));
		if(!method_exists($instance,'onTestPlugin')){
			acymailing::display('Method "onTestPlugin" not found: '.$className,'error');
       		return;
		}
		$instance->onTestPlugin();
		return;
	}
	function seereport(){
		if(!$this->isAllowed('configuration','manage')) return;
		$config = acymailing::config();
		$reportPath = JPath::clean(ACYMAILING_ROOT.trim(html_entity_decode($config->get('cron_savepath'))));
		$logFile = @file_get_contents($reportPath);
		if(empty($logFile)){
			acymailing::display(JText::_('EMPTY_LOG'),'info');
		}else{
			echo nl2br($logFile);
		}
	}
	function cleanreport(){
		if(!$this->isAllowed('configuration','manage')) return;
		jimport('joomla.filesystem.file');
		$config = acymailing::config();
		$reportPath = JPath::clean(ACYMAILING_ROOT.trim(html_entity_decode($config->get('cron_savepath'))));
		if(is_file($reportPath)){
			$result = JFile::delete($reportPath);
			if($result){
				acymailing::display(JText::_('SUCC_DELETE_LOG'),'success');
			}else{
				acymailing::display(JText::_('ERROR_DELETE_LOG'),'error');
			}
		}else{
			acymailing::display(JText::_('EXIST_LOG'),'info');
		}
	}
	function cancel(){
		$this->setRedirect( acymailing::completeLink('dashboard',false,true) );
	}
}