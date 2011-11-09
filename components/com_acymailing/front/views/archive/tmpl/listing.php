<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="acyarchivelisting">
<?php if($this->values->show_page_title){ ?>
<div class="contentheading<?php echo $this->values->suffix; ?>"><?php echo $this->values->page_title; ?></div>
<?php } ?>
	<table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" class="contentpane<?php echo $this->values->suffix; ?>">
	<?php if($this->values->show_description){ ?>
		<tr>
			<td class="contentdescription<?php echo $this->values->suffix; ?>" >
				<?php echo $this->list->description; ?>
			</td>
		</tr>
	<?php } ?>
		<tr>
			<td>
				<form action="<?php echo acymailing::completeLink('archive&listid='.$this->list->listid); ?>" method="post" name="adminForm">
					<?php echo $this->loadTemplate('newsletters'); ?>
					<?php if(!empty($this->values->itemid)){ ?>
						<input type="hidden" name="Itemid" value=<?php echo $this->values->itemid; ?> />
					<?php } ?>
					<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
					<input type="hidden" name="task" value="" />
					<input type="hidden" name="ctrl" value="archive" />
					<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
					<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
				</form>
			</td>
		</tr>
	</table>
	<?php
		if($this->access->frontEndManament){
	?>
		<span class="acynewbutton"><a href="<?php echo acymailing::completeLink('newsletter&task=add&listid='.$this->list->listid); ?>" title="<?php echo JText::_('CREATE_EMAIL',true); ?>" ><img class="icon16" src="<?php echo ACYMAILING_IMAGES; ?>icons/icon-16-add.png" alt="<?php echo JText::_('CREATE_EMAIL',true); ?>"/></a></span>
	<?php } ?>
</div>