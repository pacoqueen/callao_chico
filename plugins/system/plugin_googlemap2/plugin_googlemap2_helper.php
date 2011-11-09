<?php
/**
 * plugin_googlemap2_helper.php,v 2.13 2011/02/15 13:34:11
 * @copyright (C) Reumer.net
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 *
 /* ----------------------------------------------------------------

/** ensure this file is being included by a parent file */

defined( '_JEXEC' ) or die( 'Restricted access' );

if (!defined('_CMN_JAVASCRIPT')) define('_CMN_JAVASCRIPT', "<b>JavaScript must be enabled in order for you to use Google Maps.</b> <br/>However, it seems JavaScript is either disabled or not supported by your browser. <br/>To view Google Maps, enable JavaScript by changing your browser options, and then try again.");

class plgSystemPlugin_googlemap2_helper
{
	var $jversion;
	var $params;
	var $regex;
	var $document;
	var $brackets;
	var $debug_plugin;
	var $debug_text;
	var $protocol;
	var $googlewebsite;
	var $urlsetting;
	var $googlekey;
	var $language;
	var $langtype;
	var $iso;
	var $no_javascript;
	var $pagebreak;
	var	$google_API_version;
	var	$timeinterval;
	var	$loadmootools;
	var	$googleindexing;
	var	$langanim;
	var $proxy;
	var	$first_google;
	var	$first_mootools;
	var	$first_modalbox;
	var	$first_localsearch;
	var	$first_kmlrenderer;
	var	$first_kmlelabel;
	var	$first_svcontrol;
	var	$first_animdir;
	var	$first_arcgis;
	var	$first_panoramiolayer;
	var $initparams;
	var $clientgeotype;
	var $event;
	var $_lang;
	var	$_langanim;
	var	$_client_geo;
	var $_inline_coords;
	var $_inline_tocoords;
	var $_geocoded;
	var $_kmlsbwidthorig;
	var $_lbxwidthorig;
	
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @since       1.0
	 */
	 // Can we use _construct or should we use init?
	 //	function init() {
	public function __construct($jversion, $params, $regex, $document, $brackets)
	{
		// The params of the plugin
		$this->jversion = $jversion;
		$this->params = $params;
		$this->regex = $regex;
		$this->document = $document;
		$this->brackets = $brackets;
		// Set debug
		$this->debug_plugin = $this->params->get( 'debug', '0' );
		$this->debug_text = '';
		// Get ID
		$this->id = intval( JRequest::getVar('id', null) );	
		$this->id = explode(":", $this->id);
		$this->id = $this->id[0];
		// What is the url of website without / at the end
		$this->url = preg_replace('/\/$/', '', JURI::base());
		$this->_debug_log("url base(): ".$this->url);			
		$this->base = JURI::base(true);
		$this->_debug_log("url base(true): ".$this->base);			
		// Protocol not working with maps.google.com only with enterprise account
		if ($_SERVER['SERVER_PORT'] == 443)
			$this->protocol = "https://";
		else
			$this->protocol = "http://";
		$this->_debug_log("Protocol: ".$this->protocol);
		// Get language
		$this->langtype = $this->params->get( 'langtype', '' );
		$this->lang = JFactory::getLanguage();
		$this->lang->load("plg_system_plugin_googlemap2", JPATH_SITE."/administrator", $this->lang->getTag(), true);
		$this->language = $this->_getlang();
		$this->no_javascript = JText::_( 'CMN_JAVASCRIPT', _CMN_JAVASCRIPT);
		// Define encoding
		$this->iso = "utf-8";
		// Get params
		$this->googlewebsite = $this->params->get( 'googlewebsite', 'maps.google.com' );
		$this->_debug_log("googlewebsite: ".$this->googlewebsite);
		$this->urlsetting = $this->params->get( 'urlsetting', 'http_host' );
		$this->_debug_log("urlsetting: ".$this->urlsetting);
		if ($this->urlsetting=='mosconfig')
			$this->urlsetting = $this->url;
		else 
			$this->urlsetting = $_SERVER['HTTP_HOST'];
		$this->google_API_version = $this->params->get( 'Google_API_version', '2.x' );
		$this->googleindexing = $this->params->get( 'googleindexing', '1' );
		$this->timeinterval = $this->params->get( 'timeinterval', '500' );
		$this->loadmootools = $this->params->get( 'loadmootools', '1' );
		$this->clientgeotype = $this->params->get( 'clientgeotype', '0' );
		$this->_debug_log("loadmootools: ".$this->loadmootools);
		$this->langanim = $this->params->get( 'langanim', 'en;The requested panorama could not be displayed|Could not generate a route for the current start and end addresses|Street View coverage is not available for this route|You have reached your destination|miles|miles|ft|kilometers|kilometer|meters|In|You will reach your destination|Stop|Drive|Press Drive to follow your route|Route|Speed|Fast|Medium|Slow' );
		$this->proxy = $this->params->get( 'proxy', '1' );
		// Get key
		$this->googlekey = $this->_get_API_key();
		// Pagebreak regular expression
		$this->pagebreak = '/<hr\s(title=".*"\s)?class="system-pagebreak"(\stitle=".*")?\s\/>/si';
		// load scripts once
		$this->first_google=true;
		$this->first_mootools=true;
		$this->first_modalbox=true;
		$this->first_localsearch=true;
		$this->first_kmlrenderer=true;
		$this->first_kmlelabel=true;
		$this->first_svcontrol=true;
		$this->first_animdir= true;
		$this->first_arcgis=true;
		$this->first_panoramiolayer = true;
		$this->_debug_log("brackets: ".$this->brackets);
		// Get params
		$this->initparams = (object) null;
		$this->_getInitialParams();
	}	
	
	function process($match, $params, &$text, $counter, $event) {
		$startmem = round($this->_memory_get_usage()/1024);
		$this->_debug_log("Memory Usage Start (_process): " . $startmem . " KB");
		$this->event = $event;
		
		// Language initial value
		$this->_lang = $this->language;
		
		// Default global process parameters
		$this->_client_geo = 0;
		//track if coordinates different from config
		$this->_inline_coords = 0;
		$this->_inline_tocoords = 0;
		$this->_geocoded = 0;

		// Parameters can get the default from the plugin if not empty or from the administrator part of the plugin
		$mp = clone $this->initparams;

		// Next parameters can be set as default out of the administrtor module or stay empty and the plugin-code decides the default. 
		$mp->zoomType = $this->params->get( 'zoomType', '' );
		$mp->mapType = strtolower($this->params->get( 'mapType', '' )); 

		// default empty and should be filled as a parameter with the plugin out of the content item
		$mp->mapclass='';
		$mp->tolat='';
		$mp->tolon='';
		$mp->toaddress='';
		$mp->description='';
		$mp->tooltip='';
		$mp->kml = array();
		$mp->kmlsb = array();
		$mp->layer = array();
		$mp->lookat = array();
		$mp->camera = array();
		$mp->msid='';
		$mp->show = 1;
		$mp->imageurl='';
		$mp->imagex='';
		$mp->imagey='';
		$mp->imagexyunits='';
		$mp->imagewidth='';
		$mp->imageheight='';
		$mp->imageanchorx='';
		$mp->imageanchory='';
		$mp->imageanchorunits='';
		$mp->searchtext='';
		$mp->latitude='';
		$mp->longitude='';
		$mp->waypoints = array();
		$mp->lbxcaption = '';

		// Give the map a random name so it won't interfere with another map
		$mp->mapnm = $this->id."_".$this->_randomkeys(5)."_".$counter;
		
		// Match the field details to build the html
		$fields = explode("|", $params);

		foreach($fields as $value) {
			$value=trim($value);
			$values = explode("=",$value, 2);
			$values[0] = trim(strtolower($values[0]));
			$values=preg_replace("/^'/", '', $values);
			$values=preg_replace("/'$/", '', $values);
			$values=preg_replace("/^&#0{0,2}39;/",'',$values);
			$values=preg_replace("/&#0{0,2}39;$/",'',$values);
				
			if (count($values)>1)
				$values[1] = trim($values[1]);

			if($values[0]=='debug'){
				$this->debug_plugin=$values[1];
			}else if($values[0]=='lat'&&$values[1]!=''){
				$mp->latitude=$this->_remove_html_tags($values[1]);
				$this->_inline_coords = 1;
			}else if($values[0]=='lon'&&$values[1]!=''){
				$mp->longitude=$this->_remove_html_tags($values[1]);
				$this->_inline_coords = 1;
			}else if($values[0]=='centerlat'){
				$mp->centerlat=$this->_remove_html_tags($values[1]);
				$this->_inline_coords = 1;
			}else if($values[0]=='centerlon'){
				$mp->centerlon=$this->_remove_html_tags($values[1]);
				$this->_inline_coords = 1;
			}else if($values[0]=='$mp->tolat'){
				$mp->tolat=$this->_remove_html_tags($values[1]);
				$this->_inline_tocoords = 1;
			}else if($values[0]=='tolon'){
				$mp->tolon=$this->_remove_html_tags($values[1]);
				$this->_inline_tocoords = 1;
			}else if($values[0]=='text'){
				$mp->description=html_entity_decode(html_entity_decode(trim($values[1])));
				$mp->description=str_replace("\"","\\\"", $mp->description);
				$mp->description=str_replace("&#0{0,2}39;","'", $mp->description);
			}else if($values[0]=='tooltip'){
				$mp->tooltip=trim($values[1]);
				$mp->tooltip=str_replace("&amp;","&", $mp->tooltip);
			}else if($values[0]=='maptype'){
				$mp->mapType=strtolower($values[1]);
			}else if($values[0]=='waypoint'){
				$mp->waypoints[0] = $values[1];
			}else if(($this->brackets=='both'||$this->brackets=='[')&&preg_match("/waypoint\([0-9]+\)/", $values[0])){
				$mp->waypoints[$this->_get_index($values[0], '(')] = $values[1];
			}else if(($this->brackets=='both'||$this->brackets=='{')&&preg_match("/waypoint\[[0-9]+\]/", $values[0])){
				$mp->waypoints[$this->_get_index($values[0], '[')] = $values[1];
			}else if($values[0]=='kml'){
				$mp->kml[0]=$this->_remove_html_tags($values[1]);
			}else if(($this->brackets=='both'||$this->brackets=='[')&&preg_match("/kml\([0-9]+\)/", $values[0])){
				$mp->kml[$this->_get_index($values[0], '(')] = $this->_remove_html_tags($values[1]);
			}else if(($this->brackets=='both'||$this->brackets=='{')&&preg_match("/kml\[[0-9]+\]/", $values[0])){
				$mp->kml[$this->_get_index($values[0], '[')] = $this->_remove_html_tags($values[1]);
			}else if($values[0]=='kmlsb'){
				$mp->kmlsb[0]=$this->_remove_html_tags($values[1]);
			}else if(($this->brackets=='both'||$this->brackets=='[')&&preg_match("/kmlsb\([0-9]+\)/", $values[0])){
				$mp->kmlsb[$this->_get_index($values[0], '(')] = $this->_remove_html_tags($values[1]);
			}else if(($this->brackets=='both'||$this->brackets=='{')&&preg_match("/kmlsb\[[0-9]+\]/", $values[0])){
				$mp->kmlsb[$this->_get_index($values[0], '[')] = $this->_remove_html_tags($values[1]);
			}else if($values[0]=='layer'){
				$mp->layer[0]=$this->_remove_html_tags($values[1]);
			}else if(($this->brackets=='both'||$this->brackets=='[')&&preg_match("/layer\([0-9]+\)/", $values[0])){
				$mp->layer[$this->_get_index($values[0], '(')] = $this->_remove_html_tags($values[1]);
			}else if(($this->brackets=='both'||$this->brackets=='{')&&preg_match("/layer\[[0-9]+\]/", $values[0])){
				$mp->layer[$this->_get_index($values[0], '[')] = $this->_remove_html_tags($values[1]);
			}else if($values[0]=='lookat'){
				$mp->lookat[0] = $values[1];
			}else if(($this->brackets=='both'||$this->brackets=='[')&&preg_match("/lookat\([0-9]+\)/", $values[0])){
				$mp->lookat[$this->_get_index($values[0], '(')] = $values[1];
			}else if(($this->brackets=='both'||$this->brackets=='{')&&preg_match("/lookat\[[0-9]+\]/", $values[0])){
				$mp->lookat[$this->_get_index($values[0], '[')] = $values[1];
			}else if($values[0]=='camera'){
				$mp->camera[0] = $values[1];
			}else if(($this->brackets=='both'||$this->brackets=='[')&&preg_match("/camera\([0-9]+\)/", $values[0])){
				$mp->camera[$this->_get_index($values[0], '(')] = $values[1];
			}else if(($this->brackets=='both'||$this->brackets=='{')&&preg_match("/camera\[[0-9]+\]/", $values[0])){
				$mp->camera[$this->_get_index($values[0], '[')] = $values[1];
			}else if($values[0]=='tilelayer'){
				$mp->tilelayer=$this->_remove_html_tags($values[1]);
			}else {
				// other parameters
				if ($values[0]!='')
					$mp->$values[0]=$values[1];
			}
		}
		
		//Translate parameters
		$mp->erraddr = $this->_translate($mp->erraddr, $this->_lang);
		$mp->txtaddr = $this->_translate($mp->txtaddr, $this->_lang);
		$mp->txtaddr = str_replace(array("\r\n", "\r", "\n"), '', $mp->txtaddr );
		$mp->txtgetdir = $this->_translate($mp->txtgetdir, $this->_lang);
		$mp->txtfrom = $this->_translate($mp->txtfrom, $this->_lang);
		$mp->txtto = $this->_translate($mp->txtto, $this->_lang);
		$mp->txtdiraddr = $this->_translate($mp->txtdiraddr, $this->_lang);
		$mp->txtdir = $this->_translate($mp->txtdir, $this->_lang);
		$mp->txtlightbox = $this->_translate($mp->txtlightbox, $this->_lang);
		$mp->txt_driving = $this->_translate($mp->txt_driving, $this->_lang);
		$mp->txt_avhighways = $this->_translate($mp->txt_avhighways, $this->_lang);
		$mp->txt_walking = $this->_translate($mp->txt_walking, $this->_lang);
		$this->_langanim = $this->_translate($this->langanim, $this->_lang);
		$this->_langanim = explode("|", $this->_langanim);

		$this->_debug_log("clientgeotype: ".$this->clientgeotype);
		
		// Latitude only when no coordinates are specified and no address
		if($this->_inline_coords == 0 && empty($mp->address) && !empty($mp->latitudeid)) {
			// Get information
			$url = "http://www.google.de/latitude/apps/badge/api?user=".$mp->latitudeid."&type=kml";
			$getpage = $this->_getURL($url);
			if ($getpage!='') {
				$expr = '/xmlns/';
				$getpage = preg_replace($expr, 'id', $getpage);
				$xml = new SimpleXMLElement($getpage);
				foreach($xml->xpath('//coordinates') as $coordinates) {
					$coords = $coordinates;
					break;
				}
				if ($coords!='') {
					$this->_debug_log("Coordinates: ".join(", ", explode(",", $coords)));
					list ($mp->longitude, $mp->latitude) = explode(",", $coords);
					$this->_inline_coords = 1;
					
					// Get icon
					if ($mp->icon=='') {
						foreach($xml->xpath('//Icon/href') as $href) {
							$mp->icon = $href;
							break;
						}
						if ($mp->icon!="") {
							$mp->iconwidth = "32";
							$mp->iconheight = "32";
						}
					}
					// show description -> add to text
					if ($mp->latitudedesc=="1") {
						foreach($xml->xpath('//description') as $descr) {
							$desc = $descr;
							break;
						}
						$desc=html_entity_decode(html_entity_decode(trim($desc)));
						$desc=str_replace("\"","\\\"", $desc);
						$desc=str_replace("&#0{0,2}39;","'", $desc);
						
						$mp->description .= "<p class='latitude'>".str_replace(' http://www.google.com/latitude/apps/badge', '', $desc)."</p>";
					}
					// show coordinates -> add to text
					if ($mp->latitudecoord=="1") {
						$mp->description .= "<table class=latitudetable><tr><td>Latitude</td><td>".$mp->latitude."</td></tr><tr><td>Longitude</td><td>".$mp->longitude."</td></tr></table>";
					}
				} else
					$this->_debug_log("Latitude coordinates: null");
			} else
				$this->_debug_log("Latitude totally wrong!");
			unset($url, $getpage, $expr, $xml, $coord, $coordinates, $descr, $desc);
		}

		if($this->_inline_coords == 0 && !empty($mp->address))	{
			if ($this->clientgeotype=="local")
				$coord = "";
			else
				$coord = $this->get_geo($mp->address);
				
			if ($coord=='') {
				$this->_client_geo = 1;
			} else {
				list ($mp->longitude, $mp->latitude, $altitude) = explode(",", $coord);
				$this->_inline_coords = 1;
				$this->_geocoded = 1;
			}
		}

		if($this->_inline_tocoords == 0 && !empty($mp->toaddress))	{
			if ($this->clientgeotype=="local")
				$tocoord = "";
			else
				$tocoord = $this->get_geo($mp->toaddress);
			if ($tocoord=='') {
				$client_togeo = 1;
			} else {
				list ($mp->tolon, $mp->tolat, $altitude) = explode(",", $tocoord);
				$this->_inline_tocoords = 1;
			}
		}

		if (is_numeric($mp->svwidth)) 
			$mp->svwidth .= "px";
			
		if (is_numeric($mp->svheight))
			$mp->svheight.= "px";

		if (is_numeric($mp->kmlsbwidth)) {
			$this->_kmlsbwidthorig = $mp->kmlsbwidth;
			$mp->kmlsbwidth .= "px";
		} else 
			$this->_kmlsbwidthorig = 0;
			
		$this->_lbxwidthorig = $mp->lbxwidth;
		
		if (is_numeric($mp->lbxwidth))
			$mp->lbxwidth .= "px";
		
		if (is_numeric($mp->lbxheight))
			$mp->lbxheight .= "px";
			
		if (is_numeric($mp->width))
			$mp->width .= "px";
			
		if (is_numeric($mp->height))
			$mp->height .= "px";

		if (!is_numeric($mp->panomax))
			$mp->panomax= "50";
			
		if ($mp->msid!=''&&count($mp->kml)==0) {
			$mp->kml[0]=$this->protocol.$this->googlewebsite.'/maps/ms?';
			if ($this->_lang!='')
				$mp->kml[0] .= "hl=".$this->_lang."&amp;";
			$mp->kml[0].='ie='.$this->iso.'&amp;msa=0&amp;msid='.$mp->msid.'&amp;output=kml';
			$this->_debug_log("- msid: ".$mp->kml[0]);
		}

		// Get the code to be added to the text
		list ($code, $lbcode) = $this->_processMapv2($mp, $text);
		
		// Get memory before adding code to text
		$endmem = round($this->_memory_get_usage()/1024);
		$diffmem = $endmem-$startmem;
		$this->_debug_log("Memory Usage End: " . $endmem . " KB (".$diffmem." KB)");

		// Add code to text
		$code = "\n<!-- Plugin Google Maps version 2.13b by Mike Reumer ".(($this->debug_text!='')?$this->debug_text."\n":"")."-->".$code;

		// Clean up debug text for next _process
		$this->debug_text = '';
		
		// Depending of show place the code at end of page or on the {mosmap} position		
		if ($mp->show==0) {
			$offset = strpos($text, $match);
			$text = preg_replace($this->regex, $lbcode, $text, 1);
			// If pagebreak add code before pagebreak
			preg_match($this->pagebreak, $text, $m, PREG_OFFSET_CAPTURE, $offset);
			if (count($m)>0)
				$offsetpagebreak = $m[0][1];
			else
				$offsetpagebreak = 0;
			if ($offsetpagebreak!=0) 
				$text = substr($text, 0, $offsetpagebreak).$code.substr($text, $offsetpagebreak);
			else
				$text .= $code;
		} else
			$text = preg_replace($this->regex, $code, $text, 1);

		// Clean up generated variables
		unset($startmem, $endmem, $diffmem, $mp, $offset, $lbcode, $m, $offsetpagebreak, $code);
		
		return true;
	}
	
	function _processMapv2($mp, &$text) {
		// Variables of process
		$code='';
		$lbcode='';
		
		if ($mp->googlebar=='1'||$mp->localsearch=='1') {
			$searchoption = array();

			switch ($mp->searchlist) {
			case "suppress":
				$searchoption[] ="resultList : G_GOOGLEBAR_RESULT_LIST_SUPPRESS";
				break;
			
			case "inline":
				$searchoption[] ="resultList : G_GOOGLEBAR_RESULT_LIST_INLINE";
				break;

			case "div":
				$searchoption[] ="resultList : document.getElementById('searchresult".$mp->mapnm."')";
				break;

			default:
				if(empty($mp->searchlist))
					$searchoption[] ="resultList : G_GOOGLEBAR_RESULT_LIST_INLINE";
				else {
					$searchoption[] ="resultList : document.getElementById('".$mp->searchlist."')";
					$extsearchresult= true;
				}
				break;
			}
			
			switch ($mp->searchtarget) {
			case "_self":
				$searchoption[] ="linkTarget : G_GOOGLEBAR_LINK_TARGET_SELF";
				break;
			
			case "_blank":
				$searchoption[] ="linkTarget : G_GOOGLEBAR_LINK_TARGET_BLANK";
				break;

			case "_top":
				$searchoption[] ="linkTarget : G_GOOGLEBAR_LINK_TARGET_TOP";
				break;

			case "_parent":
				$searchoption[] ="linkTarget : G_GOOGLEBAR_LINK_TARGET_PARENT";
				break;

			default:
				$searchoption[] ="linkTarget : G_GOOGLEBAR_LINK_TARGET_BLANK";
				break;
			}
			
			if ($mp->searchzoompan=="1")
				$searchoption[] ="suppressInitialResultSelection : false
								  , suppressZoomToBounds : false";
			else

				$searchoption[] ="suppressInitialResultSelection : true
								  , suppressZoomToBounds : true";
								  
			$searchoptions = implode(', ', $searchoption);
		} else 
			$searchoptions = "";

		if ($mp->icon!='') {
			$code .= "\n<img src='".$mp->icon."' style='display:none' alt='icon' />";
			if ($mp->iconshadow!='')
				$code .= "\n<img src='".$mp->iconshadow."' style='display:none' alt='icon shadow' />";
			if ($mp->icontransparent!='')
				$code .= "\n<img src='".$mp->icontransparent."' style='display:none' alt='icon transparent' />";
		} 
		
		if ($mp->sv!='none'&&$mp->animdir=='0') {
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/images/man_arrow-0.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/images/man_arrow-1.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/images/man_arrow-2.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/images/man_arrow-3.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/images/man_arrow-4.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/images/man_arrow-5.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/images/man_arrow-6.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/images/man_arrow-7.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/images/man_arrow-8.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/images/man_arrow-9.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/images/man_arrow-10.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/images/man_arrow-11.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/images/man_arrow-12.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/images/man_arrow-13.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/images/man_arrow-14.png' style='display:none' alt='streetview icon' />";
			$code .= "\n<img src='".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/images/man_arrow-15.png' style='display:none' alt='streetview icon' />";
		}
		// Generate the map position prior to any Google Scripts so that these can parse the code
		$code.= "<!-- fail nicely if the browser has no Javascript -->
				<noscript><blockquote class='warning'><p>".$this->no_javascript."</p></blockquote></noscript>";			

		if ($mp->align!='none')
			$code.="<div id='mapbody".$mp->mapnm."' style=\"display: none; text-align:".$mp->align."\">";
		else
			$code.="<div id='mapbody".$mp->mapnm."' style=\"display: none;\">";

		if ($mp->lightbox=='1') {
			$lboptions = array();
			if (!empty($mp->lbxzoom))
				$lboptions[] = "zoom : ".$mp->lbxzoom;
			if (!empty($lbxcenterlat)&&!empty($lbxcenterlon))
				$lboptions[] = "mapcenter : \"".$lbxcenterlat." ".$lbxcenterlon."\"";

			$this->_lbxwidthorig = (is_numeric($this->_lbxwidthorig)?(($mp->kmlsidebar=="left"||$mp->kmlsidebar=="right")?$this->_lbxwidthorig+$this->_kmlsbwidthorig+5:$this->_lbxwidthorig)."px":$this->_lbxwidthorig);
			$lbname = (($mp->gotoaddr=='1'||(($mp->kmlrenderer=="google"&&count($mp->kmlsb)!=0)||($mp->kmlrenderer=="geoxml"&&(count($mp->kml)!=0||count($mp->kmlsb)!=0))&&($mp->kmlsidebar=="left"||$mp->kmlsidebar=="right"))||$mp->animdir!='0'||$mp->sv=='top'||$mp->sv=='bottom'||$mp->searchlist=='div'||$mp->dir=='5'||($mp->formaddress==1&&$mp->animdir==0))?"lightbox":"googlemap");
			
			if ($mp->show==1) {
				$code.="<a href='javascript:void(0)' onclick='javascript:MOOdalBox.open(\"".$lbname.$mp->mapnm."\", \"".$mp->lbxcaption."\", \"".$this->_lbxwidthorig." ".$mp->lbxheight."\", map".$mp->mapnm.", {".implode(",",$lboptions)."});return false;' class='lightboxlink'>".html_entity_decode($mp->txtlightbox)."</a>";
				$code .= "<div id='lightbox".$mp->mapnm."'>";
			} else {
				$lbcode.="<a href='javascript:void(0)' onclick='javascript:MOOdalBox.open(\"".$lbname.$mp->mapnm."\", \"".$mp->lbxcaption."\", \"".$this->_lbxwidthorig." ".$mp->lbxheight."\", map".$mp->mapnm.", {".implode(",",$lboptions)."});return false;' class='lightboxlink'>".html_entity_decode($mp->txtlightbox)."</a>";
				$code .= "<div id='lightbox".$mp->mapnm."' style='display:none'>";
			}
		}

		if ($mp->gotoaddr=='1')	{
			$code.="<form name=\"gotoaddress".$mp->mapnm."\" class=\"gotoaddress\" onSubmit=\"javascript:gotoAddress".$mp->mapnm."();return false;\">";
			$code.="	<input id=\"txtAddress".$mp->mapnm."\" name=\"txtAddress".$mp->mapnm."\" type=\"text\" size=\"25\" value=\"\">";
			$code.="	<input name=\"goto\" type=\"button\" class=\"button\" onClick=\"gotoAddress".$mp->mapnm."();return false;\" value=\"Goto\">";
			$code.="</form>";
		}
		
		if ($mp->formaddress==1&&$mp->animdir==0) {
			$code.="<form id='directionform".$mp->mapnm."' action='".$this->protocol.$this->googlewebsite."/maps' method='get' target='_blank' onsubmit='DirectionMarkersubmit".$mp->mapnm."(this);return false;' class='mapdirform'>";
			$code.=$mp->txtdir;
			$code.="<br />".$mp->txtfrom."<input type='text' class='inputbox' size='20' name='saddr' id='saddr' value='".(($mp->formdir=='1')?$mp->address:(($mp->formdir=='2')?$mp->toaddress:""))."' />";
			$code.="<br />".$mp->txtto."<input type='text' class='inputbox' size='20' name='daddr' id='daddr' value='".(($mp->formdir=='1')?$mp->toaddress:(($mp->formdir=='2')?$mp->address:""))."'/>";

			if ($mp->txt_driving!=''||$mp->dirtype=="D")
				$code.="<br/><input ".(($mp->txt_driving=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg' value='' ".(($mp->dirtype=="D")?"checked='checked'":"")." />".$mp->txt_driving.(($mp->txt_driving!='')?"&nbsp;":"");
			if ($mp->txt_avhighways!=''||$mp->dirtype=="1")
				$code.="<input ".(($mp->txt_avhighways=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg' value='h' ".(($mp->avoidhighways=='1')?"checked='checked'":"")." />".$mp->txt_avhighways.(($mp->txt_avhighways!='')?"&nbsp;":"");
			if ($mp->txt_walking!=''||$mp->dirtype=="W")
				$code.="<input ".(($mp->txt_walking=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg' value='w' ".(($mp->dirtype=="W")?"checked='checked'":"")." />".$mp->txt_walking.(($mp->txt_walking!='')?"&nbsp;":"");
			$code.="<input value='".$mp->txtgetdir."' class='button' type='submit' style='margin-top: 2px;'>";
			
			if ($mp->dir=='2')
				$code.= "<input type='hidden' name='pw' value='2'/>";

			if ($this->_lang!='') 
				$code.= "<input type='hidden' name='hl' value='".$this->_lang."'/>";
			$code.="</form>";
		}
		
		if ((($mp->kmlrenderer=="google"&&count($mp->kmlsb)!=0)||($mp->kmlrenderer=="geoxml"&&(count($mp->kml)!=0||count($mp->kmlsb)!=0)))&&($mp->kmlsidebar=="left"||$mp->kmlsidebar=="right"))
			$code.="<table style=\"width:100%;border-spacing:0px;\">
					<tr>";

		if ((($mp->kmlrenderer=="google"&&count($mp->kmlsb)!=0)||($mp->kmlrenderer=="geoxml"&&(count($mp->kml)!=0||count($mp->kmlsb)!=0)))&&$mp->kmlsidebar=="left")
			$code.="<td style=\"width:".$mp->kmlsbwidth.";height:".$mp->height.";vertical-align:top;\"><div id=\"kmlsidebar".$mp->mapnm."\" class=\"kmlsidebar\" style=\"align:left;width:".$mp->kmlsbwidth.";height:".$mp->height.";overflow:auto;\"></div></td>";

		if ((($mp->kmlrenderer=="google"&&count($mp->kmlsb)!=0)||($mp->kmlrenderer=="geoxml"&&(count($mp->kml)!=0||count($mp->kmlsb)!=0)))&&($mp->kmlsidebar=="left"||$mp->kmlsidebar=="right"))
			$code.="<td>";
			
		if ($mp->sv=='top'||($mp->animdir!='0'&&$mp->animdir!='3')) {
			$code.="<div id='svpanel".$mp->mapnm."' class='svPanel' style='width:".$mp->svwidth."; height:".$mp->svheight."'><div id='svpanorama".$mp->mapnm."' class='streetview' style='width:".$mp->svwidth."; height:".$mp->svheight.(($mp->kmlsidebar=="right")?"float:left;":"").";'></div>";

			if ($mp->animdir!='0') {
				$code.="<div id='status".$mp->mapnm."' class='status' style='top: -".floor($mp->svheight/2)."px'><b>Loading</b></div><div id='instruction".$mp->mapnm."' class='instruction'></div></div><div id='progressBorder".$mp->mapnm."' class='progressBorder'><div id='progressBar".$mp->mapnm."' class='progressBar'></div></div>";
				$code.= "<div class='animforms'>";
				$code.= "<div class='animbuttonforms'><input type='button' value='Drive' id='stopgo".$mp->mapnm."'  onclick='route".$mp->mapnm.".startDriving()'  disabled='disabled' /></div>";

				if ($mp->formspeed==1)
					$code.= "<div class='animformspeed'>
								<div class='animlabel'>".((array_key_exists(16, $this->_langanim))?$this->_langanim[16]:"Drive")."</div>
								<select id='speed".$mp->mapnm."' onchange='route".$mp->mapnm.".setSpeed()'>
									<option value='0'>".((array_key_exists(17, $this->_langanim))?$this->_langanim[17]:"Fast")."</option>
									<option value='1' selected='selected'>".((array_key_exists(18, $this->_langanim))?$this->_langanim[18]:"Normal")."</option>
									<option value='2'>".((array_key_exists(19, $this->_langanim))?$this->_langanim[19]:"Slow")."</option>
								</select>
							</div>";

				if ($mp->formdirtype==1)
					$code.= "<div class='animformdirtype'>
								<input ".(($mp->txt_driving=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg".$mp->mapnm."' value='' ".(($mp->dirtype=="D")?"checked='checked'":"")." />".$mp->txt_driving.(($mp->txt_driving!='')?"&nbsp;":"")."<br />
								<input ".(($mp->txt_avhighways=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg".$mp->mapnm."' value='h' ".(($mp->avoidhighways=='1')?"checked='checked'":"")." />".$mp->txt_avhighways.(($mp->txt_avhighways!='')?"&nbsp;":"")."<br />
								<input ".(($mp->txt_walking=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg".$mp->mapnm."' value='w' ".(($mp->dirtype=="W")?"checked='checked'":"")." />".$mp->txt_walking.(($mp->txt_walking!='')?"&nbsp;":"")."<br />
							</div>";

				if ($mp->formaddress==1)
					$code.= "<div class='animformaddress'>
								".(($mp->txtfrom=='')?"":"<div class='animlabel'>".$mp->txtfrom."</div>")."
								<div class='animinput'><input id='from".$mp->mapnm."' ".(($mp->txtfrom=='')?"type='hidden' ":"")." size='30' value='".(($mp->formdir=='1')?$mp->address:(($mp->formdir=='2')?$mp->toaddress:""))."'/></div>
								<div style='clear: both;'></div>
								".(($mp->txtto=='')?"":"<div class='animlabel'>".$mp->txtto."</div>")."
								<div class='animinput'><input id='to".$mp->mapnm."' ".(($mp->txtto=='')?"type='hidden' ":"")." size='30' value='".(($mp->formdir=='1')?$mp->toaddress:(($mp->formdir=='2')?$mp->address:""))."'/></div>
							</div>
							<div class='animbuttons'>
								<input type='button' value='".((array_key_exists(15, $this->_langanim))?$this->_langanim[15]:"Route")."' class='animroute' onclick='route".$mp->mapnm.".generateRoute()' />
							</div>
							";
			}
			$code.="<div style=\"clear: both;\"></div>";
			$code.="</div>";
		}

		if (($mp->animdir=='2'||$mp->animdir=='3')&&$mp->showdir!='0') {
			$code.="<table style=\"width:".$mp->width.";\"><tr>";
			$code.="<td style='width:50%;'><div id=\"googlemap".$mp->mapnm."\" ".((!empty($mp->mapclass))?"class=\"".$mp->mapclass."\"" :"class=\"map\"")." style=\"" . ($mp->align != 'none' ? ($mp->align == 'center' || $mp->align == 'left' ? 'margin-right: auto; ' : '') . ($mp->align == 'center' || $mp->align == 'right' ? 'margin-left: auto; ' : '') : '') . "width:100%; height:".$mp->height.";".(($mp->show==0&&$mp->lightbox==0)?"display:none;":"").(($mp->kmlsidebar=="right"||$mp->animdir=='2')?"float:left;":"")."\"></div></td>";
			$code.= "<td style='width:50%;'><div id=\"dirsidebar".$mp->mapnm."\" class='directions' style='float:left;width:100%;height: ".$mp->height.";overflow:auto; '></div></td>";				
			$code.="</tr></table>";
		} else {
			$code.="<div id=\"googlemap".$mp->mapnm."\" ".((!empty($mp->mapclass))?"class=\"".$mp->mapclass."\"" :"class=\"map\"")." style=\"" . ($mp->align != 'none' ? ($mp->align == 'center' || $mp->align == 'left' ? 'margin-right: auto; ' : '') . ($mp->align == 'center' || $mp->align == 'right' ? 'margin-left: auto; ' : '') : '') . "width:".$mp->width."; height:".$mp->height.";".(($mp->show==0&&$mp->lightbox==0)?"display:none;":"").(($mp->kmlsidebar=="right"||$mp->animdir=='2')?"float:left;":"")."\"></div>";
		}
					
		if ($mp->sv=='bottom'||$mp->animdir=="3") {
			if ($mp->animdir=='3') {
				$code.="<div id='progressBorder".$mp->mapnm."' class='progressBorder'><div id='progressBar".$mp->mapnm."' class='progressBar'></div></div>";
				$code.= "<div class='animforms'>";
				$code.= "<div class='animbuttonforms'><input type='button' value='Drive' id='stopgo".$mp->mapnm."'  onclick='route".$mp->mapnm.".startDriving()'  disabled='disabled' /></div>";


				if ($mp->formspeed==1)
					$code.= "<div class='animformspeed'>
								<div class='animlabel'>".((array_key_exists(16, $this->_langanim))?$this->_langanim[16]:"Drive")."</div>
								<select id='speed".$mp->mapnm."' onchange='route".$mp->mapnm.".setSpeed()'>
									<option value='0'>".((array_key_exists(17, $this->_langanim))?$this->_langanim[17]:"Fast")."</option>
									<option value='1' selected='selected'>".((array_key_exists(18, $this->_langanim))?$this->_langanim[18]:"Normal")."</option>
									<option value='2'>".((array_key_exists(19, $this->_langanim))?$this->_langanim[19]:"Slow")."</option>
								</select>
							</div>";

				if ($mp->formdirtype==1)
					$code.= "<div class='animformdirtype'>
								<input ".(($mp->txt_driving=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg".$mp->mapnm."' value='' ".(($mp->dirtype=="D")?"checked='checked'":"")." />".$mp->txt_driving.(($mp->txt_driving!='')?"&nbsp;":"")."<br />
								<input ".(($mp->txt_avhighways=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg".$mp->mapnm."' value='h' ".(($mp->avoidhighways=='1')?"checked='checked'":"")." />".$mp->txt_avhighways.(($mp->txt_avhighways!='')?"&nbsp;":"")."<br />
								<input ".(($mp->txt_walking=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg".$mp->mapnm."' value='w' ".(($mp->dirtype=="W")?"checked='checked'":"")." />".$mp->txt_walking.(($mp->txt_walking!='')?"&nbsp;":"")."<br />
							</div>";

				if ($mp->formaddress==1)
					$code.= "<div class='animformaddress'>
								".(($mp->txtfrom=='')?"":"<div class='animlabel'>".$mp->txtfrom."</div>")."
								<div class='animinput'><input id='from".$mp->mapnm."' ".(($mp->txtfrom=='')?"type='hidden' ":"")." size='30' value='".(($mp->formdir=='1')?$mp->address:(($mp->formdir=='2')?$mp->toaddress:""))."'/></div>
								<div style='clear: both;'></div>
								".(($mp->txtto=='')?"":"<div class='animlabel'>".$mp->txtto."</div>")."
								<div class='animinput'><input id='to".$mp->mapnm."' ".(($mp->txtto=='')?"type='hidden' ":"")." size='30' value='".(($mp->formdir=='1')?$mp->toaddress:(($mp->formdir=='2')?$mp->address:""))."'/></div>
							</div>
							<div class='animbuttons'>
								<input type='button' value='".((array_key_exists(15, $this->_langanim))?$this->_langanim[15]:"Route")."' class='animroute' onclick='route".$mp->mapnm.".generateRoute()' />
							</div>
							";
			}
			$code.="<div style=\"clear: both;\"></div>";
			$code.="</div>";
			$code.="<div id='svpanel".$mp->mapnm."' class='svPanel' style='width:".$mp->svwidth."; height:".$mp->svheight."'><div id='svpanorama".$mp->mapnm."' class='streetview' style='width:".$mp->svwidth."; height:".$mp->svheight.(($mp->kmlsidebar=="right")?"float:left;":"").";'></div>";
			if ($mp->animdir!='0')
				$code.="<div id='status".$mp->mapnm."' class='status' style='top: -".floor($mp->svheight/2)."px'><b>Loading</b></div><div id='instruction".$mp->mapnm."' class='instruction'></div></div>";
		}

		if ((($mp->kmlrenderer=="google"&&count($mp->kmlsb)!=0)||($mp->kmlrenderer=="geoxml"&&(count($mp->kml)!=0||count($mp->kmlsb)!=0)))&&($mp->kmlsidebar=="left"||$mp->kmlsidebar=="right"))
			$code.="</td>";
		
		if ((($mp->kmlrenderer=="google"&&count($mp->kmlsb)!=0)||($mp->kmlrenderer=="geoxml"&&(count($mp->kml)!=0||count($mp->kmlsb)!=0)))&&$mp->kmlsidebar=="right")
			$code.="<td style=\"width:".$mp->kmlsbwidth.";height:".$mp->height.";vertical-align:top;\"><div id=\"kmlsidebar".$mp->mapnm."\"  class=\"kmlsidebar\" style=\"align:left;width:".$mp->kmlsbwidth.";height:".$mp->height.";overflow:auto;\"></div></td>";
			
		if ((($mp->kmlrenderer=="google"&&count($mp->kmlsb)!=0)||($mp->kmlrenderer=="geoxml"&&(count($mp->kml)!=0||count($mp->kmlsb)!=0)))&&($mp->kmlsidebar=="left"||$mp->kmlsidebar=="right"))
			$code.="</tr>
					</table>";

		if ($mp->searchlist=='div')
			$code.="<div id=\"searchresult".$mp->mapnm."\"></div>";

		if ($mp->kmlsidebar=="left"||$mp->kmlsidebar=="right")
			$code.="<div style=\"clear: both;\"></div>";
		
		if (((!empty($mp->tolat)&&!empty($mp->tolon))||!empty($mp->address)||($mp->dir=='5'))&&($mp->animdir!='2'||($mp->animdir=='2'&&$mp->showdir=='0')))
			$code.= "<div id=\"dirsidebar".$mp->mapnm."\" class='directions' ".(($mp->showdir=='0')?"style='display:none'":"")."></div>";

		if ($mp->lightbox=='1')
			$code .= "</div>";

		// Close of mapbody div
		$code.="</div>";

		// Only add the scripts and css once
		if($this->first_google) {
			$url = $this->protocol.$this->googlewebsite."/maps?file=api&amp;v=".$this->google_API_version."&amp;oe=".$this->iso;				
			if ($this->_lang!='') 
				$url .= "&amp;hl=".$this->_lang;

			$url .= "&amp;key=".$this->googlekey;
			$url .= "&amp;sensor=false";
			$url .= "&amp;indexing=".(($this->googleindexing)?"true":"false");
			
			$this->_addscript($url, $text);
			$this->first_google=false;
		}

		if (($mp->kmllightbox=="1"||$mp->lightbox=="1"||$mp->effect!="none"||$mp->dir=="3"||$mp->dir=="4"||strpos($mp->description, "MOOdalBox"))&&$this->first_mootools) {
			JHTML::_('behavior.mootools');
			$this->first_mootools = false;
		}

		if (($mp->kmllightbox=="1"||$mp->lightbox=="1"||$mp->dir=="3"||$mp->dir=="4"||strpos($mp->description, "MOOdalBox"))&&$this->first_modalbox)	{
			if (substr($this->jversion,0,3)=='1.5')
				$this->_addscript($this->base."/media/plugin_googlemap2/site/moodalbox/js/modalbox1.2hack.js", $text);
			else
				$this->_addscript($this->base."/media/plugin_googlemap2/site/moodalbox/js/moodalbox1.3hack.js", $text);
			
			$this->_addstylesheet($this->base."/media/plugin_googlemap2/site/moodalbox/css/moodalbox.css", $text);
			$this->first_modalbox = false;
		}

		if (($mp->localsearch=="1"||$this->_client_geo==1)&&$this->first_localsearch) {
			$this->_addscript($this->protocol."www.google.com/uds/api?file=uds.js&amp;v=1.0&amp;key=".$this->googlekey, $text);
			$this->_addscript($this->protocol."www.google.com/uds/solutions/localsearch/gmlocalsearch.js".((!empty($mp->adsense))?"?adsense=".$mp->adsense:"").((!empty($mp->channel)&&!empty($mp->adsense))?"&amp;channel=".$mp->channel:""), $text);
			$style = "@import url('".$this->protocol."www.google.com/uds/css/gsearch.css');\n@import url('".$this->protocol."www.google.com/uds/solutions/localsearch/gmlocalsearch.css');";
			$this->_addstyledeclaration($style, $text);
			$this->first_localsearch = false;
		}
		
		if ($this->first_kmlelabel&&(($mp->kmlpolylabel!=""&&$mp->kmlpolylabelclass!="")||($mp->kmlmarkerlabel!=""&&$mp->kmlmarkerlabelclass!=""))) {
			$this->_addscript($this->base."/media/plugin_googlemap2/site/elabel/elabel.js", $text);
			$this->first_kmlelabel = false;
		}
		
		if (($mp->kmlrenderer=='geoxml'||count($mp->kmlsb)!=0)&&$this->first_kmlrenderer) {
			$this->_addscript($this->base."/media/plugin_googlemap2/site/geoxml/geoxml.js", $text);
			$this->first_kmlrenderer = false;
		}
		
		if ($mp->zoomType=='3D-largeSV'&&$this->first_svcontrol) {
			$this->_addscript($this->base."/media/plugin_googlemap2/site/StreetViewControl/StreetViewControl.js", $text);
			$this->first_svcontrol = false;
		}

		if ($mp->animdir!='0'&&$this->first_animdir) {
			$this->_addscript($this->base."/media/plugin_googlemap2/site/directions/directions.js", $text);
			$this->_addstylesheet($this->base."/media/plugin_googlemap2/site/directions/directions.css", $text);
			$this->first_animdir = false;
		}
		
		if ($mp->kmlrenderer=='arcgis'&&$this->first_arcgis) {
			$this->_addscript($this->protocol."serverapi.arcgisonline.com/jsapi/gmaps/?v=1.4", $text);
			$this->first_arcgis = false;
		}

		if ($mp->panotype!='none'&&$this->first_panoramiolayer) {
			$this->_addscript($this->base."/media/plugin_googlemap2/site/panoramiolayer/panoramiolayer.js", $text);
			$this->first_panoramiolayer = false;
		}

		$code.="<script type='text/javascript'>//<![CDATA[\n";
		if ($this->debug_plugin=="1")
			$code.="function VersionControl(opt_no_style){
					  this.noStyle = opt_no_style;
					};
					VersionControl.prototype = new GControl();
					VersionControl.prototype.initialize = function(map) {
					  var display = document.createElement('div');
					  map.getContainer().appendChild(display);
					  display.innerHTML = '2.'+G_API_VERSION;
					  display.className = 'api-version-display';
					  if(!this.noStyle){
						display.style.fontFamily = 'Arial, sans-serif';
						display.style.fontSize = '11px';
					  }
					  this.htmlElement = display;
					  return display;
					}
					VersionControl.prototype.getDefaultPosition = function() {
					  return new GControlPosition(G_ANCHOR_BOTTOM_LEFT, new GSize(3, 38));
					}
				";

		// Globale map variable linked to the div
		$code.="var tst".$mp->mapnm."=document.getElementById('googlemap".$mp->mapnm."');
		var tstint".$mp->mapnm.";
		var map".$mp->mapnm.";
		var mySlidemap".$mp->mapnm.";
		var overviewmap".$mp->mapnm.";
		var overmap".$mp->mapnm.";
		var xml".$mp->mapnm.";
		var imageovl".$mp->mapnm.";
		var directions".$mp->mapnm.";
		";
		
		if ($this->proxy=="1") {
			if (substr($this->jversion,0,3)=="1.5")
				$code .= "\nvar proxy = '".$this->base."/plugins/system/plugin_googlemap2_proxy.php?';";
			else
				$code .= "\nvar proxy = '".$this->base."/plugins/system/plugin_googlemap2/plugin_googlemap2_proxy.php?';";
		}

		if ($mp->traffic=='1') 
			$code.="\nvar trafficInfo".$mp->mapnm.";";
		if ($mp->localsearch=='1') 
			$code.="\nvar localsearch".$mp->mapnm.";";
		if ($mp->adsmanager=='1') 
			$code.="\nvar adsmanager".$mp->mapnm.";";
		if ($mp->kmlrenderer=='geoxml'||count($mp->kmlsb)!=0) {
			$code.="\nvar exml".$mp->mapnm.";";

			$code.="\ntop.publishdirectory = '".$this->base."/media/plugin_googlemap2/site/geoxml/';";
		}
		if (count($mp->lookat)>0||count($mp->camera)>0||$mp->tilelayer!=''||$mp->mapType=='earth')
			$code.="\nvar geplugin".$mp->mapnm.";";
		if ($mp->panotype!='none')
			$code.="\nvar panoLayer".$mp->mapnm.";";

		if ($mp->icon!='') {
			$code.="\nmarkericon".$mp->mapnm." = new GIcon(G_DEFAULT_ICON);";
			$code.="\nmarkericon".$mp->mapnm.".image = '".$mp->icon."';";
			if ($mp->iconwidth!=''&&$mp->iconheight!='')
				$code.="\nmarkericon".$mp->mapnm.".iconSize = new GSize(".$mp->iconwidth.", ".$mp->iconheight.");";
			if ($mp->iconshadow !='') {
				$code.="\nmarkericon".$mp->mapnm.".shadow = '".$mp->iconshadow."';";

				if ($mp->iconshadowwidth!=''&&$mp->iconshadowheight!='') 
					$code.="\nmarkericon".$mp->mapnm.".shadowSize = new GSize(".$mp->iconshadowwidth.", ".$mp->iconshadowheight.");";
				if ($mp->iconshadowanchorx!=''&&$mp->iconshadowanchory!='')
					$code.="\nmarkericon".$mp->mapnm.".infoShadowAnchor = new GPoint(".$mp->iconshadowanchorx.", ".$mp->iconshadowanchory.");";
			}
			if ($mp->iconanchorx!=''&&$mp->iconanchory!='')
				$code.="\nmarkericon".$mp->mapnm.".iconAnchor = new GPoint(".$mp->iconanchorx.", ".$mp->iconanchory.");";
			if ($mp->iconinfoanchorx!=''&&$mp->iconinfoanchory!='')
				$code.="\nmarkericon".$mp->mapnm.".infoWindowAnchor = new GPoint(".$mp->iconinfoanchorx.", ".$mp->iconinfoanchory.");";
			if ($mp->icontransparent!='') 			
				$code.="\nmarkericon".$mp->mapnm.".transparent = '".$mp->icontransparent."';";
			if ($mp->iconimagemap!='')
				$code.="\nmarkericon".$mp->mapnm.".imageMap = [".$mp->iconimagemap."];";
		}
		
		if ($mp->sv!='none'||$mp->animdir!='0') {
			$code.="\nvar svclient".$mp->mapnm.";
					var svmarker".$mp->mapnm.";
					var svlastpoint".$mp->mapnm.";
					var svpanorama".$mp->mapnm.";
					";
		}

		if ($mp->animdir!='0')				
			$code.="\nvar route".$mp->mapnm.";
					";
		
		if ($mp->sv!='none'&&$mp->animdir=='0') {
			$code.="\nvar guyIcon".$mp->mapnm." = new GIcon(G_DEFAULT_ICON);
					guyIcon".$mp->mapnm.".image = '".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man_arrow-0.png';
					guyIcon".$mp->mapnm.".transparent = '".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man-pick.png';
					guyIcon".$mp->mapnm.".imageMap = [26,13, 30,14, 32,28, 27,28, 28,36, 18,35, 18,27, 16,26, 16,20, 16,14, 19,13, 22,8];
					guyIcon".$mp->mapnm.".iconSize = new GSize(49, 52);
					guyIcon".$mp->mapnm.".iconAnchor = new GPoint(25, 35);
					guyIcon".$mp->mapnm.".infoWindowAnchor = new GPoint(25, 5);
					";
		}
		if ($mp->tilelayer!="") {
			$code.="\nvar tilelayer".$mp->mapnm.";
					var mercator".$mp->mapnm.";
					";
		}

		if ( array_key_exists('HTTP_USER_AGENT',$_SERVER) && strpos(" ".$_SERVER['HTTP_USER_AGENT'], 'Opera') ) {
			$code.="var _mSvgForced = true;
					var _mSvgEnabled = true; ";
		}

		if($mp->zoomwheel=='1') {
			$code.="function CancelEvent".$mp->mapnm."(event) { 
						var e = event; 
						if (typeof e.preventDefault == 'function') e.preventDefault(); 
							if (typeof e.stopPropagation == 'function') e.stopPropagation(); 

						if (window.event) { 
							window.event.cancelBubble = true; // for IE 
							window.event.returnValue = false; // for IE 
						} 
					}
				";
		}
		
		$code.="\nfunction resetposition".$mp->mapnm."() {
			map".$mp->mapnm.".returnToSavedPosition();
		}";

		if ($mp->gotoaddr=='1') {
			$code.="function gotoAddress".$mp->mapnm."() {
						var address = document.getElementById('txtAddress".$mp->mapnm."').value;

						if (address.length > 0) {
							var geocoder = new GClientGeocoder();
							geocoder.setViewport(map".$mp->mapnm.".getBounds());

							geocoder.getLatLng(address,
							function(point) {
								if (!point) {
									var erraddr = '{$mp->erraddr}';
									erraddr = erraddr.replace(/##/, address);
								  alert(erraddr);
								} else {
								  var txtaddr = '{$mp->txtaddr}';
								  txtaddr = txtaddr.replace(/##/, address);
								  map".$mp->mapnm.".setCenter(point".(($mp->gotoaddrzoom!=0)?",".$mp->gotoaddrzoom:"").");
								  map".$mp->mapnm.".openInfoWindowHtml(point,txtaddr);
								  setTimeout('map".$mp->mapnm.".closeInfoWindow();', 5000);
								}
							  });
						  }
						  return false;
						  
					}";
		}
		
		if (($mp->dir!='0')||((!empty($mp->tolat)&&!empty($mp->tolon))||!empty($mp->toaddress))&&$mp->animdir=='0') {
			$code .="function handleErrors".$mp->mapnm."(){
						var dirsidebar".$mp->mapnm." = document.getElementById('dirsidebar".$mp->mapnm."');
						var newelem = document.createElement('p');
						if (directions".$mp->mapnm.".getStatus().code == G_GEO_UNKNOWN_ADDRESS)
							newelem.innerHTML = 'No corresponding geographic location could be found for one of the specified addresses. This may be due to the fact that the address is relatively new, or it may be incorrect.<br />Error code: ' + directions".$mp->mapnm.".getStatus().code;
						else if (directions".$mp->mapnm.".getStatus().code == G_GEO_SERVER_ERROR)
							newelem.innerHTML = 'A geocoding or directions request could not be successfully processed, yet the exact reason for the failure is not known.<br />Error code: ' + directions".$mp->mapnm.".getStatus().code;
						else if (directions".$mp->mapnm.".getStatus().code == G_GEO_MISSING_QUERY)
							 newelem.innerHTML = 'The HTTP q parameter was either missing or had no value. For geocoder requests, this means that an empty address was specified as input. For directions requests, this means that no query was specified in the input.<br />Error code: ' + directions".$mp->mapnm.".getStatus().code;
						//   else if (directions".$mp->mapnm.".getStatus().code == G_UNAVAILABLE_ADDRESS)  <--- Doc bug... this is either not defined, or Doc is wrong
						//     newelem.innerHTML = 'The geocode for the given address or the route for the given directions query cannot be returned due to legal or contractual reasons.<br />Error code: ' + directions".$mp->mapnm.".getStatus().code;
						   else if (directions".$mp->mapnm.".getStatus().code == G_GEO_BAD_KEY)
							 newelem.innerHTML = 'The given key is either invalid or does not match the domain for which it was given.<br />Error code: ' + directions".$mp->mapnm.".getStatus().code;
						
						   else if (directions".$mp->mapnm.".getStatus().code == G_GEO_BAD_REQUEST)
							 newelem.innerHTML = 'A directions request could not be successfully parsed.<br />Error code: ' + directions".$mp->mapnm.".getStatus().code;
						   else newelem.innerHTML = 'An unknown error occurred.';
						dirsidebar".$mp->mapnm.".appendChild(newelem); 
					}
						";
			}
			
		if ($mp->dir!='0'&&$mp->animdir=='0') {
			$code.="\nDirectionMarkersubmit".$mp->mapnm." = function( formObj ){
						if(formObj.dir&&formObj.dir[1].checked ){
							tmp = formObj.daddr.value;
							formObj.daddr.value = formObj.saddr.value;
							formObj.saddr.value = tmp;
						}";
			if ($mp->dir=='1')
				$code.="\nformObj.submit();";
			elseif ($mp->dir=='2')
				$code.="\nformObj.submit();";
			elseif ($mp->dir=='3')
				$code.="\nfor (var i=0; i < formObj.dirflg.length; i++) {
						   if (formObj.dirflg[i].checked) {
							  var dirflg= formObj.dirflg[i].value;
							  break;
						   }
						}
						MOOdalBox.open('".$this->protocol.$this->googlewebsite."/maps?dir=to&dirflg='+dirflg+'&saddr='+formObj.saddr.value+'&hl=en&daddr='+formObj.daddr.value+'".(($this->_lang!='')?"&amp;hl=".$this->_lang:"")."&pw=2', '".$mp->lbxcaption."', '".$mp->lbxwidth." ".$mp->lbxheight."', null, 16);";
			elseif ($mp->dir=='5') 
					$code .= "\nfor (var i=0; i < formObj.dirflg.length; i++) {
								   if (formObj.dirflg[i].checked) {
									  var dirflg= formObj.dirflg[i].value;
									  break;
								   }
								}
								var dirsidebar".$mp->mapnm." = document.getElementById('dirsidebar".$mp->mapnm."');
								if (directions".$mp->mapnm.") {
									directions".$mp->mapnm.".clear();
									if ( dirsidebar".$mp->mapnm.".hasChildNodes() )
										{
											while ( dirsidebar".$mp->mapnm.".childNodes.length >= 1 )
											{
												dirsidebar".$mp->mapnm.".removeChild( dirsidebar".$mp->mapnm.".firstChild );       
											} 
										}
								} else {
									directions".$mp->mapnm." = new GDirections(map".$mp->mapnm.", dirsidebar".$mp->mapnm.");
									GEvent.addListener(directions".$mp->mapnm.", 'error', handleErrors".$mp->mapnm.");
								}
								options = Array();
								if (dirflg=='w')
									options.travelMode = G_TRAVEL_MODE_WALKING;
								if (dirflg=='h')
									options.avoidHighways = true;
								directions".$mp->mapnm.".load('from: '+formObj.saddr.value+' to: '+formObj.daddr.value, options);
							";
			else
				$code.="\nfor (var i=0; i < formObj.dirflg.length; i++) {
						   if (formObj.dirflg[i].checked) {
							  var dirflg= formObj.dirflg[i].value;
							  break;
						   }
						}
						MOOdalBox.open('".$this->protocol.$this->googlewebsite."/maps?dir=to&dirflg='+dirflg+'&saddr='+formObj.saddr.value+'&hl=en&daddr='+formObj.daddr.value+'".(($this->_lang!='')?"&amp;hl=".$this->_lang:"")."', '".$mp->lbxcaption."', '".$mp->lbxwidth." ".$mp->lbxheight."', null, 16);";
				
			$code.="\nif(formObj.dir&&formObj.dir[1].checked )
						setTimeout('DirectionRevert".$mp->mapnm."()',100);
					};";
			
			$code.="\nDirectionRevert".$mp->mapnm." = function(){
						formObj = document.getElementById('directionform".$mp->mapnm."');
						tmp = formObj.daddr.value;
						formObj.daddr.value = formObj.saddr.value;
						formObj.saddr.value = tmp;
					};";
		}
		
		// Function for overview
		if(!$mp->overview==0) {
			$code.="\nfunction checkOverview".$mp->mapnm."() {
						for (var i in overviewmap".$mp->mapnm.") {
							if (overviewmap".$mp->mapnm."[i].setMapType) {
								overmap".$mp->mapnm." = overviewmap".$mp->mapnm."[i];
								break;
							}
						}						
						if (overmap".$mp->mapnm.") {
					";
						  
			if($mp->overview==2)

			{
				$code.="\n		overviewmap".$mp->mapnm.".hide(true);";
			}

			switch ($mp->mapType) {
			case "satellite":
			
				$code.="\n		overmap".$mp->mapnm.".setMapType(G_SATELLITE_MAP);";
				break;
			
			case "hybrid":
				$code.="\n		overmap".$mp->mapnm.".setMapType(G_HYBRID_MAP);";
				break;

			case "terrain":
				$code.="\n		overmap".$mp->mapnm.".setMapType(G_PHYSICAL_MAP);";
				break;
			
			case "earth":
				break;

			default:
				$code.="\n		overmap".$mp->mapnm.".setMapType(G_NORMAL_MAP);";
				break;
			}
			
			if ($mp->ovzoom!="") {
				$code.="\n		setTimeout('overmap".$mp->mapnm.".setCenter(map".$mp->mapnm.".getCenter(), map".$mp->mapnm.".getZoom()+".$mp->ovzoom.")', 100);";
				$code.="\n		GEvent.addListener(map".$mp->mapnm.",'move',function() {
var c = Math.min(Math.max(0, map".$mp->mapnm.".getZoom()+".$mp->ovzoom."), 19);
overmap".$mp->mapnm.".setCenter(map".$mp->mapnm.".getCenter(), c);
});";
				$code.="\n		GEvent.addListener(map".$mp->mapnm.",'moveend',function() {
var c = Math.min(Math.max(0, map".$mp->mapnm.".getZoom()+".$mp->ovzoom."), 19);
overmap".$mp->mapnm.".setCenter(map".$mp->mapnm.".getCenter(), c);

});";
			}
			$code.= "\n	} else {
						  setTimeout('checkOverview".$mp->mapnm."()',100);
						}
					  }";
		}
		
		$code.="\nfunction initearth".$mp->mapnm."(geplugin) {
			if (!geplugin".$mp->mapnm.") {
					geplugin".$mp->mapnm." = geplugin;";

		// Add layers
		if ($mp->earthborders=="1")
			$code.="\n	geplugin".$mp->mapnm.".getLayerRoot().enableLayerById(geplugin".$mp->mapnm.".LAYER_BORDERS, true);";
		if ($mp->earthbuildings=="1")
			$code.="\n	geplugin".$mp->mapnm.".getLayerRoot().enableLayerById(geplugin".$mp->mapnm.".LAYER_BUILDINGS, true);";
		else
			$code.="\n	geplugin".$mp->mapnm.".getLayerRoot().enableLayerById(geplugin".$mp->mapnm.".LAYER_BUILDINGS, false);";
		if ($mp->earthroads=="1")
			$code.="\n	geplugin".$mp->mapnm.".getLayerRoot().enableLayerById(geplugin".$mp->mapnm.".LAYER_ROADS, true);";
		if ($mp->earthterrain=="1")
			$code.="\n	geplugin".$mp->mapnm.".getLayerRoot().enableLayerById(geplugin".$mp->mapnm.".LAYER_TERRAIN, true);";
		else
			$code.="\n	geplugin".$mp->mapnm.".getLayerRoot().enableLayerById(geplugin".$mp->mapnm.".LAYER_TERRAIN, false);";
			
		if ($mp->tilelayer) {
			$code.="\n	var url = '".$mp->tilelayer."';
			var newurl = url+'/doc.kml';
			var link = geplugin".$mp->mapnm.".createLink('');
			link.setHref(newurl);
			var networkLink = geplugin".$mp->mapnm.".createNetworkLink('');
			networkLink.set(link, false, false);
			geplugin".$mp->mapnm.".getFeatures().appendChild(networkLink);";
		}
		
		if (count($mp->lookat)>0||count($mp->camera)>0)
			$code.="\n	setTimeout('setearth".$mp->mapnm."()', ".$mp->earthtimeout.");";
			
		$code.="\n}
				}";
				
		if (count($mp->lookat)>0||count($mp->camera)>0) {
			$la = false;
			$cam = false;
			$code.="\nfunction setearth".$mp->mapnm."() {
						var lookat = geplugin".$mp->mapnm.".getView().copyAsLookAt(geplugin".$mp->mapnm.".ALTITUDE_RELATIVE_TO_GROUND);
						var camera = geplugin".$mp->mapnm.".getView().copyAsCamera(geplugin".$mp->mapnm.".ALTITUDE_RELATIVE_TO_GROUND);";
			
			if (count($mp->lookat)>0) {
				$values = explode(',', $mp->lookat[0]);
				if (count($values)>0&&$values[0]!='') { // Latitude
					$code.="\nlookat.setLatitude(".$values[0].");";
					$la = true;
				}
				if (count($values)>1&&$values[1]!='') { // Longitude
					$code.="\nlookat.setLongitude(".$values[1].");";
					$la = true;
				}
				if (count($values)>2&&$values[2]!='') { // Range
					$code.="\nlookat.setRange(".$values[2].");";
					$la = true;
				}
				if (count($values)>3&&$values[3]!='') { // tilt
					$code.="\nlookat.setTilt(".$values[3].");";
					$la = true;
				}
				if (count($values)>4&&$values[4]!='') { // setHeading
					$code.="\nlookat.setHeading(".$values[4].");";
					$la = true;
				}
				if (count($values)>5&&$values[5]!='') { // altitude
					$code.="\nlookat.setAltitude(".$values[5].");";
					$la = true;
				}
				if (count($values)>6&&$values[6]!='') {// flyspeed
					if ($values[6]=='teleport')
						$code.="\ngeplugin".$mp->mapnm.".getOptions().setFlyToSpeed(geplugin".$mp->mapnm.".SPEED_TELEPORT);";
					else
						$code.="\ngeplugin".$mp->mapnm.".getOptions().setFlyToSpeed(".$values[6].");";
				}
			}
			
			if (count($mp->camera)>0) {
				$values = explode(',', $mp->camera[0]);
				if (count($values)>0&&$values[0]!='') { // Latitude
					$code.="\ncamera.setLatitude(".$values[0].");";
					$cam = true;

				}
				if (count($values)>1&&$values[1]!='') { // Longitude
					$code.="\ncamera.setLongitude(".$values[1].");";
					$cam = true;
				}
				if (count($values)>2&&$values[2]!='') { // tilt
					$code.="\ncamera.setTilt(".$values[2].");";
					$cam = true;
				}
				if (count($values)>3&&$values[3]!='') { // heading
					$code.="\ncamera.setHeading(".$values[3].");";
					$cam = true;
				}
				if (count($values)>4&&$values[4]!='') { // altitude
					$code.="\ncamera.setAltitude(".$values[4].");";
					$cam = true;
				}
				if (count($values)>5&&$values[5]!='') { // roll
					$code.="\ncamera.setRoll(".$values[5].");";
					$cam = true;
				}
				if (count($values)>6&&$values[6]!='') {// flyspeed
					if ($values[6]=='teleport')
						$code.="\ngeplugin".$mp->mapnm.".getOptions().setFlyToSpeed(geplugin".$mp->mapnm.".SPEED_TELEPORT);";
					else
						$code.="\ngeplugin".$mp->mapnm.".getOptions().setFlyToSpeed(".$values[6].");";
				}
			}
					
			if ($la)
				$code.="\n	geplugin".$mp->mapnm.".getView().setAbstractView(lookat);";
			if ($cam)
				$code.="\n	geplugin".$mp->mapnm.".getView().setAbstractView(camera);";
				
			$code.="\n}";
		}

		if ($mp->kmlrenderer=='arcgis') {
			$code .="\nfunction dynmapcallback".$mp->mapnm."(mapservicelayer) {
						  map".$mp->mapnm.".addOverlay(mapservicelayer);
							}";	
		}
		
		if ($mp->kmlrenderer=='google') {
			$code .= "\nfunction savePositionKML".$mp->mapnm."() {
							ok = true;
							for (x=0;x<xml".$mp->mapnm.".length;x++) {
								if (!xml".$mp->mapnm."[x].hasLoaded())
									ok = false;
							}
							if (ok)
								map".$mp->mapnm.".savePosition();
							else
								setTimeout('savePositionKML".$mp->mapnm."()',100);
						}
					";
		}
		
			
		// Functions to watch if the map has changed
		$code.="\nfunction checkMap".$mp->mapnm."()
		{
			if (tst".$mp->mapnm.") {
			";
			
		if ($mp->show!=0)
			$code.="\n			if (tst".$mp->mapnm.".offsetWidth != tst".$mp->mapnm.".getAttribute(\"oldValue\"))
					{
						tst".$mp->mapnm.".setAttribute(\"oldValue\",tst".$mp->mapnm.".offsetWidth);
						if (tst".$mp->mapnm.".offsetWidth > 0) {
					";

		$code.="\n				if (tst".$mp->mapnm.".getAttribute(\"refreshMap\")==0)
							clearInterval(tstint".$mp->mapnm.");";
		if ($mp->effect !='none') 
			$code .="\n					mySlidemap".$mp->mapnm." = new Fx.Slide('googlemap".$mp->mapnm."',{duration: 1500, mode: '".$mp->effect."'});
							mySlidemap".$mp->mapnm.".hide();
							mySlidemap".$mp->mapnm.".slideIn();";

		$code .="\n					getMap".$mp->mapnm."();
							tst".$mp->mapnm.".setAttribute(\"refreshMap\", 1);";
		if ($mp->show!=0)
			$code .="\n				} 
					}";
		$code .="\n	}
		}
		";

		if ($mp->sv!="none"&&$mp->animdir=='0') {
			$code .="function onYawChange".$mp->mapnm."(newYaw) {
						var GUY_NUM_ICONS = 16;
						var GUY_ANGULAR_RES = 360/GUY_NUM_ICONS;
						if (newYaw < 0) {
							newYaw += 360;
						}
						var guyImageNum = Math.round(newYaw/GUY_ANGULAR_RES) % GUY_NUM_ICONS;
						var guyImageUrl = '".$this->base."/media/plugin_googlemap2/site/StreetViewControl/images/man_arrow-' + guyImageNum + '.png';
						svmarker".$mp->mapnm.".setImage(guyImageUrl);
					}

					function onNewLocation".$mp->mapnm."(point) {
						// Get the original x + y coordinates
						svmarker".$mp->mapnm.".setLatLng(point.latlng);
						map".$mp->mapnm.".panTo(point.latlng);
						svlastpoint".$mp->mapnm." = point.latlng;
					}

					function onDragEnd".$mp->mapnm."() {
						var latlng = svmarker".$mp->mapnm.".getLatLng();
						if (svpanorama".$mp->mapnm.") {
							svclient".$mp->mapnm.".getNearestPanorama(latlng, svonResponse".$mp->mapnm.");
						}
					}

					function svonResponse".$mp->mapnm."(response) {
						if (response.code != 200) {
							svmarker".$mp->mapnm.".setLatLng(svlastpoint".$mp->mapnm.");
							map".$mp->mapnm.".setCenter(svlastpoint".$mp->mapnm.");
						} else {
							var latlng = new GLatLng(response.Location.lat, response.Location.lng);

							svmarker".$mp->mapnm.".setLatLng(latlng);
							svlastpoint".$mp->mapnm." = latlng;
							svpanorama".$mp->mapnm.".setLocationAndPOV(latlng, null);
						}
					}
					";
		}

		// Function for displaying the map and marker
		$code.="\nfunction getMap".$mp->mapnm."(){";
	
		if ($mp->show!=0)
			$code.="\n	if (tst".$mp->mapnm.".offsetWidth > 0) {";
		
		$code.="\n	map".$mp->mapnm." = new GMap2(document.getElementById('googlemap".$mp->mapnm."')".(($mp->googlebar=='1'&&!empty($searchoptions))?", { googleBarOptions: {".$searchoptions." } }":"").");
				map".$mp->mapnm.".getContainer().style.overflow='hidden';
				";
		
		if ($mp->sv!="none"||$mp->animdir!='0')
			$code.="svclient".$mp->mapnm." = new GStreetviewClient();";
			
		if($mp->keyboard=='1'&&$mp->controltype=='user')
		{
			$code.="new GKeyboardHandler(map".$mp->mapnm.");
			";
		} 
		if($mp->dragging=="0")
			$code.="map".$mp->mapnm.".disableDragging();";
	
		if ($mp->showterrainmaptype=="1")
			$code.="map".$mp->mapnm.".addMapType(G_PHYSICAL_MAP);";
		if ($mp->showearthmaptype=="1")
			$code.="map".$mp->mapnm.".addMapType(G_SATELLITE_3D_MAP);";
	
		if(!$mp->overview==0)
		{
			$code.="overviewmap".$mp->mapnm." = new GOverviewMapControl();";

			$code.="map".$mp->mapnm.".addControl(overviewmap".$mp->mapnm.", new GControlPosition(G_ANCHOR_BOTTOM_RIGHT));";
			$code.="setTimeout('checkOverview".$mp->mapnm."()',100);";
	
		} elseif (!$mp->overview==0) {
			$code.="overviewmap".$mp->mapnm." = new GOverviewMapControl();";
			$code.="map".$mp->mapnm.".addControl(overviewmap".$mp->mapnm.", new GControlPosition(G_ANCHOR_BOTTOM_RIGHT));";
			
			if($mp->overview==2)
			{
				$code.="overviewmap".$mp->mapnm.".hide(true);";
			}
		}
	
		if($mp->navlabel == 1)
			$code.="map".$mp->mapnm.".addControl(new GNavLabelControl(), new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(7, 30)));";
	
		if($this->_client_geo == 1) {
			if ($this->clientgeotype=="local") {
				$code.="\nvar localSearch = new GlocalSearch();";
				$replace = array("\n", "\r", "&lt;br/&gt;", "&lt;br /&gt;", "&lt;br&gt;");
				$addr = str_replace($replace, '', $mp->address);
	
				$code.="\nvar address = \"".$addr."\";";
				$code.="\nlocalSearch.setSearchCompleteCallback(null,	function() {
						if (localSearch.results[0]) {
							var resultLat = localSearch.results[0].lat;
							var resultLng = localSearch.results[0].lng;
							var point = new GLatLng(resultLat,resultLng);
						} else 
						";
				if ($mp->latitude !=''&&$mp->longitude!='')
					$code.="var point = new GLatLng( $mp->latitude, $mp->longitude);";
				else
					$code.="var point = new GLatLng( $mp->deflatitude, $mp->deflongitude);";
			} else {
				$code.="var geocoder = new GClientGeocoder();";
				$replace = array("\n", "\r", "&lt;br/&gt;", "&lt;br /&gt;", "&lt;br&gt;");
				$addr = str_replace($replace, '', $mp->address);
	
				$code.="var address = \"".$addr."\";";
				$code.="geocoder.getLatLng(address, function(point) {
							if (!point)";
							
				if ($mp->latitude !=''&&$mp->longitude!='')
					$code.="var point = new GLatLng( $mp->latitude, $mp->longitude);";
				else
					$code.="var point = new GLatLng( $mp->deflatitude, $mp->deflongitude);";
			}
		} else { 
			if ($mp->latitude !=''&&$mp->longitude!='')
				$code.="\nvar point = new GLatLng( $mp->latitude, $mp->longitude);";
			else
				$code.="\nvar point = new GLatLng( $mp->deflatitude, $mp->deflongitude);";
		}
		if (!empty($mp->centerlat)&&!empty($mp->centerlon))
			$code.="\nvar centerpoint = new GLatLng( $mp->centerlat, $mp->centerlon);";
		else
			$code.="\nvar centerpoint = point;";
	
		if ($this->_inline_coords == 0 && count($mp->kml)>0)
			$code.="map".$mp->mapnm.".setCenter(new GLatLng(0, 0), 0);
			";					
		else
			$code.="map".$mp->mapnm.".setCenter(centerpoint, ".$mp->zoom.");
			";					
			
		if ($mp->controltype=='user') {
			switch ($mp->zoomType) {
				case "Large":
					$code.="map".$mp->mapnm.".addControl(new GLargeMapControl());";
					break;
				case "Small":
					$code.="map".$mp->mapnm.".addControl(new GSmallMapControl());";
					break;
				case "3D-large":
					$code.="map".$mp->mapnm.".addControl(new GLargeMapControl3D());";
					if ($mp->rotation)
						$code.="map".$mp->mapnm.".enableRotation();";
					break;
				case "3D-largeSV":
					$code.="map".$mp->mapnm.".addControl(new StreetViewControl());";
					if ($mp->rotation)
						$code.="map".$mp->mapnm.".enableRotation();";
					break;
				case "3D-small":
					$code.="map".$mp->mapnm.".addControl(new GSmallZoomControl3D());";
					if ($mp->rotation)
						$code.="map".$mp->mapnm.".enableRotation();";
					break;
				default:
					break;
			}
			
			if($mp->showmaptype!='0')
			{
				$code.="map".$mp->mapnm.".addControl(new GMapTypeControl());";
			} 
	
			if ($mp->showscale==1)
				$code.="map".$mp->mapnm.".addControl(new GScaleControl());";
		} else {
			$code.="map".$mp->mapnm.".setUIToDefault();";
			if ($mp->rotation)
				$code.="map".$mp->mapnm.".enableRotation();";
		}
			
		if (count($mp->kml)>0) {
			if ($mp->kmlrenderer=="google") {
				$code .= "xml".$mp->mapnm." = [];";
				foreach ($mp->kml as $idx => $val) {
					$code .= "var kmlurl = '".$mp->kml[$idx]."';";
					$code .= "kmlurl = kmlurl.replace(/&amp;/g, String.fromCharCode(38));";
					$code .= "\nxml".$mp->mapnm."[".$idx."] = new GGeoXml(kmlurl);";
					$code .= "\nmap".$mp->mapnm.".addOverlay(xml".$mp->mapnm."[".$idx."]);";
				}
				if ($this->_inline_coords==0) {
					
					$code .= "\nGEvent.addListener(xml".$mp->mapnm."[0], 'load', function() {
								if (xml".$mp->mapnm."[0].loadedCorrectly()) {";
					$code .= "\nxml".$mp->mapnm."[0].gotoDefaultViewport(map".$mp->mapnm.");";
					if ($mp->corzoom!='0')
						$code .= "\nmap".$mp->mapnm.".setZoom(map".$mp->mapnm.".getZoom()+".$mp->corzoom.");";
					$code .= "\nsavePositionKML".$mp->mapnm."();"; 
					$code .= "\n}
							});";
				}
				if (count($mp->kmlsb)!=0) {
					$mp->kmlrenderer = 'geoxml';
					$mp->kml=$mp->kmlsb;
				}
			}
			
			if ($mp->kmlrenderer=="arcgis") {
				$code .= "var xml = [];";
				foreach ($mp->kml as $idx => $val) {
					$code .= "var kmlurl = '".$mp->kml[$idx]."';";
					$code .= "\nkmlurl = kmlurl.replace(/&amp;/g, String.fromCharCode(38));";
					$code .= "\nxml[".$idx."] = new esri.arcgis.gmaps.DynamicMapServiceLayer(kmlurl, null, 0.75, dynmapcallback".$mp->mapnm.");";
				}
			}
			
			if ($mp->kmlrenderer=="geoxml") {
				$code .= "\nvar kml".$mp->mapnm." = [];";
				foreach ($mp->kml as $idx => $val) {
					$code .= "\nvar kmlurl = '".$mp->kml[$idx]."';";
					$code .= "\nkmlurl = escape(kmlurl.replace(/&amp;/g, String.fromCharCode(38)));";
					$code .= "\nkml".$mp->mapnm.".push(kmlurl);";
				}
				$xmloptions = array();
				if ($mp->kmlsidebar=="left"||$mp->kmlsidebar=="right") {
					$xmloptions[] = "sidebarid: 'kmlsidebar".$mp->mapnm."'";
				} else {
					if ($mp->kmlsidebar!="none")
						$xmloptions[] = "sidebarid: '".$mp->kmlsidebar."'";
				}
				if ($mp->kmlmessshow=='1')
					$xmloptions[] = "messshow: true";
				
				if ($this->_inline_coords==1)
					$xmloptions[] = "nozoom: true";
	
				if ($mp->dir!='0')
					$xmloptions[] = "directions: true";
					
				if ($mp->kmlfoldersopen!='0')
					$xmloptions[] = "allfoldersopen: true";
					
				if ($mp->kmlhide!='0')
					$xmloptions[] = "hideall: true";

				if ($mp->kmlopenmethod!='0')
					$xmloptions[] = "iwmethod: '".$mp->kmlopenmethod."'";
				
				if ($mp->kmlsbsort=='asc') {
					$xmloptions[] = "sortbyname: 'asc'";
				}elseif ($mp->kmlsbsort=='desc') {
					$xmloptions[] = "sortbyname: 'desc'";
				} else 	
					$xmloptions[] = "sortbyname: 'none'";
	
				if ($mp->kmlclickablemarkers!='1')
					$xmloptions[] = "clickablemarkers: false";

				if ($mp->kmlopendivmarkers!='')
					$xmloptions[] = "opendivmarkers: '".$mp->kmlopendivmarkers."'";

				if ($mp->kmlcontentlinkmarkers!='0')
					$xmloptions[] = "contentlinkmarkers: true";

				if ($mp->kmllinkablemarkers!='0')
					$xmloptions[] = "linkablemarkers: true";

				if ($mp->kmllinktarget!='')
					$xmloptions[] = "linktarget: '".$mp->kmllinktarget."'";

				if ($mp->kmllinkmethod!='')
					$xmloptions[] = "linkmethod: '".$mp->kmllinkmethod."'";

				if (($mp->kmlpolylabel!=""&&$mp->kmlpolylabelclass!="")) {
					$xmloptions[] = "polylabelopacity: '".$mp->kmlpolylabel."'";
					$xmloptions[] = "polylabelclass: '".$mp->kmlpolylabelclass."'";
				}
				if (($mp->kmlmarkerlabel!=""&&$mp->kmlmarkerlabelclass!="")) {
					$xmloptions[] = "pointlabelopacity: '".$mp->kmlmarkerlabel."'";
					$xmloptions[] = "pointlabelclass: '".$mp->kmlmarkerlabelclass."'";
				}
				if ($mp->icon!='')
					$xmloptions[] ="baseicon : markericon".$mp->mapnm;
	
				if ($mp->maxcluster!=''&&$mp->gridsize!='') {
					$clusteroptions = array();
					if ($mp->maxcluster!='')
						$clusteroptions[] ="maxVisibleMarkers : ".$mp->maxcluster;
					if ($mp->gridsize!='')
						$clusteroptions[] ="gridSize : ".$mp->gridsize;
					if ($mp->minmarkerscluster!='')
						$clusteroptions[] ="minMarkersPerCluster : ".$mp->minmarkerscluster;
					if ($mp->maxlinesinfocluster!='')
						$clusteroptions[] ="maxLinesPerInfoBox : ".$mp->maxlinesinfocluster;
					if ($mp->clusterinfowindow!='')
						$clusteroptions[] ="ClusterInfoWindow : '".$mp->clusterinfowindow."'" ;
					if ($mp->clusterzoom!='')
						$clusteroptions[] ="ClusterZoom : '".$mp->clusterzoom."'" ;
					if ($mp->clustermarkerzoom!='')
						$clusteroptions[] ="ClusterMarkerZoom : ".$mp->clustermarkerzoom;
					if ($mp->icon!='')
						$clusteroptions[] ="Icon : markericon".$mp->mapnm;
	
					$xmloptions[] = "clustering : {".implode(",",$clusteroptions)."}";
				}
				
				$xmloptions[] = "titlestyle: ' '";
					
				$code .= "\nexml".$mp->mapnm." = new GeoXml(\"exml".$mp->mapnm."\", map".$mp->mapnm.", kml".$mp->mapnm.", {".implode(",",$xmloptions)."});";
				$code .= "\nexml".$mp->mapnm.".parse(); ";
				if ($this->_inline_coords==0&&$mp->corzoom!='0')
					$code .= "\nsetTimeout('map".$mp->mapnm.".setZoom(map".$mp->mapnm.".getZoom()+".$mp->corzoom.")', 750);";
			}
		}
	
		if ($mp->traffic=='1') {
			$code .= "\ntrafficInfo".$mp->mapnm." = new GTrafficOverlay();";
			$code .= "\nmap".$mp->mapnm.".addOverlay(trafficInfo".$mp->mapnm.");";
		}
	
		if ($mp->panoramio!="none") {
			$code .= "\nmap".$mp->mapnm.".addOverlay(new GLayer('com.panoramio.".$mp->panoramio."'));";
		}
		if ($mp->panotype!="none") {
			$code .= "\n  var options = {
							order: '".$mp->panoorder."',
							set: '".$mp->panotype."', 
							to: '".$mp->panomax."' };
						panoLayer".$mp->mapnm." = new PanoramioLayer(map".$mp->mapnm.", options);
						panoLayer".$mp->mapnm.".enable();";
		}
		
		if ($mp->youtube!="none") {
			$code .= "\nmap".$mp->mapnm.".addOverlay(new GLayer('com.youtube.".$mp->youtube."'));";
		}
	
		if ($mp->wiki!="none") {
			$code .= "\nmap".$mp->mapnm.".addOverlay(new GLayer('org.wikipedia.".$mp->wiki."'));";
		}
		
		if (count($mp->layer)>0) {
			foreach ($mp->layer as $lay) {
				$code .= "\nmap".$mp->mapnm.".addOverlay(new GLayer('".$lay."'));";
			}
		}
		
		if ($mp->localsearch=='1') {
			$code .= "localsearch".$mp->mapnm." = new google.maps.LocalSearch(".((!empty($searchoptions))?"{ ".$searchoptions." }":"").");";
			$code .= "map".$mp->mapnm.".addControl(localsearch".$mp->mapnm.", new GControlPosition(G_ANCHOR_BOTTOM_RIGHT, new GSize(10,20)));";
			if (!empty($mp->searchtext))
				$code .= "localsearch".$mp->mapnm.".execute('".$mp->searchtext."');";
		}
		
		if ($mp->googlebar=='1') {
			$code .= "map".$mp->mapnm.".enableGoogleBar();";
		}
	
		if ($mp->adsmanager=='1') {
			$code .= "adsmanager".$mp->mapnm." = new GAdsManager(map".$mp->mapnm.", ".((!empty($mp->adsense))?"'".$mp->adsense."'":"''").", { style: 'adunit', maxAdsOnMap: ".$mp->maxads.((!empty($mp->searchtext))?", keywords: '".$mp->searchtext."'":"").((!empty($mp->channel)&&!empty($mp->adsense))?", channel: '".$mp->channel."'":"").(($mp->localsearch=='1')?", position: new GControlPosition(G_ANCHOR_BOTTOM_LEFT, new GSize(20,20))":"")."}); ";
			$code .= "adsmanager".$mp->mapnm.".enable();";
		}
	
		if ($this->debug_plugin=="1")
			$code.="map".$mp->mapnm.".addControl(new VersionControl());";
	
		if (((!empty($mp->tolat)&&!empty($mp->tolon))||!empty($mp->toaddress))&&$mp->animdir=='0'&&$mp->formaddress!='1') {
			// Route
			$xmloptions = array();
			if ($mp->dirtype=='W')
				$xmloptions[] = "travelMode : G_TRAVEL_MODE_WALKING";
			else
				$xmloptions[] = "travelMode : G_TRAVEL_MODE_DRIVING";
			
			if ($mp->avoidhighways=='1')
				$xmloptions[] = "avoidHighways : true";
			else
				$xmloptions[] = "avoidHighways : false";
			
			$code .= "var dirsidebar".$mp->mapnm." = document.getElementById('dirsidebar".$mp->mapnm."');";
			$code .= "if (directions".$mp->mapnm.") {
							directions".$mp->mapnm.".clear();
							if ( dirsidebar".$mp->mapnm.".hasChildNodes() )
							{
								while ( dirsidebar".$mp->mapnm.".childNodes.length >= 1 )
								{
									dirsidebar".$mp->mapnm.".removeChild( dirsidebar".$mp->mapnm.".firstChild );
								} 
							}
					} else {
							directions".$mp->mapnm." = new GDirections(map".$mp->mapnm.", dirsidebar".$mp->mapnm.");
							GEvent.addListener(directions".$mp->mapnm.", 'error', handleErrors".$mp->mapnm.");
						}
				";
				
			if (is_array($mp->waypoints)&&count($mp->waypoints)>0) {
				if ($mp->address!="")
					array_unshift($mp->waypoints, $mp->address);
				else if ($lat !=""&&$lon!="")
					array_unshift($mp->waypoints, $lat.", ".$lon);
				
				if ($mp->toaddress!="")
					array_push($mp->waypoints, $mp->toaddress);
				else if ($mp->tolat!=""&&$mp->tolon!="")
					array_push($mp->waypoints, $mp->tolat.", ".$mp->tolon);
				
				$wpstring="";
				foreach ($mp->waypoints as $wp) {
					if ($wpstring!="")
						$wpstring.= ", ";
					$wpstring .= "'".$wp."'";
				}
				$code.="\ndirections".$mp->mapnm.".loadFromWaypoints([".$wpstring."], {".implode(",",$xmloptions)."});";
			} else
				$code.="\ndirections".$mp->mapnm.".load('from: ".(($mp->address!="")?$mp->address:(($mp->latitude!='')?$mp->latitude:$mp->deflatitude).", ".(($mp->longitude!='')?$mp->longitude:$mp->deflongitude))." to: ".(($mp->toaddress!="")?$mp->toaddress:$mp->tolat.", ".$mp->tolon)."', {".implode(",",$xmloptions)."});";
		}
		
		switch (strtolower($mp->mapType)) {
		case "satellite":
			$code.="\nmap".$mp->mapnm.".setMapType(G_SATELLITE_MAP);";
			break;
		
		case "hybrid":
			$code.="\nmap".$mp->mapnm.".setMapType(G_HYBRID_MAP);";
			break;
	
		case "terrain":
			$code.="\nmap".$mp->mapnm.".setMapType(G_PHYSICAL_MAP);";
			break;
	
		case "earth":
			$code.="\nmap".$mp->mapnm.".setMapType(G_SATELLITE_3D_MAP);";
			$code.="\nmap".$mp->mapnm.".getEarthInstance(initearth".$mp->mapnm.");";
			break;
		
		default:
			$code.="\nmap".$mp->mapnm.".setMapType(G_NORMAL_MAP);";
			break;
		}
		
		$code .="\nvar mt = map".$mp->mapnm.".getMapTypes();
		for (var i=0; i<mt.length; i++) {
			mt[i].getMinimumResolution = function() {return ".$mp->minzoom.";};
			mt[i].getMaximumResolution = function() {return ".$mp->maxzoom.";};
		}";
	
		if($mp->zoomnew=='1'&&$mp->controltype=='user')
		{
			$code.="
			map".$mp->mapnm.".enableContinuousZoom();
			map".$mp->mapnm.".enableDoubleClickZoom();
			";
		} else {
			$code.="
			map".$mp->mapnm.".disableContinuousZoom();
			map".$mp->mapnm.".disableDoubleClickZoom();
			";
		}
	
		if($mp->zoomwheel=='1'&&$mp->controltype=='user')
		{
			$code.="map".$mp->mapnm.".enableScrollWheelZoom();
			";
		} 
	
		if (($this->_inline_coords == 0 && count($mp->kml)==0) // No inline coordinates and no kml => standard configuration
			||($mp->latitude !=''&&$mp->longitude!=''&&!($this->_geocoded==1&&$mp->toaddress!=''&&$mp->description==''))) { // Inline coordinates and text is not empty
			$options = '';
			
			if ($mp->tooltip!='') 
				$options .= (($options!='')?', ':'')."title:\"".$mp->tooltip."\"";
			if ($mp->icon!='')
				$options .= (($options!='')?', ':'')."icon:markericon".$mp->mapnm;
			
			$code.="var marker".$mp->mapnm." = new GMarker(point".(($options!='')?', {'.$options.'}':'').");";
			
			$code.="map".$mp->mapnm.".addOverlay(marker".$mp->mapnm.");
			";
	
			if ($mp->description!=''||$mp->dir!='0') {
				// convert $mp->description to maybe tabs?
				// Check <tab> tag
				$reg='/(<tab\s*?(title=\'(.*?)\')?>)(.*?)(<\/tab>)/si';
				$c=preg_match_all($reg,$mp->description,$m);
	
				// if <tab> then make array of $mp->description
				if ($c>0) {
					$mp->description= array();
					for ($z=0;$z<$c;$z++) {
						// transform attribute title to title of tab
						$mp->description[$z]->title = htmlspecialchars_decode($m[3][$z], ENT_NOQUOTES);
						$mp->description[$z]->text = htmlspecialchars_decode($m[4][$z], ENT_NOQUOTES);
					}
				}
				if ($mp->dir!='0') {
					$dirform="<form id='directionform".$mp->mapnm."' action='".$this->protocol.$this->googlewebsite."/maps' method='get' target='_blank' onsubmit='DirectionMarkersubmit".$mp->mapnm."(this);return false;' class='mapdirform'>";
					
					$dirform.=$mp->txtdir."<input ".(($mp->txtto=='')?"type='hidden' ":"type='radio' ")." ".(($mp->dirdefault=='0')?"checked='checked'":"")." name='dir' value='to'>".(($mp->txtto!='')?$mp->txtto."&nbsp;":"")."<input ".(($mp->txtfrom=='')?"type='hidden' ":"type='radio' ").(($mp->dirdefault=='1')?"checked='checked'":"")." name='dir' value='from'>".(($mp->txtfrom!='')?$mp->txtfrom:"");
					$dirform.="<br />".$mp->txtdiraddr."<input type='text' class='inputbox' size='20' name='saddr' id='saddr' value='' /><br />";
	
					if ($mp->txt_driving!=''||$mp->dirtype=="D")
	
						$dirform.="<input ".(($mp->txt_driving=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg' value='' ".(($mp->dirtype=="D")?"checked='checked'":"")." />".$mp->txt_driving.(($mp->txt_driving!='')?"&nbsp;":"");
					if ($mp->txt_avhighways!=''||$mp->dirtype=="1")
						$dirform.="<input ".(($mp->txt_avhighways=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg' value='h' ".(($mp->avoidhighways=='1')?"checked='checked'":"")." />".$mp->txt_avhighways.(($mp->txt_avhighways!='')?"&nbsp;":"");
					if ($mp->txt_walking!=''||$mp->dirtype=="W")
						$dirform.="<input ".(($mp->txt_walking=='')?"type='hidden' ":"type='radio' ")."class='radio' name='dirflg' value='w' ".(($mp->dirtype=="W")?"checked='checked'":"")." />".$mp->txt_walking.(($mp->txt_walking!='')?"&nbsp;":"");
					if ($mp->txt_driving!=''||$mp->txt_avhighways!=''||$mp->txt_walking!='')
						$dirform.="<br />";	
					$dirform.="<input value='".$mp->txtgetdir."' class='button' type='submit' style='margin-top: 2px;'>";
					
					if ($mp->dir=='2')
						$dirform.= "<input type='hidden' name='pw' value='2'/>";
	
					if ($this->_lang!='') 
						$dirform.= "<input type='hidden' name='hl' value='".$this->_lang."'/>";
	
					if (!empty($mp->address))
						$dirform.="<input type='hidden' name='daddr' value='".$mp->address." (".(($mp->latitude!='')?$mp->latitude:$mp->deflatitude).", ".(($mp->longitude!='')?$mp->longitude:$mp->deflongitude).")'/></form>";
					else
						$dirform.="<input type='hidden' name='daddr' value='".(($mp->latitude!='')?$mp->latitude:$mp->deflatitude).", ".(($mp->longitude!='')?$mp->longitude:$mp->deflongitude)."'/></form>";
					
					// Add form before div or at the end of the html.
					if (is_array($mp->description)) {
						$mp->description[$z+1]->title = $mp->txtdir;
						$mp->description[$z+1]->text = htmlspecialchars_decode($dirform, ENT_NOQUOTES);
					} else {
						$pat="/&lt;\/div&gt;$/";
						if (preg_match($pat, $mp->description))
							$mp->description = preg_replace($pat, $dirform."</div>", $mp->description);
						else {
							$pat="/<\/div>$/";
							if (preg_match($pat, $mp->description))
								$mp->description = preg_replace($pat, $dirform."</div>", $mp->description);
							else
								$mp->description.=$dirform;
						}
					}
				}
				
				if (!is_array($mp->description))
					$mp->description = htmlspecialchars_decode($mp->description, ENT_NOQUOTES);
	
				// If marker 
				if ($mp->marker==1) {
					if (is_array($mp->description)) {
						$code .= "marker".$mp->mapnm.".openInfoWindowTabsHtml([";
						$first = true;
						foreach ($mp->description as $tab) {
							if ($first) 
								$first = false;
							else 
								$code.=",  ";
								
							$code.= "new GInfoWindowTab(\"".$tab->title."\", \"".$tab->text."\")";
						}
						
						$code .= "]);";  
						
					} else
						$code.="marker".$mp->mapnm.".openInfoWindowHtml(\"".$mp->description."\");"; 
				}
				
				$code.="GEvent.addListener(marker".$mp->mapnm.", 'click', function() {
						marker".$mp->mapnm;
				if (is_array($mp->description)) {
					$code .=".openInfoWindowTabsHtml([";
					$first = true;
					foreach ($mp->description as $tab) {
						if ($first) 
							$first = false;
						else 
							$code.=",  ";
							
						$code.= "new GInfoWindowTab(\"".$tab->title."\", \"".$tab->text."\")";
					}
					
					$code .= "]);";  
					
				} else
					$code.=".openInfoWindowHtml(\"".$mp->description."\");";
					
				$code.="});
				";
			}
		}
		
		if ($mp->imageurl!='') {
			$code .= "imageovl".$mp->mapnm." = new GScreenOverlay('$mp->imageurl',
									new GScreenPoint($mp->imagex, $mp->imagey, '$mp->imagexyunits', '$mp->imagexyunits'),  // screenXY
									new GScreenPoint($mp->imageanchorx, $mp->imageanchory, '$mp->imageanchorunits', '$mp->imageanchorunits'),  // overlayXY
									new GScreenSize($mp->imagewidth, $mp->imageheight)  // size on screen
								);
						map".$mp->mapnm.".addOverlay(imageovl".$mp->mapnm.");
				";
		}
		if ($mp->animdir=='0'&&($mp->sv=='top'||$mp->sv=='bottom'||($mp->sv!='none'&&$mp->sv!='top'&&$mp->sv!='bottom'))) {
			if ($mp->sv!='none'&&$mp->sv!='top'&&$mp->sv!='bottom')
				$code.="\nvar panobj = document.getElementById('".$mp->sv."');
						";
			else
				$code.="\nvar panobj = document.getElementById('svpanorama".$mp->mapnm."');
						";
			$mp->svopt = "";
			if ($mp->svyaw!='0')
				$mp->svopt .= "yaw:".$mp->svyaw;
			if ($mp->svpitch!='0')
				$mp->svopt .= (($mp->svopt=="")?"":", ")."pitch:".$mp->svpitch;
			if ($mp->svzoom!='')
				$mp->svopt .= (($mp->svopt=="")?"":", ")."zoom:".$mp->svzoom;
				
			$code.="\nsvpanorama".$mp->mapnm." = new GStreetviewPanorama(panobj);
					svlastpoint".$mp->mapnm." = map".$mp->mapnm.".getCenter();
					svpanorama".$mp->mapnm.".setLocationAndPOV(svlastpoint".$mp->mapnm.", ".(($mp->svopt!='')?"{".$mp->svopt."}":'null').");
					svmarker".$mp->mapnm." = new GMarker(svlastpoint".$mp->mapnm.", {icon: guyIcon".$mp->mapnm." , draggable: true});
					map".$mp->mapnm.".addOverlay(svmarker".$mp->mapnm.");
					GEvent.addListener(svmarker".$mp->mapnm.", 'dragend', onDragEnd".$mp->mapnm.");
					GEvent.addListener(svpanorama".$mp->mapnm.", 'initialized', onNewLocation".$mp->mapnm.");
					GEvent.addListener(svpanorama".$mp->mapnm.", 'yawchanged', onYawChange".$mp->mapnm."); 
					";
		}
	
		if ($mp->animdir!="0") {
			$xmloptions = array();
			$xmloptions[] = "preserveViewport: false";
			$xmloptions[] = "getSteps: true";
			
			if ($mp->dirtype=='W')
				$xmloptions[] = "travelMode : G_TRAVEL_MODE_WALKING";
			else
				$xmloptions[] = "travelMode : G_TRAVEL_MODE_DRIVING";
			
			if ($mp->avoidhighways=='1')
				$xmloptions[] = "avoidHighways : true";
			else
				$xmloptions[] = "avoidHighways : false";
				
			$opts = array();
			if ($mp->animspeed!=1)
				$opts[] = "Speed : ".$mp->animspeed;
			if ($mp->animautostart!=0)
				$opts[] = "AutoStart : true";
			if ($mp->animunit!='')
				$opts[] = "Unit : '".$mp->animunit."'";
	//					$opts[] = "zoomlevel : ".$mp->zoom;
			if ($mp->dirtype=='W')
				$opts[] = "travelMode : G_TRAVEL_MODE_WALKING";
			else
				$opts[] = "travelMode : G_TRAVEL_MODE_DRIVING";
			
			if ($mp->avoidhighways=='1')
				$opts[] = "avoidHighways : true";
			else
				$opts[] = "avoidHighways : false";
	
			$code.="\nvar panobj = document.getElementById('svpanorama".$mp->mapnm."');
					svpanorama".$mp->mapnm." = new GStreetviewPanorama(panobj);
					directions".$mp->mapnm." = new GDirections(map".$mp->mapnm.");
					";
	
			$this->_lang = "";
			foreach ($this->_langanim as $al) {
				$this->_lang.=(($this->_lang=='')?"":",")."'".$al."'";
			}
			
			$code.="\nopts = {".implode(",",$opts)."};
					lang = [".$this->_lang."];
					";
			$code .="\nroute".$mp->mapnm." = new Directionsobj('route".$mp->mapnm."', map".$mp->mapnm.", '".$mp->mapnm."', svpanorama".$mp->mapnm.", svclient".$mp->mapnm.", directions".$mp->mapnm.", centerpoint, opts, lang);";
			
			if (is_array($mp->waypoints)&&count($mp->waypoints)>0) {
				if ($mp->address!="")
					array_unshift($mp->waypoints, $mp->address);
				if ($mp->toaddress!="")

					array_push($mp->waypoints, $mp->toaddress);
				$wpstring="";
				foreach ($mp->waypoints as $wp) {
					if ($wpstring!="")
						$wpstring.= ", ";
					$wpstring .= "'".$wp."'";
				}
				$code.="\ndirections".$mp->mapnm.".loadFromWaypoints([".$wpstring."], {".implode(",",$xmloptions)."});";
			} else
				$code.="\ndirections".$mp->mapnm.".load('from: ".$mp->address." to: ".$mp->toaddress."', {".implode(",",$xmloptions)."});";
		}
		
		if ($mp->tilelayer!="") {
			$mp->tilebounds=explode(",", $mp->tilebounds);
			if (count($mp->tilebounds)==4) {
				$code .="\ntilelayer".$mp->mapnm." = new GTileLayer(GCopyrightCollection(''), ".$mp->tileminzoom.", ".$mp->tilemaxzoom.");";
				
				if ($mp->tilemethod=='maptiler') {
					$code .="\nmercator".$mp->mapnm." = new GMercatorProjection(".($mp->tilemaxzoom+1).");
					tilelayer".$mp->mapnm.".getTileUrl = function(tile,zoom) {
						if ((zoom < ".$mp->tileminzoom.") || (zoom > ".$mp->tilemaxzoom.")) {
							return '".$mp->tilelayer."/none.png';
						} 
						var ymax = 1 << zoom;
						var y = ymax - tile.y -1;
						var tileBounds = new GLatLngBounds(
							mercator".$mp->mapnm.".fromPixelToLatLng( new GPoint( (tile.x)*256, (tile.y+1)*256 ) , zoom ),
							mercator".$mp->mapnm.".fromPixelToLatLng( new GPoint( (tile.x+1)*256, (tile.y)*256 ) , zoom )
						);
						if (tileBounds".$mp->mapnm.".intersects(tileBounds)) {
							return '".$mp->tilelayer."/'+zoom+'/'+tile.x+'/'+y+'.png';
						} else {
							return '".$mp->tilelayer."/none.png';
						}
					}
					tilelayer".$mp->mapnm.".isPng = function() { return false;};
					tilelayer".$mp->mapnm.".getOpacity = function() { return ".$mp->tileopacity."; }
					tileBounds".$mp->mapnm." = new GLatLngBounds(new GLatLng(".$mp->tilebounds[0].", ".$mp->tilebounds[1]."), new GLatLng(".$mp->tilebounds[2].", ".$mp->tilebounds[3]."));";
				} else						
					$code .="\ntilelayer".$mp->mapnm.".tileUrlTemplate = '".$mp->tilelayer."/{Z}/{X}/{Y}.png';";
	;
				
				$code .="\nvar overlay".$mp->mapnm." = new GTileLayerOverlay( tilelayer".$mp->mapnm.", {zPriority:0 } );
				map".$mp->mapnm.".addOverlay(overlay".$mp->mapnm.");";
				$code.="\nGEvent.addListener(map".$mp->mapnm.", 'maptypechanged', function() {
							if (map".$mp->mapnm.".getCurrentMapType() == G_SATELLITE_3D_MAP)
								map".$mp->mapnm.".getEarthInstance(initearth".$mp->mapnm.");
						 });
						";
			}
		}
		
		if($mp->zoomwheel=='1')
		{
			$code.="GEvent.addDomListener(tst".$mp->mapnm.", 'DOMMouseScroll', CancelEvent".$mp->mapnm.");
					GEvent.addDomListener(tst".$mp->mapnm.", 'mousewheel', CancelEvent".$mp->mapnm.");
				";
		}
	
		/* remove link in google logo. Do not use
		$code.= "\nvar func".$mp->mapnm." = function () {";
		$code.= "\n	var test_div = document.getElementById('googlemap".$mp->mapnm."');";
		$code.= "\n	var test_obj = test_div.childNodes[1];";
		$code.= "\n	test_obj = test_obj.getElementsByTagName('a');";
		$code.= "\n	if (test_obj&&test_obj.length>0)";
		$code.= "\n		test_obj[0].href = '".$this->protocol.$this->googlewebsite."';";
		$code.= "\n};";
		$code.= "\nsetTimeout(func".$mp->mapnm.", 1500);";
		*/
		
		/* remove copyright, terms and mapdata. Do not use 					
		$code.= "test_div = document.getElementById('googlemap".$mp->mapnm."');";
		$code.= "test_obj = test_div.childNodes[1].style.display='none';";
		$code.= "test_obj = test_div.childNodes[2].style.display='none';";
		*/
	
		if($this->_client_geo == 1) {
			if ($this->clientgeotype=="local")
				$code.="	});
					localSearch.execute(address);";
			else
				$code.="		       
							  });";
		}
	
		// End of script voor showing the map 
		if ($mp->show!=0)
			$code.="\n	}";
			
		$code.="\n}
		//]]></script>
		";
		
		// Call the Maps through timeout to render in IE also
		// Set an event for watching the changing of the map so it can refresh itself
		$code.= "<script type=\"text/javascript\">//<![CDATA[
				if (GBrowserIsCompatible()) {
					obj = document.getElementById('mapbody".$mp->mapnm."');
					obj.style.display = 'block';
					window.onunload=function(){window.onunload;GUnload()};
					tst".$mp->mapnm.".setAttribute(\"oldValue\",0);
					tst".$mp->mapnm.".setAttribute(\"refreshMap\",0);
					";
		
		if ($this->loadmootools=='1') {
		$code.= "if (window.MooTools==null)
					tstint".$mp->mapnm."=setInterval(\"checkMap".$mp->mapnm."()\",".$this->timeinterval.");
				else
					window.addEvent('domready', function() {
							tstint".$mp->mapnm."=setInterval('checkMap".$mp->mapnm."()', ".$this->timeinterval.");
						});
				";
		} else {
			$code.= "tstint".$mp->mapnm."=setInterval(\"checkMap".$mp->mapnm."()\",".$this->timeinterval.");
					";
		}
		
		$code.= "}
		//]]></script>
		";
	
		// Clean up variables except generated code and memory variables
		unset($fields, $value, $values, $coord, $tocoord, $client_togeo, $searchoption, $lboptions, $url, $la, $cam, $replace, $addr, $idx, $val, $xmloptions, $clusteroptions, $wpstring, $wp, $options, $reg, $c, $z, $dirform, $first, $opts, $al);
		
		return array($code, $lbcode);
	}
	
	function _getInitialParams() {
		jimport( 'joomla.utilities.simplexml' );
		$xml	= new JSimpleXML;
		if (substr($this->jversion,0,3)=="1.5")
			$filename = JPATH_SITE.DS."/plugins/system/plugin_googlemap2.xml";
		else
			$filename = JPATH_SITE.DS."/plugins/system/plugin_googlemap2/plugin_googlemap2.xml";
		
		if ($xml->loadFile($filename)) {
			if (substr($this->jversion,0,3)=="1.5")
				$root =& $xml->document;
			else
				$root = $xml->document->config[0]->fields[0];			
			foreach ($root->children() as $params) {
				foreach($params->children() as $param) {
					if ($param->attributes('export')=='1') {
						$name = $param->attributes('name');
						if ($name=='lat') {
							$this->initparams->deflatitude = $this->params->get($name, $param->attributes('default'));
						} elseif ($name=='lon') {
							$this->initparams->deflongitude = $this->params->get($name, $param->attributes('default'));
						} else {
							$nm = strtolower($name);
							$this->initparams->$nm = $this->params->get($name, $param->attributes('default'));
						}
					}
				}
			}
		}
		
		// Clean up generated variables
		unset($filename, $xml, $root, $params, $param, $name, $nm);
	}
	
	function _getURL($url) {
		$ok = false;
		$getpage = "";
		if (ini_get('allow_url_fopen')) { 
			if (file_exists($url)) {
				$getpage = file_get_contents($url);
				$ok = true;
			}
		} 
		
		if (!$ok) { 
			$this->_debug_log("URL couldn't be opened probably ALLOW_URL_FOPEN off");
			if (function_exists('curl_init')) {
				$this->_debug_log("curl_init does exists");
				$ch = curl_init();
				$timeout = 5; // set to zero for no timeout
				curl_setopt ($ch, CURLOPT_URL, $url);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				$getpage = curl_exec($ch);
				curl_close($ch);
			} else
				$this->_debug_log("curl_init doesn't exists");
		}
		$this->_debug_log("Returned page: ".htmlentities($getpage));
		
		// Clean up generated variables
		unset($ok, $ch, $timeout);
		
		return $getpage;
	}

	function get_geo($address)
	{
		$this->_debug_log("get_geo(".$address.")");
	
		$coords = '';
		$getpage='';
		$replace = array("\n", "\r", "&lt;br/&gt;", "&lt;br /&gt;", "&lt;br&gt;", "<br>", "<br />", "<br/>");
		$address = str_replace($replace, '', $address);

		// Convert address to utf-8 encoding
		if (function_exists('mb_detect_encoding')) {
			$enc = mb_detect_encoding($address);
			if (!empty($enc))
				$address = mb_convert_encoding($address, "utf-8", $enc);
			else
				$address = mb_convert_encoding($address, "utf-8");
		}

		$this->_debug_log("Address: ".$address);
		
		$uri = $this->protocol.$this->googlewebsite."/maps/geo?q=".urlencode($address)."&output=xml&key=".$this->googlekey;
		$this->_debug_log("get_geo(".$uri.")");
		$getpage = $this->_getURL($uri);

		if (function_exists('mb_detect_encoding')) {
			$enc = mb_detect_encoding($getpage);
			if (!empty($enc))
				$getpage = mb_convert_encoding($getpage, "utf-8", $enc);
		}

		if ($getpage <>'') {
			$expr = '/xmlns/';
			$getpage = preg_replace($expr, 'id', $getpage);
			$xml = new SimpleXMLElement($getpage);
			foreach($xml->xpath('//coordinates') as $coordinates) {
				$coords = $coordinates;
				break;
			}
			if ($coords=='') {
				$this->_debug_log("Coordinates: null");
			} else
				$this->_debug_log("Coordinates: ".join(", ", explode(",", $coords)));
		} else
			$this->_debug_log("get_geo totally wrong end!");
	
		// Clean up variables
		unset($coord, $getpage, $replace, $enc, $uri, $ok, $ch, $timeout, $expr, $xml, $coordinates);
		
		return $coords;
	}
	
	function _debug_log($text)
	{
		if ($this->debug_plugin =='1')
			$this->debug_text .= "\n// ".$text." (".round($this->_memory_get_usage()/1024)." KB)";
	
		return;
	}
	
	function _get_index($string)
	{
		if ($this->brackets=='{') {
			$string = preg_replace("/^(.*?)\[/", '', $string);
			$string = preg_replace("/\](.*?)$/", '', $string);
			
		} else {
			$string = preg_replace("/^.*\(/", '', $string);
			$string = preg_replace("/\).*$/", '', $string);
		}		
		return $string;
	}
	
    function _memory_get_usage()
    {
		if ( function_exists( 'memory_get_usage' ) )
			return memory_get_usage(); 
		else
			return 0;
    }

	function _get_API_key () {
		$url = trim($this->urlsetting);
		$replace = array('http://', 'https://');
		$url = str_replace($replace, '', $url);
		$url = (($this->protocol=='https://')?$this->protocol:'').$url;
		$this->_debug_log("url: ".$url);
		$key = '';
		$multikey = trim($this->params->get( 'Google_Multi_API_key', '' ));
		if ($multikey!='') {
			$this->_debug_log("multikey: ".$multikey);
			$replace = array("\n", "\r", "<br/>", "<br />", "<br>");
			$sites = preg_split("/[\n\r]+/", $multikey);
			foreach($sites as $site)
			{
				$values = explode(";",$site, 2);
				if (count($values)>1) {
					$values[0] = trim(str_replace($replace, '', $values[0]));
					$values[1] = str_replace($replace, '', $values[1]);
					$this->_debug_log("values[0]: ".$values[0]);
					$this->_debug_log("values[1]: ".$values[1]);
					if ($url==$values[0])
					{
						$key = trim($values[1]);
						break;
					}
				}
			}
		}
		if ($key=='')
			$key = trim($this->params->get( 'Google_API_key', '' ));

		// Clean up variables
		unset($url, $replace, $multikey, $sites, $site, $values);
		$this->_debug_log("key: ".$key);
		return $key;
	}
	
	function _randomkeys($length)
	{
		$key = "";
		$pattern = "1234567890abcdefghijklmnopqrstuvwxyz";
		for($i=0;$i<$length;$i++)
		{
			$key .= $pattern{rand(0,35)};
		}
		
		// Clean up variables
		unset($i, $pattern);
		return $key;
	}

	function _translate($orgtext, $lang) {
		$langtexts = preg_split("/[\n\r]+/", $orgtext);
		$text = "";

		if (is_array($langtexts)) {
			$replace = array("\n", "\r", "<br/>", "<br />", "<br>");
			$firsttext = "";
			foreach($langtexts as $langtext)
			{
				$values = explode(";",$langtext, 2);
				if (count($values)>1) {
					$values[0] = trim(str_replace($replace, '', $values[0]));
					if ($firsttext == "")
						$firsttext = $values[1];
						
					if (trim($lang)==$values[0])
					{
						$text = $values[1];
						break;
					}
				}
			}
			// Not found
			if ($text=="")
				$text = $firsttext;
		}	
		
		if ($text=="")
			$text = $orgtext;
	
		$text = htmlspecialchars_decode($text, ENT_NOQUOTES);
	
		// Clean up variables
		unset($langtexts, $replace, $langtext, $values);
		return $text;
	}
	
	function _getlang() {
		$this->_debug_log("langtype: ".$this->langtype);

		if ($this->langtype == 'site') {
			$lang = $this->lang->getTag();
			$this->_debug_log("Joomla lang: ".$lang);
			// Chinese and portugal use full iso code to indicate language
			if (!($lang=='zh'||$lang=='pt')) {
				$locale_parts = explode('-', $this->lang->getTag());
				$lang = $locale_parts[0];
			}
			$this->_debug_log("site lang: ".$lang);
		} else if ($this->langtype == 'config') {
			$lang = $this->params->get( 'lang', '' );
			$this->_debug_log("config lang: ".$lang);
		} else if ($this->langtype == 'joomfish'&&isset($_COOKIE['jfcookie'])) {
			$lang = $_COOKIE['jfcookie']['lang']; 
			$this->_debug_log("Joomfish lang: ".$lang);
		} else {
			$lang = '';
			$this->_debug_log("No language: ".$lang);
		} 
		
		// Clean up variables
		unset($locale_parts);
		return $lang;
	}
	
	function _remove_html_tags($text) {
		$reg[] = "/<span[^>]*?>/si";
		$repl[] = '';
		$reg[] = "/<\/span>/si";
		$repl[] = '';
		$text = preg_replace( $reg, $repl, $text );
		
		// Clean up variables
		unset($reg, $repl);
		return $text;
	}
	
	function _addscript($url, &$text) {
		// The method depends on event type. onAfterRender is complex and others are simple based on framework
		if ($this->event!='onAfterRender')
			$this->document->addScript($url);
		else {
			// Get header
			$reg = "/(<HEAD[^>]*>)(.*?)(<\/HEAD>)(.*)/si";
			$count = preg_match_all($reg,$text,$html);	
			if ($count>0) {
				$head=$html[2][0];
			} else {
				$head='';
			}
			// clean browser if statements
			$reg = "/<!--\[if(.*?)<!\[endif\]-->/si";
			$head = preg_replace($reg, '', $head);

			// define scripts regex
			$reg = '/<script.*src=[\'\"](.*?)[\'\"][^>]*[^<]*(<\/script>)?/i';
			$found = false;
			
			$count = preg_match_all($reg,$head,$scripts,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);	

			if ($count>0)
				foreach ($scripts[1] as $script) {
					if ($script[0]==$url) {
						$found = true;
						break;
					}
				}
				
			if (!$found) {
				$script = "\n<script type='text/javascript' src='".$url."'></script>\n";
				if ($count==0) {
					// No scripts then just add it before </head>
					$text = preg_replace("/<head(| .*?)>(.*?)<\/head>/is", "<head$1>$2".$script."</head>", $text);
				} else {
					//add script after the last script
					// position last script and add length
					$pos = strpos($text, trim($scripts[0][$count-1][0]))+strlen(trim($scripts[0][$count-1][0]));
					$text = substr($text,0, $pos).$script.substr($text,$pos+1);
				}
			}
			
			// Clean up variables
			unset($reg, $count, $head, $found, $scripts, $script, $pos);
		}
	}
	
	function _addstylesheet($url, &$text) {
		// The method depends on event type. onAfterRender is complex and others are simple based on framework
		if ($this->event!='onAfterRender')
			$this->document->addStyleSheet($url);
		else {
			// Get header
			$reg = "/(<HEAD[^>]*>)(.*?)(<\/HEAD>)(.*)/si";
			$count = preg_match_all($reg,$text,$html);	
			if ($count>0) {
				$head=$html[2][0];
			} else {
				$head='';
			}
			
			// clean browser if statements
			$reg = "/<!--\[if(.*?)<!\[endif\]-->/si";
			$head = preg_replace($reg, '', $head);

			// define scripts regex
			$reg = '/<link.*href=[\'\"](.*?)[\'\"][^>]*[^<]*(<\/link>)?/i';
			$found = false;
			
			$count = preg_match_all($reg,$head,$styles,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);	
			if ($count>0)
				foreach ($styles[1] as $style) {
					if ($style[0]==$url) {
						$found = true;
						break;
					}
				}
				
			if (!$found) {
				$style = "\n<link href='".$url."' rel='stylesheet' type='text/css' />\n";
				if ($count==0) {
					// No styles then just add it before </head>
					$text = preg_replace("/<head(| .*?)>(.*?)<\/head>/is", "<head$1>$2".$style."</head>", $text);
				} else {
					//add style after the last style
					// position last style and add length
					$pos = strpos($text, trim($styles[0][$count-1][0]))+strlen(trim($styles[0][$count-1][0]));
					$text = substr($text,0, $pos).$style.substr($text,$pos+1);
				}
			}
			
			// Clean up variables
			unset($reg, $count, $head, $found, $styles, $style, $pos);
		}
	}
	function _addstyledeclaration($source, &$text) {
		// The method depends on event type. onAfterRender is complex and others are simple based on framework
		if ($this->event!='onAfterRender')
			$this->document->addStyleDeclaration($source);
		else {
			// Get header
			$reg = "/(<HEAD[^>]*>)(.*?)(<\/HEAD>)(.*)/si";
			$count = preg_match_all($reg,$text,$html);	
			if ($count>0) {
				$head=$html[2][0];
			} else {
				$head='';
			}
			
			// clean browser if statements
			$reg = "/<!--\[if(.*?)<!\[endif\]-->/si";
			$head = preg_replace($reg, '', $head);

			// define scripts regex
			$reg = '/<style[^>]*>(.*?)<\/style>/si';
			$found = false;
			
			$count = preg_match_all($reg,$head,$styles,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);	
			if ($count>0)
				foreach ($styles[1] as $style) {
					if ($style[0]==$source) {
						$found = true;
						break;
					}
				}
				
			if (!$found) {
				$source = "\n<style type='text/css'>\n".$source."\n</style>\n";
				if ($count==0) {
					// No styles then just add it before </head>
					$text = preg_replace("/<head(| .*?)>(.*?)<\/head>/is", "<head$1>$2".$source."</head>", $text);
				} else {
					//add style after the last style
					// position last style and add length
					$pos = strpos($text, trim($styles[0][$count-1][0]))+strlen(trim($styles[0][$count-1][0]));
					$text = substr($text,0, $pos).$source.substr($text,$pos+1);
				}
			}
			
			// Clean up variables
			unset($reg, $count, $head, $found, $styles, $style, $pos);
		}
	}
}

?>