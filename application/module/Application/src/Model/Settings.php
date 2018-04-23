<?php
namespace Application\Model;

class Settings extends Base{

   public function getSettingsBySiteCode($site_code){
      return $this->fetchRow("SELECT * FROM settings WHERE site_code = ?",
         $site_code);
   }

   public function getSettingsByUserId($user_id){
      return $this->fetchRow("SELECT * FROM settings WHERE user_id = ?",
         $user_id);
   }

   public function editSettings($params, $user_id){
      unset($params['act']);
      unset($params['submit']);
      $this->update('settings', $params, "user_id = $user_id");
   }
}