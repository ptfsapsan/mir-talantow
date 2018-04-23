<?php
namespace Application\Model;

use Zend\Session\SessionManager;

class TempFiles extends Base{
   
   const FILE_MAX_SIZE = 8000000;
   
   public function getFiles($phpsessid=null){
      if(empty($phpsessid)){
         $sess_manager = new SessionManager();
         $phpsessid = $sess_manager->getId();
      }
      $model_auth = new Auth($this->_sm);
      $where = $model_auth->isEmpty()?
         "WHERE phpsessid = '$phpsessid'":
         "WHERE user_id = ".$model_auth->getUserId();

      return $this->fetchAll("SELECT * FROM `temp_files` $where ORDER BY id");
   }

   public function addFile($file){
      if($file['error'] != 0) throw new \Exception('Ошибка загрузки файла');
      $is_img = strpos($file['type'], 'image/') !== false;
      if(!$is_img && $file['size'] > self::FILE_MAX_SIZE)
         throw new \Exception('Размер файла не может превышать '.
            self::FILE_MAX_SIZE. ' байт');
      $ext = Images::getExt($file['type'], $file['name']);
      if(empty($ext)) throw new \Exception('Недопустимый тип файла');

      $name = Files::generateCode().'.'.$ext;
      $d = [
         'date' => date('Y-m-d'),
         'mime_type' => $file['type'],
         'name' => $name,
         'size' => $file['size'],
         'old_name' => $file['name'],
      ];

      $model_auth = new Auth($this->_sm);
      $is_auth = !$model_auth->isEmpty();
      if($is_auth){
         $user_id = $model_auth->getUserId();
         $dir = FILES.'/temp_files/'.$user_id.'/';
         $d['user_id'] = $user_id;
      }
      else{
         $sess_manager = new SessionManager();
         $phpsessid = $sess_manager->getId();
         $dir = FILES.'/temp_files/'.$phpsessid.'/';
         $d['phpsessid'] = $phpsessid;
      }
      if(!file_exists($dir) || !is_dir($dir)) mkdir($dir, 0777, true);


      if($is_img){
         $model_images = new Images();
         $model_images->createGD($file['tmp_name'], $dir, $name, 700, 700, true);
         $thumb = Files::generateCode().'.'.$ext;
         $model_images->createGD($file['tmp_name'], $dir, $thumb, 120, 120);
         $d['thumb'] = $thumb;
      }
      else{
         copy($file['tmp_name'], $dir.$name);
      }

      $this->insert('temp_files', $d);
   }

   public function delFile($id){
      $phpsessid = session_id();
      $file = $this->fetchRow("SELECT * FROM temp_files
         WHERE phpsessid = ? AND id = ?",
         [$phpsessid, $id]);
      if(empty($file)) throw new \Exception('Нет такого файла');

      $this->delete('temp_files', "id = $id");
      unlink(FILES.'/temp_files/'.$phpsessid.'/'.$file['name']);
      if(!empty($file['thumb']))
         unlink(FILES.'/temp_files/'.$phpsessid.'/'.$file['thumb']);
   }
   
   public function getById($id){
      return $this->fetchRow("SELECT * FROM temp_files WHERE id = ?", $id);
   }
}