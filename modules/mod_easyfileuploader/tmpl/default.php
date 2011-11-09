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
// Devuelve el texto formateado en html para los checkbox. 
// Si encuentra una cadena de la forma "[ID]texto..." devuelve 
// <a href=\"?option=com_content&view=article&id=119\">".$params->get('efu_checkbox1')."</a>&nbsp;</label><td><tr>
// Donde 119 era el ID entre corchetes.
// Sirve para los checkboxes de los formularios con condiciones legales 
// en un enlace en el texto del checkbox.
function get_label($txt){
    $patternid = "/[[0-9]+]/";
    preg_match($patternid, $txt, $matches);
    // print_r($matches);
    if ($matches){
        $txt_sin_id = str_replace($matches[0], "", $txt);
        $article_id = str_replace(array("[", "]"), "", $matches[0]);
        $res = '<a href=?option=com_content&view=article&id='.$article_id.'>'.$txt_sin_id.'</a>&nbsp;</label><td><tr>';
    }else{
        $res = $txt;
    }
    return $res;
};

// Tamaño máximo de fichero que permitiré adjuntar.
// By the way, el tamaño que se configura en el backend NUNCA 
// llega a usarse. :( 
function GET_MAX_FILESIZE(){
    return (6640 * 1024);  // Máximo experimental: 6640 KiB
}

// Joomla guarda nombre y apellidos en el mismo atributo. Los separo.
function split_nombre($nombre_completo){
    // Devuelve un array de dos elementos: nombre y apellidos.
    // Para más información sobre la regla general de cómo divide el nombre 
    // completo, ver las dos siguientes funciones.
    $items = explode(" ", $nombre_completo);
    //print_r($items);
    //print count($items);
    switch (count($items)){
        case 0:
            $nombre = "";
            $apellidos = "";
            break;
        case 1:
            $nombre = $items[0];
            $apellidos = "";
            break;
        case 2:
            $nombre = $items[0];
            $apellidos = $items[1];
            break;
        case 3:
            $nombre = $items[0];
            $apellidos = $items[1]." ".$items[2];
            break;
        default:
            $nombre = implode(" ", array_slice($items, 0, count($items)-2));
            //$apellidos = $items[-2]." ".$items[-1];
            $apellidos = implode(" ", array_slice($items, count($items)-2)); 
    }
    //print "nombre:".$nombre."<br>";
    //print "apellidos:".$apellidos."<br>";
    $res = array($nombre, $apellidos);
    return $res;
}

function get_nombre($nombre_completo){
    /* 
     * Divide nombre_completo en nombre y apellidos según los espacios que 
     * contenga. Devuelve el nombre.
     * Ejemplos:
     * "" -> ""
     * "John" -> "John"
     * "John Rambo" -> "John"
     * "Juan Heredia Expósito" -> "Juan"
     * "Juan Miguel Heredia Expósito" -> "Juan Miguel"
     * "Francisco Juan Miguel Heredia Expósito" -> "Francisco Juan Miguel"
     * BUG reconocido:
     * "Juan Miguel Heredia" -> "Juan" (y se quedaría "Miguel Heredia" como 
     * apelldos. But... it's only a dumb computer! What do you expect?)
     */
     $res = split_nombre($nombre_completo);
     return $res[0];
}

function get_apellidos($nombre_completo){
    /* 
     * Divide nombre_completo en nombre y apellidos según los espacios que 
     * contenga. Devuelve los apellidos.
     * Ejemplos:
     * "" -> ""
     * "Juan" -> ""
     * "Juan Heredia" -> "Heredia"
     * "Juan Heredia Expósito" -> "Heredia Expósito"
     * "Juan Miguel Heredia Expósito" -> "Heredia Expósito"
     * "Francisco Juan Miguel Heredia Expósito" -> "Heredia Expósito"
     * BUG reconocido:
     * "Juan Miguel Heredia" -> "Juan" (y se quedaría "Miguel Heredia" como 
     * apelldos. But... it's only a dumb computer! What do you expect?)
     */
     $res = split_nombre($nombre_completo);
     return $res[1];
}

?>
<!-- XXX: EO callao chico -->

<!-- Input form for the File Upload -->

<form enctype="multipart/form-data" action="" method="post" >
<div >
<h3 style="font-size:20px;" >

<?php
// XXX: [20111011] callao_chico. No está adjuntando bien...
function my_own_b64e_with_bitches_and_a_lot_of_smoke($file_in, &$basetext){
    /*********************************************************
     * A partir de un nombre de fichero devuelve su contenido 
     * codificado en base64 y en trozos de 76 bytes separados
     * por retorno de carro (RFC 2045).
     *********************************************************/
    $fh = fopen($file_in, 'rb'); 
    $cache = ''; 
    $eof = false; 
    while (1) { 
        if (!$eof) { 
            if (!feof($fh)) { 
                $row = fgets($fh, 4096); 
            } else { 
                $row = ''; 
                $eof = true; 
            } 
        } 
        if ($cache !== '') 
            $row = $cache.$row; 
        elseif ($eof) 
            break; 
        $b64 = base64_encode($row); 
        $put = ''; 
        if (strlen($b64) < 76) { 
            if ($eof) { 
                $put = $b64."\n"; 
                $cache = ''; 
            } else { 
                $cache = $row; 
            } 
        } elseif (strlen($b64) > 76) { 
            do { 
                $put .= substr($b64, 0, 76)."\n"; 
                $b64 = substr($b64, 76); 
            } while (strlen($b64) > 76); 
            $cache = base64_decode($b64); 
        } else { 
            if (!$eof && $b64{75} == '=') { 
                $cache = $row; 
            } else { 
                $put = $b64."\n"; 
                $cache = ''; 
            } 
        } 
        $basetext .= $put;
    } 
    unset($put, $row, $cache, $b64);
    fclose($fh); 
}

function codificar_adjunto($nameFile, &$basetext){
    //echo "DEBUG 00 <br>";
    /* 
    $file = fopen($nameFile, "rb");
    echo "DEBUG 01 <br>";
    $content = fread($file, filesize($nameFile));
    echo "DEBUG 02 <br>";
    $base64encoded = base64_encode($content);
    echo "DEBUG 03 <br>";
    $encoded_attach = chunk_split($base64encoded, 64, "\n");
    echo "DEBUG 04 <br>";
    */ 
    my_own_b64e_with_bitches_and_a_lot_of_smoke($nameFile, $basetext);
    //echo "DEBUG 01 <br>";
}

function mailattach2($docList, $texto1, $texto2, $texto3, $texto4, $texto5,
                     $titular, $mailto, $emailsender, $emailsendername, 
                     $ztext1, $activefileupload){
    /******************************************************
     * Códigos de error:
     * 0: No error.
     * 1: Error en la transmisión del fichero al servidor.
     * 2: Tipo de fichero no soportado.
     * 3: Error en el envío del correo electrónico.
     * 4: Fichero demasiado grande.
     ******************************************************/
    $emailDestination=$mailto;
    // $emailDestination="frbogado@novaweb.es";    // XXX: TMP: TODO: BORRAR
    $emailOrigin=$emailsender;
    $nameEmailOrigin = $emailsendername;
    $subject = $titular;

    $message = $texto1.": ".$_POST['answer1'];
    $message = $message."\n".$texto2.": ".$_POST['answer2'];
    $message = $message."\n".$texto3.": ".$_POST['answer3'];
    $message = $message."\n".$texto4.": ".$_POST['answer4'];
    if ($_POST['answer5']) 
        $message = $message."\n".$texto5.": ".$_POST['answer5'];
    if ($_POST['ztext1']) 
        $message = $message."\n".$ztext1.": ".$_POST['ztext1'];
    $user =& JFactory::getUser();
    if ((! $user->guest) && $user->email){
        $message .= "\nCorreo electrónico del usuario registrado: ";
        $message .= $user->email."\n";
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
    // XXX: callao_chico. Voy a higienizar un poco esto...
    $res=0;     // «Un tipo se tira por la ventana desde un piso 
                // cincuenta. Mientra va cayendo piensa: DE 
                // MOMENTO TODO VA BIEN, de momento todo va 
                // bien... Lo importante no es cuánto tiempo 
                // estás cayendo, sino cómo aterrizas.»
    if ($activefileupload){
        // print "<br>MimeType detectado:".$_FILES['file1']['type']."<br><br>";
        // Comprobación de MimeType
        if(!eregi($_FILES['file1']['type'], $docList)) 
            $res = 2;
        // Comprobación de tamaño
        else if (filesize($_FILES['file1']['tmp_name'])>GET_MAX_FILESIZE())
            $res = 4;
        else{
            /* De momento todo va bien... */
            // print var_dump($_FILES)." <---- FILES <br>";
            // print var_dump($_POST)." <---- POST <br>";
            // print "------>".strlen($body)."<br>";
            if (is_uploaded_file($_FILES['file1']['tmp_name']) 
                && eregi($_FILES['file1']['type'], $docList)){
                // print "DEBUG0 <br>";
                $body .= "\n\n--Message-Boundary\n";
                $body .= "Content-type: Binary; name=\"";
                $body .= $_FILES['file1']['name'] ."\"\n";
                $body .= "Content-Transfer-Encoding: BASE64\n";
                $body .= "Content-disposition: attachment; ";
                $body .= "filename=\"".$_FILES['file1']['name']."\"\n\n";
                // print "DEBUG1 <br>";
                // echo strlen($body);
                $encoded_attach 
                    = codificar_adjunto($_FILES['file1']['tmp_name'], 
                                        $body);
                // print "DEBUG2 <br>";
                // echo strlen($body);
                // echo memory_get_usage() . "\n";
                // print "DEBUG3 <br>";
                $body .='\n';
                // print "DEBUG4 <br>";
                $body .= "--Message-Boundary--\n";
                // print "DEBUG5 <br>";
                // print ">>>>>>>".strlen($body)."<br>";
            }else
                // TODO: Comprobar que el valor máximo configurado en el 
                // backend no es mayor que upload_max_filesize y 
                // post_max_size en php.ini. Supongo que esos valores se 
                // podrán rescatar de alguna manera desde una variable 
                // global de entorno de PHP o algo. Mostrar también un 
                // mensaje de error al usuario al recoger el valor de 
                // retorno al salir de la función.
                $res = 1;
            // print var_dump($res)." <==== res<br>";
        }
    }
    if ($res == 0)  // De momento todo va bien.
        if (!mail($emailDestination, $subject, $body, $headers))
            $res = 3;
    // print var_dump($res);
    return $res;    // Las funciones deben tener un solo punto de retorno.
                    // Así es más fácil de depurar.
    // XXX: End Of callao_chico
}
?>
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

