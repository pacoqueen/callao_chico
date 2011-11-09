<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
	$db =& JFactory::getDBO();
	$db->setQuery('SELECT count(id) FROM '.acymailing::table('users',false));
	$resultUsers = $db->loadResult();
	$db->setQuery('SELECT count(subid) FROM '.acymailing::table('subscriber').' WHERE userid > 0');
	$resultAcymailing = $db->loadResult();
?>
There are <?php echo $resultUsers; ?> Users in your Joomla User Manager.
<br/>
There are <?php echo $resultAcymailing; ?> Registered Users in AcyMailing.
<br/>
<br/>
If you click on the "import" button, the system will :
<ol>
	<li>Update the AcyMailing Users from your Joomla Users</li>
	<li>Delete the AcyMailing Users if they were linked to a Joomla User but this Joomla User does not exist any more</li>
	<li>Add all your Joomla Users into AcyMailing if they are not already there</li>
	<li>Subscribe all your Joomla Users to the selected lists if they are not already subscribed or unsubscribed from it</li>
</ol>