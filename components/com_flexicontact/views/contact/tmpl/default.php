$file = JRequest::getVar( 'fichero', null, 'files', 'array' );
 
jimport('joomla.filesystem.file');
 
        if(!is_array($file)){
        	$mensaje .= "NoSeHaSubido";
         }
         if($file['error'] || $file['size'] < 1 ){
			$mensaje .= " NoExisteFichero";
        }
         if( $file['size'] > 100  ){
         	$mensaje .=" FicheroDemasiadoGrande";
             }
 
        if(!JFile::upload($file['tmp_name'],"/directorioLocal".DS. $file['name'])){ 
 
			$mensaje .= " ErrorAlSubirElFichero";
        }