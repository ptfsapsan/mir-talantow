<?php
namespace Application\Model;

class Walletone extends Base{

   private $_key = '78347a6e6c52485d314463396269774d62643851595a633562434f';
   CONST WMI_MERCHANT_ID = '141717454887';
   CONST WMI_CURRENCY_ID = 643;
   CONST WMI_SUCCESS_URL = DOMAIN.'/walletone/success';
   CONST WMI_FAIL_URL = DOMAIN.'/walletone/fail';
   CONST WMI_DESCRIPTION = 'Организационный взнос за конкурс на сайте '.
      SITE_NAME;
   
   
   

   public function getSignature($order){
      $p = [
         'WMI_MERCHANT_ID' => self::WMI_MERCHANT_ID,
         'WMI_PAYMENT_AMOUNT' =>
            number_format(($order['price'] - $order['paid']), 2, '.', ''),
         'WMI_CURRENCY_ID' => self::WMI_CURRENCY_ID,
         'WMI_SUCCESS_URL' => self::WMI_SUCCESS_URL,
         'WMI_FAIL_URL' => self::WMI_FAIL_URL,
         'WMI_DESCRIPTION' => 'BASE64:'.
            base64_encode(self::WMI_DESCRIPTION),
         'WMI_PAYMENT_NO' => $order['id'],
         'wo_email' => $order['email'],
         'submit' => 'Отправить',
      ];

      uksort($p, "strcasecmp");
      $fieldValues = "";

      foreach($p as $value){
         $value = iconv("utf-8", "windows-1251", $value);
         $fieldValues .= $value;
      }
      return base64_encode(pack("H*", md5($fieldValues.$this->_key)));
   }

   
   public function verify($params){
      if(empty($params["WMI_SIGNATURE"]))
         throw new \Exception("Отсутствует параметр WMI_SIGNATURE");

      if(empty($params["WMI_PAYMENT_NO"]))
         throw new \Exception("Отсутствует параметр WMI_PAYMENT_NO");

      if(empty($params["WMI_ORDER_STATE"]))
         throw new \Exception("Отсутствует параметр WMI_ORDER_STATE");

      // Извлечение всех параметров POST-запроса, кроме WMI_SIGNATURE
      $p = [];
      foreach($params as $name => $value){
         if($name !== "WMI_SIGNATURE") $p[$name] = urldecode($value);
      }

      // Сортировка массива по именам ключей в порядке возрастания
      // и формирование сообщения, путем объединения значений формы
      uksort($p, "strcasecmp");
      $values = "";

      foreach($p as $name => $value) $values .= $value;

      // Формирование подписи для сравнения ее с параметром WMI_SIGNATURE
      $signature = base64_encode(pack("H*", md5($values.$this->_key)));

      //Сравнение полученной подписи с подписью W1
      if($signature != $params["WMI_SIGNATURE"])
         throw new \Exception("Неверная подпись ".$params["WMI_SIGNATURE"]);

      if(strtoupper($params["WMI_ORDER_STATE"]) != "ACCEPTED")
         throw new \Exception("Неверное состояние ".$_POST["WMI_ORDER_STATE"]);
   }

}