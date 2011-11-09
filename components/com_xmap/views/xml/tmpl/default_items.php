<?php
/**
 * @version             $Id: default_items.php 17 2011-01-23 00:04:03Z guille $
 * @copyright			Copyright (C) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @license             GNU General Public License version 2 or later; see LICENSE.txt
 * @author              Guillermo Vargas (guille@vargas.co.cr)
 */

// no direct access
defined('_JEXEC') or die;

// Create shortcut to parameters.
$params = $this->state->get('params');


// Use the class defined in default_class.php to print the sitemap
$this->displayer->printSitemap();