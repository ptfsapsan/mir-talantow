<?php
namespace Application\Model;

use PHPQRCode\QRcode;

/**
 * Class Certificates
 *
 * @package Application\Model
 */
class Certificates extends Base{

   /**
    * @param $img
    *
    * @throws \Exception
    */
   public function uploadCertificate($img){
      if($img['error'] != 0) throw new \Exception('Ошибка загрузки файла');

      $sizes = Images::getSizes();
      $dir = IMG.'/certificates/';

      $s = $sizes['certificate'];
      $name = self::generateCode(5).'.jpg';
      Images::createGD($img['tmp_name'], $dir, $name, $s[0], $s[1]);

      $s = $sizes['certificate_thumb'];
      $thumb = self::generateCode(5).'.jpg';
      Images::createGD($img['tmp_name'], $dir, $thumb, $s[0], $s[1]);

      $this->insert('certificates', [
         'name' => $name,
         'thumb' => $thumb,
      ]);
   }

   /**
    * @param null $active
    *
    * @return array
    */
   public function getAll($active=null){
      $where = $active? "WHERE active = 'yes'": "";
      return $this->fetchAll("SELECT * FROM certificates $where");
   }

   /**
    * @param null $active
    *
    * @return array
    */
   public function getAllFull($active=null){
      $where = $active? "WHERE active = 'yes'": "";
      return $this->fetchAll("SELECT *,
       (SELECT COUNT(*) FROM articles WHERE certificate_id = certificates.id) 
          article_count
       FROM certificates $where");
   }

   /**
    * @param $id
    */
   public function changeActive($id){
      $blank = $this->getById($id);
      $this->update('certificates', [
         'active' => $blank['active'] == 'yes'? 'no': 'yes'
      ], "id = ".(int)$id);
   }

   /**
    * @param $id
    *
    * @return array|bool
    */
   public function getById($id){
      return $this->fetchRow("SELECT * FROM certificates WHERE id = ?", $id);
   }

   /**
    * @param $id
    */
   public function deleteCertificate($id){
      $blank = $this->getById($id);
      unlink(IMG.'/certificates/'.$blank['name']);
      unlink(IMG.'/certificates/'.$blank['thumb']);
      $this->delete('certificates', "id = ".(int)$id);
   }

   /** Отправляем сертификат автору
    *
    * @param $article_id
    *
    * @throws \Exception
    */
   public function sendCertificate($article_id){
      $article = $this->fetchRow("SELECT * FROM articles
         WHERE id = ? AND status = 'accepted' AND send_date IS NULL",
         $article_id);
      if(empty($article))
         throw new \Exception('Нет такой подтвержденной статьи');

      $code = '';
      $f = true;
      while($f){
         $code = strtolower(self::generateCode(10));
         $f = (bool) $this->fetchOne("SELECT code FROM articles
            WHERE code = ?", $code);
      }
      $this->update('articles', [
         'code' => $code,
         'status' => 'accepted',
         'send_date' => date('Y-m-d H:i:s'),
      ], "id = $article_id");

      $model_mail = new Mail($this->_sm);
      $model_mail->sendCertificate($article, $code);
   }

   public function getCertificate($article){
      $theme = $this->fetchRow("SELECT * FROM article_themes
         WHERE id = ?", $article['article_theme_id']);

      $certificate = $this->getById($article['certificate_id']);
      $img = imagecreatefromjpeg(IMG.'/certificates/'.
         $certificate['name']);
      $center_x = round(imagesx($img) / 2);
      $font1 = ROOT_DIR.'/fonts/arial.ttf';
      $font2 = ROOT_DIR.'/fonts/Monotype_Corsiva.ttf';
      $color = imagecolorallocate($img, 0, 0, 0);
      $color2 = imagecolorallocate($img, 150, 150, 150);
      $y = 1250;

      // Сертификат
      $text = 'Сертификат';
      $font_size = 200;
      $x = self::getX($text, $font_size, $font2, $center_x);
      imagettftext($img, $font_size, 0, $x, $y, $color, $font2, $text);

      //
      $text = 'о публикации статьи';
      $font_size = 30;
      $x = self::getX($text, $font_size, $font1, $center_x);
      $y += 150;
      imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);

      //
      $text = 'Настоящим подтверждается, что';
      $font_size = 40;
      $x = self::getX($text, $font_size, $font1, $center_x);
      $y += 100;
      imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);

      // автор статьи
      $text = $article['name'];
      $font_size = 60;
      $x = self::getX($text, $font_size, $font1, $center_x);
      $y += 100;
      imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);


//
      if(!empty($article['post'])){
         // должность
         $text = $article['post'];
         $font_size = 30;
         $x = self::getX($text, $font_size, $font1, $center_x);
         $y += 50;
         imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);
      }
//
      if(!empty($article['organization_name'])){
         // название организации
         $text = $article['organization_name'];
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
      if(!empty($article['organization_address'])){
         // адрес организации
         $text = $article['organization_address'];
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
      $text = 'опубликовал(а) статью на сайте "Мир Талантов" на тему:';
      $font_size = 40;
      $x = self::getX($text, $font_size, $font1, $center_x);
      $y += 100;
      imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);

      //
      $text = '"'.$theme['title'].'"';
      $font_size = 40;
      $x = self::getX($text, $font_size, $font1, $center_x);
      $y += 70;
      imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);

      //
      $text = 'Название статьи:';
      $font_size = 40;
      $x = self::getX($text, $font_size, $font1, $center_x);
      $y += 120;
      imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);

      //
      if(!empty($article['title'])){
         // адрес организации
         $text = trim($article['title'], '"');
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

      //
      $text = 'Web-адрес статьи:';
      $font_size = 30;
      $x = self::getX($text, $font_size, $font1, $center_x);
      $y += 100;
      imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);

      //
      $router = $this->_sm->get('router');
      $url = new \Zend\View\Helper\Url();
      $url->setRouter($router);
      $text = DOMAIN.$url('article',
         ['theme' => $theme['trans'], 'id' => $article['id']]);
      $font_size = 30;
      $x = self::getX($text, $font_size, $font1, $center_x);
      $y += 50;
      imagettftext($img, $font_size, 0, $x, $y, $color, $font1, $text);

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

      // диплом
      $text = 'Сертификат № 00'.$article['id'].' выдан '.
         date('d.m.Y', strtotime($article['send_date']));
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

      // qr
      QRcode::png(DOMAIN.$url('article',
            ['theme' => $theme['trans'], 'id' => $article['id']]),
         IMG.'/certificates/qr_'.$certificate['name'], 'L', 3, 2);
      $qr = imagecreatefrompng(IMG.'/certificates/qr_'.$certificate['name']);
      $sx = imagesx($qr);
      $sy = imagesy($qr);
      imagecopyresized($img, $qr, 500, 2400, 0, 0, 300, 300, $sx, $sy);
      imagedestroy($qr);

      return $img;
   }

   private static function getX($text, $font_size, $font, $center_x){
      $box = imagettfbbox($font_size, 0, $font, $text);
      return $center_x - round(($box[2] - $box[0]) / 2);
   }



}