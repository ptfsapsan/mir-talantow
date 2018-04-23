<?php
namespace Application\Model;

/**
 * Class Thanks
 *
 * @package Application\Model
 */
class Thanks extends Base{

   /**
    *
    */
   const THANK_PRICE = 100;

   /**
    * @var string
    */
   private $table = 'thanks';

   /**
    * @param $page
    * @param $on_page
    *
    * @return array
    */
   public function getAll($page, $on_page){
      $count = $this->fetchOne("SELECT COUNT(*) FROM thanks");
      $res = $this->forPagingData2($page, $on_page, $count);
      if(empty($count)) return $res;

      $res['data'] = $this->fetchAll("SELECT * FROM thanks ORDER BY id DESC
         LIMIT ?, ?", [($page - 1) * $on_page, $on_page]);
      return $res;
   }

   /**
    * @param $params
    *
    * @throws \Exception
    */
   public function addThank($params){
      unset($params['act']);
      unset($params['submit']);
      $params['price'] = self::THANK_PRICE;
      $params['code'] = self::generateCode(7);
      $this->insert($this->table, $params);
      $params['id'] = $this->lastInsertId();
      
      $model_mail = new Mail($this->_sm);
      $subject = 'Добавлена новая заявка на благодарность';
      $model_mail->sendView($params['email'], $subject, 'new_thank', $params);
      $model_mail->sendView(null, $subject, 'new_thank', $params);
   }

   /**
    * @param $id
    */
   public function deleteThank($id){
      $id = (int)$id;
      $this->delete($this->table, "id = $id");
   }
   
   public function getById($id){
      return $this->fetchRow("SELECT * FROM $this->table WHERE id = ?", $id);
   }
   
   public function getThankByCode($code){
      return $this->fetchRow("SELECT * FROM $this->table WHERE code = ?", 
         $code);
   }

   public function saveChanges($params, $id){
      $id = (int)$id;
      unset($params['act']);
      unset($params['submit']);
      $this->update($this->table, $params, "id = $id");
   }
   
   public function sendThank($id){
      $this->update($this->table, [
         'send_date' => date('c'),
      ], "id = $id");
   }

   /**
    * @param $thank
    *
    * @return mixed
    */
   public function getThank($thank){
      $model_blanks = new Blanks($this->_sm);
      $blank = $model_blanks->getBlankById($thank['blank_id']);

      $img = imagecreatefromjpeg(IMG.'/blanks/'.$blank['name']);
      $center_x = round(imagesx($img) / 2);
      $font1 = ROOT_DIR.'/fonts/arial.ttf';
      $font2 = ROOT_DIR.'/fonts/Monotype_Corsiva.ttf';
      $color = imagecolorallocate($img, 0, 0, 0);
      $color2 = imagecolorallocate($img, 150, 150, 150);
      $y = 1250;

      // Благодарность
      $text = 'Благодарность';
      $font_size = 180;
      $x = self::getX($text, $font_size, $font2, $center_x);
      imagettftext($img, $font_size, 0, $x, $y, $color, $font2, $text);

      //
      $text = 'выражается';
      $font_size = 30;
      $x = self::getX($text, $font_size, $font1, $center_x);
      $y += 150;
      imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);

      // имя в дательном падеже
      $text = $thank['name'];
      $font_size = 40;
      $x = self::getX($text, $font_size, $font1, $center_x);
      $y += 100;
      imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);

      // должность в дательном падеже
      if(!empty($thank['post'])){
         // должность
         $text = $thank['post'];
         $font_size = 30;
         $x = self::getX($text, $font_size, $font1, $center_x);
         $y += 50;
         imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);
      }

      // за что
      $text_full = $thank['for_what'];
      $font_size = 50;
      $x = self::getX($text_full, $font_size, $font1, $center_x);
      $y += 150;
      $n = 1;
      if($x >= 500)
         imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text_full);
      else{
         $tt = explode(' ', $text_full);
         $count = count($tt);
         $t = [];
         while($x < 500){
            $n++;
            $t = array_chunk($tt, ceil($count / $n));
            $x = 1000;
            foreach($t as $i){
               $text = implode(' ', $i);
               $xx = self::getX($text, $font_size, $font1, $center_x);
               $x = $xx < $x? $xx: $x;
            }
         }
         foreach($t as $i){
            $text = implode(' ', $i);
            $y += 85;
            $x = self::getX($text, $font_size, $font1, $center_x);
            imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);
         }
      }

      if(!empty($thank['organization_name'])){
         // название организации
         $text = $thank['organization_name'];
         $font_size = 30;
         $x = self::getX($text, $font_size, $font1, $center_x);
         $y += 100;
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
      //
      if(!empty($thank['organization_address'])){
         // адрес организации
         $text = $thank['organization_address'];
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


      //
      if(!empty($thank['title'])){
         // адрес организации
         $text = trim($thank['title'], '"');
         $text = trim($text, "'");
         $text = '"'.$text.'"';
         $font_size = 40;
         $x = self::getX($text, $font_size, $font1, $center_x);
         $y += 70;
         if($x < 500){
            $n = strlen($text) / 2;
            $t = explode(' ', $text);
            $text = '';
            while(strlen($text) < $n) $text .= array_shift($t).' ';
            $x = self::getX($text, $font_size, $font1, $center_x);
            imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);

            $text = implode(' ', $t);
            $y += 50;
            $x = self::getX($text, $font_size, $font1, $center_x);
            imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);
         }
         else{
            imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);
         }
      }


      // печать
      $stamp = imagecreatefrompng(IMG.'/stamp.png');
      $sx = imagesx($stamp);
      $sy = imagesy($stamp);
      imagecopyresized($img, $stamp, 1300, 2400, 0, 0, 400, 400, $sx, $sy);

      // Председатель жюри
      $text = 'Администратор сайта';
      $font_size = 30;
      imagettftext($img, $font_size, 0, 900, 2600, $color, $font1, $text);

      //
      $text = 'Янчук Р.';
      $font_size = 30;
      imagettftext($img, $font_size, 0, 1700, 2600, $color, $font1, $text);

      //
      $text = 'Благодарность № 00'.$thank['id'].' выдан '.
         date('d.m.Y', strtotime($thank['send_date']));
      $font_size = 30;
      $x = self::getX($text, $font_size, $font1, $center_x);
      imagettftext($img, $font_size, 0, $x, 2850, $color, $font1, $text);

      //
      $text = 'Всероссийский центр детского творчества "Мир Талантов"';
      $font_size = 30;
      $x = self::getX($text, $font_size, $font1, $center_x);
      imagettftext($img, $font_size, 0, $x, 2950, $color2, $font1, $text);

      //
      $text = 'http://mir-talantow.ru';
      $font_size = 30;
      $x = self::getX($text, $font_size, $font1, $center_x);
      imagettftext($img, $font_size, 0, $x, 3000, $color2, $font1, $text);

      return $img;
   }

   /**
    * @param $text
    * @param $font_size
    * @param $font
    * @param $center_x
    *
    * @return mixed
    */
   private static function getX($text, $font_size, $font, $center_x){
      $box = imagettfbbox($font_size, 0, $font, $text);
      return $center_x - round(($box[2] - $box[0]) / 2);
   }



}