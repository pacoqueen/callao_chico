<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
	$db =& JFactory::getDBO();
	$db->setQuery('SELECT count(*) FROM `#__yanc_subscribers`');
	$resultUsers = $db->loadResult();
	$db =& JFactory::getDBO();
	$db->setQuery('SELECT count(*) FROM `#__yanc_letters`');
	$resultLists = $db->loadResult();
?>
<table class="admintable" cellspacing="1">
	<tr>
		<td colspan="2">
			<?php echo JText::sprintf('USERS_IN_COMP',$resultUsers,'Yanc'); ?>
			<br/>
			There are <?php echo $resultLists ?> lists in Yanc.
			<br/>
			You can import those <?php echo $resultLists ?> Lists and so keep the subscription of each subscriber.
		</td>
	</tr>
	<tr>
		<td class="key" >
			<?php echo JText::_('Import the Yanc Lists too?'); ?>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist', "yanc_lists"); ?>
		</td>
	</tr>
</table>