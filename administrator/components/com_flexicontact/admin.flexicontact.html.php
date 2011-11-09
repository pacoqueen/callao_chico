<?php
/********************************************************************
Copyright 2009-2011 Chris Gaebler
Version :	3.xx
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

class flexicontact_html
{

//-------------------------------------------------------------------------------
// Show the Help and Support screen
//
function showHelpScreen()
{
	flexicontact_html::addSubMenu('help');
	JToolBarHelper::title(JText::_('COM_FLEXICONTACT_TOOLBAR_HELP'), 'article.png');
	JToolBarHelper::back();
	echo '<form action="index.php" method="get" name="adminForm" id="adminForm" >';
	echo '<input type="hidden" name="option" value="com_flexicontact" />';
	echo '<input type="hidden" name="task" value="" />';
	echo '<input type="hidden" name="hidemainmenu" value="0" />';
	echo '</form>';
	$link_doc = "http://extensions.lesarbresdesign.info/en/downloads/category/2-flexicontact";
	$link_images = "http://extensions.lesarbresdesign.info/en/flexicontact/captcha-image-packs";
	$link_version = "http://extensions.lesarbresdesign.info/en/version-history/flexicontact";
	$link_rating = "http://extensions.joomla.org/extensions/contacts-and-feedback/contact-forms/9743";
	$link_chrisguk = "http://extensions.joomla.org/extensions/owner/chrisguk";
	$link_LAextensions = "http://extensions.lesarbresdesign.info/";
	?>
	<p style="color:#0B55C4; font-size:15px"><?php echo LA_COMPONENT_NAME.': '.JText::_('COM_FLEXICONTACT_HELP_TITLE');?></p>
	<p><?php echo JText::_('COM_FLEXICONTACT_VERSION').' '.LA_COMPONENT_VERSION?></p>
	<p><?php echo '<strong>'.JText::_('COM_FLEXICONTACT_HELP_CONFIG').'</strong>';?></p>
	<p><?php echo JText::_('COM_FLEXICONTACT_HELP_DOC').' '.JHTML::link($link_doc, "www.lesarbresdesign.info", 'target="_blank"');?></p>
	<p><?php echo JText::_('COM_FLEXICONTACT_HELP_IMAGES').' '.JHTML::link($link_images, "www.lesarbresdesign.info", 'target="_blank"');?></p>
	<p><?php echo JText::_('COM_FLEXICONTACT_HELP_CHECK').' '.JHTML::link($link_version, 'Les Arbres Design - Flexicontact', 'target="_blank"');?></p>
	<p><?php echo JText::sprintf('COM_FLEXICONTACT_HELP_RATING', LA_COMPONENT_NAME).' '.JHTML::link($link_rating, 'Joomla! Extensions', 'target="_blank"');?></p>
	<p><?php echo JText::sprintf('COM_FLEXICONTACT_HELP_LES_ARBRES', LA_COMPONENT_NAME, JHTML::link($link_chrisguk, 'Joomla! Extensions', 'target="_blank"')).' '.JHTML::link($link_LAextensions, 'Les Arbres Design', 'target="_blank"');?></p>
	<table>
		<tr>
			<td><?php echo JText::sprintf('COM_FLEXICONTACT_HELP_FUND_ONE', LA_COMPONENT_NAME);?><br />
				<?php echo JText::_('COM_FLEXICONTACT_HELP_FUND_TWO');?>
			</td>
			<td>
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
					<input type="hidden" name="cmd" value="_s-xclick" />
					<input type="hidden" name="hosted_button_id" value="11095351" />
					<input type="image" src="<?php echo JText::_('COM_FLEXICONTACT_HELP_DONATE_BUTTON');?>" name="submit" alt="PayPal - The safer, easier way to pay online." />
				</form>
			</td>
		</tr>
	</table>
	<?php	
}

//-------------------------------------------------------------------------------
// Show the first 1000 lines of the log file, followed by a link to download it
//
function showLog()
{
	flexicontact_html::addSubMenu('show_log');
	JToolBarHelper::title(JText::_('COM_FLEXICONTACT_TOOLBAR_LOG'), 'article.png');
	echo '<form action="index.php" method="get" name="adminForm" id="adminForm" >';
	echo '<input type="hidden" name="option" value="com_flexicontact" />';
	echo '<input type="hidden" name="task" value="" />';
	echo '<input type="hidden" name="hidemainmenu" value="0" />';
	echo '</form>';
	if (!file_exists(FILEPATH_LOG)) 
		{ 
		echo "<h3>".JText::_('COM_FLEXICONTACT_NO_LOG_FILE')."</h3>\n"; 
		return;
		}
		
	JToolBarHelper::custom('delete_log', 'cancel.png', 'cancel_f2.png', JText::_('COM_FLEXICONTACT_TOOLBAR_DELETE_LOG'), false);

	$filesize = round((@filesize(FILEPATH_LOG) / 1024), 2).' kb';
	echo "<h4>".JText::_('COM_FLEXICONTACT_LOG_FILE_SIZE')." ".$filesize."</h4>";
		
	$handle = fopen(FILEPATH_LOG, 'r');
	$count = 0;
	while (!feof($handle))
		{ 
		$line = fgets($handle); 
		echo "<div>$line</div>\n"; 
		if ($count ++ > 1000)
			{
			echo "<div>".JText::_('COM_FLEXICONTACT_LOG_TRUNCATED')."</div>\n"; 
			break;
			}
		}
	fclose($handle);
	echo '<a href="'.JURI::root().'components/com_flexicontact/'.LOG_FILENAME.'" target="_blank"> '; 
	echo JText::_('COM_FLEXICONTACT_LOG_OPEN').'</a>';
}

//-------------------------------------------------------------------------------
// Build an associative array of strings from the FRONT END flexicontact language file
// Anyone know how to get Joomla to do this for us?
// It needs to be for the front end of flexicontent in the front end default language
//
function getSiteText(&$language_text)
{
	$params = JComponentHelper::getParams('com_languages');
	$lang = $params->get('site', 'en-GB');
	$path = JLanguage::getLanguagePath(JPATH_SITE);
	$filename = $path.DS.$lang.DS.$lang.'.'.'com_flexicontact.ini';

	$file_lines = @file($filename);
	if ($file_lines === false)
		{
		echo JText::_('COM_FLEXICONTACT_NO_LANGUAGE_FILE')." $lang<br />";
		return false;
		}
	
	foreach ($file_lines as $file_line)
		{
		$parts = explode('=',$file_line);
		if (count($parts) < 2)
			continue;
		$key = substr($parts[0],17);					// strip COM_FLEXICONTACT_ prefix
		$value = trim($parts[1]," \x22\t\n\r\0"); 
		$language_text[$key] = $value;	
		}
	return true;
}

// -------------------------------------------------------------------------------
// Show the images in components/com_flexicontact/images
//
function manageImages()
{
	flexicontact_html::addSubMenu('images');
	JToolBarHelper::title(JText::_('COM_FLEXICONTACT_TOOLBAR_IMAGES'), 'article.png');
	echo '<form action="index.php" method="get" name="adminForm" id="adminForm" >';
	echo '<input type="hidden" name="option" value="com_flexicontact" />';
	echo '<input type="hidden" name="task" value="" />';
	echo '<input type="hidden" name="hidemainmenu" value="0" />';
	echo '</form>';
	$language_text = array();
	$description = '';			// default description if no language file
	flexicontact_html::getSiteText($language_text);

// get an array of filenames
	
    $imageFiles = array();					// create array
    $handle = opendir(FILEPATH_IMAGES);
	if (!$handle)
		{
		echo JText::_('COM_FLEXICONTACT_NO_IMAGES_DIRECTORY');
		return;
		}
		
	while (($filename = readdir($handle)) != false)
	    {
    	if ($filename == '.' or $filename == '..')
    		continue;
    	$imageInfo = @getimagesize(FILEPATH_IMAGES.$filename);
    	if ($imageInfo === false)
    		continue;					// not an image
    	if ($imageInfo[3] > 3)			// only support gif, jpg or png
    		continue;
    	if ($imageInfo[0] > 150)		// if X size > 150 pixels ..
    		continue;					// .. it's too big so skip it
    	$imageFiles[] = $filename;		// add to array
    	}
    closedir($handle);
    if (empty($imageFiles))
		{
		echo JText::_('COM_FLEXICONTACT_NO_IMAGES');
		return;
		}
    $image_count = count($imageFiles);
	sort($imageFiles);
	$rowCount = 0;
	$columns = 4;
	$column_width = intval(100 / ($columns * 2));
	
	echo "\n<br />".'<table class="adminlist">'."\n";
	foreach($imageFiles as $filename)
		{
		$imageInfo = getimagesize(FILEPATH_IMAGES.$filename);
		if ($imageInfo !== false)
			{
			$imageX = $imageInfo[0];
			$imageY = $imageInfo[1];
			}
			
		if (isset($language_text['IMAGE_'.strtoupper($filename)]))
			$description = $language_text['IMAGE_'.strtoupper($filename)];
		else
			$description = '';
		
		if ($rowCount == 0)
			echo '<tr>';
		echo "\n".'<td valign="top" width="'.$column_width.'%">';
		echo "\n".'  <img hspace="0" vspace="0" border="0" src="'.JURI::root().'components/com_flexicontact/images/'.$filename.'" alt="" /></td>';
		echo "\n".'<td valign="top" width="'.$column_width.'%"><b>'.utf8_encode($filename).'</b><br />';
		echo $description.'<br />';
		echo $imageX.'x'.$imageY.'<br />';
		echo "\n".' <a href="index.php?option=com_flexicontact&amp;task=delete_file&amp;file_name='.$filename.'"> '; 
		echo JText::_('COM_FLEXICONTACT_DELETE').'</a></td>';
		$rowCount++;
		if ($rowCount == $columns)
			{
			echo "</tr>\n";
			$rowCount=0;
			}
		}

	if (($rowCount > 0) and ($rowCount < $columns))
		{
		$colsleft = ($columns - $rowCount) * 2;
		echo '<td colspan="'.$colsleft.'"></td>';
		echo '</tr>';
		}
	echo '</table>';
	echo '('.$image_count." ".JText::_('COM_FLEXICONTACT_IMAGES').')';
}

// -------------------------------------------------------------------------------
// Draw the top menu and make the current item active
//
function addSubMenu($submenu = '')
{
	JSubMenuHelper::addEntry(JText::_('COM_FLEXICONTACT_LOG_FILE'), 'index.php?option='.LA_COMPONENT.'&task=show_log', $submenu == 'show_log');
	JSubMenuHelper::addEntry(JText::_('COM_FLEXICONTACT_CAPTCHA_IMAGES'), 'index.php?option='.LA_COMPONENT.'&task=images', $submenu == 'images');
	JSubMenuHelper::addEntry(JText::_('COM_FLEXICONTACT_HELP_AND_SUPPORT'), 'index.php?option='.LA_COMPONENT.'&task=help', $submenu == 'help');
}
   
}
?>

