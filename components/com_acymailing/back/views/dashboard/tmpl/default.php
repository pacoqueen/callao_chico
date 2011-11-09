<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<style type="text/css">
#acymailingcpanel div.icon {
	border:1px solid #F0F0F0;
	color:#666666;
	display:block;
	float:left;
	text-decoration:none;
	vertical-align:middle;
	width:100%;
	float:left;
	margin-bottom:5px;
	margin-right:5px;
	text-align:center;
	width: 100%;
}
#acymailingcpanel ul{
	padding-left:10px;
}
#acymailingcpanel div.icon:hover {
	-moz-background-clip:border;
	-moz-background-inline-policy:continuous;
	-moz-background-origin:padding;
	background:#F9F9F9 none repeat scroll 0 0;
	border-color:#EEEEEE #CCCCCC #CCCCCC #EEEEEE;
	border-style:solid;
	border-width:1px;
	color:#0B55C4;
	cursor:pointer;
}
#acymailingcpanel span {
	display:block;
	text-align:center;
}
#acymailingcpanel img {
	margin:0 auto;
	padding:10px 0;
}
</style>
<div id="acy_content" >
  <div id="iframedoc"></div>
<table class="adminform" width="100%">
	<tr>
		<td width="45%" valign="top">
			<div id="acymailingcpanel">
				<?php
					foreach($this->buttons as $oneButton){
						echo $oneButton;
					}
					?>
			</div>
		</td>
		<td valign="top">
			<?php
			if(acymailing::isAllowed($this->config->get('acl_subscriber_manage','all')) || acymailing::isAllowed($this->config->get('acl_statistics_manage','all'))){
				echo $this->tabs->startPane( 'dash_tab');
				if(acymailing::isAllowed($this->config->get('acl_subscriber_manage','all'))){
					echo $this->tabs->startPanel( JText::_( 'USERS' ), 'dash_users');
					include(dirname(__FILE__).DS.'users.php');
					echo $this->tabs->endPanel();
				}
				if(acymailing::isAllowed($this->config->get('acl_statistics_manage','all'))){
					echo $this->tabs->startPanel( JText::_( 'STATISTICS' ), 'dash_stats');
					include(dirname(__FILE__).DS.'stats.php');
					echo $this->tabs->endPanel();
				}
				echo $this->tabs->endPane();
			}
			?>
		</td>
	</tr>
</table>
</div>