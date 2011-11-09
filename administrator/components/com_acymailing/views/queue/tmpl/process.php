<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php acymailing::display(JText::sprintf( 'QUEUE_STATUS',acymailing::getDate(time()) ),'info'); ?>
<form action="index.php?tmpl=component&amp;option=<?php echo ACYMAILING_COMPONENT ?>" method="post" name="adminForm" autocomplete="off">
	<div>
	<?php if(!empty($this->queue)){ ?>
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'QUEUE_READY' ); ?></legend>
			<table class="adminlist" cellspacing="1" align="center">
			<tbody>
		<?php	$k = 0;
				$total = 0;
				foreach($this->queue as $mailid => $row) {
					$total += $row->nbsub;
					?>
					<tr class="<?php echo "row$k"; ?>">
						<td>
							<?php
							echo JText::sprintf('EMAIL_READY',$row->mailid,$row->subject,$row->nbsub);
							 ?>
						</td>
					</tr>
					<?php
						$k = 1 - $k;
					} ?>
				</tbody>
			</table>
			<br/>
			<input type="hidden" name="totalsend" value="<?php echo $total; ?>" />
			<input type="submit" onclick="document.adminForm.task.value = 'continuesend';" value="<?php echo JText::_('SEND'); ?>">
		</fieldset>
	<?php }?>
	<?php if(!empty($this->schedNews)){?>
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'SCHEDULE_NEWS' ); ?></legend>
			<table class="adminlist" cellspacing="1" align="center">
			<tbody>
		<?php	$k = 0;
				$sendButton = false;
				foreach($this->schedNews as $row) {
					if($row->senddate < time()) $sendButton = true; ?>
					<tr class="<?php echo "row$k"; ?>">
						<td>
							<?php
							echo JText::sprintf('QUEUE_SCHED',$row->mailid,$row->subject,acymailing::getDate($row->senddate));
							 ?>
						</td>
					</tr>
					<?php
						$k = 1 - $k;
					} ?>
				</tbody>
			</table>
			<?php if($sendButton) { ?><br/><input onclick="document.adminForm.task.value = 'genschedule';" type="submit" value="<?php echo JText::_('GENERATE'); ?>"><?php } ?>
		</fieldset>
	<?php } ?>
	<?php if(!empty($this->nextqueue)){?>
		<fieldset class="adminform">
			<legend><?php echo JText::sprintf( 'QUEUE_STATUS',acymailing::getDate(time()) ); ?></legend>
			<table class="adminlist" cellspacing="1" align="center">
			<tbody>
		<?php	$k = 0;
				foreach($this->nextqueue as $mailid => $row) {?>
					<tr class="<?php echo "row$k"; ?>">
						<td>
							<?php
							echo JText::sprintf('EMAIL_READY',$row->mailid,$row->subject,$row->nbsub);
							echo '<br/>'.JText::sprintf('QUEUE_NEXT_SCHEDULE',acymailing::getDate($row->senddate));
							 ?>
						</td>
					</tr>
					<?php
						$k = 1 - $k;
					} ?>
				</tbody>
			</table>
		</fieldset>
	<?php } ?>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="mailid" value="<?php echo $this->infos->mailid; ?>" />
	<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
	<input type="hidden" name="task" value="continuesend" />
	<input type="hidden" name="ctrl" value="send" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>