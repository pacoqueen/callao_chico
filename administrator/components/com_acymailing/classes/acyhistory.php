<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class acyhistoryClass extends acymailingClass{
	function insert($subid,$action,$data = array()){
		$user =& JFactory::getUser();
		if(!empty($user->id)){
			$data[] = 'EXECUTED_BY::'.$user->id.' ( '.$user->username.' )';
		}
		$history = null;
		$history->subid = intval($subid);
		$history->action = strip_tags($action);
		$history->data = implode("\n",$data);
		$history->date = time();
		$userHelper = acymailing::get('helper.user');
		$history->ip = $userHelper->getIP();
		if(!empty($_SERVER)){
			$source = array();
			$vars = array('HTTP_REFERER','HTTP_USER_AGENT','HTTP_HOST','SERVER_ADDR','REMOTE_ADDR','REQUEST_URI','QUERY_STRING');
			foreach($vars as $oneVar){
				if(!empty($_SERVER[$oneVar])) $source[] = $oneVar.'::'.strip_tags($_SERVER[$oneVar]);
			}
			$history->source = implode("\n",$source);
		}
		return $this->database->insertObject(acymailing::table('history'),$history);
	}
}