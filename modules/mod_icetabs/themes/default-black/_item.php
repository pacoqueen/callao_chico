<div class="ice-description">
    <h3 class="ice-title">
            <a <?php echo $target;?>  href="<?php echo $row->link;?>" title="<?php echo $row->title;?>"><?php echo $row->title;?></a>
    </h3>
<?php if( $params->get('item-content','introtext') == 'introtext') : ?>
<?php echo $row->introtext; ?>
<?php else : ?>
   <?php if( $params->get('show_readmore',1) ): ?>
    <a class="ice-readmore" <?php echo $target;?>  href="<?php echo $row->link;?>" title="<?php echo $row->title;?>">
    <?php echo $row->mainImage; ?>
    </a>
    <?php endif; ?>
    <?php echo $row->description;?>
 <?php endif; ?>
     <?php if( $params->get('show_readmore',1) ):  ?>
    <a class="ice-readmore" <?php echo $target;?>  href="<?php echo $row->link;?>" title="<?php echo $row->title;?>">
    <?php echo JText::_('Read more...');?>
    </a>
    <?php endif; ?>
  </div>