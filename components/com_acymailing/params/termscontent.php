<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
if(version_compare(JVERSION,'1.6.0','<')){
	class JElementTermscontent extends JElement
	{
		function fetchElement($name, $value, &$node, $control_name)
		{
			JHTML::_('behavior.modal','a.modal');
			$link = 'index.php?option=com_content&amp;task=element&amp;tmpl=component&amp;object=content';
			$text = '<input class="inputbox" id="'.$control_name.'termscontent" name="'.$control_name.'[termscontent]" type="text" size="20" value="'.$value.'">';
			$text .= '<a class="modal" id="termscontent" title="'.JText::_('Select one content which will be displayed for the Terms & Conditions').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}"><button onclick="return false">'.JText::_('Select').'</button></a>';
			$js = "function jSelectArticle(id, title, object) {
				document.getElementById('".$control_name."termscontent').value = id;
				try{ document.getElementById('sbox-window').close(); }catch(err){ window.parent.SqueezeBox.close(); }
			}";
			$doc =& JFactory::getDocument();
			$doc->addScriptDeclaration($js);
			return $text;
		}
	}
}else{
	class JFormFieldTermscontent extends JFormField
	{
		var $type = 'termscontent';
		function getInput() {
			JHTML::_('behavior.modal','a.modal');
			$link = 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;object=content&amp;function=jSelectArticle';
			$text = '<input class="inputbox" id="termscontent" name="'.$this->name.'" type="text" size="20" value="'.$this->value.'">';
			$text .= '<a class="modal" id="termscontent" title="'.JText::_('Select one content which will be displayed for the Terms & Conditions').'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}"><button onclick="return false">'.JText::_('Select').'</button></a>';
			$js = "function jSelectArticle(id, title,catid, object) {
				document.getElementById('termscontent').value = id;
				try{ document.getElementById('sbox-window').close(); }catch(err){ window.parent.SqueezeBox.close(); }
			}";
			$doc =& JFactory::getDocument();
			$doc->addScriptDeclaration($js);
			return $text;
		}
	}
}