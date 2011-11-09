<?php
defined('_JEXEC') or die('Direct Access to this location is not allowed.');
 
class ModInsertarticle {
    
    public function getArticles($args){
      $db = &JFactory::getDBO();
      $item = "";

      $id = $args['id'];
      if($id > 0){
 
            $query  = "select * ";
            $query .= "FROM #__content  WHERE id =".$id." AND state=1 " ;

            //echo $query;
           $db->setQuery($query);
           $item = $db->loadObject();
       }
 
      return $item;
    }
}
