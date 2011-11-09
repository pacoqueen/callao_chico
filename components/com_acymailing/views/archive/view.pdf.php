<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class archiveViewArchive extends JView
{
  function display($tpl = null)
  {
    $function = $this->getLayout();
    if(method_exists($this,$function)) $this->$function();
  }
  function view(){
	    $mailid = acymailing::getCID('mailid');
		if(empty($mailid)){
			$db=&JFactory::getDBO();
			$query = 'SELECT m.`mailid` FROM `#__acymailing_list` as l LEFT JOIN `#__acymailing_listmail` as lm ON l.listid=lm.listid LEFT JOIN `#__acymailing_mail` as m on lm.mailid = m.mailid';
			$query .= ' WHERE l.`visible` = 1 AND l.`published` = 1 AND m.`visible`= 1 AND m.`published` = 1';
			if(!empty($listid)) $query .= ' AND l.`listid` = '.(int) $listid;
			$query .= ' ORDER BY m.`mailid` DESC LIMIT 1';
			$db->setQuery($query);
			$mailid = $db->loadResult();
			if(empty($mailid)) return JError::raiseError( 404, 'Newsletter not found');
    	}
		$access_sub = true;
    	$mailClass = acymailing::get('helper.mailer');
    	$oneMail = $mailClass->load($mailid);
    	if(empty($oneMail->mailid)){
    		return JError::raiseError( 404, 'Newsletter not found : '.$mailid );
    	}
    	if(!$access_sub OR !$oneMail->published OR !$oneMail->visible){
    		$key = JRequest::getString('key');
    		if(empty($key) OR $key !== $oneMail->key){
    			$app =& JFactory::getApplication();
    			$app->enqueueMessage('You can not have access to this e-mail','error');
    			$app->redirect(acymailing::completeLink('lists',false,true));
    			return false;
    		}
    	}
		$user =& JFactory::getUser();
		if(!empty($user->email)){
			$userClass = acymailing::get('class.subscriber');
			$receiver = $userClass->get($user->email);
		}else{
			$receiver = null;
			$receiver->name = JText::_('VISITOR');
		}
		$oneMail->sendHTML = true;
		$mailClass->dispatcher->trigger('acymailing_replaceusertagspreview',array(&$oneMail,&$receiver));
		$document	=& JFactory::getDocument();
		$document->setTitle( $oneMail->subject );
		if(!empty($oneMail->text)) echo nl2br($mailClass->textVersion($oneMail->text,false));
    	else echo nl2br($mailClass->textVersion($oneMail->body,true));
	}
}