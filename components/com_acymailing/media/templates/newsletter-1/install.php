<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
$name = 'White Shadow Red';
$description = '<img src="media/com_acymailing/templates/newsletter-1/newsletter-1.png" />';
$body = JFile::read(dirname(__FILE__).DS.'index.html');
$styles['acymailing_title'] = 'color:#8a8a8a;font-weight:normal;font-size:14px;margin:0;border-bottom:5px solid #d39f9f;';
$styles['acymailing_unsub'] = 'font-weight:bold;color:#000000;';
$styles['acymailing_readmore'] = 'color:#d39f9f;';
$styles['acymailing_online'] = 'font-weight:bold;color:#000000;';
$styles['tag_h1'] = 'margin-bottom:0;margin-top:0;font-family:Tahoma, Geneva, Kalimati, sans-serif;font-size:26px;color:#d47e7e;vertical-align:top;';
$styles['tag_h2'] = 'color:#8a8a8a !important;font-weight:normal;font-size:14px;margin:0;border-bottom:5px solid #d39f9f;';
$styles['tag_h6'] = 'background-color:#d39f9f;margin:0;';
$styles['tag_h3'] = 'color:#8a8a8a !important;font-weight:normal;font-size:100%;margin:0;';
$styles['color_bg'] = '#e2e8df';