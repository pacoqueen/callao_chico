<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class ConfigViewConfig extends JView
{
	function display($tpl = null)
	{
		JHTML::_('behavior.modal','a.modal');
		$toggleClass = acymailing::get('helper.toggle');
		$config = acymailing::config();
		$db =& JFactory::getDBO();
		$doc =& JFactory::getDocument();
		$app =& JFactory::getApplication();
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$lg = &JFactory::getLanguage();
		$language = $lg->getTag();
		$styleRemind= 'float:right;margin-right:30px;position:relative;';
		$loadLink = '<a onclick="window.document.getElementById(\'acymailing_messages_warning\').style.display = \'none\';return true;" class="modal" rel="{handler: \'iframe\', size:{x:800, y:500}}" href="index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=file&amp;task=latest&amp;code='.$language.'">'.JText::_('LOAD_LATEST_LANGUAGE').'</a>';
		if(!file_exists(ACYMAILING_ROOT.'language'.DS.$language.DS.$language.'.com_acymailing.ini')){
			if($config->get('errorlanguagemissing',1)){
				$notremind = '<small style="'.$styleRemind.'">'.$toggleClass->delete('acymailing_messages_warning','errorlanguagemissing_0','config',false,JText::_('DONT_REMIND')).'</small>';
				acymailing::display(JText::_('MISSING_LANGUAGE').' '.$loadLink.' '.$notremind,'warning');
			}
		}elseif(version_compare(JText::_('ACY_LANG_VERSION'),$config->get('version'),'<')){
			if($config->get('errorlanguageupdate',1)){
				$notremind = '<small style="'.$styleRemind.'">'.$toggleClass->delete('acymailing_messages_warning','errorlanguageupdate_0','config',false,JText::_('DONT_REMIND')).'</small>';
				acymailing::display(JText::_('UPDATE_LANGUAGE').' '.$loadLink.' '.$notremind,'warning');
			}
		}
		acymailing::setTitle(JText::_('CONFIGURATION'),'config','config');
		$bar = & JToolBar::getInstance('toolbar');
		if(acymailing::level(3)){
			$bar->appendButton( 'Link', 'fields', JText::_('EXTRA_FIELDS'), acymailing::completeLink('fields') );
			JToolBarHelper::custom('bounce', 'process', '',JText::_('BOUNCE_PROCESS'), false);
		}
		JToolBarHelper::custom('test', 'send', '',JText::_('SEND_TEST'), false);
		JToolBarHelper::divider();
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel('cancel',JText::_('ACY_CLOSE'));
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','config');
		if(acymailing::isAllowed($config->get('acl_cpanel_manage','all'))) $bar->appendButton( 'Link', 'acymailing', JText::_('ACY_CPANEL'), acymailing::completeLink('dashboard') );
		$elements = null;
		$elements->add_names = JHTML::_('select.booleanlist', "config[add_names]" , '',$config->get('add_names',true) );
		$elements->embed_images = JHTML::_('select.booleanlist', "config[embed_images]" , '',$config->get('embed_images',0) );
		$elements->embed_files = JHTML::_('select.booleanlist', "config[embed_files]" , '',$config->get('embed_files',1) );
		$elements->multiple_part = JHTML::_('select.booleanlist', "config[multiple_part]" , '',$config->get('multiple_part',0) );
		$mailermethod = array();
		$mailermethod[] = JHTML::_('select.option', 'phpmail', 'PHP Mail Function' );
		$mailermethod[] = JHTML::_('select.option', 'sendmail', 'SendMail' );
		$mailermethod[] = JHTML::_('select.option', 'qmail', 'QMail');
		$mailermethod[] = JHTML::_('select.option', 'smtp', 'SMTP Server' );
		$js = "function updateMailer(){";
			$js .= "mailermethod = window.document.getElementById('mailer_method').value;";
			$js .= "if(mailermethod == 'phpmail') {window.document.getElementById('smtp_config').style.display = 'none'; window.document.getElementById('sendmail_config').style.display = 'none';}";
			$js .= "if(mailermethod == 'smtp') {window.document.getElementById('smtp_config').style.display = 'block'; window.document.getElementById('sendmail_config').style.display = 'none';}";
			$js .= "if(mailermethod == 'qmail') {window.document.getElementById('smtp_config').style.display = 'none'; window.document.getElementById('sendmail_config').style.display = 'none';}";
			$js .= "if(mailermethod == 'sendmail') {window.document.getElementById('smtp_config').style.display = 'none'; window.document.getElementById('sendmail_config').style.display = 'block';}";
		$js .= '}';
		$js .='window.addEvent(\'domready\', function(){ updateMailer(); });';
		$doc->addScriptDeclaration( $js );
		$elements->mailer_method =  JHTML::_('select.genericlist',$mailermethod, "config[mailer_method]" , 'size="1" onchange="updateMailer()"', 'value', 'text', $config->get('mailer_method','phpmail'),'mailer_method');
		$encodingval = array();
		$encodingval[] = JHTML::_('select.option', 'binary', 'Binary' );
		$encodingval[] = JHTML::_('select.option', 'quoted-printable', 'Quoted-printable' );
		$encodingval[] = JHTML::_('select.option', '7bit', '7 Bit');
		$encodingval[] = JHTML::_('select.option', '8bit', '8 Bit' );
		$encodingval[] = JHTML::_('select.option', 'base64', 'Base 64' );
		$elements->encoding_format =  JHTML::_('select.genericlist', $encodingval, "config[encoding_format]" , 'size="1"', 'value', 'text', $config->get('encoding_format','base64'));
		$charset = acymailing::get('type.charset');
		$elements->charset = $charset->display("config[charset]",$config->get('charset','UTF-8'));
		$securedVals = array();
		$securedVals[] = JHTML::_('select.option', '','- - -');
		$securedVals[] = JHTML::_('select.option', 'ssl','SSL');
		$securedVals[] = JHTML::_('select.option', 'tls','TLS');
		$elements->smtp_secured = JHTML::_('select.genericlist',$securedVals, "config[smtp_secured]" , 'size="1"', 'value', 'text', $config->get('smtp_secured'));
		$elements->smtp_auth = JHTML::_('select.booleanlist', "config[smtp_auth]" , '',$config->get('smtp_auth',0) );
		$elements->smtp_keepalive = JHTML::_('select.booleanlist', "config[smtp_keepalive]" , '',$config->get('smtp_keepalive',1) );
		$elements->allow_visitor = JHTML::_('select.booleanlist', "config[allow_visitor]" , '',$config->get('allow_visitor',1) );
		$editorType = acymailing::get('type.editor');
		$elements->editor = $editorType->display('config[editor]',$config->get('editor'));
		$elements->subscription_message = JHTML::_('select.booleanlist', "config[subscription_message]" , '',$config->get('subscription_message',1) );
		$elements->confirmation_message = JHTML::_('select.booleanlist', "config[confirmation_message]" , '',$config->get('confirmation_message',1) );
		$elements->unsubscription_message = JHTML::_('select.booleanlist', "config[unsubscription_message]" , '',$config->get('unsubscription_message',1) );
		$elements->welcome_message = JHTML::_('select.booleanlist', "config[welcome_message]" , '',$config->get('welcome_message',1) );
		$elements->unsub_message = JHTML::_('select.booleanlist', "config[unsub_message]" , '',$config->get('unsub_message',1) );
		$elements->confirm_message = JHTML::_('select.booleanlist', "config[confirm_message]" , '',$config->get('confirm_message',0) );
		$elements->show_footer = JHTML::_('select.booleanlist', "config[show_footer]" , '',$config->get('show_footer',1) );
		if(acymailing::level(1)){
			$elements->forward = JHTML::_('select.booleanlist', "config[forward]" , '',$config->get('forward',false) );
		}else{
			$elements->forward = acymailing::getUpgradeLink('essential');
		}
		if(acymailing::level(1)){
			$js = "function updateDKIM(dkimval){if(dkimval == 1){document.getElementById('dkim_config').style.display = 'block';}else{document.getElementById('dkim_config').style.display = 'none';}}
					window.addEvent('load', function(){ updateDKIM(".$config->get('dkim',0).");});";
			$doc->addScriptDeclaration( $js );
			if(function_exists('openssl_sign')){
				$elements->dkim = JHTML::_('select.booleanlist', "config[dkim]" , 'onclick="updateDKIM(this.value)"',$config->get('dkim',0) );
			}else{
				$elements->dkim = '<input type="hidden" name="config[dkim]" value="0" />PHP Extension openssl not enabled';
			}
			$js = "function updateQueueProcess(newvalue){";
			$js .= "if(newvalue == 'onlyauto') {window.document.getElementById('method_auto').style.display = ''; window.document.getElementById('method_manual').style.display = 'none';}";
			$js .= "if(newvalue == 'auto') {window.document.getElementById('method_auto').style.display = ''; window.document.getElementById('method_manual').style.display = '';}";
			$js .= "if(newvalue == 'manual') {window.document.getElementById('method_auto').style.display = 'none'; window.document.getElementById('method_manual').style.display = '';}";
			$js .= '}';
			$js .='window.addEvent(\'domready\', function(){ updateQueueProcess(\''.$config->get('queue_type','auto').'\'); });';
			$doc->addScriptDeclaration( $js );
			$queueType = array();
			$queueType[] = JHTML::_('select.option', 'onlyauto',JText::_('AUTO_ONLY'));
			$queueType[] = JHTML::_('select.option', 'auto',JText::_('AUTO_MAN'));
			$queueType[] = JHTML::_('select.option', 'manual',JText::_('MANUAL_ONLY'));
			$elements->queue_type = JHTML::_('select.radiolist', $queueType, "config[queue_type]" , 'onclick="updateQueueProcess(this.value);"', 'value', 'text', $config->get('queue_type','auto'));
		}else{
			$elements->dkim = acymailing::getUpgradeLink('essential');
		}
		$delayTypeManual = acymailing::get('type.delay');
		$elements->queue_pause = $delayTypeManual->display('config[queue_pause]',$config->get('queue_pause'),0);
		$delayTypeAuto = acymailing::get('type.delay');
		$elements->cron_frequency = $delayTypeAuto->display('config[cron_frequency]',$config->get('cron_frequency'),0);
		$cssval = array('css_frontend' => 'component', 'css_module' => 'module', 'css_backend' => 'component');
		foreach($cssval as $configval => $type){
			$myvals = array();
			$myvals[] = JHTML::_('select.option', '',JText::_('ACY_NONE'));
			$regex = '^'.$type.'_([-_a-z0-9]*)\.css$';
			$allCSSFiles = JFolder::files( ACYMAILING_MEDIA.'css', $regex );
			foreach($allCSSFiles as $oneFile){
				preg_match('#'.$regex.'#i',$oneFile,$results);
				$myvals[] = JHTML::_('select.option', $results[1],$results[1]);
			}
			$js = 'onchange="updateCSSLink(\''.$configval.'\',\''.$type.'\',this.value);"';
			$currentVal = $config->get($configval,'default');
			$aStyle = empty($currentVal) ? 'style="display:none"' : '';
			$elements->$configval = JHTML::_('select.genericlist',   $myvals, 'config['.$configval.']', 'class="inputbox" size="1" '.$js, 'value', 'text', $config->get($configval,'default'),$configval.'_choice' );
			$linkEdit = 'index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=file&amp;task=css&amp;file='.$type.'_'.$config->get($configval,'default').'&amp;var='.$configval;
			$elements->$configval .= ' <a id="'.$configval.'_link" '.$aStyle.' class="modal" title="'.JText::_('ACY_EDIT',true).'"  href="'.$linkEdit.'" rel="{handler: \'iframe\', size:{x:800, y:500}}"><img class="icon16" src="'.ACYMAILING_IMAGES.'icons/icon-16-edit.png" alt="'.JText::_('ACY_EDIT',true).'"/></a>';
		}
		$js = "function updateCSSLink(myid,type,newval){
			if(newval){document.getElementById(myid+'_link').style.display = '';}else{document.getElementById(myid+'_link').style.display = 'none'}
			document.getElementById(myid+'_link').href = 'index.php?option=com_acymailing&tmpl=component&ctrl=file&task=css&file='+type+'_'+newval+'&var='+myid;
		}";
		$doc->addScriptDeclaration( $js );
		$elements->colortype = acymailing::get('type.color');
		$elements->use_sef = JHTML::_('select.booleanlist', "config[use_sef]" , '',$config->get('use_sef',0) );
		if(version_compare(JVERSION,'1.6.0','<')){
			$query = 'SELECT a.name, a.id as itemid, b.title  FROM `#__menu` as a LEFT JOIN `#__menu_types` as b on a.menutype = b.menutype WHERE a.access = 0 ORDER BY b.title ASC,a.ordering ASC';
		}else{
			$query = 'SELECT a.alias as name, a.id as itemid, b.title  FROM `#__menu` as a LEFT JOIN `#__menu_types` as b on a.menutype = b.menutype WHERE a.client_id=0 AND a.parent_id != 0 ORDER BY b.title ASC,a.ordering ASC';
		}
		$db->setQuery($query);
		$joomMenus = $db->loadObjectList();
		$menuvalues = array();
		$menuvalues[] = JHTML::_('select.option', '0',JText::_('ACY_NONE'));
		$lastGroup = '';
		foreach($joomMenus as $oneMenu){
			if($oneMenu->title != $lastGroup){
				if(!empty($lastGroup)) $menuvalues[] = JHTML::_('select.option', '</OPTGROUP>');
				$menuvalues[] = JHTML::_('select.option', '<OPTGROUP>',$oneMenu->title);
				$lastGroup = $oneMenu->title;
			}
			$menuvalues[] = JHTML::_('select.option', $oneMenu->itemid,$oneMenu->name);
		}
		$elements->acymailing_menu = JHTML::_('select.genericlist', $menuvalues, 'config[itemid]' , 'size="1"', 'value', 'text', $config->get('itemid'));
		$link = 'index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=email&amp;task=edit&amp;mailid=confirmation';
		$elements->editConfEmail = '<a class="modal" id="confirmemail"  href="'.$link.'" rel="{handler: \'iframe\', size:{x:800, y:500}}"><button onclick="return false">'.JText::_('EDIT_CONF_MAIL').'</button></a>';
		$link = 'index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=email&amp;task=edit&amp;mailid=notification_created';
		$elements->edit_notification_created = '<a class="modal" href="'.$link.'" rel="{handler: \'iframe\', size:{x:800, y:500}}"><button onclick="return false">'.JText::_('EDIT_NOTIFICATION_MAIL').'</button></a>';
		$link = 'index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=email&amp;task=edit&amp;mailid=notification_refuse';
		$elements->edit_notification_refuse = '<a class="modal" href="'.$link.'" rel="{handler: \'iframe\', size:{x:800, y:500}}"><button onclick="return false">'.JText::_('EDIT_NOTIFICATION_MAIL').'</button></a>';
		$link = 'index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=email&amp;task=edit&amp;mailid=notification_unsuball';
		$elements->edit_notification_unsuball = '<a class="modal" href="'.$link.'" rel="{handler: \'iframe\', size:{x:800, y:500}}"><button onclick="return false">'.JText::_('EDIT_NOTIFICATION_MAIL').'</button></a>';
		$link = 'index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=email&amp;task=edit&amp;mailid=notification_unsub';
		$elements->edit_notification_unsub = '<a class="modal" href="'.$link.'" rel="{handler: \'iframe\', size:{x:800, y:500}}"><button onclick="return false">'.JText::_('EDIT_NOTIFICATION_MAIL').'</button></a>';
		$link = 'index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=email&amp;task=edit&amp;mailid=modif';
		$elements->editModifEmail = '<a class="modal" id="modifemail"  href="'.$link.'" rel="{handler: \'iframe\', size:{x:800, y:500}}"><button onclick="return false">'.JText::_('EDIT_NOTIFICATION_MAIL').'</button></a>';

		$path = JLanguage::getLanguagePath(JPATH_ROOT);
		$dirs = JFolder::folders( $path );
		foreach ($dirs as $dir)
		{
			if(strlen($dir) != 5) continue;
			$xmlFiles = JFolder::files( $path.DS.$dir, '^([-_A-Za-z]*)\.xml$' );
			$xmlFile = reset($xmlFiles);
			if(empty($xmlFile)) continue;
			$data = JApplicationHelper::parseXMLLangMetaFile($path.DS.$dir.DS.$xmlFile);
			$oneLanguage = null;
			$oneLanguage->language 	= $dir;
			$oneLanguage->name = $data['name'];
			$languageFiles = JFolder::files( $path.DS.$dir, '^(.*)\.com_acymailing\.ini$' );
			$languageFile = reset($languageFiles);
			if(!empty($languageFile)){
				$linkEdit = 'index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=file&amp;task=language&amp;code='.$oneLanguage->language;
				$oneLanguage->edit = ' <a class="modal" title="'.JText::_('EDIT_LANGUAGE_FILE',true).'"  href="'.$linkEdit.'" rel="{handler: \'iframe\', size:{x:800, y:500}}"><img id="image'.$oneLanguage->language.'" class="icon16" src="'.ACYMAILING_IMAGES.'icons/icon-16-edit.png" alt="'.JText::_('EDIT_LANGUAGE_FILE',true).'"/></a>';
			}else{
				$linkEdit = 'index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=file&amp;task=language&amp;code='.$oneLanguage->language;
				$oneLanguage->edit = ' <a class="modal" title="'.JText::_('ADD_LANGUAGE_FILE',true).'"  href="'.$linkEdit.'" rel="{handler: \'iframe\', size:{x:800, y:500}}"><img id="image'.$oneLanguage->language.'" class="icon16"  src="'.ACYMAILING_IMAGES.'icons/icon-16-add.png" alt="'.JText::_('ADD_LANGUAGE_FILE',true).'"/></a>';
			}
			$languages[] = $oneLanguage;
		}
		$js = "function updateConfirmation(newvalue){";
		$js .= "if(newvalue == 0) {window.document.getElementById('confirmemail').style.display = 'none'; window.document.getElementById('confirm_redirect').disabled = true;}else{window.document.getElementById('confirmemail').style.display = 'inline'; window.document.getElementById('confirm_redirect').disabled = false;}";
		$js .= '}';
		$js .= "function updateModification(newvalue){ if(newvalue != 'none') {window.document.getElementById('modifemail').style.display = 'none';}else{window.document.getElementById('modifemail').style.display = 'inline';}} ";
		$js .='window.addEvent(\'load\', function(){ updateModification(\''.$config->get('allow_modif','data').'\'); updateConfirmation('.$config->get('require_confirmation',0).'); });';
		$doc->addScriptDeclaration( $js );
		$elements->require_confirmation = JHTML::_('select.booleanlist', "config[require_confirmation]" , 'onclick="updateConfirmation(this.value)"',$config->get('require_confirmation',0) );
		$allowmodif = array();
		$allowmodif[] = JHTML::_('select.option', "none",JText::_('JOOMEXT_NO'));
		$allowmodif[] = JHTML::_('select.option', "data",JText::_('ONLY_SUBSCRIPTION'));
		$allowmodif[] = JHTML::_('select.option', "all",JText::_('JOOMEXT_YES'));
		$elements->allow_modif = JHTML::_('select.radiolist', $allowmodif, "config[allow_modif]" , 'size="1" onclick="updateModification(this.value)"', 'value', 'text',$config->get('allow_modif','data'));
		$delayType4 = acymailing::get('type.delay');
		$elements->queue_delay = $delayType4->display('config[queue_delay]',$config->get('queue_delay',0),2);
		if(version_compare(JVERSION,'1.6.0','<')){
			$db->setQuery("SELECT name,published,id FROM `#__plugins` WHERE `folder` = 'acymailing' AND `element` NOT LIKE 'plg%' ORDER BY published DESC, ordering ASC");
		}else{
			$db->setQuery("SELECT name,enabled as published,extension_id as id FROM `#__extensions` WHERE `folder` = 'acymailing' AND `type`= 'plugin' AND `element` NOT LIKE 'plg%' ORDER BY enabled DESC, ordering ASC");
		}
		$plugins = $db->loadObjectList();
		if(version_compare(JVERSION,'1.6.0','<')){
			$db->setQuery("SELECT name,published,id FROM `#__plugins` WHERE (`folder` != 'acymailing' OR `element` LIKE 'plg%') AND (`name` LIKE '%acymailing%' OR `element` LIKE '%acymailing%') ORDER BY published DESC, ordering ASC");
		}else{
			$db->setQuery("SELECT name,enabled as published ,extension_id as id FROM `#__extensions` WHERE (`folder` != 'acymailing' OR `element` LIKE 'plg%') AND `type` = 'plugin' AND (`name` LIKE '%acymailing%' OR `element` LIKE '%acymailing%') ORDER BY enabled DESC, ordering ASC");
		}
		$integrationplugins = $db->loadObjectList();
		$this->assignRef('bounceaction',acymailing::get('type.bounceaction'));
		$this->assignRef('config',$config);
		$this->assignRef('languages',$languages);
		$this->assignRef('elements',$elements);
		$this->assignRef('plugins',$plugins);
		$this->assignRef('integrationplugins',$integrationplugins);
		jimport('joomla.html.pane');
		$tabs	=& JPane::getInstance('tabs');
		$this->assignRef('tabs',$tabs);
		$this->assignRef('toggleClass',$toggleClass);
		$images = array('icon-32-apply.png','icon-32-save.png','icon-32-cancel.png','icon-32-send.png','icon-32-preview.png','icon-32-edit.png','icon-32-help.png');
		foreach($images as $oneImage){
			$to = ACYMAILING_MEDIA.'images'.DS.'icons'.DS.$oneImage;
			 if(version_compare(JVERSION,'1.6.0','<')){
				$from = rtrim(JPATH_ADMINISTRATOR,DS).DS.'templates'.DS.'khepri'.DS.'images'.DS.'toolbar'.DS.$oneImage;
			}else{
				$from = rtrim(JPATH_ADMINISTRATOR,DS).DS.'templates'.DS.'bluestork'.DS.'images'.DS.'toolbar'.DS.$oneImage;
			}
			if(!file_exists($to) AND file_exists($from)){
				if(!JFile::copy($from,$to)) acymailing::display('Could not copy the file from '.$from.' to '.$to,'warning');
			}
		}
		if((version_compare(JVERSION,'1.6.0','<') AND !file_exists(rtrim(JPATH_SITE,DS).DS.'plugins'.DS.'acymailing'.DS.'tagsubscriber.php')) OR (!version_compare(JVERSION,'1.6.0','<') AND !file_exists(rtrim(JPATH_SITE,DS).DS.'plugins'.DS.'acymailing'.DS.'tagsubscriber'.DS.'tagsubscriber.php'))){
			acymailing::display(JText::sprintf('ERROR_PLUGINS','href="index.php?option=com_acymailing&amp;ctrl=update&amp;task=install"'),'warning');
		}
		return parent::display($tpl);
	}
}