<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class bounceactionType{
	function bounceactionType(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'noaction',JText::_('NO_ACTION'));
		$this->values[] = JHTML::_('select.option', 'remove',JText::_('REMOVE_SUB'));
		$this->values[] = JHTML::_('select.option', 'unsub',JText::_('UNSUB_USER'));
		$this->values[] = JHTML::_('select.option', 'sub',JText::_('SUBSCRIBE_USER'));
		$this->values[] = JHTML::_('select.option', 'block',JText::_('BLOCK_USER'));
		$this->values[] = JHTML::_('select.option', 'delete',JText::_('DELETE_USER'));
		$this->config = acymailing::config();
		$this->lists = acymailing::get('type.lists');
		array_shift($this->lists->values);
		$js = "function updateSubAction(num){";
			$js .= "myAction = window.document.getElementById('bounce_action_'+num).value;";
			$js .= "if(myAction == 'sub') {window.document.getElementById('config_bounce_action_lists_'+num).style.display = '';}else{window.document.getElementById('config_bounce_action_lists_'+num).style.display = 'none';}";
		$js .= '}';
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
	}
	function display($num,$value){
		$js ='window.addEvent(\'domready\', function(){ updateSubAction(\''.$num.'\'); });';
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
		$return = JHTML::_('select.genericlist',   $this->values, 'config[bounce_action_'.$num.']', 'class="inputbox" size="1" onchange="updateSubAction(\''.$num.'\');"', 'value', 'text', $value ,'bounce_action_'.$num);
		$return .= $this->lists->display('config[bounce_action_lists_'.$num.']',$this->config->get('bounce_action_lists_'.$num),false);
		return $return;
	}
}