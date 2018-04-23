<?php
namespace Application\Model;


class SitePages extends Base{

   private $table = 'site_pages';


   public function getPageByType($type, $user_id, $article_id = 0){
      if(in_array($type, ['index']) || !empty($article_id)){
         $article_id = (int)$article_id;
         $w = empty($article_id)? '': "AND id = $article_id";

         return $this->fetchRow("SELECT * FROM site_pages WHERE user_id = ?
         AND type = ? $w", [$user_id, $type]);
      }
      else{
         return $this->fetchAll("SELECT * FROM site_pages WHERE user_id = ?
         AND type = ? ", [$user_id, $type]
         );
      }
   }

   public function getAllBlogPages(){
      return $this->fetchAll("SELECT *,
         (SELECT site_code FROM settings WHERE user_id = t.user_id) site_code 
         FROM $this->table t WHERE `type` = 'blog' AND active = 'yes'");
   }

   public function savePage($params, $type, $user_id, $article_id = 0){
      $data = [
         'user_id' => $user_id,
         'type' => $type,
         'title' => $params['title'],
         'text' => $params['text'],
      ];
      $page = in_array($type, ['index'])?
         $this->fetchRow("SELECT * FROM site_pages
            WHERE user_id = ? AND type = ?", [$user_id, $type]):
         $this->fetchRow("SELECT * FROM site_pages
            WHERE user_id = ? AND type = ? AND id = ?",
            [$user_id, $type, $article_id]);
      if(empty($page)) $this->insert('site_pages', $data);
      else $this->update('site_pages', $data, "id = {$page['id']}");

   }
   
   public function deletePage($id){
      $id = (int)$id;
      $this->delete('site_pages', "id = $id");
   }
   
   public function getById($id){
      return $this->fetchRow("SELECT * FROM site_pages WHERE id = ?", $id);
   }

   public function getCommentsByPage($id){
      return $this->fetchAll("SELECT * FROM site_page_comments
         WHERE site_page_id = ? AND active = 'yes'", $id);
   }

   public function getCommentsByUserId($user_id){
      return $this->fetchAll("SELECT spc.*, sp.title
         FROM site_page_comments spc
         LEFT JOIN site_pages sp ON sp.id = spc.site_page_id
         WHERE sp.user_id = ? ORDER BY spc.date DESC",
         $user_id);
   }

   public function addComment($params, $id){
      $this->insert('site_page_comments', [
         'site_page_id' => $id,
         'name' => $params['name'],
         'text' => $params['message'],
      ]);
   }

   public function changeActive($article_id, $active){
      $article_id = (int)$article_id;
      $active = $active == 'yes'? 'yes': 'no';
      $this->update('site_pages', ['active' => $active], "id = $article_id");
   }

   public function changeCommentActive($comment_id, $active){
      $comment_id = (int)$comment_id;
      $active = $active == 'yes'? 'yes': 'no';
      $this->update('site_page_comments', ['active' => $active],
         "id = $comment_id");
   }

   public function deleteComment($comment_id, $user_id){
      $site_page = $this->fetchRow("SELECT * FROM site_pages WHERE 
         id = (SELECT site_page_id FROM site_page_comments WHERE id = ?)",
         $comment_id);
      if($site_page['user_id'] != $user_id) return;
      $this->delete('site_page_comments', "id = $comment_id");
   }


}