<?php
/**
 * SimpleForm2
 *
 * @version 1.0.1
 * @package SimpleForm2
 * @author ZyX (allforjoomla.com)
 * @copyright (C) 2010 by ZyX (http://www.allforjoomla.com)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 *
 * If you fork this to create your own project,
 * please make a reference to allforjoomla.com someplace in your code
 * and provide a link to http://www.allforjoomla.com
 **/
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>simpleForm2</title>
</head>
<body>
<p><?php echo JTEXT::_('Hello');?></p>
<p><?php echo JTEXT::_('Sent from page');?>: <?php echo $url;?>.</p>
<p><?php echo JTEXT::_('Date');?>: <?php echo $date;?>.</p>
<p><?php echo JTEXT::_('User ip');?>: <?php echo $ip;?>.</p>
<table cellpadding="5" cellspacing="0">
<tr>
<th colspan="2"><font size="+1"><?php echo JTEXT::_('Form content');?>:</font></th>
</tr>
<?php echo $rows;?>
</table>
</body>
</html>