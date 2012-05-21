<?php
/** 
 * @author Francisco José Rodríguez Bogado
 */
defined( '_JEXEC' ) or die ( 'Restricted access' );

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
        if (strpos($docList, '*') === false) && (!eregi($_FILES['file1']['type'], $docList)))
            $res = 2;
        // Comprobación de tamaño
        else if (filesize($_FILES['file1']['tmp_name'])>GET_MAX_FILESIZE())
            $res = 4;
        else{
            /* De momento todo va bien... */
            // print var_dump($_FILES)." <---- FILES <br>";
            // print var_dump($_POST)." <---- POST <br>";
            // print "------>".strlen($body)."<br>";
            if (is_uploaded_file($_FILES['file1']['tmp_name'])){ 
                // && eregi($_FILES['file1']['type'], $docList)){
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
