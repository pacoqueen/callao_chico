<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class QueueViewQueue extends JView
{
	var $searchFields = array('b.name','b.email','c.subject','a.mailid','a.subid');
	var $selectFields = array('b.name','b.email','c.subject','c.type','c.published','a.mailid','a.subid','a.senddate','a.priority','a.try');
	function display($tpl = null)
	{
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function listing(){
		$app =& JFactory::getApplication();
		$pageInfo = null;
		$config = acymailing::config();
		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $paramBase.".filter_order", 'filter_order',	'a.senddate','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $paramBase.".filter_order_Dir", 'filter_order_Dir',	'asc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower( $pageInfo->search );
		$selectedMail = $app->getUserStateFromRequest( $paramBase."filter_mail",'filter_mail',0,'int');
		$pageInfo->limit->value = $app->getUserStateFromRequest( $paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	=& JFactory::getDBO();
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.$database->getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$this->searchFields)." LIKE $searchVal";
		}
		if(!empty($selectedMail)) $filters[] = 'a.mailid = '.intval($selectedMail);
		$query = 'SELECT '.implode(' , ',$this->selectFields);
		$query .= ' FROM '.acymailing::table('queue').' as a';
		$query .= ' LEFT JOIN '.acymailing::table('subscriber').' as b on a.subid = b.subid';
		$query .= ' LEFT JOIN '.acymailing::table('mail').' as c on a.mailid = c.mailid';
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (',$filters).')';
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir.', a.`subid` ASC';
		}
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 100;
		$database->setQuery($query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		$queryCount = 'SELECT COUNT(a.mailid) FROM '.acymailing::table('queue').' as a';
		if(!empty($filters)){
			$queryCount .= ' LEFT JOIN '.acymailing::table('subscriber').' as b on a.subid = b.subid';
			$queryCount .= ' LEFT JOIN '.acymailing::table('mail').' as c on a.mailid = c.mailid';
			$queryCount .= ' WHERE ('.implode(') AND (',$filters).')';
		}
		$database->setQuery($queryCount);
		$pageInfo->elements->total = $database->loadResult();
		if(!empty($pageInfo->search)){
			$rows = acymailing::search($pageInfo->search,$rows);
		}
		$pageInfo->elements->page = count($rows);
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		$mailqueuetype =  acymailing::get('type.queuemail');
		$filtersType = null;
		$filtersType->mail = $mailqueuetype->display('filter_mail',$selectedMail);
		acymailing::setTitle(JText::_('QUEUE'),'process','queue');
		$bar = & JToolBar::getInstance('toolbar');
		if(acymailing::isAllowed($config->get('acl_queue_process','all'))) $bar->appendButton( 'Popup', 'process', JText::_('PROCESS'), "index.php?option=com_acymailing&ctrl=queue&task=process&tmpl=component&mailid=".$selectedMail);
		if(!empty($pageInfo->elements->total) AND acymailing::isAllowed($config->get('acl_queue_delete','all'))){
			JToolBarHelper::spacer();
			JToolBarHelper::spacer();
			$bar->appendButton( 'Confirm', JText::sprintf('CONFIRM_DELETE_QUEUE',$pageInfo->elements->total), 'delete', JText::_('ACY_DELETE'), 'remove', false, false );
		}
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','queue-listing');
		if(acymailing::isAllowed($config->get('acl_cpanel_manage','all'))) $bar->appendButton( 'Link', 'acymailing', JText::_('ACY_CPANEL'), acymailing::completeLink('dashboard') );
		$toggleClass = acymailing::get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('filters',$filtersType);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
	}
	function process(){
		$mailid = acymailing::getCID('mailid');
		$queueClass = acymailing::get('class.queue');
		$queueStatus = $queueClass->queueStatus($mailid);
		$nextqueue = $queueClass->queueStatus($mailid,true);
		if(acymailing::level(1)){
			$scheduleClass = acymailing::get('helper.schedule');
			$scheduleNewsletter = $scheduleClass->getScheduled();
			$this->assignRef('schedNews',$scheduleNewsletter);
		}
		if(empty($queueStatus) AND empty($scheduleNewsletter)) acymailing::display(JText::_('NO_PROCESS'),'info');
		$infos = null;
		$infos->mailid = $mailid;
		$this->assignRef('queue',$queueStatus);
		$this->assignRef('nextqueue',$nextqueue);
		$this->assignRef('infos',$infos);
	}
}