<?php
/**
 * @package JV Facebook plugin for Joomla! 1.5
 * @author http://www.zootemplate.com
 * @copyright (C) 2010- zootemplate.Com
 * @license PHP files are GNU/GPL
**/
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class JElementMultiselect extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'multiselect';
	function fetchElement($name, $value, &$node, $control_name){
		$arySelection = array();
		$class = "inputbox";
		$html_return = JHTML::_('select.genericlist',  $arySelection, 'jv_selection_selected', 'class="'.$class.'" MULTIPLE size="10" ', 'id', 'title', $value, 'jv_selection_selected' );
		return $html_return;
	}
}