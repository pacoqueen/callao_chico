<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<table class="adminform" width="100%">
	<tr>
		<td class="key" id="subjectkey">
			<label for="subject">
				<?php echo JText::_( 'JOOMEXT_SUBJECT' ); ?>
			</label>
		</td>
		<td id="subjectinput">
			<input type="text" name="data[mail][subject]" id="subject" class="inputbox" style="width:80%" value="<?php echo $this->escape(@$this->mail->subject); ?>" />
		</td>
		<td class="key" id="publishedkey">
        	<label for="published">
          	<?php echo JText::_( 'ACY_PUBLISHED' ); ?>
        	</label>
		</td>
		<td id="publishedinput">
			<?php echo ($this->mail->published == 2) ? JText::_('SCHED_NEWS') : JHTML::_('select.booleanlist', "data[mail][published]" , '',$this->mail->published,JText::_('JOOMEXT_YES'),JText::_('JOOMEXT_NO')); ?>
		</td>
	</tr>
	<tr>
		<td class="key" id="aliaskey">
			<label for="alias">
				<?php echo JText::_( 'JOOMEXT_ALIAS' ); ?>
            </label>
		</td>
		<td id="aliasinput">
            <input class="inputbox" type="text" name="data[mail][alias]" id="alias" style="width:60%" value="<?php echo @$this->mail->alias; ?>" />
		</td>
		<td class="key" id="visiblekey">
			<label for="visible">
				<?php echo JText::_( 'JOOMEXT_VISIBLE' ); ?>
			</label>
		</td>
		<td id="visibleinput">
			<?php echo JHTML::_('select.booleanlist', "data[mail][visible]" , '',$this->mail->visible,JText::_('JOOMEXT_YES'),JText::_('JOOMEXT_NO')); ?>
		</td>
	</tr>
	<tr>
		<td class="key" id="createdkey">
			<?php echo JText::_( 'CREATED_DATE' ); ?>
		</td>
		<td id="createdinput">
			<?php echo acymailing::getDate(@$this->mail->created);?>
		</td>
		<td class="key" id="sendhtmlkey">
			<?php echo JText::_( 'SEND_HTML' ); ?>
		</td>
		<td id="sendhtmlinput">
			<?php echo JHTML::_('select.booleanlist', "data[mail][html]" , 'onclick="updateAcyEditor(this.value)"',$this->mail->html,JText::_('JOOMEXT_YES'),JText::_('JOOMEXT_NO')); ?>
		</td>
	</tr>
	<?php if(!empty($this->mail->senddate)){?>
	<tr>
		<td class="key" id="senddatekey">
			<?php echo JText::_( 'SEND_DATE' ); ?>
		</td>
		<td id="senddateinput">
			<?php echo acymailing::getDate(@$this->mail->senddate);?>
		</td>
		<td class="key" id="sentbykey">
			<?php if(!empty($this->mail->sentby)) echo JText::_( 'SENT_BY' ); ?>
		</td>
		<td id="sentbyinput">
			<?php echo @$this->sentbyname; ?>
		</td>
	</tr>
	<?php } ?>
</table>