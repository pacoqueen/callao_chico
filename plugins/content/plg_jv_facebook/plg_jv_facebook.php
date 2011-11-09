<?php
/**
 * @package JV Facebook Plugin for Joomla! 1.5
 * @author http://www.ZooTemplate.com
 * @copyright (C) 2010- ZooTemplate.Com
 * @license PHP files are GNU/GPL
**/
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );
require_once(JPATH_ROOT.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
class plgContentPlg_jv_facebook extends JPlugin
	{
		var $fbshare_pos;
		var $fblike_pos;
		var $home;
		var $com;
		var $cate;
		var $k2cate;
		var $fbc_show;
		var $info;
		var $listpage;
		var $art_ex;
		var $art_content = array();
		var $art_k2 = array();
		function plgContentPlg_jv_facebook( &$subject, $params )
		{
			parent::__construct( $subject, $params );     
			JHTML::stylesheet('style.css','plugins/content/plg_jv_facebook/assets/css/');
			// load plugin parameters
			$this->_plugin = JPluginHelper::getPlugin( 'content', 'plg_jv_facebook' );
			$this->_params = new JParameter( $this->_plugin->params );
			// get param homepage
			$this->home = $this->_params->def('homepage',1);
			// get params facebook share button
			$this->com = $this->_params->def('enable_com','both');
			$this->fbshare_pos = $this->_params->def('fbshare_pos','before_content');
			$this->cate = $this->_params->get('content_cate','');
			$this->k2cate = $this->_params->get('k2_cate','');
			// get params facebook like button
			$this->fblike_pos = $this->_params->def('fblike_position','before_content');
			// get params facebook comment
			$this->fbc_show = $this->_params->def('fbc_show',1);
			// get param show plugin list page
			$this->listpage = $this->_params->def('listpage',1);
			// get article exlude from config
			$this->art_ex = $this->_params->get('article_id','');
			// get array article exclude
			$art_arr = explode(";",$this->art_ex);
			for ( $i = 0; $i < count($art_arr); $i++ ) {
				$art = explode("_",$art_arr[$i]);
				if ($art[0] == 'content') { // article is content
					array_push($this->art_content, $art[1]);
				} else {
					@array_push($this->art_k2, $art[1]);
				}
			}
			// info 
			$this->info = '<div style="display: none;"><a title="Joomla Templates" href="http://www.zootemplate.com">Joomla Templates</a> and Joomla Extensions by ZooTemplate.Com</div>';
			
		}
		function onK2PrepareContent( &$article, &$params, $limitstart = null)
		{
			$after = '';
			$before = '';
			$option 	= JRequest::getVar('option');
			
			if ( $this->is_com_k2($article))
			{
				if ( ($this->fbc_show == 1) && ($this->is_view_page())) {
					$fb_comments = $this->jv_comments($article, $params, $limitstart);
					$after = $fb_comments;
				} else {
					if ( ($this->listpage != 1) && (!$this->is_view_page()) ) {
						$after = '';
						$before = '';
					} else {
						// set position for facebook like button
						if ( $this->fblike_pos == 'before_content') {
							$before .= $this->jv_fblike_button($article, $params, $limitstart);
						} else {
							$after .= $this->jv_fblike_button($article, $params, $limitstart);
						}
						// set positon for facebook share button
						if ( $this->fbshare_pos == 'before_content') {
							$before .= $this->jv_fbshare_button($article, $params, $limitstart);
						} else {
							$after .= $this->jv_fbshare_button($article, $params, $limitstart);
						}
					}
				}
				$before = '<div class="jv-social-share-button-contain">'.$before.'</div>';
				$after = '<div class="jv-social-share-button-contain">'.$after.'</div>';
				$article->text = $before.$article->text.$after.$this->info;
			}
		}
		function onPrepareContent( &$article, &$params, $limitstart = null)
		{
			$after = '';
			$before = '';
			$btn_share	= '';
			$btn_like	= '';
			$option 	= JRequest::getVar('option');
			if ( $this->is_com_content($article))
			{
				if ( ($this->fbc_show == 1) && ($this->is_view_page())) {
					$fb_comments = $this->jv_comments($article, $params, $limitstart);
					$after = $fb_comments;
				} else {
					if ( ($this->listpage != 1) && (!$this->is_view_page()) && (!$this->showHome()) ) {
						$after = '';
						$before = '';
					} else {
						// set position for facebook like button
						if ( $this->fblike_pos == 'before_content') {
							$before .= $this->jv_fblike_button($article, $params, $limitstart);
						} else {
							$after .= $this->jv_fblike_button($article, $params, $limitstart);
						}
						// set positon for facebook share button
						if ( $this->fbshare_pos == 'before_content') {
							$before .= $this->jv_fbshare_button($article, $params, $limitstart);
						} else {
							$after .= $this->jv_fbshare_button($article, $params, $limitstart);
						}	
					}
				}
				$before = '<div class="jv-social-share-button-contain">'.$before.'</div>';
				$after = '<div class="jv-social-share-button-contain">'.$after.'</div>';
				$article->text = $before.$article->text.$after.$this->info;
			}
		}
		// show facebook share button
		function jv_fbshare_button(&$article, &$params, $limitstart)
		{
			// get params facebook share button
			$fbshare_show = $this->_params->def('fbshare_show',0);	
			$fbshare_style = $this->_params->def('fbshare_style','normal');
			//-------------------------
			// get link and title
			$option 	= JRequest::getVar('option');
			$view 		= JRequest::getVar('view');
			$id			= JRequest::getInt('id');
			$uri		= JFactory::getURI();
			$root_url	= $uri->toString( array ('scheme', 'host', 'port' ) );
			if( $this->is_com_content($article)) {
				$cate_url = JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catslug, $article->sectionid));
			} else if($this->is_com_k2($article)) {
				$cate_url = $article->link;
			}
			$url = $root_url.$cate_url;
			$title = $article->title;   
			//--------------------------
			// get facebook like button
			$button= '';
			$btn="";
			if ($fbshare_style == 'btn'){
				$btn="-nocount";
			}
			if($fbshare_style == 'normal'){
				$style =  'box_count';   
			}
			else $style = 'button_count';
			$button = '<a name="fb_share" type="'.$style.'" share_url="'.$url.'" href="http://www.facebook.com/sharer.php">Share</a>';
			$button .= '<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>';
			$button = '<div class="jv-fbshare-button'.$btn.'">'.$button.'</div>';
			//-------------------------
			// Check params option
			$jv_fbshare = '';
			if ($fbshare_show == 1)
			{	
				if($view == 'frontpage' && $this->home != 1) {
					$jv_fbshare = '';
				} else {
					$jv_fbshare = $button;
				}
			}
			if($this->com == 'com_content') {
				if($this->is_com_content($article) == 1) {
					$jv_fbshare = $jv_fbshare;
				}
				else {
					$jv_fbshare = '';
				}
			}
			if($this->com == 'com_k2') {
				if($this->is_com_k2($article) == 1) {
					$jv_fbshare = $jv_fbshare;
				}
				else {
					$jv_fbshare = '';
				}
			}
			$catid = $article->catid;
			// filter com_content category 
			if($this->is_com_content($article)) {
				if (is_array($this->cate)) {
					$categories = $this->cate;
				} elseif ($this->cate == '') {
					$categories[] = $catid;
				} else {
					$categories[] = $this->cate;
				}
				if( !in_array( $catid , $categories )) {
					$jv_fbshare = '';
				}
			}
			// filter com_k2 category
			if($this->is_com_k2($article)) {
				if (is_array($this->k2cate)) {
					$k2categories = $this->k2cate;
				} elseif ($this->k2cate == '') {
					$k2categories[] = $catid;
				} else {
					$k2categories[] = $this->k2cate;
				}
				if( !in_array( $catid , $k2categories )) {
					$jv_fbshare = '';
				}
			}
			// check exclude article com content
			if ($this->is_com_content($article)) {
				if( in_array( $article->id ,$this->art_content )) {
					$jv_fbshare = '';
				}
			}
			// check exclude article com k2
			if ($this->is_com_k2($article)) {
				if( in_array( $article->id ,$this->art_k2 )) {
					$jv_fbshare = '';
				}
			}
			return $jv_fbshare;
		}
		// show facebook like button
		function jv_fblike_button(&$article, &$params, $limitstart)
		{
			// get params facebook like button
			$this->fbc_appid = $this->_params->def('fbc_appid','');
			$fblike_show = $this->_params->def('fblike_show',0);			
			$fblike_layout = $this->_params->def('fblike_layout_style','standard');
			$fblike_faces = $this->_params->def('fblike_show_faces',1);
			if ($fblike_faces==1) {$fblike_faces='true';}
			else {$fblike_faces='false';} 
			$fblike_width = $this->_params->def('fblike_width',200);
			$fblike_height = $this->_params->def('fblike_height',62);
			$fblike_verb = $this->_params->def('fblike_verb_display','like');
			$fblike_font = $this->_params->def('fblike_font','');
			$fblike_color = $this->_params->def('fblike_color_scheme','light');
			//-------------------------
			// get link and title
			$option 	= JRequest::getVar('option');
			$view 		= JRequest::getVar('view');
			$id			= JRequest::getInt('id');
			$uri		= JFactory::getURI();
			$root_url	= $uri->toString( array ('scheme', 'host', 'port' ) );
			if( $this->is_com_content($article)) {
				$cate_url = JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catslug, $article->sectionid));
			} else if($this->is_com_k2($article)) {
				$cate_url = $article->link;
			}
			$url = $root_url.$cate_url;
			$title = $article->title;
			$lang = JFactory::getLanguage();
			$langstr = str_replace("-", "_", $lang->_lang);
			//--------------------------
			$button = "<iframe src=\"http://www.facebook.com/plugins/like.php?href=".urlencode($url)."&amp;layout=".$fblike_layout."&amp;width=".$fblike_width."&amp;show_faces=".$fblike_faces."&amp;action=".$fblike_verb."&amp;colorscheme=".$fblike_color."&amp;font=".$fblike_font."&amp;height=".$fblike_height."\" scrolling=\"no\" frameborder=\"0\" style=\"border:none; overflow:hidden; width:".$fblike_width."px; height:".$fblike_height."px;\" allowTransparency=\"true\"></iframe>";
			// check params option
			$jv_fblike = '';		
			if($fblike_show == 1) 
			{			
				// hidden facebook like button on homepage
				if($view == 'frontpage' && $this->home != 1) {
					$jv_fblike = '';
				}
				else {
					$jv_fblike = $button;
				}
			}
			if($this->com == 'com_content') {
				if($this->is_com_content($article) == 1) {
					$jv_fblike = $jv_fblike;
				}
				else {
					$jv_fblike = '';
				}
			}
			if($this->com == 'com_k2') {
				if($this->is_com_k2($article) == 1) {
					$jv_fblike = $jv_fblike;
				}
				else {
					$jv_fblike = '';
				}
			}
			$catid = $article->catid;
			// filter com_content category 
			if($this->is_com_content($article)) {
				if (is_array($this->cate)) {
					$categories = $this->cate;
				} elseif ($this->cate == '') {
					$categories[] = $catid;
				} else {
					$categories[] = $this->cate;
				}
				if( !in_array( $catid , $categories )) {
					$jv_fblike = '';
				}
				// check exclude article com content
				if( in_array( $article->id ,$this->art_content )) {
					$jv_fblike = '';
				}
			}
			// filter com_k2 category
			if($this->is_com_k2($article)) {
				if (is_array($this->k2cate)) {
					$k2categories = $this->k2cate;
				} elseif ($this->k2cate == '') {
					$k2categories[] = $catid;
				} else {
					$k2categories[] = $this->k2cate;
				}
				if( !in_array( $catid , $k2categories )) {
					$jv_fblike = '';
				}
				// check exclude article com k2
				if( in_array( $article->id ,$this->art_k2 )) {
					$jv_fblike = '';
				}
			}
			return $jv_fblike;
		}
		// facebook comment button
		function jv_comments(&$article, &$params, $limitstart)
		{
			// get params
			$this->fbc_appid = $this->_params->def('fbc_appid','');
			$this->fbc_number = $this->_params->def('fbc_number','');
			$this->fbc_width = $this->_params->def('fbc_width','');
			$fblike_width = $this->_params->def('fblike_width',200);
			$fblike_height = $this->_params->def('fblike_height',62);
			$fblike_show = $this->_params->def('fblike_show',0);
			$fblike_layout = $this->_params->def('fblike_layout_style','standard');
			$fblike_faces = $this->_params->def('fblike_show_faces',1);
			if ($fblike_faces==1) {$fblike_faces='true';}
			else {$fblike_faces='false';}
			 
			$fblike_verb = $this->_params->def('fblike_verb_display','like');
			$fblike_font = $this->_params->def('fblike_font','');
			$fblike_color = $this->_params->def('fblike_color_scheme','light');
			$uri		= JFactory::getURI();
			$root_url	= $uri->toString( array ('scheme', 'host', 'port' ) ); 
			if($this->is_com_content($article)) { 
				$cate_url = JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catslug));
				$url = $root_url.$cate_url;
			} elseif ($this->is_com_k2($article)) {
				$cate_url = $article->link;
				$url = $root_url.$cate_url;
			}
			
			if( ($this->fbc_show == 1) && ($this->is_view_page()) ) 
			{
				$lang = JFactory::getLanguage();
				$langstr = str_replace("-", "_", $lang->_lang);
				if($fblike_show>0){
					$jv_str = "<iframe src=\"http://www.facebook.com/plugins/like.php?href=".urlencode($url)."&amp;layout=".$fblike_layout."&amp;width=".$fblike_width."&amp;show_faces=".$fblike_faces."&amp;action=".$fblike_verb."&amp;colorscheme=".$fblike_color."&amp;font=".$fblike_font."&amp;height=".$fblike_height."\" scrolling=\"no\" frameborder=\"0\" style=\"border:none; overflow:hidden; width:".$fblike_width."px; height:".$fblike_height."px;\" allowTransparency=\"true\"></iframe>";
					$jv_str .="<div id=\"fb-root\"></div><script src=\"http://connect.facebook.net/".$langstr."/all.js#xfbml=1\"></script><fb:comments href=\"".urlencode($url)."\" num_posts=\"".$this->fbc_number."\" width=\"".$this->fbc_width."\"></fb:comments>";
				}else{
					$jv_str ="<div id=\"fb-root\"></div><script src=\"http://connect.facebook.net/".$langstr."/all.js#xfbml=1\"></script><fb:comments href=\"".urlencode($url)."\" num_posts=\"".$this->fbc_number."\" width=\"".$this->fbc_width."\"></fb:comments>";
				}
			}
			$catid = $article->catid;
						// check exclude article com content
						if ($this->is_com_content($article)) {
							if (is_array($this->cate)) {
								$categories = $this->cate;
							} elseif ($this->cate == '') {
								$categories[] = $catid;
							} else {
								$categories[] = $this->cate;
							}
							if( !in_array( $catid , $categories )) {
								$jv_str = '';
							}
							if( in_array( $article->id ,$this->art_content )) {
								$jv_str = '';
							}
						}
						// check exclude article com k2
						if ($this->is_com_k2($article)) {
							if (is_array($this->k2cate)) {
								$k2categories = $this->k2cate;
							} elseif ($this->k2cate == '') {
								$k2categories[] = $catid;
							} else {
								$k2categories[] = $this->k2cate;
							}
							if( !in_array( $catid , $k2categories )) {
								$jv_str = '';
							}
							if( in_array( $article->id ,$this->art_k2 )) {
								$jv_str = '';
							}
						}
			return $jv_str;
		}
		function is_com_content($article)
		{
			return (isset($article->sectionid)) ? true : false;
		}
		function is_com_k2($article)
		{
			return (!isset($article->sectionid)) ? true : false;
		}
		function showHome()
		{
			$view 		= JRequest::getVar('view');
			if ($view == 'frontpage' && $this->home == '1') {
				return true;
			} else { return false; }
		}
		function is_view_page() 
		{
			$option 	= JRequest::getVar('option');
			$view 		= JRequest::getVar('view');
			//if its a view page
			if (($option == 'com_k2' && $view == 'item') || ($option == 'com_content' && $view == 'article')) {
				return true;
			}
			return false;
		}
	}
