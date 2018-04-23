<?php
namespace Application\Model;

class SiteImages extends Base{

   const MAX_BLOG_COUNT = 20;
   private static $_file_sizes = [
      'logo' => '150,150',
      'head' => '1000,150',
      'blog' => [
         '100,100',
         '400,400',
      ],
   ];


   public function addImage($img, $type, $user_id){
      if(empty(self::$_file_sizes[$type]))
         throw new \Exception('Ошибка загрузки');

      if($img['error'] != 0) throw new \Exception('Ошибка загрузки файла');

      if($type == 'blog' && $this->isFullBlogImages($user_id))
         throw new \Exception('Вы превысили максимальное количество картинок 
         в блогах');

      $ext = array_search($img['type'], Images::$_img_mimes);
      if(empty($ext)) throw new \Exception('Неверный тип файла');

      $model_images = new Images();
      $dir = FILES.'/sites/'.$user_id.'/';

      if(is_array(self::$_file_sizes[$type])){
         $name = self::generateCode(7).'.'.$ext;
         list($x, $y) = explode(',', self::$_file_sizes[$type][1]);
         $model_images->createGD($img['tmp_name'], $dir, $name, $x, $y);
         
         $thumb = self::generateCode(7).'.'.$ext;
         list($x, $y) = explode(',', self::$_file_sizes[$type][0]);
         $model_images->createGD($img['tmp_name'], $dir, $thumb, $x, $y);
         
         $this->insert('site_images', [
            'user_id' => $user_id,
            'type' => $type,
            'name' => $name,
            'thumb' => $thumb,
         ]);
      }
      else{
         $name = self::generateCode(7).'.'.$ext;
         list($x, $y) = explode(',', self::$_file_sizes[$type]);
         $model_images->createGD($img['tmp_name'], $dir, $name, $x, $y);
         
         $si = $this->fetchRow("SELECT * FROM site_images
            WHERE user_id = ? AND type = ?", [$user_id, $type]);
         if(empty($si)){
            $this->insert('site_images', [
               'user_id' => $user_id,
               'type' => $type,
               'name' => $name,
            ]
            );
         }
         else{
            $this->update('site_images', ['name' => $name],
               "id = {$si['id']}");
            unlink($dir.$si['name']);
         }
      }
   }

   public function isFullBlogImages($user_id){
      $count = $this->fetchOne("SELECT COUNT(*) FROM site_images
         WHERE user_id = ? AND type = 'blog'", $user_id);
      return (bool) self::MAX_BLOG_COUNT >= $count;
   }

   public function getByUser($user_id){
      $res = $this->fetchAll("SELECT * FROM site_images WHERE user_id = ?",
         $user_id);
      $r = ['logo' => '', 'head' => '', 'blog' => []];
      foreach($res as $i){
         if($i['type'] == 'blog')
            $r['blog'][] = ['name' => $i['name'], 'thumb' => $i['thumb']];
         else $r[$i['type']] = $i['name'];
      }
      return $r;
   }

   public function getByType($type, $user_id){
      return $this->fetchAll("SELECT * FROM site_images WHERE user_id = ?
         AND type = ?", [$user_id, $type]);
   }

   public function getById($id){
      return $this->fetchRow("SELECT * FROM site_images WHERE id = ?", $id);
   }

   public function deleteImage($id){
      $img = $this->getById($id);
      if(empty($img)) return;
      unlink(FILES.'/sites/'.$img['user_id'].'/'.$img['name']);
      if(!empty($img['thumb']))
         unlink(FILES.'/sites/'.$img['user_id'].'/'.$img['thumb']);
      $this->delete('site_images', "id = $id");
   }


}