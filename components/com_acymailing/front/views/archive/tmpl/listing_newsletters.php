<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php if($this->values->filter) { ?>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td width="100%">
				<?php echo JText::_( 'JOOMEXT_FILTER' ); ?>:
				<input type="text" name="search" id="acymailingsearch" value="<?php echo $this->escape($this->pageInfo->search);?>" class="inputbox" onchange="document.adminForm.submit();" />
				<button class="button buttongo" onclick="this.form.submit();"><?php echo JText::_( 'JOOMEXT_GO' ); ?></button>
				<button class="button buttonreset" onclick="document.getElementById('acymailingsearch').value='';this.form.submit();"><?php echo JText::_( 'JOOMEXT_RESET' ); ?></button>
			</td>
			<td nowrap="nowrap">
			</td>
		</tr>
	</table>
<?php } ?>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<?php
		$nbcols = $this->values->show_senddate ? 3 : 2;
		if($this->values->show_headings) { ?>
		<thead>
			<tr>
				<td class="sectiontableheader<?php echo $this->values->suffix; ?>" align="center">
					<?php echo JText::_( 'ACY_NUM' );?>
				</td>
				<td class="sectiontableheader<?php echo $this->values->suffix; ?>" align="center">
					<?php echo JHTML::_('grid.sort', JText::_('JOOMEXT_SUBJECT').' ', 'a.subject', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</td>
				<?php if($this->values->show_senddate) { ?>
				<td class="sectiontableheader<?php echo $this->values->suffix; ?>" align="center">
					<?php echo JHTML::_('grid.sort', JText::_('SEND_DATE').' ', 'a.senddate', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</td>
				<?php } ?>
			</tr>
		</thead>
	<?php } ?>
		<tfoot>
			<tr>
				<td colspan="<?php echo $nbcols ?>" class="sectiontablefooter<?php echo $this->values->suffix; ?>" align="center">
					<?php echo $this->pagination->getPagesLinks(); ?>
				</td>
			</tr>
			<tr>
				<td colspan="<?php echo $nbcols ?>" class="sectiontablefooter<?php echo $this->values->suffix; ?>" align="right">
					<?php echo $this->pagination->getPagesCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				$k = 1;
				for($i = 0,$a = count($this->rows);$i<$a;$i++){
					$row =& $this->rows[$i];
			?>
				<tr class="<?php echo "sectiontableentry$k".$this->values->suffix; ?>">
					<td align="center">
					<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td>
						<a <?php if($this->config->get('open_popup')){ echo 'class="modal" rel="{handler: \'iframe\', size: {x: '.intval($this->config->get('popup_width',750)).', y: '.intval($this->config->get('popup_height',550)).'}}"'; } ?> href="<?php echo acymailing::completeLink('archive&task=view&listid='.$this->list->listid.'-'.$this->list->alias.'&mailid='.$row->mailid.'-'.strip_tags($row->alias).$this->item,(bool)$this->config->get('open_popup')); ?>">
							<?php echo $row->subject; ?>
						</a>
						<?php if($this->access->frontEndManament){
							if(($this->config->get('frontend_modif',1) || ($row->userid == $this->my->id)) AND ($this->config->get('frontend_modif_sent',1) || empty($row->senddate))){	?>
							<span class="acyeditbutton"><a href="<?php echo acymailing::completeLink('newsletter&task=edit&mailid='.$row->mailid.'&listid='.$this->list->listid); ?>" title="<?php echo JText::_('ACY_EDIT',true) ?>" ><img class="icon16" src="<?php echo ACYMAILING_IMAGES ?>icons/icon-16-edit.png" alt="<?php echo JText::_('ACY_EDIT',true) ?>" /></a></span>
							<?php } if(!empty($row->senddate) && acymailing::isAllowed($this->config->get('acl_statistics_manage','all'))){ ?>
							<span class="acystatsbutton"><a class="modal" rel="{handler: 'iframe', size: {x: 800, y: 590}}" href="<?php echo acymailing::completeLink('newsletter&task=stats&mailid='.$row->mailid.'&listid='.$this->list->listid,true); ?>"><img src="<?php echo ACYMAILING_IMAGES; ?>icons/icon-16-stats.png" alt="<?php echo JText::_('STATISTICS',true) ?>" /></a></span>
							<?php } ?>
						<?php }?>
					</td>
					<?php if($this->values->show_senddate) { ?>
					<td align="center" nowrap="nowrap">
						<?php echo acymailing::getDate($row->senddate,JText::_('DATE_FORMAT_LC3')); ?>
					</td>
					<?php } ?>
				</tr>
			<?php
					$k = 3-$k;
				}
			?>
		</tbody>
	</table>