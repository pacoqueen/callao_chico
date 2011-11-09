<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class charsetType{
	function charsetType(){
		$charsets = array(
					'BIG5'=>'BIG5',//Iconv,mbstring
					'ISO-8859-1'=>'ISO-8859-1',//Iconv,mbstring
					'ISO-8859-2'=>'ISO-8859-2',//Iconv,mbstring
					'ISO-8859-3'=>'ISO-8859-3',//Iconv,mbstring
					'ISO-8859-4'=>'ISO-8859-4',//Iconv,mbstring
					'ISO-8859-5'=>'ISO-8859-5',//Iconv,mbstring
					'ISO-8859-6'=>'ISO-8859-6',//Iconv,mbstring
					'ISO-8859-7'=>'ISO-8859-7',//Iconv,mbstring
					'ISO-8859-8'=>'ISO-8859-8',//Iconv,mbstring
					'ISO-8859-9'=>'ISO-8859-9',//Iconv,mbstring
					'ISO-8859-10'=>'ISO-8859-10',//Iconv,mbstring
					'ISO-8859-13'=>'ISO-8859-13',//Iconv,mbstring
					'ISO-8859-14'=>'ISO-8859-14',//Iconv,mbstring
					'ISO-8859-15'=>'ISO-8859-15',//Iconv,mbstring
					'US-ASCII'=>'US-ASCII', //Iconv,mbstring
					'UTF-7'=>'UTF-7',//Iconv,mbstring
					'UTF-8'=>'UTF-8',//Iconv,mbstring
					'Windows-1251'=>'Windows-1251', //Iconv,mbstring
					'Windows-1252'=>'Windows-1252' //Iconv,mbstring
					);
		$this->values = array();
		foreach($charsets as $code => $charset){
			$this->values[] = JHTML::_('select.option', $code,$charset);
		}
	}
	function display($map,$value){
		return JHTML::_('select.genericlist', $this->values, $map , 'size="1"', 'value', 'text', $value);
	}
}