<?php
//no direct access
defined('_JEXEC') or die('Direct Access to this location is not allowed.');
 
// include the helper file
require_once(dirname(__FILE__).DS.'helper.php'); 

// get a parameter from the module's configuration

$args['id'] = $params->get('id'); 
       
$item = ModInsertarticle::getArticles($args);

// include the template for display
require(JModuleHelper::getLayoutPath('mod_insertarticle'));
?>
