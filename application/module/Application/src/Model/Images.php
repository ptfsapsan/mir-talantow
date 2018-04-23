<?

namespace Application\Model;


/**
 * Class Images
 *
 * @package Application\Model
 */
class Images{

   /**
    * @var array
    */
   private static $_sizes = [
      'blank' => [2480, 3500],
      'blank_thumb' => [100, 150],
      'certificate' => [2480, 3500],
      'certificate_thumb' => [100, 150],
      'image' => [700, 700],
      'image_thumb' => [120, 120],
   ];

   /**
    * @var array
    */
   private static $_mimes = [
      'pdf' => 'application/pdf',
      'zip' => 'application/zip',
      'gzip' => 'application/gzip',
      'mp3' => 'audio/mp3',
      'mp4' => 'audio/mp4',
      'mpeg' => 'audio/mpeg',
      'wma' => 'audio/x-ms-wma',
      'jpg' => 'image/jpeg',
      'jpeg' => 'image/pjpeg',
      'gif' => 'image/gif',
      'png' => 'image/png',
      'tiff' => 'image/tiff',
      'txt' => 'text/plain',
      'mp4v' => 'video/mp4',
      'mpegv' => 'video/mpeg',
      'wmv' => 'video/x-ms-wmv',
      'fly' => 'video/x-fly',
      '3gpp' => 'video/3gpp',
      'xls' => 'application/vnd.ms-excel',
      'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'ppt' => 'application/vnd.ms-powerpoint',
      'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
      'doc' => 'application/msword',
      'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
   ];
   
   public static $_img_mimes = [
      'jpg' => 'image/jpeg',
      'jpeg' => 'image/pjpeg',
      'gif' => 'image/gif',
      'png' => 'image/png',
      'tiff' => 'image/tiff',
   ];

   /**
    * @var array
    */
   private static $_icons = [
      'pdf' => 'pdf.png',
      'zip' => 'zip.png',
      'gzip' => 'zip.png',
      'mp3' => 'audio.png',
      'mp4' => 'audio.png',
      'mpeg' => 'audio.png',
      'wma' => 'audio.png',
      'jpg' => 'img.png',
      'jpeg' => 'img.png',
      'gif' => 'img.png',
      'png' => 'img.png',
      'tiff' => 'img.png',
      'txt' => 'text.png',
      'mp4v' => 'video.png',
      'mpegv' => 'video.png',
      'wmv' => 'video.png',
      'fly' => 'video.png',
      '3gpp' => 'video.png',
      'xls' => 'xlsx.png',
      'xlsx' => 'xlsx.png',
      'ppt' => 'pptx.png',
      'pptx' => 'pptx.png',
      'doc' => 'docx.png',
      'docx' => 'docx.png',
   ];


   /**
    * @return array
    */
   public static function getSizes(){
      return self::$_sizes;
   }

   /**
    * @param $img
    * @param $x
    * @param $y
    *
    * @return mixed
    */
   public static function imageResize($img, $x, $y){
      $to = imagecreatetruecolor($x, $y);
      imagecopyresampled($to, $img, 0, 0, 0, 0, $x, $y, imagesx($img), imagesy($img));
      imagedestroy($img);
      return $to;
   }


   /**
    * @param $dir
    *
    * @return array
    */
   public static function getImagesByDir($dir){
      $res = [];
      $imgs = glob(IMG.$dir.'*.*');
      if(count($imgs)) foreach($imgs as $i)
         $res[] = '/images'.$dir.basename($i);
      return $res;
   }

   /**
    * @param $id
    *
    * @return string
    */
   public static function getDirById($id){
      $dir = '/';
      while($id >= 100){
         $dir = '/'.($id % 100).$dir;
         $id = floor($id / 100);
      }

      return '/'.$id.$dir;
   }


   // фото с сохранением пропорций, размеры не более, чем x и y
   /**
    * @param $img
    * @param $dir
    * @param $name
    * @param $x
    * @param $y
    */
   public static function createIM($img, $dir, $name, $x, $y){
      if(!file_exists($dir) || !is_dir($dir)) mkdir($dir, 0777, true);
      $im = new \Imagick($img['tmp_name']);
      $sx = $im->getImageWidth();
      $sy = $im->getImageHeight();
      $sp = $sx / $sy;
      if($sp > $x / $y){   // горизонтальная
         $dy = $x / $sp;
         $dx = $x;
      }
      else{                // вертикальная
         $dx = $y * $sp;
         $dy = $y;
      }

      $im->thumbnailImage($dx, $dy);
      $im->writeImage($dir.$name);
      $im->clear();
      $im->destroy();
   }

   /**
    * @param      $img
    * @param      $dir
    * @param      $name
    * @param      $x
    * @param      $y
    * @param bool $watermark
    */
   public static function createGD($img, $dir, $name, $x, $y, $watermark=false){
      if(!file_exists($dir) || !is_dir($dir)) mkdir($dir, 0777, true);

      list($sx, $sy, $type, $attr) = @getimagesize($img);
      $from = $type == 6? self::ImageCreateFromBMP($img): // bmp
         imagecreatefromstring(file_get_contents($img)); // все остальные
      $sp = $sx / $sy;

      if($x == 0) $x = $y * $sp;
      if($y == 0) $y = $x / $sp;


      if($sp > $x / $y){
         $dy = $x / $sp;
         $dx = $x;
      }
      else{
         $dx = $y * $sp;
         $dy = $y;
      }

      $to = imagecreatetruecolor($dx, $dy);
      imagecopyresampled($to, $from, 0, 0, 0, 0, $dx, $dy, $sx, $sy);
      imagedestroy($from);
      // ставим водяной знак
      if($watermark){
         $wm = imagecreatefrompng(FILES.'/watermark.png');
         $w = imagecreatetruecolor($dx, $dy);
         imagecopyresized($w, $wm, 0, 0, 0, 0, $dx, $dy, imagesx($wm),
            imagesy($wm));
         imagefilter($w, IMG_FILTER_BRIGHTNESS, 100);
         imagecopymerge($to, $w, 0, 0, 0, 0, $dx, $dy, 10);
         imagedestroy($wm);
      }

      imagejpeg($to, $dir.$name, 100);
      imagedestroy($to);
   }

   public function rotateImage($file, $angle){
      $name = $file['name'];
      $thumb = $file['thumb'];
      $dir = FILES.$file['dir'];
      $img = imagecreatefromstring(file_get_contents($dir.$thumb));
      $color = imagecolorallocate($img, 0, 0, 0);

      imagerotate($img, $angle, $color);
      unlink($dir.$thumb);
      imagejpeg($img, $dir.$thumb, 100);
      imagedestroy($img);
   }

   // удаляет папку со всем ее содержимым
   /**
    * @param $dir
    */
   public static function removeDir($dir){
      if(!file_exists($dir)) return;
      if(!is_dir($dir)) return;
      if($objs = glob($dir."/*"))
         foreach($objs as $obj) is_dir($obj)? self::removeDir($obj):
            unlink($obj);
      rmdir($dir);
   }

   /**
    * @param      $mime
    * @param null $name
    *
    * @return bool
    */
   public static function getExt($mime, $name=null){
      $res = array_search($mime, self::$_mimes);
      if($res) return $res;

      $ext = pathinfo($name, PATHINFO_EXTENSION);
      if(in_array($ext, array_keys(self::$_mimes))) return $ext;
      return false;
   }

   /**
    * @param $name
    *
    * @return bool|mixed
    */
   public static function getIcon($name){
      $ext = pathinfo($name, PATHINFO_EXTENSION);
      return empty(self::$_icons[$ext])? false: self::$_icons[$ext];
   }


   /** создаем картинку для GD из bmp
    *
    * @param $filename
    *
    * @return mixed
    */
   public static function ImageCreateFromBMP($filename){
      //Ouverture du fichier en mode binaire
      if (! $f1 = fopen($filename, "rb")) die('1');

      //1 : Chargement des ent&#65533;tes FICHIER
      $FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1,14));
      if ($FILE['file_type'] != 19778) die('2');

      //2 : Chargement des ent&#65533;tes BMP
      $BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel'.
         '/Vcompression/Vsize_bitmap/Vhoriz_resolution'.
         '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1,40));
      $BMP['colors'] = pow(2,$BMP['bits_per_pixel']);
      if ($BMP['size_bitmap'] == 0) $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
      $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel']/8;
      $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
      $BMP['decal'] = ($BMP['width']*$BMP['bytes_per_pixel']/4);
      $BMP['decal'] -= floor($BMP['width']*$BMP['bytes_per_pixel']/4);
      $BMP['decal'] = 4-(4*$BMP['decal']);
      if ($BMP['decal'] == 4) $BMP['decal'] = 0;

      //3 : Chargement des couleurs de la palette
      $PALETTE = array();
      if ($BMP['colors'] < 16777216)
      {
         $PALETTE = unpack('V'.$BMP['colors'], fread($f1,$BMP['colors']*4));
      }

      //4 : Cr&#65533;ation de l'image
      $IMG = fread($f1,$BMP['size_bitmap']);
      $VIDE = chr(0);

      $res = imagecreatetruecolor($BMP['width'],$BMP['height']);
      $P = 0;
      $Y = $BMP['height']-1;
      while ($Y >= 0)
      {
         $X=0;
         while ($X < $BMP['width'])
         {
            if ($BMP['bits_per_pixel'] == 24)
               $COLOR = unpack("V",substr($IMG,$P,3).$VIDE);
            elseif ($BMP['bits_per_pixel'] == 16)
            {
               $COLOR = unpack("n",substr($IMG,$P,2));
               $COLOR[1] = $PALETTE[$COLOR[1]+1];
            }
            elseif ($BMP['bits_per_pixel'] == 8)
            {
               $COLOR = unpack("n",$VIDE.substr($IMG,$P,1));
               $COLOR[1] = $PALETTE[$COLOR[1]+1];
            }
            elseif ($BMP['bits_per_pixel'] == 4)
            {
               $COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
               if (($P*2)%2 == 0) $COLOR[1] = ($COLOR[1] >> 4) ; else $COLOR[1] = ($COLOR[1] & 0x0F);
               $COLOR[1] = $PALETTE[$COLOR[1]+1];
            }
            elseif ($BMP['bits_per_pixel'] == 1)
            {
               $COLOR = unpack("n",$VIDE.substr($IMG,floor($P),1));
               if    (($P*8)%8 == 0) $COLOR[1] =  $COLOR[1]        >>7;
               elseif (($P*8)%8 == 1) $COLOR[1] = ($COLOR[1] & 0x40)>>6;
               elseif (($P*8)%8 == 2) $COLOR[1] = ($COLOR[1] & 0x20)>>5;
               elseif (($P*8)%8 == 3) $COLOR[1] = ($COLOR[1] & 0x10)>>4;
               elseif (($P*8)%8 == 4) $COLOR[1] = ($COLOR[1] & 0x8)>>3;
               elseif (($P*8)%8 == 5) $COLOR[1] = ($COLOR[1] & 0x4)>>2;
               elseif (($P*8)%8 == 6) $COLOR[1] = ($COLOR[1] & 0x2)>>1;
               elseif (($P*8)%8 == 7) $COLOR[1] = ($COLOR[1] & 0x1);
               $COLOR[1] = $PALETTE[$COLOR[1]+1];
            }
            else
               die('3');
            imagesetpixel($res,$X,$Y,$COLOR[1]);
            $X++;
            $P += $BMP['bytes_per_pixel'];
         }
         $Y--;
         $P+=$BMP['decal'];
      }

      //Fermeture du fichier
      fclose($f1);

      return $res;
   }



}