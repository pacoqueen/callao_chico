<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class ListViewList extends JView
{
	function display($tpl = null)
	{
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function listing(){
		$app =& JFactory::getApplication();
		$config = acymailing::config();
		$pageInfo = null;
		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $paramBase.".filter_order", 'filter_order',	'a.ordering','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $paramBase.".filter_order_Dir", 'filter_order_Dir',	'asc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower( $pageInfo->search );
		$selectedCreator = $app->getUserStateFromRequest( $paramBase."filter_creator",'filter_creator',0,'int');
		$pageInfo->limit->value = $app->getUserStateFromRequest( $paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	=& JFactory::getDBO();
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.$database->getEscaped($pageInfo->search,true).'%\'';
			$filters[] = "a.name LIKE $searchVal OR a.description LIKE $searchVal OR a.listid LIKE $searchVal";
		}
		$filters[] = "a.type = 'list'";
		if(!empty($selectedCreator)) $filters[] = 'a.userid = '.$selectedCreator;
		$query = 'SELECT a.*, d.name as creatorname, d.username, d.email';
		$query .= ' FROM '.acymailing::table('list').' as a';
		$query .=  ' LEFT JOIN '.acymailing::table('users',false).' as d on a.userid = d.id';
		$query .= ' WHERE ('.implode(') AND (',$filters).')';
		$query .= ' GROUP BY a.listid';
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$database->setQuery($query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		$queryCount = 'SELECT COUNT(a.listid) FROM  '.acymailing::table('list').' as a';
		$queryCount .=  ' LEFT JOIN '.acymailing::table('users',false).' as d on a.userid = d.id';
		$queryCount .= ' WHERE ('.implode(') AND (',$filters).')';
		$database->setQuery($queryCount);
		$pageInfo->elements->total = $database->loadResult();
		$listids = array();
		foreach($rows as $oneRow){
			$listids[] = $oneRow->listid;
		}
		if(!empty($listids)){
			$querySelectSub = 'SELECT count(distinct subid) as nbsub,listid FROM '.acymailing::table('listsub').' WHERE listid IN ('.implode(',',$listids).') AND status = 1 GROUP BY listid';
			$querySelectUnsub = 'SELECT count(distinct subid) as nbunsub,listid FROM '.acymailing::table('listsub').' WHERE listid IN ('.implode(',',$listids).') AND status = -1 GROUP BY listid';
			$querySelectWaiting = 'SELECT count(distinct subid) as nbwait,listid FROM '.acymailing::table('listsub').' WHERE listid IN ('.implode(',',$listids).') AND status = 2 GROUP BY listid';
			$database->setQuery($querySelectSub);
			$subinfos = $database->loadObjectList('listid');
			$database->setQuery($querySelectUnsub);
			$unsubinfos = $database->loadObjectList('listid');
			$database->setQuery($querySelectWaiting);
			$waitinfos = $database->loadObjectList('listid');
		}
		foreach($rows as $i => $oneRow){
			$rows[$i]->nbsub = empty($subinfos[$oneRow->listid]) ? 0 : $subinfos[$oneRow->listid]->nbsub;
			$rows[$i]->nbunsub = empty($unsubinfos[$oneRow->listid]) ? 0 : $unsubinfos[$oneRow->listid]->nbunsub;
			$rows[$i]->nbwait = empty($waitinfos[$oneRow->listid]) ? 0 : $waitinfos[$oneRow->listid]->nbwait;
		}
		if(!empty($pageInfo->search)){
			$rows = acymailing::search($pageInfo->search,$rows);
		}
		$pageInfo->elements->page = count($rows);
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		acymailing::setTitle(JText::_('LISTS'),'acylist','list');
		$bar = & JToolBar::getInstance('toolbar');
		if(acymailing::isAllowed($config->get('acl_lists_filter','all'))){
			$bar->appendButton( 'Link', 'filter', JText::_('ACY_FILTERS'), acymailing::completeLink('filter') );
			JToolBarHelper::divider();
		}
		if(acymailing::isAllowed($config->get('acl_lists_manage','all'))) JToolBarHelper::addNew();
		if(acymailing::isAllowed($config->get('acl_lists_manage','all'))) JToolBarHelper::editList();
		if(acymailing::isAllowed($config->get('acl_lists_delete','all'))) JToolBarHelper::deleteList(JText::_('ACY_VALIDDELETEITEMS'));
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','list-listing');
		if(acymailing::isAllowed($config->get('acl_cpanel_manage','all'))) $bar->appendButton( 'Link', 'acymailing', JText::_('ACY_CPANEL'), acymailing::completeLink('dashboard') );
		$order = null;
		$order->ordering = false;
		$order->orderUp = 'orderup';
		$order->orderDown = 'orderdown';
		$order->reverse = false;
		if($pageInfo->filter->order->value == 'a.ordering'){
			$order->ordering = true;
			if($pageInfo->filter->order->dir == 'desc'){
				$order->orderUp = 'orderdown';
				$order->orderDown = 'orderup';
				$order->reverse = true;
			}
		}
		$filters = null;
		$listcreatorType = acymailing::get('type.listcreator');
		$filters->creator = $listcreatorType->display('filter_creator',$selectedCreator);
		$this->assignRef('filters',$filters);
		$this->assignRef('order',$order);
		$this->assignRef('toggleClass',acymailing::get('helper.toggle'));
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
	}
	function form(){
		$listid = acymailing::getCID('listid');
		$listClass = acymailing::get('class.list');
		if(!empty($listid)){
			$list = $listClass->get($listid);
		}else{
			$list->published = 0;
			$list->visible = 1;
			$list->description = '';
			$list->published = 1;
			$user = JFactory::getUser();
			$list->creatorname = $user->name;
			$list->access_manage = 'none';
			$list->access_sub = 'all';
			$list->languages = 'all';
			$colors = array('#3366ff','#7240A4','#7A157D','#157D69','#ECE649');
			$list->color = $colors[rand(0,count($colors)-1)];
		}
		$editor = acymailing::get('helper.editor');
		$editor->name = 'editor_description';
		$editor->content = $list->description;
		$editor->setDescription();
		if(version_compare(JVERSION,'1.6.0','<')){
			$script = 'function submitbutton(pressbutton){
						if (pressbutton == \'cancel\') {
							submitform( pressbutton );
							return;
						}';
		}else{
			$script = 'Joomla.submitbutton = function(pressbutton) {
						if (pressbutton == \'cancel\') {
							Joomla.submitform(pressbutton,document.adminForm);
							return;
						}';
		}
		$script .= 'if(window.document.getElementById("name").value.length < 2){alert(\''.JText::_('ENTER_TITLE',true).'\'); return false;}';
		$script .= $editor->jsCode();
		if(version_compare(JVERSION,'1.6.0','<')){
			$script .= 'submitform( pressbutton );}';
		}else{
			$script .= 'Joomla.submitform(pressbutton,document.adminForm);}; ';
		}
		$script .= 'function affectUser(idcreator,name,email){
			window.document.getElementById("creatorname").innerHTML = name;
			window.document.getElementById("listcreator").value = idcreator;
		}';
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $script );
		$colorBox = acymailing::get('type.color');
		acymailing::setTitle(JText::_('LIST'),'acylist','list&task=edit&listid='.$listid);
		$bar = & JToolBar::getInstance('toolbar');
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','list-form');
		$this->assignRef('colorBox',$colorBox);
		if(acymailing::level(1)){
			$this->assignRef('welcomeMsg',acymailing::get('type.welcome'));
			$this->assignRef('languages',acymailing::get('type.listslanguages'));
		}
		$this->assignRef('unsubMsg',acymailing::get('type.unsub'));
		$this->assignRef('list',$list);
		$this->assignRef('editor',$editor);
	}
}