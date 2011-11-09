<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
jimport('joomla.application.component.controller');
jimport( 'joomla.application.component.view');
define('ACYMAILING_COMPONENT','com_acymailing');
define('ACYMAILING_LIVE',rtrim(str_replace('https:','http:',JURI::root()),'/').'/');
define('ACYMAILING_ROOT',rtrim(JPATH_ROOT,DS).DS);
define('ACYMAILING_FRONT',rtrim(JPATH_SITE,DS).DS.'components'.DS.ACYMAILING_COMPONENT.DS);
define('ACYMAILING_BACK',rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.ACYMAILING_COMPONENT.DS);
define('ACYMAILING_HELPER',ACYMAILING_BACK.'helpers'.DS);
define('ACYMAILING_BUTTON',ACYMAILING_BACK.'buttons');
define('ACYMAILING_CLASS',ACYMAILING_BACK.'classes'.DS);
define('ACYMAILING_TYPE',ACYMAILING_BACK.'types'.DS);
define('ACYMAILING_CONTROLLER',ACYMAILING_BACK.'controllers'.DS);
define('ACYMAILING_CONTROLLER_FRONT',ACYMAILING_FRONT.'controllers'.DS);
$app =& JFactory::getApplication();
if($app->isAdmin()){
	define('ACYMAILING_IMAGES','../media/'.ACYMAILING_COMPONENT.'/images/');
	define('ACYMAILING_CSS','../media/'.ACYMAILING_COMPONENT.'/css/');
	define('ACYMAILING_JS','../media/'.ACYMAILING_COMPONENT.'/js/');
}else{
	define('ACYMAILING_IMAGES',JURI::base(true).'/media/'.ACYMAILING_COMPONENT.'/images/');
	define('ACYMAILING_CSS',JURI::base(true).'/media/'.ACYMAILING_COMPONENT.'/css/');
	define('ACYMAILING_JS',JURI::base(true).'/media/'.ACYMAILING_COMPONENT.'/js/');
}
define('ACYMAILING_DBPREFIX','#__acymailing_');
define('ACYMAILING_NAME','AcyMailing');
define('ACYMAILING_MEDIA',ACYMAILING_ROOT.'media'.DS.ACYMAILING_COMPONENT.DS);
define('ACYMAILING_TEMPLATE',ACYMAILING_MEDIA.'templates'.DS);
define('ACYMAILING_UPDATEURL','http://www.acyba.com/index.php?option=com_doc&gtask=update&task=');
define('ACYMAILING_HELPURL','http://www.acyba.com/index.php?option=com_doc&gtask=doc&component='.ACYMAILING_NAME.'&page=');
class acymailing{
	function getDate($time = 0,$format = '%d %B %Y %H:%M'){
		if(empty($time)) return '';
		static $timeoffset = null;
		if($timeoffset === null){
			$config =& JFactory::getConfig();
			$timeoffset = $config->getValue('config.offset');
		}
		if(version_compare(JVERSION,'1.6.0','>=')) $format = str_replace(array('%A','%d','%B','%m','%Y','%y','%H','%M','%S'),array('l','d','F','m','Y','y','H','i','s'),$format);
		if(is_numeric($format)) $format = JText::_('DATE_FORMAT_LC'.$format);
		return JHTML::_('date',$time- date('Z'),$format,$timeoffset);
	}
	function isAllowed($allowedGroups,$groups = null){
		if($allowedGroups == 'all') return true;
		if($allowedGroups == 'none') return false;
		$my = JFactory::getUser();
		if(empty($groups) AND empty($my->id)) return false;
		if(empty($groups)){
			if(version_compare(JVERSION,'1.6.0','<')){
				$groups = $my->gid;
			}else{
				$groups = JAccess::getGroupsByUser($my->id);
			}
		}
		if(!is_array($allowedGroups)) $allowedGroups = explode(',',trim($allowedGroups,','));
		if(is_array($groups)){
			$inter = array_intersect($groups,$allowedGroups);
			if(empty($inter)) return false;
			return true;
		}else{
			return in_array($groups,$allowedGroups);
		}
	}
	function getTime($date){
		static $timeoffset = null;
		if($timeoffset === null){
			$config =& JFactory::getConfig();
			$timeoffset = $config->getValue('config.offset');
		}
		return strtotime($date) - $timeoffset *60*60 + date('Z');
	}
	function createDir($dir,$report = true){
		if(is_dir($dir)) return true;
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$indexhtml = '<html><body bgcolor="#FFFFFF"></body></html>';
		if(!JFolder::create($dir)){
			if($report) acymailing::display('Could not create the directly '.$dir,'error');
			return false;
		}
		if(!JFile::write($dir.DS.'index.html',$indexhtml)){
			if($report) acymailing::display('Could not create the file '.$dir.DS.'index.html','error');
		}
		return true;
	}
	function getUpgradeLink($tolevel){
		$config =& acymailing::config();
		return ' <a class="acyupgradelink" href="http://www.acyba.com/index.php?option=com_doc&gtask=redirect&upgrade='.$config->get('level').'-to-'.$tolevel.'" target="_blank">'.JText::_('ONLY_FROM_'.strtoupper($tolevel)).'</a>';
	}
	function replaceDate($mydate){
		if(strpos($mydate,'{time}') !== false){
			$mydate = str_replace('{time}',time(),$mydate);
			$operators = array('+','-');
			foreach($operators as $oneOperator){
				if(!strpos($mydate,$oneOperator)) continue;
				list($part1,$part2) = explode($oneOperator,$mydate);
				if($oneOperator == '+'){
					$mydate = trim($part1) + trim($part2);
				}elseif($oneOperator == '-'){
					$mydate = trim($part1) - trim($part2);
				}
			}
		}
		return $mydate;
	}
	function initJSStrings($includejs = 'header',$params = null){
		static $i = 0;
		if(empty($i)){
			$i++;
			$doc =& JFactory::getDocument();
			if(method_exists($params,'get')){
				$nameCaption = addslashes($params->get('nametext'));
				$emailCaption = addslashes($params->get('emailtext'));
			}
			if(empty($nameCaption)) $nameCaption = JText::_('NAMECAPTION',true);
			if(empty($emailCaption)) $emailCaption = JText::_('EMAILCAPTION',true);
			$js = "<!--
					var acymailing = Array();
					acymailing['NAMECAPTION'] = '".$nameCaption."';
					acymailing['NAME_MISSING'] = '".JText::_('NAME_MISSING',true)."';
					acymailing['EMAILCAPTION'] = '".$emailCaption."';
					acymailing['VALID_EMAIL'] = '".JText::_('VALID_EMAIL',true)."';
					acymailing['ACCEPT_TERMS'] = '".JText::_('ACCEPT_TERMS',true)."';
					acymailing['CAPTCHA_MISSING'] = '".JText::_('ERROR_CAPTCHA',true)."';
					acymailing['NO_LIST_SELECTED'] = '".JText::_('NO_LIST_SELECTED',true)."';
			//-->";
			if($includejs == 'header'){
				$doc->addScriptDeclaration( $js );
			}else{
				echo "<script type=\"text/javascript\">
						$js
						</script>";
			}
		}
	}
	function absoluteURL($text){
		static $mainurl = '';
		if(empty($mainurl)){
			$urls = parse_url(ACYMAILING_LIVE);
			if(!empty($urls['path'])){
				$mainurl = substr(ACYMAILING_LIVE,0,strrpos(ACYMAILING_LIVE,$urls['path'])).'/';
			}else{
				$mainurl = ACYMAILING_LIVE;
			}
		}
		$text = str_replace(array('href="../undefined/','href="../../undefined/','href="../../../undefined//','href="undefined/'),array('href="'.$mainurl,'href="'.$mainurl,'href="'.$mainurl,'href="'.ACYMAILING_LIVE),$text);
		$replace = array();
		$replaceBy = array();
		if($mainurl !== ACYMAILING_LIVE){
			$replace[] = '#(href|src|action|background)[ ]*=[ ]*\"(?!([a-z]{3,7}://|\{|%7B|\#|mailto:|skype:|/))(?:\.\./)#i';
			$replaceBy[] = '$1="'.substr(ACYMAILING_LIVE,0,strrpos(rtrim(ACYMAILING_LIVE,'/'),'/')+1);
		}
		$replace[] = '#(href|src|action|background)[ ]*=[ ]*\"(?!([a-z]{3,7}://|\{|%7B|\#|mailto:|skype:|/))(?:\.\./|\./)?#i';
		$replaceBy[] = '$1="'.ACYMAILING_LIVE;
		$replace[] = '#(href|src|action|background)[ ]*=[ ]*\"(?!([a-z]{3,7}://|\{|%7B|\#|mailto:|skype:))/#i';
		$replaceBy[] = '$1="'.$mainurl;
		$replace[] = '#(background-image[ ]*:[ ]*url\(\'?(?!([a-z]{3,7}://|/|\'))(?:\.\./|\./)?)#i';
		$replaceBy[] = '$1'.ACYMAILING_LIVE;
		return preg_replace($replace,$replaceBy,$text);
	}
	function setTitle($name,$picture,$link){
		JToolBarHelper::title( '<a href="'.acymailing::completeLink($link).'">'.$name.'</a>' , $picture.'.png' );
	}
	function frontendLink($link,$popup = false){
		if($popup) $link .= '&tmpl=component';
		$config = acymailing::config();
		$app =& JFactory::getApplication();
		if(!$app->isAdmin() && $config->get('use_sef',0)){
			$link = ltrim(JRoute::_($link,false),'/');
		}
		static $mainurl = '';
		static $otherarguments = false;
		if(empty($mainurl)){
			$urls = parse_url(ACYMAILING_LIVE);
			if(isset($urls['path']) AND strlen($urls['path'])>0){
				$mainurl = substr(ACYMAILING_LIVE,0,strrpos(ACYMAILING_LIVE,$urls['path'])).'/';
				$otherarguments = trim(str_replace($mainurl,'',ACYMAILING_LIVE),'/');
				if(strlen($otherarguments) > 0) $otherarguments .= '/';
			}else{
				$mainurl = ACYMAILING_LIVE;
			}
		}
		if($otherarguments AND strpos($link,$otherarguments) === false){
			$link = $otherarguments.$link;
		}
		return $mainurl.$link;
	}
	function bytes($val) {
		$val = trim($val);
		if(empty($val))
		{
			return 0;
		}
		$last = strtolower($val[strlen($val)-1]);
		switch($last) {
			case 'g':
			$val *= 1024;
			case 'm':
			$val *= 1024;
			case 'k':
			$val *= 1024;
		}
		return (int)$val;
	}
	function display($messages,$type = 'success'){
		if(empty($messages)) return;
		if(!is_array($messages)) $messages = array($messages);
		echo '<div id="acymailing_messages_'.$type.'" class="acymailing_messages acymailing_'.$type.'"><ul><li>'.implode('</li><li>',$messages).'</li></ul></div>';
	}
	function completeLink($link,$popup = false,$redirect = false){
		if($popup) $link .= '&tmpl=component';
		return JRoute::_('index.php?option='.ACYMAILING_COMPONENT.'&ctrl='.$link,!$redirect);
	}
	function table($name,$component = true){
		$prefix = $component ? ACYMAILING_DBPREFIX : '#__';
		return $prefix.$name;
	}
	function secureField($fieldName){
		if (!is_string($fieldName) OR preg_match('|[^a-z0-9#_.-]|i',$fieldName) !== 0 ){
			 die('field "'.$fieldName .'" not secured');
		}
		return $fieldName;
	}
	function displayErrors(){
		error_reporting(E_ALL);
 		@ini_set("display_errors", 1);
	}
	function increasePerf(){
		@ini_set('max_execution_time',0);




	}
	function &config($reload = false){
		static $configClass = null;
		if($configClass === null || $reload){
			$configClass = acymailing::get('class.config');
			$configClass->load();
		}
		return $configClass;
	}
	function level($level){
		$config =& acymailing::config();
		if($config->get($config->get('level'),0) >= $level) return true;
		return false;
	}
	function getModuleFormName(){
		static $i = 1;
		return 'formAcymailing'.$i++;
	}
	function initModule($includejs,$params){
		static $alreadyThere = false;
		if($alreadyThere) return;
		$alreadyThere = true;
		$lang =& JFactory::getLanguage();
		$lang->load(ACYMAILING_COMPONENT,JPATH_SITE);
		acymailing::initJSStrings($includejs,$params);
		$doc =& JFactory::getDocument();
		$config = acymailing::config();
		if($includejs == 'header'){
			$doc->addScript(ACYMAILING_JS.'acymailing_module.js');
		}else{
			echo "\n".'<script type="text/javascript" src="'.ACYMAILING_JS.'acymailing_module.js" ></script>'."\n";
		}
		$moduleCSS = $config->get('css_module','default');
		if(!empty($moduleCSS)){
			$doc->addStyleSheet( ACYMAILING_CSS.'module_'.$moduleCSS.'.css' );
		}
	}
	function footer(){
		$config = acymailing::config();
		$description = $config->get('description_'.strtolower($config->get('level')),'Joomla!™ Mailing System');
		$text = '<!--  AcyMailing Component powered by http://www.acyba.com -->
		<!-- version '.$config->get('level').' : '.$config->get('version').' -->';
		if(!$config->get('show_footer',true)) return $text;
		$text .= '<div class="acymailing_footer" align="center" style="text-align:center"><a href="http://www.acyba.com" target="_blank" title="'.ACYMAILING_NAME.' : '.str_replace('TM ',' ',strip_tags($description)).'">'.ACYMAILING_NAME;
		$app =& JFactory::getApplication();
		if($app->isAdmin()) $text .= ' '.$config->get('level').' '.$config->get('version');
		$text .=' - '.$description.'</a></div>';
		return $text;
	}
	function search($searchString,$object){
		if(empty($object) OR is_numeric($object)) return $object;
		if(is_string($object) OR is_numeric($object)){
			return preg_replace('#('.str_replace('#','\#',$searchString).')#i','<span class="searchtext">$1</span>',$object);
		}
		if(is_array($object)){
			foreach($object as $key => $element){
				$object[$key] = acymailing::search($searchString,$element);
			}
		}elseif(is_object($object)){
			foreach($object as $key => $element){
				$object->$key = acymailing::search($searchString,$element);
			}
		}
		return $object;
	}
	function get($path){
		list($group,$class) = explode('.',$path);
		include_once(constant(strtoupper('ACYMAILING_'.$group)).$class.'.php');
		$className = $class.ucfirst($group);
		if(!class_exists($className)) return null;
		return new $className();
	}
	function getCID($field = ''){
		$oneResult = intval(reset(JRequest::getVar( 'cid', array(), '', 'array' )));
		if(!empty($oneResult) OR empty($field)) return $oneResult;
		return intval(JRequest::getVar( $field,0,'','int'));
	}
	function tooltip($desc,$title=' ', $image='tooltip.png', $name = '',$href='', $link=1){
		return JHTML::_('tooltip', str_replace(array("'","::"),array("&#039;",": : "),$desc.' '),str_replace(array("'",'::'),array("&#039;",': : '),$title), $image, str_replace(array("'",'"','::'),array("&#039;","&quot;",': : '),$name.' '),$href, $link);
	}
	function checkRobots(){
		if(preg_match('#(libwww-perl|python)#i',@$_SERVER['HTTP_USER_AGENT'])) die('Not allowed for robots. Please contact us if you are not a robot');
	}
}
class acymailingController extends JController{
	var $pkey = '';
	var $table = '';
	var $groupMap = '';
	var $groupVal = '';
	var $aclCat = '';
	function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerDefaultTask('listing');
	}
	function listing(){
		if(!empty($this->aclCat) AND !$this->isAllowed($this->aclCat,'manage')) return;
		JRequest::setVar( 'layout', 'listing'  );
		return parent::display();
	}
	function isAllowed($cat,$action){
		if(acymailing::level(3)){
			$config = acymailing::config();
			if(!acymailing::isAllowed($config->get('acl_'.$cat.'_'.$action,'all'))){
				acymailing::display(JText::_('ACY_NOTALLOWED'),'error');
				return false;
			}
		}
		return true;
	}
	function edit(){
		if(!empty($this->aclCat) AND !$this->isAllowed($this->aclCat,'manage')) return;
		JRequest::setVar('hidemainmenu',1);
		JRequest::setVar( 'layout', 'form'  );
		return parent::display();
	}
	function add(){
		if(!empty($this->aclCat) AND !$this->isAllowed($this->aclCat,'manage')) return;
		JRequest::setVar('hidemainmenu',1);
		JRequest::setVar( 'layout', 'form'  );
		return parent::display();
	}
	function apply(){
		$this->store();
		return $this->edit();
	}
	function save(){
		$this->store();
		return $this->listing();
	}
	function orderdown(){
		if(!empty($this->aclCat) AND !$this->isAllowed($this->aclCat,'manage')) return;
		JRequest::checkToken() or jexit( 'Invalid Token' );
		$orderClass = acymailing::get('helper.order');
		$orderClass->pkey = $this->pkey;
		$orderClass->table = $this->table;
		$orderClass->groupMap = $this->groupMap;
		$orderClass->groupVal = $this->groupVal;
		$orderClass->order(true);
		return $this->listing();
	}
	function orderup(){
		if(!empty($this->aclCat) AND !$this->isAllowed($this->aclCat,'manage')) return;
		JRequest::checkToken() or jexit( 'Invalid Token' );
		$orderClass = acymailing::get('helper.order');
		$orderClass->pkey = $this->pkey;
		$orderClass->table = $this->table;
		$orderClass->groupMap = $this->groupMap;
		$orderClass->groupVal = $this->groupVal;
		$orderClass->order(false);
		return $this->listing();
	}
	function saveorder(){
		if(!empty($this->aclCat) AND !$this->isAllowed($this->aclCat,'manage')) return;
		JRequest::checkToken() or jexit( 'Invalid Token' );
		$orderClass = acymailing::get('helper.order');
		$orderClass->pkey = $this->pkey;
		$orderClass->table = $this->table;
		$orderClass->groupMap = $this->groupMap;
		$orderClass->groupVal = $this->groupVal;
		$orderClass->save();
		return $this->listing();
	}
}
class acymailingClass extends JObject{
	var $tables = array();
	var $pkey = '';
	var $namekey = '';
	function  __construct( $config = array() ){
		$this->database =& JFactory::getDBO();
		return parent::__construct($config);
	}
	function save($element){
		$pkey = $this->pkey;
		if(empty($element->$pkey)){
			$status = $this->database->insertObject(acymailing::table(end($this->tables)),$element);
		}else{
			if(count((array) $element) > 1){
				$status = $this->database->updateObject(acymailing::table(end($this->tables)),$element,$pkey);
			}else{
				$status = true;
			}
		}
		if($status) return empty($element->$pkey) ? $this->database->insertid() : $element->$pkey;
		return false;
	}
	function delete($elements){
		if(!is_array($elements)){
			$elements = array($elements);
		}
		if(empty($elements)) return 0;
		foreach($elements as $key => $val){
			$elements[$key] = $this->database->getEscaped($val);
		}
		$column = is_numeric(reset($elements)) ? $this->pkey : $this->namekey;
		if(empty($column) OR empty($this->pkey) OR empty($this->tables) OR empty($elements)) return false;
		$whereIn = ' WHERE '.$column.' IN ('.implode(',',$elements).')';
		$result = true;
		foreach($this->tables as $oneTable){
			$query = 'DELETE FROM '.acymailing::table($oneTable).$whereIn;
			$this->database->setQuery($query);
			$result = $this->database->query() && $result;
		}
		if(!$result) return false;
		return $this->database->getAffectedRows();
	}
}