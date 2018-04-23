<?php
namespace Application\Model;

/**
 * Class Articles
 *
 * @package Application\Model
 */
class Articles extends Base{

   /**
    * стоимость сертификата
    */
   const PRICE = 0;

   /**
    * стоимость отправки оригинала сертификата
    */
   const ORIGINAL_PRICE = 300;


   /**
    * @return array
    */
   public function getAllThemes(){
      return $this->fetchAll("SELECT * FROM article_themes ORDER BY title");
   }

   /**
    * @param $id
    */
   public function delTheme($id){
      $this->delete('article_themes', "id = ".(int)$id);
   }

   /**
    * @param $params
    */
   public function addTheme($params){
      $this->insert('article_themes', [
         'title' => $params['title'],
         'trans' => self::translit($params['title']),
         'description' => $params['description'],
      ]);
   }

   /**
    * @param $params
    */
   public function changeThemeTitle($params){
      $this->update('article_themes', [
         'title' => $params['title'],
         'trans' => self::translit($params['title']),
         'description' => $params['description'],
      ], "id = ".(int)$params['id']);
   }

   /**
    * @param $trans
    *
    * @return array|bool
    */
   public function getThemeByTrans($trans){
      return $this->fetchRow("SELECT * FROM article_themes WHERE trans = ?",
         $trans);
   }

   /**
    * @param $id
    *
    * @return array|bool
    */
   public function getArticleById($id){
      return $this->fetchRow("SELECT *,
         (SELECT COUNT(*) FROM article_comments WHERE article_id = articles.id
            AND status = 'accepted') comment_count,
         (SELECT trans FROM article_themes WHERE id = articles
         .article_theme_id) 
            theme_trans
         FROM articles WHERE id = ? AND status = 'accepted'", $id);
   }

   /**
    * @param $id
    *
    * @return array|bool
    */
   public function getAdminArticleById($id){
      return $this->fetchRow("SELECT * FROM articles WHERE id = ?", $id);
   }



   /**
    * @param $theme_id
    * @param $page
    * @param $on_page
    *
    * @return array
    */
   public function getArticlesByThemeId($theme_id, $page, $on_page){
      $theme_id = (int)$theme_id;
      $where = "WHERE status = 'accepted'";
      if(!empty($theme_id)) $where .= " AND article_theme_id = $theme_id";
      $count = $this->fetchOne("SELECT COUNT(*) FROM articles $where");
      $res = ['data' => [], 'count' => $count, 'page' => $page,
         'on_page' => $on_page];
      if(empty($count)) return $res;

      $res['data'] = $this->fetchAll("SELECT *,
       (SELECT trans FROM article_themes WHERE id = articles.article_theme_id)
            theme_trans
       FROM articles $where ORDER BY id DESC LIMIT ?, ?",
         [($page - 1) * $on_page, $on_page]);
      return $res;
   }

   /**
    * @return array
    */
   public function getArticlesForSitemap(){
      return $this->fetchAll("SELECT *,
       (SELECT trans FROM article_themes WHERE id = articles.article_theme_id)
            theme_trans
       FROM articles WHERE status = 'accepted'");
   }

   /** добавляем статью
    *
    * @param $params
    */
   public function addArticle($params){
      unset($params['act']);
      unset($params['submit']);
      $params['trans'] = self::translit($params['title']);
      $discount = $this->fetchOne("SELECT COUNT(*) FROM articles 
         WHERE paid > 0 AND email = ?", $params['email']);
      if($discount > 20) $discount = 20;
      $params['discount'] = $discount;
      $params['price'] = self::PRICE * (100 - $discount) / 100;
      $this->insert('articles', $params);

      $model_mail = new Mail($this->_sm);
      $subject = 'Новая статья на сайте Мир Талантов';
      $model_mail->sendView($params['email'], $subject, 'add_article', $params);
      // админу
      $model_mail->sendView(null, $subject, 'add_article', $params);
   }
   
   public function adminAddArticle($params){
      unset($params['act']);
      unset($params['submit']);
      $params['trans'] = self::translit($params['title']);
      $discount = $this->fetchOne("SELECT COUNT(*) FROM articles 
         WHERE paid > 0 AND email = ?", $params['email']);
      if($discount > 20) $discount = 20;
      $params['discount'] = $discount;
      $params['price'] = self::PRICE * (100 - $discount) / 100 +
         ($params['original'] == 'yes'? self::ORIGINAL_PRICE: 0);
      $this->insert('articles', $params);
      return $this->lastInsertId();
   }
   
   

   public function editArticle($params, $id){
      $id = (int)$id;
      unset($params['act']);
      unset($params['submit']);
      $params['trans'] = self::translit($params['title']);
      $this->update('articles', $params, "id = $id");
   }




   /**
    * @return array
    */
   public function getAllArticles(){
      return $this->fetchAll("SELECT * FROM articles ORDER BY id DESC");
   }

   /**
    * @param $trans
    *
    * @return array|bool
    */
   public function getArticleByTrans($trans){
      return $this->fetchRow("SELECT *, 
         (SELECT COUNT(*) FROM article_comments WHERE article_id = articles.id
            AND status = 'accepted') comment_count
         FROM articles WHERE trans = ?", $trans);
   }

   /** получаем коментарии статьи
    * 
    * @param $id
    *
    * @return array
    */
   public function getCommentById($id){
      return $this->fetchAll("SELECT * FROM article_comments
         WHERE article_id = ? AND status = 'accepted'", $id);
   }

   public function addComment($params){
      $this->insert('article_comments', [
         'article_id' => $params['article_id'],
         'name' => $params['name'],
         'text' => $params['text'],
      ]);
   }
   
   public function editComment($params, $id){
      $this->update('article_comments', [
         'name' => $params['name'],
         'text' => $params['text'],
         'status' => $params['status'],
      ], "id = $id");
   }

   public function getAllComments(){
      return $this->fetchAll("SELECT *,
         (SELECT title FROM articles WHERE id = article_comments.article_id)
            article_name
       FROM article_comments ORDER BY id DESC");
   }

   public function getArticleCommentById($id){
      return $this->fetchRow("SELECT *,
         (SELECT title FROM articles WHERE id = article_comments.article_id)
            article_name
         FROM article_comments WHERE id = ?", $id);
   }

   public function getArticleByCode($code){
      return $this->fetchRow("SELECT *, 
       (SELECT trans FROM article_themes WHERE id = articles.article_theme_id)
            theme_trans
         FROM articles WHERE code = ?", $code);
   }

   public function delArticle($id){
      $this->delete('articles', "id = ".(int)$id);
   }

}