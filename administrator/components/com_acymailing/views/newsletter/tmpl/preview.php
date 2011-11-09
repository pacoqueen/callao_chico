<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="acy_content" >
<div id="iframedoc"></div>
	<table width="100%">
		<tr >
			<td width="50%" valign="top" id="testnewsletter">
				<?php include(dirname(__FILE__).DS.'test.php'); ?>
			</td>
			<td valign="top" id="receiversnewsletter">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'NEWSLETTER_SENT_TO' ); ?></legend>
					<table class="adminlist" cellspacing="1" align="center">
						<tbody>
							<?php if(!empty($this->lists)){
								$k = 0;
								foreach($this->lists as $row){
							?>
							<tr class="<?php echo "row$k"; ?>">
								<td>
									<?php
									if(!$row->published) echo JHTML::_('tooltip', JText::_('LIST_PUBLISH'), '', '../../../media/com_acymailing/images/warning.png').' ';
									echo acymailing::tooltip($row->description,$row->name,'',$row->name);
									echo ' ( '.$row->nbsub.' '.JText::_('USERS').' )';
									echo '<div class="roundsubscrib rounddisp" style="background-color:'.$row->color.'"></div>';
									?>
								</td>
							</tr>
							<?php $k = 1-$k;}}else{ ?>
								<tr>
									<td>
										<?php echo JText::_('EMAIL_AFFECT');?>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</fieldset>
			</td>
		</tr>
	</table>
	<?php include(dirname(__FILE__).DS.'previewcontent.php'); ?>
</div>