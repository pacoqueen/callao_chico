<?php
/**
* @version		0.8 (J16)
* @author		Michael A. Gilkes (jaido7@yahoo.com)
* @copyright	Michael Albert Gilkes
* @license		GNU/GPLv2
*/
//no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

//-----------------------------------------
?>
<!-- Input form for the File Upload -->
<form enctype="multipart/form-data" action="" method="post">
<div >
<h2>
popo
</h2>
</div>

<br>



<table>
<?php
print "cocou";
if($params->get('efu_flash_enabled')) 
{
print "<tr><td>";
if ($params->get('efu_flash_enabled')=="2") print "*";
//print "<td><input type=\"checkbox\" name=\"zcheckbox1\" value=\"1\" rows=\"10\" cols=\"40\" /> </td></tr>";}else print $params->get('efu_checkbox1')."</td><td><input type=\"checkbox\" name=\"zcheckbox1\" value=\"1\" rows=\"10\" cols=\"40\" checked/> </td></tr>";
?>
<object width="550" height="400">
<param name="movie" value="<?php print $params->get('efu_flashfilename');?>">
<embed src="<?php print $params->get('efu_flashfilename');?>" width="550" height="400">
</embed>
</object><br>
<?php
}
?>

	<br />
</table>

<!-- Display the Results of the file upload if uploading was attempted -->
	

