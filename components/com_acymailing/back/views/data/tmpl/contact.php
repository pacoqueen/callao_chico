<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
	$db =& JFactory::getDBO();
	$db->setQuery("SELECT count(*) FROM `#__contact_details` WHERE `email_to` LIKE '%@%'");
	$resultUsers = $db->loadResult();
	echo JText::sprintf('USERS_IN_COMP',$resultUsers,'com_contact');