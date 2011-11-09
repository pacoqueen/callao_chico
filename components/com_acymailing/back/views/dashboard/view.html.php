<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class dashboardViewDashboard extends JView
{
	function display($tpl = null)
	{
		$doc =& JFactory::getDocument();
		$config = acymailing::config();
		$buttons = array();
		$desc = array();
		$desc['subscriber'] = '<ul><li>'.JText::_('USERS_DESC_CREATE').'</li><li>'.JText::_('USERS_DESC_MANAGE').'</li><li>'.JText::_('USERS_DESC_IMPORT').'</li></ul>';
		$desc['list'] = '<ul><li>'.JText::_('LISTS_DESC_CREATE').'</li><li>'.JText::_('LISTS_DESC_SUBSCRIPTION').'</li></ul>';
		$desc['newsletter'] = '<ul><li>'.JText::_('NEWSLETTERS_DESC_CREATE').'</li><li>'.JText::_('NEWSLETTERS_DESC_TEST').'</li><li>'.JText::_('NEWSLETTERS_DESC_SEND').'</li></ul>';
		$desc['template'] = '<ul><li>'.JText::_('TEMPLATES_DESC_CREATE').'</li></ul>';
		$desc['queue'] = '<ul><li>'.JText::_('QUEUE_DESC_CONTROL').'</li></ul>';
		$desc['config'] = '<ul><li>'.JText::_('CONFIG_DESC_CONFIG').'</li><li>'.JText::_('CONFIG_DESC_MODIFY').'</li><li>'.JText::_('CONFIG_DESC_PLUGIN').'</li><li>'.JText::_('QUEUE_DESC_BOUNCE');
		if(!acymailing::level(3)){ $desc['config'] .= acymailing::getUpgradeLink('enterprise'); }
		$desc['config'] .= '</li></ul>';
		$desc['stats'] = '<ul><li>'.JText::_('STATS_DESC_VIEW').'</li><li>'.JText::_('STATS_DESC_CLICK');
		if(!acymailing::level(1)){ $desc['stats'] .= acymailing::getUpgradeLink('essential'); }
		$desc['stats'] .= '</li><li>'.JText::_('STATS_DESC_CHARTS');
		if(!acymailing::level(1)){ $desc['stats'] .= acymailing::getUpgradeLink('essential'); }
		$desc['stats'] .= '</li></ul>';
		$desc['autonews'] = '<ul><li>'.JText::_('AUTONEWS_DESC');
		if(!acymailing::level(2)){ $desc['autonews'] .= acymailing::getUpgradeLink('business'); }
		$desc['autonews'] .='</li></ul>';
		$desc['campaign'] = '<ul><li>'.JText::_('CAMPAIGN_DESC_CREATE');
		if(!acymailing::level(3)){ $desc['campaign'] .= acymailing::getUpgradeLink('enterprise'); }
		$desc['campaign'] .= '</li><li>'.JText::_('CAMPAIGN_DESC_AFFECT');
		if(!acymailing::level(3)){ $desc['campaign'] .= acymailing::getUpgradeLink('enterprise'); }
		$desc['campaign'] .='</li></ul>';
		$desc['update'] = '<ul><li>'.JText::_('UPDATE_DESC').'</li><li>'.JText::_('CHANGELOG_DESC').'</li><li>'.JText::_('ABOUT_DESC').'</li></ul>';
		$buttons[] = array('link'=>'subscriber','level'=>0,'image'=>'user','text'=>JText::_('USERS'),'acl' => 'acl_subscriber_manage');
		$buttons[] = array('link'=>'list','level'=>0,'image'=>'acylist','text'=>JText::_('LISTS'),'acl' => 'acl_lists_manage');
		$buttons[] = array('link'=>'newsletter','level'=>0,'image'=>'newsletter','text'=>JText::_('NEWSLETTERS'),'acl' => 'acl_newsletters_manage');
		$buttons[] = array('link'=>'autonews','level'=>2,'image'=>'autonewsletter','text'=>JText::_('AUTONEWSLETTERS'),'acl' => 'acl_autonewsletters_manage');
		$buttons[] = array('link'=>'campaign','level'=>3,'image'=>'campaign','text'=>JText::_('CAMPAIGN'), 'acl' => 'acl_campaign_manage');
		$buttons[] = array('link'=>'template','level'=>0,'image'=>'acytemplate','text'=>JText::_('ACY_TEMPLATES'), 'acl' => 'acl_templates_manage');
		$buttons[] = array('link'=>'queue','level'=>0,'image'=>'process','text'=>JText::_('QUEUE'), 'acl' => 'acl_queue_manage');
		$buttons[] = array('link'=>'stats','level'=>0,'image'=>'stats','text'=>JText::_('STATISTICS'), 'acl' => 'acl_statistics_manage');
		$buttons[] = array('link'=>'config','level'=>0,'image'=>'config','text'=>JText::_('CONFIGURATION'), 'acl' => 'acl_configuration_manage');
		$buttons[] = array('link'=>'update','level'=>0,'image'=>'install','text'=>JText::_('UPDATE_ABOUT'), 'acl' => 'acl_configuration_manage');
		$htmlbuttons = array();
		foreach($buttons as $oneButton){
			if(acymailing::isAllowed($config->get($oneButton['acl'],'all'))){
				$htmlbuttons[] = $this->_quickiconButton($oneButton['link'],$oneButton['image'],$oneButton['text'],$desc[$oneButton['link']],$oneButton['level']);
			}
		}
		acymailing::setTitle( ACYMAILING_NAME , 'acymailing' ,'dashboard' );
		$bar = & JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Pophelp','dashboard');
		$this->assignRef('buttons',$htmlbuttons);
		$this->assignRef('toggleClass',acymailing::get('helper.toggle'));
		$db = JFactory::getDBO();
		$db->setQuery('SELECT name,email,html,confirmed,subid,created FROM '.acymailing::table('subscriber').' ORDER BY created DESC LIMIT 15');
		$this->assignRef('users',$db->loadObjectList());
		$db->setQuery('SELECT a.*, b.subject FROM '.acymailing::table('stats').' as a LEFT JOIN '.acymailing::table('mail').' as b on a.mailid = b.mailid ORDER BY a.senddate DESC LIMIT 15');
		$this->assignRef('stats',$db->loadObjectList());
		$doc->addScript(((empty($_SERVER['HTTPS']) OR strtolower($_SERVER['HTTPS']) != "on" ) ? 'http://' : 'https://')."www.google.com/jsapi");
		$today = acymailing::getTime(date('Y-m-d'));
		$joomConfig =& JFactory::getConfig();
		$diff = date('Z') + intval($joomConfig->getValue('config.offset')*60*60);
		$db->setQuery("SELECT count(`subid`) as total, DATE_FORMAT(FROM_UNIXTIME(`created` - $diff),'%Y-%m-%d') as subday FROM ".acymailing::table('subscriber')." GROUP BY subday ORDER BY subday DESC LIMIT 15");
		$statsusers = $db->loadObjectList();
		$this->assignRef('statsusers',$statsusers);
		jimport('joomla.html.pane');
		$tabs	=& JPane::getInstance('tabs');
		$this->assignRef('tabs',$tabs);
		$this->assignRef('config',$config);
		parent::display($tpl);
	}
	function _quickiconButton( $link, $image, $text,$description,$level)
	{
		$url = acymailing::level($level) ? 'onclick="document.location.href=\''.acymailing::completeLink($link).'\';"' : '';
		$html = '<div style="float:left;width: 100%;" '.$url.' class="icon"><table width="100%"><tr><td style="text-align: center;" width="100px">';
		$html .= '<span class="icon-48-'.$image.'" style="background-repeat:no-repeat;background-position:center;height:48px" title="'.$text.'"> </span>';
		$html .= '<span>'.$text.'</span></td><td>'.$description.'</td></tr></table>';
		$html .= '</div>';
		return $html;
	}
}