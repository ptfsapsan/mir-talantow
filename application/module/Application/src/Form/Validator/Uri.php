<?

namespace Application\Form\Validator;


class Uri extends \Zend\Validator\Uri{

   public function __construct($tr){

      $this->messageTemplates = array(
         self::INVALID => $tr->translate(self::INVALID),
         self::NOT_URI => $tr->translate(self::NOT_URI),
      );
      parent::__construct();
   }

}