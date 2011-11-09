<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class archiveViewArchive extends JView
{
  function display($tpl = null)
  {
    $function = $this->getLayout();
    if(method_exists($this,$function)) $this->$function();
    parent::display($tpl);
  }
	function forward(){
		return $this->view();
	}
  function listing(){
    global $Itemid;
    $app =& JFactory::getApplication();
	$my =& JFactory::getUser();
	$pathway	=& $app->getPathway();
	$values = null;
	$menus	= &JSite::getMenu();
	$menu	= $menus->getActive();
	$config = acymailing::config();
	if(empty($menu) AND !empty($Itemid)){
		$menus->setActive($Itemid);
		$menu	= $menus->getItem($Itemid);
	}
	$myItem = empty($Itemid) ? '' : '&Itemid='.$Itemid;
	$this->assignRef('item',$myItem);
	if (is_object( $menu )) {
		jimport('joomla.html.parameter');
		$menuparams = new JParameter( $menu->params );
	}
	$pageInfo = null;
	$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName();
	$pageInfo->filter->order->value = $app->getUserStateFromRequest( $paramBase.".filter_order", 'filter_order',	'a.senddate','cmd' );
	$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
	$pageInfo->search = $app->getUserStateFromRequest( $paramBase.".search", 'search', '', 'string' );
	$pageInfo->search = JString::strtolower( $pageInfo->search );
	$pageInfo->limit->value = $app->getUserStateFromRequest( $paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
	$pageInfo->limit->start = JRequest::getInt('limitstart',0);
    $listClass = acymailing::get('class.list');
    $listid = acymailing::getCID('listid');
    if(empty($listid) AND !empty($menuparams)){
    	$listid = $menuparams->get('listid');
    }
    if(empty($listid)){
    	return JError::raiseError( 404, 'Mailing List not found' );
    }
    $oneList = $listClass->get($listid);
    if(empty($oneList->listid)){
    	return JError::raiseError( 404, 'Mailing List not found : '.$listid );
    }
	$access = null;
    $access->frontEndManament = false;
    $access->frontEndAccess = true;
    if(!$access->frontEndManament AND (!$oneList->published OR !$oneList->visible OR !$access->frontEndAccess)){
		if(empty($my->id)){
			$uri		= JFactory::getURI();
			$url  = 'index.php?option=com_user&view=login';
			$url .= '&return='.base64_encode($uri->toString());
			$app->redirect($url, JText::_('ACY_NOTALLOWED') );
	    	return false;
		}else{
			$app->enqueueMessage(JText::_('ACY_NOTALLOWED'),'error');
			$app->redirect(acymailing::completeLink('lists',false,true));
			return false;
		}
    }
    if($config->get('open_popup')) JHTML::_('behavior.modal','a.modal');
    if(!empty($menuparams)){
    	$values->suffix = $menuparams->get('pageclass_sfx','');
    	$values->page_title = $menuparams->get('page_title');
    	$values->show_page_title = $menuparams->get('show_page_title',1);
    }else{
    	$values->suffix = '';
    	$values->show_page_title = 1;
    }
	$values->show_description = $config->get('show_description',1);
	$values->show_headings = $config->get('show_headings',1);
	$values->show_senddate = $config->get('show_senddate',1);
	$values->filter = $config->get('show_filter',1);
    if(empty($values->page_title)) $values->page_title = $oneList->name;
    if(empty($menuparams)){
    	$pathway->addItem(JText::_('MAILING_LISTS'),acymailing::completeLink('lists'));
    	$pathway->addItem($values->page_title);
    }else{
    	$pathway->addItem($values->page_title);
    }
	$document	=& JFactory::getDocument();
	$document->setTitle( $values->page_title );
	$db =& JFactory::getDBO();
	$searchMap = array('a.mailid','a.subject','a.alias');
	$filters = array();
	if(!empty($pageInfo->search)){
		$searchVal = '\'%'.$db->getEscaped($pageInfo->search,true).'%\'';
		$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
	}
	$filters[] = 'a.type = \'news\'';
	if(!$access->frontEndManament){
		$filters[] = 'a.published = 1';
		$filters[] = 'a.visible = 1';
	}
	$filters[] = 'c.listid = '.$oneList->listid;
	$selection = array_merge($searchMap,array('a.senddate','a.visible','a.published','a.fromname','a.fromemail','a.replyname','a.replyemail','a.userid'));
	$query = 'SELECT '.implode(',',$selection);
	$query .= ' FROM '.acymailing::table('listmail').' as c';
	$query .= ' LEFT JOIN '.acymailing::table('mail').' as a on a.mailid = c.mailid ';
	$query .= ' WHERE ('.implode(') AND (',$filters).')';
	$query .= ' ORDER BY '.acymailing::secureField($pageInfo->filter->order->value).' '.acymailing::secureField($pageInfo->filter->order->dir).', c.mailid DESC';
	$db->setQuery($query,$pageInfo->limit->start,$pageInfo->limit->value);
	$rows = $db->loadObjectList();
	$pageInfo->elements->page = count($rows);
	if(!empty($pageInfo->search)){
		$rows = acymailing::search($pageInfo->search,$rows);
	}
	if($pageInfo->limit->value > $pageInfo->elements->page){
		$pageInfo->elements->total = $pageInfo->limit->start + $pageInfo->elements->page;
	}else{
		$queryCount = 'SELECT COUNT(c.mailid) FROM '.acymailing::table('listmail').' as c';
		$queryCount .= ' LEFT JOIN '.acymailing::table('mail').' as a on a.mailid = c.mailid ';
		$queryCount .= ' WHERE ('.implode(') AND (',$filters).')';
		$db->setQuery($queryCount);
		$pageInfo->elements->total = $db->loadResult();
	}
	jimport('joomla.html.pagination');
	$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
$js = 'function tableOrdering( order, dir, task ){
		var form = document.adminForm;
		form.filter_order.value 	= order;
		form.filter_order_Dir.value	= dir;
		document.adminForm.submit( task );
	}';
	$doc =& JFactory::getDocument();
	$doc->addScriptDeclaration( $js);
	$this->assignRef('access',$access);
	$this->assignRef('rows',$rows);
	$this->assignRef('values',$values);
	$this->assignRef('list',$oneList);
	$this->assignRef('pagination',$pagination);
	$this->assignRef('pageInfo',$pageInfo);
	$this->assignRef('config',$config);
	$this->assignRef('my',$my);
  }
	function view(){
		$app =& JFactory::getApplication();
	    $pathway	=& $app->getPathway();
	    $my = JFactory::getUser();
		$frontEndManagement = false;
	    $listid = acymailing::getCID('listid');
	    if(!empty($listid)){
	       	$listClass = acymailing::get('class.list');
       		$oneList = $listClass->get($listid);
       		if(!empty($oneList->visible) AND $oneList->published){
       			$pathway->addItem($oneList->name,acymailing::completeLink('archive&listid='.$oneList->listid.':'.$oneList->alias));
       		}
       		if(!empty($oneList->listid) AND acymailing::level(3)){
       			if(!empty($my->id) AND (int)$my->id == (int)$oneList->userid){
    				$frontEndManagement = true;
    			}
				if(!empty($my->id)){
		    		if($oneList->access_manage == 'all' OR acymailing::isAllowed($oneList->access_manage)){
		    			 $frontEndManagement = true;
		    		}
		    	}
       		}
	    }
	    $mailid = acymailing::getCID('mailid');
		if(empty($mailid)){
			$db=&JFactory::getDBO();
			$query = 'SELECT m.`mailid` FROM `#__acymailing_list` as l LEFT JOIN `#__acymailing_listmail` as lm ON l.listid=lm.listid LEFT JOIN `#__acymailing_mail` as m on lm.mailid = m.mailid';
			$query .= ' WHERE l.`visible` = 1 AND l.`published` = 1 AND m.`visible`= 1 AND m.`published` = 1';
			if(!empty($listid)) $query .= ' AND l.`listid` = '.(int) $listid;
			$query .= ' ORDER BY m.`mailid` DESC LIMIT 1';
			$db->setQuery($query);
			$mailid = $db->loadResult();
			if(empty($mailid)) return JError::raiseError( 404, 'Newsletter not found');
    	}
		$access_sub = true;
    	$mailClass = acymailing::get('helper.mailer');
    	$oneMail = $mailClass->load($mailid);
    	if(empty($oneMail->mailid)){
    		return JError::raiseError( 404, 'Newsletter not found : '.$mailid );
    	}
    	if(!$frontEndManagement AND (!$access_sub OR !$oneMail->published OR !$oneMail->visible)){
    		$key = JRequest::getString('key');
    		if(empty($key) OR $key !== $oneMail->key){
    			$app->enqueueMessage('You can not have access to this e-mail','error');
    			$app->redirect(acymailing::completeLink('lists',false,true));
    			return false;
    		}
    	}

		$subkeys = JRequest::getString('subid',JRequest::getString('sub'));
		if(!empty($subkeys)){
				$db =& JFactory::getDBO();
				$subid = intval(substr($subkeys,0,strpos($subkeys,'-')));
				$subkey = substr($subkeys,strpos($subkeys,'-')+1);
				$db->setQuery('SELECT * FROM '.acymailing::table('subscriber').' WHERE `subid` = '.$db->Quote($subid).' AND `key` = '.$db->Quote($subkey).' LIMIT 1');
				$receiver = $db->loadObject();
		}
		if(empty($receiver) AND !empty($my->email)){
			$userClass = acymailing::get('class.subscriber');
			$receiver = $userClass->get($my->email);
		}
		if(empty($receiver)){
			$receiver = null;
			$receiver->name = JText::_('VISITOR');
		}
		$oneMail->sendHTML = true;
		$mailClass->dispatcher->trigger('acymailing_replaceusertagspreview',array(&$oneMail,&$receiver));
    	$pathway->addItem($oneMail->subject);
		$document	=& JFactory::getDocument();
		$document->setTitle( $oneMail->subject );
		if (!empty($oneMail->metadesc)) {
			$document->setDescription( $oneMail->metadesc );
		}
		if (!empty($oneMail->metakey)) {
			$document->setMetadata('keywords', $oneMail->metakey);
		}
    	$this->assignRef('mail',$oneMail);
    	$this->assignRef('frontEndManagement',$frontEndManagement);
		$this->assignRef('list',$oneList);
		$this->assignRef('config',acymailing::config());
		$this->assignRef('my',$my);
	}
}
