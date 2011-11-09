<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="page-security">
<br  style="font-size:1px;" />
<?php if(acymailing::level(1)) {
} ?>
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'ACY_FILES' ); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
					<?php echo acymailing::tooltip(JText::_('ALLOWED_FILES_DESC'), JText::_('ALLOWED_FILES'), '', JText::_('ALLOWED_FILES')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[allowedfiles]" size="60" value="<?php echo strtolower(str_replace(' ','',$this->config->get('allowedfiles'))); ?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo acymailing::tooltip(JText::_('UPLOAD_FOLDER_DESC'), JText::_('UPLOAD_FOLDER'), '', JText::_('UPLOAD_FOLDER')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[uploadfolder]" size="60" value="<?php echo $this->config->get('uploadfolder'); ?>" />
				</td>
			</tr>
		</table>
	</fieldset>
<?php if(acymailing::level(1)) {
} ?>
</div>