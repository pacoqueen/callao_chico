<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class ArchiveController extends acymailingController{
	function view(){
		$statsClass = acymailing::get('class.stats');
		$statsClass->countReturn = false;
		$statsClass->saveStats();
		JRequest::setVar( 'layout', 'view'  );
		return parent::display();
	}
}