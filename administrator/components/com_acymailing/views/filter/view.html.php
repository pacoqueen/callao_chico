<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class FilterViewFilter extends JView
{
	function display($tpl = null)
	{
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function form(){
		$db =& JFactory::getDBO();
		$config = acymailing::config();
		$filid = acymailing::getCID('filid');
		$filterClass = acymailing::get('class.filter');
		if(!empty($filid)){
			$filter = $filterClass->get($filid);
		}else{
			$filter = null;
			$filter->action = JRequest::getVar('action');
			$filter->filter = JRequest::getVar('filter');
			$filter->published = 1;
		}
		JPluginHelper::importPlugin('acymailing');
		$this->dispatcher = &JDispatcher::getInstance();
		$typesFilters = array();
		$typesActions = array();
		$outputFilters = implode('',$this->dispatcher->trigger('onAcyDisplayFilters',array(&$typesFilters)));
		$outputActions = implode('',$this->dispatcher->trigger('onAcyDisplayActions',array(&$typesActions)));
		$typevaluesFilters = array();
		$typevaluesActions = array();
		$typevaluesFilters[] = JHTML::_('select.option', '',JText::_('FILTER_SELECT'));
		$typevaluesActions[] = JHTML::_('select.option', '',JText::_('ACTION_SELECT'));
		$doc =& JFactory::getDocument();
		$js = "function updateFilter(filterNum){";
		foreach($typesFilters as $oneType => $oneName){
			$typevaluesFilters[] = JHTML::_('select.option', $oneType,$oneName);
			$js .= "filterArea = 'filter'+filterNum+'$oneType';
				if(window.document.getElementById(filterArea)){window.document.getElementById(filterArea).style.display = 'none';}";
		}
		$js .= "filterArea = 'filter'+filterNum+window.document.getElementById('filtertype'+filterNum).value;
				if(window.document.getElementById(filterArea)){window.document.getElementById(filterArea).style.display = 'block';}
			}";
		$js .= "function updateAction(actionNum){";
		foreach($typesActions as $oneType => $oneName){
			$typevaluesActions[] = JHTML::_('select.option', $oneType,$oneName);
			$js .= "actionArea = 'action'+actionNum+'$oneType';
				if(window.document.getElementById(actionArea)){window.document.getElementById(actionArea).style.display = 'none';}";
		}
		$js .= "actionArea = 'action'+actionNum+window.document.getElementById('actiontype'+actionNum).value;
				if(window.document.getElementById(actionArea)){window.document.getElementById(actionArea).style.display = 'block';}
			}";
		$js .= "var numFilters = 0;
				var numActions = 0;
				function addFilter(){
					var newdiv = document.createElement('div');
					newdiv.id = 'filter'+numFilters;
					newdiv.className = 'plugarea';
					newdiv.innerHTML = document.getElementById('filters_original').innerHTML.replace(/__num__/g, numFilters);
					document.getElementById('allfilters').appendChild(newdiv); updateFilter(numFilters); numFilters++; }
				function addAction(){
					var newdiv = document.createElement('div');
					newdiv.id = 'action'+numActions;
					newdiv.className = 'plugarea';
					newdiv.innerHTML = document.getElementById('actions_original').innerHTML.replace(/__num__/g, numActions);
					document.getElementById('allactions').appendChild(newdiv); updateAction(numActions); numActions++; }";
		$js .= "window.addEvent('domready', function(){ addFilter(); addAction(); });";
		if(version_compare(JVERSION,'1.6.0','<')){
			$js .= 	'function submitbutton(pressbutton){
						if (pressbutton != \'save\') {
							submitform( pressbutton );
							return;
						}';
		}else{
			$js .= 	'Joomla.submitbutton = function(pressbutton) {
						if (pressbutton != \'save\') {
							Joomla.submitform(pressbutton,document.adminForm);
							return;
						}';
		}
		$js .= 	"if(window.document.getElementById('filterinfo').style.display == 'none'){
						window.document.getElementById('filterinfo').style.display = 'block';
						try{allspans = window.document.getElementById('toolbar-save').getElementsByTagName(\"span\"); allspans[0].className = 'icon-32-apply';}catch(err){}
						return false;}
					if(window.document.getElementById('title').value.length < 2){alert('".JText::_('ENTER_TITLE',true)."'); return false;}";
		if(version_compare(JVERSION,'1.6.0','<')){
		$js .= 	"submitform( pressbutton );} ";
		}else{ $js .= 	"Joomla.submitform(pressbutton,document.adminForm);}; "; }
		$doc->addScriptDeclaration( $js );
		$js = '';
		$data = array('action','filter');
		foreach($data as $datatype){
			if(empty($filter->$datatype)) continue;
			foreach($filter->{$datatype}['type'] as $num => $oneType){
				if(empty($oneType)) continue;
				$js .= "while(!document.getElementById('".$datatype."type$num')){add".ucfirst($datatype)."();}
						document.getElementById('".$datatype."type$num').value= '$oneType';
						update".ucfirst($datatype)."($num);";
				if(empty($filter->{$datatype}[$num][$oneType])) continue;
				foreach($filter->{$datatype}[$num][$oneType] as $key => $value){
					$js .= "document.adminForm.elements['".$datatype."[$num][$oneType][$key]'].value = '".addslashes(str_replace(array("\n","\r"),' ',$value))."';";
				}
			}
		}
		$listid = JRequest::getInt('listid');
		if(!empty($listid)){
			$js .= "document.getElementById('actiontype0').value = 'list'; updateAction(0); document.adminForm.elements['action[0][list][selectedlist]'].value = '".$listid."';";
		}
		$doc->addScriptDeclaration( "window.addEvent('domready', function(){ $js });" );
		$triggers = array();
		$triggers['daycron'] = JText::_('AUTO_CRON_FILTER');
		$nextDate = $config->get('cron_plugins_next');
		if(!empty($nextDate)){
			$triggers['daycron'] .= ' ('.JText::_('NEXT_RUN').' : '.acymailing::getDate($nextDate,'%d %B %H:%M').')';
		}
		$triggers['subcreate'] = JText::_('ON_USER_CREATE');
		$triggers['subchange'] = JText::_('ON_USER_CHANGE');
		$this->dispatcher->trigger('onAcyDisplayTriggers',array(&$triggers));
		$name = empty($filter->name) ? '' : ' : '.$filter->name;
		acymailing::setTitle(JText::_('ACY_FILTER').$name,'filter','filter&task=edit&filid='.$filid);
		$bar = & JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Confirm', JText::_('PROCESS_CONFIRMATION'), 'process', JText::_('PROCESS'), 'process', false, false );
		JToolBarHelper::divider();
		if(acymailing::level(3)){
			JToolBarHelper::save();
			if(!empty($filter->filid)) $bar->appendButton( 'Link', 'new', JText::_('NEW'), acymailing::completeLink('filter&task=edit&filid=0') );
		}
		$bar->appendButton( 'Link', 'cancel', JText::_('ACY_CLOSE'), acymailing::completeLink('list') );
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','filter');
		$subid = JRequest::getString('subid');
		if(!empty($subid)){
			$subArray = explode(',',trim($subid,','));
			JArrayHelper::toInteger($subArray);
			$db->setQuery('SELECT `name`,`email` FROM `#__acymailing_subscriber` WHERE `subid` IN ('.implode(',',$subArray).')');
			$users = $db->loadObjectList();
			if(!empty($users)){
				$this->assignRef('users',$users);
				$this->assignRef('subid',$subid);
			}
		}
		$this->assignRef('typevaluesFilters',$typevaluesFilters);
		$this->assignRef('typevaluesActions',$typevaluesActions);
		$this->assignRef('outputFilters',$outputFilters);
		$this->assignRef('outputActions',$outputActions);
		$this->assignRef('filter',$filter);
		$this->assignRef('triggers',$triggers);
		$doc->addStyleSheet( ACYMAILING_CSS.'frontendedition.css' );
		if(acymailing::level(3) AND JRequest::getCmd('tmpl') != 'component'){
			$db->setQuery('SELECT * FROM #__acymailing_filter ORDER BY `published` DESC, `filid` DESC');
			$filters = $db->loadObjectList();
			$this->assignRef('toggleClass',acymailing::get('helper.toggle'));
			$this->assignRef('filters',$filters);
		}
	}
}