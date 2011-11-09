<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class toggleHelper{
	var $ctrl = 'toggle';
	var $extra = '';
	function _getToggle($column,$table = ''){
		$params = null;
		$params->mode = 'pictures';
		if($column == 'published' AND !in_array($table,array('plugins','list'))){
			if(version_compare(JVERSION,'1.6.0','<')){
				$params->pictures = array(0=>'images/publish_r.png',1=>'images/publish_g.png',2=>ACYMAILING_IMAGES.'schedule.png');
			}else{
				$params->aclass = array(0=>'grid_false',1=>'grid_true',2=>'acyschedule');
			}
			$params->description = array(0=>JText::_('PUBLISH_CLICK',true),1=>JText::_('UNPUBLISH_CLICK',true),2=>JText::_('UNSCHEDULE_CLICK',true));
			$params->values = array(0=>1,1=>0,2=>0);
			return $params;
		}elseif($column == 'status'){
			$params->mode = 'class';
			$params->class = array(-1=>'roundsubscrib roundunsub',1=>'roundsubscrib roundsub',2=>'roundsubscrib roundconf');
			$params->description = array(-1=>JText::_('SUBSCRIBE_CLICK',true),1=>JText::_('UNSUBSCRIBE_CLICK',true),2=>JText::_('CONFIRMATION_CLICK',true) );
			$params->values = array(-1=>1,1=>-1,2=>1);
			return $params;
		}
		if(version_compare(JVERSION,'1.6.0','<')){
			$params->pictures = array(0=>'images/publish_x.png',1=>'images/tick.png');
		}else{
			$params->aclass = array(0=>'grid_false',1=>'grid_true');
		}
		$params->values = array(0=>1,1=>0);
		return $params;
	}
	function toggleText($action= '',$value='',$table='',$text=''){
		static $jsincluded = false;
		static $id = 0;
		$id++;
		if(!$jsincluded){
			$jsincluded = true;
			$js = "function joomToggleText(id,newvalue,table){
				window.document.getElementById(id).className = 'onload';
				try{
					new Ajax('index.php?option=com_acymailing&tmpl=component&ctrl=toggle&task='+id+'&value='+newvalue+'&table='+table,{ method: 'get', update: $(id), onComplete: function() {	window.document.getElementById(id).className = 'loading'; }}).request();
				}catch(err){
					new Request({url:'index.php?option=com_acymailing&tmpl=component&ctrl=toggle&task='+id+'&value='+newvalue+'&table='+table,method: 'get', onComplete: function(response) { $(id).innerHTML = response; window.document.getElementById(id).className = 'loading'; }}).send();
				}
			}";
			$doc =& JFactory::getDocument();
			$doc->addScriptDeclaration( $js );
		}
		if(!$action) return;
		return '<span id="'.$action.'_'.$value.'" ><a href="javascript:void(0);" onclick="joomToggleText(\''.$action.'_'.$value.'\',\''.$value.'\',\''.$table.'\')">'.$text.'</a></span>';
	}
	function toggle($id,$value,$table,$extra = null){
		$column = substr($id,0,strpos($id,'_'));
		$params = $this->_getToggle($column,$table);
		$newValue = $params->values[$value];
		if($params->mode == 'pictures'){
			static $pictureincluded = false;
			if(!$pictureincluded){
				$pictureincluded = true;
				$js = "function joomTogglePicture(id,newvalue,table){
					window.document.getElementById(id).className = 'onload';
					try{
						new Ajax('index.php?option=com_acymailing&tmpl=component&ctrl=toggle&task='+id+'&value='+newvalue+'&table='+table,{ method: 'get', update: $(id), onComplete: function() {	window.document.getElementById(id).className = 'loading'; }}).request();
					}catch(err){
						new Request({url:'index.php?option=com_acymailing&tmpl=component&ctrl=toggle&task='+id+'&value='+newvalue+'&table='+table,method: 'get', onComplete: function(response) { $(id).innerHTML = response; window.document.getElementById(id).className = 'loading'; }}).send();
					}
				}";
				$doc =& JFactory::getDocument();
				$doc->addScriptDeclaration( $js );
			}
			$desc = empty($params->description[$value]) ? '' : $params->description[$value];
			if(empty($params->pictures)){
				$text = ' ';
				$class='class="'.$params->aclass[$value].'"';
			}else{
				$text = '<img src="'.$params->pictures[$value].'"/>';
				$class = '';
			}
			return '<a href="javascript:void(0);" '.$class.' onclick="joomTogglePicture(\''.$id.'\',\''.$newValue.'\',\''.$table.'\')" title="'.$desc.'">'.$text.'</a>';
		}elseif($params->mode == 'class'){
			static $classincluded = false;
			if(!$classincluded){
				$classincluded = true;
				$js = "function joomToggleClass(id,newvalue,table,extra){
					var mydiv=$(id); mydiv.innerHTML = ''; mydiv.className = 'onload';
					try{
						new Ajax('index.php?option=com_acymailing&tmpl=component&ctrl=toggle&task='+id+'&value='+newvalue+'&table='+table+'&extra[color]='+extra,{ method: 'get', update: $(id), onComplete: function() {	window.document.getElementById(id).className = 'loading'; }}).request();
					}catch(err){
						new Request({url:'index.php?option=com_acymailing&tmpl=component&ctrl=toggle&task='+id+'&value='+newvalue+'&table='+table+'&extra[color]='+extra,method: 'get', onComplete: function(response) { $(id).innerHTML = response; window.document.getElementById(id).className = 'loading'; }}).send();
					}
					}";
				$doc =& JFactory::getDocument();
				$doc->addScriptDeclaration( $js );
			}

			$desc = empty($params->description[$value]) ? '' : $params->description[$value];
			$return = '<a href="javascript:void(0);" onclick="joomToggleClass(\''.$id.'\',\''.$newValue.'\',\''.$table.'\',\''.urlencode($extra['color']).'\');" title="'.$desc.'"><div class="'. $params->class[$value] .'" style="background-color:'.$extra['color'].'">';
			if(!empty($extra['tooltip'])) $return .= acymailing::tooltip($extra['tooltip'], @$extra['tooltiptitle'],'','&nbsp;&nbsp;&nbsp;&nbsp;');
			$return .= '</div></a>';
			return $return;
		}
	}
	function display($column,$value){
		$params = $this->_getToggle($column);
		if(empty($params->pictures)){
			return '<span class="'.$params->aclass[$value].'"> </span>';
		}else{
			return '<img src="'.$params->pictures[$value].'"/>';
		}
	}
	function delete($lineId,$elementids,$table,$confirm = false,$text = ''){
		static $deleteJS = false;
		if(!$deleteJS){
			$deleteJS = true;
			$js = "function joomDelete(lineid,elementids,table,reqconfirm){
				if(reqconfirm){
					if(!confirm('".JText::_('ACY_VALIDDELETEITEMS',true)."')) return false;
				}
				try{
					new Ajax('index.php?option=com_acymailing&tmpl=component&ctrl=".$this->ctrl.$this->extra."&task=delete&value='+elementids+'&table='+table, { method: 'get', onComplete: function() {window.document.getElementById(lineid).style.display = 'none';}}).request();
				}catch(err){
					new Request({url:'index.php?option=com_acymailing&tmpl=component&ctrl=".$this->ctrl.$this->extra."&task=delete&value='+elementids+'&table='+table,method: 'get', onComplete: function() { window.document.getElementById(lineid).style.display = 'none'; }}).send();
				}
			}";
			$doc =& JFactory::getDocument();
			$doc->addScriptDeclaration( $js );
		}
		if(empty($text)) $text = '<img src="'.ACYMAILING_IMAGES.'delete.png"/>';
		return '<a href="javascript:void(0);" onclick="joomDelete(\''.$lineId.'\',\''.$elementids.'\',\''.$table.'\','. ($confirm ? 'true' : 'false').')">'.$text.'</a>';
	}
}
