<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="iframedoc"></div>
<form action="index.php?option=<?php echo ACYMAILING_COMPONENT ?>&amp;ctrl=subscriber" method="post" name="adminForm">
	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_( 'JOOMEXT_FILTER' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->pageInfo->search;?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_( 'JOOMEXT_GO' ); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'JOOMEXT_RESET' ); ?></button>
				<span id="massaction" style="display:none"><a style="margin-left:40px" id="masslink" class="modal" href="index.php?option=com_acymailing&ctrl=filter&tmpl=component" rel="{handler: 'iframe', size: {x: 500, y: 300}}"><button onclick="return false"> <?php echo JText::_('ACTIONS'); ?> </button></a></span>
			</td>
			<td nowrap="nowrap">
				<?php echo $this->filters->lists; ?>
				<?php echo $this->filters->status; ?>
			</td>
		</tr>
	</table>
	<table class="adminlist" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'ACY_NUM' ); ?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->rows); ?>);" />
				</th>
				<?php
				foreach($this->displayFields as $map => $oneField){
					if($map == 'html') continue; ?>
					<th class="title">
					<?php echo JHTML::_('grid.sort',   $this->customFields->trans($oneField->fieldname), 'a.'.$map, $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
					</th>
				<?php } ?>
				<th class="title">
					<?php echo JText::_('SUBSCRIPTION'); ?>
				</th>
				<th class="title titledate">
					<?php echo JHTML::_('grid.sort',   JText::_('CREATED_DATE'), 'a.created', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<?php if(!empty($this->displayFields['html'])){ ?>
				<th class="title titletoggle">
					<?php echo JHTML::_('grid.sort',   JText::_('RECEIVE_HTML'), 'a.html', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<?php } ?>
				<?php if($this->config->get('require_confirmation',1)){ ?>
					<th class="title titletoggle">
						<?php echo JHTML::_('grid.sort',   JText::_('CONFIRMED'), 'a.confirmed', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
					</th>
				<?php } ?>
				<th class="title titletoggle">
					<?php echo JHTML::_('grid.sort',   JText::_('ENABLED'), 'a.enabled', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title titleid">
					<?php echo JHTML::_('grid.sort',   JText::_('USER_ID'), 'a.userid', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title titleid">
					<?php echo JHTML::_('grid.sort',   JText::_('ID'), 'a.subid', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="<?php echo count($this->displayFields)+9; ?>">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				$k = 0;
				$i = 0;
				foreach($this->rows as $row){
					$confirmedid = 'confirmed_'.$row->subid;
					$htmlid = 'html_'.$row->subid;
					$enabledid = 'enabled_'.$row->subid;
			?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center">
					<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td align="center">
						<input type="checkbox" onclick="isChecked(this.checked);" value="<?php echo $row->subid; ?>" name="cid[]" id="cb<?php echo $i; ?>">
					</td>
					<?php foreach($this->displayFields as $map => $oneField){
						if($map == 'html') continue; ?>
						<td>
						<?php
						if($map =='email') echo '<a href="'.acymailing::completeLink('subscriber&task=edit&subid='.$row->subid).'">';
						echo $this->customFields->listing($oneField,$row->$map);
						if($map =='email') echo '</a>';
						?>
						</td>
					<?php } ?>
					<td align="right">
						<?php
						if(empty($row->accept)){
							echo '<div class="icon-16-refuse" >'.JHTML::_('tooltip',JText::_('USER_REFUSE',true), '','','&nbsp;&nbsp;&nbsp;&nbsp;').'</div>';
						}
						foreach($this->lists as $listid => $list){
							if(empty($row->subscription->$listid)) continue;
								$statuslistid = 'status_'.$listid.'_'.$row->subid;
								echo '<div id="'.$statuslistid.'" class="loading">';
								$extra = null;
								$extra['color'] = $this->lists[$listid]->color;
								$extra['tooltiptitle'] = $this->lists[$listid]->name;
								$extra['tooltip'] = '<b>'.JText::_('LIST_NAME').' : </b>'.$this->lists[$listid]->name.'<br/>';
								if($row->subscription->$listid->status > 0){
									$extra['tooltip'] .= '<b>'.JText::_('STATUS').' : </b>';
									$extra['tooltip'] .= ($row->subscription->$listid->status == '1') ? JText::_('SUBSCRIBED') : JText::_('PENDING_SUBSCRIPTION');
									$extra['tooltip'] .= '<br/><b>'.JText::_('SUBSCRIPTION_DATE').' : </b>'.acymailing::getDate($row->subscription->$listid->subdate);
								}else{
									$extra['tooltip'] .= '<b>'.JText::_('STATUS').' : </b>'.JText::_('UNSUBSCRIBED').'<br/>';
									$extra['tooltip'] .= '<b>'.JText::_('UNSUBSCRIPTION_DATE').' : </b>'.acymailing::getDate($row->subscription->$listid->unsubdate);
								}
								echo $this->toggleClass->toggle($statuslistid,$row->subscription->$listid->status,'listsub',$extra);
								echo '</div>';
							}
						?>
						</td>
					<td align="center">
						<?php echo acymailing::getDate($row->created); ?>
					</td>
					<?php if(!empty($this->displayFields['html'])){ ?>
					<td align="center">
						<span id="<?php echo $htmlid ?>" class="loading"><?php echo $this->toggleClass->toggle($htmlid,$row->html,'subscriber') ?></span>
					</td>
					<?php } ?>
					<?php if($this->config->get('require_confirmation',1)){ ?>
					<td align="center">
						<span id="<?php echo $confirmedid ?>" class="loading"><?php echo $this->toggleClass->toggle($confirmedid,$row->confirmed,'subscriber') ?></span>
					</td>
					<?php } ?>
					<td align="center">
						<span id="<?php echo $enabledid ?>" class="loading"><?php echo $this->toggleClass->toggle($enabledid,$row->enabled,'subscriber') ?></span>
					</td>
					<td align="center">
						<?php if(!empty($row->userid)){
							if(file_exists(ACYMAILING_ROOT.'components'.DS.'com_comprofiler'.DS.'comprofiler.php')){
								$editLink = 'index.php?option=com_comprofiler&task=edit&cid[]=';
							}elseif(version_compare(JVERSION,'1.6.0','<')){
								$editLink = 'index.php?option=com_users&task=edit&cid[]=';
							}else{
								$editLink = 'index.php?option=com_users&task=user.edit&id=';
							}
							$text = JText::_('ACY_USERNAME').' : <b>'.$row->username;
							$text .= '</b><br/>'.JText::_('USER_ID').' : <b>'.$row->userid.'</b>';
							echo acymailing::tooltip($text,$row->username,'',$row->userid,$editLink.$row->userid);} ?>
					</td>
					<td align="center">
						<?php echo $row->subid; ?>
					</td>
				</tr>
			<?php
					$k = 1-$k; $i++;
				}
			?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
