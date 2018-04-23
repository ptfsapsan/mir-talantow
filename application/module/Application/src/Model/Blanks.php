<?php
namespace Application\Model;

class Blanks extends Base{

   public function uploadBlank($img){
      if($img['error'] != 0) throw new \Exception('Ошибка загрузки файла');

      $sizes = Images::getSizes();
      $dir = IMG.'/blanks/';

      $s = $sizes['blank'];
      $name = self::generateCode(5).'.jpg';
      Images::createGD($img['tmp_name'], $dir, $name, $s[0], $s[1]);

      $s = $sizes['blank_thumb'];
      $thumb = self::generateCode(5).'.jpg';
      Images::createGD($img['tmp_name'], $dir, $thumb, $s[0], $s[1]);

      $this->insert('blanks', [
         'name' => $name,
         'thumb' => $thumb,
      ]);
   }

   public function getAll($active=null){
      $where = $active? "WHERE active = 'yes'": "";
      return $this->fetchAll("SELECT * FROM blanks $where ORDER BY id DESC");
   }

   public function getAllFull($active=null){
      $where = $active? "WHERE active = 'yes'": "";
      return $this->fetchAll("SELECT *,
       (SELECT COUNT(*) FROM orders WHERE blank_id = blanks.id) orders_count
       FROM blanks $where");
   }

   public function changeActive($id){
      $blank = $this->getBlankById($id);
      $this->update('blanks', [
         'active' => $blank['active'] == 'yes'? 'no': 'yes'
      ], "id = ".(int)$id);
   }

   public function getBlankById($id){
      return $this->fetchRow("SELECT * FROM blanks WHERE id = ?", $id);
   }

   public function deleteBlank($id){
      $blank = $this->getBlankById($id);
      unlink(IMG.'/blanks/'.$blank['name']);
      unlink(IMG.'/blanks/'.$blank['thumb']);
      $this->delete('blanks', "id = ".(int)$id);
   }

   public function getDiploma($order){
      $img = imagecreatefromjpeg(IMG.'/blanks/'.$order['blank_name']);
      $center_x = round(imagesx($img) / 2);
      $font1 = ROOT_DIR.'/fonts/arial.ttf';
      $font2 = ROOT_DIR.'/fonts/Monotype_Corsiva.ttf';
      $color = imagecolorallocate($img, 0, 0, 0);
      $color2 = imagecolorallocate($img, 150, 150, 150);
      $y = 1200;

      // диплом
      $text = 'Диплом';
      $font_size = 200;
      $x = self::getX($text, $font_size, $font2, $center_x);
      imagettftext($img, $font_size, 0, $x, $y, $color, $font2, $text);

      // награждается
      $text = 'награждается';
      $font_size = 30;
      $x = self::getX($text, $font_size, $font1, $center_x);
      $y += 200;
      imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);

      // исполнитель
      $text = $order['executor_name'];
      $font_size = 40;
      $x = self::getX($text, $font_size, $font1, $center_x);
      $y += 100;
      imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);


      if(!empty($order['executor_age']) &&
         !empty($text = intval($order['executor_age']))){
         // возраст
         $text .= $text == 1? ' год': ($text > 4? ' лет': ' года');
         $font_size = 30;
         $x = self::getX($text, $font_size, $font1, $center_x);
         $y += 50;
         imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);
      }

      if(!empty($order['post'])){
         // должность
         $text = $order['post'];
         $font_size = 30;
         $x = self::getX($text, $font_size, $font1, $center_x);
         $y += 50;
         imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);
      }

      if(!empty($order['organization_name'])){
         // название организации
         $text = $order['organization_name'];
         $font_size = 30;
         $x = self::getX($text, $font_size, $font1, $center_x);
         $y += 150;
         if($x < 500){
            $t = explode(' ', $text);
            $t = array_chunk($t, count($t) / 2);
            $text = implode(' ', $t[0]);
            $x = self::getX($text, $font_size, $font1, $center_x);
            imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);
            $text = implode(' ', $t[1]);
            $x = self::getX($text, $font_size, $font1, $center_x);
            $y += 50;
            imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);
         }
         else{
            imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);
         }
      }

      if(!empty($order['organization_address'])){
         // адрес организации
         $text = $order['organization_address'];
         $font_size = 30;
         $x = self::getX($text, $font_size, $font1, $center_x);
         $y += 50;
         if($x < 500){
            $t = explode(' ', $text);
            $t = array_chunk($t, count($t) / 2);
            $text = implode(' ', $t[0]);
            $x = self::getX($text, $font_size, $font1, $center_x);
            imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);
            $text = implode(' ', $t[1]);
            $y += 50;
            $x = self::getX($text, $font_size, $font1, $center_x);
            imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);
         }
         else{
            imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);
         }
      }

      if(!empty($order['chief_name'])){
         // руководитель
         $text = 'Руководитель: '.$order['chief_name'];
         $font_size = 30;
         $x = self::getX($text, $font_size, $font1, $center_x);
         $y += 50;
         imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);
      }

      // результат
      switch($order['result']){
         case 1: $text = '1 место'; break;
         case 2: $text = '2 место'; break;
         case 3: $text = '3 место'; break;
         default: $text = 'дипломант';
      }
      $font_size = 60;
      $x = self::getX($text, $font_size, $font1, $center_x);
      $y += 150;
      imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);

      // Всероссийского конкурса
      $text = 'Всероссийского конкурса';
      $font_size = 30;
      $x = self::getX($text, $font_size, $font1, $center_x);
      $y += 100;
      imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);

      if(!empty($order['theme_title'])){
         // тема конкурса
         $text = '"'.$order['theme_title'].'"';
         $font_size = 30;
         $x = self::getX($text, $font_size, $font1, $center_x);
         $y += 50;
         imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);
      }

      // номинация
      $text = 'в номинации "'.$order['nomination_title'].'"';
      $font_size = 30;
      $x = self::getX($text, $font_size, $font1, $center_x);
      $y += 50;
      imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);

      // название работы
      $text = 'Название работы:';
      $font_size = 30;
      $x = self::getX($text, $font_size, $font1, $center_x);
      $y += 50;
      imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);

      // название работы
      $text = trim($order['work_title'], '"');
      $text = trim($text, "'");
      $text = '"'.$text.'"';
      $font_size = 30;
      $x = self::getX($text, $font_size, $font1, $center_x);
      $y += 50;
      if($x < 500){
         $text = trim($text, '"');
         $t = explode(' ', $text);
         $t = array_chunk($t, count($t) / 2);
         $text = '"'.implode(' ', $t[0]);
         $x = self::getX($text, $font_size, $font1, $center_x);
         imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);
         $text = implode(' ', $t[1]).'"';
         $x = self::getX($text, $font_size, $font1, $center_x);
         $y += 50;
         imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);
      }
      else imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);

      // печать
      $stamp = imagecreatefrompng(IMG.'/stamp.png');
      $sx = imagesx($stamp);
      $sy = imagesy($stamp);
      imagecopyresized($img, $stamp, 1300, 2400, 0, 0, 400, 400, $sx, $sy);

      // Председатель жюри
      $text = 'Председатель жюри';
      $font_size = 30;
      imagettftext($img, $font_size, 0, 900, 2600, $color, $font1, $text);

      //
      $text = 'Янчук Р.';
      $font_size = 30;
      imagettftext($img, $font_size, 0, 1700, 2600, $color, $font1, $text);

      // диплом
      $text = 'Диплом № 00'.$order['id'].' выдан '.
         date('d.m.Y', strtotime($order['send_date']));
      $font_size = 30;
      $x = self::getX($text, $font_size, $font1, $center_x);
      imagettftext($img, $font_size, 0, $x, 2850, $color, $font1, $text);

      // организатор
      $text = 'Всероссийский центр детского творчества "Мир Талантов"';
      $font_size = 30;
      $x = self::getX($text, $font_size, $font1, $center_x);
      imagettftext($img, $font_size, 0, $x, 2950, $color2, $font1, $text);

      //
      $text = 'https://mir-talantow.ru';
      $font_size = 30;
      $x = self::getX($text, $font_size, $font1, $center_x);
      imagettftext($img, $font_size, 0, $x, 3000, $color2, $font1, $text);


      return $img;
   }

   private static function getX($text, $font_size, $font, $center_x){
      $box = imagettfbbox($font_size, 0, $font, $text);
      return $center_x - round(($box[2] - $box[0]) / 2);
   }

}