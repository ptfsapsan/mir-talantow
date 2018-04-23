<?php
namespace Application\Classes;

class Instagram extends \InstagramAPI\Instagram{

   public function __construct($debug = false){
      parent::__construct($debug);
      $this->username = 'mirtalantow';
      $this->password = '1123-mirtalantow';
   }

   public function post($file, $caption){
      $this->setUser($this->username, $this->password);
      try{
         $this->login();
      }
      catch(\Exception $e){
         throw new \Exception($e->getMessage());
      }

      try{
         $this->uploadPhoto($file, $caption);
      }
      catch(\Exception $e){
         throw new \Exception($e->getMessage());
      }

   }

}