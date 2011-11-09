<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<script language="javascript" type="text/javascript">
<?php if(version_compare(JVERSION,'1.6.0','<')){ ?>
	function submitbutton(pressbutton) {
		var form = document.adminForm;
		if(pressbutton != 'cancel' && form.email){
			form.email.value = form.email.value.replace(/ /g,"");
			var filter = /^([a-z0-9_'&\.\-\+])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,10})+$/i;
			if(!filter.test(form.email.value)) {
				alert( "<?php echo JText::_( 'VALID_EMAIL', true ); ?>" );
				return false;
			}
		}
		submitform( pressbutton );
	}
<?php }else{ ?>
	Joomla.submitbutton = function(pressbutton) {
		var form = document.adminForm;
		if(pressbutton != 'cancel' && form.email){
			form.email.value = form.email.value.replace(/ /g,"");
			var filter = /^([a-z0-9_'&\.\-\+])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,10})+$/i;
			if(!filter.test(form.email.value)) {
				alert( "<?php echo JText::_( 'VALID_EMAIL', true ); ?>" );
				return false;
			}
		}
		Joomla.submitform(pressbutton,form);
	};
<?php } ?>
</script>
<div id="acy_content" >
<div id="iframedoc"></div>
<form action="index.php?option=<?php echo ACYMAILING_COMPONENT ?>&amp;ctrl=subscriber" method="post" name="adminForm" autocomplete="off">
	<table width="100%">
		<tr><td valign="top">
		<fieldset class="adminform">
		<legend><?php echo JText::_( 'USER_INFORMATIONS' ); ?></legend>
			<table width="100%">
				<tr>
					<td width="50%" align="left">
						<table class="admintable" cellspacing="1">
							<tr>
								<td width="150" class="key">
									<label for="name">
									<?php echo JText::_( 'JOOMEXT_NAME' ); ?>
									</label>
								</td>
								<td>
								<?php
								if(empty($this->subscriber->userid)){
										echo '<input type="text" name="data[subscriber][name]" id="name" class="inputbox" size="40" value="'.$this->escape(@$this->subscriber->name).'" />';
								}else{
									echo $this->subscriber->name;
								}
								?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<label for="email">
									<?php echo JText::_( 'JOOMEXT_EMAIL' ); ?>
									</label>
								</td>
								<td>
									<?php
									if(empty($this->subscriber->userid)){
										echo '<input class="inputbox required" type="text" name="data[subscriber][email]" id="email" size="40" value="'.$this->escape(@$this->subscriber->email).'" />';
									}else{
										echo $this->subscriber->email;
									}
									?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<label for="created">
									<?php echo JText::_( 'CREATED_DATE' ); ?>
									</label>
								</td>
								<td>
									<?php echo acymailing::getDate($this->subscriber->created);?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<label for="ip">
									<?php echo JText::_( 'IP' ); ?>
									</label>
								</td>
								<td>
									<?php echo $this->subscriber->ip;?>
								</td>
							</tr>
					<?php
						if(!empty($this->subscriber->userid)){
					?>
							<tr>
								<td class="key">
									<label for="username">
									<?php echo JText::_( 'ACY_USERNAME' ); ?>
									</label>
								</td>
								<td>
									<?php echo $this->subscriber->username;?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<label for="userid">
									<?php echo JText::_( 'USER_ID' ); ?>
									</label>
								</td>
								<td>
									<?php echo $this->subscriber->userid;?>
								</td>
							</tr>
					<?php
							}
					?>
						</table>
					</td>
					<td align="left" >
						<table class="admintable" cellspacing="1">
							<tr>
								<td class="key">
									<label for="html">
									<?php echo JText::_( 'RECEIVE' ); ?>
									</label>
								</td>
								<td>
								  <?php echo JHTML::_('select.booleanlist', "data[subscriber][html]" , '',$this->subscriber->html,JText::_('HTML'),JText::_('JOOMEXT_TEXT')); ?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<label for="confirmed">
									<?php echo JText::_( 'CONFIRMED' ); ?>
									</label>
								</td>
								<td>
								  <?php echo JHTML::_('select.booleanlist', "data[subscriber][confirmed]" , '',$this->subscriber->confirmed); ?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<label for="block">
									<?php echo JText::_( 'ENABLED' ); ?>
									</label>
								</td>
								<td>
								  <?php echo JHTML::_('select.booleanlist', "data[subscriber][enabled]" , '',$this->subscriber->enabled); ?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<label for="accept">
									<?php echo JText::_( 'ACCEPT_EMAIL' ); ?>
									</label>
								</td>
								<td>
								  <?php echo JHTML::_('select.booleanlist', "data[subscriber][accept]" , '',$this->subscriber->accept); ?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</fieldset>
<?php if(!empty($this->extraFields)){
	echo '</td><td valign="top">';
	include(dirname(__FILE__).DS.'extrafields.'.basename(__FILE__));
	} ?>
	</td></tr></table>
	<div>
	<?php jimport('joomla.html.pane');
		$tabs	=& JPane::getInstance('tabs');
		echo $tabs->startPane( 'user_tabs');
		echo $tabs->startPanel( JText::_( 'SUBSCRIPTION' ), 'user_subscription');
	?>
		<br  style="font-size:1px;" />
		<fieldset class="adminform">
		<legend><?php echo JText::_( 'SUBSCRIPTION' ); ?></legend>
			<table class="adminlist" cellspacing="1" align="center">
				<thead>
					<tr>
						<th class="title titlenum">
						<?php echo JText::_( 'ACY_NUM' );?>
						</th>
						<th class="title titlecolor">
						</th>
						<th  class="title" nowrap="nowrap">
						<?php echo JText::_( 'LIST_NAME' ); ?>
						</th>
						<th  class="title" nowrap="nowrap" width="480">
						<?php echo JText::_( 'STATUS' ); echo '<span style="font-style:italic;margin-left:50px">'.$this->filters->statusquick.'</span>';?>
						</th>
						<th  class="title titledate">
						<?php echo JText::_( 'SUBSCRIPTION_DATE' ); ?>
						</th>
						<th  class="title titledate">
						<?php echo JText::_( 'UNSUBSCRIPTION_DATE' ); ?>
						</th>
						<th  class="title titleid">
							<?php echo JText::_( 'ID' ); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$k = 0;
					for($i = 0,$a = count($this->subscription);$i<$a;$i++){
						$row =& $this->subscription[$i]; ?>
					<tr class="<?php echo "row$k"; ?>" >
						<td align="center">
							<?php echo $i +1; ?>
						</td>
						<td width="12">
						<?php echo '<div class="roundsubscrib rounddisp" style="background-color:'.$row->color.'"></div>'; ?>
						</td>
						<td>
							<?php
							echo acymailing::tooltip($row->description, $row->name, 'tooltip.png', $row->name);
							 ?>
						</td>
						<td align="center">
							<?php echo $this->statusType->display('data[listsub]['.$row->listid.'][status]',@$row->status); ?>
						</td>
						<td align="center">
							<?php if(!empty($row->subdate)) echo acymailing::getDate($row->subdate); ?>
						</td>
						<td align="center">
							<?php if(!empty($row->unsubdate)) echo acymailing::getDate($row->unsubdate); ?>
						</td>
						<td align="center">
							<?php echo $row->listid; ?>
						</td>
					</tr>
					<?php
						$k = 1 - $k;
					} ?>
				</tbody>
			</table>
		</fieldset>
		<?php echo $tabs->endPanel();
		if(!empty($this->open)){
			echo $tabs->startPanel( JText::_( 'ACY_SENT_EMAILS' ), 'user_open');
			?>
			<br  style="font-size:1px;" />
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'ACY_SENT_EMAILS' ); ?></legend>
				<table class="adminlist" cellspacing="1" align="center">
					<thead>
						<tr>
							<th class="title titlenum">
								<?php echo JText::_( 'ACY_NUM' );?>
							</th>
							<th class="title titledate">
								<?php echo JText::_( 'SEND_DATE' ); ?>
							</th>
							<th class="title">
								<?php echo JText::_( 'JOOMEXT_SUBJECT'); ?>
							</th>
							<th class="title titletoggle">
								<?php echo JText::_( 'RECEIVED_VERSION' ); ?>
							</th>
							<th class="title titletoggle">
								<?php echo JText::_( 'OPEN' ); ?>
							</th>
							<th class="title titledate">
								<?php echo JText::_( 'OPEN_DATE' ); ?>
							</th>
							<?php if(acymailing::level(3)){ ?>
								<th class="title titletoggle">
									<?php echo JText::_( 'BOUNCES' ); ?>
								</th>
							<?php } ?>
							<th class="title titletoggle">
								<?php echo JText::_( 'DELIVERED' ); ?>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$k = 0;
							for($i = 0,$a = count($this->open);$i<$a;$i++){
								$row =& $this->open[$i];
						?>
							<tr class="<?php echo "row$k"; ?>">
								<td align="center">
								<?php echo $i+1; ?>
								</td>
								<td align="center">
								<?php echo acymailing::getDate($row->senddate); ?>
								</td>
								<td align="center">
								<?php
									$text = '<b>'.JText::_('ID').' : </b>'.$row->mailid;
									echo acymailing::tooltip( $text, $row->subject, '', $row->subject);
								?>
								</td>
								<td align="center">
									<?php echo $row->html ? JText::_('HTML') : JText::_('JOOMEXT_TEXT'); ?>
								</td>
								<td align="center">
									<?php echo $row->open; ?>
								</td>
								<td align="center">
									<?php if(!empty($row->opendate)) echo acymailing::getDate($row->opendate); ?>
								</td>
								<?php if(acymailing::level(3)){ ?>
								<td align="center">
									<?php echo $row->bounce; ?>
								</td>
								<?php } ?>
								<td align="center">
									<?php echo $this->toggleClass->display('visible',empty($row->fail) ? true : false); ?>
								</td>
							</tr>
						<?php
								$k = 1-$k;
							}
						?>
					</tbody>
				</table>
			</fieldset>
			<?php
			echo $tabs->endPanel();
		}
		if(!empty($this->clicks)){
			echo $tabs->startPanel( JText::_( 'CLICK_STATISTICS' ), 'user_clicks');?>
			<br  style="font-size:1px;" />
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'CLICK_STATISTICS' ); ?></legend>
				<table class="adminlist" cellspacing="1" align="center">
					<thead>
						<tr>
							<th class="title titlenum">
								<?php echo JText::_( 'ACY_NUM' );?>
							</th>
							<th class="title titledate">
								<?php echo JText::_( 'CLICK_DATE' ); ?>
							</th>
							<th class="title">
								<?php echo JText::_( 'JOOMEXT_SUBJECT'); ?>
							</th>
							<th class="title">
								<?php echo JText::_( 'URL' ); ?>
							</th>
							<th class="title titletoggle">
								<?php echo JText::_( 'TOTAL_HITS' ); ?>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$k = 0;
							for($i = 0,$a = count($this->clicks);$i<$a;$i++){
								$row =& $this->clicks[$i];
								$id = 'urlclick'.$i;
						?>
							<tr class="<?php echo "row$k"; ?>" id="<?php echo $id; ?>">
								<td align="center">
								<?php echo $i+1; ?>
								</td>
								<td align="center">
									<?php echo acymailing::getDate($row->date); ?>
								</td>
								<td align="center">
									<?php
									$text = '<b>'.JText::_('ACY_ID').' : </b>'.$row->mailid;
									echo acymailing::tooltip($text, $row->subject, '', $row->subject);
									?>
								</td>
								<td align="center">
									<a target="_blank" href="<?php echo strip_tags($row->url); ?>"><?php echo $row->urlname; ?></a>
								</td>
								<td align="center">
									<?php echo $row->click; ?>
								</td>
							</tr>
						<?php
								$k = 1-$k;
							}
						?>
					</tbody>
				</table>
			</fieldset>
			<?php echo $tabs->endPanel();
		}
		if(!empty($this->queue)){
			echo $tabs->startPanel( JText::_( 'QUEUE' ), 'user_queue');?>
			<br  style="font-size:1px;" />
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'QUEUE' ); ?></legend>
				<table class="adminlist" cellspacing="1" align="center">
					<thead>
						<tr>
							<th class="title titlenum">
								<?php echo JText::_( 'ACY_NUM' );?>
							</th>
							<th class="title titledate">
								<?php echo JText::_( 'SEND_DATE' ); ?>
							</th>
							<th class="title">
								<?php echo JText::_( 'JOOMEXT_SUBJECT'); ?>
							</th>
							<th class="title">
								<?php echo JText::_( 'PRIORITY'); ?>
							</th>
							<th class="title">
								<?php echo JText::_( 'TRY' ); ?>
							</th>
							<th class="title titletoggle" >
								<?php echo JText::_( 'ACY_DELETE' ); ?>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$k = 0;
							for($i = 0,$a = count($this->queue);$i<$a;$i++){
								$row =& $this->queue[$i];
								$id = 'queue'.$i;
						?>
							<tr class="<?php echo "row$k"; ?>" id="<?php echo $id; ?>">
								<td align="center">
								<?php echo $i+1; ?>
								</td>
								<td align="center">
									<?php echo acymailing::getDate($row->senddate); ?>
								</td>
								<td align="center">
									<?php
									$text = '<b>'.JText::_('ACY_ID').' : </b>'.$row->mailid;
									echo acymailing::tooltip($text, $row->subject, '', $row->subject);
									?>
								</td>
								<td align="center">
									<?php echo $row->priority; ?>
								</td>
								<td align="center">
									<?php echo $row->priority; ?>
								</td>
								<td align="center">
									<?php echo $this->toggleClass->delete($id,$row->subid.'_'.$row->mailid,'queue'); ?>
								</td>
							</tr>
						<?php
								$k = 1-$k;
							}
						?>
					</tbody>
				</table>
			</fieldset>
			<?php echo $tabs->endPanel();
		}
		if(!empty($this->history)){
			echo $tabs->startPanel( JText::_( 'ACY_HISTORY' ), 'user_history');
			?>
			<br  style="font-size:1px;" />
			<fieldset class="adminform">
				<legend><?php echo JText::_( 'ACY_HISTORY' ); ?></legend>
				<table class="adminlist" cellspacing="1" align="center">
					<thead>
						<tr>
							<th class="title titlenum">
								<?php echo JText::_( 'ACY_NUM' );?>
							</th>
							<th class="title titledate">
								<?php echo JText::_( 'FIELD_DATE' ); ?>
							</th>
							<th class="title">
								<?php echo JText::_( 'ACY_ACTION' ); ?>
							</th>
							<th class="title">
								<?php echo JText::_( 'ACY_DETAILS'); ?>
							</th>
							<th class="title">
								<?php echo JText::_( 'IP'); ?>
							</th>
							<th class="title" width="30%">
								<?php echo JText::_( 'ACY_SOURCE' ); ?>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php
							$k = 0;
							for($i = 0,$a = count($this->history);$i<$a;$i++){
								$row =& $this->history[$i];
								$id = 'history'.$i;
						?>
							<tr class="<?php echo "row$k"; ?>" >
								<td align="center">
								<?php echo $i+1; ?>
								</td>
								<td align="center">
									<?php echo acymailing::getDate($row->date); ?>
								</td>
								<td align="center">
									<?php echo JText::_('ACTION_'.strtoupper($row->action)); ?>
								</td>
								<td>
									<?php
									$data = explode("\n",$row->data);
									foreach($data as $value){
										if(!strpos($value,'::')) continue;
										list($part1,$part2) = explode("::",$value);
										if(preg_match('#^[A-Z_]*$#',$part2)) $part2 = JText::_($part2);
										echo JText::_($part1).' : '.$part2.'<br />';
									}
									?>
								</td>
								<td align="center">
									<?php echo $row->ip ?>
								</td>
								<td>
									<?php
									if(!empty($row->source)){
										$source = explode("\n",$row->source);
										echo '<div style="cursor:pointer;text-align:center" onclick="if(document.getElementById(\''.$id.'\').style.display == \'none\'){document.getElementById(\''.$id.'\').style.display = \'block\'}else{document.getElementById(\''.$id.'\').style.display = \'none\'}">'.JText::_('VIEW_DETAILS').'</div>';
										echo '<div id="'.$id.'" style="display:none">';
										foreach($source as $value){
											if(!strpos($value,'::')) continue;
											list($part1,$part2) = explode("::",$value);
											echo $part1.' : '.$part2.'<br />';
										}
										echo '</div>';
									}
									?>
								</td>
							</tr>
						<?php
								$k = 1-$k;
							}
						?>
					</tbody>
				</table>
			</fieldset>
			<?php
			echo $tabs->endPanel();
		}
		echo $tabs->endPane(); ?>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="cid[]" value="<?php echo @$this->subscriber->subid; ?>" />
	<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="subscriber" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>