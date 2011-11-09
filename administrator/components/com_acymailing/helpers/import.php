<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class importHelper{
	var $importUserInLists = array();
	var $totalInserted = 0;
	var $totalTry = 0;
	var $totalValid = 0;
	var $allSubid = array();
	var $db;
	var $forceconfirm = false;
	var $charsetConvert;
	var $generatename = true;
	var $overwrite = false;
	var $removeSep = 0;
	var $tablename = '';
	var $equFields = array();
	function importHelper(){
		acymailing::increasePerf();
		$this->db =& JFactory::getDBO();
	}
	function database($onlyimport = false){
		$app =& JFactory::getApplication();
		$table = empty($this->tablename) ? JRequest::getCmd('tablename') : $this->tablename;
		if(empty($table)){
			$listTables = $this->db->getTableList();
			$app->enqueueMessage(JText::sprintf('SPECIFYTABLE',implode(' | ',$listTables)),'notice');
			return false;
		}
		$fields = reset($this->db->getTableFields($table));
		if(empty($fields)){
			$listTables = $this->db->getTableList();
			$app->enqueueMessage(JText::sprintf('SPECIFYTABLE',implode(' | ',$listTables)),'notice');
			return false;
		}
		$fields = array_keys($fields);
		$equivalentFields = empty($this->equFields) ? JRequest::getVar('fields',array()) : $this->equFields;
		if(empty($equivalentFields['email'])){
			$app->enqueueMessage(JText::_('SPECIFYFIELDEMAIL'),'notice');
			return false;
		}
		$select = array();
		foreach($equivalentFields as $acyField => $tableField){
			if(empty($tableField)) continue;
			if(!in_array($tableField,$fields)){
				$app->enqueueMessage(JText::sprintf('SPECIFYFIELD',$tableField,implode(' | ',$fields)),'notice');
				return false;
			}
			$select['`'.$acyField.'`'] = '`'.$tableField.'`';
		}
		if(empty($select['`created`'])){ $select['`created`'] = time(); }
		$this->db->setQuery('INSERT IGNORE INTO `#__acymailing_subscriber` ('.implode(' , ',array_keys($select)).') SELECT '.implode(' , ',$select).' FROM '.$table.' WHERE '.$select['`email`'].' LIKE \'%@%\'');
		$this->db->query();
		$affectedRows = $this->db->getAffectedRows();
		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$affectedRows));
		if($onlyimport) return true;
		$query = 'SELECT b.subid FROM '.$table.' as a LEFT JOIN '.acymailing::table('subscriber').' as b on a.'.$select['`email`'].' = b.`email` WHERE b.subid > 0';
		$this->db->setQuery($query);
		$this->allSubid = $this->db->loadResultArray();
		$this->_subscribeUsers();
		return true;
	}
	function textarea(){
		$this->forceconfirm = JRequest::getInt('import_confirmed_textarea');
		$this->generatename = JRequest::getInt('generatename_textarea');
		$this->overwrite = JRequest::getInt('overwriteexisting_textarea');
		$content = JRequest::getString('textareaentries');
		return $this->_handleContent($content);
	}
	function file(){
		$app =& JFactory::getApplication();
		$importFile =  JRequest::getVar( 'importfile', array(), 'files','array');
		if(empty($importFile['name'])){
			$app->enqueueMessage(JText::_('BROWSE_FILE'),'notice');
			return false;
		}
		$this->forceconfirm = JRequest::getInt('import_confirmed');
		$this->charsetConvert = JRequest::getString('charsetconvert','');
		$this->generatename = JRequest::getInt('generatename');
		$this->overwrite = JRequest::getInt('overwriteexisting');
		jimport('joomla.filesystem.file');
		$config =& acymailing::config();
		$allowedFiles = explode(',',strtolower($config->get('allowedfiles')));
		$uploadFolder = JPath::clean(html_entity_decode($config->get('uploadfolder')));
		$uploadFolder = trim($uploadFolder,DS.' ').DS;
		$uploadPath = JPath::clean(ACYMAILING_ROOT.$uploadFolder);
		acymailing::createDir($uploadPath);
		if(!is_writable($uploadPath)){
			@chmod($uploadPath,'0755');
			if(!is_writable($uploadPath)){
				$app->enqueueMessage(JText::sprintf( 'WRITABLE_FOLDER',$uploadPath), 'notice');
			}
		}
		$attachment = null;
		$attachment->filename = strtolower(JFile::makeSafe($importFile['name']));
		$attachment->size = $importFile['size'];
		$attachment->extension = strtolower(substr($attachment->filename,strrpos($attachment->filename,'.')+1));
		if(!in_array($attachment->extension,$allowedFiles)){
			$app->enqueueMessage(JText::sprintf( 'ACCEPTED_TYPE',$attachment->extension,$config->get('allowedfiles')), 'notice');
			return false;
		}
		if(!JFile::upload($importFile['tmp_name'], $uploadPath . $attachment->filename)){
			if ( !move_uploaded_file($importFile['tmp_name'], $uploadPath . $attachment->filename)) {
				$app->enqueueMessage(JText::sprintf( 'FAIL_UPLOAD',$importFile['tmp_name'],$uploadPath . $attachment->filename), 'error');
			}
		}
		$contentFile = file_get_contents($uploadPath . $attachment->filename);
		if(!$contentFile){
			$app->enqueueMessage(JText::sprintf( 'FAIL_OPEN',$uploadPath . $attachment->filename), 'error');
			return false;
		};
		unlink($uploadPath . $attachment->filename);
		return $this->_handleContent($contentFile);
	}
	function _handleContent(&$contentFile){
		$success = true;
		$app =& JFactory::getApplication();
		$contentFile = str_replace(array("\r\n","\r"),"\n",$contentFile);
		$importLines = explode("\n", $contentFile);
		$i = 0;
		while(empty($this->header)){
			$this->header = trim($importLines[$i]);
			$i++;
		}
		if(!$this->_autoDetectHeader()){
			$app->enqueueMessage(JText::sprintf('IMPORT_HEADER',$this->header),'error');
			$app->enqueueMessage(JText::_('IMPORT_EMAIL'),'error');
			$app->enqueueMessage(JText::_('IMPORT_EXAMPLE'),'error');
			return false;
		}
		$numberColumns = count($this->columns);
		$userHelper = acymailing::get('helper.user');
		$importUsers = array();
		$encodingHelper = acymailing::get('helper.encoding');
		while (isset($importLines[$i])) {
			$data = explode($this->separator,trim($importLines[$i]));
			if(!empty($this->removeSep)){
				for($b = $numberColumns+$this->removeSep-1;$b >= $numberColumns;$b-- ){
					if(isset($data[$b]) AND strlen($data[$b])==0){
						unset($data[$b]);
					}
				}
			}
			$i++;
			if(empty($importLines[$i-1])) continue;
			$this->totalTry++;
			if(count($data) > $numberColumns){
				$copy = $data;
				foreach($copy as $oneelem => $oneval){
					if($oneval[0] == '"' AND $oneval[strlen($oneval)-1] != '"' AND isset($copy[$oneelem+1]) AND $copy[$oneelem+1][strlen($copy[$oneelem+1])-1] == '"'){
						$data[$oneelem] = $copy[$oneelem].$this->separator.$copy[$oneelem+1];
						unset($data[$oneelem+1]);
					}
				}
				$data = array_values($data);
			}
			if(count($data) != $numberColumns){
				$success = false;
				static $errorcount = 0;
				if(empty($errorcount)){
					$app->enqueueMessage(JText::sprintf('IMPORT_ARGUMENTS',$numberColumns),'error');
				}
				$errorcount++;
				if($errorcount<20){
					$app->enqueueMessage(JText::sprintf('IMPORT_ERRORLINE',$importLines[$i-1]),'notice');
				}elseif($errorcount == 20){
					$app->enqueueMessage('...','notice');
				}
				if($this->totalTry == 1) return false;
				continue;
			}
			$newUser = null;
			foreach($data as $num => $value){
				$field = $this->columns[$num];
				if($field == 'listids'){
					$liststosub = explode('-',trim($value,'\'" '));
					foreach($liststosub as $onelistid){
						$this->importUserInLists[$onelistid][] = $this->db->Quote($newUser->email);
					}
					continue;
				}
				$newUser->$field = trim($value,'\'" ');
				if(!empty($this->charsetConvert)){
					$newUser->$field = $encodingHelper->change($newUser->$field,$this->charsetConvert,'UTF-8');
				}
			}
			$newUser->email = trim(str_replace(array(' ',"\t"),'',$encodingHelper->change($newUser->email,'UTF-8','ISO-8859-1')));
			if(!$userHelper->validEmail($newUser->email)){
				$success = false;
				static $errorcountfail = 0;
				$errorcountfail++;
				if($errorcountfail<20){
					$app->enqueueMessage(JText::sprintf('NOT_VALID_EMAIL',$newUser->email).' | '.($i-1).' : '.$importLines[$i-1],'notice');
				}elseif($errorcountfail == 20){
					$app->enqueueMessage('...','notice');
				}
				continue;
			}
			$this->_checkData($newUser);
			$importUsers[] = $newUser;
			$this->totalValid++;
			if( $this->totalValid%50 == 0){
				$this->_insertUsers($importUsers);
				$importUsers = array();
			}
		}
		$this->_insertUsers($importUsers);
		$app->enqueueMessage(JText::sprintf('IMPORT_REPORT',$this->totalTry,$this->totalInserted,$this->totalTry - $this->totalValid,$this->totalValid - $this->totalInserted));
		$this->_subscribeUsers();
		return $success;
	}
	function _subscribeUsers(){
		$app =& JFactory::getApplication();
		if(empty($this->allSubid)) return true;
		$subdate = time();
		$listClass= acymailing::get('class.list');
		if(empty($this->importUserInLists)){
			$lists = JRequest::getVar('importlists',array());
			$listsSubscribe = array();
			foreach($lists as $listid => $val){
				if(!empty($val)){
					$nbsubscribed = 0;
					$listid = (int) $listid;
					$query = 'INSERT IGNORE INTO '.acymailing::table('listsub').' (listid,subid,subdate,status) VALUES ';
					$b = 0;
					foreach($this->allSubid as $subid){
						$b++;
						if($b>200){
							$query = rtrim($query,',');
							$this->db->setQuery($query);
							$this->db->query();
							$nbsubscribed += $this->db->getAffectedRows();
							$b = 0;
							$query = 'INSERT IGNORE INTO '.acymailing::table('listsub').' (listid,subid,subdate,status) VALUES ';
						}
						$query .= "($listid,$subid,$subdate,1),";
					}
					$query = rtrim($query,',');
					$this->db->setQuery($query);
					$this->db->query();
					$nbsubscribed += $this->db->getAffectedRows();
					$myList = $listClass->get($listid);
					$app->enqueueMessage(JText::sprintf('IMPORT_SUBSCRIBE_CONFIRMATION',$nbsubscribed,$myList->name));
				}
			}
		}else{
			foreach($this->importUserInLists as $listid => $arrayEmails){
				if(!empty($listid)){
					$listid = (int) $listid;
					$query = 'INSERT IGNORE INTO '.acymailing::table('listsub').' (listid,subid,subdate,status) ';
					$query .= "SELECT $listid,`subid`,$subdate,1 FROM ".acymailing::table('subscriber')." WHERE `email` IN (";
					$query .= implode(',',$arrayEmails).')';
					$this->db->setQuery($query);
					$this->db->query();
					$nbsubscribed = $this->db->getAffectedRows();
					$myList = $listClass->get($listid);
					$app->enqueueMessage(JText::sprintf('IMPORT_SUBSCRIBE_CONFIRMATION',$nbsubscribed,$myList->name));
				}
			}
		}
		return true;
	}
	function _insertUsers($users){
		if(empty($users)) return true;
		if($this->overwrite){
			$emailstoload = array();
			foreach($users as $a => $oneUser){
				$emailstoload[] = $this->db->Quote($oneUser->email);
			}
			$this->db->setQuery('SELECT `subid`,`email`,`created` FROM `#__acymailing_subscriber` WHERE `email` IN ('.implode(',',$emailstoload).')');
			$subids = $this->db->loadObjectList('email');
			foreach($users as $a => $oneUser){
				$users[$a]->subid = (!empty($subids[$oneUser->email]->subid)) ? $subids[$oneUser->email]->subid : 'NULL';
				if(!empty($subids[$oneUser->email]->created)) $users[$a]->created = $subids[$oneUser->email]->created;
				if(isset($subids[$oneUser->email]->accept)) $users[$a]->accept = $subids[$oneUser->email]->accept;
			}
			$this->totalInserted -= (count($subids)*2);
		}
		$columns = reset($users);
		$query = $this->overwrite ? 'REPLACE' : 'INSERT IGNORE';
		$query .= ' INTO '.acymailing::table('subscriber').' (`'.implode('`,`',array_keys(get_object_vars($columns))).'`) VALUES (';
		$values = array();
		$allemails = array();
		foreach($users as $a => $oneUser){
			$value = array();
			foreach($oneUser as $map => $oneValue){
				if($map == 'created' AND !is_numeric($oneValue)){
					$oneValue = strtotime($oneValue);
				}
				if($map != 'subid'){
					$value[] = $this->db->Quote($oneValue);
				}else{
					$value[] = $oneValue;
				}
				if($map == 'email'){
					$allemails[] = $this->db->Quote($oneValue);
				}
			}
			$values[] = implode(',',$value);
		}
		$query .= implode('),(',$values).')';
		$this->db->setQuery($query);
		$this->db->query();
		$this->totalInserted += $this->db->getAffectedRows();
		$this->db->setQuery('SELECT subid FROM '.acymailing::table('subscriber').' WHERE email IN ('.implode(',',$allemails).')');
		$this->allSubid = array_merge($this->allSubid,$this->db->loadResultArray());
		return true;
	}
	function _checkData(&$user){
		if(empty($user->created)) $user->created = time();
		elseif(!is_numeric($user->created)) $user->created = strtotime($user->created);
		if(!isset($user->accept)) $user->accept = 1;
		if(empty($user->name) AND $this->generatename) $user->name = ucwords(str_replace(array('.','_','-'),' ',substr($user->email,0,strpos($user->email,'@'))));
		unset($user->subid); unset($user->userid);
		if(!isset($user->confirmed) AND $this->forceconfirm) $user->confirmed = 1;
		if(empty($user->key)) $user->key = md5(substr($user->email,0,strpos($user->email,'@')).rand(0,10000000));
	}
	function _autoDetectHeader(){
		$app =& JFactory::getApplication();
		$this->separator = ',';
		$this->header = str_replace("\xEF\xBB\xBF","",$this->header);
		$listSeparators = array("\t",';',',');
		foreach($listSeparators as $sep){
			if(strpos($this->header,$sep) !== false){
				$this->separator = $sep;
				break;
			}
		}
		$this->columns = explode($this->separator,$this->header);
		for($i=count($this->columns)-1;$i>=0;$i--){
			if(strlen($this->columns[$i]) == 0){
				unset($this->columns[$i]);
				$this->removeSep++;
			}
		}
		$columnsTable = $this->db->getTableFields(acymailing::table('subscriber'));
		$columns = reset($columnsTable);
		foreach($this->columns as $i => $oneColumn){
			$this->columns[$i] = strtolower(trim($oneColumn,'\'" '));
			if($this->columns[$i] == 'listids') continue;
			if(!isset($columns[$this->columns[$i]])){
				$app->enqueueMessage(JText::sprintf('IMPORT_ERROR_FIELD',$this->columns[$i],implode(' | ',array_diff(array_keys($columns),array('subid','userid','key')))),'error');
				return false;
			}
		}
		if(!in_array('email',$this->columns)) return false;
		return true;
	}
	function joomla(){
		$app =& JFactory::getApplication();
		$query = 'UPDATE IGNORE '.acymailing::table('users',false).' as b, '.acymailing::table('subscriber').' as a SET a.email = b.email, a.name = b.name, a.enabled = 1 - b.block WHERE a.userid = b.id AND a.userid > 0';
		$this->db->setQuery($query);
		$this->db->query();
		$nbUpdated = $this->db->getAffectedRows();
		$query = 'UPDATE IGNORE '.acymailing::table('users',false).' as b, '.acymailing::table('subscriber').' as a SET a.userid = b.id WHERE a.email = b.email';
		$this->db->setQuery($query);
		$this->db->query();
		$nbUpdated += $this->db->getAffectedRows();
		$app->enqueueMessage(JText::sprintf('IMPORT_UPDATE',$nbUpdated));
		$query = 'SELECT subid FROM '.acymailing::table('subscriber').' as a LEFT JOIN '.acymailing::table('users',false).' as b on a.userid = b.id WHERE b.id IS NULL AND a.userid > 0';
		$this->db->setQuery($query);
		$deletedSubid = $this->db->loadResultArray();
		$query = 'SELECT subid FROM '.acymailing::table('subscriber').' as a LEFT JOIN '.acymailing::table('users',false).' as b on a.email = b.email WHERE b.id IS NULL AND a.userid > 0';
		$this->db->setQuery($query);
		$deletedSubid = array_merge($this->db->loadResultArray(),$deletedSubid);
		if(!empty($deletedSubid)){
			$userClass = acymailing::get('class.subscriber');
			$deletedUsers = $userClass->delete($deletedSubid);
			$app->enqueueMessage(JText::sprintf('IMPORT_DELETE',$deletedUsers));
		}
		$time = time();
		$query = 'INSERT IGNORE INTO '.acymailing::table('subscriber').' (`email`,`name`,`confirmed`,`userid`,`created`,`enabled`,`accept`,`html`) SELECT `email`,`name`,1-`block`,`id`,UNIX_TIMESTAMP(`registerDate`),1-`block`,1,1 FROM '.acymailing::table('users',false);
		$this->db->setQuery($query);
		$this->db->query();
		$insertedUsers = $this->db->getAffectedRows();
		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$insertedUsers));
		$lists = JRequest::getVar('importlists',array());
		$listsSubscribe = array();
		foreach($lists as $listid => $val){
			if(!empty($val)) $listsSubscribe[] = (int) $listid;
		}
		if(empty($listsSubscribe)) return true;
		$query = 'INSERT IGNORE INTO '.acymailing::table('listsub').' (`listid`,`subid`,`subdate`,`status`) ';
		$query.= 'SELECT a.`listid`, b.`subid` ,'.$time.',1 FROM '.acymailing::table('list').' as a, '.acymailing::table('subscriber').' as b  WHERE a.`listid` IN ('.implode(',',$listsSubscribe).') AND b.`userid` > 0';
		$this->db->setQuery($query);
		$this->db->query();
		$nbsubscribed = $this->db->getAffectedRows();
		$app->enqueueMessage(JText::sprintf('IMPORT_SUBSCRIPTION',$nbsubscribed));
		return true;
	}
	function acajoom(){
		$app =& JFactory::getApplication();
		$time = time();
		$query = 'INSERT IGNORE INTO '.acymailing::table('subscriber').' (email,name,confirmed,created,enabled,accept,html) SELECT email,name,confirmed,UNIX_TIMESTAMP(`subscribe_date`),1-blacklist,1,receive_html FROM '.acymailing::table('acajoom_subscribers',false);
		$this->db->setQuery($query);
		$this->db->query();
		$insertedUsers = $this->db->getAffectedRows();
		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$insertedUsers));
		if(JRequest::getInt('acajoom_lists',0) == 1) $this->_importAcajoomLists();
		$query = 'SELECT b.subid FROM '.acymailing::table('acajoom_subscribers',false).' as a LEFT JOIN '.acymailing::table('subscriber').' as b on a.email = b.email WHERE b.subid > 0';
		$this->db->setQuery($query);
		$this->allSubid = $this->db->loadResultArray();
		$this->_subscribeUsers();
		return true;
	}
	function _importYancLists(){
		$app =& JFactory::getApplication();
		$query = 'SELECT `id`, `name`, `description`, `state` as `published` FROM `#__yanc_letters`';
		$this->db->setQuery($query);
		$yancLists = $this->db->loadObjectList('id');
		$user =& JFactory::getUser();
		$query = 'SELECT `listid`, `alias` FROM '.acymailing::table('list').' WHERE `alias` IN (\'yanclist'.implode('\',\'yanclist',array_keys($yancLists)).'\')';
		$this->db->setQuery($query);
		$joomLists = $this->db->loadObjectList('alias');
		$listClass = acymailing::get('class.list');
		$time = time();
		foreach($yancLists as $oneList){
			$oneList->alias = 'yanclist'.$oneList->id;
			$oneList->userid = $user->id;
			$yancListId = $oneList->id;
			if(isset($joomLists[$oneList->alias])){
				$joomListId = $joomLists[$oneList->alias]->listid;
			}else{
				unset($oneList->id);
				$joomListId = $listClass->save($oneList);
				$app->enqueueMessage(JText::sprintf('IMPORT_LIST',$oneList->name));
			}
			$querySelect = 'SELECT DISTINCT c.subid,'.$joomListId.','.$time.',1 FROM `#__yanc_subscribers` as a ';
			$querySelect .= 'LEFT JOIN '.acymailing::table('subscriber').' as c on a.email = c.email ';
			$querySelect .= 'WHERE a.lid = '.$yancListId.' AND a.state = 1 AND c.subid > 0';
			$queryInsert = 'INSERT IGNORE INTO '.acymailing::table('listsub').' (subid,listid,subdate,status) ';
			$this->db->setQuery($queryInsert.$querySelect);
			$this->db->query();
			$app->enqueueMessage(JText::sprintf('IMPORT_SUBSCRIBE_CONFIRMATION',$this->db->getAffectedRows(),$oneList->name));
		}
		return true;
	}
	function _importjnewsLists(){
		$app =& JFactory::getApplication();
		$query = 'SELECT `id`, `list_name` as `name`, `hidden` as `visible`, `list_desc` as `description`, `published`, `owner` as `userid` FROM '.acymailing::table('jnews_lists',false);
		$this->db->setQuery($query);
		$jnewsLists = $this->db->loadObjectList('id');
		$query = 'SELECT `listid`, `alias` FROM '.acymailing::table('list').' WHERE `alias` IN (\'jnewslist'.implode('\',\'jnewslist',array_keys($jnewsLists)).'\')';
		$this->db->setQuery($query);
		$joomLists = $this->db->loadObjectList('alias');
		$listClass = acymailing::get('class.list');
		foreach($jnewsLists as $oneList){
			$oneList->alias = 'jnewslist'.$oneList->id;
			$jnewsListId = $oneList->id;
			if(isset($joomLists[$oneList->alias])){
				$joomListId = $joomLists[$oneList->alias]->listid;
			}else{
				unset($oneList->id);
				$joomListId = $listClass->save($oneList);
				$app->enqueueMessage(JText::sprintf('IMPORT_LIST',$oneList->name));
			}
			$querySelect = 'SELECT DISTINCT c.subid,'.$joomListId.',a.subdate,a.unsubdate,1-(2*a.unsubscribe) FROM '.acymailing::table('jnews_listssubscribers',false).' as a ';
			$querySelect .= 'LEFT JOIN '.acymailing::table('jnews_subscribers',false).' as b on a.subscriber_id = b.id ';
			$querySelect .= 'LEFT JOIN '.acymailing::table('subscriber').' as c on b.email = c.email ';
			$querySelect .= 'WHERE a.list_id = '.$jnewsListId.' AND c.subid > 0';
			$queryInsert = 'INSERT IGNORE INTO '.acymailing::table('listsub').' (subid,listid,subdate,unsubdate,status) ';
			$this->db->setQuery($queryInsert.$querySelect);
			$this->db->query();
			$app->enqueueMessage(JText::sprintf('IMPORT_SUBSCRIBE_CONFIRMATION',$this->db->getAffectedRows(),$oneList->name));
		}
		return true;
	}
	function _importAcajoomLists(){
		$app =& JFactory::getApplication();
		$query = 'SELECT `id`, `list_name` as `name`, `hidden` as `visible`, `list_desc` as `description`, `published`, `owner` as `userid` FROM '.acymailing::table('acajoom_lists',false);
		$this->db->setQuery($query);
		$acaLists = $this->db->loadObjectList('id');
		$query = 'SELECT `listid`, `alias` FROM '.acymailing::table('list').' WHERE `alias` IN (\'acajoomlist'.implode('\',\'acajoomlist',array_keys($acaLists)).'\')';
		$this->db->setQuery($query);
		$joomLists = $this->db->loadObjectList('alias');
		$listClass = acymailing::get('class.list');
		$time = time();
		foreach($acaLists as $oneList){
			$oneList->alias = 'acajoomlist'.$oneList->id;
			$acaListId = $oneList->id;
			if(isset($joomLists[$oneList->alias])){
				$joomListId = $joomLists[$oneList->alias]->listid;
			}else{
				unset($oneList->id);
				$joomListId = $listClass->save($oneList);
				$app->enqueueMessage(JText::sprintf('IMPORT_LIST',$oneList->name));
			}
			$querySelect = 'SELECT DISTINCT c.subid,'.$joomListId.','.$time.',1 FROM '.acymailing::table('acajoom_queue',false).' as a ';
			$querySelect .= 'LEFT JOIN '.acymailing::table('acajoom_subscribers',false).' as b on a.subscriber_id = b.id ';
			$querySelect .= 'LEFT JOIN '.acymailing::table('subscriber').' as c on b.email = c.email ';
			$querySelect .= 'WHERE a.list_id = '.$acaListId.' AND c.subid > 0';
			$queryInsert = 'INSERT IGNORE INTO '.acymailing::table('listsub').' (subid,listid,subdate,status) ';
			$this->db->setQuery($queryInsert.$querySelect);
			$this->db->query();
			$app->enqueueMessage(JText::sprintf('IMPORT_SUBSCRIBE_CONFIRMATION',$this->db->getAffectedRows(),$oneList->name));
		}
		return true;
	}
	function letterman(){
		$app =& JFactory::getApplication();
		$time = time();
		$query = 'INSERT IGNORE INTO '.acymailing::table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `subscriber_email`,`subscriber_name`,`confirmed`,UNIX_TIMESTAMP(`subscribe_date`),1,1,1 FROM '.acymailing::table('letterman_subscribers',false);
		$this->db->setQuery($query);
		$this->db->query();
		$insertedUsers = $this->db->getAffectedRows();
		if($insertedUsers == -1){
			$query = 'INSERT IGNORE INTO '.acymailing::table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `email`,`name`,`confirmed`,'.$time.',1,1,1 FROM '.acymailing::table('letterman_subscribers',false);
			$this->db->setQuery($query);
			$this->db->query();
			$insertedUsers = $this->db->getAffectedRows();
			$query = 'SELECT b.subid FROM '.acymailing::table('letterman_subscribers',false).' as a LEFT JOIN '.acymailing::table('subscriber').' as b on a.email = b.email WHERE b.subid > 0';
			$this->db->setQuery($query);
		}else{
			$query = 'SELECT b.subid FROM '.acymailing::table('letterman_subscribers',false).' as a LEFT JOIN '.acymailing::table('subscriber').' as b on a.subscriber_email = b.email WHERE b.subid > 0';
			$this->db->setQuery($query);
		}
		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$insertedUsers));
		$this->allSubid = $this->db->loadResultArray();
		$this->_subscribeUsers();
		return true;
	}
	function yanc(){
		$app =& JFactory::getApplication();
		$time = time();
		$query = 'INSERT IGNORE INTO '.acymailing::table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`, `ip`) SELECT `email`,`name`,`confirmed`,UNIX_TIMESTAMP(`date`),`state`,1,`html`,`ip` FROM '.acymailing::table('yanc_subscribers',false);
		$this->db->setQuery($query);
		$this->db->query();
		$insertedUsers = $this->db->getAffectedRows();
		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$insertedUsers));
		if(JRequest::getInt('yanc_lists',0) == 1) $this->_importYancLists();
		$query = 'SELECT b.subid FROM '.acymailing::table('yanc_subscribers',false).' as a LEFT JOIN '.acymailing::table('subscriber').' as b on a.email = b.email WHERE b.subid > 0';
		$this->db->setQuery($query);
		$this->allSubid = $this->db->loadResultArray();
		$this->_subscribeUsers();
		return true;
	}
	function vemod(){
		$app =& JFactory::getApplication();
		$time = time();
		$query = 'INSERT IGNORE INTO '.acymailing::table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `email`,`name`,1,'.$time.',1,1,`mailformat` FROM `#__vemod_news_mailer_users` ';
		$this->db->setQuery($query);
		$this->db->query();
		$insertedUsers = $this->db->getAffectedRows();
		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$insertedUsers));
		$query = 'SELECT b.subid FROM `#__vemod_news_mailer_users` as a LEFT JOIN '.acymailing::table('subscriber').' as b on a.email = b.email WHERE b.subid > 0';
		$this->db->setQuery($query);
		$this->allSubid = $this->db->loadResultArray();
		$this->_subscribeUsers();
		return true;
	}
	function contact(){
		$app =& JFactory::getApplication();
		$time = time();
		$query = 'INSERT IGNORE INTO '.acymailing::table('subscriber')." (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `email_to`,`name`,1,'.$time.',1,1,1 FROM `#__contact_details` WHERE email_to LIKE '%@%'";
		$this->db->setQuery($query);
		$this->db->query();
		$insertedUsers = $this->db->getAffectedRows();
		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$insertedUsers));
		$query = 'SELECT b.subid FROM `#__contact_details` as a LEFT JOIN '.acymailing::table('subscriber').' as b on a.email_to = b.email WHERE b.subid > 0';
		$this->db->setQuery($query);
		$this->allSubid = $this->db->loadResultArray();
		$this->_subscribeUsers();
		return true;
	}
	function ccnewsletter(){
		$app =& JFactory::getApplication();
		$time = time();
		$query = 'INSERT IGNORE INTO '.acymailing::table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `email`,`name`,`enabled`,UNIX_TIMESTAMP(`sdate`),`enabled`,1,1-`plainText` FROM '.acymailing::table('ccnewsletter_subscribers',false);
		$this->db->setQuery($query);
		$this->db->query();
		$insertedUsers = $this->db->getAffectedRows();
		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$insertedUsers));
		$query = 'SELECT b.subid FROM '.acymailing::table('ccnewsletter_subscribers',false).' as a LEFT JOIN '.acymailing::table('subscriber').' as b on a.email = b.email WHERE b.subid > 0';
		$this->db->setQuery($query);
		$this->allSubid = $this->db->loadResultArray();
		$this->_subscribeUsers();
		return true;
	}
	function jnews(){
		$app =& JFactory::getApplication();
		$time = time();
		$query = 'INSERT IGNORE INTO '.acymailing::table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `email`,`name`,`confirmed`,`subscribe_date`, 1-`blacklist`,1,`receive_html` FROM '.acymailing::table('jnews_subscribers',false);
		$this->db->setQuery($query);
		$this->db->query();
		$insertedUsers = $this->db->getAffectedRows();
		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$insertedUsers));
		if(JRequest::getInt('jnews_lists',0) == 1) $this->_importjnewsLists();
		$query = 'SELECT b.subid FROM '.acymailing::table('jnews_subscribers',false).' as a LEFT JOIN '.acymailing::table('subscriber').' as b on a.email = b.email WHERE b.subid > 0';
		$this->db->setQuery($query);
		$this->allSubid = $this->db->loadResultArray();
		$this->_subscribeUsers();
		return true;
	}
	function communicator(){
		$app =& JFactory::getApplication();
		$time = time();
		$query = 'INSERT IGNORE INTO '.acymailing::table('subscriber').' (`email`,`name`,`confirmed`,`created`,`enabled`,`accept`,`html`) SELECT `subscriber_email`,`subscriber_name`,`confirmed`,'.$time.',1,1,1 FROM '.acymailing::table('communicator_subscribers',false);
		$this->db->setQuery($query);
		$this->db->query();
		$insertedUsers = $this->db->getAffectedRows();
		$app->enqueueMessage(JText::sprintf('IMPORT_NEW',$insertedUsers));
		$query = 'SELECT b.subid FROM '.acymailing::table('communicator_subscribers',false).' as a LEFT JOIN '.acymailing::table('subscriber').' as b on a.subscriber_email = b.email WHERE b.subid > 0';
		$this->db->setQuery($query);
		$this->allSubid = $this->db->loadResultArray();
		$this->_subscribeUsers();
		return true;
	}
}
