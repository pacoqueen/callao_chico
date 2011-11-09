<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="acyarchiveview">
<div class="contentheading"><?php if($this->config->get('frontend_subject',1)) echo $this->mail->subject; ?>
<?php if($this->frontEndManagement AND ($this->config->get('frontend_modif',1) || ($this->mail->userid == $this->my->id)) AND ($this->config->get('frontend_modif_sent',1) || empty($this->mail->senddate))){ ?>
		<a href="<?php echo acymailing::completeLink('newsletter&task=edit&mailid='.$this->mail->mailid.'&listid='.$this->list->listid); ?>"><img class="icon16" src="<?php echo ACYMAILING_IMAGES ?>icons/icon-16-edit.png" alt="<?php echo JText::_('ACY_EDIT',true) ?>"/></a>
	<?php } ?>
	<?php if($this->config->get('frontend_print',0) OR $this->config->get('frontend_pdf',0)) {
		$link = 'archive&task=view&mailid='.JRequest::getString('mailid');
		$listid = JRequest::getString('listid');
		if(!empty($listid)) $link .= '&listid='.$listid;
		$key = JRequest::getString('key');
		if(!empty($key)) $link .= '&key='.$key; ?>
	<div align="right" style="float:right;">
		<table>
		<tr>
	<?php if($this->config->get('frontend_pdf',0)){?>
		<td class="buttonheading">
	<?php $pdfimage = JHTML::_('image.site',  'pdf_button.png', '/images/M_images/', NULL, NULL, JText::_( 'PDF' ) );
		$pdflink = acymailing::completeLink($link,true);
		$pdflink .= strpos($pdflink,'?') ? '&format=pdf' : '?format=pdf';
		?>
		<a href="<?php echo $pdflink; ?>" title="<?php echo JText::_( 'PDF' ); ?>" onclick="window.open(this.href,'win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no'); return false;" rel="nofollow"><?php echo $pdfimage; ?></a>
		</td>
	<?php }
		if($this->config->get('frontend_print',0)){?>
		<td class="buttonheading">
		<?php
		$printimage = JHTML::_('image.site',  'printButton.png', '/images/M_images/', NULL, NULL, JText::_( 'PRINT' ) );
		if(JRequest::getString('tmpl') == 'component'){?>
			<a title="<?php echo JText::_( 'PRINT' ); ?>" href="#" onclick="window.print();return false;"><?php echo $printimage; ?></a>
		<?php }else{ ?>
			<a title="<?php echo JText::_( 'PRINT' ); ?>" href="<?php echo acymailing::completeLink($link,true); ?>" onclick="window.open(this.href,'win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no'); return false;" rel="nofollow"><?php echo $printimage; ?></a>
		<?php }	?>
		</td>
	<?php } ?>
		</tr></table>
	</div>
	<?php } ?>
</div>
<div class="newsletter_body" ><?php echo $this->mail->html ? $this->mail->body : nl2br($this->mail->altbody); ?></div>
<?php if(!empty($this->mail->attachments)){?>
<fieldset class="newsletter_attachments"><legend><?php echo JText::_( 'ATTACHMENTS' ); ?></legend>
<table>
	<?php foreach($this->mail->attachments as $attachment){
			echo '<tr><td><a href="'.$attachment->url.'" target="_blank">'.$attachment->name.'</a></td></tr>';
	}?>
</table>
</fieldset>
<?php }
if($this->config->get('comments_feature') == 'jcomments'){
	$comments = ACYMAILING_ROOT.'components'.DS.'com_jcomments'.DS.'jcomments.php';
	if (file_exists($comments)) {
		require_once($comments);
		echo JComments::showComments($this->mail->mailid, 'com_acymailing', $this->mail->subject);
	}
}elseif($this->config->get('comments_feature') == 'jomcomment'){
	$comments = ACYMAILING_ROOT.'plugins'.DS.'content'.DS.'jom_comment_bot.php';
	if (file_exists($comments)) {
		require_once($comments);
		echo jomcomment($this->mail->mailid, 'com_acymailing');
	}
}
?>
</div>