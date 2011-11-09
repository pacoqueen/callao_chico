<div id="icetabs<?php echo $module->id; ?>" class="ice-tabs<?php echo $params->get('moduleclass_sfx','');?>" style="height:<?php echo $moduleHeight;?>; width:<?php echo $moduleWidth;?>">
    <div class="<?php echo $class;?>">
        <?php if( $class && $class != 'ice-bottom' ) : ?>
            <?php require( dirname(__FILE__) . DS . '_navigator.php' );?>
        <?php endif; ?>
     <!-- MAIN CONTENT --> 
      <div class="ice-main-wapper" >
            <?php foreach( $list as $row ): ?>
            <div class="ice-main-item">
                     <?php // echo $row->mainImage; ?> 
                    <div class="ice-description">
                    <p><?php echo $row->introtext;?></p>
                 </div>
            </div> 
            <?php endforeach; ?>
      </div>
      <?php if( $params->get('display_button', '') ): ?>
    
        <div class="ice-next"><span><?php echo JText::_('Next');?></span></div>
        <div class="ice-previous"><span><?php echo JText::_('Previous');?></span></div>
    
      <?php endif; ?>
    <!-- END MAIN CONTENT -->
        <?php if( $class && $class == 'ice-bottom' ) : ?>
            <?php require( dirname(__FILE__) . DS . '_navigator.php' );?>
        <?php endif; ?> 
     </div>   
 </div> 
