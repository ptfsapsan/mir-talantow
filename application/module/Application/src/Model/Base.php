<?php
namespace Application\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class Base{

   protected $_sm;
   protected $_db;
   private $_sql;

   public function __construct(ServiceLocatorInterface $sm){
      $this->_sm = $sm;
      $db = GlobalAdapterFeature::getStaticAdapter();
      $this->_db = $db;
      $this->_sql = new Sql($db);
      //$db->query("SET time_zone 'Europe/Moscow'");


   }

   protected function getSm(){
      return $this->_sm;
   }

    /**
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
   public function getTranslator(){
      return $this->_sm->get('translator');
   }

    /**
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
   public function getConfig(){
      return $this->_sm->get('config');
   }

   protected static function generateCode($nLength = 7){
      $randStr = '';
      $feed = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
      for($i = 0; $i < $nLength; $i++){
         $randStr .= substr($feed, rand(0, strlen($feed) - 1), 1);
      }

      return $randStr;
   }

   protected static function generateDigitCode($nLength = 6){
      $randStr = '';
      $feed = "123456789";
      for($i = 0; $i < $nLength; $i++){
         $randStr .= substr($feed, rand(0, strlen($feed) - 1), 1);
      }

      return $randStr;
   }

   // транслитерация
   protected static function translit($str){
      $trans = [
         "а" => "a",
         "б" => "b",
         "в" => "v",
         "г" => "g",
         "д" => "d",
         "е" => "e",
         "ё" => "yo",
         "ж" => "zh",
         "з" => "z",
         "и" => "i",
         "й" => "j",
         "к" => "k",
         "л" => "l",
         "м" => "m",
         "н" => "n",
         "о" => "o",
         "п" => "p",
         "р" => "r",
         "с" => "s",
         "т" => "t",
         "у" => "u",
         "ф" => "f",
         "х" => "h",
         "ц" => "c",
         "ч" => "ch",
         "ш" => "sh",
         "щ" => "shch",
         "ы" => "y",
         "э" => "e",
         "ю" => "u",
         "я" => "ya",
         "А" => "A",
         "Б" => "B",
         "В" => "V",
         "Г" => "G",
         "Д" => "D",
         "Е" => "E",
         "Ё" => "Yo",
         "Ж" => "Zh",
         "З" => "Z",
         "И" => "I",
         "Й" => "J",
         "К" => "K",
         "Л" => "L",
         "М" => "M",
         "Н" => "N",
         "О" => "O",
         "П" => "P",
         "Р" => "R",
         "С" => "S",
         "Т" => "T",
         "У" => "U",
         "Ф" => "F",
         "Х" => "H",
         "Ц" => "C",
         "Ч" => "Ch",
         "Ш" => "Sh",
         "Щ" => "Shch",
         "Ы" => "y",
         "Э" => "E",
         "Ю" => "U",
         "Я" => "Ya",
         "ь" => "",
         "Ь" => "",
         "ъ" => "",
         "Ъ" => "",
         " " => "-",
         "," => "-",
         "." => "_",
         "'" => "`",
         '"' => "`"
      ];

      return strtolower(strtr(stripcslashes($str), $trans));
   }

    /**
     * @param $sql_obj
     * @return \Zend\Db\Adapter\Driver\StatementInterface|\Zend\Db\ResultSet\ResultSet
     * @throws \Exception
     */
   protected function getQuery($sql_obj){
      $db = $this->_db;
      $query = $this->_sql->buildSqlString($sql_obj);
      $res = $db->query($query, $db::QUERY_MODE_EXECUTE);
      if(!$res) throw new \Exception("Error sql");
      return $res;
   }

   protected function fetchAll($sql_string, $params = []){
      $params = (array)$params;
      $res = [];
      $r = $this->_db->query($sql_string, $params);
      foreach($r as $i){
         $res[] = (array)$i;
      }
      return $res;
   }

   protected function fetchRow($sql_string, $params = []){
      $params = (array)$params;
      $r = (array)$this->_db->query($sql_string, $params)
         ->current();
      $res = isset($r[0]) && !$r[0]? false: $r;
      return $res;
   }

   protected function fetchOne($sql_string, $params = []){
      $params = (array)$params;
      $r = $this->fetchRow($sql_string, (array)$params);
      $res = empty($r)? false: current($r);
      return $res;
   }

    /**
     * @param $table_name
     * @param $key_values
     * @throws \Exception
     */
   protected function insert($table_name, $key_values){
      $insert = $this->_sql->insert($table_name);
      $insert->columns(array_keys($key_values));
      $insert->values($key_values);
      $this->getQuery($insert);
   }

   protected function lastInsertId(){
      return $this->_db->getDriver()
         ->getLastGeneratedValue();
   }

    /**
     * @param $table_name
     * @param $key_values
     * @param $where
     * @throws \Exception
     */
   protected function update($table_name, $key_values, $where){
      $update = $this->_sql->update($table_name);
      $update->set($key_values);
      $update->where($where);
      $this->getQuery($update);
   }

    /**
     * @param $table_name
     * @param $where
     * @throws \Exception
     */
   protected function delete($table_name, $where){
      $delete = $this->_sql->delete($table_name);
      $delete->where($where);
      $this->getQuery($delete);
   }

    /**
     * @param $date
     * @return string
     * @throws \Exception
     */
   protected static function encodeDate($date){
      list($d, $m, $Y) = explode('.', $date);
      if(!checkdate($m, $d, $Y)) throw new \Exception('Неверная дата');
      return "$Y-$m-$d";
   }

    /**
     * @param $date
     * @return string
     * @throws \Exception
     */
   protected static function decodeDate($date){
      list($Y, $m, $d) = explode('-', $date);
      if(!checkdate($m, $d, $Y)) throw new \Exception('Неверная дата');
      return "$d.$m.$Y";
   }

   public function quoteValue($value){
      return $this->_db->platform->quoteValue($value);
   }

   protected static function forPagingData($session, $count){
      $page = (int)$session->page;
      $on_page = (int)$session->on_page;
      $max_page = ceil($count / $on_page);
      $page = $page > $max_page? $page = $max_page: ($page < 1? 1: $page);
      $res = [
         'data' => [],
         'count' => $count,
         'page' => $page,
         'on_page' => $on_page,
      ];
      return $res;
   }

   protected static function forPagingData2($page, $on_page, $count){
      $page = (int)$page;
      $on_page = (int)$on_page;
      $max_page = ceil($count / $on_page);
      $page = $page > $max_page? $page = $max_page: ($page < 1? 1: $page);
      $res = [
         'data' => [],
         'count' => $count,
         'page' => $page,
         'on_page' => $on_page,
      ];
      return $res;
   }

}