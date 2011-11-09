<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<script language="JavaScript" type="text/javascript">
     function statsusers(){
		var dataTable = new google.visualization.DataTable();
		dataTable.addRows(<?php echo count($this->statsusers); ?>);
		dataTable.addColumn('string');
		dataTable.addColumn('number','<?php echo JText::_('USERS',true); ?>');
		<?php
		$i = count($this->statsusers)-1;
		foreach($this->statsusers as $oneResult){
			echo "dataTable.setValue($i, 0, '".addslashes(acymailing::getDate(acymailing::getTime($oneResult->subday),JText::_('DATE_FORMAT_LC3')))."'); ";
			echo "dataTable.setValue($i, 1, ".intval(@$oneResult->total)."); ";
			if($i-- == 0) break;
		}
        ?>
        var vis = new google.visualization.ColumnChart(document.getElementById('statsusers'));
        var options = {
        	width:document.documentElement.clientWidth/2,
          height: 300,
          legend:'none'
        };
        vis.draw(dataTable, options);
	}
	google.load("visualization", "1", {packages:["corechart"]});
	google.setOnLoadCallback(statsusers);
</script>
<br  style="font-size:1px;" />
<div id="dash_users">
	<div id="statsusers" style="text-align:center;margin-bottom:20px"></div>
	<table class="adminlist" cellpadding="1">
		<thead>
			<tr>
				<th class="title">
					<?php echo JText::_('JOOMEXT_NAME'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('JOOMEXT_EMAIL'); ?>
				</th>
				<th class="title titledate">
					<?php echo JText::_( 'CREATED_DATE' );?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_( 'RECEIVE_HTML' );?>
				</th>
				<?php if($this->config->get('require_confirmation',1)) {?>
				<th class="title titletoggle">
					<?php echo JText::_( 'CONFIRMED' );?>
				</th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php
				$k = 0;
				foreach($this->users as $oneUser){
					$row =& $oneUser;
					$confirmedid = 'confirmed_'.$row->subid;
					$htmlid = 'html_'.$row->subid;
			?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $row->name; ?>
					</td>
					<td>
						<a href="<?php echo acymailing::completeLink('subscriber&task=edit&subid='.$row->subid)?>"><?php echo $row->email; ?></a>
					</td>
					<td align="center" style="text-align:center">
						<?php echo acymailing::getDate($row->created); ?>
					</td>
					<td align="center" style="text-align:center">
						<span id="<?php echo $htmlid ?>" class="loading"><?php echo $this->toggleClass->toggle($htmlid,$row->html,'subscriber') ?></span>
					</td>
					<?php if($this->config->get('require_confirmation',1)) {?>
					<td align="center" style="text-align:center">
						<span id="<?php echo $confirmedid ?>" class="loading"><?php echo $this->toggleClass->toggle($confirmedid,$row->confirmed,'subscriber') ?></span>
					</td>
					<?php } ?>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
</div>