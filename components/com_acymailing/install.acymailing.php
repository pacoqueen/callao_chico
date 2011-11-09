<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
function com_install(){
  include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php');

  @ini_set('max_execution_time',0);
  $lang =& JFactory::getLanguage();
  $lang->load(ACYMAILING_COMPONENT,JPATH_SITE);
  $installClass = new acymailingInstall();
  $installClass->updateJoomailing();
  $installClass->addPref();
  $installClass->updatePref();
  $installClass->updateSQL();
  $installClass->displayInfo();
}
class acymailingInstall{
  var $level = 'starter';
  var $version = '1.8.4';
  var $update = false;
  var $fromLevel = '';
  var $fromVersion = '';
  var $db;
  function acymailingInstall(){
    $this->db =& JFactory::getDBO();
  }
  function displayInfo(){
    unset($_SESSION['acymailing']['li']);
    echo '<h1>Please wait... </h1><h2>AcyMailing will now automatically install the Plugins and the Module</h2>';
    $url = 'index.php?option=com_acymailing&ctrl=update&task=install';
    echo '<a href="'.$url.'">Please click here if you are not automatically redirected within 3 seconds</a>';
    echo "<script language=\"javascript\" type=\"text/javascript\">document.location.href='$url';</script>\n";
  }
  function updatePref(){
    $this->db->setQuery("SELECT `namekey`, `value` FROM `#__acymailing_config` WHERE `namekey` IN ('version','level') LIMIT 2");
    $results = $this->db->loadObjectList('namekey');
    if($results['version']->value == $this->version AND $results['level']->value == $this->level) return true;
    $this->update = true;
    $this->fromLevel = $results['level']->value;
    $this->fromVersion = $results['version']->value;
    $query = "REPLACE INTO `#__acymailing_config` (`namekey`,`value`) VALUES ('level',".$this->db->Quote($this->level)."),('version',".$this->db->Quote($this->version)."),('installcomplete','0')";
    $this->db->setQuery($query);
    $this->db->query();
  }
  function updateSQL(){
    if(!$this->update) return true;
    if(version_compare($this->fromVersion,'1.1.4','<')){
      $replace1 = "REPLACE(`params`, 'showhtml=1\nshowname=1', 'customfields=name,email,html' )";
      $replace2 = "REPLACE( $replace1 , 'showhtml=0\nshowname=1', 'customfields=name,email' )";
      $replace3 = "REPLACE( $replace2 , 'showhtml=1\nshowname=0', 'customfields=email,html' )";
      $replace4 = "REPLACE( $replace3 , 'showhtml=0\nshowname=0', 'customfields=email' )";
      $this->db->setQuery("UPDATE #__modules SET `params`= $replace4 WHERE `module` = 'mod_acymailing' ");
      $this->db->query();
    }
    if(version_compare($this->fromVersion,'1.2.1','<')){
      $this->db->setQuery("UPDATE `#__acymailing_config` SET `value` = 'data' WHERE `value` = '0' AND `namekey` = 'allow_modif' LIMIT 1");
      $this->db->query();
      $this->db->setQuery("UPDATE `#__acymailing_config` SET `value` = 'all' WHERE `value` = '1' AND `namekey` = 'allow_modif' LIMIT 1");
      $this->db->query();
    }
    if(version_compare($this->fromVersion,'1.2.2','<')){
      $this->db->setQuery("ALTER TABLE `#__acymailing_mail` ADD `sentby` INT UNSIGNED NULL DEFAULT NULL");
      $this->db->query();
      $this->db->setQuery("ALTER TABLE `#__acymailing_template` ADD `subject` VARCHAR( 250 ) NULL DEFAULT NULL");
      $this->db->query();
      $this->db->setQuery("DELETE FROM `#__plugins` WHERE `folder` = 'acymailing' AND `element` = 'autocontent'");
      $this->db->query();
    }
    if(version_compare($this->fromVersion,'1.2.3','<')){
      $this->db->setQuery("UPDATE `#__plugins` SET `folder` = 'system', `element`= 'regacymailing', `name` = 'AcyMailing : (auto)Subscribe during Joomla registration', `params`= REPLACE(`params`, 'lists=', 'autosub=' ) WHERE `folder` = 'user' AND `element` = 'acymailing'");
      $this->db->query();
      $this->db->setQuery("DELETE FROM `#__plugins` WHERE `folder` = 'acymailing' AND `element` = 'autocontent'");
      $this->db->query();
      $this->db->setQuery("ALTER TABLE `#__acymailing_template` ADD `stylesheet` TEXT NULL");
      $this->db->query();
      jimport('joomla.filesystem.folder');
      if(is_dir(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.ACYMAILING_COMPONENT.DS.'plugins'.DS.'plg_user_acymailing')){
        JFolder::delete(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.ACYMAILING_COMPONENT.DS.'plugins'.DS.'plg_user_acymailing');
      }
      if(is_dir(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.ACYMAILING_COMPONENT.DS.'plugins'.DS.'plg_acymailing_autocontent')){
        JFolder::delete(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.ACYMAILING_COMPONENT.DS.'plugins'.DS.'plg_acymailing_autocontent');
      }
    }
    if(version_compare($this->fromVersion,'1.3.1','<')){
      $this->db->setQuery("ALTER TABLE `#__acymailing_config` CHANGE `value` `value` TEXT NULL ");
      $this->db->query();

      $this->db->setQuery("ALTER TABLE `#__acymailing_fields` ADD `listing` TINYINT NULL DEFAULT NULL ");
      $this->db->query();
      $this->db->setQuery("UPDATE `#__acymailing_fields` SET `listing` = 1 WHERE `namekey` IN ('name','email','html') ");
      $this->db->query();
      $this->db->setQuery("ALTER TABLE `#__acymailing_template` ADD `fromname` VARCHAR( 250 ) NULL , ADD `fromemail` VARCHAR( 250 ) NULL , ADD `replyname` VARCHAR( 250 ) NULL , ADD `replyemail` VARCHAR( 250 ) NULL ");
      $this->db->query();
    }
    if(version_compare($this->fromVersion,'1.5.2','<')){
      $this->db->setQuery("SELECT `params` FROM #__plugins WHERE `element` = 'regacymailing' LIMIT 1");
      $existingEntry = $this->db->loadResult();
      $listids = 'None';
      if(preg_match('#autosub=(.*)#i',$existingEntry,$autosubResult)){
        $listids = $autosubResult[1];
      }
      $this->db->setQuery("INSERT IGNORE INTO `#__acymailing_config` (`namekey`,`value`) VALUES ('autosub',".$this->db->Quote($listids).")");
      $this->db->query();
    }
    if(version_compare($this->fromVersion,'1.5.3','<')){
      $this->db->setQuery('UPDATE #__acymailing_config SET `value` = REPLACE(`value`,\'<sup style="font-size: 4px;">TM</sup>\',\'™\')');
      $this->db->query();
    }
    if(version_compare($this->fromVersion,'1.6.2','<')){
      $this->db->setQuery("UPDATE #__acymailing_config SET `value` = 'media/com_acymailing/upload' WHERE `namekey` = 'uploadfolder' AND `value` = 'components/com_acymailing/upload' ");
      $this->db->query();
      $this->db->setQuery("UPDATE #__acymailing_config SET `value` = 'media/com_acymailing/logs/report".rand(0,999999999).".log' WHERE `namekey` = 'cron_savepath' ");
      $this->db->query();
      if(version_compare(JVERSION,'1.6.0','<')){
        $this->db->setQuery("UPDATE #__plugins SET `params` = REPLACE(`params`,'components/com_acymailing/images','media/com_acymailing/images') ");
      }else{
        $this->db->setQuery("UPDATE #__extensions SET `params` = REPLACE(`params`,'components\/com_acymailing\/images','media\/com_acymailing\/images') ");
      }
      $this->db->query();
      $updateClass = acymailing::get('helper.update');
      $removeFiles = array();
      $removeFiles[] = ACYMAILING_FRONT.'css'.DS.'component_default.css';
      $removeFiles[] = ACYMAILING_FRONT.'css'.DS.'frontendedition.css';
      $removeFiles[] = ACYMAILING_FRONT.'css'.DS.'module_default.css';
      foreach($removeFiles as $oneFile){
        if(is_file($oneFile)) JFile::delete($oneFile);
      }
      $fromFolders = array();
      $toFolders = array();
      $fromFolders[] = ACYMAILING_FRONT.'css';
      $toFolders[] = ACYMAILING_MEDIA.'css';
      $fromFolders[] = ACYMAILING_FRONT.'templates'.DS.'plugins';
      $toFolders[] = ACYMAILING_MEDIA.'plugins';
      $fromFolders[] = ACYMAILING_FRONT.'upload';
      $toFolders[] = ACYMAILING_MEDIA.'upload';
      foreach($fromFolders as $i => $oneFolder){
        if(!is_dir($oneFolder)) continue;
        if(is_dir($toFolders[$i])){
          $updateClass->copyFolder($oneFolder,$toFolders[$i]);
        }
      }
      $deleteFolders = array();
      $deleteFolders[] = ACYMAILING_FRONT.'css';
      $deleteFolders[] = ACYMAILING_FRONT.'images';
      $deleteFolders[] = ACYMAILING_FRONT.'js';
      $deleteFolders[] = ACYMAILING_BACK.'logs';
      foreach($deleteFolders as $oneFolder){
        if(!is_dir($oneFolder)) continue;
        JFolder::delete($oneFolder);
      }
    }
    if(version_compare($this->fromVersion,'1.7.1','<')){
      $this->db->setQuery("CREATE TABLE IF NOT EXISTS `#__acymailing_history` (`subid` INT UNSIGNED NOT NULL ,`date` INT UNSIGNED NOT NULL ,`ip` VARCHAR( 50 ) NULL ,
                `action` VARCHAR( 50 ) NOT NULL , `data` TEXT NULL , `source` TEXT NULL , INDEX ( `subid` , `date` ) ) ENGINE=MyISAM ;");
      $this->db->query();
    }
    if(version_compare($this->fromVersion,'1.7.3','<')){
      $this->db->setQuery("ALTER TABLE `#__acymailing_mail` ADD `metakey` TEXT NULL , ADD `metadesc` TEXT NULL ");
      $this->db->query();
    }
    if(version_compare($this->fromVersion,'1.8.4','<')){
      $this->db->setQuery("UPDATE `#__acymailing_config` as a, `#__acymailing_config` as b SET a.`value` = b.`value` WHERE a.`namekey`= 'queue_nbmail_auto' AND b.`namekey`= 'queue_nbmail' ");
      $this->db->query();
      $this->db->setQuery("UPDATE `#__acymailing_mail` SET `body` = CONCAT(`body`,'<p>{survey}</p>') WHERE type = 'notification' AND `alias` IN ('notification_refuse','notification_unsub','notification_unsuball')");
      $this->db->query();
    }
  }
  function updateJoomailing(){
    $this->db->setQuery("SHOW TABLES LIKE '".$this->db->getPrefix()."joomailing_config'");
    $result = $this->db->loadResult();
    if(empty($result)) return true;
    $this->db->setQuery("INSERT IGNORE INTO `#__acymailing_config` (`namekey`,`value`) SELECT `namekey`, REPLACE(`value`,'com_joomailing','com_acymailing') FROM `#__joomailing_config`");
    $this->db->query();
    $this->db->setQuery("INSERT IGNORE INTO `#__acymailing_list` (`name`, `description`, `ordering`, `listid`, `published`, `userid`, `alias`, `color`, `visible`, `welmailid`, `unsubmailid`, `type`) SELECT `name`, `description`, `ordering`, `listid`, `published`, `userid`, `alias`, `color`, `visible`, `welmailid`, `unsubmailid`, `type` FROM `#__joomailing_list`");
    $this->db->query();
    $this->db->setQuery("INSERT IGNORE INTO `#__acymailing_listcampaign` (`campaignid`, `listid`) SELECT `campaignid`, `listid` FROM `#__joomailing_listcampaign`");
    $this->db->query();
    $this->db->setQuery("INSERT IGNORE INTO `#__acymailing_listmail` (`listid`, `mailid`) SELECT `listid`, `mailid` FROM `#__joomailing_listmail`");
    $this->db->query();
    $this->db->setQuery("INSERT IGNORE INTO `#__acymailing_listsub` (`listid`, `subid`, `subdate`, `unsubdate`, `status`) SELECT `listid`, `subid`, `subdate`, `unsubdate`, `status` FROM `#__joomailing_listsub`");
    $this->db->query();
    $this->db->setQuery("INSERT IGNORE INTO `#__acymailing_mail` (`mailid`, `subject`, `body`, `altbody`, `published`, `senddate`, `created`, `fromname`, `fromemail`, `replyname`, `replyemail`, `type`, `visible`, `userid`, `alias`, `attach`, `html`, `tempid`, `key`, `frequency`, `params`) SELECT `mailid`, `subject`, REPLACE(`body`,'joomailing','acymailing'), REPLACE(`altbody`,'joomailing','acymailing'), `published`, `senddate`, `created`, `fromname`, `fromemail`, `replyname`, `replyemail`, `type`, `visible`, `userid`, `alias`, REPLACE(`attach`,'com_joomailing','com_acymailing'), `html`, `tempid`, `key`, `frequency`, REPLACE(`params`,'com_joomailing','com_acymailing') FROM `#__joomailing_mail`");
    $this->db->query();
    $this->db->setQuery("INSERT IGNORE INTO `#__acymailing_queue` (`senddate`, `subid`, `mailid`, `priority`, `try`) SELECT `senddate`, `subid`, `mailid`, `priority`, `try` FROM `#__joomailing_queue`");
    $this->db->query();
    $this->db->setQuery("INSERT IGNORE INTO `#__acymailing_stats` (`mailid`, `senthtml`, `senttext`, `senddate`, `openunique`, `opentotal`, `bounceunique`, `fail`, `clicktotal`, `clickunique`, `unsub`, `forward`) SELECT `mailid`, `senthtml`, `senttext`, `senddate`, `openunique`, `opentotal`, `bounceunique`, `fail`, `clicktotal`, `clickunique`, `unsub`, `forward` FROM `#__joomailing_stats`");
    $this->db->query();
    $this->db->setQuery("INSERT IGNORE INTO `#__acymailing_subscriber` (`subid`, `email`, `userid`, `name`, `created`, `confirmed`, `enabled`, `accept`, `ip`, `html`, `key`) SELECT `subid`, `email`, `userid`, `name`, `created`, `confirmed`, `enabled`, `accept`, `ip`, `html`, `key` FROM `#__joomailing_subscriber`");
    $this->db->query();
    $this->db->setQuery("INSERT IGNORE INTO `#__acymailing_template` (`tempid`, `name`, `description`, `body`, `altbody`, `created`, `published`, `premium`, `ordering`, `namekey`, `styles`) SELECT `tempid`, `name`, REPLACE(`description`,'joomailing','acymailing'), REPLACE(`body`,'joomailing','acymailing'), REPLACE(`altbody`,'joomailing','acymailing'), `created`, `published`, `premium`, `ordering`, `namekey`, REPLACE(`styles`,'joomailing','acymailing') FROM `#__joomailing_template`");
    $this->db->query();
    $this->db->setQuery("INSERT IGNORE INTO `#__acymailing_url` (`urlid`, `name`, `url`) SELECT `urlid`, REPLACE(`name`,'com_joomailing','com_acymailing'), REPLACE(`url`,'com_joomailing','com_acymailing') FROM `#__joomailing_url`");
    $this->db->query();
    $this->db->setQuery("INSERT IGNORE INTO `#__acymailing_urlclick` (`urlid`, `mailid`, `click`, `subid`, `date`) SELECT `urlid`, `mailid`, `click`, `subid`, `date` FROM `#__joomailing_urlclick`");
    $this->db->query();
    $this->db->setQuery("INSERT IGNORE INTO `#__acymailing_userstats` (`mailid`, `subid`, `html`, `sent`, `senddate`, `open`, `opendate`, `bounce`, `fail`) SELECT `mailid`, `subid`, `html`, `sent`, `senddate`, `open`, `opendate`, `bounce`, `fail` FROM `#__joomailing_userstats`");
    $this->db->query();
    $this->db->setQuery("DROP TABLE IF EXISTS `#__joomailing_config`, `#__joomailing_list`, `#__joomailing_listcampaign`, `#__joomailing_listmail`, `#__joomailing_listsub`, `#__joomailing_mail`, `#__joomailing_queue` , `#__joomailing_stats`, `#__joomailing_subscriber`, `#__joomailing_template` , `#__joomailing_url`, `#__joomailing_urlclick`, `#__joomailing_userstats`");
    $this->db->query();
    $this->db->setQuery("UPDATE `#__modules` SET `title` = REPLACE(`title`,'JooMailing','AcyMailing'), `module` = REPLACE(`module`,'joomailing','acymailing'), `params` = REPLACE(`params`,'joomailing','acymailing')");
    $this->db->query();
    $this->db->setQuery("UPDATE `#__plugins` SET `name` = REPLACE(REPLACE(REPLACE(`name`,'jooMailing','AcyMailing'),'joomailing','acymailing'),'JooMailing','AcyMailing'), `element` = REPLACE(`element`,'joomailing','acymailing'), `folder` = REPLACE(`folder`,'joomailing','acymailing'), `params` = REPLACE(`params`,'joomailing','acymailing')");
    $this->db->query();
    $this->db->setQuery("DELETE FROM `#__components` WHERE `option` LIKE '%joomailing%' OR `admin_menu_link` LIKE '%joomailing%'");
    $this->db->query();
    $this->db->setQuery("UPDATE `#__menu` SET `menutype` = REPLACE(`menutype`,'joomailing','acymailing'), `name` = REPLACE(`name`,'joomailing','acymailing'), `alias` = REPLACE(`alias`,'joomailing','acymailing'), `link` = REPLACE(`link`,'joomailing','acymailing')");
    $this->db->query();
    $newFile = '<?php
          $app =& JFactory::getApplication();
          $url = \'index.php?option=com_acymailing\';
          foreach($_GET as $name => $value){
            if($name == \'option\') continue;
            $url .= \'&\'.$name.\'=\'.$value;
          }
          $app->redirect($url);
          ';
    @file_put_contents(rtrim(JPATH_SITE,DS).DS.'components'.DS.'com_joomailing'.DS.'joomailing.php',$newFile);
    @file_put_contents(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_joomailing'.DS.'admin.joomailing.php',$newFile);
  }
  function addPref(){
    $conf	=& JFactory::getConfig();
    $this->level = ucfirst($this->level);
    $allPref = array();
    $allPref['level'] =  $this->level;
    $allPref['version'] = $this->version;
    $allPref['from_name'] = $conf->getValue('config.fromname');
    $allPref['from_email'] = $conf->getValue('config.mailfrom');
    $allPref['reply_name'] = $conf->getValue('config.fromname');
    $allPref['reply_email'] =  $conf->getValue('config.mailfrom');
    $allPref['bounce_email'] =  '';
    $allPref['add_names'] = '1';
    $allPref['mailer_method'] =  $conf->getValue('config.mailer');
    $allPref['encoding_format'] =  '8bit';
    $allPref['charset'] = 'UTF-8';
    $allPref['word_wrapping'] = '150';
    $allPref['hostname'] =  '';
    $allPref['embed_images'] = '0';
    $allPref['embed_files'] = '1';
    $allPref['editor'] = '0';
    $allPref['multiple_part'] =  '1';
    $allPref['sendmail_path'] =  $conf->getValue('config.sendmail');
    $smtpinfos = explode(':',$conf->getValue('config.smtphost'));
    $allPref['smtp_host'] =  $smtpinfos[0];
    $allPref['smtp_port'] =  '';
    if(isset($smtpinfos[1])) $allPref['smtp_port'] = $smtpinfos[1];
    $allPref['smtp_secured'] = $conf->getValue('config.smtpsecure');
    if(!in_array($allPref['smtp_secured'],array('tls','ssl'))) $allPref['smtp_secured'] = '';
    $allPref['smtp_auth'] =  $conf->getValue('config.smtpauth');
    $allPref['smtp_username'] =  $conf->getValue('config.smtpuser');
    $allPref['smtp_password'] =  $conf->getValue('config.smtppass');
    $allPref['smtp_keepalive'] =  '1';
    $allPref['queue_nbmail'] =  '40';
    $allPref['queue_nbmail_auto'] =  '70';
    $allPref['queue_type'] = 'auto';
    $allPref['queue_delay'] =  '3600';
    $allPref['queue_try'] = '3';
    $allPref['queue_pause'] = '120';
    $allPref['allow_visitor'] = '1';
    $allPref['require_confirmation'] =  '0';
    $allPref['priority_newsletter'] =  '3';
    $allPref['allowedfiles'] = 'zip,doc,docx,pdf,xls,txt,gzip,rar,jpg,gif,xlsx,pps,csv,bmp,epg,ico,odg,odp,ods,odt,png,ppt,swf,xcf';
    $allPref['uploadfolder'] = 'media/com_acymailing/upload';
    $allPref['confirm_redirect'] = '';
    $allPref['subscription_message'] =  '1';
    $allPref['notification_unsuball'] = '';
    $allPref['cron_next'] = '1251990901';
    $allPref['confirmation_message'] =  '1';
    $allPref['welcome_message'] = '1';
    $allPref['unsub_message'] = '1';
    $allPref['cron_last'] =  '0';
    $allPref['cron_fromip'] = '';
    $allPref['cron_report'] = '';
    $allPref['cron_frequency'] = '900';
    $allPref['cron_sendreport'] =  '2';
    $allPref['cron_sendto'] =  $conf->getValue('config.mailfrom');
    $allPref['cron_fullreport'] =  '1';
    $allPref['cron_savereport'] =  '2';
    $allPref['cron_savepath'] =  'media/com_acymailing/logs/report'.rand(0,999999999).'.log';
    $allPref['notification_created'] =  '';
    $allPref['notification_accept'] =  '';
    $allPref['notification_refuse'] = '';
    $allPref['forward'] =  '0';
    $descriptions = array('Joomla!™ Newsletter Extension','Joomla!™ Mailing Extension','Joomla!™ Newsletter System','Joomla!™ E-mail Marketing','Joomla!™ Marketing Campaign');
    $allPref['description_starter'] = $descriptions[rand(0,4)];
    $allPref['description_essential'] = $descriptions[rand(0,4)];
    $allPref['description_business'] = $descriptions[rand(0,4)];
    $allPref['description_enterprise'] = $descriptions[rand(0,4)];
    $allPref['priority_followup'] =  '2';
    $allPref['unsub_redirect'] =  '';
    $allPref['show_footer'] = '1';
    $allPref['use_sef'] =  '0';
    $allPref['itemid'] =  '0';
    $allPref['css_module'] = 'default';
    $allPref['css_frontend'] = 'default';
    $allPref['css_backend'] = 'default';
    $allPref['installcomplete'] = '0';
    $allPref['bounce_email_bounce'] = 'delete';
    $allPref['bounce_regex_bounce'] = 'deliver|daemon|fail|system|return|impos|unzustellbar';
    $allPref['bounce_action_bounce'] = 'unsub';
    $allPref['bounce_rules_bounce'] = 'Mailbox not accessible';
    $allPref['bounce_email_end'] = 'forward';
    $allPref['bounce_forward_end'] = $conf->getValue('config.mailfrom');
    $allPref['bounce_rules_end'] = 'Final Action';
    $allPref['Starter'] =  '0';
    $allPref['Essential'] =  '1';
    $allPref['Business'] =  '2';
    $allPref['Enterprise'] =  '3';
    $query = "INSERT IGNORE INTO `#__acymailing_config` (`namekey`,`value`) VALUES ";
    foreach($allPref as $namekey => $value){
      $query .= '('.$this->db->Quote($namekey).','.$this->db->Quote($value).'),';
    }
    $query = rtrim($query,',');
    $this->db->setQuery($query);
    $this->db->query();
  }
}