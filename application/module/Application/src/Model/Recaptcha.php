<?php
namespace Application\Model;

class Recaptcha{

   const KEY = '6LdjqyYTAAAAAPoeFS1SoO0jUt77N_EZv_DeI8qu';
   private $_secret_key = '6LdjqyYTAAAAAEeeK56x2w3FnZoTJHub4p5lZoz5';
   private $_url = 'https://www.google.com/recaptcha/api/siteverify';

   public function verify($g_response){
      $ch = curl_init($this->_url);
      $params = [
         'secret' => $this->_secret_key,
         'response' => $g_response,
//         'remoteip' => '',
//         'version' => ''
      ];
      $p = [];
      foreach($params as $k => $v) $p[] = $k.'='.$v;

      $options = [
         CURLOPT_POST => true,
         CURLOPT_POSTFIELDS => implode('&', $p),
         CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded'
         ),
         CURLINFO_HEADER_OUT => false,
         CURLOPT_HEADER => false,
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_SSL_VERIFYPEER => true
      ];
      curl_setopt_array($ch, $options);
      $res = curl_exec($ch);
      curl_close($ch);
      $result = json_decode($res, true);
      return (bool) $result['success'];
   }

}