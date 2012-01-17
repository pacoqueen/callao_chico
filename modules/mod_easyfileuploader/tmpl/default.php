<?php
/**
* @version      0.8 (J16)
* @author       Michael A. Gilkes (jaido7@yahoo.com)
* @copyright    Michael Albert Gilkes
* @license      GNU/GPLv2
* @modified_by  Francisco José Rodríguez Bogado (frbogado@novaweb.es)
* @modified_by  Juan López Valverde (jlopez@novaweb.es)
*/
//no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

//-----------------------------------------
?>

<!-- XXX: callao chico -->
<?php
include_once "aux_functions.php";
?>
<!-- XXX: EO callao chico -->

<!-- Input form for the File Upload -->

<form enctype="multipart/form-data" action="" method="post" >
<div >
<h3 style="font-size:20px;" >
</h3>
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
        if ($error_form=="ok"){
            $retour = mailattach2($params->get('efu_filetypes'), 
                                  $params->get('efu_texto1'), 
                                  $params->get('efu_texto2'), 
                                  $params->get('efu_texto3'), 
                                  $params->get('efu_texto4'), 
                                  $params->get('efu_texto5'), 
                                  $params->get('efu_titulo'), 
                                  $params->get('efu_mailto'), 
                                  $params->get('efu_mailnoreply'), 
                                  $params->get('efu_mailnoreplyname'), 
                                  $params->get('efu_textarea1'), 
                                  $params->get('efu_fileupload1_enabled'));
            // XXX: callao_chico. Code cleanup...
            // print "juan:".$retour;exit();
            if (!$retour) {
                echo $params->get('efu_mailok');
            }else{
                echo $params->get('efu_mailnok');
                switch ($retour){
                    case 1: print "<br>Error al subir el fichero al servidor.<br>"; 
                            break;
                    case 2: print "<br>Error en el formato del fichero:<br>".$params->get('efu_filetypes')."<br>";
                            break;
                    case 3: print "<br>Error en el envío del correo electrónico.<br>";
                            break;
                    case 4: print "<br>El fichero es demasiado grande. El máximo permitido es " . round((GET_MAX_FILESIZE() / 1024 / 1024), 2) . " MiB.<br>";
                            break;
                    default: print "<br>Código de error: ".$retour."<br>";
                }
            // XXX: End Of callao_chico
            }
        }
    }
    ?>
</div>

<?php
if ($retour)
{?>

<div class="login-fields"><fieldset>
<?php 

// XXX: callao_chico. Intento rescatar los valores por defecto de la BD.
$user =& JFactory::getUser();
$valores = array('usuario' => $user->username, 
                 'nombre de usuario' => $user->username, 
                 'id' => $user->id, 
                 'email' => $user->email,
                 'correo' => $user->email,
                 'correo electrónico' => $user->email,
                 'apellidos' => get_apellidos($user->name), 
                 'nombre' => get_nombre($user->name)
                );
if ($user->guest){   // Usuario no registrado.
    ;
}else{
    // FIXME: ¿Es posible que los valores del POST escritos por el usuario 
    //        se machaquen aquí? Debería comprobar primero que no tienen 
    //        nada o algo así...
    //        UPDATE [20111011]: No solo es posible, sino que es justo lo que 
    //                           está pasando.
    for ($i=1; $i<=5; $i++){
        $campo = 'efu_texto'.$i;
        $input = 'answer'.$i;
        $label_ventana = strtolower($params->get($campo));
        foreach ($valores as $label => $valor_usuario){
            if ($label_ventana == $label)
                $_POST[$input] = $valor_usuario;
        }
    }
}
// print "=======================>".var_dump($_POST);
// print_r($user);
// XXX EO callao chico.

if($params->get('efu_texto1_enabled')) 
{
    print "<label id=\"username-lbl\"  class=\"required\" >";
    if ($params->get('efu_texto1_enabled')=="2") print "<span style=\"color:#CC0000;float:left; margin-right:3px;\">&nbsp;*</span>";
     print $params->get('efu_texto1')."</label><input  class=\"validate-username required\" size=\"25\" type=\"text\" name=\"answer1\" value=\"".$_POST['answer1']."\" /><br><br>";
}
if($params->get('efu_texto2_enabled')) 
{
    print "<label id=\"username-lbl\"  class=\"required\" >";
    if ($params->get('efu_texto2_enabled')=="2") print "<span style=\"color:#CC0000;float:left; margin-right:3px;\">&nbsp;*</span>";
        print $params->get('efu_texto2')."</label><input  class=\"validate-username required\" size=\"25\" type=\"text\" name=\"answer2\" value=\"".$_POST['answer2']."\" /><br><br>";
}


if($params->get('efu_texto3_enabled')) 
{
    print "<label id=\"username-lbl\"  class=\"required\" >";
    if ($params->get('efu_texto3_enabled')=="2") print "<span style=\"color:#CC0000;float:left; margin-right:3px;\">&nbsp;*</span>";
    print $params->get('efu_texto3')."</label><input  class=\"validate-username required\" size=\"25\" type=\"text\" name=\"answer3\" value=\"".$_POST['answer3']."\" /><br><br>";
}


if($params->get('efu_texto4_enabled')) 
{
    print "<label id=\"username-lbl\"  class=\"required\" >";
    if ($params->get('efu_texto4_enabled')=="2") print "<span style=\"color:#CC0000;float:left; margin-right:3px;\">&nbsp;*</span>";
    print $params->get('efu_texto4')."</label><input class=\"validate-username required\" size=\"25\" type=\"text\" name=\"answer4\" value=\"".$_POST['answer4']."\" /><br><br>";
}


if($params->get('efu_texto5_enabled')) 
{
    print "<label id=\"username-lbl\"  class=\"required\" >";
    if ($params->get('efu_texto5_enabled')=="2") print "<span style=\"color:#CC0000;float:right; margin-right:3px;\">&nbsp;*</span>";
    print $params->get('efu_texto5')."</label><input  class=\"validate-username required\" size=\"25\" type=\"text\" name=\"answer5\" value=\"".$_POST['answer5']."\" /><br><br>";
}

if($params->get('efu_fileupload1_enabled')) 
{
    print "<label id=\"username-lbl\"  class=\"required\" >";
    if ($params->get('efu_fileupload1_enabled')=="2") print "<span style=\"color:#CC0000;float:left; margin-right:3px;\">&nbsp;*</span>";
    print $params->get('efu_labelfileupload1')."</label><input  style=\"fileimputs\" size=\"25\" type=\"file\" name=\"file1\" value=\"\" size=\"20\"/><br><br>";
}

if($params->get('efu_textarea1_enabled')) 
{
    print "<label id=\"username-lbl\"  class=\"required\" >";
    if ($params->get('efu_textarea1_enabled')=="2") print "<span style=\"color:#CC0000;float:left; margin-right:3px;\">&nbsp;*</span>";
    print $params->get('efu_textarea1')."</label><textarea class=\"inputbox\"   name=\"ztext1\" rows=\"10\" cols=\"50\"/>".$_POST['ztext1']."</textarea><br><br>";
}

if($params->get('efu_checkbox1_enabled')) 
{
    print "<label  class=\"required\" style=\"width:auto;\">";
    if ($params->get('efu_checkbox1_enabled')=="2") print " <span style=\"color:#CC0000;float:left; margin-right:3px;\">&nbsp;*</span>";
    // XXX: callao_chico
    // if (empty($_POST['zcheckbox1'])){print $params->get('efu_checkbox1')."    &nbsp;<input type=\"checkbox\" name=\"zcheckbox1\" value=\"1\" rows=\"10\" cols=\"40\" /> ";}else print $params->get('efu_checkbox1')."<input type=\"checkbox\" name=\"zcheckbox1\" value=\"1\" rows=\"10\" cols=\"40\" checked/>";
    if (empty($_POST['zcheckbox1'])){
            print get_label($params->get('efu_checkbox1'))."    &nbsp;<input type=\"checkbox\" name=\"zcheckbox1\" value=\"1\" rows=\"10\" cols=\"40\" /> ";
        }else 
            print get_label($params->get('efu_checkbox1'))."<input type=\"checkbox\" name=\"zcheckbox1\" value=\"1\" rows=\"10\" cols=\"40\" checked/>";
   //if (empty($_POST['zcheckbox1'])){print "<a href=\"?option=com_content&view=article&id=119\">".$params->get('efu_checkbox1')."</a>&nbsp;</label><td><tr><input type=\"checkbox\" name=\"zcheckbox1\" value=\"1\" rows=\"10\" cols=\"40\" /></tr></td> ";}else print $params->get('efu_checkbox1')."<td><tr><input type=\"checkbox\" name=\"zcheckbox1\" value=\"1\" rows=\"10\" cols=\"40\" checked/></tr></td>";
    
    //hay que meter este código              "<a href=\"?option=com_content&view=article&id\">____                                </a>" dentro del 1º print de $params->get ('efu_checkbox1')."////packmaniatico
    // XXX EO callao chico
}
?>
</div>
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
</object><br/>
<?php
}
?>

<?php
}?>

<?php 
if ($params->get('efu_replace') == "0"): /* 0 means Yes. 1 means No. */ 
?>
    <div><?php echo $params->get('efu_question'); ?></div>
    <input type="radio" name="answer" value="0" /><?php echo $params->get('efu_yes'); ?><br />
    <input type="radio" name="answer" value="1" checked /><?php echo $params->get('efu_no'); ?><br />
    <br />
<?php endif; ?>
<?php
if ($retour){
    if($params->get('efu_submitbutton_enabled')=="1"){
?>
    <div class="logout-button"><input class="button" type="submit" name="submit" value=<?php echo '"'.$params->get('efu_button').'"'; ?> />
    </div>
<?php
    }
}
?>
</form>
<!-- Display the Results of the file upload if uploading was attempted -->

