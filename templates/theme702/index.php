<?php

/**

 * @version                $Id: index.php 20196 2011-01-09 02:40:25Z ian $

 * @package                Joomla.Site

 * @subpackage        tpl_beez2

 * @copyright        Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.

 * @license                GNU General Public License version 2 or later; see LICENSE.txt

 */



// No direct access.

defined('_JEXEC') or die;

$path = $this->baseurl.'/templates/'.$this->template;

error_reporting( E_ERROR );



// check modules

$showRightColumn        = ($this->countModules('position-3') or $this->countModules('position-6') or $this->countModules('position-8'));

$showbottom                        = ($this->countModules('position-9') or $this->countModules('position-10') or $this->countModules('position-11') or $this->countModules('user1'));

$showleft                        = ($this->countModules('position-4') or $this->countModules('position-7') or $this->countModules('position-5'));







JHTML::_('behavior.framework', true);



// get params

$color              = $this->params->get('templatecolor');

$logo               = $this->params->get('logo');

$navposition        = $this->params->get('navposition');

$app                = JFactory::getApplication();

$templateparams     = $app->getTemplate(true)->params;

$leftcolgrid		= $this->params->get('Leftcolgrid'); 

$rightcolgrid		= $this->params->get('Rightcolgrid'); 

?>

<!DOCTYPE HTML>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >

<?php

if ($this->countModules('left') == 0):?>

<?php $leftcolgrid	= "0";?>

<?php endif; ?>

<?php

if ($this->countModules('right') == 0):?>

<?php $rightcolgrid	= "0";?>

<?php endif; ?>

 <?php

  $itemid = JRequest::getVar('Itemid');

  $menu = &JSite::getMenu();

  $active = $menu->getItem($itemid);

  $params = $menu->getParams( $active->id );

  $pageclass = $params->get( 'pageclass_sfx' );

?> 

        <head>

        		<meta http-equiv="X-UA-Compatible" content="IE=8" />

                <jdoc:include type="head" />

                

                <link rel="stylesheet" href="<?php echo $path ?>/css/position.css" type="text/css" media="screen,projection" />

                <link rel="stylesheet" href="<?php echo $path ?>/css/layout.css" type="text/css" media="screen,projection" />

                <link rel="stylesheet" href="<?php echo $path ?>/css/print.css" type="text/css" media="Print" />

                <link rel="stylesheet" href="<?php echo $path ?>/css/personal.css" type="text/css" media="screen,projection" />

                 <link rel="stylesheet" href="<?php echo $path ?>/css/general.css" type="text/css" media="screen,projection" />
                 
                <!--<script language="JavaScript">
				function Abrir_ventana (pagina) {
				var opciones="toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=508, height=450, top=85, left=200";		
				window.open(pagina,"",opciones);
					}
				</script>-->

<?php

        $files = JHtml::_('stylesheet', 'templates/theme702/css/general.css',null,false,true);

        if ($files):

                if (!is_array($files)):

                        $files = array($files);

                endif;

                foreach($files as $file):

?>

                <link rel="stylesheet" href="<?php echo $file;?>" type="text/css" />

<?php

                 endforeach;

        endif;

?>



                <script type="text/javascript" src="<?php echo $path ?>/javascript/hide.js"></script>

                <script type="text/javascript" src="<?php echo $path ?>/javascript/jquery-1.4.2.min.js"></script>

               
                
                
         

                <script type="text/javascript">

					var $j = jQuery.noConflict();

					$j(document).ready(function(){

						$j('#header ul li.parent').hover(

							function() {

								$j(this).addClass("actives");

								$j(this).find('> ul').stop(false, true).fadeIn();

								$j(this).find('>ul ul').stop(false, true).fadeOut('fast');

								Cufon.refresh();

							},

							function() {

								$j(this).removeClass("actives");        

								$j(this).find('ul').stop(false, true).fadeOut('fast');

								Cufon.refresh();

							}

						);

						$j('.ulwrapper').hover(

							function() {

								$j('.parent').addClass("active_tab");

								Cufon.refresh();

							},

							function() {

								$j('.parent').removeClass("active_tab"); 

								Cufon.refresh();       

							}

						);

					});

					preloadImages([

				   '<?php echo $path ?>/images/subscribe_button_h.gif',

				   '<?php echo $path ?>/images/affilate_h.gif']);



				</script>

		<!--[if IE 6]><script type="text/javascript" src="http://info.template-help.com/files/ie6_warning/ie6_script_other.js"></script><![endif]-->

        </head>



        <body class=" <?php echo $pageclass; ?>">
       

        <div id="all">
        <div id="cabecera_cont">
        <div id="cabecera">
         					<div class="logoheader">

                                        <h1 id="logo">



                                        <?php if ($logo): ?>

                                        <a href="<?php echo $this->baseurl?>"><img src="<?php echo $this->baseurl ?>/<?php echo htmlspecialchars($logo); ?>"  alt="<?php echo htmlspecialchars($templateparams->get('sitetitle'));?>" /></a>

                             <?php endif;?>
                             <?php echo htmlspecialchars($templateparams->get('sitetitle'));?>

                                        <?php if (!$logo ): ?>
                                        <?php endif; ?>

                                        <span class="header1">

                                        <?php echo htmlspecialchars($templateparams->get('sitedescription'));?>

                                        </span></h1>
                                        
                               

          </div><!-- end logoheader -->
                               <div class="foot_social"><jdoc:include type="modules" name="position-7" /></div>
                                <div class="map-contact" ><jdoc:include type="modules" name="position-up" /></div>

		  </div>
          
        			</div>
                    
                            <div class="clear"> </div>
					
					<div id="header">
                               		
                       <jdoc:include type="modules" name="position-1" />

                       </div><!-- end header -->  
                       
						<div id="gallery"><jdoc:include type="modules" name="position-13"  /></div>
						
						<div id="bunner"><jdoc:include type="modules" name="position-bunner"  /></div>
						
						
                        

                        <?php if($this->countModules('top')) : ?>

                        <div id="message" class="container_24">

                          <jdoc:include type="modules" name="top" style="xhtml" />

                        </div>

                        <?php endif; ?>

                        <div id="content_bg">

                        

                        <div id="content" class="container_24" >

                        

                        <div id="maincolbck">

                          <?php if($this->countModules('left')) : ?>

                          <div id="sidebar" class="grid_<?php echo $leftcolgrid;?> alpha">

                            <jdoc:include type="modules" name="left" style="xhtml" />

                          </div>

                          <?php endif; ?>

                          <div id="maincolumn" class="grid_<?php echo (24-$leftcolgrid-$rightcolgrid)-1;?>">

                          <div class="searhc_block"><jdoc:include type="modules" name="position-0" /></div>
                          
                          

                            <jdoc:include type="modules" name="breadcrumbs" style="xhtml" />

                             					<?php if ($this->getBuffer('message')) : ?>

                                                        <div class="error">

                                                                <h2>

                                                                        <?php echo JText::_('JNOTICE'); ?>

                                                                </h2>

                                                                <jdoc:include type="message" />

                                                                

                                                        </div>

                                                <?php endif; ?>

                            <jdoc:include type="component" />
                            	<div id="news-gk4"><jdoc:include type="modules" name="position-news"  /></div>

                          </div>

                          <?php if($this->countModules('right')) : ?>

                          <div id="sidebar-2" class="grid_<?php echo $rightcolgrid;?>">

                            <jdoc:include type="modules" name="right" style="xhtml" />

                          </div>

                          <?php endif; ?>

                        </div>

                        <div class="clear"> </div>

                       </div>

                       </div> 

                       

                        <div class="push"></div>          

		</div>

        <div id="footer-outer">

                        



                        <div id="footer-sub">

                                <div id="footer">

                                        <p class="copy">

                                                Metro de Sevilla<?php echo JText::_('&copy;');?> 2011  
                                                
												<p style="float:left;margin-top:60px; margin-left:20px; padding-left:30px; color:#060;">*</p><p class="aviso-legal" style="margin-top:60px; margin-left:20px; padding-left:30px;"><a><jdoc:include type="modules" name="aviso-legal" class="aviso-legal"  /></p>
                                        </p>
                                        
                                        	 <div class="contact_info0"><jdoc:include type="modules" name="position-bottom-left" /></div>

                                         <div class="contact_info1"><jdoc:include type="modules" name="position-5" /></div>

										 <div class="contact_info2"><jdoc:include type="modules" name="position-6" /></div>







                                </div><!-- end footer -->



                        </div>



                </div>
			<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-25799170-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
        </body>

</html>
