<?php 
/**
 * @package JV Facebook Plugin for Joomla! 1.5
 * @author http://www.zootemplate.com
 * @copyright (C) 2011- zootemplate.Com
 * @license PHP files are GNU/GPL
**/
defined('_JEXEC') or die( 'Restricted access' );
class JElementCategories extends JElement
{

	var	$_name = 'Categories';
	
	var $_controlName = '';
	/**
	 * fetch Element 
	 */
	function fetchElement($name, $value, &$node, $control_name){
		$db = &JFactory::getDBO();
		$query = '
			SELECT 
				c.section,
				s.title AS section_title,
				c.id AS cat_id,
				c.title AS cat_title 
			FROM #__sections AS s
			INNER JOIN #__categories c ON c.section = s.id
			WHERE s.published=1
			AND c.published = 1
			ORDER BY c.section, c.title
			';
		$db->setQuery( $query );
		$cats = $db->loadObjectList();
		$JVCate=array();
		$JVCate[0]->id = '';
		$JVCate[0]->title = JText::_("ALL CATEGORY");
		$section_id = 0;
		foreach ($cats as $cat) {
			if($section_id != $cat->section) {
				$section_id = $cat->section;
				
				$cat->id = $cat->section;
				$cat->title = $cat->section_title;
				$optgroup = JHTML::_('select.optgroup', $cat->title, 'id', 'title');
				array_push($JVCate, $optgroup);
			}
			$cat->id = $cat->cat_id;
			$cat->title = $cat->cat_title;
			array_push($JVCate, $cat);
		}
		return JHTML::_('select.genericlist',  $JVCate, ''.$control_name.'['.$name.'][]', 'class="inputbox" style="width:95%;" multiple="multiple" size="10"', 'id', 'title', $value );
	}
}
?>