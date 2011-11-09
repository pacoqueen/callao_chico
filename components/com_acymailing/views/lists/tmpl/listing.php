<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="acylistslisting" >
<div class="componentheading"><?php echo JText::_('MAILING_LISTS'); ?></div>
<?php
	$k = 0;
	$my = JFactory::getUser();
	foreach($this->rows as $i => $oneList){
		$row =& $this->rows[$i];
		$frontEndAccess = true;
		$frontEndManagement = false;
		if(!$frontEndManagement AND (!$frontEndAccess OR !$row->published OR !$row->visible)) continue;
?>
	<div class="<?php echo "acymailing_list acymailing_row$k"; ?>">
			<div class="list_name"><a href="<?php echo acymailing::completeLink('archive&listid='.$row->listid.'-'.$row->alias.$this->item)?>"><?php echo $row->name; ?></a></div>
			<div class="list_description"><?php echo $row->description; ?></div>
	</div>
<?php
		$k = 1-$k;
	}
?>
</div>