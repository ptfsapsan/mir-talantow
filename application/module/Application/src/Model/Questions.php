<?php
namespace Application\Model;

class Questions extends Base{

   public function getByType($type, $page, $on_page){
      $where = "WHERE type = '$type' AND action = 'yes'";
      $count = $this->fetchOne("SELECT COUNT(*) FROM questions $where");
      $res = self::forPagingData2($page, $on_page, $count);
      if(empty($count)) return $res;

      $res['data'] = $this->fetchAll("SELECT * FROM questions $where
         ORDER BY date DESC LIMIT ?, ?",
         [($page - 1) * $on_page, $on_page]);

      return $res;
   }

   public function addQuestion($type, $params){
      $this->insert('questions', [
         'date' => date('c'),
         'type' => $type,
         'email' => $params['email'],
         'question' => $params['question'],
      ]);
   }
}