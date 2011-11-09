<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
$name = 'Clean White Pink';
$description = '<img src="media/com_acymailing/templates/newsletter-2/newsletter-2.png" />';
$body = JFile::read(dirname(__FILE__).DS.'index.html');
$styles['acymailing_title'] = 'color:#8a8a8a;text-align:right;border-bottom:6px solid #d39fc9;';
$styles['tag_h1'] = 'margin-bottom:0;margin-top:0;font-family:Tahoma, Geneva, Kalimati, sans-serif;font-size:26px;color:#d47e7e;vertical-align:top;text-align:center';
$styles['color_bg'] = '#ffffff';
$styles['tag_h2'] = 'color:#8a8a8a !important;text-align:right;border-bottom:6px solid #d39fc9;';
$styles['tag_h3'] = 'color:#8a8a8a !important;text-align:right;font-weight:normal;font-size:100%;margin:0;';
$styles['tag_h6'] = 'background-color:#d39fc9;margin:0;';