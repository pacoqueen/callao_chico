<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class plgSystemRegacymailing extends JPlugin
{
  function plgSystemRegacymailing(&$subject, $config){
    parent::__construct($subject, $config);
    }
    function onAfterRoute(){
      if(empty($_POST['option']) OR empty($_POST['func']) OR $_POST['option'] != 'com_virtuemart' OR $_POST['func'] != 'shopperupdate') return;
    $user =& JFactory::getUser();
    if(empty($user->id)) return;
    $acylistsdisplayed = JRequest::getString('acylistsdisplayed_dispall').','.JRequest::getString('acylistsdisplayed_onecheck');
    if(strlen($acylistsdisplayed) < 2) return;
    $listsDisplayed = explode(',',$acylistsdisplayed);
    JArrayHelper::toInteger($listsDisplayed);
    if(empty($listsDisplayed)) return;
    if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php')) return;
    $userClass = acymailing::get('class.subscriber');
    $subid = $userClass->subid($user->id);
    if(empty($subid)) return; //The user should already be there
    $visiblelistschecked = JRequest::getVar( 'acysub', array(), '', 'array' );
    $acySubHidden = JRequest::getString( 'acysubhidden');
    if(!empty($acySubHidden)){
      $visiblelistschecked = array_merge($visiblelistschecked,explode(',',$acySubHidden));
    }
    $listsClass = acymailing::get('class.list');
    $allLists = $listsClass->getLists('listid');
    if(acymailing::level(1)){
      $allLists = $listsClass->onlyCurrentLanguage($allLists);
    }
    $formLists = array();
    foreach($listsDisplayed as $listidDisplayed){
      $newlists = null;
      $newlists['status'] = in_array($listidDisplayed,$visiblelistschecked) ? '1' : '-1';
      $formLists[$listidDisplayed] = $newlists;
    }
    $userClass->saveSubscription($subid,$formLists);
    }
  function onAfterRender(){
    $option = JRequest::getString('option');
    if(empty($option)) return;
    $components = array();
    $components['com_user'] = array('view' => array('register'),'lengthafter' => 200);
    $components['com_users'] = array('view' => array('registration'),'lengthafter' => 200, 'email' => 'jform\[email2\]', 'password' => 'jform\[password2\]');
    $components['com_alpharegistration'] = array('view' => array('register'),'lengthafter' => 250);
    $components['com_ccusers'] = array('view' => array('register'),'lengthafter' => 500);
    $components['com_virtuemart'] = array('view' => array('shop.registration','account.billing','checkout.index'),'viewvar' => 'page','lengthafter' => 500, 'acysubscribestyle' => 'style="clear:both"');
    $components['com_hikashop'] = array('view' => array('checkout','user'),'viewvar' => 'ctrl', 'lengthafter' => 500 , 'tdclass' => 'key', 'email' => 'data\[register\]\[email\]','password' => 'data\[register\]\[password2\]');
    $components['com_tienda'] = array('view' => array('checkout'),'lengthafter' => 500 ,  'email' => 'email_address','password' => 'password2');
    $components['com_osemsc'] = array('view' => array('register'),'lengthafter' => 200,'email' => 'oseemail','password' => 'osepassword2');
    $components['com_gcontact'] = array('view' => array('registration'),'lengthafter' => 200);
    if(!isset($components[$option])) return;
    $viewVar = (isset($components[$option]['viewvar']) ? $components[$option]['viewvar'] : 'view');
    if(!in_array(JRequest::getString($viewVar,JRequest::getString('task')),$components[$option]['view'])) return;
    if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php')) return;
    if(!isset($this->params)){
      $plugin =& JPluginHelper::getPlugin('system', 'regacymailing');
      $this->params = new JParameter( $plugin->params );
    }
    $visibleLists = $this->params->get('lists','None');
    if($visibleLists == 'None') return;
    $visibleListsArray = array();
    $listsClass = acymailing::get('class.list');
    $allLists = $listsClass->getLists('listid');
    if(acymailing::level(1)){
      $allLists = $listsClass->onlyCurrentLanguage($allLists);
    }
    if(strpos($visibleLists,',') OR is_numeric($visibleLists)){
      $allvisiblelists = explode(',',$visibleLists);
      foreach($allLists as $oneList){
        if($oneList->published AND in_array($oneList->listid,$allvisiblelists)) $visibleListsArray[] = $oneList->listid;
      }
    }elseif(strtolower($visibleLists) == 'all'){
      foreach($allLists as $oneList){
        if($oneList->published){$visibleListsArray[] = $oneList->listid;}
      }
    }
    if(empty($visibleListsArray)) return;
    $checkedLists = $this->params->get('listschecked','All');
    $userClass = acymailing::get('class.subscriber');
    $loggedinUser = JFactory::getUser();
    if(!empty($loggedinUser->id)){
      $currentSubid = $userClass->subid($loggedinUser->id);
      if(!empty($currentSubid)){
        $currentSubscription = $userClass->getSubscriptionStatus($currentSubid,$visibleListsArray);
        $checkedLists = '';
        foreach($currentSubscription as $listid => $oneSubsciption){
          if($oneSubsciption->status == '1') $checkedLists .= $listid.',';
        }
      }
    }
    if(strtolower($checkedLists) == 'all'){ $checkedListsArray = $visibleListsArray;}
    elseif(strpos($checkedLists,',') OR is_numeric($checkedLists)){ $checkedListsArray = explode(',',$checkedLists);}
    else{ $checkedListsArray = array();}
    $subText = $this->params->get('subscribetext');
    if(empty($subText)){
      $lang =& JFactory::getLanguage();
      $lang->load(ACYMAILING_COMPONENT,JPATH_SITE);
	if(in_array($this->params->get('displaymode','dispall'),array('dispall','dropdown'))){
        $subText = JText::_('SUBSCRIPTION').':';
      }else{
        $subText = JText::_('YES_SUBSCRIBE_ME');
      }
    }
    $body = JResponse::getBody();
    if(!empty($components[$option][$this->params->get('fieldafter','password')])){
      $after = $components[$option][$this->params->get('fieldafter','password')];
    }else{
      if($this->params->get('fieldafter','password') == 'custom'){ $after =  $this->params->get('fieldaftercustom'); }
      else{ $after = ($this->params->get('fieldafter','password') == 'email') ? 'email' : 'password2'; }
    }
    $listsDisplayed = '<input type="hidden" value="'.implode(',',$visibleListsArray).'" name="acylistsdisplayed_'.$this->params->get('displaymode','dispall').'" />';
    $return = '';
    if($this->params->get('displaymode','dispall') == 'dispall'){
      $return = '<table>';
      foreach($visibleListsArray as $oneList){
        $check = in_array($oneList,$checkedListsArray) ? 'checked="checked"' : '';
        $return .= '<tr><td><input type="checkbox" id="acy_list_'.$oneList.'" class="acymailing_checkbox" name="acysub[]" '.$check.' value="'.$oneList.'"/></td><td nowrap="nowrap"><label for="acy_list_'.$oneList.'" class="acylabellist">';
        $return .= $allLists[$oneList]->name;
        $return .= '</label></td></tr>';
      }
      $return .= '</table>';
    }elseif($this->params->get('displaymode','dispall') == 'onecheck'){
      $check = '';
      foreach($visibleListsArray as $oneList){
        if(in_array($oneList,$checkedListsArray)){ $check = 'checked="checked"'; break; };
      }
      $return = '<span class="acysubscribe_span"><input type="checkbox" id="acysubhidden" name="acysubhidden" value="'.implode(',',$visibleListsArray).'" '.$check.' /> <label for="acysubhidden">'.$subText.'</label>'.$listsDisplayed.'</span>';
    }elseif($this->params->get('displaymode','dispall') == 'dropdown'){
      $return = '<select name="acysub[1]">';
		foreach($visibleListsArray as $oneList){
			$return .= '<option value="'.$oneList.'">'.$allLists[$oneList]->name.'</option>';
		}
      $return .= '</select>';
    }
    $tdclass = '';
    if(!empty($components[$option]['tdclass'])) $tdclass = 'class="'.$components[$option]['tdclass'].'"';
    $style = $this->params->get('customcss');
	if(!empty($style)){
		$stylestring = '<style type="text/css">'."\n".$style."\n".'</style>'."\n";
		$return = $stylestring.$return;
	}
    if(preg_match('#(name="'.$after.'".{0,'.$components[$option]['lengthafter'].'}</tr>)#Uis',$body)){
       if(in_array($this->params->get('displaymode','dispall'),array('dispall','dropdown'))){
        $return = '<tr class="acysubscribe"><td '.$tdclass.' style="padding-top:5px" valign="top">'.$subText.$listsDisplayed.'</td><td>'.$return.'</td></tr>';
      }else{
        $return = '<tr class="acysubscribe"><td colspan="2">'.$return.'</td></tr>';
      }
      $body = preg_replace('#(name="'.$after.'".{0,'.$components[$option]['lengthafter'].'}</tr>)#Uis','$1'.$return,$body,1);
      JResponse::setBody($body);
      return;
    }
    if(preg_match('#(name="'.$after.'".{0,'.$components[$option]['lengthafter'].'}</div>)#Uis',$body)){
      if(in_array($this->params->get('displaymode','dispall'),array('dispall','dropdown'))){
        $return = '<div class="acysubscribe"><label class="labelacysubscribe">'.$subText.$listsDisplayed.'</label>'.$return.'</div>';
      }else{
        $return = '<div class="acysubscribe" '.@$components[$option]['acysubscribestyle'].' >'.$return.'</div>';
      }
      $body = preg_replace('#(name="'.$after.'".{0,'.$components[$option]['lengthafter'].'}</div>)#Uis','$1'.$return,$body,1);
      JResponse::setBody($body);
      return;
    }
    if(preg_match('#(name="'.$after.'".{0,'.$components[$option]['lengthafter'].'}</p>)#Uis',$body)){
      if(in_array($this->params->get('displaymode','dispall'),array('dispall','dropdown'))){
        $return = '<div class="acysubscribe"><label class="labelacysubscribe">'.$subText.$listsDisplayed.'</label>'.$return.'</div>';
      }else{
        $return = '<div class="acysubscribe">'.$return.'</div>';
      }
      $body = preg_replace('#(name="'.$after.'".{0,'.$components[$option]['lengthafter'].'}</p>)#Uis','$1'.$return,$body,1);
      JResponse::setBody($body);
      return;
    }
    if(preg_match('#(name="'.$after.'".{0,'.$components[$option]['lengthafter'].'}</dd>)#Uis',$body)){
      if(in_array($this->params->get('displaymode','dispall'),array('dispall','dropdown'))){
        $return = '<dt class="acysubscribe"><label class="labelacysubscribe">'.$subText.$listsDisplayed.'</label></dt><dd><table>'.$return.'</table></dd>';
      }else{
        $return = '<div class="acysubscribe">'.$return.'</div>';
      }
      $body = preg_replace('#(name="'.$after.'".{0,'.$components[$option]['lengthafter'].'}</dd>)#Uis','$1'.$return,$body,1);
      JResponse::setBody($body);
      return;
    }
   }
  function onUserBeforeSave($user, $isnew, $new){
    return $this->onBeforeStoreUser($user, $isnew);
  }
  function onBeforeStoreUser($user, $isnew){
    $this->oldUser = $user;
    return true;
  }
  function onUserAfterSave($user, $isnew, $success, $msg){
    return $this->onAfterStoreUser($user,$isnew,$success,$msg);
  }
  function onAfterStoreUser($user, $isnew, $success, $msg){
    if($success===false OR empty($user['email'])) return false;
    if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php')) return true;
    if(!isset($this->params)){
      $plugin =& JPluginHelper::getPlugin('system', 'regacymailing');
      $this->params = new JParameter( $plugin->params );
    }
    $config = acymailing::config();
    $joomUser = null;
    $joomUser->email = trim(strip_tags($user['email']));
    if(!empty($user['name'])) $joomUser->name = trim(strip_tags($user['name']));
    if(empty($user['block'])) $joomUser->confirmed = 1;
    $joomUser->enabled = 1 - (int)$user['block'];
    $joomUser->userid = $user['id'];
    $userClass = acymailing::get('class.subscriber');
    if(!$isnew AND !empty($this->oldUser['email']) AND $user['email'] != $this->oldUser['email']){
      $joomUser->subid = $userClass->subid($this->oldUser['email']);
    }
    if(empty($joomUser->subid)){
      $joomUser->subid = $userClass->subid($joomUser->userid);
    }
    $userClass->checkVisitor = false;
    $userClass->sendConf = false;
    if(isset($joomUser->email)){
      $userHelper = acymailing::get('helper.user');
      if(!$userHelper->validEmail($joomUser->email)) return true;
    }
    $subid = $userClass->save($joomUser);
    if($isnew || empty($joomUser->subid)){
      $listsToSubscribe = $config->get('autosub','None');
      $currentSubscription = $userClass->getSubscriptionStatus($subid);
      $config = acymailing::config();
      $listsClass = acymailing::get('class.list');
      $allLists = $listsClass->getLists('listid');
      if(acymailing::level(1)){
        $allLists = $listsClass->onlyCurrentLanguage($allLists);
      }
      $visiblelistschecked = JRequest::getVar( 'acysub', array(), '', 'array' );
      $acySubHidden = JRequest::getString( 'acysubhidden');
      if(!empty($acySubHidden)){
        $visiblelistschecked = array_merge($visiblelistschecked,explode(',',$acySubHidden));
      }
      if(empty($visiblelistschecked) AND !empty($_SESSION['acysub'])){
        $visiblelistschecked = $_SESSION['acysub'];
        unset($_SESSION['acysub']);
      }
      $listsArray = array();
      if(strpos($listsToSubscribe,',') OR is_numeric($listsToSubscribe)){
        $listsArrayParam = explode(',',$listsToSubscribe);
        foreach($allLists as $oneList){
          if($oneList->published AND (in_array($oneList->listid,$visiblelistschecked) || in_array($oneList->listid,$listsArrayParam))){$listsArray[] = $oneList->listid;}
        }
      }elseif(strtolower($listsToSubscribe) == 'all'){
        foreach($allLists as $oneList){
          if($oneList->published){$listsArray[] = $oneList->listid;}
        }
      }elseif(!empty($visiblelistschecked)){
        foreach($allLists as $oneList){
          if($oneList->published AND in_array($oneList->listid,$visiblelistschecked)){$listsArray[] = $oneList->listid;}
        }
      }
      $statusAdd = (empty($joomUser->enabled) OR (empty($joomUser->confirmed) AND $config->get('require_confirmation',false))) ? 2 : 1;
      $addlists = array();
      if(!empty($listsArray)){
        foreach($listsArray as $idOneList){
          if(!isset($currentSubscription[$idOneList])){
            $addlists[$statusAdd][] = $idOneList;
          }
        }
      }
      if(!empty($addlists)) {
        $listsubClass = acymailing::get('class.listsub');
        if(!empty($user['gid'])) $listsubClass->gid = $user['gid'];
        if(!empty($user['groups'])) $listsubClass->gid = $user['groups'];
        $listsubClass->addSubscription($subid,$addlists);
      }
    }else{
      if(!empty($this->oldUser['block']) AND !empty($joomUser->confirmed)){
        $userClass->confirmSubscription($subid);
      }
    }
    return true;
  }
  function onUserAfterDelete($user,$success,$msg){
    return $this->onAfterDeleteUser($user, $success, $msg);
  }
  function onAfterDeleteUser($user, $success, $msg){
    if($success===false) return false;
    if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php')) return true;
    $userClass = acymailing::get('class.subscriber');
    $subid = $userClass->subid($user['email']);
    if(!empty($subid)){
      $userClass->delete($subid);
    }
    return true;
  }
}//endclass