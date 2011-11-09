<?php
/**
 * plugin_googlemap2.php,v 2.13 2011/01/09 13:34:11
 * @copyright (C) Reumer.net
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 *
 /* ----------------------------------------------------------------

/** ensure this file is being included by a parent file */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.html.parameter' ); 

class plgSystemPlugin_googlemap2 extends JPlugin
{
	var $config;
	var $subject;
	var $jversion;
	var $params;
	var $regex;
	var $document;
	var $doctype;
	var $published;
	var $plugincode;
	var $brackets;
	var $countmatch;
	var $event;
	var $helper;
	
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.0
	 */
	public function __construct( &$subject, $config )
	{
		parent::__construct( $subject, $config );
		$this->event = 'construct';
		// Do some extra initialisation in this constructor if required
		$this->subject = $subject;
		$this->config = $config;
		// Version of Joomla
		$this->jversion = JVERSION;
		$plugin =& JPluginHelper::getPlugin('system', 'plugin_googlemap2');
		$this->params = new JParameter( $plugin->params);
		// Check if params are defined and set otherwise try to get them from previous version
		$this->_upgrade_plugin();
		// Set document and doctype to null. Can only be retrievedwhen events are triggered. otherwise the language of the site magically changes.
		$this->document = null;
		$this->doctype = null;
		// Edit mode?
		$option = JRequest::getVar('option', '');	
		$view = JRequest::getVar('view', '');	
		$task = JRequest::getVar('task', '');	
		$this->editmode = ($option=='com_content'&&$view=='article'&&$task=="edit");
		// Get params
		$this->plugincode = $this->params->get( 'plugincode', 'mosmap' );
		$this->brackets = $this->params->get( 'brackets', '{' );
		// define the regular expression for the bot
		if ($this->brackets=="both") {
			$this->regex="/(<p\b[^>]*>\s*)?(\{|\[)".$this->plugincode."\s*(.*?)(\}|\])(\s*<\/p>)?/si";
			$this->countmatch = 3;
		} elseif ($this->brackets=="[") {
			$this->regex="/(<p\b[^>]*>\s*)?\[".$this->plugincode."\s*(.*?)\](\s*<\/p>)?/si";
			$this->countmatch = 2;
		} else {
			$this->regex="/(<p\b[^>]*>\s*)?{".$this->plugincode."\s*(.*?)}(\s*<\/p>)?/si";
			$this->countmatch = 2;
		}
		// The helper class
		$this->helper = null;

		// Clean up variables
		unset($plugin, $option, $view, $task);
	}
	
	/**
	 * Do something onAfterInitialise 
	 */
	public function onAfterInitialise()
	{
		$this->event = 'onAfterInitialise';
	}
	
	/**
	 * onPrepareContent is rename in Joomla 1.6 to onContentPrepare
	 */
	public function onContentPrepare($context, &$article, &$params, $limitstart)
	{
		$this->event = 'onContentPrepare';
		
		$app = JFactory::getApplication();
		if($app->isAdmin()) {
			return;
		}
		
		// get document types
		$this->_getdoc();

		$text = &$article->text;
		$introtext = &$article->introtext;
		
		// check whether plugin has been unpublished
		if ( !$this->params->get( 'publ', 1 ) ) {
			$text = preg_replace( $this->regex, '', $text );
			return true;
		}
		
		// PDF can't show maps so remove it
		if ($this->doctype=='pdf') {
			$text = preg_replace( $this->regex, '', $text );
			return true;
		}
		
		// check if article is in edit mode then don't replace {mosmap{ so it can be edited
		if ($this->editmode)
			return true;
	
		// perform the replacement in a normal way, but this has the disadvantage that other plugins
		// can't add information to the mosmap, other later added content is not checked and modules can't be checked
		// $this->_replace( $text );	
		// $this->_replace( $introtext );
		
		// Clean up variables
		unset($app, $text, $introtext);
	}
	
	/**
	 * onPrepareContent is for Joomla 1.5
	 */
	public function onPrepareContent(&$article)
	{
		$this->event = 'onPrepareContent';
	
		$app = JFactory::getApplication();
		if($app->isAdmin()) {
			return;
		}
		
		// get document types
		$this->_getdoc();

		$text = &$article->text;
		$introtext = &$article->introtext;
		
		// check whether plugin has been unpublished
		if ( !$this->params->get( 'publ', 1 ) ) {
			$text = preg_replace( $this->regex, '', $text );
			return true;
		}
		
		// PDF or feed can't show maps so remove it
		if ($this->doctype=='pdf'||$this->doctype=='feed') {
			$text = preg_replace( $this->regex, '', $text );
			return true;
		}
		
		// check if article is in edit mode then don't replace {mosmap{ so it can be edited
		if ($this->editmode)
			return true;
	
		// perform the replacement in a normal way, but this has the disadvantage that other plugins
		// can't add information to the mosmap, other later added content is not checked and modules can't be checked
		//$this->_replace( $text );	
		//$this->_replace( $introtext );	
		
		// Clean up variables
		unset($app, $text, $introtext);
	}
	
	/**
	 * Do something onAfterRoute 
	 */
	public function onAfterRoute()
	{
		$this->event = 'onAfterRoute';
	}
	
	/**
	 * Do something onAfterDispatch 
	 */
	public function onAfterDispatch()
	{
		$this->event = 'onAfterDispatch';
		
		$app = JFactory::getApplication();
		if($app->isAdmin()) {
			return;
		}
		
		// get document types
		$this->_getdoc();

		// FEED
		if ($this->doctype=='feed'&&isset($this->document->items)) {
			foreach($this->document->items as $item) {
				$text = &$item->description;
				$text = preg_replace( $this->regex, '', $text );
			}
			return true;
		}
		
		// PDF can't show maps so remove it
		if ($this->doctype=='pdf') {
			$text = $this->document->getBuffer("component");
			$text = preg_replace( $this->regex, '', $text );
			$this->document->setBuffer($text, "component"); 
			return true;
		}
		
		// check if article is in edit mode then don't replace {mosmap{ so it can be edited
		if ($this->editmode) {
			return true;
		}
		// In other components or leftovers
		$text = $this->document->getBuffer("component");
		if (strlen($text)>0) {
			
			// check whether plugin has been unpublished
			if ( !$this->params->get( 'publ', 1 ) )
				$text = preg_replace( $this->regex, '', $text );
			else
				$this->_replace($text);			
			$this->document->setBuffer($text, "component"); 
		}
		
		// Clean up variables
		unset($app, $item, $text, $introtext);
	}
	
	/**
	 * Do something onAfterRender 
	 */
	public function onAfterRender()
	{
		$this->event = 'onAfterRender';
		
		$app = JFactory::getApplication();
		if($app->isAdmin()) {
			return;
		}
		
		// get document types
		$this->_getdoc();

		// Get the rendered body text
		$text = JResponse::getBody();
		
		// check whether plugin has been unpublished
		if ( !$this->params->get( 'publ', 1 ) ) {
			$text = preg_replace( $this->regex, '', $text );
			return true;
		}
		
		// PDF or feed can't show maps so remove it
		if ($this->doctype=='pdf'||$this->doctype=='feed') {
			$text = preg_replace( $this->regex, '', $text );
			return true;
		}
		
		// check if article is in edit mode then don't replace {mosmap{ so it can be edited
		if ($this->editmode)
			return true;
	
		// perform the replacement
		$this->_replace( $text );
		
		// Set the body text with the replaced result
        JResponse::setBody($text);

		// Clean up variables
		unset($app, $text, $introtext);
	}
	
	function _getdoc() {
		if ($this->document==null) {
			$this->document = JFactory::getDocument();
			$this->doctype = $this->document->getType();
		}
	}
	
	function _replace(&$text) {
		$matches = array();
		$cnt = preg_match_all($this->regex,$text,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
//		print_r($matches);
		if ($cnt>0) {
			if ($this->helper==null) {
				if (substr($this->jversion,0,3)=="1.5")
					$filename = JPATH_SITE.DS."/plugins/system/plugin_googlemap2_helper.php";
				else
					$filename = JPATH_SITE.DS."/plugins/system/plugin_googlemap2/plugin_googlemap2_helper.php";
				
				include_once($filename);
				$this->helper = new plgSystemPlugin_googlemap2_helper($this->jversion, $this->params, $this->regex, $this->document, $this->brackets);
			}
			// Process the found {mosmap} codes
			for($counter = 0; $counter < $cnt; $counter ++) {
				// Very strange the first match is the plugin code??
				$this->helper->process($matches[0][$counter][0], $matches[$this->countmatch][$counter][0], $text, $counter, $this->event);
			}
		}
		
		// Clean up variables
		unset($matches, $cnt, $counter, $content, $filename);
	}
	
	function _upgrade_plugin() {
		if ($this->params->get( 'publ', '' )=='') {
			if (substr($this->jversion,0,3)=="1.5") {
				$database  =& JFactory::getDBO();
				$query = "SELECT params FROM #__plugins AS b WHERE b.element='plugin_googlemap2' AND b.folder='content'";
				$database->setQuery($query);
				if (!$database->query())
					JError::raiseWarning(1, 'plgSystemPlugin_googlemap2::install_params: '.JText::_('SQL Error')." ".$database->stderr(true));
				
				$params = $database->loadResult();
				$savparams = $database->getEscaped($params);
				if ($params!="") {
					$query = "UPDATE #__plugins AS a SET a.params = '{$savparams}' WHERE a.element='plugin_googlemap2' AND a.folder='system'";
					$database->setQuery($query);
					if (!$database->query())
						JError::raiseWarning(1, 'plgSystemPlugin_googlemap2::install_params: '.JText::_('SQL Error')." ".$database->stderr(true));
					$this->params = new JParameter( $params );
				}
				
				// Clean up variables
				unset($database, $query, $params, $savparams, $plugin);
			}


		}		
	}
}

?>