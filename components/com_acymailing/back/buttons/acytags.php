<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class JButtonAcytags extends JButton
{
	var $_name = 'Acytags';
	function fetchButton( $type='Acytags',$newstype = 'news')
	{
		JHTML::_('behavior.modal');
		$url = JURI::base()."index.php?option=com_acymailing&ctrl=tag&task=tag&tmpl=component&type=".$newstype;
		$top = 0; $left = 0;
		$width = 750;
		$height = 550;
		$text	= JText::_('TAGS');
		$class	= "icon-32-tag";
		$html	= "<a class=\"modal\" onclick=\"try{IeCursorFix();}catch(e){}\" href=\"$url\" rel=\"{handler: 'iframe', size: {x: $width, y: $height}}\">\n";
		$html .= "<span class=\"$class\" title=\"$text\"></span>$text</a>\n";
		return $html;
	}
	function fetchId($name)
	{
		return "toolbar-popup-Acytags";
	}
}