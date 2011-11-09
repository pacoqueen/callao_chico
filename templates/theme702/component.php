<?php
/**
 * @version                $Id: component.php 20196 2011-01-09 02:40:25Z ian $
 * @package                Joomla.Site
 * @subpackage        tpl_beez2
 * @copyright        Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license                GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
$path = $this->baseurl.'/templates/'.$this->template;
$color = $this->params->get('templatecolor');
?>
<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >
<head>
	<jdoc:include type="head" />
                <link rel="stylesheet" href="<?php echo $path ?>/css/position.css" type="text/css" media="screen,projection" />
                <link rel="stylesheet" href="<?php echo $path ?>/css/layout.css" type="text/css" media="screen,projection" />
                <link rel="stylesheet" href="<?php echo $path ?>/css/print.css" type="text/css" media="Print" />
                <link rel="stylesheet" href="<?php echo $path ?>/css/personal.css" type="text/css" media="screen,projection" />
                 <link rel="stylesheet" href="<?php echo $path ?>/css/general.css" type="text/css" media="screen,projection" />
<?php
	$files = JHtml::_('stylesheet','templates/theme702/css/general.css',null,false,true);
	if ($files):
		if (!is_array($files)):
			$files = array($files);
		endif;
		foreach($files as $file):
?>
			<link rel="stylesheet" href="<?php echo $file;?>" type="text/css" />
<?php
		endforeach;
	endif;
?>

</head>
<body class="contentpane">
	<div id="all">
		<div id="main">
			<jdoc:include type="message" />
			<jdoc:include type="component" />
		</div>
	</div>
</body>
</html>
