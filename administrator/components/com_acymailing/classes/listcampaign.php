<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class listcampaignClass extends acymailingClass{
	function getLists($campaignid){
		$query = 'SELECT a.*,b.campaignid FROM '.acymailing::table('list').' as a LEFT JOIN '.acymailing::table('listcampaign').' as b on a.listid = b.listid AND b.campaignid = '.intval($campaignid).' WHERE a.type = \'list\' ORDER BY b.campaignid DESC, a.ordering ASC';
		$this->database->setQuery($query);
		return $this->database->loadObjectList();
	}
	function save($campaignid,$listids){
		$campaignid = intval($campaignid);
		$query = 'DELETE FROM '.acymailing::table('listcampaign').' WHERE campaignid = '.$campaignid;
		$this->database->setQuery($query);
		if(!$this->database->query()) return false;
		JArrayHelper::toInteger($listids);
		if(empty($listids))	return true;
		$query = 'INSERT IGNORE INTO '.acymailing::table('listcampaign').' (campaignid,listid) VALUES ('.$campaignid.','.implode('),('.$campaignid.',',$listids).')';
		$this->database->setQuery($query);
		return $this->database->query();
	}
	function getAffectedLists($campaignid){
		$query = 'SELECT a.*,b.campaignid FROM '.acymailing::table('listcampaign').' as b LEFT JOIN '.acymailing::table('list').' as a on a.listid = b.listid AND b.campaignid = '.intval($campaignid).' WHERE a.type = \'list\' ORDER BY a.ordering ASC';
		$this->database->setQuery($query);
		return $this->database->loadObjectList();
	}
	function getAffectedCampaigns($listids){
		$query = 'SELECT DISTINCT a.campaignid FROM '.acymailing::table('listcampaign').' as a LEFT JOIN '.acymailing::table('list').' as b on a.campaignid = b.listid WHERE a.listid IN ('.implode(',',$listids) .') AND b.type = \'campaign\' AND b.published = 1';
		$this->database->setQuery($query);
		return $this->database->loadResultArray();
	}
}
