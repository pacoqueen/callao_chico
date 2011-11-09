<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<br  style="font-size:1px;" />
<div id="dash_stats">
	<table class="adminlist" cellpadding="1">
		<thead>
			<tr>
				<th class="title">
					<?php echo JText::_('JOOMEXT_SUBJECT'); ?>
				</th>
				<th class="title titledate">
					<?php echo JText::_('SEND_DATE'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_( 'OPEN' );?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_( 'SENT_HTML' );?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_( 'SENT_TEXT' );?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_( 'FAILED' );?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$k = 0;
				foreach($this->stats as $oneStat){
					$row =& $oneStat;
			?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<a href="<?php echo acymailing::completeLink('stats&task=detaillisting&filter_mail='.$row->mailid); ?>"><?php echo $row->subject; ?></a>
					</td>
					<td align="center" style="text-align:center">
						<?php echo acymailing::getDate($row->senddate); ?>
					</td>
					<td align="center" style="text-align:center">
						<?php echo empty($row->senthtml) ? '' : ((int)($row->openunique / $row->senthtml*100)).'%'; ?>
					</td>
					<td align="center" style="text-align:center">
						<?php echo $row->senthtml; ?>
					</td>
					<td align="center" style="text-align:center">
						<?php echo $row->senttext; ?>
					</td>
					<td align="center" style="text-align:center">
						<?php echo $row->fail; ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
</div>