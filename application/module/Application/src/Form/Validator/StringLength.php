<?

namespace Application\Form\Validator;

use Zend\Validator\StringLength as SL;


class StringLength extends SL{

   public function __construct($tr, $options = array()){
      $this->messageTemplates = array(
         self::INVALID => $tr->translate(self::INVALID),
         self::TOO_SHORT => $tr->translate(self::TOO_SHORT),
         self::TOO_LONG => $tr->translate(self::TOO_LONG),
      );
      parent::__construct($options);
   }

}