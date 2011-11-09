<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="acy_content">
<div id="iframedoc"></div>
<form action="index.php?option=com_acymailing&amp;ctrl=list" method="post" name="adminForm" autocomplete="off">
	<table class="adminform" cellspacing="1" width="100%">
		<tr>
			<td>
				<label for="name">
					<?php echo JText::_( 'LIST_NAME' ); ?>
				</label>
			</td>
			<td>
				<input type="text" name="data[list][name]" id="name" class="inputbox" size="40" value="<?php echo $this->escape(@$this->list->name); ?>" />
			</td>
			<td>
				<label for="enabled">
					<?php echo JText::_( 'ENABLED' ); ?>
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist', "data[list][published]" , '',$this->list->published); ?>
			</td>
		</tr>
		<tr>
			<td>
				<label for="alias">
					<?php echo JText::_( 'JOOMEXT_ALIAS' ); ?>
				</label>
			</td>
			<td>
				<input type="text" name="data[list][alias]" id="alias" class="inputbox" size="40" value="<?php echo $this->escape(@$this->list->alias); ?>" />
			</td>
			<td class="key">
				<label for="visible">
					<?php echo JText::_( 'JOOMEXT_VISIBLE' ); ?>
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist', "data[list][visible]" , '',$this->list->visible); ?>
			</td>
		</tr>
		<tr>
			<td>
				<label for="creator">
					<?php echo JText::_( 'CREATOR' ); ?>
				</label>
			</td>
			<td>
				<input type="hidden" id="listcreator" name="data[list][userid]" value="<?php echo @$this->list->userid; ?>" />
				<?php echo '<span id="creatorname">'.@$this->list->creatorname.'</span>';
				echo ' <a class="modal" title="'.JText::_('ACY_EDIT',true).'"  href="index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=subscriber&amp;task=choose&amp;onlyreg=1" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><img class="icon16" src="'.ACYMAILING_IMAGES.'icons/icon-16-edit.png" alt="'.JText::_('ACY_EDIT',true).'"/></a>';
				?>
			</td>
			<td>
				<?php echo JText::_('COLOUR'); ?>
			</td>
			<td>
				<?php echo $this->colorBox->displayAll('','data[list][color]',@$this->list->color); ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_('MSG_UNSUB'); ?>
			</td>
			<td>
				<?php echo $this->unsubMsg->display(@$this->list->unsubmailid); ?>
			</td>
			<td>
				<?php if(acymailing::level(1)){?>
				<label for="welcomemsg">
					<?php echo JText::_( 'MSG_WELCOME' ); ?>
				</label>
				<?php } ?>
			</td>
			<td>
				<?php if(acymailing::level(1)) echo $this->welcomeMsg->display(@$this->list->welmailid); ?>
			</td>
		</tr>
	</table>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'ACY_DESCRIPTION' ); ?></legend>
		<?php echo $this->editor->display();?>
	</fieldset>
	<?php if(acymailing::level(1)){ ?>
		<table width="100%"><tr>
		<?php if($this->languages->multipleLang){ ?>
		<td width="30%" valign="top">
		<?php include(dirname(__FILE__).DS.'languages.php'); ?>
		</td>
	<?php }} ?>
	<?php if(acymailing::level(3)){?>
		<td valign="top">
		<?php include(dirname(__FILE__).DS.'acl.php'); ?>
		</td>
	<?php } ?>
	<?php if(acymailing::level(1)){ ?>
		</tr></table>
	<?php } ?>
	<div class="clr"></div>
	<input type="hidden" name="cid[]" value="<?php echo @$this->list->listid; ?>" />
	<input type="hidden" name="option" value="com_acymailing" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="list" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>