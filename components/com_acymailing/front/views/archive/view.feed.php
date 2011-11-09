<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
jimport( 'joomla.application.component.view');
die('NOT READY YET');
class archiveViewArchive extends JView
{
	function display($tpl = null)
  {
		 global $Itemid;
		$db			=& JFactory::getDBO();
		$app =& JFactory::getApplication();
		$document	=& JFactory::getDocument();
		$params =& $app->getParams();
		$feedEmail = (@$app->getCfg('feed_email')) ? $app->getCfg('feed_email') : 'author';
		$siteEmail = $app->getCfg('mailfrom');
		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();
		if(empty($menu) AND !empty($Itemid)){
			$menus->setActive($Itemid);
			$menu	= $menus->getItem($Itemid);
		}
		$myItem = empty($Itemid) ? '' : '&Itemid='.$Itemid;
		if (is_object( $menu )) {
			jimport('joomla.html.parameter');
			$menuparams = new JParameter( $menu->params );
		}
 		$listid = acymailing::getCID('listid');
	    if(empty($listid) AND !empty($menuparams)){
	    	$listid = $menuparams->get('listid');
	    }
		$document->link = acymailing::completeLink('archive&listid='.intval($listid));
		 $listClass = acymailing::get('class.list');
 		if(empty($listid)){
    		return JError::raiseError( 404, 'Mailing List not found' );
	    }
	    $oneList = $listClass->get($listid);
	    if(empty($oneList->listid)){
	    	return JError::raiseError( 404, 'Mailing List not found : '.$listid );
	    }
	    if(!acymailing::isAllowed($oneList->access_sub) || !$oneList->published || !$oneList->visible){
	    	return JError::raiseError( 404, JText::_('ACY_NOTALLOWED') );
	    }
		$filters = array();
		$filters[] = 'a.type = \'news\'';
		$filters[] = 'a.published = 1';
		$filters[] = 'a.visible = 1';
		$filters[] = 'c.listid = '.$oneList->listid;
		$query = 'SELECT a.*';
		$query .= ' FROM '.acymailing::table('listmail').' as c';
		$query .= ' LEFT JOIN '.acymailing::table('mail').' as a on a.mailid = c.mailid ';
		$query .= ' WHERE ('.implode(') AND (',$filters).')';
		$query .= ' ORDER BY a.senddate DESC, c.mailid DESC';
		$db->setQuery($query,0,$app->getCfg('feed_limit'));
		$rows = $db->loadObjectList();
		foreach ( $rows as $row )
		{
		}
	}
}
