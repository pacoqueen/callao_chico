<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="config_interface">
<br  style="font-size:1px;" />
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'MESSAGES' ); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
				<?php echo acymailing::tooltip(JText::_('DISPLAY_MSG_SUBSCRIPTION_DESC').'<br/><br/><i>'.($this->config->get('require_confirmation',0) ? JText::_('CONFIRMATION_SENT') : JText::_('SUBSCRIPTION_OK')).'</i>', JText::_('DISPLAY_MSG_SUBSCRIPTION'), '', JText::_('DISPLAY_MSG_SUBSCRIPTION')); ?>
				</td>
				<td>
					<?php echo $this->elements->subscription_message; ?>
				</td>
			</tr>
			<tr>
				<td class="key" >
				<?php echo acymailing::tooltip(JText::_('DISPLAY_MSG_CONFIRM_DESC').'<br/><br/><i>'.JText::_('SUBSCRIPTION_CONFIRMED').'</i>', JText::_('DISPLAY_MSG_CONFIRM'), '', JText::_('DISPLAY_MSG_CONFIRM')); ?>
				</td>
				<td>
					<?php echo $this->elements->confirmation_message; ?>
				</td>
			</tr>
			<tr>
				<td class="key" >
				<?php echo acymailing::tooltip(JText::_('DISPLAY_MSG_UNSUBSCRIPTION_DESC'), JText::_('DISPLAY_MSG_UNSUBSCRIPTION'), '', JText::_('DISPLAY_MSG_UNSUBSCRIPTION')); ?>
				</td>
				<td>
					<?php echo $this->elements->unsubscription_message; ?>
				</td>
			</tr>
			<tr>
				<td class="key" >
				<?php echo acymailing::tooltip(JText::_('DISPLAY_MSG_CONFIRMATION_DESC'), JText::_('DISPLAY_MSG_CONFIRMATION'), '', JText::_('DISPLAY_MSG_CONFIRMATION')); ?>
				</td>
				<td>
					<?php echo $this->elements->confirm_message; ?>
				</td>
			</tr>
			<tr>
				<td class="key" >
				<?php echo acymailing::tooltip(JText::_('DISPLAY_MSG_WELCOME_DESC'), JText::_('DISPLAY_MSG_WELCOME'), '', JText::_('DISPLAY_MSG_WELCOME')); ?>
				</td>
				<td>
					<?php echo $this->elements->welcome_message; ?>
				</td>
			</tr>
			<tr>
				<td class="key" >
				<?php echo acymailing::tooltip(JText::_('DISPLAY_MSG_UNSUB_DESC'), JText::_('DISPLAY_MSG_UNSUB'), '', JText::_('DISPLAY_MSG_UNSUB')); ?>
				</td>
				<td>
					<?php echo $this->elements->unsub_message; ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo 'CSS' ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
				<?php echo acymailing::tooltip(JText::_('CSS_MODULE_DESC'), JText::_('CSS_MODULE'), '', JText::_('CSS_MODULE')); ?>
				</td>
				<td>
					<?php echo $this->elements->css_module;?>
				</td>
			</tr>
			<tr>
				<td class="key" >
				<?php echo acymailing::tooltip(JText::_('CSS_FRONTEND_DESC'), JText::_('CSS_FRONTEND'), '', JText::_('CSS_FRONTEND')); ?>
				</td>
				<td>
					<?php echo $this->elements->css_frontend;?>
				</td>
			</tr>
			<tr>
				<td class="key" >
				<?php echo acymailing::tooltip(JText::_('CSS_BACKEND_DESC'), JText::_('CSS_BACKEND'), '', JText::_('CSS_BACKEND')); ?>
				</td>
				<td>
					<?php echo $this->elements->css_backend;?>
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'FEATURES' ); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
				<?php echo acymailing::tooltip(JText::_('FORWARD_DESC'), JText::_('FORWARD_FEATURE'), '', JText::_('FORWARD_FEATURE')); ?>
				</td>
				<td>
					<?php echo $this->elements->forward;?>
				</td>
			</tr>
			<tr>
				<td class="key" >
				<?php echo acymailing::tooltip(JText::_('USE_SEF_DESC'), JText::_('USE_SEF'), '', JText::_('USE_SEF')); ?>
				</td>
				<td>
					<?php echo $this->elements->use_sef;?>
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'MENU' ); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
				<?php echo acymailing::tooltip(JText::_('ACYMAILING_MENU_DESC'), JText::_('ACYMAILING_MENU'), '', JText::_('ACYMAILING_MENU')); ?>
				</td>
				<td>
					<?php echo $this->elements->acymailing_menu; ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'ACY_EDITOR' ); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
				<?php echo acymailing::tooltip(JText::_('EDITOR_DESC'), JText::_('ACY_EDITOR'), '', JText::_('ACY_EDITOR')); ?>
				</td>
				<td>
					<?php echo $this->elements->editor;?>
				</td>
			</tr>
		</table>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'ARCHIVE_SECTION' ); ?></legend>
		<table class="admintable" cellspacing="1">
		<?php
		if(file_exists(ACYMAILING_ROOT.'components'.DS.'com_jcomments'.DS.'jcomments.php')){
			$jcomments = ($this->config->get('comments_feature') == 'jcomments') ? 'checked="checked"' : '';
		}else{
			$jcomments = 'disabled="disabled"';
		}
		if(file_exists(ACYMAILING_ROOT.'plugins'.DS.'content'.DS.'jom_comment_bot.php')){
			$jomcomment = ($this->config->get('comments_feature') == 'jomcomment') ? 'checked="checked"' : '';
		}else{
			$jomcomment = 'disabled="disabled"';
		}
		$no_checked = $this->config->get('comments_feature') ? '' : 'checked="checked"';
		?>
		<tr>
			<td class="key" >
			<?php echo acymailing::tooltip(JText::_('COMMENTS_ENABLED_DESC'), JText::_('COMMENTS_ENABLED'), '', JText::_('COMMENTS_ENABLED')); ?>
			</td>
			<td>
				<input name="config[comments_feature]" id="config_comments_feature" value="" <?php echo $no_checked;?> size="1" type="radio" />
				<label for="config_comments_feature"><?php echo JText::_('JOOMEXT_NO');?></label>
				<input name="config[comments_feature]" id="config_comments_feature_jcomments" value="jcomments" <?php echo $jcomments;?> size="1" type="radio" />
				<label for="config_comments_feature_jcomments">jComments</label>
				<input name="config[comments_feature]" id="config_comments_feature_jomcomment" value="jomcomment" <?php echo $jomcomment;?> size="1" type="radio" />
				<label for="config_comments_feature_jomcomment">jomComment</label>
			</td>
		</tr>
		<tr>
			<td class="key" >
			<?php echo acymailing::tooltip(JText::_('SUBJECT_DISPLAY_DESC'), JText::_('SUBJECT_DISPLAY'), '', JText::_('SUBJECT_DISPLAY')); ?>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist', "config[frontend_subject]" , '',$this->config->get('frontend_subject',1) ); ?>
			</td>
		</tr>
		<tr>
			<td class="key" >
			<?php echo acymailing::tooltip(JText::_('FRONTEND_PDF_DESC'), JText::_('FRONTEND_PDF'), '', JText::_('FRONTEND_PDF')); ?>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist', "config[frontend_pdf]" , '',$this->config->get('frontend_pdf',0) ); ?>
			</td>
		</tr>
		<tr>
			<td class="key" >
			<?php echo acymailing::tooltip(JText::_('FRONTEND_PRINT_DESC'), JText::_('FRONTEND_PRINT'), '', JText::_('FRONTEND_PRINT')); ?>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist', "config[frontend_print]" , '',$this->config->get('frontend_print',0) ); ?>
			</td>
		</tr>
		<tr>
			<td class="key" >
			<?php echo acymailing::tooltip(JText::_('SHOW_DESCRIPTION_DESC'), JText::_('SHOW_DESCRIPTION'), '', JText::_('SHOW_DESCRIPTION')); ?>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist', "config[show_description]" , '',$this->config->get('show_description',1) ); ?>
			</td>
		</tr>
		<tr>
			<td class="key" >
			<?php echo acymailing::tooltip(JText::_('SHOW_HEADINGS_DESC'), JText::_('SHOW_HEADINGS'), '', JText::_('SHOW_HEADINGS')); ?>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist', "config[show_headings]" , '',$this->config->get('show_headings',1) ); ?>
			</td>
		</tr>
		<tr>
			<td class="key" >
			<?php echo acymailing::tooltip(JText::_('SHOW_SENDDATE_DESC'), JText::_('SHOW_SENDDATE'), '', JText::_('SHOW_SENDDATE')); ?>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist', "config[show_senddate]" , '',$this->config->get('show_senddate',1) ); ?>
			</td>
		</tr>
		<tr>
			<td class="key" >
			<?php echo acymailing::tooltip(JText::_('SHOW_FILTER_DESC'), JText::_('SHOW_FILTER'), '', JText::_('SHOW_FILTER')); ?>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist', "config[show_filter]" , '',$this->config->get('show_filter',1) ); ?>
			</td>
		</tr>
		<tr>
			<td class="key" >
			<?php echo acymailing::tooltip(JText::_('OPEN_POPUP_DESC'), JText::_('OPEN_POPUP'), '', JText::_('OPEN_POPUP')); ?>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist', "config[open_popup]" , '',$this->config->get('open_popup',0) ); ?>
				<input type="text" name="config[popup_width]" size="5" value="<?php echo intval($this->config->get('popup_width',750)); ?>" /> x <input type="text" name="config[popup_height]"  size="5" value="<?php echo intval($this->config->get('popup_height',550)); ?>" />
			</td>
		</tr>
		</table>
	</fieldset>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'UNSUB_PAGE' ); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
				<?php echo acymailing::tooltip(str_replace('UNSUB_INTRO',JText::_('UNSUB_INTRO'),$this->config->get('unsub_intro','UNSUB_INTRO')), JText::_('UNSUB_INTRODUCTION'), '', JText::_('UNSUB_INTRODUCTION')); ?>
				</td>
				<td>
					<textarea cols="50" rows="5" name="config[unsub_intro]"><?php echo $this->config->get('unsub_intro','UNSUB_INTRO'); ?></textarea>
				</td>
			</tr>
			<tr>
				<td class="key" >
				<?php echo JText::_('UNSUB_DISP_CHOICE'); ?>
				</td>
				<td>
					<?php echo JHTML::_('select.booleanlist', "config[unsub_dispoptions]" , '',$this->config->get('unsub_dispoptions',1) ); ?>
				</td>
			</tr>
			<tr>
				<td class="key" >
				<?php echo JText::_('UNSUB_DISP_SURVEY'); ?>
				</td>
				<td>
					<?php echo JHTML::_('select.booleanlist', "config[unsub_survey]" , '',$this->config->get('unsub_survey',1) ); ?>
				</td>
			</tr>
		</table>
	</fieldset>
	<?php if(acymailing::level(3)) include(dirname(__FILE__).DS.'interface_enterprise.php'); ?>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'FOOTER' ); ?></legend>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
				<?php echo acymailing::tooltip(JText::_('SHOW_FOOTER_DESC'), JText::_('SHOW_FOOTER'), '', JText::_('SHOW_FOOTER')); ?>
				</td>
				<td>
					<?php echo $this->elements->show_footer; ?>
				</td>
			</tr>
		</table>
	</fieldset>
</div>