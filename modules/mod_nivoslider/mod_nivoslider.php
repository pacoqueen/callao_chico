<?php
/**
 * @package     Nivo-Szaki Slider
 * @link        http://szathmari.hu
 * @version     1.0
 * @copyright   Copyright (C) 2011 szathmari.hu
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die( 'Restricted access' );
require_once (dirname(__FILE__).DS.'helper.php');

$imagesDir = rtrim($params->get('imagesDir', 'images/banners/'), '/\\');
if ($params->get('subDir', 0)) 
{
	$folders = array();
	modNivoSliderHelper::getSubdirs($imagesDir, $folders);
	$imagesDir = $folders;
}
else
	$imagesDir = array($imagesDir);
$images = modNivoSliderHelper :: getImages($params, $imagesDir);

require(JModuleHelper::getLayoutPath('mod_nivoslider'));
?>