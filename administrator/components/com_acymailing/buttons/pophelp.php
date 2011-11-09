<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class JButtonPophelp extends JButton
{
	var $_name = 'Pophelp';
	function fetchButton( $type='Pophelp', $namekey = '', $id = 'pophelp' )
	{
		JHTML::_('behavior.mootools');
		$doc =& JFactory::getDocument();
		$config =& acymailing::config();
		$level = $config->get('level');
		$url = ACYMAILING_HELPURL.$namekey.'&level='.$level;
		$iFrame = "'<iframe src=\'$url\' width=\'100%\' height=\'100%\' scrolling=\'auto\'></iframe>'";
		$js = "var openHelp = true; function displayDoc(){var box=$('iframedoc'); if(openHelp){box.innerHTML = ".$iFrame.";box.style.display = 'block';box.style.height = '0';}";
		$js .= "try{
                   var fx = box.effects({duration: 1500, transition:
					Fx.Transitions.Quart.easeOut});
					if(openHelp){
						fx.start({'height': 300});
					}else{
						fx.start({'height': 0}).chain(function() {
							box.innerHTML='';
							box.setStyle('display','none');
						});
					}
				}catch(err){
					box.style.height = '300px';
					var myVerticalSlide = new Fx.Slide('iframedoc');
 					if(openHelp){
						myVerticalSlide.slideIn();
					}else{
						myVerticalSlide.slideOut().chain(function() {
						box.innerHTML='';
						box.setStyle('display','none');
					});
				}
			} openHelp = !openHelp;}";
		$doc->addScriptDeclaration( $js );
		return '<a href="'.$url.'" target="_blank" onclick="displayDoc();return false;" class="toolbar"><span class="icon-32-help" title="'.JText::_('ACY_HELP',true).'"></span>'.JText::_('ACY_HELP').'</a>';
	}
	function fetchId( $type='Pophelp', $html = '', $id = 'pophelp' )
	{
		return $this->_name.'-'.$id;
	}
}