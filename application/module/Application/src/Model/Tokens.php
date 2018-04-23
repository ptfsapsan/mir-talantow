<?php

namespace Application\Model;
use Application\Classes\Facebook;

/**
 * Class Tokens
 *
 * @package Application\Model
 */
class Tokens extends Base{
   /**
    * @param $type
    *
    * @return array|bool
    */
   public function getCurrentToken($type){
      $this->delete('tokens', "expires < '".date('Y-m-d H:i:s')."'");
      return $this->fetchRow("SELECT * FROM tokens WHERE `type` = ?", $type);
   }
   
   public function facebookPost($message, $file){
      $token = $this->getCurrentToken('facebook')['token'];
      $fb = new Facebook();
      $accessToken = $fb->getToken($token);
      if(is_array($accessToken)){
         $this->insert('tokens', $accessToken);
         $accessToken = $fb->getToken($accessToken['token']);
      }
      $fb->sendPost($accessToken, $message, $file);
      
   }
}