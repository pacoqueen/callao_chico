<?php defined('_JEXEC') or die('Restricted access'); // no direct access ?>

<?php
  $config=&JFactory::getConfig();
  $title=$config->getValue("title");


?>    

<?php
// get the parameter values
$moduleclass_sfx = $params->get('moduleclass_sfx');
$showtitle_article = $params->get('showtitle_article', 1);
$read_more = $params->get('read_more', 1); 
$Itemid = JRequest::getVar('Itemid', -1);
$url ="";
if(!empty($item->id)){
 $url = JRoute::_("index.php?option=com_content&amp;view=article&amp;id=".$item->id.":".$item->alias."&amp;catid=".$item->catid."&amp;Itemid=".$Itemid  , true);

?>
<div class="<?php echo $moduleclass_sfx; ?>">
<?php if($showtitle_article == 1) { ?><h2><?php echo $item->title ; ?></h2><?php } ?>
<?php echo $item->introtext; ?>
<?php   if ($read_more == 1){ ?>
  <a class='readmore' href='<?php echo $url; ?>'>
  <?php echo JText::_("Readmore") ?>
  </a>
<?php  } ?>
</div>
<?php  }else{ ?><?php echo JText::_("Not id article") ?><?php  } ?>
