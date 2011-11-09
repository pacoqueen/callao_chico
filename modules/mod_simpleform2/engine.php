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
define('ZYX_START_TIME',microtime());
define( '_JEXEC', 1 );
define( 'DS', DIRECTORY_SEPARATOR );
$base = dirname(__FILE__);
$base = str_replace(DS.'modules'.DS.'mod_simpleform2','',$base);
define('JPATH_BASE', $base );
require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );

$mainframe = JFactory::getApplication('site');
$mainframe->initialise();
$language = & JFactory::getLanguage();
$language->load('mod_simpleform2', JPATH_SITE);
$user	=& JFactory::getUser();

$task = JRequest::getCmd('task');
switch($task){
	case 'captcha':
		ob_end_clean();
		$moduleID = (int)JRequest::getInt('moduleID',0);
		if($moduleID==0) sfEcho('!'.JText::_('Form not found'));
		$module = & JTable::getInstance('module');
		$module->load($moduleID);
		if(!$module->id||$module->id!=$moduleID) sfEcho('!'.JText::_('Form not found'));
		if(class_exists('JParameter')){
			$params = new JParameter( $module->params );
		}
		else{
			$params = new JRegistry;
			$params->loadJSON($module->params);
		}
		require_once ( JPATH_BASE .DS.'modules'.DS.'mod_simpleform2'.DS.'simpleform2.class.php' );
		$form = new simpleForm2($params->get('simpleCode',''));
		$form->set('moduleID',$module->id);
		require_once(JPATH_BASE.DS.'modules'.DS.'mod_simpleform2'.DS.'kcaptcha'.DS.'kcaptcha.php');
		$captcha = null;
		foreach($form->elements as $elem){
			if($elem->type=='captcha'){
				$captcha = $elem;
				break;
			}
		}
		if(is_null($captcha)) sfEcho('!'.JText::_('Form has no captcha'));
		$width = ((int)$captcha->width>0?(int)$captcha->width:200);
		$height = ((int)$captcha->height>0?(int)$captcha->height:60);
		$color = ($captcha->color!=''?$captcha->color:null);
		$background = ($captcha->background!=''?$captcha->background:null);
		$captchaObj = new simpleCAPTCHA($width,$height,$color,$background);
		$session =& JFactory::getSession();
		$session->set('easyform2.captcha', $captchaObj->getKeyString());
		die();
	break;
	case 'sendForm':
		$moduleID = (int)JRequest::getInt('moduleID',0);
		if($moduleID==0) sfEcho('!'.JText::_('Form not found'));
		$module = & JTable::getInstance('module');
		$module->load($moduleID);
		if(!$module->id||$module->id!=$moduleID) sfEcho('!'.JText::_('Form not found'));
		if(class_exists('JParameter')){
			$params = new JParameter( $module->params );
		}
		else{
			$params = new JRegistry;
			$params->loadJSON($module->params);
		}
		require_once ( JPATH_BASE .DS.'modules'.DS.'mod_simpleform2'.DS.'simpleform2.class.php' );
		$form = new simpleForm2($params->get('simpleCode',''),true);
		$form->set('defaultError',JText::_('Enter value for'));
		$result = $form->processRequest($_POST);
		if($result!==false){
			$mailFrom = $params->get('sfMailForm',null);
			$mailTo = $params->get('sfMailTo',null);
			$subject = $params->get('sfMailSubj','--== SimpleForm2 e-mail ==--');
			$subject = html_entity_decode($subject, ENT_QUOTES);
			$now =& JFactory::getDate();
			$url = JURI::root();
			$url = str_replace('modules/mod_simpleform2/','',$url);
			$url = JRequest::getVar('url',$url);
			$date = $now->toFormat('%d.%m.%Y %H:%M:%S');
			$ip = $form->getUserIp();
			$body = $form->getTemplate('mail_form',array('url'=>$url,'date'=>$date,'ip'=>$ip,'rows'=>$result));
			$body = stripslashes(html_entity_decode($body, ENT_QUOTES));
			if(!$mailFrom||!$mailTo) sfEcho('!'.JText::_('Form not configured'));
			$mail =& JFactory::getMailer();
			$mail->setSender(array($mailFrom, $mailFrom));
			$mail->setSubject($subject);
			$mail->setBody($body);
			if(preg_match('/</',$body)&&preg_match('/>/',$body)) $mail->IsHTML(true);
			$recieps = array();
			if(preg_match('/\,/',$mailTo)){
				$tmpR = explode(',',$mailTo);
				foreach($tmpR as $tmpRr){
					$tmpRr = trim($tmpRr);
					if($tmpRr!='') $recieps[] = $tmpRr;
				}
			}
			else $recieps[] = $mailTo;
			foreach($recieps as $reciep){
				$mail->addRecipient($reciep);
			}
			$mail->addCC(null);$mail->addBCC(null);
			foreach($form->attachments as $attachment){
				$mail->AddStringAttachment(file_get_contents($attachment->file),$attachment->name);
			}
			$ok = $mail->Send();
			if(is_object($ok)){
				sfEcho('!'.$ok->message);
			}
			else{
				$okMSG = $params->get('okText',JText::_('Form succeed'));
				sfEcho('='.$okMSG);
			}
		}
		else{
			sfEcho('!'.$form->getError());
		}
	break;
}

function sfEcho($txt){
	header('Content-type: text/html; charset="utf-8"',true);
	echo $txt;
	die();
}
 
