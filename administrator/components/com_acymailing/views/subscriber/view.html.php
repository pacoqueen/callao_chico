<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class SubscriberViewSubscriber extends JView
{
	var $searchFields = array('a.name','a.email','a.subid','a.userid','b.username');
	var $selectedFields = array('a.*','b.username');
	function display($tpl = null)
	{
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function listing(){
		$pageInfo = null;
		$app =& JFactory::getApplication();
		$config = acymailing::config();
		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $paramBase.".filter_order", 'filter_order',	'a.subid','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$selectedList = $app->getUserStateFromRequest( $paramBase."filter_lists",'filter_lists',0,'int');
		$selectedStatus = $app->getUserStateFromRequest( $paramBase."filter_status",'filter_status',0,'int');
		$pageInfo->search = $app->getUserStateFromRequest( $paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower( $pageInfo->search );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	=& JFactory::getDBO();
		$displayFields = array();
		$displayFields['name'] = null;
		$displayFields['name']->fieldname = 'JOOMEXT_NAME';
		$displayFields['email'] = null;
		$displayFields['email']->fieldname = 'JOOMEXT_EMAIL';
		$displayFields['html'] = null;
		$displayFields['html']->fieldname = 'RECEIVE_HTML';
		$filters = array();
		if(!empty($pageInfo->search)){
			foreach($displayFields as $fieldname => $onfield){
				if($fieldname == 'html' OR in_array('a.'.$fieldname,$this->searchFields)) continue;
				$this->searchFields[] = 'a.`'.$fieldname.'`';
			}
			$searchVal = '\'%'.$database->getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$this->searchFields)." LIKE $searchVal";
		}
		$leftJoinQuery = array();
		if(empty($selectedList) || ($selectedStatus == -2)){
			if(empty($selectedList) && $selectedStatus == -2) $selectedStatus = 0;
			$fromQuery = ' FROM '.acymailing::table('subscriber').' as a ';
			$countField = "a.subid";
			$leftJoinQuery[] = acymailing::table('users',false).' as b ON a.userid = b.id';
			if($selectedStatus == 1){
				$filters[] = 'a.accept > 0';
			}elseif($selectedStatus == -1){
				$filters[] = 'a.accept < 1';
			}elseif($selectedStatus == -2){
				$leftJoinQuery[] = acymailing::table('listsub').' as c on a.subid = c.subid AND listid = '.$selectedList;
				$filters[] = 'c.listid IS NULL';
			}elseif($selectedStatus == 2){
				$filters[] = 'a.confirmed < 1';
			}elseif($selectedStatus == 3){
				$filters[] = 'a.enabled > 0';
			}elseif($selectedStatus == -3){
				$filters[] = 'a.enabled < 1';
			}
		}else{
			$fromQuery = ' FROM '.acymailing::table('listsub').' as c';
			$countField = "c.subid";
			$leftJoinQuery[] = acymailing::table('subscriber').' as a ON a.subid = c.subid';
			$leftJoinQuery[] = acymailing::table('users',false).' as b ON a.userid = b.id';
			$filters[] = 'c.listid = '.intval($selectedList);
			if(!in_array($selectedStatus,array(-1,1,2))) $selectedStatus = 1;
			$filters[] = 'c.status = '.$selectedStatus;
		}
		$query = 'SELECT '.implode(',',$this->selectedFields).$fromQuery;
		if(!empty($leftJoinQuery)){
			$query .= ' LEFT JOIN '.implode(' LEFT JOIN ',$leftJoinQuery);
		}
		if(!empty($filters)){
			$query .= ' WHERE ('.implode(') AND (',$filters).')';
		}
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$database->setQuery($query,$pageInfo->limit->start,empty($pageInfo->limit->value) ? 500 : $pageInfo->limit->value);
		$rows = $database->loadObjectList('subid');
		$pageInfo->elements->page = count($rows);
		if($pageInfo->limit->value > $pageInfo->elements->page){
			$pageInfo->elements->total = $pageInfo->limit->start + $pageInfo->elements->page;
		}else{
			$queryCount = 'SELECT COUNT('.$countField.') '.$fromQuery;
			if(!empty($filters)){
				if(!empty($leftJoinQuery)) $queryCount .= ' LEFT JOIN '.implode(' LEFT JOIN ',$leftJoinQuery);
				$queryCount .= ' WHERE ('.implode(') AND (',$filters).')';
			}
			$database->setQuery($queryCount);
			$pageInfo->elements->total = $database->loadResult();
		}
		if(!empty($rows)){
			$database->setQuery('SELECT * FROM `#__acymailing_listsub` WHERE `subid` IN (\''.implode('\',\'',array_keys($rows)).'\')');
			$subscriptions = $database->loadObjectList();
			if(!empty($subscriptions)){
				foreach($subscriptions as $onesub){
					$sublistid = $onesub->listid;
					$rows[$onesub->subid]->subscription->$sublistid = $onesub;
				}
			}
		}
		if(!empty($pageInfo->search)){
			$rows = acymailing::search($pageInfo->search,$rows);
		}
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 100;
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		$filters = null;
		if(empty($selectedList)){
			$statusType = acymailing::get('type.statusfilter');
		}else{
			$statusType = acymailing::get('type.statusfilterlist');
		}
		$listsType = acymailing::get('type.lists');
		$filters->status = $statusType->display('filter_status',$selectedStatus);
		$filters->lists = $listsType->display('filter_lists',$selectedList);
		acymailing::setTitle(JText::_('USERS'),'user','subscriber');
		$bar = & JToolBar::getInstance('toolbar');
		if(acymailing::isAllowed($config->get('acl_lists_filter','all'))){
			$bar->appendButton( 'Acyactions');
			JToolBarHelper::divider();
		}
		if(acymailing::isAllowed($config->get('acl_subscriber_import','all'))) $bar->appendButton( 'Link', 'import', JText::_('IMPORT'), acymailing::completeLink('data&task=import') );
		if(acymailing::isAllowed($config->get('acl_subscriber_export','all'))) JToolBarHelper::custom('export', 'acyexport', '',JText::_('ACY_EXPORT'), false);
		JToolBarHelper::divider();
		if(acymailing::isAllowed($config->get('acl_subscriber_manage','all'))) JToolBarHelper::addNew();
		if(acymailing::isAllowed($config->get('acl_subscriber_manage','all'))) JToolBarHelper::editList();
		if(acymailing::isAllowed($config->get('acl_subscriber_delete','all'))) JToolBarHelper::deleteList(JText::_('ACY_VALIDDELETEITEMS',true));
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','subscriber-listing');
		if(acymailing::isAllowed($config->get('acl_cpanel_manage','all'))) $bar->appendButton( 'Link', 'acymailing', JText::_('ACY_CPANEL'), acymailing::completeLink('dashboard') );
		$this->assignRef('lists',$listsType->getData());
		$this->assignRef('toggleClass',acymailing::get('helper.toggle'));
		$this->assignRef('rows',$rows);
		$this->assignRef('filters',$filters);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
		$this->assignRef('config',acymailing::config());
		$this->assignRef('displayFields',$displayFields);
		$this->assignRef('customFields',acymailing::get('class.fields'));
	}
	function choose(){
		$pageInfo = null;
		$app =& JFactory::getApplication();
		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName().'_'.$this->getLayout();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $paramBase.".filter_order", 'filter_order',	'a.name','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $paramBase.".filter_order_Dir", 'filter_order_Dir',	'asc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower( $pageInfo->search );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $paramBase.'.limitstart', 'limitstart', 0, 'int' );
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 100;
		$db	=& JFactory::getDBO();
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.$db->getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$this->searchFields)." LIKE $searchVal";
		}
		if(JRequest::getString('onlyreg')){
			$filters[] = 'a.userid > 0';
		}
		$query = 'SELECT '.implode(',',$this->selectedFields).' FROM #__acymailing_subscriber as a';
		$query .= ' LEFT JOIN #__users as b on a.userid = b.id';
		if(!empty($filters)){
			$query .= ' WHERE ('.implode(') AND (',$filters).')';
		}
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$db->setQuery($query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $db->loadObjectList();
		$queryWhere = 'SELECT COUNT(a.subid) FROM #__acymailing_subscriber as a';
		if(!empty($filters)){
			$queryWhere .= ' LEFT JOIN #__users as b on a.userid = b.id';
			$queryWhere .= ' WHERE ('.implode(') AND (',$filters).')';
		}
		$db->setQuery($queryWhere);
		$pageInfo->elements->total = $db->loadResult();
		if(!empty($pageInfo->search)){
			$rows = acymailing::search($pageInfo->search,$rows);
		}
		$pageInfo->elements->page = count($rows);
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
	}
	function form(){
		$subid = acymailing::getCID('subid');
		$db =& JFactory::getDBO();
		if(!empty($subid)){
			$subscriberClass = acymailing::get('class.subscriber');
			$subscriber = $subscriberClass->getFull($subid);
			$subscription = $subscriberClass->getSubscription($subid);
		}else{
			$listType = acymailing::get('class.list');
			$subscription = $listType->getLists();
			$subscriber = null;
			$subscriber->created = time();
			$subscriber->html = 1;
			$subscriber->confirmed = 1;
			$subscriber->blocked = 0;
			$subscriber->accept = 1;
			$subscriber->enabled = 1;
			$iphelper = acymailing::get('helper.user');
			$subscriber->ip = $iphelper->getIP();
		}
		acymailing::setTitle(JText::_('ACY_USER'),'user','subscriber&task=edit&subid='.$subid);
		$bar = & JToolBar::getInstance('toolbar');

		if(!empty($subid)){
			$query = 'SELECT a.`mailid`, a.`html`, a.`sent`, a.`senddate`,a.`open`, a.`opendate`, a.`bounce`, a.`fail`,b.`subject`,b.`alias`';
			$query .= ' FROM `#__acymailing_userstats` as a';
			$query .= ' LEFT JOIN '.acymailing::table('mail').' as b on a.mailid = b.mailid';
			$query .= ' WHERE a.subid = '.intval($subid).' ORDER BY a.senddate DESC LIMIT 30';
			$db->setQuery($query);
			$open = $db->loadObjectList();
			$this->assignRef('open',$open);
			$query = 'SELECT a.*,b.`subject`,b.`alias`';
			$query .= ' FROM `#__acymailing_queue` as a';
			$query .= ' LEFT JOIN '.acymailing::table('mail').' as b on a.mailid = b.mailid';
			$query .= ' WHERE a.subid = '.intval($subid).' ORDER BY a.senddate ASC LIMIT 60';
			$db->setQuery($query);
			$queue = $db->loadObjectList();
			$this->assignRef('queue',$queue);
			$query = 'SELECT * FROM #__acymailing_history WHERE subid = '.intval($subid).' ORDER BY `date` DESC LIMIT 30';
			$db->setQuery($query);
			$history = $db->loadObjectList();
			$this->assignRef('history',$history);
		}
		if(!empty($subscriber->userid)){
			if(file_exists(ACYMAILING_ROOT.'components'.DS.'com_comprofiler'.DS.'comprofiler.php')){
				$editLink = 'index.php?option=com_comprofiler&task=edit&cid[]=';
			}elseif(version_compare(JVERSION,'1.6.0','<')){
				$editLink = 'index.php?option=com_users&task=edit&cid[]=';
			}else{
				$editLink = 'index.php?option=com_users&task=user.edit&id=';
			}
			$bar->appendButton( 'Link', 'edit', JText::_('EDIT_JOOMLA_USER'), $editLink.$subscriber->userid );
			JToolBarHelper::spacer();
		}
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','subscriber-form');
		$filters = null;
		$quickstatusType = acymailing::get('type.statusquick');
		$filters->statusquick = $quickstatusType->display('statusquick');
		$this->assignRef('subscriber',$subscriber);
		$this->assignRef('toggleClass',acymailing::get('helper.toggle'));
		$this->assignRef('subscription',$subscription);
		$this->assignRef('filters',$filters);
		$this->assignRef('statusType',acymailing::get('type.status'));
	}
}
