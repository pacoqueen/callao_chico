<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class userViewuser extends JView
{
	function display($tpl = null)
	{
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function modify(){
		global $Itemid;
		$app =& JFactory::getApplication();
		$pathway	=& $app->getPathway();
		$document	=& JFactory::getDocument();
		$listsClass = acymailing::get('class.list');
		$subscriberClass = acymailing::get('class.subscriber');
		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();
		if(empty($menu) AND !empty($Itemid)){
			$menus->setActive($Itemid);
			$menu	= $menus->getItem($Itemid);
		}
		if (is_object( $menu )) {
			jimport('joomla.html.parameter');
			$menuparams = new JParameter( $menu->params );
			if(!empty($menuparams)){
				$this->assignRef('introtext',$menuparams->get('introtext'));
				$this->assignRef('finaltext',$menuparams->get('finaltext'));
			}
		}
		$subscriber = $subscriberClass->identify(true);
		if(empty($subscriber)){
			$subscription = $listsClass->getLists('listid');
			$subscriber = null;
			$subscriber->html = 1;
			$subscriber->subid = 0;
			$subscriber->key = 0;
			if(!empty($subscription)){
				foreach($subscription as $id => $onesub){
					$subscription[$id]->status = 1;
					if(!empty($menuparams) AND strtolower($menuparams->get('listschecked','all')) != 'all' AND !in_array($id,explode(',',$menuparams->get('listschecked','all')))){
						$subscription[$id]->status = 0;
					}
				}
			}
			$pathway->addItem(JText::_('SUBSCRIPTION'));
			$document->setTitle( JText::_('SUBSCRIPTION'));
		}else{
			$subscription = $subscriberClass->getSubscription($subscriber->subid,'listid');
			$pathway->addItem(JText::_('MODIFY_SUBSCRIPTION'));
			$document->setTitle( JText::_('MODIFY_SUBSCRIPTION'));
		}
		acymailing::initJSStrings();
		if(!empty($menuparams) AND strtolower($menuparams->get('lists','all')) != 'all'){
			$visibleLists = strtolower($menuparams->get('lists','all'));
			if($visibleLists == 'none') $subscription = array();
			else{
				$newSubscription = array();
				$visiblesListsArray = explode(',',$visibleLists);
				foreach($subscription as $id => $onesub){
					if(in_array($id,$visiblesListsArray)) $newSubscription[$id] = $onesub;
				}
				$subscription = $newSubscription;
			}
		}
		$displayLists = false;
		foreach($subscription as $oneSub){
			if(!empty( $oneSub->published) AND $oneSub->visible){
				$displayLists = true;
				break;
			}
		}
		$this->assignRef('status',acymailing::get('type.festatus'));
		$this->assignRef('subscription',$subscription);
		$this->assignRef('subscriber',$subscriber);
		$this->assignRef('displayLists',$displayLists);
		$this->assignRef('config',acymailing::config());
	}
	function saveunsub(){
		$subscriberClass = acymailing::get('class.subscriber');
		$subscriber = $subscriberClass->identify();
		$this->assignRef('subscriber',$subscriber);
		$listid = JRequest::getInt('listid');
		if(!empty($listid)){
			$listClass = acymailing::get('class.list');
			$mylist = $listClass->get($listid);
			$this->assignRef('list',$mylist);
		}
	}
	function unsub(){
		$subscriberClass = acymailing::get('class.subscriber');
		$config = acymailing::config();
		$this->assignRef('config',$config);
		$subscriber = $subscriberClass->identify();
		$this->assignRef('subscriber',$subscriber);
		$intro = JText::_('');
		$replace = array();
		foreach($subscriber as $oneProp => $oneVal){
			$replace['{user:'.$oneProp.'}'] = $oneVal;
		}
		$intro = str_replace('UNSUB_INTRO',JText::_('UNSUB_INTRO'),$config->get('unsub_intro','UNSUB_INTRO'));
		$intro = '<div class="unsubintro">'.str_replace(array_keys($replace),$replace,$intro).'</div>';
		$this->assignRef('intro',$intro);
		$mailid = JRequest::getInt('mailid');
		$this->assignRef('mailid',$mailid);
		if(!empty($mailid)){
			$classListmail = acymailing::get('class.listmail');
			$lists = $classListmail->getLists($mailid);
			$this->assignRef('lists',$lists);
		}
		$app =& JFactory::getApplication();
		$pathway	=& $app->getPathway();
		$pathway->addItem(JText::_('UNSUBSCRIBE'));
		$document	=& JFactory::getDocument();
		$document->setTitle( JText::_('UNSUBSCRIBE'));
	}
}