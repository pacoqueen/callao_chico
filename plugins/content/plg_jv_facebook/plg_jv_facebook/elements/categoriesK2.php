<?php 
/**
 * @package JV Facebook Plugin for Joomla! 1.5
 * @author http://www.zootemplate.com
 * @copyright (C) 2011- zootemplate.Com
 * @license PHP files are GNU/GPL
**/
defined('_JEXEC') or die( 'Restricted access' );
class JElementCategoriesK2 extends JElement
{
	var	$_name = 'CategoriesK2';	
	var $_controlName = '';
	function fetchElement($name, $childalue, &$node, $control_name){
		$this->_controlName = $name;
		$categories = JElementCategoriesK2::_fetchElement(0, '', array());
        $jvitems 	= array();
		$jvitems[] 	= JHTML::_('select.option',  '', JText::_( 'ALL CATEGORY' ) );
		foreach ( $categories as $item ) {
			$jvitems[] = JHTML::_('select.option',  $item->id, '&nbsp;&nbsp;&nbsp;'. $item->treename );
		}
		$out = JHTML::_('select.genericlist',  $jvitems, ''.$control_name.'['.$name.'][]', 'class="inputbox" style="width:95%;" multiple="multiple" size="10"', 'value', 'text', $childalue );
        return $out;
	}
    function fetchChild($parent) {
        $db = &JFactory::getDBO();
        $query = "SELECT * FROM #__k2_categories WHERE parent = '{$parent}' AND published=1";
		$db->setQuery( $query );
		$cats = $db->loadObjectList();

        return $cats;
    }

    function _fetchElement( $id, $indent, $list, $maxlevel=999, $level=0, $type=1 )
	{
        $children = JElementCategoriesK2::fetchChild($id);

		if (@$children && $level <= $maxlevel)
		{
			foreach ($children as $child)
			{
				$id = $child->id;

				if ( $type ) {
					$space = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				} else {
					$space = '&nbsp;&nbsp;';
				}

				if ($child->parent == 0) {
					$txt 	= $child->name;
				} else {
					$txt 	= $child->name;
				}
				$pt = $child->parent;
				$list[$id] = $child;
				$list[$id]->treename = "{$indent}{$txt}";
				$list[$id]->children = count( @$children);
				$list = JElementCategoriesK2::_fetchElement( $id, $indent . $space, $list, $maxlevel, $level+1, $type );
			}
		}
		return $list;
	}
}
?>