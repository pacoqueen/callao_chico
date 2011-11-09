<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class statsClass extends acymailingClass{
	var $tables = array('urlclick','userstats','stats');
	var $pkey = 'mailid';
	var $countReturn = true;
	function saveStats(){
		$subid = JRequest::getInt('subid');
		$mailid = JRequest::getInt('mailid');
		if(empty($subid) OR empty($mailid)) return false;
		$db = JFactory::getDBO();
		$db->setQuery('SELECT open FROM '.acymailing::table('userstats').' WHERE mailid = '.$mailid.' AND subid = '.$subid.' LIMIT 1');
		$actual = $db->loadObject();
		if(empty($actual)) return false;
		$open = 0;
		if(empty($actual->open)){
			$open = 1;
			$unique = ',openunique = openunique +1';
		}elseif($this->countReturn){
			$open = $actual->open +1;
			$unique = '';
		}
		if(empty($open)) return true;

		$db->setQuery('UPDATE '.acymailing::table('userstats').' SET open = '.$open.', opendate = '.time().' WHERE mailid = '.$mailid.' AND subid = '.$subid.' LIMIT 1');
		$db->query();
		$db->setQuery('UPDATE '.acymailing::table('stats').' SET opentotal = opentotal +1 '.$unique.' WHERE mailid = '.$mailid.' LIMIT 1');
		$db->query();
		if(!empty($subid)){
			$filterClass = acymailing::get('class.filter');
			$filterClass->subid = $subid;
			$filterClass->trigger('opennews');
		}
		return true;
	}
}