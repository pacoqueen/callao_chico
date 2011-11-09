<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class JButtonAcyactions extends JButton
{
	var $_name = 'Acyactions';
	function fetchButton( $type='Acyactions')
	{
		JHTML::_('behavior.modal','a.modal');
		$url = JURI::base()."index.php?option=com_acymailing&ctrl=filter&tmpl=component";
		$top = 0; $left = 0;
		$width = 700;
		$height = 500;
		$text	= JText::_('ACTIONS');
		$class	= "icon-32-acyaction";
		$js = "i = 0;
			mylink = 'index.php?option=com_acymailing&ctrl=filter&tmpl=component&subid=';
			while(window.document.getElementById('cb'+i)){
				if(window.document.getElementById('cb'+i).checked) mylink += window.document.getElementById('cb'+i).value+',';
				i++;
			}
			this.href= mylink;
			";
		$html	= "<a class=\"modal\" onclick=\"$js\" href=\"$url\" rel=\"{handler: 'iframe', size: {x: $width, y: $height}}\">\n";
		$html .= "<span class=\"$class\" title=\"$text\"></span>$text</a>\n";
		return $html;
	}
	function fetchId($name)
	{
		return "toolbar-popup-Acyactions";
	}
}