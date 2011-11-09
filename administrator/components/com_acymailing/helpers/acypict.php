<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class acypictHelper{
	var $error;
	var $maxHeight;
	var $maxWidth;
	var $destination;
	function acypictHelper(){
		jimport('joomla.filesystem.file');
	}
	function removePictures($text){
		$return = preg_replace('#< *img[^>]*>#Ui','',$text);
		$return = preg_replace('#< *div[^>]*class="jce_caption"[^>]*>[^<]*(< *div[^>]*>[^<]*<\/div>)*[^<]*<\/div>#Ui','',$return);
		return $return;
	}
	function available(){
		if(!function_exists('gd_info')){
			$this->error = 'The GD library is not installed.';
			return false;
		}
		if(!function_exists('getimagesize')){
			$this->error = 'Cound not find getimagesize function';
			return false;
		}
		if(!function_exists('imagealphablending')){
			$this->error = "Please make sure you're using GD 2.0.1 or later version";
			return false;
		}
		return true;
	}
	function resizePictures($input){
		$this->destination = ACYMAILING_MEDIA.'resized'.DS;
		acymailing::createDir($this->destination);
		$content = acymailing::absoluteURL($input);
		preg_match_all('#<img([^>]*)>#Ui',$content,$results);
		if(empty($results[1])) return $input;
		$replace = array();
		foreach($results[1] as $onepicture){
			if(!preg_match('#src="([^"]*)"#Ui',$onepicture,$path)) continue;
			$imageUrl = $path[1];
			if(strpos($imageUrl,ACYMAILING_LIVE) !== false){
				$imageUrl = str_replace(array(ACYMAILING_LIVE,'/'),array(ACYMAILING_ROOT,DS),$imageUrl);
			}
			$newPicture = $this->generateThumbnail($imageUrl);
			if(!$newPicture) continue;
			$newPicture['file'] = str_replace(array(ACYMAILING_ROOT,DS),array(ACYMAILING_LIVE,'/'),$newPicture['file']);
			$replaceImage = array();
			$replaceImage[$path[1]] = $newPicture['file'];
			if(preg_match_all('#(width|height)(:|=) *"?([0-9]+)#i',$onepicture,$resultsSize)){
				foreach($resultsSize[0] as $i => $oneArg){
					$newVal = (strtolower($resultsSize[1][$i]) == 'width') ? $newPicture['width'] : $newPicture['height'];
					if($newVal > $resultsSize[3][$i]) continue;
					$replaceImage[$oneArg] = str_replace($resultsSize[3][$i],$newVal,$oneArg);
				}
			}
			$replace[$onepicture] = str_replace(array_keys($replaceImage),$replaceImage,$onepicture);
		}
		if(!empty($replace)){
			$input = str_replace(array_keys($replace),$replace,$content);
		}
		return $input;
	}
	function generateThumbnail($picturePath){
 		list($currentwidth, $currentheight) = getimagesize($picturePath);
 		if(empty($currentwidth) || empty($currentheight)) return false;
 		$factor = min($this->maxWidth/$currentwidth,$this->maxHeight/$currentheight);
		if($factor>=1) return false;
		$newWidth = round($currentwidth*$factor);
		$newHeight = round($currentheight*$factor);
		if(strpos($picturePath,'http') === 0){
			$filename = substr($picturePath,strrpos($picturePath,'/')+1);
		}else{
			$filename = basename($picturePath);
		}
		$extension = strtolower(substr($filename,strrpos($filename,'.')+1));
		$name = strtolower(substr($filename,0,strrpos($filename,'.')));
		$newImage = $name.'thumb'.$this->maxWidth.'x'.$this->maxHeight.'.'.$extension;
		if(empty($this->destination)){
			$newFile = dirname($picturePath).DS.$newImage;
		}else{
			$newFile = $this->destination.$newImage;
		}
		if(file_exists($newFile)) return array('file' => $newFile,'width' => $newWidth,'height' => $newHeight);
		switch($extension){
			case 'gif':
				$img = ImageCreateFromGIF($picturePath);
				break;
			case 'jpg':
			case 'jpeg':
				$img = ImageCreateFromJPEG($picturePath);
				break;
			case 'png':
				$img = ImageCreateFromPNG($picturePath);
				break;
			default:
				return false;
		}
		$thumb = ImageCreateTrueColor($newWidth, $newHeight);
		if(in_array($extension,array('gif','png'))){
		  imagealphablending($thumb, false);
		  imagesavealpha($thumb,true);
		}
		ImageCopyResized($thumb, $img, 0, 0, 0, 0, $newWidth, $newHeight,$currentwidth, $currentheight);
		ob_start();
		switch($extension){
			case 'gif':
				$status = imagegif($thumb);
				break;
			case 'jpg':
			case 'jpeg':
				$status = imagejpeg($thumb,null,100);
				break;
			case 'png':
				$status = imagepng($thumb,null,0);
				break;
		}
		$imageContent = ob_get_clean();
		$status = $status && JFile::write($newFile,$imageContent);
		imagedestroy($thumb);
		imagedestroy($img);
		if(!$status) $newFile = $picturePath;
		return array('file' => $newFile,'width' => $newWidth,'height' => $newHeight);
	}
}
