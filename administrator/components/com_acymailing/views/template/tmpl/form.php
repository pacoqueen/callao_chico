<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="acy_content" >
<div id="iframedoc"></div>
<form action="index.php" method="post" name="adminForm">
<fieldset class="adminform" id="sendtest" style="display:none">
	<legend><?php echo JText::_( 'SEND_TEST' ); ?></legend>
	<table>
		<tr>
			<td valign="top">
				<?php echo JText::_( 'SEND_TEST_TO' ); ?>
			</td>
			<td>
				<?php echo $this->receiverClass->display('receiver_type',$this->infos->receiver_type); ?>
				<div id="emailfield" style="display:none" ><?php echo JText::_('EMAIL_ADDRESS')?> <input class="inputbox" type="text" name="test_email" size="40" value="<?php echo $this->infos->test_email;?>" /></div>
			</td>
		</tr>
		<tr>
			<td>
			</td>
			<td>
				<button type="submit" onclick="submitbutton('test');return false;"><?php echo JText::_('SEND_TEST')?></button>
			</td>
		</tr>
	</table>
</fieldset>
	<table class="adminform">
		<tr>
			<td width="50%" valign="top">
				<table class="adminform">
					<tr>
						<td>
							<label for="name">
								<?php echo JText::_( 'TEMPLATE_NAME' ); ?>
							</label>
						</td>
						<td>
							<input type="text" name="data[template][name]" id="name" class="inputbox" size="50" value="<?php echo $this->escape(@$this->template->name); ?>" />
						</td>
					</tr>
					<tr>
						<td>
				        	<label for="published">
				          	<?php echo JText::_( 'ACY_PUBLISHED' ); ?>
				        	</label>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist', "data[template][published]" , '',@$this->template->published); ?>
						</td>
					</tr>
					<tr>
						<td>
							<label for="default">
			          		<?php echo JText::_( 'ACY_DEFAULT' ); ?>
			        		</label>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist', "data[template][premium]" , '',@$this->template->premium); ?>
						</td>
					</tr>
					<tr>
						<td>
						<label for="bgcolor">
			          		<?php echo JText::_( 'BACKGROUND_COLOUR' ); ?>
		        		</label>
						</td>
						<td>
							<?php echo $this->colorBox->displayAll('','styles[color_bg]',@$this->template->styles['color_bg']); ?>
						</td>
					</tr>
					<tr>
						<td>
							<label for="description">
								<?php echo JText::_( 'ACY_DESCRIPTION' ); ?>
							</label>
						</td>
						<td>
							<textarea id="description" name="editor_description" cols="60" rows="10"><?php echo @$this->template->description; ?></textarea>
						</td>
					</tr>
					<tr>
						<td>
							<label for="subject">
								<?php echo JText::_( 'JOOMEXT_SUBJECT' ); ?>
							</label>
						</td>
						<td>
							<input id="subject" name="data[template][subject]" class="inputbox" size="80" value="<?php echo $this->escape(@$this->template->subject); ?>" />
						</td>
					</tr>
					<tr>
				    	<td class="paramlist_key">
				    		<label for="fromname"><?php echo JText::_( 'FROM_NAME' ); ?></label>
				    	</td>
				    	<td class="paramlist_value">
				    		<input class="inputbox" id="fromname" type="text" name="data[template][fromname]" size="40" value="<?php echo $this->escape(@$this->template->fromname); ?>" />
				    	</td>
				    </tr>
					<tr>
				    	<td class="paramlist_key">
				    		<label for="fromemail"><?php echo JText::_( 'FROM_ADDRESS' ); ?></label>
				    	</td>
				    	<td class="paramlist_value">
				    		<input class="inputbox" id="fromemail" type="text" name="data[template][fromemail]" size="40" value="<?php echo $this->escape(@$this->template->fromemail); ?>" />
				    	</td>
				    </tr>
				    <tr>
						<td class="paramlist_key">
							<label for="replyname"><?php echo JText::_( 'REPLYTO_NAME' ); ?></label>
				    	</td>
				    	<td class="paramlist_value">
				    		<input class="inputbox" id="replyname" type="text" name="data[template][replyname]" size="40" value="<?php echo $this->escape(@$this->template->replyname); ?>" />
				    	</td>
				    </tr>
				    <tr>
						<td class="paramlist_key">
							<label for="replyemail"><?php echo JText::_( 'REPLYTO_ADDRESS' ); ?></label>
				    	</td>
				    	<td class="paramlist_value">
				    		<input class="inputbox" id="replyemail" type="text" name="data[template][replyemail]" size="40" value="<?php echo $this->escape(@$this->template->replyemail); ?>" />
				    	</td>
					</tr>
				</table>
			</td>
			<td valign="top">
			<?php
				echo $this->tabs->startPane( 'template_css');
				echo $this->tabs->startPanel( JText::_( 'STYLE_IND' ), 'template_css_classes'); ?>
					<br style="font-size:1px"/>
					<table width="100%">
						<tbody id="classtable">
							<tr>
								<td><span style="<?php echo $this->escape(@$this->template->styles['tag_h1']);?>">Title h1</span></td>
								<td><input type="text" size="50" name="styles[tag_h1]" value="<?php echo $this->escape(@$this->template->styles['tag_h1']); ?>"/></td>
							</tr>
							<tr>
								<td><span style="<?php echo $this->escape(@$this->template->styles['tag_h2']);?>">Title h2</span></td>
								<td><input type="text" size="50" name="styles[tag_h2]" value="<?php echo $this->escape(@$this->template->styles['tag_h2']); ?>"/></td>
							</tr>
							<tr>
								<td><span style="<?php echo $this->escape(@$this->template->styles['tag_h3']);?>">Title h3</span></td>
								<td><input type="text" size="50" name="styles[tag_h3]" value="<?php echo $this->escape(@$this->template->styles['tag_h3']); ?>"/></td>
							</tr>
							<tr>
								<td><span style="<?php echo $this->escape(@$this->template->styles['tag_h4']);?>">Title h4</span></td>
								<td><input type="text" size="50" name="styles[tag_h4]" value="<?php echo $this->escape(@$this->template->styles['tag_h4']); ?>"/></td>
							</tr>
							<tr>
								<td><span style="<?php echo $this->escape(@$this->template->styles['tag_h5']);?>">Title h5</span></td>
								<td><input type="text" size="50" name="styles[tag_h5]" value="<?php echo $this->escape(@$this->template->styles['tag_h5']); ?>"/></td>
							</tr>
							<tr>
								<td><span style="<?php echo $this->escape(@$this->template->styles['tag_h6']);?>">Title h6</span></td>
								<td><input type="text" size="50" name="styles[tag_h6]" value="<?php echo $this->escape(@$this->template->styles['tag_h6']); ?>"/></td>
							</tr>
							<tr>
								<td><span style="<?php echo $this->escape(@$this->template->styles['tag_a']);?>">Link a</span></td>
								<td><input type="text" size="50" name="styles[tag_a]" value="<?php echo $this->escape(@$this->template->styles['tag_a']); ?>"/></td>
							</tr>
							<tr>
								<td><ul style="<?php echo $this->escape(@$this->template->styles['tag_ul']);?>"><li style="<?php echo $this->escape(@$this->template->styles['tag_li']);?>">ul</li><li style="<?php echo $this->escape(@$this->template->styles['tag_li']);?>">li</li></ul></td>
								<td><input type="text" size="50" name="styles[tag_ul]" value="<?php echo $this->escape(@$this->template->styles['tag_ul']); ?>"/>
								<br/><input type="text" size="50" name="styles[tag_li]" value="<?php echo $this->escape(@$this->template->styles['tag_li']); ?>"/></td>
							</tr>
							<tr>
								<td><span style="<?php echo $this->escape(@$this->template->styles['acymailing_unsub']);?>"><?php echo JText::_('STYLE_UNSUB'); ?></span></td>
								<td><input type="text" size="50" name="styles[acymailing_unsub]" value="<?php echo $this->escape(@$this->template->styles['acymailing_unsub']); ?>"/></td>
							</tr>
							<tr>
								<td><span style="<?php echo $this->escape(@$this->template->styles['acymailing_content']);?>"><?php echo JText::_('CONTENT_AREA'); ?></span></td>
								<td><input type="text" size="50" name="styles[acymailing_content]" value="<?php echo $this->escape(@$this->template->styles['acymailing_content']); ?>"/></td>
							</tr>
							<tr>
								<td><span style="<?php echo $this->escape(@$this->template->styles['acymailing_title']);?>"><?php echo JText::_('CONTENT_HEADER'); ?></span></td>
								<td><input type="text" size="50" name="styles[acymailing_title]" value="<?php echo $this->escape(@$this->template->styles['acymailing_title']); ?>"/></td>
							</tr>
							<tr>
								<td><span style="<?php echo $this->escape(@$this->template->styles['acymailing_readmore']);?>"><?php echo JText::_('CONTENT_READMORE'); ?></span></td>
								<td><input type="text" size="50" name="styles[acymailing_readmore]" value="<?php echo $this->escape(@$this->template->styles['acymailing_readmore']); ?>"/></td>
							</tr>
							<tr>
								<td><span style="<?php echo $this->escape(@$this->template->styles['acymailing_online']);?>"><?php echo JText::_('STYLE_VIEW'); ?></span></td>
								<td><input type="text" size="50" name="styles[acymailing_online]" value="<?php echo $this->escape(@$this->template->styles['acymailing_online']); ?>"/></td>
							</tr>
							<?php unset($this->template->styles['acymailing_unsub']); unset($this->template->styles['acymailing_content']); unset($this->template->styles['acymailing_title']); unset($this->template->styles['acymailing_readmore']); unset($this->template->styles['acymailing_online']);
							unset($this->template->styles['tag_a']);unset($this->template->styles['tag_ul']);unset($this->template->styles['tag_li']);unset($this->template->styles['tag_h1']);unset($this->template->styles['tag_h2']);unset($this->template->styles['tag_h3']);unset($this->template->styles['tag_h4']);
							unset($this->template->styles['tag_h5']);unset($this->template->styles['tag_h6']);unset($this->template->styles['color_bg']);
							if(!empty($this->template->styles)){
								foreach($this->template->styles as $className => $style){
								?>
									<tr>
									<td><span style="<?php echo $this->escape($style);?>"><?php echo $className ?></span></td>
									<td><input type="text" size="50" name="styles[<?php echo $className; ?>]" value="<?php echo $this->escape($style); ?>"/></td>
									</tr>
								<?php
								}
							}?>
						</tbody>
					</table>
					<a onclick="addStyle();return false;" href="#" ><?php echo JText::_('ADD_STYLE'); ?></a>
				<?php echo $this->tabs->startPanel( JText::_( 'TEMPLATE_STYLESHEET' ), 'template_css_stylesheet');?>
				<br style="font-size:1px"/>
				<?php
						$messages = array();
						if(version_compare(PHP_VERSION, '5.0.0', '<')) $messages[] = 'Please make sure you use at least PHP 5.0.0';
						if(!class_exists('DOMDocument')) $messages[] = 'DOMDocument class not found';
						else{
							$xmldoc =@ new DOMDocument;
							if(!is_object($xmldoc) || !method_exists($xmldoc,'loadHTML')){
								$messages[] = 'Please make sure that php_domxml.dll on windows is removed before using the domdocument class as they cannot coexist.';
							}
						}
						if(!function_exists('mb_convert_encoding')) $messages[] = 'The php extension mbstring is not installed';
						if(!empty($messages)){
							$messages[] = 'The stylesheet can not be used';
							acymailing::display($messages,'warning');
						}else{ ?>
							<textarea name="data[template][stylesheet]" style="width:100%" rows="25"><?php echo @$this->template->stylesheet; ?></textarea>
						<?php }
					echo $this->tabs->endPanel();
					echo $this->tabs->endPane(); ?>
			</td>
		</tr>
	</table>
	<fieldset class="adminform" width="100%">
		<legend><?php echo JText::_( 'HTML_VERSION' ); ?></legend>
		<?php echo $this->editor->display(); ?>
	</fieldset>
	<fieldset class="adminform" >
		<legend><?php echo JText::_( 'TEXT_VERSION' ); ?></legend>
		<textarea style="width:100%" rows="20" name="data[template][altbody]" id="altbody" ><?php echo @$this->template->altbody; ?></textarea>
	</fieldset>
	<div class="clr"></div>
	<input type="hidden" name="cid[]" value="<?php echo @$this->template->tempid; ?>" />
	<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="template" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>