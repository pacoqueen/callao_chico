<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class delaydispType{
	function display($value){
		if(empty($value)) return 0;
		$type = 'ACY_SECONDS';
		if($value >= 60  AND $value%60 == 0){
			$value = (int) $value / 60;
			$type = 'ACY_MINUTES';
			if($value >=60 AND $value%60 == 0){
				$type = 'HOURS';
				$value = $value/ 60;
				if($value >=24 AND $value%24 == 0){
					$type = 'DAYS';
					$value = $value / 24;
					if($value >= 30 AND $value%30 == 0){
						$type = 'MONTHS';
						$value = $value / 30;
					}elseif($value >=7 AND $value%7 == 0){
						$type = 'WEEKS';
						$value = $value / 7;
					}
				}
			}
		}
		return $value.' '.JText::_($type);
	}
}