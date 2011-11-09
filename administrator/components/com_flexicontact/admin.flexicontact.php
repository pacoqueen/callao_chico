<?php 
/********************************************************************
Copyright 2009-2011 Chris Gaebler
Version :	4.xx
Date    :	18 March 2011
Description:  A flexible contact component with configurable fields
Please see the pdf documentation at http://extensions.lesarbresdesign.info
*********************************************************************
This file is part of FlexiContact
FlexiContact is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
*********************************************************************/
defined('_JEXEC') or die('Restricted Access'); 

define("LA_COMPONENT_VERSION", "4.00");
define("LA_COMPONENT", "com_flexicontact");
define("LA_COMPONENT_NAME", "FlexiContact");
define("LA_COMPONENT_LINK", "index.php?option=".LA_COMPONENT);

if (file_exists(JPATH_ROOT.DS.'LA.php'))
	require_once JPATH_ROOT.DS.'LA.php';

define ("LOG_FILENAME", "flexicontact_log.txt");
define ("FILEPATH_LOG", JPATH_COMPONENT_SITE.DS.LOG_FILENAME);
define ("FILEPATH_IMAGES", JPATH_COMPONENT_SITE.DS.'images'.DS);

require_once(JApplicationHelper::getPath('admin_html'));

$task = JRequest::getCmd('task');
$app = &JFactory::getApplication();

switch ($task)
	{
	case 'show_log':
		flexicontact_html::showLog();
		break;
		
	case 'delete_log':
		@unlink(FILEPATH_LOG);
		$app->redirect(LA_COMPONENT_LINK);
		break;
		
	case 'delete_file':
		$file_name = JRequest::getVar('file_name');
		@unlink(FILEPATH_IMAGES.DS.$file_name);
		$task = 'manage_images';
		$app->redirect(LA_COMPONENT_LINK."&task=images");
		break;
		
	case 'images':
		flexicontact_html::manageImages();
		break;
		
	case 'help':
	default:
		flexicontact_html::showHelpScreen();
	}

?>
