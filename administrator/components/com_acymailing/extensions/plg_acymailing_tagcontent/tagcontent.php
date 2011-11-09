<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class plgAcymailingTagcontent extends JPlugin
{
	function plgAcymailingTagcontent(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin =& JPluginHelper::getPlugin('acymailing', 'tagcontent');
			$this->params = new JParameter( $plugin->params );
		}
    }
	 function acymailing_getPluginType() {
		$app =& JFactory::getApplication();
	 	if($this->params->get('frontendaccess') == 'none' AND !$app->isAdmin()) return;
	 	$onePlugin = null;
	 	$onePlugin->name = JText::_('JOOMLA_CONTENT');
	 	$onePlugin->function = 'acymailingtagcontent_show';
	 	$onePlugin->help = 'plugin-tagcontent';
	 	return $onePlugin;
	 }
	 function acymailingtagcontent_show(){
		$app =& JFactory::getApplication();
		$pageInfo = null;
		$my = JFactory::getUser();
		$paramBase = ACYMAILING_COMPONENT.'.tagcontent';
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $paramBase.".filter_order", 'filter_order',	'a.id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower( $pageInfo->search );
		$pageInfo->filter_cat = $app->getUserStateFromRequest( $paramBase.".filter_cat", 'filter_cat','','int' );
		$pageInfo->contenttype = $app->getUserStateFromRequest( $paramBase.".contenttype", 'contenttype','|type:intro','string' );
		$pageInfo->author = $app->getUserStateFromRequest( $paramBase.".author", 'author','','string' );
		$pageInfo->titlelink = $app->getUserStateFromRequest( $paramBase.".titlelink", 'titlelink','|link','string' );
		$pageInfo->lang = $app->getUserStateFromRequest( $paramBase.".lang", 'lang','','string' );
		$pageInfo->pict = $app->getUserStateFromRequest( $paramBase.".pict", 'pict','1','string' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$picts = array();
		$picts[] = JHTML::_('select.option', "1",JText::_('JOOMEXT_YES'));
		$pictureHelper = acymailing::get('helper.acypict');
		if($pictureHelper->available()) $picts[] = JHTML::_('select.option', "resized",JText::_('RESIZED'));
		$picts[] = JHTML::_('select.option', "0",JText::_('JOOMEXT_NO'));
		$db =& JFactory::getDBO();
		$searchFields = array('a.id','a.title','a.alias','a.created_by','b.name','b.username');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.$db->getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchFields)." LIKE $searchVal";
		}
		if(!empty($pageInfo->filter_cat)){
			$filters[] = "a.catid = ".$pageInfo->filter_cat;
		}
		if($this->params->get('displayart','all') == 'onlypub'){
			$filters[] = "a.state = 1";
		}else{
			$filters[] = "a.state != -2";
		}
		if(version_compare(JVERSION,'1.6.0','<')){
			$filters[] = 'a.`access` <= ' . (int)$my->get('aid');
		}else{
			$groups = implode(',', $my->authorisedLevels());
			$filters[] = 'a.`access` IN ('.$groups.')';
		}
		if($this->params->get('frontendaccess') == 'author' AND !$app->isAdmin()){
			$filters[] = "a.created_by = ".intval($my->id);
		}
		$whereQuery = '';
		if(!empty($filters)){
			$whereQuery = ' WHERE ('.implode(') AND (',$filters).')';
		}
		$query = 'SELECT SQL_CALC_FOUND_ROWS a.id,a.created,a.title,a.alias,a.catid,a.sectionid,b.name,b.username,a.created_by FROM '.acymailing::table('content',false).' as a';
		$query .=' LEFT JOIN `#__users` AS b ON b.id = a.created_by';
		if(!empty($whereQuery)) $query.= $whereQuery;
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$db->setQuery($query,$pageInfo->limit->start,$pageInfo->limit->value);
		$rows = $db->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = acymailing::search($pageInfo->search,$rows);
		}
		$db->setQuery('SELECT FOUND_ROWS()');
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);
		if(version_compare(JVERSION,'1.6.0','<')){
			$query = 'SELECT a.id, a.id as catid, a.title as category, b.title as section, b.id as secid from #__categories as a ';
			$query .= 'INNER JOIN #__sections as b on a.section = b.id ORDER BY b.ordering,a.ordering';
			$db->setQuery($query);
			$categories = $db->loadObjectList('id');
			$categoriesValues = array();
			$categoriesValues[] = JHTML::_('select.option', '',JText::_('ACY_ALL'));
			$currentSec = '';
			foreach($categories as $catid => $oneCategorie){
				if($currentSec != $oneCategorie->section){
					if(!empty($currentSec)) $this->values[] = JHTML::_('select.option', '</OPTGROUP>');
					$categoriesValues[] = JHTML::_('select.option', '<OPTGROUP>',$oneCategorie->section);
					$currentSec = $oneCategorie->section;
				}
				$categoriesValues[] = JHTML::_('select.option', $catid,$oneCategorie->category);
			}
		}else{
			$query = "SELECT * from #__categories WHERE `extension` = 'com_content' ORDER BY lft ASC";
			$db->setQuery($query);
			$categories = $db->loadObjectList('id');
			$categoriesValues = array();
			$categoriesValues[] = JHTML::_('select.option', '',JText::_('ACY_ALL'));
			foreach($categories as $catid => $oneCategorie){
				$categories[$catid]->title = str_repeat('- - ',$categories[$catid]->level).$categories[$catid]->title;
				$categoriesValues[] = JHTML::_('select.option', $catid,$categories[$catid]->title);
			}
		}
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		jimport('joomla.html.pane');
		$tabs	=& JPane::getInstance('tabs');
		echo $tabs->startPane( 'joomlacontent_tab');
		echo $tabs->startPanel( JText::_( 'JOOMLA_CONTENT' ), 'joomlacontent_content');
	?>
		<br style="font-size:1px"/>
		<script language="javascript" type="text/javascript">
		<!--
			var selectedContents = new Array();
			function applyContent(contentid,rowClass){
				if(selectedContents[contentid]){
					window.document.getElementById('content'+contentid).className = rowClass;
					delete selectedContents[contentid];
				}else{
					window.document.getElementById('content'+contentid).className = 'selectedrow';
					selectedContents[contentid] = 'content';
				}
				updateTag();
			}
			function updateTag(){
				var tag = '';
				var otherinfo = '';
				for(var i=0; i < document.adminForm.contenttype.length; i++){
				   if (document.adminForm.contenttype[i].checked){ selectedtype = document.adminForm.contenttype[i].value; otherinfo += document.adminForm.contenttype[i].value; }
				}
				for(var i=0; i < document.adminForm.titlelink.length; i++){
				   if (document.adminForm.titlelink[i].checked){ otherinfo += document.adminForm.titlelink[i].value; }
				}
				if(selectedtype != '|type:title'){
					for(var i=0; i < document.adminForm.author.length; i++){
					   if (document.adminForm.author[i].checked){ otherinfo += document.adminForm.author[i].value; }
					}
					for(var i=0; i < document.adminForm.pict.length; i++){
					   if (document.adminForm.pict[i].checked){ otherinfo += '|pict:'+document.adminForm.pict[i].value; }
					}
				}
				if(window.document.getElementById('jflang')  && window.document.getElementById('jflang').value != ''){
					otherinfo += '|lang:';
					otherinfo += window.document.getElementById('jflang').value;
				}
				for(var i in selectedContents){
					if(selectedContents[i] == 'content'){
						tag = tag + '{joomlacontent:'+i+otherinfo+'}<br/>';
					}
				}
				setTag(tag);
			}
		//-->
		</script>
		<table width="100%" class="adminform">
			<tr>
				<td>
					<?php echo JText::_('DISPLAY'); ?>
				</td>
				<td colspan="2">
				<?php $contentType = acymailing::get('type.content'); echo $contentType->display('contenttype',$pageInfo->contenttype);?>
				</td>
				<td>
				<?php $jflanguages = acymailing::get('type.jflanguages');
						$jflanguages->onclick = 'onchange="updateTag();"';
						echo $jflanguages->display('lang',$pageInfo->lang); ?>
				</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_('CLICKABLE_TITLE'); ?>
				 </td>
				 <td>
				 	<?php $titlelinkType = acymailing::get('type.titlelink'); echo $titlelinkType->display('titlelink',$pageInfo->titlelink);?>
				</td>
				<td>
				<?php echo JText::_('AUTHOR_NAME'); ?>
				 </td>
				 <td>
				 	<?php $authorname = acymailing::get('type.authorname'); echo $authorname->display('author',$pageInfo->author);?>
				</td>
			</tr>
			<tr>
				<td><?php echo JText::_('DISPLAY_PICTURES'); ?></td>
				<td><?php echo JHTML::_('select.radiolist', $picts, 'pict' , 'size="1" onclick="updateTag();"', 'value', 'text', $pageInfo->pict); ?></td>
				<td></td>
				<td></td>
			</tr>
		</table>
		<table>
			<tr>
				<td width="100%">
					<?php echo JText::_( 'JOOMEXT_FILTER' ); ?>:
					<input type="text" name="search" id="acymailingsearch" value="<?php echo $pageInfo->search;?>" class="text_area" onchange="document.adminForm.submit();" />
					<button onclick="this.form.submit();"><?php echo JText::_( 'JOOMEXT_GO' ); ?></button>
					<button onclick="document.getElementById('acymailingsearch').value='';this.form.submit();"><?php echo JText::_( 'JOOMEXT_RESET' ); ?></button>
				</td>
				<td nowrap="nowrap">
					<?php echo JHTML::_('select.genericlist',   $categoriesValues, 'filter_cat', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', (int) $pageInfo->filter_cat ); ?>
				</td>
			</tr>
		</table>
		<table class="adminlist" cellpadding="1" width="100%">
			<thead>
				<tr>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_( 'FIELD_TITLE'), 'a.title', $pageInfo->filter->order->dir,$pageInfo->filter->order->value ); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_( 'ACY_AUTHOR'), 'b.name', $pageInfo->filter->order->dir,$pageInfo->filter->order->value ); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort',   JText::_( 'ACY_CREATED' ), 'a.created', $pageInfo->filter->order->dir, $pageInfo->filter->order->value ); ?>
					</th>
					<th class="title titleid">
						<?php echo JHTML::_('grid.sort',   JText::_( 'ACY_ID' ), 'a.id', $pageInfo->filter->order->dir, $pageInfo->filter->order->value ); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="4">
						<?php echo $pagination->getListFooter(); ?>
						<?php echo $pagination->getResultsCounter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php
					$k = 0;
					for($i = 0,$a = count($rows);$i<$a;$i++){
						$row =& $rows[$i];
				?>
					<tr id="content<?php echo $row->id?>" class="<?php echo "row$k"; ?>" onclick="applyContent(<?php echo $row->id.",'row$k'"?>);" style="cursor:pointer;">
						<td>
						<?php
							$text = '<b>'.JText::_('ALIAS',true).': </b>'.$row->alias;
							echo acymailing::tooltip($text, $row->title, '', $row->title);
						?>
						</td>
						<td>
						<?php
							if(!empty($row->name)){
								$text = '<b>'.JText::_('JOOMEXT_NAME').' : </b>'.$row->name;
								$text .= '<br/><b>'.JText::_('ACY_USERNAME').' : </b>'.$row->username;
								$text .= '<br/><b>'.JText::_('ACY_ID').' : </b>'.$row->created_by;
								echo acymailing::tooltip($text, $row->name, '', $row->name);
							}
						?>
						</td>
						<td align="center">
							<?php echo JHTML::_('date',  $row->created, JText::_('DATE_FORMAT_LC4') ); ?>
						</td>
						<td align="center">
							<?php echo $row->id; ?>
						</td>
					</tr>
				<?php
						$k = 1-$k;
					}
				?>
			</tbody>
		</table>
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $pageInfo->filter->order->value; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $pageInfo->filter->order->dir; ?>" />
	<?php
	echo $tabs->endPanel();
	echo $tabs->startPanel( JText::_( 'TAG_CATEGORIES' ), 'joomlacontent_auto');
		$type = JRequest::getString('type');
	?>
		<br style="font-size:1px"/>
		<script language="javascript" type="text/javascript">
		<!--
			var selectedCategories = new Array();
		<?php if(version_compare(JVERSION,'1.6.0','<')){ ?>
			function applyAutoContent(secid,catid,rowClass){
				if(selectedCategories[secid] && selectedCategories[secid][catid]){
					window.document.getElementById('content_sec'+secid+'_cat'+catid).className = rowClass;
					delete selectedCategories[secid][catid];
				}else{
					if(!selectedCategories[secid]) selectedCategories[secid] = new Array();
					if(secid == 0){
						for(var isec in selectedCategories){
							for(var icat in selectedCategories[isec]){
								if(selectedCategories[isec][icat] == 'content'){
									window.document.getElementById('content_sec'+isec+'_cat'+icat).className = 'row0';
									delete selectedCategories[isec][icat];
								}
							}
						}
					}else{
						if(selectedCategories[0] && selectedCategories[0][0]){
							window.document.getElementById('content_sec0_cat0').className = 'row0';
							delete selectedCategories[0][0];
						}
						if(catid == 0){
							for(var icat in selectedCategories[secid]){
								if(selectedCategories[secid][icat] == 'content'){
									window.document.getElementById('content_sec'+secid+'_cat'+icat).className = 'row0';
									delete selectedCategories[secid][icat];
								}
							}
						}else{
							if(selectedCategories[secid][0]){
								window.document.getElementById('content_sec'+secid+'_cat0').className = 'row0';
								delete selectedCategories[secid][0];
							}
						}
					}
					window.document.getElementById('content_sec'+secid+'_cat'+catid).className = 'selectedrow';
					selectedCategories[secid][catid] = 'content';
				}
				updateAutoTag();
			}
		<?php }else{ ?>
			function applyAutoContent(catid,rowClass){
				if(selectedCategories[catid]){
					window.document.getElementById('content_cat'+catid).className = rowClass;
					delete selectedCategories[catid];
				}else{
					window.document.getElementById('content_cat'+catid).className = 'selectedrow';
					selectedCategories[catid] = 'content';
				}
				updateAutoTag();
			}
		<?php } ?>
			function updateAutoTag(){
				tag = '{autocontent:';
			<?php if(version_compare(JVERSION,'1.6.0','<')){ ?>
				for(var isec in selectedCategories){
					for(var icat in selectedCategories[isec]){
						if(selectedCategories[isec][icat] == 'content'){
							if(icat != 0){
								tag += 'cat'+icat+'-';
							}else{
								tag += 'sec'+isec+'-';
							}
						}
					}
				}
			<?php }else{ ?>
				for(var icat in selectedCategories){
					if(selectedCategories[icat] == 'content'){
						tag += icat+'-';
					}
				}
			<?php } ?>
				if(document.adminForm.min_article && document.adminForm.min_article.value && document.adminForm.min_article.value!=0){ tag += '|min:'+document.adminForm.min_article.value; }
				if(document.adminForm.max_article.value && document.adminForm.max_article.value!=0){ tag += '|max:'+document.adminForm.max_article.value; }
				if(document.adminForm.contentorder.value){ tag += document.adminForm.contentorder.value; }
				if(document.adminForm.contentfilter && document.adminForm.contentfilter.value){ tag += document.adminForm.contentfilter.value; }
				if(document.adminForm.meta_article && document.adminForm.meta_article.value){ tag += '|meta:'+document.adminForm.meta_article.value; }
				for(var i=0; i < document.adminForm.contenttypeauto.length; i++){
				   if (document.adminForm.contenttypeauto[i].checked){selectedtype = document.adminForm.contenttypeauto[i].value; tag += document.adminForm.contenttypeauto[i].value; }
				}
				for(var i=0; i < document.adminForm.titlelinkauto.length; i++){
				   if (document.adminForm.titlelinkauto[i].checked){ tag += document.adminForm.titlelinkauto[i].value; }
				}
				if(selectedtype != '|type:title'){
					for(var i=0; i < document.adminForm.authorauto.length; i++){
					   if (document.adminForm.authorauto[i].checked){ tag += document.adminForm.authorauto[i].value; }
					}
					for(var i=0; i < document.adminForm.pictauto.length; i++){
					   if (document.adminForm.pictauto[i].checked){ tag += '|pict:'+document.adminForm.pictauto[i].value; }
					}
				}
				if(window.document.getElementById('jflangauto')  && window.document.getElementById('jflangauto').value != ''){
					tag += '|lang:';
					tag += window.document.getElementById('jflangauto').value;
				}
				tag += '}';
				setTag(tag);
			}
		//-->
		</script>
		<table width="100%" class="adminform">
			<tr>
				<td>
					<?php echo JText::_('DISPLAY');?>
				</td>
				<td colspan="2">
				<?php $contentType = acymailing::get('type.content'); $contentType->onclick = "updateAutoTag();"; echo $contentType->display('contenttypeauto','|type:intro');?>
				</td>
				<td>
					<?php $jflanguages = acymailing::get('type.jflanguages');
						$jflanguages->onclick = 'onchange="updateAutoTag();"';
						$jflanguages->id = 'jflangauto';
						echo $jflanguages->display('langauto'); ?>
				</td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_('CLICKABLE_TITLE'); ?>
				 </td>
				 <td>
				 	<?php $titlelinkType = acymailing::get('type.titlelink'); $titlelinkType->onclick = "updateAutoTag();"; echo $titlelinkType->display('titlelinkauto','|link');?>
				</td>
				<td>
				<?php echo JText::_('AUTHOR_NAME'); ?>
				 </td>
				 <td>
				 	<?php $authorname = acymailing::get('type.authorname'); $authorname->onclick = "updateAutoTag();";  echo $authorname->display('authorauto','');?>
				</td>
			</tr>
			<tr>
				<td><?php echo JText::_('DISPLAY_PICTURES'); ?></td>
				<td><?php echo JHTML::_('select.radiolist', $picts, 'pictauto' , 'size="1" onclick="updateAutoTag();"', 'value', 'text', "1"); ?></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td>
				<?php echo JText::_('MAX_ARTICLE'); ?>
				 </td>
				 <td>
				 	<input name="max_article" size="10" value="20" onchange="updateAutoTag();"/>
				</td>
				<td>
				<?php echo JText::_('ACY_ORDER'); ?>
				 </td>
				 <td>
				 	<?php $ordertype = acymailing::get('type.contentorder'); $ordertype->onclick = "updateAutoTag();"; echo $ordertype->display('contentorder','|order:id'); ?>
				</td>
			</tr>
			<?php if($this->params->get('metaselect')){ ?>
				<tr>
					<td>
					<?php echo JText::_('META_KEYWORDS'); ?>
					 </td>
					 <td colspan="3">
					 	<input name="meta_article" size="50" value="" onchange="updateAutoTag();"/>
					</td>
				</tr>
			<?php } ?>
			<?php if($type == 'autonews') { ?>
			<tr>
				<td>
				<?php 	echo JText::_('MIN_ARTICLE'); ?>
				 </td>
				 <td>
				 <input name="min_article" size="10" value="1" onchange="updateAutoTag();"/>
				</td>
				<td>
				<?php echo JText::_('JOOMEXT_FILTER'); ?>
				 </td>
				 <td>
				 	<?php $filter = acymailing::get('type.contentfilter'); $filter->onclick = "updateAutoTag();"; echo $filter->display('contentfilter','|filter:created'); ?>
				</td>
			</tr>
			<?php } ?>
		</table>
		<table class="adminlist" cellpadding="1" width="100%">
			<thead>
				<tr>
				<?php if(version_compare(JVERSION,'1.6.0','<')){ ?>
					<th class="title">
						<?php echo JText::_( 'SECTION'); ?>
					</th>
				<?php } ?>
					<th class="title">
						<?php echo JText::_( 'TAG_CATEGORIES'); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$k = 0;
					if(version_compare(JVERSION,'1.6.0','<')){
				?>
					<tr id="content_sec0_cat0" class="<?php echo "row$k"; ?>" onclick="applyAutoContent(0,0,'<?php echo "row$k" ?>');" style="cursor:pointer;">
						<td style="font-weight: bold;">
						<?php
							echo JText::_('ACY_ALL');
						?>
						</td>
						<td style="text-align:center;font-weight: bold;">
						<?php
							echo JText::_('ACY_ALL');
						?>
						</td>
					</tr>
					<?php
					}
					$k = 1-$k;
					$currentSection = '';
					foreach($categories as $row){
					if(version_compare(JVERSION,'1.6.0','<') AND $currentSection != $row->section){
						?>
						<tr id="content_sec<?php echo $row->secid ?>_cat0" class="<?php echo "row$k"; ?>" onclick="applyAutoContent(<?php echo $row->secid ?>,0,'<?php echo "row$k" ?>');" style="cursor:pointer;">
							<td style="font-weight: bold;">
							<?php
								echo $row->section;
							?>
							</td>
							<td style="text-align:center;font-weight: bold;">
							<?php
								echo JText::_('ALL');
							?>
							</td>
						</tr>
						<?php
						$k = 1-$k;
						$currentSection = $row->section;
					}
					if(version_compare(JVERSION,'1.6.0','<')){
				?>
					<tr id="content_sec<?php echo $row->secid ?>_cat<?php echo $row->catid?>" class="<?php echo "row$k"; ?>" onclick="applyAutoContent(<?php echo $row->secid ?>,<?php echo $row->catid ?>,'<?php echo "row$k" ?>');" style="cursor:pointer;">
						<td>
						</td>
						<td>
						<?php
							echo $row->category;
						?>
						</td>
					</tr>
				<?php
					}else{ ?>
						<tr id="content_cat<?php echo $row->id ?>" class="<?php echo "row$k"; ?>" onclick="applyAutoContent(<?php echo $row->id ?>,'<?php echo "row$k" ?>');" style="cursor:pointer;">
						<td>
						<?php
							echo $row->title;
						?>
						</td>
					</tr>
					<?php }
						$k = 1-$k;
					}
				?>
			</tbody>
		</table>
	<?php
		echo $tabs->endPanel();
		echo $tabs->endPane();
	 }
	 function acymailing_replacetags(&$email){
		$this->_replaceAuto($email);
 		$this->_replaceArticles($email);
	 }
	 function _replaceArticles(&$email){
		$match = '#{joomlacontent:(.*)}#Ui';
		$variables = array('subject','body','altbody');
		$found = false;
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match,$email->$var,$results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}
		if(!$found) return;
		require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php';
		$mailerHelper = acymailing::get('helper.mailer');
		$htmlreplace = array();
		$textreplace = array();
		$subjectreplace = array();
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				if(isset($htmlreplace[$oneTag])) continue;
				$article = $this->_replaceContent($allresults,$i);
				$htmlreplace[$oneTag] = $article;
				$textreplace[$oneTag] = $mailerHelper->textVersion($article);
				$subjectreplace[$oneTag] = strip_tags($article);
			}
		}
		$email->body = str_replace(array_keys($htmlreplace),$htmlreplace,$email->body);
		$email->altbody = str_replace(array_keys($textreplace),$textreplace,$email->altbody);
		$email->subject = str_replace(array_keys($subjectreplace),$subjectreplace,$email->subject);
	 }
	 function _replaceContent(&$results,$i){
		$arguments = explode('|',strip_tags($results[1][$i]));
		$tag = null;
		$tag->id = (int) $arguments[0];
		for($i=1,$a=count($arguments);$i<$a;$i++){
			$args = explode(':',$arguments[$i]);
			if(isset($args[1])){
				$tag->$args[0] = $args[1];
			}else{
				$tag->$args[0] = true;
			}
		}
		if(version_compare(JVERSION,'1.6.0','<')){
			$query = 'SELECT a.*,b.name as authorname, c.alias as catalias, c.title as cattitle, s.alias as secalias, s.title as sectitle FROM '.acymailing::table('content',false).' as a ';
			$query .= 'LEFT JOIN '.acymailing::table('users',false).' as b ON a.created_by = b.id ';
			$query .= ' LEFT JOIN '.acymailing::table('categories',false).' AS c ON c.id = a.catid ';
			$query .= ' LEFT JOIN '.acymailing::table('sections',false).' AS s ON s.id = a.sectionid ';
			$query .= 'WHERE a.id = '.$tag->id.' LIMIT 1';
		}else{
			$query = 'SELECT a.*,b.name as authorname, c.alias as catalias, c.title as cattitle FROM '.acymailing::table('content',false).' as a ';
			$query .= 'LEFT JOIN '.acymailing::table('users',false).' as b ON a.created_by = b.id ';
			$query .= ' LEFT JOIN '.acymailing::table('categories',false).' AS c ON c.id = a.catid ';
			$query .= 'WHERE a.id = '.$tag->id.' LIMIT 1';
		}
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$article = $db->loadObject();
		$result = '';
		if(empty($article)){
			$app =& JFactory::getApplication();
			if($app->isAdmin()){
				$app->enqueueMessage('The article "'.$tag->id.'" could not be loaded','notice');
			}
			return $result;
		}
		if(!empty($tag->lang)){
			$langid = (int) substr($tag->lang,strpos($tag->lang,',')+1);
			if(!empty($langid)){
				$query = "SELECT reference_field, value FROM `#__jf_content` WHERE `published` = 1 AND `reference_table` = 'content' AND `language_id` = $langid AND `reference_id` = ".$tag->id;
				$db->setQuery($query);
				$translations = $db->loadObjectList();
				if(!empty($translations)){
					foreach($translations as $oneTranslation){
						if(!empty($oneTranslation->value)){
							$translatedfield =  $oneTranslation->reference_field;
							$article->$translatedfield = $oneTranslation->value;
						}
					}
				}
			}
		}
		if($this->params->get('integration') == 'jreviews' AND !empty($article->images)){
			$firstpict = explode('|',trim(reset(explode("\n",$article->images))). '|||||||');
			if(!empty($firstpict[0])){
				$myPict = '<img src="' . ACYMAILING_LIVE . 'images/stories/' . $firstpict[0] . '" hspace="5" style="margin:5px" align="left" border="' . intval($firstpict[5]) . '" />';
				$article->introtext = $myPict.$article->introtext;
			}
		}
		$completeId = $article->id;
		$completeCat = $article->catid;
		if(!empty($article->alias)) $completeId.=':'.$article->alias;
		if(!empty($article->catalias)) $completeCat .= ':'.$article->catalias;
		if(version_compare(JVERSION,'1.6.0','<')){
			$completeSec = $article->sectionid;
			if(!empty($article->secalias)) $completeSec .= ':'.$article->secalias;
			$link = ContentHelperRoute::getArticleRoute($completeId,$completeCat,$completeSec);
		}else{
			$link = ContentHelperRoute::getArticleRoute($completeId,$completeCat);
		}
		if($this->params->get('integration') == 'flexicontent'){
			$link = 'index.php?option=com_flexicontent&view=items&id='.$completeId;
		}
		if(!empty($tag->lang)) $link.= (strpos($link,'?') ? '&' : '?') . 'lang='.substr($tag->lang, 0,strpos($tag->lang,','));
		$link = acymailing::frontendLink($link);
		$styleTitle = '';
		$styleTitleEnd = '';
		if($tag->type != "title"){
			$styleTitle = '<h2 class="acymailing_title">';
			$styleTitleEnd = '</h2>';
		}
		if(empty($tag->notitle)){
			if(!empty($tag->link)){
				$result .= '<a href="'.$link.'" ';
				if($tag->type != "title") $result .= 'style="text-decoration:none" name="content-'.$article->id.'" ';
				$result .= 'target="_blank" >'.$styleTitle.$article->title.$styleTitleEnd.'</a>';
			}else{
				$result .= $styleTitle.$article->title.$styleTitleEnd;
			}
		}
		if(!empty($tag->author)){
			$authorName = empty($article->created_by_alias) ? $article->authorname : $article->created_by_alias;
			if($tag->type == 'title') $result .= '<br/>';
			$result .= $authorName.'<br/>';
		}
		if(!empty($tag->created)){
			$result .= '<span class="createddate">'.JHTML::_( 'date', $article->created, JText::_('DATE_FORMAT_LC2')).'</span>';
		}
		if(!isset($tag->pict) AND $tag->type != 'title'){
			if($this->params->get('removepictures','never') == 'always' || ($this->params->get('removepictures','never') == 'intro' AND $tag->type == "intro")){
				$tag->pict = 0;
			}else{
				$tag->pict = 1;
			}
		}
		if($tag->type != "title"){
			$contentText = '';
			if($tag->type == "intro"){
				$forceReadMore = false;
				$wordwrap = $this->params->get('wordwrap',0);
				if(!empty($wordwrap) AND empty($article->fulltext)){
					$newintrotext = strip_tags($article->introtext,'<br><img>');
					$numChar = strlen($newintrotext);
	           		if($numChar > $wordwrap){
	           			$stop = strlen($newintrotext);
             			for($i=$wordwrap;$i<$numChar;$i++){
             				if($newintrotext[$i] == " "){
             					$stop = $i;
             					$forceReadMore = true;
             					break;
             				}
             			}
             			$article->introtext = substr($newintrotext,0,$stop).'...';
         			}
         		}
			}
			if(empty($article->fulltext) OR $tag->type != "text"){
				$contentText .= $article->introtext;
			}
			if($tag->type != "intro" AND !empty($article->fulltext)){
				if( $tag->type != "text" && !empty($article->introtext)){
					$contentText .= '<br />';
				}
				$contentText .= $article->fulltext;
			}
			if(strpos($contentText,'jseblod') !== false AND file_exists(ACYMAILING_ROOT.'plugins'.DS.'content'.DS.'cckjseblod.php')){
				global $mainframe;
				include_once(ACYMAILING_ROOT.'plugins'.DS.'content'.DS.'cckjseblod.php');
				if(function_exists('plgContentCCKjSeblod')){
					$paramsContent =& JComponentHelper::getParams('com_content');
					$article->text = $contentText;
					plgContentCCKjSeblod($article,$paramsContent);
					$contentText = $article->text;
				}
			}
			if(!empty($tag->wrap)){
				$newtext = strip_tags($contentText,'<br><img>');
				$numChar = strlen($newtext);
           		if($numChar > $tag->wrap){
           			$stop = strlen($newtext);
         			for($i=$tag->wrap;$i<$numChar;$i++){
         				if($newtext[$i] == " "){
         					$stop = $i;
         					$forceReadMore = true;
         					break;
         				}
         			}
         			$contentText = substr($newtext,0,$stop).'...';
     			}
			}
			$result .= $contentText;
			if($tag->type == "intro"){
				if(empty($tag->noreadmore) AND (!empty($article->fulltext) OR $forceReadMore)){
					$readMoreText = empty($tag->readmore) ? JText::_('JOOMEXT_READ_MORE') : $tag->readmore;
					$result .= '<a style="text-decoration:none;" target="_blank" href="'.$link.'"><span class="acymailing_readmore">'.$readMoreText.'</span></a>';
				}
			}
			$result = '<div class="acymailing_content">'.$result.'</div>';
		}
		if(file_exists(ACYMAILING_MEDIA.'plugins'.DS.'tagcontent_html.php')){
			ob_start();
			require(ACYMAILING_MEDIA.'plugins'.DS.'tagcontent_html.php');
			$result = ob_get_clean();
		}elseif(file_exists(ACYMAILING_MEDIA.'plugins'.DS.'tagcontent.php')){
			ob_start();
			require(ACYMAILING_MEDIA.'plugins'.DS.'tagcontent.php');
			$result = ob_get_clean();
		}
		if($tag->type != 'title' AND $this->params->get('removejs','yes') == 'yes'){
			$result = $this->_removeJS($result);
		}
		if(isset($tag->pict)){
			$pictureHelper = acymailing::get('helper.acypict');
			$pictureHelper->maxHeight = empty($tag->maxheight) ? $this->params->get('maxheight',150) : $tag->maxheight;
			$pictureHelper->maxWidth = empty($tag->maxwidth) ? $this->params->get('maxwidth',150) : $tag->maxwidth;
			if($tag->pict == '0'){
				$result = $pictureHelper->removePictures($result);
			}elseif($tag->pict == 'resized'){
				if($pictureHelper->available()){
					$result = $pictureHelper->resizePictures($result);
				}elseif($app->isAdmin()){
					$app->enqueueMessage($pictureHelper->error,'notice');
				}
			}
		}
		return $result;
	}
	function _replaceAuto(&$email){
		$this->acymailing_generateautonews($email);
		if(!empty($this->tags)){
			$email->body = str_replace(array_keys($this->tags),$this->tags,$email->body);
			if(!empty($email->altbody)) $email->altbody = str_replace(array_keys($this->tags),$this->tags,$email->altbody);
			foreach($this->tags as $tag => $result){
				$email->subject = str_replace($tag,strip_tags(str_replace('</tr><tr>',' | ',$result)),$email->subject);
			}
		}
	}
	function acymailing_generateautonews(&$email){
		$return = null;
		$return->status = true;
		$return->message = '';
		$time = time();
		$match = '#{autocontent:(.*)}#Ui';
		$variables = array('subject','body','altbody');
		$found = false;
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match,$email->$var,$results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}
		if(!$found) return $return;
		$this->tags = array();
		$db =& JFactory::getDBO();
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				if(isset($this->tags[$oneTag])) continue;
				$arguments = explode('|',strip_tags($allresults[1][$i]));
				$allcats = explode('-',$arguments[0]);
				$parameter = null;
				for($i=1;$i<count($arguments);$i++){
					$args = explode(':',$arguments[$i]);
					$arg0 = $args[0];
					if(isset($args[1])){
						$parameter->$arg0 = $args[1];
					}else{
						$parameter->$arg0 = true;
					}
				}
				$selectedArea = array();
				foreach($allcats as $oneCat){
					if(version_compare(JVERSION,'1.6.0','<')){
						$sectype = substr($oneCat,0,3);
						$num = substr($oneCat,3);
						if(empty($num)) continue;
						if($sectype=='cat'){
							$selectedArea[] = 'catid = '.(int) $num;
						}elseif($sectype=='sec'){
							$selectedArea[] = 'sectionid = '.(int) $num;
						}
					}else{
						if(empty($oneCat)) continue;
						$selectedArea[] = (int) $oneCat;
					}
				}
				$query = 'SELECT a.id FROM `#__content` as a ';
				$where = array();
				if(!empty($parameter->featured)){
					$query .= 'LEFT JOIN `#__content_frontpage` as b ON a.id = b.content_id ';
					$where[] = 'b.content_id IS NOT NULL';
				}
				if(!empty($selectedArea)){
					if(version_compare(JVERSION,'1.6.0','<')){
						$where[] = implode(' OR ',$selectedArea);
					}else{
						$where[] = '`catid` IN ('.implode(',',$selectedArea).')';
					}
				}
				if(!empty($parameter->filter) AND !empty($email->params['lastgenerateddate'])){
					$condition = '`publish_up` >\''.date( 'Y-m-d H:i:s',$email->params['lastgenerateddate'] - date('Z')).'\'';
					$condition .= ' OR `created` >\''.date( 'Y-m-d H:i:s',$email->params['lastgenerateddate'] - date('Z')).'\'';
					if($parameter->filter == 'modify'){
						$condition .= ' OR `modified` > \''.date( 'Y-m-d H:i:s',$email->params['lastgenerateddate'] - date('Z')).'\'';
					}
					$where[] = $condition;
				}
				if(!empty($parameter->meta)){
					$allMetaTags = explode(',',$parameter->meta);
					$metaWhere = array();
					foreach($allMetaTags as $oneMeta){
						if(empty($oneMeta)) continue;
						$metaWhere[] = "`metakey` LIKE '%".$db->getEscaped($oneMeta,true)."%'";
					}
					if(!empty($metaWhere)) $where[] = implode(' OR ',$metaWhere);
				}
				$where[] = '`publish_up` < \'' .date( 'Y-m-d H:i:s',$time - date('Z')).'\'';
				$where[] = '`publish_down` > \''.date( 'Y-m-d H:i:s',$time - date('Z')).'\' OR `publish_down` = 0';
				$where[] = 'state = 1';
				if(isset($parameter->access)){
					$where[] = 'access <= '.intval($parameter->access);
				}else{
					if($this->params->get('contentaccess','registered') == 'registered') $where[] = 'access <= 1';
					elseif($this->params->get('contentaccess','registered') == 'public') $where[] = 'access = 0';
				}
				$query .= ' WHERE ('.implode(') AND (',$where).')';
				if(!empty($parameter->order)){
					$ordering = explode(',',$parameter->order);
					$query .= ' ORDER BY a.`'.acymailing::secureField($ordering[0]).'` '.acymailing::secureField($ordering[1]);
				}
				$start = '';
				if(!empty($parameter->start)) $start = intval($parameter->start).',';
				if(!empty($parameter->max)) $query .= ' LIMIT '.$start.(int) $parameter->max;
				elseif(empty($email->params['lastgenerateddate'])) $query .= ' LIMIT '.$start.'20';
				$db->setQuery($query);
				$allArticles = $db->loadResultArray();
				if(!empty($parameter->min) AND count($allArticles)< $parameter->min){
					$return->status = false;
					$return->message = 'Not enough articles for the tag '.$oneTag.' : '.count($allArticles).' / '.$parameter->min.' between '.acymailing::getDate($email->params['lastgenerateddate']).' and '.acymailing::getDate($time);
				}
				$stringTag = '';
				if(!empty($allArticles)){
					if(file_exists(ACYMAILING_MEDIA.'plugins'.DS.'autocontent.php')){
						ob_start();
						require(ACYMAILING_MEDIA.'plugins'.DS.'autocontent.php');
						$stringTag = ob_get_clean();
					}else{
						$stringTag .= '<table>';
						foreach($allArticles as $oneArticleId){
							$stringTag .= '<tr><td>';
							$args = array();
							$args[] = 'joomlacontent:'.$oneArticleId;
							if(!empty($parameter->type)) $args[] = 'type:'.$parameter->type;
							if(!empty($parameter->link)) $args[] = 'link';
							if(!empty($parameter->author)) $args[] = 'author';
							if(!empty($parameter->lang)) $args[] = 'lang:'.$parameter->lang;
							if(!empty($parameter->notitle)) $args[] = 'notitle';
							if(!empty($parameter->created)) $args[] = 'created';
							if(!empty($parameter->noreadmore)) $args[] = 'noreadmore';
							if(isset($parameter->pict)) $args[] = 'pict:'.$parameter->pict;
							if(!empty($parameter->wrap)) $args[] = 'wrap:'.$parameter->wrap;
							if(!empty($parameter->maxwidth)) $args[] = 'maxwidth:'.$parameter->maxwidth;
							if(!empty($parameter->maxheight)) $args[] = 'maxheight:'.$parameter->maxheight;
							if(!empty($parameter->readmore)) $args[] = 'readmore:'.$parameter->readmore;
							$stringTag .= '{'.implode('|',$args).'}';
							$stringTag .= '</td></tr>';
						}
						$stringTag .= '</table>';
					}
				}
				$this->tags[$oneTag] = $stringTag;
			}
		}
		return $return;
	}
	function _removeJS($text){
		$text = preg_replace("#(onmouseout|onmouseover|onclick|onfocus|onload|onblur) *= *\"(?:(?!\").)*\"#iU",'',$text);
		$text =  preg_replace("#< *script(?:(?!< */ *script *>).)*< */ *script *>#isU",'',$text);
		return $text;
	}
}//endclass