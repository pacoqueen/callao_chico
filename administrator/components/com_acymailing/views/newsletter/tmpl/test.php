<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<form action="<?php echo JRoute::_('index.php?option=com_acymailing&ctrl='.$this->ctrl); ?>" method="post" name="adminForm" autocomplete="off">
<fieldset class="adminform">
	<legend><?php echo JText::_( 'SEND_TEST' ); ?></legend>
	<table>
		<tr>
			<td valign="top">
				<?php echo JText::_( 'SEND_TEST_TO' ); ?>
			</td>
			<td>
				<?php echo $this->receiverClass->display('receiver_type',$this->infos->receiver_type); ?>
				<div id="emailfield" style="display:none" ><?php echo JText::_('EMAIL_ADDRESS')?> <input class="inputbox" type="text" id="test_email" name="test_email" size="40" value="<?php echo $this->infos->test_email;?>" />
				<?php
					$app =& JFactory::getApplication();
					if($app->isAdmin()){
						echo ' <a class="modal" title="'.JText::_('ACY_EDIT',true).'"  href="index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=subscriber&amp;task=choose" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><img class="icon16" src="'.ACYMAILING_IMAGES.'icons/icon-16-edit.png" alt="'.JText::_('ACY_EDIT',true).'"/></a>';
					}
				?>
				</div>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_( 'SEND_VERSION' ); ?>
			</td>
			<td>
			<?php if($this->mail->html){
				echo JHTML::_('select.booleanlist', "test_html" , '',$this->infos->test_html,JText::_('HTML'),JText::_('JOOMEXT_TEXT') );
			}else{
				echo JText::_('JOOMEXT_TEXT');
				echo '<input type="hidden" name="test_html" value="0" />';
			} ?>
			</td>
		</tr>
		<tr>
			<td>
			</td>
			<td>
				<button type="submit"><?php echo JText::_('SEND_TEST')?></button>
			</td>
		</tr>
	</table>
</fieldset>
<input type="hidden" name="cid[]" value="<?php echo $this->mail->mailid; ?>" />
<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
<?php if(!empty($this->lists)){
	$firstList = reset($this->lists);
	$myListId = $firstList->listid;
	}else{
		$myListId = JRequest::getInt('listid',0);
	}
if(!empty($myListId)){?> <input type="hidden" name="listid" value="<?php echo $myListId; ?>"/> <?php } ?>
<input type="hidden" name="task" value="sendtest" />
<input type="hidden" name="ctrl" value="<?php echo $this->ctrl; ?>" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>