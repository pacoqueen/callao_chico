<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class NewsletterViewNewsletter extends JView
{
	var $type = 'news';
	var $ctrl = 'newsletter';
	var $nameListing = 'NEWSLETTERS';
	var $nameForm = 'NEWSLETTER';
	var $icon = 'newsletter';
	var $aclCat = 'newsletters';
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
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $paramBase.".filter_order", 'filter_order',	'a.mailid','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower( $pageInfo->search );
		$selectedList = $app->getUserStateFromRequest( $paramBase."filter_list",'filter_list',0,'int');
		$selectedCreator = $app->getUserStateFromRequest( $paramBase."filter_creator",'filter_creator',0,'int');
		$pageInfo->limit->value = $app->getUserStateFromRequest( $paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	=& JFactory::getDBO();
		$searchMap = array('a.mailid','a.alias','a.subject','a.fromname','a.fromemail','a.replyname','a.replyemail','a.userid','b.name','b.username','b.email');
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.$database->getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$filters[] = 'a.type = \''.$this->type.'\'';
		if(!empty($selectedList)) $filters[] = 'c.listid = '.$selectedList;
		if(!empty($selectedCreator)) $filters[] = 'a.userid = '.$selectedCreator;
		$selection = array_merge($searchMap,array('a.created','a.frequency','a.senddate','a.published','a.type','a.visible'));
		if(empty($selectedList)){
			$query = 'SELECT '.implode(',',$selection).' FROM '.acymailing::table('mail').' as a';
			$queryCount = 'SELECT COUNT(a.mailid) FROM '.acymailing::table('mail').' as a';
		}else{
			$query = 'SELECT '.implode(',',$selection).' FROM '.acymailing::table('listmail').' as c';
			$query .= ' LEFT JOIN '.acymailing::table('mail').' as a on a.mailid = c.mailid ';
			$queryCount = 'SELECT COUNT(c.mailid) FROM '.acymailing::table('listmail').' as c';
			$queryCount .= ' LEFT JOIN '.acymailing::table('mail').' as a on a.mailid = c.mailid ';
		}
		$query.= ' LEFT JOIN '.acymailing::table('users',false).' as b on a.userid = b.id ';
		$query.= ' WHERE ('.implode(') AND (',$filters).')';
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$queryCount.= ' LEFT JOIN '.acymailing::table('users',false).' as b on a.userid = b.id ';
		$queryCount.= ' WHERE ('.implode(') AND (',$filters).')';
		$database->setQuery($query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = acymailing::search($pageInfo->search,$rows);
		}
		$database->setQuery($queryCount);
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		acymailing::setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$bar = & JToolBar::getInstance('toolbar');
		$buttonPreview = JText::_('ACY_PREVIEW');
		if($this->type == 'autonews'){
			JToolBarHelper::custom('generate', 'process', '',JText::_('GENERATE'),false);
		}elseif($this->type == 'news'){
			$buttonPreview.=' / '.JText::_('SEND');
		}
		JToolBarHelper::custom('preview', 'preview', '',$buttonPreview);
		JToolBarHelper::divider();
		JToolBarHelper::addNew();
		JToolBarHelper::editList();
		if(acymailing::isAllowed($config->get('acl_'.$this->aclCat.'_delete','all'))) JToolBarHelper::deleteList(JText::_('ACY_VALIDDELETEITEMS'));
		JToolBarHelper::spacer();
		JToolBarHelper::custom( 'copy', 'copy.png', 'copy.png', JText::_('ACY_COPY') );
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl.'-listing');
		if(acymailing::isAllowed($config->get('acl_cpanel_manage','all'))) $bar->appendButton( 'Link', 'acymailing', JText::_('ACY_CPANEL'), acymailing::completeLink('dashboard') );
		$filters = null;
		$listmailType = acymailing::get('type.listsmail');
		$listmailType->type = $this->type;
		$mailcreatorType = acymailing::get('type.mailcreator');
		$mailcreatorType->type = $this->type;
		$filters->list = $listmailType->display('filter_list',$selectedList);
		$filters->creator = $mailcreatorType->display('filter_creator',$selectedCreator);
		$this->assignRef('filters',$filters);
		$this->assignRef('toggleClass',acymailing::get('helper.toggle'));
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
		$this->assignRef('delay',acymailing::get('type.delaydisp'));
		$this->assignRef('config',$config);
	}
	function form(){
		$mailid = acymailing::getCID('mailid');
		$templateClass = acymailing::get('class.template');
		$config =& acymailing::config();
		if(!empty($mailid)){
			$mailClass = acymailing::get('class.mail');
			$mail = $mailClass->get($mailid);
			if(!empty($mail->tempid)){
				$myTemplate = $templateClass->get($mail->tempid);
			}
		}else{
			$mail->created = time();
			$mail->published = 0;
			if($this->type == 'followup') $mail->published = 1;
			$mail->visible = 1;
			$mail->html = 1;
			$mail->body = '';
			$mail->altbody = '';
			$mail->tempid = 0;
			$templateid = JRequest::getInt('templateid');
			if(empty($templateid)){
				$myTemplate = $templateClass->getDefault();
			}else{
				$myTemplate = $templateClass->get($templateid);
			}
			if(!empty($myTemplate->tempid)){
				$mail->body = acymailing::absoluteURL($myTemplate->body);
				$mail->altbody = $myTemplate->altbody;
				$mail->tempid = $myTemplate->tempid;
				$mail->subject = $myTemplate->subject;
				$mail->replyname = $myTemplate->replyname;
				$mail->replyemail = $myTemplate->replyemail;
				$mail->fromname = $myTemplate->fromname;
				$mail->fromemail = $myTemplate->fromemail;
			}
			if($this->type == 'autonews'){
				$mail->frequency = 604800;
			}
		}
		$sentbyname = '';
		if(!empty($mail->sentby)){
			$db =& JFactory::getDBO();
			$db->setQuery('SELECT `name` FROM `#__users` WHERE `id`= '.intval($mail->sentby).' LIMIT 1');
			$sentbyname = $db->loadResult();
		}
		$this->assignRef('sentbyname',$sentbyname);
		if(JRequest::getVar('task','') == 'replacetags'){
			$mailerHelper = acymailing::get('helper.mailer');
			JPluginHelper::importPlugin('acymailing');
			$dispatcher = &JDispatcher::getInstance();
			$dispatcher->trigger('acymailing_replacetags',array(&$mail));
			if(!empty($mail->altbody)) $mail->altbody = $mailerHelper->textVersion($mail->altbody,false);
		}
		$extraInfos = '';
		$values = null;
		if($this->type == 'followup'){
			$campaignid = JRequest::getInt('campaign',0);
			$extraInfos .= '&campaign='.$campaignid;
			$values->delay = acymailing::get('type.delay');
			$this->assignRef('campaignid',$campaignid);
		}else{
			$listmailClass = acymailing::get('class.listmail');
			$lists = $listmailClass->getLists($mailid);
		}
		acymailing::setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task=edit&mailid='.$mailid.$extraInfos);

		$bar = & JToolBar::getInstance('toolbar');
		if(empty($mail->mailid) && acymailing::isAllowed($config->get('acl_templates_view','all'))){
			$bar->appendButton( 'Popup', 'acytemplate', JText::_('ACY_TEMPLATE'), "index.php?option=com_acymailing&ctrl=template&task=theme&tmpl=component",750,550);
		}
		if(acymailing::isAllowed($config->get('acl_tags_view','all'))) $bar->appendButton( 'Acytags',$this->type);
		if(in_array($this->type,array('news','followup')) && acymailing::isAllowed($config->get('acl_tags_view','all'))){
			JToolBarHelper::custom('replacetags', 'replacetag', '',JText::_('REPLACE_TAGS'), false);
		}
		$buttonPreview = JText::_('ACY_PREVIEW');
		if($this->type=='news'){
			$buttonPreview .= ' / '.JText::_('SEND');
		}
		JToolBarHelper::divider();
		JToolBarHelper::custom('savepreview', 'preview', '',$buttonPreview, false);
		JToolBarHelper::save();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl.'-form');
		jimport('joomla.html.pane');
		$tabs	=& JPane::getInstance('tabs');
		$values->maxupload = (acymailing::bytes(ini_get('upload_max_filesize')) > acymailing::bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize');
		$toggleClass = acymailing::get('helper.toggle');
		$editor = acymailing::get('helper.editor');
		$editor->setTemplate($mail->tempid);
		$editor->name = 'editor_body';
		$editor->content = $mail->body;
		$editor->prepareDisplay();
		$js = "function updateAcyEditor(htmlvalue){";
			$js .= 'if(htmlvalue == \'0\'){window.document.getElementById("htmlfieldset").style.display = \'none\'}else{window.document.getElementById("htmlfieldset").style.display = \'block\'}';
		$js .= '}';
		$js .='window.addEvent(\'load\', function(){ updateAcyEditor('.$mail->html.'); });';
		$script = 'function addFileLoader(){
		var divfile=window.document.getElementById("loadfile");
		var input = document.createElement(\'input\');
		input.type = \'file\';
		input.size = \'30\';
		input.name = \'attachments[]\';
		divfile.appendChild(document.createElement(\'br\'));
		divfile.appendChild(input);}
		';
		if(version_compare(JVERSION,'1.6.0','<')){
			$script .= 'function submitbutton(pressbutton){
						if (pressbutton == \'cancel\') {
							submitform( pressbutton );
							return;
						}';
		}else{
			$script .= 'Joomla.submitbutton = function(pressbutton) {
						if (pressbutton == \'cancel\') {
							Joomla.submitform(pressbutton,document.adminForm);
							return;
						}';
		}
		$script .= 'if(window.document.getElementById("subject").value.length < 2){alert(\''.JText::_('ENTER_SUBJECT',true).'\'); return false;}';
		$script .= $editor->jsCode();
		if(version_compare(JVERSION,'1.6.0','<')){
			$script .= 'submitform( pressbutton );} ';
		}else{
			$script .= 'Joomla.submitform(pressbutton,document.adminForm);}; ';
		}
		$script .= "function changeTemplate(newhtml,newtext,newsubject,stylesheet,fromname,fromemail,replyname,replyemail,tempid){
			if(newhtml.length>2){".$editor->setContent('newhtml')."}
			var vartextarea =$('altbody'); if(newtext.length>2) vartextarea.innerHTML = newtext;
			document.getElementById('tempid').value = tempid;
			if(fromname.length>1){document.getElementById('fromname').value = fromname;}
			if(fromemail.length>1){document.getElementById('fromemail').value = fromemail;}
			if(replyname.length>1){document.getElementById('replyname').value = replyname;}
			if(replyemail.length>1){document.getElementById('replyemail').value = replyemail;}
			if(newsubject.length>1){document.getElementById('subject').value = newsubject;}
		}
		";
		$script .= "function insertTag(tag){ try{jInsertEditorText(tag,'editor_body'); return true;} catch(err){alert('Your editor does not enable AcyMailing to automatically insert the tag, please copy/paste it manually in your Newsletter'); return false;}}";
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $js.$script );
		if($this->type == 'autonews'){
			JHTML::_('behavior.modal','a.modal');
			$this->assignRef('delay',acymailing::get('type.delay'));
			$this->assignRef('generatingMode',acymailing::get('type.generatemode'));
			$link = 'index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=email&amp;task=edit&amp;mailid=notification_autonews';
			$values->editnotification = '<a class="modal" href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><button onclick="return false">'.JText::_('EDIT_NOTIFICATION_MAIL').'</button></a>';
		}
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('lists',$lists);
		$this->assignRef('editor',$editor);
		$this->assignRef('mail',$mail);
		$this->assignRef('tabs',$tabs);
		$this->assignRef('values',$values);
	}
	function preview(){
		$app =& JFactory::getApplication();
		$mailid = acymailing::getCID('mailid');
		$config = acymailing::config();
		JHTML::_('behavior.modal','a.modal');
		$mailerHelper = acymailing::get('helper.mailer');
		$mail = $mailerHelper->load($mailid);
		$user =& JFactory::getUser();
		$userClass = acymailing::get('class.subscriber');
		$receiver = $userClass->get($user->email);
		$mail->sendHTML = true;
		$mailerHelper->dispatcher->trigger('acymailing_replaceusertagspreview',array(&$mail,&$receiver));
		if(!empty($mail->altbody)) $mail->altbody = $mailerHelper->textVersion($mail->altbody,false);
		$listmailClass = acymailing::get('class.listmail');
		$lists = $listmailClass->getReceivers($mail->mailid,true,false);
		$receiversClass = acymailing::get('type.testreceiver');
		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName();
		$infos = null;
		$infos->receiver_type = $app->getUserStateFromRequest( $paramBase.".receiver_type", 'receiver_type', '','string' );
		$infos->test_html = $app->getUserStateFromRequest( $paramBase.".test_html", 'test_html', 1,'int' );
		$infos->test_email = $app->getUserStateFromRequest( $paramBase.".test_email", 'test_email', '','string' );
		acymailing::setTitle(JText::_('ACY_PREVIEW').' : '.$mail->subject,$this->icon,$this->ctrl.'&task=preview&mailid='.$mailid);
		$bar = & JToolBar::getInstance('toolbar');
		if($this->type == 'news'){
			if(acymailing::level(1) && acymailing::isAllowed($config->get('acl_newsletters_schedule','all'))){
				if($mail->published == 2){
					JToolBarHelper::custom('unschedule', 'unschedule', '',JText::_('UNSCHEDULE'), false);
				}else{
					$bar->appendButton( 'Popsched', "index.php?option=com_acymailing&ctrl=send&task=scheduleready&tmpl=component&mailid=".$mailid);
				}
			}
			if(acymailing::isAllowed($config->get('acl_newsletters_send','all')))$bar->appendButton( 'Popup', 'send', JText::_('SEND'), "index.php?option=com_acymailing&ctrl=send&task=sendready&tmpl=component&mailid=".$mailid);
			JToolBarHelper::divider();
		}
		JToolBarHelper::custom('edit', 'edit', '',JText::_('ACY_EDIT'), false);
		JToolBarHelper::cancel('cancel',JText::_('ACY_CLOSE'));
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl.'-preview');
		$this->assignRef('lists',$lists);
		$this->assignRef('infos',$infos);
		$this->assignRef('receiverClass',$receiversClass);
		$this->assignRef('mail',$mail);
	}
}