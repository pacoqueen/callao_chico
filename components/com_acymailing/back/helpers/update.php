<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class updateHelper{
	var $db;
	var $errors = array();
	function updateHelper(){
		$this->db =& JFactory::getDBO();
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
	}
	function installNotifications(){
		$this->db->setQuery('SELECT `alias` FROM `#__acymailing_mail` WHERE `type` = \'notification\'');
		$notifications = $this->db->loadResultArray();
		$data = array();
		if(!in_array('notification_created',$notifications)){
			$data[] = "('New Subscriber on your website', '<p>Hello {subtag:name},</p><p>A new user has been created in AcyMailing : </p><blockquote><p>Name : {user:name}</p><p>Email : {user:email}</p><p>IP : {user:ip} </p></blockquote>', '', 1, 'notification', 0,'notification_created', 1)";
		}
		if(!in_array('notification_unsuball',$notifications)){
			$data[] = "('A User unsubscribed from all your lists', '<p>Hello {subtag:name},</p><p>The user {user:name} : {user:email} unsubscribed from all your lists</p><p>{survey}</p>', '', 1, 'notification', 0, 'notification_unsuball', 1)";
		}
		if(!in_array('notification_unsub',$notifications)){
			$data[] = "('A User unsubscribed', '<p>Hello {subtag:name},</p><p>The user {user:name} : {user:email} unsubscribed from your list</p><p>{survey}</p>', '', 1, 'notification', 0, 'notification_unsub', 1)";
		}
		if(!in_array('notification_refuse',$notifications)){
			$data[] = "('A User refuses to receive e-mails from your website', '<p>The User {user:name} : {user:email} refuses to receive any e-mail anymore from your website.</p><p>{survey}</p>', '', 1, 'notification',0,'notification_refuse', 1)";
		}
		if(!in_array('confirmation',$notifications)){
			$data[] = "('{subtag:name}, please confirm your subscription', '<p> Hello {subtag:name}, </p><p><strong>{confirm}Click here to confirm your subscription{/confirm}</strong></p>', '',1, 'notification', 0, 'confirmation', 1)";
		}
		if(!in_array('report',$notifications)){
			$data[] = "('AcyMailing Cron Report', '<p>{report}</p><p>{detailreport}</p>', '',1, 'notification',0,  'report', 1)";
		}
		if(!in_array('notification_autonews',$notifications)){
			$data[] = "('A Newsletter has been generated : \"{subject}\"', '<p>The Newsletter issue {issuenb} has been generated : </p><p>Subject : {subject}</p><p>{body}</p>', '',1, 'notification', 0, 'notification_autonews',1)";
		}
		if(!in_array('modif',$notifications)){
			$data[] = "('Modify your subscription', '<p>Hello {subtag:name}, </p><p>You requested some changes on your subscription,</p><p>Please {modify}click here{/modify} to be identified as the owner of this account and then modify your subscription.</p>', '',1, 'notification', 0, 'modif', 1)";
		}
		if(!empty($data)){
			$this->db->setQuery("INSERT INTO `#__acymailing_mail` (`subject`, `body`, `altbody`, `published`, `type`, `visible`, `alias`, `html`) VALUES ".implode(',',$data));
			$this->db->query();
		}
		$query = "INSERT IGNORE INTO `#__acymailing_fields` (`fieldname`, `namekey`, `type`, `value`, `published`, `ordering`, `options`, `core`, `required`, `backend`, `frontcomp`, `default`, `listing`) VALUES
		('NAMECAPTION', 'name', 'text', '', 1, 1, '', 1, 1, 1, 1, '',1),
		('EMAILCAPTION', 'email', 'text', '', 1, 2, '', 1, 1, 1, 1, '',1),
		('RECEIVE', 'html', 'radio', '0::JOOMEXT_TEXT\n1::HTML', 1, 3, '', 1, 1, 1, 1, '1',1);";
		$this->db->setQuery($query);
		$this->db->query();
	}
	function installMenu($code = ''){
		if(empty($code)){
			$lang =& JFactory::getLanguage();
			$code = $lang->getTag();
		}
		$path = JLanguage::getLanguagePath(JPATH_ROOT).DS.$code.DS.$code.'.com_acymailing.ini';
		if(!file_exists($path)) return;
		$content = file_get_contents($path);
		if(empty($content)) return;
		$menuFileContent = 'COM_ACYMAILING="AcyMailing"'."\r\n";
		$menuFileContent = 'ACYMAILING="AcyMailing"'."\r\n";
		$menuStrings = array('USERS','LISTS','TEMPLATES','NEWSLETTERS','AUTONEWSLETTERS','CAMPAIGN','QUEUE','STATISTICS','CONFIGURATION','UPDATE_ABOUT');
		foreach($menuStrings as $oneString){
			preg_match('#(\n|\r)(ACY_)?'.$oneString.'="(.*)"#i',$content,$matches);
			if(empty($matches[3])) continue;
			if(version_compare(JVERSION,'1.6.0','<')){
				$menuFileContent .= 'COM_ACYMAILING.'.$oneString.'="'.$matches[3].'"'."\r\n";
			}else{
				$menuFileContent .= $oneString.'="'.$matches[3].'"'."\r\n";
			}
		}
		if(version_compare(JVERSION,'1.6.0','<')){
			$menuPath = ACYMAILING_ROOT.'administrator'.DS.'language'.DS.$code.DS.$code.'.com_acymailing.menu.ini';
		}else{
			$menuPath = ACYMAILING_ROOT.'administrator'.DS.'language'.DS.$code.DS.$code.'.com_acymailing.sys.ini';
		}
		if(!JFile::write($menuPath, $menuFileContent)){
			acymailing::display(JText::sprintf('FAIL_SAVE',$menuPath),'error');
		}
	}
	function installTemplates(){
		$path = ACYMAILING_TEMPLATE;
		$dirs = JFolder::folders( $path );
		$template = array();
		$order = 0;
		foreach($dirs as $oneTemplateDir){
			$order++;
			$description = '';
			$name = '';
			$body = '';
			$altbody = '';
			$premium = 0;
			$ordering = $order;
			$styles=array();
			if(!@include($path.DS.$oneTemplateDir.DS.'install.php')) continue;
			$body = str_replace(array('src="./','src="../'),array('src="media/com_acymailing/templates/'.$oneTemplateDir.'/','src="media/com_acymailing/templates/'),$body);
			$template[] = $this->db->Quote($oneTemplateDir).','.$this->db->Quote($name).','.$this->db->Quote($description).','.$this->db->Quote($body).','.$this->db->Quote($altbody).','.$this->db->Quote($premium).','.$this->db->Quote($ordering).','.$this->db->Quote(serialize($styles));
		}
		if(empty($template)) return true;
		$this->db->setQuery("INSERT IGNORE INTO `#__acymailing_template` (`namekey`, `name`, `description`, `body`, `altbody`, `premium`, `ordering`, `styles`) VALUES (".implode('),(',$template).')');
		$this->db->query();
		$ndTemplates = $this->db->getAffectedRows();
		if(!empty($ndTemplates)){
			acymailing::display(JText::sprintf('TEMPLATES_INSTALL',$ndTemplates),'success');
		}
	}
	function initList(){
		$query = 'UPDATE IGNORE '.acymailing::table('users',false).' as b, '.acymailing::table('subscriber').' as a SET a.email = b.email, a.name = b.name WHERE a.userid = b.id AND a.userid > 0';
		$this->db->setQuery($query);
		$this->db->query();
		$time = time();
		$query = 'INSERT IGNORE INTO `#__acymailing_subscriber` (`email`,`name`,`confirmed`,`userid`,`created`,`enabled`,`accept`,`html`) SELECT `email`,`name`,1-`block`,`id`,UNIX_TIMESTAMP(`registerDate`),1-`block`,1,1 FROM `#__users`';
		$this->db->setQuery($query);
		$this->db->query();
		$this->db->setQuery('SELECT COUNT(*) FROM `#__acymailing_list`');
		$nbLists = $this->db->loadResult();
		if(!empty($nbLists)) return true;
		$user =& JFactory::getUser();
		$this->db->setQuery("INSERT INTO `#__acymailing_list` (`name`, `description`, `ordering`, `published`, `alias`, `color`, `visible`, `type`,`userid`) VALUES ('Newsletters','Receive our latest news','1','1','mailing_list','#3366ff','1','list',".(int) $user->id.")");
		$this->db->query();
		$listid = $this->db->insertid();
		$this->db->setQuery('INSERT IGNORE INTO `#__acymailing_listsub` (`listid`, `subid`, `subdate`, `status`) SELECT '.$listid.', subid, '.$time.',1 FROM `#__acymailing_subscriber`');
		$this->db->query();
	}
	function installExtensions(){
		$path = ACYMAILING_BACK.'extensions';
		$dirs = JFolder::folders( $path );
		if(version_compare(JVERSION,'1.6.0') < ''){
			$query = "SELECT CONCAT(`folder`,`element`) FROM #__plugins WHERE `folder` = 'acymailing' OR `element` LIKE '%acymailing%'";
			$query .= " UNION SELECT `module` FROM #__modules WHERE `module` LIKE '%acymailing%'";
			$this->db->setQuery($query);
			$existingExtensions = $this->db->loadResultArray();
		}else{
			$this->db->setQuery("SELECT CONCAT(`folder`,`element`) FROM #__extensions WHERE `folder` = 'acymailing' OR `element` LIKE '%acymailing%'");
			$existingExtensions = $this->db->loadResultArray();
		}
		$plugins = array();
		$modules = array();
		$extensioninfo = array(); //array('name','ordering','required table or published')
		$extensioninfo['mod_acymailing'] = array('AcyMailing Module');
		$extensioninfo['plg_acymailing_share'] = array('AcyMailing : share on social networks',20,1);
		$extensioninfo['plg_acymailing_contentplugin'] = array('AcyMailing : trigger Joomla Content plugins',15,0);
		$extensioninfo['plg_acymailing_managetext'] = array('AcyMailing Manage text',10,1);
		$extensioninfo['plg_acymailing_tablecontents'] = array('AcyMailing table of contents generator',5,1);
		$extensioninfo['plg_acymailing_online'] = array('AcyMailing Tag : Website links',6,1);
		$extensioninfo['plg_acymailing_stats'] = array('AcyMailing : Statistics Plugin',50,1);
		$extensioninfo['plg_acymailing_tagcbuser'] = array('AcyMailing Tag : CB User information',4,'#__comprofiler');
		$extensioninfo['plg_acymailing_tagcontent'] = array('AcyMailing Tag : content insertion',11,1);
		$extensioninfo['plg_acymailing_tagjomsocial'] = array('AcyMailing Tag : JomSocial User Fields',4,'#__community_users');
		$extensioninfo['plg_acymailing_tagmodule'] = array('AcyMailing Tag : Insert a Module',12,1);
		$extensioninfo['plg_acymailing_tagsubscriber'] = array('AcyMailing Tag : Subscriber information',2,1);
		$extensioninfo['plg_acymailing_tagsubscription'] = array('AcyMailing Tag : Manage the Subscription',1,1);
		$extensioninfo['plg_acymailing_tagtime'] = array('AcyMailing Tag : Date / Time',5,1);
		$extensioninfo['plg_acymailing_taguser'] = array('AcyMailing Tag : Joomla User Information',3,1);
		$extensioninfo['plg_acymailing_tagvmcoupon'] = array('AcyMailing Tag : VirtueMart personnal coupons',7,'#__vm_coupons');
		$extensioninfo['plg_acymailing_tagvmproduct'] = array('AcyMailing Tag : insert VirtueMart products',8,'#__vm_product');
		$extensioninfo['plg_acymailing_template'] = array('AcyMailing Template Class Replacer',25,1);
		$extensioninfo['plg_acymailing_urltracker'] = array('AcyMailing : Handle Click tracking',30,1);
		$extensioninfo['plg_system_regacymailing'] = array('AcyMailing : (auto)Subscribe during Joomla registration',0,1);
		$extensioninfo['plg_system_vmacymailing'] = array('AcyMailing : VirtueMart checkout subscription',0,0);
		$listTables = $this->db->getTableList();
		foreach($dirs as $oneDir){
			$arguments = explode('_',$oneDir);
			if($arguments[0] == 'plg'){
				$newPlugin = null;
				$newPlugin->name = $oneDir;
				if(isset($extensioninfo[$oneDir][0])) $newPlugin->name = $extensioninfo[$oneDir][0];
				$newPlugin->type = 'plugin';
				$newPlugin->folder = $arguments[1];
				$newPlugin->element = $arguments[2];
				$newPlugin->enabled = 1;
				if(isset($extensioninfo[$oneDir][2])){
					if(is_numeric($extensioninfo[$oneDir][2])) $newPlugin->enabled = $extensioninfo[$oneDir][2];
					elseif(!in_array(str_replace('#__',$this->db->getPrefix(),$extensioninfo[$oneDir][2]),$listTables)) $newPlugin->enabled = 0;
				}
				$newPlugin->params = '{}';
				$newPlugin->ordering = 0;
				if(isset($extensioninfo[$oneDir][1])) $newPlugin->ordering = $extensioninfo[$oneDir][1];
				if(!acymailing::createDir(ACYMAILING_ROOT.'plugins'.DS.$newPlugin->folder)) continue;
				if(version_compare(JVERSION,'1.6.0','<')){
					$destinationFolder = ACYMAILING_ROOT.'plugins'.DS.$newPlugin->folder;
				}else{
					$destinationFolder = ACYMAILING_ROOT.'plugins'.DS.$newPlugin->folder.DS.$newPlugin->element;
					if(!acymailing::createDir($destinationFolder)) continue;
				}
				if(!$this->copyFolder($path.DS.$oneDir,$destinationFolder)) continue;
				if(in_array($newPlugin->folder.$newPlugin->element,$existingExtensions)) continue;
				$plugins[] = $newPlugin;
			}elseif($arguments[0] == 'mod'){
				$newModule = null;
				$newModule->name = $oneDir;
				if(isset($extensioninfo[$oneDir][0])) $newModule->name = $extensioninfo[$oneDir][0];
				$newModule->type = 'module';
				$newModule->folder = '';
				$newModule->element = $oneDir;
				$newModule->enabled = 1;
				$newModule->params = '{}';
				$newModule->ordering = 0;
				if(isset($extensioninfo[$oneDir][1])) $newModule->ordering = $extensioninfo[$oneDir][1];
				$destinationFolder = ACYMAILING_ROOT.'modules'.DS.$oneDir;
				if(!acymailing::createDir($destinationFolder)) continue;
				if(!$this->copyFolder($path.DS.$oneDir,$destinationFolder)) continue;
				if(in_array($newModule->element,$existingExtensions)) continue;
				$modules[] = $newModule;
			}else{
				acymailing::display('Could not handle : '.$oneDir,'error');
			}
		}
		if(!empty($this->errors)) acymailing::display($this->errors,'error');
		if(version_compare(JVERSION,'1.6.0','<')){
			$extensions = $plugins;
		}else{
			$extensions = array_merge($plugins,$modules);
		}
		$success = array();
		if(!empty($extensions)){
			if(version_compare(JVERSION,'1.6.0','<')){
				$queryExtensions = 'INSERT INTO `#__plugins` (`name`,`element`,`folder`,`published`,`ordering`) VALUES ';
			}else{
				$queryExtensions = 'INSERT INTO `#__extensions` (`name`,`element`,`folder`,`enabled`,`ordering`,`type`) VALUES ';
			}
			foreach($extensions as $oneExt){
				$queryExtensions .= '('.$this->db->Quote($oneExt->name).','.$this->db->Quote($oneExt->element).','.$this->db->Quote($oneExt->folder).','.$oneExt->enabled.','.$oneExt->ordering;
				if(version_compare(JVERSION,'1.6.0','>=')) $queryExtensions .= ','.$this->db->Quote($oneExt->type);
				$queryExtensions .= '),';
				if($oneExt->type != 'module') $success[] = JText::sprintf('PLUG_INSTALLED',$oneExt->name);
			}
			$queryExtensions = trim($queryExtensions,',');
			$this->db->setQuery($queryExtensions);
			$this->db->query();
		}
		if(!empty($modules)){
			foreach($modules as $oneModule){
				if(version_compare(JVERSION,'1.6.0','<')){
					$queryModule = 'INSERT INTO `#__modules` (`title`,`position`,`published`,`module`) VALUES ';
					$queryModule .= '('.$this->db->Quote($oneModule->name).",'left',0,".$this->db->Quote($oneModule->element).")";
				}else{
					$queryModule = 'INSERT INTO `#__modules` (`title`,`position`,`published`,`module`,`access`,`language`) VALUES ';
					$queryModule .= '('.$this->db->Quote($oneModule->name).",'position-7',0,".$this->db->Quote($oneModule->element).",1,'*')";
				}
				$this->db->setQuery($queryModule);
				$this->db->query();
				$moduleId = $this->db->insertid();
				$this->db->setQuery('INSERT IGNORE INTO `#__modules_menu` (`moduleid`,`menuid`) VALUES ('.$moduleId.',0)');
				$this->db->query();
				$success[] = JText::sprintf('MODULE_INSTALLED',$oneModule->name);
			}
		}
		if(!empty($success)) acymailing::display($success,'success');
	}
	function copyFolder($from,$to){
		$return = true;
		$allFiles = JFolder::files($from);
		foreach($allFiles as $oneFile){
			if(file_exists($to.DS.'index.html') AND $oneFile == 'index.html') continue;
			if(JFile::copy($from.DS.$oneFile,$to.DS.$oneFile) !== true){
				$this->errors[] = 'Could not copy the file from '.$from.DS.$oneFile.' to '.$to.DS.$oneFile;
				$return = false;
			}
		}
		$allFolders = JFolder::folders($from);
		if(!empty($allFolders)){
			foreach($allFolders as $oneFolder){
				if(!acymailing::createDir($to.DS.$oneFolder)) continue;
				if(!$this->copyFolder($from.DS.$oneFolder,$to.DS.$oneFolder)) $return = false;
			}
		}
		return $return;
	}
	function getUrl(){
		$urls = parse_url(ACYMAILING_LIVE);
		$lurl = preg_replace('#^www2?\.#Ui','',$urls['host'],1);
		if(!empty($urls['path'])) $lurl .= $urls['path'];
		return strtolower(rtrim($lurl,'/'));
	}
}