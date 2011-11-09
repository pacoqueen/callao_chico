<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class sef_acymailing {
    function create($string) {
		$string = str_replace("&amp;", "&", preg_replace('#(index\.php\??)#i','',$string));
		$query = array();
		$allValues = explode('&',$string);
		foreach($allValues as $oneValue){
			list($var,$val) = explode('=',$oneValue);
			$query[$var] = $val;
		}
		$segments = array();
		if (isset($query['ctrl'])) {
			$segments[] = $query['ctrl'];
			unset( $query['ctrl'] );
			if (isset($query['task'])) {
				$segments[] = $query['task'];
				unset( $query['task'] );
			}
		}elseif(isset($query['view'])){
			$segments[] = $query['view'];
			unset( $query['view'] );
			if(isset($query['layout'])){
				$segments[] = $query['layout'];
				unset( $query['layout'] );
			}
		}
		unset($query['option']);
		if(!empty($query)){
			foreach($query as $name => $value){
				$segments[] = $value;
			}
		}
        return implode('/',$segments);
    }
}