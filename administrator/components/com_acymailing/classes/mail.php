<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class mailClass extends acymailingClass{
	var $tables = array('queue','listmail','stats','userstats','urlclick','mail');
	var $pkey = 'mailid';
	var $namekey = 'alias';
	var $allowedFields = array('subject','published','fromname','fromemail','replyname', 'replyemail', 'type','visible','alias','html','tempid','altbody');
	function get($id){
		if(empty($id)) return null;
		$query = 'SELECT a.*, b.username,b.name,b.email from '.acymailing::table('mail').' as a ';
		$query .= 'left join '.acymailing::table('users',false).' as b on a.userid = b.id where ';
		if(is_numeric($id)) $query .= 'a.mailid';
		else $query .= 'a.alias';
		$query .= ' = '.$this->database->Quote($id);
		$query.= ' LIMIT 1';
		$this->database->setQuery($query);
		$mail =  $this->database->loadObject();
		if(!empty($mail)){
			if(!empty($mail->attach)) $mail->attach = unserialize($mail->attach);
			else $mail->attach = array();
			if(!empty($mail->params)) $mail->params = unserialize($mail->params);
			else $mail->params = array();
		}
		return $mail;
	}
	function saveForm(){
		$app =& JFactory::getApplication();
		$db=& JFactory::getDBO();
		$config =& acymailing::config();
		$mail = null;
		$mail->mailid = acymailing::getCID('mailid');
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		foreach($formData['mail'] as $column => $value){
			if($app->isAdmin() OR in_array($column,$this->allowedFields)){
				acymailing::secureField($column);
				if($column == 'params'){
					$mail->$column = $value;
				}else{
					$mail->$column = strip_tags($value);
				}
			}
		}
		$mail->body = JRequest::getVar('editor_body','','','string',JREQUEST_ALLOWRAW);
		$pregreplace = array();
		$pregreplace['#<tr([^>"]*>([^<]*<td[^>]*>[ \n\s]*<img[^>]*>[ \n\s]*</ *td[^>]*>[ \n\s]*)*</ *tr)#Uis'] = '<tr style="line-height: 0px;" $1';
		$pregreplace['#<td(((?!style|>).)*>[ \n\s]*<img[^>]*>[ \n\s]*</ *td)#Uis'] = '<td style="line-height: 0px;" $1';
		$pregreplace['#<xml>.*</xml>#Uis'] = '';
		$newbody = preg_replace(array_keys($pregreplace),$pregreplace,$mail->body);
		if(!empty($newbody)) $mail->body = $newbody;
		$mail->attach = array();
		$attachments = JRequest::getVar( 'attachments', array(), 'files', 'array' );
		if(!empty($attachments['name'][0]) OR !empty($attachments['name'][1])){
			jimport('joomla.filesystem.file');
			$allowedFiles = explode(',',strtolower($config->get('allowedfiles')));
			$uploadFolder = JPath::clean(html_entity_decode($config->get('uploadfolder')));
			$uploadFolder = trim($uploadFolder,DS.' ').DS;
			$uploadPath = JPath::clean(ACYMAILING_ROOT.$uploadFolder);
			acymailing::createDir($uploadPath,true);
			if(!is_writable($uploadPath)){
				@chmod($uploadPath,'0755');
				if(!is_writable($uploadPath)){
					$app->enqueueMessage(JText::sprintf( 'WRITABLE_FOLDER',$uploadPath), 'notice');
				}
			}
			foreach($attachments['name'] as $id => $filename){
				if(empty($filename)) continue;
				$attachment = null;
				$attachment->filename = strtolower(JFile::makeSafe($filename));
				$attachment->size = $attachments['size'][$id];
				$attachment->extension = strtolower(substr($attachment->filename,strrpos($attachment->filename,'.')+1));
				if(!in_array($attachment->extension,$allowedFiles)){
					$app->enqueueMessage(JText::sprintf( 'ACCEPTED_TYPE',$attachment->extension,$config->get('allowedfiles')), 'notice');
					continue;
				}
				if(!JFile::upload($attachments['tmp_name'][$id], $uploadPath . $attachment->filename)){
					if ( !move_uploaded_file($attachments['tmp_name'][$id], $uploadPath . $attachment->filename)) {
						$app->enqueueMessage(JText::sprintf( 'FAIL_UPLOAD',$attachments['tmp_name'][$id],$uploadPath . $attachment->filename), 'error');
						continue;
					}
				}
				$mail->attach[] = $attachment;
			}
		}
		$toggleHelper = acymailing::get('helper.toggle');
		if(!empty($mail->type) && $mail->type == 'followup' && !empty($mail->mailid)){
			$oldMail = $this->get($mail->mailid);
			if(!empty($mail->published) AND !$oldMail->published){
				$text = JText::_('FOLLOWUP_PUBLISHED_INFORMED');
				$text .= ' '.$toggleHelper->toggleText('add',$mail->mailid,'followup',JText::_('FOLLOWUP_PUBLISHED'));
				$app->enqueueMessage($text, 'notice');
			}
			if($oldMail->senddate != $mail->senddate){
				$text = JText::_('FOLLOWUP_CHANGED_DELAY_INFORMED');
				$text .= ' '.$toggleHelper->toggleText('update',$mail->mailid,'followup',JText::_('FOLLOWUP_CHANGED_DELAY'));
				$app->enqueueMessage($text, 'notice');
			}
		}
		$mailid = $this->save($mail);
		if(!$mailid) return false;
		JRequest::setVar( 'mailid', $mailid);
		if(!empty($mail->type) && $mail->type == 'followup' && empty($mail->mailid) && !empty($mail->published)){
			$text = JText::_('FOLLOWUP_PUBLISHED_INFORMED');
				$text .= ' '.$toggleHelper->toggleText('add',$mailid,'followup',JText::_('FOLLOWUP_PUBLISHED'));
			$app->enqueueMessage($text, 'notice');
		}
		$status = true;
		if(!empty($formData['listmail'])){
			$receivers = array();
			$remove = array();
			foreach($formData['listmail'] as $listid => $receiveme){
				if(!empty($receiveme)){
					$receivers[] = $listid;
				}else{
					$remove[] = $listid;
				}
			}
			$listMailClass = acymailing::get('class.listmail');
			$status = $listMailClass->save($mailid,$receivers,$remove);
		}
		return $status;
	}
	function save($mail){
		if(isset($mail->alias) OR empty($mail->mailid)){
			if(empty($mail->alias)) $mail->alias = $mail->subject;
			$mail->alias = JFilterOutput::stringURLSafe(trim($mail->alias));
		}
		if(empty($mail->mailid)){
			if(empty($mail->created)) $mail->created = time();
			if(empty($mail->userid)){
				$user	=& JFactory::getUser();
				$mail->userid = $user->id;
			}
			if(empty($mail->key)) $mail->key = md5($mail->alias.time());
		}else{
			if(!empty($mail->attach)){
				$oldMailObject = $this->get($mail->mailid);
				if(!empty($oldMailObject)){
					$mail->attach = array_merge($oldMailObject->attach,$mail->attach);
				}
			}
		}
		if(!empty($mail->attach) AND !is_string($mail->attach)) $mail->attach = serialize($mail->attach);
		if(!empty($mail->params)){
			if(!empty($mail->params['lastgenerateddate']) && !is_numeric($mail->params['lastgenerateddate'])){
				$mail->params['lastgenerateddate'] = acymailing::getTime($mail->params['lastgenerateddate']);
			}
			$mail->params = serialize($mail->params);
		}
		if(!empty($mail->senddate) && !is_numeric($mail->senddate)){
			$mail->senddate = acymailing::getTime($mail->senddate);
		}
		if(empty($mail->mailid)){
			$status = $this->database->insertObject(acymailing::table('mail'),$mail);
		}else{
			$status = $this->database->updateObject(acymailing::table('mail'),$mail,'mailid');
		}
		if(!empty($mail->params) AND is_string($mail->params)) $mail->params = unserialize($mail->params);
		if(!empty($mail->attach) AND is_string($mail->attach)) $mail->attach = unserialize($mail->attach);
		if($status) return empty($mail->mailid) ? $this->database->insertid() : $mail->mailid;
		return false;
	}
}