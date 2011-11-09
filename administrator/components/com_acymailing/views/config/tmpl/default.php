<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="acy_content" >
<div id="iframedoc"></div>
<form action="index.php" method="post" name="adminForm" autocomplete="off">
	<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="config" />
	<?php echo JHTML::_( 'form.token' );
		echo $this->tabs->startPane( 'config_tab');
		echo $this->tabs->startPanel( JText::_( 'MAIL_CONFIG' ), 'config_mail');
		include(dirname(__FILE__).DS.'mail.php');
		echo $this->tabs->endPanel();
		echo $this->tabs->startPanel( JText::_( 'QUEUE_PROCESS' ), 'config_queue');
		include(dirname(__FILE__).DS.'queue.php');
		echo $this->tabs->endPanel();
		echo $this->tabs->startPanel( JText::_( 'SUBSCRIPTION' ), 'config_subscription');
		include(dirname(__FILE__).DS.'subscription.php');
		echo $this->tabs->endPanel();
		echo $this->tabs->startPanel( JText::_( 'INTERFACE' ), 'config_interface');
		include(dirname(__FILE__).DS.'interface.php');
		echo $this->tabs->endPanel();
		echo $this->tabs->startPanel( JText::_( 'SECURITY' ), 'config_security');
		include(dirname(__FILE__).DS.'security.php');
		echo $this->tabs->endPanel();
		if(file_exists(dirname(__FILE__).DS.'others.php')){
			echo $this->tabs->startPanel( JText::_( 'OTHERS' ), 'config_others');
			include(dirname(__FILE__).DS.'others.php');
			echo $this->tabs->endPanel();
		}
		if(acymailing::level(3)){
			echo $this->tabs->startPanel( JText::_( 'ACCESS_LEVEL' ), 'config_acl');
			include(dirname(__FILE__).DS.'acl.php');
			echo $this->tabs->endPanel();
		}
		echo $this->tabs->startPanel( JText::_( 'PLUGINS' ), 'config_plugins');
		include(dirname(__FILE__).DS.'plugins.php');
		echo $this->tabs->endPanel();
		echo $this->tabs->startPanel( JText::_( 'LANGUAGES' ), 'config_languages');
		include(dirname(__FILE__).DS.'languages.php');
		echo $this->tabs->endPanel();
		echo $this->tabs->endPane();
	?>
	<div class="clr"></div>
</form>
</div>