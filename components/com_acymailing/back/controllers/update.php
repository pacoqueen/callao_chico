<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class UpdateController extends JController{
	function __construct($config = array()){
		parent::__construct($config);
		$this->registerDefaultTask('update');
	}
	function install(){
		$newConfig = null;
		$newConfig->installcomplete = 1;
		$config = acymailing::config();
		$config->save($newConfig);
		$updateHelper = acymailing::get('helper.update');
		$updateHelper->initList();
		$updateHelper->installNotifications();
		$updateHelper->installTemplates();
		$updateHelper->installMenu();
		$updateHelper->installExtensions();
		$this->_iframe(ACYMAILING_UPDATEURL.'install');
	}
	function update(){
		$config = acymailing::config();
		if(!acymailing::isAllowed($config->get('acl_config_manage','all'))){
			acymailing::display(JText::_('ACY_NOTALLOWED'),'error');
			return false;
		}
		acymailing::setTitle(JText::_('UPDATE_ABOUT'),'install','update');
		$bar = & JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Link', 'back', JText::_('ACY_CLOSE'), acymailing::completeLink('dashboard') );
		return $this->_iframe(ACYMAILING_UPDATEURL.'update');
	}
	function _iframe($url){
		$informations = null;
		$config = acymailing::config();
		$informations->version = $config->get('version');
		$informations->level = $config->get('level');
		$informations->component = 'acymailing';
		$infos = urlencode(base64_encode(serialize($informations)));
		$url .= '&infos='.$infos;
?>
        <div id="acymailing_div">
            <iframe allowtransparency="true" scrolling="auto" height="450px" frameborder="0" width="100%" name="acymailing_frame" id="acymailing_frame" src="<?php echo $url; ?>">
            </iframe>
        </div>
<?php
	}
}