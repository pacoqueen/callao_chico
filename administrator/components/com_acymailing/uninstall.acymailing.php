<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
function com_uninstall(){
	$uninstallClass = new acymailingUninstall();
	$uninstallClass->unpublishModules();
	$uninstallClass->unpublishPlugins();
}
class acymailingUninstall{
	var $db;
	function acymailingUninstall(){
		$this->db =& JFactory::getDBO();
		$this->db->setQuery("DELETE FROM `#__acymailing_config` WHERE `namekey` = 'li' LIMIT 1");
		$this->db->query();
		if(version_compare(JVERSION,'1.6.0','>=')){
			$this->db->setQuery("DELETE FROM `#__menu` WHERE link LIKE '%com_acymailing%'");
			$this->db->query();
		}
	}
	function unpublishModules(){
		$this->db->setQuery("UPDATE `#__modules` SET `published` = 0 WHERE `module` LIKE '%acymailing%'");
		$this->db->query();
	}
	function unpublishPlugins(){
		if(version_compare(JVERSION,'1.6.0','<')){
			$this->db->setQuery("UPDATE `#__plugins` SET `published` = 0 WHERE `element` LIKE '%acymailing%' AND `folder` NOT LIKE '%acymailing%'");
		}else{
			$this->db->setQuery("UPDATE `#__extensions` SET `enabled` = 0 WHERE `type` = 'plugin' AND `element` LIKE '%acymailing%' AND `folder` NOT LIKE '%acymailing%'");
		}
		$this->db->query();
	}
}