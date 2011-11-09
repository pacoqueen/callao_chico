<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class FileController extends acymailingController{
	function language(){
		JRequest::setVar( 'layout', 'language'  );
		return parent::display();
	}
	function save(){
		JRequest::checkToken() or die( 'Invalid Token' );
		$this->_savelanguage();
		return $this->language();
	}
	function savecss(){
		if(!$this->isAllowed('configuration','manage')) return;
		JRequest::checkToken() or die( 'Invalid Token' );
		$file = JRequest::getCmd('file');
		if(!preg_match('#^([-a-z0-9]*)_([-_a-z0-9]*)$#i',$file,$result)){
			acymailing::display('Could not load the file '.$file.' properly');
			exit;
		}
		$type = $result[1];
		$fileName = $result[2];
		jimport('joomla.filesystem.file');
		$path = ACYMAILING_MEDIA.'css'.DS.$type.'_'.$fileName.'.css';
		$csscontent = JRequest::getString('csscontent');
		$alreadyExists = file_exists($path);
		if(JFile::write($path, $csscontent)){
			acymailing::display(JText::_('JOOMEXT_SUCC_SAVED'),'success');
			$varName = JRequest::getCmd('var');
			if(!$alreadyExists){
				$js = "var optn = document.createElement(\"OPTION\");
						optn.text = '$fileName'; optn.value = '$fileName';
						mydrop = window.top.document.getElementById('".$varName."_choice');
						mydrop.options.add(optn);
						lastid = 0; while(mydrop.options[lastid+1]){lastid = lastid+1;} mydrop.selectedIndex = lastid;
						window.top.updateCSSLink('".$varName."','$type','$fileName');";
				$doc =& JFactory::getDocument();
				$doc->addScriptDeclaration( $js );
			}
			$config = acymailing::config();
			$newConfig = null;
			$newConfig->$varName = $fileName;
			$config->save($newConfig);
		}else{
			acymailing::display(JText::sprintf('FAIL_SAVE',$path),'error');
		}
		return $this->css();
	}
	function css(){
		JRequest::setVar( 'layout', 'css'  );
		return parent::display();
	}
	function latest(){
		return $this->language();
	}
	function send(){
		if(!$this->isAllowed('configuration','manage')) return;
		JRequest::checkToken() or die( 'Invalid Token' );
		$bodyEmail = JRequest::getString('mailbody');
		$code = JRequest::getString('code');
		JRequest::setVar('code',$code);
		if(empty($code)) return;
		$config = acymailing::config();
		$mailer = acymailing::get('helper.mailer');
		$mailer->Subject = '[ACYMAILING LANGUAGE FILE] '.$code;
		$mailer->Body = 'The website '.ACYMAILING_LIVE.' using AcyMailing '.$config->get('level').$config->get('version').' sent a language file : '.$code;
		$mailer->Body .= "\n"."\n"."\n".$bodyEmail;
		$user = JFactory::getUser();
		$mailer->AddAddress($user->email,$user->name);
		$mailer->AddAddress('translate@acyba.com','Acyba Translation Team');
		$mailer->report = false;
		jimport('joomla.filesystem.file');
		$path = JPath::clean(JLanguage::getLanguagePath(JPATH_ROOT).DS.$code.DS.$code.'.com_acymailing.ini');
		$mailer->AddAttachment($path);
		$result = $mailer->Send();
		if($result){
			acymailing::display(JText::_('THANK_YOU_SHARING'),'success');
			acymailing::display($mailer->reportMessage,'success');
		}else{
			acymailing::display($mailer->reportMessage,'error');
		}
	}
	function share(){
		if(!$this->isAllowed('configuration','manage')) return;
		JRequest::checkToken() or die( 'Invalid Token' );
		if($this->_savelanguage()){
			JRequest::setVar( 'layout', 'share' );
			return parent::display();
		}else{
			return $this->language();
		}
	}
	function _savelanguage(){
		if(!$this->isAllowed('configuration','manage')) return;
		jimport('joomla.filesystem.file');
		$code = JRequest::getString('code');
		JRequest::setVar('code',$code);
		$content = JRequest::getVar('content','','','string',JREQUEST_ALLOWRAW);
		if(empty($code) OR empty($content)) return;
		$path = JLanguage::getLanguagePath(JPATH_ROOT).DS.$code.DS.$code.'.com_acymailing.ini';
		$result = JFile::write($path, $content);
		if($result){
			acymailing::display(JText::_('JOOMEXT_SUCC_SAVED'),'success');
			$js = "window.top.document.getElementById('image$code').src = '".ACYMAILING_IMAGES."icons/icon-16-edit.png'";
			$doc =& JFactory::getDocument();
			$doc->addScriptDeclaration( $js );
			$updateHelper = acymailing::get('helper.update');
			$updateHelper->installMenu($code);
		}else{
			acymailing::display(JText::sprintf('FAIL_SAVE',$path),'error');
		}
		return $result;
	}
}