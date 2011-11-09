<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="acy_content">
<div id="iframedoc"></div>
<form action="index.php?tmpl=component&amp;option=<?php echo ACYMAILING_COMPONENT ?>" method="post" name="adminForm" autocomplete="off" enctype="multipart/form-data">
	<fieldset>
		<div class="header icon-48-newsletter" style="float: left;"><?php echo JText::_('EMAIL_NAME').' : '.$this->mail->subject; ?></div>
		<div class="toolbar" id="toolbar" style="float: right;">
			<table><tr>
			<?php if(empty($this->mail->mailid)){ ?><td><a onclick="displayTemplates(); return false;" href="#" ><span class="icon-32-acytemplate" title="<?php echo JText::_('ACY_TEMPLATES',true); ?>"></span><?php echo JText::_('ACY_TEMPLATES'); ?></a></td><?php } ?>
			<td><a onclick="try{IeCursorFix();}catch(e){}; displayTags(); return false;" href="#" ><span class="icon-32-tag" title="<?php echo JText::_('TAGS',true); ?>"></span><?php echo JText::_('TAGS'); ?></a></td>
			<td><span class="divider"></span></td>
			<td><a onclick="javascript:submitbutton('test'); return false;" href="#" ><span class="icon-32-send" title="<?php echo JText::_('SEND_TEST',true); ?>"></span><?php echo JText::_('SEND_TEST'); ?></a></td>
			<td><a onclick="javascript:submitbutton('apply'); return false;" href="#" ><span class="icon-32-save" title="<?php echo JText::_('ACY_SAVE',true); ?>"></span><?php echo JText::_('ACY_SAVE'); ?></a></td>
			</tr></table>
		</div>
	</fieldset>
<div id="iframetemplate"></div><div id="iframetag"></div>
		<?php include(dirname(__FILE__).DS.'param.'.basename(__FILE__)); ?>
		<br/>
		<fieldset class="adminform" width="100%" id="htmlfieldset">
			<legend><?php echo JText::_( 'HTML_VERSION' ); ?></legend>
			<?php echo $this->editor->display(); ?>
		</fieldset>
		<fieldset class="adminform" >
			<legend><?php echo JText::_( 'TEXT_VERSION' ); ?></legend>
			<textarea style="width:100%" rows="20" name="data[mail][altbody]" id="altbody" ><?php echo @$this->mail->altbody; ?></textarea>
		</fieldset>
	<div class="clr"></div>
	<input type="hidden" name="cid[]" value="<?php echo @$this->mail->mailid; ?>" />
	<?php if(!empty($this->mail->type)){ ?>
		<input type="hidden" name="data[mail][type]" value="<?php echo $this->mail->type; ?>" />
	<?php } ?>
	<input type="hidden" id="tempid" name="data[mail][tempid]" value="<?php echo @$this->mail->tempid; ?>" />
	<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="email" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>