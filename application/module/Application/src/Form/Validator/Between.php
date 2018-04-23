<?

namespace Application\Form\Validator;


class Between extends \Zend\Validator\Between{

   public function __construct($tr, $options){

      $this->messageTemplates = array(
         self::NOT_BETWEEN => $tr->translate(self::NOT_BETWEEN),
         self::NOT_BETWEEN_STRICT => $tr->translate(self::NOT_BETWEEN_STRICT),
      );
      parent::__construct($options);
   }

}