<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class TagViewTag extends JView
{
	function display($tpl = null)
	{
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function tag(){
		$doc =& JFactory::getDocument();
		$doc->addStyleSheet( ACYMAILING_CSS.'frontendedition.css' );
		JPluginHelper::importPlugin('acymailing');
		$dispatcher = &JDispatcher::getInstance();
		$tagsfamilies = $dispatcher->trigger('acymailing_getPluginType');
		$defaultFamily = reset($tagsfamilies);
		$app =& JFactory::getApplication();
		$fctplug = $app->getUserStateFromRequest( ACYMAILING_COMPONENT.".tag", 'fctplug',$defaultFamily->function,'cmd' );
		ob_start();
		$defaultContents = $dispatcher->trigger($fctplug);
		$defaultContent = ob_get_clean();
		$js = 'function insertTag(){if(window.parent.insertTag(window.document.getElementById(\'tagstring\').value)) {try{window.parent.document.getElementById(\'sbox-window\').close();}catch(err){window.parent.SqueezeBox.close();}}}';
		$js.= 'function setTag(tagvalue){window.document.getElementById(\'tagstring\').value = tagvalue;if(tagvalue.length>0){showTagButton();}else{hideTagButton();}}';
		$js .='function showTagButton(){window.document.getElementById(\'insertButton\').style.display = \'inline\'; window.document.getElementById(\'tagstring\').style.display=\'inline\';}';
		$js .='function hideTagButton(){window.document.getElementById(\'insertButton\').style.display = \'none\';}';
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
		$this->assignRef('fctplug',$fctplug);
		$this->assignRef('type',JRequest::getString('type','news'));
		$this->assignRef('defaultContent',$defaultContent);
		$this->assignRef('tagsfamilies',$tagsfamilies);
		$app =& JFactory::getApplication();
		$this->assignRef('app',$app);
		$this->assignRef('ctrl',JRequest::getString('ctrl'));
	}
}