<?php
namespace Application\Model;

class Comments extends Base{

   public function getActive($page, $on_page){
      $where = "WHERE active = 'yes'";
      $count = $this->fetchOne("SELECT COUNT(*) FROM comments $where");
      $res = self::forPagingData2($page, $on_page, $count);
      if(empty($count)) return $res;
      $res['data'] = $this->fetchAll("SELECT * FROM comments $where
         ORDER BY date DESC LIMIT ?, ?", [($page - 1) * $on_page, $on_page]);
      return $res;
   }

   public function getAll($page, $on_page){
      $count = $this->fetchOne("SELECT COUNT(*) FROM comments");
      $res = self::forPagingData2($page, $on_page, $count);
      if(empty($count)) return $res;
      $res['data'] = $this->fetchAll("SELECT * FROM comments
         ORDER BY active DESC, id DESC LIMIT ?, ?",
         [($page - 1) * $on_page, $on_page]);
      return $res;
   }

   public function addComment($comment){
      $this->insert('comments', [
         'date' => date('c'),
         'text' => $comment,
      ]);
      $model_mail = new Mail($this->_sm);
      $subject = 'Новый отзыв о сайте на Мир Талантов';
      $model_mail->sendView(null, $subject, 'comment', $comment);
   }

   public function delComment($id){
      $id = (int)$id;
      $this->delete('comments', "id = $id");
   }

   public function chActive($id){
      $active = $this->fetchOne("SELECT active FROM comments WHERE id = ?",
         $id);
      if(empty($active)) throw new \Exception('Нет такого коммента');
      $this->update('comments', ['active' => $active == 'yes'? 'no': 'yes'],
         "id = $id");
   }

   public function chText($id, $text){
      $this->update('comments', [
         'text' => $text,
      ], "id = ".(int)$id);
   }
}