<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class DashboardController extends acymailingController{
	var $aclCat = 'dashboard';
	function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('listing','display');
		$this->registerDefaultTask('listing');
	}
	function display(){
		if(!empty($this->aclCat) AND !$this->isAllowed($this->aclCat,'manage')) return;
		return parent::display();
	}
}