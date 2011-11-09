<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<script language="javascript" type="text/javascript">
<!--
	var selectedContents = new Array();
	var allElements = <?php echo count($this->rows);?>;
	<?php
		foreach($this->rows as $oneRow){
			if(!empty($oneRow->selected)){
				echo "selectedContents['".$oneRow->namekey."'] = 'content';";
			}
		}
	?>
	function applyContent(contentid,rowClass){
		if(selectedContents[contentid]){
			window.document.getElementById('content'+contentid).className = rowClass;
			delete selectedContents[contentid];
		}else{
			window.document.getElementById('content'+contentid).className = 'selectedrow';
			selectedContents[contentid] = 'content';
		}
	}
	function insertTag(){
		var tag = '';
		for(var i in selectedContents){
			if(selectedContents[i] == 'content'){
				allElements--;
				if(tag != '') tag += ',';
				tag = tag + i;
			}
		}
		window.top.document.getElementById('<?php echo $this->controlName; ?>customfields').value = tag;
		window.top.document.getElementById('link<?php echo $this->controlName; ?>customfields').href = 'index.php?option=com_acymailing&tmpl=component&ctrl=chooselist&task=customfields&control=<?php echo $this->controlName; ?>&values='+tag;
		try{ window.top.document.getElementById('sbox-window').close(); }catch(err){ window.top.SqueezeBox.close(); }
	}
//-->
</script>
<style type="text/css">
	table.adminlist tr.selectedrow td{
		background-color:#FDE2BA;
	}
</style>
<form action="index.php?option=<?php echo ACYMAILING_COMPONENT ?>" method="post" name="adminForm">
<div style="float:right;margin-bottom : 10px">
	<button id='insertButton' onclick="insertTag(); return false;"><?php echo JText::_('ACY_APPLY'); ?></button>
</div>
<div style="clear:both"/>
	<table class="adminlist" cellpadding="1">
		<thead>
			<tr>
				<th class="title">
					<?php echo JText::_('FIELD_COLUMN'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('FIELD_LABEL'); ?>
				</th>
				<th class="title titleid">
					<?php echo JText::_('ACY_ID'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$k = 0;
				foreach($this->rows as $row){
			?>
				<tr class="<?php echo empty($row->selected) ? "row$k" : 'selectedrow'; ?>" id="content<?php echo $row->namekey; ?>" onclick="applyContent('<?php echo $row->namekey."','row$k'"?>);" style="cursor:pointer;">
					<td>
					<?php echo $row->namekey; ?>
					</td>
					<td>
					<?php echo $this->fieldsClass->trans($row->fieldname); ?>
					</td>
					<td align="center">
						<?php echo $row->fieldid; ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
</form>
