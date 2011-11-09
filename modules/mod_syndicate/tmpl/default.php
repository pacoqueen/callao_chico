<?php
/**
 * @version		$Id: default.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Site
 * @subpackage	mod_syndicate
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 
 
 * IMPORTANTE - IMPORTANTE - IMPORTANTE - IMPORTANTE -  
 * PACKMANIATICO he eliminado el <span><?php echo $text ?></span> después de 'feed-image', NULL, true);?>'
 * IMPORTANTE - IMPORTANTE - IMPORTANTE - IMPORTANTE - IMPORTANTE - 
 */



// no direct access
defined('_JEXEC') or die;
?>
<!-- XXX callao chico
<a href="<?php echo $link ?>" class="syndicate-module<?php // echo $moduleclass_sfx ?>"> 
-->
<a href="<?php echo $link ?>" class="syndicate-module" title="RSS" <?php echo $moduleclass_sfx ?>"> 
	
	<?php echo JHTML::_('image','system/livemarks.png', 'feed-image', NULL, true); ?> </a>
	

