<?php
/**
 * @package JV Facebook Plugin for Joomla! 1.5
 * @author http://www.zootemplate.com
 * @copyright (C) 2011- zootemplate.Com
 * @license PHP files are GNU/GPL
**/
//Initiate environment
define( 'DS', DIRECTORY_SEPARATOR );
$rootFolder = explode(DS,dirname(__FILE__));
//current level in diretoty structure
$currentfolderlevel = 3;
array_splice($rootFolder,-$currentfolderlevel);
$base_folder = implode(DS,$rootFolder);
define( '_JEXEC', 1 );
define('JPATH_BASE1',implode(DS,$rootFolder));
$defines = JPATH_BASE1 .DS;
$defines = str_replace('\plugins','',$defines);
$defines = str_replace('/plugins','',$defines);
define('JPATH_BASE',$defines);
require_once ( JPATH_BASE.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE.'includes'.DS.'framework.php' );
require_once(JPATH_BASE.'libraries'.DS.'joomla'.DS.'factory.php');
$mainframe =& JFactory::getApplication('site');
$mainframe->initialise();
$db = &JFactory::getDBO();
	// Get article in content category
	$arr_contentid = trim($_GET['contentid']);
	while ( substr($arr_contentid,strlen($arr_contentid)-1,strlen($arr_contentid))==","  )
	{
		$arr_contentid = substr($arr_contentid,0,strlen($arr_contentid)-1);
	}
	$db = &JFactory::getDBO();
	if ($arr_contentid != "") {
		$query = '
			SELECT id, title 
			FROM #__content 
			WHERE catid in ('.$arr_contentid.')
			ORDER BY catid
			';
	} else {
		$query = '
		SELECT id, title 
		FROM #__content 
		ORDER BY catid
		';
	}
	$db->setQuery( $query );
	$cats_content = $db->loadObjectList();
	$art_content = array();
	$art_content[0] = '';
	$art_content[1] = "None";
	$i = 2; 
	if (isset($cats_content)) {
		foreach ($cats_content as $cat_content) {
			$art_content[$i] = 'content_'.$cat_content->id;
			$i++;
			$art_content[$i] = $cat_content->title;
			$i++;
		}
	}
	// Get article in K2 category
	$arr_k2id = trim($_GET['k2id']);
	while ( substr($arr_k2id,strlen($arr_k2id)-1,strlen($arr_k2id))==","  )
	{
		$arr_k2id = substr($arr_k2id,0,strlen($arr_k2id)-1);
	}
	if ($arr_k2id != "") {
		$query1 = '
			SELECT id, title 
			FROM #__k2_items 
			WHERE catid in ('.$arr_k2id.')
			ORDER BY catid
			';
	} else {
		$query1 = '
		SELECT id, title 
		FROM #__k2_items 
		ORDER BY catid
		';
	}
	$db->setQuery( $query1 );
	$cats_k2 = $db->loadObjectList();
	$art_k2 = array();
	$j = 0; 
	if(isset($cats_k2)) {
		foreach ($cats_k2 as $cat_k2) {
			$art_k2[$j] = 'k2_'.$cat_k2->id;
			$j++;
			$art_k2[$j] = $cat_k2->title;
			$j++;
		}
	}
	// add array $art_kc into $art_content
	for ( $n = 0; $n < count($art_k2); $n++) {
		array_push($art_content,$art_k2[$n]) ;
	}
// Output the JSON data.
echo json_encode($art_content);



































