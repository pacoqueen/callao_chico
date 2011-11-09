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
<?php


function mailattach2($docList,$texto1,$texto2,$texto3,$texto4,$texto5,$titular,$mailto,$emailsender,$emailsendername,$ztext1,$activefileupload)
{
//print $params->get('efu_texto1');
//$emailDestination = "jlopez@gea21.es";
//$emailOrigin="jlopez@gea21.es";
$emailDestination=$mailto;
$emailOrigin=$emailsender;
$nameEmailOrigin = $emailsendername;
$subject=$titular;

$message=$texto1.": ".$_POST['answer1'];
$message=$message."\n".$texto2.": ".$_POST['answer2'];
$message=$message."\n".$texto3.": ".$_POST['answer3'];
$message=$message."\n".$texto4.": ".$_POST['answer4'];
if ($_POST['answer5']) $message=$message."\n".$texto5.": ".$_POST['answer5'];
if ($_POST['ztext1']) $message=$message."\n".$ztext1.": ".$_POST['ztext1'];

//$message=$message.$_POST['answer1'];

if (is_uploaded_file($_FILES['file1']['tmp_name']) && eregi($_FILES['file1']['type'],$docList)){
$nameFile = $_FILES['file1']['tmp_name'];
$file = fopen($nameFile, "r");
$content = fread($file, filesize($nameFile));
$encoded_attach = chunk_split(base64_encode($content));
fclose($file);
}
$headers = "From: ". $nameEmailOrigin . " <". $emailOrigin .">\n";
$headers .= "Reply-To: $emailDestination\n";
$headers .= "MIME-version: 1.0\n";
$headers .= "Content-type: multipart/mixed; ";
$headers .= "boundary=\"Message-Boundary\"\n";
$headers .= "Content-transfer-encoding: 7BIT\n";
$headers .= "X-attachments: ". $_FILES['file1']['name'];
$body_top = "--Message-Boundary\n";
$body_top .= "Content-type: text/plain; charset=utf-8\n";
$body_top .= "Content-transfer-encoding: 7BIT\n";
$body_top .= "Content-description: Mail message body\n\n";
$body = $body_top.$message;
if ($activefileupload)
{
if (is_uploaded_file($_FILES['file1']['tmp_name']) && eregi($_FILES['file1']['type'],$docList)){
$body .= "\n\n--Message-Boundary\n";
$body .= "Content-type: Binary; name=\"". $_FILES['file1']['name'] ."\"\n";
$body .= "Content-Transfer-Encoding: BASE64\n";
$body .= "Content-disposition: attachment; filename=\"". $_FILES['file1']['name'] ."\"\n\n";
$body .= "$encoded_attach\n";
$body .= "--Message-Boundary--\n";
//print $body;
}
}

if ($activefileupload) {if(!eregi($_FILES['file1']['type'],$docList)) return 2;}

if(mail($emailDestination,$subject,$body,$headers) )return 0;

return 3;
}
?>
<?php //echo $params->get('efu_titulo'); ?>
</h2>
</div>




<?php 

$retour="ok";
		if ($_POST['submit'])	
		{
			?>
			<div style="display:inline-block; position:relative; margin:1em; padding:1em; width:auto; background:<?php echo $params->get('results_bgcolor'); ?>; border: 1px solid grey;">
			<?php
			
			$error_form="ok";
			if($params->get('efu_texto1_enabled')) 
			{
				if ($params->get('efu_texto1_enabled')=="2")
				{
					if ($_POST['answer1']=="") {print "El campo <strong>".$params->get('efu_texto1')." </strong>es obligatorio<br>";$error_form="nok";}
					
				}
			}
			if($params->get('efu_texto2_enabled')) 
			{
				if ($params->get('efu_texto2_enabled')=="2")
				{
					if ($_POST['answer2']=="") {print "El campo <strong>".$params->get('efu_texto2')." </strong>es obligatorio<br>";$error_form="nok";}
				}
			}
			
			if($params->get('efu_texto3_enabled')) 
			{
				if ($params->get('efu_texto3_enabled')=="2")
				{
					if ($_POST['answer3']=="") {print "El campo <strong>".$params->get('efu_texto3')." </strong>es obligatorio<br>";$error_form="nok";}
				}
			}
			
			if($params->get('efu_texto4_enabled')) 
			{
				if ($params->get('efu_texto4_enabled')=="2")
				{
					if ($_POST['answer4']=="") {print "El campo <strong>".$params->get('efu_texto4')." </strong>es obligatorio<br>";$error_form="nok";}
				}
			}
			
			if($params->get('efu_texto5_enabled')) 
			{
				if ($params->get('efu_texto5_enabled')=="2")
				{
					if ($_POST['answer5']=="") {print "El campo <strong>".$params->get('efu_texto5')." </strong>es obligatorio<br>";$error_form="nok";}
				}
			}
			if($params->get('efu_fileupload1_enabled')) 
			{
				if ($params->get('efu_fileupload1_enabled')=="2")
				{
					if ($_FILES['file1']['name']=="") {print "El campo <strong>".$params->get('efu_labelfileupload1')." </strong>es obligatorio<br>";$error_form="nok";}
				}
			}
			
			if($params->get('efu_textarea1_enabled')) 
			{
				if ($params->get('efu_textarea1_enabled')=="2")
				{
					if ($_POST['ztext1']=="") {print "El campo <strong>".$params->get('efu_textarea1')." </strong>es obligatorio<br>";$error_form="nok";}
				}
			}
			
			if($params->get('efu_checkbox1_enabled')) 
			{
				if ($params->get('efu_checkbox1_enabled')=="2")
				{
					if (empty($_POST['zcheckbox1'])) {print "El campo <strong>".$params->get('efu_checkbox1')." </strong>es obligatorio<br>".$_POST['zcheckbox1'];$error_form="nok";}
				}
			}
			
			
			
		?>
		
		<?php
		if ($error_form=="ok")
		{
		$retour=mailattach2($params->get('efu_filetypes'),$params->get('efu_texto1'),$params->get('efu_texto2'),$params->get('efu_texto3'),$params->get('efu_texto4'),$params->get('efu_texto5'),$params->get('efu_titulo'),$params->get('efu_mailto'),$params->get('efu_mailnoreply'),$params->get('efu_mailnoreplyname'),$params->get('efu_textarea1'),$params->get('efu_fileupload1_enabled'));
		if (!$retour) 
		{
		echo $params->get('efu_mailok');
		}else 
			{
			echo $params->get('efu_mailnok');
			if ($retour=="2")
			{
			print "<br>Error en el formato del fichero:<br>".$params->get('efu_filetypes');
			}
			}

		}
		}
	?>




</div>

<br>

<?php
if ($retour)
{?>


<table style="width:500px;border-style:hidden">
<?php 

if($params->get('efu_texto1_enabled')) 
{
print "<tr><td>";
if ($params->get('efu_texto1_enabled')=="2") print "*";
print $params->get('efu_texto1')."</td><td><input type=\"text\" name=\"answer1\" value=\"".$_POST['answer1']."\" /></td></tr>";
}
if($params->get('efu_texto2_enabled')) 
{
print "<tr><td>";
if ($params->get('efu_texto2_enabled')=="2") print "*";
print $params->get('efu_texto2')."</td><td><input type=\"text\" name=\"answer2\" value=\"".$_POST['answer2']."\" /></td></tr>";
}


if($params->get('efu_texto3_enabled')) 
{
print "<tr><td>";
if ($params->get('efu_texto3_enabled')=="2") print "*";
print $params->get('efu_texto3')."</td><td><input type=\"text\" name=\"answer3\" value=\"".$_POST['answer3']."\" /></td></tr>";
}


if($params->get('efu_texto4_enabled')) 
{
print "<tr><td>";
if ($params->get('efu_texto4_enabled')=="2") print "*";
print $params->get('efu_texto4')."</td><td><input type=\"text\" name=\"answer4\" value=\"".$_POST['answer4']."\" /></td></tr>";
}


if($params->get('efu_texto5_enabled')) 
{
print "<tr><td>";
if ($params->get('efu_texto5_enabled')=="2") print "*";
print $params->get('efu_texto5')."</td><td><input type=\"text\" name=\"answer5\" value=\"".$_POST['answer5']."\" /></td></tr>";
}

if($params->get('efu_fileupload1_enabled')) 
{
print "<tr><td>";
if ($params->get('efu_fileupload1_enabled')=="2") print "*";
print $params->get('efu_labelfileupload1')."</td><td><input type=\"file\" name=\"file1\" value=\"\" size=\"20\"/></td></tr>";
}

if($params->get('efu_textarea1_enabled')) 
{
print "<tr><td>";
if ($params->get('efu_textarea1_enabled')=="2") print "*";
print $params->get('efu_textarea1')."</td><td><textarea name=\"ztext1\" rows=\"10\" cols=\"40\"/>".$_POST['ztext1']."</TEXTAREA> </td></tr>";
}

if($params->get('efu_checkbox1_enabled')) 
{
print "<tr><td>";
if ($params->get('efu_checkbox1_enabled')=="2") print "*";
if (empty($_POST['zcheckbox1'])){print $params->get('efu_checkbox1')."</td><td><input type=\"checkbox\" name=\"zcheckbox1\" value=\"1\" rows=\"10\" cols=\"40\" /> </td></tr>";}else print $params->get('efu_checkbox1')."</td><td><input type=\"checkbox\" name=\"zcheckbox1\" value=\"1\" rows=\"10\" cols=\"40\" checked/> </td></tr>";
}
?>
</table>
<?php
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

<?php
}?>
	<?php if ($params->get('efu_replace') == "0"): /* 0 means Yes. 1 means No. */ ?>
	<div><?php echo $params->get('efu_question'); ?></div>
	<input type="radio" name="answer" value="0" /><?php echo $params->get('efu_yes'); ?><br />
	<input type="radio" name="answer" value="1" checked /><?php echo $params->get('efu_no'); ?><br />
	<br />
	<?php endif; ?>
	<?php
if ($retour)
{?>

	<input type="submit" name="submit" value=<?php echo '"'.$params->get('efu_button').'"'; ?> />
	<?php
	}
	?>
</form>
<!-- Display the Results of the file upload if uploading was attempted -->
	

