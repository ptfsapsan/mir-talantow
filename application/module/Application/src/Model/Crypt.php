<?php

namespace Application\Model;

use Zend\Crypt\BlockCipher;

/**
 * Class Crypt
 *
 * @package Application\Model
 */
class Crypt
{
   /**
    * @var string
    */
   private static $key = 'asdwefrge32fs23g3423dvsdjifuve';
   /**
    * @var string
    */
   private static $iv = 'f328d230a23ee45afbe145d80f5b1580';
   /**
    * @var string
    */
   private static $method = 'aes-128-ctr';
   /**
    * @var int
    */
   private static $options = OPENSSL_RAW_DATA;

   /**
    * @param string $value
    *
    * @return string
    */
   public static function encrypt(string $value) : string
   {
      return openssl_encrypt($value, self::$method, self::$key,
         self::$options, hex2bin(self::$iv));
   }

   /**
    * @param string $value
    *
    * @return string
    */
   public static function decrypt(string $value) : string
   {
      return openssl_decrypt($value, self::$method, self::$key,
         self::$options, hex2bin(self::$iv));
   }

//   private static function getBlockCipher() : BlockCipher
//   {
//      return BlockCipher::factory('openssl', [
//         'algo' => 'aes',
//         'mode' => 'ctr',
//         'key' => self::$key,
//         'iv' => hex2bin(self::$iv),
//      ]);
//   }
//
//   public static function enc(string $value) : string
//   {
//      return self::getBlockCipher()->encrypt($value);
//   }
//
//   public static function dec(string $value) : string
//   {
//      return self::getBlockCipher()->decrypt($value);
//   }

}