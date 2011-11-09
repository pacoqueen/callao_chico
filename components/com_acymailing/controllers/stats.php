<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class StatsController extends acymailingController{
	function listing(){
		JRequest::setVar('tmpl','component');
		$statsClass = acymailing::get('class.stats');
		$statsClass->saveStats();
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );
		header( 'Pragma: no-cache' );
		ob_end_clean();
		JPluginHelper::importPlugin('acymailing');
		$this->dispatcher = &JDispatcher::getInstance();
		$results = $this->dispatcher->trigger('acymailing_getstatpicture');
		$picture = reset($results);
		if(empty($picture)) $picture = 'media/com_acymailing/images/statpicture.png';
		$picture = ltrim(str_replace(array('\\','/'),DS,$picture),DS);
		$imagename = ACYMAILING_ROOT.$picture;
		$handle = fopen($imagename, 'r');
		if(!$handle) exit;
		header("Content-type: image/png");
		$contents = fread($handle, filesize($imagename));
		fclose($handle);
		echo $contents;
		exit;
	}
}