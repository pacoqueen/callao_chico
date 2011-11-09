<!-- NAVIGATOR -->
  <div class="ice-navigator-outer">
  		<ul class="ice-navigator">
         <?php $i=1 ?>
        <?php foreach( $list as $row ): ?>
            <li class="<?php echo $params->get('class_tab').$i; $i++;?>"><div><span><strong><?php echo $row->title;?></strong></span></div></li>
         <?php endforeach; ?> 		
        </ul>
  </div>
 <!-- NAVIGATOR -->
