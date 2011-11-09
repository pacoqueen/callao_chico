<?php
/********************************************************************
Copyright 2009-2010 Chris Gaebler
Version :	4.xx
Date    :	25 March 2011
Description:  A flexible contact component with configurable fields
Please see the pdf documentation at http://extensions.lesarbresdesign.info
*********************************************************************
This file is part of FlexiContact
FlexiContact is free software. You can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation.
*********************************************************************/
defined( '_JEXEC' ) or die( 'Direct access to this file is prohibited.' );

class HTML_flexicontact
{

// -------------------------------------------------------------------------------
// Display confirmation that the email was sent
//
function displayConfirmation($configData,$status)
{
	echo "\n".'<div class="flexicontact">';
	if ($configData['page_title'] != '')
		echo "\n".'<h2>'.$configData['page_title'].'</h2>';

	if ($configData['image'] == "-1")
		$imageHtml = "";
	else
		$imageHtml = '<img src="'.$configData['imgpath'].'" align="'.$configData['image_align'].'" alt="" style="padding:0 3px 0 3px" />';

	echo $imageHtml;
	if ($status === true)
		echo JText::_('COM_FLEXICONTACT_MESSAGE_SENT');
	else
		echo JText::_('COM_FLEXICONTACT_MESSAGE_FAILED');
	echo "\n</div>";
}

// -------------------------------------------------------------------------------
// Display the image test
// Returns the description of the target image
//
function displayImageTest($configData)
{

// get list of images in images directory

	$imageDir = 'components/com_flexicontact/images/';
    $images = array();					// create array
    $handle = opendir($imageDir);
	if (!$handle)
		{
		echo "Images directory not found";
		return;
		}
		
	while (($filename = readdir($handle)) != false)
	    {
    	if ($filename == '.' or $filename == '..')
    		continue;
    	$imageInfo = @getimagesize($imageDir.$filename);
    	if ($imageInfo === false)
    		continue;				// not an image
    	if ($imageInfo[3] > 3)		// only support gif, jpg or png
    		continue;
    	if ($imageInfo[0] > 150)	// if X size > 150 pixels ..
    		continue;				// .. it's too big so skip it
    	$images[] = $filename;		// add to array
    	}
    closedir($handle);
    if (empty($images))
		{
		echo "No suitable images in images directory";
		return;
		}
	$imageCount = count($images);
	if ($imageCount < $configData['num_images'])
		{
		echo 'Not enough images in images directory. Requested: '.$configData['num_images'].' Found: '.$imageCount.'<br />';
		echo 'Please check the menu item<br />';
		return;
		}
		
	// choose the images
	
	$i = 0;
	$randoms = array();
	while ($i < $configData['num_images'])
		{
		$imageNum = rand(0,$imageCount - 1);	// get a random number
		if (in_array($imageNum,$randoms))		// if already chosen
			continue;							// try again
		$randoms[] = $imageNum;					// add to random number array
		$i ++;									// got one more
		}
		
	// choose the target image, get its description and make the prompt
	
	$i = rand(0, $configData['num_images'] - 1);
	$j = $randoms[$i];
	$targetImage = $images[$j];
	$targetText = JText::_('COM_FLEXICONTACT_IMAGE_'.strtoupper($targetImage));
	echo JText::_('COM_FLEXICONTACT_SELECT_IMAGE').' '.$targetText.'<br />';
	
	// now output the images
	
	echo '<script type="text/javascript">';
	echo "<!--
			function imageSelect(pictureID)
			{	var images = document.getElementsByName('fc_image');
				for (var i = 0; i < images.length; i++)
					images[i].style.borderColor = 'transparent';
				document.getElementById(pictureID).style.borderColor = 'red';
				document.fc_form.picselected.value = pictureID;	} 
		 //--> </script>";
	
	echo '<div align="center">';
	for ($i = 0; $i < $configData['num_images']; $i++)
		{
		$j = $randoms[$i];
		$imageName = $images[$j];		// get the filename
		$imageName = $images[$j];		// get the filename
		$imageHtml = '<img id="i_'.$imageName.'" name="fc_image" src="'.$imageDir.$imageName.'" alt="" style="border:2px solid transparent;" onclick="imageSelect('."'i_".$imageName."'".')" />';
		echo "\n".$imageHtml."\n";
		}
	echo '</div>';			// class="flexicontact"
	return $targetText;
}

// -------------------------------------------------------------------------------
// Display the input form
//
function displayForm($configData,$formData,$errorMessages,$validation_result)
{
	echo "\n".'<div class="flexicontact">';
	if ($configData['page_title'] != '')
		echo "\n".'<h2>'.$configData['page_title'].'</h2>';
		
	if ($configData['image'] == "-1")
		$imageHtml = "";
	else
		$imageHtml = '<img src="'.$configData['imgpath'].'" align="'.$configData['image_align'].'" alt="" style="padding:0 5px 0 5px" />';
	
	echo $imageHtml;
	
	if (!$validation_result)
		echo JText::_('COM_FLEXICONTACT_MESSAGE_NOT_SENT');
	else
		if (!empty($configData['page_text']))
			echo $configData['page_text'];
		
	if (empty($configData['toPrimary']))
		{
		echo "\n'To' address is not set in the menu item parameters";
		echo '</div>';
		return;
		}
		
	echo '
		<form name="fc_form" action="'.JRoute::_('index.php').'" method="post" class="flexicontact_form">
		<input type="hidden" name="task" value="send" />

		<table width="100%" class="flexicontact_table">
		<tr>
		  <td align="left" width="20%">'.JText::_('COM_FLEXICONTACT_FROM_NAME').'</td>
		  <td align="left">
			<input type="text" name="fromName" size="30" value="'.$formData['fromName'].'" />
			'.$errorMessages['fromName'].'
		  </td>
		</tr>';
	if ($configData['subject'] == 'yes')
		echo '
			<tr>
			  <td align="left" width="20%">'.JText::_('COM_FLEXICONTACT_SUBJECT').'</td>
			  <td align="left">
				<input type="text" name="subject" size="30" value="'.$formData['subject'].'" />
				'.$errorMessages['subject'].'
			  </td>
			</tr>';
	if ($configData['list_opt'] != 'disabled')
		{
		echo "\n".'
			<tr>
			  <td align="left" width="20%">'.$configData['list_prompt'].'</td>
			  <td align="left">
				  <select name="list1">';
		for ($i = 0; $i < $configData['list_count']; $i++)
				//		echo '<option value="'.$i.'">'.$configData['list_array'][$i].'</option>';
		 echo '<option value="Consulta">Consulta</option>';
			echo '<option value="Sugerencia">Sugerencia</option>';
		echo '</select>
			
		 
		  </td>
		</tr>';
		}
	if ($configData['line1_opt'] != 'disabled')
		echo "\n".'
			<tr>
			  <td align="left" width="20%">'.$configData['line1_prompt'].'</td>
			  <td align="left">
				<input type="text" name="line1" size="30" value="'.$formData['line1'].'" />
				'.$errorMessages['line1'].'
			  </td>
			</tr>';
	if ($configData['line2_opt'] != 'disabled')
		echo "\n".'
			<tr>
			  <td align="left" width="20%">'.$configData['line2_prompt'].'</td>
			  <td align="left">
				<input type="text" name="line2" size="30" value="'.$formData['line2'].'" />
				'.$errorMessages['line2'].'
			  </td>
			</tr>';
	if ($configData['line3_opt'] != 'disabled')
		echo "\n".'
			<tr>
			  <td align="left" width="20%">'.$configData['line3_prompt'].'</td>
			  <td align="left">
				<input type="text" name="line3" size="30" value="'.$formData['line3'].'" />
				'.$errorMessages['line3'].'
			  </td>
			</tr>';
	if ($configData['line4_opt'] != 'disabled')
		echo "\n".'
			<tr>
			  <td align="left" width="20%">'.$configData['line4_prompt'].'</td>
			  <td align="left">
				<input type="text" name="line4" size="30" value="'.$formData['line4'].'" />
				'.$errorMessages['line4'].'
			  </td>
			</tr>';
	if ($configData['line5_opt'] != "disabled")
		echo "\n".'
			<tr>
			  <td align="left" width="20%">'.$configData['line5_prompt'].'</td>
			  <td align="left">
				<input type="text" name="line5" size="30" value="'.$formData['line5'].'" />
				'.$errorMessages['line5'].'
			  </td>
			</tr>';
	if ($configData['area_opt'] != 'disabled')
		echo "\n".'
			<tr>
			  <tr>
		  <td align="left" width="20%">'.JText::_('COM_FLEXICONTACT_FROM_ADDRESS').'</td>
		  <td align="left">
			<input type="text" name="fromAddress" size="30" value="'.$formData['fromAddress'].'" />
			'.$errorMessages['fromAddress'].'
		  </td>
		</tr>
			  <td align="left" valign="top" width="20%">'.$configData['area_prompt'].'</td>
			  <td align="left">
				<textarea name="area_data" rows="'.$configData['area_height'].'" cols="'.$configData['area_width'].'">'.$formData['area_data'].'</textarea><br />
				'.$errorMessages['area_data'].'
			  </td>
			</tr>';
	echo '
		<tr>
		  <td colspan="2" align="left">';
	echo '<input type="checkbox" name="copyMe" value="1" />';
	echo JText::_('COM_FLEXICONTACT_COPY_ME');
	echo '</td></tr>';
	
	echo '</table>';
	echo "\n".'<table width="100%" class="contentpaneopen">';
	
	if ($configData['magic_word'] != '')
		{
		echo "\n".'
			<tr><td align="left" width="20%">'.JText::_('COM_FLEXICONTACT_MAGIC_WORD').'</td>
			  <td align="left">
				<input type="text" name="magic_word" size="30" value="'.$formData['magic_word'].'" />
				'.$errorMessages['magic_word'].'
			  </td>
			</tr>';
		}
	
	if ($configData['num_images'] > 0)
		{
		echo "\n".'<tr><td colspan="2">';
		if (!empty($errorMessages['imageTest']))
			echo $errorMessages['imageTest'].'<br />';
		echo '<input type="hidden" name="picselected" value="" />';
		$targetText = HTML_flexicontact::displayImageTest($configData);
		echo '<input type="hidden" name="picrequested" value="'.$targetText.'" />';
		echo '</td></tr>';
		}
	echo "\n".'<tr><td colspan="2">';
	echo '<input type="submit" name="submit1" value="'.JText::_('COM_FLEXICONTACT_SEND_BUTTON').'" />';
	echo '</td></tr>';
	if (!empty($configData['bottom_text']))
		{
		echo "\n".'<tr><td colspan="2">';
		echo $configData['bottom_text'];
		echo '</td></tr>';
		}
	echo '</table>';
	echo '</form>';
	echo '</div>';				// class="flexicontact"
}

// -------------------------------------------------------------------------------
// Validate the user input
// Returns true if valid, false if not
//
function validateInput($configData,$formData,&$errorMessages)
{
	$ret = true;
	
// if using captcha, validate the image

	if ($configData['num_images'] > 0)
		{
		$pic_selected = substr(strtoupper($formData['pic_selected']),2);	// strip off the i_
		$targetText = JText::_('COM_FLEXICONTACT_IMAGE_'.$pic_selected);
		if (strcmp($formData['pic_requested'],$targetText) != 0)
			{
			$ret = false;
			$errorMessages['imageTest'] = JText::_('COM_FLEXICONTACT_WRONG_PICTURE');
			}
		}
	
// if using magic word, validate the word

	if ($configData['magic_word'] != '')
		{
		if (strcasecmp($formData['magic_word'],$configData['magic_word']) != 0)
			{
			$ret = false;
			$errorMessages['magic_word'] = JText::_('COM_FLEXICONTACT_WRONG_MAGIC_WORD');
			}
		}
	
// validate the from name

	if (empty($formData['fromName']))
		{
		$ret = false;
		$errorMessages['fromName'] = JText::_('COM_FLEXICONTACT_REQUIRED');
		}

// validate the from address

	jimport('joomla.mail.helper');
	if (!JMailHelper::isEmailAddress($formData['fromAddress']))
		{
		$ret = false;
		$errorMessages['fromAddress'] = JText::_('COM_FLEXICONTACT_BAD_EMAIL');
		}

// validate the subject

	if (($configData['subject'] == 'yes')
	and (empty($formData['subject'])))
		{
		$ret = false;
		$errorMessages['subject'] = JText::_('COM_FLEXICONTACT_REQUIRED');
		}

// validate the list selection

	if (($configData['list_opt'] == "mandatory") && (empty($formData['list1'])))
		{
		$ret = false;
		$errorMessages['list'] = JText::_('COM_FLEXICONTACT_REQUIRED');
		}

// validate line1

	if (($configData['line1_opt'] == "mandatory") && (empty($formData['line1'])))
		{
		$ret = false;
		$errorMessages['line1'] = JText::_('COM_FLEXICONTACT_REQUIRED');
		}

// validate line2

	if (($configData['line2_opt'] == "mandatory") && (empty($formData['line2'])))
		{
		$ret = false;
		$errorMessages['line2'] = JText::_('COM_FLEXICONTACT_REQUIRED');
		}

// validate line3

	if (($configData['line3_opt'] == "mandatory") && (empty($formData['line3'])))
		{
		$ret = false;
		$errorMessages['line3'] = JText::_('COM_FLEXICONTACT_REQUIRED');
		}

// validate line4

	if (($configData['line4_opt'] == "mandatory") && (empty($formData['line4'])))
		{
		$ret = false;
		$errorMessages['line4'] = JText::_('COM_FLEXICONTACT_REQUIRED');
		}

// validate line5

	if (($configData['line5_opt'] == "mandatory") && (empty($formData['line5'])))
		{
		$ret = false;
		$errorMessages['line5'] = JText::_('COM_FLEXICONTACT_REQUIRED');
		}

// validate area_data

	if (($configData['area_opt'] == "mandatory") && (empty($formData['area_data'])))
		{
		$ret = false;
		$errorMessages['area_data'] = JText::_('COM_FLEXICONTACT_REQUIRED');
		}
		
	return $ret;
}

}
?>
