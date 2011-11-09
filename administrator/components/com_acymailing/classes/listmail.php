<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class listmailClass extends acymailingClass{
	function getLists($mailid){
		$query = 'SELECT a.*,b.mailid FROM '.acymailing::table('list').' as a LEFT JOIN '.acymailing::table('listmail').' as b on a.listid = b.listid AND b.mailid = '.intval($mailid).' WHERE a.type = \'list\' ORDER BY b.mailid DESC, a.ordering ASC';
		$this->database->setQuery($query);
		return $this->database->loadObjectList();
	}
	function save($mailid,$listids,$removelists = array()){
		$mailid = intval($mailid);
		if(!empty($removelists)){
			JArrayHelper::toInteger($removelists);
			$query = 'DELETE FROM '.acymailing::table('listmail').' WHERE mailid = '.$mailid.' AND listid IN ('.implode(',',$removelists).')';
			$this->database->setQuery($query);
			if(!$this->database->query()) return false;
		}
		JArrayHelper::toInteger($listids);
		if(empty($listids))	return true;
		$query = 'INSERT IGNORE INTO '.acymailing::table('listmail').' (mailid,listid) VALUES ('.$mailid.','.implode('),('.$mailid.',',$listids).')';
		$this->database->setQuery($query);
		return $this->database->query();
	}
	function getCampaign($mailid){
		$query = 'SELECT a.*,b.mailid FROM '.acymailing::table('listmail').' as b LEFT JOIN '.acymailing::table('list').' as a on a.listid = b.listid WHERE b.mailid = '.intval($mailid).' AND a.type = \'campaign\' LIMIT 1';
		$this->database->setQuery($query);
		return $this->database->loadObject();
	}
	function getReceivers($mailid,$total = true,$onlypublished = true){
		$query = 'SELECT a.name,a.description,a.published,a.color,b.listid,b.mailid FROM '.acymailing::table('listmail').' as b LEFT JOIN '.acymailing::table('list').' as a on a.listid = b.listid WHERE b.mailid = '.intval($mailid);
		if($onlypublished) $query .= ' AND a.published = 1';
		$this->database->setQuery($query);
		$lists  = $this->database->loadObjectList('listid');
		if(empty($lists) OR !$total) return $lists;
		$config = acymailing::config();
		$confirmed = $config->get('require_confirmation') ? 'b.confirmed = 1 AND' : '';
		$countQuery = 'SELECT a.listid, count(b.subid) as nbsub FROM `#__acymailing_listsub` as a LEFT JOIN `#__acymailing_subscriber` as b ON a.subid = b.subid WHERE '.$confirmed.' b.`enabled` = 1 AND b.`accept` = 1 AND a.`status` = 1 AND a.`listid` IN ('.implode(',',array_keys($lists)).') GROUP BY a.`listid`';
		$this->database->setQuery($countQuery);
		$countResult = $this->database->loadObjectList('listid');
		foreach($lists as $listid => $count){
			$lists[$listid]->nbsub = empty($countResult[$listid]->nbsub) ? 0 : $countResult[$listid]->nbsub;
		}
		return $lists;
	}
	function getFollowup($listid){
		$query = 'SELECT a.* FROM '.acymailing::table('listmail').' as b LEFT JOIN '.acymailing::table('mail').' as a on a.mailid = b.mailid WHERE b.listid = '.intval($listid).' ORDER BY a.senddate ASC';
		$this->database->setQuery($query);
		return $this->database->loadObjectList();
	}
}
