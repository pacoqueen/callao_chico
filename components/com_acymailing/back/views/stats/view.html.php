<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class StatsViewStats extends JView
{
	var $searchFields = array('b.subject','a.mailid');
	var $selectFields = array('b.subject','b.type','a.*');
	var $detailSearchFields = array('b.subject','a.mailid','c.name','c.email','a.subid');
	var $detailSelectFields = array('b.subject','c.name','c.email','b.type','a.*');
	function display($tpl = null)
	{
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function detaillisting(){
		$app =& JFactory::getApplication();
		$pageInfo = null;
		$config = acymailing::config();
		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName().$this->getLayout();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $paramBase.".filter_order", 'filter_order',	'a.senddate','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower( $pageInfo->search );
		$selectedMail = $app->getUserStateFromRequest( $paramBase."filter_mail",'filter_mail',0,'int');
		$selectedStatus = $app->getUserStateFromRequest( $paramBase."filter_status",'filter_status',0,'string');
		$pageInfo->limit->value = $app->getUserStateFromRequest( $paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	=& JFactory::getDBO();
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.$database->getEscaped($pageInfo->search).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$this->detailSearchFields)." LIKE $searchVal";
		}
		if(!empty($selectedMail)) $filters[] = 'a.mailid = '.$selectedMail;
		if(!empty($selectedStatus)){
			if($selectedStatus == 'bounce') $filters[] = 'a.bounce > 0';
			elseif($selectedStatus == 'open') $filters[] = 'a.open > 0';
			elseif($selectedStatus == 'notopen') $filters[] = 'a.open < 1';
			elseif($selectedStatus == 'failed') $filters[] = 'a.fail > 0';
		}
		$query = 'SELECT '.implode(' , ',$this->detailSelectFields);
		$query .= ' FROM '.acymailing::table('userstats').' as a';
		$query .= ' LEFT JOIN '.acymailing::table('mail').' as b on a.mailid = b.mailid';
		$query .= ' LEFT JOIN '.acymailing::table('subscriber').' as c on a.subid = c.subid';
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (',$filters).')';
		if(!empty($pageInfo->filter->order->value)) $query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 100;
		$database->setQuery($query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		$queryCount = 'SELECT COUNT(a.subid) FROM #__acymailing_userstats as a';
		if(!empty($pageInfo->search)){
			$queryCount .= ' LEFT JOIN '.acymailing::table('mail').' as b on a.mailid = b.mailid';
			$queryCount .= ' LEFT JOIN '.acymailing::table('subscriber').' as c on a.subid = c.subid';
		}
		if(!empty($filters)) $queryCount .= ' WHERE ('.implode(') AND (',$filters).')';
		$database->setQuery($queryCount);
		$pageInfo->elements->total = $database->loadResult();
		if(!empty($pageInfo->search)){
			$rows = acymailing::search($pageInfo->search,$rows);
		}
		$pageInfo->elements->page = count($rows);
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		$toggleClass = acymailing::get('helper.toggle');
		$maildetailstatstype =  acymailing::get('type.detailstatsmail');
		$deliverstatus =  acymailing::get('type.deliverstatus');
		$filtersType = null;
		$filtersType->mail = $maildetailstatstype->display('filter_mail',$selectedMail);
		$filtersType->status = $deliverstatus->display('filter_status',$selectedStatus);
		acymailing::setTitle(JText::_('DETAILED_STATISTICS'),'stats','stats&task=detaillisting');
		$bar = & JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Link', 'back', JText::_('GLOBAL_STATISTICS'), acymailing::completeLink('stats') );
		if(acymailing::isAllowed($config->get('acl_subscriber_export','all'))){
			JToolBarHelper::spacer();
			JToolBarHelper::custom('export', 'acyexport', '',JText::_('EXPORT'), false);
		}
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','stats-detaillisting');
		$this->assignRef('filters',$filtersType);
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
	}
	function listing(){
		$app =& JFactory::getApplication();
		$pageInfo = null;
		$config = acymailing::config();
		JHTML::_('behavior.modal','a.modal');
		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName().$this->getLayout();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $paramBase.".filter_order", 'filter_order',	'a.senddate','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower( $pageInfo->search );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	=& JFactory::getDBO();
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.$database->getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$this->searchFields)." LIKE $searchVal";
		}
		$query = 'SELECT '.implode(' , ',$this->selectFields);
		$query .= ' FROM '.acymailing::table('stats').' as a';
		$query .= ' LEFT JOIN '.acymailing::table('mail').' as b on a.mailid = b.mailid';
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (',$filters).')';
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$database->setQuery($query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		$queryCount = 'SELECT COUNT(a.mailid) FROM '.acymailing::table('stats').' as a';
		if(!empty($pageInfo->search)){
			$queryCount .= ' LEFT JOIN '.acymailing::table('mail').' as b on a.mailid = b.mailid';
 		}
 		if(!empty($filters)) $queryCount .= ' WHERE ('.implode(') AND (',$filters).')';
		$database->setQuery($queryCount);
		$pageInfo->elements->total = $database->loadResult();
		if(!empty($pageInfo->search)){
			$rows = acymailing::search($pageInfo->search,$rows);
		}
		$pageInfo->elements->page = count($rows);
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		acymailing::setTitle(JText::_('GLOBAL_STATISTICS'),'stats','stats');
		$bar = & JToolBar::getInstance('toolbar');
		if(acymailing::level(1)) $bar->appendButton( 'Link', 'stats', JText::_('CHARTS'), acymailing::completeLink('diagram') );
		JToolBarHelper::spacer();
		JToolBarHelper::spacer();
		if(acymailing::isAllowed($config->get('acl_statistics_delete','all'))) JToolBarHelper::deleteList(JText::_('ACY_VALIDDELETEITEMS'));
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','stats-listing');
		if(acymailing::isAllowed($config->get('acl_cpanel_manage','all'))) $bar->appendButton( 'Link', 'acymailing', JText::_('ACY_CPANEL'), acymailing::completeLink('dashboard') );
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
	}
}