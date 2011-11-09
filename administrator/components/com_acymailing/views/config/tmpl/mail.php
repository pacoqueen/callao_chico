<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="page-mail">
<br  style="font-size:1px;" />
	<fieldset class="adminform" >
		<legend><?php echo JText::_( 'SENDER_INFORMATIONS' ); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td width="185" class="key">
					<?php echo acymailing::tooltip(JText::_('FROM_NAME_DESC'), JText::_('FROM_NAME'), '', JText::_('FROM_NAME')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[from_name]" size="40" value="<?php echo $this->escape($this->config->get('from_name')); ?>">
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo acymailing::tooltip(JText::_('FROM_ADDRESS_DESC'), JText::_('FROM_ADDRESS'), '', JText::_('FROM_ADDRESS')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[from_email]" size="40" value="<?php echo $this->escape($this->config->get('from_email')); ?>">
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo acymailing::tooltip(JText::_('REPLYTO_NAME_DESC'), JText::_('REPLYTO_NAME'), '', JText::_('REPLYTO_NAME')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[reply_name]" size="40" value="<?php echo $this->escape($this->config->get('reply_name')); ?>">
				</td>
			</tr>
			<tr>
				<td class="key">
				<?php echo acymailing::tooltip(JText::_('REPLYTO_ADDRESS_DESC'), JText::_('REPLYTO_ADDRESS'), '', JText::_('REPLYTO_ADDRESS')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[reply_email]" size="40" value="<?php echo $this->escape($this->config->get('reply_email')); ?>">
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo acymailing::tooltip(JText::_('BOUNCE_ADDRESS_DESC'), JText::_('BOUNCE_ADDRESS'), '', JText::_('BOUNCE_ADDRESS')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[bounce_email]" size="40" value="<?php echo $this->escape($this->config->get('bounce_email')); ?>">
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo acymailing::tooltip(JText::_('ADD_NAMES_DESC'), JText::_('ADD_NAMES'), '', JText::_('ADD_NAMES')); ?>
				</td>
				<td>
					<?php echo $this->elements->add_names; ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'MAIL_CONFIG' ); ?></legend>
		<table width="100%">
		<tr>
		<td width="50%" valign="top">
			<table class="admintable" cellspacing="1">
				<tr>
					<td width="185" class="key">
						<?php echo acymailing::tooltip(JText::_('MAILER_METHOD_DESC'), JText::_('MAILER_METHOD'), '', JText::_('MAILER_METHOD')); ?>
					</td>
					<td>
						<?php echo $this->elements->mailer_method; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo acymailing::tooltip(JText::_('ENCODING_FORMAT_DESC'), JText::_('ENCODING_FORMAT'), '', JText::_('ENCODING_FORMAT')); ?>
					</td>
					<td>
						<?php echo $this->elements->encoding_format; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo acymailing::tooltip(JText::_('CHARSET_DESC'), JText::_('CHARSET'), '', JText::_('CHARSET')); ?>
					</td>
					<td>
						<?php echo $this->elements->charset; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo acymailing::tooltip(JText::_('WORD_WRAPPING_DESC'), JText::_('WORD_WRAPPING'), '', JText::_('WORD_WRAPPING')); ?>
					</td>
					<td>
						<input class="inputbox" type="text" name="config[word_wrapping]" size="10" value="<?php echo $this->config->get('word_wrapping',0) ?>">
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo acymailing::tooltip(JText::_('HOSTNAME_DESC'), JText::_('HOSTNAME'), '', JText::_('HOSTNAME')); ?>
					</td>
					<td>
						<input class="inputbox" type="text" name="config[hostname]" size="30" value="<?php echo $this->escape($this->config->get('hostname')); ?>">
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo acymailing::tooltip(JText::_('EMBED_IMAGES_DESC'), JText::_('EMBED_IMAGES'), '', JText::_('EMBED_IMAGES')); ?>
					</td>
					<td>
						<?php echo $this->elements->embed_images; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo acymailing::tooltip( JText::_('EMBED_ATTACHMENTS_DESC'), JText::_('EMBED_ATTACHMENTS'), '', JText::_('EMBED_ATTACHMENTS')); ?>
					</td>
					<td>
						<?php echo $this->elements->embed_files; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo acymailing::tooltip(JText::_('MULTIPLE_PART_DESC'), JText::_('MULTIPLE_PART'), '', JText::_('MULTIPLE_PART')); ?>
					</td>
					<td>
						<?php echo $this->elements->multiple_part; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo acymailing::tooltip(JText::_('ACY_DKIM_DESC'), JText::_('ACY_DKIM'), '', JText::_('ACY_DKIM')); ?>
					</td>
					<td>
						<?php echo $this->elements->dkim; ?>
					</td>
				</tr>
			</table>
		</td>
		<td valign="top">
			<fieldset class="adminform" id="sendmail_config" style="display:none">
				<legend>SendMail</legend>
				<table class="admintable" cellspacing="1" >
					<tr>
						<td width="185" class="key">
							<?php echo acymailing::tooltip(JText::_('SENDMAIL_PATH_DESC'), JText::_('SENDMAIL_PATH'), '', JText::_('SENDMAIL_PATH')); ?>
						</td>
						<td>
							<input class="inputbox" type="text" name="config[sendmail_path]" size="30" value="<?php echo $this->config->get('sendmail_path','/usr/sbin/sendmail') ?>">
						</td>
					</tr>
				</table>
			</fieldset>
			<fieldset class="adminform" id="smtp_config" style="display:none">
				<legend><?php echo JText::_( 'SMTP_CONFIG' ); ?></legend>
				<table class="admintable" cellspacing="1">
					<tr>
						<td width="185" class="key">
							<?php echo acymailing::tooltip(JText::_('SMTP_SERVER_DESC'), JText::_('SMTP_SERVER'), '', JText::_('SMTP_SERVER')); ?>
						</td>
						<td>
							<input class="inputbox" type="text" name="config[smtp_host]" size="30" value="<?php echo $this->escape($this->config->get('smtp_host')); ?>">
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo acymailing::tooltip(JText::_('SMTP_PORT_DESC'), JText::_('SMTP_PORT'), '', JText::_('SMTP_PORT')); ?>
						</td>
						<td>
							<input class="inputbox" type="text" name="config[smtp_port]" size="10" value="<?php echo $this->escape($this->config->get('smtp_port')); ?>">
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo acymailing::tooltip(JText::_('SMTP_SECURE_DESC'), JText::_('SMTP_SECURE'), '', JText::_('SMTP_SECURE')); ?>
						</td>
						<td>
							<?php echo $this->elements->smtp_secured; ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo acymailing::tooltip(JText::_('SMTP_ALIVE_DESC'), JText::_('SMTP_ALIVE'), '', JText::_('SMTP_ALIVE')); ?>
						</td>
						<td>
							<?php echo $this->elements->smtp_keepalive; ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo acymailing::tooltip(JText::_('SMTP_AUTHENT_DESC'), JText::_('SMTP_AUTHENT'), '', JText::_('SMTP_AUTHENT')); ?>
						</td>
						<td>
							<?php echo $this->elements->smtp_auth; ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo acymailing::tooltip(JText::_('USERNAME_DESC'), JText::_('USERNAME'), '', JText::_('USERNAME')); ?>
						</td>
						<td>
							<input class="inputbox" type="text" name="config[smtp_username]" size="40" value="<?php echo $this->escape($this->config->get('smtp_username')); ?>">
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo acymailing::tooltip(JText::_('SMTP_PASSWORD_DESC'), JText::_('SMTP_PASSWORD'), '', JText::_('SMTP_PASSWORD')); ?>
						</td>
						<td>
							<input class="inputbox" type="password" name="config[smtp_password]" size="40" value="<?php echo $this->escape($this->config->get('smtp_password')); ?>">
						</td>
					</tr>
				</table>
			</fieldset>
			<fieldset class="adminform" id="dkim_config" style="display:none">
				<legend><?php echo JText::_('ACY_DKIM'); ?></legend>
				<table class="admintable" cellspacing="1" >
					<tr>
						<td width="185" class="key">
							<?php echo JText::_('DKIM_DOMAIN'); ?>
						</td>
						<td>
							<input class="inputbox" type="text" name="config[dkim_domain]" size="30" value="<?php echo $this->escape($this->config->get('dkim_domain','')); ?>"> *
						</td>
					</tr>
					<tr>
						<td width="185" class="key">
							<?php echo JText::_('DKIM_SELECTOR'); ?>
						</td>
						<td>
							<input class="inputbox" type="text" name="config[dkim_selector]" size="30" value="<?php echo $this->escape($this->config->get('dkim_selector','')); ?>"> *
						</td>
					</tr>
					<tr>
						<td width="185" class="key">
							<?php echo JText::_('DKIM_PRIVATE'); ?>
						</td>
						<td>
							<textarea cols="65" rows="8" name="config[dkim_private]"><?php echo $this->config->get('dkim_private',''); ?></textarea> *
						</td>
					</tr>
					<tr>
						<td width="185" class="key">
							<?php echo JText::_('DKIM_PASSPHRASE'); ?>
						</td>
						<td>
							<input class="inputbox" type="text" name="config[dkim_passphrase]" size="30" value="<?php echo $this->escape($this->config->get('dkim_passphrase','')); ?>">
						</td>
					</tr>
					<tr>
						<td width="185" class="key">
							<?php echo JText::_('DKIM_IDENTITY'); ?>
						</td>
						<td>
							<input class="inputbox" type="text" name="config[dkim_identity]" size="30" value="<?php echo $this->escape($this->config->get('dkim_identity','')); ?>">
						</td>
					</tr>
					<tr>
						<td width="185" class="key">
							<?php echo JText::_('DKIM_PUBLIC'); ?>
						</td>
						<td>
							<textarea cols="65" rows="5" name="config[dkim_public]"><?php echo $this->config->get('dkim_public',''); ?></textarea>
						</td>
					</tr>
				</table>
				<a href="http://www.acyba.com/index.php?option=com_content&amp;view=article&amp;catid=34:documentation-acymailing&amp;Itemid=30&amp;id=156:acymailing-dkim" target="_blank"><?php echo JText::_('ACY_HELP'); ?></a>
			</fieldset>
		</td>
		</tr>
		</table>
	</fieldset>
</div>