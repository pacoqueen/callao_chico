<div class="ice-navigator-wrapper clearfix">
    <!-- NAVIGATOR -->
      <div class="ice-navigator-outer">
            <ul class="ice-navigator">
            <?php foreach( $list as $row ):?>
                <li><div><?php echo $row->thumbnail;?>
					<h4 class="ice-title"><?php echo substr($row->title, 0, (int) $params->get('title_max_chars',100)) ;?></h4>
                 </div></li>
             <?php endforeach; ?> 		
            </ul>
      </div>
 	<!-- END NAVIGATOR //-->
</div>    