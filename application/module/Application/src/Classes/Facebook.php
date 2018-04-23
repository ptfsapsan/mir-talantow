<?php

namespace Application\Classes;

use Facebook\Authentication\AccessToken;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\Curl;
use Facebook\Facebook as FB;
use Facebook\Exceptions;

class Facebook extends FB{

   private static $app_id = "595261537311303";
   private static $app_secret = "eb694f99eecb2f432afb3c88a4170c5a";
   private static $callback_url = DOMAIN.'/ajax/facebook-callback';
   private static $permissions = ['publish_actions'];

   public function __construct(array $config = []){
      parent::__construct([
         'app_id' => self::$app_id,
         'app_secret' => self::$app_secret,
      ]);
   }

   public function sendLoginUrl(){
      $url = $this->getRedirectLoginHelper()
         ->getLoginUrl(self::$callback_url, self::$permissions);
var_dump($url);die;
      //file_get_contents($url);
//      $adapter = new Curl();
//      $adapter->setCurlOption(CURLOPT_RETURNTRANSFER, true);
//
//      $client = new Client($url);
//      $client->setAdapter($adapter);
//      $client->setMethod('GET');
//      try{
//         $client->send();
//      }
//      catch(\Exception $e){
//         var_dump($e->getMessage());die;
//      }
   }

    /**
     * @param $token
     * @param $message
     * @param $file
     * @throws Exceptions\FacebookSDKException
     * @throws \Exception
     */
   public function sendPost($token, $message, $file){
      $accessToken = $this->getToken($token);

      $data = [
         'message' => $message,
         'source' => $this->fileToUpload($file),
      ];

      try {
         $response = $this->post("/me/feed", $data, $accessToken);
      }
      catch(Exceptions\FacebookResponseException $e) {
         echo 'Graph returned an error: ' . $e->getMessage();
         exit;
      }
      catch(Exceptions\FacebookSDKException $e) {
         echo 'Facebook SDK returned an error: ' . $e->getMessage();
         exit;
      }

      $response->getGraphNode();
   }

    /**
     * @param null $token
     * @return array|AccessToken|null
     * @throws \Exception
     */
   public function getToken($token = null){
      $accessToken = is_string($token)? new AccessToken($token):
         $this->getRedirectLoginHelper()->getAccessToken();
      if(empty($accessToken)) throw new \Exception();
      
      $oAuth2Client = $this->getOAuth2Client();
      if($accessToken->isLongLived()) return $accessToken; 
      
      $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);

      $meta_data = $oAuth2Client->debugToken($accessToken);
      $expire_date = $meta_data->getField('expires_at');
      $expire_date->setTimezone(new \DateTimeZone('Europe/Moscow'));

      return [
         'token' => $accessToken->getValue(),
         'expires' => $expire_date->format('Y-m-d H:i:s'),
      ];
   }





}