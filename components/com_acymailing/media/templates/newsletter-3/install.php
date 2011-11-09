<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
$name = 'Rounders and corners';
$description = '<img src="media/com_acymailing/templates/newsletter-3/newsletter-3.png" />';
$body = JFile::read(dirname(__FILE__).DS.'index.html');
$styles['acymailing_title'] = 'color:#8a8a8a;border-bottom:6px solid #d3d09f;';
$styles['tag_h1'] = 'margin-bottom:0;margin-top:0;font-family:Tahoma, Geneva, Kalimati, sans-serif;font-size:26px;color:#d47e7e;vertical-align:top;';
$styles['tag_h2'] = 'color:#8a8a8a !important;border-bottom:6px solid #d3d09f;';
$styles['tag_h3'] = 'color:#8a8a8a !important;font-weight:normal;font-size:100%;margin:0;';
$styles['tag_h6'] = 'background-color:#d3d09f;margin:0;';
$styles['color_bg'] = '#dfe6e8';