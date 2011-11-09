<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class queueClass extends acymailingClass{
	function delete($filters){
		$query = 'DELETE a.* FROM '.acymailing::table('queue').' as a';
		if(!empty($filters)){
			$query .= ' LEFT JOIN '.acymailing::table('subscriber').' as b on a.subid = b.subid';
			$query .= ' LEFT JOIN '.acymailing::table('mail').' as c on a.mailid = c.mailid';
			$query .= ' WHERE ('.implode(') AND (',$filters).')';
		}
		$this->database->setQuery($query);
		$this->database->query();
		$nbRecords = $this->database->getAffectedRows();
		if(empty($filters)){
			$this->database->setQuery('TRUNCATE TABLE '.acymailing::table('queue'));
			$this->database->query();
		}
		return $nbRecords;
	}
	function nbQueue($mailid){
		$mailid = (int) $mailid;
		$this->database->setQuery('SELECT count(subid) FROM '.acymailing::table('queue').' WHERE mailid = '.$mailid.' GROUP BY mailid');
		return $this->database->loadResult();
	}
	function queue($mailid,$time,$onlyNew = false){
		$mailid = intval($mailid);
		if(empty($mailid)) return false;
		$classLists = acymailing::get('class.listmail');
		$lists = $classLists->getReceivers($mailid,false);
		if(empty($lists)) return 0;
		$config = acymailing::config();
		$querySelect = 'SELECT DISTINCT a.subid,'.$mailid.','.$time.','.(int) $config->get('priority_newsletter',3);
		$querySelect .= ' FROM '.acymailing::table('listsub').' as a ';
		$querySelect .= 'LEFT JOIN '.acymailing::table('subscriber').' as b ON a.subid = b.subid ';
		$querySelect .= 'WHERE b.enabled = 1 AND b.accept = 1 ';
		$querySelect .= 'AND a.listid IN ('.implode(',',array_keys($lists)).') AND a.status = 1 ';
		$config = acymailing::config();
		if($config->get('require_confirmation','0')){ $querySelect .= 'AND b.confirmed = 1 '; }
		$query = 'INSERT IGNORE INTO '.acymailing::table('queue').' (subid,mailid,senddate,priority) '.$querySelect;
		$this->database->setQuery($query);
		if(!$this->database->query()){
			acymailing::display($this->database->ErrorMsg(),'error');
		}
		$totalinserted = $this->database->getAffectedRows();
		if($onlyNew){
			$this->database->setQuery('DELETE b.* FROM `#__acymailing_userstats` as a LEFT JOIN `#__acymailing_queue` as b on a.subid = b.subid WHERE a.mailid = '.$mailid);
			$this->database->query();
			$totalinserted = $totalinserted - $this->database->getAffectedRows();
		}
		return $totalinserted;
	}
	function getReady($limit,$mailid = 0){
		$query = 'SELECT c.*,a.* FROM '.acymailing::table('queue').' as a';
		$query .= ' LEFT JOIN '.acymailing::table('mail').' as b on a.`mailid` = b.`mailid` ';
		$query .= ' LEFT JOIN '.acymailing::table('subscriber').' as c on a.`subid` = c.`subid` ';
		$query .= ' WHERE a.`senddate` <= '.time().' AND b.`published` = 1';
		if(!empty($mailid)) $query .= ' AND a.`mailid` = '.$mailid;
		$query .= ' ORDER BY a.`priority` ASC, a.`senddate` ASC, a.`subid` ASC';
		if(!empty($limit)) $query .= ' LIMIT '.$limit;
		$this->database->setQuery($query);
		$results = $this->database->loadObjectList();
		if($results === null){
			$this->database->setQuery('REPAIR TABLE #__acymailing_queue, #__acymailing_subscriber, #__acymailing_mail');
			$this->database->query();
		}
		return $results;
	}
	function queueStatus($mailid,$all = false){
		$query = 'SELECT a.mailid, count(a.subid) as nbsub,min(a.senddate) as senddate, b.subject FROM '.acymailing::table('queue').' as a';
		$query .= ' LEFT JOIN '.acymailing::table('mail').' as b on a.mailid = b.mailid';
		$query .= ' WHERE b.published > 0';
		if(!$all){
			$query .= ' AND a.senddate < '.time();
			if(!empty($mailid)) $query .= ' AND a.mailid = '.$mailid;
		}
		$query .= ' GROUP BY a.mailid';
		$this->database->setQuery($query);
		$queueStatus = $this->database->loadObjectList('mailid');
		return $queueStatus;
	}
}