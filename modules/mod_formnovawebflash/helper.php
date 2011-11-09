<?php
/**
* @version		0.8 (J16)
* @author		Michael A. Gilkes (jaido7@yahoo.com)
* @copyright	Michael Albert Gilkes
* @license		GNU/GPLv2
*/

/*

Easy File Uploader Module for Joomla! 1.6
Copyright (C) 2010  Michael Albert Gilkes

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*/

// no direct access
defined('_JEXEC') or die('Restricted access');

//import joomla file helper class
jimport('joomla.filesystem.file');


class modEasyFileUploaderHelper
{
	public static function getFileToUpload(&$params)
	{
		$result = "";
		
		//check to see if the upload process has started
		if (isset($_FILES[$params->get('efu_variable')]))
		{
			//now, we're going to check each of the uploaded files
			$total = intval($params->get('efu_multiple'));
			for ($i = 0; $i < $total; $i++)
			{
				//so, now, check for any other errors
				if ($_FILES[$params->get('efu_variable')]["error"][$i] > 0)
				{
					//error was found. Show the return code.
					$result .= "Return Code: ".$_FILES[$params->get('efu_variable')]["error"][$i]."<br />";
					$result .= modEasyFileUploaderHelper::fileUploadErrorMessage($_FILES[$params->get('efu_variable')]["error"][$i]);
				}
				else
				{
					//no errors found.
					//check to see if the file type is correct
					//but first, we have to store the file types in a variable. I was getting an issue with empty()
					//if (modEasyFileUploaderHelper::isValidFileType($params, $i))
					//{
						//the file type is permitted
						//so, check for the right size
						if ($_FILES[$params->get('efu_variable')]["size"][$i] < $params->get('efu_maxsize'))
						{
							//file is an acceptable size
							//check to see if file already exists in the destination folder
							if (file_exists(JPATH_SITE.DS.'images'.DS.$params->get('efu_folder').DS.$_FILES[$params->get('efu_variable')]["name"][$i]))
							{
								//file already exists
								//check whether the user wants to replace the file or not.
								if ($params->get('efu_replace') == "0" && $_POST["answer"] == "0")
								{
									//yep, the user wants to replace the file, so just delete the existing file
									JFile::delete(JPATH_SITE.DS.'images'.DS.$params->get('efu_folder').DS.$_FILES[$params->get('efu_variable')]["name"][$i]);
									$result .= "Replacement approved. Previous file replaced. ";
									modEasyFileUploaderHelper::storeUploadedFile($params, $result, $i);
								}
								else
								{
									$result .= $_FILES[$params->get('efu_variable')]["name"][$i]." already exists.";
								}
							}
							else
							{
								modEasyFileUploaderHelper::storeUploadedFile($params, $result, $i);
							}
						}
						else
						{
							//file is too large
							$result .= "ERROR: The uploaded file is too large. It must be smaller than ".modEasyFileUploaderHelper::sizeToText($params->get('efu_maxsize')).".";
						}
					/*}
					else
					{
						//the file type is not permitted
						//$fakeMIME = $_FILES[$params->get('efu_variable')]["type"][$i];
						$trueMIME = modEasyFileUploaderHelper::actualMIME($_FILES[$params->get('efu_variable')]["tmp_name"][$i]);
						$result .= "ERROR: The uploaded file type ".($trueMIME!==false?", ".$trueMIME.",":"")." is not permitted.";
					}*/
				}
				$result .= "<hr />";
			}
		}
		
		return $result;
	}
	
	private static function isValidFileType(&$params, &$i)
	{
		$valid = false;
		
		$filetypes = $params->get('efu_filetypes');
		$actualMIME = modEasyFileUploaderHelper::actualMIME($_FILES[$params->get('efu_variable')]["tmp_name"][$i]);
		if ($filetypes == "*" || 
			(stripos($filetypes, $_FILES[$params->get('efu_variable')]["type"][$i]) !== false &&
			$actualMIME !== false &&
			stripos($filetypes, $actualMIME) !== false))
		{
			$valid = true;
		}
		
		return $valid;
	}
	
	private static function actualMIME($file)
	{
		if (!file_exists($file))
		{
			return false;
		}
		
		$mime = false;
		// try to use recommended functions
		if (defined('FILEINFO_MIME_TYPE') &&
			function_exists('finfo_open') && is_callable('finfo_open') && 
			function_exists('finfo_file') && is_callable('finfo_file') && 
			function_exists('finfo_close') && is_callable('finfo_close'))
		{
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $file);
			if ($mime === '')
			{
				$mime = false;
			}
			finfo_close($finfo);
		}
		else if (function_exists('mime_content_type') && is_callable('mime_content_type'))
		{
			$mime = mime_content_type($file);
		}
		else if (strtoupper(substr(PHP_OS,0,3)) !== 'WIN')
		{
			$f = escapeshellarg($file);
			if (function_exists('exec') && is_callable('exec'))
			{
				//didn't like how system flushes output to browser. replaced with exec()
				$mime = exec("file -bi '$f'");
				//this removes the charset value if it was returned with the mime type. mime is first.
				$mime = strtok($mime, '; ');
			}
			else if (function_exists('shell_exec') && is_callable('shell_exec'))
			{
				$mime = shell_exec("file -bi '$f'");
				//this removes the charset value if it was returned with the mime type. mime is first.
				$mime = strtok($mime, '; ');
			}
		}
		return $mime;
	}
	
	/*private static function storeUploadedFile(&$params, &$result, &$i)
	{
		//move the file to the destination folder
		JFile::upload($_FILES[$params->get('efu_variable')]["tmp_name"][$i], JPATH_SITE.DS.'images'.DS.$params->get('efu_folder').DS.$_FILES[$params->get('efu_variable')]["name"][$i]);
		//Upload was successful.
		$result .= "Upload was successful.<br />";
		$result .= "Name: ".$_FILES[$params->get('efu_variable')]["name"][$i]."<br />";
		$result .= "Type: ".$_FILES[$params->get('efu_variable')]["type"][$i]."<br />";
		$result .= "Size: ".modEasyFileUploaderHelper::sizeToText($_FILES[$params->get('efu_variable')]["size"][$i])."<br />";
		//$result .= "Temp file: ".$_FILES[$params->get('efu_variable')]["tmp_name"][$i]."<br />";
		$result .= "Stored in: ".$params->get('efu_folder').DS.$_FILES[$params->get('efu_variable')]["name"][$i];
	}*/


	private static function storeUploadedFile(&$params, &$result, &$i)
	{
		//move the file to the destination folder
		JFile::upload($_FILES[$params->get('efu_variable')]["tmp_name"][$i], JPATH_SITE.DS.'images'.DS.$params->get('efu_folder').DS.$_FILES[$params->get('efu_variable')]["name"][$i]);
		//Upload was successful.
		$result .= "Upload was successful.<br />";
		$result .= "Name: ".$_FILES[$params->get('efu_variable')]["name"][$i]."<br />";
		$result .= "Type: ".$_FILES[$params->get('efu_variable')]["type"][$i]."<br />";
		$result .= "Size: ".modEasyFileUploaderHelper::sizeToText($_FILES[$params->get('efu_variable')]["size"][$i])."<br />";
		$result .= "Temp file: ".$_FILES[$params->get('efu_variable')]["tmp_name"][$i]."<br />";
		$result .= "Stored in: ".$params->get('efu_folder').DS.$_FILES[$params->get('efu_variable')]["name"][$i];
	}
	
	protected static function fileUploadErrorMessage($error_code)
	{
		switch ($error_code)
		{
			case UPLOAD_ERR_INI_SIZE:
				$message = 'The uploaded file exceeds the upload_max_filesize directive in php.ini'; 
				break;
			case UPLOAD_ERR_FORM_SIZE: 
				$message = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'; 
				break;
			case UPLOAD_ERR_PARTIAL: 
				$message = 'The uploaded file was only partially uploaded'; 
				break;
			case UPLOAD_ERR_NO_FILE: 
				$message = 'No file was uploaded'; 
				break;
			case UPLOAD_ERR_NO_TMP_DIR: 
				$message = 'Missing a temporary folder'; 
				break;
			case UPLOAD_ERR_CANT_WRITE: 
				$message = 'Failed to write file to disk'; 
				break;
			case UPLOAD_ERR_EXTENSION: 
				$message = 'File upload stopped by extension'; 
				break;
			default: 
				$message = 'Unknown upload error';
				break;
		}
		return $message;
	}
	
	protected static function sizeToText($size)
	{
		$text = "";
		$kb = 1024;
		$mb = $kb * $kb;
		$gb = $mb * $kb;
		
		if ($size >= $gb)
		{
			$size = round($size / $gb, 2);
			$text = $size."GB";
		}
		elseif ($size >= $mb)
		{
			$size = round($size / $mb, 2);
			$text = $size."MB";
		}
		elseif ($size >= $kb)
		{
			$size = round($size / $kb, 2);
			$text = $size."KB";
		}
		else
		{
			$text = $size."bytes";
		}
		return $text;
	}
}
?>