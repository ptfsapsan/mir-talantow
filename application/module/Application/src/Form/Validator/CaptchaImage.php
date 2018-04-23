<?

namespace Application\Form\Validator;

use Zend\Captcha\AbstractWord,
   Zend\Captcha\Image;


class CaptchaImage extends Image{

   public function __construct(){
      self::$C = ["2","3","4","5","6","7","8","9"];
      self::$V = ["2","3","4","5","6","7","8","9"];
      $this->setUseNumbers(false);
      $options = array(
         'font' => $_SERVER['DOCUMENT_ROOT'].'/fonts/ALGER.TTF',
         'imgDir' => IMG.'/captcha/',
         'wordLen' => 4,
         'timeout' => 1800,
         'dotNoiseLevel' => 20,
         'lineNoiseLevel' => 2,
         'messages' => array(
            AbstractWord::MISSING_VALUE =>
               AbstractWord::MISSING_VALUE,
            AbstractWord::MISSING_ID    =>
               AbstractWord::MISSING_ID,
            AbstractWord::BAD_CAPTCHA   =>
               'Неверный код капчи',
         ),
      );
      parent::__construct($options);
   }

}