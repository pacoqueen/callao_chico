<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="page-subscription">
<br  style="font-size:1px;" />
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'SUBSCRIPTION' ); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
				<?php echo acymailing::tooltip(JText::_('ALLOW_VISITOR_DESC'), JText::_('ALLOW_VISITOR'), '', JText::_('ALLOW_VISITOR')); ?>
				</td>
				<td>
					<?php echo $this->elements->allow_visitor; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
				<?php echo acymailing::tooltip(JText::_('REQUIRE_CONFIRM_DESC'), JText::_('REQUIRE_CONFIRM'), '', JText::_('REQUIRE_CONFIRM')); ?>
				</td>
				<td>
					<?php echo $this->elements->require_confirmation; ?>
					<?php echo $this->elements->editConfEmail; ?>
				</td>
			</tr>
			<tr>
				<td class="key" >
				<?php echo acymailing::tooltip(JText::_('AUTO_SUBSCRIBE_DESC'), JText::_('AUTO_SUBSCRIBE'), '', JText::_('AUTO_SUBSCRIBE')); ?>
				</td>
				<td>
					<input class="inputbox" id="configautosub" name="config[autosub]" type="text" size="20" value="<?php echo $this->config->get('autosub','None'); ?>">
					<a class="modal" id="linkconfigautosub" title="<?php echo JText::_('SELECT_LISTS'); ?>"  href="index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=chooselist&amp;task=autosub&amp;values=<?php echo $this->config->get('autosub','None'); ?>&amp;control=config" rel="{handler: 'iframe', size: {x: 650, y: 375}}"><button onclick="return false"><?php echo JText::_('SELECT'); ?></button></a>
				</td>
			</tr>
			<tr>
				<td class="key">
				<?php echo acymailing::tooltip(JText::_('ALLOW_MODIFICATION_DESC'), JText::_('ALLOW_MODIFICATION'), '', JText::_('ALLOW_MODIFICATION')); ?>
				</td>
				<td>
					<?php echo $this->elements->allow_modif; ?>
					<?php echo $this->elements->editModifEmail; ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'NOTIFICATIONS' ); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key">
				<?php echo acymailing::tooltip(JText::_('NOTIF_CREATE_DESC'), JText::_('NOTIF_CREATE'), '', JText::_('NOTIF_CREATE')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[notification_created]" size="50" value="<?php echo $this->config->get('notification_created'); ?>">
					<?php echo $this->elements->edit_notification_created; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
				<?php echo acymailing::tooltip(JText::_('NOTIF_UNSUB_DESC'), JText::_('NOTIF_UNSUB'), '', JText::_('NOTIF_UNSUB')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[notification_unsub]" size="50" value="<?php echo $this->config->get('notification_unsub'); ?>">
					<?php echo $this->elements->edit_notification_unsub; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
				<?php echo acymailing::tooltip(JText::_('NOTIF_UNSUBALL_DESC'), JText::_('NOTIF_UNSUBALL'), '', JText::_('NOTIF_UNSUBALL')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[notification_unsuball]" size="50" value="<?php echo $this->config->get('notification_unsuball'); ?>">
					<?php echo $this->elements->edit_notification_unsuball; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
				<?php echo acymailing::tooltip(JText::_('NOTIF_REFUSE_DESC'), JText::_('NOTIF_REFUSE'), '', JText::_('NOTIF_REFUSE')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[notification_refuse]" size="50" value="<?php echo $this->config->get('notification_refuse'); ?>">
					<?php echo $this->elements->edit_notification_refuse; ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'REDIRECTIONS' ); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key">
				<?php echo acymailing::tooltip(JText::_('REDIRECTION_SUB_DESC').'<br/><br/><i>'.JText::_('REDIRECTION_NOT_MODULE').'</i>', JText::_('REDIRECTION_SUB'), '', JText::_('REDIRECTION_SUB')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" id="sub_redirect" name="config[sub_redirect]" size="60" value="<?php echo $this->config->get('sub_redirect') ?>">
				</td>
			</tr>
			<tr>
				<td class="key">
				<?php echo acymailing::tooltip(JText::_('REDIRECTION_MODIF_DESC').'<br/><br/><i>'.JText::_('REDIRECTION_NOT_MODULE').'</i>', JText::_('REDIRECTION_MODIF'), '', JText::_('REDIRECTION_MODIF')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" id="modif_redirect" name="config[modif_redirect]" size="60" value="<?php echo $this->config->get('modif_redirect') ?>">
				</td>
			</tr>
			<tr>
				<td class="key">
				<?php echo acymailing::tooltip(JText::_('REDIRECTION_CONFIRM_DESC'), JText::_('REDIRECTION_CONFIRM'), '', JText::_('REDIRECTION_CONFIRM')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" id="confirm_redirect" name="config[confirm_redirect]" size="60" value="<?php echo $this->config->get('confirm_redirect') ?>">
				</td>
			</tr>
			<tr>
				<td class="key">
				<?php echo acymailing::tooltip(JText::_('REDIRECTION_UNSUB_DESC'), JText::_('REDIRECTION_UNSUB'), '', JText::_('REDIRECTION_UNSUB')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" id="unsub_redirect" name="config[unsub_redirect]" size="60" value="<?php echo $this->config->get('unsub_redirect') ?>">
				</td>
			</tr>
		</table>
	</fieldset>
</div>