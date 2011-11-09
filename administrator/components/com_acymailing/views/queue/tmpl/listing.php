<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="iframedoc"></div>
<form action="index.php?option=<?php echo ACYMAILING_COMPONENT ?>&amp;ctrl=queue" method="post" name="adminForm">
	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_( 'JOOMEXT_FILTER' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->pageInfo->search;?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_( 'JOOMEXT_GO' ); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'JOOMEXT_RESET' ); ?></button>
			</td>
			<td nowrap="nowrap">
				<?php echo $this->filters->mail; ?>
			</td>
		</tr>
	</table>
	<table class="adminlist" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'ACY_NUM' );?>
				</th>
				<th class="title titledate">
					<?php echo JHTML::_('grid.sort',   JText::_( 'SEND_DATE' ), 'a.senddate', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_( 'JOOMEXT_SUBJECT'), 'c.subject', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_( 'ACY_USER'), 'b.email', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title titletoggle" >
					<?php echo JHTML::_('grid.sort',   JText::_( 'PRIORITY' ), 'a.priority', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title titletoggle" >
					<?php echo JHTML::_('grid.sort',   JText::_( 'TRY' ), 'a.try', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title titletoggle" >
					<?php echo JText::_( 'ACY_DELETE' ); ?>
				</th>
				<th class="title titletoggle" nowrap="nowrap">
					<?php echo JHTML::_('grid.sort',   JText::_( 'ACY_PUBLISHED' ), 'c.published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="10">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				$k = 0;
				for($i = 0,$a = count($this->rows);$i<$a;$i++){
					$row =& $this->rows[$i];
					$id = 'queue'.$i;
			?>
				<tr class="<?php echo "row$k"; ?>" id="<?php echo $id; ?>">
					<td align="center">
					<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td align="center">
					<?php echo acymailing::getDate($row->senddate); ?>
					</td>
					<td align="center">
					<?php
						$link = ($row->type == 'followup' ? 'followup' : 'newsletter' ).'&task=edit&cid[]='.$row->mailid;
						echo acymailing::tooltip('ID : '.$row->mailid, $row->subject, 'tooltip.png', $row->subject,acymailing::completeLink($link));
					?>
					</td>
					<td align="center">
					<?php
						echo acymailing::tooltip(JText::_('ACY_NAME').' : '.$row->name.'<br/>'.JText::_('ACY_ID').' : '.$row->subid, $row->email, 'tooltip.png', $row->email,acymailing::completeLink('subscriber&task=edit&cid[]='.$row->subid));
					?>
					</td>
					<td align="center">
						<?php echo $row->priority; ?>
					</td>
					<td align="center">
						<?php echo $row->try; ?>
					</td>
					<td align="center">
						<?php echo $this->toggleClass->delete($id,$row->subid.'_'.$row->mailid,'queue'); ?>
					</td>
					<td align="center">
						<?php echo $this->toggleClass->display('published',$row->published); ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
