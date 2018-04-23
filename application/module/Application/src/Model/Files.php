<?php
namespace Application\Model;

use Zend\ServiceManager\ServiceManager;
use Zend\Session\Container;

class Files extends Base{

   public function __construct(ServiceManager $sm){
      parent::__construct($sm);
   }


   public function getFilesForGallery($params){
      $page = (int)$params['page'];
      $on_page = (int)$params['on_page'];
      $type = $params['type'];
      $where = "WHERE in_gallery = 'yes' AND 
         (SELECT `type` FROM orders WHERE id = f.order_id) = '$type'";
      if(!empty($params['nomination'])){
         $nomination = (int)$params['nomination'];
         $where .= " AND (SELECT nomination_id FROM orders
            WHERE id = f.order_id) = $nomination";
      }
      if(!empty($params['theme'])){
         $theme = (int)$params['theme'];
         $where .= " AND (SELECT theme_id FROM orders
            WHERE id = f.order_id) = $theme";
      }

      $count = $this->fetchOne("SELECT COUNT(*) FROM files f $where");
      $res = self::forPagingData2($page, $on_page, $count);
      if(empty($count)) return $count;

      $res['data'] = $this->fetchAll("SELECT f.*, o.code order_code, o.result
         FROM files f
          LEFT JOIN orders o ON o.id = f.order_id
          $where
         ORDER BY id DESC LIMIT ?, ?",
         [($page - 1) * $on_page, $on_page]);
      return $res;
   }

   public function getFileById($id){
      return $this->fetchRow("SELECT * FROM files WHERE id = ?", $id);
   }
   
   public function getFilesByOrderId($order_id){
      return $this->fetchAll("SELECT * FROM files WHERE order_id = ?",
         $order_id);
   }
   
   public function deleteFromGallery($id){
      $this->update('files', ['in_gallery' => 'no'], "id = ".(int)$id);
   }
   
   public function rotateImage($id, $angle){
      $file = $this->getFileById($id);
      $model_images = new Images();
      $model_images->rotateImage($file, $angle);
   }

   public function incrementViews($id){
      $id = (int)$id;
      if(empty($id)) return 0;
      $session = new Container('views');
      if(empty($session->img_views)) $session->img_views = [];
      $views = $this->fetchOne("SELECT views FROM files WHERE id = ?", $id);
      if(!in_array($id, $session->img_views)){
         $session->img_views[] = $id;
         $views++;
         $this->update('files', [
            'views' => $views,
         ], "id = $id");
      }
      return $views;
   }


}