<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php if($this->mail->html){?>
<fieldset class="adminform" width="100%" id="htmlfieldset">
	<legend><?php echo JText::_( 'HTML_VERSION' ); ?></legend>
	<div class="newsletter_body" ><?php echo $this->mail->body; ?></div>
</fieldset>
<?php } ?>
<fieldset class="adminform" id="textfieldset">
	<legend><?php echo JText::_( 'TEXT_VERSION' ); ?></legend>
	<textarea style="width:100%" rows="20" readonly="readonly"><?php echo $this->mail->altbody; ?></textarea>
</fieldset>
<?php if(!empty($this->mail->attachments)){?>
<fieldset class="newsletter_attachments"><legend><?php echo JText::_( 'ATTACHMENTS' ); ?></legend>
<table>
	<?php foreach($this->mail->attachments as $attachment){
			echo '<tr><td><a href="'.$attachment->url.'" target="_blank">'.$attachment->name.'</a></td></tr>';
	}?>
</table>
</fieldset>
<?php } ?>
<div class="clr"></div>