<?php
/**
 * @package     Nivo-Szaki Slider
 * @link        http://szathmari.hu
 * @version     1.0
 * @copyright   Copyright (C) 2011 szathmari.hu
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die( 'Restricted access' );
if (!$images) 
{
	echo JText::_('Images not found');
} 
else
	modNivoSliderHelper::render($params, $folders, $images);
?>
