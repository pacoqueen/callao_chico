<?php
 
/* 
 * +--------------------------------------------------------------------------+
 * |   	ShareThis
 * |   	Copyright (c) 2010 ShareThis, Inc.
 * |	http://sharethis.com
 * +
 * |  	Released under the GPL license
 * |	http://www.opensource.org/licenses/gpl-license.php
 * | 
 * |	This is an add-on for Joomla 
 * |	http://www.joomla.org/
 * |
 * |	**********************************************************************
 * |	This program is distributed in the hope that it will be useful, but
 * |	WITHOUT ANY WARRANTY; without even the implied warranty of
 * |	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * | 	**********************************************************************
 * |
 * | 	Plugin Name: ShareThis
 * | 	Plugin URI: http://sharethis.com
 * |	Description: Let your visitors share a post/page with others. Supports e-mail and posting to social bookmarking sites. 
 * |	Version: 4.1.0
 * |	Author: Author URI: http://sharethis.com
 * +--------------------------------------------------------------------------+
 */ 
 
/* no direct access*/
defined( '_JEXEC' ) or die( 'Restricted access' );
if(!class_exists('ContentHelperRoute')) require_once (JPATH_SITE . '/components/com_content/helpers/route.php'); 

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');

/**
 * plgContentShareThis
 * 
 * Creates ShareThis sharing button with each and every posts.
 * Reads the user settings and creates the button accordingly.
 */  
class plgContentShareThis  extends JPlugin {
  
   /**
    * Constructor
    * Loads the plugin settings and assigns them to class variables
    * 
    * @param object $subject
    */
    public function __construct(&$subject)
    {
        parent::__construct($subject);
  
        // Loading plugin parameters
        $this->_plugin = JPluginHelper::getPlugin('content', 'sharethis');
        $this->_params = new JParameter($this->_plugin->params);
        
		//Properties holding plugin settings
        $this->_widget_type = $this->_params->get('widget_type');
        $this->_button_style = $this->_params->get('button_style');
        $this->_alignment = $this->_params->get('alignment');
        $this->_widget_position = $this->_params->get('Widget_position');
	}
     
	/**
	 * Before display content method
	 *
	 * Method is called by the view and the results are imploded and displayed in a placeholder
	 *
	 * @param	string		The context for the content passed to the plugin.
	 * @param	object		The content object.  
	 * @param	object		The content params
	 * @param	int			The 'page' number
	 * @return	string
	 * @since	1.6
	 */
	public function onContentBeforeDisplay($context, &$article, &$params, $limitstart=0) {
		
		if(!($this->_widget_position)) {
			$app = JFactory::getApplication();
			
			/* Get article link */
			$url = JURI::root().ContentHelperRoute::getArticleRoute($article->id, $article->catid);
			
			/* Return whole button style code with article title and article url values. */
			return "<DIV align='".$this->_alignment."'>".$this->showWidget().$this->getWidgetStyle($url,$article->title)."</DIV>";
		}
	} 
	
	  
	/**
	 * After display content method
	 *
	 * Method is called by the view and the results are imploded and displayed in a placeholder
	 *
	 * @param	string		The context for the content passed to the plugin.
	 * @param	object		The content object.  
	 * @param	object		The content params
	 * @param	int			The 'page' number
	 * @return	string
	 * @since	1.6
	 */
	public function onContentAfterDisplay($context, &$article, &$params, $limitstart=0) {
		
		if(($this->_widget_position)) {
			$app = JFactory::getApplication();
			
			/* Get article link */
			$url = JURI::root().ContentHelperRoute::getArticleRoute($article->id, $article->catid);
			
			/* Return whole button style code with article title and article url values. */
			return "<DIV align='".$this->_alignment."'>".$this->showWidget().$this->getWidgetStyle($url,$article->title)."</DIV>";
		}
	} 
	
	/**
     * Validate Style of widget. 
	 * Display applicable ShareThis widget. 
     */
    private function getWidgetStyle($url,$title)
    {
		
		/* Button Style : Large Icons */
		if(($this->_button_style)=='lg-icons')
		{
			return "<span class='st_facebook_large' displayText='share' st_url='".$url."' st_title='".$title."' ></span><span class='st_twitter_large' displayText='share' st_url='".$url."' st_title='".$title."' ></span><span class='st_email_large' displayText='share' st_url='".$url."' st_title='".$title."' ></span><span class='st_sharethis_large' st_url='".$url."' st_title='".$title."' displayText='share'></span>";
		}
		
		/* Button Style : Horizontal Count */
		if(($this->_button_style)=='lg-horizontal')
		{
			return "<span class='st_facebook_hcount' st_url='".$url."' st_title='".$title."' displayText='share'></span><span class='st_twitter_hcount' st_url='".$url."' st_title='".$title."' displayText='share'></span><span class='st_email_hcount' st_url='".$url."' st_title='".$title."' displayText='share'></span><span class='st_sharethis_hcount' st_url='".$url."' st_title='".$title."' displayText='share'></span>";
		}
		
		/* Button Style : Vertical Count */
		if(($this->_button_style)=='lg-vertical')
		{
			return "<span class='st_facebook_vcount' st_url='".$url."' st_title='".$title."' displayText='share'></span><span class='st_twitter_vcount' st_url='".$url."' st_title='".$title."' displayText='share'></span><span class='st_email_vcount' st_url='".$url."' st_title='".$title."' displayText='share'></span><span class='st_sharethis_vcount' st_url='".$url."' st_title='".$title."' displayText='share'></span>";
		}
		
		/* Button Style : Classic */
		if(($this->_button_style)=='sm-classic')
		{
			return "<span class='st_sharethis' st_url='".$url."' st_title='".$title."' displayText='ShareThis'></span>";
		}
		
		/* Button Style : Regular Buttons */
		if(($this->_button_style)=='sm-regular')
		{
			return "<span class='st_facebook' st_url='".$url."' st_title='".$title."' displayText='share'></span><span class='st_twitter' st_url='".$url."' st_title='".$title."' displayText='share'></span><span class='st_email' st_url='".$url."' st_title='".$title."' displayText='share'></span><span class='st_sharethis' st_url='".$url."' st_title='".$title."' displayText='share'></span>";
		}
		
		/* Button Style : Regular Button No-Text */
		if(($this->_button_style)=='sm-notext')
		{
			return "<br/><span class='st_facebook' alt='compartir en facebook' title='compartir en facebook' st_url='".$url."' st_title='".$title."' ></span><span  alt='compartir en twitter' title='compartir en twitter' class='st_twitter' st_url='".$url."' st_title='".$title."' ></span><span  alt='enviar por email' class='st_email' title='enviar por email' st_url='".$url."' st_title='".$title."' ></span><span alt='compartir' class='st_sharethis' title='compartir' st_url='".$url."' st_title='".$title."' ></span>";
		}
		
		/* Button Style : Buttons */
		if(($this->_button_style)=='button')
		{
			return "<span class='st_facebook_buttons' st_url='".$url."' st_title='".$title."' displayText='share'></span><span class='st_twitter_buttons' st_url='".$url."' st_title='".$title."' displayText='share'></span><span class='st_email_buttons' st_url='".$url."' st_title='".$title."' displayText='share'></span><span class='st_sharethis_buttons' st_url='".$url."' st_title='".$title."' displayText='share'></span>";
		}

	}
	
	/**
     * Validate type of ShareThis widget version (4.x or 5.x).
	 * Display applicable ShareThis widget. 
     */
	private function showWidget()
    {
		/* Validation for sharethis widget version */
		if(($this->_widget_type)!=0)
		{
			/* New version of shareThis widget i.e. 5.x */
			$newWidget = "<script type='text/javascript'>var switchTo5x=true;</script>";
		} 
		else
		{
			/* Old version of shareThis widget 4.x */
			$newWidget ="";
		}

		/* Add main js file (button.js) */
		$includeButtonScript = "<script type='text/javascript' src='http://w.sharethis.com/button/buttons.js'></script>";
		
		/* Add script and widget elements to the current page.*/
		return $newWidget.$includeButtonScript;
    }
}
  